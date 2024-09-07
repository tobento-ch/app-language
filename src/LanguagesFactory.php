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

use Psr\Http\Message\ServerRequestInterface;
use Tobento\Service\Language\LanguageInterface;
use Tobento\Service\Language\Languages;
use Tobento\Service\Language\LanguagesFactoryInterface;
use Tobento\Service\Language\LanguagesInterface;

/**
 * LanguagesFactory
 */
class LanguagesFactory implements LanguagesFactoryInterface
{
    /**
     * Create a new LanguagesFactory.
     *
     * @param ServerRequestInterface $request
     */
    public function __construct(
        protected ServerRequestInterface $request,
    ) {}
    
    /**
     * Create a new Languages.
     *
     * @param LanguageInterface ...$languages
     * @return LanguagesInterface
     * @psalm-suppress UnusedClosureParam
     */
    public function createLanguages(LanguageInterface ...$languages): LanguagesInterface
    {
        $languages = new Languages(...$languages);
        $host = $this->request->getUri()->getHost();
        
        // Languages with the current request domain should be first,
        // otherwise if there are more default languages per domain,
        // it may take the wrong default.
        return $languages->sort(
            fn(LanguageInterface $a, LanguageInterface $b) => $b->domain() === $host
        );
    }
}