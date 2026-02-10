<?php
# Doc
# @category    XlsxResponse
# @package     ViewExcelProgramKegiatan
# @copyright   Copyright (c) 2011 Gamatechno
# @author      By Eko Susilo
# @Created     2014-01-08
# @modified    2014-01-08
# @Modified    By Eko Susilo
# @contact     eko.susilo@gamatechno.com
# /Doc
require_once GTFWConfiguration::GetValue('application','docroot').
'module/'.Dispatcher::Instance()->mModule.'/business/FinansiReferensi.class.php';

class ViewExcelProgramKegiatan extends XlsxResponse
{
   # Internal Variables
   public $Excel;
   
   function ProcessRequest()
   {
      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('daftar_program_kegiatan.xls');
      
      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(true); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle('program_kegiatan');
      # set font default
      $sheet->getDefaultStyle()->getFont()->setName('Arial')->setSize('10');
      # setting paging
      # options ORIENTATION_DEFAULT : default; ORIENTATION_LANDSCAPE: lanscape; ORIENTATION_PORTRAIT: Potrait
      $sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
      $sheet->getPageSetup()->setFitToWidth(1); # Set 0 or 1
      $sheet->getPageSetup()->setFitToHeight(0); # Set 0 or 1
      $sheet->getPageSetup()->setHorizontalCentered(true); # true or false
      $sheet->getPageSetup()->setVerticalCentered(false); # true or false
      # Set The papersize
      $sheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
      # /Document Setting
      $mObj                            = new FinansiReferensi();
      $requestData['tahun_anggaran']   = Dispatcher::Instance()->Decrypt($mObj->_GET['tahun_anggaran']);
      $requestData['kegiatan_id']      = Dispatcher::Instance()->Decrypt($mObj->_GET['kegiatan_id']);
      $requestData['kegiatan']         = Dispatcher::Instance()->Decrypt($mObj->_GET['kegiatan']);
      $requestData['output_id']        = Dispatcher::Instance()->Decrypt($mObj->_GET['output_id']);
      $requestData['output']           = Dispatcher::Instance()->Decrypt($mObj->_GET['output']);
      $requestData['kode']             = Dispatcher::Instance()->Decrypt($mObj->_GET['kode']);
      $requestData['nama']             = Dispatcher::Instance()->Decrypt($mObj->_GET['nama']);
      $offset           = 0;
      $limit            = 1000;

      $dataList         = $mObj->ChangeKeyName($mObj->GetDataProgramKegiatan(array(
         'limit' => $limit, 
         'offset' => $offset, 
         'options' => (array)$requestData
      )));

      $companyName      = GTFWConfiguration::GetValue('organization', 'company_name');
      $applicationName  = GTFWConfiguration::GetValue('organization', 'application_name');
      $program          = GTFWConfiguration::GetValue('language', 'program');
      $kegiatan         = GTFWConfiguration::GetValue('language', 'kegiatan');
      $subKegiatan      = GTFWConfiguration::GetValue('language', 'sub_kegiatan');

      $headerStyle         = array(
         'font' => array(
            'size' => 14, 
            'bold' => true
         ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 
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
               'style' => PHPExcel_Style_Border::BORDER_THIN, 
               'color' => array('argb' => 'ff000000')
            )
         ), 
         'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,  
            'startcolor' => array(
               'argb' => 'ffcccccc'
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

      // merge cells
      $sheet->mergeCells('A1:C1');
      $sheet->mergeCells('A2:C2');
      $sheet->mergeCells('A3:C3');
      $sheet->mergeCells('A4:C4');
      $sheet->mergeCells('A5:B5');
      $sheet->mergeCells('C5:C6');

      // row height
      $sheet->getRowDimension(1)->setRowHeight(18);
      $sheet->getRowDimension(2)->setRowHeight(18);
      $sheet->getRowDimension(4)->setRowHeight(15);

      // cells width
      $sheet->getColumnDimension('A')->setWidth(10);
      $sheet->getColumnDimension('B')->setWidth(70);
      $sheet->getColumnDimension('C')->setWidth(15);

      $label            = $program.', '.$kegiatan.', '.$subKegiatan;
      $sheet->setCellValue('A1', $companyName);
      $sheet->setCellValue('A2', $applicationName);
      $sheet->getStyle('A1:C2')->applyFromArray($headerStyle);

      $sheet->setCellValue('A4', 'Daftar '.$label);
      $sheet->getStyle('A4:C4')->getFont()->setBold(true);
      $sheet->getStyle('A4:C4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

      $sheet->setCellValue('A5', $label);
      $sheet->setCellValue('C5', GTFWConfiguration::GetValue('language', 'tipe'));
      $sheet->setCellValue('A6', GTFWConfiguration::GetValue('language', 'kode'));
      $sheet->setCellValue('B6', GTFWConfiguration::GetValue('language', 'nama'));
      $sheet->getStyle('A5:C6')->applyFromArray($styledTableHeaderArray);
      $sheet->freezePane('A7');
      $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(5,6);
      if(empty($dataList)){
         $sheet->setCellValue('A7', 'DATA KOSONG');
         $sheet->mergeCells('A7:C7');
         $sheet->getStyle('A7:C7')->applyFromArray($borderTableStyledArray);
      }else{
         $kegiatan      = '';
         $output        = '';
         $komponen      = '';
         $dataRow       = array();
         $index         = 0;
         $rowLevel      = array();
         
         for ($i=0; $i < count($dataList);) { 
            if((int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan && 
               (int)$dataList[$i]['output_id'] === (int)$output){
               if($dataList[$i]['komponen_id'] !== NULL){
                  // url delete
                  $dataRow[$index]['id']           = $dataList[$i]['komponen_id'];
                  $dataRow[$index]['kode']         = $dataList[$i]['komponen_kode'];
                  $dataRow[$index]['nama']         = $dataList[$i]['komponen_nama'];
                  $dataRow[$index]['label']        = $dataList[$i]['komponen_kode'].' &mdash; '.$dataList[$i]['komponen_nama'];
                  $dataRow[$index]['tipe']         = $dataList[$i]['jeniskeg_nama'];
                  $dataRow[$index]['level']        = 'komponen';
                  $dataRow[$index]['detail_belanja']  = $dataList[$i]['detail_belanja'];
                  $start++;
               }
               $i++;
            }elseif((int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan ){
               $output        = $dataList[$i]['output_id'];
               $dataRow[$index]['id']           = $dataList[$i]['output_id'];
               $dataRow[$index]['kode_sistem']  = $dataList[$i]['kegiatan_id'].'.'.$dataList[$i]['output_id'];
               $dataRow[$index]['kode']         = $dataList[$i]['output_kode'];
               $dataRow[$index]['nama']         = $dataList[$i]['output_nama'];
               $dataRow[$index]['label']        = $dataList[$i]['output_kode'].' &mdash; '.$dataList[$i]['output_nama'];
               $dataRow[$index]['tipe']         = '';
               $dataRow[$index]['level']        = 'output';
            }else{
               $kegiatan                        = $dataList[$i]['kegiatan_id'];
               $dataRow[$index]['id']           = $dataList[$i]['kegiatan_id'];
               $dataRow[$index]['kode_sistem']  = $dataList[$i]['kegiatan_id'];
               $dataRow[$index]['kode']         = $dataList[$i]['kegiatan_kode'];
               $dataRow[$index]['nama']         = $dataList[$i]['kegiatan_nama'];
               $dataRow[$index]['label']        = $dataList[$i]['kegiatan_kode'].' &mdash; '.$dataList[$i]['kegiatan_nama'];
               $dataRow[$index]['tipe']         = '';
               $dataRow[$index]['level']        = 'kegiatan';
            }
            $index++;
         }

         $rowStart      = 7;
         foreach ($dataRow as $row) {
            if($row['id'] === NULL){
               continue;
            }
            $sheet->setCellValueExplicit('A'.$rowStart, $row['kode'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('B'.$rowStart, wordwrap((string)$row['nama'], 60, "\n", false), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C'.$rowStart, $row['tipe'], PHPExcel_Cell_DataType::TYPE_STRING);
            $kodeHeight    = ceil(strlen($row['kode'])/18);
            $namaHeight    = ceil(strlen($row['nama'])/50);
            $tipeHeight    = ceil(strlen($row['tipe'])/15);
            $sheet->getRowDimension($rowStart)->setRowHeight(14*(max($kodeHeight, $namaHeight, tipeHeight)));
            switch (strtoupper($row['level'])) {
               case 'KEGIATAN':
                  $sheet->getStyle('A'.$rowStart.':C'.$rowStart)->getFont()->setUnderline(true)->setBold(true);
                  $sheet->getStyle('A'.$rowStart.':C'.$rowStart)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('70DBFF');
                  break;
               case 'OUTPUT':
                  $sheet->getStyle('A'.$rowStart.':C'.$rowStart)->getFont()->setItalic(true)->setBold(true);
                  $sheet->getStyle('A'.$rowStart.':C'.$rowStart)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('BDEEFF');
                  break;
               case 'KOMPONEN':
                  break;
            }
            $rowStart+=1;
         }

         $sheet->getStyle('A7:C'.($rowStart-1))->applyFromArray($borderTableStyledArray);
         $sheet->getStyle('A7:C'.($rowStart-1))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
         $sheet->getStyle('A7:A'.($rowStart-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
         $sheet->getStyle('B7:B'.($rowStart-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

         $sheet->setCellValue('A'.($rowStart+2), GTFWConfiguration::GetValue('language', 'program'));
         $sheet->getStyle('A'.($rowStart+2).':C'.($rowStart+2))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('70DBFF');
         $sheet->setCellValue('A'.($rowStart+3), GTFWConfiguration::GetValue('language', 'kegiatan'));
         $sheet->getStyle('A'.($rowStart+3).':C'.($rowStart+3))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('BDEEFF');
         $sheet->setCellValue('A'.($rowStart+4), GTFWConfiguration::GetValue('language', 'sub_kegiatan'));

         $sheet->mergeCells('A'.($rowStart+2).':C'.($rowStart+2));
         $sheet->mergeCells('A'.($rowStart+3).':C'.($rowStart+3));
         $sheet->mergeCells('A'.($rowStart+4).':C'.($rowStart+4));

         $sheet->getStyle('A'.($rowStart+2).':C'.($rowStart+4))->applyFromArray($borderTableStyledArray);
         $sheet->getStyle('A'.($rowStart+2).':C'.($rowStart+4))->getFont()->setBold(true);
      }
      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>