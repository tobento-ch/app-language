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

use Tobento\Service\Repository\Storage\EntityFactory;
use Tobento\Service\Language\LanguageFactoryInterface as ServiceLanguageFactoryInterface;
use Tobento\Service\Language\LanguageInterface;
use Tobento\Service\Language\LanguageException;
use Throwable;

/**
 * LanguageFactory
 */
class LanguageFactory extends EntityFactory implements LanguageFactoryInterface
{
    /**
     * Create a new LanguageFactory.
     *
     * @param ServiceLanguageFactoryInterface $languageFactory
     */
    public function __construct(
        protected ServiceLanguageFactoryInterface $languageFactory,
    ) {
        parent::__construct(null);
    }
    
    /**
     * Create an entity from array.
     *
     * @param array $attributes
     * @return LanguageInterface The created entity.
     */
    public function createEntityFromArray(array $attributes): LanguageInterface
    {
        // Process the columns reading:
        $attributes = $this->columns->processReading($attributes);
        
        try {
            return $this->languageFactory->createLanguage(...$attributes);
        } catch (Throwable $e) {
            throw new LanguageException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }
}