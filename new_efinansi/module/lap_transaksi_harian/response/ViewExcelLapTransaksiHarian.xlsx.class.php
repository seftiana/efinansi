<?php

/**
 * ================= doc ====================
 * FILENAME     : ViewExcelLapTransaksiHarian.xlsx.class.php
 * @package     : ViewExcelLapTransaksiHarian
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
        'module/lap_transaksi_harian/business/AppLapTransaksiHarian.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'main/function/date.php';

class ViewExcelLapTransaksiHarian extends XlsxResponse {
    # Internal Variables

    public $Excel;

    function ProcessRequest() {
        $Obj = new AppLapTransaksiHarian();
        $tgl_awal = Dispatcher::Instance()->Decrypt($_GET['tgl_awal']);
        $tgl = Dispatcher::Instance()->Decrypt($_GET['tgl']);
        $jenis_transaksi = Dispatcher::Instance()->Decrypt($_GET['jenis_transaksi']);
        $result = $Obj->GetDataCetak($tgl_awal, $tgl, $jenis_transaksi);

        //prepare saldo
        $Obj->prepareDataSaldoAwal($tgl_awal, $tgl, $jenis_transaksi);
        //end
        //
      # set writer for XlsxResponse
        # default Excel5 for .xls extension
        # option Excel2007 for .xlsx extension
        # $this->SetWriter('Excel2007');
        $this->SetFileName('laporan_transaksi_harian_' . date('Ymd', time()) . '.xls');

        # Write your code here
        # Get active sheet
        # Document Setting
        $sheet = $this->Excel->getActiveSheet(0);
        $sheet->setShowGridlines(true); # false: Hide the gridlines; true: show the gridlines
        # set worksheet name
        $sheet->setTitle('Laporan Harian');
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

        if (empty($result)) {
            $sheet->setCellValue('A1', GTFWConfiguration::GetValue('language', 'data_kosong'));
        } else {
            $tanggal_day = (int) date('d', strtotime($tgl_transaksi));
            $tanggal_mon = (int) date('m', strtotime($tgl_transaksi));
            $tanggal_year = (int) date('y', strtotime($tgl_transaksi));
            #set header
            $sheet->setCellValueByColumnAndRow(0, 1, 'Laporan Transaksi Harian');
            $sheet->setCellValueByColumnAndRow(0, 3, 'Tanggal Transaksi ' . IndonesianDate($tgl_awal, 'yyyy-mm-dd') . ' s/d ' . IndonesianDate($tgl, 'yyyy-mm-dd'));
            $sheet->mergeCells('A1:F1');
            $sheet->mergeCells('A3:F3');
            $sheet->getStyle('A1:F3')->getFont()->setBold(true);

            $sheet->getStyle('A1:F1')->applyFromArray(array(
                'font' => array(
                    'bold' => true,
                    'size' => 11
                ), 'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
            ));

            $sheet->getStyle('A3:F3')->applyFromArray(array(
                'font' => array(
                    'bold' => true
                ), 'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
            ));

            $no = 5;
            $sheet->getColumnDimension('A')->setWidth(6);
            $sheet->getColumnDimension('B')->setWidth(30);
            $sheet->getColumnDimension('C')->setWidth(85);
            $sheet->getColumnDimension('D')->setWidth(18);
            $sheet->getColumnDimension('E')->setWidth(18);
            $sheet->getColumnDimension('F')->setWidth(18);
            $sheet->setCellValueByColumnAndRow(0, $no, 'No.');
            $sheet->setCellValueByColumnAndRow(1, $no, 'No. Rekening / Bpkb');
            $sheet->setCellValueByColumnAndRow(2, $no, 'Nama Akun / Keterangan');
            $sheet->setCellValueByColumnAndRow(3, $no, 'Debet (Rp.)');
            $sheet->setCellValueByColumnAndRow(4, $no, 'Kredit (Rp.)');
            $sheet->setCellValueByColumnAndRow(5, $no, 'Saldo (Rp.)');

            $sheet->getStyle('A5:F5')->applyFromArray(array(
                'font' => array(
                    'bold' => true
                ), 'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
            ));
            $num = 6;

            $nomer = 1;
            for ($i = 0; $i < sizeof($result); $i++) {
                if ($result[$i]['coa_kode_akun'] != $result[$i - 1]['coa_kode_akun']) {
                    $sheet->setCellValueByColumnAndRow(0, $num, '');
                    $sheet->setCellValueByColumnAndRow(1, $num, $result[$i]['coa_kode_akun']);
                    $sheet->setCellValueByColumnAndRow(2, $num, $result[$i]['coa_nama_akun']);
                    $sheet->setCellValueByColumnAndRow(3, $num, '');
                    $sheet->setCellValueByColumnAndRow(4, $num, '');

                    //$saldoTran = $Obj->GetSaldoTransaksi($result[$i]['coa_id'], $tgl_transaksi);
                    #print_r($saldoTran); exit;   
                    $saldo = $Obj->getSaldoAwalAkunBulanLalu($result[$i]['coa_id'], $result[$i]['coa_kelompok_id']);
                    $sheet->setCellValueByColumnAndRow(5, $num, $saldo);
                    $sheet->getStyle('A' . $num . ':F' . $num)->applyFromArray(array('font' => array('bold' => true)));
                    $num++;
                }

                $sheet->setCellValueByColumnAndRow(0, $num, $nomer);
                $sheet->setCellValueByColumnAndRow(1, $num, $result[$i]['no_bpkb']);
                $sheet->setCellValueByColumnAndRow(2, $num, $result[$i]['transaksi_catatan']);

                if ($result[$i]['status_pembukuan'] == 'D') {
                    //if ($result[$i]['coa_status_debet']!=1) {
                    //   $kDebet += (2*$result[$i]['transaksi_nilai']);
                    //}
                    #$debKred = 'DEBET';
                    $sheet->setCellValueByColumnAndRow(3, $num, $result[$i]['transaksi_nilai_d']);
                    $sheet->setCellValueByColumnAndRow(4, $num, '');
                } else {
                    //if ($result[$i]['coa_status_debet']!=0) {
                    //   $dKredit += (2*$result[$i]['transaksi_nilai']);
                    //}
                    #$debKred = 'KREDIT';
                    $sheet->setCellValueByColumnAndRow(3, $num, '');
                    $sheet->setCellValueByColumnAndRow(4, $num, $result[$i]['transaksi_nilai_k']);
                }
                $sheet->setCellValueByColumnAndRow(5, $num, '');
                $nomer++;
                if ($result[$i]['coa_kode_akun'] != $result[$i + 1]['coa_kode_akun']) {
                    $num++;

                    $sheet->setCellValueByColumnAndRow(0, $num, 'Sub Total');

                    $sheet->mergeCells('A' . $num . ':C' . $num);


                    $debet = $Obj->getSaldoDebet($result[$i]['coa_id']);
                    $kredit = $Obj->getSaldoKredit($result[$i]['coa_id']);
                    $saldoBerjalan = $Obj->getSaldoAkunBulanBerjalan($result[$i]['coa_id'], $result[$i]['coa_kelompok_id']);

                    if ($debet != 0) {
                        $debetRp = $debet;
                    } else {
                        $debetRp = 0;
                    }
                    if ($kredit != 0) {
                        $kreditRp = $kredit;
                    } else {
                        $kreditRp = 0;
                    }
                    //perhitungan saldo berjalan sesuai dengan saldo normal
                    if ($result[$i]['coa_status_debet'] == '1') {
                        $saldoBerjalan = $debet - $kredit;
                    } else {
                        $saldoBerjalan = $kredit - $debet;
                    }
                    //end
                    $sheet->setCellValueByColumnAndRow(3, $num, $debetRp);
                    $sheet->setCellValueByColumnAndRow(4, $num, $kreditRp);
                    $sheet->setCellValueByColumnAndRow(5, $num, $saldo + $saldoBerjalan);
                    $nomer = 1;
                    $totalDebet += $debet;
                    $totalKredit += $kredit;
                    $debet = $kredit = $saldo = 0;
                    $sheet->getStyle('A' . $num . ':F' . $num)->applyFromArray(array('font' => array('bold' => true)));
                    #$num++;
                }
                $num++;
            }

            $sheet->setCellValueByColumnAndRow(0, $num, 'Grand Total');
            $sheet->mergeCells('A' . $num . ':C' . $num);
            $sheet->setCellValueByColumnAndRow(3, $num, $totalDebet);
            $sheet->setCellValueByColumnAndRow(4, $num, $totalKredit);
            $sheet->setCellValueByColumnAndRow(5, $num, '');

            $sheet->getStyle('A5:F' . $num)->applyFromArray(array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            ));
            $sheet->getStyle('A' . $num . ':F' . $num)->applyFromArray(array(
                'font' => array(
                    'bold' => true
                )
            ));

            $sheet->getStyle('A6:B' . $num)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                )
            ));
            $sheet->getStyle('D6:F' . $num)->getNumberFormat()->setFormatCode('_("Rp"* #,##0_);_("Rp"* \(#,##0\);_("Rp"* "-"_);_(@_)');
        }

        # Save Excel document to local hard disk
        $this->Save();
    }

}

?>