( function( data ) {

	const alert   = document.createElement( 'div' );
	const heading = document.createElement( 'h1' );
	const message = document.createElement( 'a' );

	alert.className = 'pfmc-alert';
	alert.classList.add( data.level );

	heading.appendChild( document.createTextNode( data.heading ) );

	message.href = data.url;
	message.appendChild( document.createTextNode( data.content ) );

	alert.appendChild( heading );
	alert.appendChild( message );

	document.body.prepend( alert );

}( pfmcfsAlertData ) );