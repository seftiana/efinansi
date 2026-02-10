<?php

require_once GTFWConfiguration::GetValue('application','docroot').
    'module/finansi_sppu/business/Sppu.class.php';

class ViewKonfirmasiCetak extends HtmlResponse {

    public function TemplateModule() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
                'module/finansi_sppu/template/');
        $this->SetTemplateFile('view_konfirmasi_cetak.html');
    }

    public function ProcessRequest() {
        $mObj          = new Sppu();
        $messenger     = Messenger::Instance()->Receive(__FILE__);
        $dataId        = Dispatcher::Instance()->Decrypt($mObj->_GET['data_id']);
        $dataSppu      = $mObj->getDataDetailSppu($dataId);
        $dataTransBank = $mObj->getDataTransaksiBank($dataId);

        $queryString   = $mObj->_getQueryString();
        $queryReturn   = ($queryString == '') ? '' : '&search=1&'.$queryString;   
        $setDate       = $mObj->setDate();
        $minYear       = (int)$setDate['min_year'];
        $maxYear       = (int)$setDate['max_year'];

        $tanggalSppu    = date('Y-m-d', strtotime($dataSppu['tanggal']));     
        $tipe           = Dispatcher::Instance()->Decrypt($mObj->_GET['tipe']);
         $message       = $style = $messengerData = NULL;

        if($dataTransBank != ''){
            $tanggalTampil    = date('Y-m-d', strtotime($dataTransBank['tanggal']));
        }else{
            $tanggalTampil    = date('Y-m-d');
        }
        
        switch ($tipe) {
            case '1' :
                    $tipeNama = 'Bank Payment';
                    $fileName = 'CetakSppu';
                    $action   = 'do';
                    $type     = 'json';
                    break;
            case '2' :
                    $tipeNama = 'Cash Receipt';
                    $fileName = 'ExportExcelCr';
                    $action   = 'view';
                    $type     = 'xlsx';
                    break;                
            default :
                    $tipeNama = 'Bank Payment';
                    $fileName = 'CetakSppu';
                    $action   = 'do';
                    $type     = 'json';
                    break;                
        }

        # GTFW Tanggal
        Messenger::Instance()->SendToComponent(
            'tanggal',
            'Tanggal',
            'view',
            'html',
            'tanggal',
            array(
                $tanggalTampil,
                $minYear,
                $maxYear,
                false,
                false,
                false
            ),
            Messenger::CurrentRequest
        );

        if($messenger){
            $messengerData    = $messenger[0][0];
            $message          = $messenger[0][1];
            $style            = $messenger[0][2];
        }

        $return['message']         = $message;
        $return['style']           = $style;
        $return['data_sppu']    = $dataSppu;
        $return['data_trans_bank'] = $dataTransBank;
        $return['query_return'] = $queryReturn;
        $return['query_string'] = $queryString;  
        $return['action']       = $action;   
        $return['tanggal_sppu']    = $tanggalSppu;   
        $return['tanggal_tampil']  = $tanggalTampil;
        $return['tipe_nama'] = $tipeNama;        
        $return['file_name'] = $fileName;        
        return $return;
    }

    public function ParseTemplate($data = null) {
        $dataSppu = $data['data_sppu'];
        $dataTransBank = $data['data_trans_bank'];
        $tipeNama = $data['tipe_nama'];
        $fileName = $data['file_name'];
        $action   = $data['action'];
        $tanggalSppu    = $data['tanggal_sppu'];
        $tanggalTampil  = $data['tanggal_tampil'];
        $queryString      = $data['query_string'];
        $requestQuery     = $data['query_return'];
        $message       = $data['message'];
        $style         = $data['style'];
        
        $this->mrTemplate->AddVar('content', 'TIPE_NAMA', $tipeNama);
        $this->mrTemplate->AddVar('content', 'NOMOR', $dataSppu['nomor']);
        $this->mrTemplate->AddVar('content', 'ID', $dataSppu['id']);
        $this->mrTemplate->AddVar('content', 'NOMOR_CR', $dataSppu['nomor_cr']);
        $this->mrTemplate->AddVar('content', 'NOMOR_BP', $dataSppu['nomor_bp']);
        $this->mrTemplate->AddVar('content', 'NOMOR_BUKTI', $dataSppu['nomor_bukti']);
        $this->mrTemplate->AddVar('content', 'NOMOR_BANK', $dataTransBank['nomor_bank']);
        $this->mrTemplate->AddVar('content', 'BANK', $dataSppu['bank']);
        $this->mrTemplate->AddVar('content', 'TANGGAL_SPPU', $tanggalSppu);
        $this->mrTemplate->AddVar('content', 'TANGGAL_SPPU_HIDE', $tanggalSppu);
        $this->mrTemplate->AddVar('content', 'TANGGAL_BANK', $dataTransBank['tanggal']);
        $this->mrTemplate->AddVar('content', 'TANGGAL', $tanggalTampil);
        $this->mrTemplate->AddVar('content', 'NOMOR_REKENING', $dataSppu['nomor_rekening']);
        $this->mrTemplate->AddVar('content', 'NOMOR_CEK_GIRO', $dataSppu['nomor_cek_giro']);
        $this->mrTemplate->AddVar('content', 'NOMOR', $dataSppu['nomor']);
        $this->mrTemplate->AddVar('content', 'BANK_PAYMENT', $dataSppu['bank_payment']);
        $this->mrTemplate->AddVar('content', 'CASH_RECEIPT', $dataSppu['cash_receipt']);
        $this->mrTemplate->AddVar('content', 'NOMINAL_TAMPIL', number_format($dataSppu['nominal'], 2, ',','.'));
        $this->mrTemplate->AddVar('content', 'NOMINAL', $dataSppu['nominal']);

        $urlReturn  = Dispatcher::Instance()->GetUrl(
           'finansi_sppu',
           'ListSppu',
           'view',
           'html'
        ).'&search=1&'.$queryString;

        $urlAction = Dispatcher::Instance()->GetUrl(
            'finansi_sppu',
            'CetakSppu',
            'do',
            'html'
            ).'&'.$queryString;

        $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlReturn);
        $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);


      if($message){
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $message);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $style);
      }
      
    }

}

?>