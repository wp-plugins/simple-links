<?php 


          /**
           * Creates a Widget of parent Child Categories
           * 
           * @author mat lipe
           * @since 5/16/12
           * @package Advanced Sidebar Menu
           *
           */




class advanced_sidebar_menu_category extends WP_Widget {

#-----------------------------------------------------------------------------------------------------------------------------------
	  // this creates the widget form for the dashboard
	function form( $instance ) {
				  		 require( ADVANCED_SIDEBAR_DIR . 'advanced-sidebar-menu.js' );
			?>
			
			
			
            <p> Include Parent Category <input id="<?php echo $this->get_field_name('include_parent'); ?>" 
            	name="<?php echo $this->get_field_name('include_parent'); ?>" type="checkbox" value="checked" 
            	<?php echo $instance['include_parent']; ?>/></p>
			
            			
			<p> Include Parent Even With No Children<input id="<?php echo $this->get_field_name('include_childless_parent'); ?>"
			name="<?php echo $this->get_field_name('include_childless_parent'); ?>" type="checkbox" value="checked" 
					<?php echo $instance['include_childless_parent']; ?>/></p>
					
			<p> Use Built in Styling <input id="<?php echo $this->get_field_name('css'); ?>"
			name="<?php echo $this->get_field_name('css'); ?>" type="checkbox" value="checked" 
					<?php echo $instance['css']; ?>/></p>
					
			<p> Categories to Exclude, Comma Separated:<input id="<?php echo $this->get_field_name('exclude'); ?>" 
            	name="<?php echo $this->get_field_name('exclude'); ?>" type="text" value="<?php echo $instance['exclude']; ?>"/></p>
            	
            <p> Always Display Child Categories <input id="<?php echo $this->get_field_name('display_all'); ?>" 
            	name="<?php echo $this->get_field_name('display_all'); ?>" type="checkbox" value="checked" 
            	onclick="javascript:reveal_element( 'levels-<?php echo $this->get_field_name('levels'); ?>' )"
            	<?php echo $instance['display_all']; ?>/></p>
            
            <span id="levels-<?php echo $this->get_field_name('levels'); ?>" style="<?php 
                  if( $instance['display_all'] == checked ){
                  	echo 'display:block';
                  } else {
                  	echo 'display:none';
                  } ?>"> 
            <p> Levels to Display <select id="<?php echo $this->get_field_name('levels'); ?>" 
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
			return $instance;
		}

#-------------------------------------------------------------------------------------------------------------------------

  	// This decides the name of the widget
	function advanced_sidebar_menu_category( ) {
				/* Widget settings. */
		$widget_ops = array( 'classname' => 'sidebar-menu-category', 'description' => 'Creates a menu of all the Categories using the child/parent relationship' );


		/* Create the widget. */
		$this->WP_Widget( 'advanced_sidebar_menu_category', 'Advanced Sidebar Categories Menu', $widget_ops );
		}


#---------------------------------------------------------------------------------------------------------------------------

    // adds the output to the widget area on the page
	function widget($args, $instance) {
		if( is_category() ){	
			
			
		#-- Create a usable array of the excluded pages
		$exclude = explode(',', $instance['exclude']);
			
		$cat_id = get_query_var('cat' );
        $cat_ancestors = array ();
        $cat_ancestors[] = $cat_id ;
       
        do {
             $cat_id = get_category($cat_id );
             $cat_id = $cat_id->parent;
             $cat_ancestors[] = $cat_id ; }
        while ($cat_id );
       
       
        $cat_ancestors = array_reverse( $cat_ancestors );
        $top_cat = $cat_ancestors [1];
       
         //Check for children
        $all = get_categories( array( 'child_of' => $top_cat ) );

        	
            //If there are any child categories or the include childless parent is checked
        	if( !empty($all ) || ($instance['include_childless_parent'] == 'checked' && !in_array($top_cat, $exclude))  ){
        		
        		//Start the menu
        		if( $instance['css'] == 'checked' ){
        			echo '<style type="text/css">';
        					include( advanced_sidebar_menu_file_hyercy( 'sidebar-menu.css' ) );
        		    echo '</style>';
        		}
        		
        		#!! Bring in the output from either the child theme or this folder
        		require( advanced_sidebar_menu_file_hyercy( 'category_list.php' ) );
        		
        		
            
      }  //End if any children or include childless parent
			
	}
     	     
	} #== /widget()
	
} #== /Clas