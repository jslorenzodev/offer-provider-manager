<?php
/**
 * Template: Admin — Coupons Meta Box
 *
 * @var array<int, array{discount_amount: string, discount_code: string}> $coupons
 */
declare( strict_types=1 );
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<style>
    .opm-coupon-row { display:flex; gap:16px; align-items:center; margin-bottom:10px; padding:10px; background:#f9f9f9; border:1px solid #ddd; border-radius:4px; }
    .opm-coupon-row strong { min-width:70px; }
    .opm-coupon-row label { display:flex; flex-direction:column; gap:4px; font-weight:600; font-size:13px; }
    .opm-coupon-row input { padding:4px 8px; }
</style>

<?php foreach ( $coupons as $index => $coupon ) : ?>
    <div class="opm-coupon-row">
        <strong><?php printf( esc_html__( 'Coupon %d', 'offer-provider' ), $index + 1 ); ?></strong>

        <label>
            <?php esc_html_e( 'Discount Amount', 'offer-provider' ); ?>
            <input type="text"
                   name="opm_coupons[<?php echo $index; ?>][discount_amount]"
                   value="<?php echo esc_attr( $coupon['discount_amount'] ); ?>"
                   placeholder="<?php esc_attr_e( 'e.g. 20% or $10', 'offer-provider' ); ?>">
        </label>

        <label>
            <?php esc_html_e( 'Discount Code', 'offer-provider' ); ?>
            <input type="text"
                   name="opm_coupons[<?php echo $index; ?>][discount_code]"
                   value="<?php echo esc_attr( $coupon['discount_code'] ); ?>"
                   placeholder="<?php esc_attr_e( 'e.g. SAVE20', 'offer-provider' ); ?>">
        </label>
    </div>
<?php endforeach; ?>

<p class="description">
    <?php esc_html_e( 'Leave a coupon slot blank to hide it.', 'offer-provider' ); ?>
</p>
