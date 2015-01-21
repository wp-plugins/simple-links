<?php
/**
 * Methods for the Admin Area of Simple Links
 *
 *
 * @author Mat Lipe <mat@matlipe.com>
 *
 * @uses   called by simple-links.php
 *
 *
 *
 */

if( ! class_exists( 'simple_links_admin' ) ){
	class simple_links_admin extends simple_links {

		/**
		 * Link Manager Deactive
		 *
		 * Keeps track if the setting is on to remove the links manger
		 *
		 * @var bool
		 */
		public $link_manager_deactivate;


		//The addtional fields from the settings page
		public $addtional_fields = array();


		/**
		 * Constructor
		 *
		 */
		function __construct(){

			//Change the post updating messages
			add_filter( 'post_updated_messages', array( $this, 'linksUpdatedMessages' ) );

			//Remove the Wordpress Links from admin menu
			$this->link_manager_deactivate = get_option( 'sl-remove-links', true );
			if( ! empty( $this->link_manager_deactivate ) ){
				add_filter( 'map_meta_cap', array( $this, 'remove_links' ), 99, 2 );
				add_action( 'widgets_init', array( $this, 'remove_links_widget' ), 1 );
			}

			//Add the jquery
			add_action( 'admin_print_scripts', array( $this, 'admin_scripts' ) );
			add_action( 'admin_print_styles', array( $this, 'admin_style' ) );

			//Image uploader mod
			add_action( 'admin_head-media-upload-popup', array( $this, 'upload_mod' ) );

			//The Link Ordering page
			add_action( 'admin_menu', array( $this, 'sub_menu' ) );


			//Add the function to an ajax request to sort the links in the list
			add_action( 'wp_ajax_simple_links_sort_children', array( $this, 'ajax_sort' ) );

			//Ajax request to import links
			add_action( 'wp_ajax_simple_links_import_links', array( $this, 'import_links' ) );


			//Add Contextual help to the necessary screens
			add_action( "load-simple_link_page_simple-link-settings", array( $this, 'help' ) );
			add_action( "load-post.php", array( $this, 'help' ) );
			add_action( "load-widgets.php", array( $this, 'help' ) );


			//Add the shortcode button the MCE editor
			add_action( 'init', array( $this, 'mce_button' ) );


			//Add the filter to the Links Post list
			add_action( 'restrict_manage_posts', array( $this, 'posts_list_cat_filter' ) );
			add_filter( 'request', array( $this, 'post_list_query_filter' ) );


			//Post List Columns Mod
			add_filter( 'manage_simple_link_posts_columns', array( $this, 'post_list_columns' ) );
			add_filter( 'manage_simple_link_posts_custom_column', array( $this, 'post_list_columns_output' ), 0, 2 );

		}


		/**
		 * Customizes the Message for Post Editing Like updating and creating.
		 *
		 * @since   1.7.2
		 *
		 * @uses    called by self::__construct using the 'post_updated_messages' filter
		 * @updated 4.23.13
		 */
		function linksUpdatedMessages( $messages ){
			global $post, $post_ID;

			$messages[ 'simple_link' ] =

				apply_filters( 'simple-links-updated-messages', array(

					0  => '',
					1  => __( 'Link updated.', 'simple-links' ),
					2  => __( 'Custom field updated.', 'simple-links' ),
					3  => __( 'Custom field deleted.', 'simple-links' ),
					4  => __( 'Link updated.', 'simple-links' ),
					5  => isset( $_GET[ 'revision' ] ) ? sprintf( __( 'Link restored to revision from %s', 'simple-links' ), wp_post_revision_title( (int) $_GET[ 'revision' ], false ) ) : false,
					6  => __( 'Link published.', 'simple-links' ),
					7  => __( 'Link saved.', 'simple-links' ),
					8  => __( 'Link submitted.', 'simple-links' ),
					9  => sprintf( __( 'Link scheduled for: <strong>%1$s</strong>.', 'simple-links' ), date_i18n( __( 'M j, Y @ G:i', 'simple-links' ), strtotime( $post->post_date ) ) ),
					10 => __( 'Link draft updated.', 'simple-links' )

				) );

			return $messages;

		}


		/**
		 * Adds the output to the custom post list columns created by post_list_columns()
		 *
		 * @param string $column column name
		 * @param int    $postID
		 *
		 * @since 8/29/12
		 * @uses  called by __construct()
		 */
		function post_list_columns_output( $column, $postID ){

			switch ( $column ){
				case 'web_address':
					echo get_post_meta( $postID, 'web_address', true );
					break;
				case 'category':
					$cats = simple_links()->get_link_categories( $postID );
					if( is_array( $cats ) ){
						echo implode( ' , ', $cats );
					}
					break;
			}
		}


		/**
		 * Adds the web address and the categories the the links list
		 *
		 * @param array $default existing columns
		 *
		 * @since 8/21/12
		 * @uses  Called with __construct();
		 */
		function post_list_columns( $defaults ){

			//get checkbox and title
			$output = array_slice( $defaults, 0, 2 );

			//Add a new column and label it
			$output[ 'web_address' ] = __( 'Web Address', 'simple-links' );
			$output[ 'category' ]    = __( 'Link Categories', 'simple-links' );

			//Add the rest of the default back onto the array
			$output = array_merge( $output, array_slice( $defaults, 2 ) );


			//return
			return $output;


		}


		/**
		 * Update the query request to match the slug of the link category in the links list
		 *
		 * @param array $request the query request so far
		 *
		 * @return array the full request
		 * @since 8.2.13
		 * @uses  called by __construct
		 */
		function post_list_query_filter( $request ){
			global $pagenow;

			if( ! isset( $request[ 'simple_link_category' ] ) ){
				return $request;
			}

			if( is_admin() && $pagenow == 'edit.php' && isset( $request[ 'post_type' ] ) && $request[ 'post_type' ] == 'simple_link' ){
				if( !empty( $_REQUEST[ 'filter_action' ] ) ){
					$request[ 'simple_link_category' ] = get_term( $request[ 'simple_link_category' ], 'simple_link_category' )->slug;
				}
			}

			return $request;
		}


		/**
		 * Creates a drop down list of the link categories to filter the links by in the posts list
		 *
		 * @since 8.2.13
		 * @return null
		 * @uses  called by __construct
		 */
		function posts_list_cat_filter(){
			global $typenow;
			global $wp_query;

			if( $typenow == 'simple_link' ){
				$taxonomy     = 'simple_link_category';
				$taxonomy_obj = get_taxonomy( $taxonomy );

				if( ! isset( $_GET[ 'simple_link_category' ] ) ){
					$_GET[ 'simple_link_category' ] = null;
				}

				wp_dropdown_categories( array(
					'show_option_all' => sprintf( __( 'Show All %s', 'simple-links' ), $taxonomy_obj->label ),
					'taxonomy'        => 'simple_link_category',
					'name'            => 'simple_link_category',
					'orderby'         => 'name',
					'selected'        => $_GET[ 'simple_link_category' ],
					'hierarchical'    => true,
					'depth'           => 3,
					'show_count'      => true,
					'hide_empty'      => true,
				) );
			}

		}


		/**
		 * Help
		 *
		 * Generates all contextual help screens for this plugin
		 *
		 *
		 * @uses Called at load by __construct
		 *
		 */
		function help(){
			$shortcode_help = array(
				'id'      => 'simple-links-shortcode',
				'title'   => 'Simple Links Shortcode',
				'content' => '<h5>' . __( 'You Can add a Simple Links List  to content using the shortcode', 'simple-links' ) . '[simple-links]</h5>
                        <p>
                            <em>' . __( 'Look for the puzzle button on the content editors for a form that generates the shortcode for you', 'simple-links' ) . '</em>
						</p>
						<p>
							' . __( "You may use a few or many of the options as you would like. To use all defaults just enter [simple-links]", 'simple-links' ) . '
						</p>
                        <strong>' . __( 'Supported Options', 'simple-links' ) . ':</strong><br>
                        category   = "' . __( 'Comma separated list of Link Category Names or Ids - defaults to all', 'simple-links' ) . '"<br>
                        include_child_categories = "' . __( "true of false - to include links from your selected categories' child categories as well as links from your selected categories - defaults to false", 'simple-links' ) . '"<br>
                        orderby    = "' . __( 'title, random, or date - defaults to link order', 'simple-links' ) . '"<br>
                        order      = "' . __( 'DESC or ASC - defaults to ASC', 'simple-links' ) . '"<br>
                        count      = "' . __( 'Number of links to show', 'simple-links' ) . '"<br>
                        show_image = "' . __( "true or false - to show the link's image or not", 'simple-links' ) . '"<br>
                        show_image_only = "' . __( "true or false - to show the link's image without the title under it. If show image is not true this does nothing", 'simple-links' ) . '"<br>
                        image_size = "' . __( 'Any size built into WordPress or your theme" - default to thumbnail', 'simple-links' ) . '"<br>
                        remove_line_break =  ' . __("true or false - Remove Line Break Between Images and Links - default to false", 'simple-links' ) . ' <br>
                        fields     = "' . __( "Comma separated list of the Link's Additional Fields to show", 'simple-links' ) . '"<br>
                        description = "' . __( 'true or false" - to show the description - defaults to false', 'simple-links' ) . '"<br>
                        show_description_formatting = "' . __( 'true or false - to display paragraphs format to match the editor content - defaults to false', 'simple-links' ) . '"<br>
                        separator   = "' . __( 'Any characters to display between fields and description - defaults to ', 'simple-links' ) . '\'-\'"<br>
                        id          = "' . __( 'An optional id for the list', 'simple-links' ) . '"
                        <p>
                             e.g. [simple-links show_image="true" image_size="medium" count="12"]
                        </p>'

			);

			//help for the widgets
			$widget_help = array(
				'id'      => 'simple-links-widget',
				'title'   => 'Simple Links Widget',
				'content' => '<h5>' . __( 'You May Add as Many Simple Links Widgets as You Would Like to Your Widget Areas', 'simple-links' ) . '</h5>
                                    <strong>' . __( 'Widget Options', 'simple-links' ) . ':</strong><br>
                                ' . __( 'Categories', 'simple-links' ) . ' = "' . __( 'Select with link categories to pull from', 'simple-links' ) . '"<br>
                               ' . __( 'Include Child Categories Of Selected Categories', 'simple-links' ) . ' = "' . __( "If checked, links from your selected categories' child categories will display as well as links from your selected categories", 'simple-links', 'simple-links' ) . '"<br>
                                ' . __( 'Order Links By', 'simple-links' ) . '   = "' . __( 'The Order in Which the Links will Display - defaults to link order', 'simple-links' ) . '"<br>
                                ' . __( 'Order', 'simple-links' ) . ' = "' . __( 'The Order in which the links will Display', 'simple-links' ) . '"<br>
                                ' . __( 'Show Description', 'simple-links' ) . ' = "' . __( "Display the Link's Description", 'simple-links' ) . '<br>
                                ' . __( 'Show Description Formatting', 'simple-links' ) . ' = "' . __( 'Display paragraphs to match the editor content', 'simple-links' ) . '"<br>
                                ' . __( 'Number of LInks', 'simple-links' ) . '  = "' . __( 'Number of links to show', 'simple-links' ) . '"<br>
                                ' . __( 'Show Image', 'simple-links' ) . ' = "' . __( "Check the box to display the Link's Image", 'simple-links' ) . '"<br>
                                ' . __( 'Display Image Without Title', 'simple-links' ) . '       = "' . __( "Check this box display the Link's Image without the Link's title under it. If Show Image is not checked, this will do nothing", 'simple-links' ) . '"<br>
                                ' . __( 'Image Size', 'simple-links' ) . ' = "' . __( 'The Size of Image to Show if the previous box is checked', 'simple-links' ) . '"<br>
                                ' . __( 'Include Additional Fields', 'simple-links' ) . ' = "' . __( "Display values from the Link's Additional Fields", 'simple-links' ) . '"<br>
                                ' . __( 'Field Separator', 'simple-links' ) . '  = "' . __( 'And characters to display between fields and description - defaults to ', 'simple-links' ) . '\'-\'"<br>'
			);


			//The screen we are on
			$screen = get_current_screen();

			if( empty( $screen->id ) ){
				return;
			}

			//Each page will have different help content
			switch ( $screen->id ){

				case 'widgets':
					$screen->add_help_tab( $widget_help );
					break;

				//Normal Pages and posts and widgets - The shortcode help
				case 'page':
				case 'post':

					$screen->add_help_tab( $shortcode_help );
					break;

				//The settings page
				case 'simple_link_page_simple-link-settings':

					$screen->add_help_tab( array(
						'id'      => 'wordpress-links',
						'title'   => 'WordPress Links',
						'content' => '<p>' . __( 'WordPress has deprecated the built in links functionality', 'simple-links' ) . '.<br>
                                        ' . __( 'These settings take care of cleaning up the old WordPress links', 'simple-links' ) . '<br>
                                        ' . __( 'By Checking "Remove WordPress Built in Links", the old Links menu will disappear along with the add new admin bar link', 'simple-links' ) . '. <br>
                                        ' . __( ' Pressing the "Import Links" button will automatically copy the WordPress Links into Simple Links. Keep in mind if you press this button twice it will copy the links twice and you will have duplicates', 'simple-links' ) . '.</p>'


					) );


					$screen->add_help_tab( array(
						'id'      => 'additional_fields',
						'title'   => __( 'Additional Fields', 'simple-links' ),
						'content' => '<p>' . __( 'You have the ability to add an unlimited number of additional fields to the links by click the "Add Another" button', 'simple-links' ) . '. <br>
                                            ' . __( "Once you save your changes, these fields will show up on a each link's editing page", 'simple-links' ) . '. <br>
                                            ' . __( 'You will have the ability to select any of these fields to display using the Simple Links widgets', 'simple-links' ) . '. <br>
                                            ' . __( "Each widget gets it's own list of ALL the additional fields, so you may display different fields in different widget areas", 'simple-links' ) . '. <br>
                                            ' . __( 'These fields will also be available by using the shortcode. For instance, if you wanted to display a field titled "author" and a field titled "notes" you shortcode would look something like this', 'simple-links' ) . '
                                            <br>[simple-links fields="' . __( 'author,notes', 'simple-links' ) . '" ]</p>',
					) );


					$screen->add_help_tab( array(
						'id'      => 'permissions',
						'title'   => __( 'Permissions', 'simple-links' ),
						'content' => '<p><strong>' . __( 'This is where you decide how much access editors will have', 'simple-links' ) . '</strong><br>
                        ' . __( '"Hide Link Ordering from Editors", will prevent editors from using the drag and drop ordering page. They will still be able to change the order on the individual link editing Pages', 'simple-links' ) . '.<br>
                        ' . __( '"Show Simple Link Settings to Editors" will allow editors to access the screen you are on right now without restriction', 'simple-links' ) . '.</p>',
					) );

					$screen->add_help_tab( array(
						'id'      => 'crockpot-recipe',
						'title'   => 'Crock-Pot Recipe',
						'content' => '<p>For folks out the like me that rarely have time to leave the computer and cook, a Crock-Pot meal is a great way
                        to have food hot and ready to eat.
                        </p>
                        <p><strong>Here is one of my favorites recipes "Carne Rellenos"</strong><br>
                        1 can (4 ounces) whole green chilies, drained<br>
                        4 ounces cream cheese, softened<br>
                        1 flank steak (about 2 pounds)<br>
                        1.5 cups salsa verde<br>
                        Slit whole chiles open on one side with sharp knife; stuff with cream cheese. Open steak flat on sheet of waxed paper; score
                        steak and turn over. Lay stuffed chiles across unscored side of steak. Roll up and tie with kitchen string. Place steak in Crock Pot
                        ;pour in salsa. Cover; cook on LOW 6 to 8 hours or on HIGH 3 to 4 hours or until done. Remove stead and cut into 6 pieces. Serve
                        with sauce.</p>'
					) );

					$screen->add_help_tab( $shortcode_help );

					$screen->set_help_sidebar(
						'<p>' .  __( 'These Sections will give your a brief description of what each group of settings does. Feel free to start a thread on the support forums if you would like additional help items covered in this section', 'simple-links' ) . '</p>' );

					break;


			}

		}


		/**
		 * Adds the button to the editor for the shorcode
		 *
		 * @since   8/19/12
		 * @uses    called by init in __construct()
		 * @package mce
		 * @uses    There are a couple methods that had to be called from outside the
		 */
		function mce_button(){

			add_filter( "mce_external_plugins", array( $this, 'button_js' ) );
			add_filter( 'mce_buttons_2', array( $this, 'button' ) );

		}


		/**
		 * Attached the plugins js to the mce button
		 *
		 * @since 8/19/12
		 * @uses  called by mce_button()
		 */
		function button_js( $plugins ){
			$plugins[ 'simpleLinks' ] = SIMPLE_LINKS_JS_DIR . 'editor_plugin.js';

			return $plugins;

		}


		/**
		 * Adds an MCE button to the editor for the shortcode
		 *
		 * @since 8/19/12
		 * @uses  called by mce_button()
		 */
		function button( $buttons ){

			array_push( $buttons, "|", "simpleLinks" ); //Add the button to the array with a separator first
			return $buttons;

		}


		/**
		 * Creates an Admin Flag to let new uses know where the menu is
		 *
		 * @since 2.10.14
		 *
		 * @uses  called in the admin_scripts function
		 *
		 *
		 */
		function pointer_flag(){

			// Get the list of dismissed pointers for the user
			$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );

			// Check whether our pointer has been dismissed
			if( ! in_array( 'simple-links-flag', $dismissed ) ){

				//This is the content that will be displayed
				$pointer_content = '<h3>Simple Links</h3>';
				$pointer_content .= '<p>' . __( 'Manage your Links Here. Enjoy', 'simple-links' ) . '! </p>';


				?>
				<script type="text/javascript">
					//<![CDATA[
					jQuery( document ).ready( function( $ ){

						//The element to point to
						$( '#menu-posts-simple_link' ).pointer( {
							content : ' <?php echo $pointer_content; ?>', position : {
								edge : 'left', align : 'center'
							}, close : function(){
								jQuery.post( ajaxurl, {
									pointer : 'simple-links-flag', action : 'dismiss-wp-pointer'
								} );
							}
						} ).pointer( 'open' );
					} );
					//]]>
				</script>
			<?php
			}
			// Check whether our pointer has been dismissed
			if( ! in_array( 'simple-links-shortcode-flag', $dismissed ) ){

				//This is the content that will be displayed
				$pointer_content = '<h3>Simple Links Shortcode Form</h3>';
				$pointer_content .= '<p>' . __( 'Use this icon to generate a Simple Links shortcode', 'simple-links' ) . '! </p>';

				?>

				<script type="text/javascript">
					//<![CDATA[
					jQuery( document ).ready( function( $ ){
						setTimeout( function(){
							//The element to point to
							$( '#content_simpleLinks' ).pointer( {
								content : ' <?php echo $pointer_content; ?>', position : {
									edge : 'left', align : 'center'
								}, close : function(){
									$.post( ajaxurl, {
										pointer : 'simple-links-shortcode-flag', action : 'dismiss-wp-pointer'
									} );
								}
							} ).pointer( 'open' );
						}, 2000 );
					} );
					//]]>
				</script>
			<?php
			}
		}


		/**
		 * Remove Links
		 *
		 * Removes all traces of the WordPress Built in Links from the Admin
		 *
		 *
		 * @uses added to the map_meta_cap filter by self::__construct()
		 */
		function remove_links( $caps, $cap ){
			if( $cap == 'manage_links' ){
				return array( 'do_not_allow' );
			}

			return $caps;
		}


		/**
		 * Remove Links Widget
		 *
		 * Remove the links widget from the admin
		 *
		 *
		 * @uses added to the init hook by self::__construct()
		 */
		function remove_links_widget(){
			unregister_widget( 'WP_Widget_Links' );
		}


		/**
		 * Imports the Wordpress links into this custom post type
		 *
		 * @since 8/19/12
		 * @uses  called using ajax
		 */
		function import_links(){

			check_ajax_referer( 'simple_links_import_links' ); //Match this to the nonce created on the url
			//Add the categories from the links
			$old_link_cats = get_categories( 'type=link' );
			if( is_array( $old_link_cats ) ){
				foreach( $old_link_cats as $cat ){
					if( ! term_exists( $cat->name, 'simple_link_category' ) ){
						wp_insert_term( $cat->name, 'simple_link_category' );
					}
				}
			}


			//Import Each link
			foreach( get_bookmarks() as $link ){

				$post = array(
					'post_name'   => $link->link_name,
					'post_status' => 'publish',
					'post_title'  => $link->link_name,
					'post_type'   => 'simple_link'
				);

				//Create the new post
				$id = wp_insert_post( $post );

				//Update Existing post data
				update_post_meta( $id, 'description', $link->link_description );
				update_post_meta( $id, 'target', $link->link_target );
				update_post_meta( $id, 'web_address', $link->link_url );


				//Put the post in the old categories
				$terms = get_the_terms( $link->link_id, 'link_category' );
				if( is_array( $terms ) ){
					foreach( $terms as $term ){
						wp_set_object_terms( $id, $term->slug, 'simple_link_category', true );
						// print_r( $term );
					}
				}

			}

		}


		/**
		 * Get Ordering Cap
		 *
		 * Get the capability required to order links
		 *
		 * @return string
		 */
		public function get_ordering_cap(){
			if( get_option( 'sl-hide-ordering', false ) ){
				$cap_for_ordering = apply_filters( 'simple-link-ordering-cap', 'manage_options' );
			} else {
				$cap_for_ordering = apply_filters( 'simple-link-ordering-cap', 'edit_posts' );
			}

			return $cap_for_ordering;
		}


		/**
		 * Create the submenu
		 *
		 * @uses This has built in filters to change the permissions of the link ordering and settings
		 * @uses to change the permissions outside of the dashboard settings setup the filters here
		 *
		 */
		function sub_menu(){

			//The link ordering page
			add_submenu_page(
				'edit.php?post_type=simple_link',
				'simple-link-ordering',
				__( 'Link Ordering', 'simple-links' ),
				$this->get_ordering_cap(),
				'simple-link-ordering',
				array( $this, 'link_ordering_page' )
			);
		}


		/**
		 * The link Ordering Page
		 *
		 * @since 9/11/12
		 */
		function link_ordering_page(){
			echo '<div class="wrap">';
			screen_icon( 'themes' );
			echo '<h2>' . __( 'Keeping Your Links in Order', 'simple-links' ) . '!</h2>';


			//Create the Dropdown for by Category Sorting
			$all_cats = get_terms( 'simple_link_category' );
			if( is_array( $all_cats ) ){
				?>
				<h3><?php _e( 'Select a Link Category to Sort Links in that Category Only ( optional )', 'simple-links' ); ?></br></h3>
				<select id="SL-sort-cat">
				<option value="Xall-catsX"><?php _e( 'All Categories', 'simple-links' ); ?></option>

				<?php
				foreach( $all_cats as $cat ){
					printf( '<option value="%s">%s</option>', $cat->slug, $cat->name );
				}

				echo '</select> <br> <br>';

			} else {
				?>
				<h3><?php _e( 'To Sort by Link Categories, you must Add Some Links to them', 'simple-links' ); ?>.
					<br>
					<a href="/wp-admin/edit-tags.php?taxonomy=simple_link_category&post_type=simple_link"><?php _e( 'Follow Me', 'simple-links' ); ?></a>
				</h3>
			<?php
			}


			//Retrieve all the links
			$links = get_posts( 'post_type=simple_link&orderby=menu_order&order=ASC&numberposts=200' );

			echo '<ul class="draggable-children" id="SL-drag-ordering">';

			#-- Create the items list
			foreach( $links as $link ){
				$cats = '';

				//All Cats Assigned to this
				$all_assigned_cats = get_the_terms( $link->ID, 'simple_link_category' );
				if( ! is_array( $all_assigned_cats ) ){
					$all_assigned_cats = array();
				}

				//Create a sting of cats assigned to this link
				foreach( $all_assigned_cats as $cat ){
					$cats .= ' ' . strtolower( str_replace( ' ', '-', $cat->name ) );
				}


				?>
				<li id="postID-<?php echo $link->ID; ?>" class="<?php echo $cats; ?>">
					<div class="menu-item-handle">
						<span class="item-title"><?php echo $link->post_title ?></span>
					</div>
				</li>

			<?php
			}

			echo '</ul></div><!-- End .wrap -->';
		}


		/**
		 * Changes the image uploader to be more user friendly
		 *
		 * @since 8/15/12
		 */
		function upload_mod(){
			$change = false;

			//Check to see if the uploader should be changed or not
			if( isset( $_GET[ 'type' ] ) && $_GET[ 'type' ] == 'image' ){
				if( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'simple_links' ){
					$change = true;
				}
			}

			if( $change ){
				?>
				<style type="text/css">
					#media-upload-header #sidemenu li #tab-type_url, #media-upload-header #sidemenu li #tab-gallery, #media-items tr.url, #media-items tr .align, #media-items tr.image_alt, #media-items tr.post_title, #media-items tr .image-size, #media-items tr.post_excerpt, #media-items tr.post_content, #media-items tr.image_alt p, #media-items table thead input.button, #media-items table thead img.imgedit-wait-spin, #media-items tr.align, #media-items tr.image-size {
						display : none
					}

					#media-items tr.submit a.wp-post-thumbnail {
						border           : 1px solid #666;
						padding          : 3px 8px;
						border-radius    : 15px;
						text-decoration  : none;
						color            : #dddd;
						background-image : url(/wp-admin/images/white-grad.png)
					}
				</style>
				<script type="text/javascript">
					(function( $ ){
						$( document ).ready( function(){
							$( '#media-items' ).bind( 'DOMNodeInserted', function(){
								var thumbnailLink = $( ".savesend .wp-post-thumbnail" );
								thumbnailLink.html( 'Use This Image' );
								thumbnailLink.addClass( 'target-link' );
								$( 'input[value="Insert into Post"]' ).hide();
							} );
						} );

					})( jQuery );
				</script>
			<?php
			}
		}


		/**
		 * Admin stylesheet
		 */
		function admin_style(){
			wp_enqueue_style(
				apply_filters( 'simple_links_admin_style', 'simple_links_admin_style' ), //The name of the style
				SIMPLE_LINKS_CSS_DIR . 'simple.links.admin.css'
			);

		}


		/**
		 * Add the jquery to the admin
		 */
		function admin_scripts( $post ){

			$url = array(
				'sortURL'        => esc_url( wp_nonce_url( admin_url( 'admin-ajax.php?action=simple_links_sort_children' ), "simple_links_sort_children" ) ),
				'importLinksURL' => esc_url( wp_nonce_url( admin_url( 'admin-ajax.php?action=simple_links_import_links' ), "simple_links_import_links" ) )
			);

			//Add the sortable script
			wp_enqueue_script( 'jquery-ui-sortable' );

			//For the Pointer Flag
			add_action( 'admin_print_footer_scripts', array( $this, 'pointer_flag' ) );
			wp_enqueue_style( 'wp-pointer' );
			wp_enqueue_script( 'wp-pointer' );


			wp_enqueue_script(
				'simple_admin_script',
				SIMPLE_LINKS_JS_DIR . 'simple_links_admin.js',
				array( 'jquery' ), //The scripts this depends on
				'1.0.0' //The Version of your script

			);


			//add and object of the above array to use in the this script
			wp_localize_script( 'simple_admin_script', 'SLajaxURL', $url );

		}


		/**
		 * Edits the menu_order in the database for links
		 *
		 * @since 8/28/12
		 * @return null
		 *
		 */
		function ajax_sort(){

			//print_r( $_POST['postID'] );
			check_ajax_referer( 'simple_links_sort_children' ); //Match this to the nonce created on the url
			global $wpdb;
			foreach( $_POST[ 'postID' ] as $order => $postID ){
				$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET menu_order = %d WHERE ID = %d", $order, $postID ) );
			}
			die();

		}


		/**
		 * View the info of the current admin screen
		 *
		 * @uses for development purposes only
		 */
		function check_current_screen(){
			add_action( 'admin_notices', array( $this, 'check_current_screen' ) );
			if( ! is_admin() ){
				return;
			}
			global $current_screen;
			//print_r( $current_screen );
		}

	}
}