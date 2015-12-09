<?php

    require_once dirname(__FILE__).'/Const.php';
    require_once dirname(__FILE__).'/../Const.php';
    require_once dirname(__FILE__).'/../Serializer.php';
    require_once dirname(__FILE__).'/../ITypedObject.php';
    require_once dirname(__FILE__).'/../ByteArray.php';

    /**
     * SabreAMF_AMF3_Serializer 
     * 
     * @package SabreAMF
     * @subpackage AMF3
     * @version $Id: Serializer.php,v 1.1 2009-02-18 13:09:13 konovalova Exp $
     * @copyright 2006-2007 Rooftop Solutions
     * @author Evert Pot (http://www.rooftopsolutions.nl) 
     * @author Karl von Randow http://xk72.com/
     * @author Develar
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause)
     * @uses SabreAMF_Const
     * @uses SabreAMF_AMF3_Const
     * @uses SabreAMF_ITypedObject
     */
    class SabreAMF_AMF3_Serializer extends SabreAMF_Serializer {

        /**
         * writeAMFData 
         * 
         * @param mixed $data 
         * @param int $forcetype 
         * @return mixed 
         */
        public function writeAMFData($data,$forcetype=null) {

           if (is_null($forcetype)) {
               // Autodetecting data type
               $type=false;
               if (!$type && is_null($data))    $type = SabreAMF_AMF3_Const::DT_NULL;
               if (!$type && is_bool($data))    {
                    $type = $data?SabreAMF_AMF3_Const::DT_BOOL_TRUE:SabreAMF_AMF3_Const::DT_BOOL_FALSE;
                }
                if (!$type && is_int($data))     $type = SabreAMF_AMF3_Const::DT_INTEGER;
                if (!$type && is_float($data))   $type = SabreAMF_AMF3_Const::DT_NUMBER;
                if (!$type && is_numeric($data)) $type = SabreAMF_AMF3_Const::DT_INTEGER;
                if (!$type && is_string($data))  $type = SabreAMF_AMF3_Const::DT_STRING;
                if (!$type && is_array($data))   {
                    $type = SabreAMF_AMF3_Const::DT_ARRAY;
                    foreach($data as $k=>$v) if (!is_numeric($k)) $type = SabreAMF_AMF3_Const::DT_OBJECT; 
                }
                if (!$type && is_object($data)) {

                    if ($data instanceof SabreAMF_ByteArray) {
                        $type = SabreAMF_AMF3_Const::DT_BYTEARRAY;
                    } else {
                        $type = SabreAMF_AMF3_Const::DT_OBJECT;
                    }

                }
                if ($type===false) {
                    throw new Exception('Unhandled data-type: ' . gettype($data));
                    return null;
                }
                if ($type == SabreAMF_AMF3_Const::DT_INTEGER && ($data > 268435455 || $data < -268435456)) {
                	$type = SabreAMF_AMF3_Const::DT_NUMBER;
                }
           } else $type = $forcetype;

           $this->stream->writeByte($type);

           switch ($type) {

                case SabreAMF_AMF3_Const::DT_NULL        : break;
                case SabreAMF_AMF3_Const::DT_BOOL_FALSE  : break;
                case SabreAMF_AMF3_Const::DT_BOOL_TRUE   : break;
                case SabreAMF_AMF3_Const::DT_INTEGER     : $this->writeInt($data); break;
                case SabreAMF_AMF3_Const::DT_NUMBER      : $this->stream->writeDouble($data); break;
                case SabreAMF_AMF3_Const::DT_STRING      : $this->writeString($data); break;
                case SabreAMF_AMF3_Const::DT_ARRAY       : $this->writeArray($data); break;
                case SabreAMF_AMF3_Const::DT_OBJECT      : $this->writeObject($data); break; 
                case SabreAMF_AMF3_Const::DT_BYTEARRAY   : $this->writeByteArray($data); break;
                default                   :  throw new Exception('Unsupported type: ' . gettype($data)); return null; 
 
           }

        }

        /**
         * writeObject 
         * 
         * @param mixed $data 
         * @return void
         */
        public function writeObject($data) {
           
            $encodingType = SabreAMF_AMF3_Const::ET_PROPLIST;
            if ($data instanceof SabreAMF_ITypedObject) {

                $classname = $data->getAMFClassName();
                $data = $data->getAMFData();

            } else if (!$classname = $this->getRemoteClassName(get_class($data))) {

                
                $classname = '';

            } else {

                if ($data instanceof SabreAMF_Externalized) {

                    $encodingType = SabreAMF_AMF3_Const::ET_EXTERNALIZED;

                }

            }


            $objectInfo = 0x03;
            $objectInfo |= $encodingType << 2;

            switch($encodingType) {

                case SabreAMF_AMF3_Const::ET_PROPLIST :

                    $propertyCount=0;
                    foreach($data as $k=>$v) {
                        $propertyCount++;
                    }

                    $objectInfo |= ($propertyCount << 4);


                    $this->writeInt($objectInfo);
                    $this->writeString($classname);
                    foreach($data as $k=>$v) {

                        $this->writeString($k);

                    }
                    foreach($data as $k=>$v) {

                        $this->writeAMFData($v);

                    }
                    break;

                case SabreAMF_AMF3_Const::ET_EXTERNALIZED :

                    $this->writeInt($objectInfo);
                    $this->writeString($classname);
                    $this->writeAMFData($data->writeExternal());
                    break;
            }

        }

        /**
         * writeInt 
         * 
         * @param int $int 
         * @return void
         */
        public function writeInt($int) {

            $bytes = array();
            if (($int & 0xff000000) == 0) {

                for($i = 3; $i > -1; $i--) {
                    $bytes[] = ($int >> (7 * $i)) & 0x7F;
                }
                
            } else {

                for ($i = 2; $i > -1; $i--) {
                    $bytes[] = ($int >> (8 + 7 * $i)) & 0x7F;
                }

                $bytes[] = $int & 0xFF;

            }
            for($i = 0; $i < 3; $i++) {

                if ($bytes[$i]>0) {

                    $this->stream->writeByte($bytes[$i] | 0x80);

                }
            }
            $this->stream->writeByte($bytes[3]);

        }

        public function writeByteArray(SabreAMF_ByteArray $data) {

            $this->writeString($data->getData());

        }

        /**
         * writeString 
         * 
         * @param string $str 
         * @return void
         */
        public function writeString($str) {

            $strref = strlen($str) << 1 | 0x01;
            $this->writeInt($strref);
            $this->stream->writeBuffer($str);

        }

        /**
         * writeArray 
         * 
         * @param array $arr 
         * @return void
         */
        public function writeArray($arr) {

            end($arr);
            $arrLen = count($arr); 
         
            $arrId = ($arrLen << 1) | 0x01;
            $this->writeInt($arrId);
            $this->writeInt(1); // Not sure what this is 
           
            foreach($arr as $v) {
                $this->writeAMFData($v);
            }

        }
        

    }

?>
