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
'module/laporan_realisasi_pengeluaran/business/LaporanRealisasiPengeluaran.class.php';

class ViewLaporanRealisasiPengeluaran extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      $mObj       = new LaporanRealisasiPengeluaran();
      $userId     = $mObj->getUserId();

      $requestData['ta_id']      = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
      $requestData['ta_nama']    = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_nama']);
      $requestData['unit_id']    = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
      $requestData['unit_nama']  = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);
      $requestData['start_date'] = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['start_date'])));
      $requestData['end_date']   = date('Y-m-d', strtotime(Dispatcher::Instance()->Decrypt($mObj->_GET['end_date'])));

      $dataList         = $mObj->getData(0, 10000, (array)$requestData);
      $total_data       = $mObj->Count();
      $start            = 1;
      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('laporan_realisasi_pengeluaran.xls');

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
      $sheet->freezePane('A9');
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

         $borderTableTopStyledArray = array(
            'borders' => array(
               'top' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN,
                  'color' => array('argb' => 'ff000000')
               )
            )
         );

         $borderTableOutlineStyledArray = array(
            'borders' => array(
               'outline' => array(
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

      $nomorLabel       = GTFWConfiguration::GetValue('language', 'no');
      $tanggalLabel     = GTFWConfiguration::GetValue('language', 'tanggal');
      $programLabel     = GTFWConfiguration::GetValue('language', 'program');
      $kegiatanLabel    = GTFWConfiguration::GetValue('language', 'kegiatan');
      $subkegiatanLabel = GTFWConfiguration::GetValue('language', 'sub_kegiatan');
      $anggaranAwalLabel      = 'Anggaran Awal (Rp)';
      $anggaranRevisiLabel    = 'Anggaran Revisi (Rp)';
      $anggaranDisetujuiLabel = 'Total Setelah Revisi (Rp)';
      $usulanLabel      = 'Pengajuan Fpa (Rp)';
      $nominalLabel     = 'FPA Disetujui (Rp)';
      $nominalSisaSaldo = 'Sisa Saldo (Rp)';
      $kodeLabel        = GTFWConfiguration::GetValue('language', 'kode');
      $namaLabel        = GTFWConfiguration::GetValue('language', 'nama');
      $tahunAnggaranLabel  = GTFWConfiguration::GetValue('language', 'periode_tahun');
      $unitkerjaLabel      = GTFWConfiguration::GetValue('language', 'unit_kerja');
      $periodeLabel        = GTFWConfiguration::GetValue('language', 'periode');

      $sheet->mergeCells('A3:B3');
      $sheet->mergeCells('C3:F3');
      $sheet->mergeCells('A4:B4');
      $sheet->mergeCells('C4:F4');
      $sheet->mergeCells('A5:B5');
      $sheet->mergeCells('C5:F5');
      $sheet->mergeCells('A7:A8');
      $sheet->mergeCells('B7:B8');
      $sheet->mergeCells('C7:D7');
      $sheet->mergeCells('E7:E8');
      $sheet->mergeCells('F7:F8');
      $sheet->mergeCells('G7:G8');
      $sheet->mergeCells('H7:H8');
      $sheet->mergeCells('I7:I8');
      $sheet->mergeCells('J7:J8');

      $sheet->getColumnDimension('A')->setWidth(4);
      $sheet->getColumnDimension('B')->setWidth(15);
      $sheet->getColumnDimension('C')->setWidth(18);
      $sheet->getColumnDimension('D')->setWidth(55);
      $sheet->getColumnDimension('E')->setWidth(20);
      $sheet->getColumnDimension('F')->setWidth(20);
      $sheet->getColumnDimension('G')->setWidth(20);
      $sheet->getColumnDimension('H')->setWidth(20);
      $sheet->getColumnDimension('I')->setWidth(20);
      $sheet->getColumnDimension('J')->setWidth(20);
      
      $sheet->getRowDimension(1)->setRowHeight(20);
      $sheet->setCellValue('A1', 'Laporan Realisasi Pengeluaran');
      $sheet->mergeCells('A1:F1');
      $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
      $sheet->setCellValue('A3', $tahunAnggaranLabel);
      $sheet->setCellValueExplicit('C3', $requestData['ta_nama'], PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('A4', $unitkerjaLabel);
      $sheet->setCellValue('C4', $requestData['unit_nama']);
      $sheet->setCellValue('A5', $periodeLabel);
      $sheet->setCellValue('C5', date('d-M-Y', strtotime($requestData['start_date'])).' '.GTFWConfiguration::GetValue('language', 's_d').' '.date('d-M-Y', strtotime($requestData['end_date'])));

      $sheet->getStyle('A3:J5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
      $sheet->getStyle('A3:J5')->applyFromArray(array(
         'font' => array(
            'bold' => true
         ), 'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array(
               'argb' => 'ffE6E6E6'
            )
         ), 'borders' => array(
            'bottom' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN,
               'color' => array('argb' => 'ff000000')
            ), 'top' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN,
               'color' => array('argb' => 'ff000000')
            )
         )
      ));

      $sheet->setCellValue('A7', $nomorLabel);
      $sheet->setCellValue('B7', $tanggalLabel);
      $sheet->setCellValue('C7', $programLabel.', '.$kegiatanLabel, ', '.$subkegiatanLabel);
      $sheet->setCellValue('C8', $kodeLabel);
      $sheet->setCellValue('D8', $namaLabel);
      $sheet->setCellValue('E7', $anggaranAwalLabel);
      $sheet->setCellValue('F7', $anggaranRevisiLabel);
      $sheet->setCellValue('G7', $anggaranDisetujuiLabel);
      $sheet->setCellValue('H7', $usulanLabel);
      $sheet->setCellValue('I7', $nominalLabel);
      $sheet->setCellValue('J7', $nominalSisaSaldo);
      $sheet->getStyle('A7:J8')->applyFromArray($styledTableHeaderArray);
      $row     = 9;
      if (empty($dataList)) {
         $sheet->setCellValue('A'.$row, 'DATA KOSONG');
         $sheet->mergeCells('A'.$row.':J'.$row);
         $sheet->getStyle('A'.$row.':J'.$row)->applyFromArray($borderTableStyledArray);
         $sheet->getStyle('A'.$row.':J'.$row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $sheet->getRowDimension($row)->setRowHeight(16);
      } else {
         $program          = '';
         $kegiatan         = '';
         $index            = 0;
         $dataRealisasi    = array();
         $dataGrid         = array();

         for ($i=0; $i < count($dataList);) {
            if((int)$dataList[$i]['program_id'] === (int)$program && (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan){
               $kodeSistemProgram            = $program;
               $kodeSistemKegiatan           = $program.'.'.$kegiatan;
               
               $dataGrid[$index]['nomor']    = $start;
               $dataGrid[$index]['id']       = $dataList[$i]['id'];
               $dataGrid[$index]['tanggal']  = date('Y-m-d', strtotime($dataList[$i]['tanggal']));
               if(($i > 0) && ($dataList[$i - 1]['kid'] == $dataList[$i]['kid'])){
                    $dataGrid[$index]['nomor']    = '';                        
                    $dataGrid[$index]['kode']     = '';
                    $dataGrid[$index]['nama']     = '';
                    $dataGrid[$index]['nominal_anggaran_awal'] = '';
                    $dataGrid[$index]['nominal_anggaran_revisi'] = '';
                    $dataGrid[$index]['nominal_anggaran_setuju']  = '';
                    $dataGrid[$index]['nominal_sisa_saldo']       = '';
               } else {
                    $dataGrid[$index]['nomor']    = $start;
                    $start++;
                    $dataGrid[$index]['kode']     = $dataList[$i]['sub_kegiatan_kode'];
                    $dataGrid[$index]['nama']     = $dataList[$i]['sub_kegiatan_nama'];
                    $dataGrid[$index]['nominal_anggaran_awal'] = $dataList[$i]['nominal_anggaran_awal'];
                    $dataGrid[$index]['nominal_anggaran_revisi'] = $dataList[$i]['nominal_anggaran_revisi'];
                    $dataGrid[$index]['nominal_anggaran_setuju'] = $dataList[$i]['nominal_anggaran_setuju'];
                    $dataGrid[$index]['nominal_sisa_saldo']      += $dataList[$i]['nominal_anggaran_setuju'] - $dataList[$i]['total_per_subkegiatan'];
               }
                
              
               $dataGrid[$index]['nominal_usulan'] = $dataList[$i]['nominal_usulan'];
               $dataGrid[$index]['nominal_setuju'] = $dataList[$i]['nominal_setuju'];
               $dataGrid[$index]['status']         = strtoupper($dataList[$i]['status']);
               $dataGrid[$index]['spp']            = $dataList[$i]['spp'];
               $dataGrid[$index]['spp_id']         = $dataList[$i]['spp_id'];
               $dataGrid[$index]['tipe']           = 'sub_kegiatan';
               $dataGrid[$index]['row_style']      = '';

               $dataRealisasi[$kodeSistemProgram]['nominal_usulan']  += $dataList[$i]['nominal_usulan'];
               $dataRealisasi[$kodeSistemProgram]['nominal_setuju']  += $dataList[$i]['nominal_setuju'];
               
               $dataRealisasi[$kodeSistemProgram]['nominal_anggaran_awal']    += $dataGrid[$index]['nominal_anggaran_awal'];
               $dataRealisasi[$kodeSistemProgram]['nominal_anggaran_revisi']  += $dataGrid[$index]['nominal_anggaran_revisi'];
               $dataRealisasi[$kodeSistemProgram]['nominal_anggaran_setuju']  += $dataGrid[$index]['nominal_anggaran_setuju'];

               $dataRealisasi[$kodeSistemKegiatan]['nominal_usulan'] += $dataList[$i]['nominal_usulan'];
               $dataRealisasi[$kodeSistemKegiatan]['nominal_setuju'] += $dataList[$i]['nominal_setuju'];
               
               $dataRealisasi[$kodeSistemKegiatan]['nominal_anggaran_awal']   += $dataGrid[$index]['nominal_anggaran_awal'];
               $dataRealisasi[$kodeSistemKegiatan]['nominal_anggaran_revisi'] += $dataGrid[$index]['nominal_anggaran_revisi'];
               $dataRealisasi[$kodeSistemKegiatan]['nominal_anggaran_setuju'] += $dataGrid[$index]['nominal_anggaran_setuju'];
                                           
               $i++;
            }elseif((int)$dataList[$i]['program_id'] === (int)$program && (int)$dataList[$i]['kegiatan_id'] !== (int)$kegiatan){
               $kegiatan         = (int)$dataList[$i]['kegiatan_id'];
               $kodeSistem       = $program.'.'.$kegiatan;
               $dataRealisasi[$kodeSistem]['nominal_usulan']   = 0;
               $dataRealisasi[$kodeSistem]['nominal_setuju']   = 0;

               $dataGrid[$index]['id']       = $dataList[$i]['kegiatan_id'];
               $dataGrid[$index]['kode']     = $dataList[$i]['kegiatan_kode'];
               $dataGrid[$index]['nama']     = $dataList[$i]['kegiatan_nama'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['tipe']        = 'kegiatan';
               $dataGrid[$index]['class_name']  = 'table-common-even2';
               $dataGrid[$index]['row_style']   = '';
            }else{
               $program          = (int)$dataList[$i]['program_id'];
               $kodeSistem       = $program;
               $dataRealisasi[$kodeSistem]['nominal_usulan']   = 0;
               $dataRealisasi[$kodeSistem]['nominal_setuju']   = 0;

               $dataGrid[$index]['id']       = $dataList[$i]['program_id'];
               $dataGrid[$index]['kode']     = $dataList[$i]['program_kode'];
               $dataGrid[$index]['nama']     = $dataList[$i]['program_nama'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['tipe']        = 'program';
               $dataGrid[$index]['class_name']  = 'table-common-even1';
               $dataGrid[$index]['row_style']   = 'font-weight: bold;';
            }
            $index++;
         }

         foreach ($dataGrid as $list) {
            switch (strtoupper($list['tipe'])) {
               case 'PROGRAM':
                  $tanggal                   = '';
                  $list['nominal_anggaran_awal']    = $dataRealisasi[$list['kode_sistem']]['nominal_anggaran_awal'];
                  $list['nominal_anggaran_revisi']  = $dataRealisasi[$list['kode_sistem']]['nominal_anggaran_revisi'];
                  $list['nominal_anggaran_setuju']  = $dataRealisasi[$list['kode_sistem']]['nominal_anggaran_setuju'];
                  $list['nominal_usulan']    = $dataRealisasi[$list['kode_sistem']]['nominal_usulan'];
                  $list['nominal_setuju']    = $dataRealisasi[$list['kode_sistem']]['nominal_setuju'];
                  $list['nominal_sisa_saldo']    = $dataRealisasi[$list['kode_sistem']]['nominal_anggaran_setuju'] - $dataRealisasi[$list['kode_sistem']]['nominal_setuju'];
                  $sheet->setCellValue('B'.$row, '');
                  $sheet->getStyle('A'.$row.':J'.$row)->applyFromArray(array(
                     'font' => array(
                        'bold' => true,
                        'italic' => true
                     ), 
                     'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'startcolor' => array(
                           'argb' => 'ff95CAE4'
                        )
                     ),
                    'borders' => array(
                        'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'ff000000')
                        )
                    )
                  ));
                  break;
               case 'KEGIATAN':
                  $tanggal                   = '';
                  $list['nominal_anggaran_awal']    = $dataRealisasi[$list['kode_sistem']]['nominal_anggaran_awal'];
                  $list['nominal_anggaran_revisi']  = $dataRealisasi[$list['kode_sistem']]['nominal_anggaran_revisi'];
                  $list['nominal_anggaran_setuju']  = $dataRealisasi[$list['kode_sistem']]['nominal_anggaran_setuju'];
                  $list['nominal_usulan']    = $dataRealisasi[$list['kode_sistem']]['nominal_usulan'];
                  $list['nominal_setuju']    = $dataRealisasi[$list['kode_sistem']]['nominal_setuju'];
                  $list['nominal_sisa_saldo']    = $dataRealisasi[$list['kode_sistem']]['nominal_anggaran_setuju'] - $dataRealisasi[$list['kode_sistem']]['nominal_setuju'];
                  $sheet->setCellValue('B'.$row, '');
                  $sheet->getStyle('A'.$row.':J'.$row)->applyFromArray(array(
                     'font' => array(
                        'bold' => true
                     ), 
                     'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'startcolor' => array(
                           'argb' => 'ffBEDEEE'
                        )
                     ),
                    'borders' => array(
                        'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'ff000000')
                        )
                    )
                  ));
                  break;
               case 'SUB_KEGIATAN':
                  $tanggalDay    = date('d', strtotime($list['tanggal']));
                  $tanggalMon    = date('m', strtotime($list['tanggal']));
                  $tanggalYear   = date('Y', strtotime($list['tanggal']));
                  $tanggal       = gmmktime(0,0,0, $tanggalMon, $tanggalDay, $tanggalYear);
                  $time          = PHPExcel_Shared_Date::PHPToExcel($tanggal);
                  if($list['kode'] != ''){
                     $sheet->getStyle('A'.$row.':A'.$row)->applyFromArray($borderTableTopStyledArray);
                     $sheet->getStyle('C'.$row.':J'.$row)->applyFromArray($borderTableTopStyledArray);  
                  }
                  
                  $sheet->getStyle('B'.$row.':B'.$row)->applyFromArray($borderTableStyledArray);
                  $sheet->getStyle('H'.$row.':J'.$row)->applyFromArray($borderTableStyledArray);
                  $list['nominal_usulan']    = $list['nominal_usulan'];
                  $list['nominal_setuju']    = $list['nominal_setuju'];
                  $sheet->setCellValue('B'.$row, PHPExcel_Shared_Date::PHPToExcel($tanggal));
                  $sheet->getStyle('B'.$row)->getNumberFormat()->setFormatCode('DD-MM-YYYY');
                  break;
               default:
                  $tanggalDay    = date('d', strtotime($list['tanggal']));
                  $tanggalMon    = date('m', strtotime($list['tanggal']));
                  $tanggalYear   = date('Y', strtotime($list['tanggal']));
                  $tanggal       = gmmktime(0,0,0, $tanggalMon, $tanggalDay, $tanggalYear);
                  $list['nominal_usulan']    = $list['nominal_usulan'];
                  $list['nominal_setuju']    = $list['nominal_setuju'];
                  $sheet->setCellValue('B'.$row, PHPExcel_Shared_Date::PHPToExcel($tanggal));
                  $sheet->getStyle('B'.$row)->getNumberFormat()->setFormatCode('DD-MM-YYYY');  
                  break;
            }

            $sheet->setCellValue('A'.$row, $list['nomor']);
            $sheet->setCellValueExplicit('C'.$row, $list['kode'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('D'.$row, $list['nama']);
            $sheet->setCellValue('E'.$row, $list['nominal_anggaran_awal']);
            $sheet->setCellValue('F'.$row, $list['nominal_anggaran_revisi']);
            $sheet->setCellValue('G'.$row, $list['nominal_anggaran_setuju']);
            $sheet->setCellValue('H'.$row, $list['nominal_usulan']);
            $sheet->setCellValue('I'.$row, $list['nominal_setuju']);
            $sheet->setCellValue('J'.$row, $list['nominal_sisa_saldo']);

            $sheet->getRowDimension($row)->setRowHeight(15);
            $sheet->getStyle('C'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('E'.$row.':J'.$row)->getNumberFormat()->setFormatCode('#,##0.00_);[RED](#,##0.00)');
            $sheet->getStyle('C'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $row++;
         }
         
         $sheet->getStyle('A9:J'.($row-1))->applyFromArray($borderTableOutlineStyledArray);
         $sheet->getStyle('C9:C'.($row-1))->applyFromArray($borderTableOutlineStyledArray);
         $sheet->getStyle('E9:E'.($row-1))->applyFromArray($borderTableOutlineStyledArray);
         $sheet->getStyle('F9:F'.($row-1))->applyFromArray($borderTableOutlineStyledArray);
      }
      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>