<?php

class RollbackPembukuan extends Database{
    protected $tppId;
    protected $tppIdSebelumnya;
    protected $userId;

    public function __construct($connectionNumber = 0) {
        $this->mSqlFile = 'module/tahun_pembukuan/business/rollback_pembukuan.sql.php';
        $this->userId = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
        parent::__construct($connectionNumber);
    }

    public function getTahunPembukuanAktif(){
        $result = $this->Open($this->mSqlQueries['get_tahun_pembukuan_aktif'],array());

        if(!empty($result)){
            return $result[0];
        }
    }

    public function getTahunPembukuanSebelumnya($tppId){
        $result = $this->Open($this->mSqlQueries['get_tahun_pembukuan_sebelumnya'],array($tppId));

        if(!empty($result)){
            return $result[0];
        }
    }

    public function setIdTahunPembukuan(){
        $tppAktif = $this->getTahunPembukuanAktif();
        $this->tppId = $tppAktif['tppId'];

        $tppSebelumnya = $this->getTahunPembukuanSebelumnya($this->tppId);
        $this->tppIdSebelumnya = $tppSebelumnya['tppId'];
    }

    protected function deleteBukuBesar(){
        return $this->Execute($this->mSqlQueries['delete_buku_besar'],array());
    }

    protected function deleteBukuBesarHis(){
        return $this->Execute($this->mSqlQueries['delete_buku_besar_his'],array());
    }

    protected function setSaldoAwalPembukuanAktif(){
        return $this->Execute($this->mSqlQueries['set_saldo_awal_pembukuan_aktif'],array());
    }

    protected function setSaldoAwalTahunPembukuanAktif(){
        return $this->Execute($this->mSqlQueries['set_saldo_awal_tahun_pembukuan_aktif'],array());
    }

    protected function rollbackStatusPosting(){
        return $this->Execute($this->mSqlQueries['rollback_status_posting'],array());
    }

    protected function setAktifTahunPembukuanSebelumnya(){
        $result = true;

        $result &= $this->Execute($this->mSqlQueries['set_non_aktif_tpp'],array());
        $result &= $this->Execute($this->mSqlQueries['set_aktif_tpp_sebelumnya'],array($this->tppIdSebelumnya));
        $result &= $this->Execute($this->mSqlQueries['update_tpp_buku_besar'],array($this->tppIdSebelumnya));
        $result &= $this->Execute($this->mSqlQueries['update_tpp_tahun_pembukuan'],array($this->tppIdSebelumnya));
        $result &= $this->Execute($this->mSqlQueries['delete_history_tpp_sebelumnya'],array($this->tppIdSebelumnya));
        $result &= $this->Execute($this->mSqlQueries['set_rolledback_tpp_aktif'],array($this->tppId));

        return $result;
    }

    protected function updateSaldoAwalTahunPembukuanSebelumnya(){
        $result = true;
        $get2TahunSebelumnya = $this->getTahunPembukuanSebelumnya($this->tppIdSebelumnya);
        if(!empty($get2TahunSebelumnya)){
            $id2TahunSebelumnya = $get2TahunSebelumnya['tppId'];

            $result &= $this->Execute($this->mSqlQueries['set_saldo_tahun_pembukuan_kosong'],array());

            $result &= $this->Execute($this->mSqlQueries['update_saldo_tahun_pembukuan_sebelumnya'],array(
                $id2TahunSebelumnya
            ));
        }

        return $result;
    }

    protected function insertLog($aktivitas){
        $param = array(
            $this->userId,
            $aktivitas
        );

        return $this->Execute($this->mSqlQueries['insert_log_rollback'], $param);
    }

    public function DoRollback($param){
        $this->StartTrans();
        $result = true;

        $result &= $this->deleteBukuBesar();
        $result &= $this->deleteBukuBesarHis();
        $result &= $this->setSaldoAwalPembukuanAktif();
        $result &= $this->setSaldoAwalTahunPembukuanAktif();
        $result &= $this->rollbackStatusPosting();
        $aktivitas = 'Rollback posting tahun pembukuan aktif';

        if($param['aktif_sebelumnya'] == 'Ya'){
            $this->setIdTahunPembukuan();

            $result &= $this->setAktifTahunPembukuanSebelumnya();
            $result &= $this->updateSaldoAwalTahunPembukuanSebelumnya();
            $aktivitas = 'Rollback dan membuka tahun pembukuan sebelumnya';
        }

        $result &= $this->insertLog($aktivitas);

        return $this->EndTrans($result);
    }

}
