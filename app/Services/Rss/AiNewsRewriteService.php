<?php

namespace App\Services\Rss;

use App\Models\NewsPoolItem;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AiNewsRewriteService
{
    private const INPUT_SCHEMA_VERSION = 'v7';

    public function rewrite(NewsPoolItem $item): array
    {
        if ((string) setting('ai_news_enabled', '0') !== '1') {
            return ['ok' => false, 'error' => 'AI haber duzenleme kapali.'];
        }

        $style = trim((string) setting('ai_news_prompt_style', 'Tarafsiz, bilgi odakli, Turkce haber dili.'));
        $rawTitle = $item->raw_title ?: $item->title;
        $rawSummary = $item->raw_summary ?: $item->summary;
        $rawContent = $item->raw_content ?: $item->content;

        [$cleanTitle, $cleanSummary, $cleanContent, $inputNotes] = $this->prepareAiInput(
            (string) $rawTitle,
            (string) $rawSummary,
            (string) $rawContent
        );

        $inputHash = $this->buildInputHash($cleanTitle, $cleanSummary, $cleanContent, $style);
        $meta = is_array($item->meta) ? $item->meta : [];
        $cachedHash = (string) ($meta['ai_input_hash'] ?? '');
        if ($item->ai_processed && $cachedHash === $inputHash && !empty($item->ai_title) && !empty($item->ai_content)) {
            return ['ok' => true, 'cached' => true];
        }

        [$system, $user] = $this->buildPrompts($style, $cleanTitle, $cleanSummary, $cleanContent, $inputNotes, (string) ($item->source_url ?? ''));

        $providers = $this->providerOrder();
        $errors = [];

        foreach ($providers as $provider) {
            $cfg = $this->providerConfig($provider);
            if (!$cfg['enabled']) {
                $errors[] = strtoupper($provider) . ': ayar eksik';
                continue;
            }

            try {
                $response = $this->sendRequest($provider, $cfg, $system, $user);
            } catch (ConnectionException $e) {
                $errors[] = strtoupper($provider) . ': baglanti hatasi';
                continue;
            } catch (\Throwable $e) {
                $errors[] = strtoupper($provider) . ': istek hatasi';
                continue;
            }

            if (!$response->successful()) {
                $errors[] = strtoupper($provider) . ': HTTP ' . $response->status();
                continue;
            }

            $payloadText = $this->extractContent($provider, $response);
            $data = json_decode($payloadText, true);
            if (!is_array($data)) {
                $errors[] = strtoupper($provider) . ': JSON parse hatasi';
                continue;
            }

            $title = $this->normalizePlainText((string) ($data['title'] ?? ''));
            $summary = $this->normalizePlainText((string) ($data['summary'] ?? ''));
            $body = $this->normalizeAiContent((string) ($data['content'] ?? ''));
            $body = $this->humanizeNewsBody($body);
            $focusKeywordsRaw = (string) ($data['focus_keywords'] ?? '');
            $body = $this->postProcessBodyForSeo($body, $focusKeywordsRaw, $title);
            $keywords = trim((string) ($data['keywords'] ?? ''));

            if ($title === '' || $body === '') {
                $errors[] = strtoupper($provider) . ': eksik alan';
                continue;
            }

            $summary = $this->ensureSummaryDistinctFromTitle($title, $summary, $body);
            $seoMeta = $this->extractSeoMeta($data, $title, $summary, $body, $keywords);

            $item->update([
                'ai_title' => Str::limit($title, 190, ''),
                'ai_summary' => $summary,
                'ai_content' => $body,
                'ai_keywords' => Str::limit($keywords, 1000, ''),
                'ai_processed' => true,
                'ai_status' => 'processed',
                'ai_error' => null,
                'meta' => array_merge($meta, [
                    'ai_provider' => $provider,
                    'ai_input_hash' => $inputHash,
                    'ai_input_schema' => self::INPUT_SCHEMA_VERSION,
                    'ai_updated_at' => now()->toDateTimeString(),
                    'ai_seo' => $seoMeta,
                ]),
            ]);

            return ['ok' => true, 'provider' => $provider];
        }

        return ['ok' => false, 'error' => 'Tum saglayicilar basarisiz: ' . implode(' | ', $errors)];
    }

    private function buildPrompts(
        string $style,
        string $title,
        string $summary,
        string $content,
        array $inputNotes = [],
        string $sourceUrl = ''
    ): array {
        $system = 'Sen Turkce dijital haber editorusun. Gorevin, verilen haberi EEAT ilkelerine uygun, dogrulanabilir ve okunakli bicimde yeniden yazmaktir. Her zaman yalnizca gecerli JSON dondur. Bilgileri onem sirasina gore ver, en carpici unsur ilk paragrafta olsun. Karmasik veri gruplarini maddeleme ile ver. Kritik aktorleri ve carpici rakamlari uygun yerlerde bold isle. Uzun metinlerde yaklasik her 150 kelimede bir anahtar kelime iceren H2 ara baslik kullan.';

        $notesText = empty($inputNotes) ? '- Yok' : collect($inputNotes)->map(fn ($n) => '- ' . $n)->implode("\n");
        $sourceUrlText = trim($sourceUrl) !== '' ? $sourceUrl : '-';

        $user = "GOREV\nVerilen haberi okunakli, SEO uyumlu ve editoryal kaliteyi koruyacak bicimde yeniden yaz.\n\nSTIL\n{$style}\n\nKAYNAK URL\n{$sourceUrlText}\n\nGIRDI NOTLARI\n{$notesText}\n\nKURALLAR\n- Yalnizca JSON dondur.\n- Turkce yaz.\n- Baslik net, SEO uyumlu, en fazla 90 karakter olsun.\n- Ozet 2-3 cumle olsun ve basligi tekrar etmesin.\n- Icerik en az 3 paragraf olsun.\n- Paragraflar mobilde duvar metin olmasin; paragraf basina en fazla 2-3 cumle kullan.\n- Bilgileri kronolojik degil, onem sirasina gore diz; en carpici unsur ilk paragrafta olsun.\n- Uzun metinlerde yaklasik her 150 kelimede bir anahtar kelime gecen <h2> ara baslik kullan.\n- Karmasik verileri (<ul><li>) ile maddeleme yap.\n- Girdide gecen telefon, e-posta, tarih/saat ve sayisal listeleri kaybetme; bu verileri oldugu gibi koruyup uygun yerde <ul><li> ile aktar.\n- Kritik aktorleri ve carpici rakamlari uygun yerde <strong> ile vurgula.\n- Art arda 3 cumle benzer uzunlukta olmasin; kisa ve uzun cumleleri harmanla.\n- Akisa uygun 1 retorik soru kullan.\n- Gecis ifadesi cesitliligi sagla; her haberde ayni gecis kalibini tekrar etme.\n- Etken catiyi one cikar; gereksiz edilgen yapi kullanma.\n- Kaynakta acik ifade varsa dogrudan alinti ekleyebilirsin.\n- Su kaliplari kullanma: dikkat cekti, gundem oldu, sonuc olarak, ozetle, dikkate deger, altini cizmek gerekirse, vurgulamak gerekir ki.\n- HTML/kod/menu/log artigi tasima.\n- Dogrulanamayan iddia veya veri uretme.\n- Eksik bilgide varsayim yapma.\n- Kaynak metni kopyalama; cumleleri ozgun kur.\n- keywords 5-8 etiket olsun.\n- focus_keywords 3-5 etiket olsun.\n\nCIKTI JSON SEMASI\n{\n  \"title\": \"...\",\n  \"summary\": \"...\",\n  \"content\": \"...\",\n  \"keywords\": \"etiket1,etiket2,etiket3\",\n  \"seo_title\": \"50-60 karakter\",\n  \"meta_description\": \"140-160 karakter\",\n  \"slug_suggestion\": \"kisa-url-onerisi\",\n  \"focus_keywords\": \"anahtar1,anahtar2,anahtar3\",\n  \"entities\": [\"Kisi/Kurum/Yer\"],\n  \"news_schema\": {\n    \"@context\": \"https://schema.org\",\n    \"@type\": \"NewsArticle\",\n    \"headline\": \"...\",\n    \"description\": \"...\"\n  }\n}\n\nGIRDI (TEMIZLENMIS)\n[Baslik]\n{$title}\n\n[Ozet]\n{$summary}\n\n[Icerik]\n{$content}";

        return [$system, $user];
    }

    private function providerOrder(): array
    {
        $chain = trim((string) setting('ai_news_fallback_chain', 'gemini,grok,groq'));
        $parts = collect(explode(',', $chain))
            ->map(fn ($v) => Str::lower(trim($v)))
            ->filter(fn ($v) => in_array($v, ['gemini', 'grok', 'groq'], true))
            ->unique()
            ->values()
            ->all();

        return empty($parts) ? ['gemini', 'grok', 'groq'] : $parts;
    }

    private function providerConfig(string $provider): array
    {
        if ($provider === 'gemini') {
            return [
                'enabled' => trim((string) setting('ai_news_api_key', '')) !== '',
                'api_key' => trim((string) setting('ai_news_api_key', '')),
                'base_url' => rtrim((string) setting('ai_news_base_url', 'https://generativelanguage.googleapis.com/v1beta'), '/'),
                'model' => trim((string) setting('ai_news_model', 'gemini-2.5-flash')),
            ];
        }

        if ($provider === 'grok') {
            return [
                'enabled' => trim((string) setting('ai_news_grok_api_key', '')) !== '',
                'api_key' => trim((string) setting('ai_news_grok_api_key', '')),
                'base_url' => rtrim((string) setting('ai_news_grok_base_url', 'https://api.x.ai/v1'), '/'),
                'model' => trim((string) setting('ai_news_grok_model', 'grok-2-latest')),
            ];
        }

        return [
            'enabled' => trim((string) setting('ai_news_groq_api_key', '')) !== '',
            'api_key' => trim((string) setting('ai_news_groq_api_key', '')),
            'base_url' => rtrim((string) setting('ai_news_groq_base_url', 'https://api.groq.com/openai/v1'), '/'),
            'model' => trim((string) setting('ai_news_groq_model', 'llama-3.3-70b-versatile')),
        ];
    }

    private function sendRequest(string $provider, array $cfg, string $system, string $user): Response
    {
        $request = Http::timeout(45)->withOptions(['verify' => $this->verifySsl()]);

        if ($provider === 'gemini') {
            return $request->post($cfg['base_url'] . '/models/' . $cfg['model'] . ':generateContent?key=' . urlencode($cfg['api_key']), [
                'systemInstruction' => ['parts' => [['text' => $system]]],
                'contents' => [[
                    'role' => 'user',
                    'parts' => [['text' => $user]],
                ]],
                'generationConfig' => [
                    'temperature' => 0.6,
                    'responseMimeType' => 'application/json',
                ],
            ]);
        }

        return $request
            ->withToken($cfg['api_key'])
            ->post($cfg['base_url'] . '/chat/completions', [
                'model' => $cfg['model'],
                'temperature' => 0.6,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $user],
                ],
            ]);
    }

    private function extractContent(string $provider, Response $response): string
    {
        if ($provider === 'gemini') {
            return (string) data_get($response->json(), 'candidates.0.content.parts.0.text', '');
        }

        return (string) data_get($response->json(), 'choices.0.message.content', '');
    }

    private function buildInputHash(string $title, string $summary, string $content, string $style): string
    {
        return hash('sha256', mb_strtolower(trim(self::INPUT_SCHEMA_VERSION . '|' . $title . '|' . $summary . '|' . $content . '|' . $style), 'UTF-8'));
    }

    private function verifySsl(): bool
    {
        return (string) setting('ai_news_verify_ssl', '0') === '1';
    }

    private function prepareAiInput(string $title, string $summary, string $content): array
    {
        $notes = [];

        $cleanTitle = $this->normalizePlainText($title);
        $cleanSummary = $this->normalizePlainText($summary);

        $hadHtml = preg_match('/<[^>]+>/', $content) === 1;
        $hasCodeBlocks = preg_match('/<(pre|code)[^>]*>/i', $content) === 1
            || preg_match('/```|<\\?php|function\\s+[a-zA-Z_]/i', $content) === 1;

        $cleanContent = $content;
        $cleanContent = preg_replace('/<(script|style|noscript|iframe|svg|form)[^>]*>.*?<\\/\\1>/is', ' ', $cleanContent) ?? $cleanContent;
        $cleanContent = preg_replace('/<(pre|code)[^>]*>.*?<\\/\\1>/is', ' [KOD BLOGU KALDIRILDI] ', $cleanContent) ?? $cleanContent;
        $cleanContent = preg_replace('/```.*?```/s', ' [KOD BLOGU KALDIRILDI] ', $cleanContent) ?? $cleanContent;
        $cleanContent = preg_replace('/<br\\s*\\/?>/i', "\n", $cleanContent) ?? $cleanContent;
        $cleanContent = preg_replace('/<\\/p>/i', "\n\n", $cleanContent) ?? $cleanContent;
        $cleanContent = strip_tags($cleanContent);
        $cleanContent = html_entity_decode($cleanContent, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $cleanContent = preg_replace('/\\b(anasayfa|giris yap|uye ol|tum haberler|devami icin tiklayin)\\b/ui', ' ', $cleanContent) ?? $cleanContent;
        $cleanContent = preg_replace('/https?:\\/\\/\\S+/i', ' ', $cleanContent) ?? $cleanContent;
        $cleanContent = $this->normalizePlainText($cleanContent, true);

        if ($hadHtml) {
            $notes[] = 'HTML icerik temizlendi';
        }
        if ($hasCodeBlocks) {
            $notes[] = 'Kod/teknik bloklar ayiklandi';
        }

        if ($cleanSummary === '' && $cleanContent !== '') {
            $cleanSummary = Str::limit($cleanContent, 280, '');
            $notes[] = 'Ozet bos oldugu icin icerikten ozet adayi olusturuldu';
        }

        if (mb_strlen($cleanContent, 'UTF-8') > 12000) {
            $cleanContent = mb_substr($cleanContent, 0, 12000, 'UTF-8');
            $notes[] = 'Icerik token/verim dengesi icin kisaltildi';
        }

        if ($cleanTitle === '') {
            $cleanTitle = 'Baslik belirtilmedi';
        }
        if ($cleanSummary === '') {
            $cleanSummary = 'Ozet belirtilmedi';
        }
        if ($cleanContent === '') {
            $cleanContent = $cleanSummary;
        }

        return [$cleanTitle, $cleanSummary, $cleanContent, $notes];
    }

    private function normalizePlainText(string $text, bool $preserveParagraphs = false): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[\\t\\x0B\\f]+/u', ' ', $text) ?? $text;

        if ($preserveParagraphs) {
            $text = preg_replace("/\\n{3,}/u", "\n\n", $text) ?? $text;
            $text = preg_replace('/[ ]{2,}/u', ' ', $text) ?? $text;
            $paragraphs = preg_split("/\\n{2,}/u", $text) ?: [];

            return collect($paragraphs)
                ->map(function ($paragraph) {
                    $paragraph = preg_replace('/\\s*\\n\\s*/u', ' ', (string) $paragraph) ?? (string) $paragraph;
                    $paragraph = preg_replace('/\\s{2,}/u', ' ', $paragraph) ?? $paragraph;
                    return trim($paragraph);
                })
                ->filter()
                ->values()
                ->implode("\n\n");
        }

        $text = preg_replace('/\\s+/u', ' ', $text) ?? $text;
        return trim($text);
    }

    private function normalizeAiContent(string $content): string
    {
        $content = trim($content);
        if ($content === '') {
            return '';
        }

        $content = preg_replace('/^### (.*)$/m', '<h3>$1</h3>', $content) ?? $content;
        $content = preg_replace('/^## (.*)$/m', '<h2>$1</h2>', $content) ?? $content;
        // Markdown bold ifadelerini HTML strong'a cevir.
        $content = preg_replace('/\\*\\*(.+?)\\*\\*/s', '<strong>$1</strong>', $content) ?? $content;
        $content = preg_replace('/__(.+?)__/s', '<strong>$1</strong>', $content) ?? $content;

        if (preg_match('/<(p|h[1-6]|ul|li|ol|strong)\\b/i', $content) === 1) {
            return $content;
        }

        $paragraphs = preg_split("/\\n{2,}/u", $content) ?: [];
        return collect($paragraphs)
            ->map(fn ($p) => trim((string) $p))
            ->filter()
            ->map(fn ($p) => '<p>' . e($p) . '</p>')
            ->implode("\n");
    }

    private function humanizeNewsBody(string $body): string
    {
        $body = trim($body);
        if ($body === '') {
            return '';
        }

        if (preg_match('/<p\\b[^>]*>/i', $body) !== 1) {
            return $body;
        }

        preg_match_all('/<p\\b[^>]*>(.*?)<\\/p>/is', $body, $matches);
        $paragraphTexts = collect($matches[1] ?? [])
            ->map(function ($innerHtml) {
                $text = html_entity_decode(strip_tags((string) $innerHtml), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $text = $this->normalizePlainText($text);
                $text = $this->replaceClichePhrases($text);

                return trim($text);
            })
            ->filter()
            ->values();

        if ($paragraphTexts->isEmpty()) {
            return $body;
        }

        $kept = [];
        foreach ($paragraphTexts as $paragraph) {
            $fingerprint = mb_strtolower($this->normalizePlainText($paragraph), 'UTF-8');
            $isDuplicate = false;
            foreach ($kept as $existing) {
                similar_text($fingerprint, mb_strtolower($this->normalizePlainText($existing), 'UTF-8'), $similarity);
                if ($similarity >= 95.0) {
                    $isDuplicate = true;
                    break;
                }
            }
            if (!$isDuplicate) {
                $kept[] = $paragraph;
            }
        }

        return collect($kept)
            ->map(fn ($paragraph) => '<p>' . e($paragraph) . '</p>')
            ->implode("\n");
    }

    private function replaceClichePhrases(string $text): string
    {
        $map = [
            'dikkat cekti' => ['one cikti', 'gundemin on siralarina tasindi', 'genis ilgi gordu'],
            'gundem oldu' => ['tartismalarin merkezine yerlesti', 'genis kesimlerce konusulmaya baslandi', 'gundeme tasindi'],
            'kamuoyunda yanki buldu' => ['kamuoyunda tartisildi', 'genis kitlelerde karsilik buldu', 'toplumda yankilandi'],
            'sonuc olarak' => ['durumun ozeti su ki', 'tablonun genelinde', 'nihayetinde'],
            'ozetle' => ['kisacasi', 'kisa bir cerceveyle', 'genel cercevede'],
            'vurgulamak gerekir ki' => ['acikca goruluyor ki', 'burada one cikan nokta su', 'net olarak soylenebilir ki'],
            'hayati onem tasiyor' => ['kritik bir esikte duruyor', 'belirleyici bir noktaya isaret ediyor', 'oncelikli risk olusturuyor'],
            'onemli bir rol oynuyor' => ['sureci dogrudan etkiliyor', 'belirleyici bir etki yaratiyor', 'dengeyi sekillendiriyor'],
            'ayrica' => ['ote yandan', 'diger tarafta', 'bunun yaninda'],
            'ek olarak' => ['bununla sinirli kalmadi', 'buna ilave olarak', 'devaminda'],
            'bununla birlikte' => ['hal boyle olunca', 'madalyonun diger yuzunde', 'durumun bir baska boyutunda'],
            'altini cizmek gerekirse' => ['acikca belirtmek gerekirse', 'net bir bicimde ifade etmek gerekirse', 'vurgulanmasi gereken nokta su'],
            'dikkate deger' => ['one cikan', 'kayda deger', 'goz ardi edilemeyecek'],
        ];

        foreach ($map as $from => $variants) {
            $replacement = $this->pickPhraseVariant($text, $from, $variants);
            $text = str_ireplace($from, $replacement, $text);
        }

        return $text;
    }

    private function pickPhraseVariant(string $text, string $needle, array $variants): string
    {
        if (empty($variants)) {
            return $needle;
        }

        $hash = abs(crc32(mb_strtolower($text . '|' . $needle, 'UTF-8')));
        $index = $hash % count($variants);
        return (string) $variants[$index];
    }

    private function postProcessBodyForSeo(string $body, string $focusKeywordsRaw, string $title): string
    {
        $body = $this->ensureParagraphReadability($body);
        $body = $this->enforceMinimumH2Density($body, $focusKeywordsRaw, $title);
        $body = $this->ensureFocusKeywordInH2($body, $focusKeywordsRaw, $title);
        $body = $this->limitStrongHighlights($body, 12);

        return $body;
    }

    private function ensureParagraphReadability(string $body): string
    {
        return preg_replace_callback('/<p\\b[^>]*>(.*?)<\\/p>/is', function ($matches) {
            $inner = (string) ($matches[1] ?? '');
            $plain = $this->normalizePlainText(strip_tags($inner));

            if ($plain === '' || mb_strlen($plain, 'UTF-8') <= 320) {
                return $matches[0];
            }

            $sentences = preg_split('/(?<=[.!?])\\s+/u', $plain) ?: [];
            $sentences = collect($sentences)->map(fn ($s) => trim((string) $s))->filter()->values();

            if ($sentences->isEmpty()) {
                return $matches[0];
            }

            $chunks = [];
            $buffer = '';
            $sentenceCount = 0;

            foreach ($sentences as $sentence) {
                $candidate = trim($buffer . ' ' . $sentence);
                if ($buffer !== '' && ($sentenceCount >= 2 || mb_strlen($candidate, 'UTF-8') > 260)) {
                    $chunks[] = $buffer;
                    $buffer = $sentence;
                    $sentenceCount = 1;
                    continue;
                }

                $buffer = $candidate;
                $sentenceCount++;
            }

            if ($buffer !== '') {
                $chunks[] = $buffer;
            }

            return collect($chunks)
                ->map(fn ($chunk) => '<p>' . e(trim((string) $chunk)) . '</p>')
                ->implode("\n");
        }, $body) ?? $body;
    }

    private function enforceMinimumH2Density(string $body, string $focusKeywordsRaw, string $title): string
    {
        $wordCount = count(preg_split('/\\s+/u', trim(strip_tags($body))) ?: []);
        if ($wordCount <= 0) {
            return $body;
        }

        $required = max(1, (int) ceil($wordCount / 150));
        $current = preg_match_all('/<h2\\b[^>]*>.*?<\\/h2>/is', $body, $existingHeadings);

        if ($current >= $required) {
            return $body;
        }

        preg_match_all('/<p\\b[^>]*>.*?<\\/p>/is', $body, $paragraphMatches, PREG_OFFSET_CAPTURE);
        $paragraphs = $paragraphMatches[0] ?? [];
        if (empty($paragraphs)) {
            return $body;
        }

        $needed = $required - $current;
        $keywords = $this->parseFocusKeywordList($focusKeywordsRaw);
        if (empty($keywords)) {
            $keywords = [$this->normalizePlainText($title)];
        }

        $insertions = [];
        $count = count($paragraphs);
        for ($i = 0; $i < $needed; $i++) {
            $idx = (int) floor((($i + 1) * $count) / ($needed + 1));
            $idx = max(0, min($count - 1, $idx));
            $offset = (int) $paragraphs[$idx][1];
            $kw = (string) ($keywords[$i % count($keywords)] ?? $title);
            $insertions[$offset] = ($insertions[$offset] ?? '') . '<h2>' . e($this->buildH2Heading($kw, $i + 1)) . "</h2>\n";
        }

        krsort($insertions);
        foreach ($insertions as $offset => $headingHtml) {
            $body = substr_replace($body, $headingHtml, (int) $offset, 0);
        }

        return $body;
    }

    private function ensureFocusKeywordInH2(string $body, string $focusKeywordsRaw, string $title): string
    {
        $keywords = $this->parseFocusKeywordList($focusKeywordsRaw);
        if (empty($keywords)) {
            return $body;
        }

        preg_match_all('/<h2\\b[^>]*>(.*?)<\\/h2>/is', $body, $matches);
        $h2Texts = $matches[1] ?? [];

        foreach ($h2Texts as $h2Text) {
            $h2Plain = mb_strtolower(strip_tags((string) $h2Text), 'UTF-8');
            foreach ($keywords as $keyword) {
                if (mb_strpos($h2Plain, mb_strtolower($keyword, 'UTF-8')) !== false) {
                    return $body;
                }
            }
        }

        $keyword = $keywords[0] ?? $title;
        $heading = '<h2>' . e($this->buildH2Heading($keyword, 1)) . '</h2>';

        if (preg_match('/<p\\b[^>]*>.*?<\\/p>/is', $body, $firstParagraph, PREG_OFFSET_CAPTURE) === 1) {
            $first = $firstParagraph[0];
            $insertAt = (int) $first[1] + strlen((string) $first[0]);
            return substr_replace($body, "\n" . $heading . "\n", $insertAt, 0);
        }

        return $heading . "\n" . $body;
    }

    private function limitStrongHighlights(string $body, int $max = 12): string
    {
        $count = 0;
        return preg_replace_callback('/<strong\\b[^>]*>(.*?)<\\/strong>/is', function ($m) use (&$count, $max) {
            $count++;
            if ($count <= $max) {
                return (string) $m[0];
            }

            return (string) ($m[1] ?? '');
        }, $body) ?? $body;
    }

    private function parseFocusKeywordList(string $raw): array
    {
        return collect(preg_split('/[,;]+/u', $raw) ?: [])
            ->map(fn ($v) => $this->normalizePlainText((string) $v))
            ->filter()
            ->unique()
            ->take(6)
            ->values()
            ->all();
    }

    private function buildH2Heading(string $keyword, int $sequence): string
    {
        $keyword = trim($keyword);
        if ($keyword === '') {
            return 'Gelismenin ayrintilari';
        }

        return $sequence % 2 === 0
            ? $keyword . ' cephesinde son durum'
            : $keyword . ' tarafinda one cikan basliklar';
    }

    private function extractSeoMeta(array $data, string $title, string $summary, string $body, string $keywords): array
    {
        $seoTitle = $this->normalizePlainText((string) ($data['seo_title'] ?? ''));
        $metaDescription = $this->normalizePlainText((string) ($data['meta_description'] ?? ''));
        $slugSuggestion = Str::slug((string) ($data['slug_suggestion'] ?? ''));
        $focusKeywords = $this->normalizeKeywordString((string) ($data['focus_keywords'] ?? ''), 5);
        $entities = $this->normalizeEntities($data['entities'] ?? []);
        $newsSchema = $this->normalizeNewsSchema($data['news_schema'] ?? null);

        if ($seoTitle === '') {
            $seoTitle = Str::limit($title, 60, '');
        } else {
            $seoTitle = Str::limit($seoTitle, 70, '');
        }

        if ($metaDescription === '') {
            $metaDescription = Str::limit($summary !== '' ? $summary : strip_tags($body), 160, '');
        } else {
            $metaDescription = Str::limit($metaDescription, 180, '');
        }

        if ($focusKeywords === '') {
            $focusKeywords = $this->normalizeKeywordString($keywords, 5);
        }

        if ($entities->isEmpty()) {
            $entities = collect($this->extractTitleEntities($title));
        }

        if ($slugSuggestion === '') {
            $slugSuggestion = Str::slug($title);
        }

        if (!is_array($newsSchema)) {
            $newsSchema = $this->fallbackNewsSchema($seoTitle, $metaDescription);
        }

        return [
            'seo_title' => $seoTitle,
            'meta_description' => $metaDescription,
            'slug_suggestion' => $slugSuggestion,
            'focus_keywords' => $focusKeywords,
            'entities' => $entities->values()->all(),
            'news_schema' => $newsSchema,
        ];
    }

    private function normalizeKeywordString(string $raw, int $max = 8): string
    {
        $parts = collect(preg_split('/[,;]+/u', $raw) ?: [])
            ->map(fn ($v) => $this->normalizePlainText((string) $v))
            ->filter()
            ->unique()
            ->take($max)
            ->values()
            ->all();

        return empty($parts) ? '' : implode(',', $parts);
    }

    private function normalizeEntities(mixed $entities): \Illuminate\Support\Collection
    {
        if (is_string($entities)) {
            $entities = preg_split('/[,;]+/u', $entities) ?: [];
        }

        if (!is_array($entities)) {
            return collect();
        }

        return collect($entities)
            ->map(fn ($v) => $this->normalizePlainText((string) $v))
            ->filter(fn ($v) => $v !== '' && mb_strlen($v, 'UTF-8') >= 2)
            ->unique()
            ->take(10)
            ->values();
    }

    private function extractTitleEntities(string $title): array
    {
        preg_match_all('/\\b[\\p{Lu}][\\p{L}\\p{M}0-9\\-]{2,}\\b/u', $title, $matches);

        return collect($matches[0] ?? [])
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->take(6)
            ->values()
            ->all();
    }

    private function normalizeNewsSchema(mixed $schema): ?array
    {
        if (is_string($schema)) {
            $decoded = json_decode($schema, true);
            if (is_array($decoded)) {
                $schema = $decoded;
            }
        }

        if (!is_array($schema)) {
            return null;
        }

        $headline = $this->normalizePlainText((string) ($schema['headline'] ?? ''));
        $description = $this->normalizePlainText((string) ($schema['description'] ?? ''));
        if ($headline === '' && $description === '') {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => Str::limit($headline, 170, ''),
            'description' => Str::limit($description, 220, ''),
        ];
    }

    private function fallbackNewsSchema(string $headline, string $description): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => Str::limit($headline, 170, ''),
            'description' => Str::limit($description, 220, ''),
        ];
    }

    private function ensureSummaryDistinctFromTitle(string $title, string $summary, string $body): string
    {
        $summary = $this->normalizePlainText($summary);
        if ($summary === '') {
            return $this->buildSummaryFromBody($body, $title);
        }

        $titleNorm = mb_strtolower($title, 'UTF-8');
        $summaryNorm = mb_strtolower($summary, 'UTF-8');
        similar_text($titleNorm, $summaryNorm, $similarity);

        if ($similarity >= 80.0 || mb_strpos($summaryNorm, $titleNorm) !== false) {
            return $this->buildSummaryFromBody($body, $title);
        }

        return $summary;
    }

    private function buildSummaryFromBody(string $body, string $title): string
    {
        $plain = $this->normalizePlainText(strip_tags($body));
        if ($plain === '') {
            return '';
        }

        $sentences = preg_split('/(?<=[.!?])\\s+/u', $plain) ?: [];
        $sentences = collect($sentences)
            ->map(fn ($s) => trim((string) $s))
            ->filter()
            ->values();

        if ($sentences->isEmpty()) {
            return Str::limit($plain, 220, '');
        }

        $titleNorm = mb_strtolower($this->normalizePlainText($title), 'UTF-8');
        $picked = [];
        foreach ($sentences as $sentence) {
            if (count($picked) >= 2) {
                break;
            }
            $sentenceNorm = mb_strtolower($sentence, 'UTF-8');
            if ($titleNorm !== '' && mb_strpos($sentenceNorm, $titleNorm) !== false) {
                continue;
            }
            $picked[] = $sentence;
        }

        if (empty($picked)) {
            $picked[] = $sentences->first();
        }

        return Str::limit(implode(' ', $picked), 260, '');
    }
}

