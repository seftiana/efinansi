<?php
/**
* ================= doc ====================
* FILENAME     : DoCetakTransaksi.xlsx.class.php
* @package     : DoCetakTransaksi
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-27
* @Modified    : 2015-04-27
* @Analysts    : Dyah Fajar N
* @contact     : eko.susilo@gamatechno.com
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_transaksi_spj_history/business/HistoryTransaksiSpj.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'main/function/terbilang.php';

class DoCetakTransaksi extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      $mObj          = new HistoryTransaksiSpj();
      $mNumber       = new Number();
      $transId       = $mObj->_POST['data_id'];
      $dataList            = $mObj->getTransaksiDetail($transId);
      $dataInvoice         = $mObj->getInvoiceTransaksi($transId);
      $queryString         = $mObj->_getQueryString();
      $requestData         = array();
      $arr_pejabat_pembantu_rektor  = $mObj->GetJabatanNama('PR');
      $arr_pejabat_bendahara        = $mObj->GetJabatanNama('BENDAHARA');
      $terbilang           = $mNumber->Terbilang($dataList['nominal'], 3).' Rupiah';
      $requestData['tanggal']    = date('Y-m-d', mktime(0,0,0, $currMon, $currDay, $currYear));
      $requestData['penyetor']   = 'Pengguna Anggaran '.GTFWConfiguration::GetValue('organization', 'company_name');
      $tanggal_day      = (int)$mObj->_POST['tanggal_day'];
      $tanggal_mon      = (int)$mObj->_POST['tanggal_mon'];
      $tanggal_year     = (int)$mObj->_POST['tanggal_year'];
      $tanggal          = date('Y-m-d', mktime(0,0,0, $tanggal_mon, $tanggal_day, $tanggal_year));
      $tanggal_cetak    = $mObj->indonesianDate($tanggal);
      $objDrawing       = new PHPExcel_Worksheet_Drawing();
      // configuration
      $companyName      = GTFWConfiguration::GetValue('organization', 'company_name');
      $address          = GTFWConfiguration::GetValue('organization', 'company_address');
      $nama             = ($mObj->_POST['pembuat_kwitansi'] == '') ? 'Nama Jelas' : strtoupper($mObj->_POST['pembuat_kwitansi']);
      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('transaksi_spj.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(false); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle('Bukti Setor');
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

      $objDrawing->setName('Logo');
      $objDrawing->setDescription('Logo');
      $objDrawing->setPath(GTFWConfiguration::GetValue('application', 'docroot').'/images/logo.png');
      $objDrawing->setHeight(48);
      $objDrawing->setWorksheet($sheet);
      $objDrawing->setCoordinates('I1');
      $objDrawing->setOffsetX(30);
      $objDrawing->setOffsetY(5);

      $sheet->mergeCells('A1:B1');
      $sheet->mergeCells('A2:B4');
      $sheet->mergeCells('C1:H4');
      $sheet->mergeCells('I1:J4');
      $sheet->mergeCells('I6:J6');
      $sheet->mergeCells('A7:J7');
      $sheet->mergeCells('A9:B9');
      $sheet->mergeCells('D9:J9');
      $sheet->mergeCells('A10:B10');
      $sheet->mergeCells('D10:J10');
      $sheet->mergeCells('A11:B11');
      $sheet->mergeCells('D11:J11');
      $sheet->mergeCells('B14:E14');

      $sheet->getColumnDimension('A')->setWidth(16);
      $sheet->getColumnDimension('B')->setWidth(10);
      $sheet->getColumnDimension('C')->setWidth(2);
      $sheet->getColumnDimension('G')->setWidth(14);
      $sheet->getColumnDimension('H')->setWidth(2);
      $sheet->getColumnDimension('J')->setWidth(16);
      $sheet->getRowDimension(7)->setRowHeight(24);
      $sheet->getRowDimension(14)->setRowHeight(30);

      $sheet->setCellValue('A1', $companyName);
      $sheet->setCellValue('A2', $address);
      $sheet->getStyle('A1:B4')->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP,
            'wrap' => true
         )
      ));
      $sheet->setCellValue('C1', $companyName);
      $sheet->getStyle('C1:H4')->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            'wrap' => true
         ), 'font' => array(
            'bold' => true,
            'size' => 16
         )
      ));

      $sheet->getStyle('A4:J4')->applyFromArray(array(
         'borders' => array(
            'bottom' => array(
               'style' => PHPExcel_Style_Border::BORDER_THICK
            )
         )
      ));

      $sheet->setCellValue('G6', GTFWConfiguration::GetValue('language', 'no_bukti'));
      $sheet->setCellValue('H6', ':');
      $sheet->getStyle('H6')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

      $sheet->setCellValueExplicit('I6', $dataList['nomor_referensi'], PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('A7', 'KWITANSI');
      $sheet->getStyle('A7:J7')->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         ), 'font' => array(
            'bold' => true,
            'size' => 13
         )
      ));
      $sheet->setCellValue('A9', 'SUDAH TERIMA DARI');
      $sheet->setCellValue('D9', $mObj->_POST['penyetor']);
      $sheet->setCellValue('C9', ':');
      $sheet->setCellValue('A10', 'Banyaknya Uang');
      $sheet->setCellValue('C10', ':');
      $sheet->setCellValue('D10', $terbilang);
      $sheet->setCellValue('A11', 'Untuk Pembayaran');
      $sheet->setCellValue('C11', ':');
      $sheet->setCellValue('D11', ($dataList['keterangan'] == '') ? $dataList['nama'] : $dataList['keterangan']);

      $sheet->getStyle('C9:C11')->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
         )
      ));

      $sheet->setCellValue('A14', 'JUMLAH RP');
      $sheet->setCellValueExplicit('B14', $dataList['nominal'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
      $sheet->getStyle('B14')->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');

      $sheet->getStyle('A14:E14')->applyFromArray(array(
         'borders' => array(
            'top' => array(
               'style' => PHPExcel_Style_Border::BORDER_THICK
            ), 'bottom' => array(
               'style' => PHPExcel_Style_Border::BORDER_THICK
            )
         ), 'alignment' => array(
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         )
      ));

      $sheet->getStyle('B14:E14')->applyFromArray(array(
         'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array(
               'argb' => 'ffcccccc'
            )
         )
      ));

      $sheet->mergeCells('G13:J13');
      $sheet->mergeCells('G14:J14');
      $sheet->mergeCells('G15:J15');
      $sheet->setCellValue('G13', GTFWConfiguration::GetValue('organization', 'city').', '.$tanggal_cetak);
      $sheet->setCellValue('G14', $companyName);
      $sheet->setCellValue('G15', '( '.$nama.' )');
      $sheet->getStyle('G14:J15')->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
         )
      ));
      $sheet->getStyle('G15:J15')->applyFromArray(array(
         'borders' => array(
            'top' => array(
               'style' => PHPExcel_Style_Border::BORDER_HAIR
            )
         )
      ));
      # Save Excel document to local hard disk
      $this->Save();
      exit();
   }
}
?>