<?php

declare( strict_types=1 );

namespace OPM\Src\Frontend;

use OPM\Src\Company\CompanyRepository;
use OPM\Src\Company\CompanyService;
use OPM\Src\PostTypes\OfferProviderPostType;
use OPM\Src\Taxonomy\ProviderCategoryTaxonomy;

/**
 * Frontend — filters the providers archive and loads custom templates.
 */
final class ProviderFrontend {

    private CompanyService $companyService;

    public function __construct() {
        $this->companyService = new CompanyService( new CompanyRepository() );
    }

    public function register(): void {
        add_action( 'template_redirect',  [ $this, 'redirectGuestFromArchive' ] );
        add_action( 'pre_get_posts',      [ $this, 'filterProvidersQuery' ] );
        add_filter( 'archive_template',   [ $this, 'archiveTemplate' ] );
        add_filter( 'single_template',    [ $this, 'singleTemplate' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueueAssets' ] );
    }

    /**
     * Redirect non-logged-in users away from the providers archive.
     * Runs on template_redirect — safe to call wp_redirect() here.
     */
    public function redirectGuestFromArchive(): void {
        if ( is_post_type_archive( OfferProviderPostType::POST_TYPE ) && ! is_user_logged_in() ) {
            wp_redirect( wp_login_url( get_permalink() ) );
            exit;
        }
    }

    public function filterProvidersQuery( \WP_Query $query ): void {
        if ( is_admin() || ! $query->is_main_query() ) {
            return;
        }

        if ( ! $query->is_post_type_archive( OfferProviderPostType::POST_TYPE ) ) {
            return;
        }

        $userId = get_current_user_id();

        // Guest users are handled by redirectGuestFromArchive(); nothing to filter here.
        if ( ! $userId ) {
            return;
        }

        // Admins see everything
        if ( user_can( $userId, 'manage_options' ) ) {
            return;
        }

        $company = $this->companyService->getCompanyByUser( $userId );
        if ( ! $company ) {
            $query->set( 'post__in', [ 0 ] );
            return;
        }

        $catIds = $this->companyService->getCompanyCategoryIds( $company->id );
        if ( empty( $catIds ) ) {
            $query->set( 'post__in', [ 0 ] );
            return;
        }

        $query->set( 'tax_query', [ [
            'taxonomy' => ProviderCategoryTaxonomy::TAXONOMY,
            'field'    => 'term_id',
            'terms'    => $catIds,
        ] ] );
    }

    public function archiveTemplate( string $template ): string {
        if ( is_post_type_archive( OfferProviderPostType::POST_TYPE ) ) {
            $custom = OPM_PATH . 'templates/archive-offer-provider.php';
            if ( file_exists( $custom ) ) {
                return $custom;
            }
        }
        return $template;
    }

    public function singleTemplate( string $template ): string {
        if ( get_post_type() === OfferProviderPostType::POST_TYPE ) {
            $custom = OPM_PATH . 'templates/single-offer-provider.php';
            if ( file_exists( $custom ) ) {
                return $custom;
            }
        }
        return $template;
    }

    public function enqueueAssets(): void {
        if ( is_post_type_archive( OfferProviderPostType::POST_TYPE )
             || is_singular( OfferProviderPostType::POST_TYPE )
        ) {
            wp_enqueue_style(
                'opm-frontend',
                OPM_URL . 'assets/dist/frontend.css',
                [],
                OPM_VERSION
            );
        }
    }
}
