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

	# Fix failures on HHVM. See #317.
	if [[ $DO_CODE_COVERAGE == 1 ]]; then
		alias phpunit-ms-network='phpunit-ms-network; composer remove --dev jdgrimes/wp-filesystem-mock'
	fi

	if [[ $TRAVIS_PHP_VERSION == nightly ]]; then
		alias setup-phpunit='setup-phpunit; wget -O /tmp/31982.1.diff https://core.trac.wordpress.org/raw-attachment/ticket/31982/31982.1.diff; cd $WP_DEVELOP_DIR; patch -p0 < /tmp/31982.1.diff; cd -'
	fi
}

# EOF
