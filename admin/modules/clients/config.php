<?php

$actions['orders'] = array(
    'edit' => &$actions['table']['edit'],
);

$this->crm_menu['client'] = array(
	array(
		'Данные клиента',
		'Client Details',
		'link' => 'act.php?page=clients&do=showclientinfo&client_id='.$this->client_id,
		'items' => array(
			array(
				'Системные данные',
				'System details',
				'link' => 'act.php?page=clients&do=editsystem&client_id='.$this->client_id,
			),
			array(
				'Личные данные',
				'Personal details',
				'link' => 'act.php?page=clients&do=editclient&client_id='.$this->client_id,
			),
			array(
				'Объекты',
				'Objects',
				'link' => 'act.php?page=objects_room&client_id='.$this->client_id.'&cab=1',
			),
			/*array(
				'Загородная недвижимость',
				'Objects',
				'link' => 'act.php?page=objects_house&client_id='.$this->client_id,
			),
			array(
				'Коммерческая недвижимость',
				'Objects',
				'link' => 'act.php?page=objects_commerce&client_id='.$this->client_id,
			),
			array(
				'Счета',
				'Bills',
				'link' => 'act.php?page=bills&do=show&client_id='.$this->client_id,
			),*/
			array(
				'Платежи',
				'Payments',
				'link' => 'act.php?page=payments&do=show&client_id='.$this->client_id,
			),
			array(
				'Детализация баланса',
				'Balance',
				'link' => 'act.php?page=clients&do=showbalance&client_id='.$this->client_id,
			),
		),
	),
);
?>