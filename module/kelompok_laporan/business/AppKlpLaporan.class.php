<?php

class AppKelpLaporan extends Database {

    protected $mSqlFile = 'module/kelompok_laporan/business/appklplaporan.sql.php';

    private $_mDataKelompokLaporan = array();
    
    public function __construct($connectionNumber = 0) {
        parent::__construct($connectionNumber);
    }
    
    
    public function getSumRows($kellapId){
        $sumRows = $this->Open($this->mSqlQueries['get_sum_rows'],array($kellapId));
      
        if(!empty($sumRows)) {
            return $sumRows[0]['srows'];
        } else {
            return 0;
        }
    }
    
    public function getComboRoot(){
        return $this->Open($this->mSqlQueries['get_combo_root'], array());
    }

    public function getRootKodeSistem($parentId){
        $return = $this->Open($this->mSqlQueries['get_root_kode_sistem'], array($parentId));
        if(!empty($return)) {
            return $return[0]['kode_sistem'];
        } else {
            return 0;
        }
    }
    
    public function GetKelompokInfo($id) {
        $result = $this->Open($this->mSqlQueries['get_kelompok_info'], array($id));
        return $result[0];
    }

    public function GetError() {
        $errno = mysql_errno();
        if ($errno == "1451") {
            $return = "Terdapat data lain yang menggunakan data ini.";
        }
        return $return;
    }

    public function GetJenisLaporan() {
        return $this->Open($this->mSqlQueries['get_tipe_laporan'], array());
    }

    public function getChild($id) {
        return $this->Open($this->mSqlQueries['get_child'], array($id));
    }

    public function GetData($offset, $limit, $nama = '') {
        $result = $this->Open($this->mSqlQueries['get_data'], array()); //'%' . $nama . '%', $offset, $limit));
        $data = array();
        if (!(empty($result))) {
            $parentId = null;
            $index = 0;
            for ($i = 0; $i < sizeof($result);) {
                if ($parentId === $result[$i]['kellap_pid']) {
                    $data[$index]['id'] = $result[$i]['id'];
                    $data[$index]['nama'] = $result[$i]['keterangan'];
                    $data[$index]['is_tambah'] = $result[$i]['is_tambah'];
                    $i++;
                    $index++;
                } elseif ($parentId !== $result[$i]['kellap_pid']) {
                    $parentId = $result[$i]['kellap_pid'];
                    $data[$index]['id'] = $result[$i]['id'];
                    $data[$index]['nama'] = '<b>' . $result[$i]['keterangan'] . '</b>';
                    $data[$index]['is_tambah'] = $result[$i]['is_tambah'];
                    $index++;
                }
            }
        }
        return $data;
    }

    public function GetCountData($nama, $jns_lap = '') {
        $result = $this->Open($this->mSqlQueries['get_count'], array('%' . $nama . '%'));
        if (!$result) {
            return 0;
        } else {
            return $result[0]['total'];
        }
    }

    public function GetDataById($id) {
        
        $result = $this->Open($this->mSqlQueries['get_data_by_id'], array($id));
        
        if(!empty($result)) {
            return $result[0];
        }
        
        return NULL;
    }

    public function GetDataParentChild($parentId, $namaKellap, $offset, $limit) {
        
        $result = $this->Open($this->mSqlQueries['get_data_parent_child'], array(
            $parentId, 
            '%'.$namaKellap.'%',
            $offset,
            $limit
        ));
        
        if(!empty($result)) {
            return $result;
        }
        
        return NULL;
    }

    public function GetCountkellapParent($parentId, $namaKellap) {
        $result = $this->Open($this->mSqlQueries['get_count_parent_child'], array(
            $parentId, 
            '%'.$namaKellap.'%',
        ));
        
        if (!$result) {
            return 0;
        } else {
            return $result[0]['total'];
        }
    }

    public function GetDataByArrayId($arrId) {
        $id_klp_lap = implode("', '", $arrId);
        $result = $this->Open($this->mSqlQueries['get_data_by_array_id'], array($id_klp_lap));
        return $result;
    }

    //===DO summary ==
    

    public function DoAddDataSummary($params) {
        $this->StartTrans();
            
        $kodeSistem = $this->_getKodeSistem(
            $params['kellap_parent_kode_sistem'],
            $params['kellap_parent_id']
        );
        
        $dataKlpLaporan = array();
        
        $isTambahValue = strtoupper($params['kellap_is_tambah']);
        $isSummaryValue = strtoupper($params['kellap_is_summary']);
        //jenis perhitungan basic kode 0 atau advance kode 1
        if($params['kellap_operasi_perhitungan'] == '0') {            
            $dataKlpLaporan = array(
                        'id' => $params['kellap_parent_id']
            );          
        } else {
            //untuk parameter summary (keterangan)            
            $subKlpLaporan =  $params['data_klp'];
            if(!empty($subKlpLaporan)) {
                foreach ($subKlpLaporan as $itemKlp) {
                    $dataKlpLaporan[] = array(
                        'id' => $itemKlp['id']
                    );
                }
            }    
        }
                
        
        $keterangan = json_encode(array(
                'operasiPerhitungan' => $params['kellap_operasi_perhitungan'],
                'dataKlpLap' => $dataKlpLaporan
            )
        );
        //end
        
        $levelParent = $params['kellap_parent_level'];
        $result = $this->Execute($this->mSqlQueries['do_add_summary'], array(
            $kodeSistem,
            ((int) $levelParent ) + 1,
            $params['kellap_parent_id'],
            $params['kellap_nama'],
            $params['kellap_kelompok'],
            $params['kellap_tipe'],
            $isTambahValue,
            $isSummaryValue,
            $params['kellap_no_selanjutnya'],
            $keterangan
        ));
        
        if($parentId != 0 && $result) {
            $result &= $this->Execute($this->mSqlQueries['do_update_tipe_parent'], array(
                $params['kellap_parent_tipe'],
                $params['kellap_parent_id']
            ));
        }
        $this->EndTrans($result);
        
        return $result;
    }

    public function DoUpdateDataSummary($params) {
        $this->StartTrans();       
        
        $dataKlpLaporan = array();
        
        $isTambahValue = strtoupper($params['kellap_is_tambah']);
        $isSummaryValue = strtoupper($params['kellap_is_summary']);
        //jenis perhitungan basic kode 0 atau advance kode 1
        if($params['kellap_operasi_perhitungan'] == '0') {            
            $dataKlpLaporan = array(
                        'id' => $params['kellap_parent_id']
            );          
        } else {
            //untuk parameter summary (keterangan)            
            $subKlpLaporan =  $params['data_klp'];
            if(!empty($subKlpLaporan)) {
                foreach ($subKlpLaporan as $itemKlp) {
                    $dataKlpLaporan[] = array(
                        'id' => $itemKlp['id']
                    );
                }
            }    
        }
                
        
        $keterangan = json_encode(array(
                'operasiPerhitungan' => $params['kellap_operasi_perhitungan'],
                'dataKlpLap' => $dataKlpLaporan
            )
        );
        //end
         
        $result = $this->Execute($this->mSqlQueries['do_update_summary'], array(
            $params['kellap_parent_id'],
            $params['kellap_nama'],
            $params['kellap_kelompok'],
            $isTambahValue,
            $isSummaryValue,
            $params['kellap_no_selanjutnya'],
            $keterangan,
            $params['kellap_id']
        ));
        
        $this->EndTrans($result);
        
        return $result;
    }
    
    //===DO==
    public function DoAddData($parentId,$parentKodeSistem,$levelParent,$nama, $kelompok,$tipe,$tipeParent,$isTambah,$isSummary,$noUrut) {
        $this->StartTrans();
        $kodeSistem = $this->_getKodeSistem($parentKodeSistem,$parentId);        

        $isTambahValue = strtoupper($isTambah);
        $isSummaryValue = strtoupper($isSummary);
        
        $result = $this->Execute($this->mSqlQueries['do_add'], array(
            $kodeSistem,
            ((int) $levelParent ) + 1,
            $parentId,
            $nama,
            $kelompok,
            $tipe,
            $isTambahValue,
            $isSummaryValue,
            $noUrut
        ));
        
        if($parentId != 0 && $result) {
            $result &= $this->Execute($this->mSqlQueries['do_update_tipe_parent'], array(
                $tipeParent,
                $parentId
            ));
        }
        $this->EndTrans($result);
        
        return $result;
    }

    public function DoUpdateData($parentId,$nama,$kelompok,$isTambah,$isSummary,$noUrut, $id) {
        $this->StartTrans();
        $isTambahValue = strtoupper($isTambah);
        $isSummaryValue = strtoupper($isSummary);
        
        $result = $this->Execute($this->mSqlQueries['do_update'], array(
            $parentId,
            $nama, 
            $kelompok,
            $isTambahValue,
            $isSummaryValue,
            $noUrut,
            $id
        ));
        
        $this->EndTrans($result);
        
        return $result;
    }


    public function DoDeleteDataById($id) {        
        $this->StartTrans();
        $result = true;
        //cek parent jika parent tidak memiliki child lagi,
        $parentId = $this->_getParentId($id);
        $getTipeParent = $this->_getTipeParent($parentId);
        if($getTipeParent == 'parent') {
            $tChild = $this->_getCountChildInParent($parentId);    
            //jika child di hapus maka parent berubah menjadi child        
            //hitung jumlah child sekarang            
            if(($tChild - 1) <= 0 ) {
                $result &= $this->_doParentToChild($parentId);
            }
            //end
        }
        //cek isi coa per kelompok
        $cekCoa = $this->_getCountCoa($id);
        if($cekCoa > 0) {
            $result &= $this->_doDeleteCoaPerKelompok($id);
            $result &= $this->_doDeleteKlpPerKelompok($id);
        }
        
        if($result) {
            //hapus
            $result &= $this->Execute($this->mSqlQueries['do_delete_by_array_id'], array($id));
        }
        
        $this->EndTrans($result);
        return $result;
    }

    //detil
    public function GetCountDetilKlpLaporan($id, $key) {
        $result = $this->Open($this->mSqlQueries['get_count_detil_klp_laporan'], array($id, '%' . $key . '%'));
        if (!$result) {
            return 0;
        } else {
            return $result[0]['total'];
        }
    }

    public function GetCoaPerKelompok($kellapId) {
        $result = $this->Open($this->mSqlQueries['get_coa_perkelompok'], array($kellapId));
        return $result;
    }
    
    public function GetKlpPerKelompok($kellapId) {
        $result = $this->Open($this->mSqlQueries['get_klp_perkelompok'], array($kellapId));
        return $result;
    }

    // do add detil coa kel lap
    public function DoAddDetilData($kellapId,$coa = array(),$klp = array()) {
        $this->StartTrans();        
        $result = true;
       
        $getCoaExist = $this->_getCoaExistPerKelompok($kellapId, $coa);   
        //var_dump($getCoaExist);
        //hapus coa yang di hapus di form input
        if(!empty($getCoaExist)){
            $result &= $this->_doDeleteDaftarCoaPerKelompok($kellapId, $getCoaExist);
        } else {
            //bersihkan coa dulu kalo yang lalu dihapus di form input
            //cek isi coa nya dulu dab
            $getIsiCoa = $this->_getTotalCoaPerKelompok($kellapId);               
            //nek ono isine hapus dab.
            if($getIsiCoa > 0 ){
                $result &= $this->_doDeleteCoaPerKelompok($kellapId);
            }
        }
        
        if(!empty($coa)){    
            
            foreach($coa as $value) {                                                
                $isSaldoAwal = (isset($value['is_saldo_awal']) ? 'Y' : 'T'); 
                $isPositif = (isset($value['is_positif']) ? $value['is_positif'] : 'T'); 
                
                if(isset($value['is_mutasi_dk'])){
                    if($value['is_mutasi_dk'] == 'Y') {
                        $isMutasiDK = 'Y';
                    } else {
                        $isMutasiDK = 'T';
                    }
                } else {
                    $isMutasiDK = 'T';
                }
                
                if(isset($value['is_mutasi_d'])){
                    if($value['is_mutasi_d'] == 'Y') {
                        $isMutasiD = 'Y';
                    } else {
                        $isMutasiD = 'T';
                    }
                } else {
                    $isMutasiD = 'T';
                }
                
                if(isset($value['is_mutasi_k'])){
                    if($value['is_mutasi_k'] == 'Y') {
                        $isMutasiK = 'Y';
                    } else {
                        $isMutasiK = 'T';
                    }
                } else {
                    $isMutasiK = 'T';
                }
                
                if(!in_array($value['id'], $getCoaExist)) {
                    $result &= $this->Execute( 
                        $this->mSqlQueries['do_add_detil_coa_kel_lap'], 
                        array(
                            $kellapId, 
                            $value['id'],
                            'D',//status d/k di abaikan / perhitungan sesuai dengan status d/k di jurnal coa nya / saldo normal
                            $isPositif,
                            $isSaldoAwal,
                            $isMutasiDK,
                            $isMutasiD,
                            $isMutasiK
                        )
                    );
                } else {
                  
                    //update data
                    $result &= $this->Execute( 
                        $this->mSqlQueries['do_update_detil_coa_kel_lap'], 
                        array(
                            $isPositif,
                            $isSaldoAwal,
                            $isMutasiDK,
                            $isMutasiD,
                            $isMutasiK,
                            $value['id'],
                            $kellapId
                        )
                    );
                }
            }
        }    
        
        //referensi kelompok laporan dari kelompok lain        
        $getKlpExist = $this->_getKlpExistPerKelompok($kellapId, $klp);   
        //hapus ref klp lap yang di hapus di form input
        if(!empty($getKlpExist)){
            $result &= $this->_doDeleteDaftarKlpPerKelompok($kellapId, $getKlpExist);
        } else {
            //bersihkan klp lap dulu kalo yang lalu dihapus di form input
            //cek isi klp lap nya dulu dab
            $getIsiKlp = $this->_getTotalKlpPerKelompok($kellapId);
            //nek ono isine hapus dab.
            if($getIsiKlp > 0 ){
                $result &= $this->_doDeleteKlpPerKelompok($kellapId);
            }
       }
            
        if(!empty($klp)){
            foreach($klp as $value) {                          
                if(!in_array($value['id'], $getKlpExist)) {
                    $result &= $this->Execute( 
                        $this->mSqlQueries['do_add_detil_klp_kel_lap'], 
                        array(
                            $kellapId, 
                            $value['id'],//kellap
                            'D'//status d/k di abaikan / perhitungan sesuai dengan status d/k di jurnal coa nya
                        )
                    );
                }
            }
        }

        $this->EndTrans($result);
        return $result;
    }

    public function DoDeleteDetilDataById($id) {
        $result = $this->Execute($this->mSqlQueries['do_delete_detil_by_id'], array($id));
        return $result;
    }

    public function DoDeleteDetilDataByArrayId($arrId) {
        $id_coa_klp_lap = implode("', '", $arrId);
        $result = $this->Execute($this->mSqlQueries['do_delete_detil_by_array_id'], array($id_coa_klp_lap));
        return $result;
    }


    
    /**
     * since 3 April 2017
     * @author noor hadi<noor.hadi@gamatechno.com>
     */

    public function getNoTerakhir($parentId){
        $noAkhir = $this->_getUrutanKelompokTerakhir($parentId);
        return $noAkhir;
    }
    
    public function getNoSelanjutnya($parentId){
        $noAkhir = $this->_getUrutanKelompokTerakhir($parentId);
        return ($noAkhir + 1);
    }
    /**
     * get no urut terakhir
     * @param type $parentId
     * @return type
     */
    private function _getUrutanKelompokTerakhir($parentId) {
        $result = $this->Open($this->mSqlQueries['get_urutan_kelompok_terakhir'], array($parentId));
        if(!empty($result)) {
            return $result[0]['max_urutan'];
        } else {
            return 0;
        }
    }
    
    /**
     * untuk mengetahui parent id 
     * @param type $childId
     * @return type
     */
    private function _getParentId($childId){
        $result = $this->Open($this->mSqlQueries['get_parent_id'], array($childId));        
        if(!empty($result)) {
            return $result[0]['parent_id'];
        } else {
           return null; 
        }
    }


    private function _getTipeParent($parentId){
        $result = $this->Open($this->mSqlQueries['get_tipe_parent'], array($parentId));        
        if(!empty($result)) {
            return $result[0]['tipe'];
        } else {
           return null; 
        }
    }
    
    /**
     * untuk menghitung total child dalam suatu parent
     * @param type $parentId
     * @return type
     */
    private function _getCountChildInParent($parentId){
        $result = $this->Open($this->mSqlQueries['get_count_child'], array($parentId));
        if(!empty($result)) {
            return $result[0]['total_child'];
        } else {
           return 0; 
        }
    }
    
    /**
     * ubah parent jadi child
     * @param type $parentId
     * @return type
     */
    private function _doParentToChild($parentId){
        
        $result = $this->Execute($this->mSqlQueries['do_update_tipe_parent'], array(
                'child',
                $parentId
            ));
        
        return $result;
    }
    
    /**
     * get kode sistem parent
     * @param type $kodeSistemParent
     * @param type $parentId
     * @return type
     */
    private function _getKodeSistem($kodeSistemParent,$parentId) {
        $result = $this->Open($this->mSqlQueries['get_kode_sistem'], array(
                $kodeSistemParent.'.',
                $parentId
            )
        );
        return $result[0]['rnumber'];
    }
    
    /**
     * get count coa per kelompok
     * @param type $kelompokId
     * @return int
     */
    private function _getCountCoa($kelompokId) {
        $result = $this->Open($this->mSqlQueries['get_count_coa'],array($kelompokId));
        if(!empty($result)) {
            return $result[0]['total_coa'];
        } else {
            return 0;
        }
    }

    /**
     * hapus coa per kelompok id
     * @param type $kelompokId
     * @return type
     */
    private function _doDeleteCoaPerKelompok($kelompokId) {
        $result = $this->Execute($this->mSqlQueries['delete_coa_per_kelompok'], array(
                $kelompokId
        ));        
        return $result;        
    }

    private function _doDeleteKlpPerKelompok($kelompokId) {
        $result = $this->Execute($this->mSqlQueries['delete_klp_per_kelompok'], array(
                $kelompokId
        ));        
        return $result;        
    }
    
    private function _getTotalKlpPerKelompok($kelompokId) {
        $result = $this->Open($this->mSqlQueries['get_total_klp_per_kelompok'], array(
                $kelompokId
        ));
        if(!empty($result)){
            return $result[0]['total_klp'];
        } else {
            return 0;
        }
    }
    
    private function _getTotalCoaPerKelompok($kelompokId) {
        $result = $this->Open($this->mSqlQueries['get_total_coa_per_kelompok'], array(
                $kelompokId
        ));
        if(!empty($result)){
            return $result[0]['total_coa'];
        } else {
            return 0;
        }
    }

    private function _getCoaExistPerKelompok($kelompokId,$arrayCoa = array()) {
          if(!empty($arrayCoa)){
              foreach ($arrayCoa as $v){
                  $coaIds[] = $v['id'];
              }
            $coaId = implode("','", $coaIds);
        } else {
            $coaId = null;
        }
        
        $result = $this->Open($this->mSqlQueries['get_coa_exist_per_kelompok'], array(
                $kelompokId,
                $coaId
        ));        
        
        $colectCoa = array();
        if(!empty($result)) {        
            for($i = 0 ; $i < sizeof($result) ; $i++){                
               array_push($colectCoa, $result[$i]['coa_id']);
            }
        }
        
        return $colectCoa;
    }
    
    private function _getKlpExistPerKelompok($kelompokId,$arrayKlp = array()) {
          if(!empty($arrayKlp)){
              foreach ($arrayKlp as $v){
                  $klpIds[] = $v['id'];
              }
            $klpId = implode("','", $klpIds);
        } else {
            $klpId = null;
        }
        
        $result = $this->Open($this->mSqlQueries['get_klp_exist_per_kelompok'], array(
                $kelompokId,
                $klpId
        ));        
        
        $colectKlp = array();
        if(!empty($result)) {        
            for($i = 0 ; $i < sizeof($result) ; $i++){                
               array_push($colectKlp, $result[$i]['klp_id']);
            }
        }
        
        return $colectKlp;
    }

    private function _doDeleteDaftarKlpPerKelompok($kelompokId,$klpExist = array()) {
        if(!empty($klpExist)){            
            $klpId = implode("','", $klpExist);
        } else {
            $klpId = null;
        }
        $result = $this->Execute($this->mSqlQueries['delete_daftar_klp_per_kelompok'], array(
                $kelompokId,
                $klpId
        ));        
        return $result;        
    }
    
    private function _doDeleteDaftarCoaPerKelompok($kelompokId,$coaExist = array()) {
        if(!empty($coaExist)){            
            $coaId = implode("','", $coaExist);
        } else {
            $coaId = null;
        }
        $result = $this->Execute($this->mSqlQueries['delete_daftar_coa_per_kelompok'], array(
                $kelompokId,
                $coaId
        ));        
        return $result;        
    }
    
    public function PrepareDataKelompokLaporan($rootKodeSistem){
        $this->_getKelompokLaporan($rootKodeSistem);
    }

    public function PrepareDataKelompokLaporanRoot($kodeSistemUpParent){
        $this->_getKelompokLaporanRoot($kodeSistemUpParent);
    }
    /**
     * get data kelompok laporan dan simpan dalam data array
     * Membuat array dari data hasil query
     * pastikan hasil query nya urut
     */
    private function _getKelompokLaporan($kodeSistem) {
        $dataArr = array();
        $data = $this->open($this->mSqlQueries['get_kelompok_laporan'], array(
            $kodeSistem,
            $kodeSistem.'.%'
        ));

        if (!empty($data)) {
            foreach ($data as $value) {
                $dataArr[$value['kellap_id']]['id'] = $value['kellap_id'];
                $dataArr[$value['kellap_id']]['pid'] = $value['kellap_pid'];
                $dataArr[$value['kellap_id']]['nama'] = $value['kellap_nama'];
                $dataArr[$value['kellap_id']]['level'] = $value['kellap_level'];
                $dataArr[$value['kellap_id']]['tipe'] = $value['kellap_tipe'];
                $dataArr[$value['kellap_id']]['is_tambah'] = $value['kellap_is_tambah'];
                $dataArr[$value['kellap_id']]['is_summary'] = $value['kellap_is_summary'];
                $dataArr[$value['kellap_id']]['no_urut'] = $value['kellap_order_by'];
            }
        }
        $this->_mDataKelompokLaporan = $dataArr;
    }

    private function _getKelompokLaporanRoot($kodeSistemUpParent) {
        $dataArr = array();
        if(!empty($kodeSistemUpParent)) {
            $kodeSplit = explode('.',$kodeSistemUpParent);
            for($i = 0;$i < sizeof($kodeSplit);$i++){
                $ksCollection[$i] ='';
                for($x = 0;$x < ($i+1) ; $x++){
                    $split = (!empty($ksCollection[$i]) ? '.' : '');
                    $ksCollection[$i] .= $split. $kodeSplit[$x];
                }
            }
            $filterKodeSistem = implode("','",$ksCollection);
        } else {
            $filterKodeSistem = null;
        }
        
        $data = $this->open($this->mSqlQueries['get_kelompok_laporan_root'], array(
            $filterKodeSistem
        ));

        if (!empty($data)) {
            foreach ($data as $value) {
                $dataArr[$value['kellap_id']]['id'] = $value['kellap_id'];
                $dataArr[$value['kellap_id']]['pid'] = $value['kellap_pid'];
                $dataArr[$value['kellap_id']]['nama'] = $value['kellap_nama'];
                $dataArr[$value['kellap_id']]['level'] = $value['kellap_level'];
                $dataArr[$value['kellap_id']]['tipe'] = $value['kellap_tipe'];                
                $dataArr[$value['kellap_id']]['is_tambah'] = $value['kellap_is_tambah'];
                $dataArr[$value['kellap_id']]['is_summary'] = $value['kellap_is_summary'];
                $dataArr[$value['kellap_id']]['no_urut'] = $value['kellap_order_by'];
            }
        }
        $this->_mDataKelompokLaporan = $dataArr;
    }
    
    /**
     * untuk melakukan pencarian di array multidimensi
     * @param Array $mdArrayData
     * @param String $key
     * @param Mixed $value (string lebih baik)
     * @return Array
     */
    private function _multidimensiSearch($mdArrayData = array(), $key = null, $value = '') {
        foreach ($mdArrayData as $k => $v) {
            if (array_key_exists($key, $v) && $v[$key] == $value) {
                $result[] = $mdArrayData[$k];
            }
        }
        if (!empty($result)) {
            $dataArr = array();
            foreach ($result as $value) {
                $dataArr[$value['id']]['id'] = $value['id'];
                $dataArr[$value['id']]['pid'] = $value['pid'];
                $dataArr[$value['id']]['nama'] = $value['nama'];
                $dataArr[$value['id']]['level'] = $value['level'];
                $dataArr[$value['id']]['tipe'] = $value['tipe'];  
                $dataArr[$value['id']]['is_tambah'] = $value['is_tambah'];
                $dataArr[$value['id']]['is_summary'] = $value['is_summary'];
                $dataArr[$value['id']]['no_urut'] = $value['no_urut'];
            }
            return $dataArr;
        } else {
            return null;
        }
    }

    /**
     * List kelompok laporan
     * untuk mengatur list laporan supaya urut parent dan child nya
     * rekursi
     * @param type $parentId
     * @return type
     */
    public function GetLaporan($parentId = 0,$deep = true) {
        $collecting = array();
        $data = $this->_mDataKelompokLaporan;
        //menemukan pid = $parentId
        $result = $this->_multidimensiSearch($data, 'pid', $parentId);
        if (!empty($result)) {
            foreach ($result as $k => $v) {                
                $collecting[$k]['id'] = $v['id'];
                $collecting[$k]['pid'] = $v['pid'];
                $collecting[$k]['nama'] = $v['nama'];
                $collecting[$k]['level'] = $v['level'];
                $collecting[$k]['tipe'] = $v['tipe'];
                $collecting[$k]['is_tambah'] = $v['is_tambah'];
                $collecting[$k]['is_summary'] = $v['is_summary'];
                $collecting[$k]['no_urut'] = $v['no_urut'];
                //getChild
                $getChild = $this->GetLaporan($v['id']);
                if(!empty($getChild)){
                    $collecting[$k]['is_child'] = '0';
                    if($deep == true) {
                        $collecting += $getChild;
                    }
                } else {
                    $collecting[$k]['is_child'] = '1';
                }
            }
        }
        return $collecting;
    }
    
}

?>