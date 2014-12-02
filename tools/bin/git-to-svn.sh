#!/bin/bash

if [ -z "$1" ]; then
	echo 'Usage: git-to-svn.sh <path-to-svn> [<subpath>]'
	echo '	<subpath> is the path within the SVN checkout to copy the files to.'
	echo '	It defaults to trunk. Another possible value would be branches/1.8.'
	return 0
fi

svn=$1

if [ -z "$2" ]; then
	subpath=trunk
else
	subpath=$2
fi

git=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

echo "Removing old files from SVN /$subpath..."
sudo find "$svn/$subpath" -type f -exec rm '{}' ';'

echo "Copying new files from git to SVN /$subpath..."

to_copy=( .jshintignore .jshintrc .travis.yml composer.json src composer.lock tests phpcs.ruleset.xml tools assets phpunit.xml.dist bin readme.txt wp-l10n-validator.json )

for file in "${to_copy[@]}"; do
	cp -r "$git/$file" "$svn/$subpath/"
done

echo 'Moving the assets directory...'
sudo rm -rf "$svn/assets"
mv "$svn/$subpath/assets" "$svn"

echo 'Done! You can now commit the changes.'

# EOF
