<?php
class TcLinkFinder extends TcBase{

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
		$result = 'Find more at <a href="http://www.ourstore.com">www.ourstore.com</a> or click <a href="http://www.ourstore.com/contact">here</a> to contact us.';
		$this->assertEquals($result,$lfinder->process($src,array("escape_html_entities" => false)));
		$this->assertEquals($result,$lfinder->processHtml($src));

		// in source there is already a correct HTML link
		$src = '<p>Contact as on <a href="http://www.earth.net/">www.earth.net</a></p>';
		$result = '<p>Contact as on <a href="http://www.earth.net/">www.earth.net</a></p>';
		$this->assertEquals($result,$lfinder->process($src,array("escape_html_entities" => false)));
		$this->assertEquals($result,$lfinder->processHtml($src));

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

		// URL with username and password
		$src = 'Development preview is at http://preview:project123@project.preview.example.org/';
		$this->assertEquals('Development preview is at <a href="http://preview:project123@project.preview.example.org/">http://preview:project123@project.preview.example.org/</a>',$lfinder->process($src));
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

	function test_avoid_headlines(){
		$src = '<h1>WWW.PROJECT.COM</h1><p>Welcome at www.project.com!</p>';
		$lfinder = new LinkFinder();

		// the default value is to avoid headlines
		$this->assertEquals('<h1>WWW.PROJECT.COM</h1><p>Welcome at <a href="http://www.project.com">www.project.com</a>!</p>',$lfinder->processHtml($src));

		//
		$this->assertEquals('<h1>WWW.PROJECT.COM</h1><p>Welcome at <a href="http://www.project.com">www.project.com</a>!</p>',$lfinder->processHtml($src,array("avoid_headlines" => true)));
		$this->assertEquals('<h1><a href="http://WWW.PROJECT.COM">WWW.PROJECT.COM</a></h1><p>Welcome at <a href="http://www.project.com">www.project.com</a>!</p>',$lfinder->processHtml($src,array("avoid_headlines" => false)));

		// setting default value into the constructor
		$lfinder = new LinkFinder(array("avoid_headlines" => false));
		$this->assertEquals('<h1><a href="http://WWW.PROJECT.COM">WWW.PROJECT.COM</a></h1><p>Welcome at <a href="http://www.project.com">www.project.com</a>!</p>',$lfinder->processHtml($src));
		$this->assertEquals('<h1>WWW.PROJECT.COM</h1><p>Welcome at <a href="http://www.project.com">www.project.com</a>!</p>',$lfinder->processHtml($src,array("avoid_headlines" => true)));

		// avoid_headlines has no effect when processing a plain text
		$lfinder = new LinkFinder();
		$this->assertEquals('&lt;h1&gt;<a href="http://WWW.PROJECT.COM">WWW.PROJECT.COM</a>&lt;/h1&gt;&lt;p&gt;Welcome at <a href="http://www.project.com">www.project.com</a>!&lt;/p&gt;',$lfinder->process($src));
		$this->assertEquals('&lt;h1&gt;<a href="http://WWW.PROJECT.COM">WWW.PROJECT.COM</a>&lt;/h1&gt;&lt;p&gt;Welcome at <a href="http://www.project.com">www.project.com</a>!&lt;/p&gt;',$lfinder->process($src,array("avoid_headlines" => true)));
		$this->assertEquals('&lt;h1&gt;<a href="http://WWW.PROJECT.COM">WWW.PROJECT.COM</a>&lt;/h1&gt;&lt;p&gt;Welcome at <a href="http://www.project.com">www.project.com</a>!&lt;/p&gt;',$lfinder->process($src,array("avoid_headlines" => false)));
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

	function testLinksInBrackets(){
		$lfinder = new LinkFinder();
		$this->assertEquals('Example (<a href="http://example.com/">http://example.com/</a>)',$lfinder->process('Example (http://example.com/)'));
		$this->assertEquals('Square Brackets [<a href="http://example.com/">http://example.com/</a>]',$lfinder->process('Square Brackets [http://example.com/]'));
		$this->assertEquals('Square Brackets [<a href="http://example.com/">http://example.com/</a>]. Nice!',$lfinder->process('Square Brackets [http://example.com/]. Nice!'));
		$this->assertEquals('Braces {<a href="http://example.com/">http://example.com/</a>}',$lfinder->process('Braces {http://example.com/}'));
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

			// exclamation mark
			"http://grooveshark.com/#!/album/AirMech/8457898" => "http://grooveshark.com/#!/album/AirMech/8457898",

			// dollar sign
			"https://odysee.com/$/verify?auth_token=vSDU4T3iSsFMDcV5Sga4JS4ZxSxfexDs&email=link%40finder.com&needs_recaptcha=false&verification_token=z332ov3912BfeXrfhX5p4n3PwSYUF3HE" => "https://odysee.com/$/verify?auth_token=vSDU4T3iSsFMDcV5Sga4JS4ZxSxfexDs&email=link%40finder.com&needs_recaptcha=false&verification_token=z332ov3912BfeXrfhX5p4n3PwSYUF3HE",

			// only one character in a domain name part
			"https://e.targito.com/c?a=1b4cba18-09e1-49c6-8933-7fadbe8e7395&o=atk14net&m=0725cccc-e58b-46f5-86ee-36c9c5ee7ddb" => "https://e.targito.com/c?a=1b4cba18-09e1-49c6-8933-7fadbe8e7395&o=atk14net&m=0725cccc-e58b-46f5-86ee-36c9c5ee7ddb",

			"mill.cz/_cs/mailing/online/test@example.com/afb359b921a75f8a90fa6a5c0ffb5671/000001.htm" => "http://mill.cz/_cs/mailing/online/test@example.com/afb359b921a75f8a90fa6a5c0ffb5671/000001.htm",

			// URLs with asterisk
			"http://wayback.archive.org/web/*/http://google.com" => "http://wayback.archive.org/web/*/http://google.com",
			"example.com/K/Ko%c4%8dka*Testovac%c3%ad*CZ1252156***19100101*CO01*2*2*20210118*20210118*V1CZ00024341250212071710463" => "http://example.com/K/Ko%c4%8dka*Testovac%c3%ad*CZ1252156***19100101*CO01*2*2*20210118*20210118*V1CZ00024341250212071710463",

			// no slash before question mark
			"https://example.com?utm_source=Newsletter+Pro&utm_campaign=046f656a38-EMAIL_CAMPAIGN_2018_01_03_COPY_01" => "https://example.com?utm_source=Newsletter+Pro&utm_campaign=046f656a38-EMAIL_CAMPAIGN_2018_01_03_COPY_01",
			"example.com?utm_source=Newsletter+Pro&utm_campaign=046f656a38-EMAIL_CAMPAIGN_2018_01_03_COPY_01" => "http://example.com?utm_source=Newsletter+Pro&utm_campaign=046f656a38-EMAIL_CAMPAIGN_2018_01_03_COPY_01",
		);

		$templates = array(
			"%s",
			"Lorem %s Ipsum",
			"Lorem %s, Ipsum",
			"Lorem %s. Ipsum",
			"Lorem %s",
			"%s, Lorem",
			"%s. Lorem",
			"Lorem: %s",
			"Lorem:%s",
			"Lorem %s!",
			"Brackets (%s)",
			"Brackets (%s), Nice!",
			"Brackets (%s); Nice!",
			"Brackets (%s). Nice!",
			"Angled Brackets <%s>",
			"Angled Brackets <%s>, Nice!",
			"Angled Brackets <%s>; Nice!",
			"Angled Brackets <%s>. Nice!",
			//
			"Square Brackets [%s]",
			"Square Brackets [%s], Nice!",
			"Square Brackets, italic _[%s]_",
			"Square Brackets, italic _[%s]_, Nice!",
			//
			"Brackets (%s)",
			"Brackets (%s), Nice!",
			"Brackets, italic _(%s)_",
			"Brackets, italic _(%s)_, Nice!",
			//
			"Braces {%s}",
			"Braces {%s}, Nice!",
			"Braces {%s}; Nice!",
			"Braces {%s}. Nice!",
			"Braces, italic _{%s}_",
			"Braces, italic _{%s}_, Nice!",
			"Braces, italic _{%s}_; Nice!",
			"Braces, italic _{%s}_. Nice!",
		);

		$lfinder = new LinkFinder();

		foreach($links as $link_src => $expected){
			$expected = str_replace('&','&amp;',$expected); // "www.example.com/article.pl?id=123&format=raw" => "www.example.com/article.pl?id=123&amp;format=raw"
			foreach($templates as $template){
				// LinkFinder::process()
				$_src = sprintf($template,$link_src);
				$out = $lfinder->process($_src);
				$this->assertEquals(true,!!preg_match('/<a href="([^"]+)">/',$out,$matches),"$_src is containing a link");
				$this->assertEquals($expected,$matches[1],"$_src is containing $expected");

				// LinkFinder::processHtml()
				$template = htmlspecialchars($template); // $template must be a valid HTML snippet
				$_src = sprintf($template,htmlspecialchars($link_src));
				$out = $lfinder->processHtml($_src);
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
			'.www.example.com',
			'Dostali jsme žádost o reset vašeho Facebook hesla.Zadejte tento kód pro reset', // hesla.Za
			'Bolí vás r-a.mena a krk' // r-a.me
		);

		$lfinder = new LinkFinder();

		foreach($not_links as $str){
			$out = $lfinder->process($str);
			$this->assertEquals($str,$out,"\"$out\" should not contain a link");
		}
	}

	function testSecuredWebsite(){
		global $_SERVER;

		$src = 'atk14.net, www.atk14.net, example.com/nice-page/, TWEATER.COM/?ok=1';

		$_SERVER = array();
		$lfinder = new LinkFinder();
		$this->assertEquals('<a href="http://atk14.net">atk14.net</a>, <a href="http://www.atk14.net">www.atk14.net</a>, <a href="http://example.com/nice-page/">example.com/nice-page/</a>, <a href="http://TWEATER.COM/?ok=1">TWEATER.COM/?ok=1</a>',$lfinder->process($src));

		$lfinder = new LinkFinder(array("secured_websites" => array("example.com", "tweater.com","somewhere.com")));
		$this->assertEquals('<a href="http://atk14.net">atk14.net</a>, <a href="http://www.atk14.net">www.atk14.net</a>, <a href="https://example.com/nice-page/">example.com/nice-page/</a>, <a href="https://TWEATER.COM/?ok=1">TWEATER.COM/?ok=1</a>',$lfinder->process($src));

		// Auto configuration
		$_SERVER["HTTP_HOST"] = "atk14.net";
		$lfinder = new LinkFinder();
		$this->assertEquals('<a href="http://atk14.net">atk14.net</a>, <a href="http://www.atk14.net">www.atk14.net</a>, <a href="http://example.com/nice-page/">example.com/nice-page/</a>, <a href="http://TWEATER.COM/?ok=1">TWEATER.COM/?ok=1</a>',$lfinder->process($src));

		$_SERVER["HTTP_HOST"] = "www.atk14.net";
		$lfinder = new LinkFinder();
		$this->assertEquals('<a href="http://atk14.net">atk14.net</a>, <a href="http://www.atk14.net">www.atk14.net</a>, <a href="http://example.com/nice-page/">example.com/nice-page/</a>, <a href="http://TWEATER.COM/?ok=1">TWEATER.COM/?ok=1</a>',$lfinder->process($src));

		$_SERVER["HTTP_HOST"] = "atk14.net";
		$_SERVER["HTTPS"] = "on";
		$lfinder = new LinkFinder();
		$this->assertEquals('<a href="https://atk14.net">atk14.net</a>, <a href="https://www.atk14.net">www.atk14.net</a>, <a href="http://example.com/nice-page/">example.com/nice-page/</a>, <a href="http://TWEATER.COM/?ok=1">TWEATER.COM/?ok=1</a>',$lfinder->process($src));

		$_SERVER["HTTP_HOST"] = "www.atk14.net";
		$_SERVER["HTTPS"] = "on";
		$lfinder = new LinkFinder();
		$this->assertEquals('<a href="https://atk14.net">atk14.net</a>, <a href="https://www.atk14.net">www.atk14.net</a>, <a href="http://example.com/nice-page/">example.com/nice-page/</a>, <a href="http://TWEATER.COM/?ok=1">TWEATER.COM/?ok=1</a>',$lfinder->process($src));

		$_SERVER["HTTP_HOST"] = "somewhere.org";
		$_SERVER["HTTPS"] = "on";
		$lfinder = new LinkFinder();
		$this->assertEquals('<a href="http://atk14.net">atk14.net</a>, <a href="http://www.atk14.net">www.atk14.net</a>, <a href="http://example.com/nice-page/">example.com/nice-page/</a>, <a href="http://TWEATER.COM/?ok=1">TWEATER.COM/?ok=1</a>',$lfinder->process($src));
	}

	function testShorteningUrl(){
		$src = 'Long URL: https://venturebeat.com/2018/05/01/donkey-kong-country-tropical-freeze-review-a-funky-fresh-switch-update/, short URL: https://cz.ign.com/se/?q=mario';

		$lfinder = new LinkFinder();
		$this->assertEquals('Long URL: <a href="https://venturebeat.com/2018/05/01/donkey-kong-country-tropical-freeze-review-a-funky-fresh-switch-update/">https://venturebeat.com/2018/05/01/donkey-kong-country-tropica...</a>, short URL: <a href="https://cz.ign.com/se/?q=mario">https://cz.ign.com/se/?q=mario</a>',$lfinder->process($src));

		$lfinder = new LinkFinder(array("shorten_long_urls" => false));
		$this->assertEquals('Long URL: <a href="https://venturebeat.com/2018/05/01/donkey-kong-country-tropical-freeze-review-a-funky-fresh-switch-update/">https://venturebeat.com/2018/05/01/donkey-kong-country-tropical-freeze-review-a-funky-fresh-switch-update/</a>, short URL: <a href="https://cz.ign.com/se/?q=mario">https://cz.ign.com/se/?q=mario</a>',$lfinder->process($src));
	}

	// https://github.com/yarri/LinkFinder/issues/5
	function testIssue5(){
		$src = '501018655941-lu5e4mhrmo1opkef4d8b7i5tpgjj84ac.apps.googleusercontent.com';

		$lfinder = new LinkFinder();
		$this->assertEquals('<a href="http://501018655941-lu5e4mhrmo1opkef4d8b7i5tpgjj84ac.apps.googleusercontent.com">501018655941-lu5e4mhrmo1opkef4d8b7i5tpgjj84ac.apps.googleusercontent.com</a>',$lfinder->process($src));
	}

	function testIssue(){
		$src = ' "https://www.mill.cz/vyhledavani/vyhledej.htm?search=hardline"; ';
		$lfinder = new LinkFinder();
		$this->assertEquals(' "<a href="https://www.mill.cz/vyhledavani/vyhledej.htm?search=hardline">https://www.mill.cz/vyhledavani/vyhledej.htm?search=hardline</a>"; ',$lfinder->processHtml($src));

		$src = 'Zatím ve dvou barvách <a
href="https://www.mill.cz/vyhledavani/vyhledej.htm?search=irbis">v našem
e-shopu</a>';
		$lfinder = new LinkFinder();
		$this->assertEquals('Zatím ve dvou barvách &lt;a
href=&quot;<a href="https://www.mill.cz/vyhledavani/vyhledej.htm?search=irbis">https://www.mill.cz/vyhledavani/vyhledej.htm?search=irbis</a>&quot;&gt;v našem
e-shopu&lt;/a&gt;',$lfinder->process($src));
	}

	function testIssueHtmlEntity(){
		$src = '<p>Pages about the original Markdown can be found at https://daringfireball.net/projects/markdown/.&#160;<a href="#fnref:1" class="footnote-backref" role="doc-backlink">&#8617;&#xFE0E;</a></p>';
		$lfinder = new LinkFinder();
		$this->assertEquals('<p>Pages about the original Markdown can be found at <a href="https://daringfireball.net/projects/markdown/">https://daringfireball.net/projects/markdown/</a>.&#160;<a href="#fnref:1" class="footnote-backref" role="doc-backlink">&#8617;&#xFE0E;</a></p>',$lfinder->processHtml($src));

		$src = '<p>URL1: http://www.example.com/&#xFE0E; URL2: http://www.example.com/page.html&#8617;</p>';
		$lfinder = new LinkFinder();
		$this->assertEquals('<p>URL1: <a href="http://www.example.com/">http://www.example.com/</a>&#xFE0E; URL2: <a href="http://www.example.com/page.html">http://www.example.com/page.html</a>&#8617;</p>',$lfinder->processHtml($src));
	}

	function test_invalid_utf8_char(){
		$lfinder = new LinkFinder();

		$invalid_char = chr(200);
		$src = "Lorem$invalid_char www.ipsum.com. dolor@sit.net. Thank you";
		$this->assertEquals(
			$src,
			$lfinder->process($src)
		);
		//
		$invalid_char = chr(200);
		$src = "Lorem$invalid_char <www.ipsum.com>. dolor@sit.net. Thank you";
		$this->assertEquals(
			"Lorem$invalid_char &lt;www.ipsum.com&gt;. dolor@sit.net. Thank you",
			$lfinder->process($src)
		);
	}

	function test_coma_in_url(){
		$lfinder = new LinkFinder();
		foreach(array(
			'www.example.com,see' => '<a href="http://www.example.com">www.example.com</a>,see',
			'https://www.example.com/,see' => '<a href="https://www.example.com/">https://www.example.com/</a>,see',
			'www.example.com/?p=a,b' => '<a href="http://www.example.com/?p=a,b">www.example.com/?p=a,b</a>',
			'www.example.com/?p=ab,' => '<a href="http://www.example.com/?p=ab">www.example.com/?p=ab</a>,',

			// https://github.com/yarri/LinkFinder/pull/6
			'https://www.trulia.com/for_sale/Las_Vegas,NV/2p_beds/' => '<a href="https://www.trulia.com/for_sale/Las_Vegas,NV/2p_beds/">https://www.trulia.com/for_sale/Las_Vegas,NV/2p_beds/</a>'
		) as $src => $expected){
			$result = $lfinder->processHtml($src);
			$this->assertEquals($expected,$result);
		}
	}

}
