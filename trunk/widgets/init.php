<?php 

								/**
								 * Brings in the Widgets for Simple LInks
								 * @since 8/27/12
								 * @author Mat Lipe <mat@lipeimagination.info>
								 * 
								 */

require('simple.links.replica.php' );
require('simple.links.main.widget.php');

add_action( 'widgets_init', 'simple_links_main_widget' );
function simple_links_main_widget(){
	//Register the main widget
	register_widget( 'SL_links_main' );
}


//If the settigs has been set to replace Widgets
if( get_option('sl-replace-widgets', false ) ){
	add_action( 'widgets_init', function(){
		register_widget('SL_links_replica');
	});
}