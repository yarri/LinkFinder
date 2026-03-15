#!/bin/sh

# This scripts updates tlds.txt and tlds.php.
# Once the files have been updated, commit them to the repository.

# Getting the list of all tlds
wget https://data.iana.org/TLD/tlds-alpha-by-domain.txt -O tlds.txt || exit 1

# Converting tlds.txt into tlds.php
echo '<?php' > tlds.php
echo 'return array(' >> tlds.php
cat tlds.txt | 
	grep -v '#' | # comment
	grep -v 'XN--' | # IDN domain names are not currentry supported
	tr [A-Z] [a-z] | # lowercase
	sed "s/.*/'&',/" | # com -> 'com',
	cat >> tlds.php
echo ');' >> tlds.php
