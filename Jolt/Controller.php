<?php

namespace Jolt;

class Controller{
	protected  $app;
	protected static $nonce = NULL;
	function __construct(){
	 	$this->app  = Jolt::getInstance();
	}
	protected function sanitize( $dirty ){
		return htmlentities(strip_tags($dirty), ENT_QUOTES);
	}

	protected function generate_nonce(  ){
		// Checks for an existing nonce before creating a new one
		if (empty(self::$nonce)) {
			self::$nonce = base64_encode(uniqid(NULL, TRUE));
			$_SESSION['nonce'] = self::$nonce;
		}
		return self::$nonce;
	}
	protected function check_nonce(  ){
		if (
		isset($_SESSION['nonce']) && !empty($_SESSION['nonce']) 
		&& isset($_POST['nonce']) && !empty($_POST['nonce']) 
		&& $_SESSION['nonce']===$_POST['nonce']
			) {
			$_SESSION['nonce'] = NULL;
			return TRUE;
		} else {
			return FALSE;
		}
	}

}


?>