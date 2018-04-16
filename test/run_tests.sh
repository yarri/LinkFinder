#/bin/sh

cd $(dirname $0)
exec ../vendor/bin/phpunit link_finder_test.php
