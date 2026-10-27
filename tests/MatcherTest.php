<?php

declare(strict_types=1);

namespace JobBoard\Tests;

use JobBoard\Candidate;
use JobBoard\Job;
use JobBoard\Matcher;
use PHPUnit\Framework\TestCase;

final class MatcherTest extends TestCase
{
    private function job(): Job
    {
        return new Job(
            'j1', 'PHP Dev', 'Acme', location: 'Jaipur',
            requiredSkills: ['php', 'mysql'],
            preferredSkills: ['docker'],
            minExperienceYears: 2,
        );
    }

    public function testPerfectMatchScores100(): void
    {
        $candidate = new Candidate('c1', 'Asha', skills: ['php', 'mysql', 'docker'], experienceYears: 3, location: 'Jaipur');
        $result = Matcher::match($candidate, $this->job());
        $this->assertSame(100.0, $result->score);
        $this->assertSame('strong', $result->verdict());
    }

    public function testMissingRequiredSkillCostsProportionally(): void
    {
        $candidate = new Candidate('c1', 'Ben', skills: ['php'], experienceYears: 2, location: 'Jaipur');
        $result = Matcher::match($candidate, $this->job());
        // required 27.5 + preferred 0 + experience 20 + location 10
        $this->assertSame(57.5, $result->score);
        $this->assertSame('good', $result->verdict());
        $this->assertStringContainsString('mysql', $result->explanations[0]);
    }

    public function testPartialExperienceScalesLinearly(): void
    {
        $candidate = new Candidate('c1', 'Cid', skills: ['php', 'mysql'], experienceYears: 1, location: 'Jaipur');
        $result = Matcher::match($candidate, $this->job());
        $this->assertSame(55.0 + 0.0 + 10.0 + 10.0, $result->score);
    }

    public function testRemoteJobPassesLocationRegardless(): void
    {
        $job = new Job('j2', 'Remote Dev', 'Beta', remote: true, requiredSkills: ['go']);
        $candidate = new Candidate('c1', 'Dee', skills: ['go'], experienceYears: 5, location: 'Berlin');
        $result = Matcher::match($candidate, $job);
        $this->assertSame(10.0, $result->breakdown['location']);
    }

    public function testLocationMismatchCostsAllLocationPoints(): void
    {
        $candidate = new Candidate('c1', 'Eli', skills: ['php', 'mysql', 'docker'], experienceYears: 3, location: 'Delhi');
        $result = Matcher::match($candidate, $this->job());
        $this->assertSame(90.0, $result->score);
        $this->assertSame(0.0, $result->breakdown['location']);
    }

    public function testVerdictBands(): void
    {
        $nobody = new Candidate('c9', 'Nobody', skills: [], experienceYears: 0, location: 'Nowhere');
        $this->assertSame('poor', Matcher::match($nobody, $this->job())->verdict());
    }
}
