<?php

namespace models;

class Users
{
    public function __construct(private int $id,
                                private string $email,
                                private ?string $resetTokenExpiry = null)
    {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function hasValidResetToken(): bool
    {
        return $this->resetTokenExpiry !== null
            && strtotime($this->resetTokenExpiry) >= time();
    }
}