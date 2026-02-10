<?php
/**
* ================= doc ====================
* FILENAME     : ViewBankReceive.xlsx.class.php
* @package     : ViewBankReceive
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-23
* @Modified    : 2015-04-23
* @Analysts    : Dyah Fajar N
* @contact     : eko.susilo@gamatechno.com
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_transaksi_pembayaran/business/TransaksiPembayaran.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'main/function/terbilang.php';

class ViewBankReceive extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      $mObj          = new TransaksiPembayaran();
      $mNumber       = new Number();
      $getdate       = getdate();
      $mday          = (int)$getdate['mday'];
      $mon           = (int)$getdate['mon'];
      $year          = (int)$getdate['year'];
      $time          = gmmktime(0,0,0, $mon, $mday, $year);
      $dataList      = $mObj->getDataTransaksiDetail($dataId);
      $dataList['terbilang']  = $mNumber->Terbilang($dataList[0]['nominal'], 3).' Rupiah';
      $tanggalCetak  = $mObj->indonesianDate(date('Y-m-d', time()));
      $kota          = GTFWConfiguration::GetValue('organization', 'city');
      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('bank_receive.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(false); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle('bank receive');
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
      $sheet->setCellValueExplicit('H1', '150150021');
      $sheet->setCellValue('G2', 'TGL');
      $sheet->getStyle('H2')->getNumberFormat()->setFormatCode(': _(@_)');
      $sheet->setCellValue('H2', PHPExcel_Shared_Date::PHPToExcel($time));
      $sheet->getStyle('H2')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDDSLASH);
      $sheet->getStyle('H1:I2')->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
         )
      ));

      $sheet->setCellValue('A4', 'BUKTI PEMBAYARAN BANK');
      $sheet->getStyle('A4:I4')->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         )
      ));
      $sheet->setCellValue('A6', 'Diterima Dari');
      $sheet->setCellValue('B6', ':');
      $sheet->setCellValue('C6', 'PEMBAYARAN MAHASISWA');
      $sheet->setCellValue('A7', 'Kepada Bank');
      $sheet->setCellValue('B7', ':');
      $sheet->setCellValue('C7', 'BANK BUKOPIN 2');
      $sheet->setCellValue('A8', 'Banyaknya');
      $sheet->setCellValue('B8', ':');
      $sheet->setCellValue('C8', ' # '.strtoupper($dataList['terbilang']).' # ');

      $sheet->mergeCells('A10:E10');
      $sheet->mergeCells('F10:I10');
      $sheet->setCellValue('A10', strtoupper(GTFWConfiguration::GetValue('language', 'keterangan')));
      $sheet->setCellValue('F10', strtoupper(GTFWConfiguration::GetValue('language', 'jumlah_rp')));
      $sheet->getStyle('A10:I10')->applyFromArray(array(
         'font' => array(
            'bold' => true
         ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         )
      ));
      $row        = 11;
      $maxRow     = max(3, count($dataList));
      for ($i=0; $i < $maxRow; $i++) {
         $sheet->mergeCells('A'.$row.':E'.$row);
         $sheet->mergeCells('F'.$row.':I'.$row);
         if(!empty($dataList[$i])){
            $sheet->setCellValue('A'.$row, $dataList[$i]['keterangan']);
            $sheet->setCellValue('F'.$row, $dataList[$i]['nominal']);
            $sheet->getStyle('F'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');

         }
         $row++;
      }
      $sheet->getRowDimension($row)->setRowheight(18);
      $sheet->mergeCells('A'.$row.':E'.$row);
      $sheet->mergeCells('F'.$row.':I'.$row);
      $sheet->setCellValue('A'.$row, 'T O T A L');
      $sheet->getStyle('A'.$row.':E'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
      $sheet->getStyle('A'.$row.':E'.$row)->getNumberFormat()->setFormatCode('_(@_)!:');
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
         )
      ));

      $sheet->getStyle('A10:I10')->applyFromArray(array(
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
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
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
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
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
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
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