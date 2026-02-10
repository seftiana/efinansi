<?php
/**
* Module : movement_anggaran
* FileInclude : ProcessMovement.proc.class.php
* Class : DoMovement
* Extends : JsonResponse
*/
    require_once GTFWConfiguration::GetValue('application','docroot').
    'module/movement_anggaran/response/ProcessMovement.proc.class.php';

    class DoMovement extends JsonResponse{
        function TemplateModule(){
            //$this->setTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
            //'module/movement_anggaran/template/');
            //$this->setTemplateFile('TemplateFile');
        }
        
        function ProcessRequest(){
            $obj        = new Movement;
            $url_redirect = $obj->Movement();
            
            return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$url_redirect.'&ascomponent=1")');
        }
        
        function ParseTemplate($data = null){
            # code ...
            
        }
    }
?>
