<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TMainElement extends TElems {

	######################
    var $elem_name  = "elem_main";          //название elema
	var $elem_table = "admin_groups";       //название таблицы elema (DEFAULT $elem_name)
	var $elem_type  = "single";
	var $elem_str = array(                  //строковые константы
            'name'      => array('Название', 'Name',),
            'priority'  => array('Приоритет', 'Priority',),
            'rights'    => array('Права доступа', 'Access Rights',),
            'saved'     => array('Группа была успешно сохранена', 'The group has been saved successfully',),
            'none'      => array('Нет', 'No',),
            'read'      => array('Чт', 'Rd',),
            'edit'      => array('Ред', 'Ed',),
            'delete'    => array('Уд', 'Del',),
            'deny_ids'  => array('Огран. разделы', 'The limited sections',),
            'module_title' => array('Название модуля', 'Module Title',),
	);

	//поля для выборки из базы элема
	var $elem_fields = array(
		'columns'		=> array(
			'id' => array(
				'type'		=> 'hidden',
			),
			'name' => array(
				'type'		=> 'text',
				'size'		=> 30,
				'maxlength' => 32,
			),
			'deny_ids' => array(
			    'type'      => 'input_treecheck',
			),
			'priority' => array(
				'type'		=> 'text',
				'size'		=> 30,
				'maxlength' => 32,
			),
			'rights' => array(
				'type'      => 'hidden',
			),
		),
		'id_field' => 'id',
	);
	var $elem_where="";
	var $elem_req_fields = array('name');
	var $script = "";
	//var $sql = true;

    ########################
    function ElemInit() {
		global $cfg, $sections, $hidden_sections, $_stat, $_sites, $intlang;

		$row = array();
		$modules_in_row = array();
		$i = 0;
		$id = (int)get('id');

		if ($id) {
			$row = sql_getRow("SELECT * FROM `admin_groups` WHERE id='".$id."'");
			$row['rights'] = unserialize($row['rights']);
		}
		// ALLOW ->	DEL		INS		UPD		SELECT
		// none  ->	0		0		0		0			=  0
		// view  ->	0		0		0		1           =  1
		// edit	 ->	0		1		1		1           =  7
		// del	 ->	1		1		1		1           = 15
		$row['radios'] = array('0'=>'','1'=>'','7'=>'','15'=>'');

		// если указаны скрытые модули - надо их также вывести
		// для возможности задания прав группам пользователей.
		if(isset($hidden_sections)) {
			$sections = array_merge($sections, $hidden_sections);
		}

		foreach ($sections as $key => $section) {
			$row['menu'][$i]['title'] = utf($section[langId()]);
			$row['menu'][$i]['i'] = $i;

			foreach ($section['modules'] as $module_key => $module) {
                if(count(explode("/",$module_key))>1) {
                    $arr = explode("/",$module_key);
                    $module = $arr[0];
                }

                if (!is_module_auth($module_key)) {
                    continue;
                }

                // set the title
                unset($title);
                $title = $module[langID()];

				if(!isset($title)) {
					switch ($module) {
						case 'stat' : $title = $_stat[$module_key][langID()]; break;
						case 'sites' : $title = $_sites[$module_key][langID()]; break;
					}
				}

				if(!in_array($module.'_'.$title, $modules_in_row)) {
					$row['menu'][$i]['rows'][] = array(
						'menu'		=> $i,
						'name'		=> 'fld[rights]['.$module_key.']',
						'title'		=> utf($title),
						'selected'  => !empty($row['rights'][$module_key]) ? $row['rights'][$module_key] : 0
					);
					$modules_in_row[] = $module.'_'.$title;
				}
			}
			$i++;
		}

        foreach ($this->elem_str AS $str_key=>$str_val) {
            $row['str_'.$str_key] = $str_val[$intlang];
        }
        $table = $this->Parse($row, 'admin_groups.editform.tmpl');

        $this->elem_fields['columns']['table'] = array(
            'type' => 'words',
            'value' => $table,
        );

        return parent::ElemInit();
    }

    ########################
	function ElemRedactBefore($fld) {
		global $user;

	    $rights = serialize($_POST['fld']['rights']);
        $fld['rights'] = $rights;

		# изменение сессии текущего пользователя (нафиг не нужно, просто на всякий случай)
		if ($user['group_id'] == $id) {
			session_start();
			$user['rights'] = $_SESSION['user']['rights'] = $fld['rights'];
			$user['deny_ids'] = $_SESSION['user']['deny_ids'] = $fld['deny_ids'];
			session_write_close();
		}
		return $fld;
	}

}
?>