<?php
include "include/page_template.php";
include "include/blogdoc.php";

isset($_GET['context']) ? $context = $_GET['context'] : $context = "";
$doc_list = BlogDoc::list_blogdocs($context);

print_header($context);

print "<div class=\"above-article\">\n";

#show a list of all available documents, if there are more than one
printf("<h1>Article Archive%s</h1>\n", 
		$context=="" ? $context : ": ". ucfirst($context));
list_documents($context, $doc_list);

print "</div><!--above-article-->\n";

print_footer($context);

?>
