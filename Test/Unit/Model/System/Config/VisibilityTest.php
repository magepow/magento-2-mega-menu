<?php
/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Magiccart\Magicmenu\Test\Unit\Model\System\Config;

use Magiccart\Magicmenu\Model\System\Config\Visibility;
use PHPUnit\Framework\TestCase;

class VisibilityTest extends TestCase
{
    public function testGetVisibilityReturnsAllOptions(): void
    {
        $options = Visibility::getVisibility();

        $this->assertArrayHasKey(Visibility::BOTH, $options);
        $this->assertArrayHasKey(Visibility::DESKTOP_ONLY, $options);
        $this->assertArrayHasKey(Visibility::MOBILE_ONLY, $options);
        $this->assertCount(3, $options);
    }

    public function testToOptionArrayReturnsValueLabelPairs(): void
    {
        $options = (new Visibility())->toOptionArray();

        $this->assertCount(3, $options);
        $this->assertSame(Visibility::BOTH, $options[0]['value']);
        $this->assertSame(Visibility::DESKTOP_ONLY, $options[1]['value']);
        $this->assertSame(Visibility::MOBILE_ONLY, $options[2]['value']);
        $this->assertSame(
            (string)Visibility::getVisibility()[Visibility::BOTH],
            (string)$options[0]['label']
        );
    }

    /**
     * @dataProvider matchesProvider
     * @param mixed $itemVisibility
     * @param int|string $visibility
     * @param bool $expected
     */
    public function testMatchesReturnsExpectedValue($itemVisibility, $visibility, bool $expected): void
    {
        $this->assertSame($expected, Visibility::matches($itemVisibility, $visibility));
    }

    /**
     * @return array
     */
    public static function matchesProvider(): array
    {
        return [
            'both item on desktop' => [Visibility::BOTH, Visibility::DESKTOP_ONLY, true],
            'both item on mobile' => [Visibility::BOTH, Visibility::MOBILE_ONLY, true],
            'desktop item on desktop' => [Visibility::DESKTOP_ONLY, Visibility::DESKTOP_ONLY, true],
            'desktop item on mobile' => [Visibility::DESKTOP_ONLY, Visibility::MOBILE_ONLY, false],
            'mobile item on mobile' => [Visibility::MOBILE_ONLY, Visibility::MOBILE_ONLY, true],
            'mobile item on desktop' => [Visibility::MOBILE_ONLY, Visibility::DESKTOP_ONLY, false],
            'missing value is treated as both' => [null, Visibility::DESKTOP_ONLY, true],
            'missing value is treated as both on mobile' => [null, Visibility::MOBILE_ONLY, true],
        ];
    }

    /**
     * @dataProvider invalidVisibilityProvider
     * @param mixed $visibility
     */
    public function testIsValidReturnsFalseForUnsupportedValue($visibility): void
    {
        $this->assertFalse(Visibility::isValid($visibility));
    }

    /**
     * @return array
     */
    public static function invalidVisibilityProvider(): array
    {
        return [
            'null' => [null],
            'not a number' => ['mobile'],
            'out of range' => [5],
            'negative' => [-1],
            'array' => [[]],
        ];
    }

    public function testIsValidReturnsTrueForSupportedValue(): void
    {
        $this->assertTrue(Visibility::isValid(Visibility::BOTH));
        $this->assertTrue(Visibility::isValid(Visibility::DESKTOP_ONLY));
        $this->assertTrue(Visibility::isValid(Visibility::MOBILE_ONLY));
        $this->assertTrue(Visibility::isValid('2'));
    }

    /**
     * @dataProvider normalizeProvider
     * @param mixed $visibility
     * @param int $expected
     */
    public function testNormalizeReturnsExpectedValue($visibility, int $expected): void
    {
        $this->assertSame($expected, Visibility::normalize($visibility));
    }

    /**
     * @return array
     */
    public static function normalizeProvider(): array
    {
        return [
            'both' => [Visibility::BOTH, Visibility::BOTH],
            'desktop only' => [Visibility::DESKTOP_ONLY, Visibility::DESKTOP_ONLY],
            'mobile only' => [Visibility::MOBILE_ONLY, Visibility::MOBILE_ONLY],
            'numeric string' => ['1', Visibility::DESKTOP_ONLY],
            'unsupported' => ['mobile', Visibility::BOTH],
            'null' => [null, Visibility::BOTH],
        ];
    }
}
