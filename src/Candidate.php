<?php

declare(strict_types=1);

namespace JobBoard;

use InvalidArgumentException;

final class Candidate
{
    public readonly string $id;
    public readonly string $name;
    /** @var string[] */
    public readonly array $skills;
    /** @var array<string, int> normalized skill => years of experience with it */
    public readonly array $skillYears;
    public readonly int $experienceYears;
    public readonly string $location;
    public readonly bool $openToRemote;
    public readonly string $summary;

    /**
     * @param string[] $skills
     * @param array<string, int> $skillYears raw skill name => years
     */
    public function __construct(
        string $id,
        string $name,
        array $skills = [],
        array $skillYears = [],
        int $experienceYears = 0,
        string $location = '',
        bool $openToRemote = false,
        string $summary = '',
    ) {
        if (trim($id) === '' || trim($name) === '') {
            throw new InvalidArgumentException('Candidate id and name must not be empty.');
        }
        if ($experienceYears < 0) {
            throw new InvalidArgumentException('experienceYears must not be negative.');
        }
        $normalizedYears = [];
        foreach ($skillYears as $skill => $years) {
            if ($years < 0) {
                throw new InvalidArgumentException('Skill years must not be negative.');
            }
            $normalizedYears[SkillNormalizer::normalize((string) $skill)] = $years;
        }
        $this->id = $id;
        $this->name = $name;
        $this->skills = SkillNormalizer::normalizeAll($skills);
        $this->skillYears = $normalizedYears;
        $this->experienceYears = $experienceYears;
        $this->location = $location;
        $this->openToRemote = $openToRemote;
        $this->summary = $summary;
    }

    public function hasSkill(string $skill): bool
    {
        return in_array(SkillNormalizer::normalize($skill), $this->skills, true)
            || array_key_exists(SkillNormalizer::normalize($skill), $this->skillYears);
    }

    /** Years with a skill, falling back to total experience if unlisted. */
    public function yearsWith(string $skill): int
    {
        $key = SkillNormalizer::normalize($skill);
        return $this->skillYears[$key] ?? $this->experienceYears;
    }
}
