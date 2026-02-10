<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/misi/response/ProcessMisi.proc.class.php';

#doc
#    classname:    DoUpdateMisi
#    scope:        PUBLIC
#
#/doc

class DoUpdateMisi extends HtmlResponse
{
    function TemplateModule()
    {
        # code...
    }
    
    function ProcessRequest()
    {
        $obj            = new ProcessMisi();
        $url_redirect   = $obj->Update();
        $this->RedirectTo($url_redirect);
        
        return null;
    }
    
    function ParseTemplate($data = null)
    {
        # code...
    }
}
?>
