<?php
require_once elem(OBJECT_EDITOR_MODULE . '/elems');

class TMainBaseElement extends TElems
{

    var $elem_name = "elem_main";
    var $elem_table = "elem_gallery";
    var $elem_type = "single";
    var $elem_str = array(
        'image_large' => array('Большая картинка', 'Large image',),
        'image_small' => array('Маленькая картинка', 'Small image',),
        'name' => array('Название', 'Title',),
        'visible' => array('Показывать', 'Visible',),
    );
    //поля для выборки из базы элема
    var $elem_fields = array(
        'columns' => array(
            'id' => array(
                'type' => 'hidden',
            ),
            'name' => array(
                'type' => 'text',
            ),
            'image_small' => array(
                'type' => 'input_image',
                'display' => array(
                    'size' => array('150', '150'),
                ),
            ),
            'image_large' => array(
                'type' => 'input_image',
                'display' => array(
                    'friend' => 'image_small',
                    'size' => array('400', '400'),
                ),
            ),
            'visible' => array(
                'type' => 'checkbox',
            ),
            'priority' => array(
                'type' => 'hidden',
            ),
        ),
        'id_field' => 'id',
        'folder' => 'gallery'
    );
    var $elem_where = "";
    var $script = "";
    var $elem_req_fields = array('name', 'image_small',);

    function ElemInit() {
        global $watermark_img;
        if (isset($watermark_img) && !empty($watermark_img)) {
            $this->elem_fields['columns']['watermark'] = array('type' => 'words', 'value' => '
            <div class="checkBox">
                <input type="checkbox" value="1" id="checkbox_water" class="check" name="watermark"><label class="check" for="checkbox_water">Наложить водяной знак</label>
            </div>
            ');
        }
        parent::ElemInit();
    }

    /**
     * Накладывание водяного знака
     * @param array $fld
     * @param int $id
     * @return array|bool
     */
    function ElemRedactAfter($fld, $id) {

        $id = $id ? $id : (int)$_POST['id'];
        if (!$id) {
            return true;
        }

        global $watermark_img, $watermark_position;
        $watermark = (int)get('watermark', 0, 'p');

        if (isset($watermark_img) && !empty($watermark_img) && $watermark) {

            if (!getimagesize($watermark_img)) {
                return (array('error' => "Не найден водяной знак " . $watermark_img));
            }

            $row = sql_getRow("SELECT * FROM {$this->elem_table} WHERE id={$id}");
            if (!$row) {
                return (array('error' => "Неправильный идентификатор галереи"));
            }

            if ($row['image_large']) {
                $file = '..' . $row['image_large'];
                $file = urldecode($file);

                $wmark_info = getimagesize($watermark_img);
                $file_info = getimagesize($file);

                if ($wmark_info[0] > $file_info[0]) {
                    return (array('error' => "Ширина изображения меньше чем ширина водяного знака"));
                }
                if ($wmark_info[1] > $file_info[1]) {
                    return (array('error' => "Высота изображения меньше чем высота водяного знака"));
                }

                $copy = file_getUniName($file);
                if (!copy($file, $copy)) {
                    return (array('error' => "Ошибка копирования из " . $file . " в " . $copy));
                }

                include_once ENGINE_VERSION . '/admin/modules/fm2/watermark.php';

                $handle = new RWatermark($file_info[2], $copy);

                if (!isset($watermark_position)) $watermark_position = "CM";
                $handle->SetPosition($watermark_position);

                $handle->SetTransparentColor(255, 0, 255);
                $handle->SetTransparency(100);

                switch ($wmark_info[2]) {
                    case '1':
                        $handle->AddWatermark(FILE_GIF, $watermark_img);
                        break;
                    case '2':
                        $handle->AddWatermark(FILE_JPEG, $watermark_img);
                        break;
                    case '3':
                        $handle->AddWatermark(FILE_PNG, $watermark_img);
                        break;
                }

                if ($file_info[2] == 1) {
                    imagegif($handle->marked_image, $copy);
                } elseif ($file_info[2] == 2) {
                    // Определяем примерно, с каким качеством сохранять картинки
                    $size = filesize($copy);
                    if ($size <= '10000') {
                        $quality = '90';
                    } else if ($size > '10000' && $size <= '30000') {
                        $quality = '90';
                    } else $quality = '85';
                    imagejpeg($handle->marked_image, $copy, $quality);
                } elseif ($file_info[2] == 3) {
                    imagepng($handle->marked_image, $copy);
                }
                $handle->Destroy();

                sql_updateId($this->elem_table, array('image_large' => substr($copy, 2)), $id);
            }
        }
        return true;
    }
}