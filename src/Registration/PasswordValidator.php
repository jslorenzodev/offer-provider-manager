<?php

declare( strict_types=1 );

namespace OPM\Src\Registration;

/**
 * Validates password strength against configurable rules.
 */
final class PasswordValidator {

    /** @var array<string, array{pattern: string, message: string, check: callable}> */
    private $rules = [];

    public function __construct() {
        $this->rules = [
            'length'  => [
                'pattern' => '',
                'message' => 'At least 8 characters.',
                'check'   => fn( string $p ): bool => strlen( $p ) >= 8,
            ],
            'upper'   => [
                'pattern' => '/[A-Z]/',
                'message' => 'At least one uppercase letter.',
                'check'   => fn( string $p ): bool => (bool) preg_match( '/[A-Z]/', $p ),
            ],
            'lower'   => [
                'pattern' => '/[a-z]/',
                'message' => 'At least one lowercase letter.',
                'check'   => fn( string $p ): bool => (bool) preg_match( '/[a-z]/', $p ),
            ],
            'number'  => [
                'pattern' => '/[0-9]/',
                'message' => 'At least one number.',
                'check'   => fn( string $p ): bool => (bool) preg_match( '/[0-9]/', $p ),
            ],
            'special' => [
                'pattern' => '/[\W_]/',
                'message' => 'At least one special character (!@#$%...).',
                'check'   => fn( string $p ): bool => (bool) preg_match( '/[\W_]/', $p ),
            ],
        ];
    }

    /**
     * Returns an array of error messages, empty if password is strong.
     *
     * @return string[]
     */
    public function validate( string $password ): array {
        $errors = [];
        foreach ( $this->rules as $rule ) {
            if ( ! ( $rule['check'] )( $password ) ) {
                $errors[] = __( $rule['message'], 'offer-provider' );
            }
        }
        return $errors;
    }

    public function isValid( string $password ): bool {
        return empty( $this->validate( $password ) );
    }
}
