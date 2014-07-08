<?php                         /**
                               * The Form for the MCE Shortcode Generator
                               * @uses called with a template redirect using a query var send from the mce plugin
                               * @see simple_links->load_outside_page();
                               * @see js/editor_plugin.js
 * 
                               * @author Mat Lipe <mat@lipeimagination.info>
                               */

wp_enqueue_script('jquery');
wp_enqueue_script('sl-shortcode-form', SIMPLE_LINKS_JS_DIR.'shortcode-form.js');

?> 
<title>Add Simple Links</title>
<script type="text/javascript" src="<?php echo includes_url(); ?>js/tinymce/tiny_mce_popup.js?v=3211"></script>
<?php 

wp_head();

//The plugins functions
global $simple_links_func;

?>

<style type="text/css">

	<?php 
	if( get_bloginfo('version') >= 3.8 ){
		?>
		html{
			margin-top: 46px important!;
		}
	
		body{
			margin-top: -66px; important;
		}
		<?php
	} else {
		?>
		html{
			margin-top: 28px important!;
		}
	
		body{
			margin-top: -23px; important;
		}
		<?php
		
	}
	?>
	
	.cat.child{
		margin-left: 15px !important;
	}
	
	.wrap{
		padding: 0 10px 15px !important;	
	}
	
    #generate{
		background: #2ea2cc;
		border-color: #0074a2;
		-webkit-box-shadow: inset 0 1px 0 rgba(120,200,230,0.5), 0 1px 0 rgba(0,0,0,.15);
		box-shadow: inset 0 1px 0 rgba(120,200,230,0.5), 0 1px 0 rgba(0,0,0,.15);
		color: #fff;
		text-decoration: none;
		height: 30px;
		line-height: 28px;
		padding: 0 12px 2px;
		display: inline-block;
		font-size: 13px;
		margin: 0;
		cursor: pointer;
		border-width: 1px;
		border-style: solid;
		-webkit-appearance: none;
		-webkit-border-radius: 3px;
		border-radius: 3px;
		white-space: nowrap;
		-webkit-box-sizing: border-box;
		-moz-box-sizing: border-box;
		box-sizing: border-box;
    }
    
    #generate:hover{
    	background: #1e8cbe;
		border-color: #0074a2;
		-webkit-box-shadow: inset 0 1px 0 rgba(120,200,230,0.6);
		box-shadow: inset 0 1px 0 rgba(120,200,230,0.6);
		color: #fff;	
    }
   
	body,input{
    	font-size: 14px;
    	padding: 5px;
    	border-radius: 5px;
    	
	}
</style>
</head>

<body>
	<div class="wrap">

      <h4><?php _e('This Will Generate the Shortcode to Display Simple Links','simple-links');?></h4>
        <p><em><?php _e('If no links match the options chosen, this will not display anything','simple-links');?>.</em></p>

        <p><?php _e( 'Title (optional)', 'simple-links' );?>: <input type="text" id="title"  size="50"/></p>
        
        <fieldset>
        <p>
        	<?php _e( 'Categories (optional)', 'simple-links' );?>: <br><br>
        	
           	<?php 
           	$cats = Simple_Links_Categories::get_categories();
           	if( !empty( $cats ) ){
            	foreach( $cats as $cat ){          		
                	printf('<input class="cat" type="checkbox" value="%1$s" /> %1$s <br>', $cat->name );
					
					if( !empty( $cat->children ) ){
						foreach( $cat->children as $child ){
							printf('<input class="cat child" type="checkbox" value="%1$s" /> %1$s <br>', $child->name );
						}	
					}
					
             	}
			} else {
				_e( 'No link categories have been created yet.', 'simple-links' );
			}
            ?>
        </p>
        </fieldset>
        
        
        <p><?php _e('Number of Links','simple-links');?>: 
            <select id="count">
                <option value=""><?php _e('All','simple-links');?></option>
                <?php 
                for( $i = 1; $i<30; $i++){
                    printf('<option value="%s">%s</option>', $i, $i );
                }
                ?>
            </select>
        </p>
        
        <p><?php _e('Order By (optional)','simple-links');?>: 
            <select id="orderby">
                <option value=""><?php _e('Link Order','simple-links');?></option>
                <option value="title"><?php _e('Title','simple-links');?></option>
                <option value="random"><?php _e('Random','simple-links');?></option>
            </select>
        </p>
        
        <p><?php _e('Show Description','simple-links');?> <input type="checkbox" id="description" value="true" /></p>
        
        <p><?php _e('Show Description Formatting','simple-links');?> <input type="checkbox" id="description-formatting" value="true" /></p>
        
        
        <p><?php _e('Show Image','simple-links');?> <input type="checkbox" id="show_image" value="true" /></p>
        <p><?php _e('Display Image Without Title','simple-links');?> <input type="checkbox" id="show_image_only" value="true" /></p>
        
        <p>
          <?php _e('Image Size','simple-links');?>  <select id="image-size">
          <?php 
            foreach( $simple_links_func->image_sizes() as $size ){
                printf('<option value="%s">%s</a>', $size, $size );
            }
            ?>
          </select>
        </p>
        <p><?php _e('Remove Line Break Between Image and Link','simple-links');?> <input type="checkbox" id="line_break" value="1" /></p>
        <p><?php _e('Include Additional Fields','simple-links');?>:<br>
            <?php 
            if( empty( $simple_links_func->additional_fields ) ){
                echo '<em>'.__('There have been no additional fields added','simple-links'). '</em>';
            } else {
            foreach( $simple_links_func->additional_fields as $field ){
                        printf( '<input class="additional" type="checkbox" value="%s">%s<br>', $field, $field );
                  }
            }
        ?>
        </p>
        
        <p><?php _e('Field Separator','simple-links');?>:<br> 
        <em><small><small><?php _e('HTML is Allowed and Will show up Formatted in the Editor','simple-links');?>:</small><small></em><br>
        <input type="text" value="-" id="separator" size="50"/></p>
        
        <?php do_action('simple_links_shortcode_form' ); ?>
        
      	<?php if( get_bloginfo('version') < 3.8 ){
      		?><p>&nbsp;</p><?php
      	}
		?>
      		  
      	<input type="button" id="generate" class="button-primary" value="Generate">
		

      
	</div>
</body>



