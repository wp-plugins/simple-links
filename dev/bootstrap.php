<?php

$GLOBALS['wp_tests_options']['active_plugins'][] = 'simple-links/simple-links.php';
$GLOBALS['wp_tests_options']['active_plugins'][] = 'simple-links-search/simple-links-search.php';
$GLOBALS['wp_tests_options']['active_plugins'][] = 'simple-links-display-by-category/simple-links-display-by-category.php';
$GLOBALS['wp_tests_options']['active_plugins'][] = 'simple-links-csv-import/simple-links-csv-import.php
';

$GLOBALS['wp_tests_options']['permalink_structure'] = '%postname%/';

require( 'wp-tests-config.php' );

global $wp_version; // wp's test suite doesn't globalize this, but we depend on it for loading core

require 'E:/SVN/wordpress-tests/includes/bootstrap-no-install.php';
