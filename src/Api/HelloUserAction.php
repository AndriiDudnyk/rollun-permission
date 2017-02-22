<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 16.01.17
 * Time: 12:26
 */

namespace rollun\permission\Api;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use rollun\permission\Auth\Middleware\UserResolver;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Stratigility\MiddlewareInterface;

class HelloUserAction implements MiddlewareInterface
{

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
     * @throws \Exception
     */
    public function __invoke(Request $request, Response $response, callable $out = null)
    {
        $data = [];
        $name = $request->getAttribute('name');
        $user = $request->getAttribute(UserResolver::KEY_ATTRIBUTE_USER);
        $data['user'] = $user;
        $data['str'] = "[" . constant('APP_ENV') . "] Hello $name!";

        if ($name === "error") {
            throw new \Exception("Exception by string: ".  $data['str']);
        }
        $request = $request->withAttribute('responseData', $data);

        if (isset($out)) {
            return $out($request, $response);
        }
        return $response;
    }
}
