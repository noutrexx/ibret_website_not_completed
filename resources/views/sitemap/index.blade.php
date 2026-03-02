<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

    <url>
        <loc>{{ url('/') }}</loc>
        <changefreq>hourly</changefreq>
        <priority>1.0</priority>
    </url>

    @foreach($posts as $post)
        <url>
            <loc>{{ $post->frontend_url }}</loc>
            <lastmod>{{ $post->updated_at->toAtomString() }}</lastmod>
            <changefreq>daily</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach

</urlset>


