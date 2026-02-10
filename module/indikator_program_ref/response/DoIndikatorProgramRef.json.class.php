<?php

/**
 * Class DoIndikatorProgramRef
 * @package indikator_program_ref
 * @todo Untuk menerima request dari url 
 * @subpackage response
 * @since 21 Juni 2012
 * @SystemAnalyst Dyan Galih Nugroho Wicaksi <galih@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
require_once GTFWConfiguration::GetValue('application', 'docroot') .
    'module/indikator_program_ref/response/ProcessIndikatorProgramRef.proc.class.php';

class DoIndikatorProgramRef extends JsonResponse
{
    public function TemplateModule(){}

    public function ProcessRequest()
    {
        $Obj = new ProcessIndikatorProgramRef();
        
        if (isset($_GET['dataId']) && ($_GET['dataId'] !='')){
            $urlRedirect = $Obj->Update();
        } elseif(isset($_GET['del']) && ($_GET['del'] == '1')){
            $urlRedirect = $Obj->Delete();
        } else {
            $urlRedirect = $Obj->Add();
        }   
        return array('exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","' .
                $urlRedirect . '&ascomponent=1")');
    }

    public function ParseTemplate($data = null) {}
}
