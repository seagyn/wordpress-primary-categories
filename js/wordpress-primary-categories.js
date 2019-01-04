jQuery( function ( $ ) {
	var postId = parseInt( $( '#post_ID' ).val() );

	/*
	 * We cannot do anything until we have a post ID.
	 * We will wait for that to happen (save draft or publish).
	 */
	if ( 'number' === typeof postId ) {
		$( '#categorychecklist' ).find( 'input:checked' ).each( function ( index, el ) {
			var category = {
				'id': $(el).val(),
				'name': $(el).siblings('label').text()
			};

			if ( category.id !== wpc_data.primary_category_id ) {
				$( el ).parents('li').append('<a href="#" class="wpc-primary-selector" title="' + wpc_data.link_title + '" data-category-id="' + category.id + '">' + wpc_data.label + '</a>'); // TODO: Add category name in title.
			}
		} );

		$( '#categorychecklist' ).find( 'input' ).on( 'change', function( e ) {
			var category = {
				'id': $(e.currentTarget).val(),
				'name': $(e.currentTarget).siblings('label').text()
			};
			var isPrimary = false;

			if ( category.id !== wpc_data.primary_category_id ) {
				togglePrimarySelection( e.currentTarget, category );
			}
		} );

		$( '#categorychecklist' ).on( 'click', '.wpc-primary-selector', function( e ) {
			e.preventDefault();

			var el = $(this);

			el.fadeOut();

			var postData = {
				'action': 'set_primary_category',
				'category_id': $(this).data('category-id'),
				'nonce': wpc_data.nonce,
				'old_category_id': wpc_data.primary_category_id, // passing the current id to keep post_meta entries nice and small
				'post_id': postId
			};

			/*
			 * Do the AJAX request to update the primary category.
			 *
			 * On failure, flash a message and add the link again.
			 * On success, add a link to the old category (if there was one) and then remove the link on the new category.
			 */
			$.post( ajaxurl, postData).fail( function ( jqXHR ) {
				el.before('<span class="wpc-error">' + jqXHR.responseJSON.data + '</span>');
				$('.wpc-error').fadeOut( 2000, function () {
					el.fadeIn();
					$('.wpc-error').remove();
				} );
			} ).done( function () {
				if ( wpc_data.primary_category_id !== '' ) {
					var old_primary_element =  $( '#in-category-' + wpc_data.primary_category_id );
					var category = {
						'id': wpc_data.primary_category_id,
						'name': $(old_primary_element).siblings('label').text()
					};

					$( old_primary_element ).parents('li').append('<a href="#" class="wpc-primary-selector" title="' + wpc_data.link_title + '" data-category-id="' + category.id + '">' + wpc_data.label + '</a>');
				}
				wpc_data.primary_category_id = el.data('category-id');
				el.remove();
			});
		});
	}
} );

/**
 * Checks to see if primary is checked or not.
 *
 * @since      0.1
 *
 * @param {HTMLInputElement} target Input element that was checked / unchecked.
 * @param {object}           category Category that is being selected.
 * @return void
 */
function togglePrimarySelection ( target, category ) {
	var checked = $(target).attr('checked');
	var el = $(target).parents('li');

	/*
	 * If we are now unchecked, let's remove the link.
	 * If we are now checked, let's add the link.
	 */
	if ( ! checked ) {
		el.find('.wpc-primary-selector').remove();
	} else {
		el.append('<a href="#" class="wpc-primary-selector" title="' + wpc_data.link_title + '" data-category-id="' + category.id + '">' + wpc_data.label + '</a>'); // TODO: Add category name in title.
	}
}
