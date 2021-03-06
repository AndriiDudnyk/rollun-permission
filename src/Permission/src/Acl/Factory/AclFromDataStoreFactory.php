<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 30.01.17
 * Time: 17:21
 */

namespace rollun\permission\Acl\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use rollun\datastore\DataStore\DataStoreAbstract;
use rollun\datastore\DataStore\Interfaces\DataStoresInterface;
use rollun\datastore\Rql\RqlQuery;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class AclFromDataStoreFactory implements FactoryInterface
{

    const KEY_ACL = 'acl';

    const KEY_DS_RULE_SERVICE = 'dataStoreRuleService';

    const KEY_DS_ROLE_SERVICE = 'dataStoreRoleService';

    const KEY_DS_RESOURCE_SERVICE = 'dataStoreResourceService';

    const KEY_DS_PRIVILEGE_SERVICE = 'dataStorePrivilegeService';

    const KEY_DS_ID = 'id';

    const KEY_DS_ROLE = 'role';

    const KEY_DS_RESOURCE = 'resource_id';

    const KEY_DS_PRIVILEGE = 'privileges_id';

    const DEFAULT_RULES_DS = 'rulesDS';

    const DEFAULT_ROLES_DS = 'rolesDS';

    const DEFAULT_RESOURCE_DS = 'resourceDS';

    const DEFAULT_PRIVILEGE_DS = 'privilegeDS';

    /**
     * Create an object
     * 'acl' => [
     *      AclFromDataStoreFactory::KEY_DS_RULE_SERVICE => AclFromDataStoreFactory::DEFAULT_RULES_DS,
     *      AclFromDataStoreFactory::KEY_DS_ROLE_SERVICE => AclFromDataStoreFactory::DEFAULT_ROLES_DS,
     *      AclFromDataStoreFactory::KEY_DS_RESOURCE_SERVICE => AclFromDataStoreFactory::DEFAULT_RESOURCE_DS,
     *      AclFromDataStoreFactory::KEY_DS_PRIVILEGE_SERVICE => AclFromDataStoreFactory::DEFAULT_PRIVILEGE_DS,
     *  ],
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
        $config = $container->get('config');
        $rulesDS = isset($config[static::KEY_ACL][static::KEY_DS_RULE_SERVICE]) ?
            $config[static::KEY_ACL][static::KEY_DS_RULE_SERVICE] : static::DEFAULT_RULES_DS;
        $rolesDS = isset($config[static::KEY_ACL][static::KEY_DS_ROLE_SERVICE]) ?
            $config[static::KEY_ACL][static::KEY_DS_ROLE_SERVICE] : static::DEFAULT_ROLES_DS;
        $resourceDS = isset($config[static::KEY_ACL][static::KEY_DS_PRIVILEGE_SERVICE]) ?
            $config[static::KEY_ACL][static::KEY_DS_PRIVILEGE_SERVICE] : static::DEFAULT_RESOURCE_DS;
        $privilegeDS = isset($config[static::KEY_ACL][static::KEY_DS_RESOURCE_SERVICE]) ?
            $config[static::KEY_ACL][static::KEY_DS_RESOURCE_SERVICE] : static::DEFAULT_PRIVILEGE_DS;

        if (!$container->has($rulesDS) ||
            !$container->has($rolesDS) ||
            !$container->has($resourceDS) ||
            !$container->has($privilegeDS)
        ) {
            throw new ServiceNotCreatedException('Not found dataStore service');
        }

        /** @var DataStoreAbstract $dataStoreRule */
        $dataStoreRule = $container->get($rulesDS);
        /** @var DataStoreAbstract $dataStoreRole */
        $dataStoreRole = $container->get($rolesDS);
        /** @var DataStoreAbstract $dataStorePrivilege */
        $dataStorePrivilege = $container->get($resourceDS);
        /** @var DataStoreAbstract $dataStoreResource */
        $dataStoreResource = $container->get($privilegeDS);

        $acl = new Acl();

        $this->aclAdd($dataStoreRole, $acl, "Role");
        $this->aclAdd($dataStoreResource, $acl, "Resource");

        foreach ($dataStoreRule as $item) {
            $role = $dataStoreRole->read($item['role_id']);
            $resource = $dataStoreResource->read($item['resource_id']);
            $privilege = $dataStorePrivilege->read($item['privilege_id']);
            if ($item['allow_flag']) {
                $acl->allow($role['name'], $resource['name'], $privilege['name']);
            } else {
                $acl->deny($role['name'], $resource['name'], $privilege['name']);
            }
        }

        return $acl;
    }

    /**
     * @param DataStoresInterface $dataStore
     * @param Acl $acl
     * @param $addType
     */
    private function aclAdd(DataStoresInterface $dataStore, Acl $acl, $addType)
    {
        $iterator = $dataStore->getIterator();
        foreach ($iterator as $role) {
            //todo: Check if exist role and resources.
            $parent = isset($role['parent_id']) ? $dataStore->read($role['parent_id'])['name'] : null;
            $acl->{"add" . $addType}($role['name'], $parent);
        }
    }

}
