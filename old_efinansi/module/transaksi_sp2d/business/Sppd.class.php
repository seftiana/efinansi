<?php
#doc
# package:     Sppd
# scope:       PUBLIC
# @created     ucil.619
# @Author      Eko Susilo
# @Created     2012-09-04
# @Modified    2012-09-04
# @Analysts    Nanang Ruswianto
# @copyright   Copyright (c) 2012 Gamatechno
#/doc

class Sppd extends Database
{
   #   internal variables
   protected $mSqlFile     = 'module/transaksi_sp2d/business/sppd.sql.php';
   #   Constructor
   function __construct ($connectionNumber = 0)
   {
      parent::__construct($connectionNumber);      
   }
   
   function GetDataTransaksi($no_ref, $start, $end, $offset, $limit)
   {
      $result     = $this->Open(
         $this->mSqlQueries['get_data_transaksi'], 
         array(
            $start, 
            $end, 
            '%'.$no_ref.'%',
            $offset, 
            $limit
         )
      );
      
      return $result;
   }
   
   function CountData()
   {
      $result     = $this->Open(
         $this->mSqlQueries['count_data'], 
         array()
      );
      
      return $result[0]['total'];
   }
   
   function GetTransaksiByTransId($id)
   {
      $result     = $this->Open(
         $this->mSqlQueries['get_transaksi_by_id'], 
         array(
            $id
         )
      );
      
      return $result[0];
   }
   
   function DoInsertSp2d($data = array())
   {
      $user_id    = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
      
      $sppdThAnggar        = $data['ta_id'];
      $transId             = $data['trans_id'];
      $spmId               = $data['spm_id'];
      $dataId              = $data['data_id'];
      $kepada              = $data['sppd_kpd'];
      $sppdNomor           = $data['sppd_nomor'];
      $sppdNorek           = $data['sppd_norek'];
      $sppdNpwp            = $data['sppd_npwp'];
      $sppdBank            = $data['sppd_bank'];
      $sppdKeterangan      = $data['keterangan'];
      $sppdTanggal         = $data['sppd_tgl'];
      $sppdNominal         = $data['sppdNominal'];
      
      $result     = $this->Execute(
         $this->mSqlQueries['insert_sp2d'], 
         array(
            $spmId, 
            $transId, 
            $sppdNomor, 
            $kepada,
            $sppdNpwp, 
            $sppdNorek, 
            $sppdBank, 
            $sppdKeterangan, 
            $sppdNominal, 
            $sppdThAnggar, 
            $sppdTanggal, 
            $user_id
         )
      );
      
      if($result)
      {
         return true;
      }
      else
      {
         return $this->GetLastError();
      }
   }
   
   function DoUpdateSp2d($data = array())
   {
      $id               = $data['data_id'];
      $kepada           = $data['sppd_kpd'];
      $sppdNorek        = $data['sppd_norek'];
      $sppdNpwp         = $data['sppd_npwp'];
      $sppdBank         = $data['sppd_bank'];
      $sppdKeterangan   = $data['keterangan'];
      $sppdNominal      = $data['sppdNominal'];
      $sppdNomor        = $data['sppd_nomor'];
      
      $result  = $this->Execute(
         $this->mSqlQueries['update_sp2d'], 
         array(
            $sppdNomor, 
            $kepada,
            $sppdNpwp, 
            $sppdNorek, 
            $sppdBank, 
            $sppdKeterangan, 
            $sppdNominal,
            $id
         )
      );
      
      if($result)
      {
         return true;
      }
      else
      {
         return $this->GetLastError();
      }
   }
   
   function GetLastId()
   {
      $return     = $this->Open(
         $this->mSqlQueries['get_last_insert_id'], 
         array()
      );
      
      return $return[0]['last_id'];
   }
   
   function GenerateNomor($nss, $satker, $nomor)
   {
      $formula     = $this->Open(
         $this->mSqlQueries['generate_nomor_formula'], 
         array()
      );
      
      $return     = $this->Open(
         $formula[0]['formulaFormula'], 
         array($nss, $satker, $nomor)
      );
      
      return $return[0]['nomor'];
   }
   
   function GetDataSp2dCetak($data_id)
   {
      $result     = $this->Open(
         $this->mSqlQueries['get_data_sppd_cetak'], 
         array(
            $data_id
         )
      );
      
      return $result[0];
   }
}
?>
