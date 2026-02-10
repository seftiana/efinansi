<?php
/*
$sql['get_count_data_kegiatanref'] =
   "SELECT
	  count(subprogId) as total
   FROM
      kegiatan_ref a
   LEFT JOIN sub_program ON kegrefSubprogId = subprogId
   LEFT JOIN program_ref ON subprogProgramId = programId
   WHERE
	kegrefNama LIKE '%s'
	AND programId = '%s'
";
*/
$sql['get_count_data_kegiatanref'] =
   "SELECT
      count(a.kegrefId) AS total
    FROM
      kegiatan_ref a
   LEFT JOIN sub_program ON kegrefSubprogId = subprogId
   LEFT JOIN program_ref ON subprogProgramId = programId
    LEFT JOIN finansi_pa_kegiatan_ref_unit_kerja d ON a.`kegrefId` = d.`kegrefId`
   WHERE
	kegrefNama LIKE '%s' 
	AND	unitkerjaId = '%s'
	AND programId = '%s'
";

/*
$sql['get_data_kegiatanref'] =
   "SELECT
	  subprogId as SUBPROGID,
      subprogNama as SUBPROGNAMA,
	  kegrefNomor as KODE,
      a.kegrefId as id,
	  kegrefNama as nama
   FROM
      kegiatan_ref a
   LEFT JOIN sub_program ON kegrefSubprogId = subprogId
   LEFT JOIN program_ref ON subprogProgramId = programId
   WHERE
	kegrefNama LIKE '%s'
	AND programId = '%s'
	LIMIT %s, %s
	
";*/

$sql['get_data_kegiatanref'] ="
SELECT
      IFNULL(ik.kegiatanIkIkId,'') AS ik_id,
      IFNULL(kik.ikNama,'') AS ik_nama,
	  subprogId as subprogid,
      subprogNama as subprognama,
	  kegrefNomor as kode,
      a.kegrefId as id,
	  kegrefNama as nama
FROM
      kegiatan_ref a
LEFT JOIN sub_program ON kegrefSubprogId = subprogId
LEFT JOIN program_ref ON subprogProgramId = programId
LEFT JOIN finansi_pa_kegiatan_ref_unit_kerja d ON a.kegrefId = d.kegrefId
LEFT JOIN finansi_pa_kegiatan_ik ik ON ik.kegiatanIkKegrefId = a.kegrefId
LEFT JOIN finansi_pa_ref_ik kik ON kik.ikId = ik.kegiatanIkIkId
WHERE
    %s
	kegrefNama LIKE '%s'
	AND unitkerjaId = '%s'
	AND programId = '%s'
	LIMIT %s, %s
";

$sql['get_unit'] = "
SELECT unitkerjaId AS id, 
unitkerjaNama AS nama, 
unitkerjaKode AS kode,
`unitKerjaJenisId` AS jenis,
`unitkerjaParentId` AS parent,
unitkerjaKodeSistem as kodeSistem        
FROM user_unit_kerja AS uk 
LEFT JOIN unit_kerja_ref AS ukr 
ON uk.userunitkerjaUnitkerjaId = unitkerjaId 
WHERE uk.userunitkerjaUserId = '%s'
";
?>