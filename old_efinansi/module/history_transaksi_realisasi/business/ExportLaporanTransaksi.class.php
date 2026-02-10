<?php

/**
 * ================= doc ====================
 * FILENAME     : ExportLaporanTransaksi.class.php
 * @package     : ExportLaporanTransaksi
 * scope        : PUBLIC
 * @Author      : noor hadi
 * @Created     : 2016-03-23
 * @Modified    : 2016-03-23
 * @Analysts    : Dyah Fajar N
 * @copyright   : Copyright (c) 2012 - 2016 Gamatechno
 * ================= doc ====================
 */
class ExportLaporanTransaksi extends Database {
    # Constructor

    function __construct($connectionNumber = 0) {
        $this->mSqlFile = 'module/history_transaksi_realisasi/business/export_laporan_transaksi.sql.php';
        $this->_POST = is_object($_POST) ? $_POST->AsArray() : $_POST;
        $this->_GET = is_object($_GET) ? $_GET->AsArray() : $_GET;

        parent::__construct($connectionNumber);
    }

    public function getDataExportLaporanTransaksiItems($params) {


        $return = $this->Open($this->mSqlQueries['get_data_transaksi_items'], array(
            $params['tanggal_awal'],
            $params['tanggal_akhir'],
            '%' . $params['no_bpkb'] . '%',
            $params['status_jurnal'], ((empty($params['status_jurnal']) || $params['status_jurnal'] == 'all' ) ? 1 : 0),
            '%' . $params['fpa'] . '%'
        ));

        return $return;
    }

    /**
     * [getSettingValue description]
     * @param  string $name [description]
     * @return String $name [description]
     */
    public function getSettingValue($name = '') {
        $return = $this->Open($this->mSqlQueries['get_setting_name'], array(
            $name
        ));

        return $return[0]['name'];
    }

}

?>