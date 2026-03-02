<?php

declare( strict_types=1 );

namespace OPM\Src\Registration;

/**
 * Value object representing submitted registration form data.
 * Compatible with PHP 7.4+.
 */
final class RegistrationFormData {

    /** @var string */
    public $username;

    /** @var string */
    public $email;

    /** @var string */
    public $password;

    /** @var string */
    public $passwordConfirm;

    /** @var string */
    public $honeypot;

    public function __construct( array $post ) {
        $this->username        = sanitize_user( $post['username'] ?? '' );
        $this->email           = sanitize_email( $post['email'] ?? '' );
        $this->password        = $post['password'] ?? '';
        $this->passwordConfirm = $post['password2'] ?? '';
        $this->honeypot        = $post['opm_confirm_url'] ?? '';
    }

    public function isHoneypotFilled(): bool {
        return ! empty( $this->honeypot );
    }

    public function hasRequiredFields(): bool {
        return ! empty( $this->username )
            && ! empty( $this->email )
            && ! empty( $this->password )
            && ! empty( $this->passwordConfirm );
    }

    public function passwordsMatch(): bool {
        return $this->password === $this->passwordConfirm;
    }
}
