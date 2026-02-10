<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/spp/response/ProcessSpp.proc.class.php';

class DoAddSpp extends JsonResponse{
function TemplateModule(){
    // $this->setTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/ /template/');
    // $this->setTemplateFile('');
}
function ProcessRequest(){
    $obj			= new ProcessSpp();
	$url_redirect	= $obj->Add();
	return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$url_redirect.'&ascomponent=1")');
}

function ParseTemplate($data = null){
   // $dataList    = $data;
    //$this->mrTemplate->AddVar('CONTENT','CONTENT_NAME','CONTENT');
}
}
?>