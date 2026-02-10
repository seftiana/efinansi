<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/jurnal_penerimaan/response/ProcJurnalPenerimaan.proc.class.php';

class DoDeleteJurnalPenerimaan extends HtmlResponse {

    function TemplateModule() {}

    function ProcessRequest() {
        $Obj = new ProcJurnalPenerimaan();
        $urlRedirect = $Obj->Delete();
        $this->RedirectTo($urlRedirect);
        return NULL;
    }

    function ParseTemplate($data = NULL) {}
    
}

?>