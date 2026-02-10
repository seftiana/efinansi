<?php
/**
* ================= doc ====================
* FILENAME     : ViewRealisasiPencairan.xlsx.class.php
* @package     : ViewRealisasiPencairan
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-03-24
* @Modified    : 2015-03-24
* @Analysts    : Dyah Fajar
* @contact     : eko.susilo@gamatechno.com
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue('application','docroot').
'module/'.Dispatcher::Instance()->mModule.'/business/RealisasiPencairan.class.php';

class ViewRealisasiPencairan extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      $mObj       = new RealisasiPencairan();
      $dataId     = Dispatcher::Instance()->Decrypt($mObj->_GET['id']);
      $dataList   = $mObj->getDataPengajuanrealisasiDetail($dataId);
      $unitKode   = $dataList[0]['unit_kode'];
      $unitNama   = $dataList[0]['unit_nama'];
      $namaRektor       = $mObj->getSettingValue('rektor');
      $namaKasubagKeuangan    = $mObj->getSettingValue('kasubag_keuangan');
      $namaWarekSdm           = $mObj->getSettingValue('warek_sdm');
      $namaKabiroKeuangan     = $mObj->getSettingValue('kabiro_keuangan');

      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('spak_'.date('YmdHis', time()).'.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(false); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle('Worksheet Name');
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

      $sheet->getColumnDimension('A')->setWidth(3);
      $sheet->getColumnDimension('B')->setWidth(3);
      $sheet->getColumnDimension('C')->setWidth(4);
      $sheet->getColumnDimension('D')->setWidth(14);
      $sheet->getColumnDimension('E')->setWidth(20);
      $sheet->getColumnDimension('F')->setWidth(25);
      $sheet->getColumnDimension('G')->setWidth(28);
      $sheet->getColumnDimension('H')->setWidth(28);
      $sheet->getColumnDimension('I')->setWidth(28);
      $sheet->getColumnDimension('J')->setWidth(30);

      $sheet->getRowDimension(3)->setRowHeight(20);
      $sheet->getRowDimension(13)->setRowHeight(16);

      $sheet->mergeCells('C9:D9');
      $sheet->mergeCells('E9:G9');
      $sheet->mergeCells('C10:E10');
      $sheet->mergeCells('C11:G11');
      $this->objectDrawing    = new PHPExcel_Worksheet_Drawing();
      $this->objectDrawing->setName('Logo');
      $this->objectDrawing->setDescription('Logo');
      $this->objectDrawing->setPath(GTFWConfiguration::GetValue('application', 'docroot').'/images/logo_bw_96.png');
      $this->objectDrawing->setHeight(96);
      $this->objectDrawing->setWorksheet($sheet);
      $this->objectDrawing->setCoordinates('E3');
      $sheet->mergeCells('B3:J3');
      $sheet->setCellValue('B3', strtoupper(GTFWConfiguration::GetValue('organization', 'company_full_name')));
      $sheet->getStyle('B3:J3')->applyFromArray(array(
         'font' => array(
            'size' => 11,
            'bold' => true
         ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         )
      ));
      $sheet->setCellValue('C9', GTFWConfiguration::GetValue('language', 'unit_kerja'));
      $sheet->setCellValueExplicit('E9', $unitKode.' - '.$unitNama, PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValue('C10', 'Program kerja akan dilaksanakan pada :');
      $sheet->setCellValue('C11', 'Yang akan mengambil uang : Non Cash (bdsr rek. Koran bukopin 2)');

      $sheet->setCellValue('I9', 'No. FPA : ');
      $sheet->setCellValue('J9', $dataList[0]['nomor_pengajuan']);
      $sheet->setCellValue('I10', 'Tanggal Cetak : ');
      $tanggalCetak  = date('Y-m-d', time());
      $getdate       = getdate();
      $currDay       = (int)$getdate['mday'];
      $currMon       = (int)$getdate['mon'];
      $currYear      = (int)$getdate['year'];
      $time          = gmmktime(0,0,0, $currMon, $currDay, $currYear);
      $sheet->setCellValue('J10', PHPExcel_Shared_Date::PHPToExcel($time));
      $sheet->getStyle('J10')->getNumberFormat()->setFormatCode('dd-mmm-yy;@');
      $sheet->getStyle('B9:J12')->applyFromArray(array(
         'font' => array(
            'bold' => true
         ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
         )
      ));

      $sheet->getStyle('I9:I10')->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
         )
      ));
      $sheet->mergeCells('B13:C13');
      $sheet->setCellValue('B13', 'NO');
      $sheet->setCellValue('D13', 'Kode Rek.');
      $sheet->setCellValue('E13', 'Nama Rekening');
      $sheet->setCellValue('F13', 'Rincian');
      $sheet->setCellValue('G13', 'Jumlah (Rp)');
      $sheet->setCellValue('H13', 'Anggaran (Rp)');
      $sheet->setCellValue('I13', 'Disetujui');
      $sheet->setCellValue('J13', 'Keterangan');

      $sheet->getStyle('B13:J13')->applyFromArray(array(
         'borders' => array(
            'allborders' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN,
               'color' => array('argb' => 'ff000000')
            )
         ), 'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array(
               'argb' => 'ffcccccc'
            )
         ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         ), 'font' => array(
            'bold' => true
         )
      ));

      $sheet->getStyle('B2:B12')->applyFromArray(array(
         'borders' => array(
            'left' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN,
               'color' => array('argb' => 'ff000000')
            )
         )
      ));
      $sheet->getStyle('B2:J2')->applyFromArray(array(
         'borders' => array(
            'top' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN,
               'color' => array('argb' => 'ff000000')
            )
         )
      ));
      $sheet->getStyle('J2:J12')->applyFromArray(array(
         'borders' => array(
            'right' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN,
               'color' => array('argb' => 'ff000000')
            )
         )
      ));

      $row     = 14;
      $maxRow  = max(10, count($dataList));
      $nomor   = 1;
      for ($i=0; $i < $maxRow; $i++) {
         $sheet->mergeCells('B'.$row.':C'.$row);
         if(!empty($dataList[$i])){
            $rowHeight  = max(array(
               ceil(strlen($dataList[$i]['akun_nama'])/20),
               ceil(strlen($dataList[$i]['komponen_nama'])/25),
               ceil(strlen($dataList[$i]['keterangan'])/28)
            ));
            $sheet->getRowDimension($row)->setRowHeight(($rowHeight*15));
            $sheet->setCellValue('B'.$row, $nomor);
            $sheet->setCellValueExplicit('D'.$row, $dataList[$i]['akun_kode'] , PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('E'.$row, $dataList[$i]['akun_nama']);
            $sheet->setCellValue('F'.$row, $dataList[$i]['komponen_nama']);
            $sheet->setCellValueExplicit('G'.$row, $dataList[$i]['nominal'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('H'.$row, $dataList[$i]['anggaran_approve'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('I'.$row, '');//, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValue('J'.$row, $dataList[$i]['keterangan']);
            $nomor+=1;
         }
         $row+=1;
      }
      $sheet->getStyle('B14:C'.($row-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
      $sheet->getRowDimension($row)->setRowHeight(18);
      $sheet->setCellValue('B'.$row, 'T O T A L');
      $sheet->mergeCells('B'.$row.':F'.$row);
      $sheet->getStyle('B'.$row.':F'.$row)->applyFromArray(array(
         'font' => array(
            'bold' => true
         ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         )
      ));
      $sheet->setCellValueExplicit('G'.$row, '=SUM(G14:G'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
      $sheet->setCellValueExplicit('H'.$row, '=SUM(H14:H'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
      $sheet->setCellValueExplicit('H'.$row, '=SUM(H14:H'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
      $sheet->getStyle('G14:I'.$row)->getNumberFormat()->setFormatCode('#,##0_);[Red](#,##0)');
      $sheet->getStyle('G'.$row.':J'.$row)->applyFromArray(array(
         'font' => array(
            'bold' => true
         ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         )
      ));

      $sheet->getStyle('B14:J'.($row-1))->applyFromArray(array(
         'borders' => array(
            'vertical' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN,
               'color' => array('argb' => 'ff000000')
            ), 'left' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN,
               'color' => array('argb' => 'ff000000')
            ), 'right' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN,
               'color' => array('argb' => 'ff000000')
            )
         ), 'alignment' => array(
            'wrap' => true,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
         )
      ));

      $rows       = $row+1;
      $sheet->getRowDimension($rows)->setRowHeight(8);
      $sheet->mergeCells('B'.$rows.':J'.$rows);
      $sheet->getStyle('B'.$row.':J'.$rows)->applyFromArray(array(
         'borders' => array(
            'allborders' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN,
               'color' => array('argb' => 'ff000000')
            )
         )
      ));

      $sheet->getRowDimension(($rows+1))->setRowHeight(18);
      $sheet->mergeCells('B'.($rows+1).':F'.($rows+1));
      $sheet->setCellValue('B'.($rows+1), 'Persetujuan Transaksi Operasional');

      $sheet->mergeCells('G'.($rows+1).':J'.($rows+1));
      $sheet->setCellValue('G'.($rows+1), 'Persetujuan Atas Pencairan Anggaran');

      $sheet->getStyle('B'.($rows+1).':J'.($rows+1))->applyFromArray(array(
         'borders' => array(
            'allborders' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN,
               'color' => array('argb' => 'ff000000')
            )
         ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         ), 'font' => array(
            'bold' => true
         )
      ));

      $sheet->mergeCells('B'.($rows+3).':D'.($rows+3));
      $sheet->setCellValue('B'.($rows+3), 'Yang Mengajukan,');
      $sheet->setCellValue('E'.($rows+3), 'Mengetahui,');
      $sheet->setCellValue('F'.($rows+3), 'Menyetujui,');

      $sheet->mergeCells('B'.($rows+7).':D'.($rows+7));
      $sheet->setCellValue('B'.($rows+7), str_repeat('.', 25));
      $sheet->setCellValue('E'.($rows+7), str_repeat('.', 25));
      $sheet->setCellValue('F'.($rows+7), str_repeat('.', 25));
      $sheet->getStyle('B'.($rows+7).':F'.($rows+7))->applyFromArray(array(
         'font' => array(
            'underline' => true
         )
      ));

      $sheet->setCellValue('G'.($rows+7), $namaKasubagKeuangan);
      $sheet->setCellValue('H'.($rows+7), $namaKabiroKeuangan);
      $sheet->setCellValue('I'.($rows+7), $namaWarekSdm);
      // $sheet->setCellValue('J'.($rows+7), $namaRektor);

      $sheet->mergeCells('B'.($rows+8).':D'.($rows+8));
      $sheet->setCellValue('B'.($rows+8), 'Kabag/Kaprodi');
      $sheet->setCellValue('E'.($rows+8), 'Kabiro/Direktur/Dekan*');
      $sheet->setCellValue('F'.($rows+8), 'Warek/Rektor**');

      $sheet->setCellValue('G'.($rows+8), 'Kabag Keuangan');
      $sheet->setCellValue('H'.($rows+8), 'Kabiro Keuangan');
      $sheet->setCellValue('I'.($rows+8), 'Warek Sumber Daya dan Perencanaan');
      // $sheet->setCellValue('J'.($rows+8), 'Rektor');

      $sheet->getStyle('B'.($rows+7).':J'.($rows+8))->getFont()->setBold(true);
      $sheet->getStyle('F'.($rows+2).':F'.($rows+10))->applyFromArray(array(
         'borders' => array(
            'right' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN,
               'color' => array('argb' => 'ff000000')
            )
         )
      ));

      $sheet->getStyle('B'.($rows+2).':J'.($rows+10))->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         ), 'borders' => array(
            'left' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN,
               'color' => array('argb' => 'ff000000')
            ), 'right' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN,
               'color' => array('argb' => 'ff000000')
            )
         )
      ));

      $sheet->getStyle('B'.($rows+10).':J'.($rows+10))->applyFromArray(array(
         'borders' => array(
            'bottom' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN,
               'color' => array('argb' => 'ff000000')
            )
         )
      ));

      $sheet->mergeCells('B'.($rows+12).':C'.($rows+12));
      $sheet->mergeCells('D'.($rows+12).':F'.($rows+12));
      $sheet->mergeCells('D'.($rows+13).':F'.($rows+13));
      $sheet->setCellValue('B'.($rows+12), 'Cat:');
      $sheet->getStyle('B'.($rows+12))->applyFromArray(array(
         'font' => array(
            'bold' => true
         ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         )
      ));
      $sheet->setCellValue('D'.($rows+12), ' * Coret yang tidak perlu');
      $sheet->setCellValue('D'.($rows+13), '** Coret yang tidak perlu');

      $sheet->setCellValue('I'.($rows+12), 'Yang Menerima FPA');
      $sheet->setCellValue('I'.($rows+13), 'Tanggal, '.str_repeat('_', 10));
      // $sheet->mergeCells('I'.($rows+19).':J'.($rows+19));
      $sheet->setCellValue('I'.($rows+19), 'Staf Keuangan');
      $sheet->getStyle('I'.($rows+19).':J'.($rows+19))->applyFromArray(array(
         'font' => array(
            'bold' => true
         ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
         )
      ));

      $sheet->getStyle('B'.($rows+11).':J'.($rows+20))->applyFromArray(array(
         'borders' => array(
            'left' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN,
               'color' => array('argb' => 'ff000000')
            ), 'right' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN,
               'color' => array('argb' => 'ff000000')
            )
         )
      ));
      $sheet->getStyle('B'.($rows+20).':J'.($rows+20))->applyFromArray(array(
         'borders' => array(
            'bottom' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN,
               'color' => array('argb' => 'ff000000')
            )
         )
      ));
      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>