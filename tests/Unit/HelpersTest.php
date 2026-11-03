<?php

namespace Gust\Tests\Unit;

use Brain\Monkey\Functions;
use Gust\Helpers;
use Gust\Tests\TestCase;

class HelpersTest extends TestCase
{
    // ------------------------------------------------------------------
    // classes()
    // ------------------------------------------------------------------

    public function test_classes_joins_strings_and_arrays(): void
    {
        $this->assertSame('card card--wide is-active', Helpers::classes('card', ['card--wide', 'is-active']));
    }

    public function test_classes_flattens_nested_arrays(): void
    {
        $this->assertSame('a b c d', Helpers::classes('a', ['b', ['c', ['d']]]));
    }

    public function test_classes_trims_and_skips_empty_values(): void
    {
        $this->assertSame('a b', Helpers::classes(' a ', '', '   ', [null, false, 'b']));
    }

    public function test_classes_removes_duplicates(): void
    {
        $this->assertSame('a b', Helpers::classes('a', ['b', 'a'], 'b'));
    }

    public function test_classes_returns_empty_string_for_nothing(): void
    {
        $this->assertSame('', Helpers::classes());
        $this->assertSame('', Helpers::classes([], ''));
    }

    public function test_classes_escapes_output(): void
    {
        $this->assertSame('a&quot;b', Helpers::classes('a"b'));
    }

    public function test_global_classes_helper_matches_helpers_class(): void
    {
        $this->assertSame(Helpers::classes('a', ['b']), classes('a', ['b']));
    }

    // ------------------------------------------------------------------
    // buildAttributes()
    // ------------------------------------------------------------------

    public function test_build_attributes_returns_empty_string_for_no_attributes(): void
    {
        $this->assertSame('', Helpers::buildAttributes());
        $this->assertSame('', Helpers::buildAttributes([]));
    }

    public function test_build_attributes_renders_scalars(): void
    {
        $this->assertSame(
            'id="main" data-count="3" aria-label="Main &amp; more"',
            Helpers::buildAttributes(['id' => 'main', 'data-count' => 3, 'aria-label' => 'Main & more'])
        );
    }

    public function test_build_attributes_renders_boolean_attributes(): void
    {
        $this->assertSame('hidden', Helpers::buildAttributes(['hidden' => true]));
        $this->assertSame('', Helpers::buildAttributes(['hidden' => false]));
    }

    public function test_build_attributes_treats_empty_string_as_a_present_attribute(): void
    {
        $this->assertSame('inert=""', Helpers::buildAttributes(['inert' => '']));
    }

    public function test_build_attributes_skips_null_and_invalid_values(): void
    {
        $this->assertSame('', Helpers::buildAttributes(['title' => null]));
        $this->assertSame('', Helpers::buildAttributes(['title' => new \stdClass]));
    }

    public function test_build_attributes_joins_array_values_and_drops_empty_ones(): void
    {
        $this->assertSame(
            'class="a b 0"',
            Helpers::buildAttributes(['class' => ['a', '', 'b', 'a', 0, ['nested']]])
        );
    }

    public function test_build_attributes_skips_arrays_with_no_scalar_values(): void
    {
        $this->assertSame('', Helpers::buildAttributes(['class' => [['nested']]]));
    }

    public function test_build_attributes_builds_style_declarations(): void
    {
        $this->assertSame(
            'style="color: red; margin: 0;"',
            Helpers::buildAttributes(['style' => ['color' => 'red', 'margin' => 0, 'ignored']])
        );
    }

    public function test_build_attributes_uses_esc_url_for_url_attributes(): void
    {
        Functions\when('esc_url')->alias(fn ($url) => 'URL('.$url.')');

        $this->assertSame(
            'href="URL(/about)" title="/about"',
            Helpers::buildAttributes(['href' => ' /about ', 'title' => '/about'])
        );
    }

    /**
     * Known quirk: a skipped attribute still leaves its separator behind.
     * This test documents current behaviour (harmless in HTML). Update it if
     * buildAttributes() starts filtering out skipped attributes.
     */
    public function test_build_attributes_leaves_a_space_for_skipped_attributes(): void
    {
        $this->assertSame(' id="x"', Helpers::buildAttributes(['hidden' => false, 'id' => 'x']));
    }

    public function test_global_attributes_helper_matches_helpers_class(): void
    {
        $this->assertSame(Helpers::buildAttributes(['id' => 'x']), attributes(['id' => 'x']));
    }

    // ------------------------------------------------------------------
    // isUrlAttribute()
    // ------------------------------------------------------------------

    public function test_is_url_attribute(): void
    {
        $this->assertTrue(Helpers::isUrlAttribute('href'));
        $this->assertTrue(Helpers::isUrlAttribute('src'));
        $this->assertFalse(Helpers::isUrlAttribute('title'));
        $this->assertFalse(Helpers::isUrlAttribute(''));
    }
}
