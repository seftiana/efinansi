<?php
/**
* ================= doc ====================
* FILENAME     : ViewExcelUnitKerja.xlsx.class.php
* @package     : ViewExcelUnitKerja
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-07-22
* @Modified    : 2014-07-22
* @Analysts    : Dyah Fajar N
* @contact     : eko.susilo@gamatechno.com
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/lap_realisasi_anggaran_unitkerja/business/RekapUnitKerja.class.php';
class ViewExcelUnitKerja extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('realisasi_anggaran_unit_kerja_'.date('Ymd', time()).'.xls');

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
      $mObj          = new RekapUnitKerja();
      $requestData   = array();
      $requestData['ta_id']            = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
      $requestData['unit_id']          = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
      $requestData['unit_nama']        = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);
      $requestData['program_id']       = Dispatcher::Instance()->Decrypt($mObj->_GET['program_id']);
      $requestData['program_nama']     = Dispatcher::Instance()->Decrypt($mObj->_GET['program_nama']);
      $requestData['jenis_kegiatan']   = Dispatcher::Instance()->Decrypt($mObj->_GET['jenis_kegiatan']);
      $requestData['bulan']            = Dispatcher::Instance()->Decrypt($mObj->_GET['bulan']);
      $requestData['ta_nama']          = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_nama']);
      if(!empty($requestData['bulan'])){
            $namaBulan = $mObj->indonesianMonth[$requestData['bulan']]['name'];
      } else {
            $namaBulan = 'Semua Bulan';
      }
      
      $dataUnit         = $mObj->GetUnitIdentity($requestData['unit_id']);
      $dataUnit         = $dataUnit[0];
      $dataList         = $mObj->ChangeKeyName($mObj->GetData(0, 100000, (array)$requestData));
      $dataResume       = $mObj->ChangeKeyName($mObj->GetDataResume((array)$requestData));

      $pimpinan         = $dataUnit['unitkerjaNamaPimpinan'];
      if(empty($pimpinan)){
         $pimpinan      = str_repeat('.', 40);
      }
      $date             = date('Y-m-d', time());
      if(empty($pimpinan)){
         $pimpinan      = str_repeat('.', 50);
      }
      $tanggalCetak     = $mObj->indonesianDate($date);

      if(empty($dataList)){
         $sheet->setCellValue('A', 'data kosong');
      }else{

         $program       = '';
         $kegiatan      = '';
         $unit          = '';
         $dataGrid      = array();
         $index         = 0;
         $dataRekap     = array();

         for ($i=0; $i < count($dataList);) {
            if((int)$unit === (int)$dataList[$i]['unit_id'] && (int)$program === (int)$dataList[$i]['program_id'] && (int)$kegiatan === (int)$dataList[$i]['kegiatan_id']){
               $kodeProgram      = $unit.'.'.$program;
               $kodeKegiatan     = $unit.'.'.$program.'.'.$kegiatan;
               
               $dataGrid[$index]['id']    = $dataList[$i]['id'];
               $dataGrid[$index]['idp']    = $dataList[$i]['idp'];
               $dataGrid[$index]['np']    = $dataList[$i]['n_pencairan'];
               if(($i > 0) && ($dataList[$i - 1]['id'] == $dataList[$i]['id'])){
                        
                        $dataGrid[$index]['kode']  = '';
                        $dataGrid[$index]['nama']  = '';
                        $dataGrid[$index]['nominal_usulan']    = '';
                        $dataGrid[$index]['nominal_setuju']    = '';
                        $dataGrid[$index]['nominal_revisi']    = '';
                        $dataGrid[$index]['nominal_setelah_revisi']  = '';
                        $dataGrid[$index]['sisa_dana']         = '';
                        $dataGrid[$index]['nominal_lppa']  = '';
                        $dataGrid[$index]['nominal_lppa_sisa']  = '';
                } else {
                    $dataGrid[$index]['kode']  = $dataList[$i]['sub_kegiatan_kode'];
                    $dataGrid[$index]['nama']  = $dataList[$i]['sub_kegiatan_nama'];
                    $dataGrid[$index]['nominal_usulan']    = $dataList[$i]['nominal_usulan'];
                    $dataGrid[$index]['nominal_setuju']    = $dataList[$i]['nominal_setuju'];
                    $dataGrid[$index]['nominal_revisi']    = $dataList[$i]['nominal_revisi'];
                    $dataGrid[$index]['nominal_setelah_revisi']   = $dataList[$i]['nominal_setelah_revisi'];
                    $dataGrid[$index]['nominal_lppa']  = '';
                    $dataGrid[$index]['nominal_lppa_sisa']  = '';
                    $dataGrid[$index]['sisa_dana']         = ($dataList[$i]['nominal_setelah_revisi'] - $dataList[$i]['total_approve'] - $dataList[$i]['total_belum_approve'] == '') ? '0' : $dataList[$i]['nominal_setelah_revisi'] - $dataList[$i]['total_approve'] - $dataList[$i]['total_belum_approve'];
               }
               
               if(($i > 0) && ($dataList[$i - 1]['idp'] == $dataList[$i]['idp']) &&
                    !empty($dataList[$i]['idp']) ){
                    $dataGrid[$index]['no_pengajuan']       = '';
                    $dataGrid[$index]['nominal_pencairan']  = '';
                    $dataGrid[$index]['keterangan']         = ''; 
                } elseif(empty($dataList[$i]['idp'])){
                    $dataGrid[$index]['no_pengajuan']       = '';
                    $dataGrid[$index]['nominal_pencairan']  = 0;
                    $dataGrid[$index]['keterangan']         = ''; 
                } else {
                    $dataGrid[$index]['no_pengajuan']       = $dataList[$i]['no_pengajuan'];
                    if(isset($dataList[$i]['status_approve']) || strtoupper($dataList[$i]['status_approve'] = 'YA') || strtoupper($dataList[$i]['status_approve'] = 'TIDAK')){
                        $dataGrid[$index]['nominal_pencairan']  += $dataList[$i]['nominal_pencairan_belum'] + $dataList[$i]['nominal_pencairan_ya'] + $dataList[$i]['nominal_pencairan_tidak'];
                    }else{
                        $dataGrid[$index]['nominal_pencairan']  += $dataList[$i]['nominal_pencairan_belum'] + $dataList[$i]['nominal_pencairan_ya'] + $dataList[$i]['nominal_pencairan_tidak'];
                    }
                    $dataGrid[$index]['keterangan']         = $dataList[$i]['keterangan'];   
                    $dataGrid[$index]['nominal_lppa']       =  $dataList[$i]['nominal_lppa'];
                    $dataGrid[$index]['nominal_lppa_sisa']  =  0;
                    if($dataList[$i]['nominal_lppa'] > 0) {
                     $dataGrid[$index]['nominal_lppa_sisa']  =  $dataList[$i]['nominal_realisasi'] - $dataList[$i]['nominal_lppa'] ;
                    }
               }
               
               
               $dataGrid[$index]['nominal_realisasi']  = $dataList[$i]['nominal_realisasi'];
               $dataGrid[$index]['tanggal_transaksi']  = $dataList[$i]['tanggal_transaksi'];
               $dataGrid[$index]['no_bukti']           = $dataList[$i]['no_bukti'];                           
               
               
               $dataGrid[$index]['tipe']              = 'sub_kegiatan';
               $dataGrid[$index]['class_name']        = '';
               $dataGrid[$index]['row_style']         = '';


               $dataRekap[$kodeProgram]['nominal_usulan']      += $dataGrid[$index]['nominal_usulan'];
               $dataRekap[$kodeProgram]['nominal_setuju']      += $dataGrid[$index]['nominal_setuju'];
               $dataRekap[$kodeProgram]['nominal_revisi']      += $dataGrid[$index]['nominal_revisi'];
               $dataRekap[$kodeProgram]['nominal_setelah_revisi'] += $dataGrid[$index]['nominal_setelah_revisi'];

               if(isset($dataList[$i]['status_approve']) || strtoupper($dataList[$i]['status_approve'] = 'YA') || strtoupper($dataList[$i]['status_approve'] = 'TIDAK')){
                  $dataRekap[$kodeProgram]['nominal_pencairan']  += $dataList[$i]['nominal_pencairan_belum'] + $dataList[$i]['nominal_pencairan_ya'] + $dataList[$i]['nominal_pencairan_tidak'];
                }else{
                  $dataRekap[$kodeProgram]['nominal_pencairan']  += $dataList[$i]['nominal_pencairan_belum'] + $dataList[$i]['nominal_pencairan_ya'] + $dataList[$i]['nominal_pencairan_tidak'];
                }

               $dataRekap[$kodeProgram]['nominal_realisasi']   += $dataGrid[$index]['nominal_realisasi'];
               $dataRekap[$kodeProgram]['nominal_lppa']        += $dataGrid[$index]['nominal_lppa'];
               $dataRekap[$kodeProgram]['nominal_lppa_sisa']   += $dataGrid[$index]['nominal_lppa_sisa'];

               if(isset($dataList[$i]['status_approve']) || strtoupper($dataList[$i]['status_approve'] = 'YA') || strtoupper($dataList[$i]['status_approve'] = 'TIDAK')){
                  $dataRekap[$kodeProgram]['sisa_dana']          += $dataGrid[$index]['nominal_setelah_revisi'] - $dataList[$i]['nominal_pencairan_ya'] - $dataList[$i]['nominal_pencairan_belum'] - $dataList[$i]['nominal_pencairan_tidak'];
               }else{
                  $dataRekap[$kodeProgram]['sisa_dana']          += $dataGrid[$index]['nominal_setelah_revisi'] - $dataList[$i]['nominal_pencairan_ya'] - $dataList[$i]['nominal_pencairan_belum'] - $dataList[$i]['nominal_pencairan_tidak'];;
               }


               $dataRekap[$kodeKegiatan]['nominal_usulan']     += $dataGrid[$index]['nominal_usulan'];
               $dataRekap[$kodeKegiatan]['nominal_setuju']     += $dataGrid[$index]['nominal_setuju'];
               $dataRekap[$kodeKegiatan]['nominal_revisi']     += $dataGrid[$index]['nominal_revisi'];
               $dataRekap[$kodeKegiatan]['nominal_setelah_revisi']   += $dataGrid[$index]['nominal_setelah_revisi'];

               if(isset($dataList[$i]['status_approve']) || strtoupper($dataList[$i]['status_approve'] = 'YA') || strtoupper($dataList[$i]['status_approve'] = 'TIDAK')){
                  $dataRekap[$kodeKegiatan]['nominal_pencairan']  += $dataList[$i]['nominal_pencairan_belum'] + $dataList[$i]['nominal_pencairan_ya'] + $dataList[$i]['nominal_pencairan_tidak'];
                }else{
                  $dataRekap[$kodeKegiatan]['nominal_pencairan']  += $dataList[$i]['nominal_pencairan_belum'] + $dataList[$i]['nominal_pencairan_ya'] + $dataList[$i]['nominal_pencairan_tidak'];
                }

               $dataRekap[$kodeKegiatan]['nominal_realisasi']  += $dataGrid[$index]['nominal_realisasi'];
               $dataRekap[$kodeKegiatan]['nominal_lppa']        += $dataGrid[$index]['nominal_lppa'];
               $dataRekap[$kodeKegiatan]['nominal_lppa_sisa']   += $dataGrid[$index]['nominal_lppa_sisa'];

               if(isset($dataList[$i]['status_approve']) || strtoupper($dataList[$i]['status_approve'] = 'YA') || strtoupper($dataList[$i]['status_approve'] = 'TIDAK')){
                  $dataRekap[$kodeKegiatan]['sisa_dana']          += $dataGrid[$index]['nominal_setelah_revisi'] - $dataList[$i]['nominal_pencairan_ya'] - $dataList[$i]['nominal_pencairan_belum'] - $dataList[$i]['nominal_pencairan_tidak'];
               }else{
                  $dataRekap[$kodeKegiatan]['sisa_dana']          += $dataGrid[$index]['nominal_setelah_revisi'] - $dataList[$i]['nominal_pencairan_ya'] - $dataList[$i]['nominal_pencairan_belum'] - $dataList[$i]['nominal_pencairan_tidak'];;
               }
                              
               $i++;
            }elseif((int)$unit === (int)$dataList[$i]['unit_id'] && (int)$program === (int)$dataList[$i]['program_id'] && (int)$kegiatan !== (int)$dataList[$i]['kegiatan_id']){
               $kegiatan         = (int)$dataList[$i]['kegiatan_id'];
               $kodeSistem       = $unit.'.'.$program.'.'.$kegiatan;
               $dataRekap[$kodeSistem]['nominal_usulan']       = 0;
               $dataRekap[$kodeSistem]['nominal_setuju']       = 0;
               $dataRekap[$kodeSistem]['nominal_pencairan']    = 0;
               $dataRekap[$kodeSistem]['nominal_realisasi']    = 0;

               $dataGrid[$index]['id']          = $dataList[$i]['kegiatan_id'];
               $dataGrid[$index]['kode']        = $dataList[$i]['kegiatan_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['kegiatan_nama'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['tipe']        = 'kegiatan';
            }elseif((int)$unit === (int)$dataList[$i]['unit_id'] && (int)$program !== (int)$dataList[$i]['program_id']){
               $program                   = (int)$dataList[$i]['program_id'];
               $index--;
            }else{
               $unit             = (int)$dataList[$i]['unit_id'];
               $kodeSistem       = $unit.'.'.$dataList[$i]['program_id'];
               $dataRekap[$kodeSistem]['nominal_usulan']       = 0;
               $dataRekap[$kodeSistem]['nominal_setuju']       = 0;
               $dataRekap[$kodeSistem]['nominal_pencairan']    = 0;
               $dataRekap[$kodeSistem]['nominal_realisasi']    = 0;

               $dataGrid[$index]['unit_id']     = $dataList[$i]['unit_id'];
               $dataGrid[$index]['unit_kode']   = $dataList[$i]['unit_kode'];
               $dataGrid[$index]['unit_nama']   = $dataList[$i]['unit_nama'];
               $dataGrid[$index]['kode']        = $dataList[$i]['program_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['program_nama'];
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['tipe']        = 'program';
            }

            $index++;
         }

         $headerStyle         = array(
            'font' => array(
               'size' => 12,
               'bold' => true
            ), 'alignment' => array(
               'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
               'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
         );

         $borderTableTopStyledArray = array(
            'borders' => array(
               'top' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN,
                  'color' => array('argb' => 'ff000000')
               )
            )
         );

         $borderTableOutlineStyledArray = array(
            'borders' => array(
               'outline' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN,
                  'color' => array('argb' => 'ff000000')
               )
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
               'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
               'wrap' => true
            )
         );

         $sheet->mergeCells('A1:O1');
         $sheet->mergeCells('B3:I3');
         $sheet->mergeCells('B4:I4');
         $sheet->getRowDimension(1)->setRowHeight(23);
         $sheet->getColumnDimension('A')->setWidth(18);
         $sheet->getColumnDimension('C')->setWidth(16);
         $sheet->getColumnDimension('D')->setWidth(50);
         $sheet->getColumnDimension('E')->setWidth(23);
         $sheet->getColumnDimension('F')->setWidth(16);
         $sheet->getColumnDimension('G')->setWidth(20);
         $sheet->getColumnDimension('H')->setWidth(16);
         $sheet->getColumnDimension('I')->setWidth(16);
         $sheet->getColumnDimension('J')->setWidth(16);
         $sheet->getColumnDimension('K')->setWidth(16);
         $sheet->getColumnDimension('L')->setWidth(16);
         $sheet->getColumnDimension('M')->setWidth(16);
         $sheet->getColumnDimension('N')->setWidth(16);
         $sheet->getColumnDimension('O')->setWidth(16);
         $sheet->getColumnDimension('P')->setWidth(16);
         $sheet->getColumnDimension('Q')->setWidth(16);

         $sheet->setCellValue('A1', GTFWConfiguration::GetValue('language', 'laporan_realisasi_anggaran_unit_kerja'));
         $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);
         $sheet->setCellValue('A3', GTFWConfiguration::GetValue('language', 'tahun_periode'));
         $sheet->setCellValue('A4', GTFWConfiguration::GetValue('language', 'unit'));
         $sheet->setCellValue('A5', GTFWConfiguration::GetValue('language', 'bulan_anggaran'));
         $sheet->setCellValueExplicit('B3', ': '.$requestData['ta_nama'], PHPExcel_Cell_DataType::TYPE_STRING);
         $sheet->setCellValueExplicit('B4', ': '.$requestData['unit_nama'], PHPExcel_Cell_DataType::TYPE_STRING);
         $sheet->setCellValueExplicit('B5', ': '.$namaBulan, PHPExcel_Cell_DataType::TYPE_STRING);
         $sheet->getStyle('A3:I5')->getFont()->setBold(true);
         $row = 7;
         $sheet->getRowDimension($row)->setRowHeight(20);
         $sheet->getRowDimension($row + 1)->setRowHeight(20);
         $sheet->setCellValue('A'.$row, GTFWConfiguration::GetValue('language', 'nama_unit').'/'.GTFWConfiguration::GetValue('language', 'sub_unit'));
         $sheet->setCellValue('C'.$row, GTFWConfiguration::GetValue('language', 'program').','.GTFWConfiguration::GetValue('language', 'kegiatan').','.GTFWConfiguration::GetValue('language', 'sub_kegiatan'));
         $sheet->setCellValue('C'.($row+1), GTFWConfiguration::GetValue('language', 'kode'));
         $sheet->setCellValue('D'.($row+1), GTFWConfiguration::GetValue('language', 'nama'));
         $sheet->setCellValue('E'.$row, GTFWConfiguration::GetValue('language', 'nomor_pengajuan'));
         $sheet->setCellValue('F'.$row, GTFWConfiguration::GetValue('language', 'tanggal_transaksi'));
         $sheet->setCellValue('G'.$row, GTFWConfiguration::GetValue('language', 'referensi_transaksi'));
         $sheet->setCellValue('H'.$row, GTFWConfiguration::GetValue('language', 'keterangan'));
         $sheet->setCellValue('I'.$row, GTFWConfiguration::GetValue('language', 'pengajuan_anggaran_rp'));
         $sheet->setCellValue('J'.$row, GTFWConfiguration::GetValue('language', 'anggaran_di_setujui_rp'));
         $sheet->setCellValue('K'.$row, 'Anggaran Revisi (Rp)');
         $sheet->setCellValue('L'.$row, 'Total Setelah Revisi (Rp)');
         $sheet->setCellValue('M'.$row, GTFWConfiguration::GetValue('language', 'pengajuan_fpa_rp'));
         $sheet->setCellValue('N'.$row, GTFWConfiguration::GetValue('language', 'pencairan_dana_rp'));
         $sheet->setCellValue('O'.$row, GTFWConfiguration::GetValue('language', 'lppa_rp'));
         $sheet->setCellValue('O'.($row+1), GTFWConfiguration::GetValue('language', 'nominal_rp'));
         $sheet->setCellValue('P'.($row+1), GTFWConfiguration::GetValue('language', 'sisa_rp'));
         $sheet->setCellValue('Q'.$row, GTFWConfiguration::GetValue('language', 'sisa_saldo_rp'));
         $sheet->getStyle('A'.($row).':Q'.($row+1))->applyFromArray($styledTableHeaderArray);

         $sheet->mergeCells('A'.($row).':B'.($row+1));
         $sheet->mergeCells('C'.($row).':D'.($row));
         $sheet->mergeCells('E'.($row).':E'.($row+1));
         $sheet->mergeCells('F'.($row).':F'.($row+1));
         $sheet->mergeCells('G'.($row).':G'.($row+1));
         $sheet->mergeCells('H'.($row).':H'.($row+1));
         $sheet->mergeCells('I'.($row).':I'.($row+1));
         $sheet->mergeCells('J'.($row).':J'.($row+1));
         $sheet->mergeCells('K'.($row).':K'.($row+1));
         $sheet->mergeCells('L'.($row).':L'.($row+1));
         $sheet->mergeCells('M'.($row).':M'.($row+1));
         $sheet->mergeCells('N'.($row).':N'.($row+1));
         $sheet->mergeCells('O'.($row).':P'.$row);
         // $sheet->mergeCells('P'.($row).':P'.($row+1));
         $sheet->mergeCells('Q'.($row).':Q'.($row+1));
         
         $row  = 9;
         foreach ($dataGrid as $list) {
            switch (strtoupper($list['tipe'])) {
               case 'PROGRAM':
                  $list['nominal_usulan']    = $dataRekap[$list['kode_sistem']]['nominal_usulan'];
                  $list['nominal_setuju']    = $dataRekap[$list['kode_sistem']]['nominal_setuju'];
                  $list['nominal_revisi'] = $dataRekap[$list['kode_sistem']]['nominal_revisi'];
                  $list['nominal_setelah_revisi'] = $dataRekap[$list['kode_sistem']]['nominal_setelah_revisi'];
                  $list['nominal_pencairan'] = $dataRekap[$list['kode_sistem']]['nominal_pencairan'];
                  $list['nominal_realisasi'] = $dataRekap[$list['kode_sistem']]['nominal_realisasi'];
                  $list['sisa_dana']         = $dataRekap[$list['kode_sistem']]['sisa_dana'];
                  $list['nominal_lppa']      = $dataRekap[$list['kode_sistem']]['nominal_lppa'];
                  $list['nominal_lppa_sisa'] = $dataRekap[$list['kode_sistem']]['nominal_lppa_sisa'];
                  $sheet->getStyle('B'.$row.':Q'.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('00CCCCCC');
                  $sheet->getStyle('A'.$row.':Q'.$row)->getFont()->setBold(true);
                  $sheet->mergeCells('D'.$row.':H'.$row);
                  $sheet->getStyle('C'.$row.':Q'.$row)->applyFromArray($borderTableStyledArray);
                  $unit_kerja_nama           = $list['unit_nama'];
                  break;
               case 'KEGIATAN':
                  $list['nominal_usulan']    = $dataRekap[$list['kode_sistem']]['nominal_usulan'];
                  $list['nominal_setuju']    = $dataRekap[$list['kode_sistem']]['nominal_setuju'];
                  $list['nominal_revisi'] = $dataRekap[$list['kode_sistem']]['nominal_revisi'];
                  $list['nominal_setelah_revisi'] = $dataRekap[$list['kode_sistem']]['nominal_setelah_revisi'];
                  $list['nominal_pencairan'] = $dataRekap[$list['kode_sistem']]['nominal_pencairan'];
                  $list['nominal_realisasi'] = $dataRekap[$list['kode_sistem']]['nominal_realisasi'];
                  $list['sisa_dana']         = $dataRekap[$list['kode_sistem']]['sisa_dana'];
                  $list['nominal_lppa']      = $dataRekap[$list['kode_sistem']]['nominal_lppa'];
                  $list['nominal_lppa_sisa'] = $dataRekap[$list['kode_sistem']]['nominal_lppa_sisa'];
                  $sheet->getStyle('B'.$row.':Q'.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('00DCDCDC');
                  $sheet->mergeCells('D'.$row.':H'.$row);                  
                  $sheet->getStyle('C'.$row.':Q'.$row)->applyFromArray($borderTableStyledArray);
                  $unit_kerja_nama           = '';
                  break;
               case 'SUB_KEGIATAN':
                  if($list['nominal_usulan'] > 0) {
                      $list['nominal_usulan']    = $list['nominal_usulan'];
                  } else {
                      $list['nominal_usulan'] ='';
                  }
                  if($list['nominal_setuju']  > 0) {
                     $list['nominal_setuju']    = $list['nominal_setuju'];
                  } else {
                      $list['nominal_setuju'] ='';
                  }
                  if($list['nominal_revisi'] > 0) {
                      $list['nominal_revisi']    = $list['nominal_revisi'];
                  } else {
                      $list['nominal_revisi'] ='';
                  }
                  if($list['nominal_setelah_revisi']  > 0) {
                     $list['nominal_setelah_revisi']    = $list['nominal_setelah_revisi'];
                  } else {
                      $list['nominal_setelah_revisi'] ='';
                  }
                  $list['nominal_pencairan'] = $list['nominal_pencairan'];
                  $list['nominal_realisasi'] = $list['nominal_realisasi'];
                  

                  if($list['nominal_lppa']  !=''){
                     $list['nominal_lppa']      =  $list['nominal_lppa'];
                   } else {
                     $list['nominal_lppa']      =  '';
                   }

                  if($list['nominal_lppa_sisa']  !=''){
                     $list['nominal_lppa_sisa']  = $list['nominal_lppa_sisa'];
                  } else {
                     $list['nominal_lppa_sisa']  = '';
                  }

                  if( $list['sisa_dana'] > 0) {
                    $list['sisa_dana']         = $list['sisa_dana'];
                  } else {
                       $list['sisa_dana'] = '0';
                  }  
                  //$list['nama'] .=  ' '.('D'.$row.':D'. ($row +  $list['np']));
                  if(($list['idp'] =='') || empty($list['idp'])){
                      $sheet->getStyle('E'.$row.':Q'.$row)->applyFromArray($borderTableStyledArray);     
                  }
                  //$sheet->getStyle('C'.$row.':M'.$row)->applyFromArray($borderTableStyledArray);
                  $unit_kerja_nama           = '';
                  break;
               default:
                  $list['nominal_usulan']    = $list['nominal_usulan'];
                  $list['nominal_setuju']    = $list['nominal_setuju'];
                  $list['nominal_revisi'] = $list['nominal_revisi'];
                  $list['nominal_setelah_revisi'] = $list['nominal_setelah_revisi'];
                  $list['nominal_pencairan'] = $list['nominal_pencairan'];
                  $list['nominal_realisasi'] = $list['nominal_realisasi'];
                  $list['sisa_dana']         = $list['sisa_dana'];
                  $list['nominal_lppa']      = $list['nominal_lppa'];
                  $list['nominal_lppa_sisa'] = $list['nominal_lppa_sisa'];
                  $unit_kerja_nama           = '';
                  break;
            }
            $sheet->mergeCells('A'.$row.':B'.$row);
            $max  = max(array(
               ceil(strlen(strtoupper($unit_kerja_nama))/($sheet->getColumnDimension('A')->getWidth()+$sheet->getColumnDimension('B')->getWidth())),
               ceil(strlen(strtoupper($list['kode']))/$sheet->getColumnDimension('C')->getWidth()),
               ceil(strlen(strtoupper($list['nama']))/$sheet->getColumnDimension('D')->getWidth())
            ))*15.8;
            $sheet->getRowDimension($row)->setRowHeight($max);
            $sheet->setCellValue('A'.$row, $list['unit_nama']);
            if(($list['unit_nama'] != '')){
                $sheet->getStyle('A'.$row.':B'.$row)->applyFromArray($borderTableTopStyledArray);    
            }
            
            if(($list['kode'] != '')){
                $sheet->getStyle('C'.$row.':C'.$row)->applyFromArray($borderTableTopStyledArray);
               // if($list['np'] > 0){
                //    $sheet->mergeCells('C'.$row.':C'. (($row ) + $list['np']));    
                //}  
                  
            }
            if(($list['nama'] != '') && ($list['tipe'] == 'sub_kegiatan')){
                $sheet->getStyle('D'.$row.':D'.$row)->applyFromArray($borderTableTopStyledArray);
                //$sheet->mergeCells('D'.$row.':D'. ($row + $list['np']));
            }
            if($list['no_pengajuan'] != ''){
                $sheet->getStyle('E'.$row.':E'.$row)->applyFromArray($borderTableTopStyledArray);  
            }
            
            if(($list['keterangan'] != '')){
                $sheet->getStyle('H'.$row.':H'.$row)->applyFromArray($borderTableTopStyledArray);    
            }
            
            if(($list['nominal_usulan'] != '') || $list['nominal_usulan'] > 0){
                $sheet->getStyle('I'.$row.':I'.$row)->applyFromArray($borderTableTopStyledArray);    
            }
            if(($list['nominal_setuju'] != '') || $list['nominal_setuju'] > 0){
                $sheet->getStyle('J'.$row.':J'.$row)->applyFromArray($borderTableTopStyledArray);    
            }
            
            if(($list['nominal_revisi'] != '') || $list['nominal_revisi'] > 0){
                $sheet->getStyle('K'.$row.':K'.$row)->applyFromArray($borderTableTopStyledArray);    
            }
            
            if(($list['nominal_setelah_revisi'] != '') || $list['nominal_setelah_revisi'] > 0){
                $sheet->getStyle('L'.$row.':L'.$row)->applyFromArray($borderTableTopStyledArray);    
            }
            
            if(($list['nominal_pencairan'] != '') || $list['nominal_pencairan'] > 0){
                $sheet->getStyle('M'.$row.':M'.$row)->applyFromArray($borderTableTopStyledArray);    
            }
            
            if(($list['nominal_realisasi'] != '') || $list['nominal_realisasi'] > 0){
                $sheet->getStyle('N'.$row.':N'.$row)->applyFromArray($borderTableTopStyledArray);    
            }

            if(($list['nominal_lppa'] != '') || $list['nominal_lppa'] > 0){
               $sheet->getStyle('O'.$row.':O'.$row)->applyFromArray($borderTableTopStyledArray);    
            }

            if(($list['nominal_lppa_sisa'] != '') || $list['nominal_lppa_sisa'] > 0){
               $sheet->getStyle('P'.$row.':P'.$row)->applyFromArray($borderTableTopStyledArray);    
            }

            if(($list['sisa_dana'] != '') || $list['sisa_dana'] > 0){
                $sheet->getStyle('Q'.$row.':Q'.$row)->applyFromArray($borderTableTopStyledArray);    
            }
            
            $sheet->setCellValueExplicit('C'.$row, $list['kode'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('D'.$row, $list['nama']);
            
            $sheet->setCellValueExplicit('E'.$row, $list['no_pengajuan']);
            $sheet->setCellValueExplicit('F'.$row, $list['tanggal_transaksi']);
            $sheet->setCellValueExplicit('G'.$row, $list['no_bukti']);
            $sheet->setCellValueExplicit('H'.$row, $list['keterangan']);
            
            $sheet->setCellValue('I'.$row, $list['nominal_usulan']);//, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValue('J'.$row, $list['nominal_setuju']);//, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValue('K'.$row, $list['nominal_revisi']);//, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValue('L'.$row, $list['nominal_setelah_revisi']);//, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValue('M'.$row, $list['nominal_pencairan']);//, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValue('N'.$row, $list['nominal_realisasi']);//, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValue('O'.$row, $list['nominal_lppa']);//, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValue('P'.$row, $list['nominal_lppa_sisa']);//, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValue('Q'.$row, $list['sisa_dana']);//, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $row++;
         }
         $sheet->getStyle('A8:P'.($row-1))->getAlignment()->setWrapText(true)->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
         
         $sheet->getStyle('F8:G'.($row-1))->applyFromArray($borderTableStyledArray);        
         #$sheet->getStyle('J8:J'.($row-1))->applyFromArray($borderTableStyledArray);       
         #$sheet->getStyle('K8:K'.($row-1))->applyFromArray($borderTableStyledArray);        
         $sheet->getStyle('L8:L'.($row-1))->applyFromArray($borderTableStyledArray);         
         $sheet->getStyle('M8:M'.($row-1))->applyFromArray($borderTableStyledArray);       
         $sheet->getStyle('N8:N'.($row-1))->applyFromArray($borderTableStyledArray);       
         $sheet->getStyle('O8:O'.($row-1))->applyFromArray($borderTableStyledArray); 
         $sheet->getStyle('P8:P'.($row-1))->applyFromArray($borderTableStyledArray);
         $sheet->getStyle('Q8:Q'.($row-1))->applyFromArray($borderTableStyledArray);
         
         $sheet->getStyle('A8:B'.($row-1))->applyFromArray($borderTableOutlineStyledArray);
         
         $sheet->getStyle('C8:C'.($row-1))->applyFromArray($borderTableOutlineStyledArray);
         $sheet->getStyle('D8:D'.($row-1))->applyFromArray($borderTableOutlineStyledArray);
         $sheet->getStyle('E8:E'.($row-1))->applyFromArray($borderTableOutlineStyledArray);
         $sheet->getStyle('H8:H'.($row-1))->applyFromArray($borderTableOutlineStyledArray);
         $sheet->getStyle('I8:I'.($row-1))->applyFromArray($borderTableOutlineStyledArray);
         $sheet->getStyle('J8:J'.($row-1))->applyFromArray($borderTableOutlineStyledArray);
         $sheet->getStyle('K8:K'.($row-1))->applyFromArray($borderTableOutlineStyledArray);
         $sheet->getStyle('M8:Q'.($row-1))->applyFromArray($borderTableOutlineStyledArray);
         
         $sheet->getStyle('I8:Q'.($row-1))->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
         $sheet->getStyle('C8:C'.($row-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

         $sheet->mergeCells('A'.($row+2).':I'.($row+2));
         $sheet->setCellValue('A'.($row+2), GTFWConfiguration::GetValue('language', 'resume'));
         $sheet->getStyle('A'.($row+2).':I'.($row+2))->getFont()->setBold(true)->setSize(12);
         $sheet->getRowDimension(($row+2))->setRowHeight(18);
         $rows       = $row+3;

         $sheet->mergeCells('A'.$rows.':H'.($rows+1));
         $sheet->mergeCells('I'.($rows).':I'.($rows+1));
         $sheet->mergeCells('J'.($rows).':J'.($rows+1));
         $sheet->mergeCells('K'.($rows).':K'.($rows+1));
         $sheet->mergeCells('L'.($rows).':L'.($rows+1));
         $sheet->mergeCells('M'.($rows).':M'.($rows+1));
         $sheet->mergeCells('N'.($rows).':N'.($rows+1));
         $sheet->mergeCells('O'.($rows).':P'.$rows);         
         $sheet->mergeCells('Q'.($rows).':Q'.($rows+1));
         $sheet->setCellValue('A'.$rows, GTFWConfiguration::GetValue('language', 'nama_unit').'/'.GTFWConfiguration::GetValue('language', 'sub_unit'));
         $sheet->setCellValue('I'.$rows, GTFWConfiguration::GetValue('language', 'pengajuan_anggaran_rp'));
         $sheet->setCellValue('J'.$rows, GTFWConfiguration::GetValue('language', 'anggaran_di_setujui_rp'));
         $sheet->setCellValue('K'.$rows, 'Anggaran Revisi (Rp)');
         $sheet->setCellValue('L'.$rows, 'Total Setelah Revisi (Rp)');
         $sheet->setCellValue('M'.$rows, GTFWConfiguration::GetValue('language', 'pengajuan_fpa_rp'));
         $sheet->setCellValue('N'.$rows, GTFWConfiguration::GetValue('language', 'pencairan_dana_rp'));
         $sheet->setCellValue('O'.$rows, GTFWConfiguration::GetValue('language', 'lppa_rp'));
         $sheet->setCellValue('O'.($rows+1), GTFWConfiguration::GetValue('language', 'nominal_rp'));
         $sheet->setCellValue('P'.($rows+1), GTFWConfiguration::GetValue('language', 'sisa_rp'));
         $sheet->setCellValue('Q'.$rows, GTFWConfiguration::GetValue('language', 'sisa_saldo_rp'));
         $sheet->getStyle('A'.$rows.':Q'.($rows+1))->applyFromArray($styledTableHeaderArray);         
        

         // resume
         $tableRow   = $rows+2;
         $unitId     = 0;
         $rIndex     = 0;
         $resume     = array();
         $totalAll   = array();
         $totalUnit  = array();
         for($r = 0 ; $r < (sizeof($dataResume));) {      
            if($dataResume[$r]['unit_id'] == $unitId) {               
               $resume[$rIndex]['nama']                     = $dataResume[$r]['program_nama'];
               $resume[$rIndex]['nominal_usulan']           = $dataResume[$r]['nominal_usulan'];
               $resume[$rIndex]['nominal_setuju']           = $dataResume[$r]['nominal_setuju'];
               $resume[$rIndex]['nominal_revisi']           = $dataResume[$r]['nominal_revisi'];
               $resume[$rIndex]['nominal_setelah_revisi']   = $dataResume[$r]['nominal_setelah_revisi'];
               $resume[$rIndex]['nominal_pencairan']        = $dataResume[$r]['nominal_pencairan'];
               $resume[$rIndex]['nominal_realisasi']        = $dataResume[$r]['nominal_realisasi'] ;
               $resume[$rIndex]['nominal_lppa']             = $dataResume[$r]['nominal_lppa'];
               $resume[$rIndex]['nominal_lppa_sisa']        = 0;
               if($dataResume[$r]['nominal_lppa'] > 0 ) {
                  $resume[$rIndex]['nominal_lppa_sisa']        = $dataResume[$r]['nominal_realisasi'] -  $dataResume[$r]['nominal_lppa'];
               }
               $resume[$rIndex]['sisa_dana']                = $dataResume[$r]['sisa_dana']; 
               $resume[$rIndex]['tipe']                     = 'program'; 

               // hitung total unit
               $totalUnit[$unitId]['nominal_usulan']           += $dataResume[$r]['nominal_usulan'];
               $totalUnit[$unitId]['nominal_setuju']           += $dataResume[$r]['nominal_setuju'];
               $totalUnit[$unitId]['nominal_revisi']           += $dataResume[$r]['nominal_revisi'];
               $totalUnit[$unitId]['nominal_setelah_revisi']   += $dataResume[$r]['nominal_setelah_revisi'];
               $totalUnit[$unitId]['nominal_pencairan']        += $dataResume[$r]['nominal_pencairan'];
               $totalUnit[$unitId]['nominal_realisasi']        += $dataResume[$r]['nominal_realisasi'] ;
               $totalUnit[$unitId]['nominal_lppa']             += $dataResume[$r]['nominal_lppa'];        
               
               if($dataResume[$r]['nominal_lppa'] > 0 ) {
                  $totalUnit[$unitId]['nominal_lppa_sisa']        += ($dataResume[$r]['nominal_realisasi'] -  $dataResume[$r]['nominal_lppa']);
               } else {
                  $totalUnit[$unitId]['nominal_lppa_sisa']        += 0;
               }
               $totalUnit[$unitId]['sisa_dana']                += $dataResume[$r]['sisa_dana'];
               
               // hitung total all
               $totalAll['nominal_usulan']           += $dataResume[$r]['nominal_usulan'];
               $totalAll['nominal_setuju']           += $dataResume[$r]['nominal_setuju'];
               $totalAll['nominal_revisi']           += $dataResume[$r]['nominal_revisi'];
               $totalAll['nominal_setelah_revisi']   += $dataResume[$r]['nominal_setelah_revisi'];
               $totalAll['nominal_pencairan']        += $dataResume[$r]['nominal_pencairan'];
               $totalAll['nominal_realisasi']        += $dataResume[$r]['nominal_realisasi'] ;
               $totalAll['nominal_lppa']             += $dataResume[$r]['nominal_lppa'];          
               
               if($dataResume[$r]['nominal_lppa'] > 0 ) {
                  $totalAll['nominal_lppa_sisa']        += ($dataResume[$r]['nominal_realisasi'] -  $dataResume[$r]['nominal_lppa']);
               } else {
                  $totalAll['nominal_lppa_sisa']        += 0;
               } 
               $totalAll['sisa_dana']                += $dataResume[$r]['sisa_dana'];
               // end
               $r++;
            } else {
               $unitId = $dataResume[$r]['unit_id'];               
               $resume[$rIndex]['unit_id']                  = $dataResume[$r]['unit_id'];       
               $resume[$rIndex]['nama']                     = $dataResume[$r]['unit_nama'];               
               $resume[$rIndex]['nominal_usulan']           = 0;
               $resume[$rIndex]['nominal_setuju']           = 0;
               $resume[$rIndex]['nominal_revisi']           = 0;
               $resume[$rIndex]['nominal_setelah_revisi']   = 0;
               $resume[$rIndex]['nominal_pencairan']        = 0;
               $resume[$rIndex]['nominal_realisasi']        = 0;
               $resume[$rIndex]['nominal_lppa']             = 0;
               $resume[$rIndex]['nominal_lppa_sisa']        = 0;
               $resume[$rIndex]['sisa_dana']                = 0; 
               $resume[$rIndex]['tipe']                     = 'unit'; 
            }
            $rIndex++;
         }


         foreach ($resume as $resume) {

            if($resume['tipe'] === 'unit') { 
               $sheet->getStyle('A'.$tableRow.':Q'.$tableRow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('00CCCCCC');
               $sheet->getStyle('A'.$tableRow.':Q'.$tableRow)->getFont()->setBold(true);
               $resume['nominal_usulan']           = isset($totalUnit[$resume['unit_id']]['nominal_usulan']) ? $totalUnit[$resume['unit_id']]['nominal_usulan'] : 0;
               $resume['nominal_setuju']           = isset($totalUnit[$resume['unit_id']]['nominal_setuju']) ? $totalUnit[$resume['unit_id']]['nominal_setuju'] : 0;
               $resume['nominal_revisi']           = isset($totalUnit[$resume['unit_id']]['nominal_revisi']) ? $totalUnit[$resume['unit_id']]['nominal_revisi'] : 0;
               $resume['nominal_setelah_revisi']   = isset($totalUnit[$resume['unit_id']]['nominal_setelah_revisi']) ? $totalUnit[$resume['unit_id']]['nominal_setelah_revisi'] : 0;
               $resume['nominal_pencairan']        = isset($totalUnit[$resume['unit_id']]['nominal_pencairan']) ? $totalUnit[$resume['unit_id']]['nominal_pencairan'] : 0;
               $resume['nominal_realisasi']        = isset($totalUnit[$resume['unit_id']]['nominal_realisasi']) ? $totalUnit[$resume['unit_id']]['nominal_realisasi'] : 0;
               $resume['nominal_lppa']             = isset($totalUnit[$resume['unit_id']]['nominal_lppa']) ? $totalUnit[$resume['unit_id']]['nominal_lppa'] : 0;
               $resume['nominal_lppa_sisa']        = isset($totalUnit[$resume['unit_id']]['nominal_lppa_sisa']) ? $totalUnit[$resume['unit_id']]['nominal_lppa_sisa'] : 0;
               $resume['sisa_dana']                = isset($totalUnit[$resume['unit_id']]['sisa_dana']) ? $totalUnit[$resume['unit_id']]['sisa_dana'] : 0;
            }

            $sheet->mergeCells('A'.$tableRow.':H'.$tableRow);
            $sheet->setCellValue('A'.$tableRow, $resume['nama']);
            $sheet->setCellValueExplicit('I'.$tableRow, $resume['nominal_usulan'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('J'.$tableRow, $resume['nominal_setuju'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('K'.$tableRow, $resume['nominal_revisi'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('L'.$tableRow, $resume['nominal_setelah_revisi'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('M'.$tableRow, $resume['nominal_pencairan'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('N'.$tableRow, $resume['nominal_realisasi'], PHPExcel_Cell_DataType::TYPE_NUMERIC);            
            $sheet->setCellValueExplicit('O'.$tableRow, $resume['nominal_lppa'], PHPExcel_Cell_DataType::TYPE_NUMERIC);                 
            $sheet->setCellValueExplicit('P'.$tableRow, $resume['nominal_lppa_sisa'], PHPExcel_Cell_DataType::TYPE_NUMERIC);          
            $sheet->setCellValueExplicit('Q'.$tableRow, $resume['sisa_dana'], PHPExcel_Cell_DataType::TYPE_NUMERIC);

            $tableRow++;
         }

         // total all
         $totalAllResume['nominal_usulan']           = isset($totalAll['nominal_usulan']) ? $totalAll['nominal_usulan'] : 0;
         $totalAllResume['nominal_setuju']           = isset($totalAll['nominal_setuju']) ? $totalAll['nominal_setuju'] : 0;
         $totalAllResume['nominal_revisi']           = isset($totalAll['nominal_revisi']) ? $totalAll['nominal_revisi'] : 0;
         $totalAllResume['nominal_setelah_revisi']   = isset($totalAll['nominal_setelah_revisi']) ? $totalAll['nominal_setelah_revisi'] : 0;
         $totalAllResume['nominal_pencairan']        = isset($totalAll['nominal_pencairan']) ? $totalAll['nominal_pencairan'] : 0;
         $totalAllResume['nominal_realisasi']        = isset($totalAll['nominal_realisasi']) ? $totalAll['nominal_realisasi'] : 0;
         $totalAllResume['nominal_lppa']             = isset($totalAll['nominal_lppa']) ? $totalAll['nominal_lppa'] : 0;
         $totalAllResume['nominal_lppa_sisa']        = isset($totalAll['nominal_lppa_sisa']) ? $totalAll['nominal_lppa_sisa'] : 0;
         $totalAllResume['sisa_dana']                = isset($totalAll['sisa_dana']) ? $totalAll['sisa_dana'] : 0; 
         
         $sheet->mergeCells('A'.$tableRow.':H'.$tableRow);
         $sheet->setCellValue('A'.$tableRow, 'Total');
         $sheet->getStyle('A'.$tableRow.':Q'.$tableRow)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('00CCCCCC');
         $sheet->getStyle('A'.$tableRow.':Q'.$tableRow)->getFont()->setBold(true);
         $sheet->setCellValueExplicit('I'.$tableRow, $totalAllResume['nominal_usulan'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
         $sheet->setCellValueExplicit('J'.$tableRow, $totalAllResume['nominal_setuju'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
         $sheet->setCellValueExplicit('K'.$tableRow, $totalAllResume['nominal_revisi'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
         $sheet->setCellValueExplicit('L'.$tableRow, $totalAllResume['nominal_setelah_revisi'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
         $sheet->setCellValueExplicit('M'.$tableRow, $totalAllResume['nominal_pencairan'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
         $sheet->setCellValueExplicit('N'.$tableRow, $totalAllResume['nominal_realisasi'], PHPExcel_Cell_DataType::TYPE_NUMERIC);            
         $sheet->setCellValueExplicit('O'.$tableRow, $totalAllResume['nominal_lppa'], PHPExcel_Cell_DataType::TYPE_NUMERIC);          
         $sheet->setCellValueExplicit('P'.$tableRow, $totalAllResume['nominal_lppa_sisa'], PHPExcel_Cell_DataType::TYPE_NUMERIC);                 
         $sheet->setCellValueExplicit('Q'.$tableRow, $totalAllResume['sisa_dana'], PHPExcel_Cell_DataType::TYPE_NUMERIC);

         $sheet->getStyle('A'.($rows+1).':Q'.($tableRow))->applyFromArray($borderTableStyledArray);
         $sheet->getStyle('I'.($rows+1).':Q'.($tableRow))->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');

         $rowSign       = $tableRow+2;
         $sheet->mergeCells('N'.($rowSign).':P'.($rowSign));
         $sheet->mergeCells('N'.($rowSign+1).':P'.($rowSign+1));
         $sheet->mergeCells('N'.($rowSign+2).':P'.($rowSign+2));
         $sheet->mergeCells('N'.($rowSign+3).':P'.($rowSign+6));
         $sheet->mergeCells('N'.($rowSign+7).':P'.($rowSign+7));
         $sheet->getStyle('N'.$rowSign.':P'.($rowSign+7))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
         $sheet->setCellValue('N'.$rowSign, GTFWConfiguration::GetValue('organization', 'city').', '.$tanggalCetak);
         $sheet->setCellValue('N'.($rowSign+1), GTFWConfiguration::GetValue('language', 'pimpinan_unit_kerja').'/'.GTFWConfiguration::GetValue('language', 'sub_unit'));
         $sheet->setCellValue('N'.($rowSign+2), $requestData['unit_nama']);
         $sheet->setCellValue('N'.($rowSign+7), $pimpinan);
      }

      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>