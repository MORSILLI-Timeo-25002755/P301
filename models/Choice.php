<?php

namespace models;

class Choice
{
    public function __construct(
        private int $id,
        private string $title,
        private int $questionId
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getQuestionId(): int
    {
        return $this->questionId;
    }
}
