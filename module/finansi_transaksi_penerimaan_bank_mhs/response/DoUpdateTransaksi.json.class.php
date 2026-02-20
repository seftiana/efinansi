<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/finansi_transaksi_penerimaan_bank_mhs/business/ProcessTransaksiPenerimaanBank.php';

class DoUpdateTransaksi extends JsonResponse {

    function ProcessRequest() {
        $mObj = new TransaksiPenerimaanBankMhs();
        $mProcess = new ProcessTransaksiPenerimaanBank();
        $process = $mProcess->Update();
        $urlRedirect = $process['url'];
        $module = $mObj->getModule($urlRedirect);
        $subModule = $mObj->getSubModule($urlRedirect);
        $action = $mObj->getAction($urlRedirect);
        $type = $mObj->getType($urlRedirect);

        Messenger::Instance()->Send(
                $module, $subModule, $action, $type, array(
            $process['data'],
            $process['message'],
            $process['style']
                ), Messenger::NextRequest
        );

        return array(
            'exec' => 'GtfwAjax.replaceContentWithUrl("subcontent-element","' . $urlRedirect . '&ascomponent=1")'
        );
    }

}

?>
