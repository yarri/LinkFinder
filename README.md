LinkFinder
==========

[![Tests](https://github.com/yarri/LinkFinder/actions/workflows/tests.yml/badge.svg)](https://github.com/yarri/LinkFinder/actions/workflows/tests.yml)
[![Downloads](https://img.shields.io/packagist/dt/yarri/link-finder.svg)](https://packagist.org/packages/yarri/link-finder)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/63b456d41b7c4232b3f96fe4b5da8be7)](https://app.codacy.com/gh/yarri/LinkFinder/dashboard)

LinkFinder detects URLs and email addresses in plain text or HTML and wraps them in `<a>` tags. In HTML documents it only linkifies text that is not already linked.

- [Installation](#installation)
- [Basic usage](#basic-usage)
- [Processing HTML](#processing-html)
- [Options reference](#options-reference)
- [Callbacks](#callbacks)
- [Custom templates](#custom-templates)
- [Testing](#testing)

Installation
------------

```bash
composer require yarri/link-finder
```

Basic usage
-----------

Pass plain text to `process()`. URLs and email addresses are detected automatically.

```php
$lf = new LinkFinder();

echo $lf->process('Welcome at www.example.com!');
// Welcome at <a href="https://www.example.com">www.example.com</a>!

echo $lf->process('Contact us on info@example.com.');
// Contact us on <a href="mailto:info@example.com">info@example.com</a>.
```

HTML entities in the input are escaped by default, so plain text containing `<`, `>`, or `&` is safe to pass directly:

```php
echo $lf->process('Find more at <http://www.ourstore.com/>');
// Find more at &lt;<a href="http://www.ourstore.com/">http://www.ourstore.com/</a>&gt;
```

Extra attributes can be set on the generated `<a>` elements via the `attrs` and `mailto_attrs` options:

```php
$lf = new LinkFinder([
  "attrs" => [
    "class"  => "external-link",
    "target" => "_blank",
    "rel"    => "nofollow",
  ],
  "mailto_attrs" => [
    "class" => "external-email",
  ],
]);

echo $lf->process('Welcome at www.example.com! Contact us on info@example.com.');
// Welcome at <a class="external-link" href="https://www.example.com" rel="nofollow" target="_blank">www.example.com</a>!
// Contact us on <a class="external-email" href="mailto:info@example.com">info@example.com</a>.
```

Processing HTML
---------------

Use `processHtml()` when the input is an HTML document. LinkFinder will skip content inside existing `<a>`, `<script>`, `<style>`, `<textarea>`, and `<head>` tags, and will only linkify bare URLs and emails in the visible text.

```php
$html = '
  <p>
    Visit <a href="http://www.ckrumlov.info/">Cesky Krumlov</a> or Prague.eu.
  </p>
';

$lf = new LinkFinder();
echo $lf->processHtml($html);

// <p>
//   Visit <a href="http://www.ckrumlov.info/">Cesky Krumlov</a> or <a href="https://Prague.eu">Prague.eu</a>.
// </p>
```

`processHtml()` is equivalent to calling `process($html, ["escape_html_entities" => false])`.

By default, URLs inside headline elements (`<h1>` through `<h6>`) are left alone. To linkify them as well:

```php
echo $lf->processHtml($html, ["avoid_headlines" => false]);

// or permanently via the constructor:
$lf = new LinkFinder(["avoid_headlines" => false]);
```

Options reference
-----------------

All options can be passed to the constructor or as the second argument of `process()` / `processHtml()`. Options passed to a method override the constructor defaults for that call only.

| Option | Type | Default | Description |
|---|---|---|---|
| `attrs` | array | `[]` | HTML attributes added to every link `<a>` element. |
| `mailto_attrs` | array | `[]` | HTML attributes added to every mailto `<a>` element. |
| `escape_html_entities` | bool | `true` | Escape `<`, `>`, `&`, `"` in the input before processing. Disable when the input is already HTML. |
| `avoid_headlines` | bool | `true` | Skip linkification inside `<h1>`–`<h6>` elements when processing HTML. |
| `prefer_https` | bool | `true` | Use `https://` when no protocol is specified (e.g. `www.example.com`). Can also be set globally via the `LINK_FINDER_PREFER_HTTPS` constant before the class is loaded. |
| `secured_websites` | array | `[]` | When `prefer_https` is `false`, list domains that should still get `https://`. Populated automatically from `$_SERVER["HTTP_HOST"]` when the current request is over HTTPS. |
| `shorten_long_urls` | bool | `true` | Truncate the visible link text for long URLs. The `href` is never shortened. |
| `shortened_url_max_length` | int | `65` | Maximum character length of the visible link text before truncation. |
| `href_callback` | callable | identity | Transform the URL before it is written into the `href` attribute. See [Callbacks](#callbacks). |
| `mailto_callback` | callable | `"mailto:$email"` | Transform an email address before it is written into the `href` attribute. See [Callbacks](#callbacks). |
| `link_template` | string | `<a %attrs%>%url%</a>` | Template for link elements. See [Custom templates](#custom-templates). |
| `mailto_template` | string | `<a %attrs%>%address%</a>` | Template for mailto elements. See [Custom templates](#custom-templates). |
| `utf8` | bool | `true` | Treat the input as UTF-8. |

### prefer_https and secured_websites

When `prefer_https` is `true` (the default), all bare URLs get `https://`. When it is `false`, you can still mark specific domains as secured:

```php
$lf = new LinkFinder([
  "prefer_https"     => false,
  "secured_websites" => ["example.com", "webmail.example.com"],
]);

echo $lf->process('Sign in at example.com/login/ or visit plain.org.');
// Sign in at <a href="https://example.com/login/">example.com/login/</a> or visit <a href="http://plain.org">plain.org</a>.
```

When `prefer_https` is `false` and `secured_websites` is not set, LinkFinder automatically adds the current `$_SERVER["HTTP_HOST"]` (and its `www.` variant) if the request is served over HTTPS.

### Long URL shortening

The visible link text is truncated at 65 characters by default. The `href` always contains the full URL.

```php
// Disable shortening
$lf = new LinkFinder(["shorten_long_urls" => false]);

// Change the limit
$lf = new LinkFinder(["shortened_url_max_length" => 50]);
```

Callbacks
---------

### href_callback

Runs on every detected URL just before it is written into the `href` attribute. Use it to route all external links through a redirect proxy, or to rewrite URLs in any other way.

```php
$lf = new LinkFinder([
  "href_callback" => function ($url) {
    return "https://redirect.example.com/?url=" . urlencode($url);
  },
]);

echo $lf->process('www.atk14.net');
// <a href="https://redirect.example.com/?url=https%3A%2F%2Fwww.atk14.net">www.atk14.net</a>
```

The callback can also be set after construction:

```php
$lf->setHrefCallback(function ($url) { /* … */ });
```

Default: `function($url){ return $url; }`

### mailto_callback

Runs on every detected email address before it is written into the `href` attribute. Use it to point email links at a webmail compose page instead of a `mailto:` URI.

```php
$lf = new LinkFinder([
  "mailto_callback" => function ($email) {
    return "/compose.php?to=" . urlencode($email);
  },
]);

echo $lf->process('info@example.com');
// <a href="/compose.php?to=info%40example.com">info@example.com</a>
```

The callback can also be set after construction:

```php
$lf->setMailtoCallback(function ($email) { /* … */ });
```

Default: `function($email){ return "mailto:$email"; }`

Custom templates
----------------

The `link_template` and `mailto_template` options let you replace the entire `<a>` element with your own markup. The placeholder `%attrs%` is replaced with all rendered HTML attributes; `%url%` and `%address%` are replaced with the visible link text.

```php
$lf = new LinkFinder([
  "link_template" => '<span class="link-wrapper"><a %attrs% data-external="1">%url%</a></span>',
]);

echo $lf->process('www.example.com');
// <span class="link-wrapper"><a href="https://www.example.com" data-external="1">www.example.com</a></span>
```

Testing
-------

LinkFinder is tested automatically via GitHub Actions across PHP 5.6 to PHP 8.5.

Tests use the [atk14/tester](https://packagist.org/packages/atk14/tester) wrapper for [phpunit/phpunit](https://packagist.org/packages/phpunit/phpunit).

Install development dependencies:

```bash
composer update --dev
```

Run the test suite:

```bash
cd test
../vendor/bin/run_unit_tests
```

License
-------

LinkFinder is free software distributed [under the terms of the MIT license](http://www.opensource.org/licenses/mit-license).

[//]: # ( vim: set ts=2 et: )
