<?php

namespace ACAT\Slim\Actions;


use Exception;
use Psr\Log\LoggerInterface;
use ACAT\Slim\Port\ServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;

/**
 *
 */
abstract class AbstractAction extends Action
{

    /**
     * @var ServiceInterface
     */
    protected ServiceInterface $serviceInterface;

    /**
     * @param   ServiceInterface  $serviceInterface
     * @param   LoggerInterface   $logger
     */
    public function __construct(ServiceInterface $serviceInterface, LoggerInterface $logger)
    {
        parent::__construct($logger);
        $this->serviceInterface = $serviceInterface;
    }

    /**
     * @return Response
     */
    protected function action() : Response
    {
        try {
            return $this->process();
        }
        catch (Exception $e) {
            $this->logger->critical($e);
            if ( ! $e->getCode()) {
                $code = 500;
            } else {
                $code = $e->getCode();
            }

            return $this->response->withStatus($code);
        }
    }

    /**
     * @return Response
     */
    abstract protected function process() : Response;

}