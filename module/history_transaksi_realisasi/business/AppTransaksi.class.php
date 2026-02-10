<?php

class AppTransaksi extends Database {

    protected $mSqlFile;
    protected $mUserId = NULL;
    public $_POST;
    public $_GET;
    public $indonesianMonth = array(
        0 => array(
            'id' => 0,
            'name' => 'N/A'
        ), array(
            'id' => 1,
            'name' => 'Januari'
        ), array(
            'id' => 2,
            'name' => 'Februari'
        ), array(
            'id' => 3,
            'name' => 'Maret'
        ), array(
            'id' => 4,
            'name' => 'April'
        ), array(
            'id' => 5,
            'name' => 'Mei'
        ), array(
            'id' => 6,
            'name' => 'Juni'
        ), array(
            'id' => 7,
            'name' => 'Juli'
        ), array(
            'id' => 8,
            'name' => 'Agustus'
        ), array(
            'id' => 9,
            'name' => 'September'
        ), array(
            'id' => 10,
            'name' => 'Oktober'
        ), array(
            'id' => 11,
            'name' => 'November'
        ), array(
            'id' => 12,
            'name' => 'Desember'
        )
    );

    function __construct($connectionNumber = 0) {
        $this->mSqlFile = 'module/history_transaksi_realisasi/business/apptransaksi.sql.php';
        $this->_POST = is_object($_POST) ? $_POST->AsArray() : $_POST;
        $this->_GET = is_object($_GET) ? $_GET->AsArray() : $_GET;
        parent::__construct($connectionNumber);
    }

    private function setUserId() {
        if (class_exists('Security')) {
            if (method_exists(Security::Instance(), 'GetUserId')) {
                $this->mUserId = Security::Instance()->GetUserId();
            } else {
                $this->mUserId = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
            }
        }
    }

    public function setDate() {
        $return = $this->Open($this->mSqlQueries['get_date_range'], array());

        return self::ChangeKeyName($return[0]);
    }

    public function getTransaksiDetail($id) {
        $return = $this->Open($this->mSqlQueries['get_data_detail'], array(
            $id
        ));

        return self::ChangeKeyName($return[0]);
    }

    function CekTransaksi($kkb) {
        $result = $this->Open($this->mSqlQueries['cek_transaksi'], array($kkb));
        //print_r($result);
        if ($result[0]['total'] > 0)
            return false;
        else
            return true;
    }

    function GetComboJenisTransaksi() {
        $result = $this->Open($this->mSqlQueries['get_combo_jenis_transaksi'], array());
        return $result;
    }

    function GetComboTipeTransaksi() {
        $result = $this->Open($this->mSqlQueries['get_combo_tipe_transaksi'], array($_SESSION['username']));
        return $result;
    }

    function GetTransaksiById($id) {
        $result = $this->Open($this->mSqlQueries['get_transaksi_by_id'], array($id));
        $result[0]['nominal_disetujui'] = $this->GetNominalSisaDisetujui($id);
        return $result[0];
    }

    function GetTransaksiFile($transId) {
        $result = $this->Open($this->mSqlQueries['get_transaksi_file'], array($transId));
        return $result;
    }

    function GetTransaksiInvoice($transId) {
        $result = $this->Open($this->mSqlQueries['get_transaksi_invoice'], array($transId));
        return $result;
    }

    function GetTransaksiMAK($transId) {
        $result = $this->Open($this->mSqlQueries['get_transaksi_mak'], array($transId));
        if (empty($result))
            $result = $this->Open($this->mSqlQueries['get_transaksi_mak_untuk_pencairan'], array($transId));
        //echo sprintf($this->mSqlQueries['get_transaksi_mak'], $transId);
        return $result[0];
    }

    public function getRangeTanggalPembukuan(){
        $return = $this->Open($this->mSqlQueries['get_range_tanggal_pembukuan'],array());
  
        return $return[0];
    }

    function DoAddTransaksi($arrData) {
        $result = $this->Execute($this->mSqlQueries['do_add_transaksi'], array(
            $arrData['transTtId'],
            $arrData['transTransjenId'],
            $arrData['transUnitkerjaId'],
            $arrData['transReferensi'],
            $arrData['transUserId'],
            $arrData['transTanggal'],
            $arrData['transDueDate'],
            $arrData['transCatatan'],
            $arrData['transNilai'],
            $arrData['transPenanggungJawabNama'],
            $arrData['transIsJurnal'])
        );
        $query = sprintf($this->mSqlQueries['do_add_transaksi'], $arrData['transTtId'], $arrData['transTransjenId'], $arrData['transUnitkerjaId'], $arrData['transReferensi'], $arrData['transUserId'], $arrData['transTanggal'], $arrData['transDueDate'], $arrData['transCatatan'], $arrData['transNilai'], $arrData['transPenanggungJawabNama'], $arrData['transIsJurnal']);
        //echo $query;
        $insertId = $this->LastInsertId();
        $this->DoAddLog("Tambah Transaksi", $query);
        if ($result === true)
            return $insertId;
        else
            return false;
    }

    function DoAddTransaksiDetilPengembalianAnggaran($transId, $mak) {
        list($kegdetId, $pengrealId) = explode("|", $mak);
        $result = $this->Execute($this->mSqlQueries['do_add_transaksi_detil_pengembalian_anggaran'], array($transId, $kegdetId, $pengrealId));

        return $result;
    }

    function DoAddTransaksiDetilAnggaran($transId, $mak) {
        $arrMak = explode("|", $mak); #print_r($arrMak); exit;
        $kegdetId = $arrMak[0];
        $pengrealId = $arrMak[1];

        if (!empty($arrMak[1]))
            $result = $this->Execute($this->mSqlQueries['do_add_transaksi_detil_anggaran'], array($transId, $kegdetId, $pengrealId));
        else
            $result = $this->Execute($this->mSqlQueries['do_add_transaksi_detil_anggaran_penerimaan'], array($transId, $mak));

        /*
          echo sprintf($this->mSqlQueries['do_add_transaksi_detil_anggaran'],
          $transId, $kegdetId, $pengrealId); */
        return $result;
    }

    //tambahan untuk insert transaksi_detail pencaian
    function DoAddTransaksiDetilPencairan($transId, $mak) {
        $arrMak = explode("|", $mak); #print_r($arrMak); exit;
        $kegdetId = $arrMak[0];
        $pengrealId = $arrMak[1];

        $result = $this->Execute($this->mSqlQueries['do_add_transaksi_detil_pencairan'], array($transId, $kegdetId, $pengrealId));

        /*
          echo sprintf($this->mSqlQueries['do_add_transaksi_detil_anggaran'],
          $transId, $kegdetId, $pengrealId); */
        return $result;
    }

    function DoAddTransaksiFile($transId, $arrNama, $path) {
        $arrInsert = array();
        for ($i = 0; $i < sizeof($arrNama); $i++) {
            $arrInsert[] = "('" . $transId . "', '" . $arrNama[$i] . "', '" . $path . "')";
        }
        $strInsert = implode(", ", $arrInsert);
        //echo $sql;
        $result = $this->Execute($this->mSqlQueries['do_add_transaksi_file'], array($strInsert));
        return $result;
    }

    function DoAddTransaksiInvoice($transId, $arrInvoice) {
        for ($i = 0; $i < sizeof($arrInvoice); $i++) {
            $arrInsert[] = "('" . $transId . "', '" . $arrInvoice[$i] . "')";
        }
        $strInsert = implode(", ", $arrInsert);
        //echo $sql;
        $result = $this->Execute($this->mSqlQueries['do_add_transaksi_invoice'], array($strInsert));
        return $result;
    }

    function DoAddPembukuan($transId, $userId) {
        $result = $this->Execute($this->mSqlQueries['do_add_pembukuan'], array($transId, $userId));
        //echo sprintf($this->mSqlQueries['do_add_pembukuan'], $transId, $userId);
        return $this->LastInsertId();
    }

    function DoAddPembukuanDetil($idPembukuan, $nilai, $arrSkenarioId = array()) {
        $strSkenarioId = implode("', '", $arrSkenarioId);

        $result_debet = $this->Execute($this->mSqlQueries['do_add_pembukuan_detil_debet'], array($idPembukuan, $nilai, $strSkenarioId));
        $result_kredit = $this->Execute($this->mSqlQueries['do_add_pembukuan_detil_kredit'], array($idPembukuan, $nilai, $strSkenarioId));
        return $result_debet;
    }

//MULAI EDIT DATA


    function CekTransaksiUpdate($kkb, $transId) {
        $result = $this->Open($this->mSqlQueries['cek_transaksi_update'], array($kkb, $transId));
        //echo sprintf($this->mSqlQueries['cek_transaksi_update'], $kkb, $transId);
        //print_r($result);
        if ($result[0]['total'] > 0)
            return false;
        else
            return true;
    }

    function DoUpdateTransaksi($arrData) {
        //$this->SetDebugOn();
        $result = FALSE;
        $this->StartTrans();
        //echo sprintf($this->mSqlQueries['do_update_transaksi'], $arrData['transTtId'],  $arrData['transTransjenId'],  $arrData['transUnitkerjaId'],  $arrData['transReferensi'],  $arrData['transUserId'], $arrData['transTanggal'], $arrData['transDueDate'], $arrData['transCatatan'], $arrData['transNilai'], $arrData['transPenanggungJawabNama'], $arrData['transIsJurnal'],$arrData['transId']);
        $result = $this->Execute($this->mSqlQueries['do_update_transaksi'], array(
            $arrData['transTtId'],
            $arrData['transTransjenId'],
            $arrData['transUnitkerjaId'],
            $arrData['transReferensi'],
            $arrData['transUserId'],
            $arrData['transTanggal'],
            $arrData['transDueDate'],
            $arrData['transCatatan'],
            $arrData['transNilai'],
            $arrData['transPenanggungJawabNama'],
            $arrData['transPenerimaNama'],
            $arrData['transIsJurnal'],
            $arrData['transId'])
        );


        // insert file attachment
        if ($data['attachment'] && !empty($data['attachment'])) {
            foreach ($data['attachment'] as $attachment) {
                $srcFile = $tmpDirUpload . DS . $attachment['path'];
                $destFile = $uploadDir . DS . $attachment['path'];
                if (file_exists(realpath($srcFile))) {
                    if (rename($srcFile, $destFile)) {
                        $result &= true;
                    } else {
                        $result &= false;
                    }
                }
                $result &= $this->Execute($this->mSqlQueries['do_save_attachment_transaksi'], array(
                    $transaksiId,
                    $attachment['path'],
                    $filePath
                ));
            }
        }

        if ($data['invoices'] && !empty($data['invoices'])) {
            foreach ($data['invoices'] as $inv) {
                $result &= $this->Execute($this->mSqlQueries['do_save_invoice_transaksi'], array(
                    $inv['nomor'],
                    $transaksiId
                ));
            }
        }

        // jika skenario jurnal = AUTO
        if (strtoupper($data['skenario']) == 'AUTO') {
            if ($data['skenario_jurnal'] && !empty($data['skenario_jurnal'])) {
                $result &= $this->Execute($this->mSqlQueries['do_insert_pembukuan_referensi'], array(
                    $transaksiId,
                    $userId,
                    date('Y-m-d', strtotime($data['tanggal'])),
                    $data['keterangan']
                ));

                $pembukuanId = $this->LastInsertId();
                $skenarioId = array();
                foreach ($data['skenario_jurnal'] as $skenario) {
                    $skenarioId[] = $skenario['id'];
                }
                $result &= $this->Execute($this->mSqlQueries['do_insert_pembukuan_detail'], array(
                    $pembukuanId,
                    $data['nominal'],
                    $subacc_1,
                    $subacc_2,
                    $subacc_3,
                    $subacc_4,
                    $subacc_5,
                    $subacc_6,
                    $subacc_7,
                    implode("','", $skenarioId)
                ));
            }
        }

        /**
         * transaksi detail
         */
        if (!empty($arrData['kegiatan_id']) && !empty($arrData['realisasi_id'])) {
            $getRowsTransaksiDetailAnggaran = $this->Open(
                    $this->mSqlQueries['get_rows_transaksi_detail_anggaran'], array(
                $arrData['transId']
                    )
            );

            $getRowsTransaksiDetailPencairan = $this->Open(
                    $this->mSqlQueries['get_rows_transaksi_detail_pencairan'], array(
                $arrData['transId']
                    )
            );
            //cel isi komponen anggaran jika ada, bersihkan dulu
            $getRowsKomponenAnggaran = $this->Open(
                    $this->mSqlQueries['get_rows_komponen_anggaran_by_trans_id'], array(
                $arrData['transId']
                    )
            );

            if (!empty($getRowsKomponenAnggaran) && $getRowsKomponenAnggaran[0]['total'] > 0) {
                $result &= $this->Execute(
                        $this->mSqlQueries['delete_komponen_anggaran_by_trans_id'], array(
                    $arrData['transId']
                        )
                );
            }


            if (!empty($getRowsTransaksiDetailPencairan) && $getRowsTransaksiDetailPencairan[0]['total'] > 0) {
                $result &= $this->Execute(
                        $this->mSqlQueries['delete_transaksi_detail_pencairan_by_trans_id'], array(
                    $arrData['transId']
                        )
                );
            }


            if (!empty($getRowsTransaksiDetailAnggaran) && $getRowsTransaksiDetailAnggaran[0]['total'] > 0) {
                $hapusKomponenAnggaran = $this->Execute(
                        $this->mSqlQueries['delete_transaksi_detail_anggaran_by_trans_id'], array(
                    $arrData['transId']
                        )
                );
            }

            // insert transaksi detail pencairan
            $result &= $this->Execute($this->mSqlQueries['do_insert_transaksi_det_anggaran'], array(
                $arrData['transId'],
                $arrData['kegiatan_id'],
                $arrData['realisasi_id']
            ));

            $result &= $this->Execute($this->mSqlQueries['do_insert_transaksi_det_pencairan'], array(
                $arrData['transId'],
                $arrData['kegiatan_id'],
                $arrData['realisasi_id']
            ));


            $transaksiDtPencairanId = $this->LastInsertId();
            //update isi content komponen anggaran detail
            if ($arrData['komponen'] && !empty($arrData['komponen'])) {
                foreach ($arrData['komponen'] as $komp) {
                    $result &= $this->Execute($this->mSqlQueries['do_insert_transaksi_det_pencairan_komp_belanja'], array(
                        $transaksiDtPencairanId,
                        $komp['pd_id'],
                        $komp['nominal']
                    ));
                }
            }
        }
        //$this->DoAddLog("Update Transaksi", $query);
        $this->EndTrans($result);
        return (bool) $result;
    }

    function DoUpdateTransaksiDetilAnggaran($transId, $mak) {
        $arrMak = explode("|", $mak);
        $kegdetId = $arrMak[0];
        $pengrealId = $arrMak[1];
        $this->StartTrans();
        $result = $this->Execute($this->mSqlQueries['do_update_transaksi_detil_anggaran'], array($kegdetId, $pengrealId, $transId));
        if ($result) {
            $result = $this->Execute($this->mSqlQueries['do_update_transaksi_detil_pencairan'], array($kegdetId, $pengrealId, $transId));
            //echo sprintf($this->mSqlQueries['do_update_transaksi_detil_anggaran'], $transId, $kegdetId, $pengrealId);
        }
        $this->EndTrans($result);
        return $result;
    }

    function DoUpdateTransaksiDetilBelanja($transId, $mak) {
        
    }

    function DoDeleteTransaksiDetilAnggaran($makId) {
        $result = $this->Execute(
                $this->mSqlQueries['do_delete_transaksi_detil_anggaran'], array($makId)
        );
        //echo sprintf($this->mSqlQueries['do_delete_transaksi_detil_anggaran'], $makId);
        return $result;
    }

    function DoDeleteTransaksiInvoice($arrDataId) {
        $dataId = implode("', '", $arrDataId);
        $result = $this->Execute($this->mSqlQueries['do_delete_transaksi_invoice'], array($dataId));
        //echo sprintf($this->mSqlQueries['do_delete_transaksi_invoice'], $dataId);
        return $result;
    }

    function DoDeleteTransaksiFile($arrDataId) {
        $dataId = implode("', '", $arrDataId);
        $result = $this->Execute($this->mSqlQueries['do_delete_transaksi_file'], array($dataId));
        //echo sprintf($this->mSqlQueries['do_delete_transaksi_file'], $dataId);
        return $result;
    }

//SELESAI EDIT DATA


    function DoDeleteDataByArrayId($arrDataId) {
        $dataId = implode("', '", $arrDataId);
        $result = $this->Execute($this->mSqlQueries['do_delete_data_by_array_id'], array($dataId));
        return $result;
    }

    function DoDeleteDataById($dataId) {

        $result = $this->Execute($this->mSqlQueries['do_delete_data_by_id'], array($dataId));

        return $result;
    }

//MULAI CETAK DATA
//FORM CETAK
    function GetDataFormCetak($dataId) {
        $result = $this->Open($this->mSqlQueries['get_data_form_cetak'], array($dataId));
        //echo sprintf($this->mSqlQueries['get_transaksi_mak'], $transId);
        return $result[0];
    }

    function GetComboTahunAnggaran() {
        $result = $this->Open($this->mSqlQueries['get_combo_tahun_anggaran'], array());
        return $result;
    }

    function GetComboTahunAnggaranAktif() {
        $result = $this->Open($this->mSqlQueries['get_combo_tahun_anggaran_aktif'], array());
        return $result[0];
    }

    //LOGGER LOGGER LOGGER

    function DoAddLog($keterangan, $query) {
        $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
        $ip = $_SERVER['REMOTE_ADDR'];
        $result = $this->Execute($this->mSqlQueries['do_add_log'], array($userId, $ip, $keterangan));
        $this->DoAddLogDetil($this->LastInsertId(), $query);
        return $result;
    }

    function DoAddLogDetil($id, $query) {
        $result = $this->Execute($this->mSqlQueries['do_add_log_detil'], array($id, addslashes($query)));
        return $result;
    }

    #tambahan untuk update status transaksi pencairan anggaran (pengajuan_realisaisi)

    function DoUpdateStatusTransaksiDiPengajuanRealisasi($peng_real_id) {
        $result = $this->Execute(
                $this->mSqlQueries['update_status_transaksi_di_pengajuan_realisasi'], array($peng_real_id)
        );
        return $result;
    }

    #tambahan untuk cetak bukti transaksi

    function GetJabatanNama($key) {
        $result = $this->Open($this->mSqlQueries['get_jabatan'], array('%' . $key . '%'));
        return $result;
    }

    function GetJabatan($jab) {
        $result = $this->Open($this->mSqlQueries['get_nama_pejabat'], array($jab));
        return $result[0]['nama'];
    }

    /**
     * fungsi GetLastInsertTransId
     * untuk mendapatkan trasaksi id yang baru saja di simpan
     * @since 16 Januari 2012
     * @access public
     * @return number
     */
    function GetLastInsertTransId() {
        $result = $this->Open($this->mSqlQueries['get_last_insert_trans_id'], array());
        return $result[0]['lastTransId'];
    }

    /**
     * fungsi GetNominalSisaDisetujui
     * untuk mendapatkan nominal sisa yang telah disetujui pada $transId
     * @param Number $transId , transaksi Id
     * @since 13 Februari 2012
     * @access protected
     * @return number
     */
    protected function GetNominalSisaDisetujui($transId) {
        $result = $this->Open($this->mSqlQueries['get_nominal_sisa_disetujui'], array($transId));
        if (!$result) {
            return 0;
        } else {
            return $result[0]['nominal_sisa_disetujui'];
        }
    }

    public function getKomponenAnggaranByTransId($id) {
        $return = $this->Open($this->mSqlQueries['get_komponen_anggaran_by_trans_id'], array(
            $id
        ));

        return self::ChangeKeyName($return);
    }

    /*
     * @param string $camelCasedWord Camel-cased word to be "underscorized"
     * @param string $case case type, uppercase, lowercase
     * @return string Underscore-syntaxed version of the $camelCasedWord
     */

    public static function humanize($camelCasedWord, $case = 'upper') {
        switch ($case) {
            case 'upper':
                $return = strtoupper(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
                break;
            case 'lower':
                $return = strtolower(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
                break;
            case 'title':
                $return = ucwords(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
                break;
            case 'sentences':
                $return = ucfirst(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
                break;
            default:
                $return = strtoupper(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
                break;
        }
        return $return;
    }

    /*
     * @desc change key name from input data
     * @param array $input
     * @param string $case based on humanize method
     * @return array
     */

    public function ChangeKeyName($input = array(), $case = 'lower') {
        if (!is_array($input)) {
            return $input;
        }

        foreach ($input as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $array[$key][self::humanize($k, $case)] = $v;
                }
            } else {
                $array[self::humanize($key, $case)] = $value;
            }
        }

        return (array) $array;
    }

    /**
     * @param string  path_info url to be parsed, default null
     * @return string parsed_url based on Dispatcher::Instance()->getQueryString();
     */
    public function _getQueryString($pathInfo = null) {
        $parseUrl = is_null($pathInfo) ? parse_url($_SERVER['QUERY_STRING']) : parse_url($pathInfo);
        $explodedUrl = explode('&', $parseUrl['path']);
        $requestData = '';
        foreach ($explodedUrl as $path) {
            if (preg_match('/^mod=[a-zA-Z0-9_-]+/', $path)) {
                continue;
            }
            if (preg_match('/^sub=[a-zA-Z0-9_-]+/', $path)) {
                continue;
            }
            if (preg_match('/^act=[a-zA-Z0-9_-]+/', $path)) {
                continue;
            }
            if (preg_match('/^typ=[a-zA-Z0-9_-]+/', $path)) {
                continue;
            }
            if (preg_match('/^ascomponent=[a-zA-Z0-9_-]+/', $path)) {
                continue;
            }
            if (preg_match('/uniqid=[a-zA-Z0-9_-]+/', $path)) {
                continue;
            }

            list($key, $value) = explode('=', $path);
            $requestData[$key] = Dispatcher::Instance()->Decrypt($value);
        }
        if (method_exists(Dispatcher::Instance(), 'getQueryString') === true) {
            $queryString = Dispatcher::Instance()->getQueryString($requestData);
        } else {
            foreach ($requestData as $key => $value) {
                $query[$key] = Dispatcher::Instance()->Encrypt($value);
            }
            $queryString = urldecode(http_build_query($query));
        }
        return $queryString;
    }

    /**
     * @required indonesianMonths
     * @param String $date date format YYYY-mm-dd H:i:s, YYYY-mm-dd
     * @param String $format long, short
     * @return String  Indonesian Date
     */
    public function indonesianDate($date, $format = 'long') {
        $timeFormat = '%02d:%02d:%02d';
        $patern = '/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/';
        $patern1 = '/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/';
        switch ($format) {
            case 'long':
                $dateFormat = '%02d %s %04d';
                break;
            case 'short':
                $dateFormat = '%02d-%s-%04d';
                break;
            default:
                $dateFormat = '%02d %s %04d';
                break;
        }

        if (preg_match($patern, $date, $matches)) {
            $year = (int) $matches[1];
            $month = (int) $matches[2];
            $day = (int) $matches[3];
            $hour = (int) $matches[4];
            $minute = (int) $matches[5];
            $second = (int) $matches[6];
            $mon = $this->indonesianMonth[$month];

            $date = sprintf($dateFormat, $day, $mon, $year);
            $time = sprintf($timeFormat, $hour, $minute, $second);
            $result = $date . ' ' . $time;
        } elseif (preg_match($patern1, $date, $matches)) {
            $year = (int) $matches[1];
            $month = (int) $matches[2];
            $day = (int) $matches[3];
            $mon = $this->indonesianMonth[$month]['name'];

            $date = sprintf($dateFormat, $day, $mon, $year);

            $result = $date;
        } else {
            $date = getdate();
            $year = (int) $date['year'];
            $month = (int) $date['mon'];
            $day = (int) $date['mday'];
            $hour = (int) $date['hours'];
            $minute = (int) $date['minutes'];
            $second = (int) $date['seconds'];
            $mon = $this->indonesianMonth[$month]['name'];

            $date = sprintf($dateFormat, $day, $mon, $year);
            $time = sprintf($timeFormat, $hour, $minute, $second);
            $result = $date . ' ' . $time;
        }

        return $result;
    }

}

?>