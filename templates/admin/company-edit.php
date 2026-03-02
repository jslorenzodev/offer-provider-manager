<?php
/**
 * Template: Admin — Add / Edit Company
 *
 * @var \OPM\Src\Company\CompanyDTO|null $company
 * @var \WP_Term[]                       $allCategories
 * @var int[]                            $selectedCatIds
 * @var string                           $error
 * @var string                           $success
 */
declare( strict_types=1 );
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wrap">
    <h1>
        <?php echo $company
            ? esc_html__( 'Edit Company',  'offer-provider' )
            : esc_html__( 'Add Company',   'offer-provider' );
        ?>
    </h1>

    <a href="<?php echo esc_url( admin_url( 'admin.php?page=opm-companies' ) ); ?>">
        &larr; <?php esc_html_e( 'Back to Companies', 'offer-provider' ); ?>
    </a>

    <?php if ( $error ) : ?>
        <div class="notice notice-error"><p><?php echo esc_html( $error ); ?></p></div>
    <?php endif; ?>
    <?php if ( $success ) : ?>
        <div class="notice notice-success"><p><?php echo esc_html( $success ); ?></p></div>
    <?php endif; ?>

    <form method="post" style="margin-top:20px">
        <?php wp_nonce_field( 'opm_save_company' ); ?>

        <table class="form-table">
            <tr>
                <th>
                    <label for="company_name">
                        <?php esc_html_e( 'Company Name', 'offer-provider' ); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           id="company_name"
                           name="company_name"
                           value="<?php echo esc_attr( $company->name ?? '' ); ?>"
                           class="regular-text"
                           required>
                </td>
            </tr>

            <tr>
                <th><?php esc_html_e( 'Provider Categories', 'offer-provider' ); ?></th>
                <td>
                    <?php if ( empty( $allCategories ) ) : ?>
                        <p>
                            <?php esc_html_e(
                                'No categories yet. Add them via Offer Providers → Provider Categories.',
                                'offer-provider'
                            ); ?>
                        </p>
                    <?php else : ?>
                        <?php foreach ( $allCategories as $cat ) : ?>
                            <label style="display:block;margin-bottom:4px">
                                <input type="checkbox"
                                       name="category_ids[]"
                                       value="<?php echo esc_attr( (string) $cat->term_id ); ?>"
                                       <?php checked( in_array( $cat->term_id, $selectedCatIds, true ) ); ?>>
                                <?php echo esc_html( $cat->name ); ?>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </td>
            </tr>
        </table>

        <p class="submit">
            <button type="submit" class="button button-primary">
                <?php esc_html_e( 'Save Company', 'offer-provider' ); ?>
            </button>
        </p>
    </form>
</div>
