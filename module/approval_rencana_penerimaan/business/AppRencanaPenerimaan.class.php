<?php

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/additional_lib/business/SessionFilterURI.class.php';

class AppRencanaPenerimaan extends Database {

    protected $mSqlFile = 'module/approval_rencana_penerimaan/business/apprencanapenerimaan.sql.php';

    function __construct($connectionNumber = 0) {
        parent::__construct($connectionNumber);
        //$this->setdebugOn();
    }

    function GetCountData($kodenama, $tahunAnggaran, $unitkerja = '', $approval) {
        /**
          if($unitkerja != "") {
          $str_unitkerja = " AND (unitkerjaId=$unitkerja OR tempUnitId=$unitkerja) ";
          } else {
          $str_unitkerja = "";
          }
         */
        if ($approval != 'all' && $approval != '')
            $approval_str = " AND renterimaRpstatusId='" . $approval . "' ";
        else
            $approval_str = "";

        $sql = sprintf($this->mSqlQueries['get_count_data'], $tahunAnggaran, $tahunAnggaran, '%'.$kodenama.'%', "%" . $kodenama . "%", $unitkerja, '%', $unitkerja, $approval_str);
        $data = $this->Open($sql, array());

        if (!$data) {
            return 0;
        } else {
            return $data[0]['total'];
        }
    }

    //yg dipake ini----
    function GetDataUnitkerja($kodenama, $tahunAnggaran, $unitkerja = '', $approval, $startRec, $itemView) {
        if ($approval != 'all' && $approval != '')
            $approval_str = " AND renterimaRpstatusId=$approval ";
        else
            $approval_str = "";

        $sql = sprintf($this->mSqlQueries['get_data_unitkerja'], $tahunAnggaran, $tahunAnggaran,'%'. $kodenama.'%', "%" . $kodenama . "%", $unitkerja, '%', $unitkerja, $approval_str, $startRec, $itemView);
        $result = $this->Open($sql, array());
        //file_put_contents('C:/test.txt', print_r($this->getLastError(),1));
        return $result;
    }

    //-------

    function GetDataRencanaPenerimaanById($id) {
        $result = $this->Open($this->mSqlQueries['get_data_rencana_penerimaan_by_id'], array($id));
        return $result;
    }

    //get combo tahun anggaran
    function GetComboTahunAnggaran() {
        $result = $this->Open($this->mSqlQueries['get_combo_tahun_anggaran'], array());
        return $result;
    }

    function GetTahunAnggaranAktif() {
        $result = $this->Open($this->mSqlQueries['get_tahun_anggaran_aktif'], array());
        return $result[0];
    }

    function GetTahunAnggaran($id) {
        $result = $this->Open($this->mSqlQueries['get_tahun_anggaran'], array($id));
        return $result[0];
    }

//===DO==

    function DoUpdateRencanaPenerimaan($note, $approval, $id) {
        $note = ($note != '') ? $note : NULL;
        $result = $this->Execute($this->mSqlQueries['do_update_rencana_penerimaan'], array($note, $approval, $id));
        //file_put_contents('C:/test.txt', print_r($this->getLastError(),1));
        return $result;
    }

    /* function DoDeleteRencanaPenerimaanById($id) {
      $result=$this->Execute($this->mSqlQueries['do_delete_rencana_penerimaan_by_id'], array($id));
      return $result;
      }

      function DoDeleteRencanaPenerimaanByArrayId($arrId) {
      $id = implode("', '", $arrId);
      $result = $this->Execute($this->mSqlQueries['do_delete_rencana_penerimaan_by_array_id'], array($id));
      return $result;
      } */

    function GetStatusApproval() {
        $result = $this->Open($this->mSqlQueries['status_approval'], array());
        return $result;
    }

    /**
     * untuk mendapatkan total sub unit
     * @since 11 Januari 2012
     */
    public function GetTotalSubUnitKerja($parentId) {
        $result = $this->Open($this->mSqlQueries['get_total_sub_unit_kerja'], array($parentId));
        return $result[0]['total'];
    }

    function GetStatusApprovalNama($status_id) {
        $result = $this->Open($this->mSqlQueries['get_status_approval'], array($status_id));
        return $result[0];
    }

}
