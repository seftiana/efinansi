<?php
/**
* ================= doc ====================
* FILENAME     : ViewExcelLapMonitoringAnggaran.xlsx.class.php
* @package     : ViewExcelLapMonitoringAnggaran
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-07-17
* @Modified    : 2014-07-17
* @Analysts    : Dyah Fajar N
* @contact     : eko.susilo@gamatechno.com
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/lap_monitoring_anggaran/business/AppLapMonitoringAnggaran.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'main/function/date.php';
class ViewExcelLapMonitoringAnggaran extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('laporan_monitoring_anggaran_'.date('Ymd', time()).'.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(false); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle('Worksheet Name');
      # set font default
      $sheet->getDefaultStyle()->getFont()->setName('Arial')->setSize('10');
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

      $mObj          = new AppLapMonitoringAnggaran();
      $arrPeriodeTahun  = $mObj->GetPeriodeTahun();
      $periodeTahun     = $mObj->GetPeriodeTahun(array(
         'active' => true
      ));
      $requestData['ta_id']            = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
      $requestData['program_id']       = Dispatcher::Instance()->Decrypt($mObj->_GET['program_id']);
      $requestData['program_nama']     = Dispatcher::Instance()->Decrypt($mObj->_GET['program_nama']);
      $requestData['unit_id']          = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
      $requestData['unit_nama']        = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);
      $requestData['jenis_kegiatan']   = Dispatcher::Instance()->Decrypt($mObj->_GET['jenis_kegiatan']);
      $requestData['bulan']            = Dispatcher::Instance()->Decrypt($mObj->_GET['bulan']);
      $dataUnit                        = $mObj->ChangeKeyName($mObj->GetUnitIdentity($requestData['unit_id']));
      $requestData['pimpinan_unit']    = ($dataUnit['unitkerja_nama_pimpinan'] == '') ? str_repeat('.', 50) : $dataUnit['unitkerja_nama_pimpinan'];

      if($requestData['bulan'] != 0){
            $requestData['nama_bulan']  = $mObj->indonesianMonth[$requestData['bulan']]['name'];
      } else {
            $requestData['nama_bulan'] = 'Semua Bulan';
      }

      foreach ($arrPeriodeTahun as $ta) {
         if((int)$ta['id'] === (int)$requestData['ta_id']){
            $requestData['ta_nama']    = $ta['name'];
         }
      }

      $offset        = 0;
      $limit         = 10000;
      $dataList      = $mObj->GetData($offset, $limit, (array)$requestData);
      $total_data    = $mObj->GetCountData();
      $dataProgram   = $mObj->ChangeKeyName($mObj->GetProgramById($requestData['program_id']));
      $date          = $mObj->indonesianDate(date('Y-m-d', time()));
      if(empty($dataProgram)){
         $requestData['program_nama']  = $dataList[0]['programNama'];
      }else{
         $requestData['program_nama']  = $dataProgram['nama'];
      }

      if(empty($dataList)){
         $sheet->setCellValue('A1', 'Data Kosong');
      }else{
         $dataList         = $mObj->ChangeKeyName($dataList);
         $program          = '';
         $kegiatan         = '';
         $dataGrid         = array();
         $dataMonitoring   = array();
         $index            = 0;

         for ($i=0; $i < count($dataList);) {
            if((int)$dataList[$i]['program_id'] === (int)$program && (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan){
               $programKodeSistem      = $program;
               $kegiatanKodeSistem     = $program.'.'.$kegiatan;

               // ========================= PROGRAM =========================== //

               $dataMonitoring[$programKodeSistem]['nominal_usulan']     += $dataList[$i]['nominal_usulan'];
               $dataMonitoring[$programKodeSistem]['nominal_setuju']     += $dataList[$i]['nominal_setuju'];
               $dataMonitoring[$programKodeSistem]['nominal_revisi']  += $dataList[$i]['nominal_revisi'];
               $dataMonitoring[$programKodeSistem]['nominal_setelah_revisi']  += $dataList[$i]['nominal_setelah_revisi'];

               if(isset($dataList[$i]['status_approve']) || strtoupper($dataList[$i]['status_approve'] = 'YA') || strtoupper($dataList[$i]['status_approve'] = 'TIDAK')){ 
                  $dataMonitoring[$programKodeSistem]['nominal_pencairan']  += $dataList[$i]['nominal_pencairan_belum'] + $dataList[$i]['nominal_pencairan_ya'] + $dataList[$i]['nominal_pencairan_tidak'];
               }else{ 
                  $dataMonitoring[$programKodeSistem]['nominal_pencairan']  += $dataList[$i]['nominal_pencairan_belum'] + $dataList[$i]['nominal_pencairan_ya'] + $dataList[$i]['nominal_pencairan_tidak'];
               }

               $dataMonitoring[$programKodeSistem]['nominal_realisasi']     += $dataList[$i]['nominal_realisasi'];

               if(isset($dataList[$i]['status_approve']) || strtoupper($dataList[$i]['status_approve'] = 'YA') || strtoupper($dataList[$i]['status_approve'] = 'TIDAK')){
                  $dataMonitoring[$programKodeSistem]['sisa_dana']          += $dataList[$i]['nominal_setelah_revisi'] - $dataList[$i]['nominal_pencairan_ya'] - $dataList[$i]['nominal_pencairan_belum'] - $dataList[$i]['nominal_pencairan_tidak'];
               }else{
                  $dataMonitoring[$programKodeSistem]['sisa_dana']          += 0;
               }

               // ========================= KEGIATAN =========================== //

               $dataMonitoring[$kegiatanKodeSistem]['nominal_usulan']     += $dataList[$i]['nominal_usulan'];
               $dataMonitoring[$kegiatanKodeSistem]['nominal_setuju']     += $dataList[$i]['nominal_setuju'];
               $dataMonitoring[$kegiatanKodeSistem]['nominal_revisi']  += $dataList[$i]['nominal_revisi'];
               $dataMonitoring[$kegiatanKodeSistem]['nominal_setelah_revisi']  += $dataList[$i]['nominal_setelah_revisi'];

               if(isset($dataList[$i]['status_approve']) || strtoupper($dataList[$i]['status_approve'] = 'YA') || strtoupper($dataList[$i]['status_approve'] = 'TIDAK')){
                  $dataMonitoring[$kegiatanKodeSistem]['nominal_pencairan']  += $dataList[$i]['nominal_pencairan_belum'] + $dataList[$i]['nominal_pencairan_ya'] + $dataList[$i]['nominal_pencairan_tidak'];
               }else{
                  $dataMonitoring[$kegiatanKodeSistem]['nominal_pencairan']  += $dataList[$i]['nominal_pencairan_belum'] + $dataList[$i]['nominal_pencairan_ya'] + $dataList[$i]['nominal_pencairan_tidak'];
               }

               $dataMonitoring[$kegiatanKodeSistem]['nominal_realisasi']     += $dataList[$i]['nominal_realisasi'];

               if(isset($dataList[$i]['status_approve']) || strtoupper($dataList[$i]['status_approve'] = 'YA') || strtoupper($dataList[$i]['status_approve'] = 'TIDAK')){
                  $dataMonitoring[$kegiatanKodeSistem]['sisa_dana']          += $dataList[$i]['nominal_setelah_revisi'] - $dataList[$i]['nominal_pencairan_ya'] - $dataList[$i]['nominal_pencairan_belum'] - $dataList[$i]['nominal_pencairan_tidak'];
               }else{
                  $dataMonitoring[$kegiatanKodeSistem]['sisa_dana']          += $dataList[$i]['nominal_setelah_revisi'] - $dataList[$i]['nominal_pencairan_ya'] - $dataList[$i]['nominal_pencairan_belum'] - $dataList[$i]['nominal_pencairan_tidak'];;
               }

               // ========================= DATA =========================== //

               $dataGrid[$index]['kode']        = $dataList[$i]['sub_kegiatan_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['sub_kegiatan_nama'];
               $dataGrid[$index]['unit_kode']   = $dataList[$i]['unit_kode'];
               $dataGrid[$index]['unit_nama']   = $dataList[$i]['unit_nama'];
               $dataGrid[$index]['status']      = strtoupper($dataList[$i]['status']);
               $dataGrid[$index]['nominal_usulan']    = $dataList[$i]['nominal_usulan'];
               $dataGrid[$index]['nominal_setuju']    = $dataList[$i]['nominal_setuju'];
               $dataGrid[$index]['nominal_revisi']    = $dataList[$i]['nominal_revisi'];
               $dataGrid[$index]['nominal_setelah_revisi']    = $dataList[$i]['nominal_setelah_revisi'];

               if(isset($dataList[$i]['status_approve']) && strtoupper($dataList[$i]['status_approve'] = 'YA')){ // Belum Membuat FPA / Memiliki FPA yang BELUM dan SUDAH disetujui
                  $dataGrid[$index]['nominal_pencairan']  += $dataList[$i]['nominal_pencairan_belum'] + $dataList[$i]['nominal_pencairan_ya'];
               }elseif(isset($dataList[$i]['status_approve'])){ // BELUM Disetujui
                  $dataGrid[$index]['nominal_pencairan']  += $dataList[$i]['nominal_pencairan_belum'];
               }elseif(strtoupper($dataList[$i]['status_approve'] = 'YA')){ // SUDAH Disetujui
                  $dataGrid[$index]['nominal_pencairan']  += $dataList[$i]['nominal_pencairan_ya'];
               }else{ // TIDAK Disetujui
                  $dataGrid[$index]['nominal_pencairan']  += $dataList[$i]['nominal_pencairan_tidak'];
               }

               $dataGrid[$index]['nominal_realisasi'] = $dataList[$i]['nominal_realisasi'];

               if(isset($dataList[$i]['status_approve']) && strtoupper($dataList[$i]['status_approve'] = 'YA')){ // Belum Membuat FPA / Memiliki FPA yang BELUM dan SUDAH disetujui
                  $dataGrid[$index]['total_fpa']          += $dataList[$i]['nominal_pencairan_ya'] + $dataList[$i]['nominal_pencairan_belum']; 
                  $dataGrid[$index]['sisa_dana']          += $dataList[$i]['nominal_setelah_revisi'] - $dataGrid[$index]['total_fpa'];
               }elseif(isset($dataList[$i]['status_approve'])){ // BELUM Disetujui
                  $dataGrid[$index]['sisa_dana']          += $dataList[$i]['nominal_setelah_revisi'] - $dataList[$i]['nominal_pencairan_belum'];
               }elseif(strtoupper($dataList[$i]['status_approve'] = 'YA')){ // SUDAH Disetujui
                  $dataGrid[$index]['sisa_dana']          += $dataList[$i]['nominal_setelah_revisi'] - $dataList[$i]['nominal_pencairan_ya'];
               }else{ // TIDAK Disetujui
                  $dataGrid[$index]['sisa_dana']          += $dataList[$i]['nominal_setelah_revisi'] - $dataList[$i]['nominal_pencairan_tidak'];
               }

               $dataGrid[$index]['deskripsi']   = $dataList[$i]['deskripsi'];
               $dataGrid[$index]['type']        = 'sub_kegiatan';
               $dataGrid[$index]['class_name']  = ($start % 2 <> 0) ? 'table-common-even' : '';
               $dataGrid[$index]['row_style']   = '';
               $i++;
               $start++;
            }elseif ((int)$dataList[$i]['program_id'] === (int)$program && (int)$dataList[$i]['kegiatan_id'] !== (int)$kegiatan) {
               $kegiatan      = (int)$dataList[$i]['kegiatan_id'];
               $kodeSistem    = $program.'.'.$kegiatan;
               $dataMonitoring[$kodeSistem]['nominal_usulan']     = 0;
               $dataMonitoring[$kodeSistem]['nominal_setuju']     = 0;
               $dataMonitoring[$kodeSistem]['nominal_revisi']  = 0;
               $dataMonitoring[$kodeSistem]['nominal_setelah_revisi']  = 0;
               $dataMonitoring[$kodeSistem]['nominal_pencairan']  = 0;
               $dataMonitoring[$kodeSistem]['nominal_realisasi']  = 0;
               $dataMonitoring[$kodeSistem]['sisa_dana']          = 0;

               $dataGrid[$index]['kode']        = $dataList[$i]['kegiatan_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['kegiatan_nama'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['type']        = 'kegiatan';
            }else{
               $program       = (int)$dataList[$i]['program_id'];
               $kodeSistem    = $program;
               $dataMonitoring[$kodeSistem]['nominal_usulan']     = 0;
               $dataMonitoring[$kodeSistem]['nominal_setuju']     = 0;
               $dataMonitoring[$kodeSistem]['nominal_revisi']  = 0;
               $dataMonitoring[$kodeSistem]['nominal_setelah_revisi']  = 0;
               $dataMonitoring[$kodeSistem]['nominal_pencairan']  = 0;
               $dataMonitoring[$kodeSistem]['nominal_realisasi']  = 0;
               $dataMonitoring[$kodeSistem]['sisa_dana']          = 0;

               $dataGrid[$index]['kode']        = $dataList[$i]['program_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['program_nama'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['type']        = 'program';
            }
            $index++;
         }

         $headerStyle         = array(
            'font' => array(
               'size' => 14,
               'bold' => true
            ), 'alignment' => array(
               'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
               'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
         );

         $borderTableStyledArray = array(
            'borders' => array(
               'allborders' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN,
                  'color' => array('argb' => 'ff000000')
               )
            )
         );
         $styledTableHeaderArray = array(
            'borders' => array(
               'allborders' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN,
                  'color' => array('argb' => 'ff000000')
               )
            ),
            'fill' => array(
               'type' => PHPExcel_Style_Fill::FILL_SOLID,
               'startcolor' => array(
                  'argb' => 'ffE6E6E6'
               )
            ),
            'font' => array(
               'bold' => true,
               'color' => array(
                  'rgb' => '000000'
               )
            ),
            'alignment' => array(
               'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
               'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
         );

         $sheet->mergeCells('A1:K1');
         $sheet->mergeCells('B3:I3');
         $sheet->mergeCells('B4:I4');
         $sheet->mergeCells('B5:I5');
         $sheet->mergeCells('A8:B8');
         // $sheet->mergeCells('B7:B7');
         $sheet->mergeCells('C8:C9');
         $sheet->mergeCells('D8:D9');
         $sheet->mergeCells('E8:E9');
         $sheet->mergeCells('F8:F9');
         $sheet->mergeCells('G8:G9');
         $sheet->mergeCells('H8:H9');
         $sheet->mergeCells('I8:I9');
         $sheet->mergeCells('J8:J9');
         $sheet->mergeCells('K8:K9');
         $sheet->getRowDimension(1)->setRowHeight(20);
         $sheet->getColumnDimension('A')->setWidth(16);
         $sheet->getColumnDimension('B')->setWidth(28);
         $sheet->getColumnDimension('C')->setWidth(20);
         $sheet->getColumnDimension('D')->setWidth(45);
         $sheet->getColumnDimension('E')->setWidth(23);
         $sheet->getColumnDimension('F')->setWidth(23);
         $sheet->getColumnDimension('G')->setWidth(23);
         $sheet->getColumnDimension('H')->setWidth(23);
         $sheet->getColumnDimension('I')->setWidth(23);
         $sheet->getColumnDimension('J')->setWidth(23);
         $sheet->getColumnDimension('K')->setWidth(23);

         $sheet->getStyle('A1:K1')->applyFromArray($headerStyle);
         $sheet->getStyle('A2:K6')->getFont()->setBold(true);
         $sheet->getStyle('A8:K9')->applyFromArray($styledTableHeaderArray);

         $sheet->setCellValue('A1', GTFWConfiguration::GetValue('language', 'lap_monitoring_anggaran'));
         $sheet->setCellValue('A3', GTFWConfiguration::GetValue('language', 'tahun_periode'));
         $sheet->setCellValueExplicit('B3', $requestData['ta_nama'], PHPExcel_Cell_DataType::TYPE_STRING);
         $sheet->setCellValue('A4', GTFWConfiguration::GetValue('language', 'program'));
         $sheet->setCellValueExplicit('B4', $requestData['program_nama'], PHPExcel_Cell_DataType::TYPE_STRING);
         $sheet->setCellValue('A5', GTFWConfiguration::GetValue('language', 'unit').'/'.GTFWConfiguration::GetValue('language', 'sub_unit'));
         $sheet->setCellValueExplicit('B5', $requestData['unit_nama'], PHPExcel_Cell_DataType::TYPE_STRING);
         $sheet->setCellValue('A6', GTFWConfiguration::GetValue('language', 'bulan'));
         $sheet->setCellValueExplicit('B6', $requestData['nama_bulan'], PHPExcel_Cell_DataType::TYPE_STRING);

         $sheet->setCellValue('A8', GTFWConfiguration::GetValue('language', 'program').','.GTFWConfiguration::GetValue('language', 'kegiatan').','.GTFWConfiguration::GetValue('language', 'sub_kegiatan'));
         $sheet->setCellValue('A9', GTFWConfiguration::GetValue('language', 'kode'));
         $sheet->setCellValue('B9', GTFWConfiguration::GetValue('language', 'nama'));
         $sheet->setCellValue('C8', GTFWConfiguration::GetValue('language', 'unit').'/'.GTFWConfiguration::GetValue('language', 'sub_unit'));
         $sheet->setCellValue('D8', GTFWConfiguration::GetValue('language', 'deskripsi'));
         $sheet->setCellValue('E8', GTFWConfiguration::GetValue('language', 'nominal_usulan_rp'));
         $sheet->setCellValue('F8', GTFWConfiguration::GetValue('language', 'nominal_setuju_rp'));
         $sheet->setCellValue('G8', GTFWConfiguration::GetValue('language', 'nominal_revisi_rp'));
         $sheet->getStyle('H8')->getAlignment()->setWrapText(true)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
         $sheet->setCellValue('H8', GTFWConfiguration::GetValue('language', 'nominal_setelah_revisi_rp'));
         $sheet->setCellValue('I8', GTFWConfiguration::GetValue('language', 'nominal_fpa_rp'));
         $sheet->setCellValue('J8', GTFWConfiguration::GetValue('language', 'nominal_realisasi_rp'));
         $sheet->setCellValue('K8', GTFWConfiguration::GetValue('language', 'nominal_sisa_rp'));

         $rows       = 10;

         foreach ($dataGrid as $list) {
            switch (strtoupper($list['type'])) {
               case 'PROGRAM':
                  $list['nominal_usulan']    = $dataMonitoring[$list['kode_sistem']]['nominal_usulan'];
                  $list['nominal_setuju']    = $dataMonitoring[$list['kode_sistem']]['nominal_setuju'];
                  $list['nominal_revisi'] = $dataMonitoring[$list['kode_sistem']]['nominal_revisi'];
                  $list['nominal_setelah_revisi'] = $dataMonitoring[$list['kode_sistem']]['nominal_setelah_revisi'];
                  $list['nominal_pencairan'] = $dataMonitoring[$list['kode_sistem']]['nominal_pencairan'];
                  $list['nominal_realisasi'] = $dataMonitoring[$list['kode_sistem']]['nominal_realisasi'];
                  $list['sisa_dana']         = $dataMonitoring[$list['kode_sistem']]['sisa_dana'];
                  $unit_kerja_nama           = '-';
                  $sheet->getStyle('A'.$rows.':K'.$rows)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('00CCCCCC');
                  break;
               case 'KEGIATAN':
                  $list['nominal_usulan']    = $dataMonitoring[$list['kode_sistem']]['nominal_usulan'];
                  $list['nominal_setuju']    = $dataMonitoring[$list['kode_sistem']]['nominal_setuju'];
                  $list['nominal_revisi'] = $dataMonitoring[$list['kode_sistem']]['nominal_revisi'];
                  $list['nominal_setelah_revisi'] = $dataMonitoring[$list['kode_sistem']]['nominal_setelah_revisi'];
                  $list['nominal_pencairan'] = $dataMonitoring[$list['kode_sistem']]['nominal_pencairan'];
                  $list['nominal_realisasi'] = $dataMonitoring[$list['kode_sistem']]['nominal_realisasi'];
                  $list['sisa_dana']         = $dataMonitoring[$list['kode_sistem']]['sisa_dana'];
                  $unit_kerja_nama           = '-';
                  $sheet->getStyle('A'.$rows.':K'.$rows)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('00DCDCDC');
                  break;
               default:
                  $list['nominal_usulan']    = $list['nominal_usulan'];
                  $list['nominal_setuju']    = $list['nominal_setuju'];
                  $list['nominal_revisi'] = $list['nominal_revisi'];
                  $list['nominal_setelah_revisi'] = $list['nominal_setelah_revisi'];
                  $list['nominal_pencairan'] = $list['nominal_pencairan'];
                  $list['nominal_realisasi'] = $list['nominal_realisasi'];
                  $list['sisa_dana']         = $list['sisa_dana'];
                  $unit_kerja_nama           = $list['unit_nama'];
                  break;
            }

            $max  = max(array(
               ceil(strlen($unit_kerja_nama)/($sheet->getColumnDimension('C')->getWidth()+$sheet->getColumnDimension('C')->getWidth())),
               ceil(strlen($list['kode'])/$sheet->getColumnDimension('A')->getWidth()),
               ceil(strlen($list['nama'])/$sheet->getColumnDimension('B')->getWidth()),
               ceil(strlen($list['deskripsi'])/$sheet->getColumnDimension('D')->getWidth())
            ))*15.8;
            $sheet->getRowDimension($rows)->setRowHeight($max);
            $sheet->setCellValueExplicit('A'.$rows, $list['kode'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('B'.$rows, $list['nama']);
            $sheet->setCellValue('C'.$rows, $list['unit_nama']);
            $sheet->setCellValue('D'.$rows, $list['deskripsi']);
            $sheet->setCellValueExplicit('E'.$rows, $list['nominal_usulan'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('F'.$rows, $list['nominal_setuju'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('G'.$rows, $list['nominal_revisi'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('H'.$rows, $list['nominal_setelah_revisi'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('I'.$rows, $list['nominal_pencairan'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('J'.$rows, $list['nominal_realisasi'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('K'.$rows, $list['sisa_dana'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $rows++;
         }
         $sheet->getStyle('A9:K'.($rows-1))->getAlignment()->setWrapText(true)->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
         $sheet->getStyle('A9:K'.($rows-1))->applyFromArray($borderTableStyledArray);
         $sheet->getStyle('A9:A'.($rows-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $sheet->getStyle('E9:K'.($rows-1))->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');

         $rowSign       = $rows+2;
         $sheet->mergeCells('I'.($rowSign).':K'.($rowSign));
         $sheet->mergeCells('I'.($rowSign+1).':K'.($rowSign+1));
         $sheet->mergeCells('I'.($rowSign+2).':K'.($rowSign+2));
         $sheet->mergeCells('I'.($rowSign+3).':K'.($rowSign+6));
         $sheet->mergeCells('I'.($rowSign+8).':K'.($rowSign+8));
         $sheet->getStyle('I'.($rowSign+1).':K'.($rowSign+8))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
         $sheet->setCellValue('I'.$rowSign, GTFWConfiguration::GetValue('organization', 'city').', '.$date);
         $sheet->setCellValue('I'.($rowSign+1), GTFWConfiguration::GetValue('language', 'pimpinan_unit_kerja').'/'.GTFWConfiguration::GetValue('language', 'sub_unit'));
         $sheet->setCellValue('I'.($rowSign+2), $requestData['unit_nama']);
         $sheet->setCellValue('I'.($rowSign+8), '('.$requestData['pimpinan_unit'].')');
      }
      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>