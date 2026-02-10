<?php

/**
 * @package alokasi_penerimaan
 * @since 27 Maret 2012
 * @copyright 2012 Gamatechno
 */ 


require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
        'module/alokasi_penerimaan/response/ProcessAlokasiPenerimaan.proc.class.php';


class DoDeleteAlokasiPenerimaan extends HtmlResponse 
{

    public function TemplateModule() {}
   
    public function ProcessRequest() 
    {
        $Obj = new ProcessAlokasiPenerimaan();
        $urlRedirect = $Obj->Delete();         
        $this->RedirectTo($urlRedirect) ;     
        return NULL;      
    }
   
    public function ParseTemplate($data = NULL) {}
}

?>