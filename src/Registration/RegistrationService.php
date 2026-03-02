<?php

declare( strict_types=1 );

namespace OPM\Src\Registration;

use OPM\Src\Company\CompanyDTO;
use OPM\Src\Company\CompanyService;

/**
 * Service — orchestrates user creation and company assignment.
 * Compatible with PHP 7.4+.
 */
final class RegistrationService {

    /** @var CompanyService */
    private $companyService;

    /** @var PasswordValidator */
    private $passwordValidator;

    public function __construct( CompanyService $companyService, PasswordValidator $passwordValidator ) {
        $this->companyService    = $companyService;
        $this->passwordValidator = $passwordValidator;
    }

    /**
     * Validate and create a new user from form data.
     *
     * @return true|\WP_Error
     */
    public function register( RegistrationFormData $data, CompanyDTO $company ) {
        $validation = $this->validateFormData( $data );
        if ( is_wp_error( $validation ) ) {
            return $validation;
        }

        $userId = wp_create_user( $data->username, $data->password, $data->email );
        if ( is_wp_error( $userId ) ) {
            return $userId;
        }

        $this->setupUser( (int) $userId, $company );
        $this->sendWelcomeEmail( (int) $userId, $company );

        return true;
    }

    /**
     * @return true|\WP_Error
     */
    private function validateFormData( RegistrationFormData $data ) {
        if ( ! $data->hasRequiredFields() ) {
            return new \WP_Error( 'missing_fields', __( 'All fields are required.', 'offer-provider' ) );
        }

        if ( ! validate_username( $data->username ) ) {
            return new \WP_Error( 'invalid_username', __( 'Username contains invalid characters.', 'offer-provider' ) );
        }

        if ( strlen( $data->username ) < 3 || strlen( $data->username ) > 60 ) {
            return new \WP_Error( 'username_length', __( 'Username must be between 3 and 60 characters.', 'offer-provider' ) );
        }

        if ( ! is_email( $data->email ) ) {
            return new \WP_Error( 'invalid_email', __( 'Please enter a valid email address.', 'offer-provider' ) );
        }

        if ( ! $data->passwordsMatch() ) {
            return new \WP_Error( 'password_mismatch', __( 'Passwords do not match.', 'offer-provider' ) );
        }

        $passwordErrors = $this->passwordValidator->validate( $data->password );
        if ( ! empty( $passwordErrors ) ) {
            return new \WP_Error( 'weak_password', implode( ' ', $passwordErrors ) );
        }

        if ( username_exists( $data->username ) ) {
            return new \WP_Error( 'username_exists', __( 'That username is already taken.', 'offer-provider' ) );
        }

        if ( email_exists( $data->email ) ) {
            return new \WP_Error( 'email_exists', __( 'That email is already registered.', 'offer-provider' ) );
        }

        return true;
    }

    private function setupUser( int $userId, CompanyDTO $company ): void {
        $user = new \WP_User( $userId );
        $user->set_role( 'subscriber' );
        $this->companyService->assignUserToCompany( $userId, $company->id );
    }

    private function sendWelcomeEmail( int $userId, CompanyDTO $company ): void {
        $user    = get_userdata( $userId );
        if ( ! $user ) {
            return;
        }
        $subject = sprintf( __( 'Welcome to %s', 'offer-provider' ), get_bloginfo( 'name' ) );
        $message = sprintf(
            /* translators: 1: username, 2: company name, 3: login URL */
            __( "Hi %1\$s,\n\nYour account has been created and linked to %2\$s.\n\nLog in here: %3\$s\n\nThank you!", 'offer-provider' ),
            $user->user_login,
            $company->name,
            wp_login_url()
        );
        wp_mail( $user->user_email, $subject, $message );
    }
}
