<?php

    require_once dirname(__FILE__) . '/Const.php';
    require_once dirname(__FILE__) . '/../Const.php';
    require_once dirname(__FILE__) . '/../Deserializer.php';
    require_once dirname(__FILE__) . '/../AMF3/Deserializer.php';
    require_once dirname(__FILE__) . '/../AMF3/Wrapper.php';
    require_once dirname(__FILE__) . '/../TypedObject.php';

    /**
     * SabreAMF_AMF0_Deserializer 
     * 
     * @package SabreAMF
     * @subpackage AMF0
     * @version $Id: Deserializer.php,v 1.1 2009-02-18 13:09:13 konovalova Exp $
     * @copyright 2006 Rooftop Solutions
     * @author Evert Pot <evert@collab.nl> 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause) 
     * @uses SabreAMF_Const
     * @uses SabreAMF_AMF0_Const
     * @uses SabreAMF_AMF3_Deserializer
     * @uses SabreAMF_AMF3_Wrapper
     * @uses SabreAMF_TypedObject
     */
    class SabreAMF_AMF0_Deserializer extends SabreAMF_Deserializer {

        /**
         * refList 
         * 
         * @var array 
         */
        private $refList = array();

        /**
         * amf3Deserializer 
         * 
         * @var SabreAMF_AMF3_Deserializer 
         */
        private $amf3Deserializer = null;

        /**
         * readAMFData 
         * 
         * @param int $settype 
         * @param bool $newscope
         * @return mixed 
         */
        public function readAMFData($settype = null,$newscope = false) {

           if ($newscope) $this->refList = array();

           if (is_null($settype)) {
                $settype = $this->stream->readByte();
           }

           switch ($settype) {

                case SabreAMF_AMF0_Const::DT_NUMBER      : return $this->stream->readDouble();
                case SabreAMF_AMF0_Const::DT_BOOL        : return $this->stream->readByte()==true;
                case SabreAMF_AMF0_Const::DT_STRING      : return $this->readString();
                case SabreAMF_AMF0_Const::DT_OBJECT      : return $this->readObject();
                case SabreAMF_AMF0_Const::DT_NULL        : return null; 
                case SabreAMF_AMF0_Const::DT_UNDEFINED   : return null;
                case SabreAMF_AMF0_Const::DT_REFERENCE   : return $this->readReference();
                case SabreAMF_AMF0_Const::DT_MIXEDARRAY  : return $this->readMixedArray();
                case SabreAMF_AMF0_Const::DT_ARRAY       : return $this->readArray();
                case SabreAMF_AMF0_Const::DT_DATE        : return $this->readDate();
                case SabreAMF_AMF0_Const::DT_LONGSTRING  : return $this->readLongString();
                case SabreAMF_AMF0_Const::DT_UNSUPPORTED : return null;
                case SabreAMF_AMF0_Const::DT_XML         : return $this->readLongString();
                case SabreAMF_AMF0_Const::DT_TYPEDOBJECT : return $this->readTypedObject();
                case SabreAMF_AMF0_Const::DT_AMF3        : return $this->readAMF3Data();
                default                   :  throw new Exception('Unsupported type: 0x' . strtoupper(str_pad(dechex($settype),2,0,STR_PAD_LEFT))); return false;
 
           }

        }

        /**
         * readObject 
         * 
         * @return object 
         */
        public function readObject() {

            $object = array();
            while (true) {
                $key = $this->readString();
                $vartype = $this->stream->readByte();
                if ($vartype==SabreAMF_AMF0_Const::DT_OBJECTTERM) break;
                $object[$key] = $this->readAmfData($vartype);
            }
            if (defined('SABREAMF_OBJECT_AS_ARRAY')) {
                $object = (object)$object;
            }
            $this->refList[] = $object;
            return $object;    

        }

        /**
         * readReference 
         * 
         * @return object 
         */
        public function readReference() {
            
            $refId = $this->stream->readInt();
            if (isset($this->refList[$refId])) {
                return $this->refList[$refId];
            } else {
                throw new Exception('Invalid reference offset: ' . $refId);
                return false;
            }

        }


        /**
         * readArray 
         * 
         * @return array 
         */
        public function readArray() {

            $length = $this->stream->readLong();
            $arr = array();
            while($length--) $arr[] = $this->readAMFData();
            return $arr;

        }

        /**
         * readMixedArray 
         * 
         * @return array 
         */
        public function readMixedArray() {

            $highestIndex = $this->stream->readLong();
            return $this->readObject();

        }

       /**
         * readString 
         * 
         * @return string 
         */
        public function readString() {

            $strLen = $this->stream->readInt();
            return $this->stream->readBuffer($strLen);

        }

        /**
         * readLongString 
         * 
         * @return string 
         */
        public function readLongString() {

            $strLen = $this->stream->readLong();
            return $this->stream->readBuffer($strLen);

        }

        /**
         *  
         * readDate 
         * 
         * @return int 
         */
        public function readDate() {

            $timestamp = floor($this->stream->readDouble() / 1000);
            $timezoneOffset = $this->stream->readInt();
            if ($timezoneOffset > 720) $timezoneOffset = ((65536 - $timezoneOffset));
            $timezoneOffset=($timezoneOffset * 60) - date('Z');
            return $timestamp + ($timezoneOffset);


        }

        /**
         * readTypedObject 
         * 
         * @return object
         */
        public function readTypedObject() {

            $classname = $this->readString();
            $object = array();
            while (true) {
                $key = $this->readString();
                $vartype = $this->stream->readByte();
                if ($vartype==SabreAMF_AMF0_Const::DT_OBJECTTERM) break;
                $object[$key] = $this->readAmfData($vartype);
            }
            $object = (object)$object;
            if ($classname = $this->getLocalClassName($classname)) {
                // AAARRRGGGH
            } else {
                $object = new SabreAMF_TypedObject($classname,$object);
                $this->refList[] = $object;
            }
            return $object;

        }
        
        /**
         * readAMF3Data 
         * 
         * @return SabreAMF_AMF3_Wrapper 
         */
        public function readAMF3Data() {

            if (!$this->amf3Deserializer) {
                $this->amf3Deserializer = new SabreAMF_AMF3_Deserializer($this->stream);
            }
            return new SabreAMF_AMF3_Wrapper($this->amf3Deserializer->readAMFData());

        }


   }

?>
