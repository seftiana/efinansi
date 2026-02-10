<?php
/**
* ================= doc ====================
* FILENAME     : ViewExcelUnitKerja.xlsx.class.php
* @package     : ViewExcelUnitKerja
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-07-11
* @Modified    : 2014-07-11
* @Analysts    : Dyah Fajar N
* @contact     : eko.susilo@gamatechno.com
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/lap_rekap_unitkerja/business/RekapUnitKerja.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'main/function/date.php';
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
      $this->SetFileName('laporan_rekapitulasi_unitkerja_'.date('Ymd', time()).'.xls');

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
      $date          = date('d-m-Y');
      $date          = IndonesianDate($date,'dd-mm-yyyy');
      $requestData['ta_id']            = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_id']);
      $requestData['ta_nama']          = Dispatcher::Instance()->Decrypt($mObj->_GET['ta_nama']);
      $requestData['unit_nama']        = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_nama']);
      $requestData['unit_id']          = Dispatcher::Instance()->Decrypt($mObj->_GET['unit_id']);
      $requestData['program_id']       = Dispatcher::Instance()->Decrypt($mObj->_GET['program_id']);
      $requestData['program_nama']     = Dispatcher::Instance()->Decrypt($mObj->_GET['program_nama']);
      $requestData['jenis_kegiatan']   = Dispatcher::Instance()->Decrypt($mObj->_GET['jenis_kegiatan']);

      $unitData      = $mObj->ChangeKeyName($mObj->GetUnitIdentity($requestData['unit_id']));
      $offset        = 0;
      $limit         = 10000;
      $page          = 0;
      if(isset($_GET['page'])){
         $page       = (string) $_GET['page']->StripHtmlTags()->SqlString()->Raw();
         $offset     = ($page - 1) * $limit;
      }
      $dataList      = $mObj->ChangeKeyName($mObj->GetData($offset, $limit, (array)$requestData));
      $dataResume    = $mObj->ChangeKeyName($mObj->GetResumeUnitKerja((array)$requestData));

      if(empty($dataList)){
         $sheet->setCellValue('A1', 'data kosong');
      }else{
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

         $label      = GTFWConfiguration::GetValue('language', 'laporan_rekapitulasi_unitkerja');
         $index      = 0;
         $dataGrid   = array();
         $program    = '';
         $kegiatan   = '';
         $unit       = '';
         $dataRekap  = array();

         for ($i=0; $i < count($dataList);) {
            if((int)$dataList[$i]['unit_id'] === (int)$unit && (int)$dataList[$i]['program_id'] === (int)$program && (int)$dataList[$i]['kegiatan_id'] === (int)$kegiatan){

               $programKodeSistem         = $unit.'.'.$program;
               $kegiatanKodeSistem        = $unit.'.'.$program.'.'.$kegiatan;

               $dataRekap[$programKodeSistem]['nominal_usulan']   += $dataList[$i]['nominal_usulan'];
               $dataRekap[$programKodeSistem]['nominal_setuju']   += $dataList[$i]['nominal_setuju'];
               $dataRekap[$kegiatanKodeSistem]['nominal_usulan']  += $dataList[$i]['nominal_usulan'];
               $dataRekap[$kegiatanKodeSistem]['nominal_setuju']  += $dataList[$i]['nominal_setuju'];

               $dataGrid[$index]['id']    = $dataList[$i]['sub_kegiatan_id'];
               $dataGrid[$index]['kode']  = $dataList[$i]['sub_kegiatan_kode'];
               $dataGrid[$index]['nama']  = $dataList[$i]['sub_kegiatan_nama'];
               $dataGrid[$index]['nominal_usulan']    = $dataList[$i]['nominal_usulan'];
               $dataGrid[$index]['nominal_setuju']    = $dataList[$i]['nominal_setuju'];
               $dataGrid[$index]['level']             = 'sub_kegiatan';
               $i++;
            }elseif((int)$dataList[$i]['unit_id'] === (int)$unit && (int)$dataList[$i]['program_id'] === (int)$program && (int)$dataList[$i]['kegiatan_id'] !== (int)$kegiatan){
               $kegiatan                  = (int)$dataList[$i]['kegiatan_id'];
               $kodeSistem                = $unit.'.'.$program.'.'.$dataList[$i]['kegiatan_id'];
               // unset nominal
               $dataRekap[$kodeSistem]['nominal_usulan']    = 0;
               $dataRekap[$kodeSistem]['nominal_setuju']    = 0;

               $dataGrid[$index]['id']    = $dataList[$i]['kegiatan_id'];
               $dataGrid[$index]['kode']  = $dataList[$i]['kegiatan_kode'];
               $dataGrid[$index]['nama']  = $dataList[$i]['kegiatan_nama'];
               $dataGrid[$index]['kode_sistem']    = $kodeSistem;
               $dataGrid[$index]['level']          = 'kegiatan';
            }elseif((int)$dataList[$i]['unit_id'] === (int)$unit && (int)$dataList[$i]['program_id'] !== (int)$program){
               $program    = (int)$dataList[$i]['program_id'];
               $index--;
            }else{
               $unit                      = (int)$dataList[$i]['unit_id'];
               $kodeSistem                = $unit.'.'.$dataList[$i]['program_id'];
               // unset nominal
               $dataRekap[$kodeSistem]['nominal_usulan']    = 0;
               $dataRekap[$kodeSistem]['nominal_setuju']    = 0;

               $dataGrid[$index]['id']    = $dataList[$i]['program_id'];
               $dataGrid[$index]['kode']  = $dataList[$i]['program_kode'];
               $dataGrid[$index]['nama']  = $dataList[$i]['program_nama'];
               $dataGrid[$index]['unit_nama']      = $dataList[$i]['unit_nama'];
               $dataGrid[$index]['kode_sistem']    = $kodeSistem;
               $dataGrid[$index]['level']          = 'program';
            }
            $index++;
         }

         $sheet->getRowDimension(1)->setRowHeight(20);
         $sheet->getRowDimension(6)->setRowHeight(24);
         $sheet->getColumnDimension('B')->setWidth(18);
         $sheet->getColumnDimension('C')->setWidth(20);
         $sheet->getColumnDimension('D')->setWidth(60);
         $sheet->getColumnDimension('E')->setWidth(23);
         $sheet->getColumnDimension('F')->setWidth(23);
         $sheet->mergeCells('A1:E1');
         $sheet->mergeCells('A6:B6');
         $sheet->mergeCells('A3:B3');
         $sheet->mergeCells('A4:B4');

         $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
         $sheet->getStyle('A3:F4')->getFont()->setBold(true);
         $sheet->getStyle('A6:F6')->applyFromArray($styledTableHeaderArray);

         $sheet->setCellValue('A1', $label);
         $sheet->setCellValue('A3', GTFWConfiguration::GetValue('language', 'tahun_periode'));
         $sheet->setCellValueExplicit('C3', ': '.$dataList[0]['ta_nama'], PHPExcel_Cell_DataType::TYPE_STRING);
         $sheet->setCellValue('A4', GTFWConfiguration::GetValue('language', 'unit'));
         $sheet->setCellValue('C4', ': '.$unitData[0]['unitkerja_nama']);

         // data-table

         $sheet->setCellValue('A6', GTFWConfiguration::GetValue('language', 'nama_unit').'/'.GTFWConfiguration::GetValue('language', 'sub_unit'));
         $sheet->setCellValue('C6', GTFWConfiguration::GetValue('language', 'kegiatan'));
         $sheet->setCellValue('D6', GTFWConfiguration::GetValue('language', 'nama')."\n(".GTFWConfiguration::GetValue('language', 'program').','.GTFWConfiguration::GetValue('language', 'kegiatan').','.GTFWConfiguration::GetValue('language', 'sub_kegiatan').')');
         $sheet->setCellValue('E6', GTFWConfiguration::GetValue('language', 'nominal_usulan_rp'));
         $sheet->setCellValue('F6', GTFWConfiguration::GetValue('language', 'nominal_setuju_rp'));
         $row  = 7;
         foreach ($dataGrid as $list) {
            switch (strtoupper($list['level'])) {
               case 'PROGRAM':
                  $list['nominal_usulan']       = $dataRekap[$list['kode_sistem']]['nominal_usulan'];
                  $list['nominal_setuju']       = $dataRekap[$list['kode_sistem']]['nominal_setuju'];
                  $unit_kerja_nama              = $list['unit_nama'];
                  $sheet->getStyle('A'.$row.':F'.$row)->getFont()->setBold(true);
                  $sheet->getStyle('A'.$row.':F'.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('00CCCCCC');
                  break;
               case 'KEGIATAN':
                  $list['nominal_usulan']       = $dataRekap[$list['kode_sistem']]['nominal_usulan'];
                  $list['nominal_setuju']       = $dataRekap[$list['kode_sistem']]['nominal_setuju'];
                  $unit_kerja_nama              = '-';
                  $sheet->getStyle('A'.$row.':F'.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('00DCDCDC');
                  break;
               default:
                  $list['nominal_usulan']       = $list['nominal_usulan'];
                  $list['nominal_setuju']       = $list['nominal_setuju'];
                  $unit_kerja_nama              = '-';
                  break;
            }
            $max  = max(array(
               ceil(strlen($unit_kerja_nama)/($sheet->getColumnDimension('B')->getWidth()+$sheet->getColumnDimension('B')->getWidth())),
               ceil(strlen($list['kode'])/$sheet->getColumnDimension('C')->getWidth()),
               ceil(strlen($list['nama'])/$sheet->getColumnDimension('D')->getWidth())
            ))*15.8;
            $sheet->getRowDimension($row)->setRowHeight($max);
            $sheet->mergeCells('A'.$row.':B'.$row);
            $sheet->setCellValue('A'.$row, $list['unit_nama']);
            $sheet->setCellValueExplicit('C'.$row, $list['kode'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('D'.$row, $list['nama']);
            $sheet->setCellValueExplicit('E'.$row, $list['nominal_usulan'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('F'.$row, $list['nominal_setuju'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $row++;
         }

         $sheet->getStyle('A7:F'.($row-1))->getAlignment()->setWrapText(true)->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
         $sheet->getStyle('A7:F'.($row-1))->applyFromArray($borderTableStyledArray);
         $sheet->getStyle('C7:C'.($row-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $sheet->getStyle('E7:F'.($row-1))->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');

         $sheet->mergeCells('A'.($row+1).':F'.($row+1));
         $sheet->mergeCells('A'.($row+2).':D'.($row+2));
         $sheet->setCellValue('A'.($row+1), GTFWConfiguration::GetValue('language', 'resume'));
         $sheet->setCellValue('A'.($row+2), GTFWConfiguration::GetValue('language', 'unit').'/'.GTFWConfiguration::GetValue('language', 'sub_unit'));
         $sheet->setCellValue('E'.($row+2), GTFWConfiguration::GetValue('language', 'nominal_usulan_rp'));
         $sheet->setCellValue('F'.($row+2), GTFWConfiguration::GetValue('language', 'nominal_setuju_rp'));
         $sheet->getStyle('A'.($row+1).':F'.($row+2))->applyFromArray($styledTableHeaderArray);
         $sheet->getStyle('A'.($row+1).':F'.($row+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
         $rows       = $row+3;
         foreach ($dataResume as $resume) {
            $sheet->mergeCells('A'.$rows.':D'.$rows);
            $sheet->setCellValue('A'.$rows, $resume['unit_nama']);
            $sheet->setCellValueExplicit('E'.$rows, $resume['nominal_usulan'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('F'.$rows, $resume['nominal_setuju'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $rows++;
         }

         $sheet->getStyle('A'.($row+3).':F'.($rows-1))->applyFromArray($borderTableStyledArray);
         $sheet->getStyle('E'.($row+3).':F'.($rows-1))->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');


         $rowSign       = $rows+2;
         $sheet->mergeCells('E'.($rowSign).':F'.($rowSign));
         $sheet->mergeCells('E'.($rowSign+1).':F'.($rowSign+1));
         $sheet->mergeCells('E'.($rowSign+2).':F'.($rowSign+2));
         $sheet->mergeCells('E'.($rowSign+3).':F'.($rowSign+6));
         $sheet->mergeCells('E'.($rowSign+7).':F'.($rowSign+7));
         $sheet->getStyle('E'.$rowSign.':F'.($rowSign+7))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
         $sheet->setCellValue('E'.$rowSign, GTFWConfiguration::GetValue('organization', 'city').', '.$date);
         $sheet->setCellValue('E'.($rowSign+1), GTFWConfiguration::GetValue('language', 'pimpinan_unit_kerja').'/'.GTFWConfiguration::GetValue('language', 'sub_unit'));
         $sheet->setCellValue('E'.($rowSign+2), $unitData[0]['unitkerja_nama']);
         $sheet->setCellValue('E'.($rowSign+7), empty($pimpinan) ? '('.str_repeat('.', 50).')' : $pimpinan);
      }

      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>