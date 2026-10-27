<?php

declare(strict_types=1);

namespace JobBoard\Tests;

use JobBoard\SkillNormalizer;
use PHPUnit\Framework\TestCase;

final class SkillNormalizerTest extends TestCase
{
    public function testLowercasesAndTrims(): void
    {
        $this->assertSame('python', SkillNormalizer::normalize('  Python '));
    }

    public function testAliasesCollapseSynonyms(): void
    {
        $this->assertSame('react', SkillNormalizer::normalize('ReactJS'));
        $this->assertSame('javascript', SkillNormalizer::normalize('JS'));
        $this->assertSame('go', SkillNormalizer::normalize('Golang'));
    }

    public function testNormalizeAllDeduplicatesAfterNormalization(): void
    {
        $this->assertSame(
            ['python', 'react'],
            SkillNormalizer::normalizeAll(['Python', 'py', 'ReactJS', 'react', '  ']),
        );
    }

    public function testUnknownSkillsPassThroughNormalized(): void
    {
        $this->assertSame('rust', SkillNormalizer::normalize('Rust'));
    }
}
