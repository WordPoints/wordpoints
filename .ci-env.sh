#!/bin/bash

# Use the develop branch for WPCS for compatibility with PHPCS 2.0
export WPCS_GIT_TREE=develop

# Ignore the WordPress dev lib when codesniffing.
CODESNIFF_PATH+=('!' -path "./dev-lib/*")
export CODESNIFF_PATH

# EOF
