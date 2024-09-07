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
use Tobento\App\Language\LanguagesFactory;
use Tobento\Service\Language\LanguageFactory;
use Tobento\Service\Language\LanguagesFactoryInterface;
use Tobento\Service\Language\LanguagesInterface;
use Nyholm\Psr7\Factory\Psr17Factory;

class LanguagesFactoryTest extends TestCase
{
    public function testInterface()
    {
        $request = (new Psr17Factory())->createServerRequest('GET', 'https://example.com');
        $factory = new LanguagesFactory($request);
        
        $this->assertInstanceof(LanguagesFactoryInterface::class, $factory);
    }
    
    public function testCreateLanguagesMethodSortsByCurrentDomain()
    {
        $factory = new LanguageFactory();
        $request = (new Psr17Factory())->createServerRequest('GET', 'https://example.ch');
        $languagesFactory = new LanguagesFactory($request);
        
        $languages = $languagesFactory->createLanguages(
            $factory->createLanguage(locale: 'en', domain: 'example.com', default: true),
            $factory->createLanguage(locale: 'de-CH', domain: 'example.ch', default: true),
            $factory->createLanguage(locale: 'fr', domain: 'example.fr'),
            $factory->createLanguage(locale: 'fr-CH', domain: 'example.ch'),
        );
        
        $this->assertSame(['fr-CH', 'de-CH', 'en', 'fr'], $languages->column('locale'));
        
        $request = (new Psr17Factory())->createServerRequest('GET', 'https://example.ch');
        $languagesFactory = new LanguagesFactory($request);
        
        $languages = $languagesFactory->createLanguages(
            $factory->createLanguage(locale: 'en', default: true),
            $factory->createLanguage(locale: 'de-CH', domain: 'example.ch', default: true),
            $factory->createLanguage(locale: 'fr', domain: 'example.fr'),
            $factory->createLanguage(locale: 'fr-CH', domain: 'example.ch'),
        );
        
        $this->assertSame(['fr-CH', 'de-CH', 'en', 'fr'], $languages->column('locale'));
    }
}