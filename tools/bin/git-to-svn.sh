#!/bin/bash

if [ -z "$1" ]; then
	echo 'Usage: git-to-svn.sh <path-to-svn> [<subpath>]'
	echo '	<subpath> is the path within the SVN checkout to copy the files to.'
	echo '	It defaults to trunk. Another possible value would be branches/1.8.'
	exit 0
fi

svn=$1

if [ -z "$2" ]; then
	subpath=trunk
else
	subpath=$2
fi

git=$( cd "$( dirname "${BASH_SOURCE[0]}" | xargs dirname | xargs dirname )" && pwd )

echo "Syncing files from git to SVN /$subpath..."
rsync -avz --delete "$git/src/" "$svn/$subpath/"

echo 'Syncing the assets directory...'
rsync -avz --delete  "$git/assets/" "$svn/assets/"

cd "$svn"

if svn status | grep -s '^!'; then
	echo 'Removing deleted files from SVN...'
	svn status | grep '^!' | awk '{print $2}' | xargs svn delete --force
fi

echo 'Adding new files to SVN...'
svn add --force "$svn/$subpath/" --auto-props --parents --depth infinity -q
svn add --force assets/ --auto-props --parents --depth infinity -q

echo 'Updating asset MIME types...'
OLD_IFS="$IFS"
IFS=$'\n'
for file in $( find assets/ -name "*.png" ); do
	if svn info "$file" 1>/dev/null 2>&1; then
		svn propset svn:mime-type image/png "$file"
	fi
done
IFS="$OLD_IFS"

echo 'Done! You can now commit the changes:'

svn status

# EOF
