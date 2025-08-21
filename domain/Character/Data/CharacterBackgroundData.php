<?php

declare(strict_types=1);

namespace Domain\Character\Data;

use Spatie\LaravelData\Data;

class CharacterBackgroundData extends Data
{
    public function __construct(
        public array $answers,
        public ?string $physicalDescription = null,
        public ?string $personality = null,
        public ?string $backstory = null,
        public ?string $motivations = null,
    ) {}

    public static function fromCharacterData(array $characterData): self
    {
        $background = $characterData['background'] ?? [];

        return new self(
            answers: $background['answers'] ?? [],
            physicalDescription: $background['physical_description'] ?? null,
            personality: $background['personality'] ?? null,
            backstory: $background['backstory'] ?? null,
            motivations: $background['motivations'] ?? null,
        );
    }

    public static function fromBuilderData(array $backgroundAnswers): self
    {
        return new self(
            answers: $backgroundAnswers,
            physicalDescription: $backgroundAnswers['physical_description'] ?? null,
            personality: $backgroundAnswers['personality'] ?? null,
            backstory: $backgroundAnswers['backstory'] ?? null,
            motivations: $backgroundAnswers['motivations'] ?? null,
        );
    }

    public function hasAnswers(): bool
    {
        return ! empty($this->answers);
    }

    public function getAnswerCount(): int
    {
        return count(array_filter($this->answers));
    }

    public function getAnswer(int $questionIndex): ?string
    {
        return $this->answers[$questionIndex] ?? null;
    }

    public function hasAnswer(int $questionIndex): bool
    {
        return ! empty($this->answers[$questionIndex]);
    }

    public function getAllAnswers(): array
    {
        return array_filter($this->answers);
    }

    public function getCompletedAnswers(): array
    {
        return array_filter($this->answers, function ($answer) {
            return ! empty(trim($answer));
        });
    }

    public function isComplete(int $requiredAnswers = 3): bool
    {
        return count($this->getCompletedAnswers()) >= $requiredAnswers;
    }

    public function hasPhysicalDescription(): bool
    {
        return ! empty($this->physicalDescription);
    }

    public function hasPersonality(): bool
    {
        return ! empty($this->personality);
    }

    public function hasBackstory(): bool
    {
        return ! empty($this->backstory);
    }

    public function hasMotivations(): bool
    {
        return ! empty($this->motivations);
    }

    public function getCompletionPercentage(int $totalQuestions = 3): float
    {
        $completed = count($this->getCompletedAnswers());

        return round(($completed / $totalQuestions) * 100, 1);
    }

    public function getShortPhysicalDescription(int $maxLength = 100): string
    {
        if (! $this->physicalDescription) {
            return '';
        }

        if (strlen($this->physicalDescription) <= $maxLength) {
            return $this->physicalDescription;
        }

        return substr($this->physicalDescription, 0, $maxLength).'...';
    }

    public function getShortPersonality(int $maxLength = 100): string
    {
        if (! $this->personality) {
            return '';
        }

        if (strlen($this->personality) <= $maxLength) {
            return $this->personality;
        }

        return substr($this->personality, 0, $maxLength).'...';
    }

    public function toSummary(): array
    {
        return [
            'total_answers' => count($this->answers),
            'completed_answers' => count($this->getCompletedAnswers()),
            'has_physical_description' => $this->hasPhysicalDescription(),
            'has_personality' => $this->hasPersonality(),
            'has_backstory' => $this->hasBackstory(),
            'has_motivations' => $this->hasMotivations(),
            'completion_percentage' => $this->getCompletionPercentage(),
        ];
    }

    public function addAnswer(int $questionIndex, string $answer): self
    {
        $answers = $this->answers;
        $answers[$questionIndex] = $answer;

        return new self(
            answers: $answers,
            physicalDescription: $this->physicalDescription,
            personality: $this->personality,
            backstory: $this->backstory,
            motivations: $this->motivations,
        );
    }

    public function setPhysicalDescription(string $description): self
    {
        return new self(
            answers: $this->answers,
            physicalDescription: $description,
            personality: $this->personality,
            backstory: $this->backstory,
            motivations: $this->motivations,
        );
    }

    public function setPersonality(string $personality): self
    {
        return new self(
            answers: $this->answers,
            physicalDescription: $this->physicalDescription,
            personality: $personality,
            backstory: $this->backstory,
            motivations: $this->motivations,
        );
    }
}
