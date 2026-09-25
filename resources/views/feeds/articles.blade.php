{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:media="http://search.yahoo.com/mrss/">
    <channel>
        <title>{{ $title }}</title>
        <link>{{ $link }}</link>
        <description>{{ $description }}</description>
        <language>id-ID</language>
        <lastBuildDate>{{ \Illuminate\Support\Carbon::parse($updated)->toRssString() }}</lastBuildDate>
        <atom:link href="{{ $self }}" rel="self" type="application/rss+xml" />
        @foreach ($articles as $article)
            @php($url = \App\Support\StructuredData::url($article->publicPath()))
            <item>
                <title>{{ $article->title }}</title>
                <link>{{ $url }}</link>
                <guid isPermaLink="true">{{ $url }}</guid>
                <pubDate>{{ ($article->published_at ?? $article->created_at)->toRssString() }}</pubDate>
                @if ($article->author)
                    <dc:creator>{{ $article->author->name }}</dc:creator>
                @endif
                @if ($article->category)
                    <category>{{ $article->category->name }}</category>
                @endif
                <description>{{ \App\Support\PageMeta::description($article->excerpt ?: $article->body) }}</description>
                @if ($cover = $article->getFirstMediaUrl('cover'))
                    <media:content url="{{ $cover }}" medium="image" />
                @endif
            </item>
        @endforeach
    </channel>
</rss>
