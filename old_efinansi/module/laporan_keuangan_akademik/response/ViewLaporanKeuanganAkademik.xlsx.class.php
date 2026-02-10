<?php


/**
* ================= doc ====================
* FILENAME     : ViewLaporanKeuanganAkademik.xlsx.class.php
* @package     : ViewLaporanKeuanganAkademik
* scope        : PUBLIC
* @Author      : noor hadi
* @Created     : 2015-04-30
* @Modified    : 2015-04-30
* @Analysts    : Dyah Fajar N
* @contact     : noor.hadi@gamatechno.com
* @copyright   : Copyright (c) 2015 Gamatechno
* ================= doc ====================
*/

require_once GTFWConfiguration::GetValue('application','docroot').
'module/laporan_keuangan_akademik/business/LaporanKeuanganAkademik.class.php';

class ViewLaporanKeuanganAkademik extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      $mObj       = new LaporanKeuanganAkademik();
      $mUnitObj   = new UserUnitKerja();
      $userId     = $mObj->getUserId();
      $unitKerja  = $mUnitObj->GetUnitKerjaRefUser($userId);
      $requestData         = array();
      //$arrPeriodeTahun     = $mObj->getPeriodeTahun();
      $curr_mon            = (int)date('m', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['tanggal'])));
      $curr_year           = (int)date('Y', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['tanggal'])));
      $months              = $mObj->indonesianMonth;
      $taId = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
      $getTaDetail = $mObj->GetTahunAnggaranDetailById($taId);
      
      $offset        = 0;
      $limit         = 1000000;   
      //$requestData['bulan']      = $months[$curr_mon]['name'];
      $requestData['tanggal']    = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['tanggal'])));
      
      $bulan =(int) date('m',strtotime($requestData['tanggal']));     
      $tahun =date('Y',strtotime($requestData['tanggal']));
      $namaBulan  = ($months[$bulan]);
      
      
      $dataList            = $mObj->GetDataLaporanKeuanganAkademikLimit($requestData['tanggal'],$offset,$limit);
      $total_data          = $mObj->Count();
     
      $getUnitKerja = $mObj->GetUnitKerja($taId, $bulan);
      $getJumlahKelasPerUnit = $mObj->GetJumlahKelasPerUnit($taId, $bulan);
       
      $getNominalPerUnitPenerimaan = $mObj->GetNominalPerUnitPenerimaan($requestData['tanggal'],$requestData['tanggal'],$taId, $bulan);
      $getNominalPerUnitPengeluaran = $mObj->GetNominalPerUnitPengeluaran($requestData['tanggal'],$requestData['tanggal'],$taId, $bulan);
      
      $get_nominalPerKelompokUnitPenerimaan = $mObj->GetNominalPerKelompokUnitPenerimaan();
      $get_nominalPerKelompokUnitPengeluaran = $mObj->GetNominalPerKelompokUnitPengeluaran();
      
      //total per peneirmaan pengeluarn      
      $getNominalPerPenerimaan = $mObj->GetNominalPerPenerimaan();
      $getNominalPerPengeluaran = $mObj->GetNominalPerPengeluaran();

      $getNominalPerItemPenerimaanBulan = $mObj->GetNominalPerItemPenerimaan($requestData['tanggal'],$requestData['tanggal']);
      $getNominalPerItemPenerimaanRange = $mObj->GetNominalPerItemPenerimaanRange($getTaDetail['tanggal_awal'],$requestData['tanggal']);
      
      $getNominalPerItemPengeluaranBulan = $mObj->GetNominalPerItemPengeluaran($requestData['tanggal'],$requestData['tanggal'],$taId, $bulan);
      $getNominalPerItemPengeluaranRange = $mObj->GetNominalPerItemPengeluaranRange($getTaDetail['tanggal_awal'],$requestData['tanggal']);
   
         
      $getTotalPerPenerimaanBulan = $mObj->GetTotalPerPenerimaan();
      $getTotalPerPenerimaanRange = $mObj->GetTotalPerPenerimaanRange();
      $getTotalPerPengeluaranBulan = $mObj->GetTotalPerPengeluaran();
      $getTotalPerPengeluaranRange = $mObj->GetTotalPerPengeluaranRange();
      
      $getTotalPerKelompokPenerimaanBulan = $mObj->GetTotalPerKelompokPenerimaan();
      $getTotalPerKelompokPenerimaanRange = $mObj->GetTotalPerKelompokPenerimaanRange();     
      $getTotalPerKelompokPengeluaranBulan = $mObj->GetTotalPerKelompokPengeluaran();
      $getTotalPerKelompokPengeluaranRange = $mObj->GetTotalPerKelompokPengeluaranRange();
      
      $getCoaAlokasiAkademik = $mObj->GetCoaAlokasiAkademik();
    
      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('laporan_keuangan_akademik_'.$namaBulan.'_'.$tahun.'.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(true); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle(strtoupper($requestData['bulan']));
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
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            'wrap' => true
         )
      );

      $styledTableKelompokArray = array(
         'borders' => array(
            'allborders' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN,
              // 'color' => array('argb' => 'ff000000')
            )
         ),
         'font' => array(
            'bold' => true,
            'color' => array(
               'rgb' => '000000'
            )
         ),
         'alignment' => array(
            //'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         )
      );

      $styledTableTotalArray = array(
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
           // 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         )
      );      

      $styledTableBoldArray = array(
         'borders' => array(
            'vertical' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN,
               'color' => array('argb' => 'ff000000')
            ),
            'left' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN,
               'color' => array('argb' => 'ff000000')
            ),
            'right' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN,
               'color' => array('argb' => 'ff000000')
            ),
         ),
         
         'font' => array(
            'bold' => true,
            'color' => array(
               'rgb' => '000000'
            )
         ),
         'alignment' => array(
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         )
      ); 

      $styledTableNormalArray = array(
         'borders' => array(
            'vertical' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN,
               'color' => array('argb' => 'ff000000')
            ),
            'left' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN,
               'color' => array('argb' => 'ff000000')
            ),
            'right' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN,
               'color' => array('argb' => 'ff000000')
            ),
         ),
         
         'font' => array(
            'color' => array(
               'rgb' => '000000'
            )
         ),
         'alignment' => array(
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
      
      //$sheet->mergeCells('I8:I9');

      $sheet->getRowDimension(1)->setRowHeight(20);
      
      $sheet->getColumnDimension('A')->setWidth(8);
      $sheet->getColumnDimension('B')->setWidth(50);
      $sheet->getColumnDimension('C')->setWidth(20);
      $sheet->getColumnDimension('D')->setWidth(20);


      $sheet->setCellValue('A1', 'LAPORAN KEUANGAN AKADEMIK');
      $sheet->setCellValue('A2', GTFWConfiguration::GetValue('organization', 'company_full_name'));
      $sheet->setCellValue('A3', $namaBulan.' '.$tahun);
      
      $sheet->getStyle('A1:A3')->applyFromArray(array(
         'alignment' => array(
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         ), 'font' => array(
            'bold' => true,
            'size' => 10
         )
      ));
            
      $sheet->setCellValue('A5', 'No');
      $sheet->setCellValue('B5', 'Perkiraan');
      $sheet->setCellValue('D5', $namaBulan. ' '. $tahun);
      
      if( $getTaDetail['tahun_awal'] == $tahun){
            $sheet->setCellValue('C5', $getTaDetail['nama_bulan_awal']. ' s/d '.$namaBulan. ' '. $tahun);    
      } else {
            $sheet->setCellValue('C5', $getTaDetail['nama_bulan_awal'].' '. $getTaDetail['tahun_awal'].' s/d '.$namaBulan. ' '. $tahun);
      }
      
      $sheet->setCellValue('D6', 'Jumlah Kelas');
      $sheet->mergeCells('A5:A6');
      $sheet->mergeCells('B5:B6');
      $sheet->mergeCells('C5:C6');
      $rMon       = 5;
      $cMon       = 4;

      if(!empty($getUnitKerja)) {
          foreach($getUnitKerja as $key => $v) {              
              $mColName   = $sheet->getCellByColumnAndRow($cMon, 5)->getColumn();
              $sheet->getColumnDimension($mColName)->setWidth(20);
              $sheet->setCellValueByColumnAndRow($cMon, 5, $v['unit_kerja_nama']);
              $cMon++;              
          }
          
          $cMon       = 4;
          foreach($getUnitKerja as $key => $v) {                            
              $mColName   = $sheet->getCellByColumnAndRow($cMon,6)->getColumn();
              $sheet->getColumnDimension($mColName)->setWidth(20);
              $sheet->setCellValueByColumnAndRow($cMon, 6, $getJumlahKelasPerUnit[$v['unit_kerja_id']]);
              $cMon++;              
          }
           
          
      }
          
      $colMon     = $sheet->getCellByColumnAndRow(($cMon-1), 5)->getColumn();      
      $endColHeader     = $sheet->getCellByColumnAndRow(($cMon -1), 5)->getColumn();
      
      $sheet->getStyle('A5:'.$endColHeader.'6')->applyFromArray($styledTableHeaderArray);
      
       if(empty($dataList)){
         
      }else{
         

         $dataGrid      = array();
         $program       = '';
         $kegiatan      = '';
         $subkegiatan   = '';
         $kegiatanDetailId = '';
         $index         = 0;
         $rkt           = array(); // untuk menyimpan nominal rkat
         $rkt_nominal   = array();
         

         $identitasId = '';
         $kelompokId  = '';
         $lka = array();         
         $i2 = 0;
         $kodeT = '';
         
         $noIdentitas = 0;
         $noKelompok = 0;
         $noSubKelompok = 1;
        
         $rMon       = 7;
         $cMon       = 4;
         for ($i=0; $i < count($dataList);) {
              $kodeIdentitas = $dataList[$i]['identitas'];
              $kodeKelompok =  $kodeIdentitas .  $dataList[$i]['kelompok_id'];
              $kodeSubKelompok = $kodeKelompok . $dataList[$i]['sub_kelompok_id'];
              
              $kelompokIdx = $dataList[$i]['kelompok_id'];
              
              if(($identitasId == $kodeIdentitas) &&
                   ($kelompokId == $kodeKelompok) ){
                    
                    $cMon       = 4;
                    foreach($getUnitKerja as $key => $v) {
                      
                       
                        if($dataList[$i]['identitas'] == '1') {
                           if($getNominalPerUnitPenerimaan[$v['unit_kerja_id']][$dataList[$i]['identitas']][$dataList[$i]['kelompok_id']][$dataList[$i]['sub_kelompok_id']]) {
                                $mColName   = $sheet->getCellByColumnAndRow($cMon, $rMon)->getColumn();
                                $sheet->getColumnDimension($mColName)->setWidth(20);
                                $sheet->setCellValueByColumnAndRow($cMon, $rMon,$getNominalPerUnitPenerimaan[$v['unit_kerja_id']][$dataList[$i]['identitas']][$dataList[$i]['kelompok_id']][$dataList[$i]['sub_kelompok_id']]);                                
                            } else {
                                $mColName   = $sheet->getCellByColumnAndRow($cMon, $rMon)->getColumn();
                                $sheet->getColumnDimension($mColName)->setWidth(20);
                                $sheet->setCellValueByColumnAndRow($cMon, $rMon,'');
                            }                     
                        } else {   
                            if($getNominalPerUnitPengeluaran[$v['unit_kerja_id']][$dataList[$i]['identitas']][$dataList[$i]['kelompok_id']][$dataList[$i]['sub_kelompok_id']]) {
                                $mColName   = $sheet->getCellByColumnAndRow($cMon, $rMon)->getColumn();
                                $sheet->getColumnDimension($mColName)->setWidth(20);
                                $sheet->setCellValueByColumnAndRow($cMon, $rMon,
                                    $getNominalPerUnitPengeluaran[$v['unit_kerja_id']][$dataList[$i]['identitas']][$dataList[$i]['kelompok_id']][$dataList[$i]['sub_kelompok_id']]);
                            } else {
                                $mColName   = $sheet->getCellByColumnAndRow($cMon, $rMon)->getColumn();
                                $sheet->getColumnDimension($mColName)->setWidth(20);
                                $sheet->setCellValueByColumnAndRow($cMon, $rMon,'');
                            } 
                        }
                        
                        $cMon++;
                   } 

                  
                   $lka[$i2]['nomor'] = $noIdentitas .'.'.$noKelompok . '.'.$noSubKelompok; 
                   $lka[$i2]['perkiraan'] = $dataList[$i]['sub_kelompok_nama'];
                   $lka[$i2]['nominal'] = $dataList[$i]['nominal'];
                   //$rMon += 1;  
                   $sheet->setCellValue('A'.$rMon,$lka[$i2]['nomor']);
                   $sheet->setCellValue('B'.$rMon,$lka[$i2]['perkiraan']);
                   if($identitasId == '1') {
                        $lka[$i2]['nominal_bulan'] =$getNominalPerItemPenerimaanBulan[$dataList[$i]['identitas']][$dataList[$i]['kelompok_id']][$dataList[$i]['sub_kelompok_id']];
                        $lka[$i2]['nominal'] = $getNominalPerItemPenerimaanRange[$dataList[$i]['identitas']][$dataList[$i]['kelompok_id']][$dataList[$i]['sub_kelompok_id']];
                   } else {
                       $lka[$i2]['nominal_bulan'] = $getNominalPerItemPengeluaranBulan[$dataList[$i]['identitas']][$dataList[$i]['kelompok_id']][$dataList[$i]['sub_kelompok_id']];
                       $lka[$i2]['nominal'] = $getNominalPerItemPengeluaranRange[$dataList[$i]['identitas']][$dataList[$i]['kelompok_id']][$dataList[$i]['sub_kelompok_id']];
                   }     
                   $sheet->setCellValue('D'.$rMon,$lka[$i2]['nominal_bulan']);
                   $sheet->setCellValue('C'.$rMon,$lka[$i2]['nominal']);
                   $endColHeader     = $sheet->getCellByColumnAndRow(($cMon -1), $rMon)->getColumn();
                   $sheet->getStyle('A'.$rMon.':'.$endColHeader.$rMon)->applyFromArray($styledTableNormalArray);
                        
                   //$totalPerIdentitas[$identitasId] += $dataList[$i]['nominal'];
                   //$totalPerKelompok[$kelompokId] += $dataList[$i]['nominal'];
                   
                   
                   $noSubKelompok++;
                   //untuk hitung per kelompok
                   if(isset($dataList[ $i + 1 ]['kelompok_id'])) {
                       $isCekKelompok = $dataList[ $i + 1 ]['kelompok_id'];
                   } else {
                       $isCekKelompok =NULL;
                   }
                   
                   if($kelompokIdx != $isCekKelompok ) {
                       $rMon += 1;
                       $cMon = 4;
                       foreach($getUnitKerja as $key => $v) {
                            if($dataList[$i]['identitas'] == '1') {
                                if($getNominalPerKelompokUnitPenerimaan[$v['unit_kerja_id']][$dataList[$i]['identitas']][$dataList[$i]['kelompok_id']]) {
                                    $mColName   = $sheet->getCellByColumnAndRow($cMon, $rMon)->getColumn();
                                    $sheet->getColumnDimension($mColName)->setWidth(20);
                                    $sheet->setCellValueByColumnAndRow($cMon, $rMon,$getNominalPerKelompokUnitPenerimaan[$v['unit_kerja_id']][$dataList[$i]['identitas']][$dataList[$i]['kelompok_id']]);
                                } else {
                                    $mColName   = $sheet->getCellByColumnAndRow($cMon, $rMon)->getColumn();
                                    $sheet->getColumnDimension($mColName)->setWidth(20);
                                    $sheet->setCellValueByColumnAndRow($cMon, $rMon,'');
                                }                     
                            } else {   
                                if($getNominalPerKelompokUnitPengeluaran[$v['unit_kerja_id']][$dataList[$i]['identitas']][$dataList[$i]['kelompok_id']]) {
                                    $mColName   = $sheet->getCellByColumnAndRow($cMon, $rMon)->getColumn();
                                    $sheet->getColumnDimension($mColName)->setWidth(20);
                                    $sheet->setCellValueByColumnAndRow($cMon, $rMon,$getNominalPerKelompokUnitPengeluaran[$v['unit_kerja_id']][$dataList[$i]['identitas']][$dataList[$i]['kelompok_id']]);
                                } else {
                                    $mColName   = $sheet->getCellByColumnAndRow($cMon, $rMon)->getColumn();
                                    $sheet->getColumnDimension($mColName)->setWidth(20);
                                    $sheet->setCellValueByColumnAndRow($cMon, $rMon,'');
                                } 
                            }
                             $cMon++;
                        } 
                        
                        $lka[$i2]['nomor'] ='';                        
                        $lka[$i2]['perkiraan'] = 'Total '.$dataList[$i]['kelompok_nama'];                     

                        $lka[$i2]['nominal_unit_txt'] =$nominalUnitTotalKelompokTxt;                          
                        if($identitasId == '1') {
                            $lka[$i2]['nominal_bulan'] = $getTotalPerKelompokPenerimaanBulan[$kelompokIdx];
                            $lka[$i2]['nominal'] = $getTotalPerKelompokPenerimaanRange[$kelompokIdx];
                        } else {
                            $lka[$i2]['nominal_bulan'] = $getTotalPerKelompokPengeluaranBulan[$kelompokIdx];
                            $lka[$i2]['nominal'] = $getTotalPerKelompokPengeluaranRange[$kelompokIdx];
                        }
                       
                        $sheet->setCellValue('A'.$rMon,$lka[$i2]['nomor']);
                        $sheet->setCellValue('B'.$rMon,$lka[$i2]['perkiraan']);
                        $sheet->setCellValue('D'.$rMon,$lka[$i2]['nominal_bulan']);
                        $sheet->setCellValue('C'.$rMon,$lka[$i2]['nominal']);
                        $endColHeader     = $sheet->getCellByColumnAndRow(($cMon -1), $rMon)->getColumn();
                        $sheet->getStyle('A'.$rMon.':'.$endColHeader.$rMon)->applyFromArray($styledTableKelompokArray); 
                        //if($isCekKelompok != NULL){
                            $lka[$i2]['class'] = '';
                            $lka[$i2]['row_style'] = '';
                            $lka[$i2]['nomor'] ='';
                            $lka[$i2]['perkiraan'] = '';
                            $lka[$i2]['nominal_bulan'] = '';
                            $lka[$i2]['nominal'] = '';                        
                            
                            $rMon += 1;
                            $sheet->setCellValue('A'.$rMon,$lka[$i2]['nomor']);
                            $sheet->setCellValue('B'.$rMon,$lka[$i2]['perkiraan']);
                            $sheet->setCellValue('D'.$rMon,$lka[$i2]['nominal_bulan']);
                            $sheet->setCellValue('C'.$rMon,$lka[$i2]['nominal']);
                            $endColHeader     = $sheet->getCellByColumnAndRow(($cMon -1), $rMon)->getColumn();
                            $sheet->getStyle('A'.$rMon.':'.$endColHeader.$rMon)->applyFromArray($styledTableNormalArray); 
                        //}
                   }

                   // untuk hitung total
                   if(isset($dataList[ $i + 1 ]['identitas'])) {
                       $isCek = $dataList[ $i + 1 ]['identitas'];
                   } else {
                       $isCek =NULL;
                   }
                   
                   $nominalUnitTxtSpace='';
                  
                   if($identitasId != $isCek ) {
                        $rMon += 1;
                        $cMon = 4;
                       foreach($getUnitKerja as $key => $v) {
                            if($dataList[$i]['identitas'] == '1') {                               
                                
                                if($getNominalPerPenerimaan[$v['unit_kerja_id']][$dataList[$i]['identitas']]) {
                                    $mColName   = $sheet->getCellByColumnAndRow($cMon, $rMon)->getColumn();
                                    $sheet->getColumnDimension($mColName)->setWidth(20);
                                    $sheet->setCellValueByColumnAndRow($cMon, $rMon,$getNominalPerPenerimaan[$v['unit_kerja_id']][$dataList[$i]['identitas']]);
                                } else {
                                    $mColName   = $sheet->getCellByColumnAndRow($cMon, $rMon)->getColumn();
                                    $sheet->getColumnDimension($mColName)->setWidth(20);
                                    $sheet->setCellValueByColumnAndRow($cMon, $rMon,'');
                                }                     
                            } else {   
                                if($getNominalPerPengeluaran[$v['unit_kerja_id']][$dataList[$i]['identitas']]) {
                                    $mColName   = $sheet->getCellByColumnAndRow($cMon, $rMon)->getColumn();
                                    $sheet->getColumnDimension($mColName)->setWidth(20);
                                    $sheet->setCellValueByColumnAndRow($cMon, $rMon,$getNominalPerPengeluaran[$v['unit_kerja_id']][$dataList[$i]['identitas']]);
                                } else {
                                    $mColName   = $sheet->getCellByColumnAndRow($cMon, $rMon)->getColumn();
                                    $sheet->getColumnDimension($mColName)->setWidth(20);
                                    $sheet->setCellValueByColumnAndRow($cMon, $rMon,'');
                                } 
                            }
                             $cMon++;
                        } 
                       
                      
                        $lka[$i2]['nomor'] ='';                        
                        $lka[$i2]['perkiraan'] = 'Total '.($identitasId == '1' ? 'Pendapatan' : 'Beban');                     
                        if($identitasId == '1') {
                            $lka[$i2]['nominal_bulan'] = $getTotalPerPenerimaanBulan;
                            $lka[$i2]['nominal'] = $getTotalPerPenerimaanRange;
                        } else {
                            $lka[$i2]['nominal_bulan'] = $getTotalPerPengeluaranBulan;
                            $lka[$i2]['nominal'] = $getTotalPerPengeluaranRange;
                        }          
                        
                        $sheet->setCellValue('A'.$rMon,$lka[$i2]['nomor']);
                        $sheet->setCellValue('B'.$rMon,$lka[$i2]['perkiraan']);
                        $sheet->setCellValue('D'.$rMon,$lka[$i2]['nominal_bulan']); 
                        $sheet->setCellValue('C'.$rMon,$lka[$i2]['nominal']); 
                        
                        $endColHeader     = $sheet->getCellByColumnAndRow(($cMon -1), $rMon)->getColumn();
                        $sheet->getStyle('A'.$rMon.':'.$endColHeader.$rMon)->applyFromArray($styledTableTotalArray);
                        
                        if($isCek != NULL){
                            $rMon += 1;
                           
                            $lka[$i2]['nomor'] ='';
                            $lka[$i2]['perkiraan'] = '';
                            $lka[$i2]['nominal_bulan'] = '';
                            $lka[$i2]['nominal'] = '';
                            
                            $sheet->setCellValue('A'.$rMon,$lka[$i2]['nomor']);
                            $sheet->setCellValue('B'.$rMon,$lka[$i2]['perkiraan']);
                            $sheet->setCellValue('D'.$rMon,$lka[$i2]['nominal_bulan']);   
                            $sheet->setCellValue('C'.$rMon,$lka[$i2]['nominal']);   
                            $endColHeader     = $sheet->getCellByColumnAndRow(($cMon -1), $rMon)->getColumn();
                            $sheet->getStyle('A'.$rMon.':'.$endColHeader.$rMon)->applyFromArray($styledTableNormalArray);
                        }
                   }
                
                   $i++;
               }  elseif($identitasId != $kodeIdentitas) {
                    $identitasId = $kodeIdentitas;
                    $noSubKelompok = 1;
                    $noKelompok = 0;
                    $noIdentitas++;
                    $cMon = 4;
                    foreach($getUnitKerja as $key => $v) {
                        if($dataList[$i]['identitas'] == '1') {
                            $mColName   = $sheet->getCellByColumnAndRow($cMon, $rMon)->getColumn();
                            $sheet->getColumnDimension($mColName)->setWidth(20);
                            $sheet->setCellValueByColumnAndRow($cMon, $rMon,'');
                        } else {   
                            $mColName   = $sheet->getCellByColumnAndRow($cMon, $rMon)->getColumn();
                            $sheet->getColumnDimension($mColName)->setWidth(20);
                            $sheet->setCellValueByColumnAndRow($cMon, $rMon,''); 
                        }
                         $cMon++;
                   } 
                    
                   
                   $lka[$i2]['nomor'] = $noIdentitas; 
                   $lka[$i2]['perkiraan'] = ($identitasId == '1' ? 'Pendapatan' : 'Beban');
                   $lka[$i2]['nominal_bulan'] = '';   //number_format($dataList[$i]['nominal'], 0, ',','.');
                   $lka[$i2]['nominal'] = '';   //number_format($dataList[$i]['nominal'], 0, ',','.');
                   
                   $sheet->setCellValue('A'.$rMon,$lka[$i2]['nomor']);
                   $sheet->setCellValue('B'.$rMon,$lka[$i2]['perkiraan']);
                   $sheet->setCellValue('D'.$rMon,$lka[$i2]['nominal_bulan']);
                   $sheet->setCellValue('C'.$rMon,$lka[$i2]['nominal']);
                   $endColHeader     = $sheet->getCellByColumnAndRow(($cMon -1), $rMon)->getColumn();
                   $sheet->getStyle('A'.$rMon.':'.$endColHeader.$rMon)->applyFromArray($styledTableBoldArray);
                    
               }  elseif($kelompokId != $kodeKelompok) {
                    $kelompokId = $kodeKelompok;
                    $noSubKelompok = 1;  
                    $noKelompok++;
                    $cMon = 4;
                    foreach($getUnitKerja as $key => $v) {
                        if($dataList[$i]['identitas'] == '1') {
                            $mColName   = $sheet->getCellByColumnAndRow($cMon, $rMon)->getColumn();
                            $sheet->getColumnDimension($mColName)->setWidth(20);
                            $sheet->setCellValueByColumnAndRow($cMon, $rMon,'');
                        } else {   
                            $mColName   = $sheet->getCellByColumnAndRow($cMon, $rMon)->getColumn();
                            $sheet->getColumnDimension($mColName)->setWidth(20);
                            $sheet->setCellValueByColumnAndRow($cMon, $rMon,''); 
                        }
                         $cMon++;
                   } 
                    
                  
                   $lka[$i2]['nomor'] = $noIdentitas.'.'.$noKelompok;
                   $lka[$i2]['perkiraan'] = $dataList[$i]['kelompok_nama'];
                   $lka[$i2]['nominal_bulan'] = '';   //number_format($dataList[$i]['nominal'], 0, ',','.');
                   $lka[$i2]['nominal'] = '';   //number_format($dataList[$i]['nominal'], 0, ',','.');
                   
                   $sheet->setCellValue('A'.$rMon,$lka[$i2]['nomor']);
                   $sheet->setCellValue('B'.$rMon,$lka[$i2]['perkiraan']);
                   $sheet->setCellValue('D'.$rMon,$lka[$i2]['nominal_bulan']);
                   $sheet->setCellValue('C'.$rMon,$lka[$i2]['nominal']);
                   $endColHeader     = $sheet->getCellByColumnAndRow(($cMon -1), $rMon)->getColumn();
                   $sheet->getStyle('A'.$rMon.':'.$endColHeader.$rMon)->applyFromArray($styledTableBoldArray);
               }
               $i2++;
               $rMon++;
            }            
         }  
      
      $sheet->getStyle('A7:A'.$rMon)->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
         )
      ));
      $sheet->getStyle('C7:'.$endColHeader.$rMon)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
      # Save Excel document to local hard disk
      $this->Save();
   }
}

?>