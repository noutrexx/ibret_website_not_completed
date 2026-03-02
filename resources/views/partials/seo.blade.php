@php
    $seoTitle = $seoTitle ?? setting('site_title');
    $seoDescription = $seoDescription ?? setting('seo_meta_description');
    $seoKeywords = $seoKeywords ?? setting('seo_meta_keywords');
    $seoImage = $seoImage ?? setting('seo_og_image');

    $seoMetaTitle = setting('seo_meta_title');
    if (empty($seoTitle) && !empty($seoMetaTitle)) {
        $seoTitle = $seoMetaTitle;
    }

    if (!empty($seoMetaTitle) && str_contains($seoMetaTitle, '{title}')) {
        $seoTitle = str_replace('{title}', $seoTitle, $seoMetaTitle);
    }

    $seoUrl = url()->current();

    $canonicalRoot = setting('seo_canonical_root');
    if (!empty($canonicalRoot)) {
        $path = request()->path();
        $path = $path === '/' ? '' : $path;
        $seoUrl = rtrim($canonicalRoot, '/') . '/' . ltrim($path, '/');
    }

    if (setting('seo_strip_query_params', '0') === '1') {
        $seoUrl = strtok($seoUrl, '?');
    }

    $robots = setting('seo_meta_robots');
    if (empty($robots)) {
        $robots = setting('robots_index', 'index') . ',' . setting('robots_follow', 'follow');
    }

    $ogType = setting('seo_og_type');
    if (empty($ogType)) {
        $ogType = isset($post) ? 'article' : 'website';
    }
@endphp

<title>{{ $seoTitle }}</title>

<meta name="description" content="{{ $seoDescription }}">
<meta name="keywords" content="{{ $seoKeywords }}">
@if(setting('seo_meta_author'))
<meta name="author" content="{{ setting('seo_meta_author') }}">
@endif

<meta name="robots" content="{{ $robots }}">
<link rel="canonical" href="{{ $seoUrl }}">

@if(setting('seo_news_keywords_enabled', '0') === '1')
<meta name="news_keywords" content="{{ setting('seo_news_default_keywords') }}">
@endif

<!-- Open Graph -->
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:image" content="{{ $seoImage }}">
<meta property="og:url" content="{{ $seoUrl }}">
<meta property="og:site_name" content="{{ setting('seo_og_site_name', setting('site_title')) }}">
@if(setting('seo_og_locale'))
<meta property="og:locale" content="{{ setting('seo_og_locale') }}">
@endif
@if(setting('seo_og_image_width'))
<meta property="og:image:width" content="{{ setting('seo_og_image_width') }}">
@endif
@if(setting('seo_og_image_height'))
<meta property="og:image:height" content="{{ setting('seo_og_image_height') }}">
@endif

<!-- Twitter -->
<meta name="twitter:card" content="{{ setting('seo_twitter_card', 'summary_large_image') }}">
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDescription }}">
<meta name="twitter:image" content="{{ $seoImage }}">
@if(setting('seo_twitter_site'))
<meta name="twitter:site" content="{{ setting('seo_twitter_site') }}">
@endif
@if(setting('seo_twitter_creator'))
<meta name="twitter:creator" content="{{ setting('seo_twitter_creator') }}">
@endif

@if(setting('seo_jsonld_enabled', '0') === '1')
    @include('partials.jsonld')
@endif
