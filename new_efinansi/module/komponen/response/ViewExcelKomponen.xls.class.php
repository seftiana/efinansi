<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/komponen/business/Komponen.class.php';

class ViewExcelKomponen extends XlsResponse {
   var $mWorksheets = array('Data');
   
   function GetFileName() {
      return 'Komponen.xls';
   }

   function ProcessRequest() {
		$Obj = new Komponen();
		$data = $Obj->GetExcelKomponen();

      if (empty($data)) {
         $this->mWorksheets['Data']->write(0, 0, 'Data kosong');
      } else {
		   $fTitle = $this->mrWorkbook->add_format();
		   $fTitle->set_bold();
         $fTitle->set_size(12);
         $fTitle->set_align('vcenter');

		   $fTableTitle = $this->mrWorkbook->add_format();
         $fTableTitle->set_border(1);
		   $fTableTitle->set_bold();
         $fTableTitle->set_size(10);
         //$fColKegiatan->set_column(1,1, 300);
         $fTableTitle->set_align('center');
		
// set_column($first_col, $last_col, $width, $format, $hidden, $level) 

		   $fColData = $this->mrWorkbook->add_format();
         $fColData->set_border(1);
	     //$this->mWorksheets['Data']->set_columns(array(6, 20, 20, 30, 30, 30, 20, 10));

         $this->mWorksheets['Data']->write(0, 0, 'Daftar Komponen', $fTitle);
         $no = 3;
         $this->mWorksheets['Data']->write($no, 0, 'No', $fTableTitle);
         $this->mWorksheets['Data']->write($no, 1, 'Nama', $fTableTitle);
         $this->mWorksheets['Data']->write($no, 2, 'Satuan', $fTableTitle);
         $this->mWorksheets['Data']->write($no, 3, 'Keterangan', $fTableTitle);
         $this->mWorksheets['Data']->write($no, 4, 'Formula', $fTableTitle);
          $this->mWorksheets['Data']->write($no, 5, 'Harga Satuan', $fTableTitle);
          
         $no = 4;
		   for($i=0;$i<sizeof($data);$i++) {
            $this->mWorksheets['Data']->write($no, 0, ($i+1), $fColData);
				$this->mWorksheets['Data']->write($no, 1, $data[$i]['nama'], $fColData);
				$this->mWorksheets['Data']->write($no, 2, $data[$i]['satuan'], $fColData);
				$this->mWorksheets['Data']->write($no, 3, $data[$i]['deskripsi'], $fColData);
				$this->mWorksheets['Data']->write($no, 4, $data[$i]['formula'], $fColData);
				$this->mWorksheets['Data']->write($no, 5, $data[$i]['harga_satuan'], $fColData);
            $no++;
		   }
      }
   }
}
?>
