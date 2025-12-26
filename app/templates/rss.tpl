{literal}<?xml version="1.0" encoding="utf-8" ?>{/literal}
	<rss version="2.0"
		xmlns:content="http://purl.org/rss/1.0/modules/content/"
		xmlns:wfw="http://wellformedweb.org/CommentAPI/"
		xmlns:dc="http://purl.org/dc/elements/1.1/"
	>
	<channel>
		<title>{$feedtitle}</title>
		<link>{$feedlink}</link>
		<description>{$feeddesc}</description>
		<language>{$feedlang}</language>
		<lastBuildDate>{$feeddate}</lastBuildDate>
		{section name=i loop=$feeditems}
		<item>
			<title>{$feeditems[i].xmlitem_title}</title>
			<link>{$feeditems[i].xmlitem_link}</link>
			<pubDate>{$feeditems[i].xmlitem_pubDate}</pubDate>
			<author>{$feeditems[i].xmlitem_author}</author>
			<category>{$feeditems[i].xmlitem_category}</category>
			<guid isPermaLink="false">{$feeditems[i].xmlitem_guid}</guid>
			<description>{$feeditems[i].xmlitem_description}</description>
			<content:encoded><![CDATA[{$feeditems[i].xmlitem_content}]]></content:encoded>
		</item>
		{/section}
	</channel>
</rss>