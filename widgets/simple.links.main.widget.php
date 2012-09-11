<?php 

                   /**
                    * Creates the main widget for the simple links plugin
                    * @author mat lipe
                    * @since 8/27/12
                    * @uses registerd by init
                    * @uses the output can be filtered by using the 'simple_links_widget_output' filter
                    *       *   apply_filters( 'simple_links_widget_output', $output, $args );
                    *       the $args can be filtered by using the 'simple_links_widget_args' filter
                    *       *   apply_filters( 'simple_links_widget_args', $args );
                    *       the Widget Settings Can be filtered using the 'simple_links_widget_settings' filter
                    *       *   apply_filters( 'simple_links_widget_settings', $instance );
                    *   ** All Filters can be specified for a particular widget by ID
                    *      * e.g.   add_filter( 'simple_links_widget_settings_simple-links-3')
                    * 
                    *
                    */



class SL_links_main extends WP_Widget {

	protected $defaults = array(            
			    'post_type'              =>  'simple_link',
				'orderby'                =>  'menu_order',
				'order'                  =>  'DESC',
				'numberposts'            =>  '99',
				'simple_link_category'   => '0'
			);
			
	
	
	/**
	 * Setup the Widget
	 * @since 8/27/12
	 */
	function __construct() {
		$widget_ops = array(
				'classname'   => 'sl-links-main',
				'description' => 'Displays a list of your Simple Links with options.'
		);
		
		
		$control_ops = array(
						'id_base' => 'simple-links',
						'width'   => 305,
						'height'  => 350,
			
		);

		$this->WP_Widget( 'simple-links', 'Simple Links', $widget_ops, $control_ops );

	}
	
	
	/**
	 * The output of the widget to the site
	 * @since 8/27/12
	 * @see WP_Widget::widget()
	 * @param $args the widget necessaties like $before_widget and $title
	 * @param $instance all the settings for this particular widget
	 * @uses See Class Docs for filtering the output,settings,and args
	 */
	function widget( $args, $instance ) {
		$unfiltered_instance = $instance;
		$unfiltered_args = $args;
		$output = '';
		global $simple_links_func;//
		
		//print_r( $args );
		
		
	//-- Setup the Arguments and filters ----------------------------------------------------
		
		//Filter for Changing the widget args
		$args = apply_filters('simple_links_widget_args', $args);
		$args = apply_filters('simple_links_widget_args_' . $widget_id, $args);
		
		//Create variable from the built in widget args
		extract( $args );
		
		//Call this filter to change the Widgets Settings Pre Compile
		$instance = apply_filters('simple_links_widget_settings_' . $widget_id, $instance);
		$instance = apply_filters('simple_links_widget_settings', $instance);
		
		
		//Go through all the possible categories and add the ones that are set
		foreach( $simple_links_func->get_categories() as $cat ){
			if( isset( $instance[$cat]) && ($instance[$cat]) ){
				if( isset( $instance['simple_link_category'] ) ){
					$instance['simple_link_category'] .= ',' . $cat;
				} else {
					$instance['simple_link_category'] = $cat;
				}
			}
		}
		

	//------------ Retrieve the Links	
		
		//Parse the query vars along with the defaults
        $query_args = wp_parse_args($instance, $this->defaults);
		
		//Retrieve the links
		$links = get_posts( $query_args );
		
		//Escape hatch
		if( !$links ){
			return;
		}
		
		//Add the instance stuff
		$links['title'] = $instance['title'];
		$links['id']    = $widget_id;
		
		
	//--------------- Starts the Output --------------------------------------	
		
		$output .= $before_widget;
		
		
		//Add the title
		if( !empty( $instance['title'] ) ){
			$output .= $before_title. $instance['title'].$after_title;
		}
		

		$output .= '<ul class="simple-links-list ' . $widget_id . '">';
		
		//print_r( $links );
		
		
		//Go through each link
		foreach( $links as $link ){
			$meta = get_post_meta($link->ID);
			
			//Escape Hatch
			if( !is_object( $link ) ){
				continue;
			}
		
			//Adds the meta to the main object for people using filters
			$link->meta = $meta;

			$output .= '<li class="simple-links-shortcode-item">';
		
			//Add the image
			if( $instance['show_image']){
		
				$image = get_the_post_thumbnail($link->ID, $instance['image_size']);
				//more for the filterable object
				$link->image = $image;
				if( $image != ''){
					$image .= '<br>';  //make the ones with returned image have the links below
				}
			}
		
		
			$output .= sprintf('<a href="%s" target="%s" title="%s">%s%s</a>',
					$meta['web_address'][0],
					$meta['target'][0],
					$meta['description'][0],
					$image,
					$link->post_title
			);
		
			//Add the description
			if( ($instance['description']) && ($meta['description'][0] != '') ){
				$output .= ' ' . $instance['separator'] . ' ' . $meta['description'][0];
			}
		
		
		
			//Add the addtional fields
			$post_additional_fields = json_decode( get_post_meta( $link->ID, 'link_additional_value', true), true );
			
			if( is_array( $post_additional_fields ) ){

				foreach( $post_additional_fields as $field => $value ){
					if( !empty($instance[$field]) ){
						$output .= ' ' . $instance['separator'] . ' ' . $value;
					}
				}
			}
		
			//Close this list item
			$output .= '</li>';
		
		}

		$output .= '</ul><!-- End .simple-links-list -->';

		//return the vars to normal
		$instance = $unfiltered_instance;
		$args = $unfiltered_args;
		
		//Close the Widget
		$output .= $after_widget;
		
		//The output can be filtered here
		$output = apply_filters( 'simple_links_widget_output_' . $widget_id, $output, $links, $instance, $args );
		echo apply_filters( 'simple_links_widget_output', $output, $links, $instance, $args );
		

	}
	
	
	
	
	/**
	 * Updates the instance of each widget separately
	 * @uses to make sure the data is valid
	 * @see WP_Widget::update()
	 * @since 8/27/12
	 */
	function update( $new_instance, $old_instance ) {
		$new_instance['title'] = strip_tags( $new_instance['title'] );
		return $new_instance;
	
	}
	
	
	
	/**
	 * Outputs the Widget form on the Widgets Page
	 * @since 8/27/12
	 * @see WP_Widget::form()
	 */
	function form( $instance ) {
		global $simple_links_func;
		
		?>
		
		<em>Be sure the see the Help Section in the Top Right Corner of the Screen for Questions!</em><br><br>
		
		<strong>Links Title:</strong>
		<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
		
		<br><br>
		
		<strong>Order Links By</strong>
		<select id="<?php echo $this->get_field_id( 'orderby' ); ?>" name="<?php echo $this->get_field_name( 'orderby' ); ?>">
		    <option value="menu_order" <?php selected($instance['orderby'],'menu_order'); ?>>Link Order</option>
		    <option value="title" <?php selected($instance['orderby'],'title'); ?>>Name</option>
		    <option value="random" <?php selected($instance['orderby'],'random'); ?>>Random</option>
		</select>
		
		<br><br>
		<strong>Order:</strong>
		<select id="<?php echo $this->get_field_id( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>">
			<option value="ASC" <?php selected($instance['order'],'ASC'); ?>>Acending</option>
		    <option value="DESC" <?php selected($instance['order'],'DESC'); ?>>Descending</option>
		    
		</select>
		
		<br><br>
       <strong>Categories (optional):</strong><br>
        	<?php 
       		 foreach( $simple_links_func->get_categories() as $cat ){
        		printf('&nbsp; &nbsp; <input class="cat" type="checkbox" value="1" name="%s" %s/> %s <br>', $this->get_field_name($cat), checked($instance[$cat], true, false), $cat );
	        	}
	        ?>
       
       <br><br>
       <strong>Number of Links:</strong>
        	<select id="<?php echo $this->get_field_id( 'numberposts' ); ?>" name="<?php echo $this->get_field_name( 'numberposts' ); ?>">
        		<option value="">All</option>
        		<?php 
          		for( $i = 1; $i<50; $i++){
          			printf('<option value="%s" %s>%s</option>', $i, selected($instance['numberposts'], $i ), $i );
          		}
        		?>
        	</select>

        <br><br>
       <strong>Show Description</strong> 
       		<input type="checkbox" id="<?php echo $this->get_field_id( 'description' ); ?>" name="<?php echo $this->get_field_name( 'description' ); ?>" 
       				<?php checked($instance['description']); ?> value="1"/>
        
		
		<br><br>
       <strong>Show Image</strong> 
       		<input type="checkbox" id="<?php echo $this->get_field_id( 'show_image' ); ?>" name="<?php echo $this->get_field_name( 'show_image' ); ?>" 
       				<?php checked($instance['show_image']); ?> value="1"/>
        
        
        <br><br>
       <strong>Image Size :</strong>
        	<select id="<?php echo $this->get_field_id( 'image_size' ); ?>" name="<?php echo $this->get_field_name( 'image_size' ); ?>">
        		<?php 
        		foreach( $simple_links_func->image_sizes() as $size ){
          			printf('<option value="%s" %s>%s</option>', $size, selected($instance['image_size'], $size ), $size );
          		}
        		?>
        	</select>
		
		<br><br>
       <strong>Include Additional Fields:</strong><br>
        	<?php 
			if( empty( $simple_links_func->additional_fields ) ){
            	echo '<em>There have been no additional fields added. </em>';
            } else {
            foreach( $simple_links_func->additional_fields as $field ){
        		printf('&nbsp; &nbsp; <input class="cat" type="checkbox" value="1" name="%s" %s/> %s <br>', $this->get_field_name($field), checked($instance[$field], true, false), $field);
            	            	  }
            }
	        ?>
	        
	    <br><br>    
		<strong>Field Separator:</strong><br>
		<em>HTML is allowed: - e.g. '&lt;br&gt;'</em><br>
		<input type="text" id="<?php echo $this->get_field_id( 'separator' ); ?>" name="<?php echo $this->get_field_name( 'separator' ); ?>" value="<?php echo esc_attr( $instance['separator'] ); ?>" class="widefat" />
		
		
		
		<?php 
	}
	
	
	
	
	
	
}