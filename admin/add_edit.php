<?php
include "include/page_template.php";
include "include/blogdoc.php";

$head_elements = <<<HEAD
	<link rel="stylesheet" href="/include/hashify/hashify-editor.css" media="screen" />
	<link rel="stylesheet" href="/include/fileuploader/fileuploader.css" media="screen" />
	<script src="/include/hashify/hashify-editor.min.js"></script>
	<script src="/include/fileuploader/fileuploader.min.js"></script>
	<script src="/include/js/text_selection.min.js"></script>
HEAD;

#we run some additional scripts on the admin page
print_header("", $head_elements);
?>
<p>
	<a href="/admin"><-- back to list</a>
</p>
<?php

#id is passed in
isset($_GET['id']) ? $id = $_GET['id'] : $id=null;

#if we're posting a form
if (isset($_POST['submit'])) {
	if (isset($_POST['id'])) { #we are editing an existing doc
		$document = BlogDoc::load_from_id($_POST['id']);
	} else { #we are creating a new one
		$document = new BlogDoc();
	}
	$document->set_full_text($_POST['full_text']);
	$document->save();
	$id = $document->get_id();
	print "<p class=\"oblique\">document saved</p>\n";
} else { #we're seeing this form for the first time
	#this line either loads from a file or creates a new empty blogdoc
	$document = BlogDoc::load_from_id($id);
}

?>

<fieldset>
<legend><?php isset($id) ? print "Edit" : print "Enter"; ?> Document Data</legend>
	<form action="" method="post">
		<TEXTAREA id="editor" name="full_text" rows="30" cols="77"><?php if (isset($id)) { 
			print($document->get_full_text()); 
			} else { 
				print "%title | INSERT TITLE HERE  \n";
				print "%context | INSERT CONTEXT HERE  \n";
				print "%date | ".date("Y.m.d")."  \n";
				print "%tags | #separate, #withcommas  \n\n";
			} #end if ?></TEXTAREA>
			
	<?php if (isset($id)) { #then pass the id as a form parameter ?>
		<input type="hidden" name="id" value="<?php print $id; ?>" />
	<?php } #end if ?>
		<p><input type="submit" name="submit" value="Save" /></p>
	</form>

	<p><a href="delete.php?id=<?php print $id; ?>">delete this document</a></p>

	<div id="file-uploader">
		<noscript><p>Please enable JavaScript to use file uploader.</p></noscript>         
	</div>

	<?php
	#print links to any uploaded files, so that they can be referred to
	#	in the document
	if ($id AND $file_list = get_file_list($id)) {
		print "<h3>Uploaded Files</h3>\n<ul>\n";
		foreach ($file_list as $the_file) {
			printf("<li><a href=# onClick=\"add_markdown_link('%s')\">%s</a>",
					$the_file, $the_file);
			printf(" <a href=\"/admin/delete_file.php?doc_id=%s&file_name=%s\">[Delete]</a></li>\n",
					$id, $the_file);
		}
		print "</ul>\n";
	}
	print_validation_links($id); 
	?>
</fieldset>

<?php print $document->get_body_html(); ?>

<script>
	//this is for the edit icons and "preview" in the textarea
	Hashify.editor("editor", true, function () {});

	//this is for the "upload files" functionality
	var uploader = new qq.FileUploader({
		// pass the dom node (ex. $(selector)[0] for jQuery users)
		element: document.getElementById('file-uploader'),
		// path to server-side upload script
		action: '/include/fileuploader/fileuploader.php',
		params: { doc_id: '<?php echo $id; ?>' },
		fileTemplate: '<li>' +
			  '<a onClick="" class="qq-upload-file"></a>' +
			  '<span class="qq-upload-spinner"></span>' +
			  '<span class="qq-upload-size"></span>' +
			  '<a class="qq-upload-cancel" href="#">Cancel</a>' +
			  '<span class="qq-upload-failed-text">Failed</span>' +
			  '<a class="qq-upload-delete" href="#">[Delete]</a>' +
		   '</li>'
	}); 

	// function to add markdown links to images when you click the image name
	function add_markdown_link(fileName) {
		var the_element = document.getElementById("editor");
		var url = "http://donaldmerand.com/files/<?php echo $id; ?>/" + escape(fileName);
		var the_link = "[INSERT FILE CAPTION](" + url + ")\n\n";
		replace_selection("editor",the_link);
	}

</script>

<?php 
print_footer();



#PHP FUNCTIONS FOLLOW

#This function just prints some links to validate the document being edited
#	against some web validation services. Some links are document-specific,
#	and some are site-wide.
function print_validation_links($document_id) { ?>
	<p>
		validate: 
		<a href="http://feedvalidator.org/check.cgi?url=http%3A//donaldmerand.com/rss/" target="_blank">[rss]</a>
		<a href="http://validator.w3.org/check?uri=<?php 
				print urlencode("http://donaldmerand.com/view/$document_id"); ?>" target="_blank">[this page html]</a>
		<a href="http://jigsaw.w3.org/css-validator/validator?uri=donaldmerand.com&profile=css3&usermedium=all&warning=1&vextwarning=" target="_blank">[css]</a>
	</p>
<?php } ?>
