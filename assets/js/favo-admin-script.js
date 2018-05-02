jQuery(function($){
	$('.favo_image_upload').on('click', function( event ){
		// Uploading files
		var file_frame;
		var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id
		var set_to_post_id = $(this).data("values");
		var set_to_post_ids = $(this).data("ids");

		event.preventDefault();
		// If the media frame already exists, reopen it.
		if ( file_frame ) {
			// Set the post ID to what we want
			file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
			// Open frame
			file_frame.open();
			return;
		} else {
			// Set the wp.media post id so the uploader grabs the ID we want when initialised
			wp.media.model.settings.post.id = set_to_post_id;
		}
		wp.media.model.settings.post.args = set_to_post_ids;

		// Create the media frame.
		file_frame = wp.media.frames.file_frame = wp.media({
			title: 'Select a image to upload',
			button: {
				text: 'Use this image',
			},
			multiple: false	// Set to true to allow multiple files to be selected
		});
		// When an image is selected, run a callback.
		file_frame.on( 'select', function() {
			var ids = wp.media.model.settings.post.args;
			// We set multiple to false so only get one image from the uploader
			attachment = file_frame.state().get('selection').first().toJSON();
			// Do something with attachment.id and/or attachment.url here
			$( '#preview-' + ids ).attr( 'src', attachment.url ).css( 'width', 'auto' );
			$( '#' + ids ).val( attachment.id );
			// Restore the main post ID
			wp.media.model.settings.post.id = wp_media_post_id;
		});
			// Finally, open the modal
			file_frame.open();
	});
	// Restore the main ID when the add media button is pressed
	$( 'a.add_media' ).on( 'click', function() {
		wp.media.model.settings.post.id = wp_media_post_id;
	});

	function favo_change_type( type ) {
		var text = $("#favo_type_selected_text");
		var image = $("#favo_type_selected_image");
		if ( type == 'text' ) {
			text.show();
			image.hide();
		} else if ( type == 'image' ) {
			text.hide();
			image.show();
		}
	}

	favo_change_type( $("#favo_type").val() );

	$("#favo_type").on("change", function(){
		favo_change_type( $(this).val() );
	});


	$(".favo-menu li a").on("click", function(){
		favo_change_section( $(this).attr('href') );
	});

	if ( $(location).attr('hash') ) {
		favo_change_section( $(location).attr('hash') );
	} else {
		favo_change_section( '#tab-general' );
	}

	function favo_change_section( id ) {
		$(".favo-menu li a").removeClass("active");

		$(".favo-menu li a[href=\""+id+"\"]").addClass("active");
		$("#favo-setting .form-section").hide();
		$("#favo-setting "+id).show();
	}

	$(".my-color-field").wpColorPicker();

});
