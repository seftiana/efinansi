<?php
/**
* ================= doc ====================
* FILENAME     : ViewExcelLapRincianPengeluaran.xlsx.class.php
* @package     : ViewExcelLapRincianPengeluaran
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-09-23
* @Modified    : 2014-09-23
* @Analysts    : Dyah Fajar N
* @contact     : eko.susilo@gamatechno.com
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/lap_rincian_pengeluaran_unit/business/AppLapRincianPengeluaran.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewExcelLapRincianPengeluaran extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      $moduleName       = GTFWConfiguration::GetValue('language', 'lap_rinci_pengeluaran');
      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('lap_rinci_pengeluaran_'.date('Ymd', time()).'.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(true); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle('laporan_rincian');
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

      $mObj       = new AppLapRincianPengeluaran();
      $mUnitObj   = new UserUnitKerja();
      $userid     = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
      $arrPeriodeTahun     = $mObj->GetPeriodeTahun();
      $periodeTahun        = $mObj->GetPeriodeTahun(array('active' => true));
      $dataUnit            = $mUnitObj->GetUnitKerjaRefUser($userid);
      $requestData         = array();

      $requestData['ta_id']      = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
      $requestData['unit_id']    = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
      $requestData['unit_nama']  = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);

      foreach ($arrPeriodeTahun as $ta) {
         if((int)$ta['id'] === (int)$requestData['ta_id']){
            $requestData['ta_nama'] = $ta['name'];
         }
      }

      $dataList      = $mObj->ChangeKeyName($mObj->GetData(0, 100000, (array)$requestData));
      $totalData     = $mObj->Count();
      $unitkerja     = $mObj->GetUnitKerja($requestData['unit_id']);

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
               'style' => PHPExcel_Style_Border::BORDER_HAIR,
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

      $periodeTahunLabel      = GTFWConfiguration::GetValue('language', 'periode_tahun');
      $unitLabel              = GTFWConfiguration::GetValue('language', 'unit');
      $subUnitLabel           = GTFWConfiguration::GetValue('language', 'sub_unit');
      $sheet->mergeCells('A1:J1');
      $sheet->mergeCells('A2:J2');
      $sheet->mergeCells('A4:B4');
      $sheet->mergeCells('C4:J4');
      $sheet->mergeCells('A5:A6');
      $sheet->mergeCells('B5:C5');
      $sheet->mergeCells('D5:D6');
      $sheet->mergeCells('E5:E6');
      $sheet->mergeCells('F5:F6');
      $sheet->mergeCells('G5:G6');
      $sheet->mergeCells('H5:J5');

      $sheet->getColumnDimension('A')->setWidth(8);
      $sheet->getColumnDimension('B')->setWidth(15);
      $sheet->getColumnDimension('C')->setWidth(50);
      $sheet->getColumnDimension('D')->setWidth(35);
      $sheet->getColumnDimension('E')->setWidth(18);
      $sheet->getColumnDimension('F')->setWidth(18);
      $sheet->getColumnDimension('G')->setWidth(18);
      $sheet->getColumnDimension('H')->setWidth(10);
      $sheet->getColumnDimension('I')->setWidth(22);
      $sheet->getColumnDimension('J')->setWidth(22);

      $sheet->getRowDimension(1)->setRowHeight(20);
      $sheet->setCellValue('A1', $moduleName);
      $sheet->getStyle('A1:K1')->applyFromArray($headerStyle);

      $sheet->setCellValue('A2', $periodeTahunLabel.' : '.$requestData['ta_nama']);
      $sheet->getStyle('A2:K2')->applyFromArray($headerStyle);
      $sheet->getStyle('A2:K2')->getFont()->setSize(11);

      $sheet->setCellValue('A4', $unitLabel.'/'.$subUnitLabel);
      $sheet->setCellValue('C4', $requestData['unit_nama']);
      $sheet->getStyle('A4:J4')->getFont()->setBold(true);

      $sheet->setCellValue('A5', GTFWConfiguration::GetValue('language', 'no'));
      $sheet->setCellValue('B5', GTFWConfiguration::GetValue('language', 'label_rincian'));
      $sheet->setCellValue('B6', GTFWConfiguration::GetValue('language', 'kode'));
      $sheet->setCellValue('C6', GTFWConfiguration::GetValue('language', 'nama'));
      $sheet->setCellValue('D5', $unitLabel.'/'.$subUnitLabel);
      $sheet->setCellValue('E5', GTFWConfiguration::GetValue('language', 'ikk'));
      $sheet->setCellValue('F5', GTFWConfiguration::GetValue('language', 'iku'));
      $sheet->setCellValue('G5', GTFWConfiguration::GetValue('language', 'output'));
      $sheet->setCellValue('H5', GTFWConfiguration::GetValue('language', 'perhitungan'));
      $sheet->setCellValue('H6', GTFWConfiguration::GetValue('language', 'volume'));
      $sheet->setCellValue('I6', GTFWConfiguration::GetValue('language', 'harga_satuan'));
      $sheet->setCellValue('J6', GTFWConfiguration::GetValue('language', 'jumlah_biaya'));
      $sheet->getStyle('A5:J6')->applyFromArray($styledTableHeaderArray);
      $sheet->getStyle('A5:J6')->getAlignment()->setWrapText(true);

      // set data
      if(empty($dataList)){
         $sheet->setCellValue('A7', '-- Tidak Ada Data '.$moduleName.' --');
         $sheet->mergeCells('A7:J7');
         $sheet->getStyle('A7:J7')->applyFromArray($styledTableHeaderArray);
         $sheet->getStyle('A7:J7')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
      }else{
         // inisialisasi data
         $program       = '';
         $kegiatan      = '';
         $subkegiatan   = '';
         $kegdet        = '';
         $makId         = '';
         $index         = 0;
         $dataGrid      = array();
         $dataRincian   = array();
         $start         = 1;
         for ($i=0; $i < count($dataList);) {
            if((int)$dataList[$i]['program_id'] === (int)$program
               && (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan
               && (int)$subkegiatan === (int)$dataList[$i]['sub_kegiatan_id']
               && (int)$kegdet === (int)$dataList[$i]['kegdet_id']
               && (int)$makId === (int)$dataList[$i]['mak_id']){
               // kode sistem
               $kodeSistemProgram         = $program;
               $kodeSistemKegiatan        = $program.'.'.$kegiatan;
               $kodeSistemSubKegiatan     = $program.'.'.$kegiatan.'.'.$subkegiatan;
               $kodeSistemKegdet          = $program.'.'.$kegiatan.'.'.$subkegiatan.'.'.$kegdet;
               $kodeSistemMak             = $program.'.'.$kegiatan.'.'.$subkegiatan.'.'.$kegdet.'.'.$makId;

               $dataRincian[$kodeSistemProgram]['nominal_satuan']       += $dataList[$i]['setuju_nominal'];
               $dataRincian[$kodeSistemKegiatan]['nominal_satuan']      += $dataList[$i]['setuju_nominal'];
               $dataRincian[$kodeSistemSubKegiatan]['nominal_satuan']   += $dataList[$i]['setuju_nominal'];
               $dataRincian[$kodeSistemKegdet]['nominal_satuan']        += $dataList[$i]['setuju_nominal'];
               $dataRincian[$kodeSistemMak]['nominal_satuan']           += $dataList[$i]['setuju_nominal'];

               $dataRincian[$kodeSistemProgram]['nominal_total']       += $dataList[$i]['setuju_jumlah'];
               $dataRincian[$kodeSistemKegiatan]['nominal_total']      += $dataList[$i]['setuju_jumlah'];
               $dataRincian[$kodeSistemSubKegiatan]['nominal_total']   += $dataList[$i]['setuju_jumlah'];
               $dataRincian[$kodeSistemKegdet]['nominal_total']        += $dataList[$i]['setuju_jumlah'];
               $dataRincian[$kodeSistemMak]['nominal_total']           += $dataList[$i]['setuju_jumlah'];

               $dataGrid[$index]['kode']        = $dataList[$i]['komp_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['komp_nama'];
               $dataGrid[$index]['unit_nama']   = $dataList[$i]['unit_nama'];
               $dataGrid[$index]['nomor']       = $start;
               $dataGrid[$index]['tipe']        = 'komponen';
               $dataGrid[$index]['volume']      = $dataList[$i]['volume'];
               $dataGrid[$index]['nominal_satuan']    = $dataList[$i]['setuju_nominal'];
               $dataGrid[$index]['nominal_total']     = $dataList[$i]['setuju_jumlah'];
               $i++;
               $start++;
            }elseif((int)$dataList[$i]['program_id'] === (int)$program
               && (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan
               && (int)$subkegiatan === (int)$dataList[$i]['sub_kegiatan_id']
               && (int)$kegdet === (int)$dataList[$i]['kegdet_id']
               && (int)$makId !== (int)$dataList[$i]['mak_id']){
               $makId            = (int)$dataList[$i]['mak_id'];
               $kodeSistem       = $program.'.'.$kegiatan.'.'.$subkegiatan.'.'.$kegdet.'.'.$makId;
               $dataRincian[$kodeSistem]['nominal_satuan']  = 0;
               $dataRincian[$kodeSistem]['nominal_total']   = 0;
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['nomor']       = '';
               $dataGrid[$index]['kode']        = $dataList[$i]['mak_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['mak_nama'];
               $dataGrid[$index]['tipe']        = 'rkat';
            }elseif((int)$dataList[$i]['program_id'] === (int)$program
               && (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan
               && (int)$subkegiatan === (int)$dataList[$i]['sub_kegiatan_id']
               && (int)$kegdet !== (int)$dataList[$i]['kegdet_id']){
               $kegdet           = (int)$dataList[$i]['kegdet_id'];
               $kodeSistem       = $program.'.'.$kegiatan.'.'.$subkegiatan.'.'.$kegdet;
               $dataRincian[$kodeSistem]['nominal_satuan']  = 0;
               $dataRincian[$kodeSistem]['nominal_total']   = 0;
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['nomor']       = '';
               $dataGrid[$index]['kode']        = $dataList[$i]['sub_kegiatan_kode'].'.'.$kegdet;
               $dataGrid[$index]['nama']        = $dataList[$i]['deskripsi'];
               $dataGrid[$index]['tipe']        = 'referensi';
            }elseif((int)$dataList[$i]['program_id'] === (int)$program
               && (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan
               && (int)$subkegiatan !== (int)$dataList[$i]['sub_kegiatan_id']){
               $subkegiatan      = (int)$dataList[$i]['sub_kegiatan_id'];
               $kodeSistem       = $program.'.'.$kegiatan.'.'.$subkegiatan;
               $dataRincian[$kodeSistem]['nominal_satuan']  = 0;
               $dataRincian[$kodeSistem]['nominal_total']   = 0;
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['nomor']       = '';
               $dataGrid[$index]['kode']        = $dataList[$i]['sub_kegiatan_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['sub_kegiatan_nama'];
               $dataGrid[$index]['tipe']        = 'sub_kegiatan';
               $dataGrid[$index]['rkakl']       = 'sub_kegiatan';
               $dataGrid[$index]['rkakl_nama']  = $dataList[$i]['rkakl_sub_kegiatan_nama'];
            }elseif((int)$dataList[$i]['program_id'] === (int)$program
               && (int)$dataList[$i]['kegiatan_id'] !== (int)$kegiatan){
               $kegiatan         = (int)$dataList[$i]['kegiatan_id'];
               $kodeSistem       = $program.'.'.$kegiatan;
               $dataRincian[$kodeSistem]['nominal_satuan']  = 0;
               $dataRincian[$kodeSistem]['nominal_total']   = 0;
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['nomor']       = '';
               $dataGrid[$index]['kode']        = $dataList[$i]['kegiatan_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['kegiatan_nama'];
               $dataGrid[$index]['tipe']        = 'kegiatan';
               $dataGrid[$index]['rkakl']       = 'output';
               $dataGrid[$index]['rkakl_nama']  = $dataList[$i]['rkakl_output_nama'];
               $dataGrid[$index]['ikk_nama']    = $dataList[$i]['ikk_nama'];
               $dataGrid[$index]['iku_nama']    = $dataList[$i]['iku_nama'];
               $dataGrid[$index]['output']      = '-';
            }else{
               $program          = (int)$dataList[$i]['program_id'];
               $kodeSistem       = $program;
               $dataRincian[$kodeSistem]['nominal_satuan']  = 0;
               $dataRincian[$kodeSistem]['nominal_total']   = 0;
               $dataGrid[$index]['kode_sistem'] = $kodeSistem;
               $dataGrid[$index]['kode']        = $dataList[$i]['program_kode'];
               $dataGrid[$index]['nama']        = $dataList[$i]['program_nama'];
               $dataGrid[$index]['nomor']       = '';
               $dataGrid[$index]['tipe']        = 'program';
               $dataGrid[$index]['rkakl']       = 'kegiatan';
               $dataGrid[$index]['rkakl_nama']  = $dataList[$i]['rkakl_kegiatan_nama'];
            }
            $index++;
         }

         $row     = 7;

         foreach ($dataGrid as $list) {
            $columnWidth      = $sheet->getColumnDimension('C')->getWidth();
            switch (strtoupper($list['tipe'])) {
               case 'PROGRAM':
                  $list['nominal_satuan']    = $dataRincian[$list['kode_sistem']]['nominal_satuan'];
                  $list['nominal_total']     = $dataRincian[$list['kode_sistem']]['nominal_total'];
                  $list['nama']              = $list['nama']."\n[".$list['rkakl_nama']."]";
                  $sheet->getStyle('I'.$row.':J'.$row)->getNumberFormat()->setFormatCode('#,##0.00_);(#,##0.00)');
                  $sheet->setCellValueExplicit('I'.$row, $list['nominal_satuan'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('J'.$row, $list['nominal_total'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->getStyle('A'.$row.':J'.$row)->getFont()->setBold(true);
                  break;
               case 'KEGIATAN':
                  $list['nominal_satuan']    = $dataRincian[$list['kode_sistem']]['nominal_satuan'];
                  $list['nominal_total']     = $dataRincian[$list['kode_sistem']]['nominal_total'];
                  $list['nama']              = $list['nama']."\n[".$list['rkakl_nama']."]";
                  $sheet->getStyle('I'.$row.':J'.$row)->getNumberFormat()->setFormatCode('#,##0.00_);(#,##0.00)');
                  $sheet->setCellValueExplicit('I'.$row, $list['nominal_satuan'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('J'.$row, $list['nominal_total'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->getStyle('A'.$row.':J'.$row)->getFont()->setBold(true);
                  break;
               case 'SUB_KEGIATAN':
                  $list['nominal_satuan']    = $dataRincian[$list['kode_sistem']]['nominal_satuan'];
                  $list['nominal_total']     = $dataRincian[$list['kode_sistem']]['nominal_total'];
                  $list['nama']              = $list['nama']."\n[".$list['rkakl_nama']."]";
                  $sheet->getStyle('I'.$row.':J'.$row)->getNumberFormat()->setFormatCode('#,##0.00_);(#,##0.00)');
                  $sheet->setCellValueExplicit('I'.$row, $list['nominal_satuan'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('J'.$row, $list['nominal_total'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->getStyle('A'.$row.':J'.$row)->getFont()->setBold(true);
                  break;
               case 'REFERENSI':
                  break;
               case 'RKAT':
                  $list['nominal_satuan']    = $dataRincian[$list['kode_sistem']]['nominal_satuan'];
                  $list['nominal_total']     = $dataRincian[$list['kode_sistem']]['nominal_total'];
                  $sheet->getStyle('I'.$row.':J'.$row)->getNumberFormat()->setFormatCode('#,##0.00_);(#,##0.00)');
                  $sheet->setCellValueExplicit('I'.$row, $list['nominal_satuan'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('J'.$row, $list['nominal_total'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->getStyle('A'.$row.':J'.$row)->getFont()->setBold(true);
                  break;
               case 'KOMPONEN':
                  $list['nominal_satuan']    = $list['nominal_satuan'];
                  $list['nominal_total']     = $list['nominal_total'];
                  $sheet->getStyle('I'.$row.':J'.$row)->getNumberFormat()->setFormatCode('#,##0.00_);(#,##0.00)');
                  $sheet->setCellValueExplicit('A'.$row, $list['nomor'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('I'.$row, $list['nominal_satuan'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('J'.$row, $list['nominal_total'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  break;
               default:
                  $list['nominal_satuan']    = $list['nominal_satuan'];
                  $list['nominal_total']     = $list['nominal_total'];
                  $sheet->getStyle('I'.$row.':J'.$row)->getNumberFormat()->setFormatCode('#,##0.00_);(#,##0.00)');
                  $sheet->setCellValueExplicit('A'.$row, $list['nomor'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('I'.$row, $list['nominal_satuan'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  $sheet->setCellValueExplicit('J'.$row, $list['nominal_total'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
                  break;
            }
            $rowHeight        = max(array(
               ceil(strlen($list['nama'])/45)*14,
               ceil(strlen($list['unit_nama'])/30)*14
            ));
            $sheet->getRowDimension($row)->setRowHeight($rowHeight+14);
            $sheet->setCellValueExplicit('B'.$row, $list['kode'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C'.$row, $list['nama'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D'.$row, $list['unit_nama'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('E'.$row, $list['iku_nama']);
            $sheet->setCellValue('F'.$row, $list['ikk_nama']);
            $sheet->setCellValue('G'.$row, $list['output']);
            $sheet->setCellValue('H'.$row, $list['volume']);
            $row+=1;
         }
         $sheet->getStyle('A7:J'.$row)->getAlignment()->setWrapText(true);
         $sheet->getStyle('A7:J'.$row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
         $sheet->getStyle('A7:J'.($row-1))->applyFromArray($borderTableStyledArray);
         $sheet->getStyle('A7:A'.($row-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $sheet->getStyle('B7:B'.($row-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
      }
      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>