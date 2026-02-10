<?php
/**
* Module : approval_pencairan
* FileInclude : ProcessSpm.proc.class.php
* Class : DoAddSpm
* Extends : JsonResponse
*/
    require_once GTFWConfiguration::GetValue('application','docroot').
    'module/approval_pencairan/response/ProcessSpm.proc.class.php';

    class DoAddSpm extends JsonResponse{
        function TemplateModule(){
            //$this->setTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
            //'module/approval_pencairan/template/');
            //$this->setTemplateFile('TemplateFile');
        }
        
        function ProcessRequest(){
            # code ...
            $obj        = new ProcessSpm();
            $url_redirect   = $obj->Add();
            
            return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$url_redirect.'&ascomponent=1")');
        }
        
        function ParseTemplate($data = null){
            # code ...
            
        }
    }
?>
