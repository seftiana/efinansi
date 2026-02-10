<?php
/**
* ================= doc ====================
* FILENAME     : DoCetakBkm.xlsx.class.php
* @package     : DoCetakBkm
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
'module/history_transaksi_realisasi_penerimaan/business/AppTransaksi.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'main/function/terbilang.php';

class DoCetakBkm extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      $mObj          = new AppTransaksi();
      $mNumber       = new Number();
      $transId       = $mObj->_POST['dataId'];
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
      // $tanggal          = date('Y-m-d', mktime(0,0,0, $tanggal_mon, $tanggal_day, $tanggal_year));
      $tanggal          = gmmktime(0,0,0, $tanggal_mon, $tanggal_day, $tanggal_year);
      $tanggal_cetak    = $mObj->indonesianDate($tanggal);
      $objDrawing       = new PHPExcel_Worksheet_Drawing();
      // configuration
      $companyName      = GTFWConfiguration::GetValue('organization', 'company_name');
      $address          = GTFWConfiguration::GetValue('organization', 'company_address');
      $nama             = ($mObj->_POST['pembuat_kwitansi'] == '') ? 'Nama Jelas' : strtoupper($mObj->_POST['pembuat_kwitansi']);
      $tanggalCetak  = $mObj->indonesianDate(date('Y-m-d', time()));
      $kota          = GTFWConfiguration::GetValue('organization', 'city');

      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('bkm.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(false); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle('bkm');
      # set font default
      $sheet->getDefaultStyle()->getFont()->setName('Courier New')->setSize('11');
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

      $sheet->getRowDimension(9)->setRowheight(18);
      $sheet->getRowDimension(4)->setRowheight(18);
      $sheet->getColumnDimension('A')->setWidth(18);
      $sheet->getColumnDimension('B')->setWidth(2);
      $sheet->mergeCells('H1:I1');
      $sheet->mergeCells('H2:I2');
      $sheet->mergeCells('A4:I4');
      $sheet->mergeCells('C6:I6');
      $sheet->mergeCells('C7:I7');

      $sheet->setCellValue('G1', 'BPKB');
      $sheet->getStyle('H1')->getNumberFormat()->setFormatCode(': _(@_)');
      $sheet->setCellValueExplicit('H1', $dataList['nomor_referensi']);
      $sheet->setCellValue('G2', 'TGL');
      $sheet->getStyle('H2')->getNumberFormat()->setFormatCode(': _(@_)');
      $sheet->setCellValue('H2', PHPExcel_Shared_Date::PHPToExcel($tanggal));
      $sheet->getStyle('H2')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDDSLASH);
      $sheet->getStyle('H1:I2')->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
         )
      ));

      $sheet->setCellValue('A4', 'BUKTI KAS MASUK');
      $sheet->getStyle('A4:I4')->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         )
      ));
      $sheet->setCellValue('A6', 'Diterima Dari');
      $sheet->setCellValue('B6', ':');
      $sheet->setCellValue('C6', $mObj->_POST['penyetor']);
      $sheet->setCellValue('A7', 'Banyaknya');
      $sheet->setCellValue('B7', ':');
      $sheet->setCellValue('C7', ' # '.strtoupper($terbilang).' # ');

      $sheet->mergeCells('A9:E9');
      $sheet->mergeCells('F9:I9');
      $sheet->setCellValue('A9', strtoupper(GTFWConfiguration::GetValue('language', 'keterangan')));
      $sheet->setCellValue('F9', strtoupper(GTFWConfiguration::GetValue('language', 'jumlah_rp')));
      $sheet->getStyle('A9:I9')->applyFromArray(array(
         'font' => array(
            'bold' => true
         ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         )
      ));
      $row        = 10;
      $maxRow     = max(3, count($dataList));
      for ($i=0; $i < 3; $i++) {
         $sheet->mergeCells('A'.$row.':E'.$row);
         $sheet->mergeCells('F'.$row.':I'.$row);
         if(!empty($dataList)){
            if($i == 0){
               $sheet->setCellValue('A'.$row, $dataList['map_nama']);
               $sheet->setCellValue('F'.$row, $dataList['nominal']);
               $sheet->getStyle('F'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
            }

         }
         $row++;
      }
      $sheet->getRowDimension($row)->setRowheight(18);
      $sheet->mergeCells('A'.$row.':E'.$row);
      $sheet->mergeCells('F'.$row.':I'.$row);
      $sheet->setCellValue('A'.$row, 'T O T A L');
      $sheet->getStyle('A'.$row.':E'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
      $sheet->getStyle('A'.$row.':E'.$row)->getNumberFormat()->setFormatCode('_(@_)!:');
      $sheet->setCellValueExplicit('F'.$row, '=SUM(F10:I'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
      $sheet->getStyle('F'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');

      $sheet->getStyle('A9:I'.$row)->applyFromArray(array(
         'borders' => array(
            'vertical' => array(
               'style' => PHPExcel_Style_Border::BORDER_DASHED,
               'color' => array('argb' => 'ff000000')
            ), 'outline' => array(
               'style' => PHPExcel_Style_Border::BORDER_DASHED,
               'color' => array('argb' => 'ff000000')
            )
         )
      ));

      $sheet->getStyle('A9:I9')->applyFromArray(array(
         'borders' => array(
            'outline' => array(
               'style' => PHPExcel_Style_Border::BORDER_DASHED,
               'color' => array('argb' => 'ff000000')
            ), 'top' => array(
               'style' => PHPExcel_Style_Border::BORDER_DOUBLE,
               'color' => array('argb' => 'ff000000')
            )
         )
      ));
      $sheet->getStyle('A'.$row.':I'.$row)->applyFromArray(array(
         'borders' => array(
            'outline' => array(
               'style' => PHPExcel_Style_Border::BORDER_DASHED,
               'color' => array('argb' => 'ff000000')
            ), 'bottom' => array(
               'style' => PHPExcel_Style_Border::BORDER_DOUBLE,
               'color' => array('argb' => 'ff000000')
            )
         ), 'alignment' => array(
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         ), 'font' => array(
            'bold' => true
         )
      ));

      $rowPersetujuan   = $row+2;
      $sheet->mergeCells('A'.$rowPersetujuan.':B'.($rowPersetujuan+2));
      $sheet->mergeCells('C'.$rowPersetujuan.':E'.($rowPersetujuan+2));
      $sheet->mergeCells('F'.$rowPersetujuan.':G'.($rowPersetujuan+2));
      $sheet->mergeCells('H'.$rowPersetujuan.':I'.($rowPersetujuan+2));
      $sheet->setCellValue('A'.$rowPersetujuan, 'Disiapkan Oleh,');
      $sheet->setCellValue('C'.$rowPersetujuan, 'Disetujui Oleh,');
      $sheet->setCellValue('F'.$rowPersetujuan, 'Diterima Oleh,');
      $sheet->setCellValue('H'.$rowPersetujuan, 'Dibukukan Oleh,');

      $sheet->mergeCells('A'.($rowPersetujuan+3).':B'.($rowPersetujuan+3));
      $sheet->mergeCells('C'.($rowPersetujuan+3).':E'.($rowPersetujuan+3));
      $sheet->mergeCells('F'.($rowPersetujuan+3).':G'.($rowPersetujuan+3));
      $sheet->mergeCells('H'.($rowPersetujuan+3).':I'.($rowPersetujuan+3));
      $sheet->setCellValue('A'.($rowPersetujuan+3), $mObj->_POST['disiapkan_oleh']);
      $sheet->setCellValue('C'.($rowPersetujuan+3), $mObj->_POST['disetujui_oleh']);
      $sheet->setCellValue('F'.($rowPersetujuan+3), $mObj->_POST['diterima_oleh']);
      $sheet->setCellValue('H'.($rowPersetujuan+3), $mObj->_POST['dibukukan_oleh']);

      $sheet->getStyle('A'.$rowPersetujuan.':I'.($rowPersetujuan+3))->applyFromArray(array(
         'borders' => array(
            'outline' => array(
               'style' => PHPExcel_Style_Border::BORDER_DASHED,
               'color' => array('argb' => 'ff000000')
            ), 'vertical' => array(
               'style' => PHPExcel_Style_Border::BORDER_DASHED,
               'color' => array('argb' => 'ff000000')
            )
         ), 'alignment' => array(
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP,
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
         )
      ));

      $sheet->mergeCells('G'.($rowPersetujuan+5).':I'.($rowPersetujuan+5));
      $sheet->mergeCells('G'.($rowPersetujuan+6).':I'.($rowPersetujuan+6));
      $sheet->setCellValue('G'.($rowPersetujuan+5), $kota.', '.$tanggalCetak);
      $sheet->setCellValue('G'.($rowPersetujuan+6), 'KASIR,');

      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>