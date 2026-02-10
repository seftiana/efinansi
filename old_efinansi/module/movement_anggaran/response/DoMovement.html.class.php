<?php
/**
* Module : movement_anggaran
* FileInclude : ProcessMovement.proc.class.php
* Class : DoMovement
* Extends : HtmlResponse
*/
    require_once GTFWConfiguration::GetValue('application','docroot').
    'module/movement_anggaran/response/ProcessMovement.proc.class.php';

    class DoMovement extends HtmlResponse{
        function TemplateModule(){
            //$this->setTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
            //'module/movement_anggaran/template/');
            //$this->setTemplateFile('TemplateFile');
        }
        
        function ProcessRequest(){
            $obj        = new Movement();
            $url_redirect   = $obj->Movement();
            
            $this->RedirectTo($url_redirect);
        }
        
        function ParseTemplate($data = null){
            # code ...
            
        }
    }
?>
