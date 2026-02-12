<?php

// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\SetList;
use Rector\ValueObject\PhpVersion;
use RectorLaravel\Set\LaravelSetList;

/**
 * PHP Setup - Returns paths and sets for basic PHP projects
 *
 * @return array{paths: array<string>, sets: array<string>, skip: array<string>}
 */
function phpSetup(): array
{
    return [
        'paths' => [
            // Basic library/package
            __DIR__ . '/src',
            __DIR__ . '/tests',
        ],
        'sets' => [
            // Basic PHP rules
            SetList::PHP_82,
            SetList::CODE_QUALITY,
            SetList::CODING_STYLE,
            SetList::DEAD_CODE,
            SetList::NAMING,
            SetList::PRIVATIZATION,
            SetList::TYPE_DECLARATION,
            SetList::EARLY_RETURN,

            // PHPUnit rules
            PHPUnitSetList::PHPUNIT_110,
            PHPUnitSetList::PHPUNIT_CODE_QUALITY,
            PHPUnitSetList::ANNOTATIONS_TO_ATTRIBUTES,
        ],
        'skip' => [
            // Tests fixtures
            __DIR__ . '/tests/fixtures',

            // Specific rules
            Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector::class,
        ],
    ];
}

/**
 * Laravel Setup - Returns paths and sets extending PHP setup with Laravel-specific configuration
 *
 * @return array{paths: array<string>, sets: array<string>, skip: array<string>}
 */
function laravelSetup(): array
{
    $phpConfig = phpSetup();

    return [
        'paths' => array_merge($phpConfig['paths'], [
            __DIR__ . '/src',
        ]),
        'sets' => array_merge($phpConfig['sets'], [
            // Laravel specific rules
            LaravelSetList::LARAVEL_110,
            LaravelSetList::LARAVEL_CODE_QUALITY,
            LaravelSetList::LARAVEL_COLLECTION,
            LaravelSetList::LARAVEL_ELOQUENT_MAGIC_METHOD_TO_QUERY_BUILDER,
        ]),
        'skip' => $phpConfig['skip'],
    ];
}

/**
 * Nova Setup - Returns paths and sets extending Laravel setup with Laravel Nova-specific configuration
 *
 * @return array{paths: array<string>, sets: array<string>, skip: array<string>}
 */

// Default configuration - use laravelSetup for Laravel projects
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->cacheDirectory('/tmp/rector-cache');
    $rectorConfig->cacheClass(FileCacheStorage::class);
    $rectorConfig->phpVersion(PhpVersion::PHP_82);

    // Get configuration for Laravel projects
    $config = laravelSetup();

    // Apply configuration
    $rectorConfig->paths($config['paths']);
    $rectorConfig->sets($config['sets']);
    $rectorConfig->skip($config['skip']);
};
