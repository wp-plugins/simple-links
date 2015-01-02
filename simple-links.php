<?php
/*
Plugin Name: Simple Links
Plugin URI: http://matlipe.com/simple-links-docs/
Description: Replacement for Wordpress Links Manager with many added features.
Version: 3.0.0
Author: Mat Lipe
Author URI: http://matlipe.com/
Contributors: Mat Lipe
*/


define( 'SIMPLE_LINKS_VERSION', '3.0.0' );

define( 'SIMPLE_LINKS_DIR', plugin_dir_path( __FILE__ ) );
define( 'SIMPLE_LINKS_URL', plugin_dir_url( __FILE__ ) );
define( 'SIMPLE_LINKS_ASSETS_URL', SIMPLE_LINKS_URL . 'assets/' );
define( 'SIMPLE_LINKS_IMG_DIR', SIMPLE_LINKS_ASSETS_URL . 'img/' );
define( 'SIMPLE_LINKS_JS_DIR', SIMPLE_LINKS_ASSETS_URL . 'js/' );
define( 'SIMPLE_LINKS_JS_PATH', SIMPLE_LINKS_DIR . 'assets/js/' );
define( 'SIMPLE_LINKS_CSS_DIR', SIMPLE_LINKS_ASSETS_URL . 'css/' );

require( 'includes/template-tags.php' );
require( 'includes/SimpleLinksFactory.php' );
require( 'includes/SimpleLinksTheLink.php' );
require( 'includes/simple_links.php' );
require( 'widgets/SL_links_main.php' );

function simple_links_autoload( $class ){
	if( file_exists( SIMPLE_LINKS_DIR . 'classes/' . $class . '.php' ) ){
		require( SIMPLE_LINKS_DIR . 'classes/' . $class . '.php' );
	}
}

spl_autoload_register( 'simple_links_autoload' );

$simple_link = new Simple_Link;
add_action( 'init', array( $simple_link, 'register_sl_post_type' ) );
add_action( 'plugins_loaded', array( 'Simple_Links_Categories', 'get_instance' ) );

$simple_links = new simple_links();

//backward compatibility
$simple_links_func = $simple_links;

if( is_admin() ){
	require( 'includes/simple_links_admin.php' );

	add_action( 'plugins_loaded', array( 'Simple_Links_Settings', 'get_instance' ) );

	$simple_links_admin_func = new simple_links_admin();
}


#-- Let know about new Pro Version
add_action( 'simple_links_widget_form', 'simple_links_pro_notice' );
add_action( 'simple_links_shortcode_form', 'simple_links_pro_notice' );
function simple_links_pro_notice(){
	if( defined( 'SIMPLE_LINKS_DISPLAY_BY_CATEGORY_VERSION' ) || defined( 'SIMPLE_LINKS_CSV_IMPORT_VERSION' ) ){
		return;
	}

	require( SIMPLE_LINKS_DIR . 'admin-views/pro-notice.php' );
}



