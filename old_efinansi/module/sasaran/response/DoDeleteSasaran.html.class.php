<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/sasaran/response/ProcessSasaran.proc.class.php';

#doc
#    classname:    DoDeleteSasaran
#    scope:        PUBLIC
#
#/doc

class DoDeleteSasaran extends HtmlResponse
{
    function TemplateModule()
    {
        # code...
    }
    
    
    function ProcessRequest()
    {
        $obj            = new ProcessSasaran();
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
