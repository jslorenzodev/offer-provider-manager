<?php

declare( strict_types=1 );

namespace OPM\Src\Company;

/**
 * Data Transfer Object representing a single company record.
 * Compatible with PHP 7.4+.
 */
final class CompanyDTO {

    /** @var int */
    public $id;

    /** @var string */
    public $name;

    /** @var string */
    public $uniqueToken;

    /** @var string */
    public $createdAt;

    public function __construct( int $id, string $name, string $uniqueToken, string $createdAt ) {
        $this->id          = $id;
        $this->name        = $name;
        $this->uniqueToken = $uniqueToken;
        $this->createdAt   = $createdAt;
    }

    /** Build from a raw wpdb row object. */
    public static function fromRow( object $row ): self {
        return new self(
            (int)    $row->id,
            (string) $row->name,
            (string) $row->unique_token,
            (string) $row->created_at
        );
    }

    public function registrationUrl(): string {
        return home_url( '/register/' . $this->uniqueToken . '/' );
    }
}
