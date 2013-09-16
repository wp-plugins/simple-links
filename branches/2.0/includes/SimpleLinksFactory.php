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
    public $links = array(); //the retrieved links
    public $type = false; //if this is a shortcode or widget etc.
    
    /**
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

