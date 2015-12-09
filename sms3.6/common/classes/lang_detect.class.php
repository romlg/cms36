<?php
/* Copyright (c) 2008 ��������� �������

���������� ��� ��������������� ����������� ����� ������ ��� ������������� ������

������ 1.0 ����������� �������, ���������� � ���������� �����

*/


class Lang_Auto_Detect
{

    private $charset = 'windows-1251';

	// �������� ����������
	// ����� �������������� ������
	public $lang = Array('en'=>array('English','http://en.wikipedia.org/wiki/English_language'),
						 'ru'=>array('Russian','http://ru.wikipedia.org/wiki/%D0%A0%D1%83%D1%81%D1%81%D0%BA%D0%B8%D0%B9_%D1%8F%D0%B7%D1%8B%D0%BA'),
						 'ua'=>array('Ukraine','http://uk.wikipedia.org/wiki/%D0%A3%D0%BA%D1%80%D0%B0%D1%97%D0%BD%D1%81%D1%8C%D0%BA%D0%B0_%D0%BC%D0%BE%D0%B2%D0%B0')
						);
	// ����� ���������������, ������� � % ������ ���� �������� �����, ����� �� ��� ���������
	public $detect_range = 75;
	// ������������ �� ������������ ��������� � ���������� ������ ������������ ������
	public $detect_multi_lang = false; // ����  �� �����������
	// ���������� ��� ���������� � �����������
	public $return_all_results = false; // � �������� ���������� ����� ���������
	// ������������ ������������� ������� ������ � ����������
	public $use_rules = false;
	//��������� ������ ������� (������� �������, �� ��������� ����� ��������, ��� ������ ������, ��� �����������)
	public $use_rules_only = false;
	// ��������� ������ ��� ����������� -
	public $use_rules_priory = true; // true - ������� ������������ ����������, false - ���������� ����� ���������
	// ������ ������ ������ ������� ��� �������� ����������?
	public $match_all_rules = false; // ������ ���� ����� = ���
	//������������ % �� �������� ��� ����� ���������� �������� ������� ��������
	public $use_str_len_per_lang = true; // true - ������������ ����� ����� ������ ������������, ��� % �� �������� ��������, false - ��������

	// ����������� ����� ������ ��� ��������������
	public $min_str_len_detect = 50;
	// ��� ����������� ���������� ������������������ ������� ������������ ����� � �������� ��� ���������
	public $max_str_len_detect = 1680; //


	// ��������� ������������ - ������� ��������� ������������ ��� �����������
	private $_langs = array(
					'en'=>array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'),
					'ru'=>array('�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�'),
					'ua'=>array('�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�','�')
					);

	// ������ �������
	// �������  ��� ������� ��� ������, ������� ������� (����� ��� ����)  ������������� ������ ������������� ������
	private $_lang_rules = array(
									'en'=>array('th', 'ir'),
									'ru'=>array('�', '�' ),
									'ua'=>array('�', '�')
								);


	// ����������� ������
    public function __construct()
    {
		return true;
    }


	// ���������� ��������� ������ ��� ���������
	private function _prepare_str($tmp_str = null)
	{
		if ($tmp_str == null) return false; // ���� ������ �� �������� - �����

		$tmp_str = trim($tmp_str);
		$tmp_encoding = $this->detect_encoding($tmp_str);

		if (mb_strlen($tmp_str, $tmp_encoding) > $this->max_str_len_detect)
		{
			//�������� ����� ������, ��� �����������������
			$tmp_str = mb_substr($tmp_str, 0, $this->max_str_len_detect, $tmp_encoding);
		}
		else
			if (mb_strlen($tmp_str, $tmp_encoding) <= $this->min_str_len_detect) return false;

		// ������������ ���������
		$tmp_str = mb_convert_encoding($tmp_str, $this->charset, $tmp_encoding);
		// �������� ��� � ������� ��������
		$tmp_str = mb_strtolower($tmp_str, $this->charset);

		return $tmp_str;
	}

	// ������� ����������� ����� �� ��������
	// ������� ���������� ���������� ����, ������ ����� �������� :)
	private function _detect_from_rules($tmp_str = null)
	{
		if ($tmp_str == null) return false; // ���� ������ �� �������� - �����
		if (!is_array($this->_lang_rules)) return false;

		// ������� ���� ������
		foreach ($this->_lang_rules as $lang_code=>$lang_rules)
		{
			$tmp_freq = 0;

			foreach ($lang_rules as $rule)
			{
				$tmp_term = mb_substr_count($tmp_str, $rule);

				if ($tmp_term > 1) // �� ���� ������ � ����� 1 ��� ����� ���
				{
					$tmp_freq++; // �������� ������� �������� �����, ������� � ���� ������ ����
				}

				// ������ ��������
				if ($this->match_all_rules === true)
				{
					// ����� ���������� ���� ������
					if ($tmp_freq == count($lang_rules)) return $lang_code;
				}
				else
					{
						// ���������� ������
						if ($tmp_freq > 0) return $lang_code;
					}
			}
		}

		return false;
	}

	// ������� ����������� ����� �� �������
	private function _detect_from_tables($tmp_str = null)
	{
		if ($tmp_str == null) return false; // ���� ������ �� �������� - �����

		//�� ��� ������ ����� ���������� ������ ��� ���������
		// ���������� ��� ����� � ��� ������� ��������� �����������
		$lang_res = array();

		foreach ($this->lang as $lang_code=>$lang_name)
		{
			$lang_res[$lang_code] = 0; //�� ��������� 0, �� ���� �� ���� ����

			$tmp_freq = 0; // ������� �������� �������� �����
			$full_lang_symbols = 0; //������ ���������� �������� ����� �����

			// ��� ��� ����� ������ ����� ���� ������������, � ������� ����������, �� ���� �� ���������
			$cur_lang = $this->_langs[$lang_code];

			foreach ($cur_lang as $l_item)
			{
				// ������ ���������� ���������� ��������� ������� � ������
				$tmp_term = mb_substr_count($tmp_str, $l_item);

				if ($tmp_term > 1) // �� ���� ������ � ����� 1 ��� ����� ���
				{
					$tmp_freq++; // �������� ������� �������� �����, ������� � ���� ������ ����
					$full_lang_symbols += $tmp_term;
				}
			}

			if ($this->use_str_len_per_lang === true)
			{
				//������������ ����� ���������� ��������
				$lang_res[$lang_code] = $full_lang_symbols;
			}
			else
				// ��������� ������� �� ���� �������� ��������
				$lang_res[$lang_code] = ceil((100 / count($cur_lang) ) * $tmp_freq);

		}

		// ���, ������ ��������� ��� �����
		arsort($lang_res, SORT_NUMERIC); //��������� ������ ������ ������� ���� � ������� ������������

		if ($this->return_all_results == true)
		{
			return $lang_res; // ���� ������� ��� ���������� - ����������, ����� ������� ������
		}
		else
			{
				// ���� ������ ���������� ���� ������, ���������� ��� �����, ����� - null (�� ����, �� �� ����� ���������� ��� �����)
				$key = key($lang_res);

				if ($lang_res[$key] >= $this->detect_range)
					return $key;
				else
					return null;
			}

	}


	// ����� ������� ��� ����������� �����
	public function lang_detect($tmp_str = null)
	{
		if ($tmp_str == null) return false; // ���� ������ �� �������� - �����

		$tmp_str = $this->_prepare_str($tmp_str);

		if ($tmp_str === false) return false;

		// ���� ������� ��������� �� �������
		if ($this->use_rules_only === true)
		{
			$res = $this->_detect_from_rules($tmp_str);

			return array($res, $this->lang[$res]);
		}
		else
			{
				// ��� ������������� ������ �� �� ����� �������� ������ ��������� �� �����������, ������ ���������
				$this->return_all_results = false;

				$res = $this->_detect_from_tables($tmp_str);

				if ($tmp_str === false) return false;

				if ($this->use_rules === true)
				{
					$res_rules = $this->_detect_from_rules($tmp_str);

					// ������� �� �������� ���������� ������ � ����������
					if ($this->use_rules_priory === true)
					{
						//������� ����� ������� ���, ��� ����������
						return  array($res_rules, $this->lang[$res_rules]);
					}
					else
						{
							return array($res, $this->lang[$res]);
						}
				}
				else
					return array($res, $this->lang[$res]);
			}
	}

	private function detect_encoding($string) {
	    static $list = array('utf-8', 'windows-1251');

	    foreach ($list as $item) {
	        $sample = iconv($item, $item, $string);
	        if (md5($sample) == md5($string))
	        return $item;
	    }
	    return null;
	}
}