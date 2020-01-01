# Change Log
All notable changes to LinkFinder will be documented in this file.

## [Unreleased]

## [2.4.2] 2020-01-01
- Preventing of deletion of text containing an invalid UTF-8 character

## [2.4.1] 2019-12-05
- Added detection of links in square brackets
- Project is being tested in PHP 7.4

## [2.4] 2019-02-11
- Added method LinkFinder::processHtml()
- Added some more popular top level domains
- Project is being tested in PHP 7.3

## [2.3.1] 2018-12-04
- Added support for urls with username and password

## [2.3] 2018-04-06
- Links detection tuned

### Added
- Added option utf8 for texts in UTF-8 (default true)

## [2.2] 2017-10-05
- Auto URLs detection even without "www." prefix

## [2.1] - 2017-04-01
- Improvements and fixes in URL recognitions
- Testing with PHPUnit 4.8

## [2.0] - 2016-03-15
### Added
- Attributes for ```<a>``` and ```<a href="mailto:...">``` elements can be defined freely using option attrs and mailto_attrs (https://github.com/yarri/LinkFinder/issues/1).

### Changed
- Options open_links_in_new_windows, link_class and mailto_class are considered as obsolete.
- Default templates for links and mailto-links changed.

## [1.0] - 2016-03-16
