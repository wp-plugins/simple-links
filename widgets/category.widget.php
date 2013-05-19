<?php 


          /**
           * Creates a Widget of parent Child Categories
           * 
           * @author mat lipe
           * @since 4.23.13
           * @package Advanced Sidebar Menu
           *
           */
class advanced_sidebar_menu_category extends WP_Widget {


#-------------------------------------------------------------------------------------------------------------------------

    // This decides the name of the widget
    function __construct() {
                /* Widget settings. */
        $widget_ops = array( 'classname' => 'advanced-sidebar-menu advanced-sidebar-category', 'description' => 'Creates a menu of all the Categories using the child/parent relationship' );
        $control_ops = array( 'width' => 290 );
        /* Create the widget. */
        $this->WP_Widget( 'advanced_sidebar_menu_category', 'Advanced Sidebar Categories Menu', $widget_ops, $control_ops );
        }




#-----------------------------------------------------------------------------------------------------------------------------------
      // this creates the widget form for the dashboard
    function form( $instance ) {
                    //   require( ADVANCED_SIDEBAR_DIR . 'advanced-sidebar-menu.js' );
            ?>
             <p> Title <br>
             <input id="<?php echo $this->get_field_name('title'); ?>" 
                name="<?php echo $this->get_field_name('title'); ?>" size="50" type="text" value="<?php echo $instance['title']; ?>"/></p>
            
            
            <p> Include Parent Category <input id="<?php echo $this->get_field_name('include_parent'); ?>" 
                name="<?php echo $this->get_field_name('include_parent'); ?>" type="checkbox" value="checked" 
                <?php echo $instance['include_parent']; ?>/></p>
            
                        
            <p> Include Parent Even With No Children <input id="<?php echo $this->get_field_name('include_childless_parent'); ?>"
            name="<?php echo $this->get_field_name('include_childless_parent'); ?>" type="checkbox" value="checked" 
                    <?php echo $instance['include_childless_parent']; ?>/></p>
                    
            <p> Use this plugins styling <input id="<?php echo $this->get_field_name('css'); ?>"
            name="<?php echo $this->get_field_name('css'); ?>" type="checkbox" value="checked" 
                    <?php echo $instance['css']; ?>/></p>
                    
            <p> Display Categories on Single Posts <input id="<?php echo $this->get_field_name('single'); ?>"
            name="<?php echo $this->get_field_name('single'); ?>" type="checkbox" value="checked" 
            onclick="javascript:asm_reveal_element( 'new-widget-<?php echo $this->get_field_name('new_widget'); ?>' )"
                    <?php echo $instance['single']; ?>/></p>    
            
            <span id="new-widget-<?php echo $this->get_field_name('new_widget'); ?>" style="<?php 
                  if( $instance['single'] == checked ){
                    echo 'display:block';
                  } else {
                    echo 'display:none';
                  } ?>">        
                 <p>Display Each Single Post's Category 
                    <select id="<?php echo $this->get_field_name('new_widget'); ?>" 
                            name="<?php echo $this->get_field_name('new_widget'); ?>">
                    <?php 
                        if( $instance['new_widget'] == 'widget' ){
                            echo '<option value="widget" selected> In a new widget </option>';
                            echo '<option value="list"> In another list in the same widget </option>';
                        } else {
                            echo '<option value="widget"> In a new widget </option>';
                            echo '<option value="list" selected> In another list in the same widget </option>';
                        }
                    
                    ?></select>
                 </p>
            </span>
         
                
                    
            <p> Categories to Exclude, Comma Separated:<input id="<?php echo $this->get_field_name('exclude'); ?>" 
                name="<?php echo $this->get_field_name('exclude'); ?>" type="text" value="<?php echo $instance['exclude']; ?>"/></p>
            
            
            <p> Legacy Mode: (use pre 4.0 structure and css) <input id="<?php echo $this->get_field_name('legacy_mode'); ?>"
            name="<?php echo $this->get_field_name('legacy_mode'); ?>" type="checkbox" value="checked" 
                    <?php echo $instance['legacy_mode']; ?>/>
            </p>    
                
                
            <p> Always Display Child Categories <input id="<?php echo $this->get_field_name('display_all'); ?>" 
                name="<?php echo $this->get_field_name('display_all'); ?>" type="checkbox" value="checked" 
                onclick="javascript:asm_reveal_element( 'levels-<?php echo $this->get_field_name('levels'); ?>' )"
                <?php echo $instance['display_all']; ?>/></p>
            
            <span id="levels-<?php echo $this->get_field_name('levels'); ?>" style="<?php 
                  if( $instance['display_all'] == checked ){
                    echo 'display:block';
                  } else {
                    echo 'display:none';
                  } ?>"> 
            <p> Levels to Display <select id="<?php echo $this->get_field_name('levels'); ?>" 
            name="<?php echo $this->get_field_name('levels'); ?>">
            <?php 
                for( $i= 1; $i<6; $i++ ){
                    if( $i == $instance['levels'] ){
                        echo '<option value="'.$i.'" selected>'.$i.'</option>';
                    } else {
                        echo '<option value="'.$i.'">'.$i.'</option>';
                    }
                } 
                echo '</select></p></span>';
                
                
           do_action('advanced_sidebar_menu_category_widget_form', $instance );        
                
        }


    /**
     * Updates the widget data
     * 
     * @filter - $newInstance = apply_filters('advanced_sidebar_menu_category_widget_update', $newInstance, $oldInstance );
     * @since 5.19.13
     */
    function update( $newInstance, $oldInstance ) {
            $newInstance['exclude'] = strip_tags($new_instance['exclude']);
            $newInstance = apply_filters('advanced_sidebar_menu_category_widget_update', $newInstance, $oldInstance );

            return $newInstance;
        }




#---------------------------------------------------------------------------------------------------------------------------

    /**
     * Outputs the categories widget to the page
     * 
     * @since 5.19.13
     * @uses loads the views/category_list.php
     * 
     * @filters apply_filters('advanced_sidebar_menu_category_widget_output', $content, $args, $instance ); 
     * 
     */
    function widget($args, $instance) {
        
        if( is_single() && !isset( $instance['single'] ) ) return;

        $asm = new advancedSidebarMenu;
        $asm->instance = $instance;
        $asm->args = $args;
        $already_top = array();
        
        $exclude = explode(',', $instance['exclude']);
        $asm->exclude = $exclude;
        
        extract( $args);
        #-- Create a usable array of the excluded pages
    
        $cat_ids = $already_top = array();
        $asm_once = $asm_cat_widget_count = false; //keeps track of how many widgets this created
        $count = null;

        //If on a single page create an array of each category and create a list for each
        if( is_single() && (isset($instance['single']) ) ){
            $category_array = get_the_category();
            foreach( get_the_category() as $id => $cat ){
                $cat_ids[] = $cat->term_id;
            }
            
        //IF on a category page get the id of the category
        } elseif( is_category() ){
            $cat_ids[] = get_query_var('cat');  
        }

        //Go through each category there will be only one if this is a category page mulitple possible if this is single
        foreach( $cat_ids as $cat_id ){
            
             //Get the top category id
             $asm->top_id = $asm->getTopCat($cat_id);
            
             //Keeps track or already used top levels so this won't double up
             if( in_array( $asm->top_id, $already_top ) ){
                continue;
             }
             $already_top[] = $asm->top_id;
       
            //Check for children
            $all_categories = $all = get_categories( array( 'child_of' => $asm->top_id ) );

            
            //If there are no children and not displaying childless parent - bail
            if( empty($all_categories ) && !(isset($instance['include_childless_parent'])) ) continue;
            //If there are no children and the parent is excluded bail
            if( empty($all_categories ) && in_array($asm->top_id, $exclude) ) continue;
                
                    
            //Creates a new widget for each category the single page has if the options are selected to do so
            //Also starts the first widget
            if( !$asm_once || ($instance['new_widget'] == 'widget') ){
                
                //Start the menu
                echo $before_widget;
                        
                    $count++; // To change the id of the widget if there are multiple
                    $asm_once = true;  //There has been a div
                    $close = true; //The div should be closed at the end
    
                    if($instance['new_widget'] == 'list'){
                        $close = false;  //If this is a list leave it open for now
                    } 

            } else {
                $close = false;
            }
                    
                    
            //for deprecation
            $top_cat = $cat_id;
            $cat_ancestors = $asm->ancestors;
                                    
                            
             $legacy = isset( $instance['legacy_mode'] );

             if( isset($instance['css']) && $instance['css'] == 'checked' ){
                 echo '<style type="text/css">';
                     include( $asm->file_hyercy('sidebar-menu.css', $legacy ) );
                 echo '</style>';
             }
            //Bring in the view
            require( $asm->file_hyercy( 'category_list.php', $legacy ) );
                   
            echo apply_filters('advanced_sidebar_menu_category_widget_output', $content, $args, $instance );        
      
            if( $close ){
                //End the Widget Area
                echo $after_widget;
                echo '<!-- First $after_widget -->';
            }
                    

        } //End of each cat loop
        
        
        //IF we were waiting for all the individual lists to complete
        if( !$close && $asm_once ){
            //End the Widget Area
            echo $after_widget;
            echo '<!-- Second $after_widget -->';
            
        }
            
    
             
    } #== /widget()
    
} #== /Clas