<?php
/**
* ================= doc ====================
* FILENAME     : ViewLaporanRealisasiPengeluaran.xlsx.class.php
* @package     : ViewLaporanRealisasiPengeluaran
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-03-13
* @Modified    : 2015-03-13
* @Analysts    : Dyah Fajar N
* @contact     : eko.susilo@gamatechno.com
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/lap_realisasi_penerimaan_pengeluaran/business/LapRealisasiPenerimaanPengeluaran.class.php';

class ViewLapRealisasiPenerimaanPengeluaran extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      $mObj       = new LapRealisasiPenerimaanPengeluaran();
      $userId     = $mObj->getUserId();

      $requestData['ta_id']      = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
      $requestData['ta_nama']    = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_nama']);
      $requestData['unit_id']    = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
      $requestData['unit_nama']  = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);
      $requestData['start_date'] = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['start_date'])));
      $requestData['end_date']   = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['end_date'])));
      
      $offset           = 0;
      $limit            = 10000;

      $dataList         = $mObj->getDataPenerimaanPengeluaran($offset, $limit, (array)$requestData);
      $total_data       = $mObj->Count();
      
      $dataPengeluaranBulan = $mObj->getPengeluaranPerBulan($offset, $limit, (array)$requestData);
      $dataTotalPengeluaranBulan = $mObj->getTotalPengeluaranPerBulan((array)$requestData);
      
      $dataTotalPenerimaan = $mObj->getTotalPenerimaan($offset, $limit, (array)$requestData);
      $dataTotalPengeluaran = $mObj->getTotalPengeluaran($offset, $limit, (array)$requestData);

      $getHeaderBulan = $mObj->getHeaderBulan($requestData['start_date'],$requestData['end_date']);      
            
      $start            = 1;
      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('lap_realisasi_penerimaan_pengeluaran_'.date('Ymd', time()).'.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(false); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle('laporan');
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
      $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(7,8);
      //$sheet->freezePane('A9');
      # /Document Setting

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
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            'wrap' => true,
            'shrinkToFit' => true
         )
      );

      $nomorLabel            = GTFWConfiguration::GetValue('language', 'no');
      $keteranganLabel       = GTFWConfiguration::GetValue('language', 'keterangan');
      $totalAnggaranLabel    = GTFWConfiguration::GetValue('language', 'total_anggaran');
      $anggaranLabel         = GTFWConfiguration::GetValue('language', 'anggaran');
      $realisasiLabel        = GTFWConfiguration::GetValue('language', 'realisasi');
      $bulanLabel            = GTFWConfiguration::GetValue('language', 'bulan');
      $januaraiLabel         = GTFWConfiguration::GetValue('language', 'januari');
      $februariLabel         = GTFWConfiguration::GetValue('language', 'februari');
      $maretLabel            = GTFWConfiguration::GetValue('language', 'maret');
      $aprilLabel            = GTFWConfiguration::GetValue('language', 'april');
      $meiLabel              = GTFWConfiguration::GetValue('language', 'mei');
      $juniLabel             = GTFWConfiguration::GetValue('language', 'juni');
      $juliLabel             = GTFWConfiguration::GetValue('language', 'juli');
      $agustusLabel          = GTFWConfiguration::GetValue('language', 'agustus');
      $septemberLabel        = GTFWConfiguration::GetValue('language', 'september');
      $oktoberLabel          = GTFWConfiguration::GetValue('language', 'oktober');
      $novemberLabel         = GTFWConfiguration::GetValue('language', 'november');
      $desemberLabel         = GTFWConfiguration::GetValue('language', 'desember');
      
      
      $tahunAnggaranLabel  = GTFWConfiguration::GetValue('language', 'periode_tahun');
      $unitkerjaLabel      = GTFWConfiguration::GetValue('language', 'unit_kerja');
      $periodeLabel        = GTFWConfiguration::GetValue('language', 'periode');
      
      $sheet->getRowDimension(1)->setRowHeight(20);
      $sheet->setCellValue('A1', 'Laporan Realisasi Penerimaan Pengeluaran');
      $sheet->mergeCells('A1:F1');
      $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
      $sheet->setCellValue('B3', $tahunAnggaranLabel);
      $sheet->setCellValueExplicit('C3', $requestData['ta_nama'], PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('B4', $unitkerjaLabel);
      $sheet->setCellValue('C4', $requestData['unit_nama']);
      $sheet->setCellValue('B5', $periodeLabel);
      $sheet->setCellValue('C5', date('d-M-Y', strtotime($requestData['start_date'])).' '.GTFWConfiguration::GetValue('language', 's_d').' '.date('d-M-Y', strtotime($requestData['end_date'])));
      
      $sheet->getStyle('B3:F5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
      $sheet->getStyle('B3:F5')->applyFromArray(array(
         'font' => array(
            'bold' => true
         ),
      ));
       
      $sheet->getColumnDimension('A')->setWidth(5);
      $sheet->getColumnDimension('B')->setWidth(35);
      $sheet->getColumnDimension('C')->setWidth(18);
      $widthColumn = 18;
      $sheet->getColumnDimension('D')->setWidth($widthColumn);
      $sheet->getColumnDimension('E')->setWidth($widthColumn);
      $sheet->getColumnDimension('F')->setWidth($widthColumn);
      $sheet->getColumnDimension('G')->setWidth($widthColumn);
      $sheet->getColumnDimension('H')->setWidth($widthColumn);
      $sheet->getColumnDimension('I')->setWidth($widthColumn);
      $sheet->getColumnDimension('J')->setWidth($widthColumn);
      $sheet->getColumnDimension('K')->setWidth($widthColumn);
      $sheet->getColumnDimension('L')->setWidth($widthColumn);
      $sheet->getColumnDimension('M')->setWidth($widthColumn);
      $sheet->getColumnDimension('N')->setWidth($widthColumn);
      $sheet->getColumnDimension('O')->setWidth($widthColumn);
      $sheet->getColumnDimension('P')->setWidth($widthColumn);
      $sheet->getColumnDimension('Q')->setWidth($widthColumn);
      $sheet->getColumnDimension('R')->setWidth($widthColumn);
      $sheet->getColumnDimension('S')->setWidth($widthColumn);
      $sheet->getColumnDimension('T')->setWidth($widthColumn);
      $sheet->getColumnDimension('U')->setWidth($widthColumn);
      $sheet->getColumnDimension('V')->setWidth($widthColumn);
      $sheet->getColumnDimension('W')->setWidth($widthColumn);
      $sheet->getColumnDimension('X')->setWidth($widthColumn);
      $sheet->getColumnDimension('Y')->setWidth($widthColumn);
      $sheet->getColumnDimension('Z')->setWidth($widthColumn);
      $sheet->getColumnDimension('AA')->setWidth($widthColumn);
      
      $sheet->setCellValue('A7', $nomorLabel);
      $sheet->setCellValue('B7', $keteranganLabel);
      $sheet->setCellValue('C7', $totalAnggaranLabel);
      $sheet->setCellValue('D7', $bulanLabel);

      $countColsBulan = (2 * (sizeof($getHeaderBulan)));
      
      $cols = 3;
      $startC = ($cols );
      foreach($getHeaderBulan as $bulan){
                   
         $sheet->setCellValueByColumnAndRow($cols, 8 , $bulan['nama_bulan']);
         
         $sheet->setCellValueByColumnAndRow($cols, 9 , $anggaranLabel);          
         $sheet->setCellValueByColumnAndRow(($cols + 1), 9 , $realisasiLabel);
         
         $start = PHPExcel_Cell::stringFromColumnIndex($cols);
         $end = PHPExcel_Cell::stringFromColumnIndex(($cols + 1));
         $sheet->mergeCells($start . 8 .':'.$end. 8);
         
         $widthColumn = 18;
         $sheet->getColumnDimension($start)->setWidth($widthColumn);
         $sheet->getColumnDimension($end)->setWidth($widthColumn);
         
         $cols += 2;
         
         //$sheet->mergeCells('D8:E8');
      }
      
      $endC =$startC  + $countColsBulan - 1;
      $sheet->mergeCells(PHPExcel_Cell::stringFromColumnIndex($startC) . 7 . ':'. PHPExcel_Cell::stringFromColumnIndex($endC) . 7);      
            
      $sheet->mergeCells('A7:A9');
      $sheet->mergeCells('B7:B9');
      $sheet->mergeCells('C7:C9');
      $sheet->getStyle(PHPExcel_Cell::stringFromColumnIndex(0) . 7 . ':'. PHPExcel_Cell::stringFromColumnIndex($endC) . 9)->applyFromArray($styledTableHeaderArray);
      
      $row     = 10;
      if (empty($dataList)) {
         $sheet->setCellValue('A'.$row, 'DATA KOSONG');
         $sheet->mergeCells('A'.$row.':AA'.$row);
         $sheet->getRowDimension($row)->setRowHeight(16);
      } else {

         $program          = '';
         $kegiatan         = '';
         $index            = 0;
         $dataRealisasi    = array();
         $dataGrid         = array();
         
         $sectionP =  '';
         $sectionPn =  '';
         
         for ($i=0; $i < count($dataList);) {
            if($dataList[$i]['section'] =='PENERIMAAN') {
                if($sectionPn == $dataList[$i]['section']) {
                    $dataGrid[$index]['section_id']     = $dataList[$i]['section_id'];
                    $dataGrid[$index]['section']        = $dataList[$i]['section'];
                    $dataGrid[$index]['id_kode']        = $dataList[$i]['id_kode'];
                    $dataGrid[$index]['nomor']          = '1.'.$start;
                    $dataGrid[$index]['kode']           = $dataList[$i]['kode'];
                    $dataGrid[$index]['nama']           = $dataList[$i]['nama'];
                    $dataGrid[$index]['nominal_total_usulan'] = $dataList[$i]['nominal_total_usulan'];
                    $dataGrid[$index]['tipe']           = 'kode_penerimaan';
                    $start++;
                    $i++;
                 } elseif($sectionPn != $dataList[$i]['section']) {
                    $sectionPn = $dataList[$i]['section'];
                    $dataGrid[$index]['section_id']     = $dataList[$i]['section_id'];
                    $dataGrid[$index]['section']        = $dataList[$i]['section'];
                    $dataGrid[$index]['id_kode']        = $dataList[$i]['id_kode'];
                    $dataGrid[$index]['nomor']          = '1';
                    $dataGrid[$index]['kode']           = '';
                    $dataGrid[$index]['nama']           = $dataList[$i]['section'];
                    $dataGrid[$index]['tipe']        = 'header';                
                     $start = 1;             
                 }
            } else {
            
            if($sectionP == $dataList[$i]['section']){
               $dataGrid[$index]['section_id']     = $dataList[$i]['section_id'];
               $dataGrid[$index]['section']        = $dataList[$i]['section'];
               $dataGrid[$index]['id_kode']        = $dataList[$i]['id_kode'];
               $dataGrid[$index]['nomor']    = '2.'.$start;
               $dataGrid[$index]['id']       = $dataList[$i]['id'];
               $dataGrid[$index]['tanggal']  = date('Y-m-d', strtotime($dataList[$i]['tanggal']));
               $dataGrid[$index]['kode']           = $dataList[$i]['kode'];
               $dataGrid[$index]['nama']           = $dataList[$i]['nama'];
               $dataGrid[$index]['nominal_total_usulan'] = $dataList[$i]['nominal_total_usulan'];
               $dataGrid[$index]['tipe']           = 'unit';
               $i++;
               $start++;
            
            }elseif($sectionP != $dataList[$i]['section']) {
               $sectionP = $dataList[$i]['section'];
               $dataGrid[$index]['section_id']     = $dataList[$i]['section_id'];
               $dataGrid[$index]['section']        = $dataList[$i]['section'];
               $dataGrid[$index]['id_kode']        = $dataList[$i]['id_kode'];
               $dataGrid[$index]['nomor']          = '2';
               $dataGrid[$index]['kode']           = '';
               $dataGrid[$index]['nama']           = $dataList[$i]['section'];
               $dataGrid[$index]['tipe']        = 'header';                
               $start = 1;     
            }
           
            } 
            
            $index++;
         }
      
         foreach ($dataGrid as $key => $list) {

            $sheet->setCellValue('A'.$row, $list['nomor']);
            $sheet->setCellValueExplicit('A'.$row, $list['nomor'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('B'.$row, $list['nama']);
            $sheet->setCellValue('C'.$row, $list['nominal_total_usulan']);

            $cols = 3;
            foreach($getHeaderBulan as $bulan) {
                if($list['tipe'] != 'header'){                        
                    $sheet->setCellValueByColumnAndRow($cols, $row ,$dataPengeluaranBulan[$list['section_id']][$bulan['kode_bulan']][$list['id_kode']]['nominal_usulan']);   
                    $sheet->setCellValueByColumnAndRow(($cols + 1), $row ,$dataPengeluaranBulan[$list['section_id']][$bulan['kode_bulan']][$list['id_kode']]['nominal_realisasi']);  
                                       
                } else {
                    $sheet->setCellValueByColumnAndRow($cols, $row , '');   
                    $sheet->setCellValueByColumnAndRow(($cols + 1), $row , '');  
                }          
                $cols += 2;                   
            }
            
            $sheet->getStyle('C'.$row.':'.PHPExcel_Cell::stringFromColumnIndex($cols).$row)->getNumberFormat()->setFormatCode('#,##0.00_);[RED](#,##0.00)');
            if($list['tipe'] == 'header'){
                $sheet->getStyle('A'.$row.':'.PHPExcel_Cell::stringFromColumnIndex($cols).$row)->applyFromArray(array(
                            'font' => array(
                                'bold' => true
                            ),
                 ));      
            } 
                                             
            if(isset($dataGrid[$key + 1]['section'])) {
                $cek = $dataGrid[$key + 1]['section'];
            } else {
                $cek = null;
            }
                   
            if($list['section']  != $cek){
                $row++;
                $sheet->setCellValue('A'.$row, '');
                //$sheet->setCellValueExplicit('C'.$row, $list['kode'], PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValue('B'.$row, 'TOTAL '.$list['section']);
                $sheet->setCellValue('C'.$row, $dataTotalPengeluaranBulan[$list['section_id']]['nominal_total_usulan']);
                
                $cols = 3;
                foreach($getHeaderBulan as $bulan) {
                    $sheet->setCellValueByColumnAndRow($cols, $row ,$dataTotalPengeluaranBulan[$list['section_id']][$bulan['kode_bulan']]['nominal_usulan']);   
                    $sheet->setCellValueByColumnAndRow(($cols + 1), $row , $dataTotalPengeluaranBulan[$list['section_id']][$bulan['kode_bulan']]['nominal_realisasi']);    
                                                  
                    $cols += 2;                   
                }   
                                     
                $sheet->getStyle('C'.$row.':'.PHPExcel_Cell::stringFromColumnIndex($cols).$row)->getNumberFormat()->setFormatCode('#,##0.00_);[RED](#,##0.00)');
                $sheet->getStyle('A'.$row.':'.PHPExcel_Cell::stringFromColumnIndex($cols).$row)->applyFromArray(array(
                                'font' => array(
                                'bold' => true
                            ),
                        ));                        
                if($cek != null){
                    $row++;
                    $sheet->setCellValue('A'.$row, '');                            
                    $sheet->mergeCells('A'.$row.':'.PHPExcel_Cell::stringFromColumnIndex($cols).$row);
                }
            }

                

                $sheet->getStyle('B'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('C'.$row.':'.PHPExcel_Cell::stringFromColumnIndex($cols).$row)->getNumberFormat()->setFormatCode('#,##0.00_);[RED](#,##0.00)');
            $row++;                                      
         }
         $sheet->getStyle('A10:'.PHPExcel_Cell::stringFromColumnIndex($cols - 1).($row-1))->applyFromArray($borderTableStyledArray);
      }
                # Save Excel document to local hard disk
      $this->Save();
   }
}
?>