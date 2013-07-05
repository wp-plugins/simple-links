<?php
                 /**
                  * Methods for the Simple Links Plugin
                  * @author Mat Lipe <mat@lipeimagination.info>
                  * @since 7.5.13
                  * @uses These methods are used in both the admin output of the site
                  * @see simple_links_admin() for the only admin methods
                  * @see mat_post_type_tax() for the post type and tax registrations
                  * @uses $simple_links_func
                  */

if( !class_exists( 'simple_links' ) ){
class simple_links extends SL_post_type_tax{
	
	public $additional_fields = array();

    //The fields that will be auto generated
	protected $simple_link_meta_fields = array( 'web_address','description','target','additional_fields' );
	protected $meta_box_descriptions = array();
											
    /**
     * Since 10.11.12
     */
	function __construct(){
	    $this->meta_box_descriptions = array( 'web_address' 	 => __('Example','simple-links').': <code>http://wordpress.org/</code> '.__('DO NOT forget the','simple-links').' <code>http:// or https://</code>',
											  'description'	     => __('This will be shown when someone hovers over the link, or optionally below the link','simple-links').'.',
											  'target'           => __('Choose the target frame for your link','simple-links').'.',
											  'additional_fields'=> __('Values entered in these fields will be available for shortcodes and Widgets','simple-links').' '
										);
											
	    //Add the translate ability
	    add_action('plugins_loaded', array( $this,'translate') );
	    
	    
	    
		parent::__construct();
		
		//Set the array for additional fields
		$this->additional_fields = json_decode(get_option('link_additional_fields'), true );
		
		
		//Add the custom post type
		add_action('init', array( $this, 'post_type' ) );
		
		//Add the jquery
	    /**
	     * Not used at the moment
	     */
		//add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts') );
	
		
		//Add the Link Categories
		add_action( 'init', array( $this, 'link_categories' ) );
		
		
		//Setup the form output for the new button
		add_filter('query_vars', array( $this, 'outside_page_query_var') );
		add_action('template_redirect', array( $this, 'load_outside_page') );
		
		//Bring in the shortcode
		add_shortcode('simple-links', array( $this, 'shortcode' ) );
	
	}

    
    /**
     * Generates an html link from a links ID
     * 
     * @since 5.31.13
     * 
     * @param int  $linksId - the links post->ID
     */
    public function linkFactory($linkId){
       $link = get_post( $linkId );
       $meta = get_post_meta( $linkId );
       
       $link_output = sprintf('<a href="%s" target="%s" title="%s" %s>%s</a>',
                    $meta['web_address'][0],
                    $meta['target'][0],
                    strip_tags($meta['description'][0]),
                    empty( $meta['link_target_nofollow'][0] ) ? '': 'rel="nofollow"',
                    $link->post_title
        );
            
        return apply_filters('simple_links_factory_output', $link_output, $linkId );
            
    }
    
    


	
	
	/**
	 * Add the translate ability for I18n standards
	 * @since 10.11.12
	 * @uses called on __construct()
	 */
	function translate(){
	    load_plugin_textdomain('simple-links', false, 'simple-links/languages');
	}
	
	
	/**
	 * Creates the shortcode output
	 * @return the created list based on attributes
	 * @uses [simple-links $atts]
	 * @param string $atts the attributes specified in shortcode
	 * @since 7.5.13
	 * @param $atts = 'title'              => false,
                      'category'           => false,
                       'orderby'           => 'menu_order',
                       'count'             => '-1',
                       'show_image'        => false,
                       'show_image_only'   => false,
                       'image_size'        => 'thumbnail',
                       'order'             => 'ASC',
                       'fields'            => false,
                       'description'       => false,
                       'separator'         =>  '-',
                       'id'                =>  false,
                       'remove_line_break' =>  false

     * 
     * @filters  
     *       the shortcode atts
     *      * add_filter( 'simple_links_shortcode_atts', $atts );
     *       the shortcode output
     *      * add_filter( 'simple_links_shortcode_output', $output, $links, $atts )
     *       the links object directly
     *      *  apply_filters('simple_links_shortcode_links_object', $links, $atts);
     *       the links meta data per link
     *      * apply_filters('simple_links_shortcode_link_meta', $meta, $link, $atts );
     * 
     * 
	 * @uses the function filtering this output can accept 3 args.   <br>
	 * 				$output = The Output Generated by the Function
	 * 				$links  = The complete links to direct munipulation
	 * 				$atts   = The shortcode Attributes sent to this
	 * @uses All filters may be used by id by calling them with the id appened like so  'simple_links_shortcode_output_%id%' there must be an 'id' specified in the shortcode for this to work 
	 * @uses Using the filters without the id will filter all the shortcodes
	 * 
	 */
	function shortcode( $atts ){
	    
	    global $simple_links_func;
		$output = $image = '';
		$defaults = array(  'title'         => false,
				  	   'category'            => false,
		               'orderby'            => 'menu_order',
		               'count'    	        => '-1',
		               'show_image'         => false,
		               'show_image_only'    => false,
		               'image_size'        => 'thumbnail',
				       'order'             => 'ASC',
				       'fields'            => false,
		               'description'       => false,
					   'separator'         =>  '-',
				       'id'                =>  false,
				       'remove_line_break' =>  false
		 );
		//for filtering this function
		$unfilterd_atts = $atts;
		
		//Call this filter to change the atts pre compile
		$atts = apply_filters('simple_links_shortcode_atts', $atts);
		if( isset( $atts['id'] ) ){
		    $atts = apply_filters('simple_links_shortcode_atts_' . $atts['id'], $atts);
		}
		
		
		//Create the proper atts
		$atts = shortcode_atts( $defaults, $atts );
		
		
		//Change the Random att to rand for get posts
		if( $atts['orderby'] == 'random' ){
		    $atts['orderby'] = 'rand';
		}
		
		
		
		//Setup the fields
		if( $atts['fields'] != false ){
			$atts['fields'] = explode(',', $atts['fields'] );
		}

		
		//Get us started
		$args = array(
				   'post_type'              =>  'simple_link',
				   'orderby'                =>  $atts['orderby'],
		           'order'                  =>  $atts['order'],
				   'numberposts'            =>  $atts['count'],
				  // 'simple_link_category'   =>  $atts['category'], //just plain silly
				   'posts_per_page'         =>  $atts['count'],  //Fixes the themes desire to override these
			       'posts_per_archive_page' =>  $atts['count']   //Fixes the themes desire to override these
				);
		
		//Add the categories to the query
		if( $atts['category'] ){
		    $att_cats = explode(',', $atts['category']);
		    //Go through all the possible categories and add the ones that are set
		    foreach( $simple_links_func->get_categories() as $cat ){
		        if( in_array($cat, $att_cats) ){
		            $cat = get_term_by('name', $cat, 'simple_link_category');
		            $all_cats[] = $cat->term_id;
		        }
		    }
		
		    //If there are category make them into a query
		    if( isset( $all_cats ) ){
		        $args['tax_query'][] = array(
		                'taxonomy' => 'simple_link_category',
		                'fields'   => 'id',
		                'terms'    =>  $all_cats
		        );
		    }
		}
		
		
		
		
		//For Backwards Compatibility
		if( $atts['orderby'] == 'name' ){
				$args['orderby'] = 'title';
		}

		//print_r( $args, true );

		//Retrieve the links
		$links = get_posts( $args );

        //Filter on the links object directly
        $links = apply_filters('simple_links_shortcode_links_object', $links, $atts);
        $links = apply_filters('simple_links_shortcode_links_object_' . $atts['id'], $links, $atts );
        


		if( !$links ){ 
			return;
		}
		
		//Print the title if specified
		if( $atts['title'] != false ){
			$output .= sprintf('<h4 class="simple-links-title">%s</h4>', $atts['title'] );
			
		}
		
		//Start the UL
		if( $atts['id'] ){
		    $output .= '<ul class="simple-links-list" id="' . $atts['id'] . '">';
		} else {
		    $output .= '<ul class="simple-links-list">';
		}
		
		$links['title'] = $atts['title'];
		
		
		    //Go through each link
			foreach( $links as $link ){
			    //Escape Hatch
                if( !is_object( $link ) ){
                    continue;
                }
                $meta = apply_filters('simple_links_shortcode_link_meta_' . $atts['id'], get_post_meta($link->ID, false), $link, $atts );
               $meta = apply_filters('simple_links_shortcode_link_meta', $meta, $link, $atts );

				//Adds the meta to the main object for people using filters
				$link->meta = $meta;
				
				$output .= '<li class="simple-links-shortcode-item">';

					//Add the image
					if( $atts['show_image'] == 'true' ){
					    
                        //Remove the post Title if showing image only
                        if( $atts['show_image_only'] ){
                            $link->post_title = '';
                        }
                        
						$image = get_the_post_thumbnail($link->ID, $atts['image_size']);
						//more for the filterable object
						$link->image = $image;
						if( $image != '' && !$atts['remove_line_break']){
							$image .= '<br>';  //make the ones with returned image have the links below
						}
					}
				
				     //TODO Move this to a linkFactory type method
			 		$link_output = sprintf('<a href="%s" target="%s" title="%s" %s>%s%s</a>', 
			  						$meta['web_address'][0],
			  						$meta['target'][0],
			  						$meta['description'][0],
			  						empty( $meta['link_target_nofollow'][0] ) ? '': 'rel="nofollow"', 
			 				        $image,
			  						$link->post_title
			  				 ); 
                     $link_output = apply_filters('simple_links_shortcode_link_output', $link_output, $meta, $link, $image, $atts );
            $link_output = apply_filters('simple_links_shortcode_link_output_' . $atts['id'], $link_output, $meta, $link, $image, $atts );
 
            $output .= $link_output;
                     
                             
			 	
			 		//Add the description
			 		if( ($atts['description'] == 'true') && ($meta['description'][0] != '') ){
			 			$output .= ' ' . $atts['separator'] . ' ' . $meta['description'][0];
			 		}
			 		
			 	
			 	
			 		//Add the addtional fields
			 		if( $atts['fields'] != false ){
			 			$post_additional_fields = json_decode( get_post_meta( $link->ID, 'link_additional_value', true), true );
			 			foreach( $atts['fields'] as $field ){
			 				if( isset( $post_additional_fields[$field] ) && $post_additional_fields[$field] != '' ){
			 					$output .= ' ' . $atts['separator'] . ' ' . $post_additional_fields[$field];
			 				}
			 			}
			 		}
			
			 	
			 	//Close this list item
			 	$output .= '</li>';
			 	
				}
		$output .= '</ul><!-- End .simple-links-list -->';
		
	    //print_r( $links );
		
		$atts = $unfilterd_atts;
		
		//The output can be filtered here
		if( isset( $atts['id'] ) ){
		    $output = apply_filters( 'simple_links_shortcode_output_' . $atts['id'], $output, $links, $atts );
		}
		return apply_filters( 'simple_links_shortcode_output', $output, $links, $atts );
		
	}
	
	
	
	
	
	/**
	 * Retrieves all link categories 
	 * @since 8/19/12
	 * @return object
	 */
	function get_categories(){
		
		$args = array(
				  'hide_empty' => false,
				  'fields'     => 'names'
				);
		
		return get_terms('simple_link_category', $args );
	}
	
	
	/**
	 * Retrieves all available image sizes
	 * @since 8/19/12
	 * @return array
	 */
	function image_sizes(){
		return get_intermediate_image_sizes();
	}
	
	
	
	/**
	 * Brings in the PHP page for the mce buttons shortcode popup
	 * @since 8/19/12
	 * @uses called by mce_button()
	 */
	function load_outside_page(){
		//Escape Hatch
		if( !is_user_logged_in() ){ return; }
		//Check the query var
		switch(get_query_var('simple_links_shortcode')) {
			case 'form':
				
				include(SIMPLE_LINKS_SHORTCODE_DIR . 'shortcode-form.php' );
				die();
	
		}
	}
	
	
	/**
	 * Setsup the query var to bring in the outside page to the popup form
	 * @since 8/19/12
	 * @uses called by mce_button()
	 */
	function outside_page_query_var($queries){
		array_push( $queries, 'simple_links_shortcode' );
		return $queries;
	}
	

	
	/**
	 * Saves the meta fields
	 * @since 12.15.12
	 */
	function meta_save(){
		global $post;
		$type = $post->post_type;
	
		//Make sure this is valid
		if ( defined('DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
			return;
		}
	
		//got here some other way
		if( !is_array( $this->{$type . '_meta_fields'} ) ){
			return;
		}
		
		
		//Apply Filters to Add or remove meta boxes from the links
		$this->simple_link_meta_fields  = apply_filters('simple_links_meta_boxes', $this->simple_link_meta_fields );
		
	
		//Go through the options extra fields
		foreach( $this->{$type . '_meta_fields'} as $field ){
			if( $field != 'link_aditional_fields' ){
				update_post_meta( $post->ID, $field, $_POST[$field] );
			}
		}
        
        //for the no follow checkbox
        update_post_meta( $post->ID, 'link_target_nofollow', $_POST['link_target_nofollow'] );
        
    
		//Escape Hatch
		if( !isset( $_POST['link_additional_value'] ) || !is_array( $_POST['link_additional_value'] ) ){
			return;
		}
	
		//Update the Addtional Fields
		update_post_meta( $post->ID, 'link_additional_value', json_encode( $_POST['link_additional_value'] ) );
	
	
	
	
	}
	
	
	
	/**
	 * Register the meta boxes
	 * @uses Add or remove meta boxes by adding values to the 'simple_links_meta_boxes' array via the filter here
	 * @uses Add Change or remove meta box descriptions from the array using the 'simple_links_meta_descriptions' filter
	 *     ** Any changes to the meta boxes will automatically save and become available for the output via the filters there
	 *     ** You have to use the output filters obj to retrieve a new meta boxes value
	 * @uses add or rem
	 * @since 8/13/12
	 */
	function meta_box($post){
		//Apply Filters to Change Descriptions of the Meta Boxes
		$this->meta_box_descriptions = apply_filters('simple_links_meta_descriptions', $this->meta_box_descriptions );
		
		//Apply Filters to Add or remove meta boxes from the links
		$this->simple_link_meta_fields  = apply_filters('simple_links_meta_boxes', $this->simple_link_meta_fields );
		
		//Go through each meta box in the filtered array
		foreach( $this->simple_link_meta_fields as $box){
			if( ($box != 'additional_fields') && ($box != 'target') ){
				add_meta_box( $box.'_links_meta_box', self::human_format_slug($box ) , array( $this, 'link_meta_box_output' ), $post->type , 'advanced' , 'high', $box );
			}
		}
		
		//The link Target meta box
		if( in_array( 'target', $this->simple_link_meta_fields ) ){
			add_meta_box( 'target_links_meta_box', 'Link Target' , array( $this, 'target_meta_box_output' ), $post->type , 'advanced' , 'high');
		}
	    if( in_array( 'additional_fields', $this->simple_link_meta_fields ) ){
	    	add_meta_box( 'additional_fields', 'Additional Fields' , array( $this, 'additional_fields_meta_box_output' ), $post->type , 'advanced' , 'high');
	    }
		
	}
	
	
	/**
	 * Creates the expandable meta box
	 * @since 8/18/12
	 */
	function additional_fields_meta_box_output($post){
		global $simple_links_admin_func;
		$values = json_decode(get_post_meta($post->ID, 'link_additional_value', true), true );
		$names = json_decode( get_option( 'link_additional_fields' ), true);
		$count = 0;

		if( is_array( $names ) ){
			foreach( $names as $key => $value ){
				
				printf('<p>%s:  <input type="text" name="link_additional_value[%s]" value="%s" size="70" class="SL-additonal-input">', 
																		$value, $value, $values[$value]
						);
			}
		} 
		
	if( isset( $this->meta_box_descriptions['additional_fields'] ) ){
		
		    echo '<p>' . $this->meta_box_descriptions['additional_fields'] . '</p>';
		
		   //this one has a default link to settins so don't show if can't see settings
		   if( current_user_can($simple_links_admin_func->cap_for_settings)){
		   	       echo '<p>'.__('You may add additonal fields which will be available for all links in the ', 'simple-links' ).'
					 				<a href="/wp-admin/edit.php?post_type=simple_link&page=simple-link-settings">'.__('Settings', 'simple-links' ).'</a>
			  														</p>';
		   	      
		   }
		}
		
	}
	
	
	
	
	/**
	 * The Link Target Radio Buttons Meta Box
	 * @since 12.15.12
	 */
	function target_meta_box_output($post){
	
		?>
		<p><label for="link_target_blank" class="selectit">
		<input id="link_target_blank" type="radio" name="target" value="_blank" <?php checked( get_post_meta( $post->ID, 'target', true), '_blank' );?>>
		<code>_blank</code> &minus; <?php _e('new window or tab','simple-links');?>.</label></p>
		<p><label for="link_target_top" class="selectit">
		<input id="link_target_top" type="radio" name="target" value="_top" <?php checked( get_post_meta( $post->ID, 'target', true), '_top' );?>>
		<code>_top</code> &minus; <?php _e('current window or tab, with no frames','simple-links');?>.</label></p>
		<p><label for="link_target_none" class="selectit">
		<input id="link_target_none" type="radio" name="target" value="" <?php checked( get_post_meta( $post->ID, 'target', true), '' );?>>
		<code>_none</code> &minus; <?php _e('same window or tab','simple-links');?>.</label></p>
		<?php 
		if( isset( $this->meta_box_descriptions['target'] ) ){
			echo '<p>' . $this->meta_box_descriptions['target'] . '</p>';
		}
		
		?>
		<p>
		<input id="link_target_nofollow" type="checkbox" name="link_target_nofollow" value="1" 
		      <?php checked( get_post_meta( $post->ID, 'link_target_nofollow', true), 1 );?>> 
		      &nbsp; <?php _e('Add a','simple-links');?> <code>nofollow</code> <?php _e('rel to this link','simple-links');?> 
		</p>
		<?php

	}
	
	
	/**
	 * The output of the standard meta boxes and fields
	 * @param $post
	 * @param array $box the args sent to keep track of what fields is sent over
	 * @since 12.15.12
	 */
	function link_meta_box_output($post, $box){
	    $box = $box['args'];
        
        if( $box != 'description' ){
                 printf('<input type="text" name="%s" value="%s" size="100" class="simple-links-input">', $box, get_post_meta( $post->ID, $box, true ) );
        } else {
               printf('<textarea name="%s" class="simple-links-input">%s</textarea>', $box, get_post_meta( $post->ID, $box, true ) );
        }
	    
	    if( isset( $this->meta_box_descriptions[$box] ) ){
	    	printf('<p>%s</p>', $this->meta_box_descriptions[$box] );
	    }
	}
	
	
	/**
	 * Retrieves all the link categories a link is assinged to
	 * @param int $postID the link ID
	 * @param boolean $full_array to return all values default to an array of just names
	 * @return boolean|array
	 * @since 8/21/12
	 * @uses call whereve you would like
	 */
	function get_link_categories( $postID, $full_array = false ){
		$cats = get_the_terms( $postID, 'simple_link_category' );
	
		//escape hatch
		if( !is_array($cats) ){
			return false;
		}
	
		//return full array
		if( $full_array ){
			return $cats;
		}
	
	
		foreach( $cats as $cat ){
			$cat_names[] = $cat->name;
		}
	
		return $cat_names;
	}
	
	
	
	
	/**
	 * Adds the link categories taxonomy
	 */
	function link_categories(){
		self::register_taxonomy( 'simple_link_category', 'simple_link', array(
															'labels' => array(
																		'name'             => __('Link Categories','simple-links'),
																	    'singular_name'    => __('Link Category','simple-links'),
																		'all_items'        => __('Link Categories','simple-links'),
																		'menu_name'        => __('Link Categories','simple-links'),
																		'add_new_item'     => __('Add New Category','simple-links'),
																		'update_item'      => __('Update Category','simple-links')	
															),
															'show_in_nav_menus'    => false,
															'query_var'            => 'simple_link_category'
															
													)
				);
		
	}
	
	
	
	
	/**
	 * Add the jquery to the site
	 * @since 11.2.12
	 */
	function frontend_scripts(){

		wp_enqueue_script(
				apply_filters( 'simple_links_script', 'simple_links_script' ),
				SIMPLE_LINKS_JS_DIR . 'simple_links.js',
				array('jquery' ), 
				'1.0.0'   
		
		);

		

		wp_enqueue_style(
				apply_filters( 'simple_links_style' , 'simple_links_style' ), //The name of the style
				SIMPLE_LINKS_CSS_DIR . 'simple.links.css'
		);
		
		
		
	}
		

	/**
     * Registers the Custom Post Type
     * @since 4.21.13
     */
	function post_type(){
	
	    $args = apply_filters('simple-links-register-post-type', array(
				                                           'menu_icon' => SIMPLE_LINKS_IMG_DIR . 'menu-icon.png',
				                                           'labels'    => array(
				                                           		           'singular_name' =>  __('Link','simple-links'),
				                                           				   'all_items'     =>  __('All Links','simple-links'),
				                                           				   'new_item_name' =>  __('New Link','simple-links'),
				                                           		           'add_new_item'  =>  __('Add Link','simple-links'),
				                                           				   'add_new'       =>  __('Add Link','simple-links'),
				                                           				   'view_item'     =>  __('View Link','simple-links')
				                                           		),
															'hierachical'          => false,
															'supports'	           => array( 'thumbnail','title','page-attributes','revisions' ),
															'show_in_nav_menus'    => false,
				                                            'has_archive'          => false,
															'rewrite'              => false,
															'exclude_from_search'  => true,
															'register_meta_box_cb' => array( $this, 'meta_box' )
															
				
				
				) );
	
		$this->register_post_type( 'simple_link' , $args );
		
	}
	
	
}  //-- End of Class
} //-- End of if class exists