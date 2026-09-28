<?php

namespace models;

class Form
{
    public function __construct(
        private int $id,
        private string $name,
        private int $userId,
        private int $questionsCount = 0,
        private int $answersCount = 0
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getQuestionsCount(): int
    {
        return $this->questionsCount;
    }

    public function getAnswersCount(): int
    {
        return $this->answersCount;
    }
}
