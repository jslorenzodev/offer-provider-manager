<?php

declare( strict_types=1 );

namespace OPM\Src;

use OPM\Src\Admin\CompanyAdmin;
use OPM\Src\Admin\ProviderMetaBox;
use OPM\Src\Coupon\CouponMetaBox;
use OPM\Src\Database\Schema;
use OPM\Src\Frontend\ProviderFrontend;
use OPM\Src\PostTypes\OfferProviderPostType;
use OPM\Src\Registration\RegistrationRouter;
use OPM\Src\Taxonomy\ProviderCategoryTaxonomy;

/**
 * Main plugin class — singleton entry point.
 * Responsible for instantiating and wiring all modules.
 */
final class Plugin {

    private static ?self $instance = null;

    /** Prevent direct instantiation */
    private function __construct() {}

    public static function getInstance(): self {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Boot all plugin modules.
     * Called once on the `plugins_loaded` hook.
     */
    public function boot(): void {
        Schema::install();

        $this->registerPostTypes();
        $this->registerTaxonomies();
        $this->registerAdminModules();
        $this->registerFrontendModules();
        $this->registerRegistration();
    }

    private function registerPostTypes(): void {
        ( new OfferProviderPostType() )->register();
    }

    private function registerTaxonomies(): void {
        ( new ProviderCategoryTaxonomy() )->register();
    }

    private function registerAdminModules(): void {
        if ( ! is_admin() ) {
            return;
        }
        ( new CompanyAdmin() )->register();
        ( new ProviderMetaBox() )->register();
        ( new CouponMetaBox() )->register();
    }

    private function registerFrontendModules(): void {
        ( new ProviderFrontend() )->register();
    }

    private function registerRegistration(): void {
        ( new RegistrationRouter() )->register();
    }
}
