<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot').
   'module/laporan_calk/business/AppLaporanCalk.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot').
   'main/function/date.php';

class ViewExcelLaporanCalk extends XlsxResponse{
   # Internal Variables
   public $Excel;
   protected $mObj;

   public function ProcessRequest(){
      $this->mObj        = new AppLaporanCalk();
      $this->mObj->Setup();

      $get =  is_object($_GET) ? $_GET->AsArray() : $_GET;
      $tglAwal =  Dispatcher::Instance()->Decrypt($get['tanggal_awal']);
      $tglAkhir =  Dispatcher::Instance()->Decrypt($get['tanggal_akhir']);
      $subAccount = Dispatcher::Instance()->Decrypt($get['sub_account']);

      if($subAccount == '01-00-00-00-00-00-00'){
         $header = strtoupper(GTFWConfiguration::GetValue('organization', 'header_lap_yayasan'));
      }elseif($subAccount == '00-00-00-00-00-00-00'){
         $header = strtoupper(GTFWConfiguration::GetValue('organization', 'header_lap_perbanas'));
      }else{
         $header = strtoupper(GTFWConfiguration::GetValue('organization', 'company_name'));
      }

      $this->mObj->LaporanBuilder()->PrepareData($tglAwal, $tglAkhir,$subAccount);
      $posisiKeuangan = $this->mObj->LaporanBuilder()->laporanView();

      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('laporan_calk_' . date("d-m-Y") . '.xls');

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

      if (empty($posisiKeuangan)) {
         $sheet->setCellValue('A1', GTFWConfiguration::GetValue('language', 'data_kosong'));
      } else {
         $sheet->getColumnDimension('A')->setWidth(50);
         $sheet->getColumnDimension('B')->setWidth(20);
         $sheet->getColumnDimension('C')->setWidth(20);
         $sheet->getColumnDimension('D')->setWidth(10);

         $sheet->getRowDimension(1)->setRowHeight(18);
         $sheet->getRowDimension(2)->setRowHeight(16);
         $sheet->getRowDimension(3)->setRowHeight(16);
         $sheet->getRowDimension(4)->setRowHeight(16);

         $sheet->mergeCells('A1:B1');
         $sheet->mergeCells('A2:B2');
         $sheet->mergeCells('A3:B3');
         $sheet->mergeCells('A4:B4');
         $sheet->setCellValueByColumnAndRow(0, 1, $header);
         $sheet->setCellValueByColumnAndRow(0, 2, 'Laporan Posisi Keuangan');
         $sheet->setCellValueByColumnAndRow(0, 3, 'Untuk Interval Waktu Mulai '.
            IndonesianDate($tglAwal, 'yyyy-mm-dd').' s/d '.
            IndonesianDate($tglAkhir, 'yyyy-mm-dd')
         );
         $sheet->setCellValueByColumnAndRow(0, 4, '(Dinyatakan dalam Rupiah kecuali dinyatakan lain)');

         $sheet->getStyle('A1:B4')->applyFromArray(array(
            'font' => array(
               'bold' => true
            ), 'alignment' => array(
               'wrap' => true,
               'shrinkToFit' => true
            )
         ));

         $bulanIni = date("n", strtotime($tglAkhir));
         $bulanLalu = date("n", strtotime("first day of $tglAkhir -1 month"));

         $sheet->setCellValueByColumnAndRow(0, 6, 'URAIAN');
         $sheet->setCellValueByColumnAndRow(1, 6, $this->mObj->indonesianMonth[$bulanIni] );
         $sheet->setCellValueByColumnAndRow(2, 6, $this->mObj->indonesianMonth[$bulanLalu]);
         $sheet->setCellValueByColumnAndRow(3, 6, '%');

         $sheet->getStyle('A6:D6')->applyFromArray(array(
            'font' => array(
               'bold' => true
            ), 'alignment' => array(
               'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
               'wrap' => true
            )
         ));

         $row  = 7;
         $space = " ";

         foreach ($posisiKeuangan as $itemLaporan) {
            $isShowDet = false;
            $itemLaporan['nama'] = str_repeat($space,$itemLaporan['level']-1).$itemLaporan['nama'];
            if ($itemLaporan['is_summary'] == 'Y') {
               $pengali = 1;
               $jumlahSaldoKlp = $itemLaporan['saldo_summary'] * $pengali;
               $jumlahSaldoKlpBl = $itemLaporan['saldo_summary_lalu'] * $pengali;
               $sheet->getStyle('A'.$row.':D'.$row)->applyFromArray(array(
                  'font' => array(
                     'bold' => true
                  ), 'borders' => array(
                     'top' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                     ),'bottom' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THICK
                     )
                  )
               ));

               $sheet->getStyle('B'.$row.':C'.$row)->getNumberFormat()->setFormatCode(
                  '_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)'
               );
               $sheet->getStyle('D'.$row)->getNumberFormat()->setFormatCode('0%');
               
               $sheet->setCellValueByColumnAndRow(1, $row, $jumlahSaldoKlp);
               $sheet->setCellValueByColumnAndRow(2, $row, $jumlahSaldoKlpBl);
               $sheet->setCellValue('D'.$row, "=IF(C$row <> 0,(B$row-C$row)/C$row,0)");
            } else {
               $pengali = ($itemLaporan['is_tambah'] == 'T') ? -1 : 1;
               
               if ($itemLaporan['is_child'] == '0') {
                  $sheet->mergeCells('A'.$row.':D'.$row);
                  if($itemLaporan['level'] == '2') {
                     $title = strtoupper($itemLaporan['nama']);
                     $sheet->getStyle('A'.$row.':D'.$row)->applyFromArray(array(
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
                        
                  }else{
                     $title = str_repeat($space,$itemLaporan['level']-2).$itemLaporan['nama'];
                     $sheet->getStyle('A'.$row.':D'.$row)->applyFromArray(array(
                        'font' => array(
                           'bold' => true
                        ), 'borders' => array(
                           'bottom' => array(
                              'style' => PHPExcel_Style_Border::BORDER_THICK
                           )
                        )
                     ));
                  }

                  $itemLaporan['nama'] = $title;
               } else {
                  $itemLaporan['nama'] = str_repeat($space,$itemLaporan['level']-1).$itemLaporan['nama'];
                  $isShowDet = true;

                  $dataDetail = $this->mObj->LaporanBuilder()->getLaporanDetail(
                     $tglAwal, $tglAkhir, $itemLaporan['id'],$subAccount,$status
                  );
               }
            }
            $sheet->setCellValueByColumnAndRow(0, $row, $itemLaporan['nama']);
            $row++;
            if(!empty($dataDetail) && $isShowDet){
               $nomor = 1;
               $rowStart = $row;
               foreach($dataDetail as $valueDet){

                  $nominal = $valueDet['kellap_coa_saldo']*$pengali;
                  $nominalBl = $valueDet['kellap_coa_saldo_lalu']*$pengali;

                  $sheet->setCellValueByColumnAndRow(0, $row, str_repeat($space,$itemLaporan['level']+4).$nomor.'. '.$valueDet['kellap_coa_nama']);
                  $sheet->setCellValueByColumnAndRow(1, $row, $nominal);
                  $sheet->setCellValueByColumnAndRow(2, $row, $nominalBl);
                  $sheet->getStyle('A'.$row.':D'.$row)->applyFromArray(array(
                     'font' => array(
                        'italic' => true
                     )
                  ));

                  $colSekarang = 'B'.$row;
                  $colLalu = 'C'.$row;

                  $sheet->setCellValue('D'.$row, "=IF($colLalu<>0,($colSekarang-$colLalu)/$colLalu,0)");
                  $sheet->getStyle('D'.$row)->getNumberFormat()->setFormatCode('0%');
                  $sheet->getStyle('B'.$row.':C'.$row)->getNumberFormat()->setFormatCode(
                     '_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)'
                  );

                  $row++;
                  $nomor++;
               }
               $sheet->setCellValue('A'.$row, str_repeat($space,$itemLaporan['level']+2)."Total ".trim($itemLaporan['nama']));
               $sheet->setCellValue('B'.$row, "=SUM(B".$rowStart.':B'.($row-1).')');
               $sheet->setCellValue('C'.$row, "=SUM(C".$rowStart.':C'.($row-1).')');
               $sheet->setCellValue('D'.$row, "=IF(C$row <> 0,(B$row-C$row)/C$row,0)");

               $sheet->getStyle('D'.$row)->getNumberFormat()->setFormatCode('0%');

               $sheet->getStyle('B'.$row.':C'.$row)->getNumberFormat()->setFormatCode(
                  '_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)'
               );
               $row++;
            }

         }
      }

      # Save Excel document to local hard disk
      $this->Save();
   }
}
