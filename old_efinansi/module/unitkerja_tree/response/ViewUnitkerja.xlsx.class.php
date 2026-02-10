<?php
/**
* ================= doc ====================
* FILENAME     : ViewUnitkerja.xlsx.class.php
* @package     : ViewUnitkerja
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-17
* @Modified    : 2015-04-17
* @Analysts    : Dyah Fajar N
* @contact     : eko.susilo@gamatechno.com
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'module/'.Dispatcher::Instance()->mModule.'/business/AppUnitkerja.class.php';

class ViewUnitkerja extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      $Obj     = new AppUnitkerja;
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
      $dataGrid   = $Obj->GetDataExcel($unitkerja, $kode, $tipeunit);
      $cTitle     = GTFWConfiguration::GetValue('organization', 'company_name');
      $cSubTitle  = GTFWConfiguration::GetValue('organization', 'application_name');
      $cTableTitle   = 'Daftar Unit Kerja';
      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('unit_kerja.xls');
      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(true); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle('unit_kerja');
      # set font default
      $sheet->getDefaultStyle()->getFont()->setName('Courier New')->setSize('10');
      # setting paging
      # options ORIENTATION_DEFAULT : default; ORIENTATION_LANDSCAPE: lanscape; ORIENTATION_PORTRAIT: Potrait
      $sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
      $sheet->getPageSetup()->setFitToWidth(1); # Set 0 or 1
      $sheet->getPageSetup()->setFitToHeight(0); # Set 0 or 1
      $sheet->getPageSetup()->setHorizontalCentered(true); # true or false
      $sheet->getPageSetup()->setVerticalCentered(false); # true or false
      # Set The papersize
      $sheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
      # /Document Setting

      $sheet->setCellValue('A1', $cTitle);
      $sheet->setCellValue('A2', $cTableTitle);
      $sheet->mergeCells('A1:C1');
      $sheet->mergeCells('A2:C2');
      $sheet->getRowDimension(1)->setRowHeight(18);
      $sheet->getRowDimension(2)->setRowHeight(16);
      $sheet->getRowDimension(4)->setRowHeight(18);

      $sheet->getStyle('A1:C1')->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         ), 'font' => array(
            'bold' => true,
            'size' => 12
         ))
      );

      $sheet->getStyle('A2:C2')->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         ), 'font' => array(
            'bold' => true
         ))
      );
      $sheet->setCellValue('A4', GTFWConfiguration::GetValue('language', 'kode'));
      $sheet->setCellValue('B4', GTFWConfiguration::GetValue('language', 'nama'));
      $sheet->setCellValue('C4', GTFWConfiguration::GetValue('language', 'tipe'));
      $sheet->getStyle('A4:C4')->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         ), 'font' => array(
            'bold' => true
         )
      ));
      $row     = 5;
      if (empty($dataGrid)){
         $sheet->setCellValue('A5', GTFWConfiguration::GetValue('language', 'data_kosong'));
      }else{
         foreach ($dataGrid as $grid) {
            if($grid['parentId'] == 0){
               $sheet->getStyle('A'.$row.':C'.$row)->getFont()->setBold(true);
            }
            $sheet->setCellValueExplicit('A'.$row, $grid['kodeunit']);
            $sheet->setCellValue('B'.$row, $grid['unit']);
            $sheet->setCellValue('C'.$row, $grid['tipeunit']);
            $row++;
         }

         $row  = $row-1;
      }

      $sheet->getStyle('A4:C'.$row)->applyFromArray(array(
         'borders' => array(
            'allborders' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN
            )
         )
      ));
      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>