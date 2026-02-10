<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
'module/lap_bukubesar_sementara/business/AppLapBukubesar.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 
'main/function/date.php';

class ViewExcelLapBukubesar extends XlsResponse {
   var $mWorksheets = array('Data');
   
   function GetFileName() {
      // name it whatever you want
      return 'LapBukubesarSementara.xls';
   }

   function ProcessRequest() {
      $Obj = new AppLapBukubesar();
      $rekening = Dispatcher::Instance()->Decrypt($_GET['rekening']);
      $tgl_awal = Dispatcher::Instance()->Decrypt($_GET['tgl_awal']);
      $tgl_akhir = Dispatcher::Instance()->Decrypt($_GET['tgl_akhir']);
      $data = $Obj->GetBukuBesarHis($rekening, $tgl_awal, $tgl_akhir);
      $info_coa = $Obj->GetInfoCoa($rekening);
      #print_r($data); exit;
      if (empty($data)) {
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
         $this->mWorksheets['Data']->write(0, 0, 'Laporan Buku Besar Sementara', $fTitle);
         $this->mWorksheets['Data']->write(2, 0, 'Tanggal Transaksi : '. IndonesianDate($tgl_awal, 'yyyy-mm-dd') .'s/d'. IndonesianDate($tgl_akhir, 'yyyy-mm-dd'));
         $this->mWorksheets['Data']->write(3, 0, 'Nama Rekening : '. $info_coa['rekening']);
         $this->mWorksheets['Data']->write(4, 0, 'Nomor Rekening : '. $info_coa['no_rekening']);
         
         $no=6;
         $this->mWorksheets['Data']->write($no, 0, 'Tanggal Jurnal', $fColNomorbold);
         $this->mWorksheets['Data']->write($no, 1, 'Rekening', $fColNomorbold);
         $this->mWorksheets['Data']->write($no, 2, 'Coa', $fColNomorbold);
         $this->mWorksheets['Data']->write($no, 3, 'Uraian', $fColNomorbold);
         $this->mWorksheets['Data']->write($no, 4, 'Referensi', $fColNomorbold);
         $this->mWorksheets['Data']->write($no, 5, 'Saldo Awal', $fColNomorbold);
         $this->mWorksheets['Data']->write($no, 6, 'Debet (Rp.)', $fColNomorbold);
         $this->mWorksheets['Data']->write($no, 7, 'Kredit (Rp.)', $fColNomorbold);
         $this->mWorksheets['Data']->write($no, 8, 'Saldo Akhir (Rp.)', $fColNomorbold);
         
         $num = 7;
         
         for ($i=0;$i<sizeof($data);$i++) {
            $this->mWorksheets['Data']->write($num, 0, IndonesianDate($data[$i]['bb_tanggal'],'yyyy-mm-dd'), $fColNomor);
            $this->mWorksheets['Data']->write($num, 1, $data[$i]['rekening'], $fColNomor);
            $this->mWorksheets['Data']->write($num, 2, $data[$i]['coa'], $fColNomor);
            $this->mWorksheets['Data']->write($num, 3, $data[$i]['keterangan'], $fColNomor);
            $this->mWorksheets['Data']->write($num, 4, $data[$i]['referensi'], $fColNomor);
            $this->mWorksheets['Data']->write($num, 5, number_format($data[$i]['saldo_awal'], 2, ',', '.'), $fColNilai);
            $this->mWorksheets['Data']->write($num, 6, number_format($data[$i]['debet'], 2, ',', '.'), $fColNilai);
            $this->mWorksheets['Data']->write($num, 7, number_format($data[$i]['kredit'], 2, ',', '.'), $fColNilai);
            $this->mWorksheets['Data']->write($num, 8, number_format($data[$i]['saldo_akhir'], 2, ',', '.'), $fColNilai);
            $num++;
         }
      }
      
   }
}
?>