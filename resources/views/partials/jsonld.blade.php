@php
    $customJsonLd = null;
    if (isset($post) && !empty($post->schema_jsonld)) {
        $decodedJsonLd = json_decode((string) $post->schema_jsonld, true);
        if (is_array($decodedJsonLd) && !empty($decodedJsonLd)) {
            $customJsonLd = $decodedJsonLd;
        }
    }

    $publisherName = setting('seo_publisher_name', setting('site_title'));
    $publisherLogo = setting('seo_publisher_logo', setting('seo_og_image'));
    $isArticle = isset($post);
    $authorName = $isArticle ? ($post->user?->name ?? setting('seo_meta_author')) : setting('seo_meta_author');
    $section = $isArticle ? ($post->category?->name ?? setting('seo_article_section_default')) : setting('seo_article_section_default');
    $headline = $isArticle ? $post->title : setting('site_title');
    $description = $isArticle
        ? ($post->seo_description ?: ($post->summary ?? strip_tags($post->content ?? '')))
        : setting('seo_meta_description');
    $image = $isArticle ? ($post->image ? asset('storage/' . $post->image) : $publisherLogo) : $publisherLogo;
    $publishedAt = $isArticle ? ($post->published_at ?? $post->created_at) : null;
    $updatedAt = $isArticle ? ($post->updated_at ?? $post->created_at) : null;
    $articleUrl = $isArticle ? $post->frontend_url : url('/');
    $keywords = $isArticle ? ($post->focus_keywords ?: $post->tags) : setting('seo_meta_keywords');
@endphp

<script type="application/ld+json">
{!! json_encode(
    $customJsonLd ?: [
        '@context' => 'https://schema.org',
        '@type' => $isArticle ? 'NewsArticle' : 'WebSite',
        'mainEntityOfPage' => $articleUrl,
        'headline' => $headline,
        'description' => $description,
        'image' => $image,
        'datePublished' => $publishedAt?->toAtomString(),
        'dateModified' => $updatedAt?->toAtomString(),
        'articleSection' => $section,
        'keywords' => $keywords,
        'about' => $isArticle && is_array($post->seo_entities) ? collect($post->seo_entities)->map(fn ($v) => ['@type' => 'Thing', 'name' => $v])->values()->all() : null,
        'author' => setting('seo_article_author_enabled', '1') === '1' ? [
            '@type' => 'Person',
            'name' => $authorName,
        ] : null,
        'publisher' => [
            '@type' => 'Organization',
            'name' => $publisherName,
            'logo' => [
                '@type' => 'ImageObject',
                'url' => $publisherLogo,
            ],
        ],
    ],
    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
) !!}
</script>

