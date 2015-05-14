LinkFinder
==========

LinkFinder is a PHP class. In a plain text document the LinkFinder searches for URLs and e-mail addresses and make them clickable.

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

The best way how to install LinkFinder is to download a latest version from Github...
```bash
wget https://raw.github.com/yarri/LinkFinder/master/src/link_finder.php
```

... or use a Composer:

```bash
composer require yarri/link-finder dev-master
```
