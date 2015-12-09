<?php

    /**
     * SabreAMF_UndefinedMethodException
     * 
     * @package SabreAMF
     * @version $Id: UndefinedMethodException.php,v 1.1 2009-02-18 13:09:13 konovalova Exp $
     * @copyright 2006 Renaun Erickson
     * @author Renaun Erickson (http://renaun.com/blog)
     * @author Evert Pot (http://www.rooftopsolutions.nl)
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause) 
     */

    /**
     * Detailed Exception interface
     *
     * @uses SabreAMF_DetailException
     */
    require_once dirname(__FILE__).'/DetailException.php';

    /**
     * This is the receipt for UndefinedMethodException and default values reflective of ColdFusion RPC faults
     */
    class SabreAMF_UndefinedMethodException extends Exception Implements SabreAMF_DetailException {

		/**
		 *	Constructor
		 */
		public function __construct( $class, $method ) {
			// Specific message to MethodException
			$this->message = "Undefined method '$method' in class $class";
			$this->code = "Server.Processing";

			// Call parent class constructor
			parent::__construct( $this->message );
			
		}

        public function getDetail() {

            return "Check to ensure that the method is defined, and that it is spelled correctly.";

        }


    }

?>
