<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
    'module/kelompok_laporan/business/AppKlpLaporan.class.php';

class ViewNoUrutan extends HtmlResponse 
{
	
	function TemplateModule() 
    {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 
                'module/kelompok_laporan/template');
		$this->SetTemplateFile('input_no_urutan.html');
	}
	
	function ProcessRequest() 
    {
		$idDec = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
		$Obj = new AppKelpLaporan();
		 
		 $return['no_urutan'] = $Obj->GenerateNoUrutan($_REQUEST['dataId']);
		 return $return;
	}

	function ParseTemplate($data = NULL) 
    {
            $this->mrTemplate->addVar('content','NO_URUTAN',$data['no_urutan']);
	}
}