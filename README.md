# App Language

Language support for the app.

## Table of Contents

- [Getting Started](#getting-started)
    - [Requirements](#requirements)
- [Documentation](#documentation)
    - [App](#app)
    - [Language Boot](#language-boot)
        - [Language Config](#language-config)
    - [App Languages](#app-languages)
    - [Current And Default App Language](#current-and-default-app-language)
    - [Resolving Current Language](#resolving-current-language)
    - [Localizing Routes](#localizing-routes)
    - [Localizing Routes With Domains](#localizing-routes-with-Domains)
    - [Area Languages](#area-languages)
    - [Available Areas](#available-areas)
- [Credits](#credits)
___

# Getting Started

Add the latest version of the app language project running this command.

```
composer require tobento/app-language
```

## Requirements

- PHP 8.4 or greater

# Documentation

## App

Check out the [**App Skeleton**](https://github.com/tobento-ch/app-skeleton) if you are using the skeleton.

You may also check out the [**App**](https://github.com/tobento-ch/app) to learn more about the app in general.

## Language Boot

The language boot does the following:

* installs and loads language config file
* language interfaces implementation based on config
* determines current language
* sets current language on the [date formatter](https://github.com/tobento-ch/app#dater-boot)

```php
use Tobento\App\AppFactory;

// Create the app
$app = new AppFactory()->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'public', 'public')
    ->dir($app->dir('root').'vendor', 'vendor');

// Adding boots:
$app->boot(\Tobento\App\Language\Boot\Language::class);

// Run the app:
$app->run();
```

### Language Config

The configuration for the language is located in the ```app/config/language.php``` file at the default App Skeleton config location.

## App Languages

```php
use Tobento\App\AppFactory;
use Tobento\Service\Language\LanguagesInterface;

$app = new AppFactory()->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'public', 'public')
    ->dir($app->dir('root').'vendor', 'vendor');

// Adding boots:
$app->boot(\Tobento\App\Language\Boot\Language::class);
$app->booting();

// Gets the app languages:
$languages = $app->get(LanguagesInterface::class);

// Run the app:
$app->run();
```

You may check out the [**Language Service**](https://github.com/tobento-ch/service-language) to learn more about it.

## Current And Default App Language

```php
use Tobento\App\AppFactory;
use Tobento\Service\Language\LanguagesInterface;
use Tobento\Service\Language\LanguageInterface;

$app = new AppFactory()->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'public', 'public')
    ->dir($app->dir('root').'vendor', 'vendor');

// Adding boots:
$app->boot(\Tobento\App\Language\Boot\Language::class);
$app->booting();

// Gets the app languages:
$languages = $app->get(LanguagesInterface::class);

// Current language:
$currentLanguage = $languages->current();
// var_dump($currentLanguage instanceof LanguageInterface);
// bool(true)

// Default language:
$defaultLanguage = $languages->default();
// var_dump($defaultLanguage instanceof LanguageInterface);
// bool(true)

// Run the app:
$app->run();
```

You may check out the [**Language Service**](https://github.com/tobento-ch/service-language) to learn more about it.

## Resolving Current Language

By default, the current language is resolved by the ```Tobento\App\Language\CurrentLanguageRouterResolver::class```. You may change the implementation in the language config.

## Localizing Routes

You may localize your routes by the following way:

```php
use Tobento\App\AppFactory;
use Tobento\App\Language\RouteLocalizerInterface;
use Tobento\Service\Language\LanguagesInterface;

$app = new AppFactory()->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'public', 'public')
    ->dir($app->dir('root').'vendor', 'vendor');

// Adding boots:
$app->boot(\Tobento\App\Language\Boot\Language::class);
$app->booting();

// Localize Routes:
$routeLocalizer = $app->get(RouteLocalizerInterface::class);

$route = $app->route('GET', '{?locale}/team', function(LanguagesInterface $languages): array {
    return ['page' => 'team', 'locale' => $languages->current()->locale()];
})->name('team');

$routeLocalizer->localizeRoute($route);

/*
print_r($app->routeUrl('team')->translated());
Array
(
    [de] => https://example.com/de/team
    [en] => https://example.com/team
)
*/

// Run the app:
$app->run();
```

**Translation Routing**

Simply, install the [App Translation](https://github.com/tobento-ch/app-translation) bundle and boot the ```\Tobento\App\Translation\Boot\Translation::class```:

```
composer require tobento/app-translation
```

```php
use Tobento\App\AppFactory;
use Tobento\App\Language\RouteLocalizerInterface;
use Tobento\Service\Language\LanguagesInterface;
use Tobento\Service\Translation\TranslatorInterface;
use Tobento\Service\Translation\Resource;

$app = new AppFactory()->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'public', 'public')
    ->dir($app->dir('root').'vendor', 'vendor');

// Adding boots:
$app->boot(\Tobento\App\Language\Boot\Language::class);
$app->boot(\Tobento\App\Translation\Boot\Translation::class);
$app->booting();

// Add routes translations:
$app->on(TranslatorInterface::class, function(TranslatorInterface $translator) {
    $translator->resources()->add(new Resource(
        name: 'routes',
        locale: 'en',
        translations: [
            'checkout' => 'checkout', 'payment' => 'payment',
            'products' => 'products', 'edit' => 'edit'
        ],
    ));
    $translator->resources()->add(new Resource(
        name: 'routes',
        locale: 'de',
        translations: [
            'checkout' => 'kasse', 'payment' => 'zahlung',
            'products' => 'produkte', 'edit' => 'bearbeiten',
        ],
    ));
});

// Localize Routes:
$routeLocalizer = $app->get(RouteLocalizerInterface::class);

$route = $app->route('GET', '{?locale}/{checkout}/{payment}', function(LanguagesInterface $languages) {
    return ['page' => 'checkout', 'locale' => $languages->current()->locale()];
})->name('checkout.payment');

// checkout and payment will be translated
// by the TranslatorInterface::class if implemented, otherwise
// checkout and payment is used:
$routeLocalizer->localizeRoute($route, 'checkout', 'payment');

/*
print_r($app->routeUrl('checkout.payment')->translated());
Array
(
    [de] => https://example.com/de/kasse/zahlung
    [en] => https://example.com/checkout/payment
)
*/

// Resource example:
$routeResource = $app->routeResource('{?locale}/{products}', ResourceController::class)->name('products');

$routeLocalizer->localizeRoute($routeResource, 'products', 'edit.edit');
// define resource verbs with a dot if you want to translate them too!
// e.g. 'create.create' or 'edit.edit'

/*
print_r($app->routeUrl('products.edit', ['id' => 5])->translated());
Array
(
    [de] => https://example.com/de/produkte/5/bearbeiten
    [en] => https://example.com/products/5/edit
)
*/

// Run the app:
$app->run();
```

By default, translations are loaded from the ```routes``` [named resources](https://github.com/tobento-ch/service-translation#translate-message).

You may change the localization strategy by changing the ```RouteLocalizerInterface::class``` implemenation in the language config file.

## Localizing Routes With Domains

You will need to specify the languages with its domain in the ```app/config/language.php``` file:

```php
// ...
$storage = new InMemoryStorage([
    'languages' => [
        1 => [
            'id' => 1,
            'locale' => 'de-DE',
            
            // The key is used for translations and storing resources!
            'key' => 'de',
            
            'domain' => 'example.de',
            'default' => true,
        ],
        2 => [
            'id' => 2,
            'locale' => 'de-CH',
            
            // The key is used for translations and storing resources!
            'key' => 'de',
            // or if specific translations and storing resources!
            //'key' => 'de-CH',
            
            'slug' => 'de',
            'domain' => 'example.ch',
            'fallback' => 'de-DE',
            'default' => true,
        ],
        3 => [
            'id' => 3,
            'locale' => 'fr-CH',
            'slug' => 'fr',
            'domain' => 'example.ch',
            'fallback' => 'de-DE',
        ],
    ],
]);
// ...
```

**App Example**

```php
use Tobento\App\AppFactory;
use Tobento\App\Language\RouteLocalizerInterface;
use Tobento\Service\Translation\TranslatorInterface;

$app = new AppFactory()->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'public', 'public')
    ->dir($app->dir('root').'vendor', 'vendor');

// Adding boots:
$app->boot(\Tobento\App\Language\Boot\Language::class);
$app->booting();

// Localize Routes:
$routeLocalizer = $app->get(RouteLocalizerInterface::class);

$route = $app->route('GET', '{?locale}/team', function(null|string $locale): array {
    return ['page' => 'team', 'locale' => $locale];
})->name('team');

$routeLocalizer->localizeRoute($route);

/*
print_r($app->routeUrl('team')->domained());
Array
(
    [example.ch] => http://example.ch/team
    [example.de] => http://example.de/team
)

print_r($app->routeUrl('team')->domain('example.ch')->translated());
Array
(
    [de] => http://example.ch/team
    [fr] => http://example.ch/fr/team
)

print_r($app->routeUrl('team')->domain('example.de')->translated());
Array
(
    [de-de] => http://example.de/team
)
*/

// Run the app:
$app->run();
```

## Area Languages

```php
use Tobento\App\AppFactory;
use Tobento\Service\Language\AreaLanguagesInterface;
use Tobento\Service\Language\LanguagesInterface;

$app = new AppFactory()->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'public', 'public')
    ->dir($app->dir('root').'vendor', 'vendor');

// Adding boots:
$app->boot(\Tobento\App\Language\Boot\Language::class);
$app->booting();

// Gets the area languages:
$areaLanguages = $app->get(AreaLanguagesInterface::class);

// Run the app:
$app->run();
```

You may check out the [**Area Languages**](https://github.com/tobento-ch/service-language#area-languages) to learn more about it.

## Available Areas

The available areas registry holds all defined language areas. Areas describe where languages are used, such as routing, resources, backend tools or an API. They may be registered by different apps (frontend, backend, cms, api, modules), so each one can add its own areas.

```php
use Tobento\App\AppFactory;
use Tobento\App\Language\AvailableAreasInterface;
use Tobento\App\Language\AreaInterface;

$app = new AppFactory()->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'public', 'public')
    ->dir($app->dir('root').'vendor', 'vendor');

// Adding boots:
$app->boot(\Tobento\App\Language\Boot\Language::class);
$app->booting();

// Gets the available areas:
$areas = $app->get(AvailableAreasInterface::class);

var_dump($areas->has('default'));
// bool(true)

$area = $areas->get('default');
// returns null if the area does not exist

var_dump($area instanceof AreaInterface);
// bool(true)

// Run the app:
$app->run();
```

### Defining Areas In The Config

The areas are defined in the ```app/config/language.php``` file using the ```AvailableAreasInterface::class``` entry. By default, only the ```default``` area is defined:

```php
use Tobento\App\Language as App;

return [
    // ...
    
    'interfaces' => [
        // ...
        
        App\AvailableAreasInterface::class => static function(): App\AvailableAreasInterface {
            return new App\AvailableAreas(
                new App\Area(
                    name: 'default',
                    title: 'Default',
                    description: 'Languages used for the main application such as routing and resources.',
                    appId: 'root',
                ),
            );
        },
    ],
];
```

You may add further areas by passing more ```Area``` objects:

```php
App\AvailableAreasInterface::class => static function(): App\AvailableAreasInterface {
    return new App\AvailableAreas(
        new App\Area(
            name: 'default',
            title: 'Default',
            description: 'Languages used for the main application such as routing and resources.',
            appId: 'root',
        ),
        new App\Area(
            name: 'backend',
            title: 'Backend',
            description: 'Languages used for the backend.',
            appId: 'backend',
        ),
    );
},
```

**Note:** The available areas are independent of the [Area Languages](#area-languages). The registry only describes which areas exist, for example to list them in a UI. Languages are assigned to an area through their own `area` value in the language repository.

### Creating Areas

Use the `Area` class to define an area:

```php
use Tobento\App\Language\Area;

$area = new Area(
    // The unique area name:
    name: 'resources',
    
    // Human-readable title for UI display:
    title: 'Resources',
    
    // Descriptive text for UI info texts or documentation:
    description: 'Used for translating resources.',
    
    // Identifier of the app which registers the area:
    appId: 'backend',
);

$area->name(); // 'resources'
$area->title(); // 'Resources'
$area->description(); // 'Used for translating resources.'
$area->appId(); // 'backend'
```

You may also create your own area by implementing the `Tobento\App\Language\AreaInterface`.

### Registering Areas

Other apps or bundles may add their areas after the config is loaded, using the `add` method. If an area with the same name already exists, it is replaced.

```php
use Tobento\App\Language\Area;
use Tobento\App\Language\AvailableAreasInterface;

$app->on(AvailableAreasInterface::class, function(AvailableAreasInterface $areas) {
    $areas->add(new Area(
        name: 'api',
        title: 'API',
        description: 'Used for API responses.',
        appId: 'api',
    ));
});
```

**Reading areas**

```php
// All areas keyed by name:
$all = $areas->all();

// All names:
$names = $areas->names();
// ['default', 'api']

// All titles keyed by name:
$titles = $areas->titles();
// ['default' => 'Default', 'api' => 'API']

// All descriptions keyed by name:
$descriptions = $areas->descriptions();

// Number of areas:
var_dump($areas->count());
// int(2)

// Iterating:
foreach ($areas as $name => $area) {
    // $name is the area name, $area an AreaInterface
}
```

### Filtering Areas

The `app` and `filter` methods return a new instance and leave the original unchanged.

**Filter by app**

```php
$rootAreas = $areas->app('root');
```

**Custom filter**

```php
use Tobento\App\Language\AreaInterface;

$filtered = $areas->filter(
    fn (AreaInterface $area): bool => str_starts_with($area->name(), 'back')
);
```

# Credits

- [Tobias Strub](https://www.tobento.ch)
- [All Contributors](../../contributors)