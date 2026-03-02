<?php

declare( strict_types=1 );

namespace OPM\Src\Database;

/**
 * Handles creation and upgrading of custom database tables.
 */
final class Schema {

    private const DB_VERSION_OPTION = 'opm_db_version';
    private const DB_VERSION        = '1.0';

    /**
     * Install or upgrade database tables if needed.
     */
    public static function install(): void {
        if ( get_option( self::DB_VERSION_OPTION ) === self::DB_VERSION ) {
            return;
        }

        self::createTables();
        update_option( self::DB_VERSION_OPTION, self::DB_VERSION );
    }

    private static function createTables(): void {
        global $wpdb;

        $collate = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // dbDelta works most reliably with one table per call.
        dbDelta( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}opm_companies (
            id           BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name         VARCHAR(255)        NOT NULL,
            unique_token VARCHAR(64)         NOT NULL UNIQUE,
            created_at   DATETIME            DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) {$collate};" );

        dbDelta( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}opm_company_categories (
            id         BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            company_id BIGINT(20) UNSIGNED NOT NULL,
            term_id    BIGINT(20) UNSIGNED NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY company_category (company_id, term_id)
        ) {$collate};" );

        dbDelta( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}opm_user_companies (
            id         BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id    BIGINT(20) UNSIGNED NOT NULL,
            company_id BIGINT(20) UNSIGNED NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY user_company (user_id)
        ) {$collate};" );
    }
}
