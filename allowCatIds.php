<?php

/** This will allow you to pass cat ids instead of names **/

add_filter( 'simple_links_args', 'sl_convert_ids_to_cat_names' );
 function sl_convert_ids_to_cat_names( $args ){
 	
	if( !empty( $args[ 'category' ] ) ){		
		if( is_string( $args[ 'category' ] ) ){
			$args[ 'category' ] = explode( ',', $args[ 'category' ] );
		}
		foreach( $args[ 'category' ] as &$cat ){
			if( is_numeric( $cat ) ){
				$cat = get_term( $cat, Simple_Links_Categories::TAXONOMY )->name;	
			}	
		}
		$args[ 'category' ] = implode( ',', $args[ 'category' ] );	
	}
	
	
	return $args; 
 }
 