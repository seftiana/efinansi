<?php
/**
* ================= doc ====================
* FILENAME     : ViewExcelLapPosisiKeuangan.xlsx.class.php
* @package     : ViewExcelLapPosisiKeuangan
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
'module/lap_posisi_keuangan/business/AppLapPosisiKeuangan.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'main/function/date.php';

class ViewExcelLapPosisiKeuangan extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      $Obj        = new AppLapPosisiKeuangan();
      $tgl_awal   = Dispatcher::Instance()->Decrypt($_GET['tgl_awal']);
      $tgl_akhir  = Dispatcher::Instance()->Decrypt($_GET['tgl_akhir']);

      $gridList   = $Obj->GetLaporanAll($tgl_awal,$tgl_akhir);
      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('posisi_keuangan_' . date("d-m-Y") . '.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(false); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle('BS');
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
         $sheet->getColumnDimension('B')->setWidth(20);

         $sheet->getRowDimension(1)->setRowHeight(18);
         $sheet->getRowDimension(2)->setRowHeight(16);
         $sheet->getRowDimension(3)->setRowHeight(16);
         $sheet->getRowDimension(4)->setRowHeight(16);

         $sheet->mergeCells('A1:B1');
         $sheet->mergeCells('A2:B2');
         $sheet->mergeCells('A3:B3');
         $sheet->mergeCells('A4:B4');
         $sheet->setCellValueByColumnAndRow(0, 1, GTFWConfiguration::GetValue('organization', 'company_name'));
         $row++;  $coll = 0;
         $sheet->setCellValueByColumnAndRow(0, 2, 'Laporan Posisi Keuangan');
         $row++;
         $coll = 0;
         $sheet->setCellValueByColumnAndRow(0, 3, 'Untuk Interval Waktu Mulai ' . IndonesianDate($tgl_awal, 'yyyy-mm-dd').' s/d '.IndonesianDate($tgl_akhir, 'yyyy-mm-dd'));
         $sheet->setCellValueByColumnAndRow(0, 4, '(Dinyatakan dalam Rupiah kecuali dinyatakan lain)');

         $sheet->getStyle('A1:B4')->applyFromArray(array(
            'font' => array(
               'bold' => true
            ), 'alignment' => array(
               'wrap' => true,
               'shrinkToFit' => true
            )
         ));

         foreach($gridList as $key => $value)
         {
            if ($value['status'] == 'Ya') {
               $aktiva[$value['kelJnsNama']][] = array(
                  "nama_kel_lap" => $value['nama_kel_lap'],
                  "nilai" => $value['nilai'],
                  "kelJnsNama" => $value['kelJnsNama'],
                  "kellapId" => $value['kellapId']
               );
            } else {
               $kewajiban[$value['kelJnsNama']][] = array(
                  "nama_kel_lap" => $value['nama_kel_lap'],
                  "nilai" => $value['nilai'],
                  "kelJnsNama" => $value['kelJnsNama'],
                  "kellapId" => $value['kellapId']
               );
            }
         }

         $totalAktiva = 0;
         $totalKewajiban = 0;

         $row  = 6;
         $coll = 0;
         $sheet->setCellValueByColumnAndRow($coll, $row, GTFWConfiguration::GetValue('language','aset'));
         $sheet->mergeCells('A'.$row.':B'.$row);
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
         foreach ($aktiva as $key => $value)
         {
            $row++;//= 2;
            $jmlAktiva  = 0;
            $sheet->setCellValueByColumnAndRow(0, $row, $key);
            $sheet->setCellValueByColumnAndRow(1, $row, GTFWConfiguration::GetValue('language','jumlah_rp'));
            $sheet->getStyle('A'.$row.':B'.$row)->applyFromArray(array(
               'font' => array(
                  'bold' => true
               ), 'borders' => array(
                  'bottom' => array(
                     'style' => PHPExcel_Style_Border::BORDER_THICK
                  )
               )
            ));
            $rAwal      = $row+1;
            foreach ($value as $detilKey => $detilValue)
            {
               $row++;
               $sheet->setCellValueByColumnAndRow(0, $row, $detilValue['nama_kel_lap']);
               $sheet->getCellByColumnAndRow(1, $row)->setValueExplicit($detilValue['nilai'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $jmlAktiva+= $detilValue['nilai'];
            }
            $rAkhir= $row + 1;
            $row++;
            $sheet->setCellValueByColumnAndRow(0, $row, "Total ".$key);
            $rTotalA[]= 'B'.($row);
            $sheet->setCellValueByColumnAndRow(1, $row, '=SUM(B'.($rAwal).':B'.($rAkhir-1).')');
            $sheet->getStyle('B'.$rAwal.':B'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
            $sheet->getStyle('A'.$row.':B'.$row)->applyFromArray(array(
               'font' => array(
                  'bold' => true
               ), 'borders' => array(
                  'top' => array(
                     'style' => PHPExcel_Style_Border::BORDER_THIN
                  )
               )
            ));
            $row++;
            $totalAktiva+=$jmlAktiva;
         }

         /* untuk menghitung total */
         $row++;
         $coll          = 0;
         $sheet->setCellValueByColumnAndRow(0, $row,GTFWConfiguration::GetValue('language','jumlah_aktiva'));
         $coll++;
         if(!empty($rTotalA)){
            $totalFAktiva     = implode('+',$rTotalA);
         } else {
            $totalFAktiva     = '';
         }
         $sheet->getCellByColumnAndRow(1, $row)->setValueExplicit('=('.$totalFAktiva.')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->getRowDimension($row)->setRowHeight(18);
         $sheet->getStyle('A'.$row.':B'.$row)->applyFromArray(array(
            'font' => array(
               'bold' => true
            ), 'alignment' => array(
               'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ), 'borders' => array(
               'top' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THICK
               ), 'bottom' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THICK
               )
            )
         ));
         $sheet->getStyle('B'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
         /** end hitung total **/


         $row+= 2;
         $sheet->setCellValueByColumnAndRow(0, $row, GTFWConfiguration::GetValue('language','kewajiban_dan_aktiva_bersih'));
         $sheet->mergeCells('A'.$row.':B'.$row);
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
         foreach ($kewajiban as $key => $value)
         {
            $row++;//= 2;
            $jmlKewajiban=0;
            $sheet->setCellValueByColumnAndRow(0, $row, $key);
            $sheet->setCellValueByColumnAndRow(1, $row, GTFWConfiguration::GetValue('language','jumlah_rp'));
            $sheet->getStyle('A'.$row.':B'.$row)->applyFromArray(array(
               'font' => array(
                  'bold' => true
               ), 'borders' => array(
                  'bottom' => array(
                     'style' => PHPExcel_Style_Border::BORDER_THICK
                  )
               )
            ));
            $rAwal         = $row+1;
            foreach ($value as $detilKey => $detilValue)
            {
               $row++;
               $sheet->setCellValueByColumnAndRow(0, $row, $detilValue['nama_kel_lap']);
               $sheet->getCellByColumnAndRow(1, $row)->setValueExplicit($detilValue['nilai'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $jmlKewajiban+= $detilValue['nilai'];
            }
            $rAkhir  = $row;
            $row++;
            $sheet->setCellValueByColumnAndRow(0, $row, "Total ".$key);
            $rTotalK[]= 'B'.($row);
            $sheet->setCellValueByColumnAndRow(1, $row, '=SUM(B'.$rAwal.':B'.$rAkhir.')');
            $sheet->getStyle('B'.$rAwal.':B'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
            $sheet->getStyle('A'.$row.':B'.$row)->applyFromArray(array(
               'font' => array(
                  'bold' => true
               ), 'borders' => array(
                  'top' => array(
                     'style' => PHPExcel_Style_Border::BORDER_THIN
                  )
               )
            ));
            $row++;
            $totalKewajiban+=$jmlKewajiban;
         }

         /* untuk menghitung total */
         $row++;
         $sheet->setCellValueByColumnAndRow(0, $row,GTFWConfiguration::GetValue('language','jumlah_kewajiban_dan_aktiva_bersih'));
         if(!empty($rTotalK)){
            $totalFKewajiban = implode('+',$rTotalK);
         } else {
            $totalFKewajiban = '';
         }
         $sheet->getCellByColumnAndRow(1, $row)->setValueExplicit('=('.$totalFKewajiban.')', PHPExcel_Cell_DataType::TYPE_FORMULA);

         $sheet->getRowDimension($row)->setRowHeight(18);
         $sheet->getStyle('A'.$row.':B'.$row)->applyFromArray(array(
            'font' => array(
               'bold' => true
            ), 'alignment' => array(
               'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ), 'borders' => array(
               'top' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THICK
               ), 'bottom' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THICK
               )
            )
         ));
         $sheet->getStyle('B'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
         /** end hitung total **/
      }

      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>