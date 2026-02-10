<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
'module/lap_rincian_pendapatan_per_unit/business/AppLapRincianPendapatanPerUnit.class.php';
   
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewCetakLapRincianPendapatanPerUnit extends HtmlResponse 
{
   private $mLRPPU;
   
   public function TemplateModule() 
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 
      'module/lap_rincian_pendapatan_per_unit/template');
      $this->SetTemplateFile('cetak_lap_rincian_pendapatan_per_unit.html');
   }

   public function TemplateBase() 
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 
      'main/template/');
     $this->SetTemplateFile('document-print.html');
      $this->SetTemplateFile('layout-common-print.html');
   }

   public function ProcessRequest() 
   {
      echo 'cetak';
      $this->mLRPPU     = new AppLapRincianPendapatanPerUnit();
      $tahun_anggaran   = Dispatcher::Instance()->Decrypt($_GET['tgl']);
      $unitkerja_label  = Dispatcher::Instance()->Decrypt($_GET['unitkerja_label']);
      $unitkerja        = Dispatcher::Instance()->Decrypt($_GET['unitkerja']);
      $userId           = Dispatcher::Instance()->Decrypt($_GET['id']);
      $data             = $this->mLRPPU->GetData($tahun_anggaran, $unitkerja);
      //$data_jumlah = $Obj->GetDataForTotal($tahun_anggaran, $unitkerja);
      //$unitkerja = $Obj->GetUnitKerja($unitkerjaId);
      //$tahunanggaran = $Obj->GetTahunAnggaran($tahun_anggaran);
      //$jml = count($data_jumlah);
      //$tot_jumlah = 0;
      //$tot_terima = 0;
      //for($i=0;$i<=$jml;$i++){
      // $tot_jumlah += $data_jumlah[$i]['tot_jumlah'];
      // $tot_terima += $data_jumlah[$i]['tot_terima'];
      //}
      $return['data']               = $data;
      $return['tahunanggaran_nama'] = $tahunanggaran['name'];
      $return['unitkerja_nama']     = $unitkerja_label;
      $return['penerimaan']         = $tot_jumlah;
      $return['jumlah']             = $tot_terima;
      $return['ta']                 = $this->mLRPPU->GetTahunAnggaran($tahun_anggaran);
      return $return;
   }

   public function ParseTemplate($data = NULL) 
   {
      $this->mrTemplate->AddVar('content', 'TAHUN_PERIODE', ($data['ta']['name']));
      $this->mrTemplate->AddVar('content', 'UNIT_KERJA_LABEL', ($data['unitkerja_nama']));
      $this->mrTemplate->AddVar('content', 'TAHUN_SEKARANG_TH_ANGGAR', ($data['ta']['name']));
      $this->mrTemplate->AddVar('content', 'TAHUN_DEPAN_TH_ANGGAR', ($data['ta']['tahun_tutup'] + 1));
      if (empty($data['data'])) {
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data_grid', 'IS_DATA_EMPTY', 'NO');
         $dataLaporan = $data['data'];
         //print_r($dataLaporan);
         $sdId    = '';
         $total   = '';
         $send    = array();
         $no      = 0;
         for($k = 0; $k < sizeof($dataLaporan);){
            $total += $dataLaporan[$k]['target_sekarang'];
            if($dataLaporan[$k]['sd_id'] == $sdId){
               $send[$k]['kode']    = $dataLaporan[$k]['mak_kode'];
               $send[$k]['nama']    = $dataLaporan[$k]['mak_nama'];
               $send[$k]['target_sekarang'] = $this->mLRPPU->SetFormatAngka($dataLaporan[$k]['target_sekarang']);
               $send[$k]['real_sekarang']    = $this->mLRPPU->SetFormatAngka($dataLaporan[$k]['realisasi']);
               $send[$k]['persen']           = 0;
               $send[$k]['target_depan']     = 0;
               $send[$k]['font_style']       = '';
               $this->mrTemplate->AddVars('list_data_item', $send[$k], '');
               $this->mrTemplate->parseTemplate('list_data_item', 'a');
               $k++;
            }elseif($dataLaporan[$k]['sd_id'] != $sdId){
               $sdId             = $dataLaporan[$k]['sd_id'];
               $no++;
               $send[$k]['kode'] = $no;
               $send[$k]['nama'] = $dataLaporan[$k]['sd_nama'];
               $send[$k]['target_sekarang']  ='';
               $send[$k]['real_sekarang']    = '';
               $send[$k]['persen']           = '';
               $send[$k]['target_depan']     = '';
               $send[$k]['font_style']       = 'font-style: italic;';
               $this->mrTemplate->AddVars('list_data_item', $send[$k], '');
               $this->mrTemplate->parseTemplate('list_data_item', 'a');
            }     
          }
         }
   }
}
?>