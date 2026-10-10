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
 * AreaInterface
 *
 * Represents a single language area.
 * Areas define where languages are used (routing, resources, backend tools, API, etc.).
 */
interface AreaInterface
{
    /**
     * Returns the unique area name.
     *
     * Example: "default", "resources", "backend", "api".
     *
     * @return string
     */
    public function name(): string;

    /**
     * Returns the human‑readable title for UI display.
     *
     * Example: "Default", "Resources", "Backend".
     *
     * @return string
     */
    public function title(): string;

    /**
     * Returns the descriptive text for UI infoText or documentation.
     *
     * Example: "Used for routing and public language selection."
     *
     * @return string
     */
    public function description(): string;

    /**
     * Returns the app identifier that registered the area.
     *
     * Example: "frontend", "backend", "cms", "api".
     *
     * @return string
     */
    public function appId(): string;
}