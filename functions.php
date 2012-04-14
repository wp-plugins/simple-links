<?php 


         /**
          * These Functions are Specific to the Advanced Sidebar Menu
          * @author Mat Lipe
          * @since 4/13/12
          */
         

/**
 * Allows for Overwritting files in the child theme
 * @since 4/13/12
 */
         
function advanced_sidebar_menu_file_hyercy( $file ){
	
	if ( $theme_file = locate_template(array('advanced-sidebar-menu/'.$file)) ) {
		$file = $theme_file;
	} else {
		$file = ADVANCED_SIDEBAR_VIEWS_DIR . $file;
	}
	return $file;
	
}