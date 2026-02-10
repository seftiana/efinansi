<?php
/**
* ================= doc ====================
* FILENAME     : DoCetakTransaksi.xlsx.class.php
* @package     : DoCetakTransaksi
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-28
* @Modified    : 2015-04-28
* @Analysts    : Dyah Fajar N
* @contact     : eko.susilo@gamatechno.com
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/history_transaksi_realisasi/business/AppTransaksi.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'main/function/terbilang.php';

class DoCetakTransaksi extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      $mObj          = new AppTransaksi();
      $mNumber       = new Number();
      $transId       = $mObj->_POST['data_id'];
      $dataList            = $mObj->getTransaksiDetail($transId);
      $dataInvoice         = $mObj->getInvoiceTransaksi($transId);
      $queryString         = $mObj->_getQueryString();
      $requestData         = array();
      $arr_pejabat_pembantu_rektor  = $mObj->GetJabatanNama('PR');
      $arr_pejabat_bendahara        = $mObj->GetJabatanNama('BENDAHARA');
      $terbilang                    = $mNumber->Terbilang($dataList['nominal'], 3).' Rupiah';
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
      $nama             = ($mObj->_POST['penerima'] == '') ? 'Nama Jelas' : strtoupper($mObj->_POST['penerima']);
      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('kwitansi_transaksi.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(true); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle('kwitansi');
      # set font default
      $sheet->getDefaultStyle()->getFont()->setName('Arial')->setSize('11');
      # setting paging
      # options ORIENTATION_DEFAULT : default; ORIENTATION_LANDSCAPE: lanscape; ORIENTATION_PORTRAIT: Potrait
      $sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
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
      $sheet->mergeCells('A6:J6');
      //$sheet->mergeCells('A7:J7');
      $sheet->mergeCells('A8:B8');
      $sheet->mergeCells('A9:B9');
      $sheet->mergeCells('D8:J8');
      $sheet->mergeCells('D9:J9');
      $sheet->mergeCells('A10:B10');
      $sheet->mergeCells('D10:J10');
      $sheet->mergeCells('A11:B11');
      $sheet->mergeCells('D11:J11');
      $sheet->mergeCells('B13:E13');
      $sheet->mergeCells('G20:J20');

      $sheet->getColumnDimension('A')->setWidth(11);
      $sheet->getColumnDimension('B')->setWidth(10);
      $sheet->getColumnDimension('C')->setWidth(2);
      $sheet->getColumnDimension('D')->setWidth(5);
      $sheet->getColumnDimension('E')->setWidth(5);
      $sheet->getColumnDimension('F')->setWidth(5);
      $sheet->getColumnDimension('G')->setWidth(14);
      $sheet->getColumnDimension('H')->setWidth(2);
      $sheet->getColumnDimension('H')->setWidth(2);
      $sheet->getColumnDimension('I')->setWidth(5);
      $sheet->getColumnDimension('J')->setWidth(10);
      
      $sheet->getRowDimension(1)->setRowHeight(20);
      $sheet->getRowDimension(2)->setRowHeight(10);
      $sheet->getRowDimension(3)->setRowHeight(10);
      $sheet->getRowDimension(4)->setRowHeight(10);
      $sheet->getRowDimension(6)->setRowHeight(20); 
      $sheet->getRowDimension(7)->setRowHeight(5);
      $sheet->getRowDimension(12)->setRowHeight(10);
      $sheet->getRowDimension(13)->setRowHeight(25);
      //$sheet->getRowDimension(19)->setRowHeight(20);

      $sheet->setCellValue('A1', $companyName);
      $sheet->setCellValue('A2', $address);
      $sheet->getStyle('A1:B4')->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
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
            'size' => 11
         )
      ));

      $sheet->getStyle('A4:J4')->applyFromArray(array(
         'borders' => array(
            'bottom' => array(
               'style' => PHPExcel_Style_Border::BORDER_THICK
            )
         )
      ));

      $sheet->setCellValue('G5', GTFWConfiguration::GetValue('language', 'no_bukti'));
      $sheet->setCellValue('H5', ':');
      $sheet->getStyle('G5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
      $sheet->getStyle('H5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

      $sheet->setCellValueExplicit('I5', $dataList['nomor_referensi'], PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('A6', 'KWITANSI');
      $sheet->getStyle('A6:J6')->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         ), 'font' => array(
            'bold' => true,
            'size' => 11
         )
      ));
      
      $strSTD = GTFWConfiguration::GetValue('organization', 'company_name');
      $getHeightRowSTD = ceil(strlen($strSTD)/48) *15;     
      $sheet->getRowDimension(8)->setRowHeight($getHeightRowSTD);
      $sheet->setCellValue('A8', 'SUDAH TERIMA DARI');
      $sheet->setCellValue('D8', $strSTD);
      $sheet->setCellValue('C8', ':');

      $strSTD = $mObj->_POST['penyetor'];
      $getHeightRowSTD = ceil(strlen($strSTD)/48) *15;     
      $sheet->getRowDimension(9)->setRowHeight($getHeightRowSTD);
      $sheet->setCellValue('A9', 'Untuk Unit Kerja');
      $sheet->setCellValue('D9', $strSTD);
      $sheet->setCellValue('C9', ':');
      
      $strBU = $terbilang;
      $getHeightRowBU = ceil(strlen($strBU)/48) *15;     
      $sheet->getRowDimension(10)->setRowHeight($getHeightRowBU);
      $sheet->setCellValue('A10', 'Banyaknya Uang');
      $sheet->setCellValue('C10', ':');
      $sheet->setCellValue('D10', $strBU);
      
      $sheet->getRowDimension(11)->setRowHeight(24);      
      $sheet->setCellValue('A11', 'Untuk Pembayaran');
      $sheet->setCellValue('C11', ':');      
      $sheet->getStyle('D8:J11')->getAlignment()->setWrapText(true);
      
      $getHeightRow = ceil(strlen($dataList['keterangan'] == '' ? $dataList['nama'] : $dataList['keterangan'])/48) *15;     
      $sheet->getRowDimension(11)->setRowHeight($getHeightRow);

      $sheet->setCellValue('D11', ($dataList['keterangan'] == '') ? $dataList['nama'] : $dataList['keterangan']);

      $sheet->getStyle('A8:D11')->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         )
      ));

      $sheet->setCellValue('A13', 'JUMLAH RP');
      $sheet->setCellValueExplicit('B13', $dataList['nominal'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
      $sheet->getStyle('B13')->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');

      $sheet->getStyle('A13:E13')->applyFromArray(array(
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

      $sheet->getStyle('B13:E13')->applyFromArray(array(
         'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array(
               'argb' => 'ffcccccc'
            )
         )
      ));

      //$sheet->getRowDimension(16)->setRowHeight(25);   
      $sheet->mergeCells('G13:J13');  
      $sheet->mergeCells('G14:J14');  
      $sheet->mergeCells('G15:J15'); 
      $sheet->mergeCells('G16:J16');
      $sheet->mergeCells('G17:J17');
      // $sheet->mergeCells('G18:J18');
      // $sheet->mergeCells('G19:J19');
      $sheet->setCellValue('G13', GTFWConfiguration::GetValue('organization', 'city').', '.$tanggal_cetak);
      #$sheet->setCellValue('G17', $companyName);
      
      //$sheet->getRowDimension(20)->setRowHeight(30);   
      $sheet->setCellValue('G17', '( '.$nama.' )');
      $sheet->getStyle('G13')->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_BOTTOM
         )
      ));
      $sheet->getStyle('G14:J17')->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         )
      ));
      $sheet->getStyle('G17:J17')->applyFromArray(array(
         'borders' => array(
            'top' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN
            ),
            'bottom' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN
            ),
            'left' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN
            ),
            'right' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN
            )
         )
      ));

      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>