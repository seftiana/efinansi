<?php

/**
 * Class CopyProgramKegiatan
 * @package copy_program_kegiatan
 * @todo Untuk menjalankan perintah-perintah query
 * @subpackage business
 * @since 31 Mei 2012
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @copyright 2012 Gamatechno Indonesia
 * 
 */
 
class CopyProgramKegiatan extends Database
{
    protected $mSqlFile= 'module/copy_program_kegiatan/business/copyprogramkegiatan.sql.php';
    public function __construct($connectionNumber = 0)
    {
        parent::__construct($connectionNumber);    
    }
    
    public function GetTahunAnggaranAktif()
    {
        $result = $this->Open($this->mSqlQueries['get_tahun_anggaran_aktif'],array());
        return $result['0']['thanggarId'];
	}
    
    public function GetTahunAnggaranById($id)
    {
        $result = $this->Open($this->mSqlQueries['get_tahun_anggaran_by_id'],array($id));
		return $result['0'];
	}
    
    public function GetDataTahunAnggaran()
    {
        return $this->Open($this->mSqlQueries['get_tahun_anggaran'], array());
    }
    
    public function GetProgramDetail($programId)
    {
        $result = $this->Open($this->mSqlQueries['get_program_detail'],array($programId));
        return $result[0];
    }
    public function GetProgramKegiatanById($programId,$startRec,$itemViewed)
    {
        //$this->SetDebugOn();
        $result = $this->Open($this->mSqlQueries['get_program_kegiatan_by_id'],
                                            array(
                                                    $programId,
                                                    $startRec,
                                                    $itemViewed
                                                ));
        return $result;
    }

    public function GetCountProgramKegiatanById($programId)
    {
        $result = $this->Open($this->mSqlQueries['get_count_program_kegiatan_by_id'],array($programId));
        return $result[0]['total'];
    }
        
    public function GetProgramKegiatan($th_anggar_sumber,$th_anggar_tujutan,$startRec,$itemViewed)
    {
        $result = $this->Open($this->mSqlQueries['get_program_kegiatan'],
                                            array(
                                                    $th_anggar_sumber,
                                                    $th_anggar_tujutan,
                                                    $startRec,
                                                    $itemViewed
                                                    ));
        return $result;
    }
    
    public function GetCountProgramKegiatan($th_anggar_sumber,$th_anggar_tujutan)
    {
        $result = $this->Open($this->mSqlQueries['get_count_program_kegiatan'],
                                            array(
                                                    $th_anggar_sumber,
                                                    $th_anggar_tujutan
                                                    ));
        return $result[0]['total'];
    }
    
    public function GetKodeSistem($id, $parent)
    {
      $result     = $this->Open($this->mSqlQueries['get_kode_sistem'], 
                                                    array($id, $parent, $parent)
      );
      
      return $result[0]['kode_sistem'];
    }
        
    public function CopyProgramKegiatan($arrData)
    {
        
        $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
        //$this->SetDebugOn();
        $this->StartTrans();
        if(is_array($arrData['kode']) && (!empty($arrData['kode'])) &&
            (!empty($arrData['th_anggaran_sumber'])) && 
                    ($arrData['th_anggaran_tujuan'])){
            $thAnggarSumberId = $arrData['th_anggaran_sumber'];
            $thAnggarTujuanId =  $arrData['th_anggaran_tujuan'];
             
            /**
             * proses simpan program
             */
            $kode = implode("','",$arrData['kode']);
            $query = sprintf($this->mSqlQueries['copy_program'],$thAnggarTujuanId,"'".$kode."'");
            $result = $this->Execute($query,array());
        
            /**
             * proses simpan kegiatan
             */
            if($result){
                $query = sprintf($this->mSqlQueries['copy_kegiatan'],
                                            $thAnggarSumberId,"'".$kode."'",$thAnggarTujuanId);
                $result = $this->Execute($query,array());
                /**
                 * proses simpan sub_kegiatan
                 */
                if($result){
                    $query = sprintf($this->mSqlQueries['copy_sub_kegiatan'],
                                            $thAnggarSumberId,"'".$kode."'",$thAnggarTujuanId);
                    $result_sub = $this->Execute($query,array());
                    /**
                     * proses simpan komponen
                     */
                    if($result_sub){
                        $query = sprintf($this->mSqlQueries['copy_komponen'],
                                            $thAnggarSumberId,"'".$kode."'",$thAnggarTujuanId);
                        $result_kom = $this->Execute($query,array());            
                    }
                
                    /**
                     * proses simpan sub_kegiatan_unit
                     */
                    if($result_sub){
                        $query = sprintf($this->mSqlQueries['copy_kegiatan_unit'],
                                            $thAnggarSumberId,"'".$kode."'",$thAnggarTujuanId);
                        $result_kunit = $this->Execute($query,array());
                    }
                
                    /**
                     * proses simpan sub_kegiatan_indikator_kegiatan
                     */
                    if($result_sub){
                        $query = sprintf($this->mSqlQueries['copy_sub_kegiatan_indikator_kegiatan'],
                                            $userId,$thAnggarSumberId,"'".$kode."'",$thAnggarTujuanId);
                        $result_kik = $this->Execute($query,array());                    
                    } 
                }
            }
        
            $result = ($result and $result_sub and $result_kom and $result_kunit and $result_kik);
            /**
             * simpan ke tabel master finansi_pa_mst_program_kegiatan
             */
             /**
              * simpan program
              */
            //  $this->SetDebugOn();
            if($result){
                    $query = sprintf($this->mSqlQueries['get_data_program_to_mst'],"'".$kode."'");
                    $result_prg = $this->Open($query,array());
                    if(!empty($result_prg)){
                        foreach($result_prg as $value){
                            $result_cp_prg = $this->Execute(
                                                        $this->mSqlQueries['copy_program_master'],
                                                            array(
                                                                $value['level_id'],
                                                                $value['parent_id'],
                                                                $value['kode'],
                                                                $value['nama'],
                                                                $value['tanggal'],
                                                                $userId,
                                                            ));
                        }
                    } else{
                        $result_cp_prg = true;
                    }
                    //$this->SetDebugOn();
                    /**/
                    /**
                     * simpan kegiatan
                     */
                    if($result_cp_prg){
                        $query = sprintf($this->mSqlQueries['get_data_kegiatan_to_mst'],"'".$kode."'");
                        $result_prg_kg = $this->Open($query,array());
                        if(!empty($result_prg_kg)){
                            foreach($result_prg_kg as $value){
                                $result_cp_prg_kg = $this->Execute(
                                                        $this->mSqlQueries['copy_kegiatan_master'],
                                                            array(
                                                                $value['level_id'],
                                                                $value['parent_id'],
                                                                '1',//level parent
                                                                $value['parent_id'],
                                                                '1',//level parent
                                                                $value['parent_id'],
                                                                '2',//level child
                                                                $value['parent_id'],
                                                                $value['kode'],
                                                                $value['nama'],
                                                                $value['tanggal'],
                                                                $userId,
                                                            ));
                            }
                       } else {
                            $result_cp_prg_kg = true;
                       } 
                    }
                    /**/
                    /***/
                    /**
                     * simpan sub kegiatan
                     */
                    if($result_cp_prg_kg){ 
                        $query = sprintf($this->mSqlQueries['get_data_sub_kegiatan_to_mst'],"'".$kode."'");
                        $result_prg_kg_sub = $this->Open($query,array());
                        if(!empty($result_prg_kg_sub)){
                            foreach($result_prg_kg_sub as $value){
                                $result_cp_prg_kg_sub = $this->Execute(
                                                        $this->mSqlQueries['copy_kegiatan_master'],
                                                            array(
                                                                $value['level_id'],
                                                                $value['parent_id'],
                                                                '2',//level parent
                                                                $value['parent_id'],
                                                                '2',//level parent
                                                                $value['parent_id'],
                                                                '3',//level child
                                                                $value['parent_id'],
                                                                $value['kode'],
                                                                $value['nama'],
                                                                $value['tanggal'],
                                                                $userId,
                                                            ));
                            } 
                        } else {
                            $result_cp_prg_kg_sub = true;
                        }
                    }
                    
                    /**
                     * simpan komponen
                     */
                    if($result_cp_prg_kg_sub){
                        $query = sprintf($this->mSqlQueries['get_data_komponen_to_mst'],"'".$kode."'");
                        $result_prg_komp = $this->Open($query,array());
                        if(!empty($result_prg_komp)){
                            foreach($result_prg_komp as $value){
                                $result_cp_prg_komp = $this->Execute(
                                                        $this->mSqlQueries['copy_kegiatan_master'],
                                                            array(
                                                                $value['level_id'],
                                                                $value['parent_id'],
                                                                '3',//level parent
                                                                $value['parent_id'],
                                                                '3',//level parent
                                                                $value['parent_id'],
                                                                '6',//level child
                                                                $value['parent_id'],
                                                                $value['kode'],
                                                                $value['nama'],
                                                                $value['tanggal'],
                                                                $userId,
                                                            ));
                            }
                       } else {
                            $result_cp_prg_komp = true;
                       } 
                    }
                                   
            }
            
            $result = ($result and $result_cp_prg and $result_cp_prg_kg and 
                            $result_cp_prg_kg_sub and $result_cp_prg_komp);
            /**/
            $this->EndTrans($result);
        }
        return $result;
    }
}