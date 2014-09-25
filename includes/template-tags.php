<?php

/**
 * Misc Functions for the Simple Links Plugin
 *
 * @author Mat Lipe <mat@matlipe.com>
 *
 */

/**
 * Simple Links Questions
 *
 * Creates a question mark icon of the tooltips
 *
 * @param string $id used to select with jquery
 *
 */
function simple_links_questions( $id ){
	printf( ' <img src="%squestion.png" id="%s">', SIMPLE_LINKS_IMG_DIR, $id );

}
