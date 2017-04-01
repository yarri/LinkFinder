<?php
/**
 * In a plain text document LinkFinder searches for URLs and email addresses and adds tags <a>..</a> around them
 *
 * Basic usage:
 *
 *   $lf = new LinkFinder();
 *   echo $lf->process('Welcome at www.example.com!'); // Welcome at <a href="http://www.example.com/">www.example.com</a>!
 *
 * If you need something more:
 *
 *   $lf = new LinkFinder(array("attrs" => array("class" => "external-link", "rel" => "nofollow")));
 *   echo $lf->process('Welcome at www.example.com!'); // Welcome at <a class="external-link" href="http://www.example.com/" rel="nofollow">www.example.com</a>!
 *
 * You may found More examples at https://github.com/yarri/LinkFinder 
 *
 * Original regular expressions has been taken from a function html_activate_links() by Fredrik Kristiansen (russlndr at online.no) and
 * Albrecht Guenther (ag at phprojekt.de): http://www.zend.com/codex.php?id=395&single=1
 *
 * Source code of LinkFinder can be found at https://github.com/yarri/LinkFinder
 */
class LinkFinder{

	protected $_Options = null;

	function __construct($options = array()){

		// default options
		$this->_Options = array(

			// attributes for <a> and <a href="mailto:..."> elements
			"attrs" => array(),
			"mailto_attrs" => array(),

			"escape_html_entities" => true,
			"link_template" => '<a %attrs%>%url%</a>',
			"mailto_template" => '<a %attrs%>%address%</a>',

			// legacy options (try not to use them)
			"open_links_in_new_windows" => null, // true, false
			"link_class" => "",
			"mailto_class" => "",
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
		$attrs = $options["attrs"];
		$mailto_attrs = $options["mailto_attrs"];

		$rnd = uniqid();
		$tr_table = $tr_table_rev = array();

		if($options["escape_html_entities"]){

			$text = $this->_escapeHtmlEntities($text);
			$tr_table = array(
				"&amp;" => "Xampicek{$rnd}X",
				"&lt;" => " .._XltX{$rnd}_.. ",
				"&gt;" => " .._XgtX{$rnd}_.. ",
				"&quot;" => " .._XquotX{$rnd}_.. ",
			);

		}else{

			// building replacements for existing links (<a>...</a>)
			preg_match_all('/(<a(|\s[^<>]*)\/?>.*?<\/a>)/si',$text,$matches);
			foreach($matches[1] as $i => $match){
				$tr_table[$match] = " _XatagX{$rnd}.{$i}_ "; // 'Click <a>here</a>' -> 'Click  _XatagX1234_ '
			}

			// building replacements for existing tags
			preg_match_all('/(<[a-z0-9]+(|\s[^<>]*)\/?>)/si',$text,$matches);
			foreach($matches[1] as $i => $match){
				$tr_table[$match] = " _XtagX{$rnd}.{$i}_ "; // 'My photo is here: <img src="http://example.com/image.jpg" />' -> 'My photo is here:  _XtagX1234_ '
			}

		}
		$text = strtr($text,$tr_table);

		// in PHP5.3 parameters of array_combine should have at least 1 element
		$tr_table_rev = sizeof($tr_table)>0 ? array_combine(array_values($tr_table),array_keys($tr_table)) : array();

		$this->__attrs = $attrs;
		$this->__mailto_attrs = $mailto_attrs;
		$this->__options = $options;

		// urls
		$url_allowed_chars = "[-a-zA-Z0-9@:%_+.~#?&\\/\\/=;]";
		$text = preg_replace_callback("/\b(((f|ht){1}tps?:\\/\\/|www\\.)$url_allowed_chars+)/i",array($this,"_replaceLink"),$text);

		// emails
		$text = preg_replace_callback("/([_.0-9a-z-]+@([0-9a-z][0-9a-z-]+\\.)+[a-z]{2,5})/i",array($this,"_replaceEmail"),$text);

		unset($this->__attrs);
		unset($this->__mailto_attrs);
		unset($this->__options);

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

	protected function _renderTemplate($template,$attrs,$replaces){
		ksort($attrs);

		$_attrs = array();
		foreach($attrs as $key => $value){
			$_attrs[] = sprintf('%s="%s"',$key,$value);
		}
		$attrs_str = join(" ",$_attrs);

		$out = strtr($template,array(
			"%attrs%" => $attrs_str,
		));

		// Preparing keys for legacy templates:
		//	<a href="%href%"%class%%target%>%url%</a>
		//	<a href="mailto:%mailto%"%class%>%address%</a>
		//
		// TODO: to be removed
		$replaces["%href%"] = $attrs["href"];
		if(preg_match('/^mailto:(.*)/',$attrs["href"],$matches)){
			$replaces["%mailto%"] = $matches[1];
		}
		$replaces["%target%"] = "";
		if(isset($attrs["target"]) && strlen($attrs["target"])){
			$replaces["%target%"] = " target=\"$attrs[target]\"";
		}
		$replaces["%class%"] = "";
		if(isset($attrs["class"]) && strlen($attrs["class"])){
			$replaces["%class%"] = " class=\"$attrs[class]\"";
		}

		$out = strtr($out,$replaces);

		return $out;
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

		// Dealing with legacy options
		//
		// TODO: to be removed
		if(strlen($options["link_class"])){
			$options["attrs"]["class"] = $options["link_class"];
		}
		if(isset($options["open_links_in_new_windows"])){
			if($options["open_links_in_new_windows"]){
				$options["attrs"]["target"] = "_blank";
			}else{
				unset($options["attrs"]["target"]);
			}
		}
		if(strlen($options["mailto_class"])){
			$options["mailto_attrs"]["class"] = $options["mailto_class"];
		}

		return $options;
	}

	protected function _replaceLink($matches){
		$attrs = $this->__attrs;
		$options = $this->__options;

		$key = trim($matches[1]);
		$tail = "";
		if(preg_match("/^(.+?)([.,;]+)$/",$key,$_matches)){ // dot(s) at the of a link - it probably means end of the sentence
			$key = $_matches[1];
			$tail = $_matches[2];
		}

		$attrs["href"] = preg_match('/^www\./i',$key) ? "http://$key" : $key; // "www.example.com" -> "http://www.example.com"; "http://www.domain.com/" -> "http://www.domain.com/"

		return $this->_renderTemplate($options["link_template"],$attrs,array("%url%" => $key)).$tail;
	}

	protected function _replaceEmail($matches){
		$mailto_attrs = $this->__mailto_attrs;
		$options = $this->__options;

		$key = trim($matches[1]);
		$mailto_attrs["href"] = "mailto:$key";
		return $this->_renderTemplate($options["mailto_template"],$mailto_attrs,array("%address%" => $key));
	}
}
