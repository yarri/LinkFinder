<?php
/**
 * In a plain text document LinkFinder searches for URLs and e-mail addresses and adds tags <a>..</a> around them
 *
 * Basic usage:
 *
 *   $lf = new LinkFinder();
 *   echo $lf->Process('Welcome at www.example.com!'); // Welcome at <a href="http://www.example.com/">www.example.com</a>!
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
	var $_HrefClass = "";

	/**
	* Jmeno CSS tridy pro odkazy <a mailto="...".
	*
	* @access private
	* @var string
	*/
	var $_MailtoClass = "";

	function setToOpenLinkInNewWindow(){ $this->_OpenLinkInNewWindow = true; }
	function setToNotOpenLinkInNewWindow(){ $this->_OpenLinkInNewWindow = false; }
	function setHrefClass($class){ settype($class,"string"); $this->_HrefClass = $class; }
	function setMailtoClass($class){ settype($class,"string"); $this->_MailtoClass = $class; }
	
	/**
	* Ve vstupnim textu nalezne vsechna mozna url a doplni tagy.
	* 
	* @access public
	* @param string $text					vstupni text
	* @return string
	*/
	function Process($text){
		settype($text,"string");

		$_blank = "";
		if($this->_OpenLinkInNewWindow){
			$_blank = " target=\"_blank\"";
		}
		$_href_class = "";
		if($this->_HrefClass!=""){
			$_href_class = " class=\"$this->_HrefClass\"";
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
			$value = "<a href=\"$key\"$_blank$_href_class>$key</a>";
			$replace_ar[$key] = $value;
		}

		preg_match_all("/\b(www\\.[-a-zA-Z0-9@:%_+.~#?&\\/\\/=;]+)/i", $text, $matches);
		for($i=0;$i<sizeof($matches[1]);$i++){
			$key = trim($matches[1][$i]);
			//odstrsaneni tecky na konci odkazu - je to pravdepodobne konec vety
			$key = preg_replace("/\\.$/","",$key);
			$value = "<a href=\"http://$key\"$_blank$_href_class>$key</a>";
			$replace_ar[$key] = $value;
		}
	
		preg_match_all("/([_.0-9a-z-]+@([0-9a-z][0-9a-z-]+\\.)+[a-z]{2,5})/i", $text, $matches);
		for($i=0;$i<sizeof($matches[1]);$i++){
			$key = trim($matches[1][$i]);
			//odstrsaneni tecky na konci odkazu - je to pravdepodobne konec vety
			$key = preg_replace("/\\.$/","",$key);
			$value = "<a href=\"mailto:$key\"$_mailto_class>$key</a>";
			$replace_ar[$key] = $value;
		}

		$text = strtr($text,$replace_ar);
		return $text;
	}
}
