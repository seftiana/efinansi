<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
				'module/history_transaksi/business/HTTransaksi.class.php';
				
/**
 * class HTPengeluaran
 * proses sama dengan class HTTransaksi
 */
 
class HTPengeluaran extends HTTransaksi 
{
	protected $mSqlFile= 'module/history_transaksi/business/ht_transaksi_pengeluaran.sql.php';
}