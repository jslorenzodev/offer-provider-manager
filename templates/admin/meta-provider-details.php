<?php
/**
 * Template: Admin — Provider Details Meta Box
 *
 * @var string $website
 * @var int    $logoId
 * @var string $logoUrl
 */
declare( strict_types=1 );
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<style>
    .opm-logo-wrap { display: flex; align-items: flex-start; gap: 20px; margin-bottom: 20px; }
    .opm-logo-preview-box {
        width: 160px; height: 120px; border: 2px dashed #c3c4c7; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        background: #f9f9f9; overflow: hidden; flex-shrink: 0; position: relative;
    }
    .opm-logo-preview-box img { max-width: 100%; max-height: 100%; object-fit: contain; display: block; }
    .opm-logo-placeholder { text-align: center; color: #aaa; font-size: 12px; line-height: 1.4; }
    .opm-logo-placeholder span { font-size: 30px; display: block; margin-bottom: 4px; }
    .opm-logo-actions { display: flex; flex-direction: column; gap: 8px; justify-content: center; }
    .opm-logo-actions .button { min-width: 120px; text-align: center; }
    #opm-remove-logo { color: #b32d2e; border-color: #b32d2e; }
    #opm-remove-logo:hover { background: #b32d2e; color: #fff; }
</style>

<!-- Logo Upload -->
<div class="opm-logo-wrap">
    <div class="opm-logo-preview-box" id="opm-logo-preview-box">
        <?php if ( $logoUrl ) : ?>
            <img src="<?php echo esc_url( $logoUrl ); ?>" id="opm-logo-preview" alt="Logo">
        <?php else : ?>
            <div class="opm-logo-placeholder" id="opm-logo-placeholder">
                <span>🖼️</span>
                <?php esc_html_e( 'No logo yet', 'offer-provider' ); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="opm-logo-actions">
        <button type="button" class="button button-primary" id="opm-upload-logo">
            <?php esc_html_e( '⬆ Upload Logo', 'offer-provider' ); ?>
        </button>
        <button type="button" class="button" id="opm-remove-logo"
            <?php echo $logoId ? '' : 'style="display:none"'; ?>>
            <?php esc_html_e( '✕ Remove Logo', 'offer-provider' ); ?>
        </button>
        <input type="hidden" id="opm_logo_id" name="opm_logo_id"
               value="<?php echo esc_attr( (string) $logoId ); ?>">
        <p class="description" style="margin:0;font-size:12px">
            <?php esc_html_e( 'Recommended: square image, min 200×200px.', 'offer-provider' ); ?>
        </p>
    </div>
</div>

<hr style="margin: 0 0 16px">

<!-- Website URL -->
<table class="form-table" style="margin:0">
    <tr>
        <th style="padding-top:0">
            <label for="opm_website_url">
                <?php esc_html_e( 'Website URL', 'offer-provider' ); ?>
            </label>
        </th>
        <td style="padding-top:0">
            <input type="url"
                   id="opm_website_url"
                   name="opm_website_url"
                   value="<?php echo esc_attr( $website ); ?>"
                   class="regular-text"
                   placeholder="https://">
            <p class="description">
                <?php esc_html_e( 'The provider\'s website. Users can click to visit directly.', 'offer-provider' ); ?>
            </p>
        </td>
    </tr>
</table>
