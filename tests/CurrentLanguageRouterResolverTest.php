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
use Tobento\App\Language\CurrentLanguageRouterResolver;
use Tobento\Service\Language\CurrentLanguageResolverInterface;
use Tobento\Service\Language\CurrentLanguageResolverException;
use Tobento\Service\Language\LanguageFactory;
use Tobento\Service\Language\Languages;
use Tobento\Service\Routing;

/**
 * CurrentLanguageRouterResolverTest
 */
class CurrentLanguageRouterResolverTest extends TestCase
{
    protected function createRouter(string $uri, null|string $domain = null): Routing\RouterInterface
    {
        $container = new \Tobento\Service\Container\Container();
        
        return new Routing\Router(
            new Routing\RequestData('GET', $uri, $domain),
            new Routing\UrlGenerator(
                'https://example.com/basepath',
                'a-random-32-character-secret-signature-key',
            ),
            new Routing\RouteFactory(),
            new Routing\RouteDispatcher($container, new Routing\Constrainer\Constrainer()),
            new Routing\RouteHandler($container),
            new Routing\MatchedRouteHandler($container),
            new Routing\RouteResponseParser(),
        );
    }
    
    public function testIsInstanceofCurrentLanguageResolverInterface()
    {
        $resolver = new CurrentLanguageRouterResolver(
            router: $this->createRouter(uri: 'foo'),
        );
        
        $this->assertInstanceOf(
            CurrentLanguageResolverInterface::class,
            $resolver
        );
    }
    
    public function testThrowsCurrentLanguageResolverExceptionNotAllowFallback()
    {
        $this->expectException(CurrentLanguageResolverException::class);
                
        $factory = new LanguageFactory();

        $languages = new Languages(
            $factory->createLanguage('en-US', default: true),
            $factory->createLanguage('de-CH'),
        );
        
        $resolver = new CurrentLanguageRouterResolver(
            router: $this->createRouter(uri: 'fr/foo'),
            allowFallbackToDefaultLanguage: false,
        );
        
        $resolver->resolve($languages);
    }
    
    public function testFallbacksToDefaultLanguageIfNotExist()
    {
        $factory = new LanguageFactory();

        $languages = new Languages(
            $factory->createLanguage('en-US', default: true),
            $factory->createLanguage('de-CH'),
        );
        
        $resolver = new CurrentLanguageRouterResolver(
            router: $this->createRouter(uri: 'fr/foo'),
            allowFallbackToDefaultLanguage: true,
        );
        
        $resolver->resolve($languages);

        $this->assertSame(
            'en-US',
            $languages->current()->locale()
        );
    }
    
    public function testChangesToCurrentLanguage()
    {
        $factory = new LanguageFactory();

        $languages = new Languages(
            $factory->createLanguage('en-US', default: true),
            $factory->createLanguage('de-CH'),
        );
        
        $resolver = new CurrentLanguageRouterResolver(
            router: $this->createRouter(uri: 'de-ch/foo'),
            allowFallbackToDefaultLanguage: false,
        );
        
        $resolver->resolve($languages);

        $this->assertSame(
            'de-CH',
            $languages->current()->locale()
        );
    }
    
    public function testCurrentLanguageIsDeterminedByLanguageSlug()
    {
        $factory = new LanguageFactory();

        $languages = new Languages(
            $factory->createLanguage('en-US', default: true),
            $factory->createLanguage('de-CH', slug: 'de'),
        );
        
        $resolver = new CurrentLanguageRouterResolver(
            router: $this->createRouter(uri: 'de/foo'),
            allowFallbackToDefaultLanguage: false,
        );
        
        $resolver->resolve($languages);

        $this->assertSame(
            'de-CH',
            $languages->current()->locale()
        );
    }
}