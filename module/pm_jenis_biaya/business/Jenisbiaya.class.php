<?php

/**
 * @package pm_jenis_biaya
 * modul ini digunakan untuk men-setting status jenis biaya (acrual / cash bases)
 * modul ini melakukan koneksi langsung dengan database gtfinansi pembayaran
 * 
 * @author gtPembayaran
 * 
 * @analized by dyah fajar <dyah@gamatechno.com>
 * @modified by noor hadi <noor.hadi@gamatechno.com>
 * 
 * 
 * @copyright (c) 2017, Gamatechno Indonesia
 * 
 */

/**
 * keterangan untuk status acrual/cash base 
 * acrual yang value 1, cash base value 0
 * 
 */
class Jenisbiaya extends Database {

    protected $mSqlFile = 'module/pm_jenis_biaya/business/jenisbiaya.sql.php';

    /**
     * untuk menentukan koneksi langsung ke database
     * gtPembayaran
     * 1 : untuk reguler
     * 2 : untuk pasca
     */
    private $_mProdi = array(
        1 => array('id' => '1', 'name' => 'Reguler'),
        2 => array('id' => '2', 'name' => 'Pasca Sarjana')
    );
    
    private $_mTipePencatatan = array(
        array('id' => '1', 'name' => 'Accrual'),
        array('id' => '0', 'name' => 'Cash'),
        array('id' => '2', 'name' => 'None')
    );

    public function __construct($connectionNumber = 1) {
        /**
         * koneksi langsung ke gtpembayaran
         * indek koneksi = 1
         */
        parent::__construct($connectionNumber);
    }

    public function GetProgramStudi($idProgram) {
        if(array_key_exists($idProgram, $this->_mProdi)){
            return $this->_mProdi[$idProgram];
        } else {
            return null;
        }
        
    }

    public function GetTipePencatatan() {
        return $this->_mTipePencatatan;
    }
    
    public function GetDataJenisbiaya($offset, $limit,$jenisbiaya, $jeniskeljns,$jenisBiayaAccrual) {
        if((int) $jenisBiayaAccrual > 1 ) {
            $query  = $this->mSqlQueries['get_data_jenisbiaya'];
            $query .= $this->mSqlQueries['get_data_jenisbiaya_jb_accrual_isnull'];
            $query .= $this->mSqlQueries['get_data_jenisbiaya_order_by'];
        } elseif ($jenisBiayaAccrual == 'all') {
            $query  = $this->mSqlQueries['get_data_jenisbiaya'];            
            $query .= $this->mSqlQueries['get_data_jenisbiaya_order_by'];
        }else {
            $query  = $this->mSqlQueries['get_data_jenisbiaya'];
            $query .= sprintf($this->mSqlQueries['get_data_jenisbiaya_jb_accrual'],$jenisBiayaAccrual);
            $query .= $this->mSqlQueries['get_data_jenisbiaya_order_by'];
        }
        
        $result = $this->Open($query, array(
            '%' . $jenisbiaya . '%',
            $jeniskeljns, $jeniskeljns,
            $offset,
            $limit
        ));

        return $result;
        //echo $this->GetLastError();
    }

    public function GetNamaKelompok() { //mengambil nama-nama kelompok
        // 
        $result = $this->Open($this->mSqlQueries['get_combo_kelompok'], array());

        //	echo $this->GetLastError();

        return $result;
    }

    public function GetNamaKelompokPencarian() { //mengambil nama-nama kelompok
        // 
        $result = $this->Open($this->mSqlQueries['get_combo_kelompok_pencarian'], array());

        //	echo $this->GetLastError();

        return $result;
    }

    //===DO==
    public function GetSearchCount() {
        $result = $this->Open($this->mSqlQueries['get_search_count'], array());
        return $result[0]['total'];
    }
    
    public function DoUpdateJenisbiaya($jenisBiaya){
        $this->StartTrans();
        if(!empty($jenisBiaya)){
            $result = true;
            foreach($jenisBiaya as $itemJb){
                //var_dump(isset($itemJb['tipe_catat']));
                if(isset($itemJb['tipe_catat'])) {
                    $result &= $this->Execute($this->mSqlQueries['do_update_jenisbiaya'], array(
                        $itemJb['tipe_catat'],
                        $itemJb['id']
                ));
                }
                
            }
        }
        
        return $this->EndTrans($result);
    }
    
    public function GetJenisBiayaBelumDiSetTipePencatatan() {
        $result = $this->Open($this->mSqlQueries['get_data_jenisbiaya_belum_diset_tipe_pencatatan'], array());
        if(!empty($result)){
            return $result[0]['total_row'];
        } else {
            return 0;
        }        
    }
    

}

?>