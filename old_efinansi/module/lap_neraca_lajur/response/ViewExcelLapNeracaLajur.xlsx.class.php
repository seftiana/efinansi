<?php

/**
 * ================= doc ====================
 * FILENAME     : ViewExcelLapNeracaLajur.xlsx.class.php
 * @package     : ViewExcelLapNeracaLajur
 * scope        : PUBLIC
 * @Author      : Eko Susilo
 * @Created     : 2015-05-25
 * @Modified    : 2015-05-25
 * @Analysts    : Dyah Fajar N
 * @contact     : eko.susilo@gamatechno.com
 * @modified by : noor.hadi <noor.hadi@gamatechno.com>
 * @last modified : 2016-02-15
 * @copyright   : Copyright (c) 2012 Gamatechno
 * ================= doc ====================
 */
require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/lap_neraca_lajur/business/LapNeracaLajur.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewExcelLapNeracaLajur extends XlsxResponse {
    # Internal Variables

    public $Excel;

    function ProcessRequest() {
        if (isset($_GET)) { //pasti dari form pencarian :p
            if (is_object($_GET)) {
                $v = $_GET->AsArray();
            } else {
                $v = $_GET;
            }
        }
        $mDBObj = new LapNeracaLajur();
        $tgl_awal = Dispatcher::Instance()->Decrypt($v['tgl_awal']);
        $tgl_akhir = Dispatcher::Instance()->Decrypt($v['tgl_akhir']);

        $interval_waktu = $mDBObj->indonesianDate($tgl_awal) . ' - ' . $mDBObj->indonesianDate($tgl_akhir);
        $dataLaporan = $mDBObj->GetDataLaporan($tgl_awal, $tgl_akhir);

        /**
         * label language
         */
        $labelIdentitas =  GTFWConfiguration::GetValue('organization', 'company_name');
        $labelDataKosong = GTFWConfiguration::GetValue('language', 'data_kosong');
        $labelJudulLaporan = GTFWConfiguration::GetValue('language', 'laporan_neraca_saldo');
        $labelTaunPeriode = GTFWConfiguration::GetValue('language', 'tahun_periode');
        $labelIntervalWaktu = GTFWConfiguration::GetValue('language', 'interval_waktu');
        $labelKodeAkun = GTFWConfiguration::GetValue('language', 'kode_akun');
        $labelKeterangan = GTFWConfiguration::GetValue('language', 'keterangan');
        $labelDebet = GTFWConfiguration::GetValue('language', 'debet');
        $labelKredit = GTFWConfiguration::GetValue('language', 'kredit');
        $labelTotal = GTFWConfiguration::GetValue('language', 'total');



        # set writer for XlsxResponse
        # default Excel5 for .xls extension
        # option Excel2007 for .xlsx extension
        # $this->SetWriter('Excel2007');
        $this->SetFileName( $labelJudulLaporan.'.xls');

        # Write your code here
        # Get active sheet
        # Document Setting
        $sheet = $this->Excel->getActiveSheet(0);
        $sheet->setShowGridlines(false); # false: Hide the gridlines; true: show the gridlines
        # set worksheet name
        $sheet->setTitle($labelJudulLaporan);
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


        if (empty($dataLaporan)) {
            $sheet->setCellValue('A1', $labelDataKosong);
        } else {
            $sheet->setCellValueByColumnAndRow(0, 1,$labelIdentitas);
            $sheet->setCellValueByColumnAndRow(0, 2, $labelJudulLaporan);
            $sheet->setCellValueByColumnAndRow(0, 3, $labelIntervalWaktu . ': ' . $interval_waktu);
            $sheet->getStyle('A1:A3')->applyFromArray(array(
                'font' => array(
                    'bold' => true,
                ), 
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
            ));
            $sheet->getStyle('A1:A1')->applyFromArray(array(
                'font' => array(
                    'size' => 12,
                )
            ));  
            $sheet->getStyle('A2:A2')->applyFromArray(array(
                'font' => array(
                    'size' => 11,
                )
            ));             
            $num = 5;
            
            /**
             * buat header
             */
             #set header
            $sheet->getColumnDimension('A')->setWidth(12);
            $sheet->getColumnDimension('B')->setWidth(70);
            $sheet->getColumnDimension('C')->setWidth(25);
            $sheet->getColumnDimension('D')->setWidth(25);
            $sheet->setCellValueByColumnAndRow(0, $num, $labelKodeAkun);
            $sheet->setCellValueByColumnAndRow(1, $num, $labelKeterangan);
            $sheet->setCellValueByColumnAndRow(2, $num, $labelDebet);
            $sheet->setCellValueByColumnAndRow(3, $num, $labelKredit);
            $sheet->getRowDimension($num)->setRowHeight(25);
            $sheet->getStyle('A'.$num.':D'.$num)->applyFromArray(array(
                       'font' => array(
                          'bold' => true
                       ), 'alignment' => array(
                          'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                          'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                          'wrap' => true,
                          'shrinkToFit' => true
                )
            ));
            
            
            $num = 6;
            $totalNeracaD = 0;
            $totalNeracaK = 0;
            $numAwal = $num;
            for ($k = 0; $k < sizeof($dataLaporan); $k++) {

                //mari kita menghitung neraca
                $nominalSaldoAwalD = $dataLaporan[$k]['saldo_awal_debet'];
                $nominalSaldoAwalK = $dataLaporan[$k]['saldo_awal_kredit'];
                $nominalDebet =  $nominalSaldoAwalD + $dataLaporan[$k]['neraca_debet'];
                $nominalKredit =  $nominalSaldoAwalK + $dataLaporan[$k]['neraca_kredit'];
                
                if($dataLaporan[$k]['debet_positif'] == 0 ) {
                    $nominalKreditJ = $nominalKredit - $nominalDebet;
                    $nominalDebetJ = 0;
                } else {
                    $nominalDebetJ = $nominalDebet - $nominalKredit ;
                    $nominalKreditJ = 0;
                }
                $totalNeracaD += $nominalDebetJ;
                $totalNeracaK += $nominalKreditJ;
                $sheet->setCellValueByColumnAndRow(0, $num,$dataLaporan[$k]['kode_akun']);
                $sheet->setCellValueByColumnAndRow(1, $num,$dataLaporan[$k]['nama_akun']);
                $sheet->setCellValueByColumnAndRow(2, $num,$nominalDebetJ);
                $sheet->setCellValueByColumnAndRow(3, $num,$nominalKreditJ);
                $sheet->getStyle('C'.$num.':D'.$num)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
            
                $num++;
            }
            
            $sheet->setCellValueByColumnAndRow(0, $num,$labelTotal);            
            $sheet->setCellValueByColumnAndRow(2, $num,'=SUM(C'.$numAwal.':C'.($num - 1).')');
            $sheet->setCellValueByColumnAndRow(3, $num,'=SUM(D'.$numAwal.':D'.($num - 1).')');
            $sheet->getStyle('C'.$num.':D'.$num)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
            $sheet->mergeCells('A'.$num.':B'.$num);
            $sheet->getStyle('A'.$numAwal.':A'.($num - 1))->applyFromArray(array(                
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
            ));            
            $sheet->getStyle('A'.$num.':B'.$num)->applyFromArray(array(
                'font' => array(
                    'bold' => true,
                ), 
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
            ));
            $sheet->getStyle('C'.$num.':D'.$num)->applyFromArray(array(
                'font' => array(
                    'bold' => true,
                )
            ));
            $sheet->getStyle('A'.($numAwal -1).':D'.$num)->applyFromArray(array(
            'borders' => array(
               'allborders' => array(
                  'style' => PHPExcel_Style_Border::BORDER_THIN
               )
            )
         ));
        }
        # Save Excel document to local hard disk
        $this->Save();
    }

}

?>