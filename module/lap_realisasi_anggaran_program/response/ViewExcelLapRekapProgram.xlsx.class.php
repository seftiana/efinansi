<?php
/**
* ================= doc ====================
* FILENAME     : ViewExcelLapRekapProgram.xlsx.class.php
* @package     : ViewExcelLapRekapProgram
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-07-18
* @Modified    : 2014-07-18
* @Analysts    : Dyah Fajar N
* @contact     : eko.susilo@gamatechno.com
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/lap_realisasi_anggaran_program/business/AppLapRekapProgram.class.php';

class ViewExcelLapRekapProgram extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('laporan_realisasi_program_'.date('Ymd', time()).'.xls');

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
      $mObj             = new AppLapRekapProgram();
      $requestData      = array();
      $requestData['ta_id']            = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
      $requestData['unit_id']          = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
      $requestData['unit_nama']        = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);
      $requestData['program_id']       = Dispatcher::Instance()->Decrypt($mObj->_GET['program_id']);
      $requestData['program_nama']     = Dispatcher::Instance()->Decrypt($mObj->_GET['program_nama']);
      $requestData['jenis_kegiatan']   = Dispatcher::Instance()->Decrypt($mObj->_GET['jenis_kegiatan']);
      $requestData['bulan']            = Dispatcher::Instance()->Decrypt($mObj->_GET['bulan']);
      $requestData['ta_nama']          = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_nama']);

      $dataUnit         = $mObj->ChangeKeyName($mObj->GetUnitIdentity($requestData['unit_id']));
      $dataList         = $mObj->ChangeKeyName($mObj->GetData(0, 100000, (array)$requestData));
      $total_data       = $mObj->GetCountData();
      $dataResume       = $mObj->ChangeKeyName($mObj->GetDataResume((array)$requestData));
      $requestData['program_nama']     = $requestData['program_id'] == '' ? '-' : $requestData['program_nama'];
      $unitKerja        = $dataUnit[0]['unitkerja_nama'];
      $pimpinan         = $dataUnit[0]['unitkerjaNamaPimpinan'];
      if(empty($pimpinan)){
         $pimpinan      = str_repeat('.', 40);
      }
      $date             = date('Y-m-d', time());
      if(empty($pimpinan)){
         $pimpinan      = str_repeat('.', 50);
      }
      $tanggalCetak     = $mObj->indonesianDate($date);

      if(empty($dataList)){
         $this->setCellValue('A1', 'DATA KOSONG');
      }else{
         // inisialisasi data
         $program          = '';
         $kegiatan         = '';
         $dataGrid         = array();
         $dataMonitoring   = array();
         $index            = 0;

         for ($i=0; $i < count($dataList);) {
            if((int)$dataList[$i]['program_id'] === (int)$program && (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan){
               $programKodeSistem      = $program;
               $kegiatanKodeSistem     = $program.'.'.$kegiatan;

               $dataMonitoring[$programKodeSistem]['nominal_usulan']     += $dataList[$i]['nominal_usulan'];
               $dataMonitoring[$programKodeSistem]['nominal_setuju']     += $dataList[$i]['nominal_setuju'];
               $dataMonitoring[$programKodeSistem]['nominal_pencairan']  += $dataList[$i]['nominal_pencairan'];
               $dataMonitoring[$programKodeSistem]['nominal_realisasi']  += $dataList[$i]['nominal_realisasi'];
               $dataMonitoring[$programKodeSistem]['sisa_dana']          += $dataList[$i]['sisa_dana'];

               $dataMonitoring[$kegiatanKodeSistem]['nominal_usulan']     += $dataList[$i]['nominal_usulan'];
               $dataMonitoring[$kegiatanKodeSistem]['nominal_setuju']     += $dataList[$i]['nominal_setuju'];
               $dataMonitoring[$kegiatanKodeSistem]['nominal_pencairan']  += $dataList[$i]['nominal_pencairan'];
               $dataMonitoring[$kegiatanKodeSistem]['nominal_realisasi']  += $dataList[$i]['nominal_realisasi'];
               $dataMonitoring[$kegiatanKodeSistem]['sisa_dana']          += $dataList[$i]['sisa_dana'];

               $dataGrid[$index]['kode']        = $dataList[$i]['sub_kegiatan_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['sub_kegiatan_nama'];
               $dataGrid[$index]['unit_kode']   = $dataList[$i]['unit_kode'];
               $dataGrid[$index]['unit_nama']   = $dataList[$i]['unit_nama'];
               $dataGrid[$index]['status']      = strtoupper($dataList[$i]['status']);
               $dataGrid[$index]['deskripsi']   = $dataList[$i]['deskripsi'];
               $dataGrid[$index]['nominal_usulan']    = $dataList[$i]['nominal_usulan'];
               $dataGrid[$index]['nominal_setuju']    = $dataList[$i]['nominal_setuju'];
               $dataGrid[$index]['nominal_pencairan'] = $dataList[$i]['nominal_pencairan'];
               $dataGrid[$index]['nominal_realisasi'] = $dataList[$i]['nominal_realisasi'];
               $dataGrid[$index]['sisa_dana']         = $dataList[$i]['sisa_dana'];
               $dataGrid[$index]['type']        = 'sub_kegiatan';
               $i++;
               $start++;
            }elseif ((int)$dataList[$i]['program_id'] === (int)$program && (int)$dataList[$i]['kegiatan_id'] !== (int)$kegiatan) {
               $kegiatan      = (int)$dataList[$i]['kegiatan_id'];
               $kodeSistem    = $program.'.'.$kegiatan;
               $dataMonitoring[$kodeSistem]['nominal_usulan']     = 0;
               $dataMonitoring[$kodeSistem]['nominal_setuju']     = 0;
               $dataMonitoring[$kodeSistem]['nominal_pencairan']  = 0;
               $dataMonitoring[$kodeSistem]['nominal_realisasi']  = 0;
               $dataMonitoring[$kodeSistem]['sisa_dana']          = 0;

               $dataGrid[$index]['kode']        = $dataList[$i]['kegiatan_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['kegiatan_nama'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['type']        = 'kegiatan';
            }else{
               $program       = (int)$dataList[$i]['program_id'];
               $kodeSistem    = $program;
               $dataMonitoring[$kodeSistem]['nominal_usulan']     = 0;
               $dataMonitoring[$kodeSistem]['nominal_setuju']     = 0;
               $dataMonitoring[$kodeSistem]['nominal_pencairan']  = 0;
               $dataMonitoring[$kodeSistem]['nominal_realisasi']  = 0;
               $dataMonitoring[$kodeSistem]['sisa_dana']          = 0;

               $dataGrid[$index]['kode']        = $dataList[$i]['program_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['program_nama'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['type']        = 'program';
            }
            $index++;
         }

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

         $sheet->mergeCells('A1:I1');
         $sheet->mergeCells('B3:I3');
         $sheet->mergeCells('B4:I4');
         $sheet->mergeCells('B5:I5');
         $sheet->mergeCells('A7:B7');
         $sheet->mergeCells('C7:C8');
         $sheet->mergeCells('D7:D8');
         $sheet->mergeCells('E7:E8');
         $sheet->mergeCells('F7:F8');
         $sheet->mergeCells('G7:G8');
         $sheet->mergeCells('H7:H8');
         $sheet->mergeCells('I7:I8');

         $sheet->getRowDimension(1)->setRowHeight(20);
         $sheet->getColumnDimension('A')->setWidth(18);
         $sheet->getColumnDimension('B')->setWidth(35);
         $sheet->getColumnDimension('C')->setWidth(30);
         $sheet->getColumnDimension('D')->setWidth(50);
         $sheet->getColumnDimension('E')->setWidth(23);
         $sheet->getColumnDimension('F')->setWidth(23);
         $sheet->getColumnDimension('G')->setWidth(23);
         $sheet->getColumnDimension('H')->setWidth(23);
         $sheet->getColumnDimension('I')->setWidth(23);

         $sheet->setCellValue('A1', GTFWConfiguration::GetValue('language', 'laporan_realisasi_anggaran_program_pengeluaran'));
         $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);
         $sheet->setCellValue('A3', GTFWConfiguration::GetValue('language', 'tahun_periode'));
         $sheet->setCellValueExplicit('B3', ': '.$requestData['ta_nama'], PHPExcel_Cell_DataType::TYPE_STRING);
         $sheet->setCellValue('A4', GTFWConfiguration::GetValue('language', 'program'));
         $sheet->setCellValueExplicit('B4', ': '.$requestData['program_nama'], PHPExcel_Cell_DataType::TYPE_STRING);
         $sheet->setCellValue('A5', GTFWConfiguration::GetValue('language', 'unit_kerja'));
         $sheet->setCellValueExplicit('B5', ': '.$requestData['unit_nama'], PHPExcel_Cell_DataType::TYPE_STRING);
         $sheet->getStyle('A3:I5')->getFont()->setBold(true);

         $sheet->setCellValue('A7', GTFWConfiguration::GetValue('language', 'program').','.GTFWConfiguration::GetValue('language', 'kegiatan').','.GTFWConfiguration::GetValue('language', 'sub_kegiatan'));
         $sheet->setCellValue('A8', GTFWConfiguration::GetValue('language', 'kode'));
         $sheet->setCellValue('B8', GTFWConfiguration::GetValue('language', 'nama'));
         $sheet->setCellValue('C7', GTFWConfiguration::GetValue('language', 'unit').'/'.GTFWConfiguration::GetValue('language', 'sub_unit'));
         $sheet->setCellValue('D7', GTFWConfiguration::GetValue('language', 'deskripsi'));
         $sheet->setCellValue('E7', GTFWConfiguration::GetValue('language', 'nominal_usulan_rp'));
         $sheet->setCellValue('F7', GTFWConfiguration::GetValue('language', 'nominal_setuju_rp'));
         $sheet->setCellValue('G7', GTFWConfiguration::GetValue('language', 'nominal_pencairan_rp'));
         $sheet->setCellValue('H7', GTFWConfiguration::GetValue('language', 'nominal_realisasi_rp'));
         $sheet->setCellValue('I7', GTFWConfiguration::GetValue('language', 'sisa_rp'));
         $sheet->getStyle('A7:I8')->applyFromArray($styledTableHeaderArray);

         $rows       = 9;
         foreach ($dataGrid as $list) {
            switch (strtoupper($list['type'])) {
               case 'PROGRAM':
                  $list['nominal_usulan']    = $dataMonitoring[$list['kode_sistem']]['nominal_usulan'];
                  $list['nominal_setuju']    = $dataMonitoring[$list['kode_sistem']]['nominal_setuju'];
                  $list['nominal_pencairan'] = $dataMonitoring[$list['kode_sistem']]['nominal_pencairan'];
                  $list['nominal_realisasi'] = $dataMonitoring[$list['kode_sistem']]['nominal_realisasi'];
                  $list['sisa_dana']         = $dataMonitoring[$list['kode_sistem']]['sisa_dana'];
                  $sheet->getStyle('A'.$rows.':I'.$rows)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('00CCCCCC');
                  $sheet->getStyle('A'.$rows.':I'.$rows)->getFont()->setBold(true);
                  $unit_kerja_nama           = '';
                  $deskripsi                 = '';
                  break;
               case 'KEGIATAN':
                  $list['nominal_usulan']    = $dataMonitoring[$list['kode_sistem']]['nominal_usulan'];
                  $list['nominal_setuju']    = $dataMonitoring[$list['kode_sistem']]['nominal_setuju'];
                  $list['nominal_pencairan'] = $dataMonitoring[$list['kode_sistem']]['nominal_pencairan'];
                  $list['nominal_realisasi'] = $dataMonitoring[$list['kode_sistem']]['nominal_realisasi'];
                  $list['sisa_dana']         = $dataMonitoring[$list['kode_sistem']]['sisa_dana'];
                  $sheet->getStyle('A'.$rows.':I'.$rows)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('00DCDCDC');
                  $unit_kerja_nama           = '';
                  $deskripsi                 = '';
                  break;
               default:
                  $list['nominal_usulan']    = $list['nominal_usulan'];
                  $list['nominal_setuju']    = $list['nominal_setuju'];
                  $list['nominal_pencairan'] = $list['nominal_pencairan'];
                  $list['nominal_realisasi'] = $list['nominal_realisasi'];
                  $list['sisa_dana']         = $list['sisa_dana'];
                  $unit_kerja_nama           = $list['unit_nama'];
                  $deskripsi                 = $list['deskripsi'];
                  break;
            }

            $max  = max(array(
               ceil(strlen(strtoupper($unit_kerja_nama))/($sheet->getColumnDimension('C')->getWidth())),
               ceil(strlen(strtoupper($list['kode']))/$sheet->getColumnDimension('A')->getWidth()),
               ceil(strlen(strtoupper($list['nama']))/$sheet->getColumnDimension('B')->getWidth()),
               ceil(strlen(strtoupper($deskripsi))/$sheet->getColumnDimension('D')->getWidth())
            ))*15.8;
            $sheet->getRowDimension($rows)->setRowHeight($max);
            $sheet->setCellValueExplicit('A'.$rows, $list['kode'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('B'.$rows, $list['nama']);
            $sheet->setCellValue('C'.$rows, $list['unit_nama']);
            $sheet->setCellValue('D'.$rows, $list['deskripsi']);
            $sheet->setCellValueExplicit('E'.$rows, $list['nominal_usulan'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('F'.$rows, $list['nominal_setuju'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('G'.$rows, $list['nominal_pencairan'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('H'.$rows, $list['nominal_realisasi'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('I'.$rows, $list['sisa_dana'], PHPExcel_Cell_DataType::TYPE_NUMERIC);

            $rows++;
         }

         $sheet->getStyle('A9:I'.($rows-1))->getAlignment()->setWrapText(true)->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
         $sheet->getStyle('A9:I'.($rows-1))->applyFromArray($borderTableStyledArray);
         $sheet->getStyle('E8:I'.($rows-1))->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
         $sheet->getStyle('A8:A'.($rows-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

         $sheet->mergeCells('A'.($rows+1).':I'.($rows+1));
         $sheet->setCellValue('A'.($rows+1), GTFWConfiguration::GetValue('language', 'resume'));
         $sheet->getStyle('A'.($rows+1).':I'.($rows+1))->getFont()->setBold(true)->setSize(11);
         $sheet->getRowDimension(($rows+1))->setRowHeight(18);
         $sheet->getStyle('A'.($rows+1).':I'.($rows+1))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

         $sheet->mergeCells('A'.($rows+2).':D'.($rows+2));
         $sheet->mergeCells('B'.($rows+3).':D'.($rows+3));
         $sheet->mergeCells('E'.($rows+2).':E'.($rows+3));
         $sheet->mergeCells('F'.($rows+2).':F'.($rows+3));
         $sheet->mergeCells('G'.($rows+2).':G'.($rows+3));
         $sheet->mergeCells('H'.($rows+2).':H'.($rows+3));
         $sheet->mergeCells('I'.($rows+2).':I'.($rows+3));

         $sheet->setCellValue('A'.($rows+2), GTFWConfiguration::GetValue('language', 'program'));
         $sheet->setCellValue('A'.($rows+3), GTFWConfiguration::GetValue('language', 'kode'));
         $sheet->setCellValue('B'.($rows+3), GTFWConfiguration::GetValue('language', 'nama'));
         $sheet->setCellValue('E'.($rows+2), GTFWConfiguration::GetValue('language', 'nominal_usulan_rp'));
         $sheet->setCellValue('F'.($rows+2), GTFWConfiguration::GetValue('language', 'nominal_setuju_rp'));
         $sheet->setCellValue('G'.($rows+2), GTFWConfiguration::GetValue('language', 'nominal_pencairan_rp'));
         $sheet->setCellValue('H'.($rows+2), GTFWConfiguration::GetValue('language', 'nominal_realisasi_rp'));
         $sheet->setCellValue('I'.($rows+2), GTFWConfiguration::GetValue('language', 'sisa_rp'));

         $sheet->getStyle('A'.($rows+2).':I'.($rows+3))->applyFromArray($styledTableHeaderArray);

         $row     = $rows+4;
         foreach ($dataResume as $resume) {
            $sheet->mergeCells('B'.$row.':D'.$row);
            $sheet->setCellValueExplicit('A'.$row, $resume['program_kode'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('B'.$row, $resume['program_nama']);
            $sheet->setCellValueExplicit('E'.$row, $resume['nominal_usulan'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('F'.$row, $resume['nominal_setuju'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('G'.$row, $resume['nominal_pencairan'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('H'.$row, $resume['nominal_realisasi'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('I'.$row, $resume['sisa_dana'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $row++;
         }

         $sheet->getStyle('A'.($rows+4).':I'.($row-1))->applyFromArray($borderTableStyledArray);
         $sheet->getStyle('E'.($rows+4).':I'.($row-1))->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');

         $rowSign       = $row+2;
         $sheet->mergeCells('H'.($rowSign).':I'.($rowSign));
         $sheet->mergeCells('H'.($rowSign+1).':I'.($rowSign+1));
         $sheet->mergeCells('H'.($rowSign+2).':I'.($rowSign+2));
         $sheet->mergeCells('H'.($rowSign+3).':I'.($rowSign+6));
         $sheet->mergeCells('H'.($rowSign+7).':I'.($rowSign+7));
         $sheet->getStyle('H'.$rowSign.':I'.($rowSign+7))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
         $sheet->setCellValue('H'.$rowSign, GTFWConfiguration::GetValue('organization', 'city').', '.$tanggalCetak);
         $sheet->setCellValue('H'.($rowSign+1), GTFWConfiguration::GetValue('language', 'pimpinan_unit_kerja').'/'.GTFWConfiguration::GetValue('language', 'sub_unit'));
         $sheet->setCellValue('H'.($rowSign+2), $unitKerja);
         $sheet->setCellValue('H'.($rowSign+7), $pimpinan);
      }

      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>