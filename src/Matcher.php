<?php

declare(strict_types=1);

namespace JobBoard;

/**
 * Transparent match scoring (100 points total):
 *   required skills  55 — share of required skills the candidate has
 *   preferred skills 15 — share of preferred skills the candidate has
 *   experience       20 — full at/above the minimum, else proportional
 *   location         10 — remote jobs always pass; on-site needs a
 *                          location match (case-insensitive substring)
 * A job with no required skills awards the full 55 (nothing to miss);
 * a job with no preferred skills awards 0 of the 15 bonus points.
 */
final class Matcher
{
    public static function match(Candidate $candidate, Job $job): MatchResult
    {
        $matchedRequired = array_values(array_filter(
            $job->requiredSkills,
            fn (string $s) => $candidate->hasSkill($s),
        ));
        $missingRequired = array_values(array_diff($job->requiredSkills, $matchedRequired));
        $requiredScore = $job->requiredSkills === []
            ? 55.0
            : 55.0 * count($matchedRequired) / count($job->requiredSkills);

        $matchedPreferred = array_values(array_filter(
            $job->preferredSkills,
            fn (string $s) => $candidate->hasSkill($s),
        ));
        $preferredScore = $job->preferredSkills === []
            ? 0.0
            : 15.0 * count($matchedPreferred) / count($job->preferredSkills);

        $experienceScore = $job->minExperienceYears <= 0
            ? 20.0
            : 20.0 * min(1.0, $candidate->experienceYears / $job->minExperienceYears);

        $locationScore = self::locationScore($candidate, $job);

        $breakdown = [
            'required_skills' => round($requiredScore, 1),
            'preferred_skills' => round($preferredScore, 1),
            'experience' => round($experienceScore, 1),
            'location' => round($locationScore, 1),
        ];
        $score = round(array_sum($breakdown), 1);

        $explanations = [];
        $explanations[] = $missingRequired === []
            ? 'Meets all required skills.'
            : 'Missing required skills: ' . implode(', ', $missingRequired) . '.';
        if ($matchedPreferred !== []) {
            $explanations[] = 'Bonus skills: ' . implode(', ', $matchedPreferred) . '.';
        }
        $explanations[] = $job->minExperienceYears <= 0
            ? 'No minimum experience required.'
            : ($candidate->experienceYears >= $job->minExperienceYears
                ? "Meets the {$job->minExperienceYears}+ years experience requirement."
                : "Has {$candidate->experienceYears} years vs {$job->minExperienceYears} required.");
        $explanations[] = $locationScore === 10.0
            ? 'Location is a fit.'
            : 'Location mismatch: job is in ' . ($job->location ?: 'an unspecified location') . '.';

        return new MatchResult($candidate, $job, $score, $breakdown, $explanations);
    }

    private static function locationScore(Candidate $candidate, Job $job): float
    {
        if ($job->remote || $job->location === '') {
            return 10.0;
        }
        if ($candidate->location === '') {
            return 0.0;
        }
        $jobLoc = strtolower($job->location);
        $candLoc = strtolower($candidate->location);
        return (str_contains($jobLoc, $candLoc) || str_contains($candLoc, $jobLoc)) ? 10.0 : 0.0;
    }
}
