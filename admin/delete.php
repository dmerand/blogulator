<?php
include "include/page_template.php";
include "include/blogdoc.php";

if (isset($_GET["id"])) {
	$id = $_GET["id"];
} else {
	redirect_to_page("/admin");
}

#delete the document
$doc = BlogDoc::load_from_id($id);
$doc->destroy();

#delete any files associated with the document
$dir = "../files/$id/";
if (is_dir($dir)) {
	foreach (new DirectoryIterator($dir) as $fileInfo) {
		if($fileInfo->isDot()) continue;
		unlink($dir . $fileInfo->getFilename());
	}
	rmdir($dir);
}

redirect_to_page("/admin");
?>
