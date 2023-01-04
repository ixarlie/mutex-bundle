<?php declare(strict_types=1);

namespace IXarlie\MutexBundle\NamingStrategy;

use IXarlie\MutexBundle\MutexRequest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DefaultNamingStrategy.
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 * @final
 */
class DefaultNamingStrategy implements NamingStrategy
{
    public function createName(MutexRequest $config, Request $request): string
    {
        if (null !== $config->name && '' !== $config->name) {
            return $config->name;
        }

        $name = $request->attributes->get('_controller') . $request->getPathInfo();

        return str_replace(['\\', ':', '/'], ['_', '_', '_'], $name);
    }
}
