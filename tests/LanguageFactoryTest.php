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
use Tobento\App\Language\LanguageFactory;
use Tobento\App\Language\LanguageFactoryInterface;
use Tobento\Service\Language\LanguageFactory as ServiceLanguageFactory;
use Tobento\Service\Language\LanguageInterface;
use Tobento\Service\Language\LanguageException;

/**
 * LanguageFactoryTest
 */
class LanguageFactoryTest extends TestCase
{
    public function testInterface()
    {
        $factory = new LanguageFactory(new ServiceLanguageFactory());
        
        $this->assertInstanceof(LanguageFactoryInterface::class, $factory);
    }
    
    public function testCreateEntityFromArrayMethod()
    {
        $factory = new LanguageFactory(new ServiceLanguageFactory());
        
        $language = $factory->createEntityFromArray(['locale' => 'de']);
        
        $this->assertSame('de', $language->locale());
    }
    
    public function testCreateEntityFromArrayMethodThrowsLanguageExceptionOnInvalidData()
    {
        $this->expectException(LanguageException::class);
        
        $factory = new LanguageFactory(new ServiceLanguageFactory());
        
        $language = $factory->createEntityFromArray(['locale' => 'de', 'invalid' => 'value']);
    }
}