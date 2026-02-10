<?php

//===GET===
$sql['get_count_data'] = "
   SELECT FOUND_ROWS() as total
";

$sql['get_data'] = "
   SELECT
      SQL_CALC_FOUND_ROWS
      transId as id,
      transTanggalEntri as tanggal,
      transReferensi as kkb,
      transjenNama as jenis,
      ttNamaTransaksi as tipe,
      transTtId as tipe_id,
      transCatatan as uraian,
      transNilai as nominal,
      mp.map_nama as map,
      transPenanggungJawabNama as penanggung_jawab,
      transIsJurnal as is_jurnal
   FROM 
      transaksi
      INNER JOIN transaksi_tipe_ref ON (ttId = transTtId)
      INNER JOIN transaksi_jenis_ref ON (transjenId = transTransjenId)
      INNER JOIN 
      		(
				SELECT
	        			kodeterimaKode AS kode,
						kodeterimaNama AS map_nama,
						tr.transId AS trans_id
				FROM rencana_penerimaan rp
					LEFT JOIN kode_penerimaan_ref kpr ON kpr.kodeterimaId = rp.renterimaKodeterimaId
					LEFT JOIN realisasi_penerimaan rlp	ON rlp.realrenterimaId = rp.renterimaId
					LEFT JOIN transaksi tr	ON tr.transId = rlp.realterimaTransId
				WHERE
						tr.transId = transId
     		 ) AS mp ON mp.trans_id = transId
   WHERE
      transTtId = 1 AND transTransjenId = 5
      AND transTanggalEntri BETWEEN '%s' AND '%s'
      AND transReferensi LIKE '%s' 
      AND transIsJurnal LIKE '%s'
      %s
   ORDER BY tanggal DESC
   LIMIT %s, %s
";

$sql['get_daftar_map'] = "
SELECT
 	SQL_CALC_FOUND_ROWS
	renterimaId    AS id,
	kodeterimaKode AS kode,
	kodeterimaNama AS nama
FROM rencana_penerimaan
	LEFT JOIN kode_penerimaan_ref
		ON kodeterimaId = renterimaKodeterimaId
	LEFT JOIN realisasi_penerimaan
		ON realrenterimaId = renterimaId
	LEFT JOIN transaksi
		ON transId = realterimaTransId
WHERE 
	kodeterimaNama LIKE '%s'
	AND 
	renterimaRpstatusId='2'
GROUP BY kodeterimaKode
LIMIT %s, %s
";
