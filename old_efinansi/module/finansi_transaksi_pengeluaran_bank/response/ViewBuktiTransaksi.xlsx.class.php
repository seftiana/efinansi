<?php
/**
* ================= doc ====================
* FILENAME     : ViewBuktiTransaksi.xlsx.class.php
* @package     : ViewBuktiTransaksi
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-05-19
* @Modified    : 2015-05-19
* @Analysts    : Dyah Fajar N
* @contact     : eko.susilo@gamatechno.com
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_transaksi_pengeluaran_bank/business/TransaksiPengeluaranBank.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'main/function/terbilang.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'main/function/date.php';

class ViewBuktiTransaksi extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      $mObj       = new TransaksiPengeluaranBank();
      $mNumber    = new Number();
      $transaksi_id     = Dispatcher::Instance()->Decrypt($mObj->_GET['data_id']);
      $data_transaksi   = $mObj->getTransaksiDetil($transaksi_id);
      $data_transaksi['terbilang']  = $mNumber->Terbilang($data_transaksi['nominal'], 3);
      $transaksi_detail = $mObj->getListTransaksiDetail($transaksi_id);
      $tglMon        = date('m', strtotime($data_transaksi['tanggal']));
      $tglDay        = date('d', strtotime($data_transaksi['tanggal']));
      $tglYear       = date('Y', strtotime($data_transaksi['tanggal']));
      $time          = gmmktime(0,0,0, $tglMon, $tglDay, $tglYear);
      $tanggalCetak  =  IndonesianDate(date('Y-m-d', time()),'YYYY-MM-DD');
      $kota          = GTFWConfiguration::GetValue('organization', 'city');

      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('bukti_transaksi_pengeluaran_bank.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(false); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle('BR');
      # set font default
      $sheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize('12');
      # setting paging
      # options ORIENTATION_DEFAULT : default; ORIENTATION_LANDSCAPE: lanscape; ORIENTATION_PORTRAIT: Potrait
      $sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
      $sheet->getPageSetup()->setFitToWidth(1); # Set 0 or 1
      $sheet->getPageSetup()->setFitToHeight(0); # Set 0 or 1
      $sheet->getPageSetup()->setHorizontalCentered(true); # true or false
      $sheet->getPageSetup()->setVerticalCentered(false); # true or false
      # Set The papersize
      $sheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
      $sheet->getPageMargins()->setBottom(1.95);
      # /Document Setting

      $sheet->getRowDimension(10)->setRowheight(18);
      $sheet->getRowDimension(4)->setRowheight(18);
      $sheet->getColumnDimension('A')->setWidth(18);
      $sheet->getColumnDimension('B')->setWidth(2);
      $sheet->mergeCells('H1:I1');
      $sheet->mergeCells('A3:I3');
      $sheet->mergeCells('C5:I5');
      $sheet->mergeCells('C6:I6');
      $sheet->mergeCells('C8:E8');
      $sheet->mergeCells('F8:G8');
      $sheet->mergeCells('H8:I8');
      $sheet->mergeCells('C9:E9');
      $sheet->mergeCells('F9:G9');
      $sheet->mergeCells('H9:I9');

      $sheet->setCellValue('G1', 'BPKB');
      $sheet->getStyle('G1')->getNumberFormat()->setFormatCode('_(@_) :');
      $sheet->getStyle('H1:I1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
      $sheet->setCellValue('H1', $data_transaksi['bpkb']);
      $sheet->setCellValue('A3', 'BUKTI PEMBAYARAN BANK');
      $sheet->getStyle('A3:I3')->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         )
      ));
      $sheet->setCellValue('A5',  'Dibayar Kepada');
      $sheet->setCellValue('B5', ':');
      $sheet->setCellValue('C5', strtoupper($data_transaksi['bank_penerima']));
      $sheet->setCellValue('A6', 'Banyaknya');
      $sheet->setCellValue('B6', ':');
      $sheet->setCellValue('C6', ' # '.strtoupper($data_transaksi['terbilang']).' RUPIAH # ');
      $sheet->getStyle('A5:C6')->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP,
            'wrap' => true
         )
      ));
      $getHeightRow = ceil(strlen($data_transaksi['terbilang'])/59.5) * 14;     
      $sheet->getRowDimension(6)->setRowHeight($getHeightRow);
      
      $sheet->setCellValue('A8', 'NAMA BANK');
      $sheet->setCellValue('B8', ':');
      $sheet->setCellValueExplicit('C8', strtoupper($data_transaksi['nama_penyetor']), PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('A9', 'CHECK/GIRO NO.');
      $sheet->setCellValue('B9', ':');
      $sheet->setCellValueExplicit('C9', '', PHPExcel_Cell_DataType::TYPE_STRING);

      $sheet->getStyle('F8:I9')->applyFromArray(array(
         'borders' => array(
            'allborders' => array(
               'style' => PHPExcel_Style_Border::BORDER_DASHED,
               'color' => array('argb' => 'ff000000')
            )
         ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            'wrap' => true
         )
      ));

      $sheet->setCellValue('F8', 'NO. REK');
      $sheet->setCellValueExplicit('F9', $data_transaksi['rekening_penerima'], PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('H8', 'TANGGAL');

      $sheet->setCellValue('H9', date('d-m-Y', strtotime($data_transaksi['tanggal'])));

      $sheet->getStyle('A8:I9')->applyFromArray(array(
         'borders' => array(
            'top' => array(
               'style' => PHPExcel_Style_Border::BORDER_DOUBLE,
               'color' => array('argb' => 'ff000000')
            ), 'left' => array(
               'style' => PHPExcel_Style_Border::BORDER_DASHED,
               'color' => array('argb' => 'ff000000')
            ), 'right' => array(
               'style' => PHPExcel_Style_Border::BORDER_DASHED,
               'color' => array('argb' => 'ff000000')
            )
         )
      ));
      $sheet->mergeCells('A10:E10');
      $sheet->mergeCells('F10:I10');
      $sheet->setCellValue('A10', strtoupper(GTFWConfiguration::GetValue('language', 'keterangan')));
      $sheet->setCellValue('A11', strtoupper($data_transaksi['keterangan']), PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->getStyle('A11:E20')->getAlignment()->setWrapText(TRUE);  
      $sheet->setCellValue('F10', strtoupper(GTFWConfiguration::GetValue('language', 'jumlah_rp')));
      $sheet->setCellValue('F11', $data_transaksi['nominal'], PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->getStyle('F11')->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
      $sheet->getStyle('A10:I10')->applyFromArray(array(
         'font' => array(
            'bold' => true
         ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            'wrap' => true
         )
      ));
      $row  = 11;
      $maxRow  = max(10, count($transaksi_detail));
      for ($i=0; $i < $maxRow; $i++) {
         $sheet->mergeCells('A'.$row.':E'.$row);
         $sheet->mergeCells('F'.$row.':I'.$row);
         // if(!empty($transaksi_detail[$i])){
         //    $sheet->getStyle('A'.$row.':E'.$row)->getAlignment()->setWrapText(TRUE);            
         //    $getHeightRow = ceil(strlen($transaksi_detail[$i]['nama'])/44.5) * 14;     
         //    $sheet->getRowDimension($row)->setRowHeight($getHeightRow);
            
         //    $sheet->setCellValue('A'.$row, $transaksi_detail[$i]['nama']);
         //    $sheet->setCellValue('F'.$row, $transaksi_detail[$i]['nominal']);
         //    $sheet->getStyle('F'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
         //    $sheet->getStyle('F'.$row.':I'.$row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
         //    $sheet->getStyle('A'.$row.':E'.$row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
         // }
         $row++;
      }
      $sheet->getRowDimension($row)->setRowheight(18);
      $sheet->mergeCells('A'.$row.':E'.$row);
      $sheet->mergeCells('F'.$row.':I'.$row);
      $sheet->setCellValue('A'.$row, 'T O T A L');
      $sheet->getStyle('A'.$row.':E'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
      
      $sheet->setCellValueExplicit('F'.$row, '=SUM(F11:I'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
      $sheet->getStyle('F'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');

      $sheet->getStyle('A10:I'.$row)->applyFromArray(array(
         'borders' => array(
            'vertical' => array(
               'style' => PHPExcel_Style_Border::BORDER_DASHED,
               'color' => array('argb' => 'ff000000')
            ), 'outline' => array(
               'style' => PHPExcel_Style_Border::BORDER_DASHED,
               'color' => array('argb' => 'ff000000')
            )
         ), 
        'alignment' => array(
            'wrap' => true
         )
      ));

      $sheet->getStyle('A10:I10')->applyFromArray(array(
         'borders' => array(
            'outline' => array(
               'style' => PHPExcel_Style_Border::BORDER_DASHED,
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
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            'wrap' => true
         ), 'font' => array(
            'bold' => true
         )
      ));

      $newRow     = $row+2;
      $sheet->mergeCells('A'.$newRow.':B'.$newRow);
      $sheet->mergeCells('C'.$newRow.':E'.$newRow);
      $sheet->mergeCells('F'.$newRow.':G'.$newRow);
      $sheet->mergeCells('H'.$newRow.':I'.$newRow);
      $sheet->setCellValue('A'.$newRow, 'PERKIRAAN');
      $sheet->setCellValue('C'.$newRow, 'NO. REK');
      $sheet->setCellValue('F'.$newRow, 'DEBET');
      $sheet->setCellValue('H'.$newRow, 'KREDIT');

      $sheet->getStyle('A'.$newRow.':I'.$newRow)->applyFromArray(array(
         'borders' => array(
            'outline' => array(
               'style' => PHPExcel_Style_Border::BORDER_DASHED,
               'color' => array('argb' => 'ff000000')
            ), 'top' => array(
               'style' => PHPExcel_Style_Border::BORDER_DOUBLE,
               'color' => array('argb' => 'ff000000')
            )
         ), 'alignment' => array(
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'wrap' => true
         ), 'font' => array(
            'bold' => true
         )
      ));

      $rows       = $row+3;
      for ($i=0; $i <= 4; $i++) {
         $sheet->mergeCells('A'.$rows.':B'.$rows);
         $sheet->mergeCells('C'.$rows.':E'.$rows);
         $sheet->mergeCells('F'.$rows.':G'.$rows);
         $sheet->mergeCells('H'.$rows.':I'.$rows);
         $rows++;
      }
      $sheet->mergeCells('A'.$rows.':E'.$rows);
      $sheet->mergeCells('F'.$rows.':G'.$rows);
      $sheet->mergeCells('H'.$rows.':I'.$rows);
      $sheet->getRowDimension($rows)->setRowheight(16);
      $sheet->setCellValue('A'.$rows, 'T O T A L');
      $sheet->getStyle('A'.($row+3).':I'.$rows)->applyFromArray(array(
         'borders' => array(
            'outline' => array(
               'style' => PHPExcel_Style_Border::BORDER_DASHED,
               'color' => array('argb' => 'ff000000')
            ), 'vertical' => array(
               'style' => PHPExcel_Style_Border::BORDER_DASHED,
               'color' => array('argb' => 'ff000000')
            )
         ), 'alignment' => array(
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'wrap' => true
         )
      ));

      $sheet->getStyle('A'.$rows.':I'.$rows)->applyFromArray(array(
         'borders' => array(
            'top' => array(
               'style' => PHPExcel_Style_Border::BORDER_DASHED,
               'color' => array('argb' => 'ff000000')
            )
         ), 'alignment' => array(
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'wrap' => true
         )
      ));

      $rowPersetujuan   = $rows+1;
      $sheet->mergeCells('A'.$rowPersetujuan.':B'.($rowPersetujuan+3));
      $sheet->mergeCells('C'.$rowPersetujuan.':E'.($rowPersetujuan+3));
      $sheet->mergeCells('F'.$rowPersetujuan.':G'.($rowPersetujuan+3));
      $sheet->mergeCells('H'.$rowPersetujuan.':I'.($rowPersetujuan+3));
      $sheet->setCellValue('A'.$rowPersetujuan, 'Disiapkan Oleh,');
      $sheet->setCellValue('C'.$rowPersetujuan, 'Disetujui Oleh,');
      $sheet->setCellValue('F'.$rowPersetujuan, 'Diterima Oleh,');
      $sheet->setCellValue('H'.$rowPersetujuan, 'Dibukukan Oleh,');

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
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            'wrap' => true
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