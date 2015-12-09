<?php

require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TGoogleElement extends TElems{

	var $elem_table = "rnt_objects";

    function ElemInit() {
        $this->elem_str = array(
			'x'		=>	array('Широта',			'Lat'),
			'y'		=>	array('Долгота',		'Lng'),
			'scale'	=>	array('Масштаб',		'Scale'),
		);
		return parent::ElemInit();
    }

	function getWCfromDb($id) {
		return $this->GetRow('SELECT * FROM `rnt_objects` WHERE id='.$id);
	}

	function ElemForm() {
		$id = (int)get('id', 0);
		if ($id) {
			$row = $this->getObject();
		}

        if ($row['object']['obj_type_id'] == 'newbuild') {
        	$row['object']['price'] = $row['object']['price_rub_print'].'<br>'.$row['object']['price_dollar_print'];
        } else {
        	$row['object']['price'] = number_format(doubleval(str_replace(',', '.', $row['object']['price_rub'])), 0, ',', ' ').' руб. ('.number_format(doubleval(str_replace(',', '.', $row['object']['price_dollar'])), 0, ',', ' ').'у.е. )';
        }

		$row['object']['x'] = $row['object']['y'] = '0.000000';
		if ($row['object']['address_id']) {
			$coordinats = sql_getRow('SELECT x, y FROM `obj_address` WHERE id='.$row['object']['address_id']);
			$row['object']['x'] = $coordinats['x'];
			$row['object']['y'] = $coordinats['y'];
		}

		// добавляет в шаблон дефолтные строковые константы
		$this->AddStrings($row);

		return Parse($row, 'rnt_objects/elem_google.tmpl');
	}

	########################

	function ElemEdit($id, $row) {
		global $lang;

		$sql = sql_query('UPDATE `obj_address` SET x="'.$row['x'].'", y="'.$row['y'].'" WHERE id='.$row['address_id']);
		return 1;
	}
}

?>