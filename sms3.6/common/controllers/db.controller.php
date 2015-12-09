<?

//подключаем класс работы с бд
include_once(common_controller('db/'.SQL_TYPE));

class DB_Controller{

	var $db_class;
	var $table;

	//---------------------------------------------------------------------------------------

	function DB_Controller(){
		$this->db_class = Registry::get('DB_Controller_'.SQL_TYPE);
		$GLOBALS['db'] = &$this->db_class;
	}

	//---------------------------------------------------------------------------------------

	function query($sql, $file = '', $line = '', $unbuffered = false){
		return $this->db_class->query($sql, $file, $line, $unbuffered);
	}

	//---------------------------------------------------------------------------------------

	function getValue($sql, $file = '', $line = ''){
		return $this->db_class->getValue($sql, $file, $line);
	}

	//---------------------------------------------------------------------------------------

	function getRow($sql, $file = '', $line = ''){
		return $this->db_class->getRow($sql, $file, $line);
	}

	//---------------------------------------------------------------------------------------

	function getRows($sql, $use_key = false, $file = '', $line = '', $type = true){
		return $this->db_class->getRows($sql, $use_key, $file, $line, $type);
	}

	//---------------------------------------------------------------------------------------

	function getLastId(){
		return $this->db_class->getLastId();
	}

	//---------------------------------------------------------------------------------------

	function getError(){
		return $this->db_class->getError();
	}

	//---------------------------------------------------------------------------------------

	function getErrNo(){
		return $this->db_class->getErrNo();
	}

	//---------------------------------------------------------------------------------------
	function _log($sql = false, $id = false){
		return $this->db_class->_log($sql, $id);
	}


}
?>