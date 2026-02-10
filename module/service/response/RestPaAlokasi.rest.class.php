<?php

require_once Configuration::Instance()->GetValue( 'application', 'docroot') . 
		'module/'.Dispatcher::Instance()->mModule.'/business/Service.class.php';

class RestPaAlokasi extends RestResponse {
	public function get() {
		$objProc = new Service();
		$return = $objProc->GetAlokasiPenerimaan();
		$this->responseStatus = '200';
	   	//$response = array('data'=>$return);
	   	return array(
					'status'=> $this->responseStatus,
					'message'=>'Request Data Sukses',
					'data'=>$return);      
	}
	public function post() {
		$objProc = new Service();
		$return = $objProc->GetAlokasiPenerimaan();
		$this->responseStatus = '201';
		//$response = array('data_alokasi'=>$return);
		return array(
					'status'=> $this->responseStatus,
					'message'=>'Request Data Sukses',
					'data'=>$return);      
	}
	public function put() {
	}
	public function delete() {
	}
}

?>