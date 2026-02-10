<?php
/**
* ================= doc ====================
* FILENAME     : ViewLaporanRekapAnggaranBelanjaBulanan.xlsx.class.php
* @package     : ViewLaporanRekapAnggaranBelanjaBulanan
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-30
* @Modified    : 2015-04-30
* @Analysts    : Dyah Fajar N
* @contact     : eko.susilo@gamatechno.com
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/summary_realisasi/business/LaporanRekapAnggaranBelanjaBulanan.class.php';

class ViewLaporanRekapAnggaranBelanjaBulanan extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      $mObj       = new LaporanRekapAnggaranBelanjaBulanan();
      $mUnitObj   = new UserUnitKerja();
      $userId     = $mObj->getUserId();
      $unitKerja  = $mUnitObj->GetUnitKerjaRefUser($userId);
      $requestData         = array();
      $arrPeriodeTahun     = $mObj->getPeriodeTahun();
      $curr_mon            = (int)date('m', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['tanggal'])));
      $curr_year           = (int)date('Y', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['tanggal'])));
      $months              = $mObj->indonesianMonth;
      $requestData['bulan']      = $months[$curr_mon]['name'];
      $requestData['ta_id']      = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
      $requestData['unit_id']    = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
      $requestData['unit_nama']  = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);
      $requestData['program_id'] = Dispatcher::Instance()->Decrypt($mObj->_GET['program_id']);
      $requestData['program']    = Dispatcher::Instance()->Decrypt($mObj->_GET['program']);
      $requestData['status_approval']    = Dispatcher::Instance()->Decrypt($mObj->_GET['status_approval']);
      $requestData['tanggal']    = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['tanggal'])));
      foreach ($arrPeriodeTahun as $ta) {
         if((int)$ta['id'] === (int)$requestData['ta_id']){
            $requestData['ta_nama']    = $ta['name'];
         }
      }
      $offset              = 0; 
      $nominalPerBulan           = $mObj->getNominalDetailBelanjaBulanan($requestData);
      $nominalRealisasiPerBulan  = $mObj->getNominaRealisasiBulanan($requestData); 
      $getHeaderBulan      = $mObj->getHeaderBulan($requestData['ta_id'] );
 
      $no            = GTFWConfiguration::GetValue('language', 'no');
      $program       = GTFWConfiguration::GetValue('language', 'program');
      $kegiatan      = GTFWConfiguration::GetValue('language', 'kegiatan');
      $sub_kegiatan  = GTFWConfiguration::GetValue('language', 'sub_kegiatan');
      $unit_kerja    = GTFWConfiguration::GetValue('language', 'unit_kerja');
      $bulan         = GTFWConfiguration::GetValue('language', 'bulan');
      $nilai_rp      = GTFWConfiguration::GetValue('language', 'nilai_rp');
      $kode          = GTFWConfiguration::GetValue('language', 'kode');
      $nama          = GTFWConfiguration::GetValue('language', 'nama');
      $deskripsi     = GTFWConfiguration::GetValue('language', 'deskripsi');
      $laporanTitle  = GTFWConfiguration::GetValue('language', 'laporan_rekap_anggaran_realisasi');

      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('laporan_anggaran_vs_realisasi '.$curr_mon.'_'.$curr_year.'.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(false); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle($laporanTitle);
      # set font default
      $sheet->getDefaultStyle()->getFont()->setName('Courier New')->setSize('9');
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

      $borderTableStyledArray = array(
         'borders' => array(
            'allborders' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN,
               'color' => array('argb' => 'ff000000')
            )
         ), 'alignment' => array(
            'wrap' => true,
            'shrinkToFit' => true,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
         )
      );
      // $sheet->mergeCells('I8:I9');

      $sheet->getRowDimension(1)->setRowHeight(20);
      $sheet->getColumnDimension('C')->setWidth(2);
      $sheet->getColumnDimension('A')->setWidth(8);
      $sheet->getColumnDimension('F')->setWidth(16);
      $sheet->getColumnDimension('E')->setWidth(30);
      $sheet->getColumnDimension('G')->setWidth(20);


      $sheet->setCellValue('A1', strtoupper($laporanTitle));
      $sheet->setCellValue('A2', GTFWConfiguration::GetValue('organization', 'company_full_name')); 

      $sheet->setCellValue('A3', GTFWConfiguration::GetValue('language', 'periode_tahun'));
      $sheet->setCellValue('A4', $unit_kerja); 

      $sheet->setCellValue('C3', ':');
      $sheet->setCellValue('C4', ':'); 
      $sheet->getStyle('C3:C6')->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
         )
      ));

      $sheet->setCellValueExplicit('D3', $requestData['ta_nama'], PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValueExplicit('D4', $requestData['unit_nama'], PHPExcel_Cell_DataType::TYPE_STRING); 

      $sheet->getStyle('A3:I6')->applyFromArray(array(
         'alignment' => array(
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
         ), 'font' => array(
            'bold' => true
         )
      ));
      $sheet->mergeCells('A6:E7');
      $sheet->setCellValue('A6', $deskripsi); 
      $sheet->setCellValue('F6', $bulan);
      $rMon       = 7;
      $cMon       = 5;
      
      //$countColsBulan = ((sizeof($getHeaderBulan)));
      foreach($getHeaderBulan as $bulan){
         $mColName   = $sheet->getCellByColumnAndRow($cMon, $rMon)->getColumn();
         $sheet->getColumnDimension($mColName)->setWidth(18);
         $sheet->setCellValueByColumnAndRow($cMon, $rMon, strtoupper($bulan['nama_bulan']));
         $cMon+=1;
      }
      $colMon     = $sheet->getCellByColumnAndRow(($cMon-1), 6)->getColumn();
      $sheet->mergeCells('F6:'.$colMon.'6');

      
      $sheet->setCellValueByColumnAndRow($cMon, 6, 'TOTAL ANGGARAN ');
      $endColHeader     = $sheet->getCellByColumnAndRow($cMon, 6)->getColumn();
      $sheet->mergeCells($endColHeader.'6:'.$endColHeader.'7');
      $sheet->getColumnDimension($endColHeader)->setWidth(18);
      $sheet->getStyle('A6:'.$endColHeader.'7')->applyFromArray($styledTableHeaderArray);

      $sheet->getStyle('A1:'.$endColHeader.'1')->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         ), 'font' => array(
            'bold' => true,
            'size' => 13
         )
      ));
      $sheet->getStyle('A2:'.$endColHeader.'2')->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         ), 'font' => array(
            'bold' => true,
            'size' => 13
         )
      ));

      $sheet->mergeCells('A1:'.$endColHeader.'1');
      $sheet->mergeCells('A2:'.$endColHeader.'2');
      $sheet->mergeCells('A3:B3');
      $sheet->mergeCells('A4:B4'); 
      $sheet->mergeCells('A6:A7');
      $sheet->mergeCells('B6:E6');
      $sheet->mergeCells('B7:D7');

      $row           = 8; 
      $startRow       = $row; 
      $sheet->getStyle('A10:A'.($row-1))->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
         )
      ));
      $sheet->getStyle('B10:D'.($row-1))->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
         )
      )); 
       $cMon       = 5;
       $rMon       = $row ;
       $sheet->setCellValue('A'.$row,'Anggaran Setelah Revisi');
       $sheet->setCellValue('A'.($row+1),'Realisasi Pencairan');
       foreach($getHeaderBulan as $bulan){
         $mColName   = $sheet->getCellByColumnAndRow($cMon, $rMon)->getColumn();
         $sheet->getColumnDimension($mColName)->setWidth(18); 
         
         
         if( isset($nominalPerBulan['total'][$bulan['kode_bulan']])) {         
            $sheet->setCellValueByColumnAndRow($cMon, $rMon, $nominalPerBulan['total'][$bulan['kode_bulan']]);         
         } else {
            $sheet->setCellValueByColumnAndRow($cMon, $rMon, 0);
         }

         if(isset($nominalRealisasiPerBulan['total'][$bulan['kode_bulan']])) {
            $sheet->setCellValueByColumnAndRow($cMon, ($rMon + 1), $nominalRealisasiPerBulan['total'][$bulan['kode_bulan']]);
         } else {
            $sheet->setCellValueByColumnAndRow($cMon, ($rMon + 1), 0);
         }
         $cMon+=1;
       }

       $colMon     = $sheet->getCellByColumnAndRow(($cMon-1), $row)->getColumn(); 
       
       if(isset($nominalPerBulan['jml_total'])) {        
         $sheet->setCellValue(PHPExcel_Cell::stringFromColumnIndex($cMon).$row,$nominalPerBulan['jml_total']);    
       } else {
         $sheet->setCellValue(PHPExcel_Cell::stringFromColumnIndex($cMon).$row,0);
       }

       if(isset($nominalRealisasiPerBulan['jml_total'])) {
         $sheet->setCellValue(PHPExcel_Cell::stringFromColumnIndex($cMon).($row+1),$nominalRealisasiPerBulan['jml_total']);
       } else {
         $sheet->setCellValue(PHPExcel_Cell::stringFromColumnIndex($cMon).($row+1), 0);
       }
       
       $sheet->mergeCells('A'.$row.':E'.$row);
       $sheet->mergeCells('A'.($row+1).':E'.($row+1));
       $sheet->getStyle('A'.$row.':R'.($row+1))->applyFromArray(array(
                  'font' => array(
                     'bold' => true
                  ),  
                 'alignment' => array(
                     'wrap' => true,
                     'shrinkToFit' => true,
                     'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP                     
                 ),
                 'borders' => array(
                       'allborders' => array(
                          'style' => PHPExcel_Style_Border::BORDER_THIN,
                          'color' => array('argb' => 'ff000000')
                        )
                  )
             )); 
     $sheet->getStyle('F'.$startRow.':'.PHPExcel_Cell::stringFromColumnIndex($cMon).($row+1))->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>