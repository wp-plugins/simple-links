<?php

/**
 * Simple Link
 *
 * Custom Post Type handler
 *
 * @class   Simple_Link
 * @package Simple Links
 *
 * @since   2.5.3
 *
 *
 * @todo    Remove SL_post_type_tax dependencies
 *
 */
class Simple_Link {

	const POST_TYPE = 'simple_link';
	protected $simple_link_meta_fields = array(
		'web_address',
		'description',
		'target',
		'additional_fields'
	);
	protected $meta_box_descriptions = array();

	/**
	 * Constructor
	 *
	 * @todo remove the constructor
	 */
	public function __construct(){
		$this->set_descriptions();

		add_action( 'save_post', array( $this, 'meta_save' ) );

	}


	/**
	 * Set Descriptions
	 *
	 * Set the meta box descriptions
	 *
	 * @uses $this->meta_box_descriptions
	 *
	 * @todo move this process to the meta box class and remove __construct()
	 *
	 * @return void
	 */
	private function set_descriptions(){
		$desc = array(
			'web_address'       => __( 'Example', 'simple-links' ) . ': <code>http://wordpress.org/</code> ' . __( 'DO NOT forget the', 'simple-links' ) . ' <code>http:// or https://</code>',
			'description'       => __( 'This will be shown when someone hovers over the link, or optionally below the link', 'simple-links' ) . '.',
			'target'            => __( 'Choose the target frame for your link', 'simple-links' ) . '.',
			'additional_fields' => __( 'Values entered in these fields will be available for shortcodes and Widgets', 'simple-links' ) . ' '
		);

		$this->meta_box_descriptions = apply_filters( 'simple-links-meta-box-descriptions', $desc );

	}


	/**
	 * Register Post Type
	 *
	 * Registers the simple_link post type
	 *
	 * @todo change to register_post_type once dependcies are fixed
	 *
	 * @return void
	 */
	public function register_sl_post_type(){

		$single = __( 'Link', 'simple-links' );
		$plural = __( 'Links', 'simple-links' );

		$args = array(
			'menu_icon'            => SIMPLE_LINKS_IMG_DIR . 'menu-icon.png',
			'labels'               => array(
				'name'                       => __( 'Simple Links', 'simple-links' ),
				'singular_name'              => $single,
				'search_items'               => sprintf( __( 'Search %s', 'simple-links' ), $plural ),
				'popular_items'              => sprintf( __( 'Popular %s', 'simple-links' ), $plural ),
				'all_items'                  => sprintf( __( 'All %s', 'simple-links' ), $plural ),
				'parent_item'                => sprintf( __( 'Parent %s', 'simple-links' ), $single ),
				'parent_item_colon'          => sprintf( __( 'Parent %s:', 'simple-links' ), $single ),
				'edit_item'                  => sprintf( __( 'Edit %s', 'simple-links' ), $single ),
				'update_item'                => sprintf( __( 'Update %s', 'simple-links' ), $single ),
				'add_new_item'               => sprintf( __( 'Add New %s', 'simple-links' ), $single ),
				'new_item_name'              => sprintf( __( 'New %s Name', 'simple-links' ), $single ),
				'separate_items_with_commas' => sprintf( __( 'Seperate %s with commas', 'simple-links' ), $single ),
				'add_or_remove_items'        => sprintf( __( 'Add or remove %s', 'simple-links' ), $plural ),
				'choose_from_most_used'      => sprintf( __( 'Choose from the most used %s', 'simple-links' ), $plural ),
				'view_item'                  => sprintf( __( 'View %s', 'simple-links' ), $single ),
				'add_new'                    => sprintf( __( 'Add New %s', 'simple-links' ), $single ),
				'new_item'                   => sprintf( __( 'New %s', 'simple-links' ), $single ),
				'menu_name'                  => __( 'Simple Links', 'simple-links' )
			),
			'hierachical'          => false,
			'supports'             => array(
				'thumbnail',
				'title',
				'page-attributes',
				'revisions'
			),
			'publicly_queryable'   => false,
			'public'               => false,
			'show_ui'              => true,
			'show_in_nav_menus'    => false,
			'has_archive'          => false,
			'rewrite'              => false,
			'exclude_from_search'  => true,
			'register_meta_box_cb' => array(
				$this,
				'meta_box'
			)
		);

		register_post_type( self::POST_TYPE, apply_filters( 'simple-links-register-post-type', $args ) );

	}


	/**
	 * Saves the meta fields
	 *
	 * @since 1.7.14
	 */
	function meta_save(){
		global $post;

		if( empty( $post->post_type ) || $post->post_type != self::POST_TYPE ){
			return;
		}

		//Make sure this is valid
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
			return;
		}

		//got here some other way
		if( empty( $this->{self::POST_TYPE . '_meta_fields'} ) || ! is_array( $this->{self::POST_TYPE . '_meta_fields'} ) ){
			return;
		}


		//Apply Filters to Add or remove meta boxes from the links
		$this->simple_link_meta_fields = apply_filters( 'simple_links_meta_boxes', $this->simple_link_meta_fields );

		//Go through the options extra fields
		foreach( $this->{self::POST_TYPE . '_meta_fields'} as $field ){
			if( $field != 'additional_fields' ){
				update_post_meta( $post->ID, $field, $_POST[ $field ] );
			}
		}

		//for the no follow checkbox
		if( isset( $_POST[ 'link_target_nofollow' ] ) ){
			update_post_meta( $post->ID, 'link_target_nofollow', $_POST[ 'link_target_nofollow' ] );
		} else {
			update_post_meta( $post->ID, 'link_target_nofollow', 0 );
		}


		//Escape Hatch
		if( ! isset( $_POST[ 'link_additional_value' ] ) || ! is_array( $_POST[ 'link_additional_value' ] ) ){
			return;
		}

		//Update the Addtional Fields
		update_post_meta( $post->ID, 'link_additional_value', $_POST[ 'link_additional_value' ] );


	}


	/**
	 * Register the meta boxes
	 *
	 * @uses  Add or remove meta boxes by adding values to the 'simple_links_meta_boxes' array via the filter here
	 * @uses  Add Change or remove meta box descriptions from the array using the 'simple_links_meta_descriptions' filter
	 *        ** Any changes to the meta boxes will automatically save and become available for the output via the filters there
	 *        ** You have to use the output filters obj to retrieve a new meta boxes value
	 * @uses  add or rem
	 * @since 8/13/12
	 */
	function meta_box( $post ){
		//Apply Filters to Change Descriptions of the Meta Boxes
		$this->meta_box_descriptions = apply_filters( 'simple_links_meta_descriptions', $this->meta_box_descriptions );

		//Apply Filters to Add or remove meta boxes from the links
		$this->simple_link_meta_fields = apply_filters( 'simple_links_meta_boxes', $this->simple_link_meta_fields );

		//Go through each meta box in the filtered array
		foreach( $this->simple_link_meta_fields as $box ){
			if( ( $box != 'additional_fields' ) && ( $box != 'target' ) ){
				add_meta_box( $box . '_links_meta_box', ucwords( str_replace( '_', ' ', $box ) ), array(
						$this,
						'link_meta_box_output'
					), $post->type, 'advanced', 'high', $box );
			}
		}

		//The link Target meta box
		if( in_array( 'target', $this->simple_link_meta_fields ) ){
			add_meta_box( 'target_links_meta_box', 'Link Target', array(
					$this,
					'target_meta_box_output'
				), $post->type, 'advanced', 'high' );
		}
		if( in_array( 'additional_fields', $this->simple_link_meta_fields ) ){
			add_meta_box( 'additional_fields', 'Additional Fields', array(
					$this,
					'additional_fields_meta_box_output'
				), $post->type, 'advanced', 'high' );
		}

	}


	/**
	 * The output of the standard meta boxes and fields
	 *
	 * @param WP_Post $post
	 * @param array   $box the args sent to keep track of what fields is sent over
	 *
	 * @since 12.26.12
	 */
	function link_meta_box_output( $post, $box ){
		$box = $box[ 'args' ];

		if( $box != 'description' ){
			printf( '<input type="text" name="%s" value="%s" size="100" class="simple-links-input">', $box, get_post_meta( $post->ID, $box, true ) );
		} else {
			wp_editor( get_post_meta( $post->ID, $box, true ), $box, array( 'media_buttons' => false ) );
		}

		if( isset( $this->meta_box_descriptions[ $box ] ) ){
			printf( '<p>%s</p>', $this->meta_box_descriptions[ $box ] );
		}
	}


	/**
	 * Output of the additional fields meta box
	 *
	 *
	 * @since 1.7.14
	 *
	 *
	 */
	function additional_fields_meta_box_output( $post ){
		global $simple_links_admin_func, $simple_links;

		$values = $simple_links->getAdditionalFieldsValues( $post->ID );

		$names = $simple_links->getAdditionalFields();
		$count = 0;

		if( is_array( $names ) ){
			foreach( $names as $key => $value ){
				if( empty( $values[ $value ] ) ){
					$values[ $value ] = null;
				}

				printf( '<p>%s:  <input type="text" name="link_additional_value[%s]" value="%s" size="70" class="SL-additonal-input">',
					$value, $value, $values[ $value ]
				);
			}
		}

		if( isset( $this->meta_box_descriptions[ 'additional_fields' ] ) ){

			echo '<p>' . $this->meta_box_descriptions[ 'additional_fields' ] . '</p>';

			//this one has a default link to settins so don't show if can't see settings
			if( current_user_can( Simple_Links_Settings::get_instance()->get_settings_cap() ) ){
				echo '<p>' . __( 'You may add additonal fields which will be available for all links in the ', 'simple-links' ) . '
					 				<a href="/wp-admin/edit.php?post_type=simple_link&page=simple-link-settings">' . __( 'Settings', 'simple-links' ) . '</a>
			  														</p>';

			}
		}

	}


	/**
	 * Target Meta Box Output
	 *
	 * The Link Target Radio Buttons Meta Box
	 *
	 * @return void
	 *
	 */
	function target_meta_box_output( $post ){
		$target = get_post_meta( $post->ID, 'target', true );
		if( empty( $target ) ){
			$target = apply_filters( 'simple-links-default-target', "" );
		}

		require( SIMPLE_LINKS_DIR . 'admin-views/link-target.php' );

	}


}
