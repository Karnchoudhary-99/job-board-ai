<?php

declare(strict_types=1);

namespace JobBoard;

use LogicException;

/** A candidate's application to one job, with a small state machine. */
final class Application
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_REVIEWED = 'reviewed';
    public const STATUS_SHORTLISTED = 'shortlisted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_WITHDRAWN = 'withdrawn';

    public readonly string $id;
    public readonly Job $job;
    public readonly Candidate $candidate;
    public readonly string $coverNote;
    private string $status;

    public function __construct(string $id, Job $job, Candidate $candidate, string $coverNote = '')
    {
        if (!$job->isOpen()) {
            throw new LogicException('Cannot apply to a closed job.');
        }
        $this->id = $id;
        $this->job = $job;
        $this->candidate = $candidate;
        $this->coverNote = $coverNote;
        $this->status = self::STATUS_PENDING;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function review(): void
    {
        $this->requireActive();
        $this->status = self::STATUS_REVIEWED;
    }

    public function shortlist(): void
    {
        $this->requireActive();
        $this->status = self::STATUS_SHORTLISTED;
    }

    public function reject(): void
    {
        $this->requireActive();
        $this->status = self::STATUS_REJECTED;
    }

    public function withdraw(): void
    {
        $this->requireActive();
        $this->status = self::STATUS_WITHDRAWN;
    }

    private function requireActive(): void
    {
        if ($this->status === self::STATUS_WITHDRAWN) {
            throw new LogicException('A withdrawn application cannot change state.');
        }
        if ($this->status === self::STATUS_REJECTED) {
            throw new LogicException('A rejected application cannot change state.');
        }
    }
}
