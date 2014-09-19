<?php
add_filter( 'simple_links_parsed_query_args', 'sl_require_all_cats' );
function sl_require_all_cats( $query_args ){
	if( ! empty( $query_args[ 'tax_query' ] ) ){
		foreach( $query_args[ 'tax_query' ][ 0 ][ 'terms' ] as $cat_id ){
			$tax[ ] = array(
				'taxonomy' => 'simple_link_category',
				'fields'   => 'id',
				'terms'    => $cat_id
			);
		}

		$tax[ 'relation' ]         = 'AND';
		$query_args[ 'tax_query' ] = $tax;
	}

	return $query_args;
}