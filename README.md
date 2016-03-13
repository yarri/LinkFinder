LinkFinder
==========

LinkFinder is a PHP class. In a plain text document the LinkFinder searches for URLs and e-mail addresses and makes them clickable.

    $text = '
     Welcome at www.example.com!
     Contact us on info@example.com.
    ';
    
    $lf = new LinkFinder();
    echo $lf->process($text);
    
    // ... this prints out
    //  Welcome at <a href="http://www.example.com/">www.example.com</a>!
    //  Contact us on <a href="mailto:info@example.com">info@example.com</a>.

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

The best way how to install LinkFinder is to use a Composer:

    composer require yarri/link-finder dev-master

od just download the latest version from Github:

    wget https://raw.github.com/yarri/LinkFinder/master/src/link_finder.php
