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

   function ProcessRequest()
   {
      $Obj           = new AppLapAliranKas();
      $tgl_awal      = Dispatcher::Instance()->Decrypt($_GET['tgl_awal']);
      $tgl_akhir     = Dispatcher::Instance()->Decrypt($_GET['tgl_akhir']);
      $tglKas        = Dispatcher::Instance()->Decrypt($_GET['tgl_kas']);
      $gridList      = $Obj->GetLaporanAll($tgl_awal,$tgl_akhir);
      $dataAliranKas = $Obj->GetSaldoCoaAliranKas();
      $gridListKasSetaraKas = $Obj->GetLaporanKasSetaraKas($tglKas);

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

      if (empty($gridList)) {
         $sheet->setCellValue('A1', GTFWConfiguration::GetValue('language', 'data_kosong'));
      } else {
         $sheet->getColumnDimension('A')->setWidth(50);
         $sheet->getColumnDimension('B')->setWidth(18);

         $sheet->getRowDimension(1)->setRowHeight(18);
         $sheet->setCellValue('A1', 'Laporan Aliran Kas');
         $sheet->mergeCells('A1:B1');
         $sheet->setCellValueByColumnAndRow(0, 3, 'Untuk Interval Waktu '.IndonesianDate($tgl_awal, 'yyyy-mm-dd').' s/d '.IndonesianDate($tgl_akhir, 'yyyy-mm-dd'));
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

         // untuk ARUS KAS DARI AKTIVITAS OPERASI
         $sheet->setCellValueByColumnAndRow(0, 5, 'ARUS KAS DARI AKTIVITAS OPERASI');
         $sheet->mergeCells('A5:B5');
         $sheet->getStyle('A5:B5')->applyFromArray(array(
            'font' => array(
               'bold' => true
            ), 'borders' => array(
               'bottom' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THICK
               )
            )
         ));

         $sheet->setCellValueByColumnAndRow(0, 6, 'ARUS MASUK');
         $sheet->setCellValueByColumnAndRow(1, 6, 'JUMLAH (RP)');
         $sheet->getStyle('A6:B6')->applyFromArray(array(
            'font' => array(
               'bold' => true
            ), 'borders' => array(
               'bottom' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN
               )
            )
         ));
         $row           = 7;
         $jmlOperasi    = 0;
         for($i=0;$i<count($gridList);$i++){
            if($gridList[$i]['kelJnsNama']=='Operasional' and $gridList[$i]['status'] == 'Ya')
            {
               // $row+=2;
               $sheet->setCellValueByColumnAndRow(0, $row, $gridList[$i]['nama_kel_lap']);
               $sheet->getCellByColumnAndRow(1, $row)->setValueExplicit($gridList[$i]['nilai'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->getStyle('B'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
               $jmlOperasi+=$gridList[$i]['nilai'];

               $row++;
            }
         }

         $sheet->setCellValueByColumnAndRow(0, ($row+1), 'ARUS KELUAR');
         $sheet->setCellValueByColumnAndRow(1, ($row+1), 'JUMLAH (RP)');
         $sheet->getStyle('A'.($row+1).':B'.($row+1))->applyFromArray(array(
            'font' => array(
               'bold' => true
            ), 'borders' => array(
               'bottom' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN
               )
            )
         ));
         $row+=2;
         for($i=0;$i<count($gridList);$i++){
            if($gridList[$i]['kelJnsNama']=='Operasional' and $gridList[$i]['status'] == 'Tidak')
            {
               // $row+=2;
               $sheet->setCellValueByColumnAndRow(0, $row, $gridList[$i]['nama_kel_lap']);
               $sheet->getCellByColumnAndRow(1, $row)->setValueExplicit($gridList[$i]['nilai'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->getStyle('B'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
               $jmlOperasi-=$gridList[$i]['nilai'];
               $row++;
            }
         }


         // $row+=1;
         $sheet->setCellValueByColumnAndRow(0, $row, 'Jumlah Arus Kas Bersih Dari Aktivitas Operasi');
         $sheet->getCellByColumnAndRow(1, $row)->setValueExplicit($jmlOperasi, PHPExcel_Cell_DataType::TYPE_NUMERIC);
         $sheet->getStyle('A'.$row.':B'.$row)->applyFromArray(array(
            'font' => array(
               'bold' => true
            ), 'borders' => array(
               'top' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THICK
               ), 'bottom' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN
               )
            )
         ));
         $sheet->getStyle('B'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');

         //------------------------------------------untuk ARUS KAS DARI AKTIVITAS INVESTASI----------------------------------------------------------------
         $row+=2;
         $sheet->setCellValueByColumnAndRow(0, $row, 'ARUS KAS DARI AKTIVITAS INVESTASI');
         $sheet->mergeCells('A'.$row.':B'.$row);
         $sheet->getStyle('A'.$row.':B'.$row)->applyFromArray(array(
            'font' => array(
               'bold' => true
            ), 'borders' => array(
               'bottom' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THICK
               )
            )
         ));
         $row+=1;
         $sheet->setCellValueByColumnAndRow(0, $row, 'ARUS MASUK');
         $sheet->setCellValueByColumnAndRow(1, $row, 'JUMLAH (RP)');
         $sheet->getStyle('A'.$row.':B'.$row)->applyFromArray(array(
            'font' => array(
               'bold' => true
            ), 'borders' => array(
               'bottom' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN
               )
            )
         ));
         $row+=1;
         for($i=0;$i<count($gridList);$i++){
            if($gridList[$i]['kelJnsNama']=='Investasi' and $gridList[$i]['status'] == 'Ya')
            {
               $sheet->setCellValueByColumnAndRow(0, $row, $gridList[$i]['nama_kel_lap']);
               $sheet->getCellByColumnAndRow(1, $row)->setValueExplicit($gridList[$i]['nilai'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->getStyle('B'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
               $jmlInvestasi +=$gridList[$i]['nilai'];
               $row++;
            }
         }

         $row+=1;
         $sheet->setCellValueByColumnAndRow(0,$row, 'ARUS KELUAR');
         $sheet->setCellValueByColumnAndRow(1,$row, 'JUMLAH (RP)');
         $sheet->getStyle('A'.$row.':B'.$row)->applyFromArray(array(
            'font' => array(
               'bold' => true
            ), 'borders' => array(
               'bottom' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN
               )
            )
         ));
         $row+=1;
         for($i=0;$i<count($gridList);$i++){
            if($gridList[$i]['kelJnsNama']=='Investasi' and $gridList[$i]['status'] == 'Tidak')
            {
               $sheet->setCellValueByColumnAndRow(0, $row, $gridList[$i]['nama_kel_lap']);
               $sheet->getCellByColumnAndRow(1, $row)->setValueExplicit($gridList[$i]['nilai'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->getStyle('B'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
               $jmlInvestasi-=$gridList[$i]['nilai'];

               $row++;
            }
         }

         // $row+=1;
         $sheet->setCellValueByColumnAndRow(0, $row, 'Jumlah Arus Kas Bersih Dari Aktivitas Investasi');
         $sheet->getCellByColumnAndRow(1, $row)->setValueExplicit($jmlInvestasi, PHPExcel_Cell_DataType::TYPE_NUMERIC);
         $sheet->getStyle('B'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
         $sheet->getStyle('A'.$row.':B'.$row)->applyFromArray(array(
            'font' => array(
               'bold' => true
            ), 'borders' => array(
               'top' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THICK
               ), 'bottom' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN
               )
            )
         ));

         // untuk  ARUS KAS DARI AKTIVITAS PENDANAAN
         $row+=2;
         $sheet->setCellValueByColumnAndRow(0, $row, 'ARUS KAS DARI AKTIVITAS PENDANAAN');
         $sheet->mergeCells('A'.$row.':B'.$row);
         $sheet->getStyle('A'.$row.':B'.$row)->applyFromArray(array(
            'font' => array(
               'bold' => true
            ), 'borders' => array(
               'bottom' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THICK
               )
            )
         ));
         $row+=1;
         $sheet->setCellValueByColumnAndRow(0, $row, 'ARUS MASUK');
         $sheet->setCellValueByColumnAndRow(1, $row, 'JUMLAH (RP)');
         $sheet->getStyle('A'.$row.':B'.$row)->applyFromArray(array(
            'font' => array(
               'bold' => true
            ), 'borders' => array(
               'bottom' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN
               )
            )
         ));
         $row+=1;
         $jmlPendanaan  = 0;
         for($i=0;$i<count($gridList);$i++){
            if($gridList[$i]['kelJnsNama']=='Pendanaan' and $gridList[$i]['status'] == 'Ya')
            {
               $sheet->setCellValueByColumnAndRow(0, $row, $gridList[$i]['nama_kel_lap']);
               $sheet->getCellByColumnAndRow(1, $row)->setValueExplicit($gridList[$i]['nilai'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->getStyle('B'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
               $jmlPendanaan +=$gridList[$i]['nilai'];
               $row++;
            }
         }

         $row+=1;
         $sheet->setCellValueByColumnAndRow(0, $row, 'ARUS KELUAR');
         $sheet->setCellValueByColumnAndRow(1, $row, 'JUMLAH (RP)');
         $sheet->getStyle('A'.$row.':B'.$row)->applyFromArray(array(
            'font' => array(
               'bold' => true
            ), 'borders' => array(
               'bottom' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN
               )
            )
         ));
         $row+=1;
         for($i=0;$i<count($gridList);$i++){
            if($gridList[$i]['kelJnsNama']=='Pendanaan' and $gridList[$i]['status'] == 'Tidak')
            {
               $sheet->setCellValueByColumnAndRow(0, $row, $gridList[$i]['nama_kel_lap']);
               $sheet->getCellByColumnAndRow(1, $row)->setValueExplicit($gridList[$i]['nilai'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->getStyle('B'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
               $jmlPendanaan -=$gridList[$i]['nilai'];

               $row++;
            }
         }

         // $row+=1;
         $sheet->setCellValueByColumnAndRow(0, $row, 'Jumlah Arus Kas Bersih dari Kegiatan Pendanaan');
         $sheet->getCellByColumnAndRow(1, $row)->setValueExplicit($jmlPendanaan, PHPExcel_Cell_DataType::TYPE_NUMERIC);
         $sheet->getStyle('B'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
         $sheet->getStyle('A'.$row.':B'.$row)->applyFromArray(array(
            'font' => array(
               'bold' => true
            ), 'borders' => array(
               'top' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THICK
               ), 'bottom' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN
               )
            )
         ));

         // KENAIKAN (PENURUNAN) BERSIH KAS DAN SETARA KAS
         $row+=2;
         $sRow=$row;
         $totalKenaikan       = $jmlOperasi+$jmlInvestasi+$jmlPendanaan;
         $sheet->setCellValueByColumnAndRow(0, $row, 'KENAIKAN (PENURUNAN) BERSIH KAS DAN SETARA KAS');
         $sheet->getCellByColumnAndRow(1, $row)->setValueExplicit($totalKenaikan, PHPExcel_Cell_DataType::TYPE_NUMERIC);
         $sheet->getStyle('B'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');

         //-------------------KAS DAN SETARA KAS AWAL TAHUN---
         $row+=1;
         $sheet->setCellValueByColumnAndRow(0, $row, 'KAS DAN SETARA KAS AWAL TAHUN');
         $sheet->setCellValueByColumnAndRow(1, $row, 'JUMLAH (RP)');
         $row+=1;
         $jmlKasSetaraKas     = 0;
         for($i=0;$i<count($gridListKasSetaraKas);$i++){
            $sheet->setCellValueByColumnAndRow(0, $row, $gridListKasSetaraKas[$i]['nama_kel_lap']);
            $sheet->getCellByColumnAndRow(1, $row)->setValueExplicit($gridListKasSetaraKas[$i]['nilai'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getStyle('B'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
            if($gridListKasSetaraKas[$i]['status']=='Ya') {
               $jmlKasSetaraKas +=$gridListKasSetaraKas[$i]['nilai'];
            } else {
               $jmlKasSetaraKas -=$gridListKasSetaraKas[$i]['nilai'];
            }
         }

         //-------------------KAS DAN SETARA KAS AKHIR TAHUN---
         $row+=1;
         $totalKenaikan       = $jmlOperasi+$jmlInvestasi+$jmlPendanaan;
         $sheet->setCellValueByColumnAndRow(0, $row, 'KAS DAN SETARA KAS AKHIR TAHUN');
         $sheet->getCellByColumnAndRow(1, $row)->setValueExplicit($totalKenaikan+$jmlKasSetaraKas, PHPExcel_Cell_DataType::TYPE_NUMERIC);
         $sheet->getStyle('B'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
         $sheet->getStyle('A'.$sRow.':B'.$row)->applyFromArray(array(
            'font' => array(
               'bold' => true
            ), 'borders' => array(
               'top' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THICK
               ), 'bottom' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THICK
               ), 'horizontal' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN
               )
            )
         ));
      }
      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>