<?php
/**
 * Template: Admin — Registered Users
 *
 * @var \OPM\Src\Company\CompanyDTO[] $companies
 * @var array<int, \WP_User[]>        $companyUsers          Map of company_id => WP_User[]
 * @var array<int, string[]>          $companyCategoryNames  Map of company_id => category name[]
 * @var int                           $totalUsers
 */
declare( strict_types=1 );
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<style>
    .opm-users-wrap .opm-stats-bar {
        display: flex; gap: 16px; margin-bottom: 24px; flex-wrap: wrap;
    }
    .opm-stat-card {
        background: #fff; border: 1px solid #e0e0e0; border-radius: 8px;
        padding: 16px 24px; display: flex; align-items: center; gap: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,.05);
    }
    .opm-stat-card .opm-stat-icon   { font-size: 28px; line-height: 1; }
    .opm-stat-card .opm-stat-number { font-size: 28px; font-weight: 700; color: #1d2327; line-height: 1; }
    .opm-stat-card .opm-stat-label  { font-size: 12px; color: #646970; margin-top: 2px; }

    .opm-company-section { margin-bottom: 32px; }
    .opm-company-section h2 {
        font-size: 15px; font-weight: 600; color: #1d2327;
        border-left: 4px solid #2271b1; padding-left: 10px;
        margin: 0 0 12px;
    }
    .opm-company-section h2 .opm-user-count {
        font-size: 12px; font-weight: 400; color: #646970;
        background: #f0f0f1; border-radius: 20px;
        padding: 2px 10px; margin-left: 8px;
    }

    .opm-users-table { border-collapse: collapse; width: 100%; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.07); }
    .opm-users-table th { background: #f6f7f7; color: #646970; font-size: 12px; text-transform: uppercase; letter-spacing: .5px; padding: 10px 16px; text-align: left; border-bottom: 1px solid #e0e0e0; }
    .opm-users-table td { padding: 12px 16px; border-bottom: 1px solid #f0f0f1; font-size: 13px; vertical-align: middle; }
    .opm-users-table tr:last-child td { border-bottom: none; }
    .opm-users-table tr:hover td { background: #f9f9f9; }

    .opm-avatar       { border-radius: 50%; vertical-align: middle; margin-right: 8px; }
    .opm-user-name    { font-weight: 600; color: #1d2327; }
    .opm-user-login   { color: #646970; font-size: 12px; }
    .opm-badge-role   { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 600; background: #e7f3ff; color: #0a4b78; }
    .opm-badge-cat    { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 500; background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; margin: 2px 2px 2px 0; }
    .opm-no-cats      { color: #9ca3af; font-style: italic; font-size: 12px; }
    .opm-no-users     { padding: 20px 16px; color: #646970; font-style: italic; background: #fff; border-radius: 8px; border: 1px dashed #dcdcde; text-align: center; }
    .opm-registered-date { color: #646970; font-size: 12px; }
</style>

<div class="wrap opm-users-wrap">
    <h1><?php esc_html_e( 'Registered Users', 'offer-provider' ); ?></h1>

    <!-- Stats Bar -->
    <div class="opm-stats-bar">
        <div class="opm-stat-card">
            <div class="opm-stat-icon">👥</div>
            <div>
                <div class="opm-stat-number"><?php echo esc_html( (string) $totalUsers ); ?></div>
                <div class="opm-stat-label"><?php esc_html_e( 'Total Registered Users', 'offer-provider' ); ?></div>
            </div>
        </div>
        <div class="opm-stat-card">
            <div class="opm-stat-icon">🏢</div>
            <div>
                <div class="opm-stat-number"><?php echo esc_html( (string) count( $companies ) ); ?></div>
                <div class="opm-stat-label"><?php esc_html_e( 'Total Companies', 'offer-provider' ); ?></div>
            </div>
        </div>
    </div>

    <?php if ( empty( $companies ) ) : ?>
        <div class="opm-no-users">
            <?php esc_html_e( 'No companies found. Add a company first.', 'offer-provider' ); ?>
        </div>
    <?php else : ?>

        <?php foreach ( $companies as $company ) :
            $users     = $companyUsers[ $company->id ] ?? [];
            $catNames  = $companyCategoryNames[ $company->id ] ?? [];
        ?>
        <div class="opm-company-section" id="company-<?php echo esc_attr( (string) $company->id ); ?>">
            <h2>
                <?php echo esc_html( $company->name ); ?>
                <span class="opm-user-count">
                    <?php echo esc_html( (string) count( $users ) ); ?>
                    <?php esc_html_e( 'users', 'offer-provider' ); ?>
                </span>
            </h2>

            <?php if ( empty( $users ) ) : ?>
                <div class="opm-no-users">
                    <?php esc_html_e( 'No users have registered via this company\'s link yet.', 'offer-provider' ); ?>
                </div>
            <?php else : ?>
                <table class="opm-users-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'User',        'offer-provider' ); ?></th>
                            <th><?php esc_html_e( 'Email',       'offer-provider' ); ?></th>
                            <th><?php esc_html_e( 'Role',        'offer-provider' ); ?></th>
                            <th><?php esc_html_e( 'Categories',  'offer-provider' ); ?></th>
                            <th><?php esc_html_e( 'Registered',  'offer-provider' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $users as $user ) :
                            $avatar     = get_avatar( $user->ID, 32, '', '', [ 'class' => 'opm-avatar' ] );
                            $roles      = $user->roles;
                            $role       = ! empty( $roles ) ? ucfirst( $roles[0] ) : 'N/A';
                            $registered = date_i18n( get_option( 'date_format' ), strtotime( $user->user_registered ) );
                        ?>
                        <tr>
                            <td>
                                <?php echo $avatar; ?>
                                <span class="opm-user-name"><?php echo esc_html( $user->display_name ); ?></span><br>
                                <span class="opm-user-login">@<?php echo esc_html( $user->user_login ); ?></span>
                            </td>
                            <td><?php echo esc_html( $user->user_email ); ?></td>
                            <td><span class="opm-badge-role"><?php echo esc_html( $role ); ?></span></td>
                            <td>
                                <?php if ( $catNames ) : ?>
                                    <?php foreach ( $catNames as $catName ) : ?>
                                        <span class="opm-badge-cat"><?php echo esc_html( $catName ); ?></span>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <span class="opm-no-cats"><?php esc_html_e( 'No categories assigned', 'offer-provider' ); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><span class="opm-registered-date"><?php echo esc_html( $registered ); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>

    <?php endif; ?>
</div>
