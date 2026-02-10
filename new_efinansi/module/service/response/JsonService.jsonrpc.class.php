<?php
require_once Configuration::Instance()->GetValue( 'application', 'docroot') . 'module/'.Dispatcher::Instance()->mModule.'/business/Service.class.php';

class JsonService extends JsonRpcResponse {
	
	public function sendTransaksi($arrData){
		$obj = new Service();
		$return = $obj->sendTransaksi($arrData);
		return $return;
	}
	
	public function cancelTransaksi($arrData){
		# Next devel if needed
// 		if(!empty($arrData)){
// 			#Do something here
// 		}else{	
// 			$return['status'] = 'false';
// 			$return['message'] = 'Data tidak boleh kosong';
// 		}
		return null;
	}
	
	public function autoJurnal($arrData){
		$obj = new Service();
		$return = $obj->autoJurnal($arrData);
		return $return;
	}
	
}
?>
