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
            new Column\Id(),
            new Column\Text(name: 'locale', type: 'char')->type(length: 5),
            new Column\Text(name: 'iso', type: 'char')->type(length: 2),
            new Column\Text(name: 'region', type: 'char')->type(length: 2),
            new Column\Text(name: 'name'),
            new Column\Text(name: 'key'),
            new Column\Text(name: 'slug'),
            new Column\Text(name: 'directory'),
            new Column\Text(name: 'direction', type: 'char')->type(length: 3),
            new Column\Text(name: 'area'),
            new Column\Text(name: 'domain'),
            new Column\Text(name: 'url'),
            new Column\Text(name: 'fallback', type: 'char')->type(length: 5),
            new Column\Boolean(name: 'default'),
            new Column\Boolean(name: 'active'),
            new Column\Boolean(name: 'editable'),
            new Column\Integer(name: 'order'),
        ];
    }
}