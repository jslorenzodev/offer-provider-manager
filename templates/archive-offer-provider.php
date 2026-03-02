<?php
/**
 * Archive template: Offer Providers
 */
use OPM\Src\Company\CompanyRepository;
use OPM\Src\Company\CompanyService;
use OPM\Src\Coupon\CouponMetaBox;

get_header();

$user    = wp_get_current_user();
$company = ( new CompanyService( new CompanyRepository() ) )->getCompanyByUser( $user->ID );
?>

<div class="mx-auto px-4 sm:px-6 lg:px-8 py-8 max-w-7xl">
  <div class="flex sm:flex-row flex-col sm:justify-between sm:items-end gap-3 mb-8">
    <div>
      <h1 class="font-semibold text-gray-900 text-2xl sm:text-3xl tracking-tight">
        <?php esc_html_e( 'Offer Providers', 'offer-provider' ); ?>
      </h1>
      <p class="mt-1 text-gray-600 text-sm">
        <?php esc_html_e( 'Browse available providers and active offers.', 'offer-provider' ); ?>
      </p>
    </div>

    <?php if ( $company ) : ?>
      <span class="inline-flex items-center bg-gray-100 px-3 py-1 rounded-full font-medium text-gray-800 text-sm">
        <?php echo esc_html( $company->name ); ?>
      </span>
    <?php endif; ?>
  </div>

  <?php if ( have_posts() ) : ?>
    <div class="gap-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
      <?php while ( have_posts() ) : the_post(); ?>
        <?php
        $website = get_post_meta( get_the_ID(), '_opm_website_url', true );
        $coupons = CouponMetaBox::getActiveCoupons( get_the_ID() );
        $terms   = get_the_terms( get_the_ID(), 'provider_category' );
        ?>

        <article class="bg-white shadow-sm hover:shadow-md border border-gray-200 rounded-2xl overflow-hidden transition-shadow">
          <?php $logoImg = \OPM\Src\Admin\ProviderMetaBox::getLogoImg( get_the_ID(), 'medium', 'h-16 w-auto max-w-full object-contain' ); ?>
          <?php if ( $logoImg ) : ?>
            <div class="p-6 pb-0">
              <div class="flex items-center h-16">
                <?php if ( $website ) : ?>
                  <a class="inline-flex" href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener">
                    <?php echo $logoImg; ?>
                    <span class="sr-only"><?php the_title(); ?></span>
                  </a>
                <?php else : ?>
                  <?php echo $logoImg; ?>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>

          <div class="p-6">
            <h2 class="font-semibold text-gray-900 text-lg leading-snug">
              <?php if ( $website ) : ?>
                <a class="hover:underline underline-offset-4" href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener">
                  <?php the_title(); ?>
                </a>
              <?php else : ?>
                <?php the_title(); ?>
              <?php endif; ?>
            </h2>

            <?php if ( $terms && ! is_wp_error( $terms ) ) : ?>
              <div class="flex flex-wrap gap-2 mt-3">
                <?php foreach ( $terms as $term ) : ?>
                  <span class="inline-flex items-center bg-gray-100 px-2.5 py-1 rounded-full font-medium text-gray-700 text-xs">
                    <?php echo esc_html( $term->name ); ?>
                  </span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <?php if ( $website ) : ?>
              <div class="mt-3">
                <a
                  class="text-blue-600 hover:text-blue-700 text-sm hover:underline underline-offset-4 break-all"
                  href="<?php echo esc_url( $website ); ?>"
                  target="_blank"
                  rel="noopener"
                >
                  <?php echo esc_html( preg_replace( '#^https?://#', '', rtrim( $website, '/' ) ) ); ?>
                </a>
              </div>
            <?php endif; ?>

            <?php if ( $coupons ) : ?>
              <div class="bg-gray-50 mt-5 p-4 border border-gray-200 rounded-xl">
                <h4 class="font-semibold text-gray-900 text-sm">
                  <?php esc_html_e( 'Available Offers', 'offer-provider' ); ?>
                </h4>

                <div class="space-y-2 mt-3">
                  <?php foreach ( $coupons as $coupon ) : ?>
                    <div class="flex justify-between items-center gap-3 bg-white px-3 py-2 border border-gray-200 rounded-lg">
                      <span class="font-semibold text-gray-900 text-sm">
                        <?php echo esc_html( $coupon['discount_amount'] ); ?>
                      </span>

                      <span class="inline-flex items-center bg-gray-900 px-2 py-1 rounded-md font-mono font-semibold text-white text-xs">
                        <?php echo esc_html( $coupon['discount_code'] ); ?>
                      </span>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </article>

      <?php endwhile; ?>
    </div>

    <div class="mt-10">
      <?php the_posts_pagination(); ?>
    </div>

  <?php else : ?>
    <div class="bg-gray-50 p-10 border border-gray-300 border-dashed rounded-2xl text-center">
      <p class="text-gray-700 text-sm">
        <?php esc_html_e( 'No offer providers available for your company at this time.', 'offer-provider' ); ?>
      </p>
    </div>
  <?php endif; ?>
</div>

<?php get_footer(); ?>