<?php

    /**
     * SabreAMF_Serializer 
     * 
     * @package SabreAMF 
     * @version $Id: Serializer.php,v 1.1 2009-02-18 13:09:13 konovalova Exp $
     * @copyright 2006 Rooftop Solutions
     * @author Evert Pot <evert@collab.nl> 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause) 
     */

    /**
     * Including the ClassMapper
     */
    require_once dirname(__FILE__).'/ClassMapper.php';

    /**
     * Abstract Serializer
     *
     * This is the abstract serializer class. This is used by the AMF0 and AMF3 serializers as a base class
     */
    abstract class SabreAMF_Serializer {

        /**
         * stream 
         * 
         * @var SabreAMF_OutputStream 
         */
        protected $stream;

        /**
         * __construct 
         * 
         * @param SabreAMF_OutputStream $stream 
         * @return void
         */
        public function __construct(SabreAMF_OutputStream $stream) {

            $this->stream = $stream;

        }

        /**
         * writeAMFData 
         * 
         * @param mixed $data 
         * @param int $forcetype 
         * @return mixed 
         */
        public abstract function writeAMFData($data,$forcetype=null); 

        /**
         * getStream
         *
         * @return SabreAMF_OutputStream
         */
        public function getStream() {

            return $this->stream;

        }

        /**
         * getRemoteClassName 
         * 
         * @param string $localClass 
         * @return mixed 
         */
        protected function getRemoteClassName($localClass) {

            return SabreAMF_ClassMapper::getRemoteClass($localClass);

        } 

    }

?>
