jQuery(document).ready(function() {

jQuery(".do_code").click(function() {
    var id = jQuery(this).attr("var");
	var sc = '[fc_file id=' + id + ']';
	parent.tinyMCE.activeEditor.setContent(parent.tinyMCE.activeEditor.getContent() + sc);
});

//create cat
jQuery('.new_cat_name').hide();
	jQuery('#select_cat').click(function(){		
		var option = jQuery("#select_cat option:selected").val();
			if (option == 'new_cat'){
				jQuery('.new_cat_name').show();
			}else{
				jQuery('.new_cat_name').hide();
			}
	})

/*
jQuery('#check-everyone').click(function(){
    if (jQuery('#check-everyone').attr('checked')) {
		jQuery('#note-normal').hide();
        jQuery('.checkbox').after('<tr id="note-everyone" style="color: #FF0000;"><th></th><td>Notice: When a category is marked as "Everyone" the category permission will strictly be set to "Everyone". You may still set individual files to a specific permission to create private files within a public category.</td></tr>');
    }else{
		jQuery('#note-everyone').hide();
		jQuery('#note-normal').show();		
	}
})
*/


//custom code for upload/remote file url

//hide divs
jQuery('#upload_div').hide();
jQuery('#remote_div').hide();
jQuery('#vimeo_div').hide();
jQuery('#youtube_div').hide();
jQuery('#video_options').hide();




//disable inputs
jQuery('#files').prop('disabled', true);
jQuery('#remote_url').prop('disabled', true);
jQuery('#youtube_url').prop('disabled', true);
jQuery('#vimeo_url').prop('disabled', true);



//file upload
jQuery('#new_upload').click(function() {

//show file div and enable input
jQuery('#files').prop('disabled', false);
jQuery('#upload_div').show();

//hide remote url div and disable input
jQuery('#remote_div').hide();
jQuery('#vimeo_div').hide();
jQuery('#youtube_div').hide();
jQuery('#video_options').hide();
jQuery('#remote_url').prop('disabled', true);
jQuery('#youtube_url').prop('disabled', true);
jQuery('#vimeo_url').prop('disabled', true);
});



//remote url entry
jQuery('#remote').click(function() {


//show remote div and enable remote input
jQuery('#remote_div').show();
jQuery('#remote_url').prop('disabled', false);

//hide file upload div and disable file input
jQuery('#upload_div').hide();
jQuery('#vimeo_div').hide();
jQuery('#youtube_div').hide();
jQuery('#video_options').hide();
jQuery('#files').prop('disabled', true);
jQuery('#youtube_url').prop('disabled', true);
jQuery('#vimeo_url').prop('disabled', true);
});


//vimeo url entry
jQuery('#vimeo_button').click(function() {


//show remote div and enable remote input
jQuery('#vimeo_div').show();
jQuery('#vimeo_url').prop('disabled', false);
jQuery('#video_options').show();

//hide file upload div and disable file input
jQuery('#upload_div').hide();
jQuery('#remote_div').hide();
jQuery('#youtube_div').hide();
jQuery('#files').prop('disabled', true);
jQuery('#remote_url').prop('disabled', true);
jQuery('#youtube_url').prop('disabled', true);
});


//YouTube url entry
jQuery('#youtube_button').click(function() {


//show remote div and enable remote input
jQuery('#youtube_div').show();
jQuery('#youtube_url').prop('disabled', false);
jQuery('#video_options').show();

//hide file upload div and disable file input
jQuery('#upload_div').hide();
jQuery('#remote_div').hide();
jQuery('#vimeo_div').hide();
jQuery('#files').prop('disabled', true);
jQuery('#remote_url').prop('disabled', true);
jQuery('#vimeo_url').prop('disabled', true);
});


 
jQuery('#upload_image_button').click(function() {
											  window.send_to_editor = function(html) {
 imgurl = jQuery('img',html).attr('src');
 jQuery('#upload_image').val(imgurl);
 tb_remove();
 
 
}
 
 
 tb_show('', 'media-upload.php?post_id=1&amp;type=image&amp;TB_iframe=true');
 return false;
});
 
 
});
 
jQuery(document).ready(function() {
 
jQuery('#upload_image_button2').click(function() {
											   window.send_to_editor = function(html) {
 imgurl = jQuery('img',html).attr('src');
 jQuery('#upload_image2').val(imgurl);
 tb_remove();
 
 
}
 
 tb_show('', 'media-upload.php?post_id=1&amp;type=image&amp;TB_iframe=true');
 return false;
});
 


jQuery('.embedpreview').popupWindow({
centerScreen:1
}); 
 
});


// all script below is from a jquery plugin that does popups... can be moved to separate file if needed.
(function($){ 		  
	$.fn.popupWindow = function(instanceSettings){
		
		return this.each(function(){
		
		$(this).click(function(){
		
		$.fn.popupWindow.defaultSettings = {
			centerBrowser:0, // center window over browser window? {1 (YES) or 0 (NO)}. overrides top and left
			centerScreen:0, // center window over entire screen? {1 (YES) or 0 (NO)}. overrides top and left
			height:500, // sets the height in pixels of the window.
			left:0, // left position when the window appears.
			location:0, // determines whether the address bar is displayed {1 (YES) or 0 (NO)}.
			menubar:0, // determines whether the menu bar is displayed {1 (YES) or 0 (NO)}.
			resizable:0, // whether the window can be resized {1 (YES) or 0 (NO)}. Can also be overloaded using resizable.
			scrollbars:0, // determines whether scrollbars appear on the window {1 (YES) or 0 (NO)}.
			status:0, // whether a status line appears at the bottom of the window {1 (YES) or 0 (NO)}.
			width:500, // sets the width in pixels of the window.
			windowName:null, // name of window set from the name attribute of the element that invokes the click
			windowURL:null, // url used for the popup
			top:0, // top position when the window appears.
			toolbar:0 // determines whether a toolbar (includes the forward and back buttons) is displayed {1 (YES) or 0 (NO)}.
		};
		
		settings = $.extend({}, $.fn.popupWindow.defaultSettings, instanceSettings || {});
		
		var windowFeatures =    'height=' + settings.height +
								',width=' + settings.width +
								',toolbar=' + settings.toolbar +
								',scrollbars=' + settings.scrollbars +
								',status=' + settings.status + 
								',resizable=' + settings.resizable +
								',location=' + settings.location +
								',menuBar=' + settings.menubar;

				settings.windowName = this.name || settings.windowName;
				settings.windowURL = this.href || settings.windowURL;
				var centeredY,centeredX;
			
				if(settings.centerBrowser){
						
					if ($.browser.msie) {//hacked together for IE browsers
						centeredY = (window.screenTop - 120) + ((((document.documentElement.clientHeight + 120)/2) - (settings.height/2)));
						centeredX = window.screenLeft + ((((document.body.offsetWidth + 20)/2) - (settings.width/2)));
					}else{
						centeredY = window.screenY + (((window.outerHeight/2) - (settings.height/2)));
						centeredX = window.screenX + (((window.outerWidth/2) - (settings.width/2)));
					}
					window.open(settings.windowURL, settings.windowName, windowFeatures+',left=' + centeredX +',top=' + centeredY).focus();
				}else if(settings.centerScreen){
					centeredY = (screen.height - settings.height)/2;
					centeredX = (screen.width - settings.width)/2;
					window.open(settings.windowURL, settings.windowName, windowFeatures+',left=' + centeredX +',top=' + centeredY).focus();
				}else{
					window.open(settings.windowURL, settings.windowName, windowFeatures+',left=' + settings.left +',top=' + settings.top).focus();	
				}
				return false;
			});
			
		});	
	};
})(jQuery);
