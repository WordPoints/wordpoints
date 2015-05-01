#!/usr/bin/env bash

# This project is a plugn.
export WORDPOINTS_PROJECT_TYPE=wordpoints

# The dev lib uses a non-default path.
export DEV_LIB_PATH=dev-lib-wordpoints

# Sets up custom configuration.
function wordpoints-dev-lib-config() {

	# Use the develop branch for WPCS.
	export WPCS_GIT_TREE=develop

	# Ignore the WordPress dev lib when codesniffing.
	CODESNIFF_PATH+=('!' -path "./dev-lib/*")
	export CODESNIFF_PATH
}

# EOF
