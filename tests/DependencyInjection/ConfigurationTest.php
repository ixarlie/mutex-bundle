<?php declare(strict_types=1);

namespace IXarlie\MutexBundle\Tests\DependencyInjection;

use IXarlie\MutexBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

/**
 * Class ConfigurationTest
 */
final class ConfigurationTest extends TestCase
{
    public function testDefaults(): void
    {
        $options  = [
            'ixarlie_mutex' => [],
        ];
        $result   = $this->processConfiguration($options);
        $expected = [
            'factories' => [],
        ];

        self::assertSame($expected, $result);
    }

    public function testFactories(): void
    {
        $options  = [
            'ixarlie_mutex' => [
                'factories' => [
                    'lock.main.factory',
                    'lock.alt.factory',
                ],
            ],
        ];
        $result   = $this->processConfiguration($options);
        $expected = [
            'factories' => [
                'lock.main.factory',
                'lock.alt.factory',
            ],
        ];

        self::assertSame($expected, $result);
    }

    /**
     * @param array $options
     *
     * @return array
     */
    private function processConfiguration(array $options): array
    {
        $processor     = new Processor();
        $configuration = new Configuration();

        return $processor->processConfiguration($configuration, $options);
    }
}
