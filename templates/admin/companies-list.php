<?php
/**
 * Template: Admin — Companies List
 *
 * @var \OPM\Src\Company\CompanyDTO[] $companies
 * @var array<int, string[]>          $companyCategoryNames  Map of company_id => category name[]
 * @var array<int, int>               $companyUserCounts     Map of company_id => user count
 * @var bool                          $showRegisteredUsers   Whether the Registered Users feature is enabled
 */
declare( strict_types=1 );
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<style>
    .opm-user-count-badge {
        display: inline-block; background: #e7f3ff; color: #0a4b78;
        border-radius: 20px; padding: 2px 10px; font-size: 12px; font-weight: 600;
    }
    .opm-user-count-badge.zero { background: #f0f0f1; color: #646970; }
</style>

<div class="wrap">
    <h1>
        <?php esc_html_e( 'Companies', 'offer-provider' ); ?>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=opm-company-edit' ) ); ?>"
           class="page-title-action">
            <?php esc_html_e( 'Add New', 'offer-provider' ); ?>
        </a>
        <?php if ( $showRegisteredUsers ) : ?>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=opm-registered-users' ) ); ?>"
               class="page-title-action">
                <?php esc_html_e( 'View All Users', 'offer-provider' ); ?>
            </a>
        <?php endif; ?>
    </h1>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Company Name',      'offer-provider' ); ?></th>
                <th><?php esc_html_e( 'Registration Link', 'offer-provider' ); ?></th>
                <th><?php esc_html_e( 'Categories',        'offer-provider' ); ?></th>
                <?php if ( $showRegisteredUsers ) : ?>
                    <th style="width:80px"><?php esc_html_e( 'Users', 'offer-provider' ); ?></th>
                <?php endif; ?>
                <th style="width:120px"><?php esc_html_e( 'Actions', 'offer-provider' ); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php if ( empty( $companies ) ) : ?>
            <tr>
                <td colspan="<?php echo $showRegisteredUsers ? 5 : 4; ?>">
                    <?php esc_html_e( 'No companies yet.', 'offer-provider' ); ?>
                </td>
            </tr>
        <?php else : ?>
            <?php foreach ( $companies as $company ) :
                $catNames  = $companyCategoryNames[ $company->id ] ?? [];
                $userCount = $companyUserCounts[ $company->id ] ?? 0;
            ?>
                <tr>
                    <td><strong><?php echo esc_html( $company->name ); ?></strong></td>
                    <td>
                        <input type="text"
                               value="<?php echo esc_url( $company->registrationUrl() ); ?>"
                               readonly
                               style="width:100%;font-size:12px"
                               onclick="this.select()">
                    </td>
                    <td>
                        <?php echo $catNames ? esc_html( implode( ', ', $catNames ) ) : '&mdash;'; ?>
                    </td>
                    <?php if ( $showRegisteredUsers ) : ?>
                        <td>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=opm-registered-users#company-' . $company->id ) ); ?>"
                               style="text-decoration:none">
                                <span class="opm-user-count-badge <?php echo $userCount === 0 ? 'zero' : ''; ?>">
                                    <?php echo esc_html( (string) $userCount ); ?>
                                </span>
                            </a>
                        </td>
                    <?php endif; ?>
                    <td>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=opm-company-edit&id=' . $company->id ) ); ?>">
                            <?php esc_html_e( 'Edit', 'offer-provider' ); ?>
                        </a>
                        &nbsp;|&nbsp;
                        <a href="<?php echo esc_url( wp_nonce_url(
                            admin_url( 'admin.php?page=opm-companies&delete=' . $company->id ),
                            'opm_delete_company'
                        ) ); ?>"
                           onclick="return confirm( '<?php esc_attr_e( 'Delete this company?', 'offer-provider' ); ?>' )"
                           style="color:#b32d2e">
                            <?php esc_html_e( 'Delete', 'offer-provider' ); ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
