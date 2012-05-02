<?php
include "include/page_template.php";

isset($_GET['doc_id']) ? $doc_id = $_GET['doc_id'] : $doc_id=null;
isset($_GET['file_name']) ? $file_name = $_GET['file_name'] : $file_name=null;

if (isset($doc_id) AND isset($file_name)) {
	#delete the file. only works from the admin/ context
	$file_path = sprintf("../files/%s/%s", $doc_id, $file_name);
	if (is_file ($file_path)) {
		unlink ($file_path);
	}
	redirect_to_page("/admin/add_edit.php?id=$doc_id");
} else {
	redirect_to_page("/admin/");
}
