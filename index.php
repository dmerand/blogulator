<?php
include "include/page_template.php";
include "include/blogdoc.php";

$doc_limit = 5; #number of documents to pull in the index.
isset($_GET['context']) ? $context = $_GET['context'] : $context = "";
$doc_list = BlogDoc::list_blogdocs($context, $doc_limit);

print_header($context);

#we don't want the document list printing, so...
print "<div class=\"noprint above-article\">\n";

#show a list of all available documents, if there are more than one
if (count($doc_list) > 1) {
	printf("<h1>%s Most Recent Articles%s</h1>\n", 
			count($doc_list) >= $doc_limit ? $doc_limit : count($doc_list),
			$context=="" ? $context : ": ". ucfirst($context));
	list_documents($context, $doc_list);
}

#show the "archive" link for anything but CV
if (!stristr($context, "cv")) {
	printf("<p><a href=\"/archive/%s\" class=\"oblique\">View All Articles %s</a></p>\n",
				$context,
				$context=="" ? $context : "in \"" . ucfirst($context) . "\"");
}
print "</div><!--above-article-->\n";

#then print the first document found, if any
if (isset($doc_list[0])) { print_document($doc_list[0]); }

print_footer($context);

?>
