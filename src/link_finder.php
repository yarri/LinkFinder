<?php
/**
 * In a plain text document LinkFinder searches for URLs and e-mail addresses and adds tags <a>..</a> around them
 *
 * Basic usage:
 *
 *   $lf = new LinkFinder();
 *   echo $lf->process('Welcome at www.example.com!'); // Welcome at <a href="http://www.example.com/">www.example.com</a>!
 *
 * Original regular expressions has been taken from a function html_activate_links() by Fredrik Kristiansen (russlndr at online.no) and
 * Albrecht Guenther (ag at phprojekt.de): http://www.zend.com/codex.php?id=395&single=1
 *
 */
class LinkFinder{

	/**
	 * Priznak otevirani odkazu do noveho okna.
	 *
	 * @access private
	 * @var boolean
	 */
	var $_OpenLinkInNewWindow = false;

	/**
	 * Jmeno CSS tridy pro odkazy <a href="...".
	 *
	 * @access private
	 * @var string
	 */
	var $_LinkClass = "";

	/**
	 * Jmeno CSS tridy pro odkazy <a href="mailto:...".
	 *
	 * @access private
	 * @var string
	 */
	var $_MailtoClass = "";

	var $_LinkTemplate = null;
	var $_MailtoTemplate = null;

	function __construct($options = array()){
		$options += array(
			"open_links_in_new_windows" => false,
			"link_class" => "",
			"mailto_class" => "",

			"link_template" => '<a href="%href%"%class%%target%>%url%</a>',
			"mailto_template" => '<a href="mailto:%mailto%"%class%>%address%</a>',
		);

		$this->setToOpenLinkInNewWindow($options["open_links_in_new_windows"]);
		$this->setHrefClass($options["link_class"]);
		$this->setMailtoClass($options["mailto_class"]);

		$this->setLinkTemplate($options["link_template"]);
		$this->setMailtoTemplate($options["mailto_template"]);
	}


	function setToOpenLinkInNewWindow($set = true){ $this->_OpenLinkInNewWindow = (bool)$set; }
	function setToNotOpenLinkInNewWindow(){ $this->_OpenLinkInNewWindow = false; }
	function setHrefClass($class){ $this->_LinkClass = (string)$class; }
	function setMailtoClass($class){ $this->_MailtoClass = (string)$class; }
	function setLinkTemplate($template){ $this->_LinkTemplate = (string)$template; }
	function setMailtoTemplate($template){ $this->_MailtoTemplate = (string)$template; }

	/**
	* Ve vstupnim textu nalezne vsechna mozna url a doplni tagy.
	*
	* @access public
	* @param string $text					vstupni text
	* @return string
	*/
	function process($text){
		settype($text,"string");

		$_blank = "";
		if($this->_OpenLinkInNewWindow){
			$_blank = " target=\"_blank\"";
		}
		$_href_class = "";
		if($this->_LinkClass!=""){
			$_href_class = " class=\"$this->_LinkClass\"";
		}
		$_mailto_class = "";
		if($this->_MailtoClass!=""){
			$_mailto_class = " class=\"$this->_MailtoClass\"";
		}


		// novy kod - odstranuje tecku na konci url
		$replace_ar = array();

		preg_match_all("/(((f|ht){1}tps?:\\/\\/)[-a-zA-Z0-9@:%_+.~#?&\\/\\/=;]+)/i", $text, $matches);
		for($i=0;$i<sizeof($matches[1]);$i++){
			$key = trim($matches[1][$i]);
			//odstrsaneni tecky na konci odkazu - je to pravdepodobne konec vety
			$key = preg_replace("/\\.$/","",$key);
			$replace_ar[$key] = strtr($this->_LinkTemplate,array(
				"%href%" => $key,
				"%url%" => $key,
				"%class%" => $_href_class,
				"%target%" => $_blank,
			));
		}

		preg_match_all("/\b(www\\.[-a-zA-Z0-9@:%_+.~#?&\\/\\/=;]+)/i", $text, $matches);
		for($i=0;$i<sizeof($matches[1]);$i++){
			$key = trim($matches[1][$i]);
			//odstraneni tecky na konci odkazu - je to pravdepodobne konec vety
			$key = preg_replace("/\\.$/","",$key);
			$replace_ar[$key] = strtr($this->_LinkTemplate,array(
				"%href%" => "http://$key",
				"%url%" => $key,
				"%class%" => $_href_class,
				"%target%" => $_blank,
			));
		}

		preg_match_all("/([_.0-9a-z-]+@([0-9a-z][0-9a-z-]+\\.)+[a-z]{2,5})/i", $text, $matches);
		for($i=0;$i<sizeof($matches[1]);$i++){
			$key = trim($matches[1][$i]);
			//odstrsaneni tecky na konci odkazu - je to pravdepodobne konec vety
			$key = preg_replace("/\\.$/","",$key);
			$replace_ar[$key] = strtr($this->_MailtoTemplate,array(
				"%mailto%" => $key, 
				"%address%" => $key,
				"%class%" => $_mailto_class,
			));
		}

		$text = strtr($text,$replace_ar);
		return $text;
	}
}
