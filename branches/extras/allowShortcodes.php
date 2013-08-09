<?php

/**
 * This will allow the use of shortcodes in the description
 */

add_filter('simple_links_shortcode_link_meta', 'sl_do_shortcodes');
add_filter('simple_links_shortcode_link_output', 'sl_prevent_shortcode', 99, 4);

function sl_prevent_shortcode($link_output, $meta, $link, $image ){
    $link_output = sprintf('<a href="%s" target="%s" title="%s" %s>%s%s</a>', 
                                    $meta['web_address'][0],
                                    $meta['target'][0],
                                    $link->post_title,
                                    empty( $meta['link_target_nofollow'][0] ) ? '': 'rel="nofollow"', 
                                    $image,
                                    $link->post_title
                             ); 
    return $link_output;
                             
}
                             
                             
function sl_do_shortcodes($meta){
    if( isset( $meta['description'][0] ) ){
        $meta['description'][0] = do_shortcode($meta['description'][0]);
    }
    
    //return $output;
    return $meta;
}
