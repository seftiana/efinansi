<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
				'module/history_transaksi/business/HTKodeJurnal.class.php';

/**
 * class HTKodeJurnalPenerimaan
 * proses sama dengan class HTkodeJurnal
 */
 
class HTKodeJurnalPenerimaan extends HTKodeJurnal 
{
	protected $mSqlFile= 'module/history_transaksi/business/ht_kode_jurnal_penerimaan.sql.php';
}