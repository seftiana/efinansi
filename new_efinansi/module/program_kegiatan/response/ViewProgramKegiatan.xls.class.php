<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/'.Dispatcher::Instance()->mModule.'/business/ProgramKegiatan.class.php';

class ViewProgramKegiatan extends XlsResponse
{
   var $mWorksheets = array('Program Kegiatan');
   
   function GetFileName() {
      // name it whatever you want
      return 'Program-Kegiatan.xls';
   }

   function ProcessRequest() {
		$Obj = new ProgramKegiatan;
		
		if(isset($_GET['idTahun']))
			$idTahun = Dispatcher::Instance()->Decrypt($_GET['idTahun']);
		if(isset($_GET['idProgram']))
			$idProgram = Dispatcher::Instance()->Decrypt($_GET['idProgram']);
		if(isset($_GET['idKegiatan']))
			$idKegiatan = Dispatcher::Instance()->Decrypt($_GET['idKegiatan']);
		if(isset($_GET['jenisKegiatanId']))
			$jenisKegiatanId = Dispatcher::Instance()->Decrypt($_GET['jenisKegiatanId']);
		if(isset($_GET['kodeSubKegiatan']))
			$kodeSubKegiatan = Dispatcher::Instance()->Decrypt($_GET['kodeSubKegiatan']);
		if(isset($_GET['namaSubKegiatan']))
			$namaSubKegiatan = Dispatcher::Instance()->Decrypt($_GET['namaSubKegiatan']);
      
      // inisialisasi dataGrid
      $i = 0;
      $dataGrid = $Obj->GetDataExcel($idTahun,$idProgram,$idKegiatan,$jenisKegiatanId,$kodeSubKegiatan,$namaSubKegiatan);
      $cTitle = GTFWConfiguration::GetValue('organization', 'company_name');
      $cSubTitle = GTFWConfiguration::GetValue('organization', 'application_name');
      $cTableTitle = 'Daftar Program Kegiatan';
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
      
      $fTableCellBold = $this->mrWorkbook->add_format();
      $fTableCellBold->set_border(1);
      $fTableCellBold->set_bold(1);
      $fTableCellBold->set_size(10);
      $fTableCellBold->set_align('left');
      $fTableCellBold->set_align('top');
      
      $fTableCellItalic = $this->mrWorkbook->add_format();
      $fTableCellItalic->set_border(1);
      $fTableCellItalic->set_italic(1);
      $fTableCellItalic->set_size(10);
      $fTableCellItalic->set_align('left');
      $fTableCellItalic->set_align('top');
      
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
      $row += 2; $col = 0;
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
         $oldProgram = 0;
         $oldProgramRow = $row;
         $oldSubProgram = 0;
         $oldSubProgramRow = $row;
         $oldKegiatan = 0;
         $oldKegiatanRow = $row;
         
         foreach ($dataGrid as $value)
         {
            if ($value['programId'] != $oldProgram)
            {
               $oldProgram = $value['programId'];
               $cell = $value['kodeProg'];
               $cell2 = $value['namaProgram'];
               $this->mWorksheets['Program Kegiatan']->write($row, 0, $cell, $fTableCellBold);
               $this->mWorksheets['Program Kegiatan']->write($row, 1, $cell2, $fTableCellBold);
               $this->mWorksheets['Program Kegiatan']->write_blank($row, 2, $fTableCellBold);
               $this->mWorksheets['Program Kegiatan']->merge_cells($row, 1, $row, 2);
               
               $row++; $col = 0;
            }
            
            if ($value['subprogId'] != $oldSubProgram)
            {
               $oldSubProgram = $value['subprogId'];
               $cell = $value['kodeKegiatan'];
               $cell2 = $value['namaKegiatan'];
               $this->mWorksheets['Program Kegiatan']->write($row, 0, $cell, $fTableCellItalic);
               $this->mWorksheets['Program Kegiatan']->write($row, 1, $cell2, $fTableCellItalic);
               $this->mWorksheets['Program Kegiatan']->write_blank($row, 2, $fTableCellItalic);
               $this->mWorksheets['Program Kegiatan']->merge_cells($row, 1, $row, 2);
               
               $row++; $col = 0;
            }
            
            if ($value['kegrefId'] != $oldKegiatan)
            {
               $oldKegiatan = $value['kegrefId'];
               $cell = $value['kodeSubKegiatan'];
               $cell2 = $value['namaSubKegiatan'];
               $cell3 = $value['jeniskegNama'];
               $this->mWorksheets['Program Kegiatan']->write($row, 0, $cell, $fTableCell);
               $this->mWorksheets['Program Kegiatan']->write($row, 1, $cell2, $fTableCell);
               $this->mWorksheets['Program Kegiatan']->write($row, 2, $cell3, $fTableCell);
               
               $row++; $col = 0;
            }
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
         $this->mWorksheets[$sheet]->write($row, $col, $key, $format);
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
