<?php

declare(strict_types=1);

namespace JobBoard;

use InvalidArgumentException;

final class Job
{
    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';

    public readonly string $id;
    public readonly string $title;
    public readonly string $company;
    public readonly string $location;
    public readonly bool $remote;
    /** @var string[] */
    public readonly array $requiredSkills;
    /** @var string[] */
    public readonly array $preferredSkills;
    public readonly int $minExperienceYears;
    public readonly string $employmentType;
    public readonly ?int $salaryMin;
    public readonly ?int $salaryMax;
    public readonly string $description;
    private string $status;

    /**
     * @param string[] $requiredSkills
     * @param string[] $preferredSkills
     */
    public function __construct(
        string $id,
        string $title,
        string $company,
        string $location = '',
        bool $remote = false,
        array $requiredSkills = [],
        array $preferredSkills = [],
        int $minExperienceYears = 0,
        string $employmentType = 'full-time',
        ?int $salaryMin = null,
        ?int $salaryMax = null,
        string $description = '',
    ) {
        if (trim($id) === '' || trim($title) === '' || trim($company) === '') {
            throw new InvalidArgumentException('Job id, title and company must not be empty.');
        }
        if ($minExperienceYears < 0) {
            throw new InvalidArgumentException('minExperienceYears must not be negative.');
        }
        if ($salaryMin !== null && $salaryMin < 0) {
            throw new InvalidArgumentException('salaryMin must not be negative.');
        }
        if ($salaryMin !== null && $salaryMax !== null && $salaryMax < $salaryMin) {
            throw new InvalidArgumentException('salaryMax must not be below salaryMin.');
        }
        $this->id = $id;
        $this->title = $title;
        $this->company = $company;
        $this->location = $location;
        $this->remote = $remote;
        $this->requiredSkills = SkillNormalizer::normalizeAll($requiredSkills);
        $this->preferredSkills = SkillNormalizer::normalizeAll($preferredSkills);
        $this->minExperienceYears = $minExperienceYears;
        $this->employmentType = $employmentType;
        $this->salaryMin = $salaryMin;
        $this->salaryMax = $salaryMax;
        $this->description = $description;
        $this->status = self::STATUS_OPEN;
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function close(): void
    {
        $this->status = self::STATUS_CLOSED;
    }

    public function reopen(): void
    {
        $this->status = self::STATUS_OPEN;
    }

    public function status(): string
    {
        return $this->status;
    }

    /** @return string[] all skills, required first */
    public function allSkills(): array
    {
        return array_values(array_unique([...$this->requiredSkills, ...$this->preferredSkills]));
    }
}
