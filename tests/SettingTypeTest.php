<?php

namespace NimblePHP\Settings\Tests;

use NimblePHP\Settings\SettingType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SettingTypeTest extends TestCase
{

    #[DataProvider('detectionCases')]
    public function testDetect(mixed $value, SettingType $expected): void
    {
        $this->assertSame($expected, SettingType::detect($value));
    }

    /**
     * @return array<string, array{0: mixed, 1: SettingType}>
     */
    public static function detectionCases(): array
    {
        return [
            'bool' => [true, SettingType::boolean],
            'int' => [42, SettingType::integer],
            'float' => [3.14, SettingType::float],
            'array' => [['a' => 1], SettingType::json],
            'null' => [null, SettingType::json],
            'string' => ['hello', SettingType::string],
        ];
    }

    public function testGetByKeyFallsBackToString(): void
    {
        $this->assertSame(SettingType::integer, SettingType::getByKey('integer'));
        $this->assertSame(SettingType::string, SettingType::getByKey(null));
        $this->assertSame(SettingType::string, SettingType::getByKey('nope'));
    }

    #[DataProvider('roundTripCases')]
    public function testEncodeDecodeRoundTrip(mixed $value): void
    {
        $type = SettingType::detect($value);
        $decoded = $type->decode($type->encode($value));

        $this->assertSame($value, $decoded);
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function roundTripCases(): array
    {
        return [
            'bool true' => [true],
            'bool false' => [false],
            'int' => [123],
            'int zero' => [0],
            'float' => [1.5],
            'string' => ['lorem ipsum'],
            'empty string' => [''],
            'array' => [['x' => 1, 'y' => ['z' => true]]],
            'list' => [[1, 2, 3]],
            'null' => [null],
        ];
    }

    public function testBooleanEncoding(): void
    {
        $this->assertSame('1', SettingType::boolean->encode(true));
        $this->assertSame('0', SettingType::boolean->encode(false));
        $this->assertTrue(SettingType::boolean->decode('1'));
        $this->assertFalse(SettingType::boolean->decode('0'));
    }

}
