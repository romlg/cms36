<?php

/**
 * Класс для отправки уведомлений на основе событий
 */

class RusoftNotify
{

    protected $_table_events = 'notify_events';
    protected $_table_sent = 'notify_log';
    private $_errors = array();

    /**
     * Отправка уведомления для события
     * @param string $event_name - кодовое название события
     * @param string $emails - на какие адреса отправлять (через запятую); если пусто, возьмутся из настроек события
     * @param array $data - данные для передачи в шаблон
     * @param string $attach - прикладываемый файл
     * @return bool
     */
    function Send($event_name, $emails = '', $data = array(), $attach = '') {

        $event = sql_getRow("SELECT * FROM {$this->_table_events} WHERE event='" . mysql_real_escape_string($event_name) . "'");
        if (!$event) return $this->setError("В таблице {$this->_table_events} нет события {$event_name}");

        if (!$emails) $emails = $event['mails'];
        if (!$emails) return $this->setError("Пустой список адресов для отправки");

        /**
         * @var TRusoft_View $view
         */
        $view = &Registry::get('TRusoft_View');

        // Парсинг темы и текста письма
        $text = $this->parse($event['template'], $data);
        if (!$text) return $this->setError("Пустой текст письма");
        $subject = $this->parse($event['subject'], $data);
        if (!$subject) return $this->setError("Пустая тема письма");

        // Отправка
        $res = $this->sendNotify($emails, $subject, $text, $event['replyto'], $attach);

        if ($res !== true) {
            return $this->setError("Ошибка отправки сообщения: " . $res);
        }

        $this->addToSent($event_name, $emails, $subject, $text, $attach);
        return true;
    }

    /**
     * Парсинг шаблона
     * @param $template
     * @param $data
     * @return string
     */
    private function parse($template, $data) {
        /**
         * @var TRusoft_View $view
         */
        $view = &Registry::get('TRusoft_View');

        $template = str_replace(array("{", "}"), array("<?=\$this->", "?>"), $template);
        $template = html_entity_decode($template);
        $view->assign($data);

        $view->addScriptPath(CACHE_DATA_DIR);
        $text_file = tempnam(CACHE_DATA_DIR, "notify_body");

        $fp = fopen($text_file, "w");
        if (!$fp) {
            $this->setError("Не доступен для записи файл " . $text_file);
            return false;
        }

        fwrite($fp, $template);
        fclose($fp);

        try {
            $ret = $view->render(basename($text_file));
        } catch (Exception $e) {
            $this->setError($e->getMessage() . " (проверьте права на запись для папки " . CACHE_DATA_DIR . ")");
            return false;
        }
        return $ret;
    }

    /**
     * Непосредственно отправка
     * @param string $to - кому (список адресов через запятую)
     * @param string $subject - тема письма
     * @param string $body - текст письма
     * @param string string $from - отправитель
     * @param string string $attach - путь до файла для аттача
     * @return true|string
     */
    public function sendNotify($to, $subject, $body, $from = '', $attach = '') {
        include_once 'phpmailer/class.phpmailer.php';

        $mail = new PHPMailer();

        $mail->From = $from ? $from : '';
        $mail->Mailer = 'mail';

        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags(str_replace("<br>", "\r\n", $body));

        if ($attach) {
            $mail->AddAttachment($attach);
        }

        $to = explode(',', $to);
        foreach ($to as $email) {
            $mail->AddAddress(trim($email));
        }

        $res = $mail->Send();
        if ($res === true) return $res;

        return $mail->ErrorInfo;
    }

    /**
     * Сохранение в отправленные
     * @param $event
     * @param $to
     * @param $subject
     * @param $body
     * @param $attach
     * @return bool
     */
    private function addToSent($event, $to, $subject, $body, $attach) {
        $data = array(
            'event' => $event,
            'email' => $to,
            'subject' => $subject,
            'text' => $body,
            'attach' => $attach,
            'date' => date('Y-m-d H:i:s')
        );
        return sql_insert($this->_table_sent, $data);
    }

    /**
     * Установка ошибки
     * @param $err
     * @return bool
     */
    public function setError($err) {
        $this->_errors[] = $err;
        return false;
    }

    /**
     * Возврат ошибок
     * @return array
     */
    public function getErrors() {
        return $this->_errors;
    }
}