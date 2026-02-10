<?php

/**
 * class LapProgramKegiatan
 * @package lap_program_kegiatan
 * @subpackage business
 * @todo untuk menjalankan perintah query
 * @since mei 2012
 * @copyright 2012 Gamatechno Indonesia
 * @author noor hadi <noor.hadi@gamatechno.com>
 */
 
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
	'module/user_unit_kerja/business/UserUnitKerja.class.php';


class LapProgramKegiatan extends Database 
{
    protected $mSqlFile= 'module/lap_program_kegiatan/business/lapprogramkegiatan.sql.php';

    public function __construct($connectionNumber=0) 
    {
        parent::__construct($connectionNumber);
        //$this->SetDebugOn();
    }
   	
    public function GetDataTahunAnggaran(&$idaktif) 
    {
        if(trim($idaktif)=='') {
            $id = $this->Open($this->mSqlQueries['get_ta_aktif'],array());
            if($id) {
		          $idaktif = $id[0]['id'];
            }
        }
      
        $result = $this->Open($this->mSqlQueries['get_data_ta'],array());
	    return $result;
    }
    
    public function GetDataTahunAnggaranNama($id_th_anggar)
    {
        $result = $this->Open($this->mSqlQueries['get_data_ta_nama'],array($id_th_anggar));
	    return $result[0]['nama'];
    }    
    public function GetTotalSubUnitKerja($parentId)
 	{
	 	$result = $this->Open($this->mSqlQueries['get_total_sub_unit_kerja'], array($parentId));
	 	return $result[0]['total'];
 	}
    
    public function GetListProgramKegiatan($id_parent = 0,$ta,$parent_unit_id)
    {
        //$this->SetDebugOn();
        if(empty($id_parent)){
            $id_parent = 0;
        }
        
        $kode_sistem = $this->GetUnitKerjaKodeSistem($parent_unit_id);
        $result = $this->Open($this->mSqlQueries['get_list_lap_program_kegiatan'],
                                                        array(
                                                                $ta,
                                                                $id_parent,
                                                                $kode_sistem.'.%',
                                                                $kode_sistem
                                                                ));
        return $result;
    } 
    public function CountListPrgramKegiatan($id_parent = 0,$ta,$parent_unit_id)
    {
        if(empty($id_parent)){
            $id_parent = 0;
        }
        $kode_sistem = $this->GetUnitKerjaKodeSistem($parent_unit_id);
        $result = $this->Open($this->mSqlQueries['count_lap_program_kegiatan'],
                                                        array(
                                                                $ta,
                                                                $id_parent,
                                                                $kode_sistem.'.%',
                                                                $kode_sistem
                                                                ));
        return $result[0]['total'];
    }

    public function CountNominalSubKegiatan($id_parent = 0,$ta,$parent_unit_id)
    {
        if(empty($id_parent)){
            $id_parent = 0;
        }
        $kode_sistem = $this->GetUnitKerjaKodeSistem($parent_unit_id);
        $result_lt = $this->Open($this->mSqlQueries['count_total_nominal_sub_kegiatan'],
                                                        array(
                                                                $ta,
                                                                $id_parent,
                                                                1,1,
                                                                $kode_sistem.'.%',
                                                                $kode_sistem
                                                                ));
        $result_ltt = $this->Open($this->mSqlQueries['count_total_nominal_sub_kegiatan'],
                                                        array(
                                                                $ta,
                                                                $id_parent,
                                                                1,0,
                                                                $kode_sistem.'.%',
                                                                $kode_sistem
                                                                ));
        $result_tlt = $this->Open($this->mSqlQueries['count_total_nominal_sub_kegiatan'],
                                                        array(
                                                                $ta,
                                                                $id_parent,
                                                                0,1,
                                                                $kode_sistem.'.%',
                                                                $kode_sistem
                                                                ));
        $result_tltt = $this->Open($this->mSqlQueries['count_total_nominal_sub_kegiatan'],
                                                        array(
                                                                $ta,
                                                                $id_parent,
                                                                0,0,
                                                                $kode_sistem.'.%',
                                                                $kode_sistem
                                                                ));
        $result = array(
                          'b_langsung_tetap' => $result_lt[0]['total_biaya'],
                          'b_langsung_tak_tetap' => $result_ltt[0]['total_biaya'],
                          'b_tak_langsung_tetap' => $result_tlt[0]['total_biaya'],
                          'b_tak_langsung_tak_tetap' => $result_tltt[0]['total_biaya']          
                        );                                                                                          
        return $result;
    }
    
    public function CountNominalProgram($id_program = 0,$ta,$parent_unit_id)
    {
       // $this->SetDebugOn();
        if(empty($id_program)){
            $id_program = 0;
        }
        $kode_sistem = $this->GetUnitKerjaKodeSistem($parent_unit_id);
        $result_lt = $this->Open($this->mSqlQueries['count_total_nominal_program'],
                                                        array(
                                                                $ta,
                                                                $id_program,
                                                                1,1,
                                                                $kode_sistem.'.%',
                                                                $kode_sistem
                                                                ));
        $result_ltt = $this->Open($this->mSqlQueries['count_total_nominal_program'],
                                                        array(
                                                                $ta,
                                                                $id_program,
                                                                1,0,
                                                                $kode_sistem.'.%',
                                                                $kode_sistem
                                                                ));
        $result_tlt = $this->Open($this->mSqlQueries['count_total_nominal_program'],
                                                        array(
                                                                $ta,
                                                                $id_program,
                                                                0,1,
                                                                $kode_sistem.'.%',
                                                                $kode_sistem
                                                                ));
        $result_tltt = $this->Open($this->mSqlQueries['count_total_nominal_program'],
                                                        array(
                                                                $ta,
                                                                $id_program,
                                                                0,0,
                                                                $kode_sistem.'.%',
                                                                $kode_sistem
                                                                ));                                                      
        $result = array(
                          'b_langsung_tetap' => $result_lt[0]['total_biaya'],
                          'b_langsung_tak_tetap' => $result_ltt[0]['total_biaya'],
                          'b_tak_langsung_tetap' => $result_tlt[0]['total_biaya'],
                          'b_tak_langsung_tak_tetap' => $result_tltt[0]['total_biaya']          
                        );                          
        return $result;
    }

    public function CountNominalKegiatan($id_kegiatan = 0,$ta,$parent_unit_id)
    {
        if(empty($id_kegiatan)){
            $id_kegiatan = 0;
        }
        $kode_sistem = $this->GetUnitKerjaKodeSistem($parent_unit_id);
        $result_lt = $this->Open($this->mSqlQueries['count_total_nominal_kegiatan'],
                                                        array(
                                                                $ta,
                                                                $id_kegiatan,
                                                                1,1,
                                                                $kode_sistem.'.%',
                                                                $kode_sistem
                                                                ));
        $result_ltt = $this->Open($this->mSqlQueries['count_total_nominal_kegiatan'],
                                                        array(
                                                                $ta,
                                                                $id_kegiatan,
                                                                1,0,
                                                                $kode_sistem.'.%',
                                                                $kode_sistem
                                                                ));
        $result_tlt = $this->Open($this->mSqlQueries['count_total_nominal_kegiatan'],
                                                        array(
                                                                $ta,
                                                                $id_kegiatan,
                                                                0,1,
                                                                $kode_sistem.'.%',
                                                                $kode_sistem
                                                                ));
        $result_tltt = $this->Open($this->mSqlQueries['count_total_nominal_kegiatan'],
                                                        array(
                                                                $ta,
                                                                $id_kegiatan,
                                                                0,0,
                                                                $kode_sistem.'.%',
                                                                $kode_sistem
                                                                ));
        $result = array(
                          'b_langsung_tetap' => $result_lt[0]['total_biaya'],
                          'b_langsung_tak_tetap' => $result_ltt[0]['total_biaya'],
                          'b_tak_langsung_tetap' => $result_tlt[0]['total_biaya'],
                          'b_tak_langsung_tak_tetap' => $result_tltt[0]['total_biaya']          
                        );                          
                                                                
        return $result;
    }             

    
    /**
     * untuk mendapatkan kode sistem unit kerja
     */
    protected function GetUnitKerjaKodeSistem($unit_id)
    {
        $sql = sprintf($this->mSqlQueries['get_unit_kerja_kode_sistem'],$unit_id);
        $result = $this->Open($sql,array());
        return $result[0]['kode_sistem'];
    }
           
}