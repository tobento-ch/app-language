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

class AreaTest extends TestCase
{
    public function testImplementsAreaInterface()
    {
        $area = new Area(
            name: 'default',
            title: 'Default',
            description: 'Used for routing.',
            appId: 'frontend',
        );

        $this->assertInstanceOf(AreaInterface::class, $area);
    }

    public function testGetters()
    {
        $area = new Area(
            name: 'default',
            title: 'Default',
            description: 'Used for routing and public language selection.',
            appId: 'frontend',
        );

        $this->assertSame('default', $area->name());
        $this->assertSame('Default', $area->title());
        $this->assertSame('Used for routing and public language selection.', $area->description());
        $this->assertSame('frontend', $area->appId());
    }
}