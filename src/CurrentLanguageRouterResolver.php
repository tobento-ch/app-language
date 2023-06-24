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

use Tobento\Service\Language\CurrentLanguageResolverInterface;
use Tobento\Service\Language\CurrentLanguageResolverException;
use Tobento\Service\Language\LanguagesInterface;
use Tobento\Service\Routing\RouterInterface;
use Tobento\Service\Uri\UriPath;

/**
 * CurrentLanguageRouterResolver
 */
class CurrentLanguageRouterResolver implements CurrentLanguageResolverInterface
{
    /**
     * Create a new CurrentLanguageRouterResolver.
     *
     * @param RouterInterface $router
     * @param bool $allowFallbackToDefaultLanguage
     */
    public function __construct(
        protected RouterInterface $router,
        protected bool $allowFallbackToDefaultLanguage = true,
    ) {}
    
    /**
     * Resolve the current language.
     *
     * @param LanguagesInterface $languages
     * @return void
     * @throws CurrentLanguageResolverException If the current language could not be resolved.
     */
    public function resolve(LanguagesInterface $languages): void
    {
        $locale = (new UriPath(
            $this->router->getRequestData()->uri()
        ))->getSegment(1);
                
        $localeLen = mb_strlen((string)$locale);
        
        if ($localeLen  < 2 || $localeLen  > 5) {
            return;
        }
                
        // check for default locale in slug.
        if ($locale === $languages->default()->slug()) {
            return;
        }
        
        // check for for fallback:
        if ($this->allowFallbackToDefaultLanguage === false) {
            
            $language = $languages->get($locale, fallback: false);
            
            if (is_null($language)) {
                throw new CurrentLanguageResolverException(
                    $locale,
                    'Current language not found'
                );
            }
        }
        
        $languages->current($locale);
    }
}