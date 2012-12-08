jQuery(document).ready(function($) {

	var sel = '.fc-post-type-section input';

	$(sel+'.all').bind(
		'click',
		function() {
			$(sel+'.post-type').prop('checked',false);
		}
	);

	$(sel+'.post-type').bind(
		'click',
		function() {
			$(sel+'.post-type').each(function() {
				if ( $(this).prop('checked') == true ) {
					$(sel+'.all').prop('checked',false);
				}
			});
		}
	);

});