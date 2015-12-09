<?php

    /**
     * SabreAMF_AMF3_CommandMessage 
     * 
     * @uses SabreAMF
     * @uses _AMF3_AbstractMessage
     * @package 
     * @version $Id: CommandMessage.php,v 1.1 2009-02-18 13:09:13 konovalova Exp $
     * @copyright 2006 Rooftop Solutions
     * @author Evert Pot <evert@rooftopsolutions.nl> 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause) 
     */

    require_once dirname(__FILE__).'/AbstractMessage.php';

    /**
     * This class is used for service commands, like pinging the server
     */
    class SabreAMF_AMF3_CommandMessage extends SabreAMF_AMF3_AbstractMessage {

        const SUBSCRIBE_OPERATION          = 0;
        const UNSUSBSCRIBE_OPERATION       = 1;
        const POLL_OPERATION               = 2;
        const CLIENT_SYNC_OPERATION        = 4;
        const CLIENT_PING_OPERATION        = 5;
        const CLUSTER_REQUEST_OPERATION    = 7; 
        const LOGIN_OPERATION              = 8;
        const LOGOUT_OPERATION             = 9;
        const SESSION_INVALIDATE_OPERATION = 10;

        /**
         * operation 
         * 
         * @var int 
         */
        public $operation;

        /**
         * messageRefType 
         * 
         * @var int 
         */
        public $messageRefType;

        /**
         * correlationId 
         * 
         * @var string 
         */
        public $correlationId;

    }

?>
