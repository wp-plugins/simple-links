<?php
/*
Plugin Name: Advanced Sidebar Menu
Plugin URI: http://lipeimagination.info/wordpress/advanced-sidebar-menu/
Description: Creates dynamic menu based on child/parent relationship.
Author: Mat Lipe
Version: 4.3.2
Author URI: http://lipeimagination.info
Since: 6.13.13
*/


#-- Define Constants
define( 'ADVANCED_SIDEBAR_DIR', plugin_dir_path(__FILE__) );
define( 'ADVANCED_SIDEBAR_WIDGETS_DIR', ADVANCED_SIDEBAR_DIR . 'widgets/' );
define( 'ADVANCED_SIDEBAR_VIEWS_DIR', ADVANCED_SIDEBAR_DIR . 'views/' );
define( 'ADVANCED_SIDEBAR_LEGACY_DIR', ADVANCED_SIDEBAR_DIR . 'legacy/' );


#-- Bring in the Widgets
require( ADVANCED_SIDEBAR_WIDGETS_DIR.'init.php' );
#-- Bring in the functions
require( ADVANCED_SIDEBAR_DIR.'lib/advancedSidebarMenu.php' );
$asm = new advancedSidebarMenu();



#-- Bring in the JQuery
add_action( 'admin_print_scripts', 'advanced_sidebar_menu_script');
function advanced_sidebar_menu_script(){
         wp_enqueue_script(
                apply_filters( 'asm_script', 'advanced-sidebar-menu-script' ),  //Allows developers to overright the name of the script
                plugins_url( 'advanced-sidebar-menu.js', __FILE__ ),
                array('jquery'),  //The scripts this depends on 
                '1.1.0'   //The Version of your script
                             
        );

};




