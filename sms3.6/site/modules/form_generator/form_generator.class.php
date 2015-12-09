<?php

include_once PATH_COMMON_CLASSES . 'Zend/Form.php';
include_once PATH_COMMON_CLASSES . 'Zend/Translate.php';
include_once PATH_COMMON_CLASSES . 'Zend/Validate/Abstract.php';
include_once PATH_COMMON_CLASSES . 'Zend/Form/Decorator/Abstract.php';
include_once PATH_COMMON_CLASSES . 'Zend/Config/Json.php';

/**
 * Генератор форм
 */
class TForm_generator
{
    var $table = 'elem_form';
    var $table_elems = 'elem_form_elems';
    var $table_elem_values = 'elem_form_values';
    var $form_id_field = 'form_id';
    var $data_var = 'fld';
    var $method = 'POST';
    var $zend_form;
    var $messages;

    function TForm_generator() {
        $this->zend_form = new Zend_Form();

        // Подключение сообщений об ошибках на кириллице
        $this->setTranslatorForSite();
    }

    function setTranslatorForSite($lang) {
        $lang = (!empty($lang)) ? $lang : 'ru';
        $translator = new Zend_Translate(
            'array',
                dirname(__FILE__) . '/../../../common/classes/Zend/_resources/languages',
            $lang,
            array('scan' => Zend_Translate::LOCALE_DIRECTORY)
        );
        Zend_Validate_Abstract::setDefaultTranslator($translator);
    }

    /**
     * Вызывается из common.cfg.php
     * @return bool|string
     */
    function show() {
        $page = & Registry::get('TPage');
        $form = sql_getRow('SELECT * FROM ' . $this->table . ' WHERE pid=' . $page->content['id'] . ' AND visible > 0');
        if (!$form) return;

        return $this->main($form[$this->form_id_field],array(),$form['lang']);
    }

    /**
     * Основная функция построения формы
     * @param $form_id - ID формы
     * @param $params - параметры для построение формы
     * @return bool|string
     */
    function main($form_id,$params=array(),$lang="ru") {
        if (isset($params['counter']))
            $form_counter = $params['counter'];
        else {
            static $form_counter;
            if (!isset($form_counter)) $form_counter = 0;
            $form_counter++;
        }

        $rows = sql_getRows('SELECT * FROM ' . $this->table_elems . ' WHERE pid=' . $form_id);
        if (!$rows) return false;

        $page = & Registry::get('TPage');
        $script = isset($params['script']) ? $params['script'] : '';
        $action = isset($params['action']) ? $params['action'] : '/' . $page->content['href'];

        $elements = $this->formatElements($rows);

        // Начинаем собирать с помощью Zend_Form
        $this->zend_form->setAction($action)
                ->setMethod($this->method)
                ->setAttrib('accept-charset', 'windows-1251')
                ->setAttrib('enctype', 'multipart/form-data')
                ->setAttrib('id', 'form_counter_' . $form_counter);

        // Генерация элементов формы
        $this->generateElements($elements, $lang);

        // Проверка - пришли ли данные с формы
        if (!empty($_POST)) {
            if ($this->zend_form->isValid($_POST)) {
                $values = $this->zend_form->getValues();

                $form = sql_getRow("SELECT * FROM " . $this->table . " WHERE " . $this->form_id_field . "={$form_id}");

                if (!empty($form['db_table'])) {
                    // Надо записать в БД
                    $id = $this->saveInDB($values, $elements, $form['db_table']);
                    if (!is_int($id)) {
                        redirect($page->content['href'] . '?message=msg_fail');
                    }
                }

                if (!empty($form['email'])) {
                    if (!$this->sendMail($values, $elements, $form)) {
                        redirect($page->content['href'] . '?message=msg_fail');
                    }
                }

                redirect($page->content['href'] . '?message=msg_send_email');
            }
            else {
                $script .= '<script type="text/javascript">
                $(document).ready(function (){
                    var header = $("header");
                    var offsetscroll = 0;
                    if (header.length && header.css("position") == "fixed") {
                        offsetscroll = header.height() + 30;
                    }
                    $("html, body").animate({
                        scrollTop: $("#form_counter_' . $form_counter . '").find("p.error:first").prev().prev().offset().top - offsetscroll
                    }, 200);
                });
                </script>';
            }
        }

        /**
         * @var TRusoft_View $view
         */
        $view = &Registry::get('TRusoft_View');
        return iconv('utf-8', 'windows-1251', $this->zend_form->render($view)) . $script;
    }

    /**
     * Сохранение данных с формы в БД
     * @param $fld - данные с формы
     * @param $elements - данные о полях
     * @param $db_table - название таблицы для сохранения
     * @return bool
     */
    function saveInDB($fld, $elements, $db_table) {
        $values = array();
        foreach ($fld as $k => $v) {
            $_key = substr($k, strlen($this->data_var));
            if (!empty($elements[$_key]['db_field'])) {
                $values[$_key] = iconv('utf-8', 'windows-1251', $v);
            }
        }
        if (!$values) return 1;

        return sql_insert($db_table, $values);
    }

    /**
     * Отправка письма с данными формы
     * @param $values
     * @param $elements
     * @param $form
     * @return bool
     */
    function sendMail($values, $elements, $form) {
        include_once 'phpmailer/class.phpmailer.php';
        $mail = new PHPMailer();

        $to = explode(',', str_replace(' ', '', $form['email']));

        $mail->From = $form['from_mail'];
        $mail->Mailer = 'mail';
        $mail->Subject = 'Форма ' . $form['name'];

        // Аттач файлов
        $attach = array();
        foreach ($elements as $k => $v) {
            if ($v['type'] == 'file') {
                $filename = TEMP_FILE_PATH . '/' . $values[$this->data_var . $k];
                if (is_file($filename)) {
                    $attach[] = array(
                        'name' => $filename,
                        'size' => filesize($filename),
                    );
                    $mail->AddAttachment($filename);
                }
            }
        }

        $body = '';
        foreach ($values as $key => $val) {

            $id = substr($key, strlen($this->data_var));
            $element = $elements[$id];
            if (!isset($element) || $element['type'] == 'captcha') continue;

            if (isset($element['options'])) {
                $value = array();
                if (!is_array($val)) $val = array($val);
                foreach ($val as $v) $value[] = isset($element['options'][$v]) ? $element['options'][$v] : $v;
                $value = implode(', ', $value);
            }
            else {
                $value = str_replace(array("\r\n", "\n", "\r"), "<br />\n", strip_tags($val));
                $value = iconv('utf-8', 'windows-1251', $value);
            }

            if ($element['type'] == 'headline') {
                $body .= '<span style="color:gray">=== ' . strip_tags($element['text']) . " ===</span><br />\n";
            }
            else {
                if (!empty($value)) {
                    $body .= '<span style="color:gray">&nbsp;&nbsp;&nbsp;' . wordwrap(strip_tags($element['text']), 75, " <br />\n&nbsp;&nbsp;&nbsp;") . '</span><br /><b>' . wordwrap($value, 70, "<br />\n") . '</b><br /><br />';
                }
            }
        }
        $text_body = strip_tags(str_replace('<br />', "\r\n", $body));

        $mail->Body = $body;
        $mail->AltBody = $text_body;
        foreach ($to as $k => $v) $mail->AddAddress($v);

        $result = $mail->Send();

        if (!sql_getRow("SHOW TABLES LIKE 'forms_sent'")) {
            sql_query(
                "CREATE TABLE  `forms_sent` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                `date` DATETIME NOT NULL ,
                `email` VARCHAR( 500 ) NOT NULL ,
                `subject` VARCHAR( 255 ) NOT NULL ,
                `text` TEXT NOT NULL ,
                `attach` TEXT NOT NULL ,
                `page_url` VARCHAR( 255 ) NOT NULL ,
                `page_name` VARCHAR( 255 ) NOT NULL ,
                `result` TINYINT( 1 ) NOT NULL
                ) ENGINE = INNODB"
            );
        };

        // Сохраняем письмо в соответствующую таблицу
        $pid = end($this->getPidsByUrl($_SERVER['HTTP_REFERER']));
        sql_insert('forms_sent', array(
                                      'date' => date('Y-m-d H:i:s'),
                                      'email' => implode(',',$to),
                                      'subject' => $mail->Subject,
                                      'text' => $body,
                                      'attach' => serialize($attach),
                                      'page_url' => $_SERVER['HTTP_REFERER'],
                                      'page_name' => $pid['name'],
                                      'result' => $result,
                                 ));

        return $result;
    }

    function getPidsByUrl($currentDir) {
        $currentDir = str_replace('http://','',$currentDir);
        $currentDir = explode('?',$currentDir);
        $currentDir = $currentDir[0];

        $currentArr = explode("/", $currentDir);
        $ret = array();
        foreach ($currentArr as $k=>$p) {
            if ($k == 0 || $p == lang()) continue;
            if (!empty($p)) $ret[] = $p;
        }
        $currentDir = '/' . implode('/', $ret);

        $tree = & Registry::get('TTreeUtils');
        $tree->setRootId(ROOT_ID);

        return $tree->getPidsByUrl($currentDir);
    }

    /**
     * Форматирование списка элементов формы
     * @param $rows
     * @return array
     */
    function formatElements($rows) {
        $elements = array();
        foreach ($rows as $k => $v) {
            $_key = !empty($v['db_field']) ? $v['db_field'] : $k;
            $elements[$_key] = array(
                'name' => $_key,
                'type' => $v['type'] == 'input' ? 'text' : $v['type'],
                'text' => $v['text'],
                'key' => $v['key'],
                'req' => $v['req'],
                'check' => $v['check'],
                'db_field' => $v['db_field'],
                'placeholder' => @$v['placeholder'],
            );
            if (in_array($v['type'], array('select', 'radio', 'checkbox'))) {
                $temp = sql_getRows('SELECT * FROM ' . $this->table_elem_values . ' WHERE pid=' . $v['id']);
                foreach ($temp as $key => $value) {
                    $elements[$_key]['options'][$value['value']] = $value['text'];
                }
            }
        }

        // Кнопка submit
        $submit_title = '';
        $columns = sql_getRows("SHOW COLUMNS FROM `" . $this->table . "`", true);
        if (isset($columns['submit_title'])) {
            $submit_title = sql_getValue("SELECT submit_title FROM `" . $this->table . "` WHERE " . $this->form_id_field . "=" . (int)$v['pid']);
        }
        if (!$submit_title) {
            $page = & Registry::get('TPage');
            $submit_title = $page->tpl->get_config_vars('form_send');
        }

        $elements['send'] = array(
            'name' => 'send',
            'type' => 'submit',
            'text' => $submit_title,
        );
        return $elements;
    }

    /**
     * Генерация элементов формы
     * @param $elements
     * @param $lang
     * @return bool
     */
    function generateElements($elements, $lang) {
        if (empty($lang)) $lang = "ru";
        if (!$elements) return false;

        $input_elements = array(
            'select' => 'Select',
            'textarea' => 'Textarea',
            'checkbox' => 'MultiCheckbox',
            'radio' => 'Radio',
            'file' => 'File',
            'hidden' => 'Hidden',
            'text' => 'Text',
            'password' => 'Password',
            'button' => 'Button',
            'captcha' => 'Captcha',
            'submit' => 'Submit',
            'reset' => 'Reset',
            'headline' => 'Headline',
        );

        $langs_elements = array(
            'ru' => array(
                'file_button_title' => 'Обзор..',
            ),
            'en' => array(
                'file_button_title' => 'Browse..'
            ),
        );

        $captcha_key = "";
        $button_keys = $file_keys = $radio_keys = array();
        $headlines = array();

        foreach ($elements as $ekey => $element) {

            if (!in_array($element['type'], array_keys($input_elements))) continue;

            $name = $this->data_var . ($element['name'] ? $element['name'] : $ekey);

            if (in_array($element['type'], array('button', 'submit', 'reset'))) $button_keys[] = $name;
            if (in_array($element['type'], array('radio', 'checkbox'))) $radio_keys[] = $name;
            if (in_array($element['type'], array('file'))) $file_keys[] = $name;
            if (in_array($element['type'], array('headline'))) $headlines[] = array('name' => $name, 'value' => iconv('windows-1251', 'utf-8', $element['text']));

            $options = array(
                'label' => isset($element['text']) ? iconv('windows-1251', 'utf-8', $element['text']) : '',
                'required' => isset($element['req']) ? $element['req'] : 0,
                'filters' => array('StringTrim'),
                'value' => isset($element['value']) ? iconv('windows-1251', 'utf-8', $element['value']) : '',
            );
            if (isset($element['placeholder']) && $element['placeholder']) {
                $options['placeholder'] = iconv('windows-1251', 'utf-8', $element['placeholder']);
            }

            // Дополнительные опции
            if (isset($element['options']) && $element['options']) foreach ($element['options'] as $k=>$v) {
                $element['options'][$k] = iconv('windows-1251', 'utf-8', $v);
            }
            switch ($element['type']) {
                case 'text':
                case 'textarea':
                case 'password':
                case 'captcha':
                    $options['class'] = 'text';
                    break;
                case 'checkbox' :
                case 'select' :
                    $options['multiOptions'] = $element['options'];
                    $options['label_class'] = 'check';
                    $options['class'] = 'check';
                    break;
                case 'radio' :
                    $options['multiOptions'] = $element['options'];
                    $options['label_class'] = 'check radio';
                    $options['class'] = 'check';
                    break;
                case 'headline' :
                    unset($options['label']);
                    break;
                case 'file' :
                    $options['button_title'] = iconv('windows-1251', 'utf-8', $langs_elements[$lang]['file_button_title']);
                    break;
            }

            // Добавление валидаторов
            if (isset($element['check']) && $element['check']) {
                switch ($element['check']) {
                    case 'email':
                        $options['validators'] = array(array('EmailAddress'));
                        break;
                    case 'phone':
                        $options['validators'] = array(
                            array('PhoneNumber', false)
                        );
                        break;
                    case 'zip':
                        $options['validators'] = array(
                            array('stringLength', false, array(6, 6)),
                            'Digits'
                        );
                        break;
                    case 'file':
                        $options['validators'] = array(
                            array('Size', false, '3MB'),
                            array('Count', false, '1'),
                        );
                        break;
                    case 'captcha':
                        $options['captcha'] = array(
                            'captcha' => 'ImageRusoft',
                            'wordLen' => 5,
                            'timeout' => 300,
                            'width' => 120,
                            'height' => 55,
                            'font' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'arial.ttf',
                        );
                        $captcha_key = $name;
                        break;
                }
            }
            $this->zend_form->addElement($input_elements[$element['type']], $name, $options);
        }

        $this->zend_form->setElementDecorators(array(
            'ViewHelper',
            'Description',
            'Errors',
            'FormErrors',
            'Captcha',
            array('Label', array('requiredSuffix' => '<span>*</span>', 'escape' => false)),
        ));

        $this->zend_form->setDecorators(array(
            'FormElements',
            'Form'
        ));

        if ($captcha_key) {
            $this->zend_form->getElement($captcha_key)->removeDecorator("viewhelper");
        }

        if ($button_keys) {
            foreach ($button_keys as $button_key) {
                $this->zend_form->getElement($button_key)->removeDecorator("Label");
            }
        }

        if ($radio_keys) {
            foreach ($radio_keys as $button_key) {
                $this->zend_form->getElement($button_key)->getDecorator("Label")->setOption('class', 'checkBoxTitle');
            }
        }

        if ($file_keys) {
            foreach ($file_keys as $file_key) {
                $this->zend_form->getElement($file_key)->setDecorators(array(
                    "File",
                    "Errors",
                    array('Label', array('requiredSuffix' => '<span>*</span>', 'escape' => false))
                ));
                $this->zend_form->getElement($file_key)->setMaxFileSize('10240000')
                        ->setDestination(TEMP_FILE_PATH);
            }
        }

        if ($headlines) {
            foreach ($headlines as $line) {
                $config = new Zend_Config_Json(json_encode(array('DefaultValue' => $line['value'])));
                $this->zend_form->getElement($line['name'])->setValue($line['value'])
                        ->setConfig($config);
            }
        }

        return true;
    }

}