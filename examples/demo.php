<?php

declare(strict_types=1);

/**
 * Demo: post two jobs, add three candidates, rank matches for the
 * PHP role, then run one application through review + shortlist.
 *
 * Run: php examples/demo.php
 */

require __DIR__ . '/../vendor/autoload.php';

use JobBoard\Candidate;
use JobBoard\Job;
use JobBoard\JobBoard;

$board = new JobBoard();
$board->addJob(new Job(
    'j1', 'PHP Backend Developer', 'Acme', location: 'Jaipur',
    requiredSkills: ['php', 'mysql'],
    preferredSkills: ['docker', 'redis'],
    minExperienceYears: 2,
));
$board->addJob(new Job(
    'j2', 'Remote Go Developer', 'Beta', remote: true,
    requiredSkills: ['go'], preferredSkills: ['kubernetes'],
    minExperienceYears: 3,
));
$board->addCandidate(new Candidate('c1', 'Asha', skills: ['php', 'mysql', 'docker'], experienceYears: 4, location: 'Jaipur'));
$board->addCandidate(new Candidate('c2', 'Ben', skills: ['php'], experienceYears: 2, location: 'Jaipur'));
$board->addCandidate(new Candidate('c3', 'Cid', skills: ['go', 'kubernetes'], experienceYears: 5, location: 'Berlin', openToRemote: true));

$job = $board->getJob('j1');
echo "Ranked candidates for '{$job->title}' ({$job->company}):\n";
foreach ($board->rankCandidatesForJob($job) as $result) {
    $breakdown = implode(', ', array_map(
        fn (string $k, float $v) => "$k $v",
        array_keys($result->breakdown),
        $result->breakdown,
    ));
    echo sprintf("- %s: %.1f (%s) [%s]\n", $result->candidate->name, $result->score, $result->verdict(), $breakdown);
    foreach ($result->explanations as $explanation) {
        echo "    * $explanation\n";
    }
}

echo "\nBest match for the Go role: ";
$goRanked = $board->rankJobsForCandidate($board->getCandidate('c3'));
echo $goRanked[0]->job->title . " ({$goRanked[0]->score}, {$goRanked[0]->verdict()})\n";

$app = $board->apply('a1', $job, $board->getCandidate('c1'), 'Built PHP APIs for 4 years.');
$app->review();
$app->shortlist();
echo "Application a1 for {$app->candidate->name}: {$app->status()}\n";
