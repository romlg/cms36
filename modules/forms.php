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

	function TForm($params, &$smarty){
		if(!is_array($params)) return;
		if(isset($params['name'])) $this->form_name = $params['name'];
		if(isset($params['method'])) $this->method = $params['method'];
		if(isset($params['action'])) $this->action = $params['action'];
		if(isset($params['data_var'])) $this->data_var = $params['data_var'];
		if(isset($params['req'])) $this->req = $params['req'];
		if(isset($params['class'])) $this->class = $params['class'];
		if(isset($params['elements'])) $this->elements = $params['elements'];
//		$smarty->register_object('form', $this);
	}

	function generate(){
		$input_elements = array('checkbox', 'file', 'hidden', 'text', 'password', 'button', 'submit', 'reset');
		if (!$this->elements) return;

		if ($this->method=='get'){
			if(isset($_GET[$this->data_var]) && count($_GET[$this->data_var]))
				$post=&$_GET[$this->data_var];
		} else {
			if(isset($_POST[$this->data_var]) && count($_POST[$this->data_var]))
				$post = &$_POST[$this->data_var];
			else{
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
				if ($element['type']=='checkbox' || $element['type']=='radio'){
					$name.='[]';
					if ($res[$group]) $num=count($res[$group]); else $num=0;
					for($i=1;$i<$element['count']+1;$i++){
						if ($post_value)
							if (in_array($i, $post_value)) $checked='checked'; else $checked='';
						$res[$group][$num]['items'][$i]=array(
							'title'=>'{#'.$this->form_name.'_fld_'.$element['name'].$i.'#}',
							'html'=>"<input type='".$element['type']."' name=".$name." value='".$i."' ".$checked." ".(isset($element['atrib']) ? $element['atrib'] : "").">",
							'value'=>$checked,
							'name' => $element['name'],
                            'atrib' => isset($element['atrib']) ? $element['atrib'] : "",
						);
						if (isset($element['text']))
							$res[$group][$num]['items'][$i]['text'] = $element['text'];
					}
					if ($element['req'] == 1) $res[$group][$num]['req'] = $this->req;
				}
				else{
					if (isset($element['dontreturn']) || $element['type'] == 'hidden')
						$value = $element['value'];
					else
						$value = empty($post_value) ? (isset($element['value']) ? $element['value'] : $post_value) : $post_value;

					if ((int)$ekey === $ekey){
						if (isset($res[$group])) $num = count($res[$group]); else $num=0;
					} else $num = $ekey;
					$res[$group][$num] = array(
						'title' => $element['type']=='hidden'?'':'{#'.$this->form_name.'_fld_'.$element['name'].'#}',
						'html' => "<input type='".$element['type']."' name=".$name.' value="'.h($value).'" '.(isset($element['atrib'])?$element['atrib']:'').">",
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
			elseif ($element['type']=='radio'){
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
						}elseif ($okey==$element['value']){
								$selected='checked';
								$value = $element['value'];
								$key = $okey;
						}else $selected='';

						$html .= "<input name='".$name."' ".(isset($element['atrib'])?$element['atrib']:'')." type='radio' value='".$okey."' ".$selected.">$ovalue<br />";
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
							if (in_array($ovalue, $post_value)) {
								$selected='selected';
								$value = $ovalue;
								$key = $okey;
							}else $selected='';
						}elseif ($ovalue==$element['value']){
								$selected='selected';
								$value = $element['value'];
								$key = $okey;
						}else $selected='';
						$html .= "<option value='".$ovalue."' ".(isset($selected)?$selected:'').">".$ovalue."</option>";
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
					} else $num = $ekey;
					$res[$group][$num]=array(
					'type' => $element['type'],
					'html' => $element['value'],
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
			if (!$this->CheckPhone($data[$name]))
				return isset($this->elements[$name]['onerror']) ? $this->elements[$name]['onerror'] : '{#unknown_error#}';
		} elseif($method == 'zip'){
            $len = ereg("[0-9]+",$data[$name],$regs);
            if (!$regs) return isset($this->elements[$name]['onerror']) ? $this->elements[$name]['onerror'] : '{#unknown_error#}';
            if (!$this->CheckNumber($regs[0],6))
				return isset($this->elements[$name]['onerror']) ? $this->elements[$name]['onerror'] : '{#unknown_error#}';
		} elseif($method == 'bik'){
			if (!$this->CheckNumber(str_replace(" ","",$data[$name]),9))
				return isset($this->elements[$name]['onerror']) ? $this->elements[$name]['onerror'] : '{#unknown_error#}';
		} elseif($method == 'ks'){
			if (!$this->CheckNumber(str_replace(" ","",$data[$name]),20))
				return isset($this->elements[$name]['onerror']) ? $this->elements[$name]['onerror'] : '{#unknown_error#}';
		} elseif($method == 'inn'){
			if (!$this->CheckNumber(str_replace(" ","",$data[$name]),10) && !$this->CheckNumber(trim($data[$name]),12))
				return isset($this->elements[$name]['onerror']) ? $this->elements[$name]['onerror'] : '{#unknown_error#}';
        } elseif($method == 'kpp'){
            if (!$this->CheckNumber(str_replace(" ","",$data[$name]),9))
                return isset($this->elements[$name]['onerror']) ? $this->elements[$name]['onerror'] : '{#unknown_error#}';
        } elseif($method == 'captcha') {
			$keystring = $_SESSION['captcha_keystring'];
			unset($_SESSION['captcha_keystring']);
			if ($data[$name] && (empty($keystring) || $data[$name] !== $keystring)) {
				return '{#captcha_error#}';
			}
        }
	}

    function checkPhone($phone){
        return preg_match("/^[0-9\-\—\(\)\+\s]+$/i", $phone);
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