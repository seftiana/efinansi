<?php

require_once Configuration::Instance()->GetValue( 'application', 'docroot') . 
	'module/'.Dispatcher::Instance()->mModule.'/business/AutoJurnal.class.php';

class RestSendTransaksiSP2D extends RestResponse 
{
	public function get() {}
	
	public function post() {
		
		$arrData = is_object($_POST) ? $_POST->AsArray() : $_POST;
		$obj = new AutoJurnal();
		$obj->AssignValue($arrData);
		$obj->SetKodeTransaksi('SP2D');
		$return = $obj->SendTransaksi();
		return $return;
	}
	
	public function put() {}
	
	public function delete() {}
}
?>