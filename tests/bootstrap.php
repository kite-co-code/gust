<?php

/**
 * PHPUnit bootstrap.
 *
 * Unit tests run without WordPress. WordPress functions are stubbed per test
 * with Brain Monkey (see Gust\Tests\TestCase).
 */
require dirname(__DIR__).'/vendor/autoload.php';

require dirname(__DIR__).'/Gust/functions.php';
