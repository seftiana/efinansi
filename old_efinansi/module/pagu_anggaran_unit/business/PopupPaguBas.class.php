<?php

class PopupPaguBas extends Database 
{

    protected $mSqlFile= 'module/pagu_anggaran_unit/business/popuppagubas.sql.php';
   
    public function __construct($connectionNumber=0) 
    {
        parent::__construct($connectionNumber);       
    }
   	
	public function GetPaguBas($offset, $limit, $kode='', $nama='') 
	{
		
		$result = $this->Open($this->mSqlQueries['get_pagu_bas'],
					array(
							
							'%'.$kode.'%',
							'%'.$nama.'%',
							$offset, 
                            $limit
						 )
					);
     	return $result;
     	
	}

	public function GetCountPaguBas ($kode='', $nama='') 
	{
		$result = $this->Open($this->mSqlQueries['count_pagu_bas'],
					array(
							'%'.$kode.'%',
							'%'.$nama.'%'
						 )
						);
						
		if (!$result) {
			return 0;
		} else {
			return $result[0]['total'];
		}
	}	
}