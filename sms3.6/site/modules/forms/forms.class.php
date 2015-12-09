<?php

if (!class_exists('TForm_generator')) {
    include_once 'sms3.6/site/modules/form_generator/form_generator.class.php';
}

/**
 * Модуль "Формы"
 */
class TForms extends TForm_generator
{
    var $table = 'forms';
    var $table_elems = 'forms_elems';
    var $table_elem_values = 'forms_values';
    var $form_id_field = 'id';
    var $error = false;

    function parse_template($tmpl_name, $data) {
        global $site_domains, $domain;
        /**
         * @var TRusoft_View $view
         */
        $view = & Registry::get('TRusoft_View');
        // проверим наличие кастомного шаблона
        // если нет, ищем в общей папке шаблонов
        $path_template = 'templates/'.$site_domains[$domain]['templates'].'/'.$tmpl_name;
        if ($view->template_exists($path_template)){
            $template = $path_template;
        } else {
            $template = $tmpl_name;
        }
        $view->assign($data);
        $ret = $view->render($template);
        return $ret;
    }

    /**
     * Основная функция построения формы
     * @param $form_id - ID формы
     * @param $params  - параметры формы
     *
     * @return bool|string
     */
    function main($form_id, $params = array(), $lang="ru") {
        $this->setTranslatorForSite($lang);
        $this->error = false;
        if (isset($params['counter'])) {
            $form_counter = $params['counter'];
        } else {
            static $form_counter;
            if (!isset($form_counter)) {
                $form_counter = 0;
            }
            $form_counter++;
        }

        $rows = sql_getRows('SELECT * FROM ' . $this->table_elems . ' WHERE pid=' . $form_id);
        if (!$rows) {
            return false;
        }

        $page = & Registry::get('TPage');
        $script = isset($params['script']) ? $params['script'] : '';
        $action = isset($params['action']) ? $params['action'] : '/' . $page->content['href'];

        $elements = $this->formatElements($rows, $lang);

        // Начинаем собирать с помощью Zend_Form
        $this->zend_form->setAction($action)
            ->setMethod($this->method)
            ->setAttrib('accept-charset', 'windows-1251')
            ->setAttrib('enctype', 'multipart/form-data')
            ->setAttrib('id', 'form_counter_' . $form_counter);

        if ($params['target'])
            $this->zend_form->setAttrib('target', $params['target']);

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
                        $this->error = 1;
                    }
                }

                if (!empty($form['email'])) {
                    if (!$this->sendMail($values, $elements, $form)) {
                        $this->error = 1;
                    }
                }

            } else {
                $this->error = 0;
            }
        }

        /**
         * @var TRusoft_View $view
         */
        $view = & Registry::get('TRusoft_View');
        return iconv('utf-8', 'windows-1251', $this->zend_form->render($view)) . $script;
    }

    function formsQueryHandler() {

        if (isset($_GET['get_form'])) {

            $hash = mysql_real_escape_string($_GET['get_form']);
            $is_ajax = isset($_GET['form_query']);
            $form = sql_getRow('SELECT * FROM ' . $this->table . ' WHERE hash="' . $hash . '"');
            $form_id = $form[$this->form_id_field];
            $is_popup = isset($_GET['is_popup']);

            if ($form_id) {

                $page = & Registry::get('TPage');
                $params = array(
                    'counter' => $hash,
                    'target' => "target_for_form_$hash",
                    'action'  => $page->content['href']."?get_form=$hash&form_query"
                );

                $form_html = $this->main($form_id, $params, $form['lang']);

                $submit_title = $form['submit_title'];
                if (!$submit_title) {
                    $page = & Registry::get('TPage');
                    $submit_title = $page->tpl->get_config_vars('form_send');
                }

                $form_html = $this->parse_template(
                    'dynamic_form.html', array(
                                              'hash'         => $hash,
                                              'submit_title' => $submit_title,
                                              'form_content' => $form_html,
                                              'form'         => $form,
                                              'ajax'         => $is_ajax,
                                              'can_close'    => $this->error === 1 || $this->error === false,
                                              'has_errors'   => $this->error !== false,
                                              'popup'        => $is_popup
                                         )
                );

                if ($is_ajax) {
                    $json_obj = json_encode(
                        array(
                             'message'    => iconv('windows-1251', 'utf-8', ''),
                             'html'       => iconv('windows-1251', 'utf-8', $form_html),
                             'can_close'  => $this->error === 1 || $this->error === false,
                             'has_errors' => $this->error !== false,
                        )
                    );
                    echo "<script type='text/javascript'>parent.on_form{$hash}_result($json_obj);</script>";
                } else {
                    $form_html = trim(
                        str_replace(
                            array("'", "\n", "\r", "</script>"), array("\\'", " ", " ", "<\\/script>"), $form_html
                        )
                    );
                    $script = "document.write('$form_html')";
                    header("Content-Type: text/javascript");
                    echo $script;
                }
                exit;
            } else {
                header('HTTP/1.0 404 Not Found');
                header('Status: 404 Not Found');
                exit;
            }
        }

        return false;
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

        $view = &Registry::get('TRusoft_View');
        $form_send_email = $view->messages['form_send_email'];
        $mail->From = $form_send_email;
        $mail->FromName = 'Автоматическая система сообщений';
        $mail->Mailer = 'mail';
        $mail->Subject = 'Сообщение с сайта '.date('d.m.Y H:i').' : '.$form['name_site'];

        // Аттач файлов
        $attach = array();
        //проверка существования каталога
        if(!is_dir("./files/mailfiles")) {
            mkdir ("./files/mailfiles");
        }
        $change1 = array('а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я', ' ');
        $change2 = array('a', 'b', 'v', 'g', 'd', 'e', 'jo', 'zh', 'z', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'shh', '', 'y', '', 'je', 'ju', 'ja', '');

        foreach ($elements as $k => $v) {
            if ($v['type'] == 'file') {
                if (function_exists('mb_strtolower')) $latin_name = str_replace($change1, $change2, mb_strtolower($values[$this->data_var . $k]));
                else $latin_name = str_replace($change1, $change2, strtolower($values[$this->data_var . $k]));

                $filename = TEMP_FILE_PATH . '/' . $values[$this->data_var . $k];
                if (is_file($filename)) {
                    rename($filename, "files/mailfiles/".basename($latin_name));
                    $attach[] = array(
                        'name' => "files/mailfiles/".basename($latin_name),
                        'size' => filesize("files/mailfiles/".basename($latin_name)),
                    );
                    $mail->AddAttachment("files/mailfiles/".basename($latin_name));
                }
            }
        }

        $body = 'Здравствуйте, на странице: '.$_SERVER['HTTP_REFERER'].' было создано сообщение: <br /><br />';
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
        $body .= '------------------------------------------------------ <br />'.
                 'Форма была отправлена со следующей страницы: '.$_SERVER['HTTP_REFERER'].'<br />'.
                 'Вы получили данное сообщение, так как Ваш адрес прописан в адресе формы. <br />'.
                 'Отредактировать форму можно в системе администрирования по адресу: http://'.$_SERVER['HTTP_HOST'].'/admin/editor.php?page=forms&id='.$form['id'].' <br />'.
                 'Данное письмо можно также посмотреть в системе администрирования по адресу: http://'.$_SERVER['HTTP_HOST'].'/admin/?page=forms_sent <br />';

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
        $email_id = sql_insert('forms_sent', array(
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
                    $options['class'] = 'text';
                    if ($element['req']) {
                        $options['ng-model'] = $element['ng_model_name'];
                        $options['attribs']['required'] = '';
                    }
                    break;
                case 'captcha':
                    $options['class'] = 'text captcha_fd';
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
                            'font' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'form_generator' . DIRECTORY_SEPARATOR . 'arial.ttf',
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

    /**
     * Форматирование списка элементов формы
     * @param $rows
     * @param $lang
     * @return array
     */
    function formatElements($rows, $lang) {
        $change1 = array('а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я', ' ');
        $change2 = array('a', 'b', 'v', 'g', 'd', 'e', 'jo', 'zh', 'z', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'shh', '', 'y', '', 'je', 'ju', 'ja', '');

        $elements = array();
        foreach ($rows as $k => $v) {
            $_key = !empty($v['db_field']) ? $v['db_field'] : $k;

            if (function_exists('mb_strtolower')) $ng_model_name = str_replace($change1, $change2, mb_strtolower($v['text']));
            else $ng_model_name = str_replace($change1, $change2, strtolower($v['text']));

            $elements[$_key] = array(
                'name' => $_key,
                'type' => $v['type'] == 'input' ? 'text' : $v['type'],
                'text' => $v['text'],
                'key' => $v['key'],
                'req' => $v['req'],
                'check' => $v['check'],
                'db_field' => $v['db_field'],
                'placeholder' => @$v['placeholder'],
//                'ng_model_name' => $ng_model_name,
            );
            if (in_array($v['type'], array('select', 'radio', 'checkbox'))) {
                $temp = sql_getRows('SELECT * FROM ' . $this->table_elem_values . ' WHERE pid=' . $v['id']);
                foreach ($temp as $key => $value) {
                    $elements[$_key]['options'][$value['value']] = $value['text'];
                }
            }
        }

        $langs_elements = array(
            'ru' => array(
                'send' => 'Отправить',
            ),
            'en' => array(
                'send' => 'Send',
            ),
        );

        // Кнопка submit
        $elements['send'] = array(
            'name' => 'send',
            'type' => 'submit',
            'text' => $langs_elements[$lang]['send'],

        );
        return $elements;
    }
}

