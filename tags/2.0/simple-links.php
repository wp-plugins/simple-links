<?php 
/*
Plugin Name: Simple Links
Plugin URI: http://matlipe.com/simple-links-docs/
Description: Replacement for Wordpress Links Manager with many added features.
Version: 2.0
Author: Mat Lipe
Author URI: http://matlipe.com/
*/


define( 'SIMPLE_LINKS_VERSION', 2.0 );

define( 'SIMPLE_LINKS_DIR', plugin_dir_path(__FILE__) );
define( 'SIMPLE_LINKS_URL', plugin_dir_url(__FILE__) );
define( 'SIMPLE_LINKS_ASSETS_URL', SIMPLE_LINKS_URL. 'assets/');
define( 'SIMPLE_LINKS_IMG_DIR', SIMPLE_LINKS_ASSETS_URL. 'img/' );
define( 'SIMPLE_LINKS_JS_DIR',  SIMPLE_LINKS_ASSETS_URL. 'js/' );
define( 'SIMPLE_LINKS_JS_PATH',  SIMPLE_LINKS_DIR. 'assets/js/' );
define( 'SIMPLE_LINKS_CSS_DIR', SIMPLE_LINKS_ASSETS_URL. 'css/' );

require( 'includes/functions.php' );

require('widgets/SL_links_main.php' );
require('widgets/SL_links_replica.php');


require('includes/SimpleLinksFactory.php');
require('includes/SimpleLinksTheLink.php');
require('includes/SL_post_type_tax.php');
require('includes/simple_links.php');


$simple_links = new simple_links();

//backward compatibility
$simple_links_func = $simple_links;

if( is_admin() ){
    require( 'includes/simple_links_admin.php' );
    $simple_links_admin_func = new simple_links_admin();
}



