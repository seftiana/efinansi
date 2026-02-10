<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/history_transaksi_realisasi/business/AppTransaksi.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'main/function/terbilang.php';

class ViewFormCetakBKK extends HtmlResponse
{

   public $Data;
   public $Pesan;

   public function TemplateModule()
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
         'module/history_transaksi_realisasi/template');
      $this->SetTemplateFile('view_form_cetak_bkk.html');
   }

   public function ProcessRequest()
   {
      $messenger  = Messenger::Instance()->Receive(__FILE__);
      $mObj       = new AppTransaksi();
      $transId    = Dispatcher::Instance()->Decrypt($mObj->_GET['dataId']);
      $message    = $style = NULL;
      $setDate             = $mObj->setDate();
      $minYear             = (int)$setDate['min_year'];
      $maxYear             = (int)$setDate['max_year'];
      $getdate             = getdate();
      $currDay             = (int)$getdate['mday'];
      $currMon             = (int)$getdate['mon'];
      $currYear            = (int)$getdate['year'];
      $dataList            = $mObj->getTransaksiDetail($transId);
      $queryString         = $mObj->_getQueryString();
      $requestData         = array();
      $arr_pejabat_pembantu_rektor  = $mObj->GetJabatanNama('PR');
      $arr_pejabat_bendahara        = $mObj->GetJabatanNama('BENDAHARA');

      $requestData['tanggal']    = date('Y-m-d', mktime(0,0,0, $currMon, $currDay, $currYear));
      $requestData['penyetor']   = 'Pengguna Anggaran '.GTFWConfiguration::GetValue('organization', 'company_name');
      $requestData['pembuat_kwitansi'] = Security::Instance()->mAuthentication->GetCurrentUser()->GetRealName();

      if($messenger){
         $message       = $messenger[0][1];
         $style         = $messenger[0][2];
      }

      # GTFW Tanggal
      Messenger::Instance()->SendToComponent(
         'tanggal',
         'Tanggal',
         'view',
         'html',
         'tanggal',
         array(
            $requestData['tanggal'],
            $minYear,
            $maxYear,
            false,
            false,
            false
         ),
         Messenger::CurrentRequest
      );

      $return['data_list']       = $dataList;
      $return['query_string']    = $queryString;
      $return['request_data']    = $requestData;

      /*$idDec = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
      $Obj = new AppTransaksi();
      $msg = Messenger::Instance()->Receive(__FILE__);
      $this->Pesan = $msg[0][1];
      $this->Data = $msg[0][0];

      $arr_pejabat_biro_keuangan = $Obj->GetPerjabatRef();
      Messenger::Instance()->SendToComponent(
                                    'combobox',
                                    'Combobox',
                                    'view',
                                    'html',
                                    'pejabat_diketahui',
                                    array(
                                       'pejabat_diketahui',
                                       $arr_pejabat_biro_keuangan,
                                       '',
                                       'false',
                                       ' id="pejabat_diketahui" style="width:200px;" '),
                                    Messenger::CurrentRequest);

      Messenger::Instance()->SendToComponent(
                                    'combobox',
                                    'Combobox',
                                    'view',
                                    'html',
                                    'pejabat_dikeluarkan',
                                    array(
                                       'pejabat_dikeluarkan',
                                       $arr_pejabat_biro_keuangan,
                                       '',
                                       'false',
                                       ' id="pejabat_dikeluarkan" style="width:200px;" '),
                                    Messenger::CurrentRequest);

      Messenger::Instance()->SendToComponent(
                                    'combobox',
                                    'Combobox',
                                    'view',
                                    'html',
                                    'pejabat_diterima',
                                    array(
                                       'pejabat_diterima',
                                       $arr_pejabat_biro_keuangan,
                                       '',
                                       'false',
                                       ' id="pejabat_diterima" style="width:200px;" '),
                                    Messenger::CurrentRequest);



      $dataForm = $Obj->GetDataFormCetak($idDec);
      $mak = $Obj->GetTransaksiMAK($idDec);
      $return['mak'] = $mak;
      $return['decDataId'] = $idDec;
      $return['dataForm'] = $dataForm;*/

      return $return;
   }

   public function ParseTemplate($data = NULL)
   {
      $mNumber       = new Number();
      $dataList      = $data['data_list'];
      $queryString   = $data['query_string'];
      $requestData   = $data['request_data'];
      $dataList['keterangan'] = ($dataList['keterangan'] == '') ? '-' : $dataList['keterangan'];
      $dataList['terbilang']  = $mNumber->Terbilang($dataList['nominal'], 3).' Rupiah';
      $dataList['penerima']   = ucwords($dataList['penerima']);
      if($dataList['nominal'] < 0){
         $dataList['nominal_label'] = '('.number_format(abs($dataList['nominal']), 2, ',','.').')';
      }else{
         $dataList['nominal_label'] = number_format($dataList['nominal'], 2, ',','.');
      }

      $urlReturn     = Dispatcher::Instance()->GetUrl(
         'history_transaksi_realisasi',
         'HTRealisasiPencairan',
         'view',
         'html'
      ).'&search=1&'.$queryString;
      $urlAction     = Dispatcher::Instance()->GetUrl(
         'history_transaksi_realisasi',
         'CetakBkk',
         'do',
         'xlsx'
      ).'&'.$queryString;

      $this->mrTemplate->AddVars('content', $dataList);
      $this->mrTemplate->AddVars('content', $requestData);
      $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlReturn);
      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);
      /*if ($this->Pesan) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
      }
      $this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('history_transaksi_realisasi', 'CetakBuktiTransaksi', 'view', 'html') . "&dataId=" . Dispatcher::Instance()->Encrypt($data['decDataId']) . '&tipe=bkk');
      $this->mrTemplate->AddVar('content', 'URL_CETAK', Dispatcher::Instance()->GetUrl('history_transaksi_realisasi', 'CetakBuktiTransaksi', 'view', 'html') . "&dataId=" . Dispatcher::Instance()->Encrypt($data['decDataId']) . '&tipe=bkk');

      $this->mrTemplate->AddVar('content', 'URL_BATAL', Dispatcher::Instance()->GetUrl('history_transaksi_realisasi', 'HTRealisasiPencairan', 'view', 'html'));

      $dataForm = $data['dataForm'];
      $this->mrTemplate->AddVar('content', 'NOMOR_BUKTI', $dataForm['nomor_bukti']);

      $this->mrTemplate->AddVar('content', 'TERIMA_DARI', 'Pengguna Anggaran'. GTFWConfiguration::GetValue('organization', 'company_name'));

      $this->mrTemplate->AddVar('content', 'NILAI', $dataForm['nilai']);
      $this->mrTemplate->AddVar('content', 'NILAI_LABEL', number_format($dataForm['nilai'], 2, '.', ','));
      $this->mrTemplate->AddVar('content', 'NILAI_TERBILANG', $this->terbilang($dataForm['nilai']));
      //$this->mrTemplate->AddVar('content', 'UNTUK_PEMBAYARAN', $data['mak']['nama']);
      $this->mrTemplate->AddVar('content', 'UNTUK_PEMBAYARAN', $dataForm['untuk_pembayaran']);
      $this->mrTemplate->AddVar('content', 'CATATAN_TRANSAKSI', $dataForm['untuk_pembayaran']);
      $this->mrTemplate->AddVar('content', 'PEMBUAT_KWITANSI', $dataForm['pembuat_kwitansi']);
      $this->mrTemplate->AddVar('content', 'NAMA_PEMBUAT_KWITANSI', $dataForm['nama_pembuat_kwitansi']);
      $this->mrTemplate->AddVar('content', 'TGL_PEMBAYARAN', $dataForm['tgl_pembayaran']);
      $this->mrTemplate->AddVar('content', 'PEJABAT_PEMBANTU_REKTOR', $dataForm['pejabat_pembantu_rektor']);
      $this->mrTemplate->AddVar('content', 'PEJABAT_BENDAHARA', $dataForm['pejabat_bendahara']);*/
   }
}
?>