<?php

require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TBlockstartBaseElement extends TElems
{
    var $editable = array();
    var $elem_name = "elem_blockstart";
    var $elem_table = "infoblocks";
    var $elem_type = "single";

    var $elem_str = array(
        'title' => array('Заголовок', 'Title'),
        'title_link' => array('Ссылка на заголовке', 'Title link'),
        'header_text_fck' => array('', ''),
        'header_text_area' => array('', ''),
        'header_text_radio' => array('Текст', 'Text'),
    );

    var $elem_fields = array(
        'columns' => array(
            'title' => array(
                'type' => 'text',
                'size' => 255,
            ),
            'title_link' => array(
                'type' => 'text',
            ),
            'header_text' => array(
                'type' => 'hidden',
            ),
            'header_text_radio' => array(
                'type' => 'radio',
                'option' => array('0' => 'редактор текста', '1' => 'код без форматирования'),
            ),
            'header_text_fck' => array(
                'type' => 'fck',
                'size'   => array('100%','300'),
                'db_field' => false,
            ),
            'header_text_area' => array(
                'type' => 'textarea',
                'db_field' => false,
            ),
        ),
        'id_field' => 'id',
    );

    var $elem_req_fields = array();

    var $script = "";

    function ElemInit() {
        $columns = sql_getRows("SHOW COLUMNS FROM " . $this->elem_table . "", true);
        if (!isset($columns['header_text_radio'])) {
            sql_query("ALTER TABLE " . $this->elem_table . " ADD header_text_radio TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0 -  редактор текста; 1 - html-код баннера'");
        }
        if (!isset($columns['title_link'])) {
            sql_query("ALTER TABLE {$this->elem_table} ADD title_link VARCHAR( 255 ) NOT NULL COMMENT 'Ссылка на заголовке инфоблока';");
        }

        $id = (int)get('id');
        if ($id) {
            $infoblock_start = sql_getRow("SELECT * FROM " . $this->elem_table . " WHERE id = " . $id);
            if ($infoblock_start['header_text_radio']) {
                $this->elem_fields['columns']['header_text']['value'] = htmlspecialchars($infoblock_start['header_text']);
                $this->elem_fields['columns']['header_text_area']['value'] = $infoblock_start['header_text'];
            } else $this->elem_fields['columns']['header_text_fck']['value'] = $infoblock_start['header_text'];
        }

        $this->script .= "

        function elem2(name) {
            return $('#tr_fld\\\\[".$this->tabname."\\\\]\\\\[' + name + '\\\\]');
        };

        function elemName2(name) {
            return 'fld[".$this->tabname."][' + name + ']';
        };

        function getFck2(name) {
            name = elemName2(name);
            for(nameFck in CKEDITOR.instances) {
                if(name == nameFck) {
                    return CKEDITOR.instances[name];
                }
            }
        };

        function open_fck_header(name_fck, name_area) {
            var fck = getFck2(name_fck);
            var header_text_fck = $(elem2(name_fck));
            var header_text_area = $(elem2(name_area));

            header_text_fck.children('span').show();
            header_text_area.hide();

            var data = header_text_area.children('textarea').val();
            if (data.length) fck.setData(data);

            fck.container.show();
            fck.updateElement();
        }

        function close_fck_header(name_fck, name_area) {
            var fck = getFck2(name_fck);
            var header_text_fck = $(elem2(name_fck));
            var header_text_area = $(elem2(name_area));
            fck.container.hide();
            fck.updateElement();

            header_text_fck.children('span').hide();
            header_text_area.show();
            header_text_area.children('textarea').css({
                'width'         :   '98%',
                'height'        :   fck.config.height
            });

            var data = fck.getData();
            if (data.length) header_text_area.children('textarea').val(data);
        }

        $(document).ready(function(){
            var header_text_radio   = $(elem2('header_text_radio')).children('input');
            $(header_text_radio).click(function() {
                if($(this).val() == 1) {
                    close_fck_header('header_text_fck', 'header_text_area');
                } else {
                    open_fck_header('header_text_fck', 'header_text_area');
                }
            });

            CKEDITOR.on( 'instanceReady', function( ev )
            {
                " . (isset($infoblock_start) && $infoblock_start['header_text_radio'] ? "close_fck_header" : "open_fck_header") . "('header_text_fck', 'header_text_area');
            });
        });
        ";

        TElems::ElemInit();
    }

    /**
     * Вызывается перед сохранением в базу
     *
     * @param array $fld
     * @return array
     */
    function ElemRedactBefore($fld) {
        $fld = parent::ElemRedactBefore($fld);
        $fld['header_text'] =  ($fld['header_text_radio']) ? $fld['header_text_area'] : $fld['header_text_fck'];
        return $fld;
    }

    function getRootId() {
        return domainRootID();
    }

    function infoblocks_positions() {
        global $settings;
        return $settings['infoblocks_positions'];
    }
}