<?php
class Tanggal 
{

	function __construct ()
	{
		
	}

	public function GetArrayMonth()
	{
		$month[1] = 'Januari';
		$month[2] = 'Februari';
		$month[3] = 'Maret';
		$month[4] = 'April';
		$month[5] = 'Mei';
		$month[6] = 'Juni';
		$month[7] = 'Juli';
		$month[8] = 'Agustus';
		$month[9] = 'September';
		$month[10] = 'Oktober';
		$month[11] = 'November';
		$month[12] = 'Desember';
		return $month;
	}
	
	public function GetArrayMonthCb()
	{
		#get Array Month
		$arrBulan = self::GetArrayMonth();
		
		$bulanKeys = array_keys($arrBulan);
		$bulanValues = array_values($arrBulan);
		
		for ($i = 0; $i < count($bulanKeys); $i++) {
			$arrMonth[$i]['id'] = $bulanKeys[$i];
			$arrMonth[$i]['name'] = $bulanValues[$i];
		}
		
		return $arrMonth;
	}
}
?>
