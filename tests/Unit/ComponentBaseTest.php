<?php

namespace Gust\Tests\Unit;

use Brain\Monkey\Filters;
use Gust\Tests\Fixtures\FakeComponent;
use Gust\Tests\TestCase;

class ComponentBaseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        FakeComponent::$calls = [];
    }

    public function test_defaults_fill_missing_and_null_args(): void
    {
        $component = FakeComponent::make(level: null);

        $this->assertInstanceOf(FakeComponent::class, $component);
        $this->assertSame('Default heading', $component->heading);
        $this->assertSame(2, $component->level);
    }

    public function test_passed_args_override_defaults(): void
    {
        $component = FakeComponent::make(heading: 'Hello', level: 3);

        $this->assertSame('Hello', $component->heading);
        $this->assertSame(3, $component->level);
    }

    public function test_validate_then_transform_both_see_defaults(): void
    {
        FakeComponent::make(heading: 'Hello');

        $this->assertSame(['validate', 'transform'], array_column(FakeComponent::$calls, 0));

        foreach (FakeComponent::$calls as [$hook, $args]) {
            $this->assertSame(2, $args['level'], "$hook() should receive the default level");
        }
    }

    public function test_transform_output_becomes_the_component_args(): void
    {
        $component = FakeComponent::make(heading: 'Hello', level: 4, classes: ['extra']);

        $this->assertSame(['extra', 'fake--level-4'], $component->classes);
    }

    public function test_failed_validation_returns_null_and_skips_transform(): void
    {
        $component = FakeComponent::make(heading: '');

        $this->assertNull($component);
        $this->assertSame(['validate'], array_column(FakeComponent::$calls, 0));
    }

    public function test_null_component_renders_as_empty_string(): void
    {
        $this->assertSame('', (string) FakeComponent::make(heading: ''));
    }

    public function test_extra_named_args_are_merged_into_args(): void
    {
        $component = FakeComponent::make(heading: 'Hello', subtitle: 'Sub');

        $this->assertSame('Sub', $component->subtitle);
        $this->assertArrayNotHasKey('others', $component->args);
    }

    public function test_make_from_args_runs_the_same_pipeline(): void
    {
        $component = FakeComponent::makeFromArgs(['level' => 5]);

        $this->assertSame('Default heading', $component->heading);
        $this->assertSame(['fake--level-5'], $component->classes);
        $this->assertNull(FakeComponent::makeFromArgs(['heading' => '']));
    }

    public function test_component_filter_can_change_args_after_transform(): void
    {
        Filters\expectApplied('gust/component/fake')
            ->once()
            ->andReturnUsing(function (array $args) {
                $this->assertContains('fake--level-2', $args['classes'], 'filter runs after transform()');
                $args['heading'] = 'Filtered';

                return $args;
            });

        $component = FakeComponent::make(heading: 'Hello');

        $this->assertSame('Filtered', $component->heading);
    }

    public function test_unknown_args_read_as_null(): void
    {
        $component = FakeComponent::make();

        $this->assertNull($component->missing);
        $this->assertFalse(isset($component->missing));
        $this->assertTrue(isset($component->heading));
    }
}
