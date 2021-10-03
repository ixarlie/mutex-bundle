<?php declare(strict_types=1);

namespace IXarlie\MutexBundle\Tests\Fixtures;

use IXarlie\MutexBundle\MutexRequest;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DemoController
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
final class DemoController
{
    /**
     * @MutexRequest(strategy="queue")
     *
     * @return Response
     */
    public function queue(): Response
    {
        return new Response('It works!');
    }

    /**
     * @MutexRequest(strategy="block")
     *
     * @return Response
     */
    public function block(): Response
    {
        return new Response('It works!');
    }

    /**
     * @MutexRequest(strategy="force")
     *
     * @return Response
     */
    public function force(): Response
    {
        return new Response('It works!');
    }

    /**
     * @MutexRequest("lock.default.factory", strategy="force")
     * @MutexRequest("lock.default.factory", strategy="force")
     *
     * @return Response
     */
    public function double(): Response
    {
        return new Response('It works!');
    }
}
