<?php
/**
* ================= doc ====================
* FILENAME     : ViewExcelUnitKerja.xlsx.class.php
* @package     : ViewExcelUnitKerja
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-09-22
* @Modified    : 2014-09-22
* @Analysts    : Dyah Fajar N
* @contact     : eko.susilo@gamatechno.com
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/lap_rekap_anggaran_unitkerja/business/RekapUnitKerja.class.php';

class ViewExcelUnitKerja extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('laporan_rekapitulasi_anggaran_program_dan_kegiatan_unit_'.date('Y-m-d').'.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(true); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle('rekapitulasi_anggaran_unit');
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

      $mObj             = new RekapUnitKerja();
      $requestData      = array();
      $arrPeriodeTahun  = $mObj->GetPeriodeTahun();
      $requestData['ta_id']            = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
      $requestData['jenis_kegiatan']   = Dispatcher::Instance()->Decrypt($mObj->_GET['jenis_kegiatan']);
      $requestData['unit_id']          = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
      $requestData['unit_nama']        = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);
      $requestData['program_id']       = Dispatcher::Instance()->Decrypt($mObj->_GET['program_id']);
      $requestData['program_nama']     = Dispatcher::Instance()->Decrypt($mObj->_GET['program_nama']);
      $unitData                        = $mObj->GetUnitIdentity($requestData['unit_id']);
      foreach ($arrPeriodeTahun as $ta) {
         if((int)$ta['id'] === (int)$requestData['ta_id']){
            $requestData['ta_nama']       = $ta['name'];
         }
      }

      $dataList         = $mObj->ChangeKeyName($mObj->GetData(0, 100000, (array)$requestData));
      $total_data       = $mObj->ChangeKeyName($mObj->GetCountData());
      $dataResume       = $mObj->ChangeKeyName($mObj->GetDataResume((array)$requestData));

      if(empty($dataList)){
         $sheet->setCellValue('A1', 'Data Kosong');
      }else{
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

         $headerTitle      = GTFWConfiguration::GetValue('language', 'laporan_rekapitulasi_anggaran_program_dan_kegiatan_unit');
         $taLabel          = GTFWConfiguration::GetValue('language', 'tahun_periode');
         $unitLabel        = GTFWConfiguration::GetValue('language', 'unit').'/'.GTFWConfiguration::GetValue('language', 'sub_unit');
         $programLabel     = GTFWConfiguration::GetValue('language', 'program');
         $kegiatanLabel    = GTFWConfiguration::GetValue('language', 'kegiatan');
         $subkegLabel      = GTFWConfiguration::GetValue('language', 'sub_kegiatan');
         $jenisKegLabel    = GTFWConfiguration::GetValue('language', 'jenis_kegiatan');
         $nominalLabel     = GTFWConfiguration::GetValue('language', 'nominal_setuju_rp');
         $kodeLabel        = GTFWConfiguration::GetValue('language', 'kode');
         $namaLabel        = GTFWConfiguration::GetValue('language', 'nama');
         // inisialisasi data
         $unit          = '';
         $program       = '';
         $kegiatan      = '';
         $subkegiatan   = '';
         $index         = 0;
         $dataGrid      = array();
         $nomor         = 0;
         $dataRekap     = array();
         for ($i=0; $i < count($dataList);) {
            if((int)$unit === (int)$dataList[$i]['unit_id'] && (int)$program === (int)$dataList[$i]['program_id'] && (int)$kegiatan === (int)$dataList[$i]['kegiatan_id']){
               $programKodeSistem         = $unit.'.'.$program;
               $kegiatanKodeSistem        = $unit.'.'.$program.'.'.$kegiatan;
               $dataRekap[$programKodeSistem]['nominal_setuju']   += $dataList[$i]['nominal_setuju'];
               $dataRekap[$kegiatanKodeSistem]['nominal_setuju']  += $dataList[$i]['nominal_setuju'];
               $dataGrid[$index]['id']    = $dataList[$i]['sub_kegiatan_id'];
               $dataGrid[$index]['kode']  = $dataList[$i]['sub_kegiatan_kode'];
               $dataGrid[$index]['nama']  = $dataList[$i]['sub_kegiatan_nama'];
               $dataGrid[$index]['jenis'] = 'sub_kegiatan';
               $dataGrid[$index]['nominal_setuju'] = $dataList[$i]['nominal_setuju'];
               $i++;
               $nomor++;
            }elseif((int)$unit === (int)$dataList[$i]['unit_id'] && (int)$program === (int)$dataList[$i]['program_id'] && (int)$kegiatan !== (int)$dataList[$i]['kegiatan_id']){
               unset($nomor);
               $nomor                     = 1;
               $kegiatan                  = (int)$dataList[$i]['kegiatan_id'];
               $kodeSistem                = $unit.'.'.$program.'.'.$kegiatan;
               $dataRekap[$kodeSistem]['nominal_setuju']    = 0;
               $dataGrid[$index]['id']    = $dataList[$i]['kegiatan_id'];
               $dataGrid[$index]['kode']  = $dataList[$i]['kegiatan_kode'];
               $dataGrid[$index]['nama']  = $dataList[$i]['kegiatan_nama'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['unit_nama']   = '';
               $dataGrid[$index]['jenis'] = 'kegiatan';
            }elseif((int)$unit === (int)$dataList[$i]['unit_id'] && (int)$program !== (int)$dataList[$i]['program_id']){
               $program       = (int)$dataList[$i]['program_id'];
               // continue($index);
            }else{
               $unit          = (int)$dataList[$i]['unit_id'];
               $kodeSistem    = $unit.'.'.(int)$dataList[$i]['program_id'];
               $dataRekap[$kodeSistem]['nominal_setuju']    = 0;
               $dataGrid[$index]['id']    = $dataList[$i]['program_id'];
               $dataGrid[$index]['kode']  = $dataList[$i]['program_kode'];
               $dataGrid[$index]['nama']  = $dataList[$i]['program_nama'];
               $dataGrid[$index]['unit_nama']   = $dataList[$i]['unit_nama'];
               $dataGrid[$index]['unit_id']     = $dataList[$i]['unit_id'];
               $dataGrid[$index]['unit_kode']   = $dataList[$i]['unit_kode'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['jenis']       = 'program';
            }
            $index++;
         }

         // merge cells
         $sheet->mergeCells('A1:D1');
         $sheet->mergeCells('B3:D3');
         $sheet->mergeCells('B4:D4');
         $sheet->mergeCells('A6:A7');
         $sheet->mergeCells('B6:C6');
         $sheet->mergeCells('D6:D7');

         $sheet->getColumnDimension('A')->setWidth(45);
         $sheet->getColumnDimension('B')->setWidth(20);
         $sheet->getColumnDimension('C')->setWidth(60);
         $sheet->getColumnDimension('D')->setWidth(25);
         $sheet->setCellValue('A1', $headerTitle);
         $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);
         $sheet->setCellValue('A3', $taLabel);
         $sheet->setCellValue('A4', $unitLabel);

         $sheet->setCellValueExplicit('B3', $requestData['ta_nama'], PHPExcel_Cell_DataType::TYPE_STRING);
         $sheet->setCellValueExplicit('B4', $requestData['unit_nama'], PHPExcel_Cell_DataType::TYPE_STRING);

         $sheet->setCellValue('A6', $unitLabel);
         $sheet->setCellValue('B6', $programLabel.'/'.$kegiatanLabel.'/'.$subkegLabel);
         $sheet->setCellValue('B7', $kodeLabel);
         $sheet->setCellValue('C7', $namaLabel);
         $sheet->setCellValue('D6', $nominalLabel);
         $sheet->getStyle('A6:D7')->applyFromArray($styledTableHeaderArray);

         $row        = 8;
         foreach ($dataGrid as $list) {
            switch (strtoupper($list['jenis'])) {
               case 'PROGRAM':
                  $sheet->getStyle('A'.$row.':D'.$row)->getFill()->getStartColor()->setARGB('ffCCCCCC');
                  $sheet->getStyle('A'.$row.':D'.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                  $sheet->getStyle('A'.$row.':D'.$row)->getFont()->setBold(true);
                  $list['nominal_setuju']    = $dataRekap[$list['kode_sistem']]['nominal_setuju'];
                  break;
               case 'KEGIATAN':
                  $sheet->getStyle('A'.$row.':D'.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                  $sheet->getStyle('A'.$row.':D'.$row)->getFill()->getStartColor()->setARGB('ffD9D9D9');
                  $list['nominal_setuju']    = $dataRekap[$list['kode_sistem']]['nominal_setuju'];
                  break;
               case 'SUB_KEGIATAN':
                  $list['nominal_setuju']    = $list['nominal_setuju'];
                  break;
               default:
                  $list['nominal_setuju']    = $list['nominal_setuju'];
                  break;
            }

            $columnWidth      = $sheet->getColumnDimension('C')->getWidth();
            $rowHeight        = ceil(strlen($list['nama'])/55)*14;
            $sheet->getRowDimension($row)->setRowHeight($rowHeight);

            $sheet->setCellValue('A'.$row, $list['unit_nama']);
            $sheet->setCellValueExplicit('B'.$row, $list['kode'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('C'.$row, $list['nama']);
            $sheet->setCellValueExplicit('D'.$row, $list['nominal_setuju'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $row++;
         }

         $sheet->getStyle('A8:D'.$row)->getAlignment()->setWrapText(true);
         $sheet->getStyle('A8:D'.$row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
         $sheet->getStyle('B8:B'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
         $sheet->getStyle('A8:D'.($row-1))->applyFromArray($borderTableStyledArray);
         $sheet->getStyle('D8:D'.($row-1))->getNumberFormat()->setFormatCode('#,##0.00_);(#,##0.00)');

         // DATA RESUME
         $sheet->setCellValue('A'.($row+1), GTFWConfiguration::GetValue('language', 'resume'));
         $sheet->mergeCells('A'.($row+1).':D'.($row+1));
         $sheet->getStyle('A'.($row+1).':D'.($row+1))->getFont()->setBold(true);

         $sheet->setCellValue('A'.($row+2), $unitLabel);
         $sheet->mergeCells('A'.($row+2).':C'.($row+2));
         $sheet->setCellValue('D'.($row+2), $nominalLabel);
         $sheet->getStyle('A'.($row+2).':D'.($row+2))->applyFromArray($styledTableHeaderArray);
         $rows       = $row+3;
         foreach ($dataResume as $resume) {
            $sheet->mergeCells('A'.$rows.':C'.$rows);
            $sheet->setCellValue('A'.$rows, $resume['nama']);
            $sheet->setCellValueExplicit('D'.($rows), $list['nominal_setuju'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $rows++;
         }

         $sheet->getStyle('A'.($row+3).':D'.($rows-1))->applyFromArray($borderTableStyledArray);
         $sheet->getStyle('D'.($row+3).':D'.($rows-1))->getNumberFormat()->setFormatCode('#,##0.00_);(#,##0.00)');
      }
      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>