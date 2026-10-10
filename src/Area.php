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

/**
 * Area
 *
 * Represents a single language area.
 * Areas define where languages are used (routing, resources, backend tools, API, etc.).
 */
class Area implements AreaInterface
{
    /**
     * Create a new Area.
     *
     * @param string $name The unique area name (e.g. "default", "resources", "backend", "api").
     * @param string $title Human‑readable title for UI display.
     * @param string $description Descriptive text for UI infoText or documentation.
     * @param string $appId Identifier of the app that registered the area (e.g. "frontend", "backend", "cms").
     */
    public function __construct(
        protected string $name,
        protected string $title,
        protected string $description,
        protected string $appId,
    ) {}

    /**
     * Returns the unique area name.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Returns the human‑readable title.
     *
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * Returns the descriptive text.
     *
     * @return string
     */
    public function description(): string
    {
        return $this->description;
    }

    /**
     * Returns the app identifier that registered the area.
     *
     * @return string
     */
    public function appId(): string
    {
        return $this->appId;
    }
}