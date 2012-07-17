<?php 


          /**
           * Creates a Widget of parent Child Pages
           * 
           * @author mat lipe
           * @since 7/16/12
           * @package Advanced Sidebar Menu
           *
           */



class advanced_sidebar_menu_page extends WP_Widget {

#-----------------------------------------------------------------------------------------------------------------------------------
	  // this creates the widget form for the dashboard
	function form( $instance ) {
			//	  		require( ADVANCED_SIDEBAR_DIR . 'advanced-sidebar-menu.js' );
			?>
            
            <p> Title <br>
             <input id="<?php echo $this->get_field_name('title'); ?>" 
            	name="<?php echo $this->get_field_name('title'); ?>" size="50" type="text" value="<?php echo $instance['title']; ?>"/></p>

            <p> Include Parent Page: <input id="<?php echo $this->get_field_name('include_parent'); ?>" 
            	name="<?php echo $this->get_field_name('include_parent'); ?>" type="checkbox" value="checked" 
            	<?php echo $instance['include_parent']; ?>/></p>
			
            			
			<p> Include Parent Even With No Children: <input id="<?php echo $this->get_field_name('include_childless_parent'); ?>"
			name="<?php echo $this->get_field_name('include_childless_parent'); ?>" type="checkbox" value="checked" 
					<?php echo $instance['include_childless_parent']; ?>/></p>
					
			<p> Use Built in Styling: <input id="<?php echo $this->get_field_name('css'); ?>"
			name="<?php echo $this->get_field_name('css'); ?>" type="checkbox" value="checked" 
					<?php echo $instance['css']; ?>/></p>
					
			<p> Pages to Exclude, Comma Separated: <input id="<?php echo $this->get_field_name('exclude'); ?>" 
            	name="<?php echo $this->get_field_name('exclude'); ?>" type="text" value="<?php echo $instance['exclude']; ?>"/></p>
            	
            <p> Always Display Child Pages: <input id="<?php echo $this->get_field_name('display_all'); ?>" 
            	name="<?php echo $this->get_field_name('display_all'); ?>" type="checkbox" value="checked" 
            	onclick="javascript:asm_reveal_element( 'levels-<?php echo $this->get_field_name('levels'); ?>' )"
            	<?php echo $instance['display_all']; ?>/></p>
            
            <span id="levels-<?php echo $this->get_field_name('levels'); ?>" style="<?php 
                  if( $instance['display_all'] == checked ){
                  	echo 'display:block';
                  } else {
                  	echo 'display:none';
                  } ?>"> 
            <p> Levels to Display: <select id="<?php echo $this->get_field_name('levels'); ?>" 
            name="<?php echo $this->get_field_name('levels'); ?>">
            <?php 
            	for( $i= 1; $i<6; $i++ ){
            		if( $i == $instance['levels'] ){
            			echo '<option value="'.$i.'" selected>'.$i.'</option>';
            		} else {
            			echo '<option value="'.$i.'">'.$i.'</option>';
            		}
            	} 
            	echo '</select></p></span>';
		}

#------------------------------------------------------------------------------------------------------------------------------
	// this allows more than one instance

	function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
			$instance['include_childless_parent'] = strip_tags($new_instance['include_childless_parent']);
			$instance['include_parent'] = strip_tags($new_instance['include_parent']);
			$instance['exclude'] = strip_tags($new_instance['exclude']);
			$instance['display_all'] = strip_tags($new_instance['display_all']);
			$instance['levels'] = strip_tags($new_instance['levels']);
			$instance['css'] = strip_tags($new_instance['css']);
			$instance['title'] = strip_tags($new_instance['title']);
			return $instance;
		}

#-------------------------------------------------------------------------------------------------------------------------

  	// This decides the name of the widget
	function advanced_sidebar_menu_page( ) {
				/* Widget settings. */
		$widget_ops = array( 'classname' => 'sidebar-menu', 'description' => 'Creates a menu of all the pages using the child/parent relationship' );
        $control_ops = array( 'width' => 290 );

		/* Create the widget. */
		$this->WP_Widget( 'advanced_sidebar_menu', 'Advanced Sidebar Pages Menu', $widget_ops, $control_ops);
		}


#---------------------------------------------------------------------------------------------------------------------------

    // adds the output to the widget area on the page
	function widget($args, $instance) {
		
		if( is_page() ){
			
	 		 global $wpdb, $post, $table_prefix, $asm;
	  		 
	  		 #-- Create a usable array of the excluded pages
	  		 $exclude = explode(',', $instance['exclude']);
		 
	    	 #-- if the post has parents
			if($post->ancestors){
	 		 	$top_parent = end( $post->ancestors );
			} else {
				#--------- If this is the parent ------------------------------------------------
				$top_parent = $post->ID;
			}

			/**
			 * Must be done this way to prevent doubling up of pages
			 */
			$child_pages = $wpdb->get_results( "SELECT ID FROM ".$table_prefix."posts WHERE post_parent = $top_parent AND post_type='page' AND post_status='publish' Order by menu_order" );
			
			//for depreciation
			$p = $top_parent;
			$result = $child_pages;
		
			#---- if there are no children do not display the parent unless it is check to do so
			if( ($child_pages) || (($instance['include_childless_parent'] == 'checked') && (!in_array($top_parent, $exclude)) )  ){
			
				if( $instance['css'] == 'checked' ){
					echo '<style type="text/css">';
						include( advanced_sidebar_menu_functions::file_hyercy('sidebar-menu.css' ) );
					echo '</style>';
				}
			
				//Start the menu
				echo '<div id="'.$args['widget_id'].'" class="advanced-sidebar-menu widget advanced-sidebar-page">
							<div class="widget-wrap">';
			   					 $asm->set_widget_vars( $instance, $top_parent, $exclude );
								#-- Bring in the view
    							require( $asm->file_hyercy( 'page_list.php' ) );
				echo '</div></div><!-- end #'.$args['widget_id'].' -->';
			}
		}
	} #== /widget()
	
} #== /Clas