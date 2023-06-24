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

namespace Tobento\App\Language\Migration;

use Tobento\Service\Migration\MigrationInterface;
use Tobento\Service\Migration\ActionsInterface;
use Tobento\Service\Migration\Actions;
use Tobento\Service\Repository\Storage\Migration\RepositoryAction;
use Tobento\Service\Repository\Storage\Migration\RepositoryDeleteAction;
use Tobento\Service\Repository\Storage\StorageRepository;
use Tobento\Service\Repository\Storage\StorageReadRepository;
use Tobento\Service\Repository\Storage\StorageWriteRepository;
use Tobento\App\Language\LanguageRepositoryInterface;

/**
 * LanguageStorageRepository
 */
class LanguageStorageRepository implements MigrationInterface
{
    /**
     * Create a new LanguageRepository.
     *
     * @param LanguageRepositoryInterface $languageRepository
     */
    public function __construct(
        protected LanguageRepositoryInterface $languageRepository,
    ) {}
    
    /**
     * Return a description of the migration.
     *
     * @return string
     */
    public function description(): string
    {
        return 'Language repository data.';
    }
        
    /**
     * Return the actions to be processed on install.
     *
     * @return ActionsInterface
     */
    public function install(): ActionsInterface
    {
        if (
            $this->languageRepository instanceof StorageRepository
            || $this->languageRepository instanceof StorageReadRepository
            || $this->languageRepository instanceof StorageWriteRepository
        ) {
            return new Actions(
                new RepositoryAction(
                    repository: $this->languageRepository,
                    description: 'Language repository data',
                    items: [
                        ['locale' => 'en', 'default' => true, 'active' => true],
                    ],
                ),
            );
        }
        
        return new Actions();
    }

    /**
     * Return the actions to be processed on uninstall.
     *
     * @return ActionsInterface
     */
    public function uninstall(): ActionsInterface
    {
        if (
            $this->languageRepository instanceof StorageRepository
            || $this->languageRepository instanceof StorageReadRepository
            || $this->languageRepository instanceof StorageWriteRepository
        ) {
            return new Actions(
                new RepositoryDeleteAction(
                    repository: $this->languageRepository,
                    description: 'Language repository data',
                ),
            );
        }
        
        return new Actions();
    }
}