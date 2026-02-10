<?php

/**
 * @package alokasi_penerimaan
 * @since 27 Maret 2012
 * @copyright 2012 Gamatechno
 */ 

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
        'module/alokasi_penerimaan/response/ProcessAlokasiPenerimaan.proc.class.php';

class DoAddAlokasiPenerimaan extends JsonResponse 
{

   public function TemplateModule() {}
   
   public function ProcessRequest() 
   {
        $Obj = new ProcessAlokasiPenerimaan();
        $urlRedirect = $Obj->Add();      
        return array( 'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.
                        $urlRedirect.'&ascomponent=1")');       
   }
   
   public function ParseTemplate($data = NULL) { }
}

?>