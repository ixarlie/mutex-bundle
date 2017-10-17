<?php

namespace IXarlie\MutexBundle\Tests\Fixtures;

use IXarlie\MutexBundle\Configuration\MutexRequest;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DemoController
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class DemoController
{
    /**
     * @MutexRequest(mode="queue")
     * @param int $value
     * @return Response
     */
    public function queueAction($value = 1)
    {
        return new Response('It works! ' . $value);
    }

    /**
     * @MutexRequest(mode="block")
     * @param int $value
     * @return Response
     */
    public function blockAction($value = 1)
    {
        return new Response('It works! ' . $value);
    }

    /**
     * @MutexRequest(mode="check")
     * @param int $value
     * @return Response
     */
    public function checkAction($value = 1)
    {
        return new Response('It works! ' . $value);
    }

    /**
     * @MutexRequest(mode="force")
     * @param int $value
     * @return Response
     */
    public function forceAction($value = 1)
    {
        return new Response('It works! ' . $value);
    }
}
