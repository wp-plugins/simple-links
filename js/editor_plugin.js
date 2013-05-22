
        /**               
         * The MCE plugin to add Simple LInks Shortcodes
         * @author Mat Lipe <mat@lipeimagination.info>
         * @since 10.30.12
         */

(function(){
	tinymce.create('tinymce.plugins.simpleLinks',{
		init: function( ed, url ){
			ed.addButton('simpleLinks',{           //The buttons name and title and icon
				title : 'Add Simple Links',
				image : url+'/../img/mce-icon.png',

				cmd: 'mceHighlight' //Match the addCommand
			});
			// Register commands
			ed.addCommand('mceHighlight', function() {
				ed.windowManager.open({
					file : userSettings.url+'?simple_links_shortcode=form',
					width : 500 + parseInt(ed.getLang('highlight.delta_width', 0)),
					height : 750 + parseInt(ed.getLang('highlight.delta_height', 0)),
					inline : 1,
					title  : 'Simple Links Shortcode'
				}, {
		
					plugin_url : url
	
				});
	
		  });
		},
		createControl : function(n, cm){
			return null;
		},
		getInfo : function(){  //The plugin Buttons Details
			return{
				longname : 'Simple Links Shortcode Generator',
				author: 'Mat Lipe',
				authorurl: 'http://lipeimagination.info',
				inforurl : 'http://lipeimagination.info',
				version : '1.0'
			};
		}
	});
	tinymce.PluginManager.add('simpleLinks', tinymce.plugins.simpleLinks);  //Name it the same as above
})();