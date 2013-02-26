jQuery(document).ready(function($) {
	$('.file-cabinet-permissions input').click(
		function() {
			if ( $(this).hasClass('file-cabinet-permissions-everyone') == true ) {
				$('.file-cabinet-permissions .not-everyone').attr('checked', false);
				return true;
			}
			if ( $(this).hasClass('not-everyone') == true ) {
				$('.file-cabinet-permissions-everyone').attr('checked', false);
				return true;
			}
			return false;
		}
	);
});
