#!/usr/bin/env sh

# Codesniffing tests.
if [ $TRAVISCI_RUN == codesniff ]; then

	# Check for parse errors.
	find . -path ./bin -prune -o \( -name '*.php' -o -name '*.inc' \) -exec php -lf {} \;

	# PHP Code Sniffer
	$PHPCS_DIR/scripts/phpcs -n --standard=$WPCS_STANDARD $(if [ -n "$PHPCS_IGNORE" ]; then echo --ignore=$PHPCS_IGNORE; fi) $(find . -name '*.php');

	# Check the JS files for correct style.
	jshint .
fi

# Run PHPUnit.
if [ $TRAVISCI_RUN == phpunit ]; then

	# We also check for parse errors.
	find . -path ./bin -prune -o \( -name '*.php' -o -name '*.inc' \) -exec php -lf {} \;

	# Now all the PHPUnit tests.
	wordpoints_travis_phpunit

	# On Multisite, run the PHPUnit tests again with WordPoints in network mode.
	if [ $WP_MULTISITE == 1 ]; then
		WORDPOINTS_NETWORK_ACTIVE=1 wordpoints_travis_phpunit
	fi
fi

# Function to run the PHPUnit test suite.
wordpoints_travis_phpunit() {

	# Main test suite.
	phpunit

	# Also run the install/uninstall tests (must be run apart from general suite).
	phpunit --group=uninstall

	# Run the Ajax tests (there is a bug in WP prior to 3.9 that will cause fails).
	if [ $WP_VERSION != '3.8' ]; then
		phpunit --group=ajax
	fi
}
