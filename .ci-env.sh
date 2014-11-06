
# Set up for the PHPUnit pass.
setup-phpunit() {

	if [[ $( php --version | grep ' 5.2' ) ]]; then
		mkdir -p vendor/jdgrimes/wp-plugin-uninstall-tester && curl -L \
			https://github.com/JDGrimes/wp-plugin-uninstall-tester/archive/0.3.0.tar.gz \
			| tar xvz --strip-components=1 -C vendor/jdgrimes/wp-plugin-uninstall-tester
	else
		composer install
	fi

    wget -O /tmp/install-wp-tests.sh \
        https://raw.githubusercontent.com/wp-cli/wp-cli/master/templates/install-wp-tests.sh

    bash /tmp/install-wp-tests.sh wordpress_test root '' localhost $WP_VERSION
    cd /tmp/wordpress/wp-content/plugins
    ln -s $PLUGIN_DIR $PLUGIN_SLUG
    cd $PLUGIN_DIR
}

# Set up for the codesniff pass.
setup-codesniff() {

    mkdir -p $PHPCS_DIR && curl -L \
        https://github.com/$PHPCS_GITHUB_SRC/archive/$PHPCS_GIT_TREE.tar.gz \
        | tar xvz --strip-components=1 -C $PHPCS_DIR

    mkdir -p $WPCS_DIR && curl -L \
    	https://github.com/$WPCS_GITHUB_SRC/archive/$WPCS_GIT_TREE.tar.gz \
    	| tar xvz --strip-components=1 -C $WPCS_DIR

    $PHPCS_DIR/scripts/phpcs --config-set installed_paths $WPCS_DIR

    npm install -g jshint

    if [ -e composer.json ]; then
    	wget http://getcomposer.org/composer.phar \
    		&& php composer.phar install --dev
    fi
}

# Check php files for syntax errors.
codesniff-php-syntax() {
	if [[ $TRAVISCI_RUN == codesniff ]] || [[ $TRAVISCI_RUN == phpunit ]]; then
		find . -path ./bin -prune -o \( -name '*.php' -o -name '*.inc' \) \
			-exec php -lf {} \;
	else
		echo 'Not running PHP syntax check.'
	fi
}

# Check php files with PHPCodeSniffer.
codesniff-phpcs() {
	if [[ $TRAVISCI_RUN == codesniff ]]; then
		$PHPCS_DIR/scripts/phpcs -n --standard=$WPCS_STANDARD \
			$(if [ -n "$PHPCS_IGNORE" ]; then echo --ignore=$PHPCS_IGNORE; fi) \
			$(find . -name '*.php')
	else
		echo 'Not running PHPCS.'
	fi
}

# Check JS files with jshint.
codesniff-jshint() {
	if [[ $TRAVISCI_RUN == codesniff ]]; then
		jshint .
	else
		echo 'Not running jshint.'
	fi
}

# Check PHP files for proper localization.
codesniff-l10n() {
	if [[ $TRAVISCI_RUN == codesniff ]]; then
		vendors/jdgrimes/wp-l10n-validator/bin/wp-l10n-validator
	else
		echo 'Not running wp-l10n-validator.'
	fi
}

# Run basic PHPUnit tests.
phpunit-basic() {
	if [[ $TRAVISCI_RUN == phpunit ]]; then
		phpunit \
		$(
			if [ -e .coveralls.yml ]; then
				echo --coverage-clover build/logs/clover.xml
			fi
		)
	else
		echo 'Not running PHPUnit.'
	fi
}

# Run uninstall PHPUnit tests.
phpunit-uninstall() {
	if [[ $TRAVISCI_RUN == phpunit ]]; then
		phpunit --group=uninstall
	else
		echo 'Not running PHPUnit.'
	fi
}

# Run Ajax PHPUnit tests.
phpunit-ajax() {
	if
		[[ $TRAVISCI_RUN == phpunit ]] \
		&& [[ $WP_MULTISITE == 0 || $WP_VERSION == latest ]];
	then
		phpunit --group=ajax
	else
		echo 'Not running Ajax tests.'
	fi
}

# Run basic tests for multisite in network mode.
phpunit-ms-network() {
	if [[ $TRAVISCI_RUN == phpunit ]] && [[ $WP_MULTISITE == 1 ]]; then
		WORDPOINTS_NETWORK_ACTIVE=1 phpunit
	else
		echo 'Not running network tests.'
	fi
}

# Run uninstall tests in multisite in network mode.
phpunit-ms-network-uninstall() {
	if [[ $TRAVISCI_RUN == phpunit ]] && [[ $WP_MULTISITE == 1 ]]; then
		WORDPOINTS_NETWORK_ACTIVE=1 phpunit --group=uninstall
	else
		echo 'Not running network tests.'
	fi
}

# Run Ajax tests in multisite in network mode.
phpunit-ms-network-ajax() {
	if
		[[ $TRAVISCI_RUN == phpunit ]] \
		&& [[ $WP_MULTISITE == 1 ]] && [[ $WP_VERSION == latest ]];
	then
		WORDPOINTS_NETWORK_ACTIVE=1 phpunit --group=ajax
	else
		echo 'Not running network Ajax tests.'
	fi
}

# EOF
