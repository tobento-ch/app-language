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

namespace Tobento\App\Language;

use Tobento\Service\Routing\RouteInterface;
use Tobento\Service\Routing\RouteGroupInterface;
use Tobento\Service\Routing\RouteResourceInterface;

/**
 * RouteLocalizerInterface
 */
interface RouteLocalizerInterface
{
    /**
     * Localize the specified route.
     *
     * @param RouteInterface|RouteGroupInterface|RouteResourceInterface $route
     * @param string ...$uriSegments The uri segments to get translated.
     * @return RouteInterface|RouteGroupInterface|RouteResourceInterface
     */
    public function localizeRoute(
        RouteInterface|RouteGroupInterface|RouteResourceInterface $route,
        string ...$uriSegments,
    ): RouteInterface|RouteGroupInterface|RouteResourceInterface;
}