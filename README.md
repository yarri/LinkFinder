LinkFinder
----------

LinkFinder is a PHP class. In a plain text document the LinkFinder searches for URLs and e-mail addresses and make them clickable.

```php
$lf = new LinkFinder();
echo $lf->Process('Welcome at www.example.com! Contact us on info@example.com.');
// Welcome at <a href="http://www.example.com/">www.example.com</a>! Contact us on <a href="mailto:info@example.com">info@example.com</a>.
```
