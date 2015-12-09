<?php

class TProducts extends TTable {

	############################################
        var $name = 'products';
        var $table = 'products';
        var $selector = false;

	############################################
	function TProducts() {
		global $str, $actions;
		TTable::TTable();
		 // Массив строковых констант
		 $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
                        'title'                 => array('Компоненты','Parts',),
                        'name'                  => array('Название','Product Code',),
                        'description'           => array('Краткое описание','Short Description',),
                        'manufacturer'          => array('Производитель','Manufacturer',),
                        'product_discount_group'=> array('Скидочная группа','Discount group',),
                        'product_type'          => array('Тип продукта','Type',),
                        'pname'          		=> array('Название','Name',),
                        'image'                 => array('Картинка','Image',),
                        'art'                   => array('Код','Code',),
                        'art2'                   => array('Артикул','Number',),
                        'available'             => array('В наличии','Available',),                        
                        'price'                 => array( 'Цена', 'Price',),
                        'solution'               => array( 'Вид сборки', 'Solution',),
                        'weight'                => array( 'Вес, кг', 'Weight, Kg',),
                        'visible'               => array('Показывать','Visible', ),
                        'image_descr'           => array('Маленькое изображение','Small image',),
                        'image'                 => array( 'Среднее изображение', 'Medium Image',),
						'image_popup'           => array('Увеличенное изображение','Image 4 popup', ),
                        'image_err'             => array('Ошибка! Файла не найдено.','Error! File not found.',),
                        'comment'               => array('Подробное описание','Description',),
# Product Types
                        ''                      => array('-','-',),
                        'saved'                 => array('Продукт был успешно сохранён','The product has been saved successfully',),
                        'price_saved'           => array('Цены были успешно сохранены','The price has been saved successfully',),
                        'group'                 => array('Скидочная группа','Discount Group',),
                        'restore'               => array('Восстановить','Restore',),
						'recycle'               => array('Корзина','Recycle Bin',),
						'delete'                => array('Удалить','Delete', ),
						'razdel'                => array('Раздел каталога','Razdel', ),
						'catalog'               => array('Каталог','Catalog', ),
			));
		 // Массив экшенов
		$actions[$this->name] = array(
			'save' => array(
				'Сохранить изменения',
				'Save Changes',
				'link'	=> 'cnt.document.forms.editform.actions.value=\'editchanges\';cnt.document.forms.editform.submit()',
				'img' 	=> 'icon.save.gif',
				'display'	=> 'none',
			),
			'edit' => &$actions['table']['edit'],
            'create' => &$actions['table']['create'],
			'delete' => array(
				'Удалить',
				'Delete',
				'link' => 'cnt.deleteItems(\''.$this->name.'\')',
				'img' => 'icon.delete.gif',
				'display' => 'none',
			),
			'copy' => array(
				'Копировать',
				'Copy',
				'link' => 'cnt.copyItem()',
				'img' => 'icon.copy.gif',
				'display' => 'none',
			),
		);
		$actions[$this->name.'.showproductsfororder'] = $actions[$this->name.'.showproducts'] = array(
			'add' => array(
				'Добавить выбранные',
				'Add Selected',
				'link'	=> 'window.document.frames.cnt.SelectProducts();',
				'img' 	=> 'icon.save.gif',
				'display'	=> 'block',
			),
			'close' => array(
				'Закрыть',
				'Close',
				'link'	=> 'window.parent.close();',
				'img' 	=> 'icon.close.gif',
				'display'	=> 'block',
			),
		);
	}
	############################################

        function table_get_edit(&$value, &$column, &$row) {
                $size = isset($column['size']) ? $column['size'] : '';
                $maxlength = isset($column['maxlength']) ? $column['maxlength'] : '';
                $text_align = isset($column['text-align']) ? $column['text-align'] : 'left';
                return "<input onkeypress='modified(1)' onchange='modified(1)' onpaste='modified(1)' type=text name='row[{$row['id']}][{$column['display']}]' value='$value' size='$size' maxlength='$maxlength' style='text-align: $text_align'><input type=image src='images/s.gif' width=1 height=1>";
        }

    ############################################

    function EditChanges() {
		$row = get('row', array(), 'p');
		foreach ($row as $key=>$val) {
			$res = sql_query("UPDATE $this->table SET price='{$val['price']}' WHERE id=$key");
			if (!$res) return "<script>alert('".$this->str('error').": ".sql_getError()."');</script>";
		}
		return "<script>window.parent.modified(0);alert('".$this->str('price_saved')."');</script>";
	}

    ############################################
	function Show() {
        if (!empty($GLOBALS['_POST'])) {
                $actions = get('actions', '', 'p');
                if ($actions) return $this->$actions();
        }
        require_once(core('ajax_table'));
        $ret['thisname'] = $this->name.'.editform';
        $ret['table'] = ajax_table(array(
                'columns'        => array(
                        array(
								'select'        => 'p.id',
								'display'       => 'id',
								'type'          => 'checkbox',
                        ),
                        array(
								'select'        => 'p.art',
								'display'       => 'art',
								'flags'         => FLAG_SEARCH | FLAG_SORT,
                        ),
                        array(
								'select'        => 'p.art2',
								'display'       => 'art2',
								'flags'         => FLAG_SEARCH | FLAG_SORT,
                        ),                                
                        array(
								'select'        => 'p.available',
								'display'       => 'available',
								'type'       => 'available',
								'flags'         => FLAG_SORT,
                        ),
						array(
								'select'        => 'p.name',
								'as'            => 'name',
								'display'       => 'name',
								'flags'         => FLAG_SEARCH | FLAG_SORT,
						),
						array(
								'select'        => 'tree.name',
								'as'            => 'treename',
								'display'       => 'catalog',
								'flags'         => FLAG_SORT | FLAG_FILTER,
			                    'filter_type'   => 'array',
			                    'filter_value'  => array('') + sql_getRows("SELECT id, name FROM tree WHERE tree.type='catalog' AND tree.visible>0 ORDER BY name", true),
			                    'filter_display'=> 'catalog',
			                    'filter_field'  => 'tree.id',
						),
                        array(
                                'select'        => 'p.description',
                                'flags'         => FLAG_SEARCH | FLAG_SORT,
                        ),
                        array(
                                'select'        => 'p.discount_group_id',
                                'flags'         => FLAG_FILTER,
                                'filter_type'   => 'array',
                                'filter_value'  => array('') + sql_getRows("SELECT id, name FROM product_discount_groups ORDER BY priority, name", true),
                                'filter_display'=> 'group',
                                'filter_str'    => false,
                        ),
                        array(
                                'select'        => 'd.name',
                                'as'            => 'dname',
                                'flags'         => FLAG_SORT,
                                'display'       => 'product_discount_group',
                        ),
                        array(
                                'select'        => 'p.product_type_id',
                                'flags'         => FLAG_SORT | FLAG_FILTER,
                                'filter_type'   => 'array',
                                'filter_value'  => array('') + sql_getRows("SELECT id, name FROM product_types ORDER BY priority, name", true),
                                'filter_display'=> 'product_type',
                                'align'         => 'center',                                        
                        ),
                        array(
                                'select'        => 'p.solution_id',
                                'flags'         => FLAG_SORT | FLAG_FILTER,
                                'filter_type'   => 'array',
                                'filter_value'  => array('') + sql_getRows("SELECT id, name FROM solutions_types ORDER BY priority, name", true),
                                'filter_display'=> 'solution',
                                'align'         => 'center',
                                'display'       => 'solution',
                                'type'          => 'solution',
                        ),
                        array(
                                'select'        => 'm.name',
                                'as'            => 'mname',
                                'flags'         => FLAG_SORT,
                                'display'       => 'manufacturer',
                        ),
                        array(
                                'select'        => 'p.manufacturer_id',
                                'flags'         => FLAG_SORT | FLAG_FILTER,
                                'filter_type'   => 'array',
                                'filter_value'  => array('') + sql_getRows("SELECT id, name FROM manufacturers WHERE visible>0 ORDER BY priority, name", true),
                                'filter_display'=> 'manufacturer',
                                'align'         => 'center',
                        ),

                        array(
                                'select'        => 't.name',
                                'as'            => 'tname',
                                'display'       => 'product_type',
                                'flags'         => FLAG_SORT,
                                'align'         => 'center',
                        ),
                        array(
                                'select'        => 'p.price',
                                'display'       => 'price',
                                'align'         => 'right',
                                'type'          => 'edit',
                                'size'          => 8,
                                'maxlength'     => 11,
                                'text-align'    => 'right',
                                'flags'         => FLAG_SORT,
                        ),
                        array(
                                'select'        => 'p.visible',
                                'display'       => 'visible',
                                'type'          => 'visible',
                                'align'         => 'center',
                                'flags'         => FLAG_SORT,
                        ),
                ),
				'from'     => $this->table.' AS p LEFT JOIN product_types AS t ON p.product_type_id=t.id LEFT JOIN product_discount_groups AS d ON p.discount_group_id=d.id LEFT JOIN manufacturers AS m ON p.manufacturer_id=m.id LEFT JOIN elem_product AS ep ON ep.id = p.id LEFT JOIN tree ON tree.id = ep.pid',
				'where'    => 'p.visible>=0',
				'groupby'  => 'p.id',
				'orderby'  => 'p.name',						
				'params'   => array('page' => $this->name, 'do' => 'show'),
				'click'    => 'ID=cb.value',
				'dblclick' => 'editItem(id)',
				//'_sql'=>1,
        ), $this);
        $this->AddStrings($ret);
        return $this->Parse($ret, $this->name.'.tmpl');
	}

	########################
	function table_get_solution($value){
		   return sql_getValue("SELECT name FROM solutions_types WHERE id=".$value);
	}
	#######################
	
	function EditCopy(){
		$id = get('id',0,'pg');

		sql_query('BEGIN');
		//products
		$products = sql_getRow("SELECT * FROM products WHERE id=".$id);
		$products['id'] = 'copy';
		$query = "INSERT INTO products VALUES('".implode("','",$products)."')";
		sql_query($query);
		$nid = mysql_insert_id();
		if(sql_getErrNo()){ sql_query('ROLLBACK'); return $query;}
		
		//elem_ptext
		$elem_ptext = sql_getRow("SELECT * FROM elem_ptext WHERE pid=".$id);
		if (!empty($elem_ptext)){
			$elem_ptext['pid'] = $nid;
			$query = "INSERT INTO elem_ptext VALUES('".implode("','",$elem_ptext)."')";
			sql_query($query);
			if(sql_getErrNo()){ sql_query('ROLLBACK'); return $query;}
		}
		
		//elem_pgallery
		$elem_pgallery = sql_getRows("SELECT * FROM elem_pgallery WHERE pid=".$id);
		if (!empty($elem_pgallery)){
			$query = "INSERT INTO elem_pgallery VALUES ";
			foreach ($elem_pgallery as $k=>$v){
				$elem_pgallery[$k]['id'] = 'copy';
				$elem_pgallery[$k]['pid'] = $nid;
				$query .= "('".implode("','",$elem_pgallery[$k])."'),";
			}
			sql_query(substr($query,0,-1));
			if(sql_getErrNo()){ sql_query('ROLLBACK'); return $query;}
		}
		
		//elem_pfile
		$elem_pfile = sql_getRows("SELECT * FROM elem_pfile WHERE pid=".$id);
		if (!empty($elem_pfile)){
			$query = "INSERT INTO elem_pfile VALUES ";
			foreach ($elem_pfile as $k=>$v){
				$elem_pfile[$k]['id'] = 'copy';
				$elem_pfile[$k]['pid'] = $nid;
				$query .= "('".implode("','",$elem_pfile[$k])."'),";
			}
			sql_query(substr($query,0,-1));
			if(sql_getErrNo()){ sql_query('ROLLBACK'); return $query;}
		}
		
		//products_params_extra
		$products_params_extra = sql_getRows("SELECT * FROM products_params_extra WHERE pid=".$id);
		if (!empty($products_params_extra)){
			$query = "INSERT INTO products_params_extra VALUES ";
			foreach ($products_params_extra as $k=>$v){
				$products_params_extra[$k]['id'] = 'copy';
				$products_params_extra[$k]['pid'] = $nid;
				$query .= "('".implode("','",$products_params_extra[$k])."'),";
			}
			sql_query(substr($query,0,-1));
			if(sql_getErrNo()){ sql_query('ROLLBACK'); return $query;}
		}

		//product_params
		$product_params = sql_getRows("SELECT * FROM product_params WHERE product_id=".$id);
		if (!empty($product_params)){
			$query = "INSERT INTO product_params VALUES ";
			foreach ($product_params as $k=>$v){
				$product_params[$k]['product_id'] = $nid;
				$query .= "('".implode("','",$product_params[$k])."'),";
			}
			sql_query(substr($query,0,-1));
			if(sql_getErrNo()){ sql_query('ROLLBACK'); return $query;}
		}
		sql_query('COMMIT');

		return "<script>window.parent.location.reload();</script>";
	}
	
	########################

	function ShowProducts() {
		$id = (int)get('id', 0);
		$tab = get('tab', '');
		$esId = get('esId', '');
		$row['isset'] = sql_getRows("SELECT id FROM elem_product WHERE pid=".$id,true);
		$temp = '';
		$sep = '';
		foreach ($row['isset'] as $k=>$v){
			$temp .= $sep.$v;
			$sep = ',';
		}
		if (!empty($temp)){
			$temp = ' and `id` NOT IN('.$temp.')';
		}

//		$_GET['limit'] = '10';
		require_once(core('ajax_table'));
		$table = ajax_table(array(
			'columns'	=> array(
				array(
					'select'	=> 'id',
					'display'	=> 'id',
					'type'		=> 'checkbox',
				),
				array(
					'select'	=> 'name',
					'display'	=> 'name',
					'flags'		=> FLAG_SEARCH | FLAG_SORT,
				),
			),
			'where'		=> '`visible` > 0'.$temp,
			'params'	=> array('page' => $this->name,'tab'=>$tab,'id'=>$id,'esId'=>$esId,'do' => 'showproducts'),
			'target' => 'tmpproduts_list',
		//'action'=>'ed.php',
		), $this);
		return $this->parse(array('table'=>$table), 'products_list.tmpl');
	}

	########################
    function table_get_product_type(&$value, &$column, &$row) {
        $name = sql_getValue("SELECT name FROM product_types WHERE id=".$value);
        return $name;
    }
    ########################
    function table_get_available(&$value, &$column, &$row) {
        return ($value)? 'да': 'нет';
    }
 	########################
    function ShowProductsForOrder() {
        $id = (int)get('id', 0);

        $row['isset'] = sql_getRows("SELECT DISTINCT p.id FROM products AS p
        LEFT JOIN cart as c ON c.product_id=p.id
        WHERE c.order_id=".$id);

        $temp = '';
        $sep = '';
        foreach ($row['isset'] as $k=>$v){
            $temp .= $sep.$v;
            $sep = ',';
        }
        if (!empty($temp)){
            $temp = ' AND p.id NOT IN('.$temp.') ';
        }

        require_once(core('ajax_table'));
        $table = table(array(
            'columns'    => array(
                array(
                    'select'    => 'DISTINCT p.id',
                    'display'    => 'id',
                    'type'        => 'checkbox',
                ),
                array(
                    'select'    => 'p.art',
                    'display'    => 'art',
                    'flags'        => FLAG_SEARCH | FLAG_SORT,
                ),         
                array(
                    'select'    => 'p.art2',
                    'display'    => 'art2',
                    'flags'        => FLAG_SEARCH | FLAG_SORT,
                ),
                array(
                    'select'    => 'm.name',
                    'as'		=> 'mname',
                    'display'    => 'manufacturer',
                    'flags'         => FLAG_SORT | FLAG_FILTER,
                    'filter_type'   => 'array',
                    'filter_value'  => array('') + sql_getRows("SELECT id, name FROM manufacturers ORDER BY name", true),
                    'filter_display'=> 'manufacturer',
                    'filter_field'  => 'm.id',
                ),                
                array(
                    'select'        => 'tree.name',
                    'as'			=> 'razdel',
                    'display'       => 'razdel',
                    'flags'         => FLAG_SORT | FLAG_FILTER,
                    'filter_type'   => 'array',
                    'filter_value'  => array('') + sql_getRows("SELECT id, name FROM tree WHERE tree.type='catalog' AND tree.visible>0 ORDER BY name", true),
                    'filter_display'=> 'razdel',
                    'filter_field'  => 'tree.id',
                ),
                array(
                    'select'    => 'p.price',
                    'display'    => 'price',
                    'flags'        => FLAG_SORT,
                ),
                array(
                    'select'    => 'p.name',
                    'display'    => 'pname',
                    'flags'        => FLAG_SEARCH | FLAG_SORT,
                ),                
            ),
        	'from' => $this->table.' AS p LEFT JOIN product_types AS t ON p.product_type_id=t.id
                        LEFT JOIN elem_product AS ep ON ep.id=p.id
                        LEFT JOIN manufacturers AS m ON m.id = p.manufacturer_id
                        LEFT JOIN tree AS tree ON tree.id=ep.pid
                        ',
            'where'  => ' p.solution_id = 0 AND p.visible > 0'.$temp,
            'params' => array('page' => $this->name,'id'=>$id,'do' => 'showproductsfororder'),
            'target' => 'tmpproduts_list',
            //'_sql'   => true,
        //'action'=>'ed.php',
        ), $this);
        return $this->parse(array('table'=>$table,'thisname'=>$this->name), 'products_list2.tmpl');
    }
}
$GLOBALS['products'] = & Registry::get('TProducts');

?>