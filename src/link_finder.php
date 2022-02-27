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

	protected $default_options = array(
		// attributes for <a> and <a href="mailto:..."> elements
		"attrs" => array(),
		"mailto_attrs" => array(),

		"escape_html_entities" => true,
		"avoid_headlines" => true, // when processing HTML text, whether to find and replace links in headlines (<h1>, <h2>, ...) or not?

		"shorten_long_urls" => true,

		"secured_websites" => array(), // list of websites which are run on a secured web server - by default it is configured automatically in the constructor; e.g. ["www.example.com", "google.com"]

		"link_template" => '<a %attrs%>%url%</a>',
		"mailto_template" => '<a %attrs%>%address%</a>',

		"utf8" => true, // the parsed text is supposed to be treated as utf-8

		// legacy options (try not to use them)
		"open_links_in_new_windows" => null, // true, false
		"link_class" => "",
		"mailto_class" => "",
	);

	protected $top_level_domains = array(
		// Taken from: https://en.wikipedia.org/wiki/List_of_Internet_top-level_domains

		// Original top-level domains
		"com",
		"org",
		"net",
		"int",
		"edu",
		"gov",
		"mil",

		// Country code top-level domains
		"ac", "ad", "ae", "af", "ag", "ai", "al", "am", "an", "ao", "aq", "ar", "as", "at", "au", "aw", "ax", "az",
		"ba", "bb", "bd", "be", "bf", "bg", "bh", "bi", "bj", "bl", "bm", "bn", "bo", "bq", "br", "bs", "bt", "bv", "bw", "by", "bz",
		"ca", "cc", "cd", "cf", "cg", "ch", "ci", "ck", "cl", "cm", "cn", "co", "cr", "cu", "cv", "cw", "cx", "cy", "cz",
		"de", "dj", "dk", "dm", "do", "dz",
		"ec", "ee", "eg", "eh", "er", "es", "et", "eu",
		"fi", "fj", "fk", "fm", "fo", "fr",
		"ga", "gb", "gd", "ge", "gf", "gg", "gh", "gi", "gl", "gm", "gn", "gp", "gq", "gr", "gs", "gt", "gu", "gw", "gy",
		"hk", "hm", "hn", "hr", "ht", "hu", "id",
		"ie", "il", "im", "in", "io", "iq", "ir", "is", "it",
		"je", "jm", "jo", "jp",
		"ke", "kg", "kh", "ki", "km", "kn", "kp", "kr", "kw", "ky", "kz",
		"la", "lb", "lc", "li", "lk", "lr", "ls", "lt", "lu", "lv", "ly",
		"ma", "mc", "md", "me", "mf", "mg", "mh", "mk", "ml", "mm", "mn", "mo", "mp", "mq", "mr", "ms", "mt", "mu", "mv", "mw", "mx", "my", "mz",
		"na", "nc", "ne", "nf", "ng", "ni", "nl", "no", "np", "nr", "nu", "nz",
		"om",
		"pa", "pe", "pf", "pg", "ph", "pk", "pl", "pm", "pn", "pr", "ps", "pt", "pw", "py",
		"qa",
		"re", "ro", "rs", "ru", "rw",
		"sa", "sb", "sc", "sd", "se", "sg", "sh", "si", "sj", "sk", "sl", "sm", "sn", "so", "sr", "ss", "st", "su", "sv", "sx", "sy", "sz",
		"tc", "td", "tf", "tg", "th", "tj", "tk", "tl", "tm", "tn", "to", "tp", "tr", "tt", "tv", "tw", "tz",
		"ua", "ug", "uk", "um", "us", "uy", "uz",
		"va", "vc", "ve", "vg", "vi", "vn", "vu",
		"wf", "ws",
		"ye", "yt",
		"za", "zm", "zw",

		// Popular ICANN-era generic top-level domains
		// https://domainnamestat.com/statistics/tldtype/new
		// TODO: Add more
		"academy",
		"accountant",
		"adult",
		"aero",
		"africa",
		"agency",
		"app",
		"army",
		"art",
		"asia",
		"bar",
		"bargains",
		"bayern",
		"berlin",
		"best",
		"bet",
		"bid",
		"biz",
		"blackfriday",
		"blog",
		"business",
		"buzz",
		"cam",
		"care",
		"casa",
		"cat",
		"center",
		"church",
		"city",
		"click",
		"cloud",
		"club",
		"codes",
		"company",
		"consulting",
		"cool",
		"cyou",
		"date",
		"design",
		"dev",
		"digital",
		"download",
		"earth",
		"education",
		"email",
		"estate",
		"events",
		"expert",
		"faith",
		"family",
		"finance",
		"fit",
		"fun",
		"fyi",
		"games",
		"gdn",
		"global",
		"group",
		"guru",
		"host",
		"hosting",
		"icu",
		"info",
		"ink",
		"international",
		"jobs",
		"kitchen",
		"kiwi",
		"life",
		"link",
		"live",
		"loan",
		"lol",
		"london",
		"love",
		"ltd",
		"market",
		"marketing",
		"media",
		"men",
		"mobi",
		"monster",
		"name",
		"network",
		"news",
		"ninja",
		"nyc",
		"one",
		"online",
		"ooo",
		"ovh",
		"page",
		"party",
		"photography",
		"plus",
		"politie",
		"press",
		"pro",
		"pub",
		"quest",
		"racing",
		"realtor",
		"realty",
		"red",
		"ren",
		"rest",
		"review",
		"rocks",
		"run",
		"sale",
		"science",
		"services",
		"shop",
		"site",
		"social",
		"solutions",
		"space",
		"store",
		"stream",
		"studio",
		"support",
		"systems",
		"team",
		"tech",
		"technology",
		"tel",
		"tips",
		"today",
		"tokyo",
		"top",
		"trade",
		"travel",
		"uno",
		"video",
		"vip",
		"wang",
		"watches",
		"webcam",
		"website",
		"wedding",
		"wiki",
		"win",
		"work",
		"works",
		"world",
		"wtf",
		"xin",
		"xxx",
		"xyz",
		"zone",
	);

	function __construct($options = array()){
		global $_SERVER;
		if(!isset($options["secured_websites"]) && isset($_SERVER) && isset($_SERVER["HTTP_HOST"]) && isset($_SERVER["HTTPS"])){
			if($_SERVER["HTTPS"]==="on"){
				$options["secured_websites"] = array((string)$_SERVER["HTTP_HOST"]);
			}
		}
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
	 * @param string $text
	 * @param array $options
	 * @return string
	 */
	function process($text,$options = array()){
		$text = (string)$text;
		$options = $this->_getOptions($options);

		$attrs = $options["attrs"];
		$mailto_attrs = $options["mailto_attrs"];
		$utf8 = $options["utf8"] ? "u" : "";

		$tr_table = $this->_prepareTextTrTable($text,$options);
		$tr_table_rev = sizeof($tr_table)>0 ? array_combine(array_values($tr_table),array_keys($tr_table)) : array(); // in PHP5.3 parameters of array_combine should have at least 1 element

		if($options["escape_html_entities"]){
			$flags =  ENT_COMPAT;
			if(defined("ENT_HTML401")){ $flags = $flags | ENT_HTML401; }

			// As of PHP5.4 the default encoding is UTF-8, it causes troubles in non UTF-8 applications.
			// It seems that the encoding ISO-8859-1 works well in UTF-8 applications.
			$text = htmlspecialchars($text,$flags,"ISO-8859-1");
		}

		$text_orig = $text;

		$text = strtr($text,$tr_table);

		$this->__attrs = $attrs;
		$this->__mailto_attrs = $mailto_attrs;
		$this->__options = $options;
		$this->__replaces = array();

		// Data for patterns
		$uri_allowed_chars = "[-a-zA-Z0-9@:%_+.~#&\\/=;\[\]$!*]"; // According to https://stackoverflow.com/questions/1547899/which-characters-make-a-url-invalid/1547940#1547940 there are yet more characters: '()`,
		$uri = "(\\/$uri_allowed_chars*|\\/|)(\\?$uri_allowed_chars*|)";
		$not_empty_uri = "((\\/$uri_allowed_chars*|\\/)(\\?$uri_allowed_chars*|)|\\?$uri_allowed_chars*)";
		$domain_name_part = "[a-zA-Z0-9][-a-zA-Z0-9]*"; // without dot, domain name part can be just 1 character long
		$optional_port = "(:[1-9][0-9]{1,4}|)"; // ":81", ":65535", ""
		$top_level_domains = "(".join("|",$this->top_level_domains).")";
		$username_chars = "[-a-zA-Z0-9%]+";
		$password_chars = $username_chars;

		// urls starting with http://, https://, ftp:/ and containing username and password
		$text = $this->_pregReplaceCallback("(?<first_char>.?)\b(?<link>(ftp|https?):\\/\\/$username_chars:$password_chars@$domain_name_part(\.$domain_name_part)*$optional_port$uri)","_replaceLink",$text,$options);
		if(strlen($text)==0){
			// perhaps there is an invalid UTF-8 char in $text
			return $text_orig;
		}

		// urls starting with http://, https://, ftp:/
		$text = $this->_pregReplaceCallback("(?<first_char>.?)\b(?<link>(ftp|https?):\\/\\/$domain_name_part(\.$domain_name_part)*$optional_port$uri)","_replaceLink",$text,$options);

		// urls starting with www.
		$text = $this->_pregReplaceCallback("(?<first_char>.?)\b(?<link>www\.$domain_name_part(\.$domain_name_part)*$optional_port$uri)","_replaceLink",$text,$options);

		// urls without leading www., http://, ... and with something in URI part which may look like an email address (e.g. mill.cz/_cs/mailing/online/test@example.com/afb359b921a75f8a90fa6a5c0ffb5671/000001.htm)
		$text = $this->_pregReplaceCallback("(?<first_char>.?)\b(?<link>($domain_name_part\\.)+$top_level_domains\\b$optional_port$not_empty_uri)","_replaceLink",$text,$options);

		// emails
		$text = $this->_pregReplaceCallback("(?<address>[_.0-9a-z-]+@([0-9a-z][0-9a-z-]+\\.)+[a-z]{2,5})(?<ending_interrupter>.?)","_replaceEmail",$text,$options);

		// urls without leading www., http://, ...
		$text = $this->_pregReplaceCallback("(?<first_char>.?)\b(?<link>($domain_name_part\\.)+$top_level_domains\\b$optional_port$uri)","_replaceLink",$text,$options);

		$text = strtr($text,$this->__replaces);

		unset($this->__attrs);
		unset($this->__mailto_attrs);
		unset($this->__options);
		unset($this->__replaces);

		$text = strtr($text,$tr_table_rev);

		return $text;
	}

	protected function _pregReplaceCallback($pattern,$method,$text,$options){
		$utf8 = $options["utf8"] ? "u" : "";
		// links or emails in parentheses must by properly detected
		$parentheses = array(
			'\[' => '\]',
			'\(' => '\)',
			'\{' => '\}',
			'' => '',
		);
		foreach($parentheses as $leading_parenthesis => $ending_parenthesis){
			$text = preg_replace_callback("/(?<leading_parenthesis>$leading_parenthesis)$pattern(?<ending_parenthesis>$ending_parenthesis)/i$utf8",array($this,$method),$text);
		}
		return $text;
	}

	/**
	 * In a HTML text it searches for URLs and emails that are not marked as links
	 *
	 * @access public
	 * @param string $text 									HTML text
	 * @param array $options
	 * @return string												HTML text
	 */
	function processHtml($html,$options = array()){
		$options += array(
			"escape_html_entities" => false,
		);
		return $this->process($html,$options);
	}

	protected function _prepareTextTrTable($text,$options){
		$rnd = uniqid();

		$tr_table = array(
			"&lt;" => " .._XltX{$rnd}_.. ",
			"&gt;" => " .._XgtX{$rnd}_.. ",
		);

		preg_match_all('/(\&(#\d{2,6}|#x([A-Fa-f0-9]{2}){1,3});)/',$text,$matches);
		foreach($matches[1] as $i => $match){
			$tr_table[$match] = " _XentityX{$rnd}.{$i}_ ";
		}

		if($options["escape_html_entities"]){
			$tr_table["&amp;"] = "Xampicek{$rnd}X";
			$tr_table["&quot;"] = " .._XquotX{$rnd}_.. ";
			return $tr_table;
		}

		// building replacements for existing links (<a>...</a>)
		preg_match_all('/(<a(|\s[^<>]*)\/?>.*?<\/a>)/si',$text,$matches);
		foreach($matches[1] as $i => $match){
			$tr_table[$match] = " _XatagX{$rnd}.{$i}_ "; // 'Click <a>here</a>' -> 'Click  _XatagX1234_ '
		}

		if($options["avoid_headlines"]){
			preg_match_all('/(<(h\d+)(|\s[^<>]*)\/?>.*?<\/\2>)/si',$text,$matches);
			foreach($matches[1] as $i => $match){
				$tr_table[$match] = " _XhtagX{$rnd}.{$i}_ "; // '<h1>Title</h1>' -> ' _XhtagX1234_ '
			}
		}

		// building replacements for existing tags
		preg_match_all('/(<[a-z0-9]+(|\s[^<>]*)\/?>)/si',$text,$matches);
		foreach($matches[1] as $i => $match){
			$tr_table[$match] = " _XtagX{$rnd}.{$i}_ "; // 'My photo is here: <img src="http://example.com/image.jpg" />' -> 'My photo is here:  _XtagX1234_ '
		}

		return $tr_table;
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
		$options += $this->default_options;
		$this->default_options = $options;
	}

	protected function _setOption($key,$value){
		$this->_setOptions(array((string)$key => $value));
	}

	protected function _getOptions($options = array()){
		$options += $this->default_options;

		// Dealing with legacy options
		//
		// TODO: to be removed
		if(strlen($options["link_class"])){
			$options["attrs"]["class"] = $options["link_class"];
		}
		if($options["open_links_in_new_windows"]){
			$options["attrs"]["target"] = "_blank";
		}
		if(strlen($options["mailto_class"])){
			$options["mailto_attrs"]["class"] = $options["mailto_class"];
		}

		return $options;
	}

	protected function _replaceLink($matches){
		$attrs = $this->__attrs;
		$options = $this->__options;

		$first_char = $matches["first_char"];
		$key = trim($matches["link"]);

		$leading_parenthesis = $matches["leading_parenthesis"];
		$ending_parenthesis = $matches["ending_parenthesis"];

		if(in_array($first_char,array('/','.'))){
			return $matches[0];
		}

		$tail = "";

		if(!$leading_parenthesis && preg_match("/^(.+?)([.,;!]+)$/",$key,$_matches)){ // dot(s) at the of a link - it probably means end of the sentence
			$key = $_matches[1];
			$tail = $_matches[2];
		}

		/*
		if($first_char=="[" && preg_match('/\]$/',$key)){ // [http://www.example.com/]  -> [<a href="http://www.example.com/">http://www.example.com/</a>]
			$tail = "]".$tail;
			$key = substr($key,0,-1);
		}
		*/

		if(preg_match('/^[a-z]+:\/\//i',$key)){
			$attrs["href"] = $key;
		}else{
			$_hostname = preg_replace('/\/.*$/','',$key);
			$schema = in_array(strtolower($_hostname),$options["secured_websites"]) ? "https" : "http";
			$attrs["href"] = "$schema://$key"; // "www.example.com" -> "http://www.example.com"; "http://www.domain.com/" -> "http://www.domain.com/"
		}

		$replace_key = $this->_getNewReplaceKey();

		$this->__replaces[$replace_key] = $this->_renderTemplate($options["link_template"],$attrs,array(
			"%url%" => $options["shorten_long_urls"] ? $this->_shortenUrl($key) : $key
		));

		return $leading_parenthesis.$first_char.$replace_key.$tail.$ending_parenthesis;
	}

	protected function _replaceEmail($matches){
		$mailto_attrs = $this->__mailto_attrs;
		$options = $this->__options;

		$address = trim($matches["address"]);
		$ending_interrupter = ($matches["ending_interrupter"]);

		$leading_parenthesis = $matches["leading_parenthesis"];
		$ending_parenthesis = $matches["ending_parenthesis"];

		$replace_key = $this->_getNewReplaceKey();

		if(in_array($ending_interrupter,array(":"))){
			$this->__replaces[$replace_key] = $matches[0];
			return $replace_key;
		}

		$mailto_attrs["href"] = "mailto:$address";

		$this->__replaces[$replace_key] = $this->_renderTemplate($options["mailto_template"],$mailto_attrs,array("%address%" => $address));

		return $leading_parenthesis.$replace_key.$ending_interrupter.$ending_parenthesis;
	}

	protected function _getNewReplaceKey(){
		static $rnd, $counter = 0;

		if(!$rnd){ $rnd = uniqid(); }

		$counter++;
		return " Xreplace.{$rnd}.{$counter}X ";
	}

	protected function _shortenUrl($url){
		$max_acceptable_length = 65; // In emails, lines should not be larger than 70 characters.
		if(strlen($url)<=$max_acceptable_length){
			return $url;
		}
		if(!preg_match('/^(?<proto>((ftp|https?):\/\/)|)(?<domain>[^\/]+)(?<uri>\/.*|)$/i',$url,$matches)){
			// Actually, this should not happen. $url should be a valid URL and the pattern should catch them all.
			return $url;
		}

		if(strlen($matches["uri"])<10){
			return $url;
		}

		$out = $matches["proto"].$matches["domain"];
		$length = $max_acceptable_length - strlen($out) - 3; // 3 for "..."
		if($length<5){ $length = 5; }
		$out = $out.substr($matches["uri"],0,$length)."...";

		return $out;
	}
}
