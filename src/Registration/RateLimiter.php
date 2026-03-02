<?php

declare( strict_types=1 );

namespace OPM\Src\Registration;

/**
 * Handles IP-based rate limiting for registration attempts.
 * Uses WordPress transients for storage.
 */
final class RateLimiter {

    private const MAX_ATTEMPTS     = 5;
    private const LOCKOUT_DURATION = 15 * MINUTE_IN_SECONDS;

    public function isLocked( string $token ): bool {
        $data = get_transient( $this->key( $token ) );
        return $data !== false && (int) $data['attempts'] >= self::MAX_ATTEMPTS;
    }

    public function recordAttempt( string $token ): void {
        $key  = $this->key( $token );
        $data = get_transient( $key ) ?: [ 'attempts' => 0 ];
        $data['attempts']++;
        set_transient( $key, $data, self::LOCKOUT_DURATION );
    }

    public function clearAttempts( string $token ): void {
        delete_transient( $this->key( $token ) );
    }

    public function remainingSeconds( string $token ): int {
        $timeout = get_option( '_transient_timeout_' . $this->key( $token ) );
        return $timeout ? max( 0, (int) $timeout - time() ) : 0;
    }

    public function remainingMinutes( string $token ): int {
        return (int) ceil( $this->remainingSeconds( $token ) / 60 );
    }

    private function key( string $token ): string {
        return 'opm_reg_' . md5( $this->resolveIp() . $token );
    }

    private function resolveIp(): string {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = trim( explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] )[0] );
        }
        return sanitize_text_field( $ip );
    }
}
