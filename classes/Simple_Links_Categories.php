<?php

/**
 * Simple Links Settings
 *
 * An evolution of the taxonomy handling to a single class instead of within the
 * large admin class
 *
 * @class   Simple_Links_Categorie
 * @package Simple Links
 *
 *
 */
class Simple_Links_Categories {

	const TAXONOMY = 'simple_link_category';
	/**
	 * Instance of this class for use as singleton
	 */
	private static $instance;

	public function __construct(){
		$this->hooks();
	}

	/**
	 * hooks
	 *
	 * Actions and filters go here
	 *
	 * @return void
	 */
	private function hooks(){
		add_action( 'init', array( $this, 'link_categories' ) );
	}

	/**
	 * Get Category Names
	 *
	 * Retrieves all link categories names
	 *
	 * @return array
	 */
	public static function get_category_names(){

		$args = array(
			'hide_empty' => false,
			'fields'     => 'names'
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
			} elseif( empty( $clean[ $term->parent ] ) ) {
				if( sizeof( $terms ) == 1 ){
					$clean[ $term->term_id ] = $term;
				} else {
					$terms[ ] = $term;
				}

			} else {
				$clean[ $term->parent ]->children[ ] = $term;
			}

			unset( $terms[ $k ] );
		}

		return $clean;

	}


	/********** SINGLETON FUNCTIONS **********/

	/**
	 * Get (and instantiate, if necessary) the instance of the class
	 *
	 * @static
	 * @return Steelcase_Career_Setttings
	 */
	public static function get_instance(){
		if( ! is_a( self::$instance, __CLASS__ ) ){
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Adds the link categories taxonomy
	 *
	 * @todo Make independent of silly old class
	 */
	function link_categories(){

		$single = __( 'Link Category', 'simple-links' );
		$plural = __( 'Link Categories', 'simple-links' );

		$args = array(
			'labels'            => array(
				'name'                       => $plural,
				'singular_name'              => $single,
				'search_items'               => sprintf( __( 'Search %s', 'simple-links' ), $plural ),
				'popular_items'              => sprintf( __( 'Popular %s', 'simple-links' ), $plural ),
				'all_items'                  => sprintf( __( 'All %s', 'simple-links' ), $plural ),
				'parent_item'                => sprintf( __( 'Parent %s', 'simple-links' ), $single ),
				'parent_item_colon'          => sprintf( __( 'Parent %s:', 'simple-links' ), $single ),
				'edit_item'                  => sprintf( __( 'Edit %s', 'simple-links' ), $single ),
				'update_item'                => sprintf( __( 'Update %s', 'simple-links' ), $single ),
				'add_new_item'               => __( 'Add New Category', 'simple-links' ),
				'new_item_name'              => sprintf( __( 'New %s Name', 'simple-links' ), $single ),
				'separate_items_with_commas' => sprintf( __( 'Seperate %s with commas', 'simple-links' ), $single ),
				'add_or_remove_items'        => sprintf( __( 'Add or remove %s', 'simple-links' ), $plural ),
				'choose_from_most_used'      => sprintf( __( 'Choose from the most used %s', 'simple-links' ), $plural ),
				'menu_name'                  => $plural,
			),
			'show_in_nav_menus' => false,
			'query_var'         => 'simple_link_category',
			'public'            => false,
			'show_in_nav_menus' => false,
			'show_ui'           => true,
			'show_tagcloud'     => false,
			'hierarchical'      => true

		);

		$args = apply_filters( 'simple-links-register-link-categories', $args );

		register_taxonomy( self::TAXONOMY, Simple_Link::POST_TYPE, $args );

	}


}
