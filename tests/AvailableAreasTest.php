<?php

/**
 * TOBENTO
 *
 * @copyright   Tobias Strub, TOBENTO
 * @license     MIT License, see LICENSE file distributed with this source code.
 * @author      Tobias Strub
 * @link        https://www.tobento.ch
 */

declare(strict_types=1);

namespace Tobento\App\Language\Test;

use PHPUnit\Framework\TestCase;
use Tobento\App\Language\Area;
use Tobento\App\Language\AreaInterface;
use Tobento\App\Language\AvailableAreas;
use Tobento\App\Language\AvailableAreasInterface;

class AvailableAreasTest extends TestCase
{
    protected function makeAreas(): AvailableAreas
    {
        return new AvailableAreas(
            new Area('default', 'Default', 'Default description', 'frontend'),
            new Area('resources', 'Resources', 'Resources description', 'frontend'),
            new Area('backend', 'Backend', 'Backend description', 'backend'),
        );
    }

    public function testImplementsInterfaces()
    {
        $areas = new AvailableAreas();

        $this->assertInstanceOf(AvailableAreasInterface::class, $areas);
        $this->assertInstanceOf(\Countable::class, $areas);
        $this->assertInstanceOf(\IteratorAggregate::class, $areas);
    }

    public function testEmptyByDefault()
    {
        $areas = new AvailableAreas();

        $this->assertSame([], $areas->all());
        $this->assertSame([], $areas->names());
        $this->assertSame(0, $areas->count());
    }

    public function testConstructorRegistersAreas()
    {
        $areas = $this->makeAreas();

        $this->assertSame(3, $areas->count());
        $this->assertSame(['default', 'resources', 'backend'], $areas->names());
    }

    public function testAddMethod()
    {
        $areas = new AvailableAreas();
        $area = new Area('api', 'API', 'API description', 'api');

        $result = $areas->add($area);

        $this->assertSame($areas, $result);
        $this->assertSame($area, $areas->get('api'));
        $this->assertSame(1, $areas->count());
    }

    public function testAddMethodReplacesAreaWithSameName()
    {
        $areas = new AvailableAreas(
            new Area('default', 'Default', 'Old', 'frontend'),
        );

        $new = new Area('default', 'Default', 'New', 'cms');
        $areas->add($new);

        $this->assertSame(1, $areas->count());
        $this->assertSame($new, $areas->get('default'));
    }

    public function testConstructorReplacesAreasWithSameName()
    {
        $second = new Area('default', 'Second', 'Second', 'cms');

        $areas = new AvailableAreas(
            new Area('default', 'First', 'First', 'frontend'),
            $second,
        );

        $this->assertSame(1, $areas->count());
        $this->assertSame($second, $areas->get('default'));
    }

    public function testHasMethod()
    {
        $areas = $this->makeAreas();

        $this->assertTrue($areas->has('default'));
        $this->assertTrue($areas->has('backend'));
        $this->assertFalse($areas->has('api'));
    }

    public function testGetMethod()
    {
        $areas = $this->makeAreas();

        $this->assertInstanceOf(AreaInterface::class, $areas->get('default'));
        $this->assertSame('default', $areas->get('default')->name());
    }

    public function testGetMethodReturnsNullIfNotFound()
    {
        $areas = $this->makeAreas();

        $this->assertNull($areas->get('api'));
    }

    public function testAllMethodReturnsAreasKeyedByName()
    {
        $areas = $this->makeAreas();

        $all = $areas->all();

        $this->assertSame(['default', 'resources', 'backend'], array_keys($all));
        $this->assertSame($areas->get('default'), $all['default']);
        $this->assertSame($areas->get('backend'), $all['backend']);
    }

    public function testNamesMethod()
    {
        $areas = $this->makeAreas();

        $this->assertSame(['default', 'resources', 'backend'], $areas->names());
    }

    public function testTitlesMethod()
    {
        $areas = $this->makeAreas();

        $this->assertSame(
            [
                'default' => 'Default',
                'resources' => 'Resources',
                'backend' => 'Backend',
            ],
            $areas->titles()
        );
    }

    public function testDescriptionsMethod()
    {
        $areas = $this->makeAreas();

        $this->assertSame(
            [
                'default' => 'Default description',
                'resources' => 'Resources description',
                'backend' => 'Backend description',
            ],
            $areas->descriptions()
        );
    }

    public function testTitlesAndDescriptionsAreEmptyIfNoAreas()
    {
        $areas = new AvailableAreas();

        $this->assertSame([], $areas->titles());
        $this->assertSame([], $areas->descriptions());
    }

    public function testAppMethodFiltersByAppId()
    {
        $areas = $this->makeAreas();

        $frontend = $areas->app('frontend');

        $this->assertSame(['default', 'resources'], $frontend->names());
        $this->assertSame(['backend'], $areas->app('backend')->names());
    }

    public function testAppMethodReturnsNewInstanceAndKeepsOriginal()
    {
        $areas = $this->makeAreas();

        $frontend = $areas->app('frontend');

        $this->assertNotSame($areas, $frontend);
        $this->assertSame(3, $areas->count());
        $this->assertSame(2, $frontend->count());
    }

    public function testAppMethodReturnsEmptyIfNoAreaMatches()
    {
        $areas = $this->makeAreas();

        $this->assertSame(0, $areas->app('api')->count());
    }

    public function testFilterMethod()
    {
        $areas = $this->makeAreas();

        $filtered = $areas->filter(
            fn (AreaInterface $area): bool => str_starts_with($area->name(), 'res')
        );

        $this->assertSame(['resources'], $filtered->names());
    }

    public function testFilterMethodReturnsNewInstanceAndKeepsOriginal()
    {
        $areas = $this->makeAreas();

        $filtered = $areas->filter(fn (AreaInterface $area): bool => false);

        $this->assertNotSame($areas, $filtered);
        $this->assertSame(0, $filtered->count());
        $this->assertSame(3, $areas->count());
    }

    public function testFilterMethodKeepsAreaInstancesAndKeys()
    {
        $areas = $this->makeAreas();

        $filtered = $areas->filter(
            fn (AreaInterface $area): bool => $area->appId() === 'backend'
        );

        $this->assertSame(['backend'], array_keys($filtered->all()));
        $this->assertSame($areas->get('backend'), $filtered->get('backend'));
    }

    public function testCountMethod()
    {
        $this->assertSame(3, $this->makeAreas()->count());
    }

    public function testGetIteratorMethodIteratesAreasKeyedByName()
    {
        $areas = $this->makeAreas();

        $names = [];

        foreach ($areas as $name => $area) {
            $this->assertInstanceOf(AreaInterface::class, $area);
            $this->assertSame($name, $area->name());
            $names[] = $name;
        }

        $this->assertSame(['default', 'resources', 'backend'], $names);
    }

    public function testGetIteratorMethodReturnsSameAreasAsAll()
    {
        $areas = $this->makeAreas();

        $this->assertSame($areas->all(), iterator_to_array($areas));
    }
}