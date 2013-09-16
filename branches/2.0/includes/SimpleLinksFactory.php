<?php

/**
 * Factory class for generating the links list for the widget and shortcode
 * 
 * @author Mat Lipe <mat@matlipe.com>
 * @since 2.0
 * 
 * @uses May be constructed with $args then $this->output() will output the links list
 */
class SimpleLinksFactory{
    public $args = array(); //the list args
    public $query_args = array(); //the args used by getLinks()
    public $links = array(); //the retrieved links
    public $type = false; //if this is a shortcode or widget etc.
    
    //Default args
    public $defaults = array(  
                       'title'              => false,
                       'category'           => false,
                       'orderby'            => 'menu_order',
                       'count'              => '-1',
                       'show_image'         => false,
                       'show_image_only'    => false,
                       'image_size'         => 'thumbnail',
                       'order'              => 'ASC',
                       'fields'             => false,
                       'description'        => false,
                       'separator'          =>  '-',
                       'id'                 => false,
                       'remove_line_break'  => false
      );
    
    
    
    
    /**
     * 
     * Main Constrcutor, everything goes through here
     * 
     * @param $args = array('title'              => false,
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
     * @param array $args - either from shortcode, widget, or custom
     * @param string $type - used mostly for css classes
     */
    function __construct($args, $type = false){
        $this->type = $type;
        $this->args = $this->parseArgs($args);
        $this->links = $this->getLinks();
        
    }
    
    
    /**
     * Magic method to allow for echo against the main class
     * 
     * @uses echo $links
     */
    function __toString(){
        return $this->output();
    }
    
    
    /**
     * Turns whatever args were sent over into a usabe arguments array
     * 
     * @param array $args
     * @return array
     */
    private function parseArgs($args){
        
        //shortcode atts filter - from old structure
        if( $this->type == 'shortcode'){
            $args = apply_filters('simple_links_shortcode_atts', $args);
            if( $args['id'] ){
                 $args = apply_filters('simple_links_shortcode_atts_' . $args['id'], $args);
            }
         }
        
        
        $args = wp_parse_args($args, $this->defaults);
         
        //Change the Random att to rand for get posts
        if( $args['orderby'] == 'random' ){
            $args['orderby'] = 'rand';
        }
        
        
        //Setup the fields
        if( $args['fields'] != false ){
            $args['fields'] = explode(',', $args['fields'] );
        }

    }
    
    
    /**
     * Retrieve the proper links based on argument set earlier
     * 
     * @return obj
     */
    private function getLinks(){
        
        
    }
    
    
    /**
     * Generated the output bases on retrieved links
     * 
     * @return String
     * @uses may be called normally or by using echo with the class
     */
    private function output(){
        
        
    }
    
}

