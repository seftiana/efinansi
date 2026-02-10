<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/'.Dispatcher::Instance()->mModule.'/business/AppUnitkerja.class.php';

class ViewUnitkerja extends XlsResponse
{
   var $mWorksheets = array('Program Kegiatan');
   
   function GetFileName() {
      // name it whatever you want
      return 'UnitKerja.xls';
	  //.date('Y-m-d H.i').
   }

   function ProcessRequest() {
		$Obj = new AppUnitkerja;
		
					
			if(isset($_GET['kode'])) {
				$kode = Dispatcher::Instance()->Decrypt($_GET['kode']);
			} else {
				$kode = '';
			}
		  
			if(isset($_GET['nama'])) {
				$unitkerja = Dispatcher::Instance()->Decrypt($_GET['nama']);
			} else {
				$unitkerja = '';
			}

			if(isset($_GET['tipeunit'])) {
				$tipeunit = Dispatcher::Instance()->Decrypt($_GET['tipeunit']);
			} else {
				$tipeunit = '';
			}
		
      
      // inisialisasi dataGrid
      $dataGrid = $Obj->GetDataExcel($unitkerja, $kode, $tipeunit);
 
      $cTitle = GTFWConfiguration::GetValue('organization', 'company_name');
      $cSubTitle = GTFWConfiguration::GetValue('organization', 'application_name');
      $cTableTitle = 'Daftar Unit Kerja';
      // ---------
		
      // Create format for each style
      $fTitle = $this->mrWorkbook->add_format();
      $fTitle->set_border(0);
      $fTitle->set_bold();
      $fTitle->set_size(14);
      $fTitle->set_align('center');
      
      $fSubTitle = $this->mrWorkbook->add_format();
      $fSubTitle->set_border(0);
      $fSubTitle->set_bold();
      $fSubTitle->set_size(12);
      $fSubTitle->set_align('center');
      
      $fTableTitle = $this->mrWorkbook->add_format();
      $fTableTitle->set_border(0);
      $fTableTitle->set_bold();
      $fTableTitle->set_size(11);
      
      $fTableHeader = $this->mrWorkbook->add_format();
      $fTableHeader->set_border(1);
      $fTableHeader->set_bold();
      $fTableHeader->set_size(10);
      $fTableHeader->set_align('center');
      $fTableHeader->set_align('vcenter');
      
      $fTableCell = $this->mrWorkbook->add_format();
      $fTableCell->set_border(1);
      $fTableCell->set_bold(0);
      $fTableCell->set_size(10);
      $fTableCell->set_align('left');
      $fTableCell->set_align('top');
      
      $fTableCellCenter = $this->mrWorkbook->add_format();
      $fTableCellCenter->set_border(1);
      $fTableCellCenter->set_bold(0);
      $fTableCellCenter->set_size(10);
      $fTableCellCenter->set_align('center');
      $fTableCellCenter->set_align('top');
      
      $fTableCellRight = $this->mrWorkbook->add_format();
      $fTableCellRight->set_border(1);
      $fTableCellRight->set_bold(0);
      $fTableCellRight->set_size(10);
      $fTableCellRight->set_align('right');
      $fTableCellRight->set_align('top');
      // ---------
      
      // Create layout
      $col_width = 3;
      $row = 0; $col = 0;
      
      $this->mWorksheets['Program Kegiatan']->write($row, $col, $cTitle, $fTitle);
      $this->mWorksheets['Program Kegiatan']->merge_cells($row, $col, $row, $col + $col_width - 1);
      for ($col = 1; $col < $col_width; $col++) $this->mWorksheets['Program Kegiatan']->write_blank($row, $col, $fTitle);
      $row++; $col = 0;
      
      $this->mWorksheets['Program Kegiatan']->write($row, $col, $cSubTitle, $fSubTitle);
      $this->mWorksheets['Program Kegiatan']->merge_cells($row, $col, $row, $col + $col_width - 1);
      for ($col = 1; $col < $col_width; $col++) $this->mWorksheets['Program Kegiatan']->write_blank($row, $col, $fSubTitle);
      $row++; $col = 0;
      $row++; $col = 0;
      
      $this->mWorksheets['Program Kegiatan']->write($row, $col, $cTableTitle, $fTableTitle);
      $this->mWorksheets['Program Kegiatan']->merge_cells($row, $col, $row, $col + $col_width - 1);
      for ($col = 1; $col < $col_width; $col++) $this->mWorksheets['Program Kegiatan']->write_blank($row, $col, $fTableTitle);
      $row++; $col = 0;
      
      $header = array('Kode' => '','Nama' => '','Tipe' => '');
      $this->create_header('Program Kegiatan', $header, $fTableHeader, $row, $col);
      $row ++; $col = 0;
      // ---------
      
      // dump dataGrid
      if (empty($dataGrid))
      {
         $this->mWorksheets['Program Kegiatan']->write($row, $col, '-- Data tidak ditemukan --', $fTableCellCenter);
         $this->mWorksheets['Program Kegiatan']->merge_cells($row, $col, $row, $col + $col_width - 1);
         for ($col = 1; $col < $col_width; $col++) $this->mWorksheets['Program Kegiatan']->write_blank($row, $col, $fTableCellCenter);
      }
      else
      {
         $unitkerjaParentId = 0;
         foreach ($dataGrid as $value)
         {
            if ($value['unitkerjaParentId'] != $unitkerjaParentId)
            {
               if ($unitkerjaParentId > $value['unitkerjaParentId'])
               {
                  for ($i = 0; $i < $col_width; $i++) $this->mWorksheets['Program Kegiatan']->write_blank($row, $i, $fTableCellCenter);
                  $this->mWorksheets['Program Kegiatan']->merge_cells($row, 0, $row, $col_width - 1);
                  $row++;
               }
               
               $unitkerjaParentId = $value['unitkerjaParentId'];
            }
            
            $this->mWorksheets['Program Kegiatan']->write_string($row, 0, $value['kodeunit'], $fTableCellCenter);
            $this->mWorksheets['Program Kegiatan']->write($row, 1, $value['unit'], $fTableCell);
            $this->mWorksheets['Program Kegiatan']->write($row, 2, $value['tipeunit'], $fTableCellCenter);
            
            $row++; $col = 0;
         }
      }
      // ---------
   }
   
   function create_header($sheet, $header, $format, $start_row, $start_col)
   {
      $row = $start_row;
      $col = $start_col;
      foreach ($header as $key=>$value)
      {
         $this->mWorksheets[$sheet]->write($row, $col, $key, $format); $col++; continue;
         if (is_array($value))
         {
            $this->mWorksheets[$sheet]->merge_cells($row, $col, $row, $col + count($value) - 1);
            foreach ($value as $key=>$val)
            {
               if ($key > 0) $this->mWorksheets[$sheet]->write_blank($row, $col, $format);
               $this->mWorksheets[$sheet]->write($row + 1, $col, $val, $format);
               $col++;
            }
         }
         else
         {
            $this->mWorksheets[$sheet]->write_blank($row + 1, $col, $format);
            $this->mWorksheets[$sheet]->merge_cells($row, $col, $row + 1, $col);
            $col++;
         }
      }
   }
}
?>
