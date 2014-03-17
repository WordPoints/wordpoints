#!/bin/bash

if [ -z $1 ];
	then echo "Usage: $0 <version>";
	exit;
fi

sed -i '' -e 's/\(\* @\{0,1\}[Vv]\{1\}ersion:\{0,1\}\) \([^\n]*\)/\1 '"$1"'/' \
	./src/wordpoints.php;

sed -i '' -e 's/VERSION'"'"', '"'"'[^'"'"']*/VERSION'"'"', '"'""$1"'/' \
	./src/includes/constants.php
