<?php
/**
* Module : approval_pencairan
* FileInclude : ProcessSpm.proc.class.php
* Class : DoAddSpm
* Extends : HtmlResponse
*/
    require_once GTFWConfiguration::GetValue('application','docroot').
    'module/approval_pencairan/response/ProcessSpm.proc.class.php';

    class DoAddSpm extends HtmlResponse{
        function TemplateModule(){
            //$this->setTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
            //'module/approval_pencairan/template/');
            //$this->setTemplateFile('TemplateFile');
        }
        
        function ProcessRequest(){
            # code ...
            $obj            = new ProcessSpm();
            $url_redirect   = $obj->Add();
            $this->RedirectTo($url_redirect);
            
            return null;
        }
        
        function ParseTemplate($data = null){
            # code ...
            
        }
    }
?>
