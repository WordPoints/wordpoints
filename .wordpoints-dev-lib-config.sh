#!/usr/bin/env bash

# This project is a plugin.
export WORDPOINTS_PROJECT_TYPE=wordpoints

# Sets up custom configuration.
function wordpoints-dev-lib-config() {

	# Use the develop branch for WPCS.
	if [[ $TRAVIS_BRANCH == master || $TRAVIS_BRANCH =~ release || $TRAVIS_TAG ]]; then
		export WPCS_GIT_TREE=607db751e90e6d32f96fcb15c4aec8609d059d57
	else
		export WPCS_GIT_TREE=develop
	fi

	# Use PHPCS 2.7.0, since WPCS 0.11.0 requires it.
	export PHPCS_GIT_TREE=master

	# Ignore some strings that are expected.
	CODESNIFF_IGNORED_STRINGS=(\
		"${CODESNIFF_IGNORED_STRINGS[@]}" \
		# Doesn't support HTTPS.
		-e sodipodi.sourceforge.net \
		# Ticket related to removing blank target links, mentioned in the changelog.
		-e '#558' \
	)

	CODESNIFF_PATH_STRINGS=(\
		"${CODESNIFF_PATH_STRINGS[@]}" \
		# Needs to use non-HTTPS since it may not be supported.
		'!' -path './src/classes/module/server.php' \
		# Tests for the above class.
		'!' -path './tests/phpunit/tests/classes/module/server.php' \
	)
}

# EOF
