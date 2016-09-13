#!/usr/bin/env bash

# This project is a plugin.
export WORDPOINTS_PROJECT_TYPE=wordpoints

# The dev lib uses a non-default path.
export DEV_LIB_PATH=dev-lib-wordpoints

# Sets up custom configuration.
function wordpoints-dev-lib-config() {

	# Use the develop branch for WPCS.
	export WPCS_GIT_TREE=develop

	# Use a stable commit for PHPCS.
	export PHPCS_GIT_TREE=fb72ed32f8418db5e7770be1653e62e0d6f5dd3d

	# Ignore the WordPress dev lib when codesniffing.
	CODESNIFF_PATH+=('!' -path "./dev-lib/*")
	export CODESNIFF_PATH
}

# EOF
