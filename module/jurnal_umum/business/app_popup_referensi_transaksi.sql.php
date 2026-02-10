<?php

$sql['get_data']="
SELECT
  transId AS id,
  transDueDate AS tanggal,
  transReferensi AS referensi,
  transCatatan AS catatan,
  transNilai AS nilai,
  transIsJurnal
FROM
  transaksi
WHERE
  transIsJurnal != 'Y' AND
  transTtId IN(3,6) AND
  transReferensi LIKE %s
LIMIT %s,%s
";


$sql['get_count']="
SELECT
  COUNT(transId) AS total

FROM
  transaksi
WHERE
  transIsJurnal != 'Y' AND
  transTtId IN(3,6) AND
  transReferensi LIKE %s
LIMIT 1
";



?>
