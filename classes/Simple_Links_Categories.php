<?php

/**
 * Simple Links Settings
 *
 * An evolution of the taxonomy handling to a single class instead of within the
 * large admin class
 *
 * @class Simple_Links_Categorie
 * @package Simple Links
 *
 * @todo Remove SL_post_type_tax dependencies
 * 
 */
class Simple_Links_Categories extends SL_post_type_tax{
	const TAXONOMY = 'simple_link_category';

	public function __construct() {
		
		//Add the Link Categories
		add_action( 'init', array( $this, 'link_categories' ) );

	}


	/**
	 * Get Category Names
	 * 
	 * Retrieves all link categories names
	 * 
	 * @return array
	 */
	public static function get_category_names() {

		$args = array(
			'hide_empty'   => false,
			'fields'   => 'names'
		);

		return get_terms( self::TAXONOMY, $args );
	}


	/**
	 * Get Categories
	 * 
	 * Get categories in a hierachal manner
	 * 
	 * @return array( $term->children = array( $terms ) )
	 * 
	 */
	public static function get_categories(){
		
		$terms = get_terms( self::TAXONOMY, 'hide_empty=0' );
		
		$clean = array();
		
		foreach( $terms as $k => $term ){
			if( $term->parent == 0 ){
				$clean[ $term->term_id ] = $term;
			} elseif( empty( $clean[ $term->parent ] ) ){
				if( sizeof( $terms ) == 1 ){
					$clean[ $term->term_id ] = $term;	
				} else {
					$terms[] = $term;	
				}
			
			} else {
				$clean[ $term->parent ]->children[] = $term;
			}
			
			unset( $terms[ $k ] );
		}
		
		return $clean;
		
	}


	/**
	 * Adds the link categories taxonomy
	 *
	 * @todo Make independent of silly old class
	 */
	function link_categories() {
		self::register_taxonomy( self::TAXONOMY, Simple_Link::POST_TYPE, array(
			'labels'   => array(
				'name'   => __( 'Link Categories', 'simple-links' ),
				'singular_name'   => __( 'Link Category', 'simple-links' ),
				'all_items'   => __( 'Link Categories', 'simple-links' ),
				'menu_name'   => __( 'Link Categories', 'simple-links' ),
				'add_new_item'   => __( 'Add New Category', 'simple-links' ),
				'update_item'   => __( 'Update Category', 'simple-links' )
			),
			'show_in_nav_menus'   => false,
			'query_var'   => 'simple_link_category'
		) );

	}
	
	
    /********** SINGLETON FUNCTIONS **********/

	/**
	 * Instance of this class for use as singleton
	 */
	private static $instance;

	/**
	 * Get (and instantiate, if necessary) the instance of the class
	 *
	 * @static
	 * @return Steelcase_Career_Setttings
	 */
	public static function get_instance() {
		if( !is_a( self::$instance, __CLASS__ ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


}
