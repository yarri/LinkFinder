LinkFinder
==========

[![Build Status](https://app.travis-ci.com/yarri/LinkFinder.svg?branch=master)](https://app.travis-ci.com/yarri/LinkFinder)
[![Downloads](https://img.shields.io/packagist/dt/yarri/link-finder.svg)](https://packagist.org/packages/yarri/link-finder)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/63b456d41b7c4232b3f96fe4b5da8be7)](https://app.codacy.com/gh/yarri/LinkFinder/dashboard)

In a plain text document the LinkFinder searches for URLs and email addresses and makes them clickable, in a HTML document searches for missing links and makes them clickable too.

Usage
-----

```php
$text = '
  Welcome at www.example.com!
  Contact us on info@example.com.
';

$lf = new LinkFinder();
echo $lf->process($text);

// Welcome at <a href="https://www.example.com/">www.example.com</a>!
// Contact us on <a href="mailto:info@example.com">info@example.com</a>.
```

Extra attributes for ```<a>``` and ```<a href="mailto:...">``` elements can be specified in options:

```php
$lf = new LinkFinder([
  "attrs" => ["class" => "external-link", "target" => "_blank", "rel" => "nofollow"],
  "mailto_attrs" => ["class" => "external-email"]
]);
echo $lf->process($text);

// Welcome at <a class="external-link" href="https://www.example.com/" target="_blank" rel="nofollow">www.example.com</a>!
// Contact us on <a class="external-email" href="mailto:info@example.com">info@example.com</a>.
```

Escaping of HTML entities is enabled by default:

```php
$text = '
  Find more at
  <http://www.ourstore.com/>
';

$lf = new LinkFinder();
echo $lf->process($text);

// Find more at
// &lt;<a href="http://www.ourstore.com/">http://www.ourstore.com/</a>&gt;
```

Creating missing links on URLs or emails in a HTML document:

```php
$html_document = '
  <p>
    Visit <a href="http://www.ckrumlov.info/">Cesky Krumlov</a> or Prague.eu.
  </p>
';

$lf = new LinkFinder();
echo $lf->processHtml($html_document);

// <p>
//   Visit <a href="http://www.ckrumlov.info/">Cesky Krumlov</a> or <a href="https://Prague.eu">Prague.eu</a>.
// </p>
```

Method `$lf->processHtml()` is actually an alias for `$lf->process($html_document,["escape_html_entities" => false])`.

In case of processing a HTML text, the LinkFinder doesn't create links in headlines (`<h1>`, `<h2>`, ...) by default. It can be overridden by the option avoid_headlines:

```php
echo $lf->processHtml($html_document,["avoid_headlines" => false]);

// or

$lf = new LinkFinder(["avoid_headlines" => false]);
echo $lf->processHtml($html_document);
```

If no protocol is specified in a future link (e.g. `www.example.com`), should LinkFinder prefer https over http? It can be set by the option `prefer_https`. The default value is true. There is also a constant `LINK_FINDER_PREFER_HTTPS` to change the default behaviour in the global scope.

If `prefer_https` is set to false, a list of secured websites can be specified in the option `secured_websites`:

```php
$lf = new LinkFinder([
  "prefer_https" => false,
  "secured_websites" => [
    "example.com",
    "webmail.example.com"
  ]
]);
echo $lf->process('Please, sign in at example.com/login/ or webmail.example.com');

// Please, sign in at <a href="https://example.com/login/">example.com/login/</a> or <a href="https://webmail.example.com">webmail.example.com</a>
```

If the secured_websites option is omitted and https protocol is active, the current HTTP host (```$_SERVER["HTTP_HOST"]```) will be added automatically.

#### Long URLs shortening

Long URLs are automatically shortened to a maximum of 70 characters. For example, the following URL:

```
https://venturebeat.com/2018/05/01/donkey-kong-country-tropical-freeze-review-a-funky-fresh-switch-update/
```

will be converted to:

```
<a href="https://venturebeat.com/2018/05/01/donkey-kong-country-tropical-freeze-review-a-funky-fresh-switch-update/">https://venturebeat.com/2018/05/01/donkey-kong-country-tropica...</a>
```

If the shortening is not desired behaviour, option shorten_long_urls should be set to false:

```php
$lf = new LinkFinder(["shorten_long_urls" => false]);
```

Installation
------------

Just use the Composer:

    composer require yarri/link-finder

Testing
-------

The LinkFinder is tested automatically using Travis CI in PHP 5.6 to PHP 8.4.

For the tests execution, the package [atk14/tester](https://packagist.org/packages/atk14/tester) is used. It is just a wrapping script for [phpunit/phpunit](https://packagist.org/packages/phpunit/phpunit).

Install required dependencies for development:

    composer update --dev

Run tests:

    cd test
    ../vendor/bin/run_unit_tests

License
-------

LinkFinder is free software distributed [under the terms of the MIT license](http://www.opensource.org/licenses/mit-license)

[//]: # ( vim: set ts=2 et: )
