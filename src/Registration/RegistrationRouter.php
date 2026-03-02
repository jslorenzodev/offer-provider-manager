<?php

declare( strict_types=1 );

namespace OPM\Src\Registration;

use OPM\Src\Company\CompanyRepository;
use OPM\Src\Company\CompanyService;
use OPM\Src\Company\CompanyDTO;

/**
 * Router — handles URL rewriting, request interception, and template rendering.
 * Acts as the controller layer for registration.
 */
final class RegistrationRouter {

    private const QUERY_VAR    = 'opm_register_token';
    private const TOKEN_REGEX  = '/^[a-f0-9]+$/';
    private const NONCE_ACTION = 'opm_register_';

    private CompanyService      $companyService;
    private RegistrationService $registrationService;
    private RateLimiter         $rateLimiter;

    public function __construct() {
        $repository                = new CompanyRepository();
        $this->companyService      = new CompanyService( $repository );
        $this->rateLimiter         = new RateLimiter();
        $this->registrationService = new RegistrationService(
            $this->companyService,
            new PasswordValidator()
        );
    }

    public function register(): void {
        add_action( 'init',              [ $this, 'addRewriteRules' ] );
        add_filter( 'query_vars',        [ $this, 'addQueryVars' ] );
        add_action( 'template_redirect', [ $this, 'handleRequest' ] );
        add_action( 'rest_api_init',     [ $this, 'registerRestRoutes' ] );
        add_filter( 'login_redirect',    [ $this, 'redirectAfterLogin' ], 10, 3 );

        // Block WordPress default registration — all users must use a company link
        add_filter( 'option_users_can_register', '__return_false' );

        // Block direct access to wp-login.php?action=register
        add_action( 'login_form_register', [ $this, 'blockDefaultRegistration' ] );

        // Show notice on login page when redirected from blocked registration
        add_filter( 'login_message',       [ $this, 'loginPageNotice' ] );

        // Handle users who log in but have no company assigned
        add_action( 'wp',                  [ $this, 'handleNoCompanyUser' ] );
    }

    public function addRewriteRules(): void {
        add_rewrite_rule(
            '^register/([a-f0-9]+)/?$',
            'index.php?' . self::QUERY_VAR . '=$matches[1]',
            'top'
        );
    }

    public function addQueryVars( array $vars ): array {
        $vars[] = self::QUERY_VAR;
        return $vars;
    }

    public function handleRequest(): void {
        $token = get_query_var( self::QUERY_VAR );
        if ( ! $token ) {
            return;
        }

        $this->assertValidToken( $token );

        $company = $this->companyService->getCompanyByToken( $token );
        if ( ! $company ) {
            wp_die( __( 'This registration link is invalid or has expired.', 'offer-provider' ) );
        }

        if ( is_user_logged_in() ) {
            wp_redirect( home_url( '/offer-providers/' ) );
            exit;
        }

        // React registration app assets
        $this->enqueueRegistrationAssets( $token, $company );

        $error   = '';
        $success = '';

        if ( $this->isPost() ) {
            [ $error, $success ] = $this->processPost( $token, $company );
        }

        $this->renderTemplate( 'registration', compact( 'company', 'error', 'success', 'token' ) );
    }

    /**
     * @return array Array with two string elements: [error, success]
     */
    private function processPost( string $token, CompanyDTO $company ): array {
        // 1. Rate limit check
        if ( $this->rateLimiter->isLocked( $token ) ) {
            return [
                sprintf(
                    __( 'Too many attempts. Please try again in %d minute(s).', 'offer-provider' ),
                    $this->rateLimiter->remainingMinutes( $token )
                ),
                '',
            ];
        }

        // 2. Nonce check
        if ( ! isset( $_POST['opm_reg_nonce'] )
             || ! wp_verify_nonce( $_POST['opm_reg_nonce'], self::NONCE_ACTION . $token )
        ) {
            $this->rateLimiter->recordAttempt( $token );
            return [ __( 'Security check failed. Please refresh and try again.', 'offer-provider' ), '' ];
        }

        $formData = new RegistrationFormData( $_POST );

        // 3. Honeypot check — must be BEFORE recording an attempt (bots shouldn't trigger lockout)
        if ( $formData->isHoneypotFilled() ) {
            return [ '', __( 'Registration successful! You can now log in.', 'offer-provider' ) ];
        }

        // 4. Process real submission
        $this->rateLimiter->recordAttempt( $token );
        $result = $this->registrationService->register( $formData, $company );

        if ( is_wp_error( $result ) ) {
            return [ $result->get_error_message(), '' ];
        }

        $this->rateLimiter->clearAttempts( $token );
        return [ '', __( 'Registration successful! You can now log in.', 'offer-provider' ) ];
    }

    /**
     * Redirect non-admin users to the providers archive after login.
     *
     * @param  string           $redirectTo
     * @param  string           $request
     * @param  \WP_User|\WP_Error $user
     * @return string
     */
    public function redirectAfterLogin( string $redirectTo, string $request, $user ): string {
        if ( $user instanceof \WP_User && ! in_array( 'administrator', $user->roles, true ) ) {
            return home_url( '/offer-providers/' );
        }
        return $redirectTo;
    }

    private function assertValidToken( string $token ): void {
        if ( ! preg_match( self::TOKEN_REGEX, $token ) ) {
            wp_die( __( 'Invalid registration link.', 'offer-provider' ) );
        }
    }

    /**
     * Show a friendly notice on the login page when a user
     * tried to use WordPress default registration.
     */
    public function loginPageNotice( string $message ): string {
        if ( isset( $_GET['opm_notice'] ) && $_GET['opm_notice'] === 'use_company_link' ) {
            $message .= sprintf(
                '<p class="message" style="border-left:4px solid #e65054;padding:8px 12px;">%s</p>',
                esc_html__( 'Registration is by invitation only. Please use the registration link provided by your company.', 'offer-provider' )
            );
        }
        return $message;
    }

    /**
     * Block direct access to wp-login.php?action=register.
     * Redirects to login page with an explanatory message.
     */
    public function blockDefaultRegistration(): void {
        wp_redirect(
            add_query_arg(
                'opm_notice',
                'use_company_link',
                wp_login_url()
            )
        );
        exit;
    }

    /**
     * If a logged-in non-admin user has no company assigned,
     * show them a friendly notice instead of an empty providers page.
     */
    public function handleNoCompanyUser(): void {
        if ( ! is_user_logged_in() ) {
            return;
        }

        if ( ! is_post_type_archive( 'offer_provider' ) ) {
            return;
        }

        $user = wp_get_current_user();
        if ( in_array( 'administrator', (array) $user->roles, true ) ) {
            return;
        }

        $company = $this->companyService->getCompanyByUser( $user->ID );
        if ( ! $company ) {
            wp_die(
                __( 'Your account is not linked to a company. Please contact your administrator.', 'offer-provider' ),
                __( 'No Company Assigned', 'offer-provider' ),
                [ 'response' => 403, 'link_url' => wp_login_url(), 'link_text' => __( 'Back to Login', 'offer-provider' ) ]
            );
        }
    }



/**
 * REST endpoint for React-based registration form.
 * POST /wp-json/opm/v1/register
 */
public function registerRestRoutes(): void {
    register_rest_route(
        'opm/v1',
        '/register',
        [
            'methods'             => 'POST',
            'permission_callback' => '__return_true',
            'callback'            => [ $this, 'handleRestRegister' ],
        ]
    );
}

public function handleRestRegister( \WP_REST_Request $request ): \WP_REST_Response {
    $params = (array) $request->get_json_params();
    if ( empty( $params ) ) {
        $params = (array) $request->get_params();
    }

    $token = isset( $params['token'] ) ? (string) $params['token'] : '';
    $this->assertValidToken( $token );

    $company = $this->companyService->getCompanyByToken( $token );
    if ( ! $company ) {
        return new \WP_REST_Response(
            [ 'success' => false, 'message' => __( 'This registration link is invalid or has expired.', 'offer-provider' ) ],
            404
        );
    }

    // Rate limit check
    if ( $this->rateLimiter->isLocked( $token ) ) {
        return new \WP_REST_Response(
            [
                'success' => false,
                'message' => sprintf(
                    __( 'Too many attempts. Please try again in %d minute(s).', 'offer-provider' ),
                    $this->rateLimiter->remainingMinutes( $token )
                ),
            ],
            429
        );
    }

    // Token-specific nonce check (same as HTML form)
    if ( ! isset( $params['opm_reg_nonce'] )
         || ! wp_verify_nonce( (string) $params['opm_reg_nonce'], self::NONCE_ACTION . $token )
    ) {
        $this->rateLimiter->recordAttempt( $token );
        return new \WP_REST_Response(
            [ 'success' => false, 'message' => __( 'Security check failed. Please refresh and try again.', 'offer-provider' ) ],
            403
        );
    }

    $formData = new RegistrationFormData( $params );

    // Honeypot check — must be BEFORE recording an attempt (bots shouldn't trigger lockout)
    if ( $formData->isHoneypotFilled() ) {
        return new \WP_REST_Response(
            [ 'success' => true, 'message' => __( 'Registration successful! You can now log in.', 'offer-provider' ) ],
            200
        );
    }

    // Process real submission
    $this->rateLimiter->recordAttempt( $token );
    $result = $this->registrationService->register( $formData, $company );

    if ( is_wp_error( $result ) ) {
        return new \WP_REST_Response(
            [ 'success' => false, 'message' => $result->get_error_message() ],
            400
        );
    }

    $this->rateLimiter->clearAttempts( $token );
    return new \WP_REST_Response(
        [ 'success' => true, 'message' => __( 'Registration successful! You can now log in.', 'offer-provider' ) ],
        200
    );
}

private function enqueueRegistrationAssets( string $token, CompanyDTO $company ): void {
    $dist_js  = OPM_PATH . 'assets/dist/registration.js';
    $dist_css = OPM_PATH . 'assets/dist/registration.css';

    // Prefer Webpack build outputs (React + Tailwind compiled).
    if ( file_exists( $dist_js ) && file_exists( $dist_css ) ) {
        wp_enqueue_style(
            'opm-registration',
            OPM_URL . 'assets/dist/registration.css',
            [],
            (string) filemtime( $dist_css )
        );

        wp_enqueue_script(
            'opm-registration',
            OPM_URL . 'assets/dist/registration.js',
            [],
            (string) filemtime( $dist_js ),
            true
        );
    } else {
        // Fallback (dev convenience): uses WP-bundled React + Tailwind CDN.
        wp_enqueue_style(
            'opm-registration-fallback',
            OPM_URL . 'assets/registration.css',
            [],
            OPM_VERSION
        );

        wp_enqueue_script(
            'opm-tailwind',
            'https://cdn.tailwindcss.com',
            [],
            null,
            false
        );

        wp_enqueue_script(
            'opm-registration-fallback',
            OPM_URL . 'assets/registration-app.js',
            [ 'wp-element' ],
            OPM_VERSION,
            true
        );
    }

    // Shared config for both build and fallback.
    wp_localize_script(
        file_exists( $dist_js ) ? 'opm-registration' : 'opm-registration-fallback',
        'opmRegistration',
        [
            'token'       => $token,
            'companyName' => $company->name,
            'tokenNonce'  => wp_create_nonce( self::NONCE_ACTION . $token ),
            'wpRestNonce' => wp_create_nonce( 'wp_rest' ),
            'restUrl'     => esc_url_raw( rest_url( 'opm/v1/register' ) ),
            'loginUrl'    => esc_url_raw( wp_login_url() ),
        ]
    );
}


    private function isPost(): bool {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    private function renderTemplate( string $name, array $vars = [] ): void {
        extract( $vars, EXTR_SKIP );
        include OPM_PATH . 'templates/' . $name . '.php';
        exit;
    }
}