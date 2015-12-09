<?
class TForm
{
	var $form_name='form';
	var $data_var='fld';
	var $req='<font color=red>*</font>';
	var $method='post';
	var $action='/';
	var $class = 'form';
	var $elements=array();
	var $help = '';
	var $show_res = '';

	function TForm($params){
		if(!is_array($params)) return;
		if(isset($params['name'])) $this->form_name = $params['name'];
		if(isset($params['method'])) $this->method = $params['method'];
		if(isset($params['action'])) $this->action = $params['action'];
		if(isset($params['data_var'])) $this->data_var = $params['data_var'];
		if(isset($params['req'])) $this->req = $params['req'];
		if(isset($params['class'])) $this->class = $params['class'];
		if(isset($params['elements'])) $this->elements = $params['elements'];
	}

	function generate(){
		$input_elements = array('file', 'hidden', 'text', 'password', 'button', 'submit', 'reset', 'image');
		if (!$this->elements) return;

		if ($this->method=='get'){
			if(isset($_GET[$this->data_var]) && count($_GET[$this->data_var]))
				$post=&$_GET[$this->data_var];
		} else {
			if(isset($_POST[$this->data_var]) && count($_POST[$this->data_var]))
				$post = &$_POST[$this->data_var];
			else{
				$_post = get('post', array(), 's');
				if(isset($_post[$this->data_var]) && count($_post[$this->data_var]))
					$post = &$_post[$this->data_var];
			}
		}
		foreach ($this->elements as $ekey=>$element){

			if (isset($element['group'])) $group=$element['group'];
			else $group='elems';

			// системные поля называем без приставки fld
			if($group=='system') $name=$element['name'];
			else $name=$this->data_var.'['.$element['name'].']';

			$post_value = isset($post[$element['name']])?$post[$element['name']]:null;

			if (isset($post_value)) if (!is_array($post_value)) $post_value=strip_tags($post_value);
			// делаем встроеные проверки
			if ($post_value && isset($element['check']))
				if($er=$this->check($element['name'], $element['check']))
					$res['form']['errors']['error'][] = $er;
			if (isset($post) && !empty($element['req']) && $element['req']=='1' && (!$post_value || ($element['type']=='select') && empty($post_value[0])))
				$res['form']['errors']['empty'][] = isset($element['text']) ? $element['text'] : '{#'.$this->form_name.'_fld_'.$element['name'].'#}';
			if (in_array($element['type'], $input_elements)){
				if (isset($element['dontreturn']) || $element['type'] == 'hidden')
					$value = $element['value'];
				else
					$value = empty($post_value) ? (isset($element['value']) ? $element['value'] : $post_value) : $post_value;

				if ((int)$ekey === $ekey){
					if (isset($res[$group])) $num = count($res[$group]); else $num=0;
				} else $num = $ekey;
				$res[$group][$num] = array(
				'title' => $element['type']=='hidden'?'':'{#'.$this->form_name.'_fld_'.$element['name'].'#}',
				'html' => "<input type='".$element['type']."' name=".$name.' value="'.htmlspecialchars($value).'" '.(isset($element['atrib'])?$element['atrib']:'').">",
				'value' => $value,
				'name' => $element['name'],
				'type' => $element['type'],
				'atrib' => isset($element['atrib']) ? $element['atrib'] : "",
				);
				if (isset($element['text']))
					$res[$group][$num]['text'] = $element['text'];
				if(isset($element['group']) && $element['group']!='system' && !isset($element['dontreturn'])) $res['form']['data'][$element['name']] = $value;
				$res[$group][$num]['req'] = null; // чтобы не было Notice
				if (isset($element['req']))
				if ($element['req']==1) $res[$group][$num]['req']=$this->req;
			}
			elseif ($element['type'] == 'textarea'){
				if (!$post_value)
					$value = (isset($element['value']) ? $element['value'] : '');
				else{
					if (!$element['dontreturn']) $value=$post_value;
					else $value='';
				}
				if ((int)$ekey === $ekey){
					if (isset($res[$group])) $num=count($res[$group]); else $num=0;
				} else $num = $ekey;
				$res[$group][$num]=array(
					'title' => '{#'.$this->form_name.'_fld_'.$element['name'].'#}',
					'html' => '<textarea  name='.$name.' '.(isset($element['atrib']) ? $element['atrib'] : "").'>'.$value.'</textarea>',
					'value' => $value,
					'name' => $element['name'],
                    'atrib' => isset($element['atrib']) ? $element['atrib'] : "",
				);
				if (isset($element['text']))
					$res[$group][$num]['text'] = $element['text'];
				if(isset($element['group']) && $element['group']!='system' && !isset($element['dontreturn'])) $res['form']['data'][$element['name']] = $value;
				if ($element['req']==1) $res[$group][$num]['req']=$this->req;
			}
			elseif ($element['type']=='radio' || $element['type']=='checkbox'){
				$html = '';
				$name.='[]';
				if (!$element['options'])
					for($i=1;$i<$element['count']+1;$i++)
					{
						if ($post_value)
							if (in_array($i, $post_value)) {
								$selected='checked';
								$value = "{#".$this->form_name."_fld_".$element['name'].$i."#}";
							}else $selected='';
						$html .= "<input name='".$name."' ".(isset($element['atrib'])?$element['atrib']:'')." type='radio' value='".$i."' ".$selected.">$ovalue<br />";
					}
				else
					foreach ($element['options'] as $okey=>$ovalue)
					{
						if ($post_value){
							if (in_array($okey, $post_value)) {
								$selected='checked';
								$value = $okey;
								$key = $ovalue;
							}else $selected='';
						}elseif (isset($element['value']) && $okey==$element['value']){
								$selected='checked';
								$value = $element['value'];
								$key = $okey;
						}else $selected='';

						$html .= "<input name='".$name."' ".(isset($element['atrib'])?$element['atrib']:'')." type='".$element['type']."' value='".$okey."' ".$selected."> $ovalue<br />";
					}
				if ((int)$ekey===$ekey){
					if (isset($res[$group]))  $num=count($res[$group]); else $num=0;
				}else $num = $ekey;
				$res[$group][$num]=array(
					'title'=>'{#'.$this->form_name.'_fld_'.$element['name'].'#}',
					'value'=>$value,
					'key'=>isset($key)?$key:'',
					'name'=>$element['name'],
					'html'=>$html
				);
				if (isset($element['text']))
					$res[$group][$num]['text'] = $element['text'];
				if($element['group']!='system' && !isset($element['dontreturn'])) $res['form']['data'][$element['name']] = $value;
				if ($element['req']==1) $res[$group][$num]['req']=$this->req;
			}
			elseif ($element['type']=='select'){
				$name.='[]';
				$html = "<select name='".$name."' ".(isset($element['atrib'])?$element['atrib']:'').">";
				if (!$element['options'])
					for($i=1;$i<$element['count']+1;$i++)
					{
						if ($post_value)
							if (in_array($i, $post_value)) {
								$selected='selected';
								$value = "{#".$this->form_name."_fld_".$element['name'].$i."#}";
							}else $selected='';
						$html .= "<option value='".$i."' ".$selected.">{#".$this->form_name."_fld_".$element['name'].$i."#}</option>";
					}
				else
					foreach ($element['options'] as $okey=>$ovalue)
					{
						if ($post_value){
							if (in_array($okey, $post_value)) {
								$selected='selected';
								$value = $okey;
								$key = $ovalue;
							}else $selected='';
						}elseif ($okey==$element['value']){
								$selected='selected';
								$value = $element['value'];
								$key = $okey;
						}else $selected='';
						$html .= "<option name='".$okey."' value='".$okey."' ".(isset($selected)?$selected:'').">".$ovalue."</option>";
					}
				$html .= "</select>";

				if ((int)$ekey===$ekey){
					if (isset($res[$group]))  $num=count($res[$group]); else $num=0;
				}else $num = $ekey;
				$res[$group][$num]=array(
					'title'=>'{#'.$this->form_name.'_fld_'.$element['name'].'#}',
					'value'=>$value,
					'key'=>isset($key)?$key:'',
					'name'=>$element['name'],
					'html'=>$html
				);
				if (isset($element['text']))
					$res[$group][$num]['text'] = $element['text'];
				if(isset($element['group']) && $element['group']!='system' && !isset($element['dontreturn'])) $res['form']['data'][$element['name']] = $value;
				if ($element['req']==1) $res[$group][$num]['req']=$this->req;
			}
			elseif ($element['type'] == 'html'){
					if ((int)$ekey === $ekey){
						if (isset($res[$group])) $num = count($res[$group]); else $num=0;
					} else $num = $ekey;				$res[$group][$num]=array(
					'type' => $element['type'],
					'html' => $element['value'],
				);
			}
			elseif ($element['type'] == 'calendar'){
				if (!$post_value)
					$value = (isset($element['value']) ? $element['value'] : '');
				else{
					if (!$element['dontreturn']) $value=$post_value;
					else $value='';
				}
				if ((int)$ekey === $ekey){
					if (isset($res[$group])) $num = count($res[$group]); else $num=0;
				} else $num = $ekey;
				$html = '<div style="width: 100%; text-align: left"><input '.$element['atrib'].' type="text" name="fld['.$element['name'].']" value="'.$value.'">&nbsp;<a href="javascript:void(0)" onclick="if (self.gfPop) gfPop.fPopCalendar(document.forms[\''.$this->form_name.'\'].elements[\'fld['.$element['name'].']\']);return false;" HIDEFOCUS><img name="popcal" align="absmiddle" src="admin/third/calendar/images/calbtn.gif" width="34" height="22" border="0" alt=""></a></div>';
				$res[$group][$num]=array(
					'type' => $element['type'],
					'name' => $element['name'],
					'value'=> $value,
					'html' => $html,	
				);
			}
			elseif ($element['type'] == 'upload_files'){
				if (!$post_value)
					$value = (isset($element['value']) ? $element['value'] : '');
				else{
					if (!$element['dontreturn']) $value=$post_value;
					else $value='';
				}
				$comment = $_REQUEST[$element['name'].'_comment'];
				if ((int)$ekey === $ekey){
					if (isset($res[$group])) $num = count($res[$group]); else $num=0;
				} else $num = $ekey;
				if (isset($element['files'])) {
					$html = '<div id="'.$element['name'].'_files_table" style="font-size: 90%; margin-bottom: 10px">';
					if (!empty($element['files'])) {
						$html .= '<div class="tenders_list" style="margin-bottom: 10px">
						<table class="sublevel" id="'.$element['name'].'_tender_files" style="width: 500px; margin: 0"><tr>
						<th nowrap>Название</th>
						<th nowrap>Размер</th>
						<th nowrap>Дата</th>
						<th>Комментарий</th>
						<th>Удалить</th></tr>'; 
						foreach ($element['files'] as $k=>$file) {
							$html .= '<tr class="'.($k%2 == 0 ? 'lite' : 'dark').'">
							<td>'.$file['name'].'</td>
							<td>'.$file['size']['size'].' ('.$file['size']['unit'].')</td>
							<td>'.$file['date'].'</td>
							<td>'.($file['comment'] ? $file['comment'] : '&nbsp;').'</td>
							<td><a href="'.$file['link'].'" target="upload_frame" onclick="if (confirm(\'Вы уверены, что хотите удалить файл '.$file['name'].'?\')) return true; else return false;"><img src="/admin/images/icons/icon.delete.gif" alt="Удалить"></a></td></tr>';
						}
						$html .= '</table></div>';
					}
					$html .= '</div>';
					$html .= '<div style="width: 100%; text-align: left; margin-bottom: 30px">
					<input '.$element['atrib'].' type="file" name="fld['.$element['name'].']" id="fld['.$element['name'].']" value="'.$value.'">
					<textarea name="fld['.$element['name'].'_comment]" id="fld['.$element['name'].'_comment]" class="registration" style="width: 500px; height: 50px; margin: 0">'.$comment.'</textarea>
					<input type="button" class="registration" style="width: 102px" value="Прикрепить" onClick="if (this.form.elements[\'fld['.$element['name'].']\'].value != \'\') filesend(\''.$element['name'].'\')">
					<div id="'.$element['name'].'_console" style="font-size: 90%;"></div>					
					</div>';
				}
				$html .= '';
				$res[$group][$num]=array(
					'text' => $element['text'],
					'type' => $element['type'],
					'name' => $element['name'],
					'value'=> $value,
					'html' => $html,	
				);
			}
			elseif ($element['type'] == 'captcha'){
				if ((int)$ekey === $ekey){
					if (isset($res[$group])) $num = count($res[$group]); else $num=0;
				} else $num = $ekey;
				$html = '<table width="100%">
				<tr>
				    <td width="100%" valign="top" style="padding: 3px"><input type="text" class="textInput" value="'.$post_value.'" name="fld['.$element['name'].']" style="width: 100%"/><br /><img src="/images/sp.gif" width="70" height="1" style="border: none"/></td>
				    <td valign="top" style="padding: 3px"><img src="?a=captcha" title="Щелкните на картинке, чтобы загрузить другой код" class="kaptcha" onClick="document.getElementById(\'captcha\').src=\'?a=captcha&\'+1000*Math.random()" id="captcha" align="right"></td>
				</tr>
				</table>
				    ';
				$res[$group][$num]=array(
					'title'=>$element['text'],
					'html'=>$html,
				);				
				if (isset($element['text'])) $res[$group][$num]['text'] = $element['text'];
				if ($element['req']==1) $res[$group][$num]['req']=$this->req;
			}			
		}

		$res['form']['name'] = $this->form_name;
		$res['form']['method'] = $this->method;
		$res['form']['action'] = $this->action;
		$res['form']['help'] = $this->help?$this->help:$this->form_name;
		$res['form']['show_result'] = $this->show_res?true:false;
		
		if(!isset($res['form']['result'])) $res['form']['result'] = null;

		// формируем "чистые данные"
		return $res;
	}
	
	function check($name, $method){
		$data = array();
		if (!isset($_REQUEST[$this->data_var][$name])) {
			$post = get('post', array(), 's');
			if (empty($post[$this->data_var])) return '{#unknown_error#}';
			$data = $post[$this->data_var];
		} else $data = $_REQUEST[$this->data_var];
		
		if($method[0] == '='){
			$_name = substr($method, 1);
			if($data[$name] !== $data[$_name])
				return isset($this->elements[$name]['onerror']) ? $this->elements[$name]['onerror'] : '{#unknown_error#}';
		} elseif($method == 'email'){
			if (!CheckMailAddress($data[$name]))
				return isset($this->elements[$name]['onerror']) ? $this->elements[$name]['onerror'] : '{#unknown_error#}';
		} elseif($method == 'phone'){
			if (!CheckPhone($data[$name]))
				return isset($this->elements[$name]['onerror']) ? $this->elements[$name]['onerror'] : '{#unknown_error#}';
		} elseif($method == 'zip'){
			if (!$this->CheckNumber($data[$name],6))
				return isset($this->elements[$name]['onerror']) ? $this->elements[$name]['onerror'] : '{#unknown_error#}';
		} elseif($method == 'bik'){
			if (!$this->CheckNumber($data[$name],9))
				return isset($this->elements[$name]['onerror']) ? $this->elements[$name]['onerror'] : '{#unknown_error#}';
		} elseif($method == 'ks'){
			if (!$this->CheckNumber($data[$name],20))
				return isset($this->elements[$name]['onerror']) ? $this->elements[$name]['onerror'] : '{#unknown_error#}';
		} elseif($method == 'inn'){
			if (!$this->CheckNumber($data[$name],10))
				return isset($this->elements[$name]['onerror']) ? $this->elements[$name]['onerror'] : '{#unknown_error#}';
        } elseif($method == 'kpp'){
            if (!$this->CheckNumber($data[$name],9))
                return isset($this->elements[$name]['onerror']) ? $this->elements[$name]['onerror'] : '{#unknown_error#}';
		} elseif($method == 'captcha') {
			$keystring = $_SESSION['captcha_keystring'];
			unset($_SESSION['captcha_keystring']);
			if ($data[$name] && (empty($keystring) || $data[$name] !== $keystring)) {
				return '{#captcha_error#}';
			}
		}
	}

	function CheckNumber($value,$len){
		if (!is_numeric($value)) return false;
		if (strlen($value) != $len) return false;
		return true;
	}	

	function elem($params, &$smarty){
		extract($params);
/*		switch($var['type']){
		case 'submit':
		return '<input type=submit name="'.$var['name'].'" value="'..'">';
		break;
		case 'hidden':
		break;
		case 'button':
		break;
		case 'button':
		break;
		}*/
//		return  AAA;
	}
}
?>