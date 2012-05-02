<?php
include "include/page_template.php";
include "include/blogdoc.php";

isset($_POST['query']) ? $query=$_POST['query'] : $query=null;

print_header();

#if we're returning a query via POST...
if (isset($query)) {
	$doc_list = BlogDoc::search_blogdocs($query);
	printf("<h3 class=\"noprint\">Search Results: \"%s\"</h3>", $query);
	#we don't want the document list printing, so...
	print "<div class=\"noprint\">\n";

	#show a list of all available documents, if there are more than one
	if (count($doc_list) > 1) {
		list_documents("", $doc_list);
	} else if (count($doc_list) == 1) {
		print_document($doc_list[0]);
	} else if (count($doc_list) == 0) {
		print "<div class=\"oblique\">No documents match your search query</div>";
	}
	print "</div><!--noprint-->\n";

	#then print the first document found, if any
}

print_footer();

?>
