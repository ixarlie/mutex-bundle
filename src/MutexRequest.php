<?php declare(strict_types=1);

namespace IXarlie\MutexBundle;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Required;

/**
 * Class MutexRequest
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 *
 * @Annotation
 * @Target({"METHOD"})
 */
final class MutexRequest extends Annotation
{
    /**
     * The Symfony locker service id.
     *
     * @var string
     * @Required
     */
    public $service;

    /**
     * One of the registered locking strategy.
     *
     * @var string
     * @Required
     */
    public $strategy;

    /**
     * Lock name. Otherwise, the naming strategy will generate one.
     *
     * @var string
     */
    public $name;

    /**
     * Some lockers implement a time-to-live option. This option is ignored for non compatible lockers.
     *
     * @var float
     */
    public $ttl = 300.0;

    /**
     * HTTP message if the lock throws an exception.
     *
     * @var string
     */
    public $message;

    /**
     * Append user information to the lock name to have isolated locks.
     *
     * @var bool
     */
    public $userIsolation = false;
}
