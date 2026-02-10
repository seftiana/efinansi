<?php
require_once Configuration::Instance()->GetValue( 'application', 'docroot') . 'module/'.Dispatcher::Instance()->mModule.'/business/Service.class.php';

class RestSendTransaksiSPTB extends RestResponse {
	public function get() {
		$arrData = $_GET;
		$obj = new Service();
		$return = $obj->sendTransaksi($arrData,'SPTB');
		$this->responseStatus = 200;
		$response = array($return);
		return array($response);
	}
	
	public function post() {
		$arrData = $_POST;
		$obj = new Service();
		$return = $obj->sendTransaksi($arrData,'SPTB');
		$this->responseStatus = 201;
		$response = array($return);
		return array($response);
	}
	
	public function put() {
	}
	
	public function delete() {
	}
}
?>
