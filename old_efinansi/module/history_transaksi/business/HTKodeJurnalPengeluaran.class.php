<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
				'module/history_transaksi/business/HTKodeJurnal.class.php';

/**
 * class HTKodeJurnalPengeluaran
 * proses sama dengan class HTkodeJurnal
 */
 
class HTKodeJurnalPengeluaran extends HTKodeJurnal 
{
	protected $mSqlFile= 'module/history_transaksi/business/ht_kode_jurnal_pengeluaran.sql.php';
}