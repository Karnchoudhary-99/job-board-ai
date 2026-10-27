<?php

declare(strict_types=1);

namespace JobBoard\Tests;

use InvalidArgumentException;
use JobBoard\Job;
use PHPUnit\Framework\TestCase;

final class JobTest extends TestCase
{
    public function testSkillsAreNormalizedOnConstruction(): void
    {
        $job = new Job('j1', 'Backend Dev', 'Acme', requiredSkills: ['Python', 'JS']);
        $this->assertSame(['python', 'javascript'], $job->requiredSkills);
    }

    public function testOpenByDefaultAndCloseReopenCycle(): void
    {
        $job = new Job('j1', 'Backend Dev', 'Acme');
        $this->assertTrue($job->isOpen());
        $job->close();
        $this->assertFalse($job->isOpen());
        $job->reopen();
        $this->assertTrue($job->isOpen());
    }

    public function testRejectsEmptyTitle(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Job('j1', '  ', 'Acme');
    }

    public function testRejectsInvertedSalaryRange(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Job('j1', 'Dev', 'Acme', salaryMin: 100, salaryMax: 50);
    }

    public function testRejectsNegativeExperience(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Job('j1', 'Dev', 'Acme', minExperienceYears: -1);
    }

    public function testAllSkillsDeduplicatesRequiredAndPreferred(): void
    {
        $job = new Job('j1', 'Dev', 'Acme', requiredSkills: ['python'], preferredSkills: ['Py', 'docker']);
        $this->assertSame(['python', 'docker'], $job->allSkills());
    }
}
