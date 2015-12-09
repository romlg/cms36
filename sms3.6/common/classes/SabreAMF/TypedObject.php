<?php

    require_once dirname(__FILE__) . '/ITypedObject.php';

    /**
     * SabreAMF_TypedObject 
     * 
     * @package SabreAMF 
     * @version $Id: TypedObject.php,v 1.1 2009-02-18 13:09:13 konovalova Exp $
     * @copyright 2006 Rooftop Solutions
     * @author Evert Pot <evert@collab.nl> 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause) 
     * @uses SabreAMF_ITypedObject
     */
    class SabreAMF_TypedObject implements SabreAMF_ITypedObject {

        private $amfClassName;
        private $amfData;

        public function __construct($classname,$data) {

            $this->setAMFClassName($classname);
            $this->setAMFData($data);

        }

        /**
         * getAMFClassName 
         * 
         * @return string 
         */
        public function getAMFClassName() {

            return $this->amfClassName;

        }

        /**
         * getAMFData 
         * 
         * @return mixed 
         */
        public function getAMFData() {

            return $this->amfData;

        }

        /**
         * setAMFClassName 
         * 
         * @param string $classname 
         * @return void
         */
        public function setAMFClassName($classname) {

            $this->amfClassName = $classname;
            
        }

        /**
         * setAMFData 
         * 
         * @param mixed $data 
         * @return void
         */
        public function setAMFData($data) {

            $this->amfData = $data;

        }

    }

?>
