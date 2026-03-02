<?php

declare( strict_types=1 );

namespace OPM\Src\Coupon;

use OPM\Src\PostTypes\OfferProviderPostType;

/**
 * Meta box for managing up to 3 coupons per Offer Provider.
 */
final class CouponMetaBox {

    private const NONCE_ACTION = 'opm_save_coupons';
    private const NONCE_FIELD  = 'opm_coupons_nonce';
    public  const META_KEY     = '_opm_coupons';
    private const MAX_COUPONS  = 3;

    public function register(): void {
        add_action( 'add_meta_boxes',                                [ $this, 'addMetaBox' ] );
        add_action( 'save_post_' . OfferProviderPostType::POST_TYPE, [ $this, 'saveMetaBox' ] );
    }

    public function addMetaBox(): void {
        add_meta_box(
            'opm_provider_coupons',
            sprintf( __( 'Coupons (max %d)', 'offer-provider' ), self::MAX_COUPONS ),
            [ $this, 'renderMetaBox' ],
            OfferProviderPostType::POST_TYPE,
            'normal',
            'default'
        );
    }

    public function renderMetaBox( \WP_Post $post ): void {
        wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
        $coupons = $this->getCoupons( $post->ID );
        include OPM_PATH . 'templates/admin/meta-coupons.php';
    }

    public function saveMetaBox( int $postId ): void {
        if ( ! $this->canSave( $postId ) ) {
            return;
        }
        $raw     = array_slice( (array) ( $_POST['opm_coupons'] ?? [] ), 0, self::MAX_COUPONS );
        $coupons = array_map( [ $this, 'sanitizeCoupon' ], $raw );
        update_post_meta( $postId, self::META_KEY, $coupons );
    }

    /**
     * Returns all saved coupons padded to MAX_COUPONS slots.
     *
     * @return array<int, array{discount_amount: string, discount_code: string}>
     */
    public function getCoupons( int $postId ): array {
        $saved   = get_post_meta( $postId, self::META_KEY, true );
        $coupons = is_array( $saved ) ? $saved : [];

        for ( $i = count( $coupons ); $i < self::MAX_COUPONS; $i++ ) {
            $coupons[] = [ 'discount_amount' => '', 'discount_code' => '' ];
        }

        return array_slice( $coupons, 0, self::MAX_COUPONS );
    }

    /**
     * Returns only coupons that have a non-empty code.
     *
     * @return array<int, array{discount_amount: string, discount_code: string}>
     */
    public static function getActiveCoupons( int $postId ): array {
        $saved = get_post_meta( $postId, self::META_KEY, true );
        if ( ! is_array( $saved ) ) {
            return [];
        }
        return array_values(
            array_filter( $saved, fn( $c ) => ! empty( $c['discount_code'] ) )
        );
    }

    private function sanitizeCoupon( array $coupon ): array {
        return [
            'discount_amount' => sanitize_text_field( $coupon['discount_amount'] ?? '' ),
            'discount_code'   => sanitize_text_field( $coupon['discount_code'] ?? '' ),
        ];
    }

    private function canSave( int $postId ): bool {
        return isset( $_POST[ self::NONCE_FIELD ] )
            && wp_verify_nonce( $_POST[ self::NONCE_FIELD ], self::NONCE_ACTION )
            && ! ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            && current_user_can( 'edit_post', $postId );
    }
}
