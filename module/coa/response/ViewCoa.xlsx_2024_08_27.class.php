<?php

/**
* ================= doc ====================
* FILENAME     : ViewExcelCoa.xlsx.class.php
* @package     : ViewExcelCoa
* scope        : PUBLIC
* @Author      : noor hadi
* @Created     : 2015-06-18
* @Modified    : 2015-06-18
* @Analysts    : Dyah Fajar N
* @contact     : noor.hadi@gamatechno.com
* @copyright   : Copyright (c) 2015 Gamatechno
* ================= doc ====================
*/

require_once GTFWConfiguration::GetValue('application', 'docroot') . 
    'module/coa/business/Coa.class.php';

class ViewCoa extends XlsxResponse
{
    
   # Internal Variables
   public $Excel;

   public function ProcessRequest()
   {

      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('COA_' . date('Ymd', time()).'.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(false); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle('Worksheet Name');
      # set font default
      $sheet->getDefaultStyle()->getFont()->setName('Arial')->setSize('10');
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
      $GET = is_object($_GET) ? $_GET->AsArray() : $_GET;
      $filter = $GET;
      $Obj                 = new Coa;
      $data_kosong         = GTFWConfiguration::GetValue('language','data_kosong');
      
      // inisialisasi dataGrid
      $i = 0;
      $data = $Obj->GetListCoaExcel($filter);
 
      if (empty($data)) {
         $sheet->setCellValue('A1', $data_kosong);
      } else {

         $headerStyle         = array(
            'font' => array(
               'size' => 14,
               'bold' => true
            ), 'alignment' => array(
               'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
               'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
         );

         $borderTableStyledArray = array(
            'borders' => array(
               'allborders' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN,
                  'color' => array('argb' => 'ff000000')
               )
            )
         );
         $styledTableHeaderArray = array(
            'borders' => array(
               'allborders' => array(
                  'style' => PHPExcel_Style_Border::BORDER_HAIR,
                  'color' => array('argb' => 'ff000000')
               )
            ),
            'fill' => array(
               'type' => PHPExcel_Style_Fill::FILL_SOLID,
               'startcolor' => array(
                  'argb' => 'ffE6E6E6'
               )
            ),
            'font' => array(
               'bold' => true,
               'color' => array(
                  'rgb' => '000000'
               )
            ),
            'alignment' => array(
               'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
               'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
         );
          
      }      


         /**
          * set label
          */
          
         
         $cTitle = GTFWConfiguration::GetValue('organization', 'company_name');
         $cSubTitle = GTFWConfiguration::GetValue('organization', 'application_name');
         
        // ---------
         $judul_label           = 'Chart of Account';         
         $kode_label             = GTFWConfiguration::GetValue('language','kode_rekening');
         $no_label               = GTFWConfiguration::GetValue('language','no');
         $nama_label             = GTFWConfiguration::GetValue('language','nama_rekening');
         $saldo_normal_label     = GTFWConfiguration::GetValue('language','saldo_normal');
         /**
          * end set label
          */

         $sheet->getRowDimension(1)->setRowHeight(20);
         $sheet->getColumnDimension('A')->setWidth(8);
         $sheet->getColumnDimension('B')->setWidth(18);
         $sheet->getColumnDimension('C')->setWidth(40);
         $sheet->getColumnDimension('D')->setWidth(20);
         
         $sheet->setCellValue('A1',$cTitle);
         $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);

         $sheet->setCellValue('A3',$cSubTitle);
         $sheet->setCellValueExplicit('A4',$judul_label, PHPExcel_Cell_DataType::TYPE_STRING);
         $sheet->getStyle('A3')->getFont()->setBold(true);
         $sheet->getStyle('A4')->getFont()->setBold(true);

         $sheet->setCellValue('A6', $no_label);
         $sheet->setCellValue('B6', $kode_label);
         $sheet->setCellValue('C6', $nama_label);
         $sheet->setCellValue('D6', $saldo_normal_label);

         $sheet->getStyle('A6:D6')->applyFromArray($styledTableHeaderArray);      
      /**
	    $dataGrid = new SimpleXMLElement("<root/>");
	   */
		$dataGrid = $data; 
		
       $row     = 7;
       $nomor   = 1;
      // dump dataGrid
      /**
	   $dataGrid = $this->CoaXmlAsArray($dataGrid, '.');
	   */
      foreach ($dataGrid as $value)
      {
            $sheet->setCellValue('A'.$row, $nomor);
            $sheet->setCellValueExplicit('B'.$row, $value['coaKodeAkun'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('C'.$row, $value['coaNamaAkun']);
            
            $saldoNormal = (($value['coaIsDebetPositif'] =='1') ? 'Debet' : 'Kredit');
            
            $sheet->setCellValue('D'.$row, $saldoNormal);
            $nomor++;
            $row++;
      }
      // ---------
       # Save Excel document to local hard disk
      $this->Save();
   }
}
?>
