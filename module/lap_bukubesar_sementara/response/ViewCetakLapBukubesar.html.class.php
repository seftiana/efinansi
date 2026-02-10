<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
    'module/lap_bukubesar_sementara/business/AppLapBukubesar.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
    'main/function/date.php';

class ViewCetakLapBukubesar extends HtmlResponse {
   #var $Pesan;

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 
      'module/lap_bukubesar_sementara/template');
      $this->SetTemplateFile('view_cetak_lap_bukubesar.html');
   }
   
    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-print.html');
      $this->SetTemplateFile('layout-common-print-custom-header.html');
    }
   
   function ProcessRequest() {
      $Obj = new AppLapBukubesar();
      $_GET = $_GET->AsArray();
      $rekening = Dispatcher::Instance()->Decrypt($_GET['rekening']);
      $tgl_awal = Dispatcher::Instance()->Decrypt($_GET['tgl_awal']);
      $tgl_akhir = Dispatcher::Instance()->Decrypt($_GET['tgl_akhir']);
      $sub_akun = Dispatcher::Instance()->Decrypt($_GET['sub_account']);
      
        if($sub_akun == '01-00-00-00-00-00-00'){
            $header = strtoupper(GTFWConfiguration::GetValue('organization', 'header_lap_yayasan'));
        }elseif($sub_akun == '00-00-00-00-00-00-00'){
            $header = strtoupper(GTFWConfiguration::GetValue('organization', 'header_lap_perbanas'));
        }else{
            $header = strtoupper(GTFWConfiguration::GetValue('organization', 'company_name'));
        }

      $data_cetak = $Obj->GetBukuBesarHis($rekening, $tgl_awal, $tgl_akhir,$sub_akun);
      $info_coa = $Obj->GetInfoCoa($rekening);
      #print_r($data_cetak); exit;
      $return['rekening'] = $rekening;
      $return['tgl_awal'] = $tgl_awal;
      $return['tgl_akhir'] = $tgl_akhir;
      $return['bbhis'] = $data_cetak;
      $return['info_coa'] = $info_coa;
      $return['header']= $header;
      return $return;
   }
   
   function ParseTemplate($data = NULL) {
       $dataList = $data['bbhis'];
        $this->mrTemplate->AddVar('content', 'HEADER_LAPORAN', $data['header']);
        if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         $this->mrTemplate->AddVar('content', 'REKENING', $data['rekening']);
         $this->mrTemplate->AddVar('content', 'TGL_AWAL', IndonesianDate($data['tgl_awal'], 'yyyy-mm-dd'));
         $this->mrTemplate->AddVar('content', 'TGL_AKHIR', IndonesianDate($data['tgl_akhir'], 'yyyy-mm-dd'));
         $this->mrTemplate->AddVar('content', 'REKENING', $data['info_coa']['rekening']);
         $this->mrTemplate->AddVar('content', 'NO_REKENING', $data['info_coa']['no_rekening']);
         #print_r($data['saldo']); exit;

    
         $kodeAkun = '';
         $items = array();
         $max = sizeof($dataList);
         $nk = 0;         
         $saldo = 0;
         $saldoAkhir = 0;
         ini_set('memory_limit', '1024M');
         ini_set('max_execution_time', '0');
         for($k = 0; $k < $max;){
             
            if($kodeAkun == $dataList[$k]['akun_kode']) {       
                switch ($dataList[$k]['kelompok_id']) {
                    case 1://aktiva
                    case 5://beban
                        $saldo = $dataList[$k]['debet'] - $dataList[$k]['kredit'];
                        break;
                    case 2://kewajiban
                    case 3://modal
                    case 4://pendapatan
                        $saldo = $dataList[$k]['kredit'] - $dataList[$k]['debet'];
                        break;
                    default ://default
                        $saldo = $dataList[$k]['debet'] - $dataList[$k]['kredit'];
                        break;
                }

                $items[$nk]['akun_kode'] ='';
                $items[$nk]['akun_nama'] ='';
                $items[$nk]['tanggal_jurnal_entri'] = $dataList[$k]['tanggal_jurnal_entri'];
                $items[$nk]['sub_account'] = $dataList[$k]['sub_account'];
                $items[$nk]['keterangan'] = $dataList[$k]['keterangan'];
                $items[$nk]['nomor_referensi'] = $dataList[$k]['nomor_referensi'];
              
                 if($dataList[$k]['debet'] > 1 ) {
                    $items[$nk]['debet'] = number_format($dataList[$k]['debet'] ,2,',','.');    
                } else {
                    $items[$nk]['debet'] = '(' . number_format(($dataList[$k]['debet'] * (-1)),2,',','.') . ')';
                }
                               
                 if($dataList[$k]['kredit'] > 1 ) {
                    $items[$nk]['kredit'] = number_format($dataList[$k]['kredit'] ,2,',','.');    
                } else {
                    $items[$nk]['kredit'] = '(' . number_format(($dataList[$k]['kredit'] * (-1)),2,',','.') . ')';
                }
                                    
                $saldoAkhir += ($saldo);
                
                if($saldoAkhir  > 1 ) {
                    $items[$nk]['saldo_akhir'] = number_format($saldoAkhir ,2,',','.');    
                } else {
                    $items[$nk]['saldo_akhir'] = '(' . number_format(($saldoAkhir * (-1)),2,',','.') . ')';
                }
                
               
                if(isset($dataList[$k + 1]['akun_kode'])) {
                    $cek = $dataList[$k + 1]['akun_kode'];
                } else {
                    $cek = null;
                }
                
                 if($kodeAkun != $cek){

                    $this->mrTemplate->SetAttribute('jumlah', 'visibility', 'visible');
                    $this->mrTemplate->AddVar('jumlah', 'SALDO_AKHIR', $items[$nk]['saldo_akhir'] );
                } else {
                    $this->mrTemplate->SetAttribute('jumlah', 'visibility', 'hidden');
                }
                
                $this->mrTemplate->AddVar('data_item_grid', 'HEADER','NO');                
                $this->mrTemplate->AddVars('data_item_grid', $items[$nk]);
                
                
                $k++;
            } elseif($kodeAkun != $dataList[$k]['akun_kode']) {
                $kodeAkun =  $dataList[$k]['akun_kode'];
                $saldo = 0;
                $saldoAkhir = $dataList[$k]['saldo_awal'];
                $items[$nk]['akun_kode'] = '';
                $items[$nk]['akun_nama'] = '';
                $items[$nk]['tanggal_jurnal_entri'] = '';
                $items[$nk]['sub_account'] = '';
                $items[$nk]['keterangan'] = $kodeAkun.' - '.$dataList[$k]['akun_nama'];
                $items[$nk]['nomor_referensi'] = '';
                if($dataList[$k]['saldo_awal'] > 1 ) {
                    $items[$nk]['saldo_awal'] = number_format($dataList[$k]['saldo_awal'],2,',','.');    
                } else {
                    $items[$nk]['saldo_awal'] = '(' . number_format(($dataList[$k]['saldo_awal'] * (-1)),2,',','.') . ')';
                }
                
                $items[$nk]['debet'] = '';
                $items[$nk]['kredit'] = '';
                $items[$nk]['saldo_akhir'] = '';   
                $this->mrTemplate->AddVar('data_item_grid', 'HEADER','YES');
                $this->mrTemplate->AddVars('data_item_grid', $items[$nk]);
            }
            
            $this->mrTemplate->AddVars('data_item', $items[$nk]);
            $this->mrTemplate->parseTemplate('data_item', 'a');
            $nk++; 
         }

       }
   }
   
}
?>