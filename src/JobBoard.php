<?php

declare(strict_types=1);

namespace JobBoard;

use InvalidArgumentException;

/** In-memory registry of jobs and candidates. */
final class JobBoard
{
    /** @var array<string, Job> */
    private array $jobs = [];
    /** @var array<string, Candidate> */
    private array $candidates = [];

    public function addJob(Job $job): void
    {
        if (isset($this->jobs[$job->id])) {
            throw new InvalidArgumentException("Job '{$job->id}' already exists.");
        }
        $this->jobs[$job->id] = $job;
    }

    public function addCandidate(Candidate $candidate): void
    {
        if (isset($this->candidates[$candidate->id])) {
            throw new InvalidArgumentException("Candidate '{$candidate->id}' already exists.");
        }
        $this->candidates[$candidate->id] = $candidate;
    }

    public function getJob(string $id): ?Job
    {
        return $this->jobs[$id] ?? null;
    }

    public function getCandidate(string $id): ?Candidate
    {
        return $this->candidates[$id] ?? null;
    }

    /** @return Job[] */
    public function jobs(): array
    {
        return array_values($this->jobs);
    }

    /** @return Candidate[] */
    public function candidates(): array
    {
        return array_values($this->candidates);
    }

    /**
     * Filter open jobs. Supported keys: skill, location, remote (bool),
     * company, employmentType. Unknown keys are ignored.
     *
     * @param array<string, mixed> $filters
     * @return Job[]
     */
    public function searchJobs(array $filters = []): array
    {
        $results = [];
        foreach ($this->jobs as $job) {
            if (!$job->isOpen()) {
                continue;
            }
            if (isset($filters['skill'])) {
                $skill = SkillNormalizer::normalize((string) $filters['skill']);
                if (!in_array($skill, $job->allSkills(), true)) {
                    continue;
                }
            }
            if (isset($filters['location']) && $filters['location'] !== ''
                && stripos($job->location, (string) $filters['location']) === false) {
                continue;
            }
            if (isset($filters['remote']) && $filters['remote'] === true && !$job->remote) {
                continue;
            }
            if (isset($filters['company']) && stripos($job->company, (string) $filters['company']) === false) {
                continue;
            }
            if (isset($filters['employmentType']) && $job->employmentType !== $filters['employmentType']) {
                continue;
            }
            $results[] = $job;
        }
        return $results;
    }
}
