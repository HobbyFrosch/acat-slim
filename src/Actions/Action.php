<?php

namespace ACAT\Slim\Actions;

use ACAT\Slim\Exception\RecordNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

/**
 *
 */
abstract class Action {

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var Request
     */
    protected Request $request;

    /**
     * @var Response
     */
    protected Response $response;

    /**
     * @var array
     */
    protected array $args;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws HttpNotFoundException
     * @throws HttpBadRequestException
     */
    public function __invoke(Request $request, Response $response, array $args): Response {

    	$this->request = $request;
        $this->response = $response;
        $this->args = $args;

        try {
            return $this->action();
        }
        catch (RecordNotFoundException $e) {
            throw new HttpNotFoundException($this->request, $e->getMessage());
        }

    }

    /**
     * @return Response
     */
    abstract protected function action(): Response;

    /**
     * @return mixed
     * @throws HttpBadRequestException
     */
    protected function getFormData(): mixed {

    	$input = (array) $this->request->getParsedBody();

    	if (!$input) {
			$input = file_get_contents('php://input');
			if ($input) {
				$input = json_decode($input, true, 512,JSON_OBJECT_AS_ARRAY);
			}
		}

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new HttpBadRequestException($this->request, 'Malformed JSON input -> ' . json_last_error_msg());
        }

        return $input;

    }

    /**
     * @param  string $name
     * @return mixed
     * @throws HttpBadRequestException
     */
    protected function resolveArg(string $name): mixed {

        if (!isset($this->args[$name])) {
            throw new HttpBadRequestException($this->request, "Could not resolve argument `$name`.");
        }

        return $this->args[$name];

    }

    /**
     * @param mixed
     * @param int $statusCode
     * @return Response
     */
    protected function respondWithData(mixed $data = null, int $statusCode = 200): Response {
        return $this->respond(new ActionPayload($statusCode, $data));
    }

	/**
	 * @param null $message
	 * @param int $statusCode
	 * @return Response
	 */
    protected function respondWithError($message = null, int $statusCode = 500) : Response {
    	return $this->respond(new ActionPayload($statusCode, $message));
	}

	/**
	 * @param string $destination
	 * @return Response
	 */
	protected function withRedirectFor(string $destination) : Response {
    	return $this->response->withStatus(302)->withHeader('Location', $destination);
	}

    /**
     * @param ActionPayload $payload
     * @return Response
     */
    protected function respond(ActionPayload $payload): Response {

        $json = json_encode($payload, JSON_PRETTY_PRINT);

        $this->response->getBody()->write($json);

        return $this->response
                    ->withHeader('Content-Type', 'application/json')
                    ->withHeader('X-Smurf', 'Schluuuuuumpf')
                    ->withHeader('x-FCK-Putin', '#StandWithUkraine')
                    ->withStatus($payload->getStatusCode());

    }
}
