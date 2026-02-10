<?php

/** 
 * 
 * class number format
 * @deskripsi untuk mengatur format number
 * @since januari 2013
 * @copyright (c) 2013 Gamatechno Indonesia
 * 
 */
 
final class NumberFormat
{	
	/**
	 * FormatNumberPersen
	 * @description :jika koma maka tampilkan dengan angka dibelakang koma
	 * jika tidak ada koma maka ditampilkan tanpa koma
	 * @param number $number 
	 * @param number $des  : banyak nya angka di belakang koma
	 * @access public
	 * @return mix
	 */
	public static function Decimal($number = 0,$des=0)
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

   /**
    * fungsi Accounting
    * untuk memformat angka, jika minus maka angka didalam tanda kurung
    * @param number $angka
    * @param number $des
    * @return String
    * @acces Public
    */
   public static function Accounting($number = 0,$des= 0)
   {
	   $number = (float) $number;
	    $str_number ='';
		if($number < 0){
			 $str_number= '('.number_format(($number * (-1)), $des, ',', '.').')';
		 } else{
			$str_number = number_format($number,$des, ',', '.');	
		 }
		 return $str_number;
   }		
}

?>