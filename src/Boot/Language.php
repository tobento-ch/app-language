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
 
namespace Tobento\App\Language\Boot;

use Tobento\App\Boot;
use Tobento\App\Boot\Config;
use Tobento\App\Migration\Boot\Migration;
use Tobento\App\Http\Boot\Middleware;
use Tobento\App\Http\Boot\Routing;
use Tobento\App\Database\Boot\Database;
use Tobento\App\Language\RouteLocalizerInterface;
use Tobento\Service\Language\LanguagesInterface;
use Tobento\Service\Language\AreaLanguagesInterface;
use Tobento\Service\Language\CurrentLanguageResolverInterface;
use Tobento\Service\Language\CurrentLanguageResolverException;
use Tobento\Service\Dater\DateFormatter;

/**
 * Language
 */
class Language extends Boot
{
    public const INFO = [
        'boot' => [
            'installs and loads language config file',
            'implements language interfaces based on language config',
            'determines current language',
            'sets current language on date formatter',
        ],
    ];

    public const BOOT = [
        Config::class,
        Migration::class,
        Middleware::class,
        Routing::class,
        Database::class,
    ];
    
    /**
     * Boot application services.
     *
     * @param Config $config
     * @param Migration $migration
     * @param Middleware $middleware
     * @return void
     */
    public function boot(
        Config $config,
        Migration $migration,
        Middleware $middleware,
    ): void {
        // install language config:
        $migration->install(\Tobento\App\Language\Migration\Language::class);
        
        // load the language config:
        $config = $config->load('language.php');
        
        // setting interfaces:
        foreach($config['interfaces'] as $interface => $implementation) {
            $this->app->set($interface, $implementation);
        }
        
        // install migration after interfaces are set:
        foreach($config['migrations'] as $migrationClass) {
            $migration->install($migrationClass);
        }
        
        // resolve current language as early as possible
        // so that other services can use current language:
        $currentLanguageResolver = $this->app->get(CurrentLanguageResolverInterface::class);
        
        try {
            $currentLanguageResolver->resolve($this->languages());
        } catch (CurrentLanguageResolverException $e) {
            // just ignore as the default language will be the current.
        }
        
        // set the current locale on the date formatter:
        $currentLocale = $this->languages()->current()->locale();
            
        $this->app->on(
            DateFormatter::class,
            static function(DateFormatter $df) use ($currentLocale): DateFormatter {
                return $df->withLocale($currentLocale);
            }
        );
    }
    
    /**
     * Returns the languages.
     *
     * @return LanguagesInterface
     */
    public function languages(): LanguagesInterface
    {
        return $this->app->get(LanguagesInterface::class);
    }
    
    /**
     * Returns the area languages.
     *
     * @return AreaLanguagesInterface
     */
    public function areaLanguages(): AreaLanguagesInterface
    {
        return $this->app->get(AreaLanguagesInterface::class);
    }
    
    /**
     * Returns the route localizer.
     *
     * @return RouteLocalizerInterface
     */
    public function routeLocalizer(): RouteLocalizerInterface
    {
        return $this->app->get(RouteLocalizerInterface::class);
    }
}