<?php
    #doc
    #    classname:    Spm
    #    scope:        PUBLIC
    # extends extends Database
    # construct: $connectionNumber = 0
    #/doc
    
    class Spm extends Database
    {
        #    internal variables
        protected $mSqlFile = 'module/approval_pencairan/business/spm.sql.php';
        #    Constructor
        function __construct ($connectionNumber = 0)
        {
            # code...
            parent::__construct($connectionNumber);
        }
        
        function ListCaraBayar()
        {
            $return = $this->Open($this->mSqlQueries['get_cara_bayar'], array());
            
            return $return;
        }
        
        function ListJenisPembayaran()
        {
            $return = $this->Open($this->mSqlQueries['get_jenis_pembayaran'], array());
            
            return $return;
        }
        
        function ListSifatPembayaran()
        {
            $return = $this->Open($this->mSqlQueries['get_sifat_pembayaran'], array());
            
            return $return;
        }
        
        // insert into spm
        function DoInsertSpm($cara_bayar, $jenis_bayar, $sifat_bayar, 
                            $nama, $npwp, $rekening, $bank, $keterangan, $nominal,$basId,$nominal_pajak, $userId)
        {
            $result = $this->Execute($this->mSqlQueries['insert_into_spm'], 
                      array(
                            $this->GenerateNumberSpm(),
                            $cara_bayar, 
                            $jenis_bayar, 
                            $sifat_bayar, 
                            $nama, 
                            $npwp, 
                            $rekening, 
                            $bank, 
                            $keterangan,
                            $nominal, 
                            empty($basId) ? NULL : $basId,
                            $nominal_pajak,
                            $userId
                      ));
            
            if($result){
                return $result;
            }else{
                echo $this->GetLastError();
            }
            
        }
        
        function GenerateNumberSpm()
        {
            $result = $this->Open($this->mSqlQueries['generate_number_spm'], array());
            
            return $result[0]['number'];
        }
        
        function ListKegiatanByApprovalId($id)
        {
            $result = $this->Open($this->mSqlQueries['get_transaksi_by_approval_id'], array($id));
            
            return $result;
        }
        
        function LastSpmId()
        {
            $result = $this->Open($this->mSqlQueries['get_max_id_spm'], array());
            
            return $result[0]['last_id'];
        }
        
        function InsertSpmDet($spmId,$detailId,$nominal,$userId)
        {
            $result = $this->Execute($this->mSqlQueries['insert_into_spm_det'], array($spmId,$detailId,$nominal,$userId));
            
            return $result;
        }
        
        function DeleteSpm($spmId)
        {
            $result = $this->Execute($this->mSqlQueries['delete_spm'], array($spmId));
            
            return $result;
        }
        
        function GetSpmBySpmId($spmId)
        {
            $result = $this->Open($this->mSqlQueries['get_spm_by_spm_id'], array($spmId));
            
            return $result[0];
        }
        
        function UpdateSpm($carabayar,$jenisBayar,$sifatBayar,$nama,$npwp,$rekening,$bank,$uraian,$nominal,$basId,$nominal_pajak,$userId,$spmId)
        {
            $result = $this->Execute($this->mSqlQueries['update_spm'], 
                      array(
                        $carabayar,
                        $jenisBayar,
                        $sifatBayar,
                        $nama,
                        $npwp,
                        $rekening,
                        $bank,
                        $uraian,
                        $nominal,
                        empty($basId) ? NULL : $basId,
                        $nominal_pajak,
                        $userId,
                        $spmId
                      ));
            
            return $result;
        }
        
        function DeleteSpmDetBySpmId($spmId)
        {
            $result = $this->Execute($this->mSqlQueries['delete_spm_det_by_spm_id'], array($spmId));
            
            return $result;
        }
        
        public function GetDipa()
        {
            $return = $this->Open($this->mSqlQueries['get_dipa'], array());
            
            return $return[0];
        }
        
        public function GetComboPajak()
        {
            $return = $this->Open($this->mSqlQueries['get_tipe_pajak'], array());
            
            return $return;
        }
        
        public function GetPajakSpm($spmId)
        {
            $result = $this->Open($this->mSqlQueries['get_pajak_spm'], array($spmId));
            
            return $result[0];
        }
    
    }
    ###
?>
