<?php
/**
* ================= doc ====================
* FILENAME     : ViewExcelLapRekapProgram.xlsx.class.php
* @package     : ViewExcelLapRekapProgram
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-07-10
* @Modified    : 2014-07-10
* @Analysts    : Dyah Fajar N
* @contact     : eko.susilo@gamatechno.com
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/lap_rekap_program/business/AppLapRekapProgram.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'main/function/date.php';

class ViewExcelLapRekapProgram extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('laporan_rekapitulasi_program_pengeluaran_'.date('Ymd', time()).'.xls');

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

      $Obj              = new AppLapRekapProgram();
      $tahun_anggaran   = Dispatcher::Instance()->Decrypt($Obj->_GET['tahun_anggaran']);
      $program          = Dispatcher::Instance()->Decrypt($Obj->_GET['program']);
      $unitkerja        = Dispatcher::Instance()->Decrypt($Obj->_GET['unitkerja']);
      $jenis_kegiatan   = Dispatcher::Instance()->Decrypt($Obj->_GET['jenis_kegiatan']);
      $result           = $Obj->GetCetakData($tahun_anggaran, $program, $jenis_kegiatan, $unitkerja);
      $dataResume       = $Obj->GetResumeKegiatan($tahun_anggaran, $program, $jenis_kegiatan, $unitkerja);

      if($tahun_anggaran){
         $data_tahun_anggaran = $Obj->GetTahunAnggaranById($tahun_anggaran);
      }else {
         $data_tahun_anggaran['nama'] = "-";
      }

      if(isset($program)){
         $data_program = $Obj->GetProgramById($program);
      }else {
         $data_program['nama'] = " Semua ";
      }

      if($unitkerja){
         $data_unitkerja = $Obj->GetUnitkerjaById($unitkerja);
      }else {
         $data_unitkerja['nama'] = " Semua ";
      }

      if($jenis_kegiatan != '') {
         $data_jenis_kegiatan = $Obj->GetJenisKegiatanById($jenis_kegiatan);
      } else {
         $data_jenis_kegiatan['nama'] = " Semua ";
      }

      if (empty($result)) {
         $sheet->setCellValue('A1', 'data_kosong');
      } else {
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

         $sheet->mergeCells('A1:F1');
         $sheet->mergeCells('B3:F3');
         $sheet->mergeCells('B4:F4');

         $sheet->getRowDimension(1)->setRowHeight(20);
         $sheet->getRowDimension(7)->setRowHeight(26);
         $sheet->getColumnDimension('A')->setWidth(18);
         $sheet->getColumnDimension('B')->setWidth(25);
         $sheet->getColumnDimension('C')->setWidth(28);
         $sheet->getColumnDimension('D')->setWidth(30);
         $sheet->getColumnDimension('E')->setWidth(23);
         $sheet->getColumnDimension('F')->setWidth(23);
         $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);

         $sheet->setCellValue('A1', GTFWConfiguration::GetValue('language', 'laporan_rekapitulasi_program_pengeluaran'));
         $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
         $sheet->setCellValue('A3', GTFWConfiguration::GetValue('language', 'tahun_periode'));
         $sheet->setCellValueExplicit('B3', ': '.$data_tahun_anggaran['nama'], PHPExcel_Cell_DataType::TYPE_STRING);
         $sheet->setCellValue('A4', GTFWConfiguration::GetValue('language', 'program'));
         $sheet->setCellValueExplicit('B4', ': '.$data_program['nama'], PHPExcel_Cell_DataType::TYPE_STRING);
         $sheet->setCellValue('A5', GTFWConfiguration::GetValue('language', 'unit').'/'.GTFWConfiguration::GetValue('language','sub_unit'));
         $sheet->setCellValueExplicit('B5', ': '.$data_unitkerja['nama'], PHPExcel_Cell_DataType::TYPE_STRING);
         $sheet->getStyle('A3:F5')->getFont()->setBold(true);

         $sheet->setCellValue('A7', strtoupper(GTFWConfiguration::GetValue('language', 'kegiatan')));
         $sheet->setCellValue('B7', strtoupper(GTFWConfiguration::GetValue('language', 'nama')));
         $sheet->setCellValue('C7', strtoupper(GTFWConfiguration::GetValue('language', 'unit')."/\n".GTFWConfiguration::GetValue('language', 'sub_unit')));
         $sheet->setCellValue('D7', strtoupper(GTFWConfiguration::GetValue('language', 'deskripsi')));
         $sheet->setCellValue('E7', strtoupper(GTFWConfiguration::GetValue('language', 'nominal_usulan_rp')));
         $sheet->setCellValue('F7', strtoupper(GTFWConfiguration::GetValue('language', 'nominal_setuju_rp')));
         $sheet->getStyle('A7:F7')->applyFromArray($styledTableHeaderArray);

         $index         = 0;
         $program       = '';
         $kegiatan      = '';
         $dataList      = array();
         $dataRekap     = array();
         for ($i=0; $i < count($result);) {
            if($program == $result[$i]['kodeProg'] && $kegiatan == $result[$i]['kodeKegiatan']){
               $dataRekap[$program]['nominal_usulan']   += $result[$i]['nominalUsulan'];
               $dataRekap[$program]['nominal_setuju']   += $result[$i]['nominalSetuju'];

               $dataRekap[$kegiatan]['nominal_usulan']   += $result[$i]['nominalUsulan'];
               $dataRekap[$kegiatan]['nominal_setuju']   += $result[$i]['nominalSetuju'];

               $dataList[$index]['kode']        = $result[$i]['kodeSubKegiatan'];
               $dataList[$index]['nama']        = $result[$i]['namaSubKegiatan'];
               $dataList[$index]['unit']        = $result[$i]['unitName'];
               $dataList[$index]['deskripsi']   = $result[$i]['deskripsi'];
               $dataList[$index]['nominal_usulan'] = $result[$i]['nominalUsulan'];
               $dataList[$index]['nominal_setuju'] = $result[$i]['nominalSetuju'];
               $dataList[$index]['type']        = 'sub_kegiatan';
               $i++;
            }elseif($program == $result[$i]['kodeProg'] && $kegiatan != $result[$i]['kodeKegiatan']){
               $kegiatan      = $result[$i]['kodeKegiatan'];
               $dataRekap[$kegiatan]['nominal_usulan']   = 0;
               $dataRekap[$kegiatan]['nominal_setuju']   = 0;
               $dataList[$index]['kode']     = $result[$i]['kodeKegiatan'];
               $dataList[$index]['nama']     = $result[$i]['namaKegiatan'];
               $dataList[$index]['unit']     = '';
               $dataList[$index]['type']     = 'kegiatan';
               $dataList[$index]['deskripsi']   = '';
            }else{
               $program       = $result[$i]['kodeProg'];
               $dataRekap[$program]['nominal_usulan']   = 0;
               $dataRekap[$program]['nominal_setuju']   = 0;
               $dataList[$index]['kode']     = $result[$i]['kodeProg'];
               $dataList[$index]['nama']     = $result[$i]['namaProgram'];
               $dataList[$index]['type']     = 'program';
               $dataList[$index]['unit']     = '';
               $dataList[$index]['deskripsi']   = '';
            }
            $index++;
         }

         $row     = 8;
         $nb      = array();
         foreach ($dataList as $list) {
            $sheet->setCellValue('A'.$row, $list['kode']);
            $sheet->setCellValue('B'.$row, $list['nama']);
            $sheet->setCellValue('C'.$row, $list['unit']);
            $sheet->setCellValue('D'.$row, $list['deskripsi']);
            if (strtolower($list['type']) == 'sub_kegiatan') {
               $sheet->setCellValueExplicit('E'.$row, $list['nominal_usulan'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->setCellValueExplicit('F'.$row, $list['nominal_setuju'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            }else{
               $sheet->setCellValueExplicit('E'.$row, $dataRekap[$list['kode']]['nominal_usulan'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->setCellValueExplicit('F'.$row, $dataRekap[$list['kode']]['nominal_setuju'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            }

            switch (strtolower($list['type'])) {
               case 'program':
                  $sheet->getStyle('A'.$row.':F'.$row)->getFont()->setBold(true)->setSize(11);
                  break;
               case 'kegiatan':
                  $sheet->getStyle('A'.$row.':F'.$row)->getFont()->setBold(true);
                  break;
               default:
                  // nothing
                  break;
            }

            $max  = max(array(
               ceil(strlen($list['nama'])/$sheet->getColumnDimension('B')->getWidth()),
               ceil(strlen($list['unit'])/$sheet->getColumnDimension('C')->getWidth()),
               ceil(strlen($list['deskripsi'])/$sheet->getColumnDimension('D')->getWidth())
            ))*15.8;
            $sheet->getRowDimension($row)->setRowHeight($max);

            $row++;
         }

         $sheet->getStyle('A8:F'.($row-1))->getAlignment()->setWrapText(true)->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
         $sheet->getStyle('A8:F'.($row-1))->applyFromArray($borderTableStyledArray);
         $sheet->getStyle('A8:A'.($row-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $sheet->getStyle('E8:F'.($row-1))->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');

         $newRow     = $row+1;
         $sheet->mergeCells('A'.($newRow).':F'.($newRow));
         $sheet->mergeCells('B'.($newRow+1).':D'.($newRow+1));
         $sheet->getRowDimension(($newRow+1))->setRowHeight(20);
         $sheet->getRowDimension(($newRow))->setRowHeight(20);
         $sheet->getStyle('A'.$newRow.':F'.$newRow)->getFont()->setBold(true);
         $sheet->getStyle('A'.$newRow.':F'.$newRow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
         $sheet->setCellValue('A'.$newRow, GTFWConfiguration::GetValue('language', 'resume'));
         $sheet->setCellValue('A'.($newRow+1), GTFWConfiguration::GetValue('language', 'kode'));
         $sheet->setCellValue('B'.($newRow+1), GTFWConfiguration::GetValue('language', 'program'));
         $sheet->setCellValue('E'.($newRow+1), GTFWConfiguration::GetValue('language', 'nominal_usulan_rp'));
         $sheet->setCellValue('F'.($newRow+1), GTFWConfiguration::GetValue('language', 'nominal_setuju_rp'));

         $sheet->getStyle('A'.($newRow+1).':F'.($newRow+1))->applyFromArray($styledTableHeaderArray);

         $rows       = ($newRow+2);

         foreach($dataResume as $res) {
            $sheet->setCellValueExplicit('A'.$rows, $res['kode'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('B'.$rows, $res['namaKegiatan']);
            $sheet->mergeCells('B'.$rows.':D'.$rows);
            $sheet->setCellValueExplicit('E'.$rows, $res['nominal_usulan'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('F'.$rows, $res['nominal_setuju'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $rows++;
         }

         $sheet->getStyle('E'.($newRow+2).':F'.($rows-1))->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
         $sheet->getStyle('A'.($newRow+2).':A'.($rows-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $sheet->getStyle('A'.($newRow+2).':F'.($rows-1))->applyFromArray($borderTableStyledArray);

         $unitData   = $Obj->GetUnitIdentity($unitkerja);
         $pimpinan   = $unitData['0']['unitkerjaNamaPimpinan'];

         $date       = date('d-m-Y');
         $date       = IndonesianDate($date,'dd-mm-yyyy');
         $kota       = GTFWConfiguration::GetValue('organization', 'city');
         $sheet->setCellValue('E'.($rows+2),  $kota.', '.$date,$sign);
         $sheet->getRowDimension(($rows+3))->setRowHeight(30);
         $sheet->setCellValue('E'.($rows+3), GTFWConfiguration::GetValue('language', 'pimpinan')."\n".GTFWConfiguration::GetValue('language', 'unit').'/'.GTFWConfiguration::GetValue('language', 'sub_unit'));
         $sheet->setCellValue('E'.($rows+7), $unitData['0']['unitkerjaNama']);
         if(!empty($pimpinan)){
            $sheet->setCellValue('E'.($rows+8), '('.$pimpinan.')');
         }else{
            $sheet->setCellValue('E'.($rows+8), '('.str_repeat('.', 50).')');
         }
         $sheet->getStyle('E'.($rows+2).':F'.($rows+8))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
         $sheet->mergeCells('E'.($rows+2).':F'.($rows+2));
         $sheet->mergeCells('E'.($rows+3).':F'.($rows+3));
         $sheet->mergeCells('E'.($rows+4).':F'.($rows+6));
         $sheet->mergeCells('E'.($rows+7).':F'.($rows+7));
         $sheet->mergeCells('E'.($rows+8).':F'.($rows+8));
      }
      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>