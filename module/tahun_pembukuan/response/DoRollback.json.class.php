<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot').
    'module/tahun_pembukuan/response/ProccessRollback.proc.class.php';

class DoRollback extends JsonResponse {
    public function TemplateModule() {}

    public function ProcessRequest() {
        $mObj = new ProccessRollback();

        $urlRedirect = $mObj->Rollback();

        return array(
            'exec'=> 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$urlRedirect.'&ascomponent=1")'
        );
    }

    public function ParseTemplate($data = null){}
}
