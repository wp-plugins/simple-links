<?php

/**
 * Class for generating and interacting with each individual link
 * Each link should be a new instance of the class
 * 
 * @author Mat Lipe <mat@matlipe.com>
 * @since 2.0
 * 
 * @uses May be constructed a link object or ID and using a echo will output the formatted link
 */
class SimpleLinksTheLink{
    
    public $link; //The Post object
    public $meta_data = array(); //The post meta data
    
    
    public $args = array(
        'type'            => false,
        'show_image'      => false,
        'image_size'      => 'thumbnail',
        'id'              => false,
        'show_image_only' => false
    );
    
    
    /**
     * @param WP_Post|int $link - id or post or post object
     * @param array $args
     */
    function __construct($link, $args = array()){
        
        $this->args = wp_parse_args($args, $this->args);
        
        $this->args = apply_filters('simple_links_the_link_args', $this->args);
        
        
        if( is_numeric($link) ){
            $this->link = get_post($link);
        } else {
            $this->link = $link;
        }

    }
    
    
    /**
     * Magic method for echoing the object
     * 
     * @uses self::output()
     * @uses echo $link
     */
    function __toString(){
        return $this->output();
        
    }
    
    
    /**
     * The actual links Output
     * 
     * @param bool $echo - defaults to false;
     */
    function output($echo = false){
        
        if( !$this->link instanceof WP_post ) return false;
        
        
        if( $this->args['show_image'] ){
            $image = $this->getImage();
        } else {
            $image = '';
        }
        
        
        $class = 'simple-links-item';
        if( $this->args['type'] ){
            $class .= 'simple-links-'.$this->args['type'].'-item';
        }

        $output = sprintf('<li class="%s" id="link-%s">', $class, $this->link->ID ); 
            $link_output = sprintf('<a href="%s" target="%s" title="%s" %s>%s%s</a>', 
                                    $this->getData('web_address'),
                                    $this->getData('target'),
                                    $this->getData('description'),
                                    empty( $this->meta_data['link_target_nofollow'][0] ) ? '': 'rel="nofollow"', 
                                    $image,
                                    $link->post_title
            );     
            
            $link_output = apply_filters( 'simple_links_link_output', $link_output, $this->getData, $this->link, $image, $this->args );
            
            //backward compatibility
            $link_output = apply_filters('simple_links_'.$this->args['type'].'_link_output', $link_output, $this->getData, $this->link, $image, $this->args );
            $link_output = apply_filters('simple_links_'.$this->args['type'].'_link_output_' . $this->args['id'], $link_output, $this->getData, $this->link, $image, $this->args );
 
 
            $output .= $link_output;
            
            

        $output .= '</li>';
        
        
        //handle the output
        if( $echo ){
            echo $output;
        } else {
            return $output;
        }
    }
    
    
    
    /**
     * Gets the links image formatted based on args
     * 
     * @since 9.17.13
     * 
     * return string
     */
    function getImage(){
        //Remove the post Title if showing image only
        if( $this->args['show_image_only'] ){
             $this->link->post_title = '';
        }
                        
        $image = get_the_post_thumbnail($link->ID, $this->args['image_size']);
                        
        //more for the filterable object
        $this->link->image = $image;
        if( $image != '' && !$this->args['remove_line_break']){
             $image .= '<br>';  //make the ones with returned image have the links below
        }   
        
        return $image;
        
    }
    
    
    
    /**
     * Get the links meta data
     * 
     * @param string $name - name of meta data key (defaults to all meta data );
     * 
     * @return mixed
     */
    function getData($name = false){
        
        if( empty( $this->meta_data ) ){
            $this->meta_data = get_post_meta($post->ID); 
            $this->link->meta = $this->meta_data;
            
            $this->meta_data = apply_filters( 'simple_links_meta', $this->meta_data, $this->link, $this->args );

            //backward compatibility
            $this->meta_data = apply_filters('simple_links_'.$this->args['type'].'_link_meta_' . $this->args['id'], $this->meta_data, $this->link, $this->args );
            $this->meta_data = apply_filters('simple_links_'.$this->args['type'].'_link_meta', $this->meta_data, $this->link, $this->args );  
            
        }
        
        
        //defaults to all data
        if( !$name ){
            return $this->meta_data;
        }
        
        if( isset( $this->meta_data[$name][0] ) ){
            return $this->meta_data[$name][0];
        }
        
        return false;
    }
    
    
    
    /**
     * Get a links additiona field's value
     * 
     * @param string [$name] - defaults to all additional fields
     * 
     * @return string|array
     */
    function getAdditionalField($name = false){
        
        
    }
    
    
}
