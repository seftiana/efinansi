<?php
if(isset($_GET['request'])){
   require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
   'module/history_transaksi_pencairan/business/'.$_GET['request'].'/AppTransaksi.class.php';
}
require_once GTFWConfiguration::GetValue('application','docroot').
'main/function/terbilang.php';

class ViewFormCetakTransaksi extends HtmlResponse {
   var $Data;
   var $Pesan;

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
      'module/history_transaksi_pencairan/template');
      $this->SetTemplateFile('view_form_cetak_transaksi.html');
   }

   function ProcessRequest() {
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

      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'pejabat_pembantu_rektor',
         array(
            'pejabat_pembantu_rektor',
            $arr_pejabat_pembantu_rektor,
            '',
            '-',
            ' id="pejabat_pembantu_rektor" style="width:200px;" '
         ), Messenger::CurrentRequest);

      Messenger::Instance()->SendToComponent(
         'combobox',
         'Combobox',
         'view',
         'html',
         'pejabat_bendahara',
         array(
            'pejabat_bendahara',
            $arr_pejabat_bendahara,
            '',
            '-',
            ' id="pejabat_bendahara" style="width:200px;" '
         ), Messenger::CurrentRequest);
      $return['data_list']    = $dataList;
      $return['message']      = $message;
      $return['style']        = $style;
      $return['query_string'] = $queryString;
      $return['request_data'] = $requestData;

      /*$idDec   = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
      $Obj     = new AppTransaksi();
      $msg     = Messenger::Instance()->Receive(__FILE__);
      $this->Pesan = $msg[0][1];
      $this->Data = $msg[0][0];
      $arr_tahun_anggaran = $Obj->GetComboTahunAnggaran();
      $tahun_anggaran_aktif = $Obj->GetComboTahunAnggaranAktif();
      Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'tahun_anggaran', array('tahun_anggaran', $arr_tahun_anggaran, $tahun_anggaran_aktif['aktif'], '-', ' id="tahun_anggaran" style="width:200px;" '), Messenger::CurrentRequest);
      $arr_pejabat_pembantu_rektor = $Obj->GetJabatanNama('PR');
      Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'pejabat_pembantu_rektor', array('pejabat_pembantu_rektor', $arr_pejabat_pembantu_rektor, '', '-', ' id="pejabat_pembantu_rektor" style="width:200px;" '), Messenger::CurrentRequest);

      $arr_pejabat_bendahara = $Obj->GetJabatanNama('BENDAHARA');
      Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'pejabat_bendahara', array('pejabat_bendahara', $arr_pejabat_bendahara, '', '-', ' id="pejabat_bendahara" style="width:200px;" '), Messenger::CurrentRequest);

      $dataForm = $Obj->GetDataFormCetak($idDec);
      $mak = $Obj->GetTransaksiMAK($idDec);

      $return['mak'] = $mak;
      $return['decDataId'] = $idDec;
      $return['dataForm'] = $dataForm;*/
      return $return;
   }

   function ParseTemplate($data = NULL) {
      $mNumber       = new Number();
      $dataList      = $data['data_list'];
      $requestData   = $data['request_data'];
      $dataList['keterangan'] = ($dataList['keterangan'] == '') ? '-' : $dataList['keterangan'];
      $dataList['terbilang']  = $mNumber->Terbilang($dataList['nominal'], 3).' Rupiah';
      if($dataList['nominal'] < 0){
         $dataList['nominal_label'] = '('.number_format(abs($dataList['nominal']), 2, ',','.').')';
      }else{
         $dataList['nominal_label'] = number_format($dataList['nominal'], 2, ',','.');
      }
      $dataInvoices  = $data['invoices'];
      $queryString   = $data['query_string'];
      $urlReturn     = Dispatcher::Instance()->GetUrl(
         'history_transaksi_pencairan',
         'HTRealisasiPencairan',
         'view',
         'html'
      ).'&search=1&'.$queryString;
      $urlAction     = Dispatcher::Instance()->GetUrl(
         'history_transaksi_pencairan',
         'CetakTransaksi',
         'do',
         'xlsx'
      ).'&'.$queryString;

      $this->mrTemplate->AddVars('content', $dataList);
      $this->mrTemplate->AddVars('content', $requestData);
      $this->mrTemplate->AddVar('content', 'URL_RETURN', $urlReturn);
      $this->mrTemplate->AddVar('content', 'URL_ACTION', $urlAction);

      /*$get_name = $_GET['request'];
      if ($this->Pesan) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
      }
      $this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('history_transaksi_pencairan', 'cetakTransaksi', 'view', 'html') . "&dataId=" . Dispatcher::Instance()->Encrypt($data['decDataId']).
      '&request='.$get_name);
      $this->mrTemplate->AddVar('content', 'URL_CETAK', Dispatcher::Instance()->GetUrl('history_transaksi_pencairan', 'cetakTransaksi', 'view', 'html') . "&dataId=" . Dispatcher::Instance()->Encrypt($data['decDataId']).
      '&request='.$get_name);


      switch($get_name){
         case 'transaksi': $submodul = 'HTTransaksi'; break;
         case 'transaksi_kode_jurnal': $submodul = 'HTKodeJurnal'; break;
         case 'transaksi_kode_jurnal_penerimaan': $submodul = 'HTKodeJurnalPenerimaan'; break;
         case 'transaksi_kode_jurnal_pengeluaran': $submodul = 'HTKodeJurnalPengeluaran'; break;
         case 'transaksi_penerimaan': $submodul = 'HTPenerimaan'; break;
         case 'transaksi_pengeluaran': $submodul = 'HTPengeluaran'; break;
         case 'transaksi_realisasi': $submodul = 'HTRealisasiPencairan'; break;
         case 'transaksi_realisasi_kode_jurnal': $submodul = 'HTRealisasiPencairanKodeJurnal'; break;
         case 'transaksi_realisasi_penerimaan': $submodul = 'HTRealisasiPenerimaan'; break;
         case 'transaksi_spj': $submodul = 'HTSpj'; break;
         case 'transaksi_spj_pertransaksi': $submodul = 'HTSpjPerTransaksi'; break;
      }
      $this->mrTemplate->AddVar('content', 'URL_BATAL', Dispatcher::Instance()->GetUrl('history_transaksi_pencairan', $submodul, 'view', 'html'));
      $dataForm = $data['dataForm'];
      //$this->mrTemplate->AddVar('content', 'TAHUN_ANGGARAN', $dataForm['tahun_anggaran']);
      $this->mrTemplate->AddVar('content', 'NOMOR_BUKTI', $dataForm['nomor_bukti']);
      $this->mrTemplate->AddVar('content', 'MAK', $data['mak']['nama']);
      $this->mrTemplate->AddVar('content', 'JUDUL', 'KUITANSI/BUKTI PEMBAYARAN');
      $this->mrTemplate->AddVar('content', 'TERIMA_DARI', 'Pengguna Anggaran '.GTFWConfiguration::GetValue('organization', 'company_name'));
      $this->mrTemplate->AddVar('content', 'NILAI', $dataForm['nilai']);
      $this->mrTemplate->AddVar('content', 'NILAI_LABEL', number_format($dataForm['nilai'], 2, '.', ','));
      $this->mrTemplate->AddVar('content', 'NILAI_TERBILANG', $this->terbilang($dataForm['nilai']));
      $this->mrTemplate->AddVar('content', 'UNTUK_PEMBAYARAN', $dataForm['untuk_pembayaran']);
      $this->mrTemplate->AddVar('content', 'PEMBUAT_KWITANSI', $dataForm['pembuat_kwitansi']);
      $this->mrTemplate->AddVar('content', 'NAMA_PEMBUAT_KWITANSI', $dataForm['nama_pembuat_kwitansi']);
      $this->mrTemplate->AddVar('content', 'TGL_PEMBAYARAN', $dataForm['tgl_pembayaran']);
      $this->mrTemplate->AddVar('content', 'PEJABAT_PEMBANTU_REKTOR', $dataForm['pejabat_pembantu_rektor']);
      $this->mrTemplate->AddVar('content', 'PEJABAT_BENDAHARA', $dataForm['pejabat_bendahara']);*/

      //$this->mrTemplate->AddVar('content', 'DATAID', Dispatcher::Instance()->Decrypt($_GET['dataId']));
   }
}
?>