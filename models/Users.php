<?php

namespace models;

class Users
{
    public function __construct(private int $id,
                                private string $email,
                                private string $password)
    {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}