<?php

declare(strict_types=1);

namespace JobBoard\Tests;

use JobBoard\Application;
use JobBoard\Candidate;
use JobBoard\Job;
use LogicException;
use PHPUnit\Framework\TestCase;

final class ApplicationTest extends TestCase
{
    public function testStartsPendingAndMovesThroughStates(): void
    {
        $app = new Application('a1', new Job('j1', 'Dev', 'Acme'), new Candidate('c1', 'Asha'));
        $this->assertSame(Application::STATUS_PENDING, $app->status());
        $app->review();
        $app->shortlist();
        $this->assertSame(Application::STATUS_SHORTLISTED, $app->status());
    }

    public function testCannotApplyToClosedJob(): void
    {
        $job = new Job('j1', 'Dev', 'Acme');
        $job->close();
        $this->expectException(LogicException::class);
        new Application('a1', $job, new Candidate('c1', 'Asha'));
    }

    public function testWithdrawnApplicationIsTerminal(): void
    {
        $app = new Application('a1', new Job('j1', 'Dev', 'Acme'), new Candidate('c1', 'Asha'));
        $app->withdraw();
        $this->expectException(LogicException::class);
        $app->shortlist();
    }

    public function testRejectedApplicationIsTerminal(): void
    {
        $app = new Application('a1', new Job('j1', 'Dev', 'Acme'), new Candidate('c1', 'Asha'));
        $app->reject();
        $this->expectException(LogicException::class);
        $app->review();
    }
}
