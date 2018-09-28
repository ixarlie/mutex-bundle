<?php

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;

/**
 * FixturesTestDebugProjectContainer.
 *
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 */
class FixturesTestDebugProjectContainer extends Container
{
    private $parameters;
    private $targetDirs = array();

    /**
     * Constructor.
     */
    public function __construct()
    {
        $dir = __DIR__;
        for ($i = 1; $i <= 5; ++$i) {
            $this->targetDirs[$i] = $dir = dirname($dir);
        }
        $this->parameters = $this->getDefaultParameters();

        $this->services = array();
        $this->methodMap = array(
            'annotation_reader' => 'getAnnotationReaderService',
            'i_xarlie_mutex.controller.listener' => 'getIXarlieMutex_Controller_ListenerService',
            'i_xarlie_mutex.locker_flock.default' => 'getIXarlieMutex_LockerFlock_DefaultService',
            'i_xarlie_mutex.locker_memcache.default' => 'getIXarlieMutex_LockerMemcache_DefaultService',
            'i_xarlie_mutex.locker_memcached.default' => 'getIXarlieMutex_LockerMemcached_DefaultService',
            'i_xarlie_mutex.locker_predis.default' => 'getIXarlieMutex_LockerPredis_DefaultService',
            'i_xarlie_mutex.locker_redis.default' => 'getIXarlieMutex_LockerRedis_DefaultService',
        );
        $this->aliases = array(
            'i_xarlie_mutex.locker' => 'i_xarlie_mutex.locker_flock.default',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function compile()
    {
        throw new LogicException('You cannot compile a dumped frozen container.');
    }

    /**
     * Gets the 'annotation_reader' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Doctrine\Common\Annotations\AnnotationReader A Doctrine\Common\Annotations\AnnotationReader instance.
     */
    protected function getAnnotationReaderService()
    {
        return $this->services['annotation_reader'] = new \Doctrine\Common\Annotations\AnnotationReader();
    }

    /**
     * Gets the 'i_xarlie_mutex.controller.listener' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \IXarlie\MutexBundle\EventListener\MutexRequestListener A IXarlie\MutexBundle\EventListener\MutexRequestListener instance.
     */
    protected function getIXarlieMutex_Controller_ListenerService()
    {
        $a = $this->get('i_xarlie_mutex.locker_flock.default');

        $this->services['i_xarlie_mutex.controller.listener'] = $instance = new \IXarlie\MutexBundle\EventListener\MutexRequestListener(new \Doctrine\Common\Annotations\AnnotationReader());

        $instance->addLockerManager('i_xarlie_mutex.locker_flock.default', $a);
        $instance->addLockerManager('i_xarlie_mutex.locker_redis.default', $this->get('i_xarlie_mutex.locker_redis.default'));
        $instance->addLockerManager('i_xarlie_mutex.locker_predis.default', $this->get('i_xarlie_mutex.locker_predis.default'));
        $instance->addLockerManager('i_xarlie_mutex.locker_memcache.default', $this->get('i_xarlie_mutex.locker_memcache.default'));
        $instance->addLockerManager('i_xarlie_mutex.locker_memcached.default', $this->get('i_xarlie_mutex.locker_memcached.default'));
        $instance->addLockerManager('i_xarlie_mutex.locker', $a);
        $instance->setHttpExceptionOptions('No way!', 418);
        $instance->setMaxQueueTimeout(60);
        $instance->setMaxQueueTry(8);

        return $instance;
    }

    /**
     * Gets the 'i_xarlie_mutex.locker_flock.default' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \IXarlie\MutexBundle\Manager\LockerManager A IXarlie\MutexBundle\Manager\LockerManager instance.
     */
    protected function getIXarlieMutex_LockerFlock_DefaultService()
    {
        return $this->services['i_xarlie_mutex.locker_flock.default'] = new \IXarlie\MutexBundle\Manager\LockerManager(new \NinjaMutex\Lock\FlockLock(__DIR__));
    }

    /**
     * Gets the 'i_xarlie_mutex.locker_memcache.default' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \IXarlie\MutexBundle\Manager\LockerManager A IXarlie\MutexBundle\Manager\LockerManager instance.
     */
    protected function getIXarlieMutex_LockerMemcache_DefaultService()
    {
        $a = new \Memcache();
        $a->addserver('localhost', 6379);

        return $this->services['i_xarlie_mutex.locker_memcache.default'] = new \IXarlie\MutexBundle\Manager\LockerManager(new \NinjaMutex\Lock\MemcacheLock($a));
    }

    /**
     * Gets the 'i_xarlie_mutex.locker_memcached.default' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \IXarlie\MutexBundle\Manager\LockerManager A IXarlie\MutexBundle\Manager\LockerManager instance.
     */
    protected function getIXarlieMutex_LockerMemcached_DefaultService()
    {
        $a = new \Memcached();
        $a->addServer('localhost', 6379);

        return $this->services['i_xarlie_mutex.locker_memcached.default'] = new \IXarlie\MutexBundle\Manager\LockerManager(new \NinjaMutex\Lock\MemcachedLock($a));
    }

    /**
     * Gets the 'i_xarlie_mutex.locker_predis.default' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \IXarlie\MutexBundle\Manager\LockerManager A IXarlie\MutexBundle\Manager\LockerManager instance.
     */
    protected function getIXarlieMutex_LockerPredis_DefaultService()
    {
        return $this->services['i_xarlie_mutex.locker_predis.default'] = new \IXarlie\MutexBundle\Manager\LockerManager(new \NinjaMutex\Lock\PredisRedisLock(new \Predis\Client(array('host' => 'localhost', 'port' => 6379), array())));
    }

    /**
     * Gets the 'i_xarlie_mutex.locker_redis.default' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \IXarlie\MutexBundle\Manager\LockerManager A IXarlie\MutexBundle\Manager\LockerManager instance.
     */
    protected function getIXarlieMutex_LockerRedis_DefaultService()
    {
        $a = new \Redis();
        $a->connect('localhost', 6379);

        return $this->services['i_xarlie_mutex.locker_redis.default'] = new \IXarlie\MutexBundle\Manager\LockerManager(new \IXarlie\MutexBundle\Lock\RedisLock($a));
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter($name)
    {
        $name = strtolower($name);

        if (!(isset($this->parameters[$name]) || array_key_exists($name, $this->parameters))) {
            throw new InvalidArgumentException(sprintf('The parameter "%s" must be defined.', $name));
        }

        return $this->parameters[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function hasParameter($name)
    {
        $name = strtolower($name);

        return isset($this->parameters[$name]) || array_key_exists($name, $this->parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function setParameter($name, $value)
    {
        throw new LogicException('Impossible to call set() on a frozen ParameterBag.');
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterBag()
    {
        if (null === $this->parameterBag) {
            $this->parameterBag = new FrozenParameterBag($this->parameters);
        }

        return $this->parameterBag;
    }

    /**
     * Gets the default parameters.
     *
     * @return array An array of the default parameters
     */
    protected function getDefaultParameters()
    {
        return array(
            'kernel.root_dir' => $this->targetDirs[1],
            'kernel.environment' => 'test',
            'kernel.debug' => true,
            'kernel.name' => 'Fixtures',
            'kernel.cache_dir' => __DIR__,
            'kernel.logs_dir' => __DIR__,
            'kernel.bundles' => array(
                'IXarlieMutexBundle' => 'IXarlie\\MutexBundle\\IXarlieMutexBundle',
            ),
            'kernel.charset' => 'UTF-8',
            'kernel.container_class' => 'FixturesTestDebugProjectContainer',
            'i_xarlie_mutex.lock_manager_class' => 'IXarlie\\MutexBundle\\Manager\\LockerManager',
            'i_xarlie_mutex.redis.connection.class' => 'Redis',
            'i_xarlie_mutex.predis.connection.class' => 'Predis\\Client',
            'i_xarlie_mutex.memcache.connection.class' => 'Memcache',
            'i_xarlie_mutex.memcached.connection.class' => 'Memcached',
            'ninja_mutex.locker_flock_class' => 'NinjaMutex\\Lock\\FlockLock',
            'ninja_mutex.locker_predis_class' => 'NinjaMutex\\Lock\\PredisRedisLock',
            'ninja_mutex.locker_memcache_class' => 'NinjaMutex\\Lock\\MemcacheLock',
            'ninja_mutex.locker_memcached_class' => 'NinjaMutex\\Lock\\MemcachedLock',
            'ninja_mutex.locker_redis_class' => 'IXarlie\\MutexBundle\\Lock\\RedisLock',
        );
    }
}
