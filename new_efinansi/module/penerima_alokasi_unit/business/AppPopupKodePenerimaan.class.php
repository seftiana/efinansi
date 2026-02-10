<?php
class AppPopupKodePenerimaan extends Database {

   protected $mSqlFile= 'module/penerima_alokasi_unit/business/apppopupkodepenerimaan.sql.php';
   
   function __construct($connectionNumber=0) {
      parent::__construct($connectionNumber);  
     // $this->setDebugOn();
   }

	function GetData($offset, $limit, $kodePenerimaan,$unitKerja) 
	{
		$result = $this->Open($this->mSqlQueries['get_data'], 	
						array(
								'%'.$kodePenerimaan.'%', 
								'%'.$kodePenerimaan.'%', 
								'%'.$unitKerja.'%', 
								'%'.$unitKerja.'%', 
								$offset, 
								$limit));
		for($i = 0 ; $i < count($result); $i++){
			$result[$i]['alokasi_unit']=$this->FormatNumberPersen($result[$i]['alokasi_unit'],4);
			$result[$i]['alokasi_pusat']=$this->FormatNumberPersen($result[$i]['alokasi_pusat'],4);
		}
		return $result;
	}

	function GetCountData( $kodePenerimaan,$unitKerja) 
	{
		
		$result = $this->Open($this->mSqlQueries['get_count_data'],
						array(
								'%'.$kodePenerimaan.'%', 
								'%'.$kodePenerimaan.'%', 
								'%'.$unitKerja.'%', 
								'%'.$unitKerja.'%'
						)
						);
		if (!$result) {
			return 0;
		} else {
			return $result[0]['total'];
		}
	}
		/**
	 * format number persen
	 * jika koma maka tampilkan dengan angka dibelakang koma
	 * jika tidak ada koma maka ditampilkan tanpa koma
	 */
	protected function FormatNumberPersen($number = 0,$des=0)
	{
		if($number != NULL){
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
		} else {
			return '';
		}
	}
}
?>