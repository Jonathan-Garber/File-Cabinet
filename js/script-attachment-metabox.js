jQuery(document).ready(function($) {
//create cat
//hide stuff to be hidden on page load if new_cat is selected
		var option = $("#select_cat option:selected").val();
			if (option == 'new_cat'){
				$('.new_cat_name').show();
				$('.cat_permissions_label').show();
				$('.file_permissions_label').hide();
			}else{
				$('.new_cat_name').hide();
				$('.cat_permissions_label').hide();
				$('.file_permissions_label').show();				
			}
			
	
	
	//hide and unhide the right parts when dropdown is changed
	var $option = false;
	$('#select_cat').bind(
		'change',
		function () {
			$option = $('#select_cat option:selected').attr('value');
			if ( $option == 'new_cat' ) {
				$('.new_cat_name').show();
				$('.cat_permissions_label').show();
				$('.file_permissions_label').hide();
			}
			else {
				$('.new_cat_name').hide();
				$('.cat_permissions_label').hide();
				$('.file_permissions_label').show();				
			}
		}
	);




	$('.video_width').hide();
	$('.video_height').hide();

	$("input[name='fc_video_settings']").click(function(){		
		var option = $("input[name='fc_video_settings']:checked").val();
			if (option == 'custom'){
				$('.video_width').show();
				$('.video_height').show();
				$('.tsfc-attachment-methods .video-settings input.ti').prop('disabled', false);
			}
			else {
				$('.video_width').hide();
				$('.video_height').hide();
				$('.tsfc-attachment-methods .video-settings input.ti').prop('disabled', true);
			}
	});
	
	

	var file_type = $('#attachment_type').attr('var');
			
	
	$('.tsfc-choose-attachment-method span').hover(
		function () {
			$(this).addClass('hover');
		},
		function() {
			$(this).removeClass('hover');
		}
	);

	$('.tsfc-choose-attachment-method span').click(
		function () {
			$('.tsfc-attachment-methods').removeClass('inactive');
			$('.tsfc-choose-attachment-method').css('border-bottom','none');
			$('.tsfc-attachment-methods .attachment-method, .tsfc-attachment-methods .video-settings').removeClass('active');
			$('.tsfc-attachment-methods input').prop('disabled', true);
			$('.tsfc-attachment-methods .video-settings input.rb').prop('disabled', false);
			$('.tsfc-attachment-methods .video-settings input.ti').prop('disabled', true);
			var $method = $(this).attr('attachmentMethod');
			$('.attachment-method.'+$method).addClass('active');
			$('.attachment-method.'+$method+' input').prop('disabled', false);
			if ( $method == 'youtube' || $method == 'vimeo' ) {
			    
				$('.tsfc-attachment-methods .video-settings').addClass('active');
				$('.tsfc-attachment-methods .video-settings input').prop('disabled', false);
			
			}
			return false;
		}
	);
	
	var $file_type = $('#attachment_type').attr('var');
	$('.tsfc-choose-attachment-method span').each(
		function() {
			$this_method = $(this).attr('attachmentmethod');
			if ( $this_method == $file_type ) {
				$(this).click();
			}
		}
	);
	
	$('.tsfc-attachment-methods .video-settings input.ti').prop('disabled', true);
	$('.tsfc-attachment-methods .video-settings input.rb').prop('disabled', false);

});

jQuery(window).bind("load", function() {

	jQuery('.tsfc-attachment-methods .video-settings input.rb').each(
		function() {
			if ( jQuery(this).attr('checked') == 'checked' ) {
				jQuery(this).click();
			}
		}
	);

});

