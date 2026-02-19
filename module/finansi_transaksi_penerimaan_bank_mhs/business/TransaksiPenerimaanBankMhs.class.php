<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/user_unit_kerja/business/UserUnitKerja.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/finansi_transaksi_penerimaan_bank_mhs/business/PopupPembayaranMhs.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/generate_number/business/GenerateNumber.class.php';

class TransaksiPenerimaanBankMhs extends Database {

    private $mUnitObj;
    private $mPembayaranMhs;
    protected $mSqlFile;
    protected $mUserId = NULL;
    public $_POST;
    public $_GET;
    public $method;
    # subaccount
    public $subAccName;
    public $subAccJml;
    public $defaultSubacc;
    private $mNumber;
    
    protected $mIsIntegrasiGtPm;
    
    /**
     * untuk menentukan tipe transaksi bank
     * diambil dari data enum tabel finansi_transaksi_bank field transaksiBankTipeTransaksi
     * index 0 : untuk penerimaan selain dari gtPM
     * -> index 1 - 3 untuk penerimaan dari gtPM
     * index 1 : untuk pengakuan
     * index 2 : untuk pengakuan deposit masuk
     * index 3 : untuk piutang
     * index 4 : untuk sisa lppa (@added since 7/7/2021)
     */
    private $_mTipeTransaksiBank = array(
        0 => array('id' => 'penerimaan', 'name' => 'Penerimaan'),        
        1 => array('id' => 'pengakuan', 'name' => 'Pengakuan'),
        2 => array('id' => 'pengakuan_depmasuk', 'name' => 'Pengakuan Deposit Masuk'),
        3 => array('id' => 'piutang', 'name' => 'Piutang'),
        4 => array('id' => 'lppa_sisa', 'name' => 'Sisa LPPA'),   
    );

    public function __construct($connectionNumber = 0) {
        $this->mSqlFile = 'module/finansi_transaksi_penerimaan_bank_mhs/business/transaksi_penerimaan_bank_mhs.sql.php';
        $this->_POST = is_object($_POST) ? $_POST->AsArray() : $_POST;
        $this->_GET = is_object($_GET) ? $_GET->AsArray() : $_GET;
        $this->method = strtolower($_SERVER['REQUEST_METHOD']);
        $this->mUnitObj = new UserUnitKerja();
        parent::__construct($connectionNumber);
        $this->subAccName = array('Pertama', 'Kedua', 'Ketiga', 'Keempat', 'Kelima', 'Keenam', 'Ketujuh');
        $this->subAccJml = GTFWConfiguration::GetValue('application', 'subAccJml');
        $this->defaultSubacc = str_replace('9', '0', GTFWConfiguration::GetValue('application', 'subAccFormat'));


        $this->mNumber = new GenerateNumber($connectionNumber);
        $this->mPembayaranMhs = new PopupPembayaranMhs($connectionNumber);
        
        $this->mIsIntegrasiGtPm = true;
    }

    /**
     * getTipeTransaksi
     * @return array()
     */
    public function getTipeTransaksi() {
        return $this->_mTipeTransaksiBank;
    }
    
    private function _getTipeTransaksiById($id) {
        return $this->_mTipeTransaksiBank[$id]['id'];
    }
    
    private function _getTipeTransaksiBank($key='') {
        $value = '';
        switch ($key){
            case 'pengakuan':
                $value = $this->_getTipeTransaksiById(1);
                break;
            case 'pengakuan_depmasuk':
                $value = $this->_getTipeTransaksiById(2);
                break;
            case 'piutang':
                $value = $this->_getTipeTransaksiById(3);
                break;
            case 'lppa_sisa':
                $value = $this->_getTipeTransaksiById(4);
                break;
            default :
                $value = $this->_getTipeTransaksiById(0);
                break;
        }
        
        return $value;
    }
    
    /**
     * end getTipe transaksi
     */

   /**
    * [getPeriodeTahunPembukuan description]
    * @param  array
    * @return [type]
    */
   public function getTahunPembukuanPeriode($param = array())
   {
      $default    = array(
         'open' => false
      );
      $options    = array_merge($default, (array)$param);
      $return     = $this->Open($this->mSqlQueries['get_tahun_pembukuan_periode'], array(
         (int)($options['open'] === false)
      ));

      return $return;
   }

    public function getRangeTanggalPembukuan(){
        $return = $this->Open($this->mSqlQueries['get_tahun_pembukuan_periode'],array(0));

        return $return[0];
    }

    //untuk export excel
    public function getTransaksiDetil($id) {
        $return = $this->Open($this->mSqlQueries['get_transaksi_detail'], array(
            $id
        ));

        return self::ChangeKeyName($return[0]);
    }

    public function getListTransaksiDetail($id) {
        $return = $this->Open($this->mSqlQueries['get_list_transaksi_detil'], array($id));

        return self::ChangeKeyName($return);
    }

    //end
    private function setUserId() {
        if (class_exists('Security')) {
            $this->mUserId = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
        }
    }

    public function getUserId() {
        $this->setUserId();
        return (int) $this->mUserId;
    }

    public function GetRealIP() {
        if ($_ENV["HTTP_CLIENT_IP"]) :
            $ip_address = $_ENV["HTTP_CLIENT_IP"];
        elseif ($_ENV["HTTP_X_FORWARDED_FOR"]) :
            $ip_address = $_ENV["HTTP_X_FORWARDED_FOR"];
        elseif ($_ENV["HTTP_X_FORWARDED"]) :
            $ip_address = $_ENV["HTTP_X_FORWARDED"];
        elseif ($_ENV["HTTP_FORWARDED_FOR"]) :
            $ip_address = $_ENV["HTTP_FORWARDED_FOR"];
        elseif ($_ENV["HTTP_FORWARDED"]) :
            $ip_address = $_ENV["HTTP_FORWARDED"];
        elseif ($_SERVER['REMOTE_ADDR']) :
            $ip_address = $_SERVER['REMOTE_ADDR'];
        endif;

        return $ip_address;
    }

    public function getApplicationSetting($param = null) {
        $return = $this->Open($this->mSqlQueries['get_setting_value'], array(
            strtoupper($param)
        ));

        if ($return && !empty($return)) {
            return $return[0]['setting'];
        } else {
            return null;
        }
    }

    public function getCekNoref($noref) {
        $return = $this->Open($this->mSqlQueries['get_cek_noref'], array($noref));
        if ($return[0]['total'] > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function getPaternSubAccount() {
        $return = $this->Open($this->mSqlQueries['get_patern_sub_account'], array());
        if ($return && !empty($return)) {
            $return['patern'] = $return[0]['patern'];
            $return['regex'] = '/^' . $return[0]['regex'] . '$/';
        } else {
            $return['patern'] = GTFWConfiguration::GetValue('application', 'subAccFormat');
            $return['regex'] = '/^([a-zA-Z0-9]{1,2})-([a-zA-Z0-9]{1,2})-([a-zA-Z0-9]{1,2})-([a-zA-Z0-9]{1,2})-([a-zA-Z0-9]{1,2})-([a-zA-Z0-9]{1,2})-([a-zA-Z0-9]{1,2})$/';
        }

        return $return;
    }

    public function getDefaultSubAkun() {
        $return = $this->Open($this->mSqlQueries['get_patern_sub_account'], array());
        $result['patern'] = $return[0]['patern'];
        $result['regex'] = '/^' . $return[0]['regex'] . '$/';
        $result['default'] = $return[0]['default'];

        return $result;
    }

    public function getSubAccountCombo(){
        return $this->Open($this->mSqlQueries['get_sub_account_combobox'],array());
    }
     
    public function doCheckSubAkun($kode) {
        $subAkun = array(1 => NULL, NULL, NULL, NULL, NULL, NULL, NULL);
        $getSubAkun = $this->Open($this->mSqlQueries['get_patern_sub_account'], array());
        $regex = '/^' . $getSubAkun[0]['regex'] . '$/';
        $default = $getSubAkun[0]['default'];
        $kode = preg_replace('/\s[\s]+/', '', $kode);
        $kode = preg_replace('/[\_]+/', '', $kode);
        $kode = preg_replace('/[\_]+/', '', $kode);

        if (preg_match($regex, $kode, $matches)) {
            while (list($key, $subakunKode) = each($matches)) {
                if ((int) $key === 0) {
                    continue;
                }
                $subAkun[$key] = $subakunKode;
            }
        } else {
            preg_match($regex, $default, $matches);
            while (list($key, $subakunKode) = each($matches)) {
                if ((int) $key === 0) {
                    continue;
                }
                $subAkun[$key] = $subakunKode;
            }
        }

        $return = $this->Open($this->mSqlQueries['get_sub_account'], array(
            $subAkun[2],
            $subAkun[3],
            $subAkun[4],
            $subAkun[5],
            $subAkun[6],
            $subAkun[7],
            $subAkun[1]
        ));

        $subAccount = $return[0]['subAkun'];
        if (strcmp($kode, $subAccount) == 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getTahunPencatatan() {
        $getdate = getdate();
        $year = (int) $getdate['year'];
        $return = $this->Open($this->mSqlQueries['get_min_max_tahun_pencatatan'], array());
        if ($return && !empty($return)) {
            $result['min_year'] = $return[0]['minTahun'];
            $result['max_year'] = $return[0]['maxTahun'];
        } else {
            $result['min_year'] = $year - 5;
            $result['max_year'] = $year + 5;
        }

        return $result;
    }

    public function GetBentukTransaksi() {
        $return = $this->open($this->mSqlQueries['get_bentuk_transaksi'], array());
        return $return;
    }

    public function getDataJurnal($offset, $limit, $param = array()) {

        $return = $this->Open($this->mSqlQueries['get_data_jurnal'], array(
            '%' . $param['referensi'] . '%',
            $param['posting'],
            (int) ($param['posting'] == '' OR strtolower($param['posting']) == 'all'),
            date('Y-m-d', strtotime($param['start_date'])),
            date('Y-m-d', strtotime($param['end_date'])),
            $offset,
            $limit
        ));

        return $return;
    }

    public function getCountJurnal($param = array()) {
        $return = $this->Open($this->mSqlQueries['get_count_jurnal'], array(
            '%' . $param['referensi'] . '%',
            $param['posting'],
            (int) ($param['posting'] == '' OR strtolower($param['posting']) == 'all'),
            date('Y-m-d', strtotime($param['start_date'])),
            date('Y-m-d', strtotime($param['end_date']))
        ));

        if ($return) {
            return $return[0]['count'];
        } else {
            return 0;
        }
    }

    public function getDataJurnalDetail($id, $prId) {
        $return = $this->Open($this->mSqlQueries['get_data_jurnal_detail'], array(
            $id,
            $prId
        ));

        return $return[0];
    }

    public function getDataHistoryJurnal($id) {
        $return = $this->Open($this->mSqlQueries['get_history_jurnal'], array(
            $id
        ));

        return $return;
    }

    public function getDataJurnalSubAkun($id, $prId) {
        $return = $this->Open($this->mSqlQueries['get_data_jurnal_sub_akun'], array(
            $id,
            $prId
        ));

        return $return;
    }

    public function getDataPembayaranByTransId($id) {
        $return = $this->Open($this->mSqlQueries['get_data_pembayaran_by_trans_id'], array(
            $id
        ));

        return $return;
    }

    public function doSaveJurnal($param = array()) {
        $userId = $this->getUserId();
        $ipAddress = (string) $this->GetRealIP();
        $queryLog = array();
        $result = true;
        $this->StartTrans();
        if (!is_array($param)) {
            $result &= false;
        }
        //$unitKerja = $this->mUnitObj->GetUnitKerjaRefUser($userId);
        $unitId = $param['unit_kerja_id']; //$unitKerja['id'];
        $tanggal = date('Y-m-d', strtotime($param['tanggal']));
        // set tahun pembukuan dan tahun anggaran
        $result &= $this->Execute($this->mSqlQueries['do_set_tahun_pembukuan'], array());
        $result &= $this->Execute($this->mSqlQueries['do_set_tahun_anggaran'], array());
        $result &= $this->Execute($this->mSqlQueries['get_set_tahun_pembukuan'], array());
        $result &= $this->Execute($this->mSqlQueries['get_set_tahun_anggaran'], array());
        $result &= $this->Execute($this->mSqlQueries['get_set_reference_number'], array(
            $tanggal,
            $tanggal,
            $unitId,
            $unitId,
            $tanggal,
            $tanggal
        ));

        $result &= $this->Execute($this->mSqlQueries['do_set_realname_user'], array(
            $userId
        ));
        if ($param['auto_number'] === 'Y') {
            $nomorReferensi = $this->mNumber->getTransReferenceBR(
                    $tanggal
            );
        } else {
            $nomorReferensi = $param['bpkb'];
        }

        $result &= $this->Execute($this->mSqlQueries['do_save_transaksi'], array(
            $unitId,
            $nomorReferensi,
            $userId,
            $tanggal,
            $tanggal,
            $param['keterangan'],
            $param['nominal_debet']
        ));

        $queryLog[] = sprintf($this->mSqlQueries['do_set_tahun_pembukuan']);
        $queryLog[] = sprintf($this->mSqlQueries['do_set_tahun_anggaran']);
        $queryLog[] = sprintf($this->mSqlQueries['get_set_tahun_pembukuan']);
        $queryLog[] = sprintf($this->mSqlQueries['get_set_tahun_anggaran']);
        $queryLog[] = sprintf($this->mSqlQueries['get_set_reference_number'], $tanggal, $tanggal, $unitId, $unitId, $tanggal, $tanggal);
        $queryLog[] = sprintf($this->mSqlQueries['do_set_realname_user'], $userId);
        $queryLog[] = sprintf($this->mSqlQueries['do_save_transaksi'], $unitId, $nomorReferensi, $userId, $tanggal, $tanggal, $param['keterangan'], $param['nominal_debet']);

        $transaksiId = $this->LastInsertId();  

        $kelompokReferensi = $param['ref_kelompok'];
        if($kelompokReferensi == 'LPPA') {            
            $param['lppa_id'] = $param['rpen_id'];
            $param['rpen_id'] = '';
            $tipeTransaksiBank = $this->_getTipeTransaksiBank('lppa_sisa');
        } else {
            $param['lppa_id'] = '';
            $tipeTransaksiBank = $this->_getTipeTransaksiBank($param['pemb_tipe_pembayaran']);
        }

        //transaksi bank 
        $result &= $this->Execute($this->mSqlQueries['do_insert_transaksi_bank'], array(
            $nomorReferensi,
            $nomorReferensi,
            $param['tanggal'],
            $param['nama_penyetor'],
            $param['nama_penerima'],
            $param['nominal_debet'],
            $userId,
            $param['unit_kerja_id'],
            $param['rpen_id'] == '' ? NULL : $param['rpen_id'],
            $param['rpen_nominal'],
            (empty($param['skenario_id']) ? NULL : $param['skenario_id']),
            $tipeTransaksiBank,
            $param['pemb_nominal'],
            $param['auto_number'],
            $param['lppa_id'] == '' ? NULL : $param['lppa_id']
        ));

        $transaksi_bank_id = $this->LastInsertId();

        // insert transaksi detail penerimaan bank
        $result &= $this->Execute($this->mSqlQueries['do_insert_transaksi_det_penerimaan_bank'], array(
            $transaksiId,
            $transaksi_bank_id
        ));
        $queryLog[] = sprintf($this->mSqlQueries['do_insert_transaksi_bank'], $nomorReferensi, $nomorReferensi, $param['tanggal'], 
            $param['nama_penyetor'], $param['nama_penerima'], $param['nominal_debet'], $userId, $param['unit_kerja_id'], $param['rpen_id'], 
            $param['rpen_nominal'], (empty($param['skenario_id']) ? NULL : $param['skenario_id']), $tipeTransaksiBank, $param['pemb_nominal'], 
            $param['auto_number'], $param['lppa_id']);
        $queryLog[] = sprintf($this->mSqlQueries['do_insert_transaksi_det_penerimaan_bank'], $transaksiId, $transaksi_bank_id);

        //insert ke table finansi_pa_transaksi_pembayaran dan finansi_pa_transaksi_pembayaran_deposit_masuk   
        if($param['pemb_prodi_id'] != ''){    
            $result &= $this->_saveTransaksiPembayaran($transaksi_bank_id, $param);
        }
        //end
        // end transaksi bank
        // CHECK STATUS AUTO APPROVE JURNAL
        // $getSettingAutoApprove  = GTFWConfiguration::GetValue('application', 'auto_approve');
        $autoApprove = $this->getApplicationSetting('JURNAL_AUTO_APPROVE');
        $statusKas = NULL;
        $bentukTransaksi = NULL;
        $statusApproved = 'T';
        if ($autoApprove !== NULL AND (bool) $autoApprove === TRUE) {
            $statusKas = $param['status'];
            $bentukTransaksi = $param['bentuk_transaksi'];
            $statusApproved = 'Y';
        }

        // insert into pembukuan referensi
        $result &= $this->Execute($this->mSqlQueries['do_insert_pembukuan_referensi'], array(
            $transaksiId,
            $userId,
            $tanggal,
            $param['keterangan'],
            $statusApproved,
            $statusKas,
            $statusKas,
            $statusKas,
            $bentukTransaksi,
            $bentukTransaksi,
            $bentukTransaksi
        ));

        $queryLog[] = sprintf($this->mSqlQueries['do_insert_pembukuan_referensi'], $transaksiId, $userId, $tanggal, $param['keterangan'], $statusApproved, $statusKas, $statusKas, $statusKas, $bentukTransaksi, $bentukTransaksi, $bentukTransaksi);

        // get pembukuan id
        $pembukuanId = $this->LastInsertId();
        // delete pembukuan detail
        $result &= $this->Execute($this->mSqlQueries['do_delete_pembukuan_detail'], array(
            $pembukuanId
        ));

        $queryLog[] = sprintf($this->mSqlQueries['do_delete_pembukuan_detail'], $pembukuanId);

        if (!empty($param['akun_debet'])) {
            // insert pembukuan detail debet
            foreach ($param['akun_debet'] as $debet) {
                $subAkun = preg_replace('/\s[\s]+/', '', $debet['sub_akun']);
                $subAkun = preg_replace('/[\_]+/', '', $subAkun);
                $subAkun = preg_replace('/[\_]+/', '', $subAkun);
                list($subacc_1, $subacc_2, $subacc_3, $subacc_4, $subacc_5, $subacc_6, $subacc_7) = explode('-', $subAkun);

                $result &= $this->Execute($this->mSqlQueries['do_insert_pembukuan_detail'], array(
                    $pembukuanId,
                    $debet['id'],
                    $debet['nominal'],
                    $debet['keterangan'],
                    $debet['referensi'],
                    'D',
                    $subacc_1,
                    $subacc_2,
                    $subacc_3,
                    $subacc_4,
                    $subacc_5,
                    $subacc_6,
                    $subacc_7
                ));

                $queryLog[] = sprintf($this->mSqlQueries['do_insert_pembukuan_detail'], $pembukuanId, $debet['id'], $debet['nominal'], $debet['keterangan'], $debet['referensi'], 'D', $subacc_1, $subacc_2, $subacc_3, $subacc_4, $subacc_5, $subacc_6, $subacc_7);
            }
        }

        if (!empty($param['akun_kredit'])) {
            // insert pembukuan detail kredit
            foreach ($param['akun_kredit'] as $kredit) {
                $subAkun = preg_replace('/\s[\s]+/', '', $kredit['sub_akun']);
                $subAkun = preg_replace('/[\_]+/', '', $subAkun);
                $subAkun = preg_replace('/[\_]+/', '', $subAkun);
                list($subacc_1, $subacc_2, $subacc_3, $subacc_4, $subacc_5, $subacc_6, $subacc_7) = explode('-', $subAkun);

                $result &= $this->Execute($this->mSqlQueries['do_insert_pembukuan_detail'], array(
                    $pembukuanId,
                    $kredit['id'],
                    $kredit['nominal'],
                    $kredit['keterangan'],
                    $kredit['referensi'],
                    'K',
                    $subacc_1,
                    $subacc_2,
                    $subacc_3,
                    $subacc_4,
                    $subacc_5,
                    $subacc_6,
                    $subacc_7
                ));

                //isi tabel finansi_pa_transaksi_bank_detail
                $result &= $this->Execute($this->mSqlQueries['do_insert_transaksi_bank_detil'], array(
                    $transaksi_bank_id,
                    $kredit['nama'],
                    $param['tanggal'],
                    $kredit['nominal'],
                    $userId
                ));

                $queryLog[] = sprintf($this->mSqlQueries['do_insert_pembukuan_detail'], $pembukuanId, $kredit['id'], $kredit['nominal'], $kredit['keterangan'], $kredit['referensi'], 'K', $subacc_1, $subacc_2, $subacc_3, $subacc_4, $subacc_5, $subacc_6, $subacc_7);
                $queryLog[] = sprintf($this->mSqlQueries['do_insert_transaksi_bank_detil'], $transaksi_bank_id, $kredit['nama'], $param['tanggal'], $kredit['nominal'], $userId);
            }
        }

        //$result &= $this->_doSendDataGtPM($param, $result);

        if ($result) {
            // log query
            $result &= $this->Execute($this->mSqlQueries['do_add_log'], array(
                $userId,
                $ipAddress,
                'Tambah Jurnal dan atau Transaki Penerimaan Bank'
            ));

            $loggerId = $this->LastInsertId();
            if (is_array($queryLog)) {
                foreach ($queryLog as $query) {
                    $result &= $this->Execute($this->mSqlQueries['do_add_log_detil'], array(
                        $loggerId,
                        addslashes($query)
                    ));
                }
            }
        }

        return $this->EndTrans($result);
    }

    public function doUpdateData($param = array()) {
        $userId = $this->getUserId();
        $ipAddress = (string) $this->GetRealIP();
        $queryLog = array();
        $result = true;
        $this->StartTrans();
        if (!is_array($param)) {
            $result &= false;
        }
        $transaksiId = $param['id'];
        $pembukuanId = $param['pembukuan_id'];
        //$unitKerja = $this->mUnitObj->GetUnitKerjaRefUser($userId);
        $unitId = $param['unit_kerja_id']; //,$unitKerja['id'];
        $tanggal = date('Y-m-d', strtotime($param['tanggal']));
        $result &= $this->Execute($this->mSqlQueries['do_set_realname_user'], array(
            $userId
        ));

        if ($param['auto_number'] == 'Y') {
            if ((date('Y-m', strtotime($param['tanggal'])) != date('Y-m', strtotime($param['tanggal_old']))) ||
                    ($param['auto_number'] !== $param['auto_number_status'])) {
                $nomorReferensi = $this->mNumber->getTransReferenceBR($tanggal);
            } else {
                $nomorReferensi = $param['bpkb_old'];
            }
        } else {
            $nomorReferensi = $param['bpkb'];
        }


        // update table transaksi
        $result &= $this->Execute($this->mSqlQueries['do_update_transaksi_jurnal'], array(
            $unitId,
            $nomorReferensi,
            $userId,
            $tanggal,
            $tanggal,
            $param['keterangan'],
            $param['nominal_debet'],
            $transaksiId
        )); 
        //update table finansi_pa_transaksi_bank 

        $kelompokReferensi = $param['ref_kelompok'];
        if($kelompokReferensi == 'LPPA') {            
            $param['lppa_id'] = $param['rpen_id'];
            $param['rpen_id'] = '';
            $tipeTransaksiBank = $this->_getTipeTransaksiBank('lppa_sisa');
        } else {
            $param['lppa_id'] = '';
            $tipeTransaksiBank = $this->_getTipeTransaksiBank($param['pemb_tipe_pembayaran']);
        }
        //memastikan transaksi memiliki bank id
        $getCheckTransaksiBank = $this->Open($this->mSqlQueries['get_transaksi_bank_id'],array($transaksiId));
        $transaksiBankId =''; 
        if(!empty($getCheckTransaksiBank)) {
            $result &= $this->Execute($this->mSqlQueries['do_update_transaksi_bank'], array(
                $nomorReferensi,
                $nomorReferensi,
                $param['tanggal'],
                $param['nama_penyetor'],
                $param['nama_penerima'],
                $param['nominal_debet'],
                $userId,
                $param['unit_kerja_id'],
                $param['rpen_id'] == '' ? NULL : $param['rpen_id'],
                $param['rpen_nominal'],
                (empty($param['skenario_id']) ? NULL : $param['skenario_id']),
                $tipeTransaksiBank,
                $param['pemb_nominal'],
                $param['auto_number'],
                $param['lppa_id'] == '' ? NULL : $param['lppa_id'],
                $transaksiId
            ));

            $queryLog[] = sprintf($this->mSqlQueries['do_update_transaksi_bank'], $nomorReferensi, $nomorReferensi,
                 $param['tanggal'], $param['nama_penyetor'], $param['nama_penerima'], $param['nominal_debet'], $userId, 
                 $param['unit_kerja_id'], $param['rpen_id'], $param['rpen_nominal'], (empty($param['skenario_id']) ? NULL : $param['skenario_id']), 
                 $tipeTransaksiBank, $param['pemb_nominal'], $param['auto_number'],$param['lppa_id'] , $transaksiId);
        } else {
            // input baru
            $result &= $this->Execute($this->mSqlQueries['do_insert_transaksi_bank'], array(
                $nomorReferensi,
                $nomorReferensi,
                $param['tanggal'],
                $param['nama_penyetor'],
                $param['nama_penerima'],
                $param['nominal_debet'],
                $userId,
                $param['unit_kerja_id'],
                $param['rpen_id'] == '' ? NULL : $param['rpen_id'],
                $param['rpen_nominal'],
                (empty($param['skenario_id']) ? NULL : $param['skenario_id']),
                $tipeTransaksiBank,
                $param['pemb_nominal'],
                $param['auto_number'],
                $param['lppa_id'] == '' ? NULL : $param['lppa_id']
            ));
    
            $transaksiBankId = $this->LastInsertId(); 
            // insert transaksi detail penerimaan bank
            $result &= $this->Execute($this->mSqlQueries['do_insert_transaksi_det_penerimaan_bank'], array(
                $transaksiId,
                $transaksiBankId
            ));

            $queryLog[] = sprintf($this->mSqlQueries['do_insert_transaksi_bank'], $nomorReferensi, $nomorReferensi, 
                $param['tanggal'], $param['nama_penyetor'], $param['nama_penerima'], $param['nominal_debet'], $userId, $param['unit_kerja_id'], 
                $param['rpen_id'], $param['rpen_nominal'], (empty($param['skenario_id']) ? NULL : $param['skenario_id']), $tipeTransaksiBank, 
                $param['pemb_nominal'], $param['auto_number'], $param['lppa_id'] );
            $queryLog[] = sprintf($this->mSqlQueries['do_insert_transaksi_det_penerimaan_bank'], $transaksiId, $transaksiBankId);
        } 
        //hapus table finansi_transaksi_pemerinaan_bank_detail untuk diisi lagi pada detail kredit
        $result &= $this->Execute( $this->mSqlQueries['do_delete_transaksi_bank_detail_transaksi'], array(
            $transaksiId
        )); 
        
        $queryLog[] = sprintf($this->mSqlQueries['do_delete_transaksi_bank_detail_transaksi'], $transaksiId);
        //end
        // delete pembukuan detail
        $result &= $this->Execute($this->mSqlQueries['do_delete_pembukuan_detail'], array(
            $pembukuanId
        ));
 
        //update data transaksi pembayaran 
        if($param['pemb_prodi_id'] != '' && $param['tipe_transaksi'] != 'penerimaan'){
            $result &= $this->_updateTransaksiPembayaran($transaksiId, $param);
        }


        //end
        // CHECK STATUS AUTO APPROVE JURNAL
        // $getSettingAutoApprove  = GTFWConfiguration::GetValue('application', 'auto_approve');
        $autoApprove = $this->getApplicationSetting('JURNAL_AUTO_APPROVE');
        $statusKas = NULL;
        $bentukTransaksi = NULL;
        $statusApproved = 'T';
        if ($autoApprove !== NULL AND (bool) $autoApprove === TRUE) {
            $statusKas = $param['status'];
            $bentukTransaksi = $param['bentuk_transaksi'];
            $statusApproved = 'Y';
        }

        // UPDATE PEMBUKUAN REFERENSI
        $result &= $this->Execute($this->mSqlQueries['do_update_pembukuan_referensi'], array(
            $transaksiId,
            $userId,
            $tanggal,
            $param['keterangan'],
            $statusKas,
            $statusKas,
            $statusKas,
            $bentukTransaksi,
            $bentukTransaksi,
            $bentukTransaksi,
            $pembukuanId
        ));

        $queryLog[] = sprintf($this->mSqlQueries['do_set_realname_user'], $userId);
        $queryLog[] = sprintf($this->mSqlQueries['do_update_transaksi_jurnal'], $unitId, $nomorReferensi, $userId, $tanggal, $tanggal, $param['keterangan'], $param['nominal_debet'], $transaksiId);
        $queryLog[] = sprintf($this->mSqlQueries['do_delete_pembukuan_detail'], $pembukuanId);
        $queryLog[] = sprintf($this->mSqlQueries['do_update_pembukuan_referensi'], $transaksiId, $userId, $tanggal, $param['keterangan'], $statusKas, $statusKas, $statusKas, $bentukTransaksi, $bentukTransaksi, $bentukTransaksi, $pembukuanId);

        if (!empty($param['akun_debet'])) {
            // insert pembukuan detail debet
            foreach ($param['akun_debet'] as $debet) {
                $subAkun = preg_replace('/\s[\s]+/', '', $debet['sub_akun']);
                $subAkun = preg_replace('/[\_]+/', '', $subAkun);
                $subAkun = preg_replace('/[\_]+/', '', $subAkun);
                list($subacc_1, $subacc_2, $subacc_3, $subacc_4, $subacc_5, $subacc_6, $subacc_7) = explode('-', $subAkun);

                $result &= $this->Execute($this->mSqlQueries['do_insert_pembukuan_detail'], array(
                    $pembukuanId,
                    $debet['id'],
                    $debet['nominal'],
                    $debet['keterangan'],
                    $debet['referensi'],
                    'D',
                    $subacc_1,
                    $subacc_2,
                    $subacc_3,
                    $subacc_4,
                    $subacc_5,
                    $subacc_6,
                    $subacc_7
                ));

                $queryLog[] = sprintf($this->mSqlQueries['do_insert_pembukuan_detail'], $pembukuanId, $debet['id'], $debet['nominal'], $debet['keterangan'], $debet['referensi'], 'D', $subacc_1, $subacc_2, $subacc_3, $subacc_4, $subacc_5, $subacc_6, $subacc_7);
            }
        }

        if (!empty($param['akun_kredit'])) {
            // insert pembukuan detail kredit
            foreach ($param['akun_kredit'] as $kredit) {
                $subAkun = preg_replace('/\s[\s]+/', '', $kredit['sub_akun']);
                $subAkun = preg_replace('/[\_]+/', '', $subAkun);
                $subAkun = preg_replace('/[\_]+/', '', $subAkun);
                list($subacc_1, $subacc_2, $subacc_3, $subacc_4, $subacc_5, $subacc_6, $subacc_7) = explode('-', $subAkun);

                $result &= $this->Execute($this->mSqlQueries['do_insert_pembukuan_detail'], array(
                    $pembukuanId,
                    $kredit['id'],
                    $kredit['nominal'],
                    $kredit['keterangan'],
                    $kredit['referensi'],
                    'K',
                    $subacc_1,
                    $subacc_2,
                    $subacc_3,
                    $subacc_4,
                    $subacc_5,
                    $subacc_6,
                    $subacc_7
                ));

                //isi tabel finansi_pa_transaksi_bank_detail
                if( !empty($getCheckTransaksiBank)) {
                    $result &= $this->Execute($this->mSqlQueries['do_update_transaksi_bank_detil'], array(
                        $transaksiId,
                        $kredit['nama'],
                        $param['tanggal'],
                        $kredit['nominal'],
                        $userId
                    ));
                } else { 
                    // input baru
                    $result &= $this->Execute($this->mSqlQueries['do_insert_transaksi_bank_detil'], array(
                        $transaksiBankId,
                        $kredit['nama'],
                        $param['tanggal'],
                        $kredit['nominal'],
                        $userId
                    ));
                }

                $queryLog[] = sprintf($this->mSqlQueries['do_insert_pembukuan_detail'], $pembukuanId, $kredit['id'], $kredit['nominal'], $kredit['keterangan'], $kredit['referensi'], 'K', $subacc_1, $subacc_2, $subacc_3, $subacc_4, $subacc_5, $subacc_6, $subacc_7);
                $queryLog[] = sprintf($this->mSqlQueries['do_update_transaksi_bank_detil'], $transaksiId, $kredit['nama'], $param['tanggal'], $kredit['nominal'], $userId);
            }
        }

 

        // log query

        if ($result) {
            $result &= $this->Execute($this->mSqlQueries['do_add_log'], array(
                $userId,
                $ipAddress,
                'Update Jurnal dan atau transaksi penerimaan bank'
            ));
            $loggerId = $this->LastInsertId();
            if (is_array($queryLog)) {
                foreach ($queryLog as $query) {
                    $result &= $this->Execute($this->mSqlQueries['do_add_log_detil'], array(
                        $loggerId,
                        addslashes($query)
                    ));
                }
            }
        }

        return $this->EndTrans($result);
    }

    public function doDeleteDataJurnal($id, $pembukuanId) {
        $userId = $this->getUserId();
        $ipAddress = (string) $this->GetRealIP();
        $queryLog = array();
        $result = true;
        $this->StartTrans();
        // data transaksi pembayaran mahasiswa
        //get id tagihan detail pembayaran mahasiswa

        $getIds = $this->Open($this->mSqlQueries['get_id_transaksi_pembayaran'], array(
            $id
        ));

        $getIdsDepositMasuk = $this->Open($this->mSqlQueries['get_id_transaksi_pembayaran_deposit_masuk'], array(
            $id
        ));
        /*
          $statusFlagPembayaran = false;
          if ($getIds) {
          //get id detail tagihan dan tipe pembayaran
          $params['detilId'] = $getIds[0]['id_detil'];
          $params['type'] = $getIds[0]['tipe_pembayaran'];

          //balik flagging is transaksi
          $returnValue = $this->mPembayaranMhs->doUpdateStatusPembayaran($params, 0);
          if ($returnValue['status'] == '201') {
          // hapus data di transaksi pembayaran
          $result &= $this->Execute($this->mSqlQueries['delete_transaksi_pembayaran'], array(
          $id
          ));
          //jika gagal maka balik flagging lagi
          if ($result == false) {
          $returnValue = $this->mPembayaranMhs->doUpdateStatusPembayaran($params, 1);
          $statusFlagPembayaran = false;
          }

          $statusFlagPembayaran = true;
          }
          }
         */
        //end

        $result &= $this->Execute($this->mSqlQueries['do_delete_pembukuan_detail'], array(
            $pembukuanId
        ));
        $result &= $this->Execute($this->mSqlQueries['do_delete_pembukuan_referensi'], array(
            $pembukuanId
        ));
        // trans pembayaran
        if(!empty($getIds) || !empty($getIdsDepositMasuk)){
            $result &= $this->_deleteTransaksiPembayaran($id);
        }
        // end  
        //transaksi penerimaan bank
        //hapus data finansi_pa_transaksi_bank_detil
        $result &= $this->Execute($this->mSqlQueries['do_delete_transaksi_bank_detail_transaksi'], array(
            $id
        ));

        //hapus data finansi_pa_transaksi_bank
        $result &= $this->Execute($this->mSqlQueries['do_delete_transaksi_bank'], array(
            $id
        ));

        //hapus data transaksi_detail_penerimaan_bank
        $result &= $this->Execute($this->mSqlQueries['do_delete_transaksi_det_penerimaan_bank'], array(
            $id
        ));

        //end
        $result &= $this->Execute($this->mSqlQueries['do_delete_transaksi'], array(
            $id
        ));

        $queryLog[] = sprintf($this->mSqlQueries['do_delete_pembukuan_detail'], $pembukuanId);
        $queryLog[] = sprintf($this->mSqlQueries['do_delete_pembukuan_referensi'], $pembukuanId);
        $queryLog[] = sprintf($this->mSqlQueries['do_delete_transaksi'], $id);

        // log query
        $result &= $this->Execute($this->mSqlQueries['do_add_log'], array(
            $userId,
            $ipAddress,
            'Delete Jurnal dan atau Transaksi Penerimaan bank'
        ));
        $loggerId = $this->LastInsertId();
        if (is_array($queryLog)) {
            foreach ($queryLog as $query) {
                $result &= $this->Execute($this->mSqlQueries['do_add_log_detil'], array(
                    $loggerId,
                    addslashes($query)
                ));
            }
        }
        /*
          if ($getIds && $statusFlagPembayaran == false) {
          $result = false;
          }
         */
        return $this->EndTrans($result);
    }

    public function GetTrBankId($transaksiId) {
        $result = $this->Open($this->mSqlQueries['get_tr_bank_id'], array(
            $transaksiId
        ));

        return $result[0]['bank_id'];
    }

    //===============//
    function date2string($date) {
        $bln = array(
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        );
        $arrtgl = explode('-', $date);

        return $arrtgl[2] . ' ' . $bln[(int) $arrtgl[1]] . ' ' . $arrtgl[0];
    }

    //LOGGER LOGGER LOGGER
    function DoAddLog($keterangan, $query) {
        $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
        $ip = $this->GetRealIP();
        $result = $this->Execute($this->mSqlQueries['do_add_log'], array(
            $userId,
            $ip,
            $keterangan
        ));
        $id_logger = $this->LastInsertId();

        if (is_array($query)) {

            foreach ($query as $val) {
                $this->DoAddLogDetil($id_logger, $val);
            }
        } else
            $this->DoAddLogDetil($id_logger, $query);

        return $result;
    }

    function DoAddLogDetil($id, $query) {
        $result = $this->Execute($this->mSqlQueries['do_add_log_detil'], array(
            $id,
            addslashes($query)
        ));

        return $result;
    }

    function GetUnitKode($userId) {
        $result = $this->Open($this->mSqlQueries['get_unit_kode'], array(
            $userId
        ));

        return $result[0]['unitkerjaKode'];
    }

    function GetDataHistoryJurnalByPrId($prId) {
        $sql = $this->mSqlQueries['get_data_history_jurnal_by_pr_id'];

        # generate dynamic subaccount view
        if ($this->subAccJml > 0) {
            $defaultSubAcc = explode('-', str_replace('9', '0', GTFWConfiguration::GetValue('application', 'subAccFormat')));
            for ($i = 0; $i <= ($this->subAccJml - 1); $i++) {
                $arrView[$i] = 'IFNULL(pdSubacc' . $this->subAccName[$i] . 'Kode,"' . $defaultSubAcc[$i] . '")';
            }
            $addSqlView = ',CONCAT(' . implode(",'-',", $arrView) . ') AS subakun';
            $sql = str_replace('[SUBACC_VIEW]', $addSqlView, $sql);
        } else {
            $sql = str_replace('[SUBACC_VIEW]', '', $sql);
        }

        $data = $this->Open($sql, array($prId));
        return $data;
    }

    /**
     * untuk simpan transaki pembayaran
     */
    private function _saveTransaksiPembayaran($transaksi_bank_id, $param) {
        //insert ke table finansi_pa_transaksi_pembayaran dan finansi_pa_transaksi_pembayaran_deposit_masuk
        $result = true;
        $idDetailTagihanAll = array();
        if (!empty($param['pemb_prodi_id']) && !empty($param['pemb_tipe_pembayaran'])
                 && ($param['pemb_tipe_pembayaran'] == 'pengakuan_depmasuk') ) {
            $result &= $this->Execute($this->mSqlQueries['do_insert_trans_pembayaran_dep_masuk'], array(
                $transaksi_bank_id,
                $param['pemb_prodi_id'],
                $param['pemb_prodi_nama'],
                $param['pemb_nominal'],
                'pengakuan',
                $param['pemb_keterangan'],
                $param['pemb_penanggung_jawab'],
                $param['pemb_id_detail']
            ));
        }

        if (!empty($param['jb'])) {
            foreach ($param['jb'] as $jbp) {
                $result &= $this->Execute($this->mSqlQueries['do_insert_trans_pembayaran'], array(
                    $transaksi_bank_id,
                    $jbp['prodi_id'],
                    $jbp['prodi_nama'],
                    $jbp['jenis_biaya_id'],
                    $jbp['jenis_biaya_nama'],
                    $jbp['nominal'],
                    $jbp['potongan'],
                    $jbp['deposit'],
                    $jbp['keterangan'],
                    $jbp['id_detail'],
                    $param['pemb_tipe_pembayaran']
                ));
                $idDetailTagihanAll[] = $jbp['id_detail'];
            }
        }
        
        //integrasi ke gtPM
        if(!empty($param['pemb_id_detail']) && $param['pemb_tipe_pembayaran'] == 'pengakuan_depmasuk') {
            $idDetail = $param['pemb_id_detail'];
        } else {
            $idDetail = implode(',', $idDetailTagihanAll);
        }
        if($result) {
            $result &= $this->_doSendDataGtPM($idDetail,$param['pemb_tipe_pembayaran'],1);
        }
        return $result;
    }

    /**
     * untnuk mendapatkan tipe tranasaksi penerimaan (pembayaran) bank
     * @param type $transId
     * @return string
     */
    private function _getTipeTransaksiPemb($transId) {
        $return = $this->Open($this->mSqlQueries['get_tipe_transaksi_pemb'], array($transId));
        if(!empty($return)) {
            $tipe = $return[0]['pemb_tipe_transaksi'];
        } else {
            $tipe = null;
        }
        return $tipe;
    }
    
    private function _deleteTransaksiPembayaran($transId) {
        $result = true;  
        
        //get tipe transaksi pemb
        $tipeBayar = $this->_getTipeTransaksiPemb($transId);
        switch ($tipeBayar) {
            case 'pengakuan':
            case 'pengakuan_depmasuk':
            case 'piutang':        
                $idDetail = $this->_getIdDetailTagihan($transId,$tipeBayar);

                $result &= $this->Execute($this->mSqlQueries['do_delete_trans_pembayaran_dep_masuk'], array(
                    $transId
                ));
                $result &= $this->Execute($this->mSqlQueries['do_delete_trans_pembayaran'], array(
                    $transId
                ));
                //integrasi ke gtPM
                //role back ke state awal        
                if($result){
                    $result &= $this->_doSendDataGtPM($idDetail['idDetail'],$tipeBayar,0);
                }
                break;
        }
        //end        
        
        return $result;
    }

    private function _updateTransaksiPembayaran($transId, $param) {
        //clean data sbelum nya
        $result = true;
        $getTbankId = $this->GetTrBankId($transId);        
        $result &= $this->_deleteTransaksiPembayaran($transId);
        //insert data baru
        $result &= $this->_saveTransaksiPembayaran($getTbankId, $param);
        return $result;
    }

    //untuk mengirim data ke gtPembayaran
    private function _getIdDetailTagihan($transId,$tipePembayaran) {
        $idDetailTagihanAll = array();
        if($tipePembayaran == 'pengakuan_depmasuk') {
            $idDetailTagihans = $this->Open($this->mSqlQueries['get_id_detail_tagihan_deposit_masuk'], array($transId));
            if (!empty($idDetailTagihans)) {
                foreach ($idDetailTagihans as $valueId) {
                    $idDetailTagihanAll[] = $valueId['id_detail_tagihan'];
                }
            }
        } else {
            $idDetailTagihans = $this->Open($this->mSqlQueries['get_id_detail_tagihan'], array($transId));
            if (!empty($idDetailTagihans)) {
                foreach ($idDetailTagihans as $valueId) {
                    $idDetailTagihanAll[] = $valueId['id_detail_tagihan'];
                }
            }
        }
        return array('idDetail' => implode(',', $idDetailTagihanAll));
    }

    //integrasi
    private function _doSendDataGtPM($idDetail,$tipeBayar,$status = 1) {        
        $statusIntegrasi = $this->mIsIntegrasiGtPm;
        
        if($statusIntegrasi == true) {
            $params['detilId']  = $idDetail;
            $params['type']     = $tipeBayar;
            $return = $this->mPembayaranMhs->doUpdateStatusPembayaran($params, $status);
            if ($return['status'] == '201') {
                $result = true;
            } else {
                $result = false;
            }
        } else {
            $result = true;
        }
        
        return $result;
    }

    /**
     * end
     */
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
            if (!empty($requestData)) {
                foreach ($requestData as $key => $value) {
                    $query[$key] = Dispatcher::Instance()->Encrypt($value);
                }
                $queryString = urldecode(http_build_query($query));
            }
        }
        return $queryString;
    }

    public function getModule($pathInfo = null) {
        $module = NULL;
        if (is_null($pathInfo)) {
            $parseUrl = parse_url($_SERVER['QUERY_STRING']);
            $explodedUrl = explode('&', $parseUrl['path']);
        } else {
            $parseUrl = parse_url($pathInfo);
            $explodedUrl = explode('&', $parseUrl['query']);
        }

        foreach ($explodedUrl as $path) {
            if (preg_match('/^mod=[a-zA-Z0-9_-]+/', $path, $matches)) {
                list($key, $value) = explode('=', $matches[0]);
                $module = $value;
            }
        }

        return $module;
    }

    public function getSubModule($pathInfo = null) {
        $subModule = NULL;
        if (is_null($pathInfo)) {
            $parseUrl = parse_url($_SERVER['QUERY_STRING']);
            $explodedUrl = explode('&', $parseUrl['path']);
        } else {
            $parseUrl = parse_url($pathInfo);
            $explodedUrl = explode('&', $parseUrl['query']);
        }

        foreach ($explodedUrl as $path) {
            if (preg_match('/^sub=[a-zA-Z0-9_-]+/', $path, $matches)) {
                list($key, $value) = explode('=', $matches[0]);
                $subModule = $value;
            }
        }

        return $subModule;
    }

    public function getAction($pathInfo = null) {
        $action = NULL;
        if (is_null($pathInfo)) {
            $parseUrl = parse_url($_SERVER['QUERY_STRING']);
            $explodedUrl = explode('&', $parseUrl['path']);
        } else {
            $parseUrl = parse_url($pathInfo);
            $explodedUrl = explode('&', $parseUrl['query']);
        }

        foreach ($explodedUrl as $path) {
            if (preg_match('/^act=[a-zA-Z0-9_-]+/', $path, $matches)) {
                list($key, $value) = explode('=', $matches[0]);
                $action = $value;
            }
        }

        return $action;
    }

    public function getType($pathInfo = null) {
        $type = NULL;
        if (is_null($pathInfo)) {
            $parseUrl = parse_url($_SERVER['QUERY_STRING']);
            $explodedUrl = explode('&', $parseUrl['path']);
        } else {
            $parseUrl = parse_url($pathInfo);
            $explodedUrl = explode('&', $parseUrl['query']);
        }

        foreach ($explodedUrl as $path) {
            if (preg_match('/^typ=[a-zA-Z0-9_-]+/', $path, $matches)) {
                list($key, $value) = explode('=', $matches[0]);
                $type = $value;
            }
        }

        return $type;
    }

    /**
     * @required indonesianMonths
     * @param String $date date format YYYY-mm-dd H:i:s, YYYY-mm-dd
     * @param String $format long, short
     * @return String  Indonesian Date
     */
    function _dateToIndo($date) {
        $indonesian_months = array(
            'N/A',
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'Nopember',
            'Desember'
        );

        if (preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/", $date, $patch)) {
            $year = (int) $patch[1];
            $month = (int) $patch[2];
            $month = $indonesian_months[(int) $patch[2]];
            $day = (int) $patch[3];
            $hour = (int) $patch[4];
            $min = (int) $patch[5];
            $sec = (int) $patch[6];

            $return = $day . ' ' . $month . ' ' . $year;
        } elseif (preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $date, $patch)) {
            $year = (int) $patch[1];
            $month = (int) $patch[2];
            $month = $indonesian_months[$month];
            $day = (int) $patch[3];

            $return = $day . ' ' . $month . ' ' . $year;
        } else {
            $return = (int) $date;
        }
        return $return;
    }

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

    private function __oldIntegrate() {

        if (!empty($param['pemb_id_detil']) && !empty($param['pemb_tipe_pembayaran'])) {

            $params_new['detilId'] = $param['pemb_id_detil'];
            $params_new['type'] = $param['pemb_tipe_pembayaran'];

            /*             * ****************************************************************
             * Jika tipe beda dan id detail beda dengan old  - proses
             * Jika tipe beda dan id detail sama dengan old  - proses
             * Jika tipe sama dan id detail beda dengan old  - proses
             * Jika tipe sama dan id detail sama dengan old  tak ada proses
             * ***************************************************************** */
            if (($param['pemb_tipe_pembayaran'] !== $param['pemb_tipe_pembayaran_old']) ||
                    ($param['pemb_id_detil'] !== $param['pemb_id_detil_old'])) {

                // balik flaging is transaksi di gtPM
                $params_old['detilId'] = $param['pemb_id_detil_old'];
                $params_old['type'] = $param['pemb_tipe_pembayaran_old'];
                if (!empty($params_old['detilId']) && !empty($params_old['type'])) {
                    $return_old = $this->mPembayaranMhs->doUpdateStatusPembayaran($params_old, 0);
                } else {
                    $return_old['status'] = '201';
                }
                //print_r($return_old);
                if ($return_old['status'] == '201') {
                    //flaging data pembayaran gt PM yang baru
                    $return_new = $this->mPembayaranMhs->doUpdateStatusPembayaran($params_new, 1);
                    if ($return_new['status'] == '201') {

                        if (!empty($params_old['detilId']) && !empty($params_old['type'])) {
                            //update data transaksi pembayaran dengan yang baru
                            $result &= $this->Execute($this->mSqlQueries['do_update_transaksi_pembayaran'], array(
                                $param['pemb_prodi_id'],
                                $param['pemb_prodi_nama'],
                                $param['pemb_jenis_biaya'],
                                $param['pemb_nominal'],
                                $param['pemb_potongan'],
                                $param['pemb_deposit'],
                                $param['pemb_tipe_pembayaran'],
                                $param['pemb_id_detil'],
                                $transaksiId
                            ));
                            $queryLog[] = sprintf($this->mSqlQueries['do_update_transaksi_pembayaran'], $param['pemb_prodi_id'], $param['pemb_prodi_nama'], $param['pemb_jenis_biaya'], $param['pemb_nominal'], $param['pemb_potongan'], $param['pemb_deposit'], $param['pemb_tipe_pembayaran'], $param['pemb_id_detil'], $transaksiId
                            );
                        } else {
                            $transaksi_bank_id = $this->GetTrBankId($transaksiId);
                            $result &= $this->Execute($this->mSqlQueries['do_insert_transaksi_pembayaran'], array(
                                $transaksi_bank_id,
                                $param['pemb_prodi_id'],
                                $param['pemb_prodi_nama'],
                                $param['pemb_jenis_biaya'],
                                $param['pemb_nominal'],
                                $param['pemb_potongan'],
                                $param['pemb_deposit'],
                                $param['pemb_tipe_pembayaran'],
                                $param['pemb_id_detil']
                            ));
                            $queryLog[] = sprintf($this->mSqlQueries['do_insert_transaksi_pembayaran'], $transaksi_bank_id, $param['pemb_prodi_id'], $param['pemb_prodi_nama'], $param['pemb_jenis_biaya'], $param['pemb_nominal'], $param['pemb_potongan'], $param['pemb_deposit'], $param['pemb_tipe_pembayaran'], $param['pemb_id_detil']
                            );
                        }

                        if ($result == false) {
                            $this->mPembayaranMhs->doUpdateStatusPembayaran($params_new, 0);
                            if (!empty($params_old['detilId']) && !empty($params_old['type'])) {
                                $this->mPembayaranMhs->doUpdateStatusPembayaran($params_old, 1);
                            }
                        }
                    } else {
                        $this->mPembayaranMhs->doUpdateStatusPembayaran($params_old, 1);
                        $result = false;
                    }
                } else {
                    $result = false;
                }
            }
        }
    }

}

?>