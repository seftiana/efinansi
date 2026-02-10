<?php
/**
* ================= doc ====================
* FILENAME     : ViewExcelLapPenerimaanPNBP.xlsx.class.php
* @package     : ViewExcelLapPenerimaanPNBP
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-07-10
* @Modified    : 2014-07-10
* @Analysts    : Dyah Fajar N
* @contact     : eko.susilo@gamatechno.net
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/lap_realisasi_penerimaan_pnbp/business/AppLapPenerimaanPNBP.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';
class ViewExcelLapPenerimaanPNBP extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('laporan_penerimaan_'.date('Ymd', time()).'.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(false); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle('Laporan Penerimaan');
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

      $Obj              = new AppLapPenerimaanPNBP();
      $tahun_anggaran   = Dispatcher::Instance()->Decrypt($Obj->_GET['tgl']);
      $unitkerja_label  = Dispatcher::Instance()->Decrypt($Obj->_GET['unitkerja_label']);
      $unitkerja        = Dispatcher::Instance()->Decrypt($Obj->_GET['unitkerja']);
      $userId           = Dispatcher::Instance()->Decrypt($Obj->_GET['id']);
      $data             = $Obj->GetDataRealisasiPNBPCetak($tahun_anggaran, $unitkerja);
      $unitkerja        = $Obj->GetUnitKerja($unitkerjaId);
      $tahunanggaran    = $Obj->GetTahunAnggaran($tahun_anggaran);
      $unitkerja_nama   = $unitkerja_label;
      $tahunanggaran_nama = $tahunanggaran['name'];

      if (empty($data)) {
         $sheet->setCellValue('A1', 'Data kosong');
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
         $fontStyle         = array(
            'font' => array(
               'italic' => true
            )
         );
         $unitStyle = array(
            'fill' => array(
               'type' => PHPExcel_Style_Fill::FILL_SOLID,
               'startcolor' => array(
                  'argb' => 'ffE6E6E6'
               )
            )
         );

         /**
          * set label
          */
         $lap_penerimaan_label   = GTFWConfiguration::GetValue('language','lap_penerimaan_pnbp');
         $unit_sub_unit_label    = GTFWConfiguration::GetValue('language','unit').'/'. GTFWConfiguration::GetValue('language','sub_unit');
         $tahun_periode_label    = GTFWConfiguration::GetValue('language','tahun_periode');
         $no_label               = GTFWConfiguration::GetValue('language','no');
         $kode_label             = GTFWConfiguration::GetValue('language','kode');
         $unit_kerja_label       = GTFWConfiguration::GetValue('language','unit_kerja');
         $jenis_penerimaan_label = GTFWConfiguration::GetValue('language','jenis_penerimaan');
         $target_pnbp_label      = GTFWConfiguration::GetValue('language','target_pnbp');
         $realisasi_pnbp_label   = GTFWConfiguration::GetValue('language','realisasi_pnbp');
         $total_label            = GTFWConfiguration::GetValue('language','total');
         $januarai_label         = GTFWConfiguration::GetValue('language','januari');
         $februari_label         = GTFWConfiguration::GetValue('language','februari');
         $maret_label            = GTFWConfiguration::GetValue('language','maret');
         $april_label            = GTFWConfiguration::GetValue('language','april');
         $mei_label              = GTFWConfiguration::GetValue('language','mei');
         $juni_label             = GTFWConfiguration::GetValue('language','juni');
         $juli_label             = GTFWConfiguration::GetValue('language','juli');
         $agustus_label          = GTFWConfiguration::GetValue('language','agustus');
         $september_label        = GTFWConfiguration::GetValue('language','september');
         $oktober_label          = GTFWConfiguration::GetValue('language','oktober');
         $november_label         = GTFWConfiguration::GetValue('language','november');
         $desember_label         = GTFWConfiguration::GetValue('language','desember');
         $total_realisasi_label  = GTFWConfiguration::GetValue('language','total_realisasi');
         $surplus_defisit_label  = 'Surplus Defisit';
         /**
          * end set label
          */
         $sheet->mergeCells('A1:R1');
         $sheet->mergeCells('A3:B3');
         $sheet->mergeCells('C3:R3');
         $sheet->mergeCells('A4:B4');
         $sheet->mergeCells('C4:R4');
         $sheet->mergeCells('A6:A7');
         $sheet->mergeCells('B6:B7');
         $sheet->mergeCells('C6:C7');
         $sheet->mergeCells('D6:D7');
         $sheet->mergeCells('E6:P6');
         $sheet->mergeCells('Q6:Q7');
         $sheet->mergeCells('R6:R7');

         $sheet->getRowDimension(1)->setRowHeight(20);
         $sheet->getColumnDimension('A')->setWidth(8);
         $sheet->getColumnDimension('B')->setWidth(18);
         $sheet->getColumnDimension('C')->setWidth(40);
         $sheet->getRowDimension('6')->setRowHeight(18);
         $sheet->getRowDimension('7')->setRowHeight(18);
         $sheet->getColumnDimension('D')->setWidth(23);
         $sheet->getColumnDimension('E')->setWidth(23);
         $sheet->getColumnDimension('F')->setWidth(23);
         $sheet->getColumnDimension('G')->setWidth(23);
         $sheet->getColumnDimension('H')->setWidth(23);
         $sheet->getColumnDimension('I')->setWidth(23);
         $sheet->getColumnDimension('J')->setWidth(23);
         $sheet->getColumnDimension('K')->setWidth(23);
         $sheet->getColumnDimension('L')->setWidth(23);
         $sheet->getColumnDimension('M')->setWidth(23);
         $sheet->getColumnDimension('N')->setWidth(23);
         $sheet->getColumnDimension('O')->setWidth(23);
         $sheet->getColumnDimension('P')->setWidth(23);
         $sheet->getColumnDimension('Q')->setWidth(23);
         $sheet->getColumnDimension('R')->setWidth(23);

         $sheet->getStyle('A1:R1')->applyFromArray($headerStyle);
         $sheet->getStyle('A3:R5')->getFont()->setBold(true);
         $sheet->getStyle('A6:R7')->applyFromArray($styledTableHeaderArray);
         $sheet->setCellValue('A1', $lap_penerimaan_label);
         $sheet->setCellValue('A3', $tahun_periode_label);
         $sheet->setCellValueExplicit('C3', ': '.$tahunanggaran_nama, PHPExcel_Cell_DataType::TYPE_STRING);
         $sheet->setCellValue('A4', $unit_sub_unit_label);
         $sheet->setCellValueExplicit('C4', ': '.$unitkerja_nama, PHPExcel_Cell_DataType::TYPE_STRING);

         // cell-data
         // table-header
         $sheet->setCellValue('A6', $no_label);
         $sheet->setCellValue('B6', $kode_label);
         $sheet->setCellValue('C6', $unit_kerja_label." / ". $jenis_penerimaan_label);
         $sheet->setCellValue('D6', $target_pnbp_label);
         $sheet->setCellValue('E6', $realisasi_pnbp_label);
         $sheet->setCellValue('E7', $januarai_label);
         $sheet->setCellValue('F7', $februari_label);
         $sheet->setCellValue('G7', $maret_label);
         $sheet->setCellValue('H7', $april_label);
         $sheet->setCellValue('I7', $mei_label);
         $sheet->setCellValue('J7', $juni_label);
         $sheet->setCellValue('K7', $juli_label);
         $sheet->setCellValue('L7', $agustus_label);
         $sheet->setCellValue('M7', $september_label);
         $sheet->setCellValue('N7', $oktober_label);
         $sheet->setCellValue('O7', $november_label);
         $sheet->setCellValue('P7', $desember_label);

         $sheet->setCellValue('Q6', $total_realisasi_label);
         $sheet->setCellValue('R6', $surplus_defisit_label);

         $row              = 8;
         $index            = 0;
         $dataList         = array();
         $unit             = '';
         $nomor            = 1;
         $dataPenerimaan   = array();
         $dataPnbp['target_pnbp']   = 0;
         $dataPnbp['realJan']       = 0;
         $dataPnbp['realFeb']       = 0;
         $dataPnbp['realMar']       = 0;
         $dataPnbp['realApr']       = 0;
         $dataPnbp['realMei']       = 0;
         $dataPnbp['realJun']       = 0;
         $dataPnbp['realJul']       = 0;
         $dataPnbp['realAgs']       = 0;
         $dataPnbp['realSep']       = 0;
         $dataPnbp['realOkt']       = 0;
         $dataPnbp['realNov']       = 0;
         $dataPnbp['realDes']       = 0;
         $dataPnbp['total_realisasi']  = 0;
         for ($i=0; $i < count($data);) {
            if((int)$unit === (int)$data[$i]['idunit']){
               $dataPenerimaan[$unit]['target_pnbp']  += (float)$data[$i]['target_pnbp'];
               $dataPenerimaan[$unit]['realJan']      += (float)$data[$i]['realJan'];
               $dataPenerimaan[$unit]['realFeb']      += (float)$data[$i]['realFeb'];
               $dataPenerimaan[$unit]['realMar']      += (float)$data[$i]['realMar'];
               $dataPenerimaan[$unit]['realApr']      += (float)$data[$i]['realApr'];
               $dataPenerimaan[$unit]['realMei']      += (float)$data[$i]['realMei'];
               $dataPenerimaan[$unit]['realJun']      += (float)$data[$i]['realJun'];
               $dataPenerimaan[$unit]['realJul']      += (float)$data[$i]['realJul'];
               $dataPenerimaan[$unit]['realAgs']      += (float)$data[$i]['realAgs'];
               $dataPenerimaan[$unit]['realSep']      += (float)$data[$i]['realSep'];
               $dataPenerimaan[$unit]['realOkt']      += (float)$data[$i]['realOkt'];
               $dataPenerimaan[$unit]['realNov']      += (float)$data[$i]['realNov'];
               $dataPenerimaan[$unit]['realDes']      += (float)$data[$i]['realDes'];
               $dataPenerimaan[$unit]['total_realisasi']    += (float)$data[$i]['total_realisasi'];

               $dataPnbp['target_pnbp']     += (float)$data[$i]['target_pnbp'];
               $dataPnbp['realJan']         += (float)$data[$i]['realJan'];
               $dataPnbp['realFeb']         += (float)$data[$i]['realFeb'];
               $dataPnbp['realMar']         += (float)$data[$i]['realMar'];
               $dataPnbp['realApr']         += (float)$data[$i]['realApr'];
               $dataPnbp['realMei']         += (float)$data[$i]['realMei'];
               $dataPnbp['realJun']         += (float)$data[$i]['realJun'];
               $dataPnbp['realJul']         += (float)$data[$i]['realJul'];
               $dataPnbp['realAgs']         += (float)$data[$i]['realAgs'];
               $dataPnbp['realSep']         += (float)$data[$i]['realSep'];
               $dataPnbp['realOkt']         += (float)$data[$i]['realOkt'];
               $dataPnbp['realNov']         += (float)$data[$i]['realNov'];
               $dataPnbp['realDes']         += (float)$data[$i]['realDes'];
               $dataPnbp['total_realisasi'] += (float)$data[$i]['total_realisasi'];

               $dataList[$index]['kode']     = $data[$i]['kode'];
               $dataList[$index]['nama']     = $data[$i]['jenisBiayaNama'];
               $dataList[$index]['nomor']    = $nomor;
               $dataList[$index]['type']     = 'child';
               $dataList[$index]['keterangan']    = $data[$i]['keterangan'];
               $dataList[$index]['target_pnbp']    = $data[$i]['target_pnbp'];
               $dataList[$index]['realJan']  = $data[$i]['realJan'];
               $dataList[$index]['realFeb']  = $data[$i]['realFeb'];
               $dataList[$index]['realMar']  = $data[$i]['realMar'];
               $dataList[$index]['realApr']  = $data[$i]['realApr'];
               $dataList[$index]['realMei']  = $data[$i]['realMei'];
               $dataList[$index]['realJun']  = $data[$i]['realJun'];
               $dataList[$index]['realJul']  = $data[$i]['realJul'];
               $dataList[$index]['realAgs']  = $data[$i]['realAgs'];
               $dataList[$index]['realSep']  = $data[$i]['realSep'];
               $dataList[$index]['realOkt']  = $data[$i]['realOkt'];
               $dataList[$index]['realNov']  = $data[$i]['realNov'];
               $dataList[$index]['realDes']  = $data[$i]['realDes'];
               $dataList[$index]['total_realisasi']   = $data[$i]['total_realisasi'];
               $nomor++;
               $i++;
            }else{
               $unit       = (int)$data[$i]['idunit'];
               $nomor      = 1;
               $dataPenerimaan[$unit]['target_pnbp'] = 0;
               $dataPenerimaan[$unit]['realJan'] = 0;
               $dataPenerimaan[$unit]['realFeb'] = 0;
               $dataPenerimaan[$unit]['realMar'] = 0;
               $dataPenerimaan[$unit]['realApr'] = 0;
               $dataPenerimaan[$unit]['realMei'] = 0;
               $dataPenerimaan[$unit]['realJun'] = 0;
               $dataPenerimaan[$unit]['realJul'] = 0;
               $dataPenerimaan[$unit]['realAgs'] = 0;
               $dataPenerimaan[$unit]['realSep'] = 0;
               $dataPenerimaan[$unit]['realOkt'] = 0;
               $dataPenerimaan[$unit]['realNov'] = 0;
               $dataPenerimaan[$unit]['realDes'] = 0;
               $dataPenerimaan[$unit]['total_realisasi'] = 0;
               $dataList[$index]['nomor']    = '-';
               $dataList[$index]['id']       = $data[$i]['idunit'];
               $dataList[$index]['kode']     = $data[$i]['kode_unit'];
               $dataList[$index]['nama']     = $data[$i]['nama_unit'];
               $dataList[$index]['type']     = 'parent';
            }

            $index++;
         }


         foreach ($dataList as $list) {
            $sheet->setCellValue('A'.$row, $list['nomor']);
            $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValueExplicit('B'.$row, $list['kode'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('C'.$row, $list['nama']);
            $sheet->getStyle('C'.$row)->getFont()->setBold(true);

            if($list['type'] == 'parent'){
               $sheet->getStyle('C'.$row.':C'.$row)->applyFromArray(array(
                  'borders' => array(
                     'bottom' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'ff000000')
                     ), 'top' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'ff000000')
                     )
               )));
               $sheet->getStyle('A'.$row.':R'.$row)->applyFromArray($unitStyle);
               $sheet->getStyle('A'.$row.':R'.$row)->getFont()->setBold(true)->setUnderline(true);
               /*
               $sheet->setCellValueExplicit('D'.$row, $dataPenerimaan[$list['id']]['target_pnbp'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->setCellValueExplicit('E'.$row, $dataPenerimaan[$list['id']]['realJan'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->setCellValueExplicit('F'.$row, $dataPenerimaan[$list['id']]['realFeb'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->setCellValueExplicit('G'.$row, $dataPenerimaan[$list['id']]['realMar'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->setCellValueExplicit('H'.$row, $dataPenerimaan[$list['id']]['realApr'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->setCellValueExplicit('I'.$row, $dataPenerimaan[$list['id']]['realMei'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->setCellValueExplicit('J'.$row, $dataPenerimaan[$list['id']]['realJun'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->setCellValueExplicit('K'.$row, $dataPenerimaan[$list['id']]['realJul'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->setCellValueExplicit('L'.$row, $dataPenerimaan[$list['id']]['realAgs'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->setCellValueExplicit('M'.$row, $dataPenerimaan[$list['id']]['realSep'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->setCellValueExplicit('N'.$row, $dataPenerimaan[$list['id']]['realOkt'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->setCellValueExplicit('O'.$row, $dataPenerimaan[$list['id']]['realNov'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->setCellValueExplicit('P'.$row, $dataPenerimaan[$list['id']]['realDes'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->setCellValueExplicit('Q'.$row, $dataPenerimaan[$list['id']]['total_realisasi'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               */
            }else{
               $sheet->getStyle('C'.$row.':C'.($row+1))->applyFromArray(array(
                  'borders' => array(
                     'bottom' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'ff000000')
                     ), 'top' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'ff000000')
                     )
               )));

               $sheet->mergeCells('A'.$row.':A'.($row+1));
               $sheet->mergeCells('B'.$row.':B'.($row+1));
               $sheet->mergeCells('D'.$row.':D'.($row+1));
               $sheet->mergeCells('E'.$row.':E'.($row+1));
               $sheet->mergeCells('F'.$row.':F'.($row+1));
               $sheet->mergeCells('G'.$row.':G'.($row+1));
               $sheet->mergeCells('H'.$row.':H'.($row+1));
               $sheet->mergeCells('I'.$row.':I'.($row+1));
               $sheet->mergeCells('J'.$row.':J'.($row+1));
               $sheet->mergeCells('K'.$row.':K'.($row+1));
               $sheet->mergeCells('L'.$row.':L'.($row+1));
               $sheet->mergeCells('M'.$row.':M'.($row+1));
               $sheet->mergeCells('N'.$row.':N'.($row+1));
               $sheet->mergeCells('O'.$row.':O'.($row+1));
               $sheet->mergeCells('P'.$row.':P'.($row+1));
               $sheet->mergeCells('Q'.$row.':Q'.($row+1));
               $sheet->mergeCells('R'.$row.':R'.($row+1));
               $sheet->getStyle('C'.($row+1))->applyFromArray($fontStyle);
               $sheet->getStyle('A'.$row.':B'.($row+1))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
               $sheet->getStyle('C'.($row+1))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
               $sheet->getStyle('D'.$row.':R'.($row+1))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
               $sheet->setCellValueExplicit('C'.($row+1), $list['keterangan'], PHPExcel_Cell_DataType::TYPE_STRING);
               $sheet->setCellValueExplicit('D'.$row, $list['target_pnbp'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->setCellValueExplicit('E'.$row, $list['realJan'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->setCellValueExplicit('F'.$row, $list['realFeb'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->setCellValueExplicit('G'.$row, $list['realMar'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->setCellValueExplicit('H'.$row, $list['realApr'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->setCellValueExplicit('I'.$row, $list['realMei'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->setCellValueExplicit('J'.$row, $list['realJun'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->setCellValueExplicit('K'.$row, $list['realJul'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->setCellValueExplicit('L'.$row, $list['realAgs'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->setCellValueExplicit('M'.$row, $list['realSep'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->setCellValueExplicit('N'.$row, $list['realOkt'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->setCellValueExplicit('O'.$row, $list['realNov'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->setCellValueExplicit('P'.$row, $list['realDes'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->setCellValueExplicit('Q'.$row, $list['total_realisasi'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
               $sheet->setCellValueExplicit('R'.$row, '=Q'.$row.'-D'.$row, PHPExcel_Cell_DataType::TYPE_FORMULA);
               
               $row++;
            }
            
            $row++;
         }

         $sheet->getStyle('C'.$row.':C'.$row)->applyFromArray(array(
                  'borders' => array(
                     'bottom' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'ff000000')
                     ), 'top' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'ff000000')
                     )
               )));

         $sheet->mergeCells('A'.$row.':C'.$row);
         $sheet->setCellValue('A'.$row, 'TOTAL');
         $sheet->setCellValueExplicit('D'.$row, $dataPnbp['target_pnbp'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
         $sheet->setCellValueExplicit('E'.$row, $dataPnbp['realJan'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
         $sheet->setCellValueExplicit('F'.$row, $dataPnbp['realFeb'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
         $sheet->setCellValueExplicit('G'.$row, $dataPnbp['realMar'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
         $sheet->setCellValueExplicit('H'.$row, $dataPnbp['realApr'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
         $sheet->setCellValueExplicit('I'.$row, $dataPnbp['realMei'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
         $sheet->setCellValueExplicit('J'.$row, $dataPnbp['realJun'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
         $sheet->setCellValueExplicit('K'.$row, $dataPnbp['realJul'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
         $sheet->setCellValueExplicit('L'.$row, $dataPnbp['realAgs'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
         $sheet->setCellValueExplicit('M'.$row, $dataPnbp['realSep'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
         $sheet->setCellValueExplicit('N'.$row, $dataPnbp['realOkt'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
         $sheet->setCellValueExplicit('O'.$row, $dataPnbp['realNov'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
         $sheet->setCellValueExplicit('P'.$row, $dataPnbp['realDes'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
         $sheet->setCellValueExplicit('Q'.$row, $dataPnbp['total_realisasi'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
         $sheet->setCellValueExplicit('R'.$row, '=Q'.$row.'-D'.$row, PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->getStyle('A'.$row.':R'.$row)->getFont()->setBold(true);
         $sheet->getStyle('A'.$row.':C'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $sheet->getStyle('A8:B'.$row)->applyFromArray($borderTableStyledArray);
         $sheet->getStyle('D8:R'.$row)->applyFromArray($borderTableStyledArray);
         $sheet->getStyle('D8:R'.$row)->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
      }
      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>