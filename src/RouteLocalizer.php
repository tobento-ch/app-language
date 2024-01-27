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

use Tobento\Service\Language\LanguagesFactoryInterface;
use Tobento\Service\Language\LanguagesInterface;
use Tobento\Service\Language\LanguageInterface;
use Tobento\Service\Routing\RouteInterface;
use Tobento\Service\Routing\RouteGroupInterface;
use Tobento\Service\Routing\RouteResourceInterface;
use Tobento\Service\Translation\TranslatorInterface;
use Tobento\Service\Support\Str;

/**
 * RouteLocalizer
 */
class RouteLocalizer implements RouteLocalizerInterface
{
    /**
     * @var null|array<string, LanguagesInterface>
     */
    protected null|array $domainedLanguages = null;
    
    /**
     * Create a new RouteLocalizer.
     *
     * @param LanguagesFactoryInterface $languagesFactory
     * @param LanguagesInterface $languages
     * @param null|TranslatorInterface $translator
     * @param string $translationSrc
     */
    public function __construct(
        protected LanguagesFactoryInterface $languagesFactory,
        protected LanguagesInterface $languages,
        protected null|TranslatorInterface $translator = null,
        protected string $translationSrc = 'routes',
    ) {}
    
    /**
     * Localize the specified route.
     *
     * @param RouteInterface|RouteGroupInterface|RouteResourceInterface $route
     * @param string ...$uriSegments The uri segments to get translated.
     * @return RouteInterface|RouteGroupInterface|RouteResourceInterface
     */
    public function localizeRoute(
        RouteInterface|RouteGroupInterface|RouteResourceInterface $route,
        string ...$uriSegments,
    ): RouteInterface|RouteGroupInterface|RouteResourceInterface {
        
        if (empty($this->getDomainedLanguages())) {                
            $route = $this->localizingRoute($route, $this->languages);
            $route = $this->translateRoute($route, ...$uriSegments);
            return $route;
        }
        
        foreach($this->getDomainedLanguages() as $domain => $languages) {
                        
            $route->domain($domain, function($route) use ($languages, $uriSegments) {
                
                $route = $this->localizingRoute($route, $languages);
                
                $this->translateRoute($route, ...$uriSegments);
            });
        }
        
        return $route;
    }
    
    /**
     * Localizing route.
     *
     * @param RouteInterface|RouteGroupInterface|RouteResourceInterface $route
     * @param LanguagesInterface $languages
     * @return RouteInterface|RouteGroupInterface|RouteResourceInterface
     */
    protected function localizingRoute(
        RouteInterface|RouteGroupInterface|RouteResourceInterface $route,
        LanguagesInterface $languages
    ): RouteInterface|RouteGroupInterface|RouteResourceInterface {
        
        $defaultLanguage = $languages->default();
        $currentLanguage = $languages->current();

        $route->locales($languages->column('slug'))
              ->localeOmit($defaultLanguage->slug())
              ->localeFallbacks($this->languages->fallbacks('slug'))
              ->locale($currentLanguage->slug());
        
        return $route;
    }

    /**
     * Returns the domained languages.
     *
     * @return array<string, LanguagesInterface>
     */
    protected function getDomainedLanguages(): array
    {
        if (!is_null($this->domainedLanguages)) {
            return $this->domainedLanguages;
        }
        
        $domained = [];
        $undomained = [];
        $this->domainedLanguages = [];

        foreach($this->languages->all() as $language) {
            
            if (!empty($language->domain())) {
                $domained[$language->domain()][] = $language;
            } else {
                $undomained[] = $language;
            }
        }
        
        foreach($domained as $domain => $domainLanguages) {
            
            $languages = $this->languagesFactory->createLanguages(...array_merge($domainLanguages, $undomained));
            
            $this->domainedLanguages[$domain] = $languages;
        }
        
        return $this->domainedLanguages;
    }
    
    /**
     * Translate the specified route uri segments.
     *
     * @param RouteInterface|RouteGroupInterface|RouteResourceInterface $route
     * @param string ...$uriSegments
     * @return RouteInterface|RouteGroupInterface|RouteResourceInterface
     */
    protected function translateRoute(
        RouteInterface|RouteGroupInterface|RouteResourceInterface $route,
        string ...$uriSegments
    ): RouteInterface|RouteGroupInterface|RouteResourceInterface {
        
        foreach($uriSegments as $uriSegment) {
            
            $translated = [];
            $action = null;
            
            if (
                $route instanceof RouteResourceInterface
                && str_contains($uriSegment, '.')
            ) {
                $segments = explode('.', $uriSegment);
                $uriSegment = $segments[0];
                $action = $segments[1] ?? null;
            }
            
            foreach($this->languages->all() as $language) {
                
                if (is_null($this->translator)) {
                    $translation = $uriSegment;
                } else {
                    $translation = $this->translator->trans(
                        message: $uriSegment,
                        parameters: ['src' => $this->translationSrc],
                        locale: $language->locale(),
                    );
                    
                    $translation = Str::slug($translation);
                }

                $translated[$language->slug()] = $translation;
            }
            
            // handle verbs:
            if (
                $route instanceof RouteResourceInterface
            ) {
                $route->trans($uriSegment, $translated, $action);
            } else {
                $route->trans($uriSegment, $translated);
            }
        }
        
        return $route;
    }
}