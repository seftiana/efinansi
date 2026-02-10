<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/misi/response/ProcessMisi.proc.class.php';

#doc
#    classname:    DoDeleteMisi
#    scope:        PUBLIC
#
#/doc

class DoDeleteMisi extends HtmlResponse
{
    function TemplateModule()
    {
        # code...
    }
    
    function ProcessRequest()
    {
        $obj            = new ProcessMisi();
        $url_redirect   = $obj->Delete();
        $this->RedirectTo($url_redirect);
        
        return null;
    }
    
    function ParseTemplate($data = null)
    {
        # code...
    }

}
?>
