<?php
include "../include/page_template.php";
include "../include/blogdoc.php";

print_header();

$doc_list = BlogDoc::list_blogdocs();
?>
<h1>Documents List</h1>
<p><a href="add_edit.php">[create a new document]</a></p>
<ul>
<?php
foreach($doc_list as $d) {
	printf("<li><a href=\"add_edit.php?id=%s\">%s: %s</a> (%s)</li>\n",
			$d->get_id(),
			$d->get_metadata("date"),
			$d->get_metadata("title"),
			strtolower($d->get_metadata("context")));
}
?>
</ul>

<?php print_footer(); ?>
