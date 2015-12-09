<?php

/* $Id: discounts.php,v 1.1 2009-02-18 13:09:08 konovalova Exp $
 */

class Tdiscounts extends TTable {

	var $name = 'discounts';
	var $table = 'discounts';
	var $table2 = 'discounts_volume';
	########################

	function Tdiscounts() {
		global $str, $actions;

		TTable::TTable();

		$actions[$this->name] = array(
			'save' => array(
				'Сохранить изменения',
				'Save Changes',
				'link'		=> 'cnt.document.forms[this.formname].actions.value=\'editchanges\';cnt.document.forms[this.formname].submit();',
				'img' 		=> 'icon.save.gif',
				'display'	=> 'none',
			),
			'create' => &$actions['table']['create'],
			'delete' => array(
				'Удалить',
				'Delete',
				'link'	=> 'cnt.deleteItems()',
				'img' 	=> 'icon.delete.gif',
				'display'	=> 'none',
			),
		);

		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'	=> array(
				'Скидки',
				'Discounts',
			),
			'title_one'	=> array(
				'Скидка',
				'Discount',
			),
			'volume'	=> array(
				'Объём',
				'Volume',
			),
			'upvolume'	=> array(
				'Верхний предел',
				'upVolume',
			),
			'discount'	=> array(
				'Значение, %',
				'Value, %'
			),
			'product_group' => array(
				'Скидочные группы',
				'Discount groups'
			),
			'discount_group' => array(
				'Скидки для группы клиентов',
				'Discount for user group'
			),
			'saved' => array(
				'Скидка была сохранена',
				'The discount has been saved'
			),
			'discount_saved' => array(
				'Скидки сохранены',
				'Discounts has been saved'
			),
		));
	}
	

	########################
	function DelItem() {
		$id = get('id', 0, 'g');
		$res = sql_query('DELETE FROM '.$this->table2.' WHERE id='.$id);
			
		return "<script>alert('".$this->str('discount_saved')."');window.parent.location.reload();</script>";
	}

	########################
	
	function EditChanges() {
		$row = get('row', array(), 'p');
		$auth_group = get('auth_group', '', 'p');
		
		$type = sql_getValue('
			SELECT dt.type
			FROM auth_groups AS ag
			LEFT JOIN discount_types AS dt ON dt.id = ag.discount_type
			WHERE ag.id = '.$auth_group
		);
		
		if ($type!='volume'){
			foreach ($row as $key=>$val) {
				$res = sql_query('REPLACE INTO '.$this->table.' VALUES ('.$_POST['auth_group'].', '.$key.', "'.($val['discount']/100).'")');
			}
		}
		else {
			foreach ($row as $key=>$val) {
				$res = sql_query('UPDATE '.$this->table2.' SET `id`='.$key.', `auth_group_id`='.$_POST['auth_group'].', `discount` ="'.($val['discount']/100).'", `volume`='.$val['upvolume'].' WHERE id='.$key);
			}
		}
		return "<script>window.parent.modified(0);alert('".$this->str('discount_saved')."');window.parent.location.reload();</script>";
	}

	########################

	function table_get_edit(&$value, &$column, &$row) {
		$size = isset($column['size']) ? $column['size'] : '';
		$maxlength = isset($column['maxlength']) ? $column['maxlength'] : '';
		$text_align = isset($column['text-align']) ? $column['text-align'] : 'left';
		$mod = 2;
		if ($value<100){$value *= 100;$mod = 1;}
		return "<input onkeypress='modified(".$mod.")' onpaste='modified(".$mod.")' type=text name='row[{$row['id']}][{$column['display']}]' value='$value' size='$size' maxlength='$maxlength' style='text-align:$text_align'><input type=image src='images/s.gif' width=1 height=1>";
	}

	########################

	function Show(){
		if (!empty($GLOBALS['_POST'])) {
			$actions = get('actions', '', 'p');
			if ($actions) return $this->$actions();
		}
		require_once(core('ajax_table'));
		$ret['id'] = 0;
		$ret['auth_group'] = (int)get('auth_group');
		
		$ret['auth_groups'] = sql_getRows('select id, name from auth_groups order by priority, name', true);
		if (!$ret['auth_group']) $ret['auth_group'] = key($ret['auth_groups']);
		

		$ret['auth_group_type'] = sql_getValue('
			SELECT dt.type
			FROM auth_groups AS ag
			LEFT JOIN discount_types AS dt ON dt.id = ag.discount_type
			WHERE ag.id = '.$ret['auth_group']
		);

		if ($ret['auth_group_type'] == 'fix') {
			$ret['table'] = ajax_table(array(
				'columns'	=> array(
					array(
						'select'	=> 'dg.id',
						'type'		=> 'checkbox',
						//'flags'		=> FLAG_FILTER,
						//'filter_type'	=> 'array',
						//'filter_value'	=> $this->GetRows("SELECT id, name FROM discount_groups ORDER BY priority, name", true),
						//'filter_display'	=> 'discount_group',
						//'filter_str'	=> false,
					),
					array(
						'select'	=> 'dg.name',
						'display'	=> 'product_group',
					),
					array(
						'select'	=> 'discounts.discount',
						'display'	=> 'discount',
						'type'		=> 'edit',
						'align'		=> 'right',
						'text-align' => 'right',
						'maxlength'	=> 2,
						'size'		=> 2,
					),
					array(
						'select'	=> '""',
						'display'	=> '',
						'width'		=> '50%',
					),
				),
				'from' => 'discount_groups AS dg 
				LEFT OUTER JOIN discounts ON discounts.discount_group_id = dg.id AND discounts.user_discount_group_id='.$ret['auth_group'],				
				'orderby' => 'priority, name',
				'params' => array('page' => $this->name, 'do' => 'show', 'id' => '', 'auth_group' =>  $ret['auth_group']),
				'click'	=> 'ID=cb.value;',
			), $this);
		}
		else {
			$ret['table'] = ajax_table(array(				
				'columns'	=> array(
					array(
						'select'	=> 'id',
						'type'		=> 'checkbox',
						'display'   => 'id',
						//'flags'		=> FLAG_FILTER,
						//'filter_type'	=> 'array',
						//'filter_value'	=> $this->GetRows("SELECT id, name FROM discount_groups ORDER BY priority, name", true),
						//'filter_display'	=> 'discount_group',
						//'filter_str'	=> false,
					),
					array(
						'select'	=> 'volume',
						'display'	=> 'upvolume',
						'type'		=> 'edit',
						'align'		=> 'right',
						'text-align' => 'right',
						'maxlength'	=> 10,
						'size'		=> 10,
					),
					array(
						'select'	=> 'discount',
						'display'	=> 'discount',
						'type'		=> 'edit',
						'align'		=> 'right',
						'text-align' => 'right',
						'maxlength'	=> 2,
						'size'		=> 2,
					),
					array(
						'select'	=> '""',
						'display'	=> '',
						'width'		=> '50%',
					),
				),
				'from' => 'discounts_volume',
				'where'=>'auth_group_id='.$ret['auth_group'],
				'orderby' => 'volume',
				'params'	=> array('page' => $this->name, 'do' => 'show', 'id' => '', 'auth_group' =>  $ret['auth_group']),
				'click'	=> 'ID=cb.value;',
			  //'_sql'=>1,
			), $this);
		}
		$this->AddStrings($ret);
		return $this->Parse($ret, $this->name.'.tmpl');
	}

	######################

	function EditForm() {
		$row['auth_group'] = get('auth_group');
		// добавляет в шаблон дефолтные строковые константы
		$this->AddStrings($row);
		return $this->Parse($row, $this->name.'.editform.tmpl');
	}

	########################

	function Edit() {
		$temp = $this->table;
		$this->table = $this->table2;
		$_POST['fld']['discount'] = $_POST['fld']['discount']/100;
		$id = $this->Commit(array('volume','discount'));
		$this->table = $temp;
		$reload = !sql_getErrNo() ? 'window.parent.location.reload()' : '';
		if (!is_int($id)) {
			return $this->Error($id);
		}
		return '<script>alert(\''.$this->str('saved').'\'); '.$reload.'</script>';
	}

	########################
}

$GLOBALS['discounts'] =  & Registry::get('TDiscounts');

?>