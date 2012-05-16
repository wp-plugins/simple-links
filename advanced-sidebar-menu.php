<?php
/*
Plugin Name: Advanced Sidebar Menu
Plugin URI: http://lipeimagination.info
Description: Creates dynamic menu based on child/parent relationship.
Author: Mat Lipe
Version: 3.0.2
Author URI: http://lipeimagination.info
Since: 5/19/12
Email: mat@lipeimagination.info

*/



#-- Bring in the functions
require( 'functions.php' );


#-- Bring in the Widgets
require( 'widgets/init.php' );


#-- Define Constants
define( 'ADVANCED_SIDEBAR_WIDGETS_DIR', plugin_dir_path(__FILE__) . 'widgets/' );
define( 'ADVANCED_SIDEBAR_VIEWS_DIR', plugin_dir_path(__FILE__) . 'views/' );
define( 'ADVANCED_SIDEBAR_DIR', plugin_dir_path(__FILE__) );






