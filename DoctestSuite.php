<?php

declare(strict_types=1);

namespace Doctest;

use Doctest\Configuration\Configuration;
use Doctest\TestCase\DoctestRunner;

/**
 * PHPUnit test suite file for doctest-php.
 *
 * Add this file to your phpunit.xml testsuite configuration:
 *
 * <testsuites>
 *     <testsuite name="Doctest">
 *         <file>vendor/doctest/doctest-php/DoctestSuite.php</file>
 *     </testsuite>
 * </testsuites>
 *
 * And configure the extension:
 *
 * <extensions>
 *     <bootstrap class="Doctest\DoctestExtension">
 *         <parameter name="directories" value="src"/>
 *     </bootstrap>
 * </extensions>
 */
final class DoctestSuite extends DoctestRunner
{
    protected static function configure(): Configuration
    {
        // Get configuration from DoctestExtension
        $config = DoctestExtension::getConfiguration();

        if ($config === null) {
            // Extension not configured, return empty configuration
            // This will result in no tests being discovered
            return new Configuration(directories: []);
        }

        return $config;
    }
}
