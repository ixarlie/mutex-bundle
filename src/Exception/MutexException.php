<?php declare(strict_types=1);

namespace IXarlie\MutexBundle\Exception;

use IXarlie\MutexBundle\MutexRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Lock\Exception\ExceptionInterface;

/**
 * Class MutexException.
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class MutexException extends HttpException
{
    /**
     * @var MutexRequest
     */
    protected $config;

    /**
     * @param MutexRequest            $config
     * @param ExceptionInterface|null $e
     */
    public function __construct(MutexRequest $config, ExceptionInterface $e = null)
    {
        parent::__construct(Response::HTTP_LOCKED, $config->message ?? 'Resource is not available at this moment.', $e);

        $this->config = $config;
    }

    /**
     * @return MutexRequest
     */
    public function getConfig(): MutexRequest
    {
        return $this->config;
    }
}
