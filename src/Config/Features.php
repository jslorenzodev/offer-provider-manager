<?php

declare( strict_types=1 );

namespace OPM\Src\Config;

/**
 * Feature flags for the Offer Provider Manager plugin.
 *
 * Toggle features by adding constants to wp-config.php.
 *
 * Example:
 *   define( 'OPM_ENABLE_REGISTERED_USERS', true );
 *
 * Available flags:
 * ┌──────────────────────────────┬─────────┬─────────────────────────────────────────┐
 * │ Constant                     │ Default │ Description                             │
 * ├──────────────────────────────┼─────────┼─────────────────────────────────────────┤
 * │ OPM_ENABLE_REGISTERED_USERS  │  false  │ Show Registered Users submenu page      │
 * └──────────────────────────────┴─────────┴─────────────────────────────────────────┘
 */
final class Features {

    /**
     * Whether the Registered Users admin page is enabled.
     * Not part of core requirements — disabled by default.
     *
     * Enable by adding to wp-config.php:
     *   define( 'OPM_ENABLE_REGISTERED_USERS', true );
     */
    public static function registeredUsersEnabled(): bool {
        return defined( 'OPM_ENABLE_REGISTERED_USERS' ) && OPM_ENABLE_REGISTERED_USERS === true;
    }
}
