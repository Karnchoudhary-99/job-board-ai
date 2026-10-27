<?php

declare(strict_types=1);

namespace JobBoard\Tests;

use InvalidArgumentException;
use JobBoard\Candidate;
use PHPUnit\Framework\TestCase;

final class CandidateTest extends TestCase
{
    public function testHasSkillMatchesThroughAliases(): void
    {
        $candidate = new Candidate('c1', 'Asha', skills: ['ReactJS']);
        $this->assertTrue($candidate->hasSkill('react'));
        $this->assertFalse($candidate->hasSkill('vue'));
    }

    public function testSkillYearsAreNormalized(): void
    {
        $candidate = new Candidate('c1', 'Asha', skillYears: ['Py' => 3]);
        $this->assertSame(3, $candidate->yearsWith('python'));
        $this->assertSame(0, (new Candidate('c2', 'Ben'))->yearsWith('python'));
    }

    public function testRejectsEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Candidate('c1', '  ');
    }

    public function testRejectsNegativeSkillYears(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Candidate('c1', 'Asha', skillYears: ['python' => -1]);
    }
}
