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
     * Rank all candidates for one job, best match first. Closed jobs
     * rank an empty list — nobody should be matched to a closed role.
     *
     * @return MatchResult[]
     */
    public function rankCandidatesForJob(Job $job, ?float $minScore = null): array
    {
        if (!$job->isOpen()) {
            return [];
        }
        $results = [];
        foreach ($this->candidates as $candidate) {
            $result = Matcher::match($candidate, $job);
            if ($minScore === null || $result->score >= $minScore) {
                $results[] = $result;
            }
        }
        usort($results, fn (MatchResult $a, MatchResult $b) => $b->score <=> $a->score);
        return $results;
    }

    /**
     * Rank a candidate's open-job matches, best first.
     *
     * @return MatchResult[]
     */
    public function rankJobsForCandidate(Candidate $candidate, ?float $minScore = null): array
    {
        $results = [];
        foreach ($this->jobs as $job) {
            if (!$job->isOpen()) {
                continue;
            }
            $result = Matcher::match($candidate, $job);
            if ($minScore === null || $result->score >= $minScore) {
                $results[] = $result;
            }
        }
        usort($results, fn (MatchResult $a, MatchResult $b) => $b->score <=> $a->score);
        return $results;
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
