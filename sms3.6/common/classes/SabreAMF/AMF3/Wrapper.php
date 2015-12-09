<?php

    /**
     * SabreAMF_AMF3_Wrapper 
     * 
     * @package SabreAMF
     * @subpackage AMF3
     * @version $Id: Wrapper.php,v 1.1 2009-02-18 13:09:13 konovalova Exp $
     * @copyright 2006 Rooftop Solutions
     * @author Evert Pot <evert@collab.nl> 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause) 
     */
    class SabreAMF_AMF3_Wrapper {


        /**
         * data 
         * 
         * @var mixed
         */
        private $data;


        /**
         * __construct 
         * 
         * @param mixed $data 
         * @return void
         */
        public function __construct($data) {

            $this->setData($data);

        }
        

        /**
         * getData 
         * 
         * @return mixed 
         */
        public function getData() {

            return $this->data;

        }

        /**
         * setData 
         * 
         * @param mixed $data 
         * @return void
         */
        public function setData($data) {

            $this->data = $data;

        }
            

    }

?>
