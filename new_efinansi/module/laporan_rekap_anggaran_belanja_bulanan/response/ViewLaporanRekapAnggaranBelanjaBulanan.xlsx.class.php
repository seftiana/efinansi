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
'module/laporan_rekap_anggaran_belanja_bulanan/business/LaporanRekapAnggaranBelanjaBulanan.class.php';

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
      $limit               = 1000000;
      $dataList            = $mObj->getDataAnggaranBelanjaBulanan($offset, $limit, $requestData);
      $nominalPerBulan     = $mObj->getNominalDetailBelanjaBulanan($requestData);

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

      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('laporan_rekap_anggaran_belanja_'.$curr_mon.'_'.$curr_year.'.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(false); # false: Hide the gridlines; true: show the gridlines
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
      $sheet->getColumnDimension('E')->setWidth(50);
      $sheet->getColumnDimension('G')->setWidth(20);


      $sheet->setCellValue('A1', strtoupper(GTFWConfiguration::GetValue('language', 'laporan_rekap_anggaran_belanja_bulanan')));
      $sheet->setCellValue('A2', GTFWConfiguration::GetValue('organization', 'company_full_name'));


      $sheet->setCellValue('A3', GTFWConfiguration::GetValue('language', 'periode_tahun'));
      $sheet->setCellValue('A4', $unit_kerja);
      //$sheet->setCellValue('A5', $bulan);
      //$sheet->setCellValue('A6', GTFWConfiguration::GetValue('language', 'tahun'));

      $sheet->setCellValue('C3', ':');
      $sheet->setCellValue('C4', ':');
      //$sheet->setCellValue('C5', ':');
      //$sheet->setCellValue('C6', ':');
      $sheet->getStyle('C3:C6')->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
         )
      ));

      $sheet->setCellValueExplicit('D3', $requestData['ta_nama'], PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValueExplicit('D4', $requestData['unit_nama'], PHPExcel_Cell_DataType::TYPE_STRING);
      //$sheet->setCellValueExplicit('D5', $requestData['bulan'], PHPExcel_Cell_DataType::TYPE_STRING);
      //$sheet->setCellValueExplicit('D6', $curr_year, PHPExcel_Cell_DataType::TYPE_STRING);

      $sheet->getStyle('A3:I6')->applyFromArray(array(
         'alignment' => array(
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
         ), 'font' => array(
            'bold' => true
         )
      ));

      $sheet->setCellValue('A6', $no);
      $sheet->setCellValue('B6', $program.', '.$kegiatan.', '.$sub_kegiatan);
      $sheet->setCellValue('B7', $kode);
      $sheet->setCellValue('E7', $nama);
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
      //$sheet->mergeCells('A5:B5');
      //$sheet->mergeCells('A6:B6');
      //$sheet->mergeCells('D3:'.$endColHeader.'3');
      //$sheet->mergeCells('D4:'.$endColHeader.'4');
      //$sheet->mergeCells('D5:'.$endColHeader.'5');
      //$sheet->mergeCells('D6:'.$endColHeader.'6');
      $sheet->mergeCells('A6:A7');
      $sheet->mergeCells('B6:E6');
      $sheet->mergeCells('B7:D7');

      if(empty($dataList)){
         $sheet->setCellValue('A10', GTFWConfiguration::GetValue('language', 'data_kosong'));
         $sheet->mergeCells('A10:I10');
         $sheet->getRowDimension(10)->setRowHeight(20);
         $sheet->getStyle('A10:I10')->applyFromArray(array(
            'alignment' => array(
               'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
               'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ), 'font' => array(
               'bold' => true
            ), 'borders' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN
            )
         ));
      }else{
         $row           = 8;
         $dataGrid      = array();
         $program       = '';
         $kegiatan      = '';
         $subkegiatan   = '';
         $index         = 0;
         $rkt           = array(); // untuk menyimpan nominal rkat
         $rkt_nominal   = array();
         $start         = 1;
         $totalPerBulan = array();
         for ($i=0; $i < count($dataList);) {
            if(
                (int)$program === (int)$dataList[$i]['program_id'] && 
                (int)$kegiatan === (int)$dataList[$i]['kegiatan_id'] && 
                (int)$subkegiatan === (int)$dataList[$i]['sub_kegiatan_id']
               ){
               //$index--;
               $programKodeSistem      = $program.'.0.0.0';
               $kegiatanKodeSistem     = $program.'.'.$kegiatan.'.0.0';
               $subKegiatanKodeSistem  = $program.'.'.$kegiatan.'.'.$subkegiatan .'.0';
               $kodeSistem             = $program.'.'.$kegiatan.'.'.$subkegiatan .'.' . $dataList[$i]['detail_belanja_kode'];
               
                $dataGrid[$index]['id']          = $dataList[$i]['id'];
               $dataGrid[$index]['parent_id']   = $dataList[$i]['keg_id'];
               $dataGrid[$index]['nomor']       = $start;
               $dataGrid[$index]['program_id']  = $dataList[$i]['program_id'];
               $dataGrid[$index]['kegiatan_id'] = $dataList[$i]['kegiatan_id'];
               $dataGrid[$index]['kode']        = '';
               $dataGrid[$index]['nama']        = $dataList[$i]['detail_belanja_nama'];
               $dataGrid[$index]['type']        = 'RP';
               $dataGrid[$index]['nominal']     = $dataList[$i]['detail_belanja_nominal'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;

               $start++;
               $i++;
            } elseif(
                (int)$program === (int)$dataList[$i]['program_id'] && 
                (int)$kegiatan === (int)$dataList[$i]['kegiatan_id'] && 
                (int)$subkegiatan !== (int)$dataList[$i]['sub_kegiatan_id']){
               $subkegiatan            = (int)$dataList[$i]['sub_kegiatan_id'];
               $programKodeSistem      = $program.'.0.0.0';
               $kegiatanKodeSistem     = $program.'.'.$kegiatan.'.0.0';
               $kodeSistem             = $program.'.'.$kegiatan.'.'.$dataList[$i]['sub_kegiatan_id'].'.0';
               $rkt_nominal[$subKegiatanKodeSistem]['nominal']    = 0;

               $dataGrid[$index]['id']          = $dataList[$i]['id'];
               $dataGrid[$index]['parent_id']   = $dataList[$i]['keg_id'];
               $dataGrid[$index]['program_id']  = $dataList[$i]['program_id'];
               $dataGrid[$index]['kegiatan_id'] = $dataList[$i]['kegiatan_id'];
               $dataGrid[$index]['kode']        = $dataList[$i]['sub_kegiatan_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['sub_kegiatan_nama'];
               $dataGrid[$index]['type']        = 'SUB_KEGIATAN';
               #$dataGrid[$index]['nomor']       = $start;
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['deskripsi']   = $dataList[$i]['deskripsi'];
               $dataGrid[$index]['class_name']     = ($start % 2 <> 0) ? 'table-common-even' : '';
               $dataGrid[$index]['nominal'] = $nominalPerBulan['nominal'][$kodeSistem];

               #$start++;
            }elseif((int)$program === (int)$dataList[$i]['program_id'] && (int)$kegiatan !== (int)$dataList[$i]['kegiatan_id']){
               $kegiatan      = (int)$dataList[$i]['kegiatan_id'];
               $kodeSistem    = $program.'.'.$kegiatan.'.0.0';
               $rkt_nominal[$kodeSistem]['nominal']     = 0;
               $dataGrid[$index]['kode']        = $dataList[$i]['kegiatan_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['kegiatan_nama'];
               $dataGrid[$index]['type']        = 'KEGIATAN';
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['class_name']  = 'table-common-even2';
               $dataGrid[$index]['row_style']   = 'font-weight: bold; font-style: italic;';
               $dataGrid[$index]['nominal'] = $nominalPerBulan['nominal'][$kodeSistem];               
               
            }else{
               $program       = (int)$dataList[$i]['program_id'];
               $kodeSistem    = $program.'.0.0.0';

               $rkt_nominal[$kodeSistem]['nominal']     = 0;
               $dataGrid[$index]['kode']        = $dataList[$i]['program_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['program_nama'];
               $dataGrid[$index]['type']        = 'PROGRAM';
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['class_name']  = 'table-common-even1';
               $dataGrid[$index]['row_style']   = 'font-weight: bold;';
               $dataGrid[$index]['nominal'] = $nominalPerBulan['nominal'][$kodeSistem];

            }

            $index++;
         }
         $startRow = $row;
         foreach ($dataGrid as $grid) {

            $sheet->mergeCells('B'.$row.':D'.$row);
            $sheet->setCellValue('A'.$row, $grid['nomor']);
            $sheet->setCellValueExplicit('B'.$row, $grid['kode'],PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('E'.$row, $grid['nama']);
            $col_rkat   = 5;
            $start_col  = $sheet->getCellByColumnAndRow(5, $row)->getColumn();
                 
            foreach($getHeaderBulan as $key => $bulan) {   
               $sheet->getCellByColumnAndRow($col_rkat, $row)->setValueExplicit($nominalPerBulan[$grid['kode_sistem']][$bulan['kode_bulan']], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $col_rkat+=1;
            }
            $end_col    = $sheet->getCellByColumnAndRow(($col_rkat-1), $row)->getColumn();
            $sheet->getCellByColumnAndRow($col_rkat, $row)->setValueExplicit('=SUM('.$start_col.$row.':'.$end_col.$row.')', PHPExcel_Cell_DataType::TYPE_FORMULA);
            $ecol       = $sheet->getCellByColumnAndRow($col_rkat, $row)->getColumn();
            //$sheet->getStyle($start_col.$row.':'.$ecol.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
            $sheet->getStyle('A'.$row.':'.$ecol.$row)->applyFromArray($borderTableStyledArray);
            switch (strtoupper($grid['type'])) {
               case 'PROGRAM':
                  $sheet->getStyle('A'.$row.':'.$ecol.$row)->applyFromArray(array(
                     'font' => array(
                        'bold' => true
                     ), 'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                           'startcolor' => array(
                              'argb' => 'ffa6a6a6'
                           )
                     )
                  ));
                  break;
               case 'KEGIATAN':
                  $sheet->getStyle('A'.$row.':'.$ecol.$row)->applyFromArray(array(
                     'font' => array(
                        'bold' => true
                     ), 'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                           'startcolor' => array(
                              'argb' => 'ffE6E6E6'
                           )
                     )
                  ));
                  break;
               case 'SUB_KEGIATAN':
                  $nominal       = $grid['nominal'];
                  $sheet->getStyle('A'.$row.':'.$ecol.$row)->applyFromArray(array(
                     'font' => array(
                        'bold' => true
                     )));
                  break;
               default:
                  $nominal       = 0;
                  break;
            }

            $row++;
         }

         // $sheet->getStyle('I10:I'.($row-1))->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
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
         // $sheet->getStyle('F10:F'.($row-1))->applyFromArray(array(
         //    'alignment' => array(
         //       'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
         //    )
         // ));
         //$row += 1;
          $cMon       = 5;
          $rMon       = $row ;
          $sheet->setCellValue('A'.$row,'Total');
          foreach($getHeaderBulan as $bulan){
            $mColName   = $sheet->getCellByColumnAndRow($cMon, $rMon)->getColumn();
            $sheet->getColumnDimension($mColName)->setWidth(18);
            $sheet->setCellValueByColumnAndRow($cMon, $rMon, $nominalPerBulan['total'][$bulan['kode_bulan']]);
            $cMon+=1;
          }
         
          $colMon     = $sheet->getCellByColumnAndRow(($cMon-1), $row)->getColumn();
          $sheet->setCellValue(PHPExcel_Cell::stringFromColumnIndex($cMon).$row,$nominalPerBulan['jml_total']);
          
          $sheet->mergeCells('A'.$row.':E'.$row);
          $sheet->getStyle('A'.$row.':R'.$row)->applyFromArray(array(
                     'font' => array(
                        'bold' => true
                     ), 
                     'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                           'startcolor' => array(
                               'argb' => 'ffcccccc'
                           )
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
      }
     $sheet->getStyle('F'.$startRow.':'.PHPExcel_Cell::stringFromColumnIndex($cMon).$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>