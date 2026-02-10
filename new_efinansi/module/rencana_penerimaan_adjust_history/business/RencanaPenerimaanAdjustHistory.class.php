<?php
#doc
#    classname:    RencanaPenerimaanAdjustHistory
#    scope:        PUBLIC
#
#/doc

class RencanaPenerimaanAdjustHistory extends Database
{
    #    internal variables
    protected $mSqlFile = 'module/rencana_penerimaan_adjust_history/business/rencana_penerimaan_adjust_history.sql.php';
    #    Constructor
    function __construct ($connectionNumber = 0)
    {
        # code...
        parent::__construct($connectionNumber);
    }
    ###
    
    
    function GetData($kodenama,$tahunAnggaran,$unitkerja,$startRec,$itemView)
    {
        if($kodenama !=""){
            $str_kode =" AND (kodeterimaKode like '%".$kodenama."%' OR kodeterimaNama LIKE '%".$kodenama."%') ";
        } else{
            $str_kode ="";
        }
        $query = sprintf($this->mSqlQueries['get_data_history'],$tahunAnggaran,
        $tahunAnggaran, 
        $unitkerja ,'%',$unitkerja,
        $str_kode,
        $startRec,
        $itemView);
        $result = $this->Open($query,array());
        return $result;
    }
    
    function CountData($kodenama,$tahunAnggaran,$unitkerja)
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
        $query = sprintf($this->mSqlQueries['count_data'],
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
    
    function GetDataById($id)
    {
        $return = $this->Open($this->mSqlQueries['get_data_adjustment_by_id'], array($id));
        
        return $return[0];
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
    
    function UpdateAdjustmentRencanaPenerimaan($data)
    {
        $result     = $this->Execute(
            $this->mSqlQueries['update_adjustment_rencana_penerimaan'], 
            array(
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
                $data['user_id'],
                $data['id_adjustment']
            )
        );
        
        return $result;
    }
    
    function DoApprovalAdjustment($id,$user,$status,$nominal,$data_id){
        $result     = $this->Execute(
            $this->mSqlQueries['do_approval_adjustment'], 
            array(
                $this->GetStatus($status),
                $user,
                $id
            )
        );
        
        if($result){
            $update_rencana_penerimaan  = $this->Execute(
                $this->mSqlQueries['do_update_rencana_penerimaan'], 
                array(
                    $nominal, 
                    $data_id
                )
            );
            
            if($update_rencana_penerimaan){
                return true;
            }else{
                return false;
            }
        }else{
            return $this->GetLasError();
        }
    }
    
    function GetStatus($param)
    {
        $result = $this->Open($this->mSqlQueries['get_status'], array('%'.$param.'%'));
        
        return $result[0]['id'];
    }
}
?>
