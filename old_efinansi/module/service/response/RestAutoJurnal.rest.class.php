<?php
/**
* @package RestAutoJurnal
* @copyright Copyright (c) PT Gamatechno Indonesia
* @Analyzed By Dyan Galih <galih@gamatechno.com>
* @author Dyan Galih <galih@gamatechno.com>
* @version 0.1
* @startDate 2013-01-01
* @lastUpdate 2013-01-01
* @description Rest Service For AutoJurnal
*/

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/'.Dispatcher::Instance()->mModule.'/business/Service.class.php';

class RestAutoJurnal extends RestResponse {

   public function get() {
		$arrData = $_GET;
		$obj = new Service();
		$return = $obj->autoJurnal($arrData);
		$this->responseStatus = 200;
		return $return;
	}
	
	public function post() {
		$arrData = $_POST;
		$obj = new Service();
		$return = $obj->autoJurnal($arrData);
		$this->responseStatus = 201;
		return $return;
	}
	
	public function put() {}
	public function delete() {}
}
?>
