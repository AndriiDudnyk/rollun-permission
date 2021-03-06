<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 01.03.17
 * Time: 15:53
 */

namespace rollun\permission\Auth\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use rollun\dic\InsideConstruct;
use rollun\logger\Logger;
use rollun\permission\Auth\Adapter\AbstractWebAdapter;
use rollun\permission\Auth\Adapter\Session as SessionAuthAdapter;
use rollun\permission\Auth\AlreadyLogginException;
use rollun\permission\Auth\CredentialInvalidException;
use rollun\permission\Auth\RuntimeException;
use Zend\Session\Container;


class AuthenticationAction extends AbstractAuthentication
{
    const DEFAULT_SESSION_MEMBER = SessionAuthAdapter::DEFAULT_SESSION_MEMBER;

    const DEFAULT_SESSION_SERVICE_NAME = SessionAuthAdapter::DEFAULT_SESSION_SERVICE_NAME;

    /** @var  Container */
    protected $sessionContainer;

    /** @var  Logger */
    protected $logger;

    /**
     * AuthenticationAction constructor.
     * @param AbstractWebAdapter $adapter
     * @param Container|null $sessionContainer
     * @throws RuntimeException
     */
    public function __construct(AbstractWebAdapter $adapter, Container $sessionContainer = null)
    {
        InsideConstruct::setConstructParams(
            [
                'sessionContainer' => static::DEFAULT_SESSION_SERVICE_NAME,
                'logger' => Logger::DEFAULT_LOGGER_SERVICE
            ]);
        if (!isset($this->sessionContainer)) {
            throw new RuntimeException("Session container not found!");
        }
        parent::__construct($adapter);
    }

    /**
     * Authentication user
     * @param Request $request
     * @param Response $response
     * @param null|callable $out
     * @return null|Response
     * @throws AlreadyLogginException
     * @throws CredentialInvalidException
     */
    public function __invoke(Request $request, Response $response, callable $out = null)
    {
        if (!$this->sessionContainer->offsetExists(static::DEFAULT_SESSION_MEMBER)) {

            $this->adapter->setRequest($request);
            $this->adapter->setResponse($response);

            $result = $this->adapter->authenticate();
            if ($result->isValid()) {
                $identity = $result->getIdentity();
                $this->sessionContainer->offsetSet(static::DEFAULT_SESSION_MEMBER, $identity);
                $request = $request->withAttribute(static::KEY_IDENTITY, $identity)
                    ->withAttribute('responseData', ['status' => 'login']);
                $this->logger->debug("credential valid. Loggined $identity user. [" . microtime(true) . "]");
            } else {
                $this->logger->debug("credential error. [" . microtime(true) . "]");
                $request = $request->withAttribute('responseData', ['status' => 'credential error.']);
                //throw new CredentialInvalidException("Auth credential error.");
            }
        }
        if (isset($out)) {
            return $out($request, $response);
        }
        return $response;
    }
}
