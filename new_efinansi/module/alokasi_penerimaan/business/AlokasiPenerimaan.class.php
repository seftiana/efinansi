<?php

/**
 * class AlokasiPenerimaan
 * @package alokasi_penerimaan
 * @since 27 Maret 2012
 * @copyright 2012 Gamatechno
 */ 
 
class AlokasiPenerimaan extends Database
{
    protected $mSqlFile= 'module/alokasi_penerimaan/business/alokasi_penerimaan.sql.php';
    
    public function __construct($connectionNumber = 0)
    {
        parent::__construct($connectionNumber);
        //$this->SetDebugOn();
    }
    
    public function GetCountData($kode='',$nama='',$unitkerjaId=1)
    {
        $kode = '%'.$kode.'%';
        $nama = '%'.$nama.'%';
        $result = $this->Open($this->mSqlQueries['get_count_data'],
                array(
                        $kode,
                        $nama,
                        $unitkerjaId,'%',
                        $unitkerjaId));
        return $result[0]['total'];
    }
    
    public function GetAlokasiPenerimaan($startRec,$itemViewed,$kode='',$nama='',$unitkerjaId = 1)
    {
        $kode = '%'.$kode.'%';
        $nama = '%'.$nama.'%';
        $result  = $this->Open($this->mSqlQueries['get_alokasi_penerimaan'],
                                array(
                                        $kode,
                                        $nama,
                                        $unitkerjaId,'%',
                                        $unitkerjaId,
                                        $startRec,
                                        $itemViewed
                                ));
                                
        for($i = 0 ; $i < count($result); $i++){
            $result[$i]['alokasi_unit'] = $this->FormatNumberPersen($result[$i]['alokasi_unit'],4);
            $result[$i]['alokasi_pusat'] = $this->FormatNumberPersen($result[$i]['alokasi_pusat'],4);
        }
        return $result;
    }
    
    public function GetById($id ='')
    {
        return $this->Open($this->mSqlQueries['get_alokasi_penerimaan_by_id'],array($id));
    }
    
    public function IsAlokasiExist($datas=array())
    {
        if(is_array($datas) && !empty($datas) ){
            /**
             * melakukan cek input data pada proses edit data jikalau data lama sama
             * dengan yang sedang di inputkan maka loloskan
             */
            if(($datas['id_unit_lama'] != $datas['id_unit']) || 
                    $datas['id_kode_terima_lama'] != $datas['id_kode_terima'] || 
                        $datas['alokasi_operan_lama'] != $datas['alokasi_operan']){
                            
                if(!empty($datas['alokasi_operan'])){
                    $sql_operan = " AND `penerimaanUnitAlokasiOperan` = '".$datas['alokasi_operan']."'";
                } else {
                    $sql_operan="";
                }
            
                $sql = sprintf($this->mSqlQueries['is_alokasi_exist'],
                                                $datas['id_unit'],
                                                $datas['id_kode_terima'],$sql_operan);
                $cek = $this->Open($sql,array());
                if($cek[0]['total'] > 0){
                    return true;
                }
            }
        }
        return false;
    }
    
    public function Insert($datas=array())
    {
        if(is_array($datas) && !empty($datas)){
            $datas['alokasi_operan'] = (empty($datas['alokasi_operan']) ? NULL:$datas['alokasi_operan']);
            $datas['alokasi_nilai_batas'] = (empty($datas['alokasi_nilai_batas']) ? 
                                        NULL:$datas['alokasi_nilai_batas']);
            /**
            $sql =sprintf($this->mSqlQueries['insert_alokasi'],
                                        
                                            $datas['id_kode_terima'],
                                            $datas['id_unit'],
                                            $datas['alokasi_unit'],
                                            $datas['alokasi_pusat'],
                                            $datas['alokasi_operan'],
                                            $datas['alokasi_nilai_batas']
                                        );
            */
            return ($this->Execute($this->mSqlQueries['insert_alokasi'],
                                        array(
                                            $datas['id_kode_terima'],
                                            $datas['id_unit'],
                                            $datas['alokasi_unit'],
                                            $datas['alokasi_pusat'],
                                            $datas['alokasi_operan'],
                                            $datas['alokasi_nilai_batas'],
                                            $datas['id_unit_pusat']
                                        )));
            
        }
        return false;
    }
    
    public function Update($datas = array())
    {
        if(is_array($datas) && !empty($datas) && !empty($datas['alokasi_id'])){
            /**
            $sql=(sprintf($this->mSqlQueries['update_alokasi'],
                                       
                                            $datas['id_kode_terima'],
                                            $datas['id_unit'],
                                            $datas['alokasi_unit'],
                                            $datas['alokasi_pusat'],
                                            $datas['alokasi_operan'],
                                            $datas['alokasi_nilai_batas'],
                                            $datas['alokasi_id']
                                        ));
            */
            $datas['alokasi_operan'] = (empty($datas['alokasi_operan']) ? NULL :$datas['alokasi_operan']);
            $datas['alokasi_nilai_batas'] = (empty($datas['alokasi_nilai_batas']) ? 
                                        NULL :$datas['alokasi_nilai_batas']);
            $result=($this->Execute($this->mSqlQueries['update_alokasi'],
                                        array(
                                            $datas['id_kode_terima'],
                                            $datas['id_unit'],
                                            $datas['alokasi_unit'],
                                            $datas['alokasi_pusat'],
                                            $datas['alokasi_operan'],
                                            $datas['alokasi_nilai_batas'],
                                            $datas['id_unit_pusat'],
                                            $datas['alokasi_id']
                                        )));
            
            return $result;
        }
        return false;
    }
    public function Delete($id ='')
    {
        if(!empty($id)){
            $sql = sprintf($this->mSqlQueries['delete_alokasi'],$id);
            return ($this->Execute($this->mSqlQueries['delete_alokasi'],array($id)));
        }
        return false;
    }
    
    public function DeleteArray($ids=array())
    {
        if(is_array($ids) && !empty($ids)){
            $id = implode(',',$ids);
            $query = sprintf($this->mSqlQueries['delete_alokasi_array'],$id);
            return ($this->Execute($query,array()));
        } 
        return false;
    }
    
    	/**
	 * format number persen
	 * jika koma maka tampilkan dengan angka dibelakang koma
	 * jika tidak ada koma maka ditampilkan tanpa koma
	 */
	protected function FormatNumberPersen($number = 0,$des = 0)
	{
		$snumber = number_format($number,$des,',','.');
		$split_snumber =explode(',',$snumber);
		if(is_array($split_snumber)){
			if(intval($split_snumber[1])> 0){
				$desimal = floatval('0.'.$split_snumber[1]);
				return $split_snumber[0] + $desimal; 
			} else {
				return $split_snumber[0];
			}
		} else {
			return 0;
		}
	}
}

?>