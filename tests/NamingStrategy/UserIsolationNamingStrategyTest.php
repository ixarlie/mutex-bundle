<?php declare(strict_types=1);

namespace IXarlie\MutexBundle\Tests\NamingStrategy;

use IXarlie\MutexBundle\MutexRequest;
use IXarlie\MutexBundle\NamingStrategy\NamingStrategy;
use IXarlie\MutexBundle\NamingStrategy\UserIsolationNamingStrategy;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Class UserIsolationNamingStrategyTest.
 */
final class UserIsolationNamingStrategyTest extends TestCase
{
    public function testInstance(): void
    {
        $strategy = new UserIsolationNamingStrategy(
            $this->createMock(NamingStrategy::class)
        );

        self::assertInstanceOf(NamingStrategy::class, $strategy);
    }

    public function testUserIsolationNotEnabled(): void
    {
        $request  = Request::create('/test');
        $config   = new MutexRequest(['name' => '']);
        $inner    = $this->createMock(NamingStrategy::class);
        $strategy = new UserIsolationNamingStrategy($inner);

        self::assertFalse($config->userIsolation);

        $inner
            ->expects(self::once())
            ->method('createName')
            ->willReturn('test_name')
        ;

        $name = $strategy->createName($config, $request);

        self::assertSame('test_name', $name);
    }

    public function testUserIsolationEnabledButMissingTokenStorage(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot use user isolation with missing "security.token_storage".');

        $request  = Request::create('/test');
        $config   = new MutexRequest(['name' => '', 'userIsolation' => true]);
        $inner    = $this->createMock(NamingStrategy::class);
        $strategy = new UserIsolationNamingStrategy($inner);

        self::assertTrue($config->userIsolation);

        $inner
            ->expects(self::once())
            ->method('createName')
            ->willReturn('test_name')
        ;

        $name = $strategy->createName($config, $request);

        self::assertSame('test_name', $name);
    }

    public function testUserIsolationEnabled(): void
    {
        $request      = Request::create('/test');
        $config       = new MutexRequest(['name' => '', 'userIsolation' => true]);
        $inner        = $this->createMock(NamingStrategy::class);
        $tokenStorage = new TokenStorage();
        $strategy     = new UserIsolationNamingStrategy($inner, $tokenStorage);

        self::assertTrue($config->userIsolation);

        $inner
            ->expects(self::once())
            ->method('createName')
            ->willReturn('test_name')
        ;

        $token = new AnonymousToken('secret', 'anon');
        $tokenStorage->setToken($token);

        $name = $strategy->createName($config, $request);

        self::assertSame('test_name' . md5($token->serialize()), $name);
    }
}
