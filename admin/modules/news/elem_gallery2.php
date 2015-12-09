<?php
require_once elem(OBJECT_EDITOR_MODULE.'/elems');

class TGalleryElement extends TElems{

	######################
	var $elem_name  = "elem_gallery";  				//название elema
	var $elem_table = "elem_gallery_news";          //название таблицы elema (DEFAULT $elem_name)
	var $elem_type  = 'multi';
    var $elem_class = "gallery";
	var $elem_str = array(                       //строковые константы
			'image_large'    => array('Большая картинка','Large image',),
			'image_small'    => array('Маленькая картинка','Small image',),
			'name'           => array('Название','Title',),
			'visible'        => array('Показывать','Visible',),
		);
	var $order = " ORDER BY priority ";
	//поля для выборки из базы элема
	var $elem_fields = array('id_field' => 'pid');
	var $elem_where=" type='photos'";
	var $script;
	//var $sql = true;

	########################
	function ElemInit() {
        global $multielemactions;
        $this->list_buttons['create'] = &$multielemactions['create'];
        $this->list_buttons['delete'] = &$multielemactions['delete'];

        $this->columns = array(
            array(
                'select'    => 'id',
                'display'   => 'ids',
                'type'      => 'checkbox',
                'width'     => '1px',
            ),
            array(
                'select'    => 'name',
                'display'   => 'name',
                'flags'     => FLAG_SEARCH,
            ),
            array(
                'select'    => 'image_small',
                'type'      => 'smallimagepath',
                'display'   => 'image_small',
            ),
            array(
                'select'    => 'image_large',
                'type'      => 'smallimagepath',
                'display'   => 'image_large',
            ),
        );
        TElems::ElemInit();
	}
	########################
	function table_get_smallimagepath(&$value, &$column, &$row) {
		$size = isset($column['size']) ? $column['size'] : '';
		$maxlength = isset($column['maxlength']) ? $column['maxlength'] : '';
		$text_align = isset($column['text-align']) ? $column['text-align'] : 'left';
		return "<a href='#' onclick=\"window.open('../scripts/popup.php?img=$value', 'popup', 'width=100,height=100'); return false\">$value</a>";
	}
}
?>