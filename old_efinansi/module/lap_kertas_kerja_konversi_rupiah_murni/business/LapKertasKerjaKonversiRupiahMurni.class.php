<?php

/**
 * Class LapKertasKerjaKonversiRupiahMurni
 * @package lap_kertas_kerja_konversi_rupiah_murni
 * @todo handle query database
 * @copyright 2011 Gamatechno
 */

class LapKertasKerjaKonversiRupiahMurni extends Database
{
   protected $mSqlFile;
   public $_POST;
   public $_GET;

   public function __construct ($connectionNumber=0)
   {
      $this->mSqlFile   = 'module/'.Dispatcher::Instance()->mModule.'/business/lapkertaskerjakonversirupiahmurni.sql.php';
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
      parent::__construct($connectionNumber);
   }

   public function GetPeriodeTahun($param = array())
   {
      $default       = array(
         'active' => false,
         'open' => false
      );
      $options       = array_merge($default, (array)$param);
      $return        = $this->Open($this->mSqlQueries['get_periode_tahun'], array(
         (int)($options['active'] === false),
         (int)($options['open'] === false)
      ));

      return $return;
   }

	/**
	 * function GetComboTahunAnggaran()
	 * @todo Mendapatkan tahun anggaran untuk ditampilkan di combobox
	 * @return array()
	 */
   public function GetComboTahunAnggaran()
   {
      return $this->Open($this->mSqlQueries['get_combo_tahun_anggaran'], array());
   }

   	/**
	 * function GetCombotahunAnggaran()
	 * @todo Mendapatkan tahun anggaran berdasarkan ID tahun anggaran
	 * @param $id number
	 * @return array()
	 */
   public function GetTahunAnggaranById($id)
   {
      return $this->Open($this->mSqlQueries['get_tahun_anggaran_by_id'], array($id));
   }

   public function GetDataLapKertasKerjaKonversiRupiahMurni($tahunAnggaranId,$tanggal,
   	$startRec,$itemViewed)
   {
   		return $this->Open($this->mSqlQueries['get_data'],
		   array($tahunAnggaranId,$tanggal,$startRec,$itemViewed));
   }
   public function GetCountDataLapKertasKerjaKonversiRupiahMurni($tahunAnggaranId,$tanggal)
   {
   		$result=$this->Open($this->mSqlQueries['get_count_data'], array($tahunAnggaranId,$tanggal));
   		return $result[0]['total'];
   }
}