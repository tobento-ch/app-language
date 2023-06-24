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
use Tobento\App\Language\LanguageStorageRepository;
use Tobento\App\Language\LanguageRepositoryInterface;
use Tobento\Service\Storage\InMemoryStorage;
use Tobento\App\Language\LanguageFactory;
use Tobento\Service\Language\LanguageFactory as ServiceLanguageFactory;
use Tobento\Service\Language\LanguageInterface;

/**
 * LanguageStorageRepositoryTest
 */
class LanguageStorageRepositoryTest extends TestCase
{
    public function testInterface()
    {
        $repository = new LanguageStorageRepository(
            storage: new  InMemoryStorage(items: []),
            table: 'languages',
            entityFactory: new LanguageFactory(new ServiceLanguageFactory()),
        );
        
        $this->assertInstanceof(LanguageRepositoryInterface::class, $repository);
    }
    
    public function testWriteAndRead()
    {
        $repository = new LanguageStorageRepository(
            storage: new  InMemoryStorage(items: []),
            table: 'languages',
            entityFactory: new LanguageFactory(new ServiceLanguageFactory()),
        );
        
        $language = $repository->create(attributes: [
            'locale' => 'de',
        ]);
        
        $this->assertInstanceof(LanguageInterface::class, $language);
        $this->assertSame('de', $language->locale());
        
        $language = $repository->findOne(where: [
            'locale' => 'de',
        ]);
        
        $this->assertInstanceof(LanguageInterface::class, $language);
        $this->assertSame('de', $language->locale());
    }
}