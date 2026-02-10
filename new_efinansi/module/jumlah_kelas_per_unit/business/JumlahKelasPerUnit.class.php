<?php

class JumlahKelasPerUnit extends Database 
{

	protected $mSqlFile= 'module/jumlah_kelas_per_unit/business/jumlah_kelas_per_unit.sql.php';
	
	public function __construct($connectionNumber=0) 
	{
		parent::__construct($connectionNumber);
	}

    public function GetCountRowData($tahunAnggaranId,$unitKerjaId)
    {
        //$this->SetDebugOn();
        $result = $this->Open(
                    $this->mSqlQueries['get_count_row_data'], 
                    array(
                        $tahunAnggaranId,
                        $unitKerjaId
                    )
                );
        return $result[0]['total'];
    }
    
	public function GetDataJumlahKelasPerUnit($tahunAnggaranId,$unitKerjaId,$offset, $limit) 
    { 
        
		$sql = sprintf(
				$this->mSqlQueries['get_data_jml_kelas_per_unit'],
				$tahunAnggaranId,
                $offset, 
                $limit
        );
		//echo $sql;
		return $this->Open($sql, array());
	}


    public function GetCountDataJumlahKelasPerUnit() 
    { 
    	$result = $this->Open(
    				$this->mSqlQueries['get_count_data_jml_kelas_per_unit'], 
    				array()
    			);
        return $result[0]['total'];
    }
        
	public function GetDataJummlahKelasPerUnitById($id) 
	{
      $result = $this->Open($this->mSqlQueries['get_data_jml_kelas_per_unit_by_id'], array($id));
	  return $result[0];
	}
		
	public function DoAddJumlahKelasPerUnit($tahunAnggaranId,$unitKerjaId,$jumlahKelas,$prodiNamaGasal,$prodiNamaGenap,$prodiSmGasalId,$prodiSmGenapId) 
	{   //$this->SetDebugOn();
	    if($jumlahKelas == 0){
	        $getProdiGasal = explode('|',$prodiNamaGasal);
            $kelasGasal =$getProdiGasal[0];
            $prodiNamaG =$getProdiGasal[1]; 

            $getProdiGenap= explode('|',$prodiNamaGenap);
            $kelasGenap =$getProdiGenap[0]; 
        //$prodiNamaG =$getProdiGenap[1]; 
            
        } else {
            $prodiSmGasalId = NULL;
            $prodiSmGenapId = NULL;
            $prodiNamaG = NULL;
            $kelasGasal = NULL;
            $kelasGenap = NULL;
        }
        
		$result = $this->Execute($this->mSqlQueries['do_add_jml_kelas_per_unit'], array(
		      $tahunAnggaranId,
		      $unitKerjaId,
		      $jumlahKelas,$prodiNamaG,$prodiSmGasalId,$prodiSmGenapId,$kelasGasal,$kelasGenap
             )
         );
		
		return $result;
	}

	public function DoUpdateJumlahKelasPerUnit($tahunAnggaranId,$unitKerjaId,$jumlahKelas,$prodiNamaGasal,$prodiNamaGenap,$prodiSmGasalId,$prodiSmGenapId,$id) 
	{  // $this->SetDebugOn();
	  if($jumlahKelas == 0){
            $getProdiGasal = explode('|',$prodiNamaGasal);
            $kelasGasal =$getProdiGasal[0];
            $prodiNamaG =$getProdiGasal[1]; 

            $getProdiGenap= explode('|',$prodiNamaGenap);
            $kelasGenap =$getProdiGenap[0];
            //$prodiNamaG =$getProdiGenap[1]; 
           
        } else {
            $prodiSmGasalId = NULL;
            $prodiSmGenapId = NULL;
            $prodiNamaG = NULL;
            $kelasGasal = NULL;
            $kelasGenap = NULL;
        }
		$result = $this->Execute(
		      $this->mSqlQueries['do_update_jml_kelas_per_unit'], 
		      array(
		            $tahunAnggaranId,
                    $unitKerjaId,
                    $jumlahKelas,$prodiNamaG,$prodiSmGasalId,$prodiSmGenapId,$kelasGasal,$kelasGenap,
                    $id
              )
        );
		
		return $result;
	}

    public function DoDeleteJumlahKelasPerUnitById($id) 
    {
        $result=$this->Execute($this->mSqlQueries['do_delete_jml_kelas_per_unit_by_id'], array($id));
        return $result;
    }
    
    public function DoDeleteJumlahKelasPerUnitByArrayId($arrayId) 
    {
        $Ids = @implode("', '", $arrayId);
        $result=$this->Execute($this->mSqlQueries['do_delete_jml_kelas_per_unit_by_id'], array($Ids));
        return $result;
    }
    	
	//get combo tahun anggaran
	public function GetComboTahunAnggaran() 
	{
		$result = $this->Open($this->mSqlQueries['get_combo_tahun_anggaran'], array());
		return $result;
	}

	public function GetTahunAnggaranAktif() 
	{
		$result = $this->Open($this->mSqlQueries['get_tahun_anggaran_aktif'], array());
		return $result[0];
	}

}
?>