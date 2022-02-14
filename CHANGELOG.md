# Change Log
All notable changes to LinkFinder will be documented in this file.

## [Unreleased]

## [2.7.6] 2022-02-14

* 16b2ae1 - Resolved issue with HTML entities

## [2.7.5] 2021-12-20

* ce2cec3 - Fixed false detection of URLs

## [2.7.4] 2021-12-17

* 85317e4 - URLs and emails in parentheses are correctly recognized (e.g. _[http://www.example.com/]_, [http://www.example.com/]:)
* 9497b22 - Proper recognition of URLs with URI starting with question mark (e.g. www.example.com?page=1)
* db545ab - The asterisk is considered a valid character in the URI

## [2.7.3] 2021-11-23
* 1b1bd38 - Proper detection of URLs with email in URI part (e.g www.example.com/unsubscribe/john@doe.com/)

## [2.7.2] 2021-09-06
* URL can include exclamation mark and dollar sign
* A domain name part can be one character long (e.g. i.example.com)

## [2.7.1] 2021-05-06
* Function for URL shortening fixed

## [2.7] 2021-04-26
* Long URLs are automatically shortened to a maximum of 70 characters. It can be disabled with option shorten_long_urls set to false.

## [2.6] 2020-10-30
* Added option secured_websites
* Project is being tested in PHP 8

## [2.5] 2020-04-17
* Added option avoid_headlines (default true) to control links creation in headlines (&lt;h1&gt;, &lt;h2&gt;, ....)
* Added more generic TLDs (approx 40)

## [2.4.3] 2020-01-03
* Fixes link recognition in angled braces (in a HTML snippet)

## [2.4.2] 2020-01-01
* Preventing of deletion of text containing an invalid UTF-8 character

## [2.4.1] 2019-12-05
* Added detection of links in square brackets
* Project is being tested in PHP 7.4

## [2.4] 2019-02-11
* Added method LinkFinder::processHtml()
* Added some more popular top level domains
* Project is being tested in PHP 7.3

## [2.3.1] 2018-12-04
* Added support for urls with username and password

## [2.3] 2018-04-06
* Links detection tuned

### Added
* Added option utf8 for texts in UTF-8 (default true)

## [2.2] 2017-10-05
* Auto URLs detection even without "www." prefix

## [2.1] - 2017-04-01
* Improvements and fixes in URL recognitions
* Testing with PHPUnit 4.8

## [2.0] - 2016-03-15
### Added
* Attributes for ```<a>``` and ```<a href="mailto:...">``` elements can be defined freely using option attrs and mailto_attrs (https://github.com/yarri/LinkFinder/issues/1).

### Changed
* Options open_links_in_new_windows, link_class and mailto_class are considered as obsolete.
* Default templates for links and mailto-links changed.

## [1.0] - 2016-03-16
