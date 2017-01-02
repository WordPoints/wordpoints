#!/usr/bin/env bash

# This project is a plugin.
export WORDPOINTS_PROJECT_TYPE=wordpoints

# Sets up custom configuration.
function wordpoints-dev-lib-config() {

	# Use the develop branch for WPCS.
	if [[ $TRAVIS_BRANCH != stable && ! $TRAVIS_BRANCH =~ release ]]; then
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
}

# EOF
