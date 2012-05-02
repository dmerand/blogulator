<?php
include "include/blogdoc.php";
isset($_GET['context']) ? $context = ucfirst($_GET['context']) : $context = null;

header('Content-Type: application/rss+xml; charset=UTF-8');

print <<<THE_END
<?xml version="1.0"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
   <channel>
      <title>Donald Merand $context</title>
      <link>http://donaldmerand.com/</link>
      <description>Donald Merand: Writings, Code, and Other Works</description>
      <atom:link href="http://donaldmerand.com/rss/" rel="self" type="application/rss+xml" />
THE_END;


#list the 25 most recent news items
$doc_list = BlogDoc::list_blogdocs($context, 25);
foreach ($doc_list as $doc) {
	print "      <item>\n";
	printf("         <title>%s</title>\n", $doc->get_metadata("title")); 
	printf("         <link>%s</link>\n", "http://donaldmerand.com/view/" . $doc->get_id());
	printf("         <guid>%s</guid>\n", "http://donaldmerand.com/view/" . $doc->get_id());
	printf("         <description><![CDATA[ %s ]]></description>\n", $doc->get_body_html());
	printf("         <pubDate>%s</pubDate>\n", date("r", $doc->get_id()));
	print "      </item>\n";
}

?>
   </channel>
</rss>
