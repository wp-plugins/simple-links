<?php 

                       /**
                        * Misc Functions for the Simple Links Plugin
                        * @author Mat Lipe <mat@matlipe.com>
                        * @since 9.16.13
                        */

/**
 * Creates a question mark icon of the tooltips
 * @param string $id used to select with jquery
 * @since 8/19/12
 */
function simple_links_questions( $id ){
    printf('<img src="%squestion.png" id="%s">', SIMPLE_LINKS_IMG_DIR, $id );
}