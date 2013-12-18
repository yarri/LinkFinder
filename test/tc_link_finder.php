<?php
class TcLinkFinder extends TcBase{
	function testBasicUsage(){
		$src = 'Lorem www.ipsum.com. dolor@sit.net. Thank you';
		$lf = new LinkFinder();
		$this->assertEquals('Lorem <a href="http://www.ipsum.com">www.ipsum.com</a>. <a href="mailto:dolor@sit.net">dolor@sit.net</a>. Thank you',$lf->process($src));
	}

	function testLinks(){
		$links = array(
			"www.ipsum.com" => "http://www.ipsum.com",
			"http://www.ipsum.com/" => "http://www.ipsum.com/",
			"https://www.example.com/article.pl?id=123" => "https://www.example.com/article.pl?id=123",
			"www.example.com/article.pl?id=123" => "http://www.example.com/article.pl?id=123",
			"www.example.com/article.pl?id=123&format=raw" => "http://www.example.com/article.pl?id=123&format=raw",
			"www.example.com/article.pl?id=123;format=raw" => "http://www.example.com/article.pl?id=123;format=raw",
			"www.www.example.intl" => "http://www.www.example.intl",

			"ftp://example.com/public/" => "ftp://example.com/public/"
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
		);

		$lf = new LinkFinder();
		foreach($links as $src => $expected){
			foreach($templates as $template){
				$out = $lf->process($_src = sprintf($template,$src));
				$this->assertEquals(true,!!preg_match('/<a href="([^"]+)">/',$out,$matches),"$_src is containing a link");
				$this->assertEquals($expected,$matches[1],"$_src is containing $expected");
			}
		}
	}
}
