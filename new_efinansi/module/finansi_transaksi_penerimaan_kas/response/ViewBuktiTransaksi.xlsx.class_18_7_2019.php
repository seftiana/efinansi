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
'module/finansi_transaksi_penerimaan_kas/business/TransaksiPenerimaanKas.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'main/function/terbilang.php';

class ViewBuktiTransaksi extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      $mObj       = new TransaksiPenerimaanKas();
      $mNumber    = new Number();
      $transaksi_id     = Dispatcher::Instance()->Decrypt($mObj->_GET['transaksi_id']);
      $data_transaksi   = $mObj->getTransaksiDetil($transaksi_id);
      $data_transaksi['terbilang']  = $mNumber->Terbilang($data_transaksi['nominal'], 3);
      $transaksi_detail = $mObj->getListTransaksiDetail($transaksi_id);
      $tglMon        = date('m', strtotime($data_transaksi['tanggal']));
      $tglDay        = date('d', strtotime($data_transaksi['tanggal']));
      $tglYear       = date('Y', strtotime($data_transaksi['tanggal']));
      $time          = gmmktime(0,0,0, $tglMon, $tglDay, $tglYear);
      $tanggalCetak  = $mObj->indonesianDate(date('Y-m-d', time()));
      $kota          = GTFWConfiguration::GetValue('organization', 'city');

      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('bukti_transaksi_penerimaan_kas.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(false); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle('BR');
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
	  
	  $sheet->getRowDimension(4)->setRowHeight(30); # 1A
	  $sheet->getRowDimension(1)->setRowHeight(30); # 1B
      $sheet->getRowDimension(10)->setRowheight(18);
      $sheet->getRowDimension(3)->setRowheight(18);
      $sheet->getColumnDimension('A')->setWidth(18);
      $sheet->getColumnDimension('B')->setWidth(2);
	  $sheet->getColumnDimension('H')->setWidth(15); # 2 
	  
	  $sheet->mergeCells('G1:I1'); # 3
      $sheet->mergeCells('H2:I2'); # 4
      $sheet->mergeCells('H3:I3'); # 5
      $sheet->mergeCells('A4:I4'); # 6
      $sheet->mergeCells('C5:I5');
      $sheet->mergeCells('C6:I6');
      $sheet->mergeCells('C7:I8');
      $sheet->mergeCells('F8:G8');
      $sheet->mergeCells('H8:I8');
      $sheet->mergeCells('C9:E9');
      $sheet->mergeCells('F9:G9');
      $sheet->mergeCells('H9:I9');

	  $sheet->setCellValue('G1', 'KODE DOKUMEN : F-KUA-001.003'); # 7 
      $sheet->setCellValue('G2', 'BPKB');
      $sheet->getStyle('G2')->getNumberFormat()->setFormatCode('_(@_) :');
      $sheet->getStyle('H2:I2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
      $sheet->setCellValue('H2', $data_transaksi['bpkb']);
      $sheet->setCellValue('G3', 'TGL ');
      $sheet->getStyle('G3')->getNumberFormat()->setFormatCode('_(@_) :');
      $sheet->setCellValue('H3', date('d-m-Y', strtotime($data_transaksi['tanggal'])));
      
      $sheet->getStyle('H2:I3')->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
         )
      ));
	  
	  # 8
	  $sheet->getStyle('G1:I1')->applyFromArray(array(
         'font' => array(
            'bold' => true
         ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         ),'borders' => array(
            'allborders' => array(
               'style' => PHPExcel_Style_Border::BORDER_DOUBLE,
               'color' => array('argb' => 'ff000000')
            )
      )));

      $sheet->setCellValue('A4', 'BUKTI PENERIMAAN KAS'); # 9
      $sheet->getStyle('A4:I4')->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         )
      ));
      $sheet->setCellValue('A5', 'Diterima Dari');
      $sheet->setCellValue('B5', ':');
      $sheet->setCellValue('C5', $data_transaksi['nama_penyetor'].' - '.$data_transaksi['rekening_penyetor']);
      $sheet->setCellValue('A6', 'Kepada');
      $sheet->setCellValue('B6', ':');
      $sheet->setCellValueExplicit('C6', $data_transaksi['kas_penerima'] .' - '.$data_transaksi['rekening_penerima'], PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('A7', 'Banyaknya');
      $sheet->setCellValue('B7', ':');
      $sheet->setCellValue('C7', ' # '.strtoupper($data_transaksi['terbilang']).' # ');

      $sheet->getStyle('A5:I8')->applyFromArray(array(
         'alignment' => array(
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP,
            'wrap' => true
         )
      ));
      $sheet->getStyle('C5:I8')->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
         )
      ));

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
      $row  = 11;
      $maxRow  = max(10, count($transaksi_detail));
      for ($i=0; $i < $maxRow; $i++) {
         $sheet->mergeCells('A'.$row.':E'.$row);
         $sheet->mergeCells('F'.$row.':I'.$row);
         if(!empty($transaksi_detail[$i])){
            $sheet->setCellValue('A'.$row, $transaksi_detail[$i]['nama']);
            $sheet->setCellValue('F'.$row, $transaksi_detail[$i]['nominal']);
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