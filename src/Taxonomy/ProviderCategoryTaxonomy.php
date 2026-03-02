<?php

declare( strict_types=1 );

namespace OPM\Src\Taxonomy;

use OPM\Src\PostTypes\OfferProviderPostType;

/**
 * Registers the 'provider_category' custom taxonomy.
 */
final class ProviderCategoryTaxonomy {

    public const TAXONOMY = 'provider_category';

    public function register(): void {
        add_action( 'init', [ $this, 'registerTaxonomy' ] );
    }

    public function registerTaxonomy(): void {
        register_taxonomy( self::TAXONOMY, OfferProviderPostType::POST_TYPE, [
            'labels'            => $this->labels(),
            'hierarchical'      => true,
            'public'            => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rewrite'           => [ 'slug' => 'provider-category' ],
        ] );
    }

    private function labels(): array {
        return [
            'name'          => __( 'Provider Categories',      'offer-provider' ),
            'singular_name' => __( 'Provider Category',        'offer-provider' ),
            'add_new_item'  => __( 'Add New Provider Category','offer-provider' ),
            'edit_item'     => __( 'Edit Provider Category',   'offer-provider' ),
        ];
    }
}
