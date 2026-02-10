<?php
/*
   @ClassName : ViewListJurnal
   @Copyright : PT Gamatechno Indonesia
   @Analyzed By : Dyan Galih <galih@gamatechno.com>
   @Author By : Dyan Galih <galih@gamatechno.com>
   @Version : 0.1
   @StartDate : 2010-05-25
   @LastUpdate : 2010-05-25
   @Description :
*/

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/jurnal/business/Jurnal.class.php';

class ViewListJurnal extends XlsResponse {
   var $mWorksheets = array('List Jurnal');

   function GetFileName() {
      // name it whatever you want
      return 'list_jurnal.xls';
   }

   function ProcessRequest() {
      #get data from GET
      $noReferensi = Dispatcher::Instance()->Decrypt($_GET['no_referensi']);
      $tahun = Dispatcher::Instance()->Decrypt($_GET['tahun']);
      $bulan = Dispatcher::Instance()->Decrypt($_GET['bulan']);

      #get data from database
      $objJurnal = new Jurnal();
      $data = $objJurnal->GetDataCetak($noReferensi, $tahun, $bulan);

      $title = $this->mrWorkbook->add_format();
      $title->set_align('center');
      $title->set_bold();
      $title->set_size(14);

      $thead = $this->mrWorkbook->add_format();
      $thead->set_align('center');
      $thead->set_bold();
      $thead->set_border(1);
      $thead->set_size(12);

      $tcol = $this->mrWorkbook->add_format();
      $tcol->set_border(1);
      $tcol->set_size(10);

      $tmcol = $this->mrWorkbook->add_format();
      $tmcol->set_align('vcenter');
      $tmcol->set_align('center');
      $tmcol->set_border(1);
      $tmcol->set_size(10);

      $row=0;
      $col=0;

      $row +=2;

      $jurnal = $this->mWorksheets['List Jurnal'];

      #set Title
      $jurnal->write($row,$col,'List Jurnal',$title);
      $jurnal->merge_cells($row,$col,$row,$col+6);

      #set Header
      $row += 2;
      $jurnal->write($row,$col,GTFWConfiguration::GetValue('language','no'),$thead);
      $jurnal->write($row,$col+1,GTFWConfiguration::GetValue('language','referensi'),$thead);
      $jurnal->write($row,$col+2,GTFWConfiguration::GetValue('language','tanggal_entri'),$thead);
      $jurnal->write($row,$col+3,GTFWConfiguration::GetValue('language','kode_rekening'),$thead);
      $jurnal->write($row,$col+4,GTFWConfiguration::GetValue('language','nama_rekening'),$thead);
      $jurnal->write($row,$col+5,GTFWConfiguration::GetValue('language','debet_rp'),$thead);
      $jurnal->write($row,$col+6,GTFWConfiguration::GetValue('language','kredit_rp'),$thead);
      $jurnal->write($row,$col+7,GTFWConfiguration::GetValue('language','petugas'),$thead);

      $jurnal->set_column($col, $col, 10, 0, 0);
      $col++;
      $jurnal->set_column($col, $col, 20, 0, 0);
      $col++;
      $jurnal->set_column($col, $col, 15, 0, 0);
      $col++;
      $jurnal->set_column($col, $col, 15, 0, 0);
      $col++;
      $jurnal->set_column($col, $col, 40, 0, 0);
      $col++;
      $jurnal->set_column($col, $col, 15, 0, 0);
      $col++;
      $jurnal->set_column($col, $col, 15, 0, 0);
      $col++;
      $jurnal->set_column($col, $col, 10, 0, 0);

      $referensi = '';

      $col=0;
      $firstRow = 0;
      $no=1;
      for ($i = 0; $i < count($data); $i++) {
         $row++;

         if($referensi!=$data[$i]['id']){
            $firstRow = $row;
            $jurnal->write($row,$col,$no,$tmcol);
            $jurnal->write($row,$col+1,$data[$i]['referensi'],$tmcol);
            $jurnal->write($row,$col+2,$data[$i]['tanggal'],$tmcol);
            $jurnal->write($row,$col+7,$data[$i]['petugas_entri'],$tmcol);
            $referensi=$data[$i]['id'];
            $no++;
         }else{
         	$jurnal->merge_cells($firstRow,$col,$row,$col);
            $jurnal->merge_cells($firstRow,$col+1,$row,$col+1);
            $jurnal->merge_cells($firstRow,$col+7,$row,$col+7);
            $jurnal->merge_cells($firstRow,$col+2,$row,$col+2);
         }

         $jurnal->write($row,$col+3,$data[$i]['rekening_kode'],$tcol);
         $jurnal->write($row,$col+4,$data[$i]['rekening_nama'],$tcol);
         $debet = $data[$i]['tipeakun']=='D'?$data[$i]['nilai']:0;
         $kredit = $data[$i]['tipeakun']=='K'?$data[$i]['nilai']:0;
         $jurnal->write($row,$col+5,$debet,$tcol);
         $jurnal->write($row,$col+6,$kredit,$tcol);

      }

   }
}
?>