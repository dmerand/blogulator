<?php
#turn on page compression if applicable for _all_ served content
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
	ob_start("ob_gzhandler");
} else {
	ob_start();
}

function print_header($context="", $head_elements="") { ?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8" />
	<title>Donald Merand</title>
	<link rel="alternate" type="application/rss+xml" href="/rss/" />
	<link rel="shortcut icon" href="/favicon.ico" />
	<link rel="stylesheet" href="/include/css/style.css" media="all" />
	<link rel="stylesheet" href="/include/css/print.css" media="print" />
	<link rel="stylesheet" href="/include/prettify/prettify.css" media="all" />
<?php if ($head_elements != "") { print "$head_elements\n"; } ?>
	<script src="/include/prettify/prettify.js"></script>
</head>

<body onload="prettyPrint()">
	<div class="donald"><a href="/">Donald L. Merand</a></div>
	<div class="page">
		<nav class="noprint">
			<div class="context">
				<a href="/context/cv"<?php print select_it($context, "cv"); ?>>CV</a>
			</div>
			<div class="context">
				<a href="/context/code"<?php print select_it($context, "code"); ?>>Code</a>
			</div>
			<div class="context">
				<a href="/context/projects"<?php print select_it($context, "projects"); ?>>Projects</a>
			</div>
			<div class="context">
				<a href="/context/writing"<?php print select_it($context, "writing"); ?>>Writing</a>
			</div>
		</nav>
		<div class="clf"></div>

<?php } #print_header

function print_footer($context="") { ?>
		<footer>
			<form action="/search" method="post">
				<span class="noprint">
					<!--<input type="submit" value="search" />-->
					<input type="search" name="query" placeholder="search" />
					&nbsp;&nbsp;&nbsp;
				</span>
				<a href="&#x6D;&#x61;&#105;&#108;&#116;&#111;:&#100;&#108;&#109;&#64;&#x64;o&#110;&#x61;l&#100;&#x6D;&#x65;&#x72;&#x61;&#x6E;&#x64;&#x2E;&#x63;&#111;&#x6D;">&#100;&#108;&#109;&#64;&#x64;o&#110;&#x61;l&#100;&#x6D;&#x65;&#x72;&#x61;&#x6E;&#x64;&#x2E;&#x63;&#111;&#x6D;</a>
				<span class="noprint">
					 | <a href="/rss/<?php print $context; ?>">[rss feed<?php 
						if ($context != "") { print " for $context"; } ?>]</a>
				</span>
			</form>
			<p>This work is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/3.0/" target="_blank">Creative Commons Attribution-NonCommercial-ShareAlike 3.0 License</a>.</p>
		</footer>
	</div><!-- page -->
</body>

</html>
<?php } #footer

#print the contents of a docuemnt
function print_document($document) {
	print "<article>\n\t<header>\n";
	printf("\t\t<h1 class=\"article-title\">%s</h1>\n", $document->get_metadata("title"));
	$keys = $document->get_all_metadata();
	foreach($keys as $key => $value) {
		#don't print the "title" or "context" metadata
		if (!stristr($key, "title") AND !stristr($key, "context")) {
			#in HTML5, time/date is its own semantic element
			if (stristr($key, "date")) {
				printf("\t\t<time datetime=\"%s\" class=\"article-metadata\">%s</time>\n",
						str_replace(".", "-", rtrim($value)), 
						rtrim($value));
			} else {
				#print any other generic metadata
				printf("\t\t<div class=\"article-metadata\">%s: %s</div>\n",
						$key,
						rtrim($value));
			}
		}
	}
	print "\t</header>\n";
	print $document->get_body_html();
	print "</article>\n";
}

function print_document_link($document, $context="", $selected=false) {
	$selected==TRUE ? $sel_text = " class=\"selected\"" : $sel_text = "";
	printf("<a href=\"/view/%s\"%s><span class=\"oblique\">%s: </span>%s%s</a>\n",
			$document->get_id(),
			$sel_text,
			$document->get_metadata("date"),
			$document->get_metadata("title"),
			$context == "" ? " (".ucfirst($document->get_metadata("context").")"): "");
}

#print a list of documents, with links, from an array of documents
function list_documents($context="", $doc_list) {
	print "<ul>\n";
	foreach ($doc_list as $line_num=>$doc) { 
		print "<li>";
		#the first line is selected
		if($line_num==0) {
			print_document_link($doc, $context, TRUE);
		} else {
			print_document_link($doc, $context); 
		}	
		print "</li>\n";
	}
	print "</ul>\n";
}

#given a document ID, return any files that have been uploaded in association with it
function get_file_list($document_id) {
	$file_directory = sprintf("../files/%s/", $document_id);
	$file_list = array();
	if (is_dir($file_directory)) {
		foreach (new DirectoryIterator($file_directory) as $fileInfo) {
			if($fileInfo->isDot()) continue;
			$file_list[] = $fileInfo->getFilename();
		}
		return $file_list;
	} else { return false; }
}

#used to select an item, when compared against a match item
function select_it($item, $match) {
	$ret = stristr($item, $match) ? " class=\"selected\"" : "";
	return $ret;
}

#send a header to redirect to a page. defaults to the root of the current directory 
function redirect_to_page($the_path="/") {
  $host  = $_SERVER['HTTP_HOST'];
  $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
  $extra = $the_path;
  #header("Location: http://$host$uri/$extra");
  header("Location: $the_path");
}
?>
