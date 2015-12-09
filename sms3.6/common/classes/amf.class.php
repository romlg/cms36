<?php
require_once('SabreAMF/CallbackServer.php');

class TAmf 
{
	public $serviceClass;
	
	public function TAmf($serviceClass)
	{
		$this->serviceClass = $serviceClass;
	  // Init server
        $server = new SabreAMF_CallbackServer();
        $server->onInvokeService = array($this, 'amfService');
        $server->exec();
	}
	
    public function amfService($service, $method, $data)
    {
    	if (!$this->serviceClass){
    		echo ('Unknown service class: '.$this->serviceClass);
    		die();
    	}
        return call_user_func_array(array( $this->serviceClass, $method), $data);
    }
}