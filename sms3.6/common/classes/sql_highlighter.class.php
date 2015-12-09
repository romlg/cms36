<?php

/**
* SQL SINTAX HIGHLIGHTER
* Compatibility: PHP >= 4.1
* ---------------------------------------------------------------------
* WHAT IS THIS:
* Hihghlight a SQL string with personalizable colors for text, sintax,
* numbers and background.
*
* ---------------------------------------------------------------------
* HOW TO USE:
* $mySQLhighlighted = new SqlHighlighter();
* echo $mySQLhighlighted->highlight($sql_string);
* .
* @Author		Andrea Giammarchi
* @Alias			andr3a
* @Site			http://www.3site.it
* @Mail			andrea@3site.it
* @Version		0.1.1
* @Begin			07/02/2004
* @lastModify	10/01/2004 16:40
*/
class TSqlHighlighter {

	var $__sql_words = Array();
	var $stringtextColor, $numberColor, $backgroundColor;

	/**
	* Public constructor.
	* You can chose different SQL.sintax* file's location, change highlighted colors and background.
	* [*] read on top for more info
	*
	* SqlHighlighter($genericSintaxcolorColor[ , $informationSintaxcolorColor[ , $manipolationSintaxcolorColor[ , $columnsSintaxcolorColor[ , $stringtextColor[ , $sintaxcolorColor[ , $numberColor[ , $backtickColor[, $backgroundColor ]]]]]]]]]])
	*
	* @Param	String		$genericSintaxcolorColor => color for generic sintax. DEFAULT: "#002260"
	* @Param	String		$informationSintaxcolorColor => color for information sintax type. DEFAULT: "#F00000"
	* @Param	String		$manipolationSintaxcolorColor => color for manipolation sintax type. DEFAULT: "#105355"
	* @Param	String		$columnsSintaxcolorColor => color for knowed column types. DEFAULT: "#404000"
	* @Param	String		$stringtextColor => color for internal strings between ' or ". DEFAULT: "#2222C0"
	* @Param	String		$numberColor => color for numbers. DEFAULT: "#00B000"
	* @Param	String		$backtickColor => color for internal backticks. DEFAULT: "#459898"
	* @Param	String		$backgroundColor => background color. DEFAULT: "#FFFFFF"
	*/
	function TSqlHighlighter() {

		$genericSintaxcolorColor="#002060";
		$informationSintaxcolorColor="#F00000";
		$manipolationSintaxcolorColor="#105355";
		$columnsSintaxcolorColor="#404000";
		$stringtextColor="#2222C0";
		$numberColor="#00B000";
		$backtickColor="#459898";
		$backgroundColor="#FFFFFF";

		$this->stringtextColor = $stringtextColor;
		$this->numberColor = $numberColor;
		$this->backtickColor = $backtickColor;
		$this->backgroundColor = $backgroundColor;
		$this->__sql_words['find'] = array();
		$this->__sql_words['replace'] = array();

		$this->_replacer_generic = 'FROM|LEFT|JOIN|RIGHT|ON|WHERE|AND|MATCH|AGAINST|IN|MODE|LIMIT|LIKE|EXPLAIN|SELECT';
		$this->_replacer_information = 'COUNT|MAX|MIN';
		$this->_replacer_manipolation = 'CONCAT';
		$this->_replacer_columns = 'TINYINT|BIT|BOOL|BOOLEAN|SMALLINT|MEDIUMINT|INT|INTEGER|BIGINT|FLOAT|DOUBLE|PRECISION|REAL|DECIMAL|DEC|NUMERIC|FIXED|DATE|DATETIME|TIMESTAMP|TAMP|TIME|YEAR|CHAR|VARCHAR|TINYBLOB|TINYTEXT|BLOB|TEXT|MEDIUMBLOB|MEDIUMTEXT|LONGBLOB|LONGTEXT|ENUM|SET';

		$this->popolateReplacer($this->_replacer_generic, "\\1<span style=\"color: {$genericSintaxcolorColor}; background-color: ".$this->backgroundColor.";\">\\2</span>\\3" );
		$this->popolateReplacer($this->_replacer_information, "\\1<span style=\"color: {$informationSintaxcolorColor}; background-color: ".$this->backgroundColor.";\">\\2</span>\\3" );
		$this->popolateReplacer($this->_replacer_manipolation, "\\1<span style=\"color: {$manipolationSintaxcolorColor}; background-color: ".$this->backgroundColor.";\">\\2</span>\\3" );
		$this->popolateReplacer($this->_replacer_columns, "\\1<span style=\"color: {$columnsSintaxcolorColor}; background-color: ".$this->backgroundColor.";\">\\2</span>\\3" );
	}

	function popolateReplacer($sql_arr, $toReplace) {
		$sql_arr = explode('|', $sql_arr);
		$sql_word_starter = 0;
		$sql_word_end = count( $sql_arr );
		while( $sql_word_starter < $sql_word_end ) {
			$this->__sql_words['find'][] = "/(?i)(^|[^a-z0-9\_]){1}(".$sql_arr[$sql_word_starter++].")([^a-z0-9\_]|$){1}/";
			$this->__sql_words['replace'][] = $toReplace;
		}
	}

	function rewriteText(&$st, &$ar) {
		foreach($ar as $k => $v) {
			$st = str_replace($k, "<span style=\"color: ".$this->stringtextColor."; background-color: ".$this->backgroundColor.";\">".$v."</span>", $st);
		}
	}

	/**
	* Public , will return highlighted string.
	* @Param	String		sql string to highlight
	*/
	function highlight($st) {
		global $__remember_replacment_on_sql_string;
		$__remember_replacment_on_sql_string = array();
		if(get_magic_quotes_gpc()) {
			$st = stripslashes($st);
		}
		$symbol__1 = "_replace[mono][".md5(microtime())."]";
		$symbol__2 = '_replace[double]['.md5(microtime()).']';
		$st = str_replace('\\"', $symbol__2, str_replace( "\\'", $symbol__1, $st ));
		$__replacer_function = create_function(
			'$replacement',
			'global $__remember_replacment_on_sql_string;
			$returned = md5($replacement[1].$replacement[2].$replacement[1]);
			$__remember_replacment_on_sql_string[$returned] = htmlspecialchars($replacement[1].$replacement[2].$replacement[1]);
			return $returned;'
		);
		$st = preg_replace_callback('/(")([^\a]*?)(")/i', "$__replacer_function", $st);
		$st = preg_replace_callback("/(')([^\a]*?)(')/i", "$__replacer_function", $st);
		$st = preg_replace("/([^a-zA-Z0-9\_]){1}([0-9]+)([^a-zA-Z0-9\_]{1}|$)/", "\\1<span style=\"color: ".$this->numberColor."; background-color: ".$this->backgroundColor.";\">\\2</span>\\3", $st);
		$st = preg_replace("/(`)([^\a]*?)(`)/i", "\\1<span style=\"color: ".$this->backtickColor."; background-color: ".$this->backgroundColor.";\">\\2</span>\\3", $st);
		$st = preg_replace($this->__sql_words["find"], $this->__sql_words["replace"], $st);
		if(count($__remember_replacment_on_sql_string) > 0) {
			$this->rewriteText($st, $__remember_replacment_on_sql_string);
			$this->rewriteText($st, $__remember_replacment_on_sql_string); // not an error ... need 2 times same function for "without order" compatiblity
		}
		$st = str_replace($symbol__2, '\\"', str_replace( $symbol__1, "\\'", $st ));
		unset($__remember_replacment_on_sql_string);
		return $st;
	}
}
?>