LinkFinder
==========

[![Build Status](https://travis-ci.org/yarri/LinkFinder.svg?branch=master)](https://travis-ci.org/yarri/LinkFinder)
[![Downloads](https://img.shields.io/packagist/dt/yarri/link-finder.svg)](https://packagist.org/packages/yarri/link-finder)

LinkFinder is a PHP class. In a plain text document the LinkFinder searches for URLs and email addresses and makes them clickable.

    $text = '
     Welcome at www.example.com!
     Contact us on info@example.com.
    ';
    
    $lf = new LinkFinder();
    echo $lf->process($text);
    
    // ... this prints out
    //  Welcome at <a href="http://www.example.com/">www.example.com</a>!
    //  Contact us on <a href="mailto:info@example.com">info@example.com</a>.

Extra attributes for ```<a>``` and ```<a href="mailto:...">``` elements can be specified in options:

    $lf = new LinkFinder([
      "attrs" => ["class" => "external-link", "target" => "_blank", "rel" => "nofollow"],
      "mailto_attrs" => ["class" => "external-email"]
    ]);
    echo $lf->process($text);
    
    // ... this prints out
    //  Welcome at <a class="external-link" href="http://www.example.com/" target="_blank" rel="nofollow">www.example.com</a>!
    //  Contact us on <a class="external-email" href="mailto:info@example.com">info@example.com</a>.


Escaping of HTML entities is enabled by default:

    $text = '
      Find more at
      <http://www.ourstore.com/>
    ';
    echo $lf->process($text);
    // Find more at
    // &lt;<a href="http://www.ourstore.com/">http://www.ourstore.com/</a>&gt;

The LinkFinder can be used for auto-creating links in a Markdown document:

    $markdown = file_get_contents("/path/to/markdown/document");
    $html = Michelf\Markdown::defaultTransform($markdown);
    $lf = new LinkFinder(array("escape_html_entities" => false));
    echo $lf->process($html);

Installation
------------

The best way how to install LinkFinder is to use a Composer:

    composer require yarri/link-finder dev-master

od just download the latest version from Github:

    wget https://raw.github.com/yarri/LinkFinder/master/src/link_finder.php

License
-------

LinkFinder is free software distributed [under the terms of the MIT license](http://www.opensource.org/licenses/mit-license)

[//]: # ( vim: set ts=2 et: )
