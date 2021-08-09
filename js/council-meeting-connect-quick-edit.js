/**
 * Check the "Pin…" checkbox on the Council Meeting Connect
 * term quick edit form if appropriate.
 */
( function() {
	// Create a copy of the WP inline edit tax function.
	const wpInlineEdit = inlineEditTax.edit;

	// Overwrite the function with our own.
	inlineEditTax.edit = function( id ) {

		// Call the original WP edit function.
		wpInlineEdit.apply( this, arguments );

		// Get the term ID.
		const termID = ( typeof( id ) == 'object' ) ? parseInt( this.getId( id ) ) : 0;

		if ( termID > 0 ) {
			if ( document.querySelector( `#tag-${ termID } .column-pinned` ).innerHTML ) {
				document.querySelector( `#edit-${ termID } input[name="_pinned"]` ).setAttribute( 'checked', true );
			}
		}
	};
} )();
