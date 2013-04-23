<?php
/*
Plugin Name: Advanced Sidebar Menu
Plugin URI: http://lipeimagination.info/wordpress/advanced-sidebar-menu/
Description: Creates dynamic menu based on child/parent relationship.
Author: Mat Lipe
Version: 4.1.2
Author URI: http://lipeimagination.info
Since: 4.23.13
*/

#-- Bring in the functions
require( 'lib/advancedSidebarMenu.php' );
$asm = new advancedSidebarMenu();


#-- Bring in the Widgets
require( 'widgets/init.php' );

#-- Define Constants
define( 'ADVANCED_SIDEBAR_WIDGETS_DIR', plugin_dir_path(__FILE__) . 'widgets/' );
define( 'ADVANCED_SIDEBAR_VIEWS_DIR', plugin_dir_path(__FILE__) . 'views/' );
define( 'ADVANCED_SIDEBAR_LEGACY_DIR', plugin_dir_path(__FILE__) . 'legacy/' );
define( 'ADVANCED_SIDEBAR_DIR', plugin_dir_path(__FILE__) );


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




