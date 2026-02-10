<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/jurnal_penerimaan/response/ProcJurnalPenerimaan.proc.class.php';

class ViewConfirmBalikJurnal extends HtmlResponse {

    protected $proc;
    protected $data;

    function ViewConfirmBalikJurnal() {
        $this->proc = new ProcJurnalPenerimaan;
        $this->data = $this->proc->getPOST();
    }

    function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'module/jurnal_penerimaan/template');
        $this->SetTemplateFile('view_confirm_balik_jurnal.html');
    }

    function ProcessRequest() {

        $grp = Dispatcher::Instance()->Decrypt($_GET['grp']);
        $jurnal_lama = $this->proc->db->GetDataById($grp);
        $return['grp'] = $grp;
        $return['jurnal_lama'] = $jurnal_lama;
        //start menghandle pesan yang diparsing
        $tmp = $this->proc->parsingUrl(__FILE__);
        if (isset($tmp['msg']))
            $return['msg'] = $tmp['msg'];

        return $return;
    }

    function ParseTemplate($data = NULL) {
        //print_r($data);
        $this->mrTemplate->AddVar('content', 'PESAN_CONFIRM', 'Apakah anda ingin merevisi transaksi dengan referensi');
        $this->mrTemplate->AddVar('content', 'REFERENSI', $data['jurnal_lama'][0]['referensi_nama']);
        $url_ya = Dispatcher::Instance()->GetUrl('jurnal_penerimaan', 'inputJurnalPenerimaan', 'view', 'html') . '&grp=' . $data['grp'] . '&is_revisi=Ya';

        $this->mrTemplate->AddVar('content', 'URL_YA', $url_ya);
        $this->mrTemplate->AddVar('content', 'URL_TIDAK', Dispatcher::Instance()->GetUrl('jurnal_penerimaan', 'jurnalPenerimaan', 'view', 'html'));

        if (isset($data['msg'])) {
            $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
            $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['msg']['message']);
            if ($data['msg']['action'] == 'msg')
                $class = 'notebox-done';
            else
                $class = 'notebox-warning';
            $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
            $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['msg']['message']);
            $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $class);
        }
    }

}

?>
