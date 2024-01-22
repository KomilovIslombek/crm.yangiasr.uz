<?php
if ($_SERVER['HTTP_HOST'] == "crm.yangiasr.uz" || $_SERVER['HTTP_HOST'] == "www.crm.yangiasr.uz") {
	return array (
	  'pdo' => 
		array (
			'db_host' => 'localhost',
			'db_name' => 'crm_yangiasr',
			'db_user' => 'crm_yangiasr',
			'db_pass' => 'fRaZCRzKrPgeeMt5',
		),
	);
} else if ($_SERVER['HTTP_HOST'] == "old.yangiasr.uz" || $_SERVER['HTTP_HOST'] == "www.old.yangiasr.uz") {
	return array (
	  'pdo' => 
		array (
			'db_host' => 'localhost',
			'db_name' => 'crm_yangiasr_old',
			'db_user' => 'crm_yangiasr',
			'db_pass' => 'fRaZCRzKrPgeeMt5',
		),
	);
} else {
	return array (
		'pdo' => 
		array (
			'db_host' => 'localhost',
			'db_name' => 'crm_yangi_asr',
			'db_user' => 'root',
			'db_pass' => '',
		),
	);
}