<?php

namespace Tests\Fixtures;

use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class FakeTranslator.
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class FakeTranslator implements TranslatorInterface
{
    /**
     * @inheritdoc
     */
    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        return '[trans]'.$id.'[/trans]';
    }

    /**
     * @inheritdoc
     */
    public function transChoice($id, $number, array $parameters = array(), $domain = null, $locale = null)
    {
        return '[trans]'.$id.'[/trans]';
    }

    /**
     * @inheritdoc
     */
    public function setLocale($locale)
    {
    }

    /**
     * @inheritdoc
     */
    public function getLocale()
    {
    }
}
