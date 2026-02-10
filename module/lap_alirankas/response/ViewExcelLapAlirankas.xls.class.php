<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/lap_alirankas/business/AppLapAliranKas.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';

class ViewExcelLapAlirankas extends XlsResponse {
   var $mWorksheets = array('Data');
   
   function GetFileName() {
      // name it whatever you want
      return 'LapAliranKas.xls';
   }

   function ProcessRequest() {
      $Obj = new AppLapAliranKas();
      $tgl_akhir = Dispatcher::Instance()->Decrypt($_GET['tgl_akhir']);
      $operasional = $Obj->GetDataAliranKasOperasional($tgl_akhir);
      $investasi = $Obj->GetDataAliranKasInvestasi($tgl_akhir);
      $pendanaan = $Obj->GetDataAliranKasPendanaan($tgl_akhir);
      $saldo_coa_aliran_kas = $Obj->GetSaldoCoaAliranKas();
      #$data = 12;
      
      if (empty($operasional) AND empty($investasi) AND empty($pendanaan)) {
         $this->mWorksheets['Data']->write(0, 0, 'Data kosong');
      } else {
         $fTitle = $this->mrWorkbook->add_format();
         $fTitle->set_bold();
         $fTitle->set_size(12);
         $fTitle->set_align('vcenter');
         
         #set colom
         $fColNomorbold = $this->mrWorkbook->add_format();
         $fColNomorbold->set_border(1);
         $fColNomorbold->set_bold();
         $fColNomorbold->set_size(10);
         //$fColKegiatan->set_column(1,1, 300);
         $fColNomorbold->set_align('center');
         
         $fColNomor = $this->mrWorkbook->add_format();
         $fColNomor->set_border(1);
         #$fColNomor->set_bold();
         $fColNomor->set_size(10);
         //$fColKegiatan->set_column(1,1, 300);
         $fColNomor->set_align('center');
         
         $fColNomorakun = $this->mrWorkbook->add_format();
         $fColNomorakun->set_border(1);
         $fColNomorakun->set_size(10);
         $fColNomorakun->set_align('left');
         
         $fColCtn = $this->mrWorkbook->add_format();
         $fColCtn->set_border(1);
         $fColCtn->set_size(10);
         $fColCtn->set_align('left');
         
         $fColNilai = $this->mrWorkbook->add_format();
         $fColNilai->set_border(1);   
         $fColNilai->set_size(10);
         $fColNilai->set_align('right');
         
         $fColCtnitalic = $this->mrWorkbook->add_format();
         $fColCtnitalic->set_border(1);
         $fColCtnitalic->set_italic();
         $fColCtnitalic->set_size(10);
         $fColCtnitalic->set_align('left');
         
         $fColNilaitalic = $this->mrWorkbook->add_format();
         $fColNilaitalic->set_border(1);   
         $fColNilaitalic->set_italic();
         $fColNilaitalic->set_size(10);
         $fColNilaitalic->set_align('right');
         
         #set header
         $this->mWorksheets['Data']->write(0, 0, 'Laporan Aliran Kas', $fTitle);
         $this->mWorksheets['Data']->write(2, 0, 'Untuk Tahun Yang Berakhir Tanggal '.IndonesianDate($tgl_akhir, 'yyyy-mm-dd'), $fTitle);
         
         $no=4;
         $this->mWorksheets['Data']->write($no, 0, 'ARUS KAS DARI AKTIVITAS OPERASI', $fColNomorbold);
         $this->mWorksheets['Data']->write($no, 1, 'Jumlah (Rp.)', $fColNomorbold);
         #$this->mWorksheets['Data']->write($no, 2, 'Status', $fColNomorbold);
         
         $num_operasional = 5;
         
         //operasional
         $total_operasional_tambah = 0;
         $total_operasional_kurang = 0;
         for($i=0; $i<sizeof($operasional); $i++) {
            if ($operasional[$i]['status'] == 'Ya')
               $total_operasional_tambah += $operasional[$i]['nilai'];
            elseif($operasional[$i]['status'] == 'Tidak')
               $total_operasional_kurang -= $operasional[$i]['nilai'];
            $this->mWorksheets['Data']->write($num_operasional, 0, $operasional[$i]['nama_kel_lap'], $fColCtn);
            $this->mWorksheets['Data']->write($num_operasional, 1, number_format($operasional[$i]['nilai'],2, ',', '.'), $fColCtn);
            #$this->mWorksheets['Data']->write($num_operasional, 2, $operasional[$i]['status_lap'], $fColCtn);
            $num_operasional++;
         }
         
         $this->mWorksheets['Data']->write($num_operasional, 0, 'Kas Bersih diperoleh (digunakan untuk) aktivitas operasi', $fColCtn);
         $this->mWorksheets['Data']->write($num_operasional, 1, number_format(($total_operasional_tambah - $total_operasional_kurang), 2, ',', '.'), $fColCtn);
         #$this->mWorksheets['Data']->write($num_operasional, 2, '', $fColCtn);
         
         //investasi
         $num_investasi = $num_operasional+3;
         $this->mWorksheets['Data']->write($num_investasi-1, 0, 'ARUS KAS DARI AKTIVITAS INVESTASI', $fColNomorbold);
         $this->mWorksheets['Data']->write($num_investasi-1, 1, 'Jumlah (Rp.)', $fColNomorbold);
         $total_investasi_tambah = 0;
         $total_investasi_kurang = 0;
         for($i=0; $i<sizeof($investasi); $i++) {
            if ($investasi[$i]['status'] == 'Ya')
               $total_investasi_tambah += $investasi[$i]['nilai'];
            elseif($investasi[$i]['status'] == 'Tidak')
               $total_investasi_kurang += $investasi[$i]['nilai'];
            $this->mWorksheets['Data']->write($num_investasi, 0, $investasi[$i]['nama_kel_lap'], $fColCtn);
            $this->mWorksheets['Data']->write($num_investasi, 1, number_format($investasi[$i]['nilai'],2, ',', '.'), $fColCtn);
            #$this->mWorksheets['Data']->write($num_investasi, 2, $investasi[$i]['status_lap'], $fColCtn);
            $num_investasi++;
         }
         
         $this->mWorksheets['Data']->write($num_investasi, 0, 'Kas Bersih diperoleh (digunakan untuk) aktivitas investasi', $fColCtn);
         $this->mWorksheets['Data']->write($num_investasi, 1, number_format(($total_investasi_tambah - $total_investasi_kurang), 2, ',', '.'), $fColCtn);
         #$this->mWorksheets['Data']->write($num_investasi, 2, '', $fColCtn);
         
         //pendanaan
         $num_pendanaan = $num_investasi+3;
         $this->mWorksheets['Data']->write($num_pendanaan-1, 0, 'ARUS KAS DARI AKTIVITAS PENDANAAN', $fColNomorbold);
         $this->mWorksheets['Data']->write($num_pendanaan-1, 1, 'Jumlah (Rp.)', $fColNomorbold);
         $total_pendanaan_tambah = 0;
         $total_pendanaan_kurang = 0;
         for($i=0; $i<sizeof($pendanaan); $i++) {
            if ($pendanaan[$i]['status'] == 'Ya')
               $total_pendanaan_tambah += $pendanaan[$i]['nilai'];
            elseif($pendanaan[$i]['status'] == 'Tidak')
               $total_pendanaan_kurang += $pendanaan[$i]['nilai'];
            $this->mWorksheets['Data']->write($num_pendanaan, 0, $pendanaan[$i]['nama_kel_lap'], $fColCtn);
            $this->mWorksheets['Data']->write($num_pendanaan, 1, number_format($pendanaan[$i]['nilai'],2, ',', '.'), $fColCtn);
            #$this->mWorksheets['Data']->write($num_pendanaan, 2, $pendanaan[$i]['status_lap'], $fColCtn);
            $num_pendanaan++;
         }
         
         $this->mWorksheets['Data']->write($num_pendanaan, 0, 'Kas Bersih diperoleh (digunakan untuk) aktivitas pendanaan', $fColCtn);
         $this->mWorksheets['Data']->write($num_pendanaan, 1, number_format(($total_pendanaan_tambah - $total_pendanaan_kurang), 2, ',', '.'), $fColCtn);
         #$this->mWorksheets['Data']->write($num_pendanaan, 2, '', $fColCtn);
         
         $total_kas = ($total_operasional_tambah - $total_operasional_kurang) + ($total_investasi_tambah - $total_investasi_kurang) + ($total_investasi_tambah - $total_investasi_kurang);
         $this->mWorksheets['Data']->write($num_pendanaan+2, 0, 'KENAIKAN (PENURUNAN) BERSIH KAS DAN SETARA KAS', $fColNomorbold);
         $this->mWorksheets['Data']->write($num_pendanaan+2, 1, 'Rp. '. number_format($total_kas,2,',','.'), $fColNomorbold);
         
         $this->mWorksheets['Data']->write($num_pendanaan+3, 0, 'KAS DAN SETARA KAS AWAL TAHUN', $fColNomorbold);
         $this->mWorksheets['Data']->write($num_pendanaan+3, 1, 'Rp. '. number_format($saldo_coa_aliran_kas, 2, ',', '.'), $fColNomorbold);
         
         $this->mWorksheets['Data']->write($num_pendanaan+4, 0, 'KAS DAN SETARA KAS AKHIR TAHUN', $fColNomorbold);
         $this->mWorksheets['Data']->write($num_pendanaan+4, 1, 'Rp. '. number_format($total_kas + $saldo_coa_aliran_kas, 2, ',', '.'), $fColNomorbold);
      }
      
   }
}
?>
