<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/visi/response/ProcessVisi.proc.class.php';

#doc
#    classname:    DoSaveVisi
#    scope:        PUBLIC
#
#/doc

class DoSaveVisi extends HtmlResponse
{
    #    internal variables
    
    # code
    function TemplateModule()
    {
        # code...
    }
    
    function ProcessRequest()
    {
        $obj            = new ProcessVisi();
        $url_redirect   = $obj->Save();
        
        $this->RedirectTo($url_redirect);
        
        return null;
    }
    
    function ParseTemplate($data = null)
    {
    
    }
}
?>
