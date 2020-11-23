<?php

namespace Tests\Fixtures;

use IXarlie\MutexBundle\Configuration\MutexRequest;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DemoController
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
final class DemoController
{
    /**
     * @MutexRequest(mode="queue")
     *
     * @return Response
     */
    public function queueAction()
    {
        return new Response('It works!');
    }

    /**
     * @MutexRequest(mode="block")
     *
     * @return Response
     */
    public function blockAction()
    {
        return new Response('It works!');
    }

    /**
     * @MutexRequest(mode="check")
     *
     * @return Response
     */
    public function checkAction()
    {
        return new Response('It works!');
    }

    /**
     * @MutexRequest(mode="force")
     *
     * @return Response
     */
    public function forceAction()
    {
        return new Response('It works!');
    }
}
