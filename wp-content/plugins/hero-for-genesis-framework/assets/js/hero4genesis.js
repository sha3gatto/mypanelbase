(function( $, window, document, undefined ) {
	if( $('.hero4genesis-inner').length > 0 ){
		$('.hero4genesis-inner').each(function() {
		  w = $(this).parent('.site-inner').width();
		  $(this).css({ 'min-width' : w + 'px' });
		});
	}

	if( $('.hero4genesis-full').length > 0 ){
		windowH = $(window).height();
		$('.hero4genesis-full').each(function() {
		  h = $( this ).height();
		  $( this ).css({ 'min-height' : windowH + 'px' });
		  $(this).find('.hero4genesis-inner').css({ 'height': h +'px' });
		});
	}
	if( $('.hero4genesis-behind').length > 0 ){
		// var scrollTop     = $(window).scrollTop();
		$('.hero4genesis-behind').each(function() {
			elementOffset = $( this ).offset().top;
	    	// distance      = ( elementOffset - scrollTop );
		  	$(this).find('.hero4genesis-inner').css({ 'padding-top': elementOffset +'px', });
		  	$(this).css({ 'margin-bottom': -elementOffset + 'px', 'top': -elementOffset + 'px' });
		});
	}

})( jQuery, window, document );