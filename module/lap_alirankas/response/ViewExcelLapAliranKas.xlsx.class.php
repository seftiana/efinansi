<?php
/**
* ================= doc ====================
* FILENAME     : ViewExcelLapAliranKas.xlsx.class.php
* @package     : ViewExcelLapAliranKas
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
'module/lap_alirankas/business/AppLapAliranKas.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'main/function/date.php';
class ViewExcelLapAliranKas extends XlsxResponse
{
   # Internal Variables
   public $Excel;
   protected $mObj;

   function ProcessRequest()
   {
      $this->mObj = new AppLapAliranKas();
      $this->mObj->Setup();

      $get =  is_object($_GET) ? $_GET->AsArray() : $_GET;
      $tglAwal =  Dispatcher::Instance()->Decrypt($get['tanggal_awal']);
      $tglAkhir =  Dispatcher::Instance()->Decrypt($get['tanggal_akhir']);
      $subAccount = Dispatcher::Instance()->Decrypt($get['sub_account']);

      $this->mObj->LaporanBuilder()->PrepareData($tglAwal, $tglAkhir,$subAccount);
      $aliranKas = $this->mObj->LaporanBuilder()->laporanView();

      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('laporan_aliran_kas_'.date('Ymd', time()).'.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(false); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle('aliran khas');
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

      if (empty($aliranKas)) {
         $sheet->setCellValue('A1', GTFWConfiguration::GetValue('language', 'data_kosong'));
      } else {
         $sheet->getColumnDimension('A')->setWidth(50);
         $sheet->getColumnDimension('B')->setWidth(18);

         $sheet->getRowDimension(1)->setRowHeight(18);
         $sheet->setCellValue('A1', 'Laporan Aliran Kas');
         $sheet->mergeCells('A1:B1');
         $sheet->setCellValueByColumnAndRow(0, 3, 'Untuk Interval Waktu '.IndonesianDate($tglAwal, 'yyyy-mm-dd').' s/d '.IndonesianDate($tglAkhir, 'yyyy-mm-dd'));
         $sheet->mergeCells('A3:B3');
         $sheet->getStyle('A1:B3')->applyFromArray(array(
            'font' => array(
               'bold' => true
            ), 'alignment' => array(
               'shrinkToFit' => true,
               'wrap' => true,
               'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
               'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
         ));

         $row = 6;
         $space = " ";
         foreach ($aliranKas as $itemLaporan) {
            $itemLaporan['nama'] = str_repeat($space,$itemLaporan['level']-1).$itemLaporan['nama'];
            if ($itemLaporan['is_summary'] == 'Y') {
               $pengali = 1;
               $jumlahSaldoKlp = $itemLaporan['saldo_summary'] * $pengali;
               $sheet->getStyle('A'.$row.':B'.$row)->applyFromArray(array(
                  'font' => array(
                     'bold' => true
                  ), 'borders' => array(
                     'top' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                     ),'bottom' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THICK
                     )
                  )
               ));
               $sheet->getStyle('B'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
            } else {
               $pengali = ($itemLaporan['is_tambah'] == 'T') ? -1 : 1;
               $jumlahSaldoKlp = $itemLaporan['saldo'] * $pengali;
   
               if ($itemLaporan['is_child'] == '0') {
                  $sheet->mergeCells('A'.$row.':B'.$row);
                  switch ($itemLaporan['level']) {
                     case '2':
                        $title = strtoupper($itemLaporan['nama']);
                        $sheet->getStyle('A'.$row.':B'.$row)->applyFromArray(array(
                           'font' => array(
                              'bold' => true
                           ), 'borders' => array(
                              'bottom' => array(
                                 'style' => PHPExcel_Style_Border::BORDER_DOUBLE
                              ), 'top' => array(
                                 'style' => PHPExcel_Style_Border::BORDER_THICK
                              )
                           )
                        ));
                        
                        break;
                     default : 
                        $title = str_repeat($space,$itemLaporan['level']-2).$itemLaporan['nama'];
                        $sheet->getStyle('A'.$row.':B'.$row)->applyFromArray(array(
                           'font' => array(
                              'bold' => true
                           ), 'borders' => array(
                              'bottom' => array(
                                 'style' => PHPExcel_Style_Border::BORDER_THICK
                              )
                           )
                        ));
                        break;
                  }

                  $itemLaporan['nama'] = $title;
               } else {                  
                  $itemLaporan['nama'] = str_repeat($space,$itemLaporan['level']-1).$itemLaporan['nama'];
                  $sheet->getStyle('B'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
               }
            }
            $sheet->setCellValueByColumnAndRow(0, $row, $itemLaporan['nama']);
            $sheet->setCellValueByColumnAndRow(1, $row, $jumlahSaldoKlp);
            $row++;
         }
      }
      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>