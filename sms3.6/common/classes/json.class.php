<?php
class Json
{
    /**
     * Array of visited objects; used to prevent cycling.
     *
     * @var array
     */
    var $_visited = array();


    /**
     * Use the JSON encoding scheme for the value specified
     *
     * @param mixed $value  value the object to be encoded
     * @return string  The encoded value
     */
    function encode($value)
    {
        $encoder = & Registry::get('Json');

        return $encoder->_encodeValue($value);
    }

    /**
     * Recursive driver which determines the type of value to be encoded
     * and then dispatches to the appropriate method. $values are either
     *    - objects (returns from {@link _encodeObject()})
     *    - arrays (returns from {@link _encodeArray()})
     *    - basic datums (e.g. numbers or strings) (returns from {@link _encodeDatum()})
     *
     * @param $value mixed The value to be encoded
     * @return string Encoded value
     */
    function _encodeValue(&$value)
    {
    	if (is_object($value)) {
            return $this->_encodeObject($value);
    	} else if (is_array($value)) {
            return $this->_encodeArray($value);
    	}

        return $this->_encodeDatum($value);
    }



    /**
     * Encode an object to JSON by encoding each of the public properties
     *
     * A special property is added to the JSON object called '__className'
     * that contains the name of the class of $value. This is used to decode
     * the object on the client into a specific class.
     *
     * @param $value object
     * @return string
     * @throws Zend_Json_Exception
     */
    function _encodeObject(&$value)
    {
        if ($this->_wasVisited($value)) {
    	    die(
                'Cycles not supported in JSON encoding, cycle introduced by '
                . 'class "' . get_class($value) . '"'
            );
    	}

        $this->_visited[] = $value;

    	$props = '';
    	foreach (get_object_vars($value) as $name => $propValue) {
    	    if (isset($propValue)) {
        		$props .= ', '
                        . $this->_encodeValue($name)
        		        . ' : '
                        . $this->_encodeValue($propValue);
    	    }
    	}

    	return '{' . '"__className": "' . get_class($value) . '"'
                . $props . '}';
    }


    /**
     * Determine if an object has been serialized already
     *
     * @access protected
     * @param mixed $value
     * @return boolean
     */
    function _wasVisited(&$value)
    {
        if (in_array($value, $this->_visited, true)) {
            return true;
        }

        return false;
    }


    /**
     * JSON encode an array value
     *
     * Recursively encodes each value of an array and returns a JSON encoded
     * array string.
     *
     * Arrays are defined as integer-indexed arrays starting at index 0, where
     * the last index is (count($array) -1); any deviation from that is
     * considered an associative array, and will be encoded as such.
     *
     * @param $array array
     * @return string
     */
    function _encodeArray(&$array)
    {
        $tmpArray = array();

        // Check for associative array
        if (array_keys($array) !== range(0, count($array) - 1)) {
            // Associative array
            $result = '{';
            foreach ($array as $key => $value) {
                $key = (string) $key;
        		$tmpArray[] = $this->_encodeString($key)
        		            . ' : '
                            . $this->_encodeValue($value);
            }
            $result .= implode(', ', $tmpArray);
            $result .= '}';
        } else {
            // Indexed array
            $result = '[';
            $length = count($array);
            for ($i = 0; $i < $length; $i++) {
                $tmpArray[] = $this->_encodeValue($array[$i]);
            }
            $result .= implode(', ', $tmpArray);
            $result .= ']';
        }

    	return $result;
    }


    /**
     * JSON encode a basic data type (string, number, boolean, null)
     *
     * If value type is not a string, number, boolean, or null, the string
     * 'null' is returned.
     *
     * @param $value mixed
     * @return string
     */
    function _encodeDatum(&$value)
    {
        $result = 'null';

    	if (is_numeric($value)) {
    	    $result = '"'.(string)$value.'"';
        } elseif (is_string($value)) {
            $result = $this->_encodeString($value);
    	} elseif (is_bool($value)) {
    	    $result = $value ? 'true' : 'false';
        }

    	return $result;
    }


    /**
     * JSON encode a string value by escaping characters as necessary
     *
     * @param $value string
     * @return string
     */
    function _encodeString(&$string)
    {
        // Escape these characters with a backslash:
        // " \ / \n \r \t \b \f
        $search  = array('\\', "\n", "\t", "\r", "\b", "\f", '"');
        $replace = array('\\\\', '\\n', '\\t', '\\r', '\\b', '\\f', '\"');
        $string  = str_replace($search, $replace, $string);

        // Escape certain ASCII characters:
        // 0x08 => \b
        // 0x0c => \f
        $string = str_replace(array(chr(0x08), chr(0x0C)), array('\b', '\f'), $string);

    	return '"' . $string . '"';
    }



}

