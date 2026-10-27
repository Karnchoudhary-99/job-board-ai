<?php

declare(strict_types=1);

namespace JobBoard;

/** Transparent result of matching one candidate against one job. */
final class MatchResult
{
    /**
     * @param array<string, float> $breakdown component => points awarded
     * @param string[] $explanations
     */
    public function __construct(
        public readonly Candidate $candidate,
        public readonly Job $job,
        public readonly float $score,
        public readonly array $breakdown,
        public readonly array $explanations,
    ) {
    }

    public function verdict(): string
    {
        return match (true) {
            $this->score >= 75 => 'strong',
            $this->score >= 55 => 'good',
            $this->score >= 35 => 'weak',
            default => 'poor',
        };
    }
}
