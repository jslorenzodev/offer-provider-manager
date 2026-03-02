<?php

declare( strict_types=1 );

namespace OPM\Src\Company;

/**
 * Service — business logic for company operations.
 * Sits between the controller (Admin) and the repository (data).
 */
final class CompanyService {

    /** @var CompanyRepository */
    private $repository;

    public function __construct( CompanyRepository $repository ) {
        $this->repository = $repository;
    }

    /** @return CompanyDTO[] */
    public function getAllCompanies(): array {
        return $this->repository->findAll();
    }

    public function getCompanyById( int $id ): ?CompanyDTO {
        return $this->repository->findById( $id );
    }

    public function getCompanyByToken( string $token ): ?CompanyDTO {
        return $this->repository->findByToken( $token );
    }

    public function getCompanyByUser( int $userId ): ?CompanyDTO {
        return $this->repository->findByUserId( $userId );
    }

    /** @return int[] */
    public function getCompanyCategoryIds( int $companyId ): array {
        return $this->repository->getCategoryIds( $companyId );
    }

    /**
     * Get all WP_User objects registered under a company.
     *
     * @return \WP_User[]
     */
    public function getUsersByCompany( int $companyId ): array {
        $userIds = $this->repository->findUserIdsByCompanyId( $companyId );
        if ( empty( $userIds ) ) {
            return [];
        }
        $users = get_users( [ 'include' => $userIds, 'orderby' => 'registered', 'order' => 'DESC' ] );
        return is_array( $users ) ? $users : [];
    }

    /**
     * Get total registered user count across all companies.
     */
    public function getTotalUserCount(): int {
        return $this->repository->getTotalUserCount();
    }

    /**
     * Create or update a company and sync its category assignments.
     *
     * @param  int     $id      0 = create new
     * @param  string  $name
     * @param  int[]   $catIds
     * @return int     The company ID
     */
    public function save( int $id, string $name, array $catIds ): int {
        if ( $id > 0 ) {
            $this->repository->update( $id, $name );
        } else {
            $id = $this->repository->create( $name, $this->generateToken() );
        }
        $this->repository->syncCategories( $id, $catIds );
        return $id;
    }

    public function deleteCompany( int $id ): void {
        $this->repository->delete( $id );
    }

    public function assignUserToCompany( int $userId, int $companyId ): void {
        $this->repository->assignUser( $userId, $companyId );
    }

    private function generateToken(): string {
        return bin2hex( random_bytes( 24 ) );
    }
}
