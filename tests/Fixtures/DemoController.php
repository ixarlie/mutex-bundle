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
    #[MutexRequest(service: 'lock.default.factory', strategy: 'queue')]
    public function queue(): Response
    {
        return new Response('It works!');
    }

    #[MutexRequest(service: 'lock.default.factory', strategy: 'block')]
    public function block(): Response
    {
        return new Response('It works!');
    }

    #[MutexRequest(service: 'lock.default.factory', strategy: 'force')]
    public function force(): Response
    {
        return new Response('It works!');
    }

    #[MutexRequest(service: 'lock.default.factory', strategy: 'force')]
    #[MutexRequest(service: 'lock.default.factory', strategy: 'force')]
    public function double(): Response
    {
        return new Response('It works!');
    }
}
