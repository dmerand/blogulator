<?php
include "markdown.php";

class BlogDoc {
	#many functions refer to the cache
	#cache format is: id | title | context
	const data_dir = "data/";
	const file_extension = "txt";
	const cache_file = "cache";
	const field_separator = " | ";

	private $body = ""; #the actual text of the document, markdown format
	private $body_html = ""; #the text, converted from markdown to html
	private $id = ""; #unique identifier for this document
	private $keys = array(); #meta values such as title, author, etc.

	#when instantiated, set the local filename to an arbitrary string
	function __construct() {
		$this->id = time();
		if (!is_dir(self::data_dir)) { 
			mkdir(self::data_dir);
		}
	}

	#delete this file, and the cache entry for it
	function destroy() {
		unlink($this->get_file_name());
		self::remove_from_cache($this);
	}

	#set the ID for a blogdoc. 
	private function set_id($id) {
		$this->id = $id;
	}

	#ID is file_name minus the extension
	public function get_id() {
		return $this->id;
	}

	private function get_file_name() {
		return self::determine_file_name($this->get_id());
	}

	#need the ability to add eg. author, title, creation date, etc.
	#anything but the body text and the filename is (optional) metadata
	public function set_metadata($key, $value) {
		$this->keys[$key] = $value;
	}

	#retrieve a piece of metadata based on a key
	public function get_metadata($key) {
		$rval = isset($this->keys[$key]) ? $this->keys[$key] : "";
		return rtrim($rval);
	}

	#return _all_ the metadata
	public function get_all_metadata() { return $this->keys; }

	#body text is assumed to be in markdown format
	public function set_body($text) {
		$this->body = $text;
		$this->body_html = markdown($text);
	}

	#get the body text as entered
	public function get_body() {
		return $this->body;
	}

	#get the body text as html
	public function get_body_html() {
		return $this->body_html;
	}

	#set the body and key values based on the full text
	public function set_full_text($the_text) {
		$the_array = explode("\n", $the_text);
		$body="";
		#clear out the old keys
		$this->keys = array();
		#now loop through each line of the text
		foreach($the_array as $line_num => $line) {
			if (substr($line, 0, 1) == "%") { #we have a key
				#read the values into an array (there should be two)
				$xpl = explode(self::field_separator, $line);
				#get rid of the "%" character at the beginning
				$key = substr($xpl[0], 1);
				#now actually set the metadata
				$this->set_metadata($key, $xpl[1]);
			} else { #a line of body text
				$body = $body . $line; 
				if ($line_num != count($the_array)-1) { $body .= "\n"; }
			}
		}
		#the body is whatever in the file is not a key
		$this->set_body($body);
	}

	#get the fulltext of the document file as saved to disk
	public function get_full_text() {
		return implode("", file($this->get_file_name()));
	}

	#save the document to disk.
	public function save() {
		#open file for writing at the top. re-write from scratch
		$fp = fopen ($this->get_file_name(), "w");
		
		#write any metadata
		foreach ($this->keys as $key => $value) {
			fwrite($fp, "%$key".self::field_separator."$value\n");
		}
		#now the body
		fwrite($fp, $this->body);

		#always close after opening!
		fclose ($fp);

		#we update the cache file when a file is changed/added
		self::update_cache($this);
	}

	#send a document ID, get a Document back. If no document found, get empty one.
	public static function load_from_id($id) {
		return self::load_from_file(self::determine_file_name($id));
	}

	#read a document from a file_name. If no doc found, returns an empty one.
	public static function load_from_file($file_name) {
		#return false if file doesn't exist
		if (!file_exists($file_name)) { return new BlogDoc(); }

		#create a new empty blogdoc in which to store the file
		$d = new BlogDoc();
		#remove the data dir, and file extension from the file, and that's the ID
		$id = self::strip_file_name($file_name);
		$d->set_id($id);

		#now read from the actual file into an array
		$fp = file($file_name);
		#the full text of the blogdoc is what's read from the file
		#setting it will also set the keys and the body of the file
		$d->set_full_text(implode("", $fp));
		return $d;
	}

	#return an array of all blogdocs in the cache
	public static function list_blogdocs($context="", $limit=0) {
		$bdoc_list = array();
		$cf = file(BlogDoc::cache_file);
		rsort($cf);
		foreach ($cf as $line_num => $line) {
			$xpl = explode(BlogDoc::field_separator, $line);
				if ((rtrim($xpl[0]) != "") AND 
						(($context != "" and stristr($xpl[2],$context)) OR
						($context == ""))) { 
					$bdoc_list[] = BlogDoc::load_from_id($xpl[0]);
				}
		}
		if ($limit > 0) { $bdoc_list = array_slice($bdoc_list, 0, $limit); }
		return $bdoc_list;
	}

	#returns an array of blogdocs that have content that matches the query
	# query is a grep regular expression
	public static function search_blogdocs($query) {
		$bdoc_array = array();
		#grep -il = extended Regex, case-insensitive search
		#	just show the filenames and not the line
		$the_command = sprintf("grep -Eil %s %s*.%s | sort -r", 
				escapeshellcmd($query), 
				BlogDoc::data_dir, 
				BlogDoc::file_extension);
		exec($the_command, $bdoc_array);
		foreach ($bdoc_array as $index => $file) {
			$bdoc_array[$index] = BlogDoc::load_from_file($file);
		}
		return $bdoc_array;
	}

	#file name is constructed from the ID, using the data dir and extension
	private function determine_file_name($id) { 
		return sprintf("%s%s.%s", self::data_dir, $id, self::file_extension);
	}

	#given a full file name/directory, return just the "ID" portion.
	private function strip_file_name($file_name) {
		#get rid of the data directory
		$file_name = str_replace(self::data_dir, "", $file_name);
		#get rid of the file extension
		$file_name = str_replace("." . self::file_extension, "", $file_name);
		return $file_name;
	}

	#add a document to the cache. update the cache if the document is already in there
	private function update_cache($document) {
		$match_found = false; #file_name does not exist in the cache until found

		touch(self::cache_file); #make sure the cache exists

		#load the existing cache file into an array
		$cache_file = file(self::cache_file);
		#loop through each line
		foreach ($cache_file as $line_num => $line) {
			#split out the line based on the field separator
			$xpl = explode(self::field_separator, $line);
			#check to see if we have a file name match
			if ($xpl[0] == $document->get_id()) {
				$match_found = true;
				$xpl[1] = $document->get_metadata("title"); 
				$xpl[2] = $document->get_metadata("context"); 
				#replace the line with whatever we passed in
				$cache_file[$line_num] = implode(self::field_separator, $xpl) . "\n";
			}
		}

		#implode the cache contents array back into one big string
		$new_cache = implode("", $cache_file);

		#if no match was found in the cache, add the file to the bottom
		if (!$match_found) {
			$new_cache .= sprintf("%s%s%s%s%s\n",
					$document->get_id(),
					self::field_separator,
					$document->get_metadata("title"),
					self::field_separator,
					$document->get_metadata("context"));
		}

		#now actually write the results to disk
		$cf = fopen(self::cache_file, "w");
		fwrite($cf, $new_cache);
		fclose($cf);
	}

	#remove a document from the cache
	private function remove_from_cache($document) {
		$cache_file = file(self::cache_file);
		$cache_array = array();
		#loop through the lines, adding to a new cache array.
		foreach($cache_file as $line_num =>$line) {
			#add unless the line matches the document ID
			if (strpos($line, $document->id) === FALSE) {
				$cache_array[] = $line;
			}
		}
		$cf = fopen(self::cache_file, "w");
		fwrite($cf, implode("", $cache_array));
		fclose($cf);
	}

	#re-calculate the cache
	public static function recalculate_cache() {
		#first delete the cache file
		if (file_exists(self::cache_file)) {
			$cf = fopen(self::cache_file, "w");
			fwrite($cf, "");
			fclose($cf);
		}
		#now re-read the data directory
		if ($handle = opendir(self::data_dir)) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
					$doc = self::load_from_file(self::data_dir.$file);
					printf("file '%s' read<br />", $file);
					printf("document '%s' added to cache<br />", $doc->get_metadata("title"));
					self::update_cache($doc);
				}
			}
			closedir($handle);
		}
	}

} #end class

?>
