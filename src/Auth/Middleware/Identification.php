<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 27.01.17
 * Time: 14:54
 */

namespace rollun\permission\Auth\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Stratigility\MiddlewareInterface;

class Identification implements MiddlewareInterface
{
    const DEFAULT_ROLE = 'guest';

    /** @var  AuthenticationServiceInterface */
    protected $authService;

    protected function __construct(AuthenticationServiceInterface $authenticationService)
    {
        $this->authService = $authenticationService;
    }

    /**
     * Process an incoming request and/or response.
     *
     * Accepts a server-side request and a response instance, and does
     * something with them.
     *
     * If the response is not complete and/or further processing would not
     * interfere with the work done in the middleware, or if the middleware
     * wants to delegate to another process, it can use the `$out` callable
     * if present.
     *
     * If the middleware does not return a value, execution of the current
     * request is considered complete, and the response instance provided will
     * be considered the response to return.
     *
     * Alternately, the middleware may return a response instance.
     *
     * Often, middleware will `return $out();`, with the assumption that a
     * later middleware will return a response.
     *
     * @param Request $request
     * @param Response $response
     * @param null|callable $out
     * @return null|Response
     */
    public function __invoke(Request $request, Response $response, callable $out = null)
    {
        $role = $this->authService->hasIdentity() ? $this->authService->getIdentity()['role'] : static::DEFAULT_ROLE;
        $request = $request->withAttribute('role', $role);

        if (isset($out)) {
            return $out($request, $response);
        }

        return $response;
    }
}
