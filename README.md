LinkFinder
==========

LinkFinder is a PHP class. In a plain text document the LinkFinder searches for URLs and e-mail addresses and makes them clickable.

```php
$text = '
 Welcome at www.example.com!
 Contact us on info@example.com.
';

$lf = new LinkFinder();
echo $lf->process($text);

// ... this prints out
//  Welcome at <a href="http://www.example.com/">www.example.com</a>!
//  Contact us on <a href="mailto:info@example.com">info@example.com</a>.
```

Escaping of HTML entities is enabled by default:

```php
$text = 'Find more at www.ourstore.com <http://www.ourstore.com/>';
echo $lf->process($text);
// Find more at <a href="http://www.ourstore.com">www.ourstore.com</a> &lt;<a href="http://www.ourstore.com/">http://www.ourstore.com/</a>&gt;
```

The best way how to install LinkFinder is to download a latest version from Github...
```bash
wget https://raw.github.com/yarri/LinkFinder/master/src/link_finder.php
```

... or use a Composer:

```bash
composer require yarri/link-finder dev-master
```
