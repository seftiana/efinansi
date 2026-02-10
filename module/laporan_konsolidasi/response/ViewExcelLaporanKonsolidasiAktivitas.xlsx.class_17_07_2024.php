<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/laporan_konsolidasi/business/AppLaporanKonsolidasi.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'main/function/date.php';

class ViewExcelLaporanKonsolidasiAktivitas extends XlsxResponse
{
   # Internal Variables
   public $Excel;
   protected $mObj;

   function ProcessRequest()
   {
      $this->mObj        = new AppLaporanKonsolidasi();
      $this->mObj->Setup(6);

      $GET = is_object($_GET) ? $_GET->AsArray() : $_GET;
      $tgl_awal   = Dispatcher::Instance()->Decrypt($GET['tanggal_awal']);
      $tgl_akhir  = Dispatcher::Instance()->Decrypt($GET['tanggal_akhir']);

      $this->mObj->LaporanBuilder()->PrepareData(
         $tgl_awal,
         $tgl_akhir,
         '00'
      );
      $dataInstitute = $this->mObj->LaporanBuilder()->laporanView();

      $this->mObj->LaporanBuilder()->PrepareData(
         $tgl_awal,
         $tgl_akhir,
         '01'
      );
      $dataYayasan = $this->mObj->LaporanBuilder()->laporanView();

      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('laporan_konsolidasi_aktivitas_' . date("d-m-Y") . '.xls');

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

      if (empty($dataInstitute)) {
         $sheet->setCellValue('A1', GTFWConfiguration::GetValue('language', 'data_kosong'));
      } else {
         $sheet->getColumnDimension('A')->setWidth(50);
         $sheet->getColumnDimension('B')->setWidth(10);
         $sheet->getColumnDimension('C')->setWidth(20);
         $sheet->getColumnDimension('D')->setWidth(20);
         $sheet->getColumnDimension('E')->setWidth(20);
         $sheet->getColumnDimension('F')->setWidth(20);
         $sheet->getColumnDimension('G')->setWidth(20);

         $sheet->getRowDimension(1)->setRowHeight(18);
         $sheet->getRowDimension(2)->setRowHeight(16);
         $sheet->getRowDimension(3)->setRowHeight(16);
         $sheet->getRowDimension(4)->setRowHeight(16);

         $sheet->mergeCells('A1:E1');
         $sheet->mergeCells('A2:E2');
         $sheet->mergeCells('A3:E3');
         $sheet->mergeCells('A4:E4');
         $sheet->setCellValueByColumnAndRow(0, 1, GTFWConfiguration::GetValue('organization', 'header_lap_all'));
         $sheet->setCellValueByColumnAndRow(0, 2, 'Laporan Konsolidasi Aktivitas');
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

         $sheet->mergeCells('A6:A7');
         $sheet->mergeCells('B6:B7');
         $sheet->mergeCells('C6:D6');
         $sheet->mergeCells('E6:E7');

         $sheet->setCellValue('A6','Description');
         $sheet->setCellValue('B6','WP Reff');
         $sheet->setCellValue('C6','UNIT');
         $sheet->setCellValue('E6','Combined');
         $sheet->setCellValue('C7','Institut Perbanas');
         $sheet->setCellValue('D7','Sekretariat');

         $row  = 8;
         $coll = 0;
         $space = " ";
         foreach ($dataInstitute as $key => $itemLaporan) {
            $itemLaporan['nama'] = str_repeat($space,$itemLaporan['level']-1).$itemLaporan['nama'];
            if ($itemLaporan['is_summary'] == 'Y') {
               $pengali = 1;
               $jumlahSaldoKlp = $itemLaporan['saldo_summary'] * $pengali;
               $jumlahSaldoyayasan = $dataYayasan[$key]['saldo_summary'] * $pengali;
               $jumlahGabung = $jumlahSaldoKlp + $jumlahSaldoyayasan;

               $sheet->getStyle('A'.$row.':E'.$row)->applyFromArray(array(
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
               $sheet->getStyle('C'.$row.':E'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
            } else {
               $pengali = ($itemLaporan['is_tambah'] == 'T') ? -1 : 1;
               $jumlahSaldoKlp = $itemLaporan['saldo'] * $pengali;
               $jumlahSaldoyayasan =$dataYayasan[$key]['saldo'] * $pengali;
               $jumlahGabung = $jumlahSaldoKlp + $jumlahSaldoyayasan;

               if ($itemLaporan['is_child'] == '0') {
                  $sheet->mergeCells('A'.$row.':E'.$row);
                  switch ($itemLaporan['level']) {
                     case '2':
                        $title = strtoupper($itemLaporan['nama']);
                        $sheet->getStyle('A'.$row.':E'.$row)->applyFromArray(array(
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
                        
                        break;
                     default :
                        $title = str_repeat($space,$itemLaporan['level']-2).$itemLaporan['nama'];
                        $sheet->getStyle('A'.$row.':E'.$row)->applyFromArray(array(
                           'font' => array(
                              'bold' => true
                           ), 'borders' => array(
                              'bottom' => array(
                                 'style' => PHPExcel_Style_Border::BORDER_THICK
                              )
                           )
                        ));
                        break;
                  }

                  $itemLaporan['nama'] = $title;
               } else {
                  $itemLaporan['nama'] = str_repeat($space,$itemLaporan['level']-1).$itemLaporan['nama'];
                  $sheet->getStyle('C'.$row.':E'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
               }
            }
            $sheet->setCellValueByColumnAndRow(0, $row, $itemLaporan['nama']);
            $sheet->setCellValueByColumnAndRow(2, $row, $jumlahSaldoKlp);
            $sheet->setCellValueByColumnAndRow(3, $row, $jumlahSaldoyayasan);
            $sheet->setCellValueByColumnAndRow(4, $row, $jumlahGabung);
            $row++;
         }
      }

      # Save Excel document to local hard disk
      $this->Save();
   }
}
