# Job Board with AI Matching

A job board in PHP with AI-powered candidate–job matching: job postings,
candidate profiles, and a transparent match score explaining why a
candidate fits a role. No black box — every score ships with its
breakdown and plain-language reasons.

## Roadmap

- [x] Project scaffold (Composer, PHPUnit, CI, README)
- [x] Job postings + candidate profiles
- [x] AI matching engine (skill overlap, experience fit, explanations)
- [x] Search, ranking and application flow
- [ ] Self-review checklist, then public

## Quick start

```sh
composer install
vendor/bin/phpunit
php examples/demo.php
```

## Core concepts

```php
use JobBoard\Candidate;
use JobBoard\Job;
use JobBoard\JobBoard;

$board = new JobBoard();
$board->addJob(new Job(
    'j1', 'PHP Backend Developer', 'Acme', location: 'Jaipur',
    requiredSkills: ['php', 'mysql'],
    preferredSkills: ['docker'],
    minExperienceYears: 2,
));
$board->addCandidate(new Candidate(
    'c1', 'Asha', skills: ['php', 'mysql', 'docker'],
    experienceYears: 4, location: 'Jaipur',
));

$ranked = $board->rankCandidatesForJob($board->getJob('j1'));
echo $ranked[0]->candidate->name . ': ' . $ranked[0]->score
    . ' (' . $ranked[0]->verdict() . ')'; // Asha: 100 (strong)
```

## How matching works

100 points, always explainable via `MatchResult::$breakdown` and
`MatchResult::$explanations`:

| Component | Points | Rule |
| --- | --- | --- |
| Required skills | 55 | Share of required skills the candidate has |
| Preferred skills | 15 | Share of preferred skills (bonus; 0 if none listed) |
| Experience | 20 | Full at/above the minimum, else proportional |
| Location | 10 | Remote jobs always pass; on-site needs a location match |

Verdicts: **strong** ≥ 75, **good** ≥ 55, **weak** ≥ 35, **poor** below.

Skill names are normalized before matching (`SkillNormalizer`): case and
whitespace are ignored and synonyms collapse — `ReactJS` → `react`,
`JS` → `javascript`, `Golang` → `go` — so a candidate is never missed
over a spelling variant.

## Search and applications

```php
$remote = $board->searchJobs(['remote' => true]);
$phpJobs = $board->searchJobs(['skill' => 'php', 'location' => 'Jaipur']);

$app = $board->apply('a1', $job, $candidate, 'Keen to join.');
$app->review();
$app->shortlist(); // pending → reviewed → shortlisted
```

One live application per candidate per job (re-applying after a
withdrawal is allowed); withdrawn and rejected applications are
terminal, and closed jobs accept no applications and never appear in
rankings.

## License

MIT — see [LICENSE](LICENSE).
