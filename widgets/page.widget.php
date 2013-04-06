<?php 


          /**
           * Creates a Widget of parent Child Pages
           * 
           * @author mat lipe
           * @since 4.5.13
           * @package Advanced Sidebar Menu
           *
           */
class advanced_sidebar_menu_page extends WP_Widget {

    /**
     * Build the widget like a Mo Fo
     * 
     * @since 4.5.13
     * 
     */
    function __construct() {
                /* Widget settings. */
        $widget_ops = array( 'classname' => 'advanced-sidebar-menu', 'description' => 'Creates a menu of all the pages using the child/parent relationship' );
        $control_ops = array( 'width' => 290 );

        /* Create the widget. */
        $this->WP_Widget( 'advanced_sidebar_menu', 'Advanced Sidebar Pages Menu', $widget_ops, $control_ops);
    }
    
    
    /**
     * Output a simple widget Form
     * Not of ton of options here but who need them
     * Most of the magic happens automatically
     * 
     * @since 4.5.13
     */
	function form( $instance ) {
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
            <p> Legacy Mode: (displays all 3rd level and down pages when on a second level page) <input id="<?php echo $this->get_field_name('legacy_mode'); ?>"
            name="<?php echo $this->get_field_name('legacy_mode'); ?>" type="checkbox" value="checked" 
                    <?php echo $instance['legacy_mode']; ?>/>
            </p>    
            	
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


    /**
     * Handles the saving of the widget
     * 
     * @since 4.5.13
     */
	function update( $new_instance, $old_instance ) {
			$new_instance['exclude'] = strip_tags($new_instance['exclude']);
			return $new_instance;
    }


#---------------------------------------------------------------------------------------------------------------------------

    /**
     * Outputs the page list
     * @see WP_Widget::widget()
     * 
     * @uses for custom post types send the type to the filter titled 'advanced_sidebar_menu_post_type'
     * @uses change the top parent manually with the filter 'advanced_sidebar_menu_top_parent'
     * @uses change the order of the 2nd level pages with 'advanced_sidebar_menu_order_by' filter
     * 
     * @since 4.5.13
     */
	function widget($args, $instance) {
	    global $wpdb, $post, $table_prefix;
        $asm = new advancedSidebarMenu;

        $asm->instance = $instance;
	    extract($args);
	    
	    //Filter this one with a 'single' for a custom post type will default to working for pages only
	    $post_type = apply_filters('advanced_sidebar_menu_post_type', 'page' );
	    $asm->post_type = $post_type;
	    
	    if( $post_type != 'page' ){
             add_filter('page_css_class', array( $asm, 'custom_post_type_css'), 2, 4 );
             
        }
        
        
			
	    #-- Create a usable array of the excluded pages
	    $exclude = explode(',', $instance['exclude']);
        $asm->exclude = $exclude;

		 
	    #-- if the post has parents
		if($post->ancestors){
	 		 	$top_parent = end( $post->ancestors );
		} else {
				#--------- If this is the parent ------------------------------------------------
				$top_parent = $post->ID;
		}
			
			
		//Filter for specifying the top parent
		$top_parent = apply_filters('advanced_sidebar_menu_top_parent', $top_parent, $post );
        $asm->top_id = $top_parent;
        
        
        //Filter for specifiying the order by
        $order_by = apply_filters('advanced_sidebar_menu_order_by', 'menu_order', $post );
		$asm->order_by = $order_by;	
            
            
		/**
	     * Must be done this way to prevent doubling up of pages
		 */
		 $child_pages = $wpdb->get_results( "SELECT ID FROM ". $wpdb->posts ." WHERE post_parent = $top_parent AND post_status='publish' AND post_type='$post_type' Order by $order_by" );
			
		//for depreciation
		$p = $top_parent;
		$result = $child_pages;

		#---- if there are no children do not display the parent unless it is check to do so
		if( ($child_pages) || (($instance['include_childless_parent'] == 'checked') && (!in_array($top_parent, $exclude)) )  ){
			
			    if( $instance['css'] == 'checked' ){
					echo '<style type="text/css">';
						include( $asm->file_hyercy('sidebar-menu.css' ) );
					echo '</style>';
				}
	
				
				//Start the menu
				echo $before_widget;
			   					 
								#-- Bring in the view
    							require( $asm->file_hyercy( 'page_list.php' ) );
				echo $after_widget;
		}

	} #== /widget()
	
} #== /Clas