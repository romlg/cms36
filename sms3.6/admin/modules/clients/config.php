<?php
//����� ���������� ������
/*$actions[$this->name]['save'] = array(
		'���������',
		'Save',
		'link'	=> 'cnt.mySubmit(\''.$this->name.'\')',
		'img' 	=> 'icon.save.gif',
		'display'	=> 'none',
);*/
		
$actions['orders'] = array(
    'edit' => &$actions['table']['edit'],
);
        
//����� ���������� ��������� ���������
//$str[get_class_name($this)][''] = array('','');        

//���������� ����
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
				'������ ��������',
				'Company details',
				'link' => 'act.php?page=clients&do=editcompany&client_id='.$this->client_id,
			),
		),
	),
	array(
		'������',
		'Orders',
		'link' => 'act.php?page=orders&show=all&client_id='.$this->client_id,
		'items' => array(
			array(
				'�����',
				'New',
				'link' => 'act.php?&sort=-4&client_id='.$this->client_id.'&page=orders&do=show&filter%5Bo.status%5D=New',
			),
			array(
				'� ���������',
				'In progress',
				'link' => 'act.php?&sort=-4&client_id='.$this->client_id.'&page=orders&do=showprocessing',
			),
			array(
				'�����������',
				'Delivered',
				'link' => 'act.php?&sort=-4&client_id='.$this->client_id.'&page=orders&do=show&filter%5Bo.status%5D=Delivered',
			),
			array(
				'����������',
				'Canceled',
				'link' => 'act.php?&sort=-4&client_id='.$this->client_id.'&page=orders&do=show&filter%5Bo.status%5D=Canceled',
			),
			array(
				'��������� ��������',
				'Bought products',
				'link' => 'act.php?page=cart&client_id='.$this->client_id,
			),
		),
	),
	array(
		'�����',
		'Orders',
		'link' => 'act.php?page=bills&client_id='.$this->client_id,
		'items' => array(
			array(
				'������������',
				'',
				'link' => 'act.php?filter%5Bstatus%5D=new&find=&client_id='.$this->client_id.'&offset=0&page=bills&do=show',
				'target' => 'temp',
			),
			array(
				'����������',
				'',
				'link' => 'act.php?filter%5Bstatus%5D=paid&find=&client_id='.$this->client_id.'&offset=0&page=bills&do=show',
			),
		),
	),
	array(
		'�������',
		'Questions',
		'link' => 'act.php?page=support&client_id='.$this->client_id,
		'items' => array(
			array(
				'�����',
				'New',
				'link' => 'act.php?page=support&show=new&client_id='.$this->client_id,
			),
			array(
				'� ���������',
				'In progress',
				'link' => 'act.php?page=support&show=progress&client_id='.$this->client_id,
			),
			array(
				'��������',
				'New',
				'link' => 'act.php?page=support&show=closed&client_id='.$this->client_id,
			),
		),
	),
	array(
		'����������',
		'Communication',
		'items' => array(
			array(
				'�������',
				'Notes',
				'link' => 'act.php?page=notes&client_id='.$this->client_id,
			),
			array(
				'�������',
				'History',
				'link' => 'act.php?page=history&client_id='.$this->client_id,
			),
		),
	),
);
?>