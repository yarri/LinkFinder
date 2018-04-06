<?php
use PHPUnit\Framework\TestCase;

class LinkFinderTest extends TestCase{

	function testBasicUsage(){
		$lfinder = new LinkFinder();

		// a basic example
		$src = 'Lorem www.ipsum.com. dolor@sit.net. Thank you';
		$this->assertEquals(
			'Lorem <a href="http://www.ipsum.com">www.ipsum.com</a>. <a href="mailto:dolor@sit.net">dolor@sit.net</a>. Thank you',
			$lfinder->process($src)
		);

		//
		$src = 'Image: <img src="http://example.com/logo.gif" />, Url: www.ipsum.com';
		$this->assertEquals(
			'Image: <img src="http://example.com/logo.gif" />, Url: <a href="http://www.ipsum.com">www.ipsum.com</a>',
			$lfinder->process($src,array("escape_html_entities" => false))
		);

		// auto escaping of HTML entities
		$src = 'Lorem www.ipsum.com <http://www.ipsum.com/>.
			Dolor: dolor@sit.new <dolor@sit.net>. Thank you';
		$this->assertEquals(
			'Lorem <a href="http://www.ipsum.com">www.ipsum.com</a> &lt;<a href="http://www.ipsum.com/">http://www.ipsum.com/</a>&gt;.
			Dolor: <a href="mailto:dolor@sit.new">dolor@sit.new</a> &lt;<a href="mailto:dolor@sit.net">dolor@sit.net</a>&gt;. Thank you',
			$lfinder->process($src)
		);

		// disabling auto escaping may produce invalid markup
		$src = 'Lorem www.ipsum.com <http://www.ipsum.com/>.
			Dolor: dolor@sit.new <dolor@sit.net>. Thank you';
		$this->assertEquals(
			'Lorem <a href="http://www.ipsum.com">www.ipsum.com</a> <<a href="http://www.ipsum.com/">http://www.ipsum.com/</a>>.
			Dolor: <a href="mailto:dolor@sit.new">dolor@sit.new</a> <<a href="mailto:dolor@sit.net">dolor@sit.net</a>>. Thank you',
			$lfinder->process($src,array("escape_html_entities" => false))
		);

		// a git repository must not be interpreted as an email
		$src = 'Source is located at git@github.com:yarri/LinkFinder.git';
		$this->assertEquals('Source is located at git@github.com:yarri/LinkFinder.git',$lfinder->process($src));

		// an example from the README.md
		$src = 'Find more at www.ourstore.com <http://www.ourstore.com/>';
		$this->assertEquals(
			'Find more at <a href="http://www.ourstore.com">www.ourstore.com</a> &lt;<a href="http://www.ourstore.com/">http://www.ourstore.com/</a>&gt;',
			$lfinder->process($src)
		);

		// source text contains a real link
		$src = 'Find more at www.ourstore.com or click <a href="http://www.ourstore.com/contact">here</a> to contact us.';
		$this->assertEquals(
			'Find more at <a href="http://www.ourstore.com">www.ourstore.com</a> or click <a href="http://www.ourstore.com/contact">here</a> to contact us.',
			$lfinder->process($src,array("escape_html_entities" => false))
		);

		// in source there is already a correct HTML link
		$src = '<p>Contact as on <a href="http://www.earth.net/">www.earth.net</a></p>';
		$this->assertEquals('<p>Contact as on <a href="http://www.earth.net/">www.earth.net</a></p>',$lfinder->process($src,array("escape_html_entities" => false)));

		// a tag immediately after an URL
		$src = '<p>Contact as on www.earth.net<br />
or we@earth.net</p>';
		$this->assertEquals('<p>Contact as on <a href="http://www.earth.net">www.earth.net</a><br />
or <a href="mailto:we@earth.net">we@earth.net</a></p>',$lfinder->process($src,array("escape_html_entities" => false)));

		$tr_table = array(
			'url: www.domain.com, www.ourstore.com' => 'url: <a href="http://www.domain.com">www.domain.com</a>, <a href="http://www.ourstore.com">www.ourstore.com</a>',
			'url: www.domain.com; www.ourstore.com' => 'url: <a href="http://www.domain.com">www.domain.com</a>; <a href="http://www.ourstore.com">www.ourstore.com</a>',
			'just visit www.ourstore.com...' => 'just visit <a href="http://www.ourstore.com">www.ourstore.com</a>...',
		);
		foreach($tr_table as $src => $expected){
			$this->assertEquals($expected,$lfinder->process($src),"source: $src");
		}

		// URLs in quotes
		// Steam sends strange formatted text in email address verification messages
		$src = 'Sometimes in emails in text/plain parts not well formatted text occurs: <a href="http://www.click.me/now/">click here</a>';
		$this->assertEquals('Sometimes in emails in text/plain parts not well formatted text occurs: &lt;a href=&quot;<a href="http://www.click.me/now/">http://www.click.me/now/</a>&quot;&gt;click here&lt;/a&gt;',$lfinder->process($src));
		//
		$src = "Sometimes in emails in text/plain parts not well formatted text occurs: <a href='http://www.click.me/now/'>click here</a>";
		$this->assertEquals('Sometimes in emails in text/plain parts not well formatted text occurs: &lt;a href=\'<a href="http://www.click.me/now/">http://www.click.me/now/</a>\'&gt;click here&lt;/a&gt;',$lfinder->process($src));
		//
		$src = 'Link: "http://www.example.org/"';
		$this->assertEquals('Link: "<a href="http://www.example.org/">http://www.example.org/</a>"',$lfinder->process($src,array("escape_html_entities" => false)));
	}

	function testOptions(){
		$src = '<em>Lorem</em> www.ipsum.com. dolor@sit.net. Thank you';
		$lfinder = new LinkFinder(array(
			"attrs" => array(
				"class" => "link",
				"target" => "_blank",
			),
			"mailto_attrs" => array(
				"class" => "email",
			),
			"escape_html_entities" => false,
		));

		$this->assertEquals(
			'<em>Lorem</em> <a class="link" href="http://www.ipsum.com" target="_blank">www.ipsum.com</a>. <a class="email" href="mailto:dolor@sit.net">dolor@sit.net</a>. Thank you',
			$lfinder->process($src)
		);

		$this->assertEquals(
			'<em>Lorem</em> <a class="external-link" href="http://www.ipsum.com">www.ipsum.com</a>. <a class="email" href="mailto:dolor@sit.net">dolor@sit.net</a>. Thank you',
			$lfinder->process($src,array("attrs" => array("class" => "external-link")))
		);

		$this->assertEquals(
			'&lt;em&gt;Lorem&lt;/em&gt; <a class="article-link" href="http://www.ipsum.com">www.ipsum.com</a>. <a class="article-email" href="mailto:dolor@sit.net">dolor@sit.net</a>. Thank you',
			$lfinder->process($src,array("attrs" => array("class" => "article-link"), "mailto_attrs" => array("class" =>  "article-email"), "escape_html_entities" => true))
		);

		$this->assertEquals(
			'<em>Lorem</em> <a class="link" href="http://www.ipsum.com" target="_blank">www.ipsum.com</a>. <a class="email" href="mailto:dolor@sit.net">dolor@sit.net</a>. Thank you',
			$lfinder->process($src)
		);
	}

	function testLegacyUsage(){
		$src = '<em>Lorem</em> www.ipsum.com. dolor@sit.net. Thank you';

		$lfinder = new LinkFinder(array(
			"open_links_in_new_windows" => true,
			"escape_html_entities" => false,

			"link_template" => '<a href="%href%"%class%%target%>%url%</a>',
			"mailto_template" => '<a href="mailto:%mailto%"%class%>%address%</a>',

			"link_class" => "link",
			"mailto_class" => "email",
		));
		$this->assertEquals(
			'<em>Lorem</em> <a href="http://www.ipsum.com" class="link" target="_blank">www.ipsum.com</a>. <a href="mailto:dolor@sit.net" class="email">dolor@sit.net</a>. Thank you',
			$lfinder->process($src)
		);

		$lfinder->setToOpenLinkInNewWindow(false);
		$lfinder->setLinkClass("external-link");

		$this->assertEquals(
			'<em>Lorem</em> <a href="http://www.ipsum.com" class="external-link">www.ipsum.com</a>. <a href="mailto:dolor@sit.net" class="email">dolor@sit.net</a>. Thank you',
			$lfinder->process($src)
		);

		$this->assertEquals(
			'&lt;em&gt;Lorem&lt;/em&gt; <a href="http://www.ipsum.com" class="article-link">www.ipsum.com</a>. <a href="mailto:dolor@sit.net" class="article-email">dolor@sit.net</a>. Thank you',
			$lfinder->process($src,array("link_class" => "article-link", "mailto_class" => "article-email", "escape_html_entities" => true))
		);

		$this->assertEquals(
			'<em>Lorem</em> <a href="http://www.ipsum.com" class="external-link">www.ipsum.com</a>. <a href="mailto:dolor@sit.net" class="email">dolor@sit.net</a>. Thank you',
			$lfinder->process($src)
		);

		$lfinder = new LinkFinder(array(
			"open_links_in_new_windows" => true,
			"escape_html_entities" => false,

			"attrs" => array("class" => "link"),
			"mailto_attrs" => array("class" => "email"),

			"link_template" => '<a href="%href%"%class%%target%>%url%</a>',
			"mailto_template" => '<a href="mailto:%mailto%"%class%>%address%</a>',
		));
		$this->assertEquals('<em>Lorem</em> <a href="http://www.ipsum.com" class="link" target="_blank">www.ipsum.com</a>. <a href="mailto:dolor@sit.net" class="email">dolor@sit.net</a>. Thank you',$lfinder->process($src));
	}

	function testLinks(){
		$links = array(
			"http://www.ipsum.com/" => "http://www.ipsum.com/",
			"http://www.ipsum.com:81/" => "http://www.ipsum.com:81/",
			//
			"https://www.example.com/article.pl?id=123" => "https://www.example.com/article.pl?id=123",
			"https://www.example.com:81/article.pl?id=123" => "https://www.example.com:81/article.pl?id=123",
			//
			"www.ipsum.com" => "http://www.ipsum.com",
			"www.ipsum.com:81" => "http://www.ipsum.com:81",
			//
			"www.example.com/article.pl?id=123" => "http://www.example.com/article.pl?id=123",
			"www.example.com/article.pl?id=123&format=raw" => "http://www.example.com/article.pl?id=123&format=raw",
			"www.example.com/article.pl?id=123;format=raw" => "http://www.example.com/article.pl?id=123;format=raw",
			"www.www.example.intl" => "http://www.www.example.intl",

			"ftp://example.com/public/" => "ftp://example.com/public/",
			"ftp://example.com:1122/public/" => "ftp://example.com:1122/public/",

			"example.com" => "http://example.com",
			"subdomain.example.com" => "http://subdomain.example.com",

			"example.com/" => "http://example.com/",
			"example.com/page.html" => "http://example.com/page.html",

			"example.com:81" => "http://example.com:81",
			"example.com:81/" => "http://example.com:81/",
			"example.com:81/page.html" => "http://example.com:81/page.html",

			"subdomain.example.com" => "http://subdomain.example.com",

			"http://domain.com/var=[ID]" => "http://domain.com/var=[ID]",

			//"http://grooveshark.com/#!/album/AirMech/8457898" => "http://grooveshark.com/#!/album/AirMech/8457898", // TODO:
		);

		$templates = array(
			"%s",
			"Lorem %s Ipsum",
			"Lorem %s, Ipsum",
			"Lorem %s. Ipsum",
			"Lorem %s",
			"%s, Lorem",
			"%s,Lorem",
			"%s. Lorem",
			"Lorem: %s",
			"Lorem:%s",
			"Lorem %s!",
			"Lorem <%s>",
		);

		$lfinder = new LinkFinder();

		foreach($links as $src => $expected){
			$expected = str_replace('&','&amp;',$expected); // "www.example.com/article.pl?id=123&format=raw" => "www.example.com/article.pl?id=123&amp;format=raw"
			foreach($templates as $template){
				$out = $lfinder->process($_src = sprintf($template,$src));
				$this->assertEquals(true,!!preg_match('/<a href="([^"]+)">/',$out,$matches),"$_src is containing a link");
				$this->assertEquals($expected,$matches[1],"$_src is containing $expected");
			}
		}
	}

	function testNotLinks(){
		$not_links = array(
			"i like indian food.how about you.",
			"tlds are .com, .net, .org, etc.",
			"pattern is *.com",
			"pattern is -.com",
			"somehing like.xx",
			"DůmLátek.cz",
			"/var/www/app.com/index.html",
			"/var/www/www.app.com/index.html",
			'.example.com',
			'.www.example.com'
		);

		$lfinder = new LinkFinder();

		foreach($not_links as $str){
			$out = $lfinder->process($str);
			$this->assertEquals($str,$out,"\"$out\" should not contain a link");
		}
	}
}
