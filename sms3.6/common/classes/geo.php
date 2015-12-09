<?php
class Geo
{
    var $table = 'stat_ipgeobase';
    var $dbname = 'stat';

    public function __construct($options = null) {

        $this->dirname = dirname(__file__);

        if (isset($options['dbname'])) $this->dbname = $options['dbname'];
        if (isset($options['tablename'])) $this->table = $options['tablename'];

        if (!sql_getRows("SHOW TABLES FROM `{$this->dbname}` LIKE '{$this->table}'")) {
            sql_query(
                "
                    CREATE TABLE IF NOT EXISTS `{$this->dbname}`.`{$this->table}` (
                      `from_ip` bigint(20) NOT NULL,
                      `to_ip` bigint(20) NOT NULL,
                      `country` varchar(255) NOT NULL,
                      `city` varchar(255) NOT NULL,
                      `region` varchar(255) NOT NULL,
                      `district` varchar(255) NOT NULL,
                      `lat` varchar(32) NOT NULL,
                      `lng` varchar(32) NOT NULL,
                      `udate` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
                      PRIMARY KEY  (`from_ip`,`to_ip`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=cp1251;
                "
            );
        }

        // ip
        if (!isset($options['ip']) || !$this->is_valid_ip($options['ip'])) {
            $this->ip = $this->get_ip();
        } elseif ($this->is_valid_ip($options['ip'])) {
            $this->ip = $options['ip'];
        }
        // ���������
        if (isset($options['charset']) && $options['charset'] && $options['charset'] != 'windows-1251') {
            $this->charset = $options['charset'];
        }
    }

    /*
     * ���������� �������� ������������� ip-������
     * @param string - ip-�����
     * @return int
     */
    private function numeric_ip($ip) {
        $ip_nums = explode('.', $ip);
        $val = 0;
        foreach ($ip_nums as $num) {
            $val = $val * 256 + intval(trim($num));
        }
        return $val;
    }

    /**
     * ������� ��������� ���������� �������� �� ����������� ������� ������ �� ip
     *
     * @param string  - ���� �������. ���� ���������� ���������� ��������.
     * ���� ����� ���� ������ 'inetnum', 'country', 'city', 'region', 'district', 'lat', 'lng'
     * @param boolean - ������������� ������� ������ � ���� ��� ���
     * ���� true, �� � ������� ipgeobase ����� �������� ������ �� ip � ��������� ������� �� ipgeobase ����������� �� �����.
     * ���� false, �� ������ ��������� ����� ������������� � ipgeobase
     *
     * @return array OR string - ������������� ������� ����������� ������ �������.
     */
    function get_value($key = false, $from_db = true) {
        $key_array = array('inetnum', 'country', 'city', 'region', 'district', 'lat', 'lng');
        if (!in_array($key, $key_array)) {
            $key = false;
        }

        $data = null;
        // ���� ���������� ����, �� ������� ������
        if ($from_db) {
            $numeric_ip = $this->numeric_ip($this->ip);
            $data = sql_getRow(
                "SELECT * FROM `{$this->dbname}`.`{$this->table}` WHERE (from_ip>=$numeric_ip AND to_ip<=$numeric_ip) LIMIT 1"
            );
        }

        if (!$data) {
            $data = $this->get_geobase_data();
            $inetnum = explode('-', $data['inetnum']);
            if ($data && $data['country']) {
                sql_insert("`{$this->dbname}`.`{$this->table}`",
                    array(
                        'from_ip' => $this->numeric_ip($inetnum[0]),
                        'to_ip' => $this->numeric_ip($inetnum[1]),
                        'country' => $data['country'],
                        'city' => $data['city'],
                        'region' => $data['region'],
                        'district' => $data['district'],
                        'lat' => $data['lat'],
                        'lng' => $data['lng'],
                    )
                );
            }
        }
        if ($key) {
            return $data[$key]; // ���� ������ ����, ���������� ������ � ������� �������
        } else {
            return $data; // ����� ���������� ������ �� ����� �������
        }
    }

    /**
     * ������� �������� ������ �� ip.
     *
     * @return array - ���������� ������ � �������
     */
    function get_geobase_data() {
        // �������� ������ �� ip
        //$link = 'http://ipgeobase.ru:7020/geo?ip=' . $this->ip;
        $link = 'http://ipgeobase.rusoft.ru/geo?ip=' . $this->ip;

        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $link);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (PHP/CURL)');
            $string = curl_exec($ch);
        } else {
            $string = file_get_contents($link);
        }
        // ��� 0.5�, ����� �� ����������� ������ ipgeobase
        usleep(500000);

        // ���� ������� ��������� �������� �� windows-1251, �������� ���������
        if ($this->charset) {
            $string = iconv('windows-1251', $this->charset, $string);
        }

        $data = $this->parse_string($string);

        return $data;
    }

    /**
     * ������� ������ ���������� � XML ������ � ������, ���� �� ������� �� ����������� ���������� Simplexml
     *
     * @return array - ���������� ������ � �������
     */

    function parse_string($string) {
        $pa['inetnum'] = '#<inetnum>(.*)</inetnum>#is';
        $pa['country'] = '#<country>(.*)</country>#is';
        $pa['city'] = '#<city>(.*)</city>#is';
        $pa['region'] = '#<region>(.*)</region>#is';
        $pa['district'] = '#<district>(.*)</district>#is';
        $pa['lat'] = '#<lat>(.*)</lat>#is';
        $pa['lng'] = '#<lng>(.*)</lng>#is';
        $data = array();
        foreach ($pa as $key => $pattern) {
            if (preg_match($pattern, $string, $out)) {
                $data[$key] = trim($out[1]);
            }
        }
        return $data;
    }

    /**
     * ������� ���������� ip ����� �� ����������� ������� $_SERVER
     * ip ������ ����������� ������� � �������������, ��� ����������� ���������� ������������� ������
     *
     * @return ip-�����
     */
    function get_ip() {
        $ip = false;
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipa[] = trim(strtok($_SERVER['HTTP_X_FORWARDED_FOR'], ','));
        }

        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipa[] = $_SERVER['HTTP_CLIENT_IP'];
        }

        if (isset($_SERVER['REMOTE_ADDR'])) {
            $ipa[] = $_SERVER['REMOTE_ADDR'];
        }

        if (isset($_SERVER['HTTP_X_REAL_IP'])) {
            $ipa[] = $_SERVER['HTTP_X_REAL_IP'];
        }

        // ��������� ip-������ �� ���������� ������� � �������������.
        foreach ($ipa as $ips) {
            //  ���� ip �������� �������� ����, ��������� ip ����� � ���������� ���
            if ($this->is_valid_ip($ips)) {
                $ip = $ips;
                break;
            }
        }
        return $ip;

    }

    /**
     * ������� ��� �������� ���������� ip ������
     *
     * @param ip ����� � ������� 1.2.3.4
     *
     * @return bolean : true - ���� ip ��������, ����� false
     */
    function is_valid_ip($ip = null) {
        if (preg_match("#^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$#", $ip)) {
            return true;
        } // ���� ip-����� �������� ��� ���������� ���������, ���������� true

        return false; // ����� ���������� false
    }


}

