<?php

class TVoting {

    function show_form(&$param){ // Форма на главной странице
        $page = &Registry::get('TPage');
        $limit = $page->tpl->get_config_vars("voting_limit");
        if (!$limit) $limit = 1;

        $vote_list = sql_getRows("SELECT * FROM voting
        WHERE lang='".$page->lang."' AND visible > 0 AND root_id=".ROOT_ID."
        ORDER BY priority");

        if (empty($vote_list)) return false;
        $i = 0;

        foreach ($vote_list as $key=>$val) {
            // Если у голосования стоит ipcheck=check и по нему уже голосовали, то убираем его списка
            if (($val['ipcheck'] == 'check' || $val['ipcheck'] == 'cookie') && !$this->check_vote($val)) {
            	unset($vote_list[$key]);
            }
            // Если голосование закрыто, то убираем его списка
            elseif ($val['open'] < 1) unset($vote_list[$key]);
            // Выбираем ответы
            else {
                if ($i >= $limit) break;
                $vote_list[$key]['answers'] = sql_getRows("SELECT * FROM voting_answers WHERE pid=".$val['id'],true);
                $i++;
            }
        }

        if (empty($vote_list)) { // Выводим ссылку на результаты голосования
            return array('show_type' => 'results_links');
        }
        return array('show_type' => 'form', 'vote_list' => $vote_list);
    }

	function show(&$params) {
        // Выбираем голосование, которое надо показать
        $current_voting = $this->printVote();
        $res['show_type'] = $current_voting['show_type'];

		if ($current_voting['votes_list']['row']) { /* Если есть другие голосования кроме текущего */
            $votes_list=$current_voting['votes_list'];
			unset($current_voting['votes_list']);
		}
		if ($current_voting['voting_result']['row']) { /* Отображаем результаты голосования */
            $id = $current_voting['voting_result']['id'];
            $res['answers'] = $current_voting['voting_result'];
			$list = $this->getList();
            if ($id) $list = $this->parse($list, $id); //Вырезаем текущее голосование из списка
			$res['votes_list']['row'] = $list['res'];
		} elseif ($current_voting['voting']) {  /* Отображаем форму для голосования */
            $id = $current_voting['voting']['id'];
			$res['form'] = $current_voting['voting'];
			$list = $votes_list['row'];
            if ($id)  $list = $this->parse($list, $id); //Вырезаем текущее голосование из списка
			$res['votes_list']['row'] = $list['res'];
		}
		if (!$res) return;
		return $res;
	}

	function printVote(){
		$result = get('result',NULL,'pg');
        $id = get('id',NULL,'g');

		$votes = $this->getList(); //Выбираем список всех голосований

        //Вырезаем текущее голосование из списка
        $temp = $this->parse($votes, $id);
		$data['votes_list']['row'] = $temp['res'];
        if (isset($temp['id'])) $id = $temp['id'];

        $info = $this->getVote($id, $votes); //Берем инфу по текущему голосованию

        $mode = $id?1:0;

        //Проверяем голосование
		if($this->check_vote($info) === true && !$result && $info['visible'] && $info['open']) {
            $data['show_type'] = "questions";
            $data['voting'] = $this->getQuestions($info);
        }
		else {
            $data['show_type'] = "answers";
			$data['voting_result'] = $this->getAnswers($info , $mode);
        }
        return $data;
	}


	function parse($res, $id) {
	    $i = 0;
        $ret_id = -1;
    	foreach($res as $k=>$v) {
            if ($id && $v['id']==$id) {
                $ret_id = $v['id'];
                unset($res[$k]);
    	        return array('res'=>$res,'id'=>$ret_id);
            }
    		elseif(!$id && $v['open']>0) {
                $ret_id = $v['id'];
                unset($res[$k]);
                return array('res'=>$res,'id'=>$ret_id);
            }
    	}
        unset($res[0]);
        return array('res'=>$res);
	}


	//Выбираем голосование
	function getVote($id, $res = '') {
    	//Если не пришел массив с данными берем из базы
        if (!$res) {
            if ($id) {
                $info = sql_getRows("SELECT * FROM voting WHERE id=".$id);
                $info['answers'] = sql_getRows("SELECT * FROM voting_answers WHERE pid=".$id, true);
            } else {
                $info = sql_getRows("SELECT * FROM voting WHERE visible > 0 AND root_id=".ROOT_ID." AND lang='".lang()."'");
                foreach ($info as $key=>$val)
                    $info[$key]['answers'] = sql_getRows("SELECT * FROM voting_answers WHERE pid=".$val['id'], true);
            }
    	}
    	//Если есть id то выбираем нужную строку из массива
    	elseif($id){
    		foreach ($res as $k=>$v)
    			if ($v['id'] == $id) $info = $v;
    	}
        //Выбираем первую строку из массива
    	else
    		$info = $res[0];
    	return $info;
	}

	function getQuestions($info) {

        $vote = $info['answers'];
    	$n = 1;
    	foreach ($vote as $num => $val) {
    			$d['name']	= $info['name'];
    			$d['id']	= $info['id'];
    			$d['one_vote'][$n] = array(
    						'type'		=> $info['type'],
							'free'		=> (int)$val['free'],
    						'number' 	=> ($info['type'] == "radio")?"1":$num,
    						'value'		=> $num,
    						'text' 		=> $val['name'],
    						'v_id'		=> 'v_'.$info['id'].'_'.$num,
    							);
    			$n++;
    	}

    	return $d;
	}

	//Выбираем все голосования
	function getList() {
	    $res = sql_getRows("SELECT * FROM voting WHERE visible > 0 AND root_id=".ROOT_ID." AND lang='".lang()."' ORDER BY date DESC");
        foreach ($res as $key=>$val)
            $res[$key]['answers'] = sql_getRows("SELECT * FROM voting_answers WHERE pid=".$val['id'], true);
	    return $res;
	}

	function check_vote($res){
		if (!$res['id']) return false;
		if (isset($res['answers'])) $voting = get('voting',NULL,'g');
		$ip = $_SERVER["REMOTE_ADDR"];
		if (!$ip) $ip = $_SERVER["REMOTE_HOST"];
		if (!empty($_SERVER["HTTP_VIA"])){
			$z = split(",", $_SERVER["HTTP_X_FORWARDED_FOR"]);
			$ip = $ip.':'.$z[0];
		}


		switch ($res['ipcheck']){
			case 'check':
				if (!preg_match("/$ip/i", $res['hosts'])) {
					$hosts = $res['hosts'] . $ip;
				}
				$cookie = '0';
				break;
			case 'cookie':
				$cookie = '1';
				if (isset($_COOKIE["Vote"][$res['id']])){
					$cookie = $_COOKIE["Vote"][$res['id']];
				}
				break;
			case 'none':
				break;
		}

		if ($res['ipcheck']=='none' || $hosts || $cookie==1){
			if (!empty($voting)) {
				$vote = $res['answers'];
				if ($voting) foreach ($voting as $key => $val) $vote[$val]['count']++;
	            foreach($vote as $key=>$val){
				    sql_query("UPDATE voting_answers SET count=".$val['count']." WHERE id=".$key);
	            }
	            if ($res['ipcheck'] == 'check') {
	                sql_query("UPDATE voting SET hosts='".$hosts."' WHERE id=".$res['id']);
			    } elseif ($res['ipcheck'] == 'cookie') {
	                setcookie ("Vote[".$res['id']."]", $ip,time()+24*3600, "/");
			    }
				if (isset($_GET['free']) && !empty($_GET['free'])) {
					$text = $_GET['free'];
					if (get_magic_quotes_gpc()) {
						$text = stripslashes($text);
					}
					$text = e($text);
					$query = 'INSERT INTO voting_free (voting_id,text,time)
						VALUES ('.(int)$res['id'].',"'.$text.'",NOW())';
					sql_query($query);
				}
				redirect('/voting?id='.$res['id'].'&result=1');
			}
			return true;
		}
		else {
			return false;
		}
	}

	function getAnswers($voting , $mode = 1){

        $page = &Registry::get('TPage');
        $page->tpl->config_load($page->content['domain'] . "__" . lang().'.conf', 'voting');
        # Results graph parameters
		$Vlenght = 200; # maximum graph field length (px)
		$Vheight = 7; # graph field width (px)

		if (!$offset) $offset = 0;
		if (!$limit) $limit = 10;

		$count = count($res);

		$vote = $voting['answers'];
		$max = 1;
		$sum = 0;

		foreach ($vote as $str){
			$sum += $str['count'];
			if ($str['count'] > $max) $max = $str['count'];
		}

		$k = $Vlenght / $max;

		if (!$mode){
			$data['date'] = $page->tpl->get_config_vars("voting_current");
			$first = 0;
		}
		else
			$data['date'] = $page->tpl->get_config_vars("voting_by")." ".substr($voting['date'],0,10);

		$data['name'] = $voting['name'];
		$data['id'] = $voting['id'];

		if ($vote) foreach ($vote as $str) {

				$width = round($str['count'] * $k);
				$percent = 0;
				if ($sum != 0) $percent = sprintf("%.2f", $str['count'] / $sum * 100);

				$data['row'][] = array(
					'text'=>$str['name'] ,
					'vote' => $str['count'],
					'res'=>array('width'=>$width,'percent'=>$percent,'Vheight'=>$Vheight),
					'empty'=>$width==0?array('text'=>''):'',
					'percent'=>$percent,
				);
			}

		$data['sum'] = $sum;

		return $data;
	}

}

?>