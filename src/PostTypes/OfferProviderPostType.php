<?php

declare( strict_types=1 );

namespace OPM\Src\PostTypes;

/**
 * Registers the 'offer_provider' custom post type.
 */
final class OfferProviderPostType {

    public const POST_TYPE = 'offer_provider';

    public function register(): void {
        add_action( 'init', [ $this, 'registerPostType' ] );
    }

    public function registerPostType(): void {
        register_post_type( self::POST_TYPE, [
            'labels'       => $this->labels(),
            'public'       => true,
            'has_archive'  => true,
            'show_in_menu' => true,
            'menu_icon'    => 'dashicons-store',
            'supports'     => [ 'title', 'thumbnail' ],
            'rewrite'      => [ 'slug' => 'offer-providers' ],
            'show_in_rest' => true,
        ] );
    }

    private function labels(): array {
        return [
            'name'          => __( 'Offer Providers',      'offer-provider' ),
            'singular_name' => __( 'Offer Provider',       'offer-provider' ),
            'add_new_item'  => __( 'Add New Offer Provider','offer-provider' ),
            'edit_item'     => __( 'Edit Offer Provider',  'offer-provider' ),
            'search_items'  => __( 'Search Offer Providers','offer-provider' ),
        ];
    }
}
