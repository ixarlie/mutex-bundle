<?php

namespace Tests\Fixtures;

use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class FakeTranslator.
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
final class FakeTranslator implements TranslatorInterface
{
    /**
     * @inheritdoc
     */
    public function trans(string $id, array $parameters = [], string $domain = null, string $locale = null)
    {
        return '[trans]' . $id . '[/trans]';
    }
}
