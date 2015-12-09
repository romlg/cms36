<?php

    /**
     * SabreAMF_Const 
     *
     * SabreAMF global constants
     * 
     * @package SabreAMF 
     * @version $Id: Const.php,v 1.1 2009-02-18 13:09:13 konovalova Exp $
     * @copyright 2006 Rooftop Solutions
     * @author Evert Pot <evert@collab.nl> 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause) 
     */
    final class SabreAMF_Const {

        /**
         * AC_Flash
         *
         * Specifies FlashPlayer 5.0 - 8.0 client
         */
        const AC_Flash    = 0;

        /**
         * AC_FlashCom
         *
         * Specifies FlashCom / Flash Media Server client
         */
        const AC_FlashCom = 1;

        /**
         * AC_Flex
         *
         * Specifies a FlashPlayer 9.0 client
         */
        const AC_Flash9 = 3;

        /**
         * R_RESULT
         *
         * Normal result to a methodcall
         */
        const R_RESULT = 1;

        /**
         * R_STATUS
         *
         * Faulty result
         */
        const R_STATUS = 2;

        /**
         * R_DEBUG
         *
         * Result to a debug-header
         */
        const R_DEBUG  = 3;

        /**
         * AMF0 Encoding
         */
        const AMF0 = 0;

        /**
         * AMF3 Encoding
         */
        const AMF3 = 3;

   }


?>
