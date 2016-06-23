#!/usr/bin/env bash

# This project is a plugn.
export WORDPOINTS_PROJECT_TYPE=wordpoints

# The dev lib uses a non-default path.
export DEV_LIB_PATH=dev-lib-wordpoints

# Sets up custom configuration.
function wordpoints-dev-lib-config() {

	# Use the develop branch for WPCS.
	#export WPCS_GIT_TREE=develop

	# Use a stable commit for PHPCS.
	export PHPCS_GIT_TREE=4122da6604e2967c257d6c81151122d08cae60cf

	# Ignore the WordPress dev lib when codesniffing.
	CODESNIFF_PATH+=('!' -path "./dev-lib/*")
	export CODESNIFF_PATH

	# Fix failures on HHVM (#317) and when running Codeception tests (#321).
	if [[ $DO_CODE_COVERAGE == 1 || $DO_WP_CEPT == 1 ]]; then
		alias phpunit-ms-network='phpunit-ms-network; A=$?; composer remove --dev jdgrimes/wp-filesystem-mock; [[ $A == 0 ]]'
	fi
}

# EOF
