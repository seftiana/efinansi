<?php
/**
* ================= doc ====================
* FILENAME     : ViewLaporanHarianKas.xlsx.class.php
* @package     : ViewLaporanHarianKas
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-05-26
* @Modified    : 2015-05-26
* @Analysts    : Dyah Fajar N
* @contact     : eko.susilo@gamatechno.com
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/history_apbnp/business/HistoryApbnp.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'main/function/date.php';


class ViewExportHistoryApbnp extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      $get_date      = getdate();
      $curr_mon      = (int)$get_date['mon'];
      $curr_day      = (int)$get_date['mday'];
      $curr_year     = (int)$get_date['year'];
      $tgl_proses    = gmmktime(0,0,0, $curr_mon, $curr_day, $curr_year);
      $tanggalCetak  =  IndonesianDate(date('Y-m-d', time()),'YYYY-MM-DD');

      $mObj          = new HistoryApbnp();

      $request_data['kode']               = Dispatcher::Instance()->Decrypt($mObj->_GET['kode']);
      $request_data['unit_id_asal']       = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id_asal']);
      $request_data['unit_id_tujuan']     = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id_tujuan']);
      $request_data['bulan_asal']         = Dispatcher::Instance()->Decrypt($mObj->_GET['bulan_asal']);
      $request_data['bulan_tujuan']       = Dispatcher::Instance()->Decrypt($mObj->_GET['bulan_tujuan']);
      $request_data['ta_id']              = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
      $request_data['type']               = Dispatcher::Instance()->Decrypt($mObj->_GET['type']);

      // getData
      $data_list     = $mObj->getDataMovementExport($request_data);

      // $saldoAwal     = $mObj->getSaldoAwal();
      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('history_revisi_intern.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(false); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle('history_revisi_intern');
      # set font default
      $sheet->getDefaultStyle()->getFont()->setName('Arial')->setSize('9');
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

      $sheet->getRowDimension(1)->setRowHeight(20);

      $sheet->getColumnDimension('A')->setWidth(5);
      $sheet->getColumnDimension('B')->setWidth(15);
      $sheet->getColumnDimension('C')->setWidth(40);
      $sheet->getColumnDimension('D')->setWidth(8);
      $sheet->getColumnDimension('E')->setWidth(25);
      $sheet->getColumnDimension('F')->setWidth(10);
      $sheet->getColumnDimension('G')->setWidth(20);
      $sheet->getColumnDimension('H')->setWidth(40);
      $sheet->getColumnDimension('I')->setWidth(8);
      $sheet->getColumnDimension('J')->setWidth(25);
      $sheet->getColumnDimension('K')->setWidth(10);
      $sheet->getColumnDimension('L')->setWidth(20);

      $sheet->mergeCells('A1:L1');

      $sheet->setCellValue('A1', 'HISTORY REVISI INTERN');

      $sheet->getStyle('A1')->applyFromArray(array(
         'font' => array(
            'size' => 12,
            'bold' => true
         ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         )
      ));

      $sheet->mergeCells('A3:A4');
      $sheet->mergeCells('B3:B4');
      $sheet->mergeCells('C3:G3');
      $sheet->mergeCells('H3:L3');
      $sheet->setCellValue('A3', GTFWConfiguration::GetValue('language', 'no'));
      $sheet->setCellValue('B3', 'Tanggal Revisi');
      $sheet->setCellValue('C3', 'Kegiatan Asal');
      $sheet->setCellValue('C4', 'Unit Kerja');
      $sheet->setCellValue('D4', 'Kode');
      $sheet->setCellValue('E4', 'Nama');
      $sheet->setCellValue('F4', 'Bulan');
      $sheet->setCellValue('G4', 'Nominal Sekarang');
      $sheet->setCellValue('H3', 'Kegiatan Tujuan');
      $sheet->setCellValue('H4', 'Unit Kerja');
      $sheet->setCellValue('I4', 'Kode');
      $sheet->setCellValue('J4', 'Nama');
      $sheet->setCellValue('K4', 'Bulan');
      $sheet->setCellValue('L4', 'Nominal Sekarang');
      $sheet->getStyle('A3:L4')->applyFromArray($styledTableHeaderArray);

      $row     = 5;
      if(empty($data_list)){
         $sheet->setCellValue('A'.$row, GTFWConfiguration::GetValue('language', 'data_kosong'));
         $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $sheet->getStyle('A'.$row.':L'.$row)->applyFromArray($borderTableStyledArray);
         $sheet->mergeCells('A'.$row.':L'.$row);
      }else{

         $start      = 1;

         for($i=0; $i < count($data_list); $i++)
         {

            $data[$i]['tanggal_sort']      = date_format(date_create($data_list[$i]['tanggal']), 'Y-m-d');
            $data[$i]['tanggal_indo']      = $mObj->_dateToIndo($data[$i]['tanggal_sort']);

            $data[$i]['nama_bulan_asal']    = $mObj->indonesianMonth[($data_list[$i]['bulan_asal'] -1 )]['name'];
            $data[$i]['nama_bulan_tujuan']  = $mObj->indonesianMonth[($data_list[$i]['bulan_tujuan'] -1)]['name'];

            $data[$i]['nomor']          = $start;

            $sheet->setCellValueExplicit('A'.$row, $data[$i]['nomor'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('B'.$row, $data[$i]['tanggal_indo'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C'.$row, $data_list[$i]['unit_kerja_nama_asal'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D'.$row, $data_list[$i]['nomor_kegiatan_asal'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('E'.$row, $data_list[$i]['kegiatan_asal'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('F'.$row, $data[$i]['nama_bulan_asal'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('G'.$row, $data_list[$i]['nilai_sekarang_asal'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getStyle('G'.$row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $sheet->setCellValueExplicit('H'.$row, $data_list[$i]['unit_kerja_nama_tujuan'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('I'.$row, $data_list[$i]['nomor_kegiatan_tujuan'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('J'.$row, $data_list[$i]['kegiatan_tujuan'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('K'.$row, $data[$i]['nama_bulan_tujuan'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('L'.$row, $data_list[$i]['nilai_sekarang_tujuan'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getStyle('L'.$row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

            $getHeightRow = ceil(strlen($data_list[$i]['unit_kerja_nama_asal'])/40) * 14;
            $sheet->getRowDimension($row)->setRowHeight($getHeightRow);

            $row++;

            $start++;
         }

         $sheet->getStyle('A4:L'.($row-1))->applyFromArray($borderTableStyledArray);

         $sheet->getStyle('A4:A'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $sheet->getStyle('B4:B'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $sheet->getStyle('D4:D'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $sheet->getStyle('F4:F'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $sheet->getStyle('I4:I'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $sheet->getStyle('K4:K'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
      }
      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>