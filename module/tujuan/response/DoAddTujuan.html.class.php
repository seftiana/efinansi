<?php

/**
 * 
 * class DoAddTujuan [html]
 * @package tujuan
 * @subpackage response
 * @filename DoAddTujuan.html.class.php
 * @analyst nanang ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * @since 2 Agustus 2012
 * 
 */
 
require_once GTFWConfiguration::GetValue('application','docroot').
	'module/tujuan/response/ProcessTujuan.proc.class.php';

class DoAddTujuan extends HtmlResponse
{
    public function TemplateModule(){}
    
    public function ProcessRequest()
    {
        $obj            = new ProcessTujuan();
        $url_redirect   = $obj->Add();
        $this->RedirectTo($url_redirect);
        return null;
    }
    
    public function ParseTemplate($data = null){}
}
