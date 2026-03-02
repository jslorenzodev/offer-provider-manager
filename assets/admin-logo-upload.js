/**
 * Offer Provider Manager — Logo Upload Handler
 * Uses the WordPress Media Library uploader (wp.media).
 */
( function ( $ ) {

    'use strict';

    var mediaUploader;

    // ── Open Media Library ──────────────────────────────────
    $( '#opm-upload-logo' ).on( 'click', function ( e ) {
        e.preventDefault();

        // Reuse existing uploader instance if already created
        if ( mediaUploader ) {
            mediaUploader.open();
            return;
        }

        // Create a new media frame
        mediaUploader = wp.media( {
            title:    opmLogo.title,
            button:   { text: opmLogo.button },
            multiple: false,
            library:  { type: 'image' }   // images only
        } );

        // On image selected
        mediaUploader.on( 'select', function () {
            var attachment = mediaUploader
                .state()
                .get( 'selection' )
                .first()
                .toJSON();

            // Set the hidden input value (attachment ID)
            $( '#opm_logo_id' ).val( attachment.id );

            // Show preview image
            var previewUrl = attachment.sizes && attachment.sizes.medium
                ? attachment.sizes.medium.url
                : attachment.url;

            $( '#opm-logo-placeholder' ).hide();

            var $preview = $( '#opm-logo-preview' );
            if ( $preview.length ) {
                $preview.attr( 'src', previewUrl ).show();
            } else {
                $( '#opm-logo-preview-box' ).append(
                    $( '<img>' )
                        .attr( { src: previewUrl, id: 'opm-logo-preview', alt: 'Logo' } )
                );
            }

            // Show the remove button
            $( '#opm-remove-logo' ).show();
        } );

        mediaUploader.open();
    } );

    // ── Remove Logo ─────────────────────────────────────────
    $( '#opm-remove-logo' ).on( 'click', function ( e ) {
        e.preventDefault();

        // Clear hidden input
        $( '#opm_logo_id' ).val( '' );

        // Remove preview image
        $( '#opm-logo-preview' ).remove();

        // Show placeholder
        $( '#opm-logo-placeholder' ).show();

        // Hide remove button
        $( this ).hide();

        // Reset uploader so a new selection can be made cleanly
        mediaUploader = null;
    } );

} )( jQuery );
