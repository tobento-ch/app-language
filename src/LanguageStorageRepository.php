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

use Tobento\Service\Repository\Storage\StorageRepository;
use Tobento\Service\Repository\Storage\Column\ColumnsInterface;
use Tobento\Service\Repository\Storage\Column\ColumnInterface;
use Tobento\Service\Repository\Storage\Column;
use Tobento\Service\Repository\RepositoryReadException;
use Tobento\Service\Language\LanguageInterface;

/**
 * LanguageStorageRepository
 */
class LanguageStorageRepository extends StorageRepository implements LanguageRepositoryInterface
{
    /**
     * Returns the configured columns.
     *
     * @return iterable<ColumnInterface>|ColumnsInterface
     */
    protected function configureColumns(): iterable|ColumnsInterface
    {
        return [
            Column\Id::new(),
            Column\Text::new('locale', type: 'char')->type(length: 5),
            Column\Text::new('iso', type: 'char')->type(length: 2),
            Column\Text::new('region', type: 'char')->type(length: 2),
            Column\Text::new('name'),
            Column\Text::new('key'),
            Column\Text::new('slug'),
            Column\Text::new('directory'),
            Column\Text::new('direction', type: 'char')->type(length: 3),
            Column\Text::new('area'),
            Column\Text::new('domain'),
            Column\Text::new('url'),
            Column\Text::new('fallback', type: 'char')->type(length: 5),
            Column\Boolean::new('default'),
            Column\Boolean::new('active'),
            Column\Boolean::new('editable'),
            Column\Integer::new('order'),
        ];
    }
}