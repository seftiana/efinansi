<?php
/**
* ================= doc ====================
* FILENAME     : ViewExportExcelBp.xlsx.class.php
* @package     : ViewExportExcelBp
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-14
* @Modified    : 2015-04-14
* @Analysts    : Dyah Fajar N
* @contact     : eko.susilo@gamatechno.com
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/finansi_sppu/business/Sppu.class.php';
require_once GTFWConfiguration::GetValue('application','docroot').
'main/function/terbilang.php';

class ViewExportExcelBp extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      $mObj          = new Sppu();
      $mNumber       = new Number();
      $dataId        = Dispatcher::Instance()->Decrypt($mObj->_GET['data_id']);
      $dataSppu      = $mObj->getDataDetailSppu($dataId);
      $dataTransBank = $mObj->getDataTransaksiBank($dataId);
      $dataSppu['terbilang']  = $mNumber->Terbilang($dataSppu['nominal'], 3).' Rupiah';
      $sppuTanggal   = date('Y-m-d', strtotime($dataSppu['tanggal']));
      $sppuTglDay    = (int)date('d', strtotime($dataSppu['tanggal']));
      $sppuTglMon    = (int)date('m', strtotime($dataSppu['tanggal']));
      $sppuTglYear   = (int)date('Y', strtotime($dataSppu['tanggal']));
      $time          = gmmktime(0,0,0, $sppuTglMon, $sppuTglDay, $sppuTglYear);
      $dataList      = $mObj->getDataSppuItemsDetail($dataId);
      $tanggalCetak  = $mObj->_dateToIndo(date('Y-m-d', time()));
      $kota          = GTFWConfiguration::GetValue('organization', 'city');

      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('bank_payment.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(false); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle('bank_payment');
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

      $sheet->getRowDimension(4)->setRowheight(18);
      $sheet->getRowDimension(10)->setRowheight(18);
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
      $sheet->setCellValue('H1', $dataTransBank['nomor_bp']);
      $sheet->setCellValue('A3', 'BUKTI PEMBAYARAN BANK');
      $sheet->getStyle('A3:I3')->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         )
      ));
      $sheet->setCellValue('A5', 'Dibayar Kepada');
      $sheet->setCellValue('B5', ':');

      if($dataSppu['bank_payment'] == 'Y' && $dataSppu['cash_receipt'] == 'T'){
         $sheet->setCellValue('C5', '.................................');
      }else{
         $sheet->setCellValue('C5', 'KAS BESAR');
      }
      
      $sheet->setCellValue('A6', 'Banyaknya');
      $sheet->setCellValue('B6', ':');
      $sheet->setCellValue('C6', ' # '.strtoupper($dataSppu['terbilang']).' # ');
      $sheet->getStyle('C6')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);

      $sheet->getStyle('C6')->getAlignment()->setWrapText(TRUE);
      $getHeightRow = ceil(strlen($dataSppu['terbilang'])/50) * 17;
      $sheet->getRowDimension(6)->setRowHeight($getHeightRow);
      $sheet->getStyle('A6:B6')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);

      $sheet->setCellValue('A8', 'NAMA BANK');
      $sheet->setCellValue('B8', ':');
      $sheet->setCellValueExplicit('C8', strtoupper($dataSppu['bank']), PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('A9', 'CHECK/GIRO NO.');
      $sheet->setCellValue('B9', ':');
      $sheet->setCellValueExplicit('C9', $dataSppu['nomor_cek_giro'], PHPExcel_Cell_DataType::TYPE_STRING);

      $sheet->getStyle('F8:I9')->applyFromArray(array(
         'borders' => array(
            'allborders' => array(
               'style' => PHPExcel_Style_Border::BORDER_DASHED,
               'color' => array('argb' => 'ff000000')
            )
         ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         )
      ));

      $sheet->setCellValue('F8', 'NO. REK');
      $sheet->setCellValueExplicit('F9', $dataSppu['nomor_rekening'], PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('H8', 'TANGGAL');

      $sheet->setCellValue('H9',date('d/m/Y', strtotime($dataTransBank['tanggal'])));
      $sheet->getStyle('H9')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

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
      $rowAwal  = $row;
      $maxRow  = count($dataList);
      for ($i=0; $i < $maxRow; $i++) {
         $sheet->mergeCells('A'.$row.':E'.$row);
         $sheet->mergeCells('F'.$row.':I'.$row);
         if(!empty($dataList[$i])){
               $sheet->setCellValue('A'.$row, strtoupper($dataList[$i]['lingkup_komponen']));
               $sheet->setCellValue('F'.$row, $dataList[$i]['nominal']);
               $sheet->getStyle('F'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
               $sheet->getStyle('A'.$row.':F'.$row)->getAlignment()->setWrapText(TRUE);
               $getCountContent = ceil(strlen($dataList[$i]['lingkup_komponen'])/30); 
               if($getCountContent > 10){
                  $getHeightRow = ceil(strlen($dataList[$i]['lingkup_komponen'])/30) * 20;     
                  $sheet->getRowDimension($row)->setRowHeight($getHeightRow);
               }elseif($getCountContent > 20){
                  $getHeightRow = ceil(strlen($dataList[$i]['lingkup_komponen'])/30) * 25;     
                  $sheet->getRowDimension($row)->setRowHeight($getHeightRow);
               }else{
                  $getHeightRow = ceil(strlen($dataList[$i]['lingkup_komponen'])/30) * 16;     
                  $sheet->getRowDimension($row)->setRowHeight($getHeightRow);
               }
         }
         $row++;
      }
      $sheet->getStyle('A'.$rowAwal.':F'.$row)->applyFromArray(array(
            'alignment' => array(
               'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )));
      $sheet->getStyle('A'.$rowAwal.':I'.$row)->applyFromArray(array(     
          'borders' => array(
             'allborders' => array(
               'style' => PHPExcel_Style_Border::BORDER_DASHED,
               'color' => array('argb' => 'ff000000')
            ))
      ));     

      $sheet->mergeCells('A'.$row.':E'.$row);
      $sheet->mergeCells('F'.$row.':I'.$row);

      $sheet->getRowDimension($row+1)->setRowheight(18);
      $sheet->mergeCells('A'.($row+1).':E'.($row+1));
      $sheet->mergeCells('F'.($row+1).':I'.($row+1));
      $sheet->setCellValue('A'.($row+1), 'T O T A L');
      $sheet->getStyle('A'.($row+1).':E'.($row+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
      // $sheet->getStyle('A'.$row.':E'.$row)->getNumberFormat()->setFormatCode('_(@_)!:');
      $sheet->setCellValueExplicit('F'.($row+1), '=SUM(F11:I'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
      $sheet->getStyle('F'.($row+1))->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');

      $sheet->getStyle('A10:I'.($row+1))->applyFromArray(array(
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
      $sheet->getStyle('A'.($row+1).':I'.($row+1))->applyFromArray(array(
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

      $newRow     = $row+3;
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
      $sheet->getStyle('G'.($rowPersetujuan+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
      // $sheet->setCellValue('H'.($rowPersetujuan+5), $tanggalCetak);
      // $sheet->getStyle('H'.($rowPersetujuan+5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
      $sheet->setCellValue('G'.($rowPersetujuan+6), 'KASIR,');
      $sheet->getStyle('G'.($rowPersetujuan+6))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>