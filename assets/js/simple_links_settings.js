/**
 * The js for the Settings page on the Simple Links
 *
 * @author mat lipe <mat@matlipe.com>
 *
 *
 */

jQuery(function($) {
	// close postboxes that should be closed
	$('.if-js-closed').removeClass('if-js-closed').addClass('closed');

	// postboxes setup  These need to be set the handlers (screens) of the post boxes
	postboxes.add_postbox_toggles('sl-settings-boxes');
	
	$('.link_delete_additional').click( function(){
		$(this).parent().remove();
	});

	SLsettingsAjax.init();
	SLsettingsQtips.init();
});

var $s = jQuery.noConflict();

/**
 * Any Ajax Requests for the Settings page
 *
 * @since 8/19/12
 *
 */
var SLsettingsAjax = {
	init : function() {
		//the import links ajax
		$s('#sl-import-links').click(function() {
			SLsettingsAjax.importLinks();
			return false;
		});

	},

	/**
	 * Make the request to import the links
	 *
	 */
	importLinks : function() {

		var data = '';
		$s('#sl-import-loading').show();
		$s.post(SLajaxURL.importLinksURL, data, function(respon) {
			$s('#sl-import-loading').hide();
			$s('#import-links-success').slideDown('slow');
		});

	}
};

/**
 *
 * The Toopltips
 * @since 3.2.14
 *
 *
 */
var SLsettingsQtips = {

	init : function() {

		//The Hide Ordering Option
		$s('#SL-hide-ordering').qtip({
			'content' : 'This will prevent editors from using the drag and drop ordering.',
			style : {
				border : {
					width : 1,
					radius : 8,
					color : '#21759B'
				},
				tip : 'topLeft' // Notice the corner value is identical to the previously mentioned positioning corners
			}
		});

		//The show setting option
		$s('#SL-show-settings').qtip({
			'content' : 'This will allow editors access to this Settings Page.',
			style : {
				border : {
					width : 1,
					radius : 8,
					color : '#21759B'
				},
				tip : 'topLeft' // Notice the corner value is identical to the previously mentioned positioning corners
			}
		});

		//The remove links option
		$s('#SL-remove-links').qtip({
			'content' : 'This will remove all traces of the Wordpres built in links',
			style : {
				border : {
					width : 1,
					radius : 8,
					color : '#21759B'
				},
				tip : 'topLeft' // Notice the corner value is identical to the previously mentioned positioning corners
			}
		});

		//The import links
		$s('#SL-import-links').qtip({
			content : 'This will import all existing Wordpress Links into the Simple Links',
			style : {
				border : {
					width : 1,
					radius : 8,
					color : '#21759B'
				},
				tip : 'topLeft' // Notice the corner value is identical to the previously mentioned positioning corners
			}
		});
	}
};

