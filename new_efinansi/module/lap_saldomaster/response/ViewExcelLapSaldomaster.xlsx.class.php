<?php

/**
 * ================= doc ====================
 * FILENAME     : ViewExcelLapSaldomaster.xlsx.class.php
 * @package     : ViewExcelLapSaldomaster
 * scope        : PUBLIC
 * @Author      : Eko Susilo
 * @Created     : 2015-05-25
 * @Modified    : 2015-05-25
 * @Analysts    : Dyah Fajar N
 * @contact     : eko.susilo@gamatechno.com
 * @copyright   : Copyright (c) 2012 Gamatechno
 * ================= doc ====================
 */
require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/lap_saldomaster/business/AppLapSaldomaster.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'main/function/date.php';

class ViewExcelLapSaldomaster extends XlsxResponse {
    # Internal Variables

    public $Excel;

    function ProcessRequest() {
        $Obj = new AppLapSaldoMaster();
        $tgl_akhir = Dispatcher::Instance()->Decrypt($_GET['tgl_akhir']);
        $tgl_awal = Dispatcher::Instance()->Decrypt($_GET['tgl_awal']);
        $data = $Obj->GetSaldo($tgl_awal, $tgl_akhir);
        $saldoBerjalan = $Obj->GetSaldoBerjalan($tgl_akhir);

        # set writer for XlsxResponse
        # default Excel5 for .xls extension
        # option Excel2007 for .xlsx extension
        # $this->SetWriter('Excel2007');
        $this->SetFileName('laporan_saldo_master_' . date('Ymd', time()) . '.xls');

        # Write your code here
        # Get active sheet
        # Document Setting
        $sheet = $this->Excel->getActiveSheet(0);
        $sheet->setShowGridlines(true); # false: Hide the gridlines; true: show the gridlines
        # set worksheet name
        $sheet->setTitle('Saldo Master');
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

        //set perlabelan dari language.conf.ini
        $label_header_identitas = GTFWConfiguration::GetValue('organization', 'company_name');
        $label_header_judul = GTFWConfiguration::GetValue('language', 'laporan_saldo_master');
        $label_header_interval = GTFWConfiguration::GetValue('language', 'interval_waktu');
        $label_data_kosong = GTFWConfiguration::GetValue('language', 'data_kosong');
        $label_no = GTFWConfiguration::GetValue('language', 'no');
        $label_no_rekening = GTFWConfiguration::GetValue('language', 'no_rekening');
        $label_nama_rekening = GTFWConfiguration::GetValue('language', 'nama_rekening');
        $label_saldo_awal = GTFWConfiguration::GetValue('language', 'saldo_awal_rp');
        $label_sado_akhir = GTFWConfiguration::GetValue('language', 'saldo_akhir_rp');
        $label_mutasi_debet = GTFWConfiguration::GetValue('language', 'mutasi_debet_rp');
        $label_mutasi_kredit = GTFWConfiguration::GetValue('language', 'mutasi_kredit_rp');
        $label_sub_total = GTFWConfiguration::GetValue('language', 'sub_total');

            
        if (empty($data)) {
            $sheet->setCellValue('B1', $label_data_kosong );
        } else {
            #set header
            
            $sheet->getRowDimension(1)->setRowHeight(20);
            $sheet->getColumnDimension('A')->setWidth(4);
            $sheet->getColumnDimension('B')->setWidth(16);
            $sheet->getColumnDimension('C')->setWidth(40);
            $sheet->getColumnDimension('D')->setWidth(25);
            $sheet->getColumnDimension('E')->setWidth(25);
            $sheet->getColumnDimension('F')->setWidth(25);
            $sheet->getColumnDimension('G')->setWidth(25);
            $sheet->setCellValueByColumnAndRow(1, 1, $label_header_identitas);
            $sheet->getStyle('B1:G1')->applyFromArray(array(
                'font' => array(
                    'size' => 14,
                )
            ));
            
            $sheet->setCellValueByColumnAndRow(1, 2, $label_header_judul);
            $sheet->getStyle('B2:G2')->applyFromArray(array(
                'font' => array(
                    'size' => 12,
                )
            ));
            
            $intervalTanggal = IndonesianDate($tgl_awal, 'yyyy-mm-dd').' s/d '.IndonesianDate($tgl_akhir, 'yyyy-mm-dd');
            $sheet->setCellValueByColumnAndRow(1, 3, $label_header_interval.' : ' . $intervalTanggal);
            $sheet->mergeCells('B1:G1');
            $sheet->mergeCells('B2:G2');
            $sheet->mergeCells('B3:G3');
            
            $sheet->getStyle('B1:G3')->applyFromArray(array(
                'font' => array(
                    'bold' => true
                ), 'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
            ));

            $num = 5;
            $numPreAwal = $num;
            //$sheet->setCellValueByColumnAndRow(0, $no, $label_no);
            $sheet->setCellValueByColumnAndRow(1, $num, $label_no_rekening);
            $sheet->setCellValueByColumnAndRow(2, $num, $label_nama_rekening);
            $sheet->setCellValueByColumnAndRow(3, $num, $label_saldo_awal);
            $sheet->setCellValueByColumnAndRow(4, $num, $label_mutasi_debet);
            $sheet->setCellValueByColumnAndRow(5, $num, $label_mutasi_kredit);
            $sheet->setCellValueByColumnAndRow(6, $num, $label_saldo_awal);
            $sheet->getStyle('A'.$num.':G'.$num)->applyFromArray(array(
                'font' => array(
                    'bold' => true
                ), 'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
            ));
            $num = 6;
            $no = 1;
            $saldoAwal = $mutasiDebet = $mutasiKredit = $saldoAkhir = 0;
            $saldoAwal = $debet = $kredit = $saldoAkhir = 0;
            $numAwal = $num;//cek point row awal
            for ($i = 0; $i < sizeof($data); $i++) {
                if ($i == 0) {
                    $saldoAwal = $data[$i]['saldo_awal'];
                }
                $debet += $data[$i]['debet'];
                $kredit += $data[$i]['kredit'];

                if($data[$i]['rl_awal'] === '1') {
                    if($saldoBerjalan > 0) {                        
                        $kredit -= $saldoBerjalan;
                    } else {
                        $debet += $saldoBerjalan;
                    }
                }

                if($data[$i]['rl_berjalan'] === '1') {
                    if($saldoBerjalan > 0) {                        
                        $debet -= $saldoBerjalan;
                    } else {
                        $kredit += $saldoBerjalan;
                    }
                }

                if ($data[$i]['coa_kode_akun'] != $data[$i + 1]['coa_kode_akun']) {
                    $saldoAkhir = $data[$i]['saldo_akhir'];
                    if($data[$i]['rl_awal'] === '1') { 
                        $saldoAkhir += ($saldoBerjalan > 0 ? ($saldoBerjalan * -1) :  $saldoBerjalan);
                    }
    
                    if($data[$i]['rl_berjalan'] === '1') {
                        $saldoAkhir += ($saldoBerjalan > 0 ?  $saldoBerjalan : ($saldoBerjalan * -1));
                    }
                    $kode = explode(".", $data[$i]['coa_kode_akun']);
                    $kodeNext = explode(".", $data[$i + 1]['coa_kode_akun']);
                    //$sheet->setCellValueByColumnAndRow(0, $num, $no);
                    $sheet->setCellValueByColumnAndRow(1, $num, $data[$i]['coa_kode_akun']);
                    $sheet->setCellValueByColumnAndRow(2, $num, $data[$i]['coa_nama_akun']);

                    $sheet->getCellByColumnAndRow(3, $num)->setValueExplicit($saldoAwal, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $sheet->getCellByColumnAndRow(4, $num)->setValueExplicit($debet, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $sheet->getCellByColumnAndRow(5, $num)->setValueExplicit($kredit, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $sheet->getCellByColumnAndRow(6, $num)->setValueExplicit($saldoAkhir, PHPExcel_Cell_DataType::TYPE_NUMERIC);

                  
                    $no++;
                    $subSAwal += $saldoAwal;
                    $subSAkhir += $saldoAkhir;
                    $mDebet += $debet;
                    $mKredit += $kredit;
                    if ($kode[0] != $kodeNext[0]) {
                        $num = $num + 1;
                        //$sheet->setCellValueByColumnAndRow(0, $num, '');
                        $sheet->setCellValueByColumnAndRow(1, $num, '');
                        $sheet->setCellValueByColumnAndRow(2, $num, $label_sub_total);
                        $sheet->getCellByColumnAndRow(3, $num)->setValueExplicit($subSAwal, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                        $sheet->getCellByColumnAndRow(4, $num)->setValueExplicit($mDebet, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                        $sheet->getCellByColumnAndRow(5, $num)->setValueExplicit($mKredit, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                        $sheet->getCellByColumnAndRow(6, $num)->setValueExplicit($subSAkhir, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                        $sheet->getStyle('C'.$num.':G'.$num)->applyFromArray(array(
                            'font' => array(
                                'bold' => true
                            )
                        ));
                        $subSAwal = $subSAkhir = $mDebet = $mKredit = 0;
                        $no = 1;
                    }
                    $saldoAwal = $debet = $kredit = $saldoAkhir = 0;
                    $saldoAwal = $data[$i + 1]['saldo_awal'];                    
                    $num++;                   
                }

               
            }//end for
            //mari kita beriwarna border table nya
            $sheet->getStyle('B' . $numPreAwal . ':G' . ($num-1))->applyFromArray(array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                ));            
            // mari kita format nominal nya
            $sheet->getStyle('D' . $numAwal . ':G' . ($num-1))->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
            $sheet->getStyle('C' . $numAwal . ':C' . ($num-1))->getAlignment()->setWrapText(true);
            
        }
        # Save Excel document to local hard disk
        $this->Save();
    }

}

?>