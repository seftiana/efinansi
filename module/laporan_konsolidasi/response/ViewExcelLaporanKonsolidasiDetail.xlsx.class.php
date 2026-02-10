<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/laporan_konsolidasi/business/AppLaporanKonsolidasi.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'main/function/date.php';

class ViewExcelLaporanKonsolidasiDetail extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      $Obj        = new AppLaporanKonsolidasi();
      $tgl_awal   = Dispatcher::Instance()->Decrypt($_GET['tgl_awal']);
      $tgl_akhir  = Dispatcher::Instance()->Decrypt($_GET['tgl_akhir']);

      $gridList   = $Obj->GetLaporanAllDetil($tgl_awal,$tgl_akhir,'00');
      $dataYayasan   = $Obj->GetLaporanAllDetil($tgl_awal,$tgl_akhir,'01');
      $saldoBerjalan = $Obj->GetSaldoBerjalan($tgl_akhir,'00');
      $saldoBerjalanYayasan = $Obj->GetSaldoBerjalan($tgl_akhir,'01');

      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('laporan_konsolidasi_detail_' . date("d-m-Y") . '.xls');

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
         $sheet->getColumnDimension('A')->setWidth(60);
         $sheet->getColumnDimension('B')->setWidth(10);
         $sheet->getColumnDimension('C')->setWidth(20);
         $sheet->getColumnDimension('D')->setWidth(20);
         $sheet->getColumnDimension('E')->setWidth(20);
         $sheet->getColumnDimension('F')->setWidth(20);
         $sheet->getColumnDimension('G')->setWidth(20);
         $sheet->getColumnDimension('H')->setWidth(20);
         $sheet->getColumnDimension('I')->setWidth(20);

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
         $sheet->setCellValueByColumnAndRow(0, 2, 'Laporan Posisi Keuangan Detil');
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

        foreach ($gridList as $key => $value) {
            
                
            if($value['rl_awal'] === '1') { 
               $value['nilai'] += ($saldoBerjalan > 0 ? ($saldoBerjalan * -1) :  $saldoBerjalan);
            }

            if($value['rl_berjalan'] === '1') {
               $value['nilai'] += ($saldoBerjalan > 0 ?  $saldoBerjalan : ($saldoBerjalan * -1));
            }

            if($dataYayasan[$key]['rl_awal'] === '1') { 
               $dataYayasan[$key]['nilai'] += ($saldoBerjalanYayasan > 0 ? ($saldoBerjalanYayasan * -1) :  $saldoBerjalanYayasan);
            }

            if($dataYayasan[$key]['rl_berjalan'] === '1') {
               $dataYayasan[$key]['nilai'] += ($saldoBerjalanYayasan > 0 ?  $saldoBerjalanYayasan : ($saldoBerjalanYayasan * -1));
            }

            $totalPerKelJns[$value['kellapId']] += $value['nilai'];
            $totalPerKelJnsYayasan[$value['kellapId']] += $dataYayasan[$key]['nilai'];

            if ($value['status'] == 'Ya') {
                $totalKelJns[$value['kellapId']] += $value['nilai'];
                $aktiva[$value['kelJnsNama']][] = array(
                    "nama_kel_lap" => $value['nama_kel_lap'],
                    "kode_coa" => $value['kode_coa'],
                    "nama_coa" => $value['nama_coa'],
                    "nilai" => $value['nilai'],
                    "nilai_yayasan" => $dataYayasan[$key]['nilai'],
                    "kelJnsNama" => $value['kelJnsNama'],
                    "kellapId" => $value['kellapId']
                );
            } else {
                $kewajiban[$value['kelJnsNama']][] = array(
                    "nama_kel_lap" => $value['nama_kel_lap'],
                    "kode_coa" => $value['kode_coa'],
                    "nama_coa" => $value['nama_coa'],
                    "nilai" => $value['nilai'],
                    "nilai_yayasan" => $dataYayasan[$key]['nilai'],
                    "kelJnsNama" => $value['kelJnsNama'],
                    "kellapId" => $value['kellapId']
                );
            }    
        }

         $sheet->mergeCells('A6:A7');
         $sheet->mergeCells('B6:B7');
         $sheet->mergeCells('C6:D6');
         $sheet->mergeCells('E6:E7');
         $sheet->mergeCells('F6:G6');
         $sheet->mergeCells('H6:H7');
         $sheet->mergeCells('I6:I7');

         $sheet->setCellValue('A6','Description');
         $sheet->setCellValue('B6','WP Reff');
         $sheet->setCellValue('C6','UNIT');
         $sheet->setCellValue('E6','Combined');
         $sheet->setCellValue('F6','Elimitation Entry');
         $sheet->setCellValue('H6','Bal Per Audit');
         $sheet->setCellValue('I6','Bal Per Audit');
         $sheet->setCellValue('C7','STIE');
         $sheet->setCellValue('D7','Sekretariat');
         $sheet->setCellValue('F7','Debit');
         $sheet->setCellValue('G7','Credit');

         $totalAktiva = 0;
         $totalKewajiban = 0;

         $row  = 8;
         $coll = 0;
         $sheet->setCellValueByColumnAndRow($coll, $row, GTFWConfiguration::GetValue('language','aset'));
         $sheet->mergeCells('A'.$row.':I'.$row);
         $sheet->getStyle('A'.$row.':I'.$row)->applyFromArray(array(
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
            $jmlAktivaYayasan  = 0;
            $sheet->setCellValueByColumnAndRow(0, $row, $key);
            $sheet->getStyle('A'.$row.':I'.$row)->applyFromArray(array(
               'font' => array(
                  'bold' => true
               ), 'borders' => array(
                  'bottom' => array(
                     'style' => PHPExcel_Style_Border::BORDER_THICK
                  )
               )
            ));
            $rAwal      = $row+1;
            $kelapId = null;
            for ($k = 0; $k < sizeof($value); ) {           
                if($value[$k]['kellapId'] ==  $kelapId) {                   
                    $row++;
                    $jmlAktiva+= $value[$k]['nilai'];
                    $jmlAktivaYayasan+= $value[$k]['nilai_yayasan'];
                    $sheet->setCellValueByColumnAndRow(0, $row,  $value[$k]['kode_coa']. ' - '.$value[$k]['nama_coa']);
                    $sheet->getCellByColumnAndRow(2, $row)->setValueExplicit($value[$k]['nilai'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $sheet->getCellByColumnAndRow(3, $row)->setValueExplicit($value[$k]['nilai_yayasan'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $sheet->setCellValueByColumnAndRow(4, $row, '=SUM(C'.($row).':D'.($row).')', PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $k++;
                } elseif($kelapId != $value[$k]['kellapId'])  {
                     $kelapId =  $value[$k]['kellapId'];                    
                     $row++;
                     $sheet->setCellValueByColumnAndRow(0, $row, $value[$k]['nama_kel_lap']);
                     
                     if(array_key_exists($kelapId, $totalPerKelJns)) {
                        $nilai =  $totalPerKelJns[$kelapId];
                     } else {
                        $nilai = 0;
                     }
                     if(array_key_exists($kelapId, $totalPerKelJnsYayasan)) {
                        $nilaiYayasan =  $totalPerKelJnsYayasan[$kelapId];
                     } else {
                        $nilaiYayasan = 0;
                     }

                    $sheet->getCellByColumnAndRow(2, $row)->setValueExplicit($nilai, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $sheet->getCellByColumnAndRow(3, $row)->setValueExplicit($nilaiYayasan, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $sheet->setCellValueByColumnAndRow(4, $row, '=SUM(C'.($row).':D'.($row).')', PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $sheet->getStyle('A' . $row . ':I' . $row)->getFont()->setBold(true);
                }
            }

            $rAkhir= $row + 1;
            $row++;
            $sheet->setCellValueByColumnAndRow(0, $row, "Total ".$key);
            $rTotalA[]= 'C'.($row);
            $rTotalYayasan[]= 'D'.($row);
            $rTotalGabung[]= 'E'.($row);
            $sheet->setCellValueByColumnAndRow(2, $row, $jmlAktiva);
            $sheet->setCellValueByColumnAndRow(3, $row, $jmlAktivaYayasan);
            $sheet->setCellValueByColumnAndRow(4, $row, '=SUM(C'.($row).':D'.($row).')', PHPExcel_Cell_DataType::TYPE_NUMERIC);

            $sheet->getStyle('C'.$rAwal.':I'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
            $sheet->getStyle('A'.$row.':I'.$row)->applyFromArray(array(
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
         if(!empty($rTotalYayasan)){
            $totalFAktivaYayasan    = implode('+',$rTotalYayasan);
         } else {
            $totalFAktivaYayasan     = '';
         }
         if(!empty($rTotalGabung)){
            $totalFAktivaGabung    = implode('+',$rTotalGabung);
         } else {
            $totalFAktivaGabung= '';
         }

         $sheet->getCellByColumnAndRow(2, $row)->setValueExplicit('=('.$totalFAktiva.')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->getCellByColumnAndRow(3, $row)->setValueExplicit('=('.$totalFAktivaYayasan.')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->getCellByColumnAndRow(4, $row)->setValueExplicit('=('.$totalFAktivaGabung.')', PHPExcel_Cell_DataType::TYPE_FORMULA);

         $sheet->getRowDimension($row)->setRowHeight(18);
         $sheet->getStyle('A'.$row.':I'.$row)->applyFromArray(array(
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
         $sheet->getStyle('C'.$row.':I'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
         /** end hitung total **/


         $row+= 2;
         $sheet->setCellValueByColumnAndRow(0, $row, GTFWConfiguration::GetValue('language','kewajiban_dan_aktiva_bersih'));
         $sheet->mergeCells('A'.$row.':I'.$row);
         $sheet->getStyle('A'.$row.':I'.$row)->applyFromArray(array(
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
            $jmlKewajibanYayasan=0;
            $sheet->setCellValueByColumnAndRow(0, $row, $key);
            $sheet->getStyle('A'.$row.':I'.$row)->applyFromArray(array(
               'font' => array(
                  'bold' => true
               ), 'borders' => array(
                  'bottom' => array(
                     'style' => PHPExcel_Style_Border::BORDER_THICK
                  )
               )
            ));
            $rAwal         = $row+1;
            $kelapId = null;
            for ($k = 0; $k < sizeof($value); ) {           
                if($value[$k]['kellapId'] ==  $kelapId) {                   
                    $row++;
                    $jmlKewajiban+= $value[$k]['nilai'];
                    $jmlKewajibanYayasan+= $value[$k]['nilai_yayasan'];
                    $sheet->setCellValueByColumnAndRow(0, $row,  $value[$k]['kode_coa']. ' - '.$value[$k]['nama_coa']);
                    $sheet->getCellByColumnAndRow(2, $row)->setValueExplicit($value[$k]['nilai'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $sheet->getCellByColumnAndRow(3, $row)->setValueExplicit($value[$k]['nilai_yayasan'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $sheet->setCellValueByColumnAndRow(4, $row, '=SUM(C'.($row).':D'.($row).')', PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $k++;
                } elseif($kelapId != $value[$k]['kellapId'])  {
                    $kelapId =  $value[$k]['kellapId'];                    
                    $row++;
                    $sheet->setCellValueByColumnAndRow(0, $row, $value[$k]['nama_kel_lap']);
                    if(array_key_exists($kelapId, $totalPerKelJns)) {
                        $nilai =  $totalPerKelJns[$kelapId];
                    } else {
                        $nilai = 0;
                    }
                    
                     if(array_key_exists($kelapId, $totalPerKelJnsYayasan)) {
                        $nilaiYayasan =  $totalPerKelJnsYayasan[$kelapId];
                     } else {
                        $nilaiYayasan = 0;
                     }

                    $sheet->getCellByColumnAndRow(2, $row)->setValueExplicit($nilai, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $sheet->getCellByColumnAndRow(3, $row)->setValueExplicit($nilaiYayasan, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $sheet->setCellValueByColumnAndRow(4, $row, '=SUM(C'.($row).':D'.($row).')', PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $sheet->getStyle('A' . $row . ':I' . $row)->getFont()->setBold(true);
                }                
            }
            
            $rAkhir  = $row;
            $row++;
            $sheet->setCellValueByColumnAndRow(0, $row, "Total ".$key);
            $rTotalK[]= 'C'.($row);
            $rTotalKYayasan[]= 'D'.($row);
            $rTotalKGabung[]= 'E'.($row);
            $sheet->setCellValueByColumnAndRow(2, $row, $jmlKewajiban);
            $sheet->setCellValueByColumnAndRow(3, $row, $jmlKewajibanYayasan);
            $sheet->setCellValueByColumnAndRow(4, $row, '=SUM(C'.($row).':D'.($row).')', PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getStyle('C'.$rAwal.':I'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
            $sheet->getStyle('A'.$row.':I'.$row)->applyFromArray(array(
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
         if(!empty($rTotalKYayasan)){
            $totalFKewajibanYayasan    = implode('+',$rTotalKYayasan);
         } else {
            $totalFKewajibanYayasan     = '';
         }
         if(!empty($rTotalGabung)){
            $totalFKewajibanGabung    = implode('+',$rTotalKGabung);
         } else {
            $totalFKewajibanGabung= '';
         }

         $sheet->getCellByColumnAndRow(2, $row)->setValueExplicit('=('.$totalFKewajiban.')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->getCellByColumnAndRow(3, $row)->setValueExplicit('=('.$totalFKewajibanYayasan.')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->getCellByColumnAndRow(4, $row)->setValueExplicit('=('.$totalFKewajibanGabung.')', PHPExcel_Cell_DataType::TYPE_FORMULA);

         $sheet->getRowDimension($row)->setRowHeight(18);
         $sheet->getStyle('A'.$row.':I'.$row)->applyFromArray(array(
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
         $sheet->getStyle('C'.$row.':I'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
         /** end hitung total **/
      }

      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>