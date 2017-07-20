<?php
$upl = wp_upload_dir();
//
// V1.2 (COUNTRY MULTILANG)
//
$q = $wpdb->get_var('SELECT c_liste_categ FROM '.$wpdb->prefix.'rencontre_liste WHERE c_liste_categ="Pays" LIMIT 1');
if($q)
	{
	$wpdb->query("TRUNCATE TABLE ".$wpdb->prefix."rencontre_liste"); // empty
	$wpdb->query("ALTER TABLE ".$wpdb->prefix."rencontre_liste DROP COLUMN i_liste_lien ");
	$wpdb->query("ALTER TABLE ".$wpdb->prefix."rencontre_liste ADD `c_liste_iso` varchar(2) NOT NULL");
	$wpdb->query("ALTER TABLE ".$wpdb->prefix."rencontre_liste ADD `c_liste_lang` varchar(2) NOT NULL");
	}
//
// V1.4 (GPS)
//
$q = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix."rencontre_users LIKE 'e_lat' ");
if(!$q)
	{
	$wpdb->query("ALTER TABLE ".$wpdb->prefix."rencontre_users 
		ADD `e_lat` decimal(10,5) NOT NULL,
		ADD `e_lon` decimal(10,5) NOT NULL,
		ADD `d_session` datetime NOT NULL");
	}
//
// V1.7 (DUPLICATE ID)
//
$unique = $wpdb->get_results("SHOW INDEXES FROM ".$wpdb->prefix."rencontre_users WHERE Column_name='user_id' AND NOT Non_unique"); // unique => 1
if(!$unique)
	{
	$q = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."rencontre_users ORDER BY user_id");
	$id = -1;
	if($q) foreach($q as $r)
		{
		if($r->user_id==$id) // Duplicate
			{
			$wpdb->delete($wpdb->prefix.'rencontre_users', array('user_id'=>$id));
			$wpdb->insert($wpdb->prefix.'rencontre_users', array(
				'user_id'=>$id,
				'c_ip'=>$r->c_ip,
				'c_pays'=>$r->c_pays,
				'c_region'=>$r->c_region,
				'c_ville'=>$r->c_ville,
				'e_lat'=>$r->e_lat,
				'e_lon'=>$r->e_lon,
				'i_sex'=>$r->i_sex,
				'd_naissance'=>$r->d_naissance,
				'i_taille'=>$r->i_taille,
				'i_poids'=>$r->i_poids,
				'i_zsex'=>$r->i_zsex,
				'i_zage_min'=>$r->i_zage_min,
				'i_zage_max'=>$r->i_zage_max,
				'i_zrelation'=>$r->i_zrelation,
				'i_photo'=>$r->i_photo,
				'd_session'=>$r->d_session
				));
			}
		$id = $r->user_id;
		}
	$q = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."rencontre_users_profil ORDER BY user_id");
	$id = -1;
	if($q) foreach($q as $r)
		{
		if($r->user_id==$id) // Duplicate
			{
			$wpdb->delete($wpdb->prefix.'rencontre_users_profil', array('user_id'=>$id));
			$wpdb->insert($wpdb->prefix.'rencontre_users_profil', array(
				'user_id'=>$id,
				'd_modif'=>$r->d_modif,
				't_titre'=>$r->t_titre,
				't_annonce'=>$r->t_annonce,
				't_profil'=>$r->t_profil,
				't_action'=>$r->t_action,
				't_signal'=>$r->t_signal
				));
			}
		$id = $r->user_id;
		}
	$wpdb->query("ALTER TABLE ".$wpdb->prefix."rencontre_users ADD UNIQUE(`user_id`)");
	$wpdb->query("ALTER TABLE ".$wpdb->prefix."rencontre_users_profil ADD UNIQUE(`user_id`)");
	}
//
// V1.7.5 (ORDER in PROFILE)
//
$q = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix."rencontre_profil LIKE 'i_categ' ");
if(!$q)
	{
	$wpdb->query("ALTER TABLE ".$wpdb->prefix."rencontre_profil ADD `i_categ` tinyint unsigned NOT NULL AFTER `id`");
	$wpdb->query("ALTER TABLE ".$wpdb->prefix."rencontre_profil ADD `i_label` tinyint unsigned NOT NULL AFTER `i_categ`");
	$q = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."rencontre_profil ORDER BY c_categ");
	$c = 1; $l = 1; $lang = ''; $categ = '';
	foreach($q as $r)
		{
		if(!$lang) $lang = $r->c_lang;
		if(!$categ) $categ = $r->c_categ;
		if($r->c_lang==$lang)
			{
			if($r->c_categ!=$categ)
				{
				++$c; $l = 1;
				$categ = $r->c_categ;
				}
			$wpdb->update($wpdb->prefix.'rencontre_profil', array('i_categ'=>$c,'i_label'=>$l), array('id'=>$r->id));
			++$l;
			}
		}
	}
//
// V1.7.8 (DBIP-COUNTRY)
//
$nom = $wpdb->prefix . 'rencontre_dbip';
if($wpdb->get_var("SHOW TABLES LIKE '$nom'")!=$nom)
	{
	if (function_exists('geoip_detect_get_info_from_ip'))
		{
		function ipdb() { echo '<div class="update-nag"><p>Plugin <strong>Rencontre</strong> - '.__('Rencontre no longer uses "GeoIP Detect". You can disable and delete this plugin.','rencontre').'</p></div>'; }
		add_action('admin_notices', 'ipdb');
		}
	if(!empty($wpdb->charset)) $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
	if(!empty($wpdb->collate)) $charset_collate .= " COLLATE $wpdb->collate";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' ); // pour utiliser dbDelta()
	$sql = "CREATE TABLE ".$nom." (
		`ip_start` bigint unsigned NOT NULL,
		`ip_end` bigint unsigned NOT NULL,
		`country` char(2) NOT NULL,
		PRIMARY KEY (`ip_start`)
		) $charset_collate;";
	dbDelta($sql); // necessite wp-admin/includes/upgrade.php
	}
//
// V1.8 (MULTI-CHOICE in SEX SEARCH)
//
$q = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix."rencontre_users LIKE 'c_zsex' ");
if(!$q)
	{
	$wpdb->query('ALTER TABLE '.$wpdb->prefix.'rencontre_users ADD `c_zsex` VARCHAR(50) NOT NULL AFTER `i_zsex`');
	$wpdb->query('ALTER TABLE '.$wpdb->prefix.'rencontre_users ADD `c_zrelation` VARCHAR(50) NOT NULL AFTER `i_zrelation`');
	}
//
// V1.9.12 (PROFIL BY GENRE)
//
$q = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix."rencontre_profil LIKE 'c_genre' ");
if(!$q)
	{
	$wpdb->query("ALTER TABLE ".$wpdb->prefix."rencontre_profil 
		ADD `c_genre` varchar(255) DEFAULT 0");
	}
//
// V1.9.8 (REMOVE SUBJECT IN MSG) - later (rencontreP)
//
	//$q = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."rencontre_msg");
	//if($q && isset($q->subject))
	//	{
	//	$wpdb->query('ALTER TABLE '.$wpdb->prefix.'rencontre_msg DROP COLUMN `subject`');
	//	}
//
// V1.10 (USERS USER_STATUS => RENCONTRE_USERS I_STATUS)
//
$q = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix."rencontre_users LIKE 'i_status' ");
if(!$q)
	{
	$wpdb->query("ALTER TABLE ".$wpdb->prefix."rencontre_users ADD `i_status` tinyint unsigned NOT NULL DEFAULT 0 ");
	$q = $wpdb->get_results("SELECT ID, user_status FROM ".$wpdb->prefix."users WHERE user_status!=0");
	foreach($q as $r)
		{
		$wpdb->update($wpdb->prefix.'rencontre_users', array('i_status'=>$r->user_status), array('user_id'=>$r->ID));
		}
	}
//
// V1.10.4 Pass i_status=0 to regular members - error from V1.10
$q = $wpdb->get_results("SELECT 
		R.user_id 
	FROM
		".$wpdb->prefix."rencontre_users R
	WHERE
		R.i_status=4 and
		R.i_sex!='98' and
		NOT EXISTS (
			SELECT 
				M.umeta_id 
			FROM 
				".$wpdb->prefix."usermeta M 
			WHERE 
				M.user_id=R.user_id and
				M.meta_key='rencontre_confirm_email')
	");
foreach($q as $r)
	{
	$wpdb->update($wpdb->prefix.'rencontre_users', array('i_status'=>0), array('user_id'=>$r->user_id));
	}
//
// V2.1
//
	// RAZ fiche libre (CSS)
	if(file_exists($upl['basedir'].'/portrait/cache/cache_portraits_accueil.html')) @unlink($upl['basedir'].'/portrait/cache/cache_portraits_accueil.html');
	if(file_exists($upl['basedir'].'/portrait/cache/cache_portraits_accueil1.html')) @unlink($upl['basedir'].'/portrait/cache/cache_portraits_accueil1.html');
	if(file_exists($upl['basedir'].'/portrait/cache/cache_portraits_accueilgirl.html')) @unlink($upl['basedir'].'/portrait/cache/cache_portraits_accueilgirl.html');
	if(file_exists($upl['basedir'].'/portrait/cache/cache_portraits_accueilmen.html')) @unlink($upl['basedir'].'/portrait/cache/cache_portraits_accueilmen.html');
	//
	// REPLACE SHORTCODE IN PAGE
	$a = array(
		'[rencontre_libre_mix]'=>'[rencontre_libre gen=mix]',
		'[rencontre_libre_girl]'=>'[rencontre_libre gen=girl]',
		'[rencontre_libre_men]'=>'[rencontre_libre gen=men]',
		'[rencontre_nbmembre_girl]'=>'[rencontre_nbmembre gen=girl]',
		'[rencontre_nbmembre_men]'=>'[rencontre_nbmembre gen=men]',
		'[rencontre_nbmembre_girlphoto]'=>'[rencontre_nbmembre gen=girl ph=1]',
		'[rencontre_nbmembre_menphoto]'=>'[rencontre_nbmembre gen=men ph=1]');
	$ps = get_posts(array('post_type'=>'page','numberposts'=>-1));
	foreach($ps as $p)
		{
		$b = 0;
		foreach($a as $k=>$v)
			{
			if(strpos($p->post_content,$k)!==false)
				{
				$p->post_content = str_replace($k, $v, $p->post_content);
				$b = 1;
				var_dump($p);
				}
			}
		if($b) $wpdb->update($wpdb->prefix.'posts', array('post_content'=>$p->post_content), array('ID'=>$p->ID));
		}
	//
	// PRISON ADD TYPE
	$q = $wpdb->query("SHOW COLUMNS FROM ".$wpdb->prefix."rencontre_prison LIKE 'i_type' ");
	if(!$q) $wpdb->query("ALTER TABLE ".$wpdb->prefix."rencontre_prison ADD `i_type` tinyint unsigned NOT NULL AFTER `c_ip`");
//
// *******************************************************************************************************************

//
// PERMANENT : Update DBIP
//
$versionDBIP = "201612"; // new version with this update
$a = get_option('rencontre_dbip');
$b = $wpdb->get_var("SELECT ip_end FROM ".$wpdb->prefix."rencontre_dbip LIMIT 1"); // empty ?
if(file_exists(dirname(__FILE__).'/dbip-country.csv.gz') && (!$a || intval($a)<intval($versionDBIP) || !$b))
	{
	// 1. Extract in array
//	$t = gzfile(dirname(__FILE__).'/dbip-country.csv.gz'); // OK but ERROR with big file
	if(file_exists(dirname(__FILE__).'/dbip-country.csv')) unlink(dirname(__FILE__).'/dbip-country.csv');
	$gzp = gzopen(dirname(__FILE__).'/dbip-country.csv.gz', "rb");
	$fp = fopen(dirname(__FILE__).'/dbip-country.csv', "w");
	while(!gzeof($gzp))
		{
		$s = gzread($gzp, 4096);
		fwrite($fp, $s, strlen($s));
		}
	gzclose($gzp);
	fclose($fp);
	$f = file_get_contents(dirname(__FILE__).'/dbip-country.csv');
	$fp = fopen(dirname(__FILE__).'/dbip-country.csv', "r");
	// 2. Empty DB
	$wpdb->query("TRUNCATE TABLE ".$wpdb->prefix."rencontre_dbip");
			//	$q = "LOAD DATA LOCAL INFILE '".dirname(__FILE__)."/dbip-country.csv' 
			//		INTO TABLE ".$wpdb->prefix."rencontre_dbip 
			//		FIELDS TERMINATED BY ',' 
			//		ENCLOSED BY '\"' 
			//		LINES TERMINATED BY '".PHP_EOL."' 
			//		IGNORE 1 LINES";
			//	$wpdb->query($q);
	// 3. Update DB
	$sql = array();
	$c = 0;
	while(($tt = fgets($fp,4096))!==false)
		{
		if(strlen($tt)>50) continue;
		$a = explode(',', str_replace('"', '', $tt));
		if(isset($a[2]))
			{
			if(preg_match('/^[0-9\.]+$/', $a[0]))
				{
				++$c;
				$a0=''; $a1='';
				$b = explode('.', $a[0]);
				foreach($b as $r)
					{
					if(strlen($r)==1 && $a0) $a0.='00'.$r;
					else if (strlen($r)==2 && $a0) $a0.='0'.$r;
					else if($r!='0') $a0.=$r;
					}
				$b = explode('.', $a[1]);
				foreach($b as $r) 
					{
					if(strlen($r)==1 && $a1) $a1.='00'.$r;
					else if (strlen($r)==2 && $a1) $a1.='0'.$r;
					else if($r!='0') $a1.=$r;
					}
				if($a0 && $a1) $sql[] = '("'.$a0.'", "'.$a1.'", "'.substr($a[2],0,2).'")';
				if($c>20000)
					{
					if(count($sql)) $wpdb->query('INSERT IGNORE INTO '.$wpdb->prefix.'rencontre_dbip (ip_start, ip_end, country) VALUES '.implode(',', $sql));
					$sql = array();
					$c = 0;
					}
				}
			}
		}
	fclose($fp);
	if(count($sql)) $wpdb->query('INSERT IGNORE INTO '.$wpdb->prefix.'rencontre_dbip (ip_start, ip_end, country) VALUES '.implode(',', $sql));
	// 4. Save and clean
	update_option('rencontre_dbip', $versionDBIP);
	@copy(dirname(__FILE__).'/dbip-country.csv.gz', dirname(__FILE__).'/dbip-country_off.csv.gz');
	@unlink(dirname(__FILE__).'/dbip-country.csv.gz');
	if(file_exists(dirname(__FILE__).'/dbip-country.csv')) unlink(dirname(__FILE__).'/dbip-country.csv');
	}
//
// END PATCH - PATCH OFF
//
@copy(dirname(__FILE__).'/patch.php', dirname(__FILE__).'/patch_off.php');
@unlink(dirname(__FILE__).'/patch.php');
?>
