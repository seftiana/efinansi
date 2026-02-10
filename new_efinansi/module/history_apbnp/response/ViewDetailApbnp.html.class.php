<?php

/**
 * @package HistoryApbnp
 * @className HistoryApbnp
 * @analyst dyah fajar n <dyah@gamatechno.com>
 * @author noor hadi<noor.hadi@gamatechno.com> 
 * @copyright (c) 2015 Gamatechno Indonesia
 * 
 */

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/history_apbnp/business/HistoryApbnp.class.php';

class ViewDetailApbnp extends HtmlResponse {

    public $obj;
    public $decId;
    public $data;

    function __construct() {
        $this->obj = new HistoryApbnp();
        $this->decId = Dispatcher::Instance()->Decrypt($_GET['id']);
    }

    function TemplateModule() {
        $this->setTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') .
                'module/history_apbnp/template/');
        $this->setTemplateFile('detail_apbnp.html');
    }

    function TemplateBase() {
        $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
        $this->SetTemplateFile('document-common-popup.html');
        $this->SetTemplateFile('layout-common-popup.html');
    }

    function ProcessRequest() {
        $this->data = $this->obj->DetailApbnp($this->decId);

        return $return;
    }

    function ParseTemplate($data = null) {
        $dataList = $this->data;
        $kode_unit_asal = $dataList[0]['unit_kerja_kode_asal'];
        $nama_unit_asal = $dataList[0]['unit_kerja_nama_asal'];
        $kode_kegiatan_asal = $dataList[0]['nomor_kegiatan_asal'];
        $nama_kegiatan_asal = $dataList[0]['kegiatan_asal'];
        $kode_unit_tujuan = $dataList[0]['unit_kerja_kode_tujuan'];
        $nama_unit_tujuan = $dataList[0]['unit_kerja_nama_tujuan'];
        $kode_kegiatan_tujuan = $dataList[0]['nomor_kegiatan_tujuan'];
        $nama_kegiatan_tujuan = $dataList[0]['kegiatan_tujuan'];

        $this->mrTemplate->AddVar('content', 'UNIT_KERJA_KODE_ASAL', $kode_unit_asal);
        $this->mrTemplate->AddVar('content', 'UNIT_KERJA_NAMA_ASAL', $nama_unit_asal);
        $this->mrTemplate->AddVar('content', 'KODE_KEGIATAN_ASAL', $kode_kegiatan_asal);
        $this->mrTemplate->AddVar('content', 'NAMA_KEGIATAN_ASAL', $nama_kegiatan_asal);

        $this->mrTemplate->AddVar('content', 'UNIT_KERJA_KODE_TUJUAN', $kode_unit_tujuan);
        $this->mrTemplate->AddVar('content', 'UNIT_KERJA_NAMA_TUJUAN', $nama_unit_tujuan);
        $this->mrTemplate->AddVar('content', 'KODE_KEGIATAN_TUJUAN', $kode_kegiatan_tujuan);
        $this->mrTemplate->AddVar('content', 'NAMA_KEGIATAN_TUJUAN', $nama_kegiatan_tujuan);

        $komponen_asal = array();
        $komponen_tujuan = array();


        $index = 0;
        for ($i = 0; $i < count($dataList); $i++) {
            #echo $index.'<br />';
            if ($dataList[$i]['type_movement'] != $dataList[$i - 1]['type_movement']) {
                $index = 0;
            }

            if ($dataList[$i]['type_movement'] == 'asal') {
                $komponen_asal[$index]['kode'] = $dataList[$i]['kode_komponen'];
                $komponen_asal[$index]['nama'] = $dataList[$i]['nama_komponen'];
                $komponen_asal[$index]['nilai_awal'] = $dataList[$i]['nilai_komponen_semula'];
                $komponen_asal[$index]['nilai_movement'] = $dataList[$i]['nilai_komponen_movement'];
                $komponen_asal[$index]['nilai_sekarang'] = ($dataList[$i]['nilai_komponen_semula'] - $dataList[$i]['nilai_komponen_movement']);
                $komponen_asal[$index]['nama_bulan_asal'] = $this->obj->indonesianMonth[($dataList[$i]['bulan_anggaran_asal'] - 1)]['name'];
            } else {
                $komponen_tujuan[$index]['kode'] = $dataList[$i]['kode_komponen'];
                $komponen_tujuan[$index]['nama'] = $dataList[$i]['nama_komponen'];
                $komponen_tujuan[$index]['nilai_awal'] = $dataList[$i]['nilai_komponen_semula'];
                $komponen_tujuan[$index]['nilai_movement'] = $dataList[$i]['nilai_komponen_movement'];
                $komponen_tujuan[$index]['nilai_sekarang'] = ($dataList[$i]['nilai_komponen_semula'] + $dataList[$i]['nilai_komponen_movement']);
                $komponen_tujuan[$index]['nama_bulan_tujuan'] = $this->obj->indonesianMonth[($dataList[$i]['bulan_anggaran_tujuan'] - 1 )]['name'];
            }
            $index++;
        }

        // get bulan kegiatan
        $this->mrTemplate->AddVar('content', 'BULAN_KEGIATAN_ASAL', $komponen_asal[0]['nama_bulan_asal']);
        $this->mrTemplate->AddVar('content', 'BULAN_KEGIATAN_TUJUAN', $komponen_tujuan[0]['nama_bulan_tujuan']);

        if (count($komponen_asal) == 0) {
            $this->mrTemplate->AddVar('komp_asal_grid', 'DATA_EMPTY', 'YES');
        } else {
            $this->mrTemplate->AddVar('komp_asal_grid', 'DATA_EMPTY', 'NO');
            for ($i = 0; $i < count($komponen_asal); $i++) {
                # code...
                $komponen_asal[$i]['nilai'] = number_format($komponen_asal[$i]['nilai_movement'], 0, ',', '.');
                $komponen_asal[$i]['nilai_sekarang'] = number_format($komponen_asal[$i]['nilai_sekarang'], 0, ',', '.');
                $komponen_asal[$i]['nilai_lalu'] = number_format($komponen_asal[$i]['nilai_awal'], 0, ',', '.');
                $this->mrTemplate->AddVars('komp_asal_list', $komponen_asal[$i], '');
                $this->mrTemplate->parseTemplate('komp_asal_list', 'a');
            }
        }

        if (count($komponen_tujuan) == 0) {
            $this->mrTemplate->AddVar('komp_tujuan_grid', 'DATA_EMPTY', 'YES');
        } else {
            $this->mrTemplate->AddVar('komp_tujuan_grid', 'DATA_EMPTY', 'NO');
            for ($i = 0; $i < count($komponen_tujuan); $i++) {
                # code...
                $komponen_tujuan[$i]['nilai'] = number_format($komponen_tujuan[$i]['nilai_movement'], 0, ',', '.');
                $komponen_tujuan[$i]['nilai_sekarang'] = number_format($komponen_tujuan[$i]['nilai_sekarang'], 0, ',', '.');
                $komponen_tujuan[$i]['nilai_lalu'] = number_format($komponen_tujuan[$i]['nilai_awal'], 0, ',', '.');
                $this->mrTemplate->AddVars('komp_tujuan_list', $komponen_tujuan[$i], '');
                $this->mrTemplate->parseTemplate('komp_tujuan_list', 'a');
            }
        }
    }

}

?>