#!/bin/sh

cd $(dirname $0)
exec ../vendor/bin/run_unit_tests
