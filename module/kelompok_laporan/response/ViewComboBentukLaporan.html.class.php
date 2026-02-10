<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
    'module/kelompok_laporan/business/AppKlpLaporan.class.php';

class ViewComboBentukLaporan extends HtmlResponse {

    public function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 
        'module/kelompok_laporan/template');
        $this->SetTemplateFile('combo_bentuk_transaksi.html');
    }

    public function ProcessRequest() {
        $idDec = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
        $Obj = new AppKelpLaporan();

        $bentukTransaksi = $Obj->getChild($idDec);
        if (empty($bentukTransaksi)) {
            $disabledStatus = "disabled";
            $bentukTransaksi['0']['name'] = "Tidak Ada Data";
            $cbstatus = 'none';
        } else {
            $cbstatus = 'false';
        }
        
        Messenger::Instance()->SendToComponent(
            'combobox', 
            'Combobox', 
            'view', 
            'html', 
            'bentuk_transaksi', 
             array(
                'bentuk_transaksi', 
                $bentukTransaksi, 
                '', 
                $cbstatus,
                $disabledStatus . ' onChange="getNoUrut(this.value)"'
            ), 
            Messenger::CurrentRequest
        );

        return null;
    }

    public function ParseTemplate($data = NULL) {}
}

?>