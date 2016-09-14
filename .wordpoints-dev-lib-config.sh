#!/usr/bin/env bash

# This project is a plugin.
export WORDPOINTS_PROJECT_TYPE=wordpoints

# Sets up custom configuration.
function wordpoints-dev-lib-config() {

	# Use the develop branch for WPCS.
	export WPCS_GIT_TREE=develop

	# Use PHPCS 2.7.0, since WPCS 0.11.0 requires it.
	export PHPCS_GIT_TREE=571e27b6348e5b3a637b2abc82ac0d01e6d7bbed
}

# EOF
