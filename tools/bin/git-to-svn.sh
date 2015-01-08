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
cp -r "$git/src" "$svn/$subpath/"

echo 'Copying the assets directory...'
sudo rm -rf "$svn/assets"
cp -r "$git/assets" "$svn/assets"

echo 'Done! You can now commit the changes.'

# EOF
