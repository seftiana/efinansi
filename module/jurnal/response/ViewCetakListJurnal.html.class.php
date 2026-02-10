<?php
/*
   @ClassName : ViewCetakListJurnal
   @Copyright : PT Gamatechno Indonesia
   @Analyzed By : Dyan Galih <galih@gamatechno.com>
   @Author By : Dyan Galih <galih@gamatechno.com>
   @Version : 0.1
   @StartDate : 2010-05-25
   @LastUpdate : 2010-05-25
   @Description :
*/

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/jurnal/business/Jurnal.class.php';

class ViewCetakListJurnal extends HtmlResponse {

   function TemplateBase(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-print.html');
      $this->SetTemplateFile('layout-common-print-custom-header.html');
    }

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue( 'application', 'docroot').'module/jurnal/template');
      $this->SetTemplateFile('view_cetak_list_jurnal.html');
   }

   function ProcessRequest() {

      $noReferensi = Dispatcher::Instance()->Decrypt($_GET['no_referensi']);
      $tahun = Dispatcher::Instance()->Decrypt($_GET['tahun']);
      $bulan = Dispatcher::Instance()->Decrypt($_GET['bulan']);
      $sub_account = Dispatcher::Instance()->Decrypt($_GET['sub_account']);
      $tampilkanSemua = Dispatcher::Instance()->Decrypt($_GET['tampilkanSemua']);

        if($sub_account == '01-00-00-00-00-00-00'){
            $header = strtoupper(GTFWConfiguration::GetValue('organization', 'header_lap_yayasan'));
        }elseif($sub_account == '00-00-00-00-00-00-00'){
            $header = strtoupper(GTFWConfiguration::GetValue('organization', 'header_lap_perbanas'));
        }else{
            $header = strtoupper(GTFWConfiguration::GetValue('organization', 'header_lap_all'));
        }

      if(is_object($bulan)){
          $bulanN = $bulan->mrVariable;
      } else {
          $bulanN = $bulan;
      }
     
      if(is_object($tampilkanSemua)){
          $tampilkanSemuaN = $tampilkanSemua->mrVariable;
      } else {
          $tampilkanSemuaN = $tampilkanSemua;
      } 
      
      $this->objJurnal = new Jurnal();
      if($tampilkanSemuaN === 'true') {
        $periode = $this->objJurnal->GetPeriodePembukuanAktif();
        $tglAwal = $periode['tanggal_awal'];
        $tglAkhir = $periode['tanggal_akhir'];
        $return['data'] = $this->objJurnal->GetDataAllCetak($tglAwal,$tglAkhir);
      } else {
        $return['data'] = $this->objJurnal->GetDataCetak($noReferensi, $sub_account, $tahun, $bulanN);
      }
      $return['data_bulan'] = $bulanN;
      $return['data_tahun'] = $tahun;
      
      $return['tanggal_awal'] = $tglAwal;
      $return['tanggal_akhir'] = $tglAkhir;
      $return['tampilkan_semua'] = $tampilkanSemuaN; 
      $return['header']= $header;
      
      return $return;
   }

   function ParseTemplate($data = NULL) 
   {
      
      if($data['tampilkan_semua'] === 'true') {
          
          $bulanAwal = date("n", strtotime($data['tanggal_awal']));
          $tahunAwal = date("Y", strtotime($data['tanggal_awal']));
          
          $bulanAkhir = date("n", strtotime($data['tanggal_akhir']));
          $tahunAkhir = date("Y", strtotime($data['tanggal_akhir']));
          $this->mrTemplate->AddVar('content_title', 'PERIODE','ALL');
          
          $this->mrTemplate->AddVar('content_title', 'BULAN_AWAL',$this->objJurnal->month2string($bulanAwal));
          $this->mrTemplate->AddVar('content_title', 'TAHUN_AWAL', $tahunAwal);
          $this->mrTemplate->AddVar('content_title', 'BULAN_AKHIR',$this->objJurnal->month2string($bulanAkhir));
          $this->mrTemplate->AddVar('content_title', 'TAHUN_AKHIR', $tahunAkhir);
      } else {
          $this->mrTemplate->AddVar('content_title', 'PERIODE','BULAN');
          
          $this->mrTemplate->AddVar('content_title', 'BULAN',$this->objJurnal->month2string($data['data_bulan']));
          $this->mrTemplate->AddVar('content_title', 'TAHUN', $data['data_tahun']);
      }
      
      $this->mrTemplate->AddVar('content', 'BULAN',$this->objJurnal->month2string($data['data_bulan']));
      $this->mrTemplate->AddVar('content', 'TAHUN', $data['data_tahun']);
        $this->mrTemplate->AddVar('content', 'HEADER_LAPORAN', $data['header']);
            
      if (empty($data['data']))
      {
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'YES');
      }
      else
      {
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'NO');
         $dataGrid = $data['data'];
         $nomor = $data['start'];
         $referensi_id = $dataGrid[0]['id'];
         $flag_i = 0;
         $first = true;
         $nomor = 1;
                                                                                                         
         $refId = '';
         $dataGridx = array();
         $ix = 0;
         for ($i = 0;$i < sizeof($dataGrid);)
         {
            if($refId == $dataGrid[$i]['id']) {                
                $idEnc = Dispatcher::Instance()->Encrypt($dataGrid[$i]['id']);
                $id = Dispatcher::Instance()->Encrypt($dataGrid[$i]['transId']);
                $dataGridx[$ix]['nomor'] = $nomor;
                $dataGridx[$ix]['rekening_kode'] = $dataGrid[$i]['rekening_kode'];
                $dataGridx[$ix]['rekening_nama'] = $dataGrid[$i]['rekening_nama'];   

                //menentukan tampilan debet atau kredit

                if (strtoupper($dataGrid[$i]['tipeakun']) == 'D') $dataGridx[$ix]['debet'] = number_format($dataGrid[$i]['nilai'], 2, ',', '.');
                elseif (strtoupper($dataGrid[$i]['tipeakun']) == 'K') $dataGridx[$ix]['kredit'] = number_format($dataGrid[$i]['nilai'], 2, ',', '.');
                $dataGridx[$ix]['tanggal'] = $this->objJurnal->date2string($dataGrid[$i]['tanggal']);

                if ($dataGrid[$i]['is_posting'] == 'Y') 
                    $dataGridx[$ix]['class_name'] = 'table-common-even2';
                else 
                    $dataGridx[$ix]['class_name'] = '';
            
                $dataGridx[$ix]['referensi_view'] = '';
                $dataGridx[$ix]['tanggal_view'] = '';
                $dataGridx[$ix]['petugas_entri_view'] = '';
                $dataGridx[$ix]['aksi_view'] = '';
                $dataGridx[$ix]['nomor'] = '';
                $dataGridx[$ix]['font'] = 'normal';
                
                $dataGridx[$ix]['type'] = 'coa';
                
                $i++;

            } elseif($refId != $dataGrid[$i]['id']){
                
                $refId = $dataGrid[$i]['id'];
                $dataGridx[$ix]['nomor'] = $nomor ;
                $dataGridx[$ix]['rekening_kode'] = $this->objJurnal->date2string($dataGrid[$i]['tanggal']);
                if(!empty($dataGrid[$i]['catatan'])) {
                    $keterangan =  $dataGrid[$i]['catatan'];
                } else {
                     $keterangan = '-';
                }
                
                $dataGridx[$ix]['referensi_view'] = $dataGrid[$i]['referensi'];
                $dataGridx[$ix]['rekening_nama'] = $keterangan;
                $dataGridx[$ix]['petugas_entri_view'] = $dataGrid[$i]['petugas_entri'];
               
                $dataGridx[$ix]['debet'] = '';
                $dataGridx[$ix]['kredit'] = '';
                $dataGridx[$ix]['font'] = 'bold';
                $dataGridx[$ix]['type'] = 'grup';
               
                $nomor++;   
                
            }

            if ($dataGrid[$i]['is_posting'] == 'Y') 
                $dataGridx[$ix]['class_name'] = 'table-common-even2';
            else 
                $dataGridx[$ix]['class_name'] = '';
            
            $ix++;
         }
       

         //debug($dataGrid);

         foreach($dataGridx as $key => $val)
         {
             if($val['type'] == 'grup') {
                if($key > 0) {
                    $this->mrTemplate->SetAttribute('space', 'visibility', 'visible');
                } else {
                    $this->mrTemplate->SetAttribute('space', 'visibility', 'hidden');
                }
                $this->mrTemplate->AddVars('grup', $val, 'DATA_');
                $this->mrTemplate->SetAttribute('grup', 'visibility', 'visible');
                $this->mrTemplate->SetAttribute('coa', 'visibility', 'hidden');
             } else {
                $this->mrTemplate->AddVars('coa', $val, 'DATA_');
                $this->mrTemplate->SetAttribute('grup', 'visibility', 'hidden');
                $this->mrTemplate->SetAttribute('coa', 'visibility', 'visible');
                $this->mrTemplate->SetAttribute('space', 'visibility', 'hidden');
             }
             //$this->mrTemplate->AddVars('data_item', $val, 'DATA_');
                $this->mrTemplate->parseTemplate('data_item', 'a'); 
         }
      }
   }
}
?>