<?php

/**
 * Allow category slugs to be used in place of category names
 */
add_filter( 'simple_links_args', 'sl_use_category_slugs' );
function sl_use_category_slugs($args) {

	if( !is_array( $args[ 'category' ] ) ) {
		$args[ 'category' ] = explode( ',', $args[ 'category' ] );
	}

	//swap from slug to name
	foreach( $args['category'] as &$cat ) {
		$cat =  get_term_by( 'slug', $cat, Simple_Links_Categories::TAXONOMY )->name;
	}

	return $args;

}
