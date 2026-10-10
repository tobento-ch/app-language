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

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * AvailableAreas
 *
 * Registry for all defined language areas.
 * Areas may be registered by different apps (frontend, backend, cms, api, modules).
 */
class AvailableAreas implements AvailableAreasInterface
{
    /**
     * @var array<string, AreaInterface>
     */
    protected array $areas = [];

    /**
     * Create a new AvailableAreas registry.
     *
     * @param AreaInterface ...$areas The initial areas to register.
     */
    public function __construct(AreaInterface ...$areas)
    {
        foreach ($areas as $area) {
            $this->areas[$area->name()] = $area;
        }
    }
    
    /**
     * Add a new area to the registry.
     *
     * @param AreaInterface $area The area instance to add.
     * @return static
     */
    public function add(AreaInterface $area): static
    {
        $this->areas[$area->name()] = $area;
        return $this;
    }

    /**
     * Determine if an area exists.
     *
     * @param string $name The area name (e.g. "default", "resources").
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->areas[$name]);
    }

    /**
     * Get an area instance by name.
     *
     * @param string $name The area name.
     * @return null|AreaInterface
     */
    public function get(string $name): null|AreaInterface
    {
        return $this->areas[$name] ?? null;
    }

    /**
     * Get all areas keyed by name.
     *
     * @return array<string, AreaInterface>
     */
    public function all(): array
    {
        return $this->areas;
    }

    /**
     * Get all area names.
     *
     * @return array<int, string>
     */
    public function names(): array
    {
        return array_keys($this->areas);
    }

    /**
     * Get all area titles keyed by name.
     *
     * @return array<string, string>
     */
    public function titles(): array
    {
        $titles = [];
        foreach ($this->areas as $name => $area) {
            $titles[$name] = $area->title();
        }
        return $titles;
    }

    /**
     * Get all area descriptions keyed by name.
     *
     * @return array<string, string>
     */
    public function descriptions(): array
    {
        $descriptions = [];
        foreach ($this->areas as $name => $area) {
            $descriptions[$name] = $area->description();
        }
        return $descriptions;
    }

    /**
     * Filter areas by app identifier.
     *
     * @param string $appId The app identifier (e.g. "frontend", "backend", "cms", "api").
     * @return static
     */
    public function app(string $appId): static
    {
        return $this->filter(fn(AreaInterface $area): bool => $area->appId() === $appId);
    }

    /**
     * Filter method.
     *
     * @param callable $callback Callback receives AreaInterface and must return true to keep the area.
     * @return static
     */
    public function filter(callable $callback): static
    {
        $new = new static();
        foreach ($this->areas as $name => $area) {
            if ($callback($area)) {
                $new->areas[$name] = $area;
            }
        }
        return $new;
    }

    /**
     * Count areas.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->areas);
    }

    /**
     * Get iterator.
     *
     * @return Traversable<string, AreaInterface>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->areas);
    }
}