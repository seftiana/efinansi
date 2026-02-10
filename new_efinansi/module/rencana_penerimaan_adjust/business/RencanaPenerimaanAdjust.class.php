<?php
#doc
#    classname:    RencanaPenerimaanAdjust
#    scope:        PUBLIC
#
#/doc

class RencanaPenerimaanAdjust extends Database
{
    protected $mSqlFile = 'module/rencana_penerimaan_adjust/business/rencana_penerimaan_adjust.sql.php';
    function __construct ($connectionNumber = 0)
    {
        parent::__construct($connectionNumber);
        
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

    public function GetTahunAnggaran($id) 
    {
        $result = $this->Open($this->mSqlQueries['get_tahun_anggaran'], array($id));
        return $result[0];
    }

    public function CheckTahunAnggaran($id)
    {
        $result    = $this->Open($this->mSqlQueries['check_tahun_anggaran'], array($id));
        return $result[0];
    }
    
    public function GetDataUnitkerja($kodenama,$tahunAnggaran,$unitkerja,$startRec,$itemView) 
    {
        if($kodenama !=""){
            $str_kode =" AND (kodeterimaKode like '%".$kodenama."%' OR kodeterimaNama LIKE '%".$kodenama."%') ";
        } else{
            $str_kode ="";
        }
        $query = sprintf($this->mSqlQueries['get_data_unitkerja'],$tahunAnggaran,
        $tahunAnggaran, 
        $unitkerja ,'%',$unitkerja,
        $str_kode,
        $startRec,
        $itemView);
        $result = $this->Open($query,array());
        return $result;
    }
    
    public function GetCountData($kodenama,$tahun_anggaran, $unitkerja='') 
    {

        if($unitkerja != "") {
            $str_unitkerja = " AND (unitkerjaId=$unitkerja OR tempUnitId=$unitkerja) ";
        } else {
            $str_unitkerja = "";
        }

        if($kodenama !=""){
            $str_kode =" AND (kodeterimaKode like '%".$kodenama."%' OR kodeterimaNama LIKE '%".$kodenama."%') ";
        } else{
            $str_kode ="";
        }
        $query = sprintf($this->mSqlQueries['get_count_data'],
        $tahunAnggaran,
        $tahunAnggaran, 
        $unitkerja ,'%',$unitkerja ,
        $str_kode);
        
        $data = $this->Open($query, array());
        if (!$data) {
            return 0;
        } else {
            return $data[0]['total'];
        }
    }
    
    public function GetDataRencanaPenerimaanById($id) 
    {
        $result = $this->Open($this->mSqlQueries['get_data_rencana_penerimaan_by_id'], array($id));
        return $result[0];
    }
    
    function GenerateNomorAdjustment()
    {
        $result = $this->Open($this->mSqlQueries['generate_nomor_adjustment'], array());
        
        return $result[0]['nomor'];
    }
    
    function GetStatus($param)
    {
        $result = $this->Open($this->mSqlQueries['get_status'], array('%'.$param.'%'));
        
        return $result[0]['id'];
    }
    
    function DoInputAdjustmentRencanaPenerimaan($data, $status)
    {
        
        $result = $this->Execute(
            $this->mSqlQueries['insert_into_rencana_penerimaan_adjust'], 
            array(
                $data['data_id'],
                $this->GenerateNomorAdjustment(),
                $data['penerimaan_januari'],
                $data['adjust_januari'],
                $data['penerimaan_februari'],
                $data['adjust_februari'],
                $data['penerimaan_maret'],
                $data['adjust_maret'],
                $data['penerimaan_april'],
                $data['adjust_april'],
                $data['penerimaan_mei'],
                $data['adjust_mei'],
                $data['penerimaan_juni'],
                $data['adjust_juni'],
                $data['penerimaan_juli'],
                $data['adjust_juli'],
                $data['penerimaan_agustus'],
                $data['adjust_agustus'],
                $data['penerimaan_september'],
                $data['adjust_september'],
                $data['penerimaan_oktober'],
                $data['adjust_oktober'],
                $data['penerimaan_nopember'],
                $data['adjust_nopember'],
                $data['penerimaan_desember'],
                $data['adjust_desember'],
                $data['total'],
                $status,//$this->GetStatus($status),
                $data['user_id']
            )
        );
        #print_r($result);
        return $result;
    }
    
    function DoCheckAdjustmentRencanaPenerimaan($dataId)
    {
        $return = $this->Open($this->mSqlQueries['do_check_adjustment_penerimaan'], array($dataId));
        
        return $return[0]['count'];
    }
}
?>
