( function() {
	// Get the fields for handling sticky status.
	const stickyFields = document.querySelectorAll(
		'.pfmc-inline-edit-sticky'
	);

	// Create a copy of the core WP inline edit post function.
	const pfmcInlineEdit = window.inlineEditPost.edit;

	// Move all sticky checkboxes and dropdowns to a more "core WP" location.
	stickyFields.forEach( ( field ) => {
		const column = field.previousElementSibling;
		const group = column.querySelectorAll( '.inline-edit-group' );
		const lastGroup = group[ group.length - 1 ];

		lastGroup.appendChild( field );
	} );

	// Hide the "Sticky" checkbox from the screen options Columns group.
	// The "Sticky" column itself is hidden with CSS.
	document.getElementById( 'sticky-hide' ).parentElement.remove();

	/**
	 * Sets the `checked` attribute for quick edit checkboxes, if appropriate.
	 *
	 * @param {number|Object} id The id of the clicked post or an element within a post
	 *                           table row.
	 */
	window.inlineEditPost.edit = function( id ) {
		// Merge arguments of the core WP inline edit post function.
		pfmcInlineEdit.apply( this, arguments );

		if ( typeof id === 'object' ) {
			id = parseInt( this.getId( id ) );
		}

		if ( id > 0 ) {
			const editRow = document.getElementById( `edit-${ id }` );
			const postRow = document.getElementById( `post-${ id }` );

			// The post is sticky if it's title column containst the sticky flag.
			if ( postRow.querySelector( '.column-title .post-state' ) ) {
				editRow.querySelector( '#pfmc-sticky-quick' ).checked = true;
			}
		}
	};

	/**
	 * Handles sticky status changes from the bulk edit interface.
	 */
	document.addEventListener( 'click', ( event ) => {
		// Return early if the click wasn't on the bulk edit "Submit" button.
		if ( 'bulk_edit' !== event.target.id ) {
			return;
		}

		// Get the value of the "Sticky" dropdown.
		const sticky = document.getElementById( 'pfmc-sticky-bulk' ).value;

		// Return early if the "No Change" option of the "Sticky" dropdown is selected.
		if ( '' === sticky ) {
			return;
		}

		const nonce = document.getElementById( 'pfmc-sticky-bulk-nonce' ).value;
		const posts = document.getElementById( 'bulk-titles' ).children;

		// Initialize a new FormData object for capturing request data.
		const params = new FormData();

		// Initialize an array for capturing IDs of the selected posts.
		const postIds = new Array();

		// Get ids of the posts being edited.
		Array.from( posts ).forEach( ( title ) => {
			postIds.push( title.id.replace( 'ttle', '' ) );
		} );

		// Add data to send with the body of the request.
		params.append( 'action', 'save_bulk_edit_sticky_status' );
		params.append( 'post_ids', postIds );
		params.append( 'sticky', sticky );
		params.append( 'nonce', nonce );

		// Send an AJAX request to save the sticky status change.
		fetch( window.ajaxurl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: new Headers( {
				'Content-Type': 'application/x-www-form-urlencoded',
			} ),
			body: new URLSearchParams( params ),
		} );
	} );
} )();
