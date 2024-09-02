/**
 * Block type frontend script definition.
 * It will be enqueued both in the editor and when viewing the content on the front of the site.
 */

/* global jQuery */

import './style.scss';

const main = async () => {
	/* SLIDE UP */
	function slideUp( target ) {
		if ( jQuery( target ).parent().hasClass( 'c-block-accordion-item--active' ) ) {
			jQuery( target ).parent().removeClass( 'c-block-accordion-item--opened' );
			jQuery( target ).css( 'height', '0' );
			jQuery( target ).on( 'transitionend', function() {
				jQuery( target ).removeAttr( 'style' );
				jQuery( target ).parent().removeClass( 'c-block-accordion-item--active' );
				jQuery( target ).removeClass( 'c-block-accordion-item__content--active' );
			} );
		}
	}

	/* SLIDE DOWN */
	function slideDown( target ) {
		jQuery( target ).addClass( 'c-block-accordion-item__content--active' );
		jQuery( target ).parent().addClass( 'c-block-accordion-item--active' );
		jQuery( target ).parent().addClass( 'c-block-accordion-item--opened' );
		jQuery( target ).css( 'height', '100%' );
	}

	function handleAccordionEvent( title ) {
		if ( jQuery( title ).parent().hasClass( 'c-block-accordion-item--active' ) ) {
			slideUp( jQuery( title ).next() );
		} else {
			slideDown( jQuery( title ).next() );
		}
	}

	jQuery( '.c-block-accordion-wrapper' ).each( function( index, accordion ) {
		jQuery( accordion ).find( '.c-block-accordion-item__header' ).each( function( index1, title ) {
			if ( jQuery( title ).parent().hasClass( 'c-block-accordion-item--active' ) ) {
				slideDown( title.nextElementSibling );
			}
		} );
	} );

	jQuery( document ).on( 'click', '.c-block-accordion-item__header', function() {
		handleAccordionEvent( this );
	} );
};

if ( 'loading' === document.readyState ) {
	// The DOM has not yet been loaded.
	document.addEventListener( 'DOMContentLoaded', main );
} else {
	// The DOM has already been loaded.
	main();
}
