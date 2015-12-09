<?php

function objects_getAlign(){
    return array('rooms' => '', 'purpose' => '', 'address' => 'left', 'metro' => 'left', 'metro_dest' => '', 'direction' => '', 'distance' => '', 'status' => '', 'storey' => '', 'house_type' => '', 'square_all' => '', 'land_area' => '', 'balcony' => '', 'phone' => '', 'lavatory' => '', 'price_dollar' => 'right', 'price_rub' => 'right', 'short_description' => 'left', 'comment' => 'left');
}

function objects_getFields($type, $region){
    $fields = array();
    $fields['lot_id'] = '���';
    switch ($type) {
        case 'room'		:
	        $fields['rooms'] = '����.';
	        $fields['address'] = '�����';
	        if ($region == '2') {
	            $fields['metro'] = '�����';
	            $fields['metro_dest'] = '����������� �� �����';
	        }
			$fields['status'] = '������';
	        $fields['storey'] = '����/������';
	        $fields['house'] = '��� ����';
	        $fields['square_all'] = '������� (��.�.)';
	        $fields['balcony'] = '������';
	        $fields['phone'] = '�������';
	        $fields['lavatory'] = '�������';
	        break;
        case 'house'	:
	        $fields['rooms'] = '����.';
	        $fields['address'] = '�����';
            $fields['direction'] = '�����������';
            $fields['distance'] = '����������� ��  ���� (� ��)';
	        $fields['storey'] = '����/������';
	        $fields['living_area'] = '������� ��������, �2';
	        $fields['land_area'] = '������ �������, ���.';
	        break;
        case 'commerce'	:
	        $fields['purpose'] = '����������';
	        $fields['location'] = '������������';
	        if ($region == '2') {
	            $fields['metro'] = '�����';
	            $fields['metro_dest'] = '����������� �� �����';
	        }
	        $fields['square_all'] = '������� (��.�.)';
        break;
    }
    $fields['price_dollar'] = '����&nbsp;(�.�.)';
    $fields['price_rub'] = '����&nbsp;(���)';
    $fields['short_description'] = '������� ��������';
    $fields['manager'] = '�������';
    return $fields;
}

function objects_getList($session, $section){
    // ����������
    $sort = '';
    if (isset($session['sort']) && $session['sort'] != '0') {
        switch (abs($session['sort'])) {
            case '2' : $sort = ' ORDER BY o.visible'; break;
            case '3' : $sort = ' ORDER BY o.lot_id'; break;
            case '4' : $sort = ' ORDER BY o.address'; break;
            case '5' : $sort = ' ORDER BY o.room'; break;
            case '6' : $sort = ' ORDER BY om.name'; break;
        }
        if (!empty($sort) && $session['sort'] < 0) $sort .= ' DESC';
    }

    // ������
    $where[] = 'o.visible > -1';
    $where[] = 'obj_type_id="'.$section.'"';
    if (isset($session['print_filter'])) {
        foreach ($session['print_filter'] as $paramName=>$paramValue) {
            if (empty($paramValue)) continue;
            if ($paramName == 'visible') {
                if ($paramValue == '1') $where[] = 'o.visible > 0';
                else $where[] = 'o.visible = 0';
            }
            elseif ($paramName == 'otr.name') {
				$where[] = 'otr.id = "'.$paramValue.'"';
            }
            else $where[] = $paramName.' = "'.$paramValue.'"';
        }
    }

    // �����
    if (isset($session['find']) && !empty($session['find'])) {
        $where[] = 'LOWER(CONCAT(o.lot_id, o.address)) LIKE LOWER("%'.$session['find'].'%")';
    }

    $sql = 'SELECT o.* FROM objects AS o
	    LEFT JOIN obj_transaction otr ON ( o.sell_type_id  = otr.id )
	    WHERE '.(!empty($where) ? implode(' AND ', $where) : '').' '.$sort;

    return sql_getRows($sql);
}

function objects_formatList(&$list){
    $metro = sql_getRows('SELECT id, name FROM obj_locat_metrostations', true);
    $direction = sql_getRows('SELECT id, name FROM obj_direction', true);

    foreach ($list as $key=>$val) {
        switch ($val['obj_type_id']) {
            case 'room'		:
	            $list[$key]['rooms'] = $val['room'];
	            $list[$key]['storey'] = $val['storey'].'/'.$val['storeys_number'];
	            $list[$key]['square_all'] = $val['total_area'].'/'.$val['living_area'].'/'.$val['kitchen_area'];
            break;
            case 'house'	:
				$list[$key]['rooms'] = $val['room'];
	            $list[$key]['storey'] = $val['storey'].'/'.$val['storeys_number'];
				$list[$key]['direction'] = $direction[$val['direction']];
            break;
            case 'commerce'	:
	            $list[$key]['purpose'] = $types[$val['purpose']];
    	        $list[$key]['square_all'] = $val['total_area'];
            break;
        }
        if ($val['metro_id']) {
            $list[$key]['metro'] = $metro[$val['metro_id']];
            $list[$key]['metro_dest'] = $val['metro_dest_value'] > 0 ? $val['metro_dest_value'].($val['metro_dest_text'] == 0 ? ' <img src="/images/man.gif">' : ' <img src="/images/car.gif">') : '&nbsp;';
        }
        $list[$key]['lavatory'] = $val['lavatory'] == '0' ? '�' : ($val['lavatory'] == '1' ? '�' : '?');
        $list[$key]['phone'] = $val['phone'] == '1' ? '+' : ($val['phone'] == '0' ? '-' : '?');
        $list[$key]['balcony'] = $val['balcony'] > 0 ? '+' : '-';
        if ($val['region'] == '170') { // �����������
            $list[$key]['direction'] = $direction[$val['direction']];
        }
        $list[$key]['photo'] = isset($photo_counts[$val['id']]) && $photo_counts[$val['id']] > 0 ? '<img src="/images/photo.gif">' : '&nbsp;';
        $list[$key]['video'] = isset($video_counts[$val['id']]) && $video_counts[$val['id']] > 0 ? '<img src="/images/video.gif">' : '&nbsp;';

        $list[$key]['price_dollar'] = sprintf('%d', $val['price_dollar']);
        // ��������� � ���� ������ ��������
        $list[$key]['price_dollar'] = number_format($list[$key]['price_dollar'], ' ', ' ', ' ');
        $list[$key]['price_rub'] = number_format($list[$key]['price_rub'], ' ', ' ', ' ');
        $list[$key]['manager'] = isset($managers[$val['manager']]) ? $managers[$val['manager']] : '';
    }

    function objects_getFilter($filter) {
        $res = array();
        foreach ($filter as $key=>$val) {
            if (empty($val)) continue;
            switch ($key) {
                case 'otr.name' : $res['��� ��������'] = sql_getValue('SELECT name FROM obj_transaction WHERE id='.$val); break;
                case 'o.district_id' : $res['�����'] = sql_getValue('SELECT name FROM obj_locat_districts WHERE id='.$val); break;
                case 'o.direction' : $res['�����������'] = sql_getValue('SELECT name FROM obj_direction WHERE id='.$val); break;
                case 'visible' : $res['����������'] = $val > 1 ? '���' : '��'; break;
                default: $res[$key] = $val;
            }
        }
        return $res;
    }
}
?>
