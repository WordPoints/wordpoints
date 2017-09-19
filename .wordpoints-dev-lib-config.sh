#!/usr/bin/env bash

# This project is a plugin.
export WORDPOINTS_PROJECT_TYPE=wordpoints

# Sets up custom configuration.
function wordpoints-dev-lib-config() {

	# Use the develop branch for WPCS.
	if [[ $TRAVIS_BRANCH == master || $TRAVIS_BRANCH =~ release || $TRAVIS_TAG ]]; then

		export WPCS_GIT_TREE=607db751e90e6d32f96fcb15c4aec8609d059d57

		# Use PHPCS 2.9, since WPCS doesn't support 3.0 yet.
		export PHPCS_GIT_TREE=2.9

	else
		export WPCS_GIT_TREE=develop
	fi

	# Ignore some strings that are expected.
	CODESNIFF_IGNORED_STRINGS=(\
		"${CODESNIFF_IGNORED_STRINGS[@]}" \
		# Ticket related to removing blank target links, mentioned in the changelog.
		-e '#558' \
	)

	CODESNIFF_PATH_STRINGS=(\
		"${CODESNIFF_PATH_STRINGS[@]}" \
		# Needs to use non-HTTPS since it may not be supported.
		'!' -path './src/classes/extension/server.php' \
		# Tests for the above class.
		'!' -path './tests/phpunit/tests/classes/extension/server.php' \
		# Needs to use non-HTTPS for XML URIs.
		'!' -path './assets/icon.svg' \
	)

	# This file requires PHP 7.
	if [[ ${TRAVIS_PHP_VERSION:0:2} != "7." && $TRAVIS_PHP_VERSION != nightly ]]; then
		CODESNIFF_PATH_PHP_SYNTAX=(\
			"${CODESNIFF_PATH_PHP_SYNTAX[@]}" \
			'!' -path "./tests/phpunit/data/modules/extension-9/extension-9.php" \
		)
	fi

	# Has to be set to something or else the WP HTTP Testcase will not use the cache.
	export WP_HTTP_TC_HOST=example.com

	# Disable code coverage for now, it takes to long to run.
	export DO_CODE_COVERAGE=0
}

# EOF
