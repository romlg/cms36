<?php

/**
 * ������ ������������ �����
 */

class User
{
    protected $_table = 'auth_users';
    protected $_table_roles = 'auth_users_roles';
    protected $_table_socials = 'auth_users_socials';
    protected $_id = null;
    protected $_data = array();

    function __construct($id = 0) {
        if ((int)$id) {
            $this->_id = (int)$id;
            $this->init();
        }
    }

    /**
     * �������������
     * @param bool $reset - ������������� �������� ������ � ������������
     */
    function init($reset = false) {
        static $_user_data = array();
        if (!isset($_user_data[$this->_id]) || $reset) {
            $_user_data[$this->_id] = sql_getRow("SELECT * FROM {$this->_table} WHERE id={$this->_id} LIMIT 1");
        }
        $this->_data = $_user_data[$this->_id];
    }

    /**
     * ���������� id ������������
     * @return mixed
     */
    function getId() {
        return $this->_data['id'];
    }

    /**
     * ��������� ������
     * @param $data
     */
    public function setData($data) {
        $this->_data = $data;
    }

    /**
     * ��������� ������
     * @return array
     */
    public function getData() {
        return $this->_data;
    }

    /**
     * ��������� �������� ��� ����
     * @param $name
     * @param $value
     */
    public function set($name, $value) {
        $this->_data[$name] = $value;
    }

    /**
     * ��������� �������� ����
     * @param $name
     * @return bool
     */
    public function get($name) {
        if (array_key_exists($name, $this->_data)) {
            return $this->_data[$name];
        } else return false;
    }

    /**
     * �������� ������������
     * @return bool|int|null
     */
    function create() {
        $id = sql_insert($this->_table, $this->_data);
        if (is_int($id)) {
            $this->_id = $id;
            $this->init();
            return $this->_id;
        }
        return false;
    }

    /**
     * ���������� ������
     * @return bool
     */
    function update() {
        if (sql_updateId($this->_table, $this->_data, $this->_id)) {
            $this->init();
            return true;
        }
        return false;
    }

    /**
     * �������� ������������
     * @return bool
     */
    function delete() {
        if (sql_delete($this->_table, $this->_id)) {
            return true;
        }
        return false;
    }

    /**
     * �������� ������������ ����
     * @param $role_id
     * @param $root_id
     */
    function addRole($role_id, $root_id = '') {
        $root_id = $root_id ? $root_id : ROOT_ID;
        sql_insert($this->_table_roles, array('user_id' => $this->_id, 'role_id' => $role_id, 'root_id' => $root_id), true);
    }

    /**
     * ������ ���� � ������������
     * @param $role_id
     * @param $root_id
     */
    function delRole($role_id, $root_id = '') {
        $root_id = $root_id ? $root_id : ROOT_ID;
        sql_query("DELETE FROM {$this->_table_roles} WHERE user_id={$this->_id} AND role_id={$role_id} AND root_id=" . $root_id);
    }

    /**
     * ������� ��� ������� ���� ������������
     * @param $root_id
     * @return array
     */
    function getRoles($root_id = '') {
        static $user_roles;
        if (!isset($user_roles)) $user_roles = array();
        $root_id = $root_id ? $root_id : ROOT_ID;
        if (!isset($user_roles[$this->_id][$root_id])) {
            $user_roles[$this->_id][$root_id] = sql_getColumn("SELECT role_id FROM {$this->_table_roles} WHERE user_id={$this->_id} AND root_id=" . $root_id);
        }
        return $user_roles[$this->_id][$root_id];
    }

    /**
     * ������� ��� ���� ������������
     * @param $root_id
     * @return array
     */
    function delRoles($root_id = '') {
        $root_id = $root_id ? $root_id : ROOT_ID;
        return sql_query("DELETE FROM {$this->_table_roles} WHERE user_id={$this->_id} AND root_id=" . $root_id);
    }

    /**
     * ���� �� � ������������ ����� ����?
     * @param $role_id
     * @param $root_id
     * @return bool
     */
    function hasRole($role_id, $root_id = '') {
        if (!$this->_id) return false;
        $root_id = $root_id ? $root_id : ROOT_ID;
        static $_roles;
        if (!isset($_roles)) $_roles = sql_getRows("SELECT role_id, user_id FROM {$this->_table_roles} WHERE root_id=" . $root_id . " AND user_id = " . $this->_id, true);
        return isset($_roles[$role_id]) ? $_roles[$role_id] : false;
    }

    /**
     * �������� ���� �������
     * @param $resource_id - ������
     * @param $action_id - ��������
     * @return bool
     */
    function isAllowed($resource_id, $action_id) {
        // ������ ���� ������������
        $roles = $this->getRoles();
        if (!$roles) return false;

        /**
         * @var UserAcl $acl
         */
        global $acl;

        foreach ($roles as $role_id) {
            // ��������� ����� ��� ����
            if ($acl->isAllowed($role_id, $resource_id, $action_id)) return true;
        }
        return false;
    }

    /**
     * �������� ����� ������ �� ���. ���� ��� ���������� �������
     * @param $data
     * @return int|string
     */
    function createSocialLink($data) {
        $link_id = sql_getValue("SELECT id FROM {$this->_table_socials} WHERE user_id={$this->_id} AND provider='{$data['provider']}'");
        $link_data = array(
            'identifier' => $data['identifier'],
            'profileURL' => $data['profileURL'],
            'photoURL' => $data['photoURL'],
            'name' => $data['name'],
        );
        if ($link_id) {
            sql_updateId($this->_table_socials, $link_data, $link_id);
        } else {
            $link_data['user_id'] = $this->_id;
            $link_data['provider'] = $data['provider'];
            $link_id = sql_insert($this->_table_socials, $link_data);
        }
        return $link_id;
    }

    /**
     * �������� ������ �� ���. ����
     * @param $link_id
     * @return bool
     */
    function delSocialLink($link_id) {
        $id = sql_getValue("SELECT user_id FROM {$this->_table_socials} WHERE id={$link_id}");
        if ($id != $this->_id) return false;
        sql_delete($this->_table_socials, $link_id);
        return true;
    }

    /**
     * ���������� ������ ���. �������� ������������
     * @return array
     */
    function getSocialLinks() {
        return sql_getRows("SELECT * FROM {$this->_table_socials} WHERE user_id={$this->_id}");
    }

    /**
     * ��������� �������� ������������
     * @param string  $default - �������� ��������� � ������ �� ���������
     * @return bool|string
     */
    public function getImage($default = 'person_no_pic_img') {
        $image = $this->get('image');
        if (!$image || !is_file(ltrim($image, '/\\'))) {
            $image = sql_getValue("SELECT photoUrl FROM {$this->_table_socials} WHERE user_id={$this->_id} AND photoUrl<>'' ORDER BY id DESC LIMIT 1");
            if (!$image) {
                $page = & Registry::get('TPage');
                $image = $page->tpl->get_config_vars($default);
            }
        }
        return $image;
    }

}