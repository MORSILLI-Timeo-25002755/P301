<?php

namespace models;

class Question
{
    public const TYPE_SELECTION = 'selection';
    public const TYPE_GRADE = 'grade';
    public const TYPE_FREETEXT = 'freetext';

    /**
     * @param Choice[] $choices
     */
    public function __construct(
        private int $id,
        private string $title,
        private int $order,
        private int $formId,
        private string $type,
        private array $choices = []
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function getFormId(): int
    {
        return $this->formId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return Choice[]
     */
    public function getChoices(): array
    {
        return $this->choices;
    }

    public function isSelection(): bool
    {
        return $this->type === self::TYPE_SELECTION;
    }

    public function isGrade(): bool
    {
        return $this->type === self::TYPE_GRADE;
    }

    public function isFreeText(): bool
    {
        return $this->type === self::TYPE_FREETEXT;
    }

    public function getTypeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_SELECTION => 'Choix unique',
            self::TYPE_GRADE => 'Évaluation (1 à 5)',
            self::TYPE_FREETEXT => 'Texte libre',
            default => 'Inconnu'
        };
    }
}
