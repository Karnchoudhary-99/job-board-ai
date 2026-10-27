<?php

declare(strict_types=1);

namespace JobBoard\Tests;

use InvalidArgumentException;
use JobBoard\Candidate;
use JobBoard\Job;
use JobBoard\JobBoard;
use PHPUnit\Framework\TestCase;

final class JobBoardTest extends TestCase
{
    private function board(): JobBoard
    {
        $board = new JobBoard();
        $board->addJob(new Job('j1', 'PHP Dev', 'Acme', location: 'Jaipur', requiredSkills: ['php']));
        $board->addJob(new Job('j2', 'Remote Go Dev', 'Beta', remote: true, requiredSkills: ['go']));
        $board->addCandidate(new Candidate('c1', 'Asha', skills: ['php']));
        return $board;
    }

    public function testDuplicateJobIdRejected(): void
    {
        $board = $this->board();
        $this->expectException(InvalidArgumentException::class);
        $board->addJob(new Job('j1', 'Other', 'Acme'));
    }

    public function testSearchBySkillUsesNormalizedSkill(): void
    {
        $results = $this->board()->searchJobs(['skill' => 'Golang']);
        $this->assertSame(['j2'], array_map(fn (Job $j) => $j->id, $results));
    }

    public function testRemoteFilterAndClosedJobsExcluded(): void
    {
        $board = $this->board();
        $this->assertSame(['j2'], array_map(fn (Job $j) => $j->id, $board->searchJobs(['remote' => true])));
        $board->getJob('j2')->close();
        $this->assertSame([], $board->searchJobs(['remote' => true]));
    }

    public function testLocationSubstringMatch(): void
    {
        $results = $this->board()->searchJobs(['location' => 'jaip']);
        $this->assertSame(['j1'], array_map(fn (Job $j) => $j->id, $results));
    }

    public function testUnknownIdsReturnNull(): void
    {
        $board = $this->board();
        $this->assertNull($board->getJob('nope'));
        $this->assertNull($board->getCandidate('nope'));
    }
}
