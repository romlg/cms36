<?php

/**
 * ����� ��� �������� ����������� �� ������ �������
 */

class RusoftNotify
{

    protected $_table_events = 'notify_events';
    protected $_table_sent = 'notify_log';
    private $_errors = array();

    /**
     * �������� ����������� ��� �������
     * @param string $event_name - ������� �������� �������
     * @param string $emails - �� ����� ������ ���������� (����� �������); ���� �����, ��������� �� �������� �������
     * @param array $data - ������ ��� �������� � ������
     * @param string $attach - �������������� ����
     * @return bool
     */
    function Send($event_name, $emails = '', $data = array(), $attach = '') {

        $event = sql_getRow("SELECT * FROM {$this->_table_events} WHERE event='" . mysql_real_escape_string($event_name) . "'");
        if (!$event) return $this->setError("� ������� {$this->_table_events} ��� ������� {$event_name}");

        if (!$emails) $emails = $event['mails'];
        if (!$emails) return $this->setError("������ ������ ������� ��� ��������");

        /**
         * @var TRusoft_View $view
         */
        $view = &Registry::get('TRusoft_View');

        // ������� ���� � ������ ������
        $text = $this->parse($event['template'], $data);
        if (!$text) return $this->setError("������ ����� ������");
        $subject = $this->parse($event['subject'], $data);
        if (!$subject) return $this->setError("������ ���� ������");

        // ��������
        $res = $this->sendNotify($emails, $subject, $text, $event['replyto'], $attach);

        if ($res !== true) {
            return $this->setError("������ �������� ���������: " . $res);
        }

        $this->addToSent($event_name, $emails, $subject, $text, $attach);
        return true;
    }

    /**
     * ������� �������
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
            $this->setError("�� �������� ��� ������ ���� " . $text_file);
            return false;
        }

        fwrite($fp, $template);
        fclose($fp);

        try {
            $ret = $view->render(basename($text_file));
        } catch (Exception $e) {
            $this->setError($e->getMessage() . " (��������� ����� �� ������ ��� ����� " . CACHE_DATA_DIR . ")");
            return false;
        }
        return $ret;
    }

    /**
     * ��������������� ��������
     * @param string $to - ���� (������ ������� ����� �������)
     * @param string $subject - ���� ������
     * @param string $body - ����� ������
     * @param string string $from - �����������
     * @param string string $attach - ���� �� ����� ��� ������
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
     * ���������� � ������������
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
     * ��������� ������
     * @param $err
     * @return bool
     */
    public function setError($err) {
        $this->_errors[] = $err;
        return false;
    }

    /**
     * ������� ������
     * @return array
     */
    public function getErrors() {
        return $this->_errors;
    }
}