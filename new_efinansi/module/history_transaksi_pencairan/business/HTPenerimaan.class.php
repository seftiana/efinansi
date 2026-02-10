<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
				'module/history_transaksi_pencairan/business/HTTransaksi.class.php';

/**
 * class HTPenerimaan
 * proses sama dengan class HTTransaksi
 */

class HTPenerimaan extends HTTransaksi
{
	protected $mSqlFile= 'module/history_transaksi_pencairan/business/ht_transaksi_penerimaan.sql.php';
}