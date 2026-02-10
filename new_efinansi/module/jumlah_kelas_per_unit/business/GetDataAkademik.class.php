<?php

/**
 * 
 * 
 * GetDataAkademik
 * @analyst dyah fajar n
 * @author noor hadi <noor.hadi@gamatechno.com>
 * @since november 2015
 * @description untuk mendapatkan data dari akademik melalui servcie format json
 * @copyright (c) 2015 Gamatechno Indonesia
 * 
 */
class GetDataAkademik extends Database {

    protected $semesterGenap;
    protected $semesterGasal;
    protected $mRest;
    protected $urlDataSouce;
    protected $mSqlFile = 'module/jumlah_kelas_per_unit/business/get_data_akademik.sql.php';
    protected $mStatusService;

    public function __construct($connectionNumber = 0) {
        //$this->urlDataSouce = GTFWConfiguration::GetValue('application', 'url_source');
        parent::__construct($connectionNumber);
        $this->_setPathUrlService();
    }

    /**
     * _getDataSourceAddress
     * untuk mendapatkan data source adress dari database
     * @access private
     * @return array();
     */
    private function _getDataSourceAddress() {
        $appKode = '200';
        $result = $this->Open(
            $this->mSqlQueries['get_data_souce_address'], array(
                $appKode
            )
        );

        return $result[0];
    }

    /**
     * _setPathUrlService
     * untuk set alamat url service
     * @access private
     * @return null
     */
    private function _setPathUrlService() {
        $urlDataSource = $this->_getDataSourceAddress();        
        $getSemesterList = @file_get_contents($urlDataSource['app_service_address']);
        
        if($getSemesterList == false){
            $this->mStatusService = false;
        } else {
            $this->mStatusService = true;
        }
        
        $this->urlDataSouce =  $urlDataSource['app_service_address'];
    }

    public function GetStatusService(){
        return $this->mStatusService;
    }
    
    public function GetSemesterList() {
        // get data semester
        $getSemesterList = @file_get_contents($this->urlDataSouce . "?act=getSemesterList");

        $decodeGetSemesterList = json_decode($getSemesterList, true);

        if (!empty($decodeGetSemesterList['gtfwResult']['data'])) {
            $indexG = 0;
            $indexGs = 0;
            foreach ($decodeGetSemesterList['gtfwResult']['data'] as $key => $s) {
                if ($s[0] % 2 == 0) {
                    $this->semesterGenap[$indexG]['id'] = $s['SEM_ID'];
                    $this->semesterGenap[$indexG]['name'] = $s['SEM_NAMA'];
                    $indexG++;
                } else {
                    $this->semesterGasal[$indexGs]['id'] = $s['SEM_ID'];
                    $this->semesterGasal[$indexGs]['name'] = $s['SEM_NAMA'];
                    $indexGs++;
                }
            }
        }
    }

    public function GetSemesterGenap() {
        return $this->semesterGenap;
    }

    public function GetSemesterGasal() {
        return $this->semesterGasal;
    }

    public function GetJumlahKelasPerProdi($semesterId) {
        // get data semester
        if (is_object($semesterId)) {
            $semesterId = $semesterId->mrVariable;
        }

        $data = array();

        // get data semester
        $getKelasSemester = @file_get_contents($this->urlDataSouce  . "?act=getKelasInfo&semId=" . $semesterId);
        $decodeGetKelasSemester = json_decode($getKelasSemester, tru);

        if (!empty($decodeGetKelasSemester['gtfwResult']['data'])) {
            $index = 0;
            foreach ($decodeGetKelasSemester['gtfwResult']['data'] as $key => $s) {
                $data[$index]['id'] = $s['JUMLAH_KELAS'] . '|' . $s['PRODI_NAMA'];
                $data[$index]['name'] = $s['PRODI_NAMA'] . ' - ' . $s['JUMLAH_KELAS'];
                //$data[$index]['name'] =$s['JUMLAH_KELAS'];
                $index++;
            }
        }
        return $data;
    }

}

?>