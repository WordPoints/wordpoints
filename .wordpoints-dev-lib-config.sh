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

	alias phpunit-uninstall='wordpoints-phpunit-basic uninstall'
	alias phpunit-ms-uninstall='WP_MULTISITE=1 wordpoints-phpunit-basic uninstall ms'
	alias phpunit-ms-network-uninstall='WORDPOINTS_NETWORK_ACTIVE=1 WP_MULTISITE=1 wordpoints-phpunit-basic uninstall ms-network'
}

# Special handling for PHPUnit uninstall tests.
wordpoints-wpdl-test-phpunit() {

	local TEST_GROUP=${1-''}
	local CLOVER_FILE=${2-basic}

	local GROUP_OPTION=()
	local COVERAGE_OPTION=()

	if [[ $TEST_GROUP != '' ]]; then
		if [[ $TEST_GROUP == ajax && $RUN_AJAX_TESTS == 0 ]]; then
			echo 'Not running Ajax tests.'
			return
		elif [[ $TEST_GROUP == uninstall && $RUN_UNINSTALL_TESTS == 0 ]]; then
			echo 'Not running uninstall tests.'
			return
		fi

		if [[ $WP_VERSION == '3.8' && $TEST_GROUP == ajax && $WP_MULTISITE == 1 ]]; then
			echo 'Not running multisite Ajax tests on 3.8, see https://github.com/WordPoints/wordpoints/issues/239.'
			return
		fi

		GROUP_OPTION=(--group="$TEST_GROUP")
		CLOVER_FILE+="-$TEST_GROUP"

		if [[ $TRAVIS_PHP_VERSION == '5.2' ]]; then
			sed -i '' -e "s/<group>$TEST_GROUP<\/group>//" ./phpunit.xml.dist
		fi
	fi

	if [[ $DO_CODE_COVERAGE == 1 ]]; then
		COVERAGE_OPTION=(--coverage-clover "build/logs/clover-$CLOVER_FILE.xml")
	fi

	if [[ $TEST_GROUP == uninstall ]]; then
		# Back-compat because PHP 5.2 runs PHPUnit 3.6, which doesn't support the
		# --testsuite option.
		if [[ $TRAVIS_PHP_VERSION == 5.2 ]]; then
			phpunit "${GROUP_OPTION[@]}" "${COVERAGE_OPTION[@]}" --no-configuration \
				--no-globals-backup --bootstrap tests/phpunit/includes/bootstrap.php \
				tests/phpunit/tests/uninstall.php
		else
			phpunit "${GROUP_OPTION[@]}" "${COVERAGE_OPTION[@]}" --testsuite uninstall
		fi
	else
		phpunit "${GROUP_OPTION[@]}" "${COVERAGE_OPTION[@]}"
	fi
}

# Special handling for PHPUnit uninstall tests.
wordpoints-phpunit-basic() {
	if [[ $TRAVISCI_RUN != phpunit ]]; then
		echo 'Not running PHPUnit.'
		return
	fi

	wordpoints-wpdl-test-phpunit "${@:1}"
}

# EOF
