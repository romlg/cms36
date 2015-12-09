<?php

/* $Id: cart.php,v 1.1 2009-02-18 13:09:08 konovalova Exp $
 */
//require_once 'ss_zip.class.php';

class Tcart extends TTable {

	var $name = 'cart';
	var $table = 'cart';

	########################

	function Tcart() {
		global $str, $actions;

		TTable::TTable();

		$actions[$this->name] = array();

		$str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
			'title'		=> array('Проданные товары',			'Sold products',),
			'name'		=> array('Название',				'Product name'),
			'product_name'	=> array('Название',				'Product name'),
			'serial'	=> array('Серийный номер',			'Serial #',),
			'order_date'	=> array('Дата покупки',			'Order Date',),
			'order_id'	=> array('Номер заказа',			'Order ID',),
			'status'	=> array('Статус',				'Status',),
			'status_3'	=> array('Оплачен',				'Paid',),
			'status_4'	=> array('Доставлен',				'Shipped',),
			'quantity'	=> array('Количество',				'Quantity',),
			'price'		=> array('Стоимость',				'Total Price',),
            'customer_price'        => array('Стоимость со скидкой',                'Customer Price',),
            'art'        => array('Код товара',                'Product Code',),
            'art2'        => array('Артикул',      'Manufacturer Code',),
            'manufacturer'        => array('Производитель',                'Manufacturer',),
            'catalog'        => array('Каталог',                'Catalog',),
            ######## Navig Form ###########
            'nav_period'     => array(
                'Экспорт в CSV',
                'Period',
            ),
            'select_date'     => array(
                'Выбрать дату',
                'Select date',
            ),
            'nav_show'     => array(
                'Экспорт в CSV',
                'show',
            ),

		));
	}
	
	########################

	function table_get_order(&$value, &$column, &$row) {
		$edit = "window.open('ced.php?page=orders&do=editform&id=".$value."', 'editorders', 'width=900, height=600, resizable=1, status=1').focus()";
		return '<a href="#" onclick="'.$edit.'; return false;"><b>#'.$value.'</b> <img src="images/icons/icon.edit.gif" width=16 height=16 border=0 alt="'.$this->str('edit').'" align="absmiddle"></a>';
	}

	function table_get_status(&$value, &$column, &$row) {
        global $directories;
        return $directories['order_status'][$value];
	}

	########################

	function Show() {
		require_once(core('ajax_table'));
		$client_id = get('client_id', '');

		$ret['table'] = ajax_table(array(
			'columns'	=> array(
                array(
                    'select'    => 'p.id',
                    'display'   => 'id',
                    'type'      => 'checkbox',
                ),
				array(
					'select'	=> 'p.name',
					'display'	=> 'product_name',
					'flags'		=> FLAG_SORT,
				),
				array(
					'select'	=> 'o.order_date',
					'display'	=> 'order_date',
					'type'		=> 'datetime',
					'flags'		=> FLAG_SORT,
				),
				array(
					'select'	=> 'o.id',
					'display'	=> 'order_id',
					'type'		=> 'order',
					'flags'		=> FLAG_SORT,
				),
				array(
					'select'	=> 'o.status',
					'display'	=> 'status',
					'type'		=> 'status',
					'flags'		=> FLAG_SORT,
				),
				array(
					'select'	=> 'c.quantity',
					'display'	=> 'quantity',
					'align'		=> 'right',
					'flags'		=> FLAG_SORT,
				),
				array(
					'select'	=> 'ROUND((c.price+c.delta)*(1-c.discount))',
					'display'	=> 'price',
					'align'		=> 'right',
					'type'		=> 'number',
					'flags'		=> FLAG_SORT,
				),
			),
			'from'		=> '
				orders AS o,
				cart AS c
				LEFT JOIN products AS p ON c.product_id=p.id
				LEFT JOIN auth_users AS cl ON o.client_id=cl.id',
			'where'		=>
				'c.order_id=o.id'.
				' AND o.status IN ("Delivered","ReadyPaid")'.
				($client_id ? ' AND o.client_id='.$client_id : ''),
			'orderby'	=> 'o.order_date',
            'params'    => array('page' => $this->name, 'do' => 'show','client_id'=>$client_id),
//            'click'    => 'ID=cb.value',
//            'dblclick' => 'editItem(id)',
			//'_sql' => true
		), $this);

        /****** Навигация *********/
/*
        $navig['from_date'] = get('from_date', '');
        $navig['to_date'] = get('to_date', '');

        // Set Default From_date and To_date
        if (empty($navig['from_date'])) {
            $navig['from_date'] = mktime(0,0,0,date('m') ,date('d')-7 ,date('Y'));
            $navig['from_date'] = date('Y-m-d', $navig['from_date']);
        }
        else
            $navig['from_date'] = date('Y-m-d', get('from_date', ''));

        if (empty($navig['to_date'])) {
            $navig['to_date'] = mktime(23, 59, 59, date('m'), date('d') ,date('Y'));
            $navig['to_date'] = date('Y-m-d', $navig['to_date']);
        }
        else
            $navig['to_date'] = date('Y-m-d', get('to_date', ''));

        $ret['navig'] = $navig;
*/
        /**************************/

        $ret['client_id'] = $client_id;
        $this->AddStrings($ret);
        return $this->Parse($ret, $this->name.'.tmpl');
	}

	######################

    function EditCSV(){
        $period = get('period','all');

        $date_state = "";

        if ($period != 'all') {
            $from = get('from_date','');
            $to = get('to_date','');
            if ($from && $to) {
                $from = explode('-', $from);
                $to = explode('-', $to);
                if(count($to)>2 && count($from)>2) {
                    $from_date = mktime(0, 0, 0, $from[1], $from[2], $from[0]);
                    $to_date = mktime(23, 59, 59, $to[1], $to[2], $to[0]);
                }
            }
            $date_state = " AND (o.order_date BETWEEN '".$from_date."' AND '".$to_date."')";
        }
        $filename = $this->name.'_'.date('Y-m-d').'.csv';
        $data =
            $this->str('art').';'.
            $this->str('art2').';'.
            $this->str('name').';'.
            $this->str('manufacturer').';'.
            $this->str('catalog').';'.
            $this->str('order_id').';'.
            $this->str('order_date').';'.
            $this->str('quantity').';'.
            $this->str('price').';'.
            $this->str('customer_price')."\n";

        $client_id = get('client_id',NULL);
        if (isset($client_id)) {
            $rows = $this->getRows("SELECT p.art, p.art2, p.name, m.name as manufacturer, t.name as catalog, o.id as order_id, DATE_FORMAT(FROM_UNIXTIME(o.order_date),'%Y-%m-%d') as order_date, c.quantity, c.price, c.customer_price
            FROM orders AS o, cart AS c, elem_product AS ep
            LEFT JOIN products AS p ON c.product_id = p.id
            LEFT JOIN manufacturers AS m ON m.id = p.manufacturer_id
            LEFT JOIN tree AS t ON ep.pid = t.id
            LEFT JOIN auth_users AS c1 ON o.client_id=c1.id
            WHERE c.order_id=o.id AND ep.id=p.id AND o.status IN ('Delivered', 'ReadyPaid') ".$date_state." AND o.client_id=".$client_id." ORDER BY o.order_date");
        } else {
            $sql = "SELECT p.art, p.art2, p.name, m.name as manufacturer, t.name as catalog, o.id as order_id, DATE_FORMAT(FROM_UNIXTIME(o.order_date),'%Y-%m-%d') as order_date, c.quantity, c.price, c.customer_price
            FROM orders AS o, cart AS c, elem_product AS ep
            LEFT JOIN products AS p ON c.product_id = p.id
            LEFT JOIN manufacturers AS m ON m.id = p.manufacturer_id
            LEFT JOIN tree AS t ON ep.pid = t.id
            WHERE c.order_id = o.id AND ep.id=p.id AND o.status IN ('Delivered', 'ReadyPaid') ".$date_state." ORDER BY o.order_date";

            $rows = $this->getRows($sql);
        }
        if ($rows) foreach ($rows as $i=>$row) {
            $data .=
            $row['art'].';'.
            $row['art2'].';'.
            $row['name'].';'.
            $row['manufacturer'].';'.
            $row['catalog'].';'.
            $row['order_id'].';'.
            $row['order_date'].';'.
            $row['quantity'].';'.
            $row['price'].';'.
            $row['customer_price']."\n";
        }
        $zip= new ss_zip('',6);
        $zip->add_data($filename, $data);
        $zip->save($filename.".zip",'d');
    }
}

$GLOBALS['cart'] = & Registry::get('Tcart');

?>