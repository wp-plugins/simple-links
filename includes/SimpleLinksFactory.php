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
    public $full_args = array(); //combined args and query args
    public $links = array(); //the retrieved links
    public $type = false; //if this is a shortcode or widget etc.
    
    //Default args - used for output
    public $args = array(  
       'title'              => false,
       'show_image'         => false,
       'show_image_only'    => false,
       'image_size'         => 'thumbnail',
       'fields'             => false,
       'description'        => false,
       'separator'          =>  '-',
       'id'                 => false,
       'remove_line_break'  => false
      );
    
      //Default Query Args - used by getLinks();
      public $query_args = array(
         'order'    => 'ASC',
         'orderby'  => 'menu_order',
         'count'    => '-1',
         'category' => false,
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
        
        $this->parseArgs($args);
        
        $this->getLinks();
        
    }
    
    
    /**
     * Magic method to allow for echo against the main class
     * 
     * @uses echo $links
     */
    function __toString(){
        return $this->output(false);
    }
    
    
    /**
     * Turns whatever args were sent over into a usabe arguments array
     * 
     * @param array $args
     * @return array
     */
    protected  function parseArgs($args){
        
        $args = apply_filters('simple_links_args', $args, $this->type);
        
        
        //shortcode atts filter - from old structure
        if( $this->type == 'shortcode'){
            $args = apply_filters('simple_links_shortcode_atts', $args);
            if( $args['id'] ){
                 $args = apply_filters('simple_links_shortcode_atts_' . $args['id'], $args);
            }
         } 
        
        
        //Merge with defaults - done this way to split to two lists
        $this->args = shortcode_atts($this->args, $args); 
        $this->query_args = shortcode_atts($this->query_args, $args);
        
        $this->full_args = array_merge( $this->args, $this->query_args);
        $this->full_args['type'] = $this->type;
           
         
        //Change the Random att to rand for get posts
        if( $this->query_args['orderby'] == 'random' ){
            $this->query_args['orderby'] = 'rand';
        } else {
            //For Backwards Compatibility
            if( $atts['orderby'] == 'name' ){
                $args['orderby'] = 'title';
            }
        }
        
        
        //Setup the fields
        if( $this->args['fields'] != false ){
            $this->args['fields'] = explode(',', $this->args['fields'] );
        }
        
        
        //Add the categories to the query
        if( $this->query_args['category'] ){
            $args_cats = explode(',', $this->query_args['category']);
            //Go through all the possible categories and add the ones that are set
            foreach( $this->get_categories() as $cat ){
                if( in_array($cat, $args_cats) ){
                    $cat = get_term_by('name', $cat, 'simple_link_category');
                    $all_cats[] = $cat->term_id;
                }
            }
            $this->query_args['tax_query'][] = array(
                        'taxonomy' => 'simple_link_category',
                        'fields'   => 'id',
                        'terms'    =>  $all_cats
            );
        }


        return $this->args = apply_filters( 'simple_links_parsed_args', $this->full_args );
        

    }
    
    
    /**
     * Retrieve the proper links based on argument set earlier
     * 
     * @return obj
     * 
     * @since 9.17.13
     */
    protected  function getLinks(){
        
        
        $this->query_args['post_type'] = 'simple_link';
        $this->query_args['posts_per_page'] = $this->query_args['count'];
        $this->query_args['posts_per_archive_page'] = $this->query_args['count'];
        
        //Get the links
        $links = get_posts( $this->query_args );

        $links = apply_filters( 'simple_links_object', $links, $this->full_args );


        //backwards compatible
        $links = apply_filters('simple_links_'.$this->full_args['type'].'_links_object', $links, $this->full_args);
        $links = apply_filters('simple_links_'.$this->full_args['type'].'_links_object_' . $this->args['id'], $links, $this->full_args );      

  
        return $this->links = $links;
    }
    
    
    /**
     * Generated the output bases on retrieved links
     * 
     *
     * @uses may be called normally or by using echo with the class
     * @uses SimpleLinksTheLink
     * 
     * @param bool $echo - defaults to false
     * @return String|void
     */
    protected function output($echo = false){
        
        if( empty( $this->links ) ) return false;
        
        
        //if there is a title
        if( $this->args['title'] ){
            $output .= sprintf('<h4 class="simple-links-title">%s</h4>', $this->args['title'] );
            
        }
        
        //Start the list
        if( $this->args['id'] ){
            $output .= '<ul class="simple-links-list" id="' . $this->args['id'] . '">';
        } else {
            $output .= '<ul class="simple-links-list">';
        }
            
            //Add the links to the list
            foreach( $links as $link ){
                
                $link = new SimpleLinksTheLink($link, $this->full_args, $this->type);
                
                $output .= $link->output();   
            }
 
        //end the list
        $output .= '</ul><!-- End .simple-links-list -->';
        
        
        
        // Backwards compatibility
        $output =  apply_filters( 'simple_links_'.$this->full_args['type'].'_output', $output, $links, $this->full_args );
        $output = apply_filters( 'simple_links_'.$this->full_args['type'].'_output_' . $this->args['id'], $output, $links, $this->full_args );

            
        $output = apply_filters( 'simple_links__output', $output, $links, $this->full_args );
        
        if( $echo ){
            echo $output;
        } else {
            return $output;
        }
        
    }
    
}

