#!/usr/bin/env bash

# This project is a plugin.
export WORDPOINTS_PROJECT_TYPE=wordpoints

# Sets up custom configuration.
function wordpoints-dev-lib-config() {

	# Use the develop branch for WPCS.
	export WPCS_GIT_TREE=develop

	# Use PHPCS 2.7.0, since WPCS 0.11.0 requires it.
	export PHPCS_GIT_TREE=master
}

# EOF
