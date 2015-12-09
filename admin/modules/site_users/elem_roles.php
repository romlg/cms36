<?php

include_once PATH_COMMON . "/models/User.php";

/**
 *
 * Модуль "Пользователи сайта" (форма редактирования ролей)
 *
 * @package    admin/modules
 */
require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TRolesElement extends TElems
{

    var $editable = array();
    var $elem_name = "elem_acl_roles";
    var $elem_table = "auth_users";
    var $elem_type = "single";
    var $elem_str = array();
    var $elem_fields = array(
        'columns' => array(
            'updated' => array(
                'type' => 'hidden',
            ),
            'data' => array(
                'type' => 'words',
            ),
        ),
        'id_field' => 'id',
        'folder' => ''
    );
    var $elem_req_fields = array();
    var $script = "
    function approveItems(status) {
        var formname = 'editform';
        var form = $('form[name=' + formname + ']');

        $('<input type=\"hidden\" name=\"approve_status\" value=\"' + status + '\" />').appendTo(form);
        form.submit();
    }";

    ########################

    function ElemInit() {
        $id_user = (int)get('id', 0, 'pg');

        global $site_domains;

        $user_roles = array();
        $user_roles_tmp = sql_getRows("SELECT * FROM auth_users_roles WHERE user_id = " . (int)$id_user);
        $roles = sql_getRows("SELECT * FROM acl_roles");
        foreach ($user_roles_tmp as $v) {
            $user_roles[$v['root_id']][$v['role_id']] = true;
        }
        unset($user_roles_tmp);

        // рисуем таблицу
        $data = '<table class="ajax_table_main" cellspacing="1">';
        $data .= '<tr class="ajax_table_header_row">';
        $data .= '<th class="ajax_table_header_cell">&nbsp;</th>';
        foreach ($roles as $role) {
            $data .= '<th class="ajax_table_header_cell" style="text-align: center;">' . $role['description'] . '</th>';
        }
        $data .= '</tr>';

        foreach ($site_domains as $site) {
            foreach ($site['langs'] as $l) {
                $data .= '<tr class="ajax_table_row">';
                $data .= '<td class="ajax_table_cell" style="text-align: left; vertical-align: middle">' . $site['descr'] . (count($site['langs']) > 1 ? ' (' . $l['descr'] . ')' : '') . '<br /><a target="_blank" href="http://' . $site['name'] . '/' . (count($site['langs']) > 1 ? $l['name'] : '') . '">' . $site['name'] . '</a></td>';
                reset($roles);
                foreach ($roles as $role) {
                    $role_checked = (isset($user_roles[$l['root_id']][$role['id']])) ? "checked" : null;
                    $data .= '<td class="ajax_table_cell" style="text-align: center;"><input type="checkbox" name="fld[sites][' . $l['root_id'] . '][roles][' . $role['id'] . ']" value="1" ' . $role_checked . ' /></td>';
                }
                $data .= '</tr>';
            }
        }
        $data .= '</table>';

        $this->elem_fields['columns']['data']['value'] = $data;
        return parent::ElemInit();
    }

    /**
     * Вызывается после сохранения в БД
     * @param array $fld
     * @param integer $id
     * @return array
     */
    function ElemRedactAfter($fld, $id) {
        global $site_domains;

        $id_user = (int)get('id', 0, 'pg');
        $fld = $_POST['fld'];

        if (!empty($id_user)) {
            $user = new User($id_user);
            foreach ($site_domains as $site) {
                foreach ($site['langs'] as $l) {
                    $user->delRoles($l['root_id']);
                    $roles = $fld['sites'][$l['root_id']]['roles'];
                    if (is_array($roles) AND !empty($roles)) {
                        foreach ($fld['sites'][$l['root_id']]['roles'] as $role_id => $role) {
                            $user->addRole($role_id, $l['root_id']);
                        }
                    }
                }
            }
        }
        return $fld;
    }
}

?>