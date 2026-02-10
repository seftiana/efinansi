<?php
/**
* ================= doc ====================
* FILENAME     : ViewExcelLapTransaksi.xlsx.class.php
* @package     : ViewExcelLapTransaksi
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-05-25
* @Modified    : 2015-05-25
* @Analysts    : Dyah Fajar N
* @contact     : eko.susilo@gamatechno.com
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/lap_transaksi/business/AppLapTransaksi.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'main/function/date.php';

class ViewExcelLapTransaksi extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      $Obj           = new AppLapTransaksi();
      $tgl_awal      = Dispatcher::Instance()->Decrypt($_GET['tgl_awal']);
      $tgl_akhir     = Dispatcher::Instance()->Decrypt($_GET['tgl_akhir']);
      $key           = Dispatcher::Instance()->Decrypt($_GET['key']);
      $tipeTransaksi = Dispatcher::Instance()->Decrypt($_GET['tipe_transaksi']);
      $result        = $Obj->GetDataCetak($tgl_awal,$tgl_akhir,$key,$tipeTransaksi);
      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('laporan_transaksi.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(false); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle('Laporan Harian');
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

      if (empty($result)) {
         $sheet->setCellValue('A1', GTFWConfiguration::GetValue('language', 'data_kosong'));
      } else {
         #set header
         $sheet->getColumnDimension('A')->setWidth(6);
         $sheet->getColumnDimension('B')->setWidth(16);
         $sheet->getColumnDimension('C')->setWidth(28);
         $sheet->getColumnDimension('D')->setWidth(30);
         $sheet->getColumnDimension('E')->setWidth(18);
         $sheet->getColumnDimension('F')->setWidth(30);
         $sheet->setCellValue('A1', 'Laporan Transaksi');
         $sheet->setCellValue('A3', 'Interval waktu '.IndonesianDate($tgl_awal, 'yyyy-mm-dd').' s/d '.IndonesianDate($tgl_akhir, 'yyyy-mm-dd'));

         $sheet->mergeCells('A1:F1');
         $sheet->mergeCells('A3:F3');
         $sheet->getStyle('A1:F3')->getFont()->setBold(true);

         $sheet->setCellValue('A5', 'No.');
         $sheet->setCellValue('B5', 'Tanggal');
         $sheet->setCellValue('C5', 'Referensi');
         $sheet->setCellValue('D5', 'Catatan');
         $sheet->setCellValue('E5', 'Nilai (Rp.)');
         $sheet->setCellValue('F5', 'Tipe');
         $sheet->getStyle('A5:F5')->applyFromArray(array(
            'alignment' => array(
               'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
               'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ), 'font' => array(
               'bold' => true
            )
         ));
         $num        = 6;
         for($i=0; $i<count($result); $i++) {
            if(strtoupper($result[$i]['transaksi_is_jurnal']) == 'Y') {
               $format        = $fColCtnitalic;
               $format_nilai  = $fColNilaitalic;
            } else {
               $format        = $fColCtn;
               $format_nilai  = $fColNilai;
            }
            $number = $i+1;
            $trans_tgl_day    = date('d', strtotime($result[$i]['transaksi_tanggal']));
            $trans_tgl_mon    = date('m', strtotime($result[$i]['transaksi_tanggal']));
            $trans_tgl_year   = date('Y', strtotime($result[$i]['transaksi_tanggal']));
            $time             = gmmktime(0,0,0,(int)$trans_tgl_mon,(int)$trans_tgl_day,(int)$trans_tgl_year);
            $sheet->setCellValueByColumnAndRow(0, $num, $number);
            $sheet->setCellValueByColumnAndRow(1, $num, PHPExcel_Shared_Date::PHPToExcel($time));
            $sheet->getStyle('B'.$num)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDDSLASH);

            $sheet->getCellByColumnAndRow(2, $num)->setValueExplicit($result[$i]['transaksi_referensi'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueByColumnAndRow(3, $num, $result[$i]['transaksi_catatan']);
            $sheet->getCellByColumnAndRow(4, $num)->setValueExplicit($result[$i]['transaksi_nilai'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueByColumnAndRow(5, $num, $result[$i]['transaksi_tipe']);
            $num++;
         }
         $sheet->setCellValueByColumnAndRow(0, $num, 'Total');
         $sheet->getCellByColumnAndRow(4, $num)->setValueExplicit('=SUM(E6:E'.($num-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->setCellValueByColumnAndRow(5, $num, '');
         $sheet->mergeCells('A'.$num.':D'.$num);
         $sheet->getStyle('A5:F'.$num)->applyFromArray(array(
            'borders' => array(
               'allborders' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN
               )
            )
         ));
         $sheet->getStyle('E6:E'.$num)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
      }
      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>