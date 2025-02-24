<?php

namespace ACAT\Slim\Actions;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;

/**
 *
 */
abstract class AbstractAction extends Action {

    /**
	 * @return Response
	 */
	protected function action() : Response {
		try {
			return $this->process();
		}
		catch (Exception $e) {
			$this->logger->critical($e);
			if ( ! $e->getCode()) {
				$code = 500;
			}
			else {
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