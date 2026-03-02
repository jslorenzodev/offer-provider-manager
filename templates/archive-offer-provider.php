<?php
/**
 * Archive template: Offer Providers
 */
use OPM\Src\Company\CompanyRepository;
use OPM\Src\Company\CompanyService;
use OPM\Src\Coupon\CouponMetaBox;
use OPM\Src\Admin\ProviderMetaBox;

get_header();

$user    = wp_get_current_user();
$company = ( new CompanyService( new CompanyRepository() ) )->getCompanyByUser( $user->ID );
?>
<div class="opm-archive-wrap">
    <div class="opm-archive-header">
        <h1 class="opm-page-title"><?php esc_html_e( 'Offer Providers', 'offer-provider' ); ?></h1>
        <?php if ( $company ) : ?>
            <span class="opm-company-label"><?php echo esc_html( $company->name ); ?></span>
        <?php endif; ?>
    </div>

    <?php if ( have_posts() ) : ?>
        <div class="opm-providers-grid">
            <?php while ( have_posts() ) : the_post(); ?>
                <?php
                $website = get_post_meta( get_the_ID(), '_opm_website_url', true );
                $coupons = CouponMetaBox::getActiveCoupons( get_the_ID() );
                $terms   = get_the_terms( get_the_ID(), 'provider_category' );
                ?>
                <div class="opm-provider-card">
                    <?php $logoImg = \OPM\Src\Admin\ProviderMetaBox::getLogoImg( get_the_ID(), 'medium', 'opm-logo-img' ); ?>
                    <?php if ( $logoImg ) : ?>
                        <div class="opm-provider-logo">
                            <?php if ( $website ) : ?>
                                <a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener">
                                    <?php echo $logoImg; ?>
                                </a>
                            <?php else : ?>
                                <?php echo $logoImg; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="opm-provider-body">
                        <h2 class="opm-provider-name">
                            <?php if ( $website ) : ?>
                                <a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener">
                                    <?php the_title(); ?>
                                </a>
                            <?php else : ?>
                                <?php the_title(); ?>
                            <?php endif; ?>
                        </h2>

                        <?php if ( $terms && ! is_wp_error( $terms ) ) : ?>
                            <div class="opm-provider-cats">
                                <?php foreach ( $terms as $term ) : ?>
                                    <span class="opm-cat-tag"><?php echo esc_html( $term->name ); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ( $website ) : ?>
                            <a href="<?php echo esc_url( $website ); ?>" class="opm-website-link" target="_blank" rel="noopener">
                                <?php echo esc_html( preg_replace( '#^https?://#', '', rtrim( $website, '/' ) ) ); ?>
                            </a>
                        <?php endif; ?>

                        <?php if ( $coupons ) : ?>
                            <div class="opm-coupons">
                                <h4><?php esc_html_e( 'Available Offers', 'offer-provider' ); ?></h4>
                                <?php foreach ( $coupons as $coupon ) : ?>
                                    <div class="opm-coupon">
                                        <span class="opm-coupon-amount"><?php echo esc_html( $coupon['discount_amount'] ); ?></span>
                                        <span class="opm-coupon-code"><?php echo esc_html( $coupon['discount_code'] ); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <?php the_posts_pagination(); ?>

    <?php else : ?>
        <div class="opm-no-providers">
            <p><?php esc_html_e( 'No offer providers available for your company at this time.', 'offer-provider' ); ?></p>
        </div>
    <?php endif; ?>
</div>

<?php get_footer();
