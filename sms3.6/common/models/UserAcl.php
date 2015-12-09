<?php

include_once PATH_COMMON_CLASSES . 'Zend/Acl.php';

/**
 * Класс для настройки прав через Zend_Acl
 * В коде будут доступны константы, начинающиеся с ACL_
 */

class UserAcl extends Zend_Acl
{

    protected $_table_roles = 'acl_roles';
    protected $_table_resources = 'acl_resources';
    protected $_table_actions = 'acl_actions';
    protected $_table_permissions = 'acl_permissions';

    public function __construct() {

        $sql = "SHOW TABLES LIKE  'acl_%'";
        $tables = sql_getColumn($sql);

        try {
            // Роли
            if (in_array($this->_table_roles, $tables)) {
                $roles = $this->getList('roles');
                if ($roles) {
                    $this->createRoles($roles);
                }
            }

            // Ресурсы
            if (in_array($this->_table_resources, $tables)) {
                $resources = $this->getList('resources');
                if ($resources) {
                    $this->createResources($resources);
                }
            }

            // Действия
            if (in_array($this->_table_actions, $tables)) {
                $actions = $this->getList('actions');
                if ($actions) {
                    $this->createActions($actions);
                }
            }

            // Установка прав
            if (in_array($this->_table_permissions, $tables)) {
                $permissions = sql_getRows("SELECT * FROM {$this->_table_permissions}");
                if ($permissions) {
                    $this->setPermissions($permissions);
                }
            }
        }
        catch (Exception $e) {

        }
    }

    /**
     * Получение списка из кеша
     * @param $type
     * @return array
     */
    function getList($type) {

        $list = cache_get('acl_' . $type, true);
        if ($list) return $list;

        $list = $this->createList($type);
        cache_save('acl_' . $type, $list, true);
        return $list;
    }

    /**
     * Создание списка из БД
     * @param $type
     * @param int $pid
     * @param array $ret
     * @return array
     */
    function createList($type, $pid = 0, $ret = array()) {
        $table = "_table_" . $type;
        $list = sql_getRows("
        SELECT t.*, t2.name AS parent
        FROM {$this->$table} AS t
        LEFT JOIN {$this->$table} AS t2 ON t2.id=t.pid
        WHERE " . ($pid ? "t.pid=" . $pid : "t.pid IS NULL"));
        $ret = array_merge($ret, $list);
        foreach ($list as $item) {
            $l = $this->createList($type, $item['id']);
            if (is_array($l)) $ret = array_merge($ret, $l);
        }
        return $ret;
    }

    /**
     * Создание ролей
     * @param array $roles
     */
    function createRoles($roles) {
        foreach ($roles as $role) {
            $_main = 'ACL_ROLE_' . strtoupper($role['name']);
            define($_main, $role['id']);
            if ((int)$role['pid'] > 0) {
                $_parent = 'ACL_ROLE_' . strtoupper($role['parent']);
                if (!defined($_parent)) {
                    define($_parent, $role['pid']);
                }
                $this->addRole(new Zend_Acl_Role(constant($_main)), constant($_parent));
            } else {
                $this->addRole(new Zend_Acl_Role(constant($_main)));
            }
        }
    }

    /**
     * Создание ресурсов
     * @param array $resources
     */
    function createResources($resources) {
        foreach ($resources as $resource) {
            $_main = 'ACL_RESOURCE_' . strtoupper($resource['name']);
            define($_main, $resource['id']);
            if ((int)$resource['pid'] > 0) {
                $_parent = 'ACL_RESOURCE_' . strtoupper($resource['parent']);
                if (!defined($_parent)) {
                    define($_parent, $resource['pid']);
                }
                $this->addResource(new Zend_Acl_Resource(constant($_main)), constant($_parent));
            } else {
                $this->addResource(new Zend_Acl_Resource(constant($_main)));
            }
        }
    }

    /**
     * Создание действий
     * @param array $actions
     */
    function createActions($actions) {
        foreach ($actions as $action) {
            $_main = 'ACL_ACTION_' . strtoupper($action['name']);
            define($_main, $action['id']);
            if ((int)$action['pid'] > 0) {
                $_parent = 'ACL_ACTION_' . strtoupper($action['parent']);
                if (!defined($_parent)) {
                    define($_parent, $action['pid']);
                }
            }
        }
    }

    /**
     * Установка прав
     * @param $permissions
     */
    function setPermissions($permissions) {
        foreach ($permissions as $value) {
            $this->allow($value['role_id'], $value['resource_id'], $value['action_id']);
        }
    }

}