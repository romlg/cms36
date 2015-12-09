<?
/**
 * ����� ��������������� ������� ��� ���������
 *
 */
class EdUtils
{

    /**
     *  ������������� root_id ��� ������ ��������
     *
     * @param ������� ������ $id
     * @param ������ $pid
     */
    function setRootID($id, $pid) {
        //$id = get('id','0','p');
        $root_id = sql_getValue("SELECT root_id FROM tree WHERE id = " . $id);
        $err = sql_getErrNo(); // �������� �� ������������� ���� � �������( ���� ��� : 1054)

        if ((!$root_id || $root_id == '0') && !$err) {
            // ���������� $root_id
            // ���� root_id ����� � �����������
            $home_id = sql_getValue("SELECT root_id FROM tree WHERE id = " . $pid['pid']);
            if ($home_id) {
                sql_query("UPDATE tree SET root_id = " . $home_id . " WHERE id=" . $id);
            } else {
                $pid = $pid['pid'];
                do {
                    $home = sql_getRow("SELECT pid,root_id FROM tree WHERE id = " . $pid);
                    // ���� ��� ���� �� ����� �� ��������������� , ����� �������� �� �����
                    if ($pid == $home['pid']) {
                        $home['root_id'] = $pid;
                        break;
                    }
                    $pid = $home['pid'];
                }
                while (empty($home['root_id']));
                sql_query("UPDATE tree SET root_id = " . $home['root_id'] . " WHERE id=" . $id);
            }
        }
    }

    /**
     * ����������, ��������� �� � ������ ��������, ��� ���
     *
     * @param class $parent
     * @return bool
     */
    function isTree($parent) {
        if ($parent->name == 'tree' && $parent->elem_name == 'elem_main' && (!defined('LANG_SELECT') || !LANG_SELECT)) return true;
        return false;
    }

    /**
     * ��������� ������ �� ������������� ���� � �������� ������� �����
     *
     * @param array() $fld
     * @param class $parent
     */
    function verifyFiles(&$fld, &$parent) {

        $files_params = array();
        // ���������� ����, ��� ����� ��������� ����������� �� ��������� ������
        foreach ($parent->elem_fields['columns'] as $k => $v) {
            if ($v['type'] == 'input_image' || $v['type'] == 'input_file') {
                $files_params[$k] = $v['display'];
            }
        }

        //2 forecha ����� ����������, ��� ���������, �� ���-��������:)
        //������������� ������������� ����, � ���� ��� �� ���������, �� ��������� ��
        for ($i = 0; $i < count($files_params); $i++) {
            foreach ($files_params as $k => $v) {
                if (isset($v['friend']) && array_key_exists($v['friend'], $files_params)) {
                    $friend = $v['friend'];
                    if (!empty($fld[$friend]) && substr($fld[$friend], -strlen(' - auto')) == ' - auto') {
                        // ������ ��� �������� ������������ ������������� (friend)
                        $fld[$friend] = '';
                    }
                    if (!empty($fld[$k]) && empty($fld[$friend])) {
                        //������� ����� �����
                        $temp = false;
                        if (substr($fld[$k], 0, 5) == '@temp') $temp = true;
                        $filename = $temp ? substr($fld[$k], 5) : $fld[$k];
                        $new_filename = file_getUniName($filename);
                        copy($filename, $new_filename);
                        $fld[$friend] = $temp ? '@temp' . $new_filename : $new_filename;
                    }
                }
            }
        }
    }

    /**
     * ��������� ������ ������ � �������� ��
     *
     * @param array() $fld
     * @param class $parent
     */
    function resizeFiles(&$fld, &$parent) {

        $quality = sql_getValue('SELECT value FROM strings WHERE name="resize_quality" AND module="site" AND root_id=' . domainRootId() . ' AND lang="' . lang() . '"');
        if (!$quality) $quality = 85;

        // ���������� ����, ��� ����� ��������� ����������� �� ��������� ������
        foreach ($parent->elem_fields['columns'] as $k => $v) {
            if ($v['type'] == 'input_image' || $v['type'] == 'input_file' && !empty($fld[$k])) {
                // �� ���� ��������� �� ��������� ��������
                if (strpos($fld[$k], 'http://') !== false) continue;
                if (!in_array(strtolower(substr($fld[$k], strrpos($fld[$k], '.') + 1)), array('jpeg', 'jpg', 'gif', 'png'))) continue;
                $delete = true;
                //�������� � ��������� ���������
                if (substr($fld[$k], 0, 5) == '@temp') {
                    $temp = true;
                    $delete = false;
                }

                $path = $temp ? substr($fld[$k], 5) : $fld[$k];
                $file_dir = dirname($path);

                if (!is_dir($file_dir)) {
                    $file_dir = ".." . $file_dir;
                    if (!is_dir($file_dir)) {
                        echo "���������� �� �������";
                    }
                }
                $file_name = basename($path);
                $size = $v['display']['size']; //������ �����
                $as_size = isset($v['display']['as_size']) ? $v['display']['as_size'] : false; //��� �������
                $file = ResampleImage($file_dir, $file_name, $size, $delete, $quality, $as_size);
                if (!$temp) {
                    $file = substr($file, 2);
                }
                $fld[$k] = $temp ? '@temp' . $file : $file;
            }
        }
    }

    /**
     * ��������� ������ ������ � �������� ��
     *
     * @param array() $fld
     * @param class $parent
     */
    function putFiles(&$fld, &$parent) {

        $main_root_id = defined('MAIN_ROOT_ID') ? MAIN_ROOT_ID : 100;
        $root_id = domainRootId();

        $module = & Registry::get('T' . $parent->name);
        $selector = $module ? (bool)$module->selector : true;

        foreach ($parent->elem_fields['columns'] as $k => $v) {
            if (($v['type'] == 'input_image' || $v['type'] == 'input_file') && !empty($fld[$k])) {
                if (substr($fld[$k], 0, 5) == '@temp') {
                    //������������� ����� � ������ ����������
                    //���������� ���������� ��������� id � folder ��� ����� ��� �����
                    $source_file = substr($fld[$k], 5);

                    /*// ���� ���������������� �������� �������� root_id, �.�. ����� �� ��� ����������
                    // ����� ������ �����-������ ���������
                     * if ($main_root_id == $root_id || !$selector) {
                        $folder = FILES_DIR;
                    }
                    else {
                        $folder = FILES_DIR . $root_id . '/';
                        if (!is_dir($folder)) {
                            mkdir($folder);
                            chmod($folder, DIRS_MOD);
                        }
                    }*/

                    $folder = FILES_DIR;

                    if (isset($v['display']['folder'])) {
                        $folder .= $v['folder'] . '/'; //���������� ��� ����������� �����������
                    }
                    elseif ($parent->elem_fields['folder']) {
                        $folder .= $parent->elem_fields['folder'] . '/'; //���������� ��� ����������� ����������
                    }
                    verifyDir($folder);
                    $id = get('id', '0', 'p');
                    $is_fancy = get('is_fancy', '', 'gp');
                    if ($is_fancy) {
                        $id = get('pid', '0', 'gp');
                    }

                    //������ ������-�� �������� id :)
                    if (is_array($id)) $id = $_POST['fld']['id'];

                    //@todo ������� ��������, ��� ������ ������ � id!=0 ����� ���-���� ������������ � ������ ������ ����� �������,
                    //����� ��������� ��� ������ � ��� ������� ���� ��� ����� ��..�� ���� ����:)
                    if ($id) $folder .= $id;
                    verifyDir($folder);
                    $dest_file = $folder . '/' . basename($source_file);
                    $dest_file = file_getUniName(path($dest_file));
                    //����������
                    rename($source_file, $dest_file);
                    /*
                         copy($source_file, $dest_file);
                         unlink($source_file);
                         */
                    $fld[$k] = substr($dest_file, 2);
                } elseif ($fld[$k] == FILE_NO_SELECT_STR) {
                    $fld[$k] = "";
                }
            }
        }
    }
}

?>