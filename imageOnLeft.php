<?php
add_filter( 'simple_links_list_item', 'sl_img_on_left', 1, 3 );
function sl_img_on_left( $output, $link, $obj ){

	$output = '<li class="simple-links-item" id="link-' . $link->ID . '" style="width: 100%; float:left; clear: both; list-style: none">';

	$output .= '<div style="width: 50%; float: left; ">';

	//Main link output
	$link_output = sprintf( '<a href="%s" target="%s" title="%s" %s>%s</a>',
		esc_attr( $obj->getData( 'web_address' ) ),
		esc_attr( $obj->getData( 'target' ) ),
		esc_attr( strip_tags( $obj->getData( 'description' ) ) ),
		esc_attr( empty( $obj->meta_data[ 'link_target_nofollow' ][ 0 ] ) ? '' : 'rel="nofollow"' ),
		$obj->getImage()
	);

	$output .= $link_output . '</div><div style="width: 50%; float: right; ">';


	$output .= $obj->link->post_title . '<br>';


	//The description
	if( ( $obj->args[ 'description' ] ) && ( $obj->getData( 'description' ) != '' ) ){
		if( $obj->args[ 'show_description_formatting' ] ){
			$description = wpautop( $obj->getData( 'description' ) );
		} else {
			$description = $obj->getData( 'description' );
		}
		$output .= sprintf( '%s <span class="link-description">%s</span>', $obj->args[ 'separator' ], $description );
	}

	//The additional fields
	if( is_array( $obj->args[ 'fields' ] ) ){
		foreach( $obj->args[ 'fields' ] as $field ){
			$data = $obj->getAdditionalField( $field );
			if( ! empty( $data ) ){
				$output .= sprintf( '%s <span class="%s">%s</span>',
					$obj->args[ 'separator' ],
					str_replace( ' ', '-', strtolower( $field ) ),
					$data
				);
			}
		}
	}

	$output .= '</div>';

	return $output;

}