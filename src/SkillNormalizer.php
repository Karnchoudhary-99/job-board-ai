<?php

declare(strict_types=1);

namespace JobBoard;

/**
 * Normalizes skill names so "ReactJS", "react" and "React.js" all match
 * the same skill, and a small alias map collapses common synonyms.
 */
final class SkillNormalizer
{
    /** @var array<string, string> */
    private const ALIASES = [
        'js' => 'javascript',
        'py' => 'python',
        'ts' => 'typescript',
        'reactjs' => 'react',
        'react.js' => 'react',
        'nodejs' => 'node.js',
        'node' => 'node.js',
        'golang' => 'go',
        'k8s' => 'kubernetes',
        'postgres' => 'postgresql',
        'mysql' => 'mysql',
        'html5' => 'html',
        'css3' => 'css',
        'ml' => 'machine learning',
    ];

    public static function normalize(string $skill): string
    {
        $key = strtolower(trim($skill));
        $key = preg_replace('/\s+/', ' ', $key) ?? $key;
        return self::ALIASES[$key] ?? $key;
    }

    /**
     * @param string[] $skills
     * @return string[] normalized, de-duplicated, in first-seen order
     */
    public static function normalizeAll(array $skills): array
    {
        $out = [];
        foreach ($skills as $skill) {
            $normalized = self::normalize((string) $skill);
            if ($normalized === '' || in_array($normalized, $out, true)) {
                continue;
            }
            $out[] = $normalized;
        }
        return $out;
    }
}
