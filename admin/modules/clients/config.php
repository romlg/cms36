<?php

$actions['orders'] = array(
    'edit' => &$actions['table']['edit'],
);

$this->crm_menu['client'] = array(
	array(
		'������ �������',
		'Client Details',
		'link' => 'act.php?page=clients&do=showclientinfo&client_id='.$this->client_id,
		'items' => array(
			array(
				'��������� ������',
				'System details',
				'link' => 'act.php?page=clients&do=editsystem&client_id='.$this->client_id,
			),
			array(
				'������ ������',
				'Personal details',
				'link' => 'act.php?page=clients&do=editclient&client_id='.$this->client_id,
			),
			array(
				'�������',
				'Objects',
				'link' => 'act.php?page=objects_room&client_id='.$this->client_id.'&cab=1',
			),
			/*array(
				'���������� ������������',
				'Objects',
				'link' => 'act.php?page=objects_house&client_id='.$this->client_id,
			),
			array(
				'������������ ������������',
				'Objects',
				'link' => 'act.php?page=objects_commerce&client_id='.$this->client_id,
			),
			array(
				'�����',
				'Bills',
				'link' => 'act.php?page=bills&do=show&client_id='.$this->client_id,
			),*/
			array(
				'�������',
				'Payments',
				'link' => 'act.php?page=payments&do=show&client_id='.$this->client_id,
			),
			array(
				'����������� �������',
				'Balance',
				'link' => 'act.php?page=clients&do=showbalance&client_id='.$this->client_id,
			),
		),
	),
);
?>