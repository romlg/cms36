<?php

require_once (PATH_CONFIG . 'banners.cfg.php');

class TBanners extends TTable
{

    var $name = 'banners';
    var $table = 'banners';
    var $selector = true;

    ########################

    function TBanners() {
        global $actions, $str;

        TTable::TTable();

        $actions[$this->name] = array(
            'edit' => &$actions['table']['edit'],
            'create' => &$actions['table']['create'],
            'delete' => array(
                '�������',
                'Delete',
                'link' => 'cnt.deleteItems(\'' . $this->name . '\', \'\', -1)',
                'img' => 'icon.delete.gif',
                'display' => 'none',
            ),
            'clear' => array(
                '��������&nbsp;CTR',
                'Clear&nbsp;CTR',
                'link' => 'cnt.clearCTR()',
                'img' => 'icon.clear_ctr.gif',
                'display' => 'none',
                'show_title' => true,
            ),
            'recycle' => array(
                '�������',
                'Recycle Bin',
                'link' => 'cnt.showRecycle()',
                'img' => 'icon.trash.gif',
                'display' => 'block',
            ),
            'moveup' => array(
                '�����',
                'Move up',
                'link' => 'cnt.EditPriority(-1)',
                'img' => 'icon.moveup.gif',
                'display' => 'block',
            ),
            'movedown' => array(
                '����',
                'Move down',
                'link' => 'cnt.EditPriority(+1)',
                'img' => 'icon.movedown.gif',
                'display' => 'block',
            ),
        );

        $actions[$this->name . '.editform'] = array(
            'save' => array(
                '���������',
                'Save',
                'link' => 'cnt.SaveSubmit()',
                'img' => 'icon.save.gif',
                'display' => 'block',
                'show_title' => true,
            ),
            'apply' => array(
                '��������� ���������',
                'Apply',
                'link' => 'cnt.ApplySubmit()',
                'img' => 'icon.save.gif',
                'display' => 'block',
                'show_title' => true,
            ),
            'close' => &$actions['table']['close'],
        );

        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
            'title' => array('�������', 'Banners',),
            'add' => array('���������� ������ �������', 'Add new banner',),
            'edit' => array('�������������� �������', 'Edit banner',),
            'empty' => array('������ �� �������', 'Empty result set',),
##### Editor #####
            'caption' => array('�������� ����', 'Main Fields',),
            'name' => array('�������� �������', 'Banner\'s name',),
            'position' => array('������������ �� �����', 'Banner\'s position',),
            'type' => array('��� �������', 'Type of banner',),
            'img' => array('�������� ���������', 'Specify as image',),
            'html' => array('�������� ��� HTML', 'Specify as HTML',),
            'image' => array('�������� �������', 'Image of banner',),
            'alt_image' => array('�������������� ��������<br><small>(��� ����-��������)</small>',
                'Alternative image<br><small>(for flash-banner)</small>',),
            'preview' => array('�����������', 'Preview',),
            'bottom' => array('�����', 'Bottom',),
            'link' => array('������', 'Link',),
            'text' => array('����� ������� �� �������', 'Legend of banner',),
            'target' => array('��������� ������', 'Open link',),
            '_self' => array('� ��� �� ����', 'self window',),
            '_blank' => array('� ����� ����', 'new window',),
            'visible' => array('����������', 'Show banner',),
            'second_visible' => array('���������� �� ������ ���������', 'Show banner at second pages',),
            'pages' => array('������ �� ��������� ���������', 'only in the following pages',),
            'except' => array('�� ���� ��������� �� ����������� ���������', 'anywhere except the following pages',),
            'priority' => array('�������', 'Priority',),
            'banner_html' => array('HTML ��� �������', 'HTML code of banner',),
            'ctr' => array('CTR (����� / ������)', 'CTR (clicks / views)',),
            'views' => array('���������� �������', 'Views count',),
            'clicks' => array('���������� ������', 'Clicks count',),
            'lang' => array('������ �����', 'Language',),
            'show_at_sites' => array('���������� ����� �� ������', 'Also show at sites'),
            'no_file' => array('��� �����������', 'no preview',),
            'restore' => array('������������', 'Restore',),
            'delete' => array('�������', 'Delete',),
            'saved' => array('������ ������� ���������', 'The information has been saved successfully',),
            'ctr_cleared' => array('CTR �������', 'CTR has been cleared successfully',),
            'e_no_items' => array('��� ���������� ���������', 'No elements selected',),
            'e_no_title' => array('�� ��������� ���� "��� �������"', 'Empty name of banner',),
        ));

        // ������ ��������� ��������
        $this->position = &$GLOBALS['cfg']['banners_module']['positions'];

        // ������ � preview ��� �������
        $this->review_width = 100;

        // ��� swf �������
        $this->swf_code = '
<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=5,0,0,0" width="{width}" height="{height}" id="sel_guide_banner" align="middle">
<param name="allowScriptAccess" value="sameDomain" />
<param name="movie" value="{filename}" />
<param name="quality" value="high" />
<param name="scale" value="noscale" />
<param name="bgcolor" value="#ffffff" />
<embed src="{filename}" quality="high" scale="noscale" bgcolor="#ffffff" width="{width}" height="{height}" name="sel_guide_banner" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
</object>';
    }

    ########################

    function EditPriority() {
        $id = get('id', false, 'g');
        $move = get('move', false, 'g');
        $root_id = get('root_id', '100');
        // ������� ���������
        $priority = sql_getValue("select priority from " . $this->table . " where id = " . $id);
        // id ������� ������� ������������� ���������
        $banner_id = sql_getValue("select id from " . $this->table . " where priority = " . ($priority + $move) . " and root_id=" . $root_id);
        // �������� ��������� ��� �������� �������
        sql_query('update ' . $this->table . ' set priority = ' . ($priority + $move) . ' where id = ' . $id);
        // �������� ��������� ��� �������, ������� ����������������� ���������
        sql_query('update ' . $this->table . ' set priority = ' . ($priority) . ' where id = ' . $banner_id);
        // ������ ������
        $str = 'Id: ' . $id . ' Mode: ' . $move . ' Priority: ' . $priority . ' Banner_id: ' . $banner_id;
        touch_cache($this->table);
        return "<script>window.parent.location.reload();</script>";
    }


    function table_get_position(&$value, &$column, &$row) {
        return utf($this->position[$value]['display'][int_langId()]);
    }

    function table_get_image(&$value, &$column, &$row) {
        if (is_file(".." . $value)) {
            $ext = strtolower(get_file_ext($value));
            if ($ext == '.gif' || $ext == '.jpg' || $ext == '.png')
                return '<img src="' . $value . '" width="' . $this->review_width . '">';
            elseif ($ext == '.swf') {
                $size = getimagesize(".." . $value);
                return ' ' . str_replace(
                    array('{filename}', '{width}', '{height}'),
                    array(
                        $value,
                        $this->review_width,
                        round($size[1] * $this->review_width / $size[0])
                    ),
                    $this->swf_code
                );
            }
            else
                return '"' . strtoupper($ext) . '" File';
        }
        else
            return $this->str('no_file');
    }

    function table_get_link(&$value, &$column, &$row) {
        return "<a href='" . $value . "' target=_blank>" . $value . "</a>";
    }

    function table_get_ctr(&$value, &$column, &$row) {
        if ($row['views'] > 0) {
            $ctr = round($row['clicks'] * 100 / $row['views'], 2);
        }
        else {
            $ctr = 0;
        }
        return $row['clicks'] . ' / ' . $row['views'] . '<br>' . $ctr . '%';
    }

    ########################

    function Show() {
        if (!empty($_POST)) {
            $action = get('actions', '', 'p');
            if ($action) {
                if ($this->Allow($action)) {
                    return $this->$action();
                }
                else {
                    return $this->alert_method_not_allowed();
                }
            }
        }
        require_once(core('ajax_table'));

        $data['thisname'] = $this->name;

        $this->AddStrings($data);

        $options_pos = array();
        foreach ($this->position as $key => $val) {
            $options_pos[$key] = utf($val['display'][int_langID()]);
        }

        $data['table'] = ajax_table(array(
            'columns' => array(
                array(
                    'select' => 'id',
                    'display' => 'id',
                    'type' => 'checkbox',
                ),
                array(
                    'select' => 'name',
                    'display' => 'name',
                    'flags' => FLAG_SORT,
                ),
                array(
                    'select' => 'image',
                    'display' => 'preview',
                    'type' => 'image',
                ),
                array(
                    'select' => 'link',
                    'display' => 'link',
                    'type' => 'link',
                ),
                array(
                    'select' => 'position',
                    'display' => 'position',
                    'type' => 'position',
                    'flags' => FLAG_SORT | FLAG_FILTER,
                    'filter_type' => 'array',
                    'filter_value' => array('') + $options_pos,
                ),
                array(
                    'select' => 'visible',
                    'display' => 'visible',
                    'type' => 'visible',
                    'align' => 'center',
                    'flags' => FLAG_SORT,
                ),
                array(
                    'select' => 'priority',
                    'display' => 'priority',
                    'type' => 'text',
                    'align' => 'center',
                    'flags' => FLAG_SORT,
                ),
                array(
                    'select' => "clicks",
                ),
                array(
                    'select' => "views",
                    'display' => 'ctr',
                    'type' => 'ctr',
                    'align' => 'center',
                    'flags' => FLAG_SORT,
                ),
            ),
            'where' => "lang='" . lang() . "' AND visible>=0 AND root_id=" . domainRootID(),
            'orderby' => 'priority',
            'params' => array('page' => $this->name, 'do' => 'show'),
            'dblclick' => 'editItem(id)',
            'click' => 'ID = cb.value',
        ), $this);
        $data['root_id'] = domainRootID();
        return $this->Parse($data, $this->name . '.tmpl');
    }

    ########################

    function EditForm() {
        $id = (int)get('id', 0);
        if ($id) {
            $row = $this->getRow($id);
            $type = $row['html'] ? 'html' : 'img';
        }
        else {
            $row['id'] = $id;
            $row['visible'] = 1;
            $row['target'] = 1;
            $row['position'] = '';
            $row['root_id'] = get('root_id', '100');
            $columns = sql_getRows('SHOW COLUMNS FROM banners', true);
            if (isset($columns['alt_image'])) $row['alt_image'] = '';
            if (isset($columns['show_at_sites'])) $row['show_at_sites'] = '';
            $type = 'image';
        }

        $GLOBALS['title'] = $this->str($id ? 'edit' : 'add');

        $this->AddStrings($row);

        $row['visible_checked'] = $row['visible'] ? 'checked' : '';

        if (!empty($row['pages'])) {
            $row['pages_checked'] = 'checked';
        }

        if (isset($row['image']) && is_file(".." . $row['image'])) {
            $ext = strtolower(get_file_ext($row['image']));
            $size = getimagesize(".." . $row['image']);
            // ������ ��������
            if ($ext == '.gif' || $ext == '.jpg' || $ext == '.png') {
                $row['img_preview'] = '<img src="' . $row['image'] . '" ' . $size[3] . ' alt="" />';
            }
            // ������ ����
            elseif ($ext == '.swf') {
                $row['img_preview'] = str_replace(
                    array('{filename}', '{width}', '{height}'),
                    array(
                        $row['image'],
                        $size[0],
                        $size[1]
                    ),
                    $this->swf_code
                );
            }
        }
        if (isset($row['alt_image']) && is_file(".." . $row['alt_image'])) {
            $ext = strtolower(get_file_ext($row['alt_image']));
            $size = getimagesize(".." . $row['alt_image']);
            // ������ ��������
            if ($ext == '.gif' || $ext == '.jpg' || $ext == '.png') {
                $row['alt_img_preview'] = '<img src="' . $row['alt_image'] . '" ' . $size[3] . ' alt="" />';
            }
        }

        $row['options_target'] = $this->GetArrayOptions(array('_self', '_blank'), $row['target'], true, true);

        // ������ ��������� �������
        foreach ($this->position as $key => $val) {
            $options_pos[$key] = utf($val['display'][int_langId()]);
        }
        $row['options_pos'] = $this->GetArrayOptions($options_pos, $row['position'], true, false);

        // ������ ������
        if (isset($row['show_at_sites'])) {
            global $site_domains;
            $sites = array();
            $root = domainRootID();
            foreach ($site_domains as $key => $val) {
                foreach ($val['langs'] as $l) {
                    if ($l['root_id'] != $root) {
                        $sites[$l['root_id']] = (LANG_SELECT && !empty($val['descr_' . lang()]) ? $val['descr_' . lang()] : (!empty($val['descr']) ? $val['descr'] : $val['name'])) . ' (' . $l['descr'] . ')';
                    }
                }
            }
            $row['sites'] = $sites;
            if (!empty($row['show_at_sites'])) $row['show_at_sites'] = explode(",", $row['show_at_sites']);
        }

        // ������ ��������� ��� �������������� ��������
        $options_type = array('img', 'html');
        $row['options_type'] = $this->GetArrayOptions($options_type, $type, false, true);

        // ����� ��� HTML �������
        include_fckeditor();
        $oFCKeditor = new FCKeditor;
        $oFCKeditor->ToolbarSet = 'Common';
        $oFCKeditor->Value = isset($row['html']) ? $row['html'] : '';
        $row['html'] = $oFCKeditor->ReturnFCKeditor('fld[html]', '100%', '300px');

        return $this->Parse($row, $this->name . '.editform.tmpl');
    }

    ######################

    function Edit() {
        $id = (int)get('id', 0, 'p');
        $row = get('fld', array(), 'p');
        $apply = (int)get('apply', 0, 'p');

        $row['lang'] = lang();

        if (!$row['name']) {
            return "<script>alert('" . $this->str('error') . ": " . $this->str('e_no_title') . "');</script>";
        }
        $row['name'] = htmlspecialchars($row['name']);

        if ($_POST['type'] == 'img') {
            $row['html'] = '';
        }
        if (empty($_POST['pages'])) {
            $row['pages'] = '';
        }
        $pages = explode(',', $row['pages']);
        $pages = array_filter($pages);
        $row['pages'] = array_unique($pages);

        if (empty($_POST['except'])) {
            $row['except'] = '';
        }
        $except = explode(',', $row['except']);
        $except = array_filter($except);
        $row['except'] = array_unique($except);

        if (isset($row['show_at_sites']) && !empty($row['show_at_sites'])) {
            $row['show_at_sites'] = implode(",", $row['show_at_sites']);
        }
        else {
            $row['show_at_sites'] = '';
        }

        if ($_POST['type'] == 'html') {
            $row['html'] = str_replace(array('&lt;script', '/script&gt;'), array('<script', '/script>'), $row['html']);
        }

        if (!isset($row['visible'])) $row['visible'] = 0;

        if ($id) {
            sql_updateId($this->table, $row, $id, 'id', __FILE__, __LINE__);
        }
        else {
            $id = sql_insert($this->table, $row);
        }
        if (sql_getError()) {
            return "<script>alert('" . $this->str('error') . ": " . addslashes(sql_getError()) . "');</script>";
        }
        touch_cache($this->table);

        return "<script>alert('" . $this->str('saved') . "');" . ($apply ? 'window.top.location = window.top.location+"&id=' . $id . '" ;' : "window.top.close(); window.top.opener.location.reload();") . "</script>";
    }

    ######################

    function EditClearCTR() {
        $ids = get('id', array(), 'p');
        if (!$ids) {
            return "<script>alert('" . $this->str('e_no_items') . "');</script>";
        }

        $res = sql_query('UPDATE ' . $this->table . ' SET views=0, clicks=0 WHERE id IN (' . join(',', $ids) . ')');

        if (sql_getError()) {
            return $this->Error(sql_getError());
        }
        touch_cache($this->table);

        return "<script>alert('" . $this->str('ctr_cleared') . "');window.parent.location.reload();</script>";
    }

    ########################

    function ShowRecycle() {
        global $limit;

        $limit = -1;
        require_once(core('ajax_table'));

        $this->AddStrings($row);
        $row['table'] = ajax_table(array(
            'columns' => array(
                array(
                    'select' => 'id',
                    'display' => 'id',
                    'type' => 'checkbox',
                ),
                array(
                    'select' => "name",
                    'display' => 'name',
                ),
            ),
            'where' => 'lang="' . lang() . '" AND visible<0',
            'orderby' => 'id DESC',
            'params' => array('page' => $this->name, 'do' => 'Show'),
        ), $this);

        return Parse($row, 'recycle.tmpl');
    }

}

$GLOBALS['banners'] = &Registry::get('TBanners');

?>