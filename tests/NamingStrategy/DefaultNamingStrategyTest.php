<?php declare(strict_types=1);

namespace IXarlie\MutexBundle\Tests\NamingStrategy;

use IXarlie\MutexBundle\MutexRequest;
use IXarlie\MutexBundle\NamingStrategy\DefaultNamingStrategy;
use IXarlie\MutexBundle\NamingStrategy\NamingStrategy;
use IXarlie\MutexBundle\Tests\Fixtures\DemoController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DefaultNamingStrategyTest.
 */
final class DefaultNamingStrategyTest extends TestCase
{
    public function testInstance(): void
    {
        self::assertInstanceOf(NamingStrategy::class, new DefaultNamingStrategy());
    }

    public function testEmptyName(): void
    {
        $request  = Request::create('/test');
        $config   = new MutexRequest(['name' => '']);
        $strategy = new DefaultNamingStrategy();

        $request->attributes->set('_controller', DemoController::class . '::' . 'block');

        self::assertEmpty($config->name);

        $name = $strategy->createName($config, $request);

        self::assertSame('IXarlie_MutexBundle_Tests_Fixtures_DemoController__block_test', $name);
    }

    public function testNullName(): void
    {
        $request  = Request::create('/test');
        $config   = new MutexRequest(['name' => null]);
        $strategy = new DefaultNamingStrategy();

        $request->attributes->set('_controller', DemoController::class . '::' . 'block');

        self::assertEmpty($config->name);

        $name = $strategy->createName($config, $request);

        self::assertSame('IXarlie_MutexBundle_Tests_Fixtures_DemoController__block_test', $name);
    }

    public function testNameNeitherNullNorEmpty(): void
    {
        $request  = Request::create('/test');
        $config   = new MutexRequest(['name' => 'foobar']);
        $strategy = new DefaultNamingStrategy();

        $request->attributes->set('_controller', DemoController::class . '::' . 'block');

        $name = $strategy->createName($config, $request);

        self::assertSame('foobar', $name);
    }
}
