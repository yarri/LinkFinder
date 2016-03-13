<?php
/**
 * In a plain text document LinkFinder searches for URLs and email addresses and adds tags <a>..</a> around them
 *
 * Basic usage:
 *
 *   $lf = new LinkFinder();
 *   echo $lf->process('Welcome at www.example.com!'); // Welcome at <a href="http://www.example.com/">www.example.com</a>!
 *
 * Original regular expressions has been taken from a function html_activate_links() by Fredrik Kristiansen (russlndr at online.no) and
 * Albrecht Guenther (ag at phprojekt.de): http://www.zend.com/codex.php?id=395&single=1
 */
class LinkFinder{

	protected $_Options = null;

	function __construct($options = array()){

		// default options
		$this->_Options = array(
			"open_links_in_new_windows" => false,
			"link_class" => "",
			"mailto_class" => "",
			"escape_html_entities" => true,

			"link_template" => '<a href="%href%"%class%%target%>%url%</a>',
			"mailto_template" => '<a href="mailto:%mailto%"%class%>%address%</a>',
		);

		$this->_setOptions($options);
	}

	function setToOpenLinkInNewWindow($set = true){ $this->_setOption("open_links_in_new_windows",(bool)$set); }
	function setToNotOpenLinkInNewWindow(){ $this->_setOption("open_links_in_new_windows",false); }
	function setLinkClass($class){ $this->_setOption("link_class",(string)$class); }
	function setHrefClass($class){ return $this->setLinkClass($class); } // alias
	function setMailtoClass($class){ $this->_setOption("mailto_class",(string)$class); }
	function setLinkTemplate($template){ $this->_setOption("link_template",(string)$template); }
	function setMailtoTemplate($template){ $this->_setOption("mailto_template",(string)$template); }

	/**
	 * In the given text it searches for URLs and emails and adds <a> tags around them.
	 *
	 * @access public
	 * @param string $text					vstupni text
	 * @param array $options
	 * @return string
	 */
	function process($text,$options = array()){
		settype($text,"string");

		$options = $this->_getOptions($options);

		$_blank = "";
		if($options["open_links_in_new_windows"]){
			$_blank = " target=\"_blank\"";
		}

		$_href_class = "";
		if($options["link_class"]!=""){
			$_href_class = " class=\"$options[link_class]\"";
		}
		$_mailto_class = "";
		if($options["mailto_class"]!=""){
			$_mailto_class = " class=\"$options[mailto_class]\"";
		}

		$rnd = uniqid();
		$tr_table = $tr_table_rev = array();

		if($options["escape_html_entities"]){

			$text = $this->_escapeHtmlEntities($text);
			$tr_table = array(
				"&amp;" => "Xampicek{$rnd}X",
				"&lt;" => " .._XltX{$rnd}_.. ",
				"&gt;" => " .._XgtX{$rnd}_.. ",
			);

		}else{

			// building replacements for existing tags
			preg_match_all('/(<[a-z0-9]+(|\s[^<>]*)\/?>)/si',$text,$matches);
			foreach($matches[1] as $i => $match){
				$tr_table[$match] = ".._XtagX{$rnd}.{$i}_.."; // My photo is here: <img src="http://example.com/image.jpg" /> -> My photo is here: .._XtagX1234_..
			}

		}
		$text = strtr($text,$tr_table);
		$tr_table_rev = array_combine(array_values($tr_table),array_keys($tr_table));


		// novy kod - odstranuje tecku na konci url
		$replace_ar = array();

		preg_match_all("/(((f|ht){1}tps?:\\/\\/)[-a-zA-Z0-9@:%_+.~#?&\\/\\/=;]+)/i", $text, $matches);
		for($i=0;$i<sizeof($matches[1]);$i++){
			$key = trim($matches[1][$i]);
			$key = preg_replace("/\\.$/","",$key); // removing a dot at the of a link - it probably means end of the sentence
			$replace_ar[$key] = strtr($options["link_template"],array(
				"%href%" => $key,
				"%url%" => $key,
				"%class%" => $_href_class,
				"%target%" => $_blank,
			));
		}

		preg_match_all("/\b(www\\.[-a-zA-Z0-9@:%_+.~#?&\\/\\/=;]+)/i", $text, $matches);
		for($i=0;$i<sizeof($matches[1]);$i++){
			$key = trim($matches[1][$i]);
			$key = preg_replace("/\\.$/","",$key); // removing a dot at the of a link - it probably means end of the sentence
			$replace_ar[$key] = strtr($options["link_template"],array(
				"%href%" => "http://$key",
				"%url%" => $key,
				"%class%" => $_href_class,
				"%target%" => $_blank,
			));
		}

		// emails
		preg_match_all("/([_.0-9a-z-]+@([0-9a-z][0-9a-z-]+\\.)+[a-z]{2,5})/i", $text, $matches);
		for($i=0;$i<sizeof($matches[1]);$i++){
			$key = trim($matches[1][$i]);
			$replace_ar[$key] = strtr($options["mailto_template"],array(
				"%mailto%" => $key, 
				"%address%" => $key,
				"%class%" => $_mailto_class,
			));
		}

		$text = strtr($text,$replace_ar);

		$text = strtr($text,$tr_table_rev);

		return $text;
	}

	protected function _escapeHtmlEntities($text){
		$flags =  ENT_COMPAT;
		if(defined("ENT_HTML401")){ $flags = $flags | ENT_HTML401; }

 		// as of PHP5.4 the default encoding is UTF-8, it causes troubles in non UTF-8 applications,
		// I think that the encoding ISO-8859-1 works well in UTF-8 applications
		$encoding = "ISO-8859-1";

		return htmlspecialchars($text,$flags,$encoding);
	}

	protected function _setOptions($options){
		$options += $this->_Options;
		$this->_Options = $options;
	}

	protected function _setOption($key,$value){
		$this->_setOptions(array((string)$key => $value));
	}

	protected function _getOptions($options = array()){
		$options += $this->_Options;
		return $options;
	}
}
