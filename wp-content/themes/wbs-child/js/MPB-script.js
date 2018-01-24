(function($) {

	$( '#header-menu' ).children().last().click(function () {

		$( '.dropdown-menu' ).toggleClass( 'visible' );
	});

	if ( ! $( '#header-menu li' ).hasClass( 'active' ) ) {

		$( '#header-menu .signup' ).addClass( 'active' );
	}

	$( '#header-menu li' ).click(function () {

		$( '#header-menu .signup' ).removeClass( 'active' );
		$( this ).addClass( "active" );
	});

	$( '#footer-menu .signin' ).click(function () {

		$( '#header-menu .signin' ).addClass( "active" );
	});
})( jQuery );