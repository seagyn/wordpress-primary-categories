jQuery( function ( $ ) {
	$( '#categorychecklist' ).find( 'input' ).on( 'change', function( event ) {
		var categoryId = $(event.currentTarget).val();
		var isPrimary = false;

		if ( !isPrimary ) {
			togglePrimarySelection( event.currentTarget );
		}
	} );

	$
} );

/**
 * Checks to see if primary is checked or not.
 *
 * @since      0.1
 *
 * @param {HTMLInputElement} target Input element that was checked / unchecked.
 *
 * @return void
 */
function togglePrimarySelection ( target ) {
	var checked = $(target).attr('checked');
	var el = $(target).parent();

	/*
	 * If we are now unchecked, let's remove the link.
	 * If we are now checked, let's add the link.
	 */
	if ( ! checked ) {
		el.find('.wpc-primary-selector').remove();
	} else {
		el.append('<a href="#" class="wpc-primary-selector">' + wpc_data.label + '</a>');
	}
}
