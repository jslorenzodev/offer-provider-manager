<?php

declare( strict_types=1 );

namespace OPM\Src\Admin;

use OPM\Src\PostTypes\OfferProviderPostType;

/**
 * Meta box for Offer Provider details — Logo upload and Website URL.
 */
final class ProviderMetaBox {

    private const NONCE_ACTION    = 'opm_save_provider';
    private const NONCE_FIELD     = 'opm_provider_nonce';
    private const META_WEBSITE    = '_opm_website_url';
    private const META_LOGO_ID    = '_opm_logo_id';

    public function register(): void {
        add_action( 'add_meta_boxes',                                    [ $this, 'addMetaBox' ] );
        add_action( 'add_meta_boxes',                                    [ $this, 'hideFeaturedImageBox' ] );
        add_action( 'save_post_' . OfferProviderPostType::POST_TYPE,     [ $this, 'saveMetaBox' ] );
        add_action( 'admin_enqueue_scripts',                             [ $this, 'enqueueScripts' ] );
    }

    /**
     * Remove the default Featured Image meta box — replaced by our Logo Upload box.
     */
    public function hideFeaturedImageBox(): void {
        remove_meta_box( 'postimagediv', OfferProviderPostType::POST_TYPE, 'side' );
    }

    public function enqueueScripts( string $hook ): void {
        // Only load on add/edit post screens for our post type
        if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
            return;
        }
        $screen = get_current_screen();
        if ( ! $screen || $screen->post_type !== OfferProviderPostType::POST_TYPE ) {
            return;
        }
        // WordPress media uploader
        wp_enqueue_media();
        wp_enqueue_script(
            'opm-logo-upload',
            OPM_URL . 'assets/admin-logo-upload.js',
            [ 'jquery' ],
            OPM_VERSION,
            true
        );
        wp_localize_script( 'opm-logo-upload', 'opmLogo', [
            'title'  => __( 'Select Provider Logo', 'offer-provider' ),
            'button' => __( 'Use as Logo', 'offer-provider' ),
        ] );
    }

    public function addMetaBox(): void {
        add_meta_box(
            'opm_provider_details',
            __( 'Provider Details', 'offer-provider' ),
            [ $this, 'renderMetaBox' ],
            OfferProviderPostType::POST_TYPE,
            'normal',
            'high'
        );
    }

    public function renderMetaBox( \WP_Post $post ): void {
        wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );

        $website  = (string) get_post_meta( $post->ID, self::META_WEBSITE, true );
        $logoId   = (int)    get_post_meta( $post->ID, self::META_LOGO_ID,  true );
        $logoUrl  = $logoId ? wp_get_attachment_image_url( $logoId, 'medium' ) : '';

        include OPM_PATH . 'templates/admin/meta-provider-details.php';
    }

    public function saveMetaBox( int $postId ): void {
        if ( ! $this->canSave( $postId ) ) {
            return;
        }

        update_post_meta( $postId, self::META_WEBSITE, esc_url_raw( $_POST['opm_website_url'] ?? '' ) );

        $logoId = isset( $_POST['opm_logo_id'] ) ? (int) $_POST['opm_logo_id'] : 0;
        update_post_meta( $postId, self::META_LOGO_ID, $logoId );

        // Also set as featured image for theme compatibility
        if ( $logoId > 0 ) {
            set_post_thumbnail( $postId, $logoId );
        } else {
            delete_post_thumbnail( $postId );
        }
    }

    private function canSave( int $postId ): bool {
        return isset( $_POST[ self::NONCE_FIELD ] )
            && wp_verify_nonce( $_POST[ self::NONCE_FIELD ], self::NONCE_ACTION )
            && ! ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            && current_user_can( 'edit_post', $postId );
    }

    /**
     * Get the logo URL for a provider post — usable from templates.
     */
    public static function getLogoUrl( int $postId, string $size = 'medium' ): string {
        $logoId = (int) get_post_meta( $postId, self::META_LOGO_ID, true );
        if ( $logoId > 0 ) {
            return (string) wp_get_attachment_image_url( $logoId, $size );
        }
        // Fallback to featured image
        $thumbId = get_post_thumbnail_id( $postId );
        if ( $thumbId ) {
            return (string) wp_get_attachment_image_url( $thumbId, $size );
        }
        return '';
    }

    /**
     * Get the logo IMG tag for a provider post.
     */
    public static function getLogoImg( int $postId, string $size = 'medium', string $class = '' ): string {
        $logoId = (int) get_post_meta( $postId, self::META_LOGO_ID, true );
        if ( $logoId > 0 ) {
            return (string) wp_get_attachment_image( $logoId, $size, false, [ 'class' => $class ] );
        }
        $thumbId = get_post_thumbnail_id( $postId );
        if ( $thumbId ) {
            return (string) wp_get_attachment_image( $thumbId, $size, false, [ 'class' => $class ] );
        }
        return '';
    }
}
