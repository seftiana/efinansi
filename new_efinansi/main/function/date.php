<?php
   function addYear($tanggal,$params){
      $t_start_date = explode('-',$tanggal);
         
      if ((!checkdate($t_start_date[1], $t_start_date[2], $t_start_date[0])))       
         return false;      
           
      $tanggal=strtotime($tanggal,time());
      $params = "+".$params." month";
      return $curYear=date("Y-m-d", strtotime($params,$tanggal));    
   } 
  //addYear(date("Y-m-d"),4);

  // fungsi cek tahun kabisat
		function isKabisat($thn) {
			// jika tahun habis dibagi 4, maka tahun kabisat
			if (($thn % 4) != 0) {
				return false;
			} // jika tidak habis dibagi 4, maka jika habis dibagi 100 dan 400 maka tahun kabisat
			else if ((($thn % 100) == 0) && (($thn % 400) != 0)) {
				return false;
			}
			else {
				return true;
			}
		}

   // mendapatkan tanggal terakhir dari sutau bulan
	function getLastDate($tahun,$bulan){
      $kabisat = isKabisat($tahun);
      if ($kabisat == true)
         $febLastDate = 29;
      else
         $febLastDate = 28;
      $bln = number_format($bulan) - 1;
      $arrLastDate = array(31,$febLastDate,31,30,31,30,31,31,30,31,30,31);
      for ($i=0;$i<12;$i++){
         if ($i == $bln)  
            $lastDate =  $tahun.'-'.$bulan.'-'.$arrLastDate[$i];
      }
      return $lastDate;
   }

  function ConvertDate($StrDate, $StrFormat, $ResultFormat)
	{
		/*
		*	Fungsi untuk menconvert format Tanggal
		*/
		$StrFormat = strtoupper($StrFormat);
		switch ($StrFormat)
		{
			case "MM/DD/YYYY" :	list($Month, $Day, $Year) = explode("/", $StrDate);
								break;
			case "DD/MM/YYYY" :	list($Day, $Month, $Year) = explode("/", $StrDate);
								break;
			case "YYYY/MM/DD" :	list($Year, $Month, $Day) = explode("/", $StrDate);
								break;
			case "MM-DD-YYYY" :	list($Month, $Day, $Year) = explode("-", $StrDate);
								break;
			case "DD-MM-YYYY" :	list($Day, $Month, $Year) = explode("-", $StrDate);
								break;
			case "YYYY-MM-DD" :	list($Year, $Month, $Day) = explode("-", $StrDate);
								break;
		}//End switch
		$ResultFormat = strtoupper($ResultFormat);
		switch ($ResultFormat)
		{
			case "MM-DD-YYYY" :	$StrResult = $Month."-".$Day."-".$Year;
								break;
			case "DD-MM-YYYY" :	$StrResult = $Day."-".$Month."-".$Year;
								break;
			case "YYYY-MM-DD" :	$StrResult = $Year."-".$Month."-".$Day;
								break;
			case "MM/DD/YYYY" :	$StrResult = $Month."/".$Day."/".$Year;
								break;
			case "DD/MM/YYYY" :	$StrResult = $Day."/".$Month."/".$Year;
								break;
			case "YYYY/MM/DD" :	$StrResult = $Year."/".$Month."/".$Day;
								break;
		}//End switch
		return $StrResult;
	} //End function

   function IndonesianDate($StrDate, $StrFormat)
	{
		$StrFormat = strtoupper($StrFormat);
		switch ($StrFormat)
		{
			case "MM-DD-YYYY" :	list($Month, $Day, $Year) = explode("-", $StrDate);
								break;
			case "DD-MM-YYYY" :	list($Day, $Month, $Year) = explode("-", $StrDate);
								break;
			case "YYYY-MM-DD" :	list($Year, $Month, $Day) = explode("-", $StrDate);
								break;
			case "MM/DD/YYYY" :	list($Month, $Day, $Year) = explode("/", $StrDate);
								break;
			case "DD/MM/YYYY" :	list($Day, $Month, $Year) = explode("/", $StrDate);
								break;
			case "YYYY/MM/DD" :	list($Year, $Month, $Day) = explode("/", $StrDate);
								break;
		}//End switch

		switch ($Month)
		{
			case "01" :	$StrResult = $Day." Januari ".$Year;
						break;
			case "02" :	$StrResult = $Day." Pebuari ".$Year;
						break;
			case "03" :	$StrResult = $Day." Maret ".$Year;
						break;
			case "04" :	$StrResult = $Day." April ".$Year;
						break;
			case "05" :	$StrResult = $Day." Mei ".$Year;
						break;
			case "06" :	$StrResult = $Day." Juni ".$Year;
						break;
			case "07" :	$StrResult = $Day." Juli ".$Year;
						break;
			case "08" :	$StrResult = $Day." Agustus ".$Year;
						break;
			case "09" :	$StrResult = $Day." September ".$Year;
						break;
			case "10" :	$StrResult = $Day." Oktober ".$Year;
						break;
			case "11" :	$StrResult = $Day." Nopember ".$Year;
						break;
			case "12" :	$StrResult = $Day." Desember ".$Year;
						break;
		} //end switch
		return $StrResult;
	}

 /*get jumlah waktu
 interval - yyyy dalam satuan tahun
 interval - m dalam satuan bulan
interval - d dalam satuan hari
 interval - w dalam satuan minggu
 interval - h dalam satuan jam
 interval - n dalam satuan menit
 interval - s dalam satuan detik
 
 echo dateDiff("d","1983/01/19","2006/05/15");*/

function dateDiff($interval,$dateTimeBegin,$dateTimeEnd) {
//Parse about any English textual datetime
//$dateTimeBegin, $dateTimeEnd

$dateTimeBegin=strtotime($dateTimeBegin);
if($dateTimeBegin === -1) {
  return("..begin date Invalid");
}

$dateTimeEnd=strtotime($dateTimeEnd);
if($dateTimeEnd === -1) {
  return("..end date Invalid");
}

$dif=$dateTimeEnd - $dateTimeBegin;

switch($interval) {
  case "s"://seconds
    return($dif);

  case "n"://minutes
    return(floor($dif/60)); //60s=1m

  case "h"://hours
    return(floor($dif/3600)); //3600s=1h

  case "d"://days
    return(floor($dif/86400)); //86400s=1d

  case "ww"://Week
    return(floor($dif/604800)); //604800s=1week=1semana

  case "m": //similar result "m" dateDiff Microsoft
    $monthBegin=(date("Y",$dateTimeBegin)*12)+
                date("n",$dateTimeBegin);
    $monthEnd=(date("Y",$dateTimeEnd)*12)+
              date("n",$dateTimeEnd);
    $monthDiff=$monthEnd-$monthBegin;
    return($monthDiff);

  case "yyyy": //similar result "yyyy" dateDiff Microsoft
    return(date("Y",$dateTimeEnd) - date("Y",$dateTimeBegin));

  default:
    return(floor($dif/86400)); //86400s=1d
}
}
?>