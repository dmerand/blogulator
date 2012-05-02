<?php
include "include/page_template.php";
include "include/blogdoc.php";

isset($_GET['id']) ? $id = $_GET['id'] : redirect_to_page('/index');

$doc = BlogDoc::load_from_id($id);

print_header($doc->get_metadata("context"));
print "<p class=\"noprint\">\n";
printf ("\t<a href=\"/context/%s\">&larr; go back</a>\n", 
		strtolower($doc->get_metadata("context")));
print "</p>\n";
print_document($doc);
print_footer($doc->get_metadata("context"));
?>
