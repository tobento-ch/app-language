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
use Tobento\App\Language\RouteLocalizer;
use Tobento\App\Language\RouteLocalizerInterface;
use Tobento\Service\Language\LanguagesFactory;
use Tobento\Service\Language\LanguageFactory;
use Tobento\Service\Language\Languages;
use Tobento\Service\Routing;
use Tobento\Service\Translation;

/**
 * RouteLocalizerTest
 */
class RouteLocalizerTest extends TestCase
{
    protected function createRouter(string $uri = '', null|string $domain = null): Routing\RouterInterface
    {
        $container = new \Tobento\Service\Container\Container();
        
        return new Routing\Router(
            new Routing\RequestData('GET', $uri, $domain),
            new Routing\UrlGenerator(
                'https://example.com',
                'a-random-32-character-secret-signature-key',
            ),
            new Routing\RouteFactory(),
            new Routing\RouteDispatcher($container, new Routing\Constrainer\Constrainer()),
            new Routing\RouteHandler($container),
            new Routing\MatchedRouteHandler($container),
            new Routing\RouteResponseParser(),
        );
    }
    
    public function testIsInstanceofRouteLocalizerInterface()
    {
        $factory = new LanguageFactory();
        
        $localizer = new RouteLocalizer(
            languagesFactory: new LanguagesFactory(),
            languages: new Languages(
                $factory->createLanguage('en-US', default: true),
                $factory->createLanguage('de-CH'),
            ),
            translator: null,
            translationSrc: 'routes',
        );
        
        $this->assertInstanceOf(RouteLocalizerInterface::class, $localizer);
    }
    
    public function testLocalizeRouteMethod()
    {
        $factory = new LanguageFactory();
        
        $localizer = new RouteLocalizer(
            languagesFactory: new LanguagesFactory(),
            languages: new Languages(
                $factory->createLanguage('en-US', default: true),
                $factory->createLanguage('de-CH'),
            ),
        );
        
        $router = $this->createRouter();

        $route = $router->get('{?locale}/foo', [Ctr::class, 'method'])->name('foo');
        
        $localizer->localizeRoute($route);
        
        $this->assertSame(['en-us', 'de-ch'], $route->getParameter('locales'));
        $this->assertSame('en-us', $route->getParameter('locale_omit'));
        $this->assertSame([], $route->getParameter('locale_fallbacks'));
        $this->assertSame('en-us', $route->getParameter('locale'));
    }
    
    public function testLocalizeRouteMethodUsesLanguageSlug()
    {
        $factory = new LanguageFactory();
        
        $localizer = new RouteLocalizer(
            languagesFactory: new LanguagesFactory(),
            languages: new Languages(
                $factory->createLanguage('en-US', slug: 'en', default: true),
                $factory->createLanguage('de-CH', slug: 'de'),
            ),
        );
        
        $router = $this->createRouter();

        $route = $router->get('{?locale}/foo', [Ctr::class, 'method'])->name('foo');
        
        $localizer->localizeRoute($route);
        
        $this->assertSame(['en', 'de'], $route->getParameter('locales'));
        $this->assertSame('en', $route->getParameter('locale_omit'));
        $this->assertSame([], $route->getParameter('locale_fallbacks'));
    }
    
    public function testLocalizeRouteMethodWithLanguageFallback()
    {
        $factory = new LanguageFactory();
        
        $localizer = new RouteLocalizer(
            languagesFactory: new LanguagesFactory(),
            languages: new Languages(
                $factory->createLanguage('en', default: true),
                $factory->createLanguage('de', fallback: 'en'),
            ),
        );
        
        $router = $this->createRouter();

        $route = $router->get('{?locale}/foo', [Ctr::class, 'method'])->name('foo');
        
        $localizer->localizeRoute($route);
        
        $this->assertSame(['en', 'de'], $route->getParameter('locales'));
        $this->assertSame('en', $route->getParameter('locale_omit'));
        $this->assertSame(['de' => 'en'], $route->getParameter('locale_fallbacks'));
    }
    
    public function testLocalizeRouteMethodRouteUrl()
    {
        $factory = new LanguageFactory();
        
        $localizer = new RouteLocalizer(
            languagesFactory: new LanguagesFactory(),
            languages: new Languages(
                $factory->createLanguage('en', default: true),
                $factory->createLanguage('de', fallback: 'en'),
            ),
        );
        
        $router = $this->createRouter();

        $route = $router->get('{?locale}/foo', [Ctr::class, 'method'])->name('foo');
        
        $localizer->localizeRoute($route);
        
        $this->assertSame(
            [
                'en' => 'https://example.com/foo',
                'de' => 'https://example.com/de/foo',
            ],
            $router->url('foo')->translated()
        );
        
        $this->assertSame(
            [],
            $router->url('foo')->domained()
        );
    }
    
    public function testLocalizeRouteMethodRouteUrlWithDomainedLanguages()
    {
        $factory = new LanguageFactory();
        
        $localizer = new RouteLocalizer(
            languagesFactory: new LanguagesFactory(),
            languages: new Languages(
                $factory->createLanguage('en', default: true), // shared language as no domain
                $factory->createLanguage('de', domain: 'example.de', default: true),
                $factory->createLanguage('fr', domain: 'example.fr', default: true),
            ),
        );
        
        $router = $this->createRouter(domain: 'example.de');

        $route = $router->get('{?locale}/foo', [Ctr::class, 'method'])->name('foo');
        
        $localizer->localizeRoute($route);
        
        $this->assertSame(
            [
                'de' => 'https://example.de/foo',
                'en' => 'https://example.de/en/foo',
            ],
            $router->url('foo')->translated()
        );
        
        $this->assertSame(
            [
                'example.de' => 'https://example.de/foo',
                'example.fr' => 'https://example.fr/foo',
            ],
            $router->url('foo')->domained()
        );
        
        $this->assertSame(
            [
                'fr' => 'https://example.fr/foo',
                'en' => 'https://example.fr/en/foo',
            ],
            $router->url('foo')->domain('example.fr')->translated()
        );
    }
    
    public function testLocalizeRouteMethodWithTranslatorRouteUrl()
    {
        $factory = new LanguageFactory();
        
        $localizer = new RouteLocalizer(
            languagesFactory: new LanguagesFactory(),
            languages: new Languages(
                $factory->createLanguage('en', default: true),
                $factory->createLanguage('de', fallback: 'en'),
            ),
            translator: new Translation\Translator(
                new Translation\Resources(
                    new Translation\Resource(
                        name: 'routes',
                        locale: 'en',
                        translations: ['checkout' => 'checkout', 'payment' => 'payment'],
                    ),
                    new Translation\Resource(
                        name: 'routes',
                        locale: 'de',
                        translations: ['checkout' => 'kasse', 'payment' => 'zahlung'],
                    ),
                ),
                new Translation\Modifiers(
                    new Translation\Modifier\Pluralization(),
                    new Translation\Modifier\ParameterReplacer(),
                ),
                new Translation\MissingTranslationHandler(),
                'en',
            ),
        );
        
        $router = $this->createRouter();

        $route = $router->get('{?locale}/{checkout}/{payment}', [Ctr::class, 'method'])->name('foo');
        
        $localizer->localizeRoute($route, 'checkout', 'payment');
        
        $this->assertSame(
            [
                'en' => 'https://example.com/checkout/payment',
                'de' => 'https://example.com/de/kasse/zahlung',
            ],
            $router->url('foo')->translated()
        );
        
        $this->assertSame(
            [],
            $router->url('foo')->domained()
        );
    }
    
    public function testLocalizeRouteMethodWithTranslatorTranslationGetsSlugged()
    {
        $factory = new LanguageFactory();
        
        $localizer = new RouteLocalizer(
            languagesFactory: new LanguagesFactory(),
            languages: new Languages(
                $factory->createLanguage('en', default: true),
                $factory->createLanguage('de', fallback: 'en'),
            ),
            translator: new Translation\Translator(
                new Translation\Resources(
                    new Translation\Resource(
                        name: 'routes',
                        locale: 'en',
                        translations: ['checkout' => 'check Out'],
                    ),
                    new Translation\Resource(
                        name: 'routes',
                        locale: 'de',
                        translations: ['checkout' => 'die Kasse'],
                    ),
                ),
                new Translation\Modifiers(
                    new Translation\Modifier\Pluralization(),
                    new Translation\Modifier\ParameterReplacer(),
                ),
                new Translation\MissingTranslationHandler(),
                'en',
            ),
        );
        
        $router = $this->createRouter();

        $route = $router->get('{?locale}/{checkout}', [Ctr::class, 'method'])->name('foo');
        
        $localizer->localizeRoute($route, 'checkout');
        
        $this->assertSame(
            [
                'en' => 'https://example.com/check-out',
                'de' => 'https://example.com/de/die-kasse',
            ],
            $router->url('foo')->translated()
        );
    }
    
    public function testLocalizeRouteMethodWithTranslatorRouteUrlAndDomainedLanguages()
    {
        $factory = new LanguageFactory();
        
        $localizer = new RouteLocalizer(
            languagesFactory: new LanguagesFactory(),
            languages: new Languages(
                $factory->createLanguage('en', default: true), // shared language as no domain
                $factory->createLanguage('de', domain: 'example.de', default: true),
                $factory->createLanguage('fr', domain: 'example.fr', default: true),
            ),
            translator: new Translation\Translator(
                new Translation\Resources(
                    new Translation\Resource(
                        name: 'routes',
                        locale: 'en',
                        translations: ['checkout' => 'checkout', 'payment' => 'payment'],
                    ),
                    new Translation\Resource(
                        name: 'routes',
                        locale: 'de',
                        translations: ['checkout' => 'kasse', 'payment' => 'zahlung'],
                    ),
                ),
                new Translation\Modifiers(
                    new Translation\Modifier\Pluralization(),
                    new Translation\Modifier\ParameterReplacer(),
                ),
                new Translation\MissingTranslationHandler(),
                'en',
            ),
        );
        
        $router = $this->createRouter(domain: 'example.de');

        $route = $router->get('{?locale}/{checkout}/{payment}', [Ctr::class, 'method'])->name('foo');
        
        $localizer->localizeRoute($route, 'checkout', 'payment');
        
        $this->assertSame(
            [
                'de' => 'https://example.de/kasse/zahlung',
                'en' => 'https://example.de/en/checkout/payment',
            ],
            $router->url('foo')->translated()
        );
        
        $this->assertSame(
            [
                'example.de' => 'https://example.de/kasse/zahlung',
                'example.fr' => 'https://example.fr/checkout/payment',
            ],
            $router->url('foo')->domained()
        );
        
        $this->assertSame(
            [
                'fr' => 'https://example.fr/checkout/payment',
                'en' => 'https://example.fr/en/checkout/payment',
            ],
            $router->url('foo')->domain('example.fr')->translated()
        );
    }
    
    public function testLocalizeRouteMethodWithCustomTranslatorSrcRouteUrl()
    {
        $factory = new LanguageFactory();
        
        $localizer = new RouteLocalizer(
            languagesFactory: new LanguagesFactory(),
            languages: new Languages(
                $factory->createLanguage('en', default: true),
                $factory->createLanguage('de', fallback: 'en'),
            ),
            translator: new Translation\Translator(
                new Translation\Resources(
                    new Translation\Resource(
                        name: '*',
                        locale: 'en',
                        translations: ['checkout' => 'checkout', 'payment' => 'payment'],
                    ),
                    new Translation\Resource(
                        name: '*',
                        locale: 'de',
                        translations: ['checkout' => 'kasse', 'payment' => 'zahlung'],
                    ),
                ),
                new Translation\Modifiers(
                    new Translation\Modifier\Pluralization(),
                    new Translation\Modifier\ParameterReplacer(),
                ),
                new Translation\MissingTranslationHandler(),
                'en',
            ),
            translationSrc: '*',
        );
        
        $router = $this->createRouter();

        $route = $router->get('{?locale}/{checkout}/{payment}', [Ctr::class, 'method'])->name('foo');
        
        $localizer->localizeRoute($route, 'checkout', 'payment');
        
        $this->assertSame(
            [
                'en' => 'https://example.com/checkout/payment',
                'de' => 'https://example.com/de/kasse/zahlung',
            ],
            $router->url('foo')->translated()
        );
        
        $this->assertSame(
            [],
            $router->url('foo')->domained()
        );
    }
    
    public function testLocalizeRouteMethodWithTranslatorAndRouteGroup()
    {
        $factory = new LanguageFactory();
        
        $localizer = new RouteLocalizer(
            languagesFactory: new LanguagesFactory(),
            languages: new Languages(
                $factory->createLanguage('en', default: true),
                $factory->createLanguage('de', fallback: 'en'),
            ),
            translator: new Translation\Translator(
                new Translation\Resources(
                    new Translation\Resource(
                        name: 'routes',
                        locale: 'en',
                        translations: ['checkout' => 'checkout', 'payment' => 'payment'],
                    ),
                    new Translation\Resource(
                        name: 'routes',
                        locale: 'de',
                        translations: ['checkout' => 'kasse', 'payment' => 'zahlung'],
                    ),
                ),
                new Translation\Modifiers(
                    new Translation\Modifier\Pluralization(),
                    new Translation\Modifier\ParameterReplacer(),
                ),
                new Translation\MissingTranslationHandler(),
                'en',
            ),
        );
        
        $router = $this->createRouter();

        $routeGroup = $router->group('{?locale}/{checkout}', function(Routing\RouteGroupInterface $group) use ($localizer) {
            $group->get('payment', [Ctr::class, 'method'])->name('foo');
            
            $route = $group->get('{payment}', [Ctr::class, 'method'])->name('payment');
            $localizer->localizeRoute($route, 'checkout', 'payment');
        });
        
        $localizer->localizeRoute($routeGroup, 'checkout');
        
        $this->assertSame(
            [
                'en' => 'https://example.com/checkout/payment',
                'de' => 'https://example.com/de/kasse/payment',
            ],
            $router->url('foo')->translated()
        );
        
        $this->assertSame(
            [
                'en' => 'https://example.com/checkout/payment',
                'de' => 'https://example.com/de/kasse/zahlung',
            ],
            $router->url('payment')->translated()
        );
    }
    
    public function testLocalizeRouteMethodWithTranslatorAndRouteResource()
    {
        $factory = new LanguageFactory();
        
        $localizer = new RouteLocalizer(
            languagesFactory: new LanguagesFactory(),
            languages: new Languages(
                $factory->createLanguage('en', default: true),
                $factory->createLanguage('de', fallback: 'en'),
            ),
            translator: new Translation\Translator(
                new Translation\Resources(
                    new Translation\Resource(
                        name: 'routes',
                        locale: 'en',
                        translations: ['products' => 'products', 'edit' => 'edit'],
                    ),
                    new Translation\Resource(
                        name: 'routes',
                        locale: 'de',
                        translations: ['products' => 'produkte', 'edit' => 'bearbeiten'],
                    ),
                ),
                new Translation\Modifiers(
                    new Translation\Modifier\Pluralization(),
                    new Translation\Modifier\ParameterReplacer(),
                ),
                new Translation\MissingTranslationHandler(),
                'en',
            ),
        );
        
        $router = $this->createRouter();

        $routeResource = $router->resource('{?locale}/{products}', ProductsController::class)->name('products');
        
        $localizer->localizeRoute($routeResource, 'products', 'edit.edit');
        
        $this->assertSame(
            [
                'en' => 'https://example.com/products',
                'de' => 'https://example.com/de/produkte',
            ],
            $router->url('products.index')->translated()
        );
        
        $this->assertSame(
            [
                'en' => 'https://example.com/products/5/edit',
                'de' => 'https://example.com/de/produkte/5/bearbeiten',
            ],
            $router->url('products.edit', ['id' => '5'])->translated()
        );
    }
}