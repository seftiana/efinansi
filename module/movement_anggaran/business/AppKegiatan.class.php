<?php
    #doc
    #    classname:    AppKegiatan
    #    scope:        PUBLIC
    # extends extends Database
    #/doc
    
    class AppKegiatan extends Database
    {

    protected $mSqlFile;
    public $_POST;
    public $_GET;    

    public function __construct($connectionNumber = 0) {
        $this->mSqlFile = 'module/movement_anggaran/business/app_kegiatan.sql.php';
        $this->_POST = is_object($_POST) ? $_POST->AsArray() : $_POST;
        $this->_GET = is_object($_GET) ? $_GET->AsArray() : $_GET;
        parent::__construct($connectionNumber);
    }

        #    internal variables
   public $indonesianMonthCombo    = array(
      0 =>array(
         'id' => 1,
         'name' => 'Januari'
      ), array(
         'id' => 2,
         'name' => 'Februari'
      ), array(
         'id' => 3,
         'name' => 'Maret'
      ), array(
         'id' => 4,
         'name' => 'April'
      ), array(
         'id' => 5,
         'name' => 'Mei'
      ), array(
         'id' => 6,
         'name' => 'Juni'
      ), array(
         'id' => 7,
         'name' => 'Juli'
      ), array(
         'id' => 8,
         'name' => 'Agustus'
      ), array(
         'id' => 9,
         'name' => 'September'
      ), array(
         'id' => 10,
         'name' => 'Oktober'
      ), array(
         'id' => 11,
         'name' => 'November'
      ), array(
         'id' => 12,
         'name' => 'Desember'
      )
   );        
        public $indonesianMonth    = array(
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        );
                
        // protected $mSqlFile = 'module/movement_anggaran/business/app_kegiatan.sql.php';
        // #    Constructor
        // function __construct ($connectionNumber = 0)
        // {
        //     parent::__construct($connectionNumber);
        // }
        public function GetTahunAnggaran()
        {
           $result  = $this->Open($this->mSqlQueries['get_combo_tahun_anggaran'], array());
           
           return $result;
        }
       
        public function GetTahunAnggaranIsAktif()
        {
           $result  = $this->Open($this->mSqlQueries['get_tahun_anggaran_aktif'], array());
           
           return $result[0]['id'];
        }
        function GetDataKegiatanRef($offset, $limit, $kegiatanref,$kode, $bulan,$unit_id='') 
        {  //$this->SetDebugOn();
        
            if($bulan == '' || $bulan == 'all') {
                $bFlag = 1;
            } else {
                $bFlag = 0;
            }
			/*
            if ($unit_id != '')
            {
                $and = 'AND ';
            }else{
                $and = " OR ";
            }*/
            //$sql    = sprintf($this->mSqlQueries['get_data_kegiatanref'], '%s','%s','%s','%s','%s', $and, '%s', '%d', '%d');
            $result = $this->Open($this->mSqlQueries['get_data_kegiatanref'], 
                      	array(
                      		$kode,
                      		'%'.$kegiatanref.'%',
                      		$bulan,
                      		$bFlag, 
                      		$unit_id,
                      		$unit_id,
                      		$unit_id, 
                      		$offset,
                      		$limit
						));            
            return $result;
        }

        function GetCountDataKegiatanRef () 
        {
            $result = $this->Open($this->mSqlQueries['get_count_data_kegiatanref'],array());
            if (!$result) {
                return 0;
            }  else {
                return $result[0]['total'];
            }
        }
           
        function GetKomponenAnggaran($subkegiatan)
        {
            if(!empty($subkegiatan)){
                foreach ($subkegiatan as $value)
                {
                    $return[$value['kegiatandetail_id']] = $this->Open($this->mSqlQueries['get_komponen_anggaran'], 
                    array($value['kegiatandetail_id']));
                }
                return $return;
            } else {
                return NULL;
            }
        }

        function GetKomponenAnggaranTujuan($subkegiatan)
        {//$this->SetDebugOn();
            if(!empty($subkegiatan)){
                foreach ($subkegiatan as $value)
                {
                    $return[$value['kegiatandetail_id']] = $this->Open($this->mSqlQueries['get_komponen_anggaran_tujuan'], 
                    array($value['kegiatandetail_id']));
                }
                return $return;
            } else {
                return NULL;
            }
        }
                
}
        ###
?>