jQuery(function($) {

	//	Listen Click
	$(".wen-ic-refresh-media").click(function(e) {

		//	This Action
		var $thisAction = $(this);

		//	Attachment ID
		var attachmentID = $(this).data('id');

		//	Confirm
		if( confirm(WEN_IMAGE_CLEANER_INFO.i18n.confirm_refresh_thumbnails) ) {

			//	Run Ajax
			jQuery.ajax({
				data: {
					attachment_id: attachmentID,
					action: 'wen_image_cleaner_refresh_media'
				},
				type: 'POST',
				url: WEN_IMAGE_CLEANER_INFO.ajax_url,
				beforeSend: function() {

					//	Create the Loading Bar
					var $lBar = $('<div class="wen-image-cleaner-loading">' + WEN_IMAGE_CLEANER_INFO.i18n.processing + '...</div>');

					//	Assign CSS
					$lBar.css({
						position: 'absolute',
						top: 0,
						left: 0,
						width: '100%',
						height: '100%',
						overflow: 'hidden',
						background: 'rgba(0, 0, 0, 0.8)',
						color: '#FFF',
						'text-align': 'center',
						'font-weight': 'bold',
						'padding-top': '5.5%'
					});

					//	Add the Loading Bar
					$thisAction.parent().parent().parent().append($lBar);

					//	Set TD Relative
					$thisAction.parent().parent().parent().css('position', 'relative');
				},
				success: function(response) {

					//	Set Message
					$thisAction.parent().parent().parent().find('.wen-image-cleaner-loading').html( (response.success ? 'Success: ' : 'Error: ') + response.message );
				},
				complete: function() {

					//	Set Timeout
					setTimeout(function() {

						//	Remove Loading Bar
						$thisAction.parent().parent().parent().find('.wen-image-cleaner-loading').fadeOut(500, function() {
							$(this).remove();
						});
					}, 1500);
				}
			});
		}

		//	Prevent Default
		e.preventDefault();
		return false;
	});
});