<?php

declare( strict_types=1 );

namespace OPM\Src\Company;

/**
 * Repository — all database interactions for companies, categories, and user assignments.
 * No business logic here; only data access.
 */
final class CompanyRepository {

    private \wpdb $db;

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
    }

    /* ── Companies ─────────────────────────────────────────── */

    /** @return CompanyDTO[] */
    public function findAll(): array {
        $rows = $this->db->get_results(
            "SELECT * FROM {$this->db->prefix}opm_companies ORDER BY name ASC"
        );
        return array_map( [ CompanyDTO::class, 'fromRow' ], $rows ?: [] );
    }

    public function findById( int $id ): ?CompanyDTO {
        $row = $this->db->get_row(
            $this->db->prepare(
                "SELECT * FROM {$this->db->prefix}opm_companies WHERE id = %d",
                $id
            )
        );
        return $row ? CompanyDTO::fromRow( $row ) : null;
    }

    public function findByToken( string $token ): ?CompanyDTO {
        $row = $this->db->get_row(
            $this->db->prepare(
                "SELECT * FROM {$this->db->prefix}opm_companies WHERE unique_token = %s",
                sanitize_text_field( $token )
            )
        );
        return $row ? CompanyDTO::fromRow( $row ) : null;
    }

    public function findByUserId( int $userId ): ?CompanyDTO {
        $row = $this->db->get_row(
            $this->db->prepare(
                "SELECT c.* FROM {$this->db->prefix}opm_companies c
                 JOIN {$this->db->prefix}opm_user_companies uc ON uc.company_id = c.id
                 WHERE uc.user_id = %d",
                $userId
            )
        );
        return $row ? CompanyDTO::fromRow( $row ) : null;
    }

    public function create( string $name, string $token ): int {
        $this->db->insert(
            $this->db->prefix . 'opm_companies',
            [ 'name' => $name, 'unique_token' => $token ],
            [ '%s', '%s' ]
        );
        return (int) $this->db->insert_id;
    }

    public function update( int $id, string $name ): void {
        $this->db->update(
            $this->db->prefix . 'opm_companies',
            [ 'name' => $name ],
            [ 'id'   => $id ],
            [ '%s' ],
            [ '%d' ]
        );
    }

    public function delete( int $id ): void {
        $this->db->delete( $this->db->prefix . 'opm_companies',          [ 'id'         => $id ], [ '%d' ] );
        $this->db->delete( $this->db->prefix . 'opm_company_categories', [ 'company_id' => $id ], [ '%d' ] );
        $this->db->delete( $this->db->prefix . 'opm_user_companies',     [ 'company_id' => $id ], [ '%d' ] );
    }

    /* ── Category assignments ───────────────────────────────── */

    /** @return int[] */
    public function getCategoryIds( int $companyId ): array {
        return array_map(
            'intval',
            $this->db->get_col(
                $this->db->prepare(
                    "SELECT term_id FROM {$this->db->prefix}opm_company_categories WHERE company_id = %d",
                    $companyId
                )
            ) ?: []
        );
    }

    /** @param int[] $termIds */
    public function syncCategories( int $companyId, array $termIds ): void {
        $this->db->delete(
            $this->db->prefix . 'opm_company_categories',
            [ 'company_id' => $companyId ],
            [ '%d' ]
        );
        foreach ( $termIds as $termId ) {
            $this->db->insert(
                $this->db->prefix . 'opm_company_categories',
                [ 'company_id' => $companyId, 'term_id' => $termId ],
                [ '%d', '%d' ]
            );
        }
    }

    /* ── User assignments ───────────────────────────────────── */

    public function assignUser( int $userId, int $companyId ): void {
        $this->db->replace(
            $this->db->prefix . 'opm_user_companies',
            [ 'user_id' => $userId, 'company_id' => $companyId ],
            [ '%d', '%d' ]
        );
    }

    /**
     * Get all WordPress user IDs assigned to a company.
     *
     * @return int[]
     */
    public function findUserIdsByCompanyId( int $companyId ): array {
        return array_map(
            'intval',
            $this->db->get_col(
                $this->db->prepare(
                    "SELECT user_id FROM {$this->db->prefix}opm_user_companies WHERE company_id = %d ORDER BY user_id ASC",
                    $companyId
                )
            ) ?: []
        );
    }

    /**
     * Get total registered user count across all companies.
     */
    public function getTotalUserCount(): int {
        return (int) $this->db->get_var(
            "SELECT COUNT(*) FROM {$this->db->prefix}opm_user_companies"
        );
    }
}
