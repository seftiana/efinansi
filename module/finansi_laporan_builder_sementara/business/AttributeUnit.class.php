<?php

/**
 * Class Attribute
 * class ini untuk mengolah kode unit menjadi atribut coa
 * 
 * @package finansi_laporan_builder
 * 
 * @added since Agustus 2018
 * @analyzed diyah fajar <dyah@gamatechno.com>
 * @analyzed urip <urip@gamatechno.com>
 * @author noor hadi <noor.hadi@gamatecno.com>
 * 
 * 
 * @copyright (c) 2009 - 2018, Gamatechno Indonesia
 * 
 */

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/finansi_laporan_builder/business/AttributeUnit.class.php';

class AttributeUnit extends Database {

    public function __construct($connectionNumber = 0) {
        $this->mSqlFile = 'module/finansi_laporan_builder/business/attribute_unit.sql.php';
        parent::__construct($connectionNumber);
    }

    /**
     * _getUnitKerjaInfo
     * @param type $kodeUnit get unit info by kode
     * @return type
     */
    private function _getUnitKerjaInfo($kodeUnit) {
        $return = $this->Open($this->mSqlQueries['get_unit_kerja_info'], array(
            $kodeUnit
        ));
        
        if ($return) {
            return $return[0];
        } else {
            return array(
                'unit_id' => '',
                'unit_kode' => '',
                'unit_nama' => '',
                'unit_parent' => ''
            );
        }
    }

    /**
     * getAttributUnit
     * @param type $kode untuk kode unit
     * @param type $isRegex true| false, default false, untuk menentukan format filter
     * @param type $dash default '' / kosong. menentukan pemisah kode atribut
     * @return type mixed
     */
    public function getAttributUnit($kode,$isRegex = false,$dash = '-' ){
        //get info unit
        $unitInfo = $this->_getUnitKerjaInfo($kode);
        $filterKode = null;
        
        //jika unit parent tertinggi
        //maka juga tampilkan data dibawah nya
        if($unitInfo['unit_parent'] === '0') {
            $getChildKode = $this->Open($this->mSqlQueries['get_child_unit_kerja_kode'],array($unitInfo['unit_id']));
            if(!empty($getChildKode)) {
                $rLike = '';
                foreach ($getChildKode as $key => $itemKode) {
                    if ($key == (count($getChildKode) - 1)) {
                        $rLike .= '^'.str_replace('.',$dash, $itemKode['kode']).$dash;
                    } else {
                        $rLike .= '^'.str_replace('.',$dash, $itemKode['kode']).$dash.'|';
                    }
                    $collKode[$key] = $itemKode['kode'];
                } 
                if($isRegex == true){
                    $filterKode = $rLike;
                } else {
                    $filterKode = $collKode;
                }
            }
        } else {
            if($isRegex == true){
                $filterKode = '^'.str_replace('.',$dash, $kode).$dash;
            } else {
                $filterKode = str_replace('.',$dash, $kode);
            }
        }
        
        return $filterKode;
    }
}
