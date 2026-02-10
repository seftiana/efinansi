<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/spp/response/ProcessSpp.proc.class.php';

class DoAddSpp extends HtmlResponse{
function TemplateModule(){
    // $this->setTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module//template/');
    // $this->setTemplateFile('');
}
function ProcessRequest(){
    $obj			= new ProcessSpp();
	$url_redirect	= $obj->Add();
	$this->RedirectTo($url_redirect);
	return NULL;
}

function ParseTemplate($data = null){
   // $dataList    = $data;
    //$this->mrTemplate->AddVar('CONTENT','CONTENT_NAME','CONTENT');
}
}
?>