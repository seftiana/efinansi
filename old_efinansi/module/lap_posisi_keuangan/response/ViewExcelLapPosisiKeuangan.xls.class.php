<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/lap_posisi_keuangan/business/AppLapPosisiKeuangan.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';

class ViewExcelLapPosisiKeuangan extends XlsResponse {
   var $mWorksheets = array('Data');
   
   function GetFileName() {
      // name it whatever you want
      return 'LapPosisiKeuangan.xls';
   }

   function ProcessRequest() {
      $Obj = new AppLapPosisiKeuangan();
      $tgl_akhir = Dispatcher::Instance()->Decrypt($_GET['tgl_akhir']);
      $penambah = $Obj->GetDataPenambahPerubModal($tgl_akhir);
      $pengurang = $Obj->GetDataPengurangPerubModal($tgl_akhir);
      $laba_thn_lalu = $Obj->GetLabaTahunLalu($tgl_akhir);
      $surplus_thn_berjalan = $Obj->GetSurplusTahunBerjalan($tgl_akhir);
      #$data = 12;
      
      if (empty($surplus_thn_berjalan)) {
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
         
         $fColNilaibold = $this->mrWorkbook->add_format();
         $fColNilaibold->set_border(1);
         $fColNilaibold->set_bold();
         $fColNilaibold->set_size(10);
         //$fColKegiatan->set_column(1,1, 300);
         $fColNilaibold->set_align('right');
         
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
         
         $fColCtnBold = $this->mrWorkbook->add_format();
         $fColCtnBold->set_border(1);
         $fColCtnBold->set_size(10);
         $fColCtnBold->set_bold();
         $fColCtnBold->set_align('left');
         
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
         $this->mWorksheets['Data']->write(0, 0, 'Laporan Posis Keuangan / Modal', $fTitle);
         $this->mWorksheets['Data']->write(2, 0, 'Untuk Tahun Yang Berakhir Tanggal '.IndonesianDate($tgl_akhir, 'yyyy-mm-dd'), $fTitle);
         
         $no=4;
         $this->mWorksheets['Data']->write($no, 0, 'EKUITAS AWAL', $fColNomorbold);
         $this->mWorksheets['Data']->write($no, 1, number_format($laba_thn_lalu['saldo_akhir'], 2, ',', '.'), $fColNilaibold);
         $this->mWorksheets['Data']->write($no+1, 0, 'PENAMBAH', $fColNomorbold);
         $this->mWorksheets['Data']->write($no+1, 1, 'Jumlah (Rp.)', $fColNomorbold);
         #$this->mWorksheets['Data']->write($no, 2, 'Status', $fColNomorbold);
         
         $num_penambah = 6;
         
         //penambah
         $total_penambah = 0;
         for($i=0; $i<sizeof($penambah); $i++) {
            $total_penambah += $penambah[$i]['nilai'];
            $this->mWorksheets['Data']->write($num_penambah, 0, $penambah[$i]['nama_kel_lap'], $fColCtn);
            $this->mWorksheets['Data']->write($num_penambah, 1, number_format($penambah[$i]['nilai'],2, ',', '.'), $fColNilai);
            $num_penambah++;
         }
         
         $this->mWorksheets['Data']->write($num_penambah, 0, 'Surplus Tahun Berjalan', $fColCtn);
         if($surplus_thn_berjalan['saldo_akhir'] > 0) {
            $this->mWorksheets['Data']->write($num_penambah, 1, number_format($surplus_thn_berjalan['saldo_akhir'], 2, ',', '.'), $fColNilai);
            $surplus = $surplus_thn_berjalan['saldo_akhir'];
         }else{
            $this->mWorksheets['Data']->write($num_penambah, 1, ' - ', $fColCtn);
         }
         
         $num_jml_penambah = $num_penambah+1;
         $jml_total_penambah = ($total_penambah + $surplus);
         $this->mWorksheets['Data']->write($num_jml_penambah, 0, 'Jumlah Penambahan', $fColCtnBold);
         $this->mWorksheets['Data']->write($num_jml_penambah, 1, number_format($jml_total_penambah, 2, ',', '.'), $fColNilaibold);
         
         $this->mWorksheets['Data']->write($num_jml_penambah+1, 0, 'PENGURANG', $fColNomorbold);
         $this->mWorksheets['Data']->write($num_jml_penambah+1, 1, 'Jumlah (Rp.)', $fColNomorbold);
         
         //pengurang
         $num_pengurang = $num_jml_penambah+2;
         $total_pengurang = 0;
         for($i=0; $i<sizeof($pengurang); $i++) {
            $total_pengurang += $pengurang[$i]['nilai'];
            $this->mWorksheets['Data']->write($num_pengurang, 0, $pengurang[$i]['nama_kel_lap'], $fColCtn);
            $this->mWorksheets['Data']->write($num_pengurang, 1, number_format($pengurang[$i]['nilai'],2, ',', '.'), $fColNilai);
            $num_pengurang++;
         }
         
         $this->mWorksheets['Data']->write($num_pengurang, 0, 'Defisit Tahun Berjalan', $fColCtn);
         if($surplus_thn_berjalan['saldo_akhir'] < 0) {
            #jika negatif, tetep ditampilkan posisitf
            $datapositif = ($surplus_thn_berjalan['saldo_akhir'] - (2*$surplus_thn_berjalan['saldo_akhir']));
            $this->mWorksheets['Data']->write($num_pengurang, 1, number_format($datapositif, 2, ',', '.'), $fColNilai);
            $defisit = $datapositif;
         }else{
            $this->mWorksheets['Data']->write($num_pengurang, 1, ' - ', $fColCtn);
         }
         
         $num_jml_pengurang = $num_pengurang+1;
         $jml_total_pengurang = ($total_pengurang + $defisit);
         $this->mWorksheets['Data']->write($num_jml_pengurang, 0, 'Jumlah Pengurangan', $fColCtnBold);
         $this->mWorksheets['Data']->write($num_jml_pengurang, 1, number_format($jml_total_pengurang, 2, ',', '.'), $fColNilaibold);
         
         $ekuitas_akhir = ($laba_thn_lalu['saldo_akhir'] + ($jml_total_penambah - $jml_total_pengurang));
         $this->mWorksheets['Data']->write($num_jml_pengurang+1, 0, 'EKUITAS AKHIR', $fColNomorbold);
         $this->mWorksheets['Data']->write($num_jml_pengurang+1, 1, number_format($ekuitas_akhir, 2, ',', '.'), $fColNilaibold);
         
      }
      
   }
}
?>