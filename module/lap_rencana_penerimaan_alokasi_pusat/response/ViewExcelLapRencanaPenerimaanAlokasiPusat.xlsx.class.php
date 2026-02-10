<?php
/**
* ================= doc ====================
* FILENAME     : ViewExcelLapRencanaPenerimaanAlokasiPusat.xlsx.class.php
* @package     : ViewExcelLapRencanaPenerimaanAlokasiPusat
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
'module/lap_rencana_penerimaan_alokasi_pusat/business/AppLapRencanaPenerimaanAlokasiPusat.class.php';
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewExcelLapRencanaPenerimaanAlokasiPusat extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('laporan_rencana_penerimaan_alokasi_pusat_'.date('Ymd', time()).'.xls');

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

      $Obj              = new AppLapRencanaPenerimaanAlokasiPusat();
      $UserUnitKerja    = new UserUnitKerja();
      $tahun_anggaran   = Dispatcher::Instance()->Decrypt($Obj->_GET['tgl']);
      $unitkerja        = Dispatcher::Instance()->Decrypt($Obj->_GET['unitkerja']);
      $kodePenerimaanId = Dispatcher::Instance()->Decrypt($Obj->_GET['kode_penerimaan_id']);

      $data             = $Obj->GetDataRencanaPenerimaan($tahun_anggaran,$unitkerja,$kodePenerimaanId);
      $unitkerja        = $UserUnitKerja->GetUnitKerja($unitkerja);
      $tahunanggaran    = $Obj->GetTahunAnggaran($tahun_anggaran);
      $unitkerja_nama   = $unitkerja['unit_kerja_nama'];
      $tahunanggaran_nama = $tahunanggaran['name'];


      $data_kosong = GTFWConfiguration::GetValue('language','data_kosong');
      if (empty($data)) {
         $sheet->setCellValue('A1', $data_kosong);
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

         /**
          * set label
          */
         $lap_penerimaan_label   = GTFWConfiguration::GetValue('language','lap_rencana_penerimaan_alokasi_pusat');
         $unit_sub_unit_label    = GTFWConfiguration::GetValue('language','unit') .'/'. GTFWConfiguration::GetValue('language','sub_unit');
         $tahun_periode_label    = GTFWConfiguration::GetValue('language','tahun_periode');
         $no_label               = GTFWConfiguration::GetValue('language','no');
         $kode_label             = GTFWConfiguration::GetValue('language','kode_penerimaan');
         $unit_kerja_label       = GTFWConfiguration::GetValue('language','unit_kerja');
         $jenis_penerimaan_label = GTFWConfiguration::GetValue('language','jenis_penerimaan');
         $t_penerimaan_label     = GTFWConfiguration::GetValue('language','total_penerimaan');
         $dist_penerimaan_label  = GTFWConfiguration::GetValue('language','distribusi_penerimaan');
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
         $total_label            = GTFWConfiguration::GetValue('language','total');
         $persen_label           = '%';
         /**
          * end set label
          */

         $sheet->mergeCells('A1:AC1');
         $sheet->mergeCells('A3:B3');
         $sheet->mergeCells('C3:AC3');
         $sheet->mergeCells('A4:B4');
         $sheet->mergeCells('C4:AC4');
         $sheet->mergeCells('A6:A7');
         $sheet->mergeCells('B6:B7');
         $sheet->mergeCells('C6:C7');
         $sheet->mergeCells('D6:D7');
         $sheet->mergeCells('E6:E7');
         $sheet->mergeCells('F6:AC6');

         $sheet->getRowDimension(1)->setRowHeight(20);
         $sheet->getColumnDimension('A')->setWidth(8);
         $sheet->getColumnDimension('B')->setWidth(18);
         $sheet->getColumnDimension('C')->setWidth(40);
         $sheet->getColumnDimension('D')->setWidth(20);
         $sheet->getColumnDimension('E')->setWidth(23);
         $sheet->getColumnDimension('F')->setWidth(5);
         $sheet->getColumnDimension('G')->setWidth(23);
         $sheet->getColumnDimension('H')->setWidth(5);
         $sheet->getColumnDimension('I')->setWidth(23);
         $sheet->getColumnDimension('J')->setWidth(5);
         $sheet->getColumnDimension('K')->setWidth(23);
         $sheet->getColumnDimension('L')->setWidth(5);
         $sheet->getColumnDimension('M')->setWidth(23);
         $sheet->getColumnDimension('N')->setWidth(5);
         $sheet->getColumnDimension('O')->setWidth(23);
         $sheet->getColumnDimension('P')->setWidth(5);
         $sheet->getColumnDimension('Q')->setWidth(23);
         $sheet->getColumnDimension('R')->setWidth(5);
         $sheet->getColumnDimension('S')->setWidth(23);
         $sheet->getColumnDimension('T')->setWidth(5);
         $sheet->getColumnDimension('U')->setWidth(23);
         $sheet->getColumnDimension('V')->setWidth(5);
         $sheet->getColumnDimension('W')->setWidth(23);
         $sheet->getColumnDimension('X')->setWidth(5);
         $sheet->getColumnDimension('Y')->setWidth(23);
         $sheet->getColumnDimension('Z')->setWidth(5);
         $sheet->getColumnDimension('AA')->setWidth(23);
         $sheet->getColumnDimension('AB')->setWidth(5);
         $sheet->getColumnDimension('AC')->setWidth(23);

         $sheet->setCellValue('A1', $lap_penerimaan_label);
         $sheet->getStyle('A1:AC1')->applyFromArray($headerStyle);
         $sheet->setCellValue('A3', $tahun_periode_label);
         $sheet->setCellValueExplicit('C3', ': '.$tahunanggaran_nama, PHPExcel_Cell_DataType::TYPE_STRING);
         $sheet->setCellValue('A4', $unit_sub_unit_label);
         $sheet->setCellValueExplicit('C4', ': '.$unitkerja_nama, PHPExcel_Cell_DataType::TYPE_STRING);
         $sheet->getStyle('A3:AC4')->getFont()->setBold(true);

         // data-table header
         $sheet->setCellValue('A6', $no_label);
         $sheet->setCellValue('B6', $kode_label);
         $sheet->setCellValue('C6', $jenis_penerimaan_label);
         $sheet->setCellValue('D6', $unit_kerja_label);
         $sheet->setCellValue('E6', $t_penerimaan_label);
         $sheet->setCellValue('F6', $dist_penerimaan_label);
         $sheet->setCellValue('F7', $persen_label);
         $sheet->setCellValue('G7', $januarai_label);
         $sheet->setCellValue('H7', $persen_label);
         $sheet->setCellValue('I7', $februari_label);
         $sheet->setCellValue('J7', $persen_label);
         $sheet->setCellValue('K7', $maret_label);
         $sheet->setCellValue('L7', $persen_label);
         $sheet->setCellValue('M7', $april_label);
         $sheet->setCellValue('N7', $persen_label);
         $sheet->setCellValue('O7', $mei_label);
         $sheet->setCellValue('P7', $persen_label);
         $sheet->setCellValue('Q7', $juni_label);
         $sheet->setCellValue('R7', $persen_label);
         $sheet->setCellValue('S7', $juli_label);
         $sheet->setCellValue('T7', $persen_label);
         $sheet->setCellValue('U7', $agustus_label);
         $sheet->setCellValue('V7', $persen_label);
         $sheet->setCellValue('W7', $september_label);
         $sheet->setCellValue('X7', $persen_label);
         $sheet->setCellValue('Y7', $oktober_label);
         $sheet->setCellValue('Z7', $persen_label);
         $sheet->setCellValue('AA7', $november_label);
         $sheet->setCellValue('AB7', $persen_label);
         $sheet->setCellValue('AC7', $desember_label);
         $sheet->getStyle('A6:AC7')->applyFromArray($styledTableHeaderArray);

         $row     = 8;
         $nomor   = 1;
         // data table
         foreach ($data as $list) {
            $sheet->setCellValue('A'.$row, $nomor);
            $sheet->setCellValue('B'.$row, $list['kode_penerimaan']);
            $sheet->setCellValue('C'.$row, $list['kode_penerimaan_nama']);
            $sheet->setCellValue('D'.$row, $list['unit_kerja_nama']);
            $sheet->setCellValueExplicit('E'.$row, $list['total_terima'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('F'.$row, $list['pjanuari'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('G'.$row, $list['januari'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('H'.$row, $list['pfebruari'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('I'.$row, $list['februari'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('J'.$row, $list['pmaret'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('K'.$row, $list['maret'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('L'.$row, $list['papril'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('M'.$row, $list['april'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('N'.$row, $list['pmei'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('O'.$row, $list['mei'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('P'.$row, $list['pjuni'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('Q'.$row, $list['juni'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('R'.$row, $list['pjuli'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('S'.$row, $list['juli'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('T'.$row, $list['pagustus'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('U'.$row, $list['agustus'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('V'.$row, $list['pseptember'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('W'.$row, $list['september'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('X'.$row, $list['poktober'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('Y'.$row, $list['oktober'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('Z'.$row, $list['pnovember'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('AA'.$row, $list['november'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('AB'.$row, $list['pdesember'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('AC'.$row, $list['desember'], PHPExcel_Cell_DataType::TYPE_NUMERIC);

            $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G'.$row)->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
            $sheet->getStyle('H'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('I'.$row)->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
            $sheet->getStyle('J'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('K'.$row)->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
            $sheet->getStyle('L'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('M'.$row)->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
            $sheet->getStyle('N'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('O'.$row)->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
            $sheet->getStyle('P'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('Q'.$row)->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
            $sheet->getStyle('R'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('S'.$row)->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
            $sheet->getStyle('T'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('U'.$row)->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
            $sheet->getStyle('V'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('W'.$row)->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
            $sheet->getStyle('X'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('Y'.$row)->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
            $sheet->getStyle('X'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('AA'.$row)->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
            $sheet->getStyle('AB'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('AC'.$row)->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
            $sheet->getStyle('E'.$row)->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');

            $nomor++;
            $row++;
         }

         $sheet->getStyle('A8:AC'.$row)->applyFromArray($borderTableStyledArray);
         $sheet->mergeCells('A'.$row.':D'.$row);
         $sheet->setCellValue('A'.$row, 'TOTAL');
         $sheet->getStyle('A'.$row.':D'.$row)->getFont()->setBold(true);
         $sheet->getStyle('A'.$row.':D'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
         $sheet->setCellValueExplicit('E'.$row, '=SUM(E8:E'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->setCellValueExplicit('G'.$row, '=SUM(G8:G'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->setCellValueExplicit('I'.$row, '=SUM(I8:I'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->setCellValueExplicit('K'.$row, '=SUM(K8:K'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->setCellValueExplicit('M'.$row, '=SUM(M8:M'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->setCellValueExplicit('O'.$row, '=SUM(O8:O'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->setCellValueExplicit('Q'.$row, '=SUM(Q8:Q'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->setCellValueExplicit('S'.$row, '=SUM(S8:S'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->setCellValueExplicit('U'.$row, '=SUM(U8:U'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->setCellValueExplicit('W'.$row, '=SUM(W8:W'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->setCellValueExplicit('Y'.$row, '=SUM(Y8:Y'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->setCellValueExplicit('AA'.$row, '=SUM(AA8:AA'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->setCellValueExplicit('AC'.$row, '=SUM(AC8:AC'.($row-1).')', PHPExcel_Cell_DataType::TYPE_FORMULA);
         $sheet->getStyle('E'.$row.':AC'.$row)->getNumberFormat()->setFormatCode('#,##0_);[RED](#,##0)');
      }

      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>