<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/visi/response/ProcessVisi.proc.class.php';
#doc
#    classname:    DoUpdateVisi
#    scope:        PUBLIC
#
#/doc

class DoUpdateVisi extends HtmlResponse
{
    function TemplateModule()
    {
        # code...
    }
    
    function ProcessRequest()
    {
        $obj            = new ProcessVisi();
        $url_redirect   = $obj->Update();
        
        $this->RedirectTo($url_redirect);
        
        return null;
    }
    
    function ParseTemplate($data = null)
    {
        # code
    }
}
?>
