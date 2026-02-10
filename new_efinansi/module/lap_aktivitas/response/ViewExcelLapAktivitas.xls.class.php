<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/lap_aktivitas/business/AppLapAktifitas.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';

class ViewExcelLapAktivitas extends XlsResponse {

    var $mWorksheets = array(
        'Data'
    );

    function GetFileName() {

        // name it whatever you want

        return 'laporan_penghasilan_komprehensif_' . date("d-m-Y") . '.xls';
    }

    function ProcessRequest() {
        $Obj = new AppLapAktivitas();
        $get = $_GET->AsArray();

        if (!empty($get['tgl_awal'])) {
            $tglAwal = $get['tgl_awal'];
        } else {
            $tglAwal = date("Y-01-01");
        }

        if (!empty($get['tgl_akhir'])) {
            $tgl = $get['tgl_akhir'];
        } else {
            $tgl = date("Y-m-d");
        }
        
        $gridList = $Obj->GetLaporanAll($tglAwal, $tgl);
        $tgl_akhir = $tgl;
        if (empty($gridList)) {
            $this->mWorksheets['Data']->write(0, 0, 'Data kosong');
        } else {
            $fTitle = $this->mrWorkbook->add_format();
            $fTitle->set_bold();
            $fTitle->set_size(12);
            $fTitle->set_align('center');

            $fFormat = $this->mrWorkbook->add_format();
            $fFormat->set_size(10);
            $fFormat->set_align('center');

            $fFormatKelompok = $this->mrWorkbook->add_format();
            $fFormatKelompok->set_bold();
            $fFormatKelompok->set_size(10);
            $fFormatKelompok->set_align('center');

            #set colom
            $fColNomorbold = $this->mrWorkbook->add_format();
            $fColNomorbold->set_border(1);
            $fColNomorbold->set_bold();
            $fColNomorbold->set_size(10);
            $fColNomorbold->set_align('center');
            $fColNomorbold->set_align('vcenter');

            $fColNilaibold = $this->mrWorkbook->add_format();
            $fColNilaibold->set_border(1);
            $fColNilaibold->set_bold();
            $fColNilaibold->set_size(10);
            $fColNilaibold->set_num_format(4);
            $fColNilaibold->set_align('right');
            $fColNilaibold->set_align('vright');

            $fColNomor = $this->mrWorkbook->add_format();
            $fColNomor->set_border(1);
            $fColNomor->set_size(10);
            $fColNomor->set_align('center');

            $fColNomorakun = $this->mrWorkbook->add_format();
            $fColNomorakun->set_border(1);
            $fColNomorakun->set_size(10);
            $fColNomorakun->set_align('left');

            $fColCtn = $this->mrWorkbook->add_format();
            $fColCtn->set_border(1);
            $fColCtn->set_size(10);
            $fColCtn->set_align('left');

            $fColCtnBold = $this->mrWorkbook->add_format();
            $fColCtnBold->set_border(1);
            $fColCtnBold->set_size(10);
            $fColCtnBold->set_bold();
            $fColCtnBold->set_align('left');

            $fColNilai = $this->mrWorkbook->add_format();
            $fColNilai->set_border(1);
            $fColNilai->set_size(10);
            $fColNilai->set_align('right');
            $fColNilai->set_num_format(4);

            $fColCtnitalic = $this->mrWorkbook->add_format();
            $fColCtnitalic->set_border(1);
            $fColCtnitalic->set_italic();
            $fColCtnitalic->set_size(10);
            $fColCtnitalic->set_align('left');
            $fColCtnitalic->set_num_format(4);

            $fColNilaitalic = $this->mrWorkbook->add_format();
            $fColNilaitalic->set_border(1);
            $fColNilaitalic->set_italic();
            $fColNilaitalic->set_size(10);
            $fColNilaitalic->set_align('right');
            $fColNilaitalic->set_num_format(4);

            #format widht column
            $this->mWorksheets['Data']->set_column(0, 0, 50);
            $this->mWorksheets['Data']->set_column(1, 1, 20);

            #set header
            $this->mWorksheets['Data']->write(0, 0, 'Badan Layanan Umum Universitas Sriwijaya', $fTitle);
            $this->mWorksheets['Data']->merge_cells(0, 0, 0, 1);
            $this->mWorksheets['Data']->write(1, 0, 'Laporan Aktivitas', $fTitle);
            $this->mWorksheets['Data']->merge_cells(1, 0, 1, 1);
            $this->mWorksheets['Data']->write(2, 0, 'Interval waktu ' . IndonesianDate($tglAwal, 'yyyy-mm-dd') . ' s/d ' . IndonesianDate($tgl_akhir, 'yyyy-mm-dd'), $fFormat);
            $this->mWorksheets['Data']->merge_cells(2, 0, 2, 1);
            $this->mWorksheets['Data']->write(3, 0, '(Dinyatakan dalam Rupiah kecuali dinyatakan lain)', $fFormat);
            $this->mWorksheets['Data']->merge_cells(3, 0, 3, 1);
            
            //inisialisasi variable array
            $pendapatan = array();
            $beban = array();
            
            foreach ($gridList as $key => $value) {

                if ($value['status'] == 'Ya') {
                    $pendapatan[$value['kelJnsNama']][] = array(
                        "nama_kel_lap" => $value['nama_kel_lap'],
                        "nilai" => $value['nilai'],
                        "kelJnsNama" => $value['kelJnsNama'],
                        "kellapId" => $value['kellapId']
                    );
                } else {
                    $beban[$value['kelJnsNama']][] = array(
                        "nama_kel_lap" => $value['nama_kel_lap'],
                        "nilai" => $value['nilai'],
                        "kelJnsNama" => $value['kelJnsNama'],
                        "kellapId" => $value['kellapId']
                    );
                }
            }
            
            $totalPendapatan = 0;
            $totalBiaya = 0;

            $row+= 5;
            $coll = 0;
            $this->mWorksheets['Data']->write($row, $coll, GTFWConfiguration::GetValue('language', 'pendapatan'), $fFormatKelompok);
            $this->mWorksheets['Data']->merge_cells($row, $coll, $row, $coll + 1);
            if(!empty($pendapatan)){
                foreach ($pendapatan as $key => $value) {
                    $row+= 2;
                    $coll = 0;
                    $jmlPendapatan = 0;

                    $this->mWorksheets['Data']->write($row, $coll, $key, $fColNomorbold);
                    $this->mWorksheets['Data']->merge_cells($row, $coll, $row + 1, $coll);
                    $coll++;
                    $this->mWorksheets['Data']->write($row, $coll, GTFWConfiguration::GetValue('language', 'jumlah_rp'), $fColNomorbold);
                    $this->mWorksheets['Data']->merge_cells($row, $coll, $row + 1, $coll);
                    $row++;

                    foreach ($value as $detilKey => $detilValue) {
                        $row++;
                        $coll = 0;
                        $this->mWorksheets['Data']->write($row, $coll, $detilValue['nama_kel_lap'], $fColCtn);
                        $coll++;
                        $this->mWorksheets['Data']->write($row, $coll, $detilValue['nilai'], $fColNilai);
                        $jmlPendapatan+= $detilValue['nilai'];
                    }

                    $row++;
                    $coll = 0;
                    $this->mWorksheets['Data']->write($row, $coll, "Total " . $key, $fColCtnBold);
                    $coll++;
                    $this->mWorksheets['Data']->write($row, $coll, $jmlPendapatan, $fColNilaibold);

                    $row++;
                    $totalPendapatan+=$jmlPendapatan;
                }
            }
            $row++;
            $coll = 0;
            $this->mWorksheets['Data']->write($row, $coll, GTFWConfiguration::GetValue('language', 'total_pendapatan'), $fColCtnBold);
            $coll++;
            $this->mWorksheets['Data']->write($row, $coll, $totalPendapatan, $fColNilaibold);

            $row+= 2;
            $coll = 0;
            $this->mWorksheets['Data']->write($row, $coll, GTFWConfiguration::GetValue('language', 'beban'), $fFormatKelompok);
            $this->mWorksheets['Data']->merge_cells($row, $coll, $row, $coll + 1);

            foreach ($beban as $key => $value) {
                $row+= 2;
                $coll = 0;
                $jmlBeban = 0;

                $this->mWorksheets['Data']->write($row, $coll, $key, $fColNomorbold);
                $this->mWorksheets['Data']->merge_cells($row, $coll, $row + 1, $coll);
                $coll++;
                $this->mWorksheets['Data']->write($row, $coll, GTFWConfiguration::GetValue('language', 'jumlah_rp'), $fColNomorbold);
                $this->mWorksheets['Data']->merge_cells($row, $coll, $row + 1, $coll);
                $row++;

                foreach ($value as $detilKey => $detilValue) {
                    $row++;
                    $coll = 0;
                    $this->mWorksheets['Data']->write($row, $coll, $detilValue['nama_kel_lap'], $fColCtn);
                    $coll++;
                    $this->mWorksheets['Data']->write($row, $coll, $detilValue['nilai'], $fColNilai);
                    $jmlBeban+= $detilValue['nilai'];
                }

                $row++;
                $coll = 0;
                $this->mWorksheets['Data']->write($row, $coll, "Total " . $key, $fColCtnBold);
                $coll++;
                $this->mWorksheets['Data']->write($row, $coll, $jmlBeban, $fColNilaibold);

                $row++;
                $totalBeban+=$jmlBeban;
            }

            $row++;
            $coll = 0;
            $this->mWorksheets['Data']->write($row, $coll, GTFWConfiguration::GetValue('language', 'total_beban'), $fColCtnBold);
            $coll++;
            $this->mWorksheets['Data']->write($row, $coll, $totalBeban, $fColNilaibold);

            $row+= 2;
            $coll = 0;
            $this->mWorksheets['Data']->write($row, $coll, GTFWConfiguration::GetValue('language', 'kenaikan_aktiva_bersih'), $fColNomorbold);
            $coll++;
            $this->mWorksheets['Data']->write($row, $coll, ($totalPendapatan - $totalBeban), $fColNilai);
        }
    }

}

?>