<?php

declare(strict_types=1);

namespace JobBoard\Tests;

use InvalidArgumentException;
use JobBoard\Application;
use JobBoard\Candidate;
use JobBoard\Job;
use JobBoard\JobBoard;
use PHPUnit\Framework\TestCase;

final class BoardApplicationsTest extends TestCase
{
    public function testApplyAndListByJobAndCandidate(): void
    {
        $board = new JobBoard();
        $job = new Job('j1', 'Dev', 'Acme');
        $candidate = new Candidate('c1', 'Asha');
        $app = $board->apply('a1', $job, $candidate, 'Keen to join.');
        $this->assertSame(Application::STATUS_PENDING, $app->status());
        $this->assertCount(1, $board->applicationsForJob('j1'));
        $this->assertCount(1, $board->applicationsForCandidate('c1'));
        $this->assertSame('Keen to join.', $app->coverNote);
    }

    public function testDuplicateLiveApplicationRefused(): void
    {
        $board = new JobBoard();
        $job = new Job('j1', 'Dev', 'Acme');
        $candidate = new Candidate('c1', 'Asha');
        $board->apply('a1', $job, $candidate);
        $this->expectException(InvalidArgumentException::class);
        $board->apply('a2', $job, $candidate);
    }

    public function testReapplyAllowedAfterWithdrawal(): void
    {
        $board = new JobBoard();
        $job = new Job('j1', 'Dev', 'Acme');
        $candidate = new Candidate('c1', 'Asha');
        $board->apply('a1', $job, $candidate)->withdraw();
        $second = $board->apply('a2', $job, $candidate);
        $this->assertSame(Application::STATUS_PENDING, $second->status());
        $this->assertCount(2, $board->applicationsForJob('j1'));
    }
}
