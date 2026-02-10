<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/visi/response/ProcessVisi.proc.class.php';
#doc
#    classname:    DoDeleteVisi
#    scope:        PUBLIC
#
#/doc

class DoDeleteVisi extends HtmlResponse
{
    
    function TemplateModule()
    {
        # code...
    }
    
    function ProcessRequest()
    {
        $obj        = new ProcessVisi();
        $url_delete = $obj->Delete();
        $this->RedirectTo($url_delete);
        
        return null;
    }
    
    function ParseTemplate($data = null)
    {
        # code...
    }
}
?>
