<?php

declare(strict_types=1);

namespace JobBoard\Tests;

use JobBoard\Candidate;
use JobBoard\Job;
use JobBoard\JobBoard;
use PHPUnit\Framework\TestCase;

final class RankingTest extends TestCase
{
    private function board(): JobBoard
    {
        $board = new JobBoard();
        $board->addJob(new Job('j1', 'PHP Dev', 'Acme', location: 'Jaipur', requiredSkills: ['php', 'mysql'], minExperienceYears: 2));
        $board->addCandidate(new Candidate('c1', 'Asha', skills: ['php', 'mysql'], experienceYears: 3, location: 'Jaipur'));
        $board->addCandidate(new Candidate('c2', 'Ben', skills: ['php'], experienceYears: 2, location: 'Jaipur'));
        $board->addCandidate(new Candidate('c3', 'Cid', skills: [], experienceYears: 0, location: 'Delhi'));
        return $board;
    }

    public function testCandidatesRankedBestFirst(): void
    {
        $ranked = $this->board()->rankCandidatesForJob($this->board()->getJob('j1'));
        $this->assertSame(['c1', 'c2', 'c3'], array_map(fn ($r) => $r->candidate->id, $ranked));
        $this->assertSame(100.0, $ranked[0]->score);
    }

    public function testMinScoreFiltersWeakMatches(): void
    {
        $ranked = $this->board()->rankCandidatesForJob($this->board()->getJob('j1'), minScore: 55);
        $this->assertSame(['c1', 'c2'], array_map(fn ($r) => $r->candidate->id, $ranked));
    }

    public function testClosedJobRanksNobody(): void
    {
        $board = $this->board();
        $board->getJob('j1')->close();
        $this->assertSame([], $board->rankCandidatesForJob($board->getJob('j1')));
        $this->assertSame([], $board->rankJobsForCandidate($board->getCandidate('c1')));
    }
}
