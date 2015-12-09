<?php
//можем переписать экшены
/*$actions[$this->name]['save'] = array(
		'Сохранить',
		'Save',
		'link'	=> 'cnt.mySubmit(\''.$this->name.'\')',
		'img' 	=> 'icon.save.gif',
		'display'	=> 'none',
);*/
		
$actions['orders'] = array(
    'edit' => &$actions['table']['edit'],
);
        
//можем переписать строковые константы
//$str[get_class_name($this)][''] = array('','');        

//собственно меню
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
				'Данные компании',
				'Company details',
				'link' => 'act.php?page=clients&do=editcompany&client_id='.$this->client_id,
			),
		),
	),
	array(
		'Заказы',
		'Orders',
		'link' => 'act.php?page=orders&show=all&client_id='.$this->client_id,
		'items' => array(
			array(
				'Новые',
				'New',
				'link' => 'act.php?&sort=-4&client_id='.$this->client_id.'&page=orders&do=show&filter%5Bo.status%5D=New',
			),
			array(
				'В обработке',
				'In progress',
				'link' => 'act.php?&sort=-4&client_id='.$this->client_id.'&page=orders&do=showprocessing',
			),
			array(
				'Доставленые',
				'Delivered',
				'link' => 'act.php?&sort=-4&client_id='.$this->client_id.'&page=orders&do=show&filter%5Bo.status%5D=Delivered',
			),
			array(
				'Отмененные',
				'Canceled',
				'link' => 'act.php?&sort=-4&client_id='.$this->client_id.'&page=orders&do=show&filter%5Bo.status%5D=Canceled',
			),
			array(
				'Купленные продукты',
				'Bought products',
				'link' => 'act.php?page=cart&client_id='.$this->client_id,
			),
		),
	),
	array(
		'Счета',
		'Orders',
		'link' => 'act.php?page=bills&client_id='.$this->client_id,
		'items' => array(
			array(
				'Выставленные',
				'',
				'link' => 'act.php?filter%5Bstatus%5D=new&find=&client_id='.$this->client_id.'&offset=0&page=bills&do=show',
				'target' => 'temp',
			),
			array(
				'Оплаченные',
				'',
				'link' => 'act.php?filter%5Bstatus%5D=paid&find=&client_id='.$this->client_id.'&offset=0&page=bills&do=show',
			),
		),
	),
	array(
		'Вопросы',
		'Questions',
		'link' => 'act.php?page=support&client_id='.$this->client_id,
		'items' => array(
			array(
				'Новые',
				'New',
				'link' => 'act.php?page=support&show=new&client_id='.$this->client_id,
			),
			array(
				'В обработке',
				'In progress',
				'link' => 'act.php?page=support&show=progress&client_id='.$this->client_id,
			),
			array(
				'Закрытые',
				'New',
				'link' => 'act.php?page=support&show=closed&client_id='.$this->client_id,
			),
		),
	),
	array(
		'Информация',
		'Communication',
		'items' => array(
			array(
				'Заметки',
				'Notes',
				'link' => 'act.php?page=notes&client_id='.$this->client_id,
			),
			array(
				'История',
				'History',
				'link' => 'act.php?page=history&client_id='.$this->client_id,
			),
		),
	),
);
?>