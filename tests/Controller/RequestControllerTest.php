<?php

namespace Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RequestControllerTest.
 */
class RequestControllerTest extends WebTestCase
{
    public function testCheck()
    {
        $response = $this->request('/test-check');

        static::assertInstanceOf(Response::class, $response);
    }

    public function testBlock()
    {
        $response = $this->request('/test-block');

        static::assertInstanceOf(Response::class, $response);
    }

    public function testForce()
    {
        $response = $this->request('/test-force');

        static::assertInstanceOf(Response::class, $response);
    }

    public function testQueue()
    {
        $response = $this->request('/test-queue');

        static::assertInstanceOf(Response::class, $response);
    }

    /**
     * @param string $path
     *
     * @return Response
     */
    protected function request($path)
    {
        $client = static::createClient(
            [
                'environment' => 'test',
                'debug'       => false
            ]
        );

        $client->request('GET', $path);

        $response = $client->getResponse();

        return $response;
    }
}
