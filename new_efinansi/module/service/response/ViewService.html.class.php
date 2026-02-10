<?php
/**
* @package ViewService
* @copyright Copyright (c) PT Gamatechno Indonesia
* @Analyzed By Dyan Galih
* @author Didi Zuliansyah
* @version 01
* @startDate 2012-01-01
* @lastUpdate 2012-01-01
* @description ViewService
*/

class ViewService extends HtmlResponse {

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue( 'application', 'docroot').'module/'.Dispatcher::Instance()->mModule.'/template');
		$this->SetTemplateFile('view_service.html');
	}

	function ProcessRequest() {
		return @$return;
	}

	function ParseTemplate($data = NULL) {
	}
}
?>