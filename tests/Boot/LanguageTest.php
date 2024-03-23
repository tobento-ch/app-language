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

namespace Tobento\App\Language\Test\Boot;

use PHPUnit\Framework\TestCase;
use Tobento\App\AppInterface;
use Tobento\App\AppFactory;
use Tobento\App\Language\Boot\Language;
use Tobento\App\Language\LanguageFactoryInterface;
use Tobento\App\Language\LanguageRepositoryInterface;
use Tobento\App\Language\RouteLocalizerInterface;
use Tobento\Service\Language\LanguageFactoryInterface as ServiceLanguageFactoryInterface;
use Tobento\Service\Language\LanguagesFactoryInterface;
use Tobento\Service\Language\AreaLanguagesInterface;
use Tobento\Service\Language\LanguagesInterface;
use Tobento\Service\Language\CurrentLanguageResolverInterface;
use Tobento\Service\Translation\TranslatorInterface;
use Tobento\Service\Translation\Resource;
use Tobento\Service\Dater\DateFormatter;
use Tobento\Service\Filesystem\Dir;
use Tobento\App\Http\Boot\Routing;
use Tobento\App\Http\Boot\Http;
use Tobento\App\Http\ResponseEmitterInterface;
use Tobento\App\Http\Test\Mock\ResponseEmitter;
use Psr\Http\Message\ServerRequestInterface;
use Nyholm\Psr7\Factory\Psr17Factory;

/**
 * LanguageTest
 */
class LanguageTest extends TestCase
{
    protected function createApp(bool $deleteDir = true): AppInterface
    {
        if ($deleteDir) {
            (new Dir())->delete(__DIR__.'/../app/');
        }
        
        (new Dir())->create(__DIR__.'/../app/');
        (new Dir())->create(__DIR__.'/../app/config/');
        
        $app = (new AppFactory())->createApp();
        
        $app->dirs()
            ->dir(realpath(__DIR__.'/../app/'), 'app')
            ->dir($app->dir('app').'config', 'config', group: 'config');
        
        return $app;
    }
    
    public static function tearDownAfterClass(): void
    {
        (new Dir())->delete(__DIR__.'/../app/');
    }
    
    public function testInterfacesAreAvailable()
    {
        $app = $this->createApp();
        $app->boot(Language::class);
        $app->booting();
        
        $this->assertInstanceof(LanguageFactoryInterface::class, $app->get(LanguageFactoryInterface::class));
        $this->assertInstanceof(LanguageRepositoryInterface::class, $app->get(LanguageRepositoryInterface::class));
        $this->assertInstanceof(RouteLocalizerInterface::class, $app->get(RouteLocalizerInterface::class));
        $this->assertInstanceof(ServiceLanguageFactoryInterface::class, $app->get(ServiceLanguageFactoryInterface::class));
        $this->assertInstanceof(LanguagesFactoryInterface::class, $app->get(LanguagesFactoryInterface::class));
        $this->assertInstanceof(AreaLanguagesInterface::class, $app->get(AreaLanguagesInterface::class));
        $this->assertInstanceof(LanguagesInterface::class, $app->get(LanguagesInterface::class));
        $this->assertInstanceof(CurrentLanguageResolverInterface::class, $app->get(CurrentLanguageResolverInterface::class));
    }
    
    public function testCurrentLanguage()
    {
        $app = $this->createApp();
        
        // Replace response emitter for testing:
        $app->on(ResponseEmitterInterface::class, ResponseEmitter::class);
        
        $app->on(ServerRequestInterface::class, function() {
            return (new Psr17Factory())->createServerRequest(
                method: 'GET',
                uri: 'foo',
                serverParams: [],
            );
        });
        
        $app->boot(Language::class);
        $app->booting();
        
        $languages = $app->get(LanguagesInterface::class);
        
        $this->assertSame('en', $languages->current()->locale());
    }
    
    public function testCurrentLanguageWithLocaleSlugSameAsDefault()
    {
        $app = $this->createApp();
        
        // Replace response emitter for testing:
        $app->on(ResponseEmitterInterface::class, ResponseEmitter::class);
        
        $app->on(ServerRequestInterface::class, function() {
            return (new Psr17Factory())->createServerRequest(
                method: 'GET',
                uri: 'en/foo',
                serverParams: [],
            );
        });
        
        $app->boot(Language::class);
        $app->booting();
        
        $languages = $app->get(LanguagesInterface::class);
        
        $this->assertSame('en', $languages->current()->locale());
    }

    public function testCurrentLanguageWithLocaleSlugNotExistFallsbackToDefault()
    {
        $app = $this->createApp();
        
        // Replace response emitter for testing:
        $app->on(ResponseEmitterInterface::class, ResponseEmitter::class);
        
        $app->on(ServerRequestInterface::class, function() {
            return (new Psr17Factory())->createServerRequest(
                method: 'GET',
                uri: 'de/foo',
                serverParams: [],
            );
        });
        
        $app->boot(Language::class);
        $app->booting();
        
        $languages = $app->get(LanguagesInterface::class);
        
        $this->assertSame('en', $languages->current()->locale());
    }
    
    public function testCurrentLanguageWithLocaleSlug()
    {
        $app = $this->createApp();
        
        // Replace response emitter for testing:
        $app->on(ResponseEmitterInterface::class, ResponseEmitter::class);
        
        $app->on(ServerRequestInterface::class, function() {
            return (new Psr17Factory())->createServerRequest(
                method: 'GET',
                uri: 'de/foo',
                serverParams: [],
            );
        });

        $app->on(LanguageRepositoryInterface::class, function($repo) {
            $repo->create(['locale' => 'de']);
        });
        
        $app->boot(Language::class);
        $app->booting();
        
        $languages = $app->get(LanguagesInterface::class);
        
        $this->assertSame('de', $languages->current()->locale());
    }
    
    public function testDefaultLanguage()
    {
        $app = $this->createApp();
        
        // Replace response emitter for testing:
        $app->on(ResponseEmitterInterface::class, ResponseEmitter::class);
        
        $app->on(ServerRequestInterface::class, function() {
            return (new Psr17Factory())->createServerRequest(
                method: 'GET',
                uri: 'foo',
                serverParams: [],
            );
        });
        
        $app->boot(Language::class);
        $app->booting();
        
        $languages = $app->get(LanguagesInterface::class);
        
        $this->assertSame('en', $languages->default()->locale());
    }
    
    public function testLocalizeRoute()
    {
        $app = $this->createApp();
        
        // Replace response emitter for testing:
        $app->on(ResponseEmitterInterface::class, ResponseEmitter::class);
        
        $app->on(ServerRequestInterface::class, function() {
            return (new Psr17Factory())->createServerRequest(
                method: 'GET',
                uri: 'http://localhost/foo',
                serverParams: [],
            );
        });
        
        $app->boot(Language::class);
        $app->booting();
        
        $route = $app->route('GET', '{?locale}/foo', function(null|string $locale, LanguagesInterface $languages) {
            return [
                'route_locale' => $locale,
                'current_locale' => $languages->current()->locale(),
            ];
        })->name('foo');
        
        $routeLocalizer = $app->get(RouteLocalizerInterface::class);
        $routeLocalizer->localizeRoute($route);
                
        $app->run();
        
        $this->assertSame(
            ['en' => 'http://localhost/foo'],
            $app->routeUrl('foo')->translated()
        );
        
        $this->assertSame(
            '{"route_locale":"en","current_locale":"en"}',
            (string)$app->get(Http::class)->getResponse()->getBody()
        );
    }
    
    public function testLocalizeRouteWithLocaleSlug()
    {
        $app = $this->createApp();
        
        // Replace response emitter for testing:
        $app->on(ResponseEmitterInterface::class, ResponseEmitter::class);
        
        $app->on(ServerRequestInterface::class, function() {
            return (new Psr17Factory())->createServerRequest(
                method: 'GET',
                uri: 'http://localhost/de/foo',
                serverParams: [],
            );
        });
        
        $app->on(LanguageRepositoryInterface::class, function($repo) {
            $repo->create(['locale' => 'de']);
        });
        
        $app->boot(Language::class);
        $app->booting();
        
        $route = $app->route('GET', '{?locale}/foo', function(null|string $locale, LanguagesInterface $languages) {
            return [
                'route_locale' => $locale,
                'current_locale' => $languages->current()->locale(),
            ];
        })->name('foo');
        
        $routeLocalizer = $app->get(RouteLocalizerInterface::class);
        $routeLocalizer->localizeRoute($route);
        
        $app->run();

        $this->assertSame(
            ['de' => 'http://localhost/de/foo', 'en' => 'http://localhost/foo'],
            $app->routeUrl('foo')->translated()
        );
        
        $this->assertSame(
            '{"route_locale":"de","current_locale":"de"}',
            (string)$app->get(Http::class)->getResponse()->getBody()
        );
    }
    
    public function testLocalizeRouteWithTranslation()
    {
        $app = $this->createApp();
        
        // Replace response emitter for testing:
        $app->on(ResponseEmitterInterface::class, ResponseEmitter::class);
        
        $app->on(ServerRequestInterface::class, function() {
            return (new Psr17Factory())->createServerRequest(
                method: 'GET',
                uri: 'http://localhost/de/kasse/zahlung',
                serverParams: [],
            );
        });
        
        $app->on(LanguageRepositoryInterface::class, function($repo) {
            $repo->create(['locale' => 'de', 'fallback' => 'en']);
        });
        
        $app->boot(Language::class);
        $app->boot(\Tobento\App\Translation\Boot\Translation::class);
        $app->booting();
        
        // Add routes translations:
        $app->on(TranslatorInterface::class, function(TranslatorInterface $translator) {
            $translator->resources()->add(new Resource(
                name: 'routes',
                locale: 'en',
                translations: ['checkout' => 'checkout', 'payment' => 'payment'],
            ));
            $translator->resources()->add(new Resource(
                name: 'routes',
                locale: 'de',
                translations: ['checkout' => 'kasse', 'payment' => 'zahlung'],
            ));
        });
        
        $route = $app->route('GET', '{?locale}/{checkout}/{payment}', function(null|string $locale, LanguagesInterface $languages) {
            return [
                'route_locale' => $locale,
                'current_locale' => $languages->current()->locale(),
            ];
        })->name('checkout.payment');
        
        $routeLocalizer = $app->get(RouteLocalizerInterface::class);
        $routeLocalizer->localizeRoute($route, 'checkout', 'payment');
        
        $app->run();        
        
        $this->assertSame(
            ['de' => 'http://localhost/de/kasse/zahlung', 'en' => 'http://localhost/checkout/payment'],
            $app->routeUrl('checkout.payment')->translated()
        );
        
        $this->assertSame(
            '{"route_locale":"de","current_locale":"de"}',
            (string)$app->get(Http::class)->getResponse()->getBody()
        );
    }
    
    public function testLocalizeRouteWithDomains()
    {
        $app = $this->createApp();
        
        // Replace response emitter for testing:
        $app->on(ResponseEmitterInterface::class, ResponseEmitter::class);
        
        $app->on(ServerRequestInterface::class, function() {
            return (new Psr17Factory())->createServerRequest(
                method: 'GET',
                uri: 'http://example.ch/de/kasse/zahlung',
                serverParams: [],
            );
        });
        
        $app->on(LanguageRepositoryInterface::class, function($repo) {
            $repo->create(['locale' => 'de', 'domain' => 'example.ch']);
        });
        
        $app->boot(Language::class);
        $app->boot(\Tobento\App\Translation\Boot\Translation::class);
        $app->booting();
        
        // Add routes translations:
        $app->on(TranslatorInterface::class, function(TranslatorInterface $translator) {
            $translator->resources()->add(new Resource(
                name: 'routes',
                locale: 'en',
                translations: ['checkout' => 'checkout', 'payment' => 'payment'],
            ));
            $translator->resources()->add(new Resource(
                name: 'routes',
                locale: 'de',
                translations: ['checkout' => 'kasse', 'payment' => 'zahlung'],
            ));
        });
        
        $route = $app->route('GET', '{?locale}/{checkout}/{payment}', function(null|string $locale, LanguagesInterface $languages) {
            return [
                'route_locale' => $locale,
                'current_locale' => $languages->current()->locale(),
            ];
        })->name('checkout.payment');
        
        $routeLocalizer = $app->get(RouteLocalizerInterface::class);
        $routeLocalizer->localizeRoute($route, 'checkout', 'payment');
        
        $app->run();        

        $this->assertSame(
            ['example.ch' => 'http://example.ch/checkout/payment'],
            $app->routeUrl('checkout.payment')->domained()
        );
        
        $this->assertSame(
            ['de' => 'http://example.ch/de/kasse/zahlung', 'en' => 'http://example.ch/checkout/payment'],
            $app->routeUrl('checkout.payment')->domain('example.ch')->translated()
        );
        
        $this->assertSame(
            '{"route_locale":"de","current_locale":"de"}',
            (string)$app->get(Http::class)->getResponse()->getBody()
        );
    }
    
    public function testDateFormatterUsesCurrentLanguage()
    {
        $app = $this->createApp();
        
        // Replace response emitter for testing:
        $app->on(ResponseEmitterInterface::class, ResponseEmitter::class);
        
        $app->on(ServerRequestInterface::class, function() {
            return (new Psr17Factory())->createServerRequest(
                method: 'GET',
                uri: 'de/foo',
                serverParams: [],
            );
        });

        $app->on(LanguageRepositoryInterface::class, function($repo) {
            $repo->create(['locale' => 'de']);
        });
        
        $app->boot(Language::class);
        $app->booting();
        
        $df = $app->get(DateFormatter::class);
        
        $this->assertSame('Freitag, 15. März 2024', $df->date('2024-03-15'));
    }
}