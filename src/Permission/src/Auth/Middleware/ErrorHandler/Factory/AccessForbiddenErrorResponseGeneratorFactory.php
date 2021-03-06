<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 02.03.17
 * Time: 14:33
 */

namespace rollun\permission\Auth\Middleware\ErrorHandler\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use rollun\permission\Auth\Middleware\ErrorHandler\AccessForbiddenErrorResponseGenerator;
use Zend\Expressive\Helper\UrlHelper;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class AccessForbiddenErrorResponseGeneratorFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return object
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when
     *     creating a service.
     * @throws ContainerException if any other error occurs
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $urlHelper = $container->get(UrlHelper::class);
        return new AccessForbiddenErrorResponseGenerator($urlHelper);
    }
}