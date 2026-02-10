<?php

require_once Configuration::Instance()->GetValue( 'application', 'docroot') . 
	'module/'.Dispatcher::Instance()->mModule.'/business/AutoJurnal.class.php';

class RestSendTransaksiPOK extends RestResponse 
{
	public function get() {}
	
	public function post() {
		
		$arrData = is_object($_POST) ? $_POST->AsArray() : $_POST;
		$obj = new AutoJurnal();
		$obj->AssignValue($arrData);
		$obj->SetKodeTransaksi('POK');
		$return = $obj->SendTransaksi();
		return $return;
	}
	
	public function put() {}
	
	public function delete() {}
}
?>