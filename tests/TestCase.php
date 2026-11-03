<?php

namespace Gust\Tests;

use Brain\Monkey;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Base test case. Sets up Brain Monkey so WordPress functions can be stubbed.
 *
 * Brain Monkey defines apply_filters() and do_action() for us: apply_filters()
 * returns its first value unless a test sets an expectation for it. The esc_*
 * functions are stubbed with htmlspecialchars()-style escaping.
 */
abstract class TestCase extends BaseTestCase
{
    use MockeryPHPUnitIntegration;

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        Monkey\Functions\stubEscapeFunctions();
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }
}
