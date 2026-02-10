<?php
/**
* ================= doc ====================
* FILENAME     : ViewListJurnal.xlsx.class.php
* @package     : ViewListJurnal
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-28
* @Modified    : 2015-04-28
* @Analysts    : Dyah Fajar N
* @contact     : eko.susilo@gamatechno.com
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/jurnal/business/Jurnal.class.php';

class ViewListJurnal extends XlsxResponse
{
   # Internal Variables
   public $Excel;

   function ProcessRequest()
   {
      #get data from GET
      $noReferensi   = Dispatcher::Instance()->Decrypt($_GET['no_referensi']);
      $tahun         = Dispatcher::Instance()->Decrypt($_GET['tahun']);
      $bulan         = Dispatcher::Instance()->Decrypt($_GET['bulan']);
      $sub_account         = Dispatcher::Instance()->Decrypt($_GET['sub_account']);
      $tampilkanSemua = Dispatcher::Instance()->Decrypt($_GET['tampilkanSemua']);
      
      if($sub_account == '01-00-00-00-00-00-00'){
         $header = strtoupper(GTFWConfiguration::GetValue('organization', 'header_lap_yayasan'));
      }elseif($sub_account == '00-00-00-00-00-00-00'){
         $header = strtoupper(GTFWConfiguration::GetValue('organization', 'header_lap_perbanas'));
      }else{
         $header = strtoupper(GTFWConfiguration::GetValue('organization', 'header_lap_all'));
      }

      if(is_object($bulan)){
          $bulanN = $bulan->mrVariable;
      } else {
          $bulanN = $bulan;
      }
      
      if(is_object($tampilkanSemua)){
          $tampilkanSemuaN = $tampilkanSemua->mrVariable;
      } else {
          $tampilkanSemuaN = $tampilkanSemua;
      } 
      #get data from database
      $objJurnal     = new Jurnal();

      if($tampilkanSemuaN === 'true') {
        $periode = $objJurnal->GetPeriodePembukuanAktif();
        $tglAwal = $periode['tanggal_awal'];
        $tglAkhir = $periode['tanggal_akhir'];
        $data          = $objJurnal->GetDataAllCetak($tglAwal,$tglAkhir);
                  
        $bulanAwal = date("n", strtotime($tglAwal));
        $tahunAwal = date("Y", strtotime($tglAwal));
          
        $bulanAkhir = date("n", strtotime($tglAkhir));
        $tahunAkhir = date("Y", strtotime($tglAkhir));
          
      } else {
        $data          = $objJurnal->GetDataCetak($noReferensi, $sub_account, $tahun, $bulan);
      }
      # set writer for XlsxResponse
      # default Excel5 for .xls extension
      # option Excel2007 for .xlsx extension
      # $this->SetWriter('Excel2007');
      $this->SetFileName('list_jurnal.xls');

      # Write your code here
      # Get active sheet
      # Document Setting
      $sheet   = $this->Excel->getActiveSheet(0);
      $sheet->setShowGridlines(true); # false: Hide the gridlines; true: show the gridlines
      # set worksheet name
      $sheet->setTitle('Jurnal');
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

      $headerStyledArray      = array(
         'font' => array(
            'bold' => true,
            'size' => '14'
         ),
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
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
               'argb' => 'ffcccccc'
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

      $tableCellStyledArray   = array(
         'borders' => array(
            'allborders' => array(
               'style' => PHPExcel_Style_Border::BORDER_THIN,
               'color' => array('argb' => 'ff000000')
            )
         ), 'alignment' => array(
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
         )
      );

      $tableCellAlignCenterStyledArr   = array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
         )
      );

      $sheet->mergeCells('A1:F1');
      $sheet->mergeCells('A2:F2');
      $sheet->mergeCells('A3:F3');
      $sheet->getRowDimension(1)->setRowHeight(18);
      $sheet->getRowDimension(3)->setRowHeight(16);
      $sheet->getColumnDimension('A')->setWidth(8);
      $sheet->getColumnDimension('B')->setWidth(20);
      $sheet->getColumnDimension('C')->setWidth(12);
      $sheet->getColumnDimension('D')->setWidth(36);
      $sheet->getColumnDimension('E')->setWidth(20);
      $sheet->getColumnDimension('F')->setWidth(20);
      $sheet->setCellValue('A1', $header);
      $sheet->setCellValue('A2', GTFWConfiguration::GetValue('language', 'jurnal'));
      //echo intval($bulan);
       if($tampilkanSemuaN === 'true') {
           $sheet->setCellValue('A3', GTFWConfiguration::GetValue('language', 'periode') . ' '.
                                ($objJurnal->month2string($bulanAwal)) .' '. $tahunAwal.' s/d '.
                                ($objJurnal->month2string($bulanAkhir)). ' '. $tahunAkhir);
       } else {
            $sheet->setCellValue('A3', GTFWConfiguration::GetValue('language', 'periode') . ' '.
                                ($objJurnal->month2string($bulanN)) . ' '.
                                GTFWConfiguration::GetValue('language', 'tahun') .' '. $tahun);
       }                          
      $sheet->getStyle('A1:F1')->applyFromArray($headerStyledArray);
      $sheet->getStyle('A2:F2')->applyFromArray($headerStyledArray);
      $sheet->getStyle('A3:F3')->applyFromArray($headerStyledArray);

      $sheet->setCellValue('A5', GTFWConfiguration::GetValue('language','no'));
      $sheet->setCellValue('B5', GTFWConfiguration::GetValue('language','no_referensi'));
      $sheet->setCellValue('C5', GTFWConfiguration::GetValue('language','kode_rekening'));
      $sheet->setCellValue('D5', GTFWConfiguration::GetValue('language','nama_rekening'));
      $sheet->setCellValue('E5', GTFWConfiguration::GetValue('language','debet_rp'));
      $sheet->setCellValue('F5', GTFWConfiguration::GetValue('language','kredit_rp'));
      $sheet->getStyle('A5:F5')->applyFromArray($styledTableHeaderArray);
      $row        = 6;
      $nomor        = 1;
      $refId  = '';
      $startJurnal = $row;

      for ($i = 0; $i < count($data);) {
          
            if($refId == $data[$i]['id']) {
                
                $sheet->setCellValue('A'.$row, '');
                $sheet->setCellValue('C'.$row, $data[$i]['rekening_kode']);
                $sheet->setCellValue('D'.$row, $data[$i]['rekening_nama']);
                
                if (strtoupper($data[$i]['tipeakun']) == 'D') 
                    $sheet->setCellValue('E'.$row, $data[$i]['nilai']);
                elseif (strtoupper($data[$i]['tipeakun']) == 'K')
                   $sheet->setCellValue('F'.$row, $data[$i]['nilai']);
              
               $sheet->getStyle('E'.$row.':F'.$row)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');  
               $i++;

            } elseif($refId != $data[$i]['id']){
                $refId = $data[$i]['id'];
                $rowG = $row;
                if($i > 0) {
                    $sheet->mergeCells('A'.$row.':F'.$row);    
                    $sheet->setCellValue('C'.$row, '');
                    $sheet->setCellValue('D'.$row, '');
                    $sheet->setCellValue('E'.$row, '');
                    $sheet->setCellValue('F'.$row, '');
                     $sheet->mergeCells('A'.$startJurnal.':A'.($row-1));
                     $sheet->mergeCells('B'.$startJurnal.':B'.($row-1));
                     $startJurnal = $row+1;
                     $rowG = $row  + 1;
                }
                $sheet->setCellValue('A'.$rowG, $nomor);
               if(!empty($data[$i]['catatan'])) {
                    $keterangan = $data[$i]['catatan'];
                } else {
                    $keterangan = '-';
                }
            
                $currDay       = date('d', strtotime($data[$i]['tanggal']));
                $currMon       = date('m', strtotime($data[$i]['tanggal']));
                $currYear      = date('Y', strtotime($data[$i]['tanggal']));
                $time          = gmmktime(0,0,0, $currMon, $currDay, $currYear);

                $sheet->mergeCells('D'.$rowG.':F'.$rowG);
                $sheet->setCellValue('B'.$rowG, $data[$i]['referensi']);
                $sheet->setCellValue('C'.$rowG, PHPExcel_Shared_Date::PHPToExcel($time));
                $sheet->getStyle('C'.$rowG)->getNumberFormat()->setFormatCode('dd mmm YYYY;@');
                $sheet->setCellValue('D'.$rowG,  $keterangan );
                $sheet->setCellValue('E'.$rowG, '');
                $sheet->setCellValue('F'.$rowG, '');
                
                $row = $rowG;
                $nomor++;                
            } 
          $row++;
      }

      $sheet->mergeCells('A'.$startJurnal.':A'.($row-1));
      $sheet->mergeCells('B'.$startJurnal.':B'.($row-1));

      $sheet->getStyle('A5:F'.($row-1))->applyFromArray($tableCellStyledArray);
      $sheet->getStyle('A5:C'.($row-1))->applyFromArray(array(
         'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
         )
      ));
     
      # Save Excel document to local hard disk
      $this->Save();
   }
}
?>