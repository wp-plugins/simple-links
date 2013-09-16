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
    
    public $meta_data = array(); //The post meta data
    
    
    /**
     * @param WP_Post|int $link - id or post or post object
     */
    function __construct($link){
        if( is_numeric($link) ){
            $link = get_post($link);
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
     */
    function output(){
        
    }
    
    
    /**
     * Get the links meta data
     * 
     * @param string $name - name of meta data key
     * 
     * @return mixed
     */
    function getData($name){
        if( isset( $this->meta_data[$name][0] ) ){
            return $this->meta_data[$name][0];
        }
        
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
