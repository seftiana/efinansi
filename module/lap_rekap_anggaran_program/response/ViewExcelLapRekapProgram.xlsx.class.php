<?php
/**
* ================= doc ====================
* FILENAME     : ViewExcelLapRekapProgram.xlsx.class.php
* @package     : ViewExcelLapRekapProgram
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-07-14
* @Modified    : 2014-07-14
* @Analysts    : Dyah Fajar N
* @contact     : eko.susilo@gamatechno.com
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/lap_rekap_anggaran_program/business/AppLapRekapProgram.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'main/function/date.php';
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
      $this->SetFileName('laporan_rekapitulasi_anggaran_program_pengeluaran.'.date('Ymd', time()).'.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(false); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle('laporan_rekap_program');
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

      $mObj                = new AppLapRekapProgram();
      $requestData         = array();
      $arrTahunAnggaran    = $mObj->GetPeriodeTahun();
      $arrJenisKegiatan    = $mObj->GetComboJenisKegiatan();
      $requestData['ta_id']            = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
      $requestData['unit_id']          = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
      $requestData['unit_nama']        = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);
      $requestData['program_id']       = Dispatcher::Instance()->Decrypt($mObj->_GET['program_id']);
      $requestData['program_nama']     = Dispatcher::Instance()->Decrypt($mObj->_GET['program_nama']);
      $requestData['jenis_kegiatan']   = Dispatcher::Instance()->Decrypt($mObj->_GET['jenis_kegiatan']);

      foreach ($arrTahunAnggaran as $ta) {
         if((int)$ta['id'] === (int)$requestData['ta_id']){
            $requestData['ta_nama']    = $ta['name'];
         }
      }

      foreach ($arrJenisKegiatan as $jenis) {
         if((int)$jenis['id'] === (int)$requestData['jenis_kegiatan']){
            $requestData['jenis_kegiatan_nama']    = $jenis['name'];
         }else{
            $requestData['jenis_kegiatan_nama']    = 'Semua '.GTFWConfiguration::GetValue('language', 'jenis_kegiatan');
         }
      }

      $requestData['program_nama']  = ($requestData['program_id'] == '') ? 'Semua '.GTFWConfiguration::GetValue('language', 'program') : $requestData['program_nama'];
      $offset              = 0;
      $limit               = 100000;
      $dataList            = $mObj->GetData($offset, $limit, (array)$requestData);
      $total_data          = $mObj->GetCountData();
      $dataResume          = $mObj->GetResume((array)$requestData);

      if (empty($dataList)) {
         $sheet->setCellValue('A1', 'Data Kosong');
      } else {
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

         $headerTitle      = GTFWConfiguration::GetValue('language', 'laporan_rekapitulasi_anggaran_program_pengeluaran');
         $taLabel          = GTFWConfiguration::GetValue('language', 'tahun_periode');
         $unitLabel        = GTFWConfiguration::GetValue('language', 'unit').'/'.GTFWConfiguration::GetValue('language', 'sub_unit');
         $programLabel     = GTFWConfiguration::GetValue('language', 'program');
         $kegiatanLabel    = GTFWConfiguration::GetValue('language', 'kegiatan');
         $subkegLabel      = GTFWConfiguration::GetValue('language', 'sub_kegiatan');
         $jenisKegLabel    = GTFWConfiguration::GetValue('language', 'jenis_kegiatan');
         $nominalLabel     = GTFWConfiguration::GetValue('language', 'nominal_setuju_rp');
         $kodeLabel        = GTFWConfiguration::GetValue('language', 'kode');
         $namaLabel        = GTFWConfiguration::GetValue('language', 'nama');

         // merge cells
         $sheet->mergeCells('A1:D1');
         $sheet->mergeCells('A8:B8');
         $sheet->mergeCells('C8:C9');
         $sheet->mergeCells('D8:D9');
         $sheet->mergeCells('B3:D3');
         $sheet->mergeCells('B4:D4');
         $sheet->mergeCells('B5:D5');
         $sheet->mergeCells('B6:D6');

         $sheet->getColumnDimension('A')->setWidth(15);
         $sheet->getColumnDimension('B')->setWidth(50);
         $sheet->getColumnDimension('C')->setWidth(35);
         $sheet->getColumnDimension('D')->setWidth(20);
         $sheet->getRowDimension(1)->setRowHeight(20);

         $sheet->setCellValue('A1', $headerTitle);
         $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);

         $sheet->setCellValue('A3', $taLabel);
         $sheet->setCellValue('A4', $unitLabel);
         $sheet->setCellValue('A5', $programLabel);
         $sheet->setCellValue('A6', $jenisKegLabel);

         $sheet->setCellValueExplicit('B3', $requestData['ta_nama'], PHPExcel_Cell_DataType::TYPE_STRING);
         $sheet->setCellValueExplicit('B4', $requestData['unit_nama'], PHPExcel_Cell_DataType::TYPE_STRING);
         $sheet->setCellValueExplicit('B5', $requestData['program_nama'], PHPExcel_Cell_DataType::TYPE_STRING);
         $sheet->setCellValueExplicit('B6', $requestData['jenis_kegiatan_nama'], PHPExcel_Cell_DataType::TYPE_STRING);

         $sheet->setCellValue('A8', $programLabel.'/'.$kegiatanLabel.'/'.$subkegLabel);
         $sheet->setCellValue('A9', $kodeLabel);
         $sheet->setCellValue('B9', $namaLabel);
         $sheet->setCellValue('C8', $unitLabel);
         $sheet->setCellValue('D8', $nominalLabel);
         $sheet->getStyle('A8:D9')->applyFromArray($styledTableHeaderArray);
         $dataList      = $mObj->ChangeKeyName($dataList);
         $dataResume    = $mObj->ChangeKeyName($dataResume);

         # inisialisasi data
         $program       = '';
         $kegiatan      = '';
         $index         = 0;
         $dataGrid      = array();
         $dataRekap     = array();
         $row           = 10;
         for ($i=0; $i < count($dataList);) {
            if((int)$dataList[$i]['program_id'] === (int)$program && (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan){
               $programKodeSistem         = $program;
               $kegiatanKodeSistem        = $program.'.'.$kegiatan;
               $dataRekap[$programKodeSistem]['nominal_setuju']      += $dataList[$i]['nominal_setuju'];
               $dataRekap[$kegiatanKodeSistem]['nominal_setuju']     += $dataList[$i]['nominal_setuju'];

               $dataGrid[$index]['id']    = $dataList[$i]['sub_kegiatan_id'];
               $dataGrid[$index]['kode']  = $dataList[$i]['sub_kegiatan_kode'];
               $dataGrid[$index]['nama']  = $dataList[$i]['sub_kegiatan_nama'];
               $dataGrid[$index]['unit_nama']      = $dataList[$i]['unit_nama'];
               $dataGrid[$index]['nominal_setuju'] = $dataList[$i]['nominal_setuju'];
               $dataGrid[$index]['type']           = 'sub_kegiatan';
               $dataGrid[$index]['class_name']     = ($start % 2 <> 0) ? 'table-common-even' : '';
               $i++;
               $start++;
            }elseif((int)$dataList[$i]['program_id'] === (int)$program && (int)$dataList[$i]['kegiatan_id'] !== (int)$kegiatan){
               $kegiatan         = (int)$dataList[$i]['kegiatan_id'];
               $kodeSistem       = $program.'.'.$kegiatan;
               $dataRekap[$kodeSistem]['nominal_setuju']    = 0;
               $dataGrid[$index]['id']    = $dataList[$i]['kegiatan_id'];
               $dataGrid[$index]['kode']  = $dataList[$i]['kegiatan_kode'];
               $dataGrid[$index]['nama']  = $dataList[$i]['kegiatan_nama'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['type']        = 'kegiatan';
               $dataGrid[$index]['class_name']  = 'table-common-even2';
            }else{
               $program          = (int)$dataList[$i]['program_id'];
               $kodeSistem       = $program;
               $dataRekap[$kodeSistem]['nominal_setuju']    = 0;
               $dataGrid[$index]['id']    = $dataList[$i]['program_id'];
               $dataGrid[$index]['kode']  = $dataList[$i]['program_kode'];
               $dataGrid[$index]['nama']  = $dataList[$i]['program_nama'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['type']        = 'program';
               $dataGrid[$index]['class_name']  = 'table-common-even1';
               $dataGrid[$index]['row_style']   = 'font-weight: bold;';
            }
            $index++;
         }

         foreach ($dataGrid as $list) {
            switch (strtoupper($list['type'])) {
               case 'PROGRAM':
                  $list['nominal_setuju']    = $dataRekap[$list['kode_sistem']]['nominal_setuju'];
                  $sheet->getStyle('A'.$row.':D'.$row)->getFill()->getStartColor()->setARGB('ffCCCCCC');
                  $sheet->getStyle('A'.$row.':D'.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                  $sheet->mergeCells('B'.$row.':C'.$row);
                  $sheet->getStyle('A'.$row.':D'.$row)->getFont()->setBold(true);
                  break;
               case 'KEGIATAN':
                  $sheet->getStyle('A'.$row.':D'.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                  $sheet->getStyle('A'.$row.':D'.$row)->getFill()->getStartColor()->setARGB('ffD9D9D9');
                  $sheet->mergeCells('B'.$row.':C'.$row);
                  $sheet->getStyle('A'.$row.':D'.$row)->getFont()->setBold(true);
                  $list['nominal_setuju']    = $dataRekap[$list['kode_sistem']]['nominal_setuju'];
                  break;
               default:
                  $list['nominal_setuju']    = $list['nominal_setuju'];

                  $columnWidth      = $sheet->getColumnDimension('H')->getWidth();
                  $rowHeight        = ceil(strlen($list['nama'])/50)*14;
                  $sheet->getRowDimension($row)->setRowHeight($rowHeight);
                  break;
            }
            $sheet->setCellValueExplicit('A'.$row, $list['kode'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('B'.$row, $list['nama'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C'.$row, $list['unit_nama'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D'.$row, $list['nominal_setuju'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $row+=1;
         }
         $sheet->getStyle('A10:D'.$row)->getAlignment()->setWrapText(true);
         $sheet->getStyle('A10:D'.$row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
         $sheet->getStyle('A10:A'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
         $sheet->getStyle('A10:D'.($row-1))->applyFromArray($borderTableStyledArray);
         $sheet->getStyle('D10:D'.($row-1))->getNumberFormat()->setFormatCode('#,##0.00_);(#,##0.00)');

         $sheet->setCellValue('A'.($row+1), GTFWConfiguration::GetValue('language', 'resume'));
         $sheet->mergeCells('A'.($row+1).':D'.($row+1));
         $sheet->getStyle('A'.($row+1).':D'.($row+1))->getFont()->setBold(true);
         $sheet->setCellValue('A'.($row+2), $programLabel);
         $sheet->setCellValue('A'.($row+3), $kodeLabel);
         $sheet->setCellValue('B'.($row+3), $namaLabel);
         $sheet->setCellValue('D'.($row+2), $nominalLabel);
         $sheet->mergeCells('A'.($row+2).':C'.($row+2));
         $sheet->mergeCells('B'.($row+3).':C'.($row+3));
         $sheet->mergeCells('D'.($row+2).':D'.($row+3));
         $sheet->getStyle('A'.($row+2).':D'.($row+3))->applyFromArray($styledTableHeaderArray);
         $rows       = $row+4;
         foreach ($dataResume as $res) {
            $sheet->mergeCells('B'.($rows).':C'.($rows));
            $sheet->setCellValueExplicit('A'.($rows), $res['kode'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('B'.($rows), $res['nama'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D'.($rows), $res['nominal_setuju'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $rows+=1;
         }

         $sheet->getStyle('A'.($row+4).':D'.$rows)->getAlignment()->setWrapText(true);
         $sheet->getStyle('A'.($row+4).':D'.$rows)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
         $sheet->getStyle('A'.($row+4).':A'.$rows)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
         $sheet->getStyle('A'.($row+4).':D'.($rows-1))->applyFromArray($borderTableStyledArray);
         $sheet->getStyle('D'.($row+4).':D'.($rows-1))->getNumberFormat()->setFormatCode('#,##0.00_);(#,##0.00)');
      }
      # ===================================== end of document ============================ #
      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>