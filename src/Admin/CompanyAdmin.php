<?php

declare( strict_types=1 );

namespace OPM\Src\Admin;

use OPM\Src\Company\CompanyRepository;
use OPM\Src\Company\CompanyService;
use OPM\Src\Config\Features;
use OPM\Src\Taxonomy\ProviderCategoryTaxonomy;

/**
 * Admin controller — Companies list and edit pages.
 */
final class CompanyAdmin {

    /** @var CompanyService */
    private $service;

    public function __construct() {
        $this->service = new CompanyService( new CompanyRepository() );
    }

    public function register(): void {
        add_action( 'admin_menu', [ $this, 'registerMenuPages' ] );
    }

    public function registerMenuPages(): void {
        add_menu_page(
            __( 'Companies', 'offer-provider' ),
            __( 'Companies', 'offer-provider' ),
            'manage_options',
            'opm-companies',
            [ $this, 'renderListPage' ],
            'dashicons-building',
            26
        );

        add_submenu_page(
            'opm-companies',
            __( 'Add / Edit Company', 'offer-provider' ),
            __( 'Add Company', 'offer-provider' ),
            'manage_options',
            'opm-company-edit',
            [ $this, 'renderEditPage' ]
        );

        // Optional feature — enable via wp-config.php:
        // define( 'OPM_ENABLE_REGISTERED_USERS', true );
        if ( Features::registeredUsersEnabled() ) {
            add_submenu_page(
                'opm-companies',
                __( 'Registered Users', 'offer-provider' ),
                __( 'Registered Users', 'offer-provider' ),
                'manage_options',
                'opm-registered-users',
                [ $this, 'renderRegisteredUsersPage' ]
            );
        }
    }

    /* ── List Page ──────────────────────────────────────────── */

    public function renderListPage(): void {
        if ( isset( $_GET['delete'] ) && check_admin_referer( 'opm_delete_company' ) ) {
            $this->service->deleteCompany( (int) $_GET['delete'] );
            $this->notice( __( 'Company deleted.', 'offer-provider' ), 'success' );
        }

        $companies            = $this->service->getAllCompanies();
        $companyCategoryNames = [];
        $companyUserCounts    = [];

        foreach ( $companies as $company ) {
            // Category names
            $catIds = $this->service->getCompanyCategoryIds( $company->id );
            $names  = [];
            foreach ( $catIds as $termId ) {
                $term = get_term( $termId, ProviderCategoryTaxonomy::TAXONOMY );
                if ( $term instanceof \WP_Term ) {
                    $names[] = $term->name;
                }
            }
            $companyCategoryNames[ $company->id ] = $names;

            // User count — only fetch when the feature is enabled
            if ( Features::registeredUsersEnabled() ) {
                $companyUserCounts[ $company->id ] = count(
                    $this->service->getUsersByCompany( $company->id )
                );
            }
        }

        $showRegisteredUsers = Features::registeredUsersEnabled();

        include OPM_PATH . 'templates/admin/companies-list.php';
    }

    /* ── Edit Page ──────────────────────────────────────────── */

    public function renderEditPage(): void {
        $id      = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
        $company = $id > 0 ? $this->service->getCompanyById( $id ) : null;
        $error   = '';
        $success = '';

        if ( $this->isPost() && check_admin_referer( 'opm_save_company' ) ) {
            $name    = sanitize_text_field( $_POST['company_name'] ?? '' );
            $catIds  = array_map( 'intval', (array) ( $_POST['category_ids'] ?? [] ) );

            if ( empty( $name ) ) {
                $error = __( 'Company name is required.', 'offer-provider' );
            } else {
                $id      = $this->service->save( $id, $name, $catIds );
                $company = $this->service->getCompanyById( $id );
                $success = __( 'Company saved successfully.', 'offer-provider' );
            }
        }

        $allCategories  = get_terms( [ 'taxonomy' => ProviderCategoryTaxonomy::TAXONOMY, 'hide_empty' => false ] );
        $selectedCatIds = $id > 0 ? $this->service->getCompanyCategoryIds( $id ) : [];

        include OPM_PATH . 'templates/admin/company-edit.php';
    }

    private function isPost(): bool {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /* ── Registered Users Page ──────────────────────────────── */

    public function renderRegisteredUsersPage(): void {
        $companies  = $this->service->getAllCompanies();
        $totalUsers = $this->service->getTotalUserCount();

        $companyUsers         = [];
        $companyCategoryNames = [];

        foreach ( $companies as $company ) {
            // Users
            $companyUsers[ $company->id ] = $this->service->getUsersByCompany( $company->id );

            // Category names for this company
            $catIds = $this->service->getCompanyCategoryIds( $company->id );
            $names  = [];
            foreach ( $catIds as $termId ) {
                $term = get_term( $termId, ProviderCategoryTaxonomy::TAXONOMY );
                if ( $term instanceof \WP_Term ) {
                    $names[] = $term->name;
                }
            }
            $companyCategoryNames[ $company->id ] = $names;
        }

        include OPM_PATH . 'templates/admin/registered-users.php';
    }

    private function notice( string $message, string $type = 'info' ): void {
        printf(
            '<div class="notice notice-%s"><p>%s</p></div>',
            esc_attr( $type ),
            esc_html( $message )
        );
    }
}
