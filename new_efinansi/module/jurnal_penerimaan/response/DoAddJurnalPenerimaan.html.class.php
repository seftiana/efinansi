<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/jurnal_penerimaan/response/ProcJurnalPenerimaan.proc.class.php';

class DoAddJurnalPenerimaan extends HtmlResponse {

    function TemplateModule() {
        
    }

    function ProcessRequest() { 
            $mObj = new JurnalPenerimaan();
            $mProcess = new ProcJurnalPenerimaan();
            $urlRedirect = $mProcess->Save();

            $this->RedirectTo($urlRedirect);

            return NULL;
        }

        function ParseTemplate($data = NULL) {
            
        }

    }

?>