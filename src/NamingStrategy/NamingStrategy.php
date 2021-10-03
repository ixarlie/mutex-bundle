<?php declare(strict_types=1);

namespace IXarlie\MutexBundle\NamingStrategy;

use IXarlie\MutexBundle\MutexRequest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface NamingStrategy
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
interface NamingStrategy
{
    /**
     * @param MutexRequest $config
     * @param Request      $request
     *
     * @return string
     */
    public function createName(MutexRequest $config, Request $request): string;
}
