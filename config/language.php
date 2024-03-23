<?php

/**
 * TOBENTO
 *
 * @copyright   Tobias Strub, TOBENTO
 * @license     MIT License, see LICENSE file distributed with this source code.
 * @author      Tobias Strub
 * @link        https://www.tobento.ch
 */

use Tobento\App\Language as App;
use Tobento\Service\Language;
use Tobento\Service\Storage\StorageInterface;
use Tobento\Service\Storage\InMemoryStorage;
use Tobento\Service\Database\DatabasesInterface;
use Psr\Container\ContainerInterface;

return [

    /*
    |--------------------------------------------------------------------------
    | Migrations
    |--------------------------------------------------------------------------
    |
    | The migrations.
    |
    */
    
    'migrations' => [
        // you may migrate repository resource data if you use another
        // storage than InMemoryStorage. This migration will create
        // the database table based on the LanguageStorageRepository
        // and create the default language.
        
        //\Tobento\App\Language\Migration\LanguageStorageRepository::class,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Interfaces
    |--------------------------------------------------------------------------
    |
    | Do not change the interface's names as it may be used in other app bundles!
    |
    */
    
    'interfaces' => [
        
        Language\LanguageFactoryInterface::class => Language\LanguageFactory::class,
        
        Language\LanguagesFactoryInterface::class => Language\LanguagesFactory::class,
        
        Language\AreaLanguagesInterface::class => static function(ContainerInterface $c) {

            $languages = $c->get(App\LanguageRepositoryInterface::class)->findAll(
                orderBy: [
                    'order' => 'desc',
                    'locale' => 'asc',
                ],
            );

            return new Language\AreaLanguages(
                $c->get(Language\LanguagesFactoryInterface::class),
                ...$languages,
            );
        },
        
        Language\LanguagesInterface::class => static function(ContainerInterface $c) {
            return $c->get(Language\AreaLanguagesInterface::class)->get(area: 'default');
        },
        
        Language\CurrentLanguageResolverInterface::class => App\CurrentLanguageRouterResolver::class,
        
        App\LanguageFactoryInterface::class => App\LanguageFactory::class,
        
        App\LanguageRepositoryInterface::class => static function(ContainerInterface $c) {
            
            // At least a default language needs to be available for each area (and domain):
            
            $storage = new InMemoryStorage([
                'languages' => [
                    1 => [
                        'locale' => 'en',
                        //'region' => null,
                        'name' => 'English',
                        'id' => 1,
                        //'key' => 'en',                        
                        //'slug' => 'en',
                        //'directory' => 'en',
                        //'direction' => 'ltr',
                        //'area' => 'default',
                        
                        // if you set a domain do not forget to
                        // add it on the domains in the http config file!
                        //'domain' => null, // 'example.com',
                        
                        //'url' => 'https://example.com',
                        //'fallback' => null,
                        'default' => true,
                        //'active' => true,
                        //'editable' => true,
                        //'order' => 1,
                    ],
                ],
            ]);
            
            return new App\LanguageStorageRepository(
                storage: $storage,
                
                // You may use another storage:
                // If you change it you may use the specified migration above:
                //storage: $c->get(DatabasesInterface::class)->get('mysql-storage')->storage()->new(),
                //storage: $c->get(StorageInterface::class)->new(),
                
                table: 'languages',
                entityFactory: $c->get(App\LanguageFactoryInterface::class),
            );
        },
        
        App\RouteLocalizerInterface::class => App\RouteLocalizer::class,
    ],
];