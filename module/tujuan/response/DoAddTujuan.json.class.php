<?php

/**
 * 
 * class DoAddTujuan [json]
 * @package tujuan
 * @subpackage response
 * @filename DoAddTujuan.json.class.php
 * @analyst nanang ruswianto <nanang@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * @since 2 Agustus 2012
 * 
 */
 
require_once GTFWConfiguration::GetValue('application','docroot').
	'module/tujuan/response/ProcessTujuan.proc.class.php';

class DoAddTujuan extends JsonResponse
{
    public function ProcessRequest()
    {
        $obj            = new ProcessTujuan();
        $url_redirect   = $obj->Add();
        
        return array( 
            'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","'.$url_redirect.'&ascomponent=1")'
        );
    }
}
