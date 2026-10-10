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

use Countable;
use IteratorAggregate;

/**
 * AvailableAreasInterface
 *
 * Registry for all defined language areas.
 * Areas may be registered by different apps (frontend, backend, cms, api, modules).
 *
 * @extends IteratorAggregate<string, AreaInterface>
 */
interface AvailableAreasInterface extends Countable, IteratorAggregate
{
    /**
     * Add a new area to the registry.
     *
     * @param AreaInterface $area The area instance to add.
     * @return static
     */
    public function add(AreaInterface $area): static;

    /**
     * Determine if an area exists.
     *
     * @param string $name The area name (e.g. "default", "resources").
     * @return bool True if the area exists, otherwise false.
     */
    public function has(string $name): bool;

    /**
     * Get an area instance by name.
     *
     * @param string $name The area name.
     * @return null|AreaInterface The area instance or null if not found.
     */
    public function get(string $name): null|AreaInterface;

    /**
     * Get all areas keyed by name.
     *
     * @return array<string, AreaInterface> An associative array of area name => AreaInterface.
     */
    public function all(): array;

    /**
     * Get all area names.
     *
     * @return array<int, string> A list of area names.
     */
    public function names(): array;

    /**
     * Get all area titles keyed by name.
     *
     * @return array<string, string> An associative array of area name => title.
     */
    public function titles(): array;

    /**
     * Get all area descriptions keyed by name.
     *
     * @return array<string, string> An associative array of area name => description.
     */
    public function descriptions(): array;
    
    /**
     * Filter areas by app identifier.
     *
     * @param string $appId The app identifier (e.g. "frontend", "backend", "cms", "api").
     * @return static A new immutable instance containing only areas registered by the given app.
     */
    public function app(string $appId): static;
    
    /**
     * Filter method.
     *
     * @param callable $callback Callback receives AreaInterface and must return true to keep the area.
     * @return static A new immutable instance containing only areas passing the filter.
     */
    public function filter(callable $callback): static;
}