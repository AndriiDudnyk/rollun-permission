<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 02.02.17
 * Time: 17:48
 */

namespace rollun\permission\Auth\Middleware\ErrorHandler;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use rollun\permission\Acl\AccessForbiddenException;
use rollun\permission\Auth\CredentialInvalidException;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Helper\UrlHelper;

class CredentialErrorHandlerMiddleware
{
    public function __invoke($error, Request $request, Response $response, callable $next) {
        if ($error instanceof CredentialInvalidException) {
            $response = new HtmlResponse("Invalid credentials!", 401);
            return $response;

        }

//        $request = $request->withAttribute(Response::class, $response);


        if (isset($next)) {
            return $next($error, $request, $response);
        }

        return $response;
    }
}
