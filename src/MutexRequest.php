<?php declare(strict_types=1);

namespace IXarlie\MutexBundle;

use Symfony\Contracts\Service\Attribute\Required;

/**
 * Class MutexRequest
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class MutexRequest
{
    public function __construct(
        #[Required]
        public readonly string $service,
        #[Required]
        public readonly string $strategy,
        public ?string $name = null,
        public readonly ?string $message = null,
        public readonly ?float $ttl = 300.0,
        public readonly bool $userIsolation = false
    ) {
    }
}
