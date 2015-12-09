<?php

    /**
     * SabreAMF_AMF3_RemotingMessage 
     * 
     * @uses SabreAMF_AM3_AbstractMessage
     * @package SabreAMF
     * @subpackage AMF3
     * @version $Id: RemotingMessage.php,v 1.1 2009-02-18 13:09:13 konovalova Exp $
     * @copyright 2006 Rooftop Solutions
     * @author Evert Pot <evert@rooftopsolutions.nl> 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause) 
     */

    require_once dirname(__FILE__).'/AbstractMessage.php';

    /**
     * Invokes a message on a service
     */
    class SabreAMF_AMF3_RemotingMessage extends SabreAMF_AMF3_AbstractMessage {

        /**
         * operation 
         * 
         * @var string 
         */
        public $operation;

        /**
         * source 
         * 
         * @var string 
         */
        public $source;

    }

?>
