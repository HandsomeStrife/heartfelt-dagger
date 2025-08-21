<?php

declare(strict_types=1);

namespace Domain\Character\Data;

use Spatie\LaravelData\Data;

class CharacterConnectionsData extends Data
{
    public function __construct(
        public array $connections,
    ) {}

    public static function fromCharacterData(array $characterData): self
    {
        $connections = $characterData['connections'] ?? [];

        return new self(connections: $connections);
    }

    public static function fromBuilderData(array $connectionAnswers): self
    {
        return new self(connections: $connectionAnswers);
    }

    public function hasConnections(): bool
    {
        return ! empty($this->connections);
    }

    public function getConnectionCount(): int
    {
        return count(array_filter($this->connections));
    }

    public function getConnection(int $connectionIndex): ?string
    {
        return $this->connections[$connectionIndex] ?? null;
    }

    public function hasConnection(int $connectionIndex): bool
    {
        return ! empty($this->connections[$connectionIndex]);
    }

    public function getAllConnections(): array
    {
        return array_filter($this->connections);
    }

    public function getCompletedConnections(): array
    {
        return array_filter($this->connections, function ($connection) {
            return ! empty(trim($connection));
        });
    }

    public function isComplete(int $requiredConnections = 2): bool
    {
        return count($this->getCompletedConnections()) >= $requiredConnections;
    }

    public function getCompletionPercentage(int $totalConnections = 3): float
    {
        $completed = count($this->getCompletedConnections());

        return round(($completed / $totalConnections) * 100, 1);
    }

    public function getShortConnection(int $connectionIndex, int $maxLength = 100): string
    {
        $connection = $this->getConnection($connectionIndex);

        if (! $connection) {
            return '';
        }

        if (strlen($connection) <= $maxLength) {
            return $connection;
        }

        return substr($connection, 0, $maxLength).'...';
    }

    public function getFormattedConnections(): array
    {
        return array_map(function ($connection, $index) {
            return [
                'index' => $index,
                'text' => $connection,
                'short_text' => $this->getShortConnection($index, 80),
                'has_content' => ! empty(trim($connection)),
                'word_count' => str_word_count($connection),
                'character_count' => strlen($connection),
            ];
        }, $this->connections, array_keys($this->connections));
    }

    public function getConnectionsByLength(): array
    {
        $connections = $this->getCompletedConnections();

        return [
            'short' => array_filter($connections, fn ($conn) => strlen($conn) < 50),
            'medium' => array_filter($connections, fn ($conn) => strlen($conn) >= 50 && strlen($conn) < 150),
            'long' => array_filter($connections, fn ($conn) => strlen($conn) >= 150),
        ];
    }

    public function getTotalWordCount(): int
    {
        return array_sum(array_map('str_word_count', $this->getCompletedConnections()));
    }

    public function getAverageConnectionLength(): float
    {
        $connections = $this->getCompletedConnections();

        if (empty($connections)) {
            return 0;
        }

        $totalLength = array_sum(array_map('strlen', $connections));

        return round($totalLength / count($connections), 1);
    }

    public function toSummary(): array
    {
        return [
            'total_connections' => count($this->connections),
            'completed_connections' => count($this->getCompletedConnections()),
            'total_word_count' => $this->getTotalWordCount(),
            'average_length' => $this->getAverageConnectionLength(),
            'completion_percentage' => $this->getCompletionPercentage(),
            'length_distribution' => $this->getConnectionsByLength(),
        ];
    }

    public function addConnection(int $connectionIndex, string $connection): self
    {
        $connections = $this->connections;
        $connections[$connectionIndex] = $connection;

        return new self(connections: $connections);
    }

    public function removeConnection(int $connectionIndex): self
    {
        $connections = $this->connections;
        unset($connections[$connectionIndex]);

        return new self(connections: $connections);
    }

    public function updateConnection(int $connectionIndex, string $connection): self
    {
        return $this->addConnection($connectionIndex, $connection);
    }

    public function hasMinimumConnections(int $minimum = 2): bool
    {
        return count($this->getCompletedConnections()) >= $minimum;
    }

    public function getConnectionQuestions(array $classConnectionQuestions): array
    {
        return array_map(function ($question, $index) {
            return [
                'index' => $index,
                'question' => $question,
                'answer' => $this->getConnection($index),
                'has_answer' => $this->hasConnection($index),
                'is_required' => $index < 2, // First 2 connections typically required
            ];
        }, $classConnectionQuestions, array_keys($classConnectionQuestions));
    }
}
