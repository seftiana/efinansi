<?php


require_once GTFWConfiguration::GetValue('application', 'docroot') .
    'module/rkakl_sumber_dana/response/ProcessRkaklSumberDana.proc.class.php';
    
class DoDeleteRkaklSumberDana extends HtmlResponse
{

    public function TemplateModule(){}

    public function ProcessRequest()
    {
        $obj = new ProcessRkaklSumberDana();
        $urlRedirect = $obj->Delete();
        $this->RedirectTo($urlRedirect);
        return null;
    }

    public function ParseTemplate($data = null){}
}