<?php
$sql['get_count_data'] = "
   SELECT FOUND_ROWS() as total
";

$sql['get_data'] = "
SELECT SQL_CALC_FOUND_ROWS
   renterimaId AS id,
   kodeTerimaKode AS kode,
   kodeTerimaNama AS nama,
   IFNULL(renterimaDeskripsi, '-') AS keterangan,
   renterimaTotalTerima AS nominal_aprove,
   IFNULL(realisasi.nominal, 0) AS nominal_realisasi
FROM
   rencana_penerimaan
   LEFT JOIN kode_penerimaan_ref
      ON kodeterimaId = renterimaKodeterimaId
   LEFT JOIN realisasi_penerimaan
      ON realrenterimaId = renterimaId
   LEFT JOIN unit_kerja_ref
      ON unitkerjaid = renterimaUnitkerjaId
   LEFT JOIN (SELECT realrenterimaId AS id,
      SUM(realterimaTotalTerima) AS nominal
   FROM realisasi_penerimaan
   GROUP BY realrenterimaId
   ) AS realisasi ON realisasi.id = renterimaId
WHERE 1 = 1
   AND renterimaRpstatusId = '2'
   AND unitkerjaId = '%s'
   AND kodeterimaNama LIKE '%s'
GROUP BY renterimaId
LIMIT %s, %s
";
?>