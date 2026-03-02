<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo esc_html__( 'Register', 'offer-provider' ) . ' – ' . get_bloginfo( 'name' ); ?></title>
    <?php wp_head(); ?>
</head>
<body class="opm-reg-body">
    <div id="opm-registration-root"></div>

    <noscript>
        <div class="opm-wrap">
            <div class="opm-badge"><?php esc_html_e( 'Invitation-only', 'offer-provider' ); ?></div>
            <h2><?php esc_html_e( 'Create your account', 'offer-provider' ); ?></h2>
            <p class="opm-subtitle">
                <?php
                echo isset( $company ) && $company ? esc_html( sprintf( __( 'You are registering for %s.', 'offer-provider' ), $company->name ) ) : esc_html__( 'Complete the form below.', 'offer-provider' );
                ?>
            </p>

            <?php if ( ! empty( $error ) ) : ?>
                <div class="opm-alert opm-alert--error" role="alert"><?php echo esc_html( $error ); ?></div>
            <?php endif; ?>

            <?php if ( ! empty( $success ) ) : ?>
                <div class="opm-alert opm-alert--success" role="status"><?php echo esc_html( $success ); ?></div>
            <?php endif; ?>

            <form method="post">
                <input type="hidden" name="opm_reg_nonce" value="<?php echo esc_attr( wp_create_nonce( 'opm_register_' . $token ) ); ?>">
                <input type="text" name="opm_confirm_url" value="" style="display:none" tabindex="-1" autocomplete="off">

                <div class="opm-field">
                    <label><?php esc_html_e( 'Username', 'offer-provider' ); ?></label>
                    <input type="text" name="username" required>
                </div>

                <div class="opm-field">
                    <label><?php esc_html_e( 'Email', 'offer-provider' ); ?></label>
                    <input type="email" name="email" required>
                </div>

                <div class="opm-row">
                    <div>
                        <div class="opm-field">
                            <label><?php esc_html_e( 'Password', 'offer-provider' ); ?></label>
                            <input type="password" name="password" required>
                        </div>
                    </div>
                    <div>
                        <div class="opm-field">
                            <label><?php esc_html_e( 'Confirm', 'offer-provider' ); ?></label>
                            <input type="password" name="password2" required>
                        </div>
                    </div>
                </div>

                <div class="opm-actions">
                    <button class="opm-btn" type="submit"><?php esc_html_e( 'Create account', 'offer-provider' ); ?></button>
                </div>

                <div class="opm-note">
                    <a href="<?php echo esc_url( wp_login_url() ); ?>"><?php esc_html_e( 'Log in', 'offer-provider' ); ?></a>
                </div>
            </form>
        </div>
    </noscript>

    <?php wp_footer(); ?>
</body>
</html>
