<?php

require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TFormElement extends TElems{

	var $multi_types = array('select', 'radio', 'checkbox');
	
    function ElemInit() {
        $this->elem_str = array(
			'name'		=>	array('Название формы',				'Title'),
			'db_table'	=>	array('Название таблицы в БД',		'DB table name'),
			'email'		=>	array('Отправлять на адреса (через запятую)',
								'Send to (comma-separated)'),
			'visible'	=>	array('Отображать на странице',		'Visible',),
		);
		return parent::ElemInit();
    }

	function getWCfromDb($id) {
		$row = $this->GetRow('SELECT *, '.$this->getFieldName('name', true).', '.$this->getFieldName('email', true).' FROM elem_form WHERE pid='.$id);
		if ($row['form_id']) {
			$row['elems'] = sql_getRows('SELECT *, '.$this->getFieldName('text', true).' FROM elem_form_elems WHERE pid='.$row['form_id']);
			foreach ($row['elems'] as $k=>$v){
				$row['elems'][$k]['value'] = "'".implode("','", sql_getRows('SELECT '.$this->getFieldName('text', true).' FROM elem_form_values WHERE pid='.$v['id']))."'";
			}
		}
		if (empty($row['elems'])) {
			unset($row['form_id']);
			unset($row['elems']);
		}
		return $row;
	}

	function ElemForm() {
		$id = (int)get('id', 0);
		if ($id) {
			$row = $this->getObject();
		}

		$row['types'] = array(
			'input'		=> 'Input',
			'textarea'	=> 'Textarea',
			'checkbox'	=> 'Checkbox',
			'radio'		=> 'Radio',
			'select'	=> 'Select',
			'file'		=> 'File',
			'captcha'	=> 'Captcha',
		);
		$row['check'] = array(
			'0'			=> ' нет ',
			'email'		=> 'E-mail',
			'phone'		=> 'Телефон',
			'zip'		=> 'Индекс',
			'captcha'	=> 'Captcha',
		);
		$row['req'] = array(
			'0'			=> 'нет',
			'1'			=> 'да',
		);
		$row['show'] = array(
			'0'			=> 'нет',
			'1'			=> 'да',
		);
		
		// добавляет в шаблон дефолтные строковые константы
		$this->AddStrings($row);

		return $this->Parse($row, 'elem_form.tmpl');
	}

	########################

	function ElemEdit($id, $row) {
		global $lang;
		$pid	=	$id;							// ID страницы
		$id		=	$row['form_id'];				// ID формы

		$error = '';
		sql_query('BEGIN');
		if (!$id){
			//добавляем форму
			if(sql_query('INSERT INTO elem_form(pid, '.$this->getFieldName('name').', '.$this->getFieldName('email').', db_table, visible) VALUES ("'.$pid.'","'.str_replace('"', '&quot;', $row['name']).'","'.$row['email'].'","'.$row['db_table'].'","'.(isset($row['visible']) ? $row['visible'] : 0).'")') === true){
				$form_id = sql_getLastId();
				foreach ($row['select'] as $k=>$v){
					if (sql_query('INSERT INTO elem_form_elems(pid, `key`, type, '.$this->getFieldName('text').', `check`, req, `show`, db_field) VALUES ("'.$form_id.'","'.$k.'","'.$v.'","'.$row['text'][$k].'", "'.$row['check'][$k].'", "'.$row['req'][$k].'", "'.$row['show'][$k].'", "'.$row['db_field'][$k].'")') === true){
						$epid = sql_getLastId();
						if ($this->isMulti($v)){
							//смотрим и заполняем массив значений
							if (!empty($row['textarea'][$k])){
								$arr = array();							
                                // Заменяем последовательность ',любой символ' на ','
                                $row['textarea'][$k] = ereg_replace("', +'", "','", $row['textarea'][$k]);
                                // Теперь разбиваем
                                $arr = explode("','", $row['textarea']);
								$arr[0] = substr($arr[0], 1);
								$arr[count($arr)-1] = substr($arr[count($arr)-1], 0, -1);
								foreach ($arr as $value2=>$text2){
									$arr[$value2] = '('.$epid.','.$value2.',"'.str_replace('"', '&quot;', $text2).'")';
								}
								if (sql_query('INSERT INTO elem_form_values(pid, value, '.$this->getFieldName('text').') VALUES '.implode(',',$arr)) !== true ){
									$error = sql_getError(); break;
								}
							}					
						}
					} else { $error = sql_getError();break;}
				}
			} else { $error = sql_getError();}
		} else {
			// редактируем форму
			$sql = 'UPDATE elem_form SET '.$this->getFieldName('name').'="'.str_replace('"', '&quot;', $row['name']).'", '.$this->getFieldName('email').'="'.$row['email'].'", db_table="'.$row['db_table'].'", visible="'.(isset($row['visible']) ? $row['visible'] : 0).'" WHERE form_id='.$id;
			sql_query($sql);
			$error = sql_getError();
			if (!$error){
			    foreach ($row['select'] as $k=>$v){
			        // Ищем, если ли такая строчка
					$sql = 'SELECT * FROM elem_form_elems WHERE `pid`='.$id.' AND `key`='.$k.' AND `type`="'.$v.'"';
					$_row = sql_getRow($sql);
			        if ($_row) {
			            $sql = 'UPDATE elem_form_elems SET 
			            '.$this->getFieldName('text').'="'.str_replace('"', '&quot;', $row['text'][$k]).'", 
			            `check`="'.$row['check'][$k].'",
			            `req`="'.$row['req'][$k].'",
			            `show`="'.$row['show'][$k].'",
			            `db_field`="'.$row['db_field'][$k].'"
			            WHERE id='.$_row['id'];
    			        sql_query($sql);
                        $epid = $_row['id'];
			        } else {
			            $sql = 'INSERT INTO elem_form_elems(pid, `key`, type, '.$this->getFieldName('text').', `check`, req, `show`, db_field) VALUES ("'.$id.'","'.$k.'","'.$v.'","'.str_replace('"', '&quot;', $row['text'][$k]).'", "'.$row['check'][$k].'", "'.$row['req'][$k].'", "'.$row['show'][$k].'", "'.$row['db_field'][$k].'")';
    			        sql_query($sql);
                        $epid = sql_getLastId();
			        }
			        if (!$epid) break;
			        
			        if ($this->isMulti($v)){
			            //смотрим и заполняем массив значений
			            if (!empty($row['textarea'][$k])){
			                $arr = array();
			                // Заменяем последовательность ',любой символ' на ','
			                $row['textarea'][$k] = ereg_replace("', +'", "','", $row['textarea'][$k]);
			                // Теперь разбиваем
			                $arr = explode("','", $row['textarea'][$k]);
			                $arr[0] = substr($arr[0], 1);
			                $arr[count($arr)-1] = substr($arr[count($arr)-1], 0, -1);
			                
			                foreach ($arr as $value2=>$text2){
			                    $text2 = str_replace('"', '&quot;', $text2);
    			                $sql = 'SELECT * FROM elem_form_values WHERE pid='.$epid.' AND value='.$value2;
    			                $__row = sql_getRow($sql);
    			                if ($__row) {
    			                    $sql = 'UPDATE elem_form_values SET '.$this->getFieldName('text').'="'.$text2.'" WHERE id='.$__row['id'];
    			                } else {
    			                    $sql = 'INSERT INTO elem_form_values(pid, value, '.$this->getFieldName('text').') VALUES ('.$epid.','.$value2.',"'.$text2.'")';
    			                }
    			                sql_query($sql);
    			                $error = sql_getError();
    			                if ($error) {
    			                    break 2;
    			                }
			                }
			            }
			        }
			    }
			    
				// Удалим старые данные из базы
				foreach ($row['elems'] AS $key=>$value){
					if ($row['select'][$value['key']] != $value['type']){
						sql_query("DELETE FROM `elem_form_values` WHERE pid=".$value['id']);
						sql_query("DELETE FROM `elem_form_elems` WHERE id=".$value['id']);
					}
				}
			}
		}
		
		$script = 'window.top.location.reload()';		
		if ($error){
			sql_query('ROLLBACK');	
			return $error;
		} else {
			sql_query('COMMIT');	
			return 1;
		}
	}
	
	function ElemRedactS($fld) {
		if (!isset($fld['visible'])) $fld['visible'] = 0;
		$fld['name'] = str_replace('"', '&quot;', $fld['name']);
		return $fld;
	}
	
	function isMulti($v) {
		return in_array($v, $this->multi_types);
	}
	
	function getFieldName($field, $select = false){
	    if (!defined('LANG_SELECT') || LANG_SELECT === false) return $field;
	    if (!$select) return '`'.$field.'_'.lang().'`';
	    return 'IF(`'.$field.'_'.lang().'`<>"", `'.$field.'_'.lang().'`, `'.$field.'_'.LANG_DEFAULT.'`) AS '.$field;
	}
}

?>