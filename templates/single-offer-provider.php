<?php
/**
 * Single Offer Provider Template
 */
use OPM\Src\Coupon\CouponMetaBox;

get_header();
while ( have_posts() ) : the_post();
    $website = get_post_meta( get_the_ID(), '_opm_website_url', true );
    $coupons = CouponMetaBox::getActiveCoupons( get_the_ID() );
    $terms   = get_the_terms( get_the_ID(), 'provider_category' );
?>
<div class="opm-single-wrap">
    <a href="<?php echo esc_url( get_post_type_archive_link( 'offer_provider' ) ); ?>" class="opm-back-link">
        &larr; <?php esc_html_e( 'Back to Offer Providers', 'offer-provider' ); ?>
    </a>

    <div class="opm-single-card">
        <?php $logoImg = \OPM\Src\Admin\ProviderMetaBox::getLogoImg( get_the_ID(), 'large', 'opm-single-logo-img' ); ?>
        <?php if ( $logoImg ) : ?>
            <div class="opm-single-logo">
                <?php echo $logoImg; ?>
            </div>
        <?php endif; ?>

        <div class="opm-single-body">
            <h1><?php the_title(); ?></h1>

            <?php if ( $terms && ! is_wp_error( $terms ) ) : ?>
                <div class="opm-provider-cats">
                    <?php foreach ( $terms as $term ) : ?>
                        <span class="opm-cat-tag"><?php echo esc_html( $term->name ); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ( $website ) : ?>
                <p>
                    <a href="<?php echo esc_url( $website ); ?>" class="opm-btn-visit" target="_blank" rel="noopener">
                        <?php esc_html_e( 'Visit Website', 'offer-provider' ); ?> &rarr;
                    </a>
                </p>
            <?php endif; ?>

            <?php if ( $coupons ) : ?>
                <div class="opm-coupons">
                    <h3><?php esc_html_e( 'Available Coupons', 'offer-provider' ); ?></h3>
                    <?php foreach ( $coupons as $coupon ) : ?>
                        <div class="opm-coupon">
                            <span class="opm-coupon-amount"><?php echo esc_html( $coupon['discount_amount'] ); ?> OFF</span>
                            <span class="opm-coupon-code" title="<?php esc_attr_e( 'Click to copy', 'offer-provider' ); ?>"
                                  onclick="navigator.clipboard.writeText('<?php echo esc_js( $coupon['discount_code'] ); ?>');this.textContent='Copied!'">
                                <?php echo esc_html( $coupon['discount_code'] ); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endwhile; ?>
<?php get_footer();
