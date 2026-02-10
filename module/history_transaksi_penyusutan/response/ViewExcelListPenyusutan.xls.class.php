<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/transaksi_penyusutan/business/AppTransaksiPenyusutanAsper.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';

class ViewExcelListPenyusutan extends XlsResponse
{
   var $mWorksheets = array('none');

   function GetFileName()
   {
      return 'ListPenyusutan.xls';
   }
   function ProcessRequest()
   {
      $Obj = new AppTransaksiPenyusutanAsper();

      if ($_GET['key'] != '') $key = Dispatcher::Instance()->Decrypt($_GET['key']);

      if ($_GET['jenis_kib'] != '') $jenis_kib = Dispatcher::Instance()->Decrypt($_GET['jenis_kib']);

      $data = $Obj->GetListPenyusutanAll($key, $jenis_kib);
      $totalSheet = ceil(sizeof($data) / 40000);

      for ($i = 0; $i < $totalSheet; $i++)
      {
         $array_temp['Sheet'.($i+1)] =& $this->mrWorkbook->add_worksheet('Sheet'.($i+1));
      }

      $this->mWorksheets = &$array_temp;


      if (empty($data))
      {
         $this->mWorksheets['Sheet1']->write(0, 0, 'Data kosong');
      }else
      {
         $end = 0;
         $start = 0;
         for ($i = 0;$i < $totalSheet;$i++)
         {

            $end += 40000;
            if(sizeof($data)<$end)
               $end=sizeof($data);

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
            $this->mWorksheets['Sheet'.($i+1)]->write(0, 0, 'List Penyusutan', $fTitle);
            $this->mWorksheets['Sheet'.($i+1)]->write(2, 0, 'Periode Penyusutan ' . IndonesianDate($tgl_awal, 'yyyy-mm-dd') . 's/d' . IndonesianDate($tgl_akhir, 'yyyy-mm-dd'));
            $no = 4;
            $this->mWorksheets['Sheet'.($i+1)]->write($no, 0, 'No.', $fColNomorbold);
            $this->mWorksheets['Sheet'.($i+1)]->write($no, 1, 'Kode Aset', $fColNomorbold);
            $this->mWorksheets['Sheet'.($i+1)]->write($no, 2, 'Nama Aset', $fColNomorbold);
            $this->mWorksheets['Sheet'.($i+1)]->write($no, 3, 'Unit PJ Barang', $fColNomorbold);
            $this->mWorksheets['Sheet'.($i+1)]->write($no, 4, 'Nilai Penyusutan (Rp.)', $fColNomorbold);
            $this->mWorksheets['Sheet'.($i+1)]->write($no, 5, 'Akumulasi Penyusutan (Rp.)', $fColNomorbold);
            $this->mWorksheets['Sheet'.($i+1)]->write($no, 6, 'Nilai Buku (Rp.)', $fColNomorbold);
            $num = 5;
            $nom = 1;


            for ($j = $start;$j < $end; $j++)
            {
               $this->mWorksheets['Sheet'.($i+1)]->write($num, 0, $nom++, $fColNomor);
               $this->mWorksheets['Sheet'.($i+1)]->write($num, 1, $data[$j]['kode_aset'], $fColCtn);
               $this->mWorksheets['Sheet'.($i+1)]->write($num, 2, $data[$j]['nama_aset'], $fColCtn);
               $this->mWorksheets['Sheet'.($i+1)]->write($num, 3, $data[$j]['unitkerjaNama'], $fColCtn);
               $this->mWorksheets['Sheet'.($i+1)]->write($num, 4, number_format($data[$j]['nilai_penyusutan'], 2, ',', '.') , $fColNilai);
               $this->mWorksheets['Sheet'.($i+1)]->write($num, 5, number_format($data[$j]['akumulasi_penyusutan'], 2, ',', '.') , $fColNilai);
               $this->mWorksheets['Sheet'.($i+1)]->write($num, 6, number_format($data[$j]['total_penyusutan'], 2, ',', '.') , $fColNilai);
               $num++;
            }

            $start = $end;
         }
      }
   }
}
?>