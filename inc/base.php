<?php
// *****************************************
// **** ONGLET GENERAL
// *****************************************
function f_exportCsv()
	{
	// Export CSV de la base des membres
	// $_POST['activ'], $_POST['photo'], $_POST['ad'];
	if(!is_admin()) die;
	global $wpdb; global $rencDiv;
	$q = $wpdb->get_results("SELECT
			U.ID,
			U.user_login,
			U.user_pass,
			U.user_email,
			U.user_registered,
			R.c_ip,
			R.c_pays,
			R.c_region,
			R.c_ville,
			R.i_sex,
			R.d_naissance,
			R.i_taille,
			R.i_poids,
			R.i_zsex,
			R.c_zsex,
			R.i_zage_min,
			R.i_zage_max,
			R.i_zrelation,
			R.c_zrelation,
			R.i_photo,
			P.t_titre,
			P.t_annonce
		FROM 
			".$wpdb->prefix."users U,
			".$wpdb->prefix."rencontre_users R,
			".$wpdb->prefix."rencontre_users_profil P
		WHERE 
			U.ID=R.user_id and 
			R.user_id=P.user_id and
			R.i_status=0
			".(!empty($_POST['photo'])?" and R.i_photo!=0 ":"")."
			".(!empty($_POST['ad'])?" and CHAR_LENGTH(P.t_titre)>4 and CHAR_LENGTH(P.t_annonce)>30 ":"")."
			".($_POST['activ']!=0?" and R.d_session>'".date("Y-m-d H:i:s",mktime()-2628000*intval($_POST['activ']))."'":"")."
		");
	$rd = mt_rand();
	$d = $rencDiv['basedir'].'/tmp/';
	if(!is_dir($d)) mkdir($d);
	if(is_dir($d.'photo_export/'))
		{
		array_map('unlink', glob($d."photo_export/*.*"));
		}
	else mkdir($d.'photo_export/');
	$t = fopen($d.'index.php', 'w');
	fclose($t);
	$t = fopen($d.$rd.'export_rencontre.csv', 'w');
	fputcsv($t, array(
		'user_login',
		'user_pass (MD5)',
		'user_email',
		'user_registered (AAAA-MM-DD HH:MM:SS)',
		'c_ip',
		'c_pays (2 letters ISO)',
		'c_region',
		'c_ville',
		'i_sex (girl, men)',
		'd_naissance (AAAA-MM-DD)',
		'i_taille',
		'i_poids',
		'i_zsex (girl, men)',
		'i_zage_min',
		'i_zage_max',
		'i_zrelation (open, friendly, serious)',
		'i_photo',
		't_titre',
		't_annonce'
		));
	foreach($q as $r)
		{
		fputcsv($t, array(
			"'".$r->user_login."'",
			"'".$r->user_pass."'",
			"'".$r->user_email."'",
			"'".$r->user_registered."'",
			"'".($r->c_ip?$r->c_ip:'127.0.0.1')."'",
			"'".$r->c_pays."'",
			"'".$r->c_region."'",
			"'".$r->c_ville."'",
			"'".(($r->i_sex)?'girl':'men')."'",
			"'".$r->d_naissance."'",
			"'".$r->i_taille."'",
			"'".$r->i_poids."'",
			"'".(($r->i_zsex)?'girl':'men')."'",
			"'".$r->i_zage_min."'",
			"'".$r->i_zage_max."'",
			"'".(($r->i_zrelation)?(($r->i_zrelation==1)?'open':'friendly'):'serious')."'",
			"'".(($r->i_photo)?(($r->ID)*10).'.jpg':'0')."'",
			"'".$r->t_titre."'",
			"'".$r->t_annonce."'"
			),chr(9));
		if($r->i_photo) @copy($rencDiv['basedir'].'/portrait/'.floor(($r->ID)/1000).'/'.Rencontre::f_img((($r->ID)*10)).'.jpg', $rencDiv['basedir'].'/tmp/photo_export/'.($r->ID*10).'.jpg');
		}
	fclose($t);
	echo $rd;
	}
function f_importCsv()
	{
	// Import CSV de la base des membres
		// 0 : login
		// 1 : pass MD5
		// 2 : email
		// 3 : user_registered (AAAA-MM-JJ HH:MM:SS)
		// 4 : IP
		// 5 : Pays 2 lettres MAJ
		// 6 : Region
		// 7 : Ville
		// 8 : sex (men / girl)
		// 9 : date naissance AAAA-MM-JJ
		// 10 : taille
		// 11 : poids
		// 12 : sex recherche (men / girl)
		// 13 : age min recherche
		// 14 : age max recherche
		// 15 : type de relation recherche (open / friendly / serious)
		// 16 : fichier photo (ou 0)
		// 17 : titre
		// 18 : Annonce
	if(!is_admin()) die;
	global $rencDiv;
	$d = $rencDiv['basedir'].'/tmp/import_rencontre.csv';
	if(isset($_POST['cas'])) switch ($_POST['cas'])
		{
		// First opening : count
		case 3:
			$c =0;
			$t = fopen($d,'r');
			if($t)while(($a=fgetcsv($t,3000,"\t"))!==FALSE) ++$c;
			fclose($t);
			if($c) echo ' : '.$c.'&nbsp;'.__('lines','rencontre');
		break;
		// Next opening : read
		case 2:
			global $wpdb; global $rencDiv;
			$p = 0;
			if(is_dir($rencDiv['basedir'].'/tmp/photo_import/'))
				{
				$p = 1;
				@chmod($rencDiv['basedir'].'/tmp/photo_import/',0777);
				}
			ini_set('auto_detect_line_endings',TRUE); // Mac
			$t = fopen($d,'r');
			$c = 0; $c1 = 0;
			while(($a=fgetcsv($t,3000,"\t"))!==FALSE)
				{
				if($a===false || $a===null || !isset($a[0]) || !isset($a[1]) || !isset($a[2])) continue;
				foreach($a as $k=>$r)
					{
					if(substr($r,0,1)=="'") $a[$k]=substr($r,1,-1); // suppression des guillemets
					}
				if($c) // not first line
					{
					$q = $wpdb->get_var("SELECT ID
						FROM
							".$wpdb->prefix."users
						WHERE
							user_login='".$a[0]."' or
							user_email='".$a[2]."'
						LIMIT 1 "
						);
					if(!$q && preg_match("/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/",$a[2])) // pas la premiere ligne - pas de doublon
						{
						$j = DateTime::createFromFormat('Y-m-d H:i:s', (!empty($a[3])?$a[3]:date("Y-m-d H:i:s"))); // $j
						if(is_object($j)) $j = $j->format('Y-m-d H:i:s');
						else $j = date("Y-m-d H:i:s");
						$n = DateTime::createFromFormat('Y-m-d', (!empty($a[9])?$a[9]:date("Y-m-d", 631180800))); // $n
						if(is_object($n)) $n = $n->format('Y-m-d');
						else $n = date("Y-m-d", 631180800); // 1990-01-01;
						$s = $wpdb->insert($wpdb->prefix.'users',array(
							'user_login'=>str_replace("'","",$a[0]),
							'user_pass'=>(strlen($a[1])>31?$a[1]:md5('123456')),
							'user_nicename'=>$a[0],
							'user_email'=>$a[2],
							'user_registered'=>$j,
							'display_name'=>$a[0]
							));
						if($s)
							{
							$id = $wpdb->insert_id;
							$s1 = $wpdb->insert($wpdb->prefix.'rencontre_users',array(
								'user_id'=>$id,
								'c_ip'=>(!empty($a[4])?$a[4]:'127.0.0.1'),
								'c_pays'=>(!empty($a[5])?$a[5]:'FR'),
								'c_region'=>(!empty($a[6])?$a[6]:''),
								'c_ville'=>(!empty($a[7])?$a[7]:''),
								'i_sex'=>(isset($a[8])?($a[8]=='men'?0:1):0),
								'd_naissance'=>$n,
								'i_taille'=>(!empty($a[10])?intval($a[10]):170),
								'i_poids'=>(!empty($a[11])?intval($a[11]):65),
								'i_zsex'=>(isset($a[12])?($a[12]=='girl'?1:0):1),
								'c_zsex'=>',',
								'i_zage_min'=>(isset($a[13])?intval($a[13]):18),
								'i_zage_max'=>(!empty($a[14])?intval($a[14]):99),
								'i_zrelation'=>(isset($a[15])?($a[15]=='serious'?0:($a[15]=='open'?1:2)):0), // ( serious (0) / open (1) / friendly (2))
								'c_zrelation'=>',',
								'i_photo'=>0,
								'i_status'=>0
								));
							if($s1) $s2 = $wpdb->insert($wpdb->prefix.'rencontre_users_profil',array(
								'user_id'=>$id,
								'd_modif'=>date("Y-m-d H:i:s"),
								't_titre'=>(!empty($a[17])?$a[17]:''),
								't_annonce'=>(!empty($a[18])?$a[18]:''),
								't_profil'=>'[]'
								));
							if(!$s2)
								{
								if($id) $wpdb->delete($wpdb->prefix.'users', array('ID'=>$id));
								if($s1) $wpdb->delete($wpdb->prefix.'rencontre_users', array('user_id'=>$id));
								continue; // INVALIDE
								}
							}
						if($p && !empty($a[16]) && strlen($a[16])>3 && file_exists($rencDiv['basedir'].'/tmp/photo_import/'.$a[16]))
							{
							$t1 = fopen($rencDiv['basedir'].'/tmp/photo_import/'.$id.'.txt', 'w+');
							fwrite($t1,$a[16],40);
							fclose($t1);
							++$c1;
							}
						}
					}
				++$c;
				}
			ini_set('auto_detect_line_endings',FALSE); // Mac
			fclose($t);
			@unlink($rencDiv['basedir'].'/portrait/cache/cache_portraits_accueil.html');
			if(!empty($cr))
				{
				echo $cr;
				$cr = 0;
				}
			else echo (($c1)?$c1:999999);
		break;
		// Last phase : photo
		case 1:
			global $wpdb; global $rencDiv;
			$p = (is_dir($rencDiv['basedir'].'/tmp/photo_import/')?$rencDiv['basedir'].'/tmp/photo_import/':0);
			if(!is_dir($rencDiv['basedir'].'/portrait/')) @mkdir($rencDiv['basedir'].'/portrait/');
			$tab = '';
			if($p && $dh=opendir($p))
				{
				$c = 0;
				while (($file=readdir($dh))!==false)
					{
					$ext = explode('.',$file);
					$ext = $ext[count($ext)-1];
					if($ext=='txt' && $file!='.' && $file!='..')
						{
						$t = fopen($p.$file, 'r');
						$img = fread($t,filesize($p.$file));
						fclose($t);
						RencontreWidget::f_photo(intval(substr($file,0,-4).'0'),$p.$img);
						$wpdb->update($wpdb->prefix.'rencontre_users', array('i_photo'=>substr($file,0,-4).'0'), array('user_id'=>substr($file,0,-4)));
						unlink($p.$file);
						++$c;
						if($c>24) break;
						}
					}
				closedir($dh);
				}
			echo $c;
		break;
		}
	return;
	}
// *****************************************
// **** ONGLET PROFIL
// *****************************************
function f_rencProfil() // (submit)
	{
	// Ajax - plus & edit profil
	if($_POST["a1"]=="edit") profil_edit($_POST["a2"],$_POST["a3"],$_POST["a4"],$_POST["a5"],$_POST["a6"],$_POST["g"]);
	else if($_POST["a1"]=="plus") profil_plus($_POST["a2"],$_POST["a3"],$_POST["a4"],$_POST["a5"]);
	}
function profil_edit($a2,$a3,$a4,$a5,$a6,$g)
	{
	// a2 : ID - a3 : colonne - a4 : valeur colonne - a5 : position (select ou check) - a6 : type - g : genre ",0,1,"
	if(!is_admin()) die;
	global $wpdb; global $rencCustom; global $rencOpt;
	if(!empty($rencCustom['sex']) && strpos($g,',')!==false)
		{
		$g1 = explode(',',$g); $g = ',';
		foreach($g1 as $r) if($r!='') $g .= (intval($r)+2).',';
		}
	$gdef = ',';
	for($v=(!empty($rencCustom['sex'])?2:0);$v<(!empty($rencCustom['sex'])?count($rencOpt['iam']):2);++$v)
		{
		$gdef .= $v.',';
		}
	if($g==$gdef) $g = 0;
	$a4 = urldecode($a4); // stripslashes() a ajouter : fr=Un pays où j\'aimerais vivre&en=A country where I want to live&
	$b4 = explode('&',substr($a4, 0, -1));
	$c4 = Array();
	foreach($b4 as $b)
		{
		$t=explode('=',$b);
		if($t)
			{
			$c4[] = array('a'=>$t[0], 'b'=>$t[1]);
			}
		}
	if($a3=="c_categ")
		{
		$q = $wpdb->get_results("SELECT c_categ FROM ".$wpdb->prefix."rencontre_profil WHERE id='".$a2."' ");
		foreach($q as $qr)
			{
			for($v=0;$v<count($c4);++$v)
				{
				$wpdb->query("UPDATE ".$wpdb->prefix."rencontre_profil
					SET
						c_categ='".$c4[$v]['b']."',
						c_genre='".$g."'
					WHERE
						c_categ='".$qr->c_categ."' and
						c_lang='".$c4[$v]['a']."' 
					");
				}
			}
		}
	else if($a3=="c_label")
		{
		$typ = $wpdb->get_var("SELECT i_type FROM ".$wpdb->prefix."rencontre_profil WHERE id='".$a2."' LIMIT 1");
		if($a6==1 || $a6==2)
			{
			$q = $wpdb->get_var("SELECT i_poids FROM ".$wpdb->prefix."rencontre_profil WHERE id='".$a2."' LIMIT 1");
			for($v=0;$v<count($c4);++$v)
				{
				$wpdb->query("UPDATE ".$wpdb->prefix."rencontre_profil
					SET
						c_label='".$c4[$v]['b']."',
						t_valeur='' ,
						i_type='".$a6."',
						i_poids='".(($q<5 && $typ!=$a6)?($q+5):$q)."',
						c_genre='".$g."'
					WHERE
						id='".$a2."' and
						c_lang='".$c4[$v]['a']."'
					");
				}
			}
		else if($a6==3 || $a6==4)
			{
			$q = $wpdb->get_results("SELECT t_valeur, c_lang, i_poids FROM ".$wpdb->prefix."rencontre_profil WHERE id='".$a2."' ");
			foreach($q as $qr)
				{
				for($v=0;$v<count($c4);++$v)
					{
					if($c4[$v]['a']==$qr->c_lang)
						{
						$a = $qr->t_valeur;
						if($a=="") $a = '["*** '. __('TO CHANGE','rencontre').' ***"]';
						$wpdb->query("UPDATE ".$wpdb->prefix."rencontre_profil
							SET
								c_label='".$c4[$v]['b']."',
								t_valeur='".$a."',
								i_type='".$a6."',
								i_poids='".(($qr->i_poids<5 && $typ!=$a6)?($qr->i_poids+5):$qr->i_poids)."',
								c_genre='".$g."'
							WHERE
								id='".$a2."' and
								c_lang='".$c4[$v]['a']."'
							");
						}
					}
				}
			}
		else if($a6==5)
			{
			$q = $wpdb->get_results("SELECT t_valeur, c_lang, i_poids FROM ".$wpdb->prefix."rencontre_profil WHERE id='".$a2."' ");
			foreach($q as $qr)
				{
				for($v=0;$v<count($c4);++$v)
					{
					if($c4[$v]['a']==$qr->c_lang)
						{
						$a = $qr->t_valeur;
						if($a=="") $a = '["0","50","1","'. __('Unit','rencontre').'"]';
						$wpdb->query("UPDATE ".$wpdb->prefix."rencontre_profil
							SET
								c_label='".$c4[$v]['b']."',
								t_valeur='".$a."',
								i_type='".$a6."',
								i_poids='".(($qr->i_poids<5 && $typ!=$a6)?($qr->i_poids+5):$qr->i_poids)."',
								c_genre='".$g."'
							WHERE
								id='".$a2."' and
								c_lang='".$c4[$v]['a']."'
							");
						}
					}
				}
			}
		if($typ!=$a6)
			{
			if(!file_exists(dirname(__FILE__).'/rencontre_synchronise.json')) $a = array();
			else
				{
				$q = file_get_contents(dirname(__FILE__).'/rencontre_synchronise.json');
				$a = json_decode($q,true);
				}
			$a[$a2] = array(); // [] : purge
			file_put_contents(dirname(__FILE__).'/rencontre_synchronise.json', json_encode($a));  // info modif
			echo 'reload';
			}
		}
	else if($a3=="t_valeur") 
		{
		$q = $wpdb->get_results("SELECT t_valeur, c_lang, i_poids FROM ".$wpdb->prefix."rencontre_profil WHERE id='".$a2."' ");
		foreach($q as $qr)
			{
			if($a5=='ns')
				{
				$s = '["'.$b4[0].'","'.$b4[1].'","'.$b4[2].'","'.$b4[3].'"]';
				$wpdb->query("UPDATE ".$wpdb->prefix."rencontre_profil
					SET
						t_valeur='".$s."',
						i_poids='".(($qr->i_poids<5)?($qr->i_poids+5):$qr->i_poids)."'
					WHERE
						id='".$a2."'
					");
				// suppression systematique
				if(!file_exists(dirname(__FILE__).'/rencontre_synchronise.json')) $a = array();
				else
					{
					$q = file_get_contents(dirname(__FILE__).'/rencontre_synchronise.json');
					$a = json_decode($q,true);
					}
				$a[$a2] = array(0);
				file_put_contents(dirname(__FILE__).'/rencontre_synchronise.json', json_encode($a));  // info modif
				}
			else
				{
				$r =  json_decode($qr->t_valeur);
				for($v=0;$v<count($c4);++$v)
					{
					if($c4[$v]['a']==$qr->c_lang)
						{
						$r[$a5-1] = $c4[$v]['b']; // a5 : indice a partir de 1 (this)
						$s = '['; foreach ($r as $rr) {$s .='"'. $rr . '",';} $s = substr($s,0,-1) . "]"; $s = str_replace("'", "&#39;", $s);
						$wpdb->query("UPDATE ".$wpdb->prefix."rencontre_profil
							SET
								t_valeur='".$s."'
							WHERE
								id='".$a2."' and
								c_lang='".$c4[$v]['a']."'
							");
						}
					}
				}
			}
		}
	}
//
function profil_plus($a2,$a3,$a4,$a5)
	{
	// a5 : langues separees par &
	if(!is_admin()) die;
	global $wpdb; global $rencCustom; global $rencOpt;
	$genreDef = '';
	for($v=(isset($rencCustom['sex'])?2:0);$v<(isset($rencCustom['sex'])?count($rencOpt['iam']):2);++$v)
		{
		$genreDef .= preg_replace('/[^a-z0-9]/i','_',$rencOpt['iam'][$v]).'=1&';
		}
	$a4 = urldecode($a4); // stripslashes() a ajouter : fr=Un pays où j\'aimerais vivre&en=A country where I want to live&
	$b4 = explode('&',substr($a4, 0, -1));
	$c4 = Array();
	foreach($b4 as $b)
		{
		$t=explode('=',$b);
		if($t)
			{
			$c4[] = array('a'=>$t[0], 'b'=>$t[1]);
			}
		}
	if($a3=="c_categ")
		{
		$m = $wpdb->get_var("SELECT MAX(i_categ) FROM ".$wpdb->prefix."rencontre_profil");
		for($v=0;$v<count($c4);++$v)
			{
			if($v==0)
				{
				$wpdb->query("INSERT INTO ".$wpdb->prefix."rencontre_profil (i_categ,i_label,c_categ,c_label,t_valeur,i_type,i_poids,c_lang) VALUES(".($m+1).",1,'".$c4[$v]['b']."','*** ". __('TO CHANGE','rencontre')." ***','',1,0,'".$c4[$v]['a']."')");
				$lastid = $wpdb->insert_id;
				}
			else $wpdb->query("INSERT INTO ".$wpdb->prefix."rencontre_profil (id,i_categ,i_label,c_categ,c_label,t_valeur,i_type,i_poids,c_lang) VALUES('".$lastid."',".($m+1).",1,'".$c4[$v]['b']."','*** ". __('TO CHANGE','rencontre')." ***','',1,0,'".$c4[$v]['a']."')");
			}
		}
	else if($a3=="c_label") 
		{
		$ic = $wpdb->get_var("SELECT i_categ FROM ".$wpdb->prefix."rencontre_profil WHERE id='".$a2."' LIMIT 1");
		$m = $wpdb->get_var("SELECT MAX(i_label) FROM ".$wpdb->prefix."rencontre_profil WHERE i_categ='".$ic."'");
		for($v=0;$v<count($c4);++$v)
			{
			$q = $wpdb->get_var("SELECT c_categ FROM ".$wpdb->prefix."rencontre_profil WHERE id='".$a2."' AND c_lang='".$c4[$v]['a']."' LIMIT 1");
			if($v==0)
				{
				$wpdb->query("INSERT INTO ".$wpdb->prefix."rencontre_profil (i_categ,i_label,c_categ,c_label,t_valeur,i_type,i_poids,c_lang) VALUES(".$ic.",".($m+1).",'".$q."','".$c4[$v]['b']."','',1,0,'".$c4[$v]['a']."')");
				$lastid = $wpdb->insert_id;
				}
			else $wpdb->query("INSERT INTO ".$wpdb->prefix."rencontre_profil (id,i_categ,i_label,c_categ,c_label,t_valeur,i_type,i_poids,c_lang) VALUES('".$lastid."',".$ic.",".($m+1).",'".$q."','".$c4[$v]['b']."','',1,0,'".$c4[$v]['a']."')");
			}
		echo $lastid.'|'.$genreDef; // response needed to actualize display and events
		}
	else if($a3=="t_valeur") 
		{
		for($v=0;$v<count($c4);++$v)
			{
			$tval = $wpdb->get_var("SELECT t_valeur FROM ".$wpdb->prefix."rencontre_profil WHERE id='".$a2."' AND c_lang='".$c4[$v]['a']."' LIMIT 1");
			$s = substr($tval,0,-1) . ",\"" . $c4[$v]['b'] . "\"]";
			$s = str_replace("'", "&#39;", $s);
			$wpdb->query("UPDATE ".$wpdb->prefix."rencontre_profil SET t_valeur='".$s."' WHERE id='".$a2."' AND c_lang='".$c4[$v]['a']."' ");
			}
			
		if(!file_exists(dirname(__FILE__).'/rencontre_synchronise.json')) $a = array();
		else
			{
			$q = file_get_contents(dirname(__FILE__).'/rencontre_synchronise.json');
			$a = json_decode($q,true);
			}
		if(isset($a[$a2])) $a[$a2][] = count($a[$a2]); // [0,1,2]  => add 3 (count=3)
		else
			{
			$a[$a2] = array();
			for($v=0;$v<=count(json_decode($tval));++$v)
				{
				$a[$a2][] = $v;
				}
			}
		file_put_contents(dirname(__FILE__).'/rencontre_synchronise.json', json_encode($a));  // info modif

		}
	}
//
function profil_langplus($loc,$a4)
	{
	// a4 : langue
	if(!is_admin()) die;
	global $wpdb;
	$q = $wpdb->get_var("SELECT c_lang FROM ".$wpdb->prefix."rencontre_profil WHERE c_lang='".$a4."' LIMIT 1");
	if(!$q)
		{
		$q = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."rencontre_profil WHERE c_lang='".$loc."' ORDER BY id");
		if(!$q) $q = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."rencontre_profil WHERE c_lang='en' ORDER BY id");
		foreach($q as $r)
			{
			if($r->t_valeur=='') $wpdb->query("INSERT INTO ".$wpdb->prefix."rencontre_profil (id,i_categ,i_label,c_categ,c_label,t_valeur,i_type,i_poids,c_lang) VALUES('".$r->id."','".$r->i_categ."','".$r->i_label."','?','?','','".$r->i_type."','".$r->i_poids."','".$a4."')");
			else
				{
				$s='['; for($v=0;$v<count(json_decode($r->t_valeur));++$v) {$s.='"?",';} $s = str_replace("'", "&#39;", $s);
				$wpdb->query("INSERT INTO ".$wpdb->prefix."rencontre_profil (id,i_categ,i_label,c_categ,c_label,t_valeur,i_type,i_poids,c_lang) VALUES('".$r->id."','".$r->i_categ."','".$r->i_label."','?','?','".substr($s,0,-1)."]"."','".$r->i_type."','".$r->i_poids."','".$a4."')");
				}
			}
		}
	}
//
function profil_langsupp($a4)
	{
	// a4 : langue
	if(!is_admin()) die;
	global $wpdb;
	$wpdb->query("DELETE FROM ".$wpdb->prefix."rencontre_profil WHERE c_lang='".$a4."'");
	}
//
function f_rencUpDown()
	{
	// Ajax
	if(!is_admin()) die;
	global $wpdb;
	$n = 0; $max = 0;
	$lang = $wpdb->get_var("SELECT c_lang FROM ".$wpdb->prefix."rencontre_profil LIMIT 1"); // au pif
	$move = strip_tags($_POST['move']);
	$id = strip_tags($_POST['id']);
	$typ = strip_tags($_POST['typ']);
	$id2 = strip_tags($_POST['id2']); // position a partir de 1 !
	if($typ=='c_categ' && $move=='up')
		{
		$n = $wpdb->get_var("SELECT i_categ FROM ".$wpdb->prefix."rencontre_profil WHERE id='".$id."' LIMIT 1");
		if($n && $n>1)
			{
			$q = $wpdb->get_results("SELECT id, i_categ FROM ".$wpdb->prefix."rencontre_profil WHERE c_lang='".$lang."' ");
			if($q)
				{
				foreach($q as $r)
					{
					if($r->i_categ==$n) $wpdb->update($wpdb->prefix.'rencontre_profil', array('i_categ'=>($n-1)), array('id'=>$r->id));
					if($r->i_categ==($n-1)) $wpdb->update($wpdb->prefix.'rencontre_profil', array('i_categ'=>($n)), array('id'=>$r->id));
					}
				echo 'OK';
				}
			}
		}
	else if($typ=='c_categ' && $move=='down')
		{
		$q = $wpdb->get_results("SELECT id, i_categ FROM ".$wpdb->prefix."rencontre_profil ");
		if($q) foreach($q as $r)
			{
			if($r->id==$id) $n = $r->i_categ;
			if($r->i_categ>$max) $max = $r->i_categ;
			}
		if($n && $n<$max)
			{
			$q = $wpdb->get_results("SELECT id, i_categ FROM ".$wpdb->prefix."rencontre_profil WHERE c_lang='".$lang."' ");
			if($q)
				{
				foreach($q as $r)
					{
					if($r->i_categ==$n) $wpdb->update($wpdb->prefix.'rencontre_profil', array('i_categ'=>($n+1)), array('id'=>$r->id));
					if($r->i_categ==($n+1)) $wpdb->update($wpdb->prefix.'rencontre_profil', array('i_categ'=>($n)), array('id'=>$r->id));
					}
				echo 'OK';
				}
			}
		}
	else if($typ=='c_label' && $move=='up')
		{
		$q = $wpdb->get_row("SELECT
				i_categ,
				i_label
			FROM
				".$wpdb->prefix."rencontre_profil
			WHERE
				id='".$id."'
			LIMIT 1
			");
		if($q)
			{
			$n = $q->i_label;
			$cat = $q->i_categ;
			}
		if($n && $n>1)
			{
			$q = $wpdb->get_results("SELECT id, i_categ, i_label FROM ".$wpdb->prefix."rencontre_profil WHERE c_lang='".$lang."' ");
			if($q)
				{
				foreach($q as $r)
					{
					if($r->i_label==$n && $r->i_categ==$cat) $wpdb->update($wpdb->prefix.'rencontre_profil', array('i_label'=>($n-1)), array('id'=>$r->id));
					if($r->i_label==($n-1) && $r->i_categ==$cat) $wpdb->update($wpdb->prefix.'rencontre_profil', array('i_label'=>($n)), array('id'=>$r->id));
					}
				echo 'OK';
				}
			}
		}
	else if($typ=='c_label' && $move=='down')
		{
		$q = $wpdb->get_row("SELECT
				i_categ,
				i_label
			FROM
				".$wpdb->prefix."rencontre_profil
			WHERE
				id='".$id."'
			LIMIT 1
			");
		if($q)
			{
			$n = $q->i_label;
			$cat = $q->i_categ;
			}
		$q = $wpdb->get_results("SELECT i_label FROM ".$wpdb->prefix."rencontre_profil WHERE c_lang='".$lang."' and i_categ='".$cat."' ");
		if($q) foreach($q as $r) { if($r->i_label>$max) $max = $r->i_label; }
		if($n && $n<$max)
			{
			$q = $wpdb->get_results("SELECT id, i_categ, i_label FROM ".$wpdb->prefix."rencontre_profil WHERE c_lang='".$lang."' ");
			if($q)
				{
				foreach($q as $r)
					{
					if($r->i_label==$n && $r->i_categ==$cat) $wpdb->update($wpdb->prefix.'rencontre_profil', array('i_label'=>($n+1)), array('id'=>$r->id));
					if($r->i_label==($n+1) && $r->i_categ==$cat) $wpdb->update($wpdb->prefix.'rencontre_profil', array('i_label'=>($n)), array('id'=>$r->id));
					}
				echo 'OK';
				}
			}
		}
	else if($typ=='t_valeur' && $move=='up' && $id2>1)
		{
		$q = $wpdb->get_results("SELECT t_valeur, c_lang, i_poids FROM ".$wpdb->prefix."rencontre_profil WHERE id='".$id."'");
		if($q)
			{
			foreach($q as $r)
				{
				$j =  json_decode($r->t_valeur);
				$le = count($j);
				$a = $j[$id2-1];
				$b = $j[$id2-2];
				$j[$id2-1] = $b;
				$j[$id2-2] = $a;
				$s = '[';
				foreach ($j as $rr) { $s .= '"'. $rr . '",'; }
				$s = substr($s,0,-1) . "]";
				$s = str_replace("'", "&#39;", $s);
				$wpdb->update($wpdb->prefix.'rencontre_profil', array('t_valeur'=>$s, 'i_poids'=>(($r->i_poids<5)?($r->i_poids+5):($r->i_poids))), array('id'=>$id, 'c_lang'=>$r->c_lang) );
				}
			if(!file_exists(dirname(__FILE__).'/rencontre_synchronise.json')) $a = array();
			else
				{
				$q = file_get_contents(dirname(__FILE__).'/rencontre_synchronise.json');
				$a = json_decode($q,true);
				}
			$t = array();
			if(!isset($a[$id]))
				{
				for($v=0;$v<$le;++$v)
					{
					if($v==$id2-2) $t[] = intval($id2-1); // 3 en 2
					else if($v==$id2-1) $t[] = intval($id2-2); // 2 en 3
					else $t[] = $v;
					}
				}
			else
				{
				foreach($a[$id] as $k=>$v)
					{
					if($k==$id2-2) $t[] = intval($a[$id][$k+1]); // 3 en 2
					else if($k==$id2-1) $t[] = intval($a[$id][$k-1]); // 2 en 3
					else $t[] = $v;
					}
				}
			$a[$id] = $t; // [0,1,3,2,4,5,6,7,8,9] // 3 up
			file_put_contents(dirname(__FILE__).'/rencontre_synchronise.json', json_encode($a));  // info modif
			echo 'OK';
			}
		}
	else if($typ=='t_valeur' && $move=='down')
		{
		$q = $wpdb->get_results("SELECT t_valeur, c_lang, i_poids FROM ".$wpdb->prefix."rencontre_profil WHERE id='".$id."'");
		if($q)
			{
			foreach($q as $r)
				{
				$j =  json_decode($r->t_valeur);
				$le = count($j);
				if(!isset($j[$id2])) return;
				$a = $j[$id2-1];
				$b = $j[$id2];
				$j[$id2-1] = $b;
				$j[$id2] = $a;
				$s = '[';
				foreach ($j as $rr) { $s .= '"'. $rr . '",'; }
				$s = substr($s,0,-1) . "]";
				$s = str_replace("'", "&#39;", $s);
				$wpdb->update($wpdb->prefix.'rencontre_profil', array('t_valeur'=>$s, 'i_poids'=>(($r->i_poids<5)?($r->i_poids+5):($r->i_poids))), array('id'=>$id, 'c_lang'=>$r->c_lang) );
				}
			if(!file_exists(dirname(__FILE__).'/rencontre_synchronise.json')) $a = array();
			else
				{
				$q = file_get_contents(dirname(__FILE__).'/rencontre_synchronise.json');
				$a = json_decode($q,true);
				}
			$t = array();
			if(!isset($a[$id]))
				{
				for($v=0;$v<$le;++$v)
					{
					if($v==$id2) $t[] = intval($id2-1); // 3 en 2
					else if($v==$id2-1) $t[] = intval($id2); // 2 en 3
					else $t[] = $v;
					}
				}
			else
				{
				foreach($a[$id] as $k=>$v)
					{
					if($k==$id2-1) $t[] = intval($a[$id][$k+1]); // 3 en 2
					else if($k==$id2) $t[] = intval($a[$id][$k-1]); // 2 en 3
					else $t[] = $v;
					}
				}
			$a[$id] = $t; // [0,1,3,2,4,5,6,7,8,9] // 3 up
			file_put_contents(dirname(__FILE__).'/rencontre_synchronise.json', json_encode($a));  // info modif
			echo 'OK';
			}
		}
	if($typ=='c_categ' && $move=='supp')
		{
		$q = $wpdb->get_var("SELECT i_categ FROM ".$wpdb->prefix."rencontre_profil WHERE id='".$id."' LIMIT 1");
		if($q)
			{
			$wpdb->delete($wpdb->prefix.'rencontre_profil', array('i_categ'=>$q));
			$wpdb->query("UPDATE ".$wpdb->prefix."rencontre_profil SET i_categ=i_categ-1 WHERE i_categ>".$q);
			if(!file_exists(dirname(__FILE__).'/rencontre_synchronise.json'))
				{
				$a = array();
				file_put_contents(dirname(__FILE__).'/rencontre_synchronise.json', json_encode($a)); // info modif(vide)
				}
			echo 'OK';
			}
		}
	if($typ=='c_label' && $move=='supp')
		{
		$q = $wpdb->get_row("SELECT
				i_categ,
				i_label
			FROM
				".$wpdb->prefix."rencontre_profil
			WHERE
				id='".$id."'
			LIMIT 1
			");
		$wpdb->delete($wpdb->prefix.'rencontre_profil', array('id'=>$id));
		$wpdb->query("UPDATE ".$wpdb->prefix."rencontre_profil SET i_label=i_label-1 WHERE i_categ=".$q->i_categ." and i_label>".$q->i_label);
		if(!file_exists(dirname(__FILE__).'/rencontre_synchronise.json'))
			{
			$a = array();
			file_put_contents(dirname(__FILE__).'/rencontre_synchronise.json', json_encode($a)); // info modif(vide)
			}
		echo 'OK';
		}
	if($typ=='t_valeur' && $move=='supp')
		{
		$q = $wpdb->get_results("SELECT t_valeur, c_lang, i_poids FROM ".$wpdb->prefix."rencontre_profil WHERE id='".$id."'");
		if($q)
			{
			foreach($q as $qr)
				{
				$r =  json_decode($qr->t_valeur);
				$le = count($r);
				unset($r[$id2-1]);
				$s = '[';
				foreach ($r as $rr) { $s .= '"' . $rr . '",'; }
				$s = substr($s,0,-1) . "]";
				$s = str_replace("'", "&#39;", $s);
				$wpdb->update($wpdb->prefix.'rencontre_profil', array('t_valeur'=>$s, 'i_poids'=>(($qr->i_poids<5)?($qr->i_poids+5):($qr->i_poids))), array('id'=>$id, 'c_lang'=>$qr->c_lang) );
				}
			if(!file_exists(dirname(__FILE__).'/rencontre_synchronise.json')) $a = array();
			else
				{
				$q = file_get_contents(dirname(__FILE__).'/rencontre_synchronise.json');
				$a = json_decode($q,true);
				}
			$t = array();
			if(!isset($a[$id]))
				{
				for($v=0;$v<$le;++$v) if($v!=$id2-1) $t[] = $v;
				}
			else
				{
				foreach($a[$id] as $k=>$v) if($k!=$id2-1) $t[] = $v;
				}
			$a[$id] = $t; // [0,1,2,3,4,6,7,8,9] avec n°5 supp
			file_put_contents(dirname(__FILE__).'/rencontre_synchronise.json', json_encode($a));  // info modif
			echo 'OK';
			}
		}
	}
//
function profil_defaut()
	{
	// chargement des profils par defaut
	if(!is_admin()) die;
	$f = file_get_contents(plugin_dir_path( __FILE__ ).'rencontre_profil_defaut.txt');
	global $wpdb;
	$wpdb->query('INSERT INTO '.$wpdb->prefix.'rencontre_profil (id, i_categ, i_label, c_categ, c_label, t_valeur, i_type, i_poids, c_lang) VALUES '.$f);
	$g = $wpdb->get_var("SELECT MAX(id) FROM ".$wpdb->prefix."rencontre_profil");
	$wpdb->query("ALTER TABLE ".$wpdb->prefix."rencontre_profil AUTO_INCREMENT = ".$g);
	}
//
function liste_defaut()
	{
	// chargement des pays et regions par defaut
	if(!is_admin()) die;
	$f = file_get_contents(plugin_dir_path( __FILE__ ).'rencontre_liste_defaut.txt');
	global $wpdb;
	$wpdb->query("ALTER TABLE ".$wpdb->prefix."rencontre_liste AUTO_INCREMENT = 1");
	$wpdb->query("INSERT INTO ".$wpdb->prefix."rencontre_liste (c_liste_categ, c_liste_valeur, c_liste_iso, c_liste_lang) VALUES ".$f);
		// **** PATCH V1.2 : langue pour les pays *****************************************
			$q = $wpdb->get_results("SELECT user_id, c_pays FROM ".$wpdb->prefix."rencontre_users");
			foreach($q as $r)
				{
				if(strlen($r->c_pays)>2)
					{
					$iso = $wpdb->get_var("SELECT c_liste_iso FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='p' and c_liste_valeur='".$r->c_pays."' LIMIT 1");
					if($iso) $wpdb->update($wpdb->prefix.'rencontre_users', array('c_pays'=>$iso), array('user_id'=>$r->user_id));
					else $wpdb->update($wpdb->prefix.'rencontre_users', array('c_pays'=>'CI'), array('user_id'=>$r->user_id)); // et pourquoi pas !
					}
				}
		// ************************************************************************************
	}
//
function synchronise()
	{
	// Sur le compte de chaque utilisateur (rencontre_users_profil) : supprime les ID inexistants dans la colonne t_profil et reorganise les valeurs
	if(!is_admin()) die;
	global $wpdb;
	if(file_exists(dirname(__FILE__).'/rencontre_synchronise.json'))
		{
		$a = 0; $typ = array();
		$q = file_get_contents(dirname(__FILE__).'/rencontre_synchronise.json'); // {"14":[1,4,2,0],"24":[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14]}
		$a = json_decode($q,true);
		if(is_array($a))
			{
			$q = $wpdb->get_results("SELECT user_id, t_profil FROM ".$wpdb->prefix."rencontre_users_profil WHERE CHAR_LENGTH(t_profil)>5");
			$q1 = $wpdb->get_results("SELECT DISTINCT(id), i_type FROM ".$wpdb->prefix."rencontre_profil");
			$t=',';
			foreach($q1 as $r1)
				{
				$t.=$r1->id.","; // liste des id de profil existants : $t = ",1,2,4,5,12,15,"
				$typ[$r1->id] = $r1->i_type;
				}
			foreach($q as $k=>$r)
				{ // boucle users
				$profil = json_decode($r->t_profil,true); $b=0;
				if(is_array($profil)) foreach($profil as $k2=>$r2)
					{ // boucle profil users
					// 1. suppression modifs sur type 5
					if($typ[$r2['i']]==5 && $r2['i']==$k)
						{
						unset($profil[$k2]);
						$b = 1;
						}
					// 2. suppression id inexistantes
					else if(strpos($t,",".$r2['i'].",")===false)
						{
						unset($profil[$k2]);
						$b = 1;
						}
					// 3. ordre des options et checkbox
					else if(isset($a[$r2['i']]))
						{
						$b = 1;
						if(!is_array($r2['v'])) // cas 1, 2 et 3
							{
							if(in_array($r2['v'], $a[$r2['i']])) $profil[$k2]['v'] = array_search($r2['v'], $a[$r2['i']]);
							else unset($profil[$k2]);
							}
						else  // cas 4
							{ 
							// $a : {"13":[0,3,1,4,5,6]} : 2 SUPP puis 3 UP
							// $profil : {"i":13,"v":[0,2,3,4]}
							$profil[$k2]['v'] = array(); // purge
							foreach($r2['v'] as $r3) // [0,2,3,4]
								{
								if(in_array($r3, $a[$r2['i']])) $profil[$k2]['v'][] = array_search($r3, $a[$r2['i']]);
								// [0,2,3,4] devient [0,1,3]
								}
							if($profil[$k2]['v']==array()) unset($profil[$k2]);
							}
						}
					}
				if($b==1)
					{
					$profil2=array(); foreach ($profil as $k=>$r2) { $profil2[]=$r2; } // reorder pour eviter apparition de key dans le JSON
					$c = json_encode($profil2);
					$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('t_profil'=>$c), array('user_id'=>$r->user_id));
					}
				}
			$q = $wpdb->get_results("SELECT id, i_poids, c_lang FROM ".$wpdb->prefix."rencontre_profil WHERE i_poids>4 ");
			if($q) foreach($q as $r)
				{
				$wpdb->update($wpdb->prefix.'rencontre_profil', array('i_poids'=>($r->i_poids-5)), array('id'=>$r->id, 'c_lang'=>$r->c_lang));
				}
			}
		unlink(dirname(__FILE__).'/rencontre_synchronise.json');
		}
// SALLY : [{"i":24,"v":[0,5,11,14]},{"i":25,"v":[12,18,20]},{"i":26,"v":[2,11,26]},{"i":27,"v":[8,24,26]},{"i":6,"v":"sympa"},{"i":10,"v":"cool"},{"i":13,"v":4},{"i":1,"v":3},{"i":2,"v":0},{"i":3,"v":"en or"},{"i":5,"v":[1]}]
	}
//
// *****************************************
// **** ONGLET REGION
// *****************************************
function liste_edit($a2,$a3,$a4,$a5,$a6)
	{
	// a2 : iso/id - a3 : colonne - a4 : valeur colonne - a5 : position (select ou check) - a6 : type
	if(!is_admin()) die;
	global $wpdb;
	if($a3=="p")
		{
		$a4 = urldecode($a4); // stripslashes() a ajouter : fr=Un pays où j\'aimerais vivre&en=A country where I want to live&
		$b4 = explode('&',substr($a4, 0, -1));
		foreach($b4 as $b)
			{
			$t=explode('=',$b);
			if($t) $wpdb->update($wpdb->prefix.'rencontre_liste', array('c_liste_valeur'=>ucwords(stripslashes($t[1]))), array('c_liste_iso'=>$a2, 'c_liste_lang'=>$t[0]));
			}
		}
	else if($a3=="r") $wpdb->update($wpdb->prefix.'rencontre_liste', array('c_liste_valeur'=>ucwords(stripslashes($a4))), array('id'=>$a2));
	}
//
function liste_supp($a2,$a3,$a4)
	{
	// a2 : ID - a3 : colonne - a4 :
	if(!is_admin()) die;
	global $wpdb;
	if($a3=="p") $wpdb->query("DELETE FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_iso='".$a2."' ");
	else if($a3=="r") $wpdb->query("DELETE FROM ".$wpdb->prefix."rencontre_liste WHERE id='".$a2."' and c_liste_categ='r' ");
	}
//
function liste_plus($a2,$a3,$a4,$a5,$a6)
	{
	// a5 : langues separees par &
	if(!is_admin()) die;
	global $wpdb;
	if($a3=="p" && strlen($a5)==2)
		{
		$a4 = urldecode($a4); // stripslashes() a ajouter : fr=Un pays où j\'aimerais vivre&en=A country where I want to live&
		$b4 = explode('&',substr($a4, 0, -1));
		foreach($b4 as $b)
			{
			$t=explode('=',$b);
			if($t) $wpdb->query("INSERT INTO ".$wpdb->prefix."rencontre_liste (c_liste_categ,c_liste_valeur,c_liste_iso,c_liste_lang) VALUES('p','".ucwords($t[1])."','".$a5."','".$t[0]."')");
			}
		$wpdb->query("INSERT INTO ".$wpdb->prefix."rencontre_liste (c_liste_categ,c_liste_valeur,c_liste_iso,c_liste_lang) VALUES('d','".$a6."','".$a5."','')");
		}
	else if($a3=="r") $wpdb->query("INSERT INTO ".$wpdb->prefix."rencontre_liste (c_liste_categ,c_liste_valeur,c_liste_iso,c_liste_lang) VALUES('r','".ucwords(urldecode($a4))."','".$a2."','')");
	}
//
function liste_langplus($loc,$a4)
	{
	// a4 : langue
	if(!is_admin()) die;
	global $wpdb;
	$q = $wpdb->get_var("SELECT c_liste_lang FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_lang='".$a4."' LIMIT 1");
	if(!$q)
		{
		$q = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='p' and c_liste_lang='".$loc."' ORDER BY id");
		if(!$q) $q = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='p' and c_liste_lang='en' ORDER BY id");
		foreach($q as $r)
			{
			$wpdb->query("INSERT INTO ".$wpdb->prefix."rencontre_liste (c_liste_categ,c_liste_valeur,c_liste_iso,c_liste_lang) VALUES('p','?','".$r->c_liste_iso."','".$a4."')");
			}
		}
	}
//
function liste_langsupp($a4)
	{
	// a4 : langue
	if(!is_admin()) die;
	global $wpdb;
	$wpdb->query("DELETE FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_lang='".$a4."'");
	}
// *****************************************
// **** TAB ADMIN
// *****************************************
function update_rencontre_options($f)
	{
	global $rencOpt;
	if(empty($_GET['renctab']))
		{
		if(!empty($f['facebook'])) $rencOpt['facebook'] = stripslashes($f['facebook']); else unset($rencOpt['facebook']);
		if(isset($f['rol'])) $rencOpt['rol'] = $f['rol']; else unset($rencOpt['rol']);
		if(isset($f['rolu'])) $rencOpt['rolu'] = $f['rolu']; else unset($rencOpt['rolu']);
		if(isset($f['home'])) $rencOpt['home'] = $f['home']; else unset($rencOpt['home']);
		if(isset($f['pays'])) $rencOpt['pays'] = $f['pays']; else unset($rencOpt['pays']);
		if(isset($f['prison'])) $rencOpt['prison'] = $f['prison']; else unset($rencOpt['prison']);
		if(isset($f['avatar'])) $rencOpt['avatar'] = 1; else unset($rencOpt['avatar']);
		if(isset($f['msgdel'])) $rencOpt['msgdel'] =  $f['msgdel']; else unset($rencOpt['msgdel']);
		if(isset($f['hcron'])) $rencOpt['hcron'] = $f['hcron']; else unset($rencOpt['hcron']);
		if(isset($f['imcode'])) $rencOpt['imcode'] = $f['imcode']; else unset($rencOpt['imcode']);
		}
	else if($_GET['renctab']=='log')
		{
		if(!empty($f['fblog'])) $rencOpt['fblog'] = $f['fblog']; else unset($rencOpt['fblog']);
		// !!!!!! RENCONTRE_FILTER LIGNE 200 !!!!!!!!!
		if(isset($f['fastreg'])) $rencOpt['fastreg'] = $f['fastreg']; else unset($rencOpt['fastreg']);
		if(isset($f['passw'])) $rencOpt['passw'] = $f['passw']; else unset($rencOpt['passw']);
		if(isset($f['logredir'])) $rencOpt['logredir'] = $f['logredir']; else unset($rencOpt['logredir']);
		}
	else if($_GET['renctab']=='dis')
		{
		if(isset($f['npa'])) $rencOpt['npa'] = $f['npa']; else unset($rencOpt['npa']);
		if(isset($f['rlibre'])) $rencOpt['rlibre'] = $f['rlibre']; else unset($rencOpt['rlibre']);
		if(isset($f['jlibre'])) $rencOpt['jlibre'] = $f['jlibre']; else unset($rencOpt['jlibre']);
		if(isset($f['limit'])) $rencOpt['limit'] = $f['limit']; else unset($rencOpt['limit']);
		if(isset($f['anniv'])) $rencOpt['anniv'] = 1; else unset($rencOpt['anniv']);
		if(isset($f['ligne'])) $rencOpt['ligne'] = 1; else unset($rencOpt['ligne']);
		if(isset($f['tchat'])) $rencOpt['tchat'] = 1; else unset($rencOpt['tchat']);
		if(isset($f['map'])) $rencOpt['map'] = 1; else unset($rencOpt['map']);
		if(!empty($f['mapapi'])) $rencOpt['mapapi'] = stripslashes($f['mapapi']); else unset($rencOpt['mapapi']);
		if(isset($f['imnb'])) $rencOpt['imnb'] = $f['imnb']; else unset($rencOpt['imnb']);
		if(isset($f['imcopyright'])) $rencOpt['imcopyright'] = $f['imcopyright']; else unset($rencOpt['imcopyright']);
		if(!empty($f['txtcopyright'])) $rencOpt['txtcopyright'] = stripslashes($f['txtcopyright']); else unset($rencOpt['txtcopyright']);
		if(isset($f['onlyphoto'])) $rencOpt['onlyphoto'] = 1; else unset($rencOpt['onlyphoto']);
		if(isset($f['photoz'])) $rencOpt['photoz'] = 1; else unset($rencOpt['photoz']);
		if(isset($f['pacamsg'])) $rencOpt['pacamsg'] = 1; else unset($rencOpt['pacamsg']);
		if(isset($f['pacasig'])) $rencOpt['pacasig'] = 1; else unset($rencOpt['pacasig']);
		}
	else if($_GET['renctab']=='mel')
		{
		if(isset($f['mailsupp'])) $rencOpt['mailsupp'] = 1; else unset($rencOpt['mailsupp']);
		if(isset($f['mailmois'])) $rencOpt['mailmois'] =  $f['mailmois']; else unset($rencOpt['mailmois']);
		if(!empty($f['textmail'])) $rencOpt['textmail'] = $f['textmail']; else unset($rencOpt['textmail']);
		if(isset($f['mailsmile'])) $rencOpt['mailsmile'] = 1; else unset($rencOpt['mailsmile']);
		if(isset($f['mailanniv'])) $rencOpt['mailanniv'] = 1; else unset($rencOpt['mailanniv']);
		if(!empty($f['textanniv'])) $rencOpt['textanniv'] = $f['textanniv']; else unset($rencOpt['textanniv']);
		if(isset($f['qmail'])) $rencOpt['qmail'] = $f['qmail']; else unset($rencOpt['qmail']);
		if(isset($f['mailph'])) $rencOpt['mailph'] = 1; else unset($rencOpt['mailph']);
		}
	else if($_GET['renctab']=='pre')
		{
		$v = 0; // Premium
		while(isset($f['premium'.$v]))
			{
			$rencOpt['premium'.$v] = $f['premium'.$v];
			++$v;
			}
		}
	if(isset($rencOpt['page_id'])) unset($rencOpt['page_id']);
	if(isset($rencOpt['for'])) unset($rencOpt['for']);
	if(!isset($rencOpt['custom'])) $rencOpt['custom'] = '';
	update_option('rencontre_options',$rencOpt);
	}
//
function rencMenuGeneral()
	{
	wp_enqueue_script('rencontre', plugins_url('rencontre/js/rencontre-adm.js'));
	wp_enqueue_style( 'rencontre', plugins_url('rencontre/css/rencontre-adm.css'));
	if(!empty($_POST)) update_rencontre_options($_POST);
	?>
	<?php if(empty($_GET['renctab']) && file_exists(dirname(__FILE__).'/rencontre_don.php')) include(dirname(__FILE__).'/rencontre_don.php'); ?>

	<div id="rencGen" class='wrap' style="max-width:620px;<?php if(empty($_GET['renctab'])) echo 'float:left;'; ?>">
		<h2 class="nav-tab-wrapper">
			<a href="admin.php?page=rencontre.php" class="nav-tab<?php if(empty($_GET['renctab'])) echo ' nav-tab-active'; ?>"><?php _e('General', 'rencontre'); ?></a>
			<a href="admin.php?page=rencontre.php&renctab=log" class="nav-tab<?php if(isset($_GET['renctab']) && $_GET['renctab']=='log') echo ' nav-tab-active'; ?>"><?php _e('Connection', 'rencontre'); ?></a>
			<a href="admin.php?page=rencontre.php&renctab=dis" class="nav-tab<?php if(isset($_GET['renctab']) && $_GET['renctab']=='dis') echo ' nav-tab-active'; ?>"><?php _e('Display', 'rencontre'); ?></a>
			<a href="admin.php?page=rencontre.php&renctab=mel" class="nav-tab<?php if(isset($_GET['renctab']) && $_GET['renctab']=='mel') echo ' nav-tab-active'; ?>"><?php _e('E-mails', 'rencontre'); ?></a>
			<a href="admin.php?page=rencontre.php&renctab=csv" class="nav-tab<?php if(isset($_GET['renctab']) && $_GET['renctab']=='csv') echo ' nav-tab-active'; ?>"><?php _e('CSV', 'rencontre'); ?></a>
		<?php $hoPre = false;
		if(has_filter('rencPremiumOptP', 'f_rencPremiumOptP')) $hoPre = apply_filters('rencPremiumOptP', 1);
		if($hoPre) { ?>
			
			<a href="admin.php?page=rencontre.php&renctab=pre" class="nav-tab<?php if(isset($_GET['renctab']) && $_GET['renctab']=='pre') echo ' nav-tab-active'; ?>"><?php _e('Premium', 'rencontre'); ?></a>
		<?php } ?>
		</h2>
	<?php if(!empty($_GET['renctab']))
		{
		if($_GET['renctab']=='log') rencTabLog();
		else if($_GET['renctab']=='dis') rencTabDis();
		else if($_GET['renctab']=='mel') rencTabMel();
		else if($_GET['renctab']=='csv') rencTabCsv();
		else if($hoPre && $_GET['renctab']=='pre') rencTabPre();
		?>
		
	</div>
	<div style="clear:both;"></div>
		<?php return;
		} 
	global $rencOpt; global $rencDiv; global $rencVersion;
	?>
	
		<h2>Rencontre&nbsp;<span style='font-size:80%;'>v<?php echo $rencVersion; ?></span></h2>
		<form method="post" name="rencontre_options" action="admin.php?page=rencontre.php">
			<table class="form-table" style="max-width:600px;clear:none;z-index:5;">
				<tr valign="top">
					<th scope="row"><label><?php _e('Framework for the Facebook Like button', 'rencontre'); ?></label></th>
					<td><textarea  name="facebook"><?php if(isset($rencOpt['facebook'])) echo $rencOpt['facebook']; ?></textarea></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Do not remove WP roles', 'rencontre'); ?><strong style="color:#500"> *</strong></label></th>
					<td><input type="checkbox" name="rol" value="1" <?php if(!empty($rencOpt['rol'])) echo 'checked'; ?> onClick="document.getElementById('blocRolu').style.display=((this.checked==true)?'table-row':'none')"></td>
				</tr>
				<tr valign="top" id="blocRolu" style="<?php echo ((!empty($rencOpt['rol']))?'display:table-row;':'display:none;'); ?>">
					<th scope="row"><label><?php _e('Do not remove user in WP when remove in Rencontre', 'rencontre'); ?></label></th>
					<td><input type="checkbox" name="rolu" value="1" <?php if(!empty($rencOpt['rolu'])) echo 'checked'; ?>></td>
				</tr>
				<tr>
					<td colspan = "2">
						<strong style="color:#500">* </strong>
						<em> - <?php echo __('Unchecked : WP roles will be destroyed. Users who are not in Rencontre will be destroyed. This is the best mode for a big and exclusive dating site.', 'rencontre'); ?></em><br />
						<em> - <?php echo __('Checked : Better choice to test this plugin or to use with other one (forum...). Over time, the site will be slower with numerous abandoned account.', 'rencontre'); ?></em>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Page where is settled the plugin', 'rencontre'); ?></label></th>
					<td>
						<select name="home">
							<option value="<?php echo get_home_url(); ?>" <?php echo (!empty($rencOpt['home'])?'':'selected'); ?>>Index</option>
							<?php $pages = get_pages(); $tmp = '';
							foreach($pages as $page) { $tmp .= '<option value="'.get_page_link($page->ID).'" '.($rencOpt['home']==get_page_link($page->ID)?'selected':'').'>'.$page->post_title.'</option>'; }
							echo $tmp; ?>

						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Country selected by default', 'rencontre'); ?></label></th>
					<td>
						<select name="pays">
						<?php RencontreWidget::f_pays((!empty($rencOpt['pays'])?$rencOpt['pays']:'FR'),1); ?>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Number of days in jail (deleted account)', 'rencontre'); ?></label></th>
					<td>
						<select name="prison">
							<?php for($v=7;$v<361;++$v)
								{
								if($v>90) $v += 29;
								else if($v>15) $v += 4;
								echo '<option value="'.$v.'"'.((isset($rencOpt['prison'])&&$rencOpt['prison']==$v)?' selected':'').'>'.$v.'</option>';
								}
							?>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Use member\'s picture as WordPress avatar', 'rencontre'); ?></label></th>
					<td><input type="checkbox" name="avatar" value="1" <?php if(!empty($rencOpt['avatar'])) echo 'checked'; ?>></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Old messages deleted during maintenance (recommended)', 'rencontre'); ?></label></th>
					<td>
						<select name="msgdel">
							<option value="0" <?php if(empty($rencOpt['msgdel'])) echo 'selected'; ?>><?php _e('No', 'rencontre'); ?></option>
							<option value="4" <?php if(isset($rencOpt['msgdel']) && $rencOpt['msgdel']==4) echo 'selected'; ?>><?php _e('Monthly', 'rencontre'); ?></option>
							<option value="1" <?php if(isset($rencOpt['msgdel']) && $rencOpt['msgdel']==1) echo 'selected'; ?>><?php _e('Quarterly', 'rencontre'); ?></option>
							<option value="2" <?php if(isset($rencOpt['msgdel']) && $rencOpt['msgdel']==2) echo 'selected'; ?>><?php _e('Biannual', 'rencontre'); ?></option>
							<option value="3" <?php if(isset($rencOpt['msgdel']) && $rencOpt['msgdel']==3) echo 'selected'; ?>><?php _e('Annual', 'rencontre'); ?></option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Hour maintenance tasks (off peak)', 'rencontre'); ?></label></th>
					<td>
						<select name="hcron">
							<?php for ($v=0;$v<24;++$v) {echo '<option value="'.$v.'" '.(($rencOpt['hcron']==$v)?'selected':'').'>&nbsp;'.$v.__('hours','rencontre').'</option>';} ?>
						</select>
					</td>
				</tr>
			</table>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save','rencontre') ?>" />
			</p>
		</form>
		<hr />
		<?php if(isset($_GET['bottom'])) echo '<script>jQuery("html,body").animate({scrollTop:jQuery(document).height()},1000);</script>'."\r\n"; ?>
		<h2><?php _e('Images names','rencontre') ?></h2>
		<p><?php _e('Be careful, all pictures of the members will have another name.','rencontre') ?>
		<form method="post" name="rencontre_code" action="admin.php?page=rencontre.php">
			<input id="rencCode" type="hidden" name="rencCode" value="" />
			<?php
			if(isset($_POST['rencCode'])) renc_encodeImg((($_POST['rencCode']=='code')?'1':'0'));
			$cod = rencImEncoded();
			if($cod===1) echo '<p style="color:green;">'. __('Images names are encoded','rencontre');
			else if($cod===0) echo '<p style="color:#D54E21;">'. __('Images names are not encoded','rencontre');
			else echo '<p style="color:red;">'.__('I don\'t know if it\'s encoded or not','rencontre');
			echo '.</p>';
			if(empty($rencOpt['imcode']) && $cod===1)
				{
				$rencOpt['imcode'] = 1;
				update_option('rencontre_options',$rencOpt);
				}
			?>
			<input type="submit" class="button-primary" onclick="document.getElementById('rencCode').value='code';" value="<?php _e('Encode all images names','rencontre');?>" />
			<input type="submit" class="button-primary" onclick="document.getElementById('rencCode').value='back';" value="<?php _e('Decode all images names','rencontre');?>" />
		</form>
	</div>
	<div style="clear:both;"></div>
	<?php
	}
function rencTabLog()
	{
	global $rencOpt;
	?>
	
	<form method="post" name="rencontre_options" action="admin.php?page=rencontre.php&renctab=log">
		<table class="form-table" style="max-width:600px;clear:none;z-index:5;">
			<tr valign="top">
				<th scope="row"><label><?php _e('AppID for Facebook connection (empty if not installed)', 'rencontre'); ?></label></th>
				<td><input type="text" class="regular-text" name="fblog" value="<?php if(isset($rencOpt['fblog'])) echo $rencOpt['fblog']; ?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php _e('Enable rapid registration', 'rencontre'); ?></label></th>
				<td><input type="checkbox" name="fastreg" value="1" <?php if(!empty($rencOpt['fastreg'])) echo 'checked'; ?>></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php _e('Do not request a new password after registration', 'rencontre'); ?></label></th>
				<td><input type="checkbox" name="passw" value="1" <?php if(!empty($rencOpt['passw'])) echo 'checked'; ?>></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php _e('Redirect to the plugin page after login', 'rencontre'); ?><br /></label></th>
				<td><input type="checkbox" name="logredir" value="1" <?php if(!empty($rencOpt['logredir'])) echo 'checked'; ?>></td>
			</tr>
		</table>
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save','rencontre') ?>" />
		</p>
	</form>
	<?php
	}
function rencTabDis()
	{
	global $rencOpt;
	?>
	
	<form method="post" name="rencontre_options" action="admin.php?page=rencontre.php&renctab=dis">
		<table class="form-table" style="max-width:600px;clear:none;z-index:5;">
			<tr valign="top">
				<th scope="row"><label><?php _e('Number of portrait in unconnected homepage', 'rencontre'); ?></label></th>
				<td>
					<select name="npa">
						<?php for($v=0;$v<91;++$v) echo '<option value="'.$v.'"'.((isset($rencOpt['npa'])&&$rencOpt['npa']==$v)?' selected':'').'>'.$v.'</option>'; ?>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php _e('Reload unconnected homepage', 'rencontre'); ?></label></th>
				<td>
					<select name="rlibre">
						<option value="0" <?php if(empty($rencOpt['rlibre'])) echo 'selected'; echo '>'.__('24h (recommended)', 'rencontre'); ?></option>
						<option value="43200" <?php if(isset($rencOpt['rlibre']) && $rencOpt['rlibre']=='43200') echo 'selected'; echo '>12'.__('h', 'rencontre'); ?></option>
						<option value="21600" <?php if(isset($rencOpt['rlibre']) && $rencOpt['rlibre']=='21600') echo 'selected'; echo '>6'.__('h', 'rencontre'); ?></option>
						<option value="10800" <?php if(isset($rencOpt['rlibre']) && $rencOpt['rlibre']=='10800') echo 'selected'; echo '>3'.__('h', 'rencontre'); ?></option>
						<option value="3600" <?php if(isset($rencOpt['rlibre']) && $rencOpt['rlibre']=='3600') echo 'selected'; echo '>1'.__('h', 'rencontre'); ?></option>
						<option value="1800" <?php if(isset($rencOpt['rlibre']) && $rencOpt['rlibre']=='1800') echo 'selected'; echo '>30'.__('min', 'rencontre'); ?></option>
						<option value="900" <?php if(isset($rencOpt['rlibre']) && $rencOpt['rlibre']=='900') echo 'selected'; echo '>15'.__('min', 'rencontre'); ?></option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php _e('Number of days to wait before presence homepage', 'rencontre'); ?></label></th>
				<td>
					<select name="jlibre">
						<?php for($v=0;$v<30;++$v) echo '<option value="'.$v.'"'.((isset($rencOpt['jlibre'])&&$rencOpt['jlibre']==$v)?' selected':'').'>'.$v.'</option>'; ?>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php _e('Max number of results per search', 'rencontre'); ?></label></th>
				<td>
					<select name="limit">
						<?php for($v=5;$v<51;++$v)
							{
							if($v>15) $v += 4;
							echo '<option value="'.$v.'"'.((isset($rencOpt['limit'])&&$rencOpt['limit']==$v)?' selected':'').'>'.$v.'</option>';
							}
						?>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php _e('Today\'s birthday', 'rencontre'); ?></label></th>
				<td><input type="checkbox" name="anniv" value="1" <?php if(!empty($rencOpt['anniv'])) echo 'checked'; ?>></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php _e('Profiles currently online', 'rencontre'); ?></label></th>
				<td><input type="checkbox" name="ligne" value="1" <?php if(!empty($rencOpt['ligne'])) echo 'checked'; ?>></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php _e('Enable chat', 'rencontre'); ?></label></th>
				<td><input type="checkbox" name="tchat" value="1" <?php if(!empty($rencOpt['tchat'])) echo 'checked'; ?>></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php _e('Enable Google-Map', 'rencontre'); ?></label></th>
				<td><input type="checkbox" name="map" value="1" <?php if(!empty($rencOpt['map'])) echo 'checked'; ?> onClick="document.getElementById('blocMapu').style.display=((this.checked==false)?'none':'table-row')"></td>
			</tr>
			<tr valign="top" id="blocMapu" style="<?php echo (empty($rencOpt['map'])?'display:none;':'display:table-row;'); ?>">
				<th scope="row"><label><?php _e('Google-Map API Key', 'rencontre'); ?></label></th>
				<td><input type="text" name="mapapi" value="<?php if(!empty($rencOpt['mapapi'])) echo $rencOpt['mapapi']; ?>"></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php _e('Number of photos', 'rencontre'); ?></label></th>
				<td>
					<select name="imnb">
						<?php if(empty($rencOpt['imnb']) || $rencOpt['imnb']>8) $rencOpt['imnb'] = 4;
						for($v=1; $v<9; ++$v)
							{
							echo '<option value="'.$v.'"'.(($rencOpt['imnb']==$v)?' selected':'').'>'.$v.'</option>';
							} ?>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php _e('View a discrete copyright on photos', 'rencontre'); ?></label></th>
				<td>
					<select name="imcopyright">
						<option value="0" <?php if(empty($rencOpt['imcopyright'])) echo 'selected'; ?>><?php _e('No', 'rencontre'); ?></option>
						<option value="1" <?php if(isset($rencOpt['imcopyright']) && $rencOpt['imcopyright']==1) echo 'selected'; ?>><?php _e('Upwardly inclined', 'rencontre'); ?></option>
						<option value="2" <?php if(isset($rencOpt['imcopyright']) && $rencOpt['imcopyright']==2) echo 'selected'; ?>><?php _e('Downwardly inclined', 'rencontre'); ?></option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php _e('Copyright text on pictures. Empty => Site URL.', 'rencontre'); ?></label></th>
				<td><input type="text" name="txtcopyright" value="<?php if(isset($rencOpt['txtcopyright'])) echo $rencOpt['txtcopyright']; ?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php _e('Members without photo are less visible', 'rencontre'); ?></label></th>
				<td><input type="checkbox" name="onlyphoto" value="1" <?php if(!empty($rencOpt['onlyphoto'])) echo 'checked'; ?>></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php _e('Members without photo cannot zoom other members', 'rencontre'); ?></label></th>
				<td><input type="checkbox" name="photoz" value="1" <?php if(!empty($rencOpt['photoz'])) echo 'checked'; ?>></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php _e('Members without photo, attention-catcher and ad cannot send message', 'rencontre'); ?></label></th>
				<td><input type="checkbox" name="pacamsg" value="1" <?php if(!empty($rencOpt['pacamsg'])) echo 'checked'; ?>></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php _e('Members without photo, attention-catcher and ad cannot make a report', 'rencontre'); ?></label></th>
				<td><input type="checkbox" name="pacasig" value="1" <?php if(!empty($rencOpt['pacasig'])) echo 'checked'; ?>></td>
			</tr>
		</table>
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save','rencontre') ?>" />
		</p>
	</form>
	<?php
	}
function rencTabMel()
	{
	global $rencOpt; global $rencDiv;
	?>
	
	<form method="post" name="rencontre_options" action="admin.php?page=rencontre.php&renctab=mel">
		<table class="form-table" style="max-width:600px;clear:none;z-index:5;">
			<tr valign="top">
				<th scope="row"><label><?php _e('Send an email to the user whose account is deleted', 'rencontre'); ?></label></th>
				<td><input type="checkbox" name="mailsupp" value="1" <?php if(!empty($rencOpt['mailsupp'])) echo 'checked'; ?>></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php _e('Automatic sending a summary email to members (shared daily)', 'rencontre'); ?></label></th>
				<td>
					<select name="mailmois">
						<option value="0" <?php if(empty($rencOpt['mailmois'])) echo 'selected'; ?>><?php _e('No', 'rencontre'); ?></option>
						<option value="1" <?php if(isset($rencOpt['mailmois']) && $rencOpt['mailmois']==1) echo 'selected'; ?>><?php _e('Monthly', 'rencontre'); ?></option>
						<option value="2" <?php if(isset($rencOpt['mailmois']) && $rencOpt['mailmois']==2) echo 'selected'; ?>><?php _e('Fortnightly', 'rencontre'); ?></option>
						<option value="3" <?php if(isset($rencOpt['mailmois']) && $rencOpt['mailmois']==3) echo 'selected'; ?>><?php _e('Weekly', 'rencontre'); ?></option>
					</select>
					<?php 
					$d2 = $rencDiv['basedir'].'/portrait/cache/rencontre_cron.txt';
					if(file_exists($d2)) echo "<p style='color:#D54E21;'>".__('Up this week', 'rencontre')."&nbsp;:&nbsp;<span style='color:#111;font-weight:700;'>".file_get_contents($d2)."</span>&nbsp;".__('mail/hour', 'rencontre')."<br />(".__('sent during maintenance', 'rencontre').")</p>";
					?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php _e('Introductory text for the monthly email (After hello login - Before the smiles and contact requests)', 'rencontre'); ?></label></th>
				<td><textarea name="textmail"><?php if(isset($rencOpt['textmail'])) echo stripslashes($rencOpt['textmail']); ?></textarea></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php _e('Also send an email for a smile', 'rencontre'); ?></label></th>
				<td><input type="checkbox" name="mailsmile" value="1" <?php if(!empty($rencOpt['mailsmile'])) echo 'checked'; ?>></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php _e('Automatically sending an email happy birthday members', 'rencontre'); ?></label></th>
				<td><input type="checkbox" name="mailanniv" value="1" <?php if(!empty($rencOpt['mailanniv'])) echo 'checked'; ?>></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php _e('Full text for the birthday mail (After hello pseudo)', 'rencontre'); ?></label></th>
				<td><textarea name="textanniv"><?php if(isset($rencOpt['textanniv'])) echo stripslashes($rencOpt['textanniv']); ?></textarea></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php _e('Max number of mails sent per hour', 'rencontre'); ?></label></th>
				<td>
					<select name="qmail">
						<?php for($v=0;$v<1000001;++$v)
							{
							echo '<option value="'.$v.'"'.((isset($rencOpt['qmail'])&&$rencOpt['qmail']==$v)?' selected':'').'>'.$v.'</option>';
							if($v<50) $v+=4;
							else if($v<150) $v+=9;
							else if($v<1000) $v+=49;
							else if($v<2000) $v+=199;
							else if($v<10000) $v+=499;
							else if($v<20000) $v+=1999;
							else if($v<100000) $v+=9999;
							else if($v<200000) $v+=19999;
							else $v+=99999;
							} ?>
					</select>
					
					<?php 
					$d2 = $rencDiv['basedir'].'/portrait/cache/rencontre_cronListe.txt';
					if(file_exists($d2)) echo "<p style='color:#D54E21;'>".__('Up this week', 'rencontre')."&nbsp;:&nbsp;<span style='color:#111;font-weight:700;'>".file_get_contents($d2)."</span>&nbsp;".__('mail/hour', 'rencontre')."<br />(".__('except during maintenance', 'rencontre').")</p>";
					?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php _e('No members without photo in automatic mails', 'rencontre'); ?></label></th>
				<td><input type="checkbox" name="mailph" value="1" <?php if(!empty($rencOpt['mailph'])) echo 'checked'; ?>></td>
			</tr>
		</table>
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save','rencontre') ?>" />
		</p>
	</form>
	<?php
	}
function rencTabCsv()
	{
	global $rencOpt; global $rencDiv;
	$a=array();
	if(!is_dir($rencDiv['basedir'].'/tmp/')) mkdir($rencDiv['basedir'].'/tmp/');
	if($h=opendir($rencDiv['basedir']."/tmp/"))
		{
		while(($file=readdir($h))!==false)
			{
			$ext=explode('.',$file);
			$ext=$ext[count($ext)-1];
			if($ext=='csv' && $file!='.' && $file!='..' && strpos($file,"rencontre")!==false) $a[]=$rencDiv['basedir']."/tmp/".$file;
			}
		closedir($h);
		}
	if(is_array($a)) array_map('unlink', $a);
	?>
	
	<h2><?php _e('Export members in CSV','rencontre') ?></h2>
	<div>
		<div style="margin:5px 0;">
			<select id="selectCsv">
				<option value="0"><?php _e('All members','rencontre');?></option>
				<option value="1"><?php _e('Active last month','rencontre');?></option>
				<option value="2"><?php _e('Active last 2 months','rencontre');?></option>
				<option value="6"><?php _e('Active last 6 months','rencontre');?></option>
				<option value="12"><?php _e('Active last 12 months','rencontre');?></option>
			</select>
			<br />
			<input type="checkbox" id="csvPhoto" style="vertical-align:bottom;margin-left:10px;" value="1" />
			<label><?php _e('Only members with photo','rencontre');?></label>
			<br />
			<input type="checkbox" id="csvAd" style="vertical-align:bottom;margin-left:10px;" value="1" />
			<label><?php _e('Only members with attention-catcher and ad','rencontre');?></label>
		</div>
		<a class="button-primary" href="javascript:void(0)" onclick="f_exportCsv();"><?php _e('Export in CSV','rencontre');?></a>
		<img id="waitCsv" src="<?php echo plugins_url('rencontre/images/loading.gif'); ?>" style="margin:0 0 -10px 20px;display:none;" />
		<a href="" style="display:none;margin:0 10px;" id="rencCsv" type='text/csv' >export_rencontre.csv</a>
		<div style="display:none;" id="photoCsv"><?php _e('Get back photos by FTP in wp-content/uploads/tmp/','rencontre') ?></div>
	</div>
	<hr />
	<h2><?php _e('Import members in CSV','rencontre') ?></h2>
		<ol>
			<li><?php _e('Put members photos in wp-content/uploads/tmp/photo_import/ by FTP before the start (right RW - no sub folder - .jpg - no zip).','rencontre') ?></li>
			<li><?php _e('Select your CSV file (.csv - no zip).','rencontre') ?></li>
			<li>"<?php _e('Import in CSV','rencontre');?>".</li>
		</ol>
	<p><?php _e('Make an export and look at it to get the right format (The first line is not treated).','rencontre') ?></p>
	<p><?php _e('In case of interruption during the import of photos, restart the procedure. Doubloons are killed.','rencontre') ?></p>
	<form name='rencCsv' action="<?php echo plugins_url('rencontre/inc/upload_csv.php'); ?>" method="post" enctype="multipart/form-data" target="uplFrame" onSubmit="startUpload();">
		<div>
			<label><?php _e('CSV File','rencontre') ?> : <label>
			<input name="fileCsv" type="file" />
			<img id="loadingCsv" src="<?php echo plugins_url('rencontre/images/loading.gif'); ?>" style="margin:0 0 -10px 20px;display:none;" />
		</div>
		<br />
		<div>
			<input type="submit" class="button-primary" name="submitCsv" value="<?php _e('Import in CSV','rencontre');?>" />
			<span id="impCsv1" style="margin:0 10px;display:none;"><?php _e('File loaded','rencontre');?></span>
			<span id="impCsv2" style="margin:0 10px;display:none;"><?php _e('Error !','rencontre');?></span>
			<span id="impCsv3" style="margin:0 10px;display:none;"><?php _e('Import data','rencontre');?>...</span>
			<span id="impCsv4" style="display:none;"><?php _e('completed','rencontre');?></span>
			<br /><span style="padding-left:130px">&nbsp;</span>
			<span id="impCsv5" style="margin:0 10px;display:none;"><?php _e('Photos Import','rencontre');?> : </span>
			<span id="impCsv6" style="margin-left:-5px;"></span>
			<span id="impCsv7" style="margin:0 10px;display:none;"><?php _e('Import completed','rencontre');?></span>
		</div>
	</form>
	<iframe id="uplFrame" name="uplFrame" src="#" style="width:0;height:0;border:0px solid #fff;">
	</iframe>
	<?php
	}
function rencTabPre()
	{
	$hoPre = false;
	if(has_filter('rencPremiumOptP', 'f_rencPremiumOptP')) $hoPre = apply_filters('rencPremiumOptP', $hoPre);
	if($hoPre) echo $hoPre;
	}
function rencMenuMembres()
	{
	wp_enqueue_script('rencontre', plugins_url('rencontre/js/rencontre-adm.js'));
	wp_enqueue_style( 'rencontre', plugins_url('rencontre/css/rencontre-adm.css'));
	require(dirname (__FILE__) . '/../lang/rencontre-js-admin-lang.php');
	wp_localize_script('rencontre', 'rencobjet', $lang);
	global $wpdb; global $rencOpt; global $rencDiv; global $rencVersion; global $rencCustom;
	$q = $wpdb->get_results("SELECT c_liste_categ, c_liste_valeur, c_liste_iso FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='d' or (c_liste_categ='p' and c_liste_lang='".substr($rencDiv['lang'],0,2)."') ");
	$rencDrap=''; $rencDrapNom='';
	foreach($q as $r)
		{
		if($r->c_liste_categ=='d') $rencDrap[$r->c_liste_iso] = $r->c_liste_valeur;
		else if($r->c_liste_categ=='p')$rencDrapNom[$r->c_liste_iso] = $r->c_liste_valeur;
		}
	?>
	<div class='wrap'>
		<div id="bulle"></div>
		<div class='icon32' id='icon-options-general'><br/></div>
		<h2>Rencontre&nbsp;<span style='font-size:60%;'>v<?php echo $rencVersion; ?></span></h2>
		<h2><?php _e('Members', 'rencontre'); ?></h2>
		<?php 
		$nm = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."rencontre_users");
		$np = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P WHERE R.user_id=P.user_id AND R.i_photo>0 AND CHAR_LENGTH(P.t_titre)>4 AND CHAR_LENGTH(P.t_annonce)>30");
		$nl=0; $d=$rencDiv['basedir'].'/session/';
		if($dh=opendir($d))
			{
			while(($file = readdir($dh))!==false) if($file!='.' && $file!='..' && filemtime($d.$file)>time()-180) ++$nl;
			closedir($dh);
			}
		echo "<p style='color:#D54E21;'>".__('Number of registered members','rencontre')."&nbsp;:&nbsp;<span style='color:#111;font-weight:700;'>".$nm."</span></p>";
		echo "<p style='color:#D54E21;'>".__('Number of members with profile and photo','rencontre')."&nbsp;:&nbsp;<span style='color:#111;font-weight:700;'>".$np."</span></p>";
		echo "<p style='color:#D54E21;'>".__('Online now','rencontre')."&nbsp;:&nbsp;<span style='color:#111;font-weight:700;'>".$nl."</span></p>";
		echo "\r\n".'<script src="'.plugins_url('rencontre/js/jquery.flot.min.js').'"></script>'."\r\n";
		echo '<script src="'.plugins_url('rencontre/js/jquery.flot.time.min.js').'"></script>'."\r\n";
		echo '<div class="button-primary" onClick="rencStat()">'.__('Statistic','rencontre').'</div><div id="rencStat" style="display:none;"><div id="rencStat1" style="float:left;height:300px;width:600px;"></div><div id="rencStat2" style="float:left;height:300px;width:400px;"></div></div><div style="clear:left;"></div>';
		// 1. ALL MEMBERS
		if(!isset($_GET["id"]))
			{ ?>
		
			<?php 
			$q = $wpdb->get_results("SELECT
					U.ID,
					U.user_login
				FROM
					".$wpdb->prefix."users U
				LEFT OUTER JOIN
					".$wpdb->prefix."rencontre_users R
				ON
					U.ID=R.user_id
				WHERE
					R.user_id IS NULL
				ORDER BY
					U.user_login
				");
			if($q)
				{
				
				echo '<div style="float:right;margin:0 0 0 10px;"><select id="rencNewMember"><option value="0" selected="selected">-</option>';
				foreach($q as $r)
					{
					if(!user_can($r->ID,'activate_plugins') && !user_can($r->ID,'switch_themes')) echo '<option value="'.$r->ID.'">'.$r->user_login.'</option>';
					}
				echo '</select><div class="button-primary" onClick="f_newMember(document.getElementById(\'rencNewMember\'))">'.__('Add new from WordPress','rencontre').'</div></div>';
				} ?>
		
		<form name="rencPseu" method="post" action="">
			<label><?php _e('Alias or email or ID', 'rencontre'); ?> : </label>
			<input type="text" name="pseu" />
			<input type="submit" class="button-primary" value="<?php _e('Find', 'rencontre'); ?>" />
		</form>
			<?php
			$ho = false; if(has_filter('rencMurP', 'f_rencMurP')) $ho = apply_filters('rencMurP', $ho); if($ho) echo $ho;
			if(isset($_POST["a1"]) && $_POST["a1"] && $_POST["a2"]) 
				{
				if($_POST["a2"]=='b0' || $_POST["a2"]=='b1' || $_POST["a2"]=='m0' || $_POST["a2"]=='m1')
					{
					// Status : blocked = +1 ; no message = +2 ; (both : 3) ; fastreg : 4
					$st = $wpdb->get_var("SELECT i_status FROM ".$wpdb->prefix."rencontre_users WHERE user_id='".$_POST["a1"]."' LIMIT 1");
					if($t!=4)
					if(!rencistatus($st,2))
						{
						if($_POST["a2"]=='b1') $st = rencistatusSet($st,0,0); // ($st>1?2:0);
						else if($_POST["a2"]=='b0') $st = rencistatusSet($st,0,1); // ($st>1?3:1);
						else if($_POST["a2"]=='m1') $st = rencistatusSet($st,1,0); // (($st==1||$st==3)?1:0);
						else if($_POST["a2"]=='m0') $st = rencistatusSet($st,1,1); // (($st==1||$st==3)?3:2);
						$wpdb->update($wpdb->prefix.'rencontre_users', array('i_status'=>$st), array('user_id'=>$_POST["a1"]));
						}
					}
				else
					{
					f_userSupp($_POST["a1"],$_POST["a2"],1);
					if(!empty($rencOpt['mailsupp']))
						{
						$q = $wpdb->get_var("SELECT user_email FROM ".$wpdb->prefix."users WHERE ID='".$_POST["a1"]."' LIMIT 1");
						$objet  = wp_specialchars_decode($rencDiv['blogname'], ENT_QUOTES).' - '.__('Account deletion','rencontre');
						$message  = __('Your account has been deleted','rencontre');
						@wp_mail($q, $objet, $message);
						}
					}
				}
			$tri="";
			$ho = false; if(has_filter('rencMemP', 'f_rencMemP')) $ho = apply_filters('rencMemP', $ho); // ouput : array()
			if(isset($_GET['tri']))
				{
				$p = 'c_pays';
				if(isset($rencCustom['pays']) && isset($rencCustom['region'])) $p = 'c_ville';
				else if(isset($rencCustom['pays'])) $p = 'c_region';
				if($_GET['tri']=='id') $tri='ORDER BY R.user_id ASC';
				else if($_GET['tri']=='Rid') $tri='ORDER BY R.user_id DESC';
				else if($_GET['tri']=='pseudo') $tri='ORDER BY U.user_login ASC';
				else if($_GET['tri']=='Rpseudo') $tri='ORDER BY U.user_login DESC';
				else if($_GET['tri']=='age') $tri='ORDER BY R.d_naissance DESC';
				else if($_GET['tri']=='Rage') $tri='ORDER BY R.d_naissance ASC';
				else if($_GET['tri']=='pays') $tri='ORDER BY R.'.$p.' ASC, P.d_modif DESC';
				else if($_GET['tri']=='Rpays') $tri='ORDER BY R.'.$p.' DESC, P.d_modif DESC';
				else if($_GET['tri']=='modif') $tri='ORDER BY P.d_modif ASC';
				else if($_GET['tri']=='Rmodif') $tri='ORDER BY P.d_modif DESC';
				else if($_GET['tri']=='ip') $tri='ORDER BY R.c_ip ASC';
				else if($_GET['tri']=='Rip') $tri='ORDER BY R.c_ip DESC';
				else if($_GET['tri']=='signal') $tri='ORDER BY length(P.t_signal) DESC, P.d_modif DESC';
				else if($_GET['tri']=='action') $tri='ORDER BY R.i_status DESC, P.d_modif DESC';
				else if($ho!==false && isset($ho[5]) && isset($ho[6]) && $_GET['tri']==$ho[5]) $tri=$ho[6];
				}
			else $tri='ORDER BY P.d_modif DESC';
			if(isset($_POST['pseu']) && $_POST['pseu']!="")
				{
				$tri = "and (U.user_login='".$_POST['pseu']."' or U.user_email='".$_POST['pseu']."' or U.ID='".$_POST['pseu']."') ".$tri;
				$pagenum = 1;
				$page_links= 1;
				}
			else $pagenum = isset($_GET['pagenum'])?absint($_GET['pagenum']):1;
			$limit = 100;
			$q = $wpdb->get_results("SELECT 
					U.ID, 
					U.user_login, 
					U.display_name, 
					U.user_registered, 
					R.c_ip, 
					R.c_pays, 
					R.c_region, 
					R.c_ville, 
					R.d_naissance, 
					R.i_taille, 
					R.i_poids, 
					R.i_sex, 
					R.i_zage_min, 
					R.i_zage_max, 
					R.i_zrelation, 
					R.i_photo, 
					P.d_modif, 
					P.t_titre, 
					P.t_annonce".($ho?', '.$ho[0].'':'')." 
				FROM (".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users R, ".$wpdb->prefix."rencontre_users_profil P) ".($ho?$ho[1]:'')." 
				WHERE 
					R.user_id=P.user_id 
					and R.user_id=U.ID ".$tri."
				LIMIT ".(($pagenum-1)*$limit).",".$limit);
			if(!isset($page_links))
				{
				$total = $wpdb->get_var("SELECT COUNT(user_id) FROM ".$wpdb->prefix . "rencontre_users");
				$page_links = paginate_links(array('base'=>add_query_arg('pagenum','%#%'),'format'=>'','prev_text'=>'&laquo;','next_text'=>'&raquo;','total'=>ceil($total/$limit),'current'=>$pagenum,'mid_size'=>5));
				}
			if($page_links) echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">'.$page_links.'</div></div>';
			?>
			<form name='listUser' method='post' action=''>
			<input type='hidden' name='a1' value='' />
			<input type='hidden' name='a2' value='' />
			<table class="membre">
				<tr>
					<td><a href="admin.php?page=rencmembers&tri=<?php if(isset($_GET['tri']) && $_GET['tri']=='id') echo 'R'; ?>id" title="<?php _e('Sort','rencontre'); ?>">ID</a></td>
					<td><?php _e('Photo','rencontre');?></td>
					<td><a href="admin.php?page=rencmembers&tri=<?php if(isset($_GET['tri']) && $_GET['tri']=='pseudo') echo 'R'; ?>pseudo" title="<?php _e('Sort','rencontre'); ?>"><?php _e('Alias','rencontre');?></a></td>
					<td><?php _e('Sex','rencontre');?></td>
					<td><a href="admin.php?page=rencmembers&tri=<?php if(isset($_GET['tri']) && $_GET['tri']=='age') echo 'R'; ?>age" title="<?php _e('Sort','rencontre'); ?>"><?php _e('Age','rencontre');?><a></td>
					<?php if(!isset($rencCustom['size'])) echo '<td>'.__('Size','rencontre').'</td>'; ?>
					<?php if(!isset($rencCustom['weight'])) echo '<td>'.__('Weight','rencontre').'</td>'; ?>
					<td><?php _e('Search','rencontre');?></td>
					<td><?php _e('Hang','rencontre');?></td>
					<?php if(!isset($rencCustom['place'])) echo '<td><a href="admin.php?page=rencmembers&tri='.((isset($_GET['tri']) && $_GET['tri']=='pays')?'R':'').'pays" title="'.__('Sort','rencontre').'">'.__('Place','rencontre').'</a></td>'; ?>
					<td><a href="admin.php?page=rencmembers&tri=<?php if(isset($_GET['tri']) && $_GET['tri']=='modif') echo 'R'; ?>modif" title="<?php _e('Sort','rencontre'); ?>"><?php _e('Ad (change)','rencontre');?></a><br /><em style="font-size:.9em;color:#777;"><?php _e('Registered','rencontre');?></em></td>
					<td><a href="admin.php?page=rencmembers&tri=<?php if(isset($_GET['tri']) && $_GET['tri']=='ip') echo 'R'; ?>ip" title="<?php _e('Sort','rencontre'); ?>"><?php _e('IP address','rencontre');?></a></td>
					<td><a href="admin.php?page=rencmembers&tri=signal" title="<?php _e('Sort','rencontre'); ?>"><?php _e('Reporting','rencontre');?></a></td>
					<td><a href="admin.php?page=rencmembers&tri=action" title="<?php _e('Sort','rencontre'); ?>"><?php _e('Action','rencontre');?></a></td>
					<?php if($ho) echo '<td>'.$ho[2].'</td>'; ?>
				</tr>
			<?php
			$categ="";
			$jailaction = 0; if(has_filter('rencJailActionP', 'f_rencJailActionP')) $jailaction = 1;
			foreach($q as $s)
				{
				$q1 = $wpdb->get_row("SELECT
						P.t_signal,
						R.i_status
					FROM 
						".$wpdb->prefix."rencontre_users_profil P,
						".$wpdb->prefix."rencontre_users R 
					WHERE 
						P.user_id='".$s->ID."' and
						P.user_id=R.user_id
					LIMIT 1
					");
				$signal = ($q1?json_decode($q1->t_signal,true):0);
				$block = $q1?rencistatus($q1->i_status,0):0; // ($q1?(($q1->i_status==1||$q1->i_status==3)?1:0):0); // weight : 1
				$blockmail = $q1?rencistatus($q1->i_status,1):0; // ($q1?(($q1->i_status==2||$q1->i_status==3)?1:0):0); // weight : 2
				$fastreg = $q1?rencistatus($q1->i_status,2):0; // ($q1?($q1->i_status==4?1:0):0);
				echo '<tr>';
				echo '<td><a href="admin.php?page=rencmembers&id='.$s->ID.'" title="'.__('See','rencontre').'" onMouseover="f_bulleOn(this,\''.urlencode($s->t_annonce).'\')" onMouseout="f_bulleOff()">'.$s->ID.'</a></td>';
				echo '<td><a href="admin.php?page=rencmembers&id='.$s->ID.'" title="'.__('See','rencontre').'"><img class="tete" src="'.($s->i_photo!=0?get_bloginfo('url').'/wp-content/uploads/portrait/'.floor(($s->ID)/1000).'/'.Rencontre::f_img((($s->ID)*10).'-mini').'.jpg?r='.rand().'" alt="" /></a></td>':plugins_url('rencontre/images/no-photo60.jpg').'" alt="'.$s->display_name.'" /></td>');
				echo '<td>'.$s->user_login.'</td>';
				if($s->i_sex==0) echo '<td><img class="imgsex" src="'.plugins_url('rencontre/images/men32.png').'" alt="'.$rencOpt['iam'][$s->i_sex].'" title="'.$rencOpt['iam'][$s->i_sex].'" /></td>';
				else if($s->i_sex==1) echo '<td><img class="imgsex" src="'.plugins_url('rencontre/images/girl32.png').'" alt="'.$rencOpt['iam'][$s->i_sex].'" title="'.$rencOpt['iam'][$s->i_sex].'" /></td>';
				else if(isset($rencOpt['iam'][$s->i_sex])) echo '<td>'.$rencOpt['iam'][$s->i_sex].'</td>';
				else echo '<td></td>';
				echo '<td>'.Rencontre::f_age($s->d_naissance).'</td>';
				if(!isset($rencCustom['size'])) echo '<td>'.(!isset($rencCustom['sizeu'])?$s->i_taille.' '.__('cm','rencontre'):floor($s->i_taille/24-1.708).' '.__('ft','rencontre').' '.round(((($s->i_taille/24-1.708)-floor($s->i_taille/24-1.708))*12),1).' '.__('in','rencontre')).'</td>';
				if(!isset($rencCustom['weight'])) echo '<td>'.((!isset($rencCustom['weightu']))?$s->i_poids.' '.__('kg','rencontre'):($s->i_poids*2+10).' '.__('lbs','rencontre')).'</td>';
				if(isset($rencOpt['for'][$s->i_zrelation])) echo '<td>'.$rencOpt['for'][$s->i_zrelation]; 
				else if($s->i_zrelation==99) echo '<td>'.__('multiple choice','rencontre');
				else echo '<td>'.$s->i_zrelation;
				if($s->i_zage_min) echo '<br />'.$s->i_zage_min.' '. __('to','rencontre').' '.$s->i_zage_max.'</td>'; else echo '</td>';
				echo '<td>'.$s->t_titre.'</td>';
				if(!isset($rencCustom['place']))
					{
					echo '<td>';
					if(!isset($rencCustom['country']) && isset($rencDrapNom[$s->c_pays]) && $s->c_pays!="") echo '<img class="flag" src="'.plugins_url('rencontre/images/drapeaux/').$rencDrap[$s->c_pays].'" alt="'.$rencDrapNom[$s->c_pays].'" title="'.$rencDrapNom[$s->c_pays].'" /><br />';
					else if(!isset($rencCustom['country'])) echo $s->c_pays.'<br />';
					if(!isset($rencCustom['region'])) echo $s->c_region.'<br />';
					echo $s->c_ville.'</td>';
					}
				echo '<td>'.$s->d_modif.'<br /><em style="font-size:.9em;color:#777;">'.substr($s->user_registered,0,10).'</em></td>';
				$c = '';
				if(strpos($s->c_ip,':')===false)
					{
					$b = explode('.', $s->c_ip); $geoip = '';
					foreach($b as $r)
						{
						if(strlen($r)==1 && $c) $c.='00'.$r;
						else if(strlen($r)==2 && $c) $c.='0'.$r;
						else if($r!='0') $c.=$r;
						}
					}
				if($c) $geoip = $wpdb->get_row("SELECT
						country,
						ip_end
					FROM
						".$wpdb->prefix."rencontre_dbip
					WHERE
						ip_start<=".$c."
					ORDER BY ip_start DESC
					LIMIT 1
					");
				$ipays = (($geoip && isset($rencDrap[$geoip->country]) && $geoip->ip_end>=$c && $c!='127000000001' && $c!='127000000090')?$rencDrap[$geoip->country]:null);
				echo '<td>'.$s->c_ip.(($ipays)?'<br/><img class="flag" src="'.plugins_url('rencontre/images/drapeaux/').$ipays.'" alt="'.$geoip->country.'" title="'.$geoip->country.'" />':'').'</td>';
				echo '<td>'.((count($signal))?count($signal):'').'</td>';
				echo '<td>';
				if(!$fastreg)
					{
					echo '<a href="javascript:void(0)" class="rencBlock'.($block?'off':'on').'" onClick="f_block('.$s->ID.',\'b'.$block.'\')" title="'.($block?__('Unblock this member','rencontre'):__('Block this member','rencontre')).'"></a><br />';
					echo '<a href="javascript:void(0)" class="rencMail'.($blockmail?'off':'on').'" onClick="f_blockMail('.$s->ID.',\'m'.$blockmail.'\')" title="'.($blockmail?__('Allow sending message','rencontre'):__('Prohibit contact','rencontre')).'"></a><br />';
					}
				else echo '<img src="'.plugins_url('rencontre/images/fastreg.png').'" alt="" title="'.__('New fast account not completed','rencontre').'" /><br />';
				echo '<a href="javascript:void(0)" class="rencSupp" onClick="f_fin('.$s->ID.',\''.$s->user_login.'\')" title="'.__('Remove - Black list email','rencontre').'"></a>';
				if($jailaction) apply_filters('rencJailActionP', $s);
				echo '</td>';
				if($ho) echo '<td>'.(($s->{$ho[3]}!='')?$ho[4][$s->{$ho[3]}]:'').'</td>';
				echo '</tr>';
				}
			?>
			</table>
			</form>
		<?php
			if($page_links) echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">'.$page_links.'</div></div>';
			}
		// 2. MEMBER PROFIL
		else
			{
			$id = $_GET["id"];
			$q = $wpdb->get_results("SELECT
					P.id,
					P.c_categ,
					P.c_label,
					P.t_valeur,
					P.i_type,
					P.c_genre
				FROM
					".$wpdb->prefix."rencontre_profil P
				WHERE
					P.c_lang='".substr($rencDiv['lang'],0,2)."' and
					P.c_categ!='' and
					P.c_label!='' and
					P.i_poids<5
				ORDER BY P.i_categ,P.i_label");
			$sex = $wpdb->get_var("SELECT i_sex FROM ".$wpdb->prefix."rencontre_users WHERE user_id=".$id." LIMIT 1");
			$in = '';
			foreach ($q as $r)
				{
				if($r->c_genre==='0' || strpos($r->c_genre,','.$sex.',')!==false)
					{
					$in[$r->id][0] = $r->i_type;
					$in[$r->id][1] = $r->c_categ;
					$in[$r->id][2] = $r->c_label;
					$in[$r->id][3] = $r->t_valeur;
					}
				}
			if(isset($_POST["a1"]) &&  isset($_POST["rnd"]) && isset($_SESSION["rnd"]) && $_POST["rnd"]==$_SESSION["rnd"])
				{
				if($_POST["a1"]=="suppImg") RencontreWidget::suppImg($_POST["a2"],$id);
				if($_POST["a1"]=="plusImg") RencontreWidget::plusImg($_POST["a2"],$id);
				if($_POST["a1"]=="suppImgAll") RencontreWidget::suppImgAll($id);
				}
			if(isset($_POST["a1"]) && $_POST["a1"]=="sauvProfil") sauvProfilAdm($in,$id);
			$s = $wpdb->get_row("SELECT 
					U.ID, 
					U.display_name, 
					R.c_pays, 
					R.c_region,
					R.c_ville, 
					R.e_lat,
					R.e_lon,
					R.i_sex,
					R.d_naissance,
					R.i_taille,
					R.i_poids,
					R.i_zsex,
					R.c_zsex,
					R.i_zage_min,
					R.i_zage_max,
					R.i_zrelation,
					R.c_zrelation,
					R.i_photo, 
					R.i_status,
					P.t_titre, 
					P.t_annonce, 
					P.t_profil,
					P.t_action
				FROM 
					".$wpdb->prefix."users U, 
					".$wpdb->prefix."rencontre_users R, 
					".$wpdb->prefix."rencontre_users_profil P 
				WHERE 
					R.user_id=".$id." 
					and R.user_id=P.user_id 
					and R.user_id=U.ID
				LIMIT 1
				");
			$_SESSION['rnd'] = md5(rand(0,10000000));
			?>
			
			<h3><?php _e('Change My Profile','rencontre');?></h3>
			<div class="bouton"><a href="javascript:void(0)" onclick="javascript:history.back();"><?php _e('Previous page','rencontre');?></a></div>
			<div class="bouton"><a href="<?php echo admin_url(); ?>admin.php?page=rencmembers"><?php _e('Back Members','rencontre');?></a></div>
			<div class="rencPortrait">
				<form name='portraitChange' method='post' enctype="multipart/form-data" action=''>
					<input type='hidden' name='a1' value='' />
					<input type='hidden' name='a2' value='' />
					<input type='hidden' name='page' value='' />
					<input type="hidden" name="rnd" value="<?php echo $_SESSION['rnd']; ?>" />
					<div id="portraitSauv"><span onClick="f_sauv_profil(<?php echo $id; ?>)"><?php _e('Save profile','rencontre');?></span></div>
					<div class="petiteBox portraitPhoto left">
						<div class="rencBox">
							<img id="portraitGrande" src="<?php if(($s->i_photo)!=0) echo $rencDiv['baseurl'].'/portrait/'.floor($id/1000).'/'.Rencontre::f_img(($id*10).'-grande').'.jpg?r='.rand(); else echo plugins_url('rencontre/images').'/no-photo600.jpg'; ?>" width=250 height=250 alt="" />
							<div class="rencBlocimg">
							<?php for($v=$id*10;$v<=$s->i_photo;++$v) // cleaning
								{
								if(!file_exists($rencDiv['basedir'].'/portrait/'.floor($id/1000).'/'.Rencontre::f_img(($v).'-mini').'.jpg')) RencontreWidget::suppImg($v,$id);
								}
							for ($v=0;$v<(empty($rencOpt['imnb'])?4:$rencOpt['imnb']);++$v)
								{
								if($s->i_photo>=$id*10+$v)
									{
									echo '<a href="javascript:void(0)" onClick="f_supp_photo('.($id*10+$v).')"><img onMouseOver="f_vignette_change('.($id*10+$v).',\''.Rencontre::f_img(($id*10+$v).'-grande').'\')" class="portraitMini" src="'.$rencDiv['baseurl'].'/portrait/'.floor($id/1000).'/'.Rencontre::f_img(($id*10+$v).'-mini').'.jpg?r='.rand().'" alt="'.__('Click to delete','rencontre').'" title="'.__('Click to delete','rencontre').'" /></a>'."\n";
									echo '<img style="display:none;" src="'.$rencDiv['baseurl'].'/portrait/'.floor($id/1000).'/'.Rencontre::f_img(($id*10+$v).'-grande').'.jpg?r='.rand().'" />'."\n";
									}
								else { ?><a href="javascript:void(0)" onClick="f_plus_photo(<?php echo $s->i_photo; ?>)"><img class="portraitMini" src="<?php echo plugins_url('rencontre/images/no-photo60.jpg'); ?>" alt="<?php _e('Click to add a photo','rencontre'); ?>" title="<?php _e('Click to add a photo','rencontre'); ?>" /></a>
								<?php } } ?>
							</div>
							<div id="changePhoto"></div>
							<div class="bouton"><a href="javascript:void(0)" onClick="f_suppAll_photo()"><?php _e('Delete all photos','rencontre');?></a></div>
						</div>
					</div>
					<div class="grandeBox right">
						<div class="rencBox">
							<?php
							if($s->c_pays!="") echo '<img class="flag" src="'.plugins_url('rencontre/images/drapeaux/').$rencDrap[$s->c_pays].'" alt="'.$rencDrapNom[$s->c_pays].'" title="'.$rencDrapNom[$s->c_pays].'" />'; ?>

							<div class="grid_10">
								<h3><?php echo $s->display_name; ?></h3>
								<div class="ville"><?php echo $s->c_ville; ?></div>
								<label><?php _e('My attention-catcher','rencontre');?></label><br />
								<input type="text" name="titre" value="<?php echo $s->t_titre; ?>" /><br /><br />
								<label><?php _e('My ad','rencontre');?></label><br />
								<textarea name="annonce" rows="10" style="width:95%;"><?php echo $s->t_annonce; ?></textarea>
							</div>
						</div>
					</div>
					<div class="pleineBox clear">
						<div class="rencBox">
							<div class="compte">
								<span><?php _e('I am','rencontre');?></span><br>
								<p>
									<select name="sex" size=2>
										<?php for($v=(isset($rencCustom['sex'])?2:0);$v<(isset($rencCustom['sex'])?count($rencOpt['iam']):2);++$v) echo '<option value="'.$v.'" '.(($s->i_sex==$v)?' selected':'').'>'.$rencOpt['iam'][$v].'</option>'; ?>
									</select>
								</p>
							</div>
						<?php if(!isset($rencCustom['born'])) { 
							list($Y, $m, $j) = explode('-', $s->d_naissance); ?>
							<div class="compte">
								<span><?php _e('Born','rencontre'); ?></span><br>
								<p>
									<select name="jour" size=2>
										<?php for($v=1;$v<32;++$v) echo '<option value="'.$v.'"'.(($v==$j)?' selected':'').'>'.$v.'</option>'; ?>
										
									</select>
									<select name="mois" size=2>
										<?php for($v=1;$v<13;++$v) echo '<option value="'.$v.'"'.(($v==$m)?' selected':'').'>'.$v.'</option>'; ?>
										
									</select>
									<select name="annee" size=2>
										<?php $y=(date('Y'));
										$oldmax = $y-(isset($rencCustom['agemax'])?intval($rencCustom['agemax']):99)-1;
										$oldmin = $y-(isset($rencCustom['agemin'])?intval($rencCustom['agemin']):18)+1;
										for($v=$oldmax;$v<$oldmin;++$v) echo '<option value="'.$v.'"'.(($v==$Y)?' selected':'').'>'.$v.'</option>'; ?>
										
									</select>
								</p>
							</div>
						<?php } ?>
						<?php if(!isset($rencCustom['place']) && !isset($rencCustom['country'])) { ?>
							<div class="compte">
								<span><?php _e('My country','rencontre'); ?></span><br>
								<p>
									<select id="rencPays" name="pays" size=2 onChange="f_region_select_adm(this.options[this.selectedIndex].value,'<?php echo admin_url('admin-ajax.php'); ?>','regionSelect2');">
										<?php RencontreWidget::f_pays($s->c_pays); ?>
										
									</select>
								</p>
							</div>
						<?php } ?>
						<?php if(!isset($rencCustom['place']) && !isset($rencCustom['region'])) { ?>
							<div class="compte">
								<span><?php _e('My region','rencontre'); ?></span><br>
								<p>
									<select id="regionSelect2" size=2 name="region">
										<?php if($s->c_region) RencontreWidget::f_regionBDD($s->c_region,$s->c_pays); else RencontreWidget::f_regionBDD(1,$s->c_pays); ?>
										
									</select>
								</p>
							</div>
						<?php } ?>
						<?php if(!isset($rencCustom['place'])) { ?>
							<div class="compte">
								<span><?php _e('My city','rencontre'); ?></span><br>
								<p style="line-height:1em;">
									<input id="rencVille" name="ville" type="text" size="18" value="<?php echo $s->c_ville; ?>" <?php if(function_exists('wpGeonames')) echo 'onkeyup="f_city(this.value,\''.admin_url('admin-ajax.php').'\','.(!isset($rencCustom['country'])?'document.getElementById(\'rencPays\').options[document.getElementById(\'rencPays\').selectedIndex].value':'\''.$s->c_pays.'\'').',0);"'; ?> />
									<br /><?php _e('Reset GPS','rencontre'); ?> <input type="checkbox" name="resetgps" style="width:auto;margin:0;padding:0;" value="1" />
									<br /><em style="display:block;text-align:right;margin-top:-5px;font-size:.8em;letter-spacing:-1px;color:#888;"><?php echo $s->e_lat.'|'.$s->e_lon ?></em>
								</p>
							</div>
						<?php } ?>
						<?php if(!isset($rencCustom['size'])) { ?>
							<div class="compte">
								<span><?php _e('My size','rencontre'); ?></span><br>
								<p>
									<select name="taille" size=2>
										<?php for($v=140;$v<220;++$v)
											{
											if(empty($rencCustom['sizeu'])) echo '<option value="'.$v.'"'.(($v==$s->i_taille)?' selected':'').'>'.$v.'&nbsp;'.__('cm','rencontre').'</option>';
											else echo '<option value="'.$v.'"'.(($v==$s->i_taille)?' selected':'').'>'.(floor($v/24-1.708)).'&nbsp;'.__('ft','rencontre').'&nbsp;'.(round(((($v/24-1.708)-floor($v/24-1.708))*12),1)).'&nbsp;'.__('in','rencontre').'</option>';
											} ?>
									</select>
								</p>
							</div>
						<?php } ?>
						<?php if(!isset($rencCustom['weight'])) { ?>
							<div class="compte">
								<span><?php _e('My weight','rencontre'); ?></span><br>
								<p>
									<select name="poids" size=2>
										<?php for($v=40;$v<140;++$v)
											{
											if(empty($rencCustom['weightu'])) echo '<option value="'.$v.'"'.(($v==$s->i_poids)?' selected':'').'>'.$v.'&nbsp;'.__('kg','rencontre').'</option>';
											else echo '<option value="'.$v.'"'.(($v==$s->i_poids)?' selected':'').'>'.($v*2+10).'&nbsp;'.__('lbs','rencontre').'</option>';
											} ?>
									</select>			
								</p>
							</div>
						<?php } ?>
							<div class="compte">
								<span><?php _e('I\'m looking for','rencontre'); ?></span><br>
								<p>
									<?php
									if(!isset($rencCustom['multiSR']) || !$rencCustom['multiSR'])
										{
										echo '<select name="zsex" size=2>';
										for($v=(isset($rencCustom['sex'])?2:0);$v<(isset($rencCustom['sex'])?count($rencOpt['iam']):2);++$v) echo '<option value="'.$v.'" '.(($s->i_zsex==$v)?' selected':'').'>'.$rencOpt['iam'][$v].'</option>';
										echo '</select>';
										}
									else
										{
										for($v=(isset($rencCustom['sex'])?2:0);$v<(isset($rencCustom['sex'])?count($rencOpt['iam']):2);++$v) echo $rencOpt['iam'][$v].'&nbsp;<input type="checkbox" name="zsex[]" value="'.$v.'" '.((strpos($s->c_zsex,','.$v.',')!==false)?'checked':'').' />';
										}
									?>
								</p>
							</div>
						<?php if(!isset($rencCustom['born'])) { ?>
							<div class="compte">
								<span><?php _e('Age min/max','rencontre'); ?></span><br>
								<p>
									<select name="zageMin" size=2 onChange="f_minA(this.options[this.selectedIndex].value,'portraitChange','zageMin','zageMax');">
										<?php for($v=(isset($rencCustom['agemin'])?intval($rencCustom['agemin']):18);$v<(isset($rencCustom['agemax'])?intval($rencCustom['agemax']):99);++$v) { ?>
										
										<option value="<?php echo $v; ?>"<?php if($v==$s->i_zage_min) echo ' selected'; ?>><?php echo $v; ?>&nbsp;<?php _e('years','rencontre'); ?></option>
										<?php } ?>
										
									</select>
									<select name="zageMax" size=2 onChange="f_maxA(this.options[this.selectedIndex].value,'portraitChange','zageMin','zageMax');">
										<?php for($v=(isset($rencCustom['agemin'])?intval($rencCustom['agemin']):18)+1;$v<(isset($rencCustom['agemax'])?intval($rencCustom['agemax']):99)+1;++$v) { ?>
									
										<option value="<?php echo $v; ?>"<?php if($v==$s->i_zage_max) echo ' selected'; ?>><?php echo $v; ?>&nbsp;<?php _e('years','rencontre'); ?></option>
										<?php } ?>
										
									</select>
								</p>
							</div>
						<?php } ?>
							<div class="compte">
								<span><?php _e('For','rencontre'); ?></span><br>
								<p>
									<?php
									if(!isset($rencCustom['multiSR']) || !$rencCustom['multiSR'])
										{
										echo '<select name="zrelation" size=2>';
										for($v=(isset($rencCustom['relation'])?3:0);$v<(isset($rencCustom['relation'])?count($rencOpt['for']):3);++$v) echo '<option value="'.$v.'" '.(($s->i_zrelation==$v)?' selected':'').'>'.$rencOpt['for'][$v].'</option>';
										echo '</select>';
										}
									else
										{
										for($v=(isset($rencCustom['relation'])?3:0);$v<(isset($rencCustom['relation'])?count($rencOpt['for']):3);++$v) echo $rencOpt['for'][$v].'&nbsp;<input type="checkbox" name="zrelation[]" value="'.$v.'" '.((strpos($s->c_zrelation,','.$v.',')!==false)?'checked':'').' />';
										}
									?>
								</p>
							</div>
							<div class="compte">
								<span><?php _e('Reset','rencontre'); ?></span><br>
								<p style="line-height:1.5em;">
									<?php _e('Actions (contact request...)','rencontre'); ?> <input type="checkbox" name="resetact" style="width:auto;margin:0;padding:0;" value="1" />
									<br /><?php _e('Reports','rencontre'); ?> <input type="checkbox" name="resetsig" style="width:auto;margin:0;padding:0;" value="1" />
									<?php if(strpos($s->t_action,',nomail,')!==false)
										{
										echo '<br /><em style="display:block;text-align:right;margin-top:-5px;font-size:.8em;letter-spacing:0;color:#888;">';
										_e('no mail','rencontre');
										echo '</em><input type="hidden" name="nomail" value="1" />';
										} ?>
								</p>
							</div>
						<?php if($s->i_status==4) { ?>
							<div class="compte">
								<span><?php _e('Confirmation email','rencontre'); ?></span><br>
								<p style="line-height:1.5em;">
								<?php _e('Confirm','rencontre'); ?> <input type="checkbox" name="confmail" style="width:auto;margin:0;padding:0;" value="1" />
								</p>
							</div>
						<?php } ?>

							<div style="clear:both;"></div>
						</div>
					</div>
					<div class="pleineBox portraitProfil">
						<div class="rencBox">
							<div class="br"></div>
						<?php
						$profil = json_decode($s->t_profil,true);
						$out = '';
						if($profil) foreach ($profil as $r)
							{
							$out[$r['i']] = $r['v'];
							}
						$out1="";$out2=""; $c=0; $d="";
						foreach ($in as $r=>$r1)
							{
							if($d!=$r1[1]) // nouvel onglet
								{
								if($d!="") $out2.='</table>'."\n";
								$out1.='<span class="portraitOnglet" id="portraitOnglet'.$c.'" '.(($c==0)?'style="background-color:#e5d4ac;" ':'').' onclick="javascript:f_onglet('.$c.');">'.$r1[1].'</span>'."\n";
								$out2.='<table '.(($c==0)?'style="display:table;" ':'').'id="portraitTable'.$c.'" class="portraitTable" border="0">'."\n";
								++$c;
								}
							$c1 = 0;
							switch ($r1[0])
								{
								case 1:
									$out2.='<tr><td>'.$r1[2].'</td><td><input type="text" name="text'.$r.'" value="'.(isset($out[$r])?$out[$r]:'').'" /></td></tr>'."\n";
								break;
								case 2:
									$out2.='<tr><td>'.$r1[2].'</td><td><textarea name="area'.$r.'" rows="4" cols="50">'.(isset($out[$r])?$out[$r]:'').'</textarea></td></tr>'."\n";
								break;
								case 3:
									$out2.='<tr><td>'.$r1[2].'</td><td><select name="select'.$r.'"><option value="0">&nbsp;</option>';
									$list = json_decode($r1[3]);
									foreach($list as $r2)
										{
										$out2.='<option value="'.($c1+1).'"'.((isset($out[$r]) && $c1===$out[$r])?' selected':'').'>'.$r2.'</option>';
										++$c1;
										}
									$out2.='</select></td></tr>'."\n";
								break;
								case 4:
									$out2.='<tr><td>'.$r1[2].'</td><td>';
									$list = json_decode($r1[3]);
									if(isset($out[$r])) $c3=" ".implode(" ",$out[$r])." ";
									else $c3="";
									foreach($list as $r2)
										{
										$out2.='<label>'.$r2.' : <input type="checkbox" name="check'.$r.'[]" value="'.$c1.'" '.((strstr($c3, " ".$c1." ")!=false)?'checked':'').' /></label>';
										++$c1;
										}
									$out2.='</td></tr>'."\n";
								break;
								case 5:
									$out2.='<tr><td>'.$r1[2].'</td><td><select name="ns'.$r.'"><option value="0">&nbsp;</option>';
									$list = json_decode($r1[3]);
									for($v=$list[0]; $v<=$list[1]; $v+=$list[2])
										{
										$out2.='<option value="'.($c1+1).'"'.((isset($out[$r]) && $c1===$out[$r])?' selected':'').'>'.$v.' '.$list[3].'</option>';
										++$c1;
										}
									$out2.='</select></td></tr>'."\n";
								break;
								}
							$d=$r1[1];
							}
						$out2.='</table>'."\n";
						echo $out1.$out2;
						?>
						
							<em id="infoChange"><?php if(isset($_POST["a1"]) && $_POST["a1"]=="sauvProfil") _e('Done','rencontre'); ?>&nbsp;</em>
						</div>
					</div>
				</form>
			</div>
		<?php } ?>
		
	</div>
	<?php
	}
//
function rencMenuPrison()
	{
	wp_enqueue_script('rencontre', plugins_url('rencontre/js/rencontre-adm.js'));
	wp_enqueue_style( 'rencontre', plugins_url('rencontre/css/rencontre-adm.css'));
	require(dirname (__FILE__) . '/../lang/rencontre-js-admin-lang.php');
	wp_localize_script('rencontre', 'rencobjet', $lang);
	global $wpdb; global $rencOpt; global $rencDiv; global $rencVersion
	?>
	<div class='wrap'>
		<div class='icon32' id='icon-options-general'><br/></div>
		<h2>Rencontre&nbsp;<span style='font-size:60%;'>v<?php echo $rencVersion; ?></span></h2>
		<h2><?php _e('Jail', 'rencontre'); ?></h2>
		<p><?php _e('List of members removed by Admin. They are blacklisted. Subscription blocked', 'rencontre'); ?>&nbsp;<span style='color:#111;font-weight:700;'><?php echo (empty($rencOpt['prison'])?7:$rencOpt['prison']); ?></span>&nbsp;<?php _e('days', 'rencontre'); ?>.</p>
		<?php 
		if(isset($_POST["a1"])) 
			{
			f_userPrison($_POST["a1"]);
			}
		$tri='ORDER BY Q.d_prison DESC';
		if(isset($_GET['tri']))
			{
			if($_GET['tri']=='date') $tri='ORDER BY Q.d_prison ASC';
			else if($_GET['tri']=='Rdate') $tri='ORDER BY Q.d_prison DESC';
			else if($_GET['tri']=='mail') $tri='ORDER BY Q.c_mail ASC';
			else if($_GET['tri']=='Rmail') $tri='ORDER BY Q.c_mail DESC';
			else if($_GET['tri']=='ip') $tri='ORDER BY Q.c_ip ASC';
			else if($_GET['tri']=='Rip') $tri='ORDER BY Q.c_ip DESC';
			else if($_GET['tri']=='typ') $tri='ORDER BY Q.i_type ASC';
			else if($_GET['tri']=='Rtyp') $tri='ORDER BY Q.i_type DESC';
			}
		$pagenum = isset($_GET['pagenum'])?absint($_GET['pagenum']):1;
		$limit = 100;
		$q = $wpdb->get_results("SELECT
				Q.id,
				Q.d_prison,
				Q.c_mail,
				Q.c_ip,
				Q.i_type
			FROM
				".$wpdb->prefix."rencontre_prison Q
			".$tri."
			LIMIT ".(($pagenum-1)*$limit).",".$limit);
		$total = $wpdb->get_var("SELECT COUNT(id) FROM ".$wpdb->prefix . "rencontre_prison");
		$page_links = paginate_links(array('base'=>add_query_arg('pagenum','%#%'),'format'=>'','prev_text'=>'&laquo;','next_text'=>'&raquo;','total'=>ceil($total/$limit),'current'=>$pagenum,'mid_size'=>5));
		if($page_links) echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">'.$page_links.'</div></div>';
		?>
		<form name='listPrison' method='post' action=''><input type='hidden' name='a1' value='' />
		<table class="prison">
			<tr>
				<td><a href="admin.php?page=rencjail&tri=<?php if(isset($_GET['tri']) && $_GET['tri']=='date') echo 'R'; ?>date" title="<?php _e('Sort','rencontre'); ?>"><?php _e('Date','rencontre');?></a></td>
				<td><a href="admin.php?page=rencjail&tri=<?php if(isset($_GET['tri']) && $_GET['tri']=='mail') echo 'R'; ?>mail" title="<?php _e('Sort','rencontre'); ?>"><?php _e('Email address','rencontre');?></a></td>
				<td><a href="admin.php?page=rencjail&tri=<?php if(isset($_GET['tri']) && $_GET['tri']=='ip') echo 'R'; ?>ip" title="<?php _e('Sort','rencontre'); ?>"><?php _e('IP address','rencontre');?><a></td>
				<td><?php _e('End','rencontre');?></td>
				<td><a href="admin.php?page=rencjail&tri=<?php if(isset($_GET['tri']) && $_GET['tri']=='typ') echo 'R'; ?>typ" title="<?php _e('Sort','rencontre'); ?>"><?php _e('Banning','rencontre');?><a></td>
			</tr>
		<?php
		$categ="";
		foreach($q as $s)
			{
			echo '<tr>';
			echo '<td>'.$s->d_prison.'</td>';
			echo '<td>'.$s->c_mail.'</td>';
			echo '<td>'.$s->c_ip.'</td>';
			echo '<td><a href="javascript:void(0)" class="rencSupp" onClick="f_liberte('.$s->id.')" title="'.__('Release','rencontre').'"></a></td>';
			echo '<td>'.((!$s->i_type)?__('Only email','rencontre'):(($s->i_type==1)?__('Only IP','rencontre'):(($s->i_type==2)?__('Email and IP','rencontre'):''))).'</td>';
			echo '</tr>';
			}
		?>
		</table>
		<p><i>* <?php _e('Banning IP is a Premium feature.','rencontre');?></i></p>
		</form>
	</div>
	<?php
	}
//
function rencMenuProfil()
	{
	wp_enqueue_script('rencontre', plugins_url('rencontre/js/rencontre-adm.js'));
	wp_enqueue_style('rencontre', plugins_url('rencontre/css/rencontre-adm.css'));
	require(dirname(__FILE__).'/../lang/rencontre-js-admin-lang.php');
	wp_localize_script('rencontre', 'rencobjet', $lang);
	global $wpdb; global $rencVersion; global $rencCustom; global $rencOpt;
	$loc = substr(get_locale(),0,2); $loc2 = $loc."&";
	$q2 = $wpdb->get_var("SELECT c_lang FROM ".$wpdb->prefix."rencontre_profil WHERE c_lang='".$loc."' LIMIT 1");
	if(!$q2) {$loc = "en"; $loc2 = "en&";}
	if(!isset($_SESSION["a2"])) $_SESSION["a2"] = "off";
	if(!isset($_SESSION["a4"])) $_SESSION["a4"] = "off";
	if(isset($_POST["a1"]) && isset($_POST["a2"]) && isset($_POST["a4"]) && !($_SESSION['a2']==$_POST["a2"] && $_SESSION['a4']==$_POST["a4"]) || (isset($_POST["a6"]) && $_POST["a6"]!=''))
		{
		if($_POST["a1"]=="edit") profil_edit($_POST["a2"],$_POST["a3"],$_POST["a4"],$_POST["a5"],$_POST["a6"]);
		else if($_POST["a1"]=="plus") profil_plus($_POST["a2"],$_POST["a3"],$_POST["a4"],$_POST["a5"]);
		else if($_POST["a1"]=="langplus") profil_langplus($loc,$_POST["a4"]);
		else if($_POST["a1"]=="langsupp") profil_langsupp($_POST["a4"]);
		else if($_POST["a1"]=="synchro") synchronise();
		else if($_POST["a1"]=="profil") profil_defaut();
		else if($_POST["a1"]=="pays") liste_defaut();
		}
	if(isset($_POST["a1"]))
		{
		$_SESSION['a2'] = $_POST["a2"];
		$_SESSION['a4'] = $_POST["a4"];
		}
	$q2 = $wpdb->get_results("SELECT c_lang FROM ".$wpdb->prefix."rencontre_profil WHERE c_lang!='".$loc."' GROUP BY c_lang ");
	if($q2!=null) foreach($q2 as $r2) { $loc2 .= $r2->c_lang."&"; }
	$genre = array(); $genreDef = '';
	for($v=(isset($rencCustom['sex'])?2:0);$v<(isset($rencCustom['sex'])?count($rencOpt['iam']):2);++$v)
		{
		$genre[$v] = array('v'=>preg_replace('/[^a-z0-9]/i','_',$rencOpt['iam'][$v]));
		$genreDef .= preg_replace('/[^a-z0-9]/i','_',$rencOpt['iam'][$v]).'=1&';
		}
	?>
	<div class='wrap'>
		<form name='menu_profil' method='post' action=''>
			<input type='hidden' name='a1' value='' /><input type='hidden' name='a2' value='' /><input type='hidden' name='a3' value='' />
			<input type='hidden' name='a4' value='' /><input type='hidden' name='a5' value='' /><input type='hidden' name='a6' value='' />
		</form>
		<div class='icon32' id='icon-options-general'><br/></div>
		<h2>Rencontre&nbsp;<span style='font-size:60%;'>v<?php echo $rencVersion; ?></span></h2>
		<h2><?php _e('Profile', 'rencontre'); ?></h2>
		<?php $n = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."rencontre_profil");
		if($n==0)
			{
			echo "<p>".__('It does not appear to be any profile. You can load the default profile if you wish.', 'rencontre')."</p>";
			echo "<a href='javascript:void(0)' class='button-primary' onClick='document.forms[\"menu_profil\"].elements[\"a1\"].value=\"profil\";document.forms[\"menu_profil\"].elements[\"a2\"].value=\"profil\";document.forms[\"menu_profil\"].submit();'>". __('Load profiles', 'rencontre')."</a>";
			}
		if(file_exists(dirname(__FILE__).'/rencontre_synchronise.json')) { ?>
		<p>
			<a href='javascript:void(0)' class='button-primary' onClick='f_synchronise();'><?php _e('Update member profile', 'rencontre'); ?></a>
			&nbsp;:&nbsp;<span style="color:red;font-weight:700;"><?php _e('You have made changes. Remember to update when you\'re done.', 'rencontre'); ?></span>
		</p><?php } ?>
		
		<p><?php _e('You can create, rename and delete items from the profile.', 'rencontre'); ?></p>
		<p>
			<?php _e('Warning, this is not without consequences. The changes will be applied to the member profiles that can offend. Caution !', 'rencontre'); ?>&nbsp;
		</p>
		<h3><?php _e('Ref language', 'rencontre'); echo ' : <span style="color:#700;">'.$loc.'</span> --- ' . __('Other', 'rencontre').'&nbsp;:&nbsp;';
		$ls = '';
		foreach($q2 as $r2)
			{
			if($r2->c_lang!=$loc)
				{
				$ls .= '<option value="'.$r2->c_lang.'">'.$r2->c_lang.'</option>';
				echo '<span style="color:#700;">' . $r2->c_lang . '</span>&nbsp;-&nbsp;';
				}
			}
		?></h3>
		<ul>
			<li>
				<label><?php _e('Add Language (2 lowercase letters comply with country code)', 'rencontre'); ?>&nbsp;</label>&nbsp;
				<input type="text" name="langplus" maxlength="2" size="2" />
				<a href='javascript:void(0)' class='button-primary' onClick='f_langplus();'><?php _e('Add a language', 'rencontre'); ?></a>
			</li>
			<li>
				<label><?php _e('Remove a language and all related content', 'rencontre'); ?>&nbsp;</label>&nbsp;
				<select id="langsupp">
					<?php echo $ls; ?>
				</select>
				<a href='javascript:void(0)' class='button-primary' onClick='f_langsupp();'><?php _e('Remove a language', 'rencontre'); ?></a>
			</li>
		</ul>
		<br />
		<div style='margin:8px 12px 12px;'>
			<a href='javascript:void(0)' class='rencPlus' onClick='f_plus(this,0,"c_categ","","<?php echo $loc2; ?>");' title='Ajouter une cat&eacute;gorie'></a>
			<span style='font-style:italic;'><?php _e('Add category','rencontre');?></span>
		</div>
		<div class="profil">
		<?php
		$q = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."rencontre_profil WHERE c_lang='".$loc."' ORDER BY i_categ, i_label");
		$categ=""; $label = 0;
		foreach($q as $r)
			{
			$g = $genreDef;
			if(strpos($r->c_genre,',')!==false)
				{
				$g = '';
				$a = explode(',',$r->c_genre);
				foreach($genre as $k=>$v)
					{
					if(strpos($r->c_genre,','.$k.',')!==false) $g.= $genre[$k]['v'].'=1&'; // men=1&girl=1&
					else $g.= $genre[$k]['v'].'=0&';
					}
				}
			$q1 = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."rencontre_profil WHERE id='".$r->id."' and c_lang!='".$loc."' ORDER BY c_lang"); // multilangue
			if($categ!=$r->c_categ) // nouvelle categorie
				{
				if($label) echo '</div><!-- .label -->'."\r\n";
				$label = 0;
			// CATEGORIE
				if($categ!="") echo '</div><!-- .categ -->'."\r\n";
				$categ = $r->c_categ;
				$a4 = $r->c_lang . '=' . $r->c_categ . '&';
				$out = '<div style="margin:-15px 0 10px 37px;color:#777;">';
				foreach($q1 as $r1)
					{
					$out .= $r1->c_lang.' : '.$r1->c_categ. ' -- ';
					$a4 .= $r1->c_lang . '=' . $r1->c_categ . '&';
					}
				echo '<div class="categ">'."\r\n";
				echo '<h3>';
				echo '<a href="javascript:void(0)" class="rencUp" onClick="f_rencUp('.$r->id.',\'c_categ\',this);" title="'.__('Move Up','rencontre').'"></a>';
				echo '<a href="javascript:void(0)" class="rencDown" onClick="f_rencDown('.$r->id.',\'c_categ\',this);" title="'.__('Move Down','rencontre').'"></a>';
				echo '<a href="javascript:void(0)" class="rencEdit" onClick="f_edit(this,'.$r->id.',\'c_categ\',\''.urlencode($a4).'\',\'\',\''.$g.'\');" title="'.__('Change the name','rencontre').'"></a>';
				echo '<a href="javascript:void(0)" class="rencSupp" onClick="f_supp('.$r->id.',\'c_categ\',this);" title="'.__('Remove the category','rencontre').'"></a>';
				echo $categ.'</h3>';
				echo $out . '</div>';
			// LABEL
				echo '<a href="javascript:void(0)" class="rencPlus" onClick="f_plus(this,'.$r->id.',\'c_label\',\'\',\''.$loc2.'\');" title="'.__('Add value to this category','rencontre').'"></a>';
				echo '<span style="font-style:italic;">'.__('Add value to this category','rencontre').'</span><br /><br />';
				}
			$out = '';
			$a4 = $r->c_lang . '=' . $r->c_label . '&';
			foreach($q1 as $r1)
				{
				$out .= '<span style="margin:0 0 0 37px;color:#777;" class="rencLabel'.$r1->c_lang.'">'.$r1->c_lang.' : '.$r1->c_label. '</span><br />';
				$a4 .= $r1->c_lang . '=' . $r1->c_label . '&';
				}
			if($label) echo '</div><!-- .label -->'."\r\n";
			$label = 1;
			echo '<div class="label">'."\r\n".'<div class="rencLabel" id="rencLabel'.$r->id.'">';
			echo '<a href="javascript:void(0)" class="rencUp" onClick="f_rencUp('.$r->id.',\'c_label\',this);" title="'.__('Move Up','rencontre').'"></a>';
			echo '<a href="javascript:void(0)" class="rencDown" onClick="f_rencDown('.$r->id.',\'c_label\',this);" title="'.__('Move Down','rencontre').'"></a>';
			echo '<a href="javascript:void(0)" class="rencEdit" onClick="f_edit(this,'.$r->id.',\'c_label\',\''.urlencode($a4).'\','.$r->i_type.',\''.$g.'\');" title="'.__('Change the name or type','rencontre').'"></a>';
			echo '<a href="javascript:void(0)" class="rencSupp" onClick="f_supp('.$r->id.',\'c_label\',this);" title="'.__('Remove','rencontre').'"></a>';
			echo '<span class="rencLabel'.$r->c_lang.'">'.$r->c_label . '</span><br />';
			echo $out . '</div><!-- .rencLabel -->'."\r\n";
			echo '<div style="height:5px;"></div>';
			// VALEUR
			switch($r->i_type)
				{
				case 1 :
				echo '<div class="rencValeur rencType">'.__('A line of text (TEXT)','rencontre').'</div>'."\r\n";
				break;
				// *******
				case 2 :
				echo '<div class="rencValeur rencType">'.__('Large text box (TEXTAREA)','rencontre').'</div>'."\r\n";
				break;
				// *******
				case 3 :
				echo '<div class="rencValeur" id="rencValeur'.$r->id.'">'."\r\n";
				echo '<a href="javascript:void(0)" class="rencPlus" onClick="f_plus(this,'.$r->id.',\'t_valeur\',\'\',\''.$loc2.'\');" title="'.__('Add Value','rencontre').'"></a>';
				echo '<span class="rencType">'.__('Single choice list (SELECT)','rencontre').'</span>';
				$s = json_decode($r->t_valeur);
				$s1=Array(); $s2=Array(); foreach($q1 as $r1) { $s1[] = json_decode($r1->t_valeur); $s2[] = $r1->c_lang; }
				$c=0;
				foreach($s as $ss)
					{
					$a4 = $r->c_lang . '=' . $ss. '&';
					$t = '';
					for($v=0; $v<count($s1); ++$v)
						{
						$a4 .= $s2[$v] . '=' . $s1[$v][$c] . '&';
						$t .= ($v!=0?'<br />':''). '<span style="margin:0 0 0 37px;color:#777;" class="rencValeur'.$s2[$v].'">'.$s2[$v].' : '.$s1[$v][$c]. '</span>';
						}
					echo '<div class="valeur">';
					echo '<br />';
					echo '<a href="javascript:void(0)" class="rencUp" onClick="f_rencUp('.$r->id.',\'t_valeur\',this);" title="'.__('Move Up','rencontre').'"></a>';
					echo '<a href="javascript:void(0)" class="rencDown" onClick="f_rencDown('.$r->id.',\'t_valeur\',this);" title="'.__('Move Down','rencontre').'"></a>';
					echo '<a href="javascript:void(0)" class="rencEdit" onClick="f_edit(this,'.$r->id.',\'t_valeur\',\''.urlencode($a4).'\',0,0);" title="'.__('Change','rencontre').'"></a>';
					echo '<a href="javascript:void(0)" class="rencSupp" onClick="f_supp('.$r->id.',\'t_valeur\',this);" title="'.__('Remove','rencontre').'"></a>';
					echo '<span class="rencValeur'.$r->c_lang.'">' . $ss . '</span><br />';
					echo $t . '</div><!-- .valeur -->'."\r\n";
					++$c;
					}
				echo '</div><!-- .rencValeur -->'."\r\n";
				break;
				// *******
				case 4 :
				echo '<div class="rencValeur" id="rencValeur'.$r->id.'">';
				echo '<a href="javascript:void(0)" class="rencPlus" onClick="f_plus(this,'.$r->id.',\'t_valeur\',\'\',\''.$loc2.'\');" title="'.__('Add Value','rencontre').'"></a>';
				echo '<span class="rencType">'.__('Multiple choice list (CHECKBOX)','rencontre').'</span>';
				$s = json_decode($r->t_valeur);
				$s1=Array(); $s2=Array(); foreach($q1 as $r1) { $s1[] = json_decode($r1->t_valeur); $s2[] = $r1->c_lang; }
				$c=0;
				foreach($s as $ss)
					{
					$a4 = $r->c_lang . '=' . $ss. '&';
					$t = '';
					for($v=0; $v<count($s1); ++$v)
						{
						$a4 .= $s2[$v] . '=' . $s1[$v][$c] . '&';
						$t .= ($v!=0?'<br />':''). '<span style="margin:0 0 0 37px;color:#777;" class="rencValeur'.$s2[$v].'">'.$s2[$v].' : '.$s1[$v][$c]. '</span>';
						}
					echo '<div class="valeur">'."\r\n";
					echo '<br />';
					echo '<a href="javascript:void(0)" class="rencUp" onClick="f_rencUp('.$r->id.',\'t_valeur\',this);" title="'.__('Move Up','rencontre').'"></a>';
					echo '<a href="javascript:void(0)" class="rencDown" onClick="f_rencDown('.$r->id.',\'t_valeur\',this);" title="'.__('Move Down','rencontre').'"></a>';
					echo '<a href="javascript:void(0)" class="rencEdit" onClick="f_edit(this,'.$r->id.',\'t_valeur\',\''.urlencode($a4).'\',0,0);" title="'.__('Change','rencontre').'"></a>';
					echo '<a href="javascript:void(0)" class="rencSupp" onClick="f_supp('.$r->id.',\'t_valeur\',this);" title="'.__('Remove','rencontre').'"></a>';
					echo '<span class="rencValeur'.$r->c_lang.'">' . $ss . '</span><br />';
					echo $t . '</div><!-- .valeur -->'."\r\n";
					++$c;
					}
				echo '</div><!-- .rencValeur -->'."\r\n";
				break;
				// *******
				case 5 :
				echo '<div class="rencValeur" id="rencValeur'.$r->id.'">'."\r\n";
				echo '<span class="rencType">'.__('Numeric choice list (SELECT)','rencontre').'</span>';
				$s = json_decode($r->t_valeur);
				echo '<div class="valeur">';
				echo '<br />';
				echo '<a href="javascript:void(0)" class="rencEdit" onClick="f_edit(this,'.$r->id.',\'t_valeur\',\''.urlencode($s[0].'&'.$s[1].'&'.$s[2].'&'.$s[3].'&').'\',\'ns\',0);" title="'.__('Change','rencontre').'"></a>';
				echo '<span>['.$s[0].' ; '.$s[1].']</span><br />';
				echo '<q style="margin:0 0 0 18px;color:blue;">'.__('Step','rencontre').'</q><span> : '.$s[2].'</span><br />';
				echo '<q style="margin:0 0 0 18px;color:blue;">'.__('Unit','rencontre').'</q><span> : '.$s[3].'</span>';
				echo '</div><!-- .valeur -->'."\r\n";
				echo '</div><!-- .rencValeur -->'."\r\n";
				break;
				}
			?>
			<br style="clear:left;"/>
			<?php
			}
		if($categ!="") echo '</div><!-- .categ -->'."\r\n";
		?>
		
		</div><!-- .profil -->
	</div><!-- .wrap -->
	<?php
	}
//
function rencMenuPays()
	{
	wp_enqueue_script('rencontre', plugins_url('rencontre/js/rencontre-adm.js'));
	wp_enqueue_style( 'rencontre', plugins_url('rencontre/css/rencontre-adm.css'));
	require(dirname (__FILE__) . '/../lang/rencontre-js-admin-lang.php');
	wp_localize_script('rencontre', 'rencobjet', $lang);
	global $wpdb; global $rencDiv; global $rencVersion;
	$q = $wpdb->get_results("SELECT c_liste_categ, c_liste_valeur, c_liste_iso FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='d' or (c_liste_categ='p' and c_liste_lang='".substr($rencDiv['lang'],0,2)."') ");
	$rencDrap=''; $rencDrapNom='';
	foreach($q as $r)
		{
		if($r->c_liste_categ=='d') $rencDrap[$r->c_liste_iso] = $r->c_liste_valeur;
		else if($r->c_liste_categ=='p')$rencDrapNom[$r->c_liste_iso] = $r->c_liste_valeur;
		}
	$loc = substr(get_locale(),0,2); $loc2 = $loc."&";
	$q2 = $wpdb->get_var("SELECT c_liste_lang FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_lang='".$loc."' LIMIT 1");
	if(!$q2) {$loc = "en"; $loc2 = "en&";}
	if(isset($_POST["a1"]) && (!isset($_SESSION['a2']) || !($_SESSION['a2']==$_POST["a2"] && $_SESSION['a4']==$_POST["a4"])) || (isset($_POST["a6"]) && $_POST["a6"]!=''))
		{
		if($_POST["a1"]=="supp") liste_supp($_POST["a2"],$_POST["a3"],$_POST["a4"]);
		else if($_POST["a1"]=="edit") liste_edit($_POST["a2"],$_POST["a3"],$_POST["a4"],$_POST["a5"],$_POST["a6"]);
		else if($_POST["a1"]=="plus") liste_plus($_POST["a2"],$_POST["a3"],$_POST["a4"],$_POST["a5"],$_POST["a6"]);
		else if($_POST["a1"]=="langplus") liste_langplus($loc,$_POST["a4"]);
		else if($_POST["a1"]=="langsupp") liste_langsupp($_POST["a4"]);
		else if($_POST["a1"]=="pays") liste_defaut();
		}
	if(isset($_POST["a1"]))
		{
		$_SESSION['a2'] = $_POST["a2"];
		$_SESSION['a4'] = $_POST["a4"];
		}
	$q2 = $wpdb->get_results("SELECT c_liste_lang FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_lang!='".$loc."' and c_liste_lang!='' GROUP BY c_liste_lang ");
	if($q2!=null) foreach($q2 as $r2) { $loc2 .= $r2->c_liste_lang."&"; }
	?>
	<div class='wrap'>
		<form name='menu_liste' method='post' action=''>
			<input type='hidden' name='a1' value='' /><input type='hidden' name='a2' value='' /><input type='hidden' name='a3' value='' />
			<input type='hidden' name='a4' value='' /><input type='hidden' name='a5' value='' /><input type='hidden' name='a6' value='' />
		</form>
		<div class='icon32' id='icon-options-general'><br/></div>
		<h2>Rencontre&nbsp;<span style='font-size:60%;'>v<?php echo $rencVersion; ?></span></h2>
		<h2><?php _e('Countries and Regions', 'rencontre'); ?></h2>
		<?php $n = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."rencontre_liste");
		if($n==0)
			{
			echo "<p>".__('The country table is empty. You can load the countries and regions by default if you wish.', 'rencontre')."</p>";
			echo "<a href='javascript:void(0)' class='button-primary' onClick='document.forms[\"menu_liste\"].elements[\"a1\"].value=\"pays\";document.forms[\"menu_liste\"].elements[\"a2\"].value=\"pays\";document.forms[\"menu_liste\"].submit();'>". __('Load countries', 'rencontre')."</a>";
			} ?>
		
		<p><?php _e('You can create, rename and delete countries and regions.', 'rencontre'); ?></p>
		<h3><?php _e('Ref language', 'rencontre'); echo ' : <span style="color:#700;">'.$loc.'</span> --- ' . __('Other', 'rencontre').'&nbsp;:&nbsp;';
		$ls = '';
		foreach($q2 as $r2)
			{
			if($r2->c_liste_lang!=$loc)
				{
				$ls .= '<option value="'.$r2->c_liste_lang.'">'.$r2->c_liste_lang.'</option>';
				echo '<span style="color:#700;">' . $r2->c_liste_lang . '</span>&nbsp;-&nbsp;';
				}
			}
		?></h3>
		<ul>
			<li>
				<label><?php _e('Add Language (2 lowercase letters comply with country code)', 'rencontre'); ?>&nbsp;</label>&nbsp;
				<input type="text" name="langplus" maxlength="2" size="2" />
				<a href='javascript:void(0)' class='button-primary' onClick='f_liste_langplus();'><?php _e('Add a language', 'rencontre'); ?></a>
			</li>
			<li>
				<label><?php _e('Remove a language and all related content', 'rencontre'); ?>&nbsp;</label>&nbsp;
				<select id="langsupp">
					<?php echo $ls; ?>
				</select>
				<a href='javascript:void(0)' class='button-primary' onClick='f_liste_langsupp();'><?php _e('Remove a language', 'rencontre'); ?></a>
			</li>
		</ul>
		<br />
		<div id="edit_liste"></div>
		<div style='margin:8px 12px 12px;'>
			<a href='javascript:void(0)' class='rencPlus' onClick='f_liste_plus(0,"p","","<?php echo $loc2; ?>");' title='Ajouter un pays'></a>
			<span style='font-style:italic;'><?php _e('Add a country','rencontre');?></span>
		</div>
		<?php
		$q = $wpdb->get_results("SELECT c_liste_iso FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='p' GROUP BY c_liste_iso"); // liste des codes ISO
		foreach($q as $r)
			{
			$q1 = $wpdb->get_results("SELECT c_liste_categ, c_liste_valeur, c_liste_lang FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_iso='".$r->c_liste_iso."' and c_liste_categ='p' ORDER BY c_liste_lang");
			$out = ''; $out1 = ''; $a4 = '';
			foreach($q1 as $r1)
				{
				if($r1->c_liste_lang==$loc) $out1 = $r1->c_liste_valeur;
				else $out .= '<span style="margin:0 0 0 37px;color:#777;">'.$r1->c_liste_lang.' : '.$r1->c_liste_valeur. '</span><br />';
				$a4 .= $r1->c_liste_lang . '=' . $r1->c_liste_valeur . '&';
				}
			echo '<div class="rencLabel">';
			echo '<a href="javascript:void(0)" class="rencEdit" onClick="f_liste_edit(\''.$r->c_liste_iso.'\',\'p\',\''.urlencode($a4).'\');" title="'.__('Change the name or type','rencontre').'"></a>';
			echo '<a href="javascript:void(0)" class="rencSupp" onClick="f_liste_supp(\''.$r->c_liste_iso.'\',\'p\',0);" title="'.__('Remove','rencontre').'"></a>';
			echo $out1.'&nbsp;('.$r->c_liste_iso.')<br />';
			if(isset($rencDrap[$r->c_liste_iso])) echo '<img style="position:absolute;width:30px;height:20px;" src="'.plugins_url('rencontre/images/drapeaux/').$rencDrap[$r->c_liste_iso].'" />';
			echo $out . '</div><div style="height:5px;"></div>';
			echo '<div class="rencValeur">';
			echo '<a href="javascript:void(0)" class="rencPlus" onClick="f_liste_plus(\''.$r->c_liste_iso.'\',\'r\',\'\',\''.$loc2.'\');" title="'.__('Add Value','rencontre').'"></a>';
			echo '<span class="rencType">'.__('Regions','rencontre').'</span>';
			$q2 = $wpdb->get_results("SELECT id, c_liste_valeur FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_iso='".$r->c_liste_iso."' and c_liste_categ='r' ");
			foreach($q2 as $r2)
				{
				echo '<br /><a href="javascript:void(0)" class="rencEdit" onClick="f_liste_edit('.$r2->id.',\'r\',\''.$r2->c_liste_valeur.'\');" title="'.__('Change','rencontre').'"></a>';
				echo '<a href="javascript:void(0)" class="rencSupp" onClick="f_liste_supp('.$r2->id.',\'r\',0);" title="'.__('Remove','rencontre').'"></a>';
				echo '<span style="margin:0 0 0 5px;color:#777;">'.$r2->c_liste_valeur. '</span>' . "\r\n";
				}
			echo '</div><br style="clear:left;"/>'."\r\n";
			}
		?>
	</div>
	<?php
	}
//
function rencMenuCustom()
	{
	wp_enqueue_script('rencontre', plugins_url('rencontre/js/rencontre-adm.js'));
	wp_enqueue_style( 'rencontre', plugins_url('rencontre/css/rencontre-adm.css'));
	global $rencOpt; global $rencDiv; global $rencCustom;
	if((isset($_POST['a1']) && $_POST['a1']=='custom')) f_update_custom($_POST); 
	?>
	
	<div id="rencFea" class='wrap' style="max-width:620px;">
		<h2 class="nav-tab-wrapper">
			<a href="admin.php?page=renccustom" class="nav-tab<?php if(empty($_GET['renctab'])) echo ' nav-tab-active'; ?>"><?php _e('Features', 'rencontre'); ?></a>
			<a href="admin.php?page=renccustom&renctab=wor" class="nav-tab<?php if(isset($_GET['renctab']) && $_GET['renctab']=='wor') echo ' nav-tab-active'; ?>"><?php _e('Words', 'rencontre'); ?></a>
			<a href="admin.php?page=renccustom&renctab=sea" class="nav-tab<?php if(isset($_GET['renctab']) && $_GET['renctab']=='sea') echo ' nav-tab-active'; ?>"><?php _e('Search', 'rencontre'); ?></a>
			<a href="admin.php?page=renccustom&renctab=tem" class="nav-tab<?php if(isset($_GET['renctab']) && $_GET['renctab']=='tem') echo ' nav-tab-active'; ?>"><?php _e('Templates', 'rencontre'); ?></a>
		</h2>
	<?php if(!empty($_GET['renctab']))
		{
		if($_GET['renctab']=='wor') rencTabWor();
		else if($_GET['renctab']=='sea') rencTabSea();
		else if($_GET['renctab']=='tem') rencTabTem();
		?>
		
	</div>
	<div style="clear:both;"></div>
		<?php return;
		} 
	?>

	<div class="wrap">
		<div class="icon32" id="icon-options-general"><br/></div>
		<form id="customForm" name="customForm" method="post" action="?page=renccustom">
			<input type="hidden" name="a1" value="custom" />
			<input type="hidden" name="a2" value="" />
			<table class="form-table" style="max-width:600px;clear:none;">
				<tr valign="top">
					<th scope="row"><label><?php _e('No country', 'rencontre'); ?></label></th>
					<td><input type="checkbox" name="country" value="1" <?php if(isset($rencCustom['country']))echo 'checked'; ?>></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('No region', 'rencontre'); ?></label></th>
					<td><input type="checkbox" name="region" value="1" <?php if(isset($rencCustom['region']))echo 'checked'; ?>></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('No localisation', 'rencontre'); ?></label></th>
					<td><input type="checkbox" name="place" value="1" <?php if(isset($rencCustom['place']))echo 'checked'; ?>></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('No birth date', 'rencontre'); ?></label></th>
					<td><input type="checkbox" name="born" value="1" <?php if(isset($rencCustom['born']))echo 'checked'; ?> onClick="document.getElementById('blocAgeMin').style.display=((this.checked==true)?'none':'table-row');document.getElementById('blocAgeMax').style.display=((this.checked==true)?'none':'table-row')"></td>
				</tr>
				<tr valign="top" id="blocAgeMin" style="<?php echo (!empty($rencCustom['born'])?'display:none;':'display:table-row;'); ?>">
					<th scope="row"><label><?php _e('Min age', 'rencontre'); ?></label></th>
					<td>
						<select name="agemin" onChange="f_minA(parseInt(this.options[this.selectedIndex].value),'customForm','agemin','agemax');">
						<?php for($v=0; $v<130; ++$v)
							{
							if(!isset($rencCustom['agemin'])&&$v==18) echo '<option value="18" selected>18</option>';
							else echo '<option value="'.$v.'" '.((isset($rencCustom['agemin'])&&$rencCustom['agemin']==$v)?'selected':'').'>'.$v.'</option>';
							} ?>
							
						</select>
					</td>
				</tr>
				<tr valign="top" id="blocAgeMax" style="<?php echo (!empty($rencCustom['born'])?'display:none;':'display:table-row;'); ?>">
					<th scope="row"><label><?php _e('Max age', 'rencontre'); ?></label></th>
					<td>
						<select name="agemax" onChange="f_maxA(parseInt(this.options[this.selectedIndex].value),'customForm','agemin','agemax');">
						<?php for($v=0; $v<130; ++$v)
							{
							if(!isset($rencCustom['agemax'])&&$v==99) echo '<option value="99" selected>99</option>';
							else echo '<option value="'.$v.'" '.((isset($rencCustom['agemax'])&&$rencCustom['agemax']==$v)?'selected':'').'>'.$v.'</option>';
							} ?>
							
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('No smiles', 'rencontre'); ?></label></th>
					<td><input type="checkbox" name="smile" value="1" <?php if(isset($rencCustom['smile']))echo 'checked'; ?>></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('No weight', 'rencontre'); ?></label></th>
					<td><input type="checkbox" name="weight" value="1" <?php if(isset($rencCustom['weight']))echo 'checked'; ?> onClick="document.getElementById('blocWeightu').style.display=((this.checked==true)?'none':'table-row')"></td>
				</tr>
				<tr valign="top" id="blocWeightu" style="<?php echo (!empty($rencCustom['weight'])?'display:none;':'display:table-row;'); ?>">
					<th scope="row"><label><?php _e('Weight unit', 'rencontre'); ?></label></th>
					<td>
						<select name="weightu">
							<option value="0" <?php if(empty($rencCustom['weightu'])) echo 'selected'; ?>><?php _e('Kilograms', 'rencontre'); ?></option>
							<option value="1" <?php if(!empty($rencCustom['weightu'])) echo 'selected'; ?>><?php _e('Pounds', 'rencontre'); ?></option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('No size', 'rencontre'); ?></label></th>
					<td><input type="checkbox" name="size" value="1" <?php if(isset($rencCustom['size']))echo 'checked'; ?> onClick="document.getElementById('blocSizeu').style.display=((this.checked==true)?'none':'table-row')"></td>
				</tr>
				<tr valign="top" id="blocSizeu" style="<?php echo (!empty($rencCustom['size'])?'display:none;':'display:table-row;'); ?>">
					<th scope="row"><label><?php _e('Size unit', 'rencontre'); ?></label></th>
					<td>
						<select name="sizeu">
							<option value="0" <?php if(empty($rencCustom['sizeu'])) echo 'selected'; ?>><?php _e('Meter', 'rencontre'); ?></option>
							<option value="1" <?php if(!empty($rencCustom['sizeu'])) echo 'selected'; ?>><?php _e('Feet and Inches', 'rencontre'); ?></option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('No report', 'rencontre'); ?></label></th>
					<td><input type="checkbox" name="report" value="1" <?php if(isset($rencCustom['report']))echo 'checked'; ?>></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('No emoticon', 'rencontre'); ?></label></th>
					<td><input type="checkbox" name="emot" value="1" <?php if(isset($rencCustom['emot']))echo 'checked'; ?>></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('No menu (I use WordPress menu)', 'rencontre'); ?></label></th>
					<td><input type="checkbox" name="menu" value="1" <?php if(isset($rencCustom['menu']))echo 'checked'; ?>></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('No sidebar (I put it with widget)', 'rencontre'); ?></label></th>
					<td><input type="checkbox" name="side" value="1" <?php if(isset($rencCustom['side']))echo 'checked'; ?>></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('No ad in unconnected homepage', 'rencontre'); ?></label></th>
					<td><input type="checkbox" name="libreAd" value="1" <?php if(isset($rencCustom['libreAd']))echo 'checked'; ?>></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('No flag in unconnected homepage', 'rencontre'); ?></label></th>
					<td><input type="checkbox" name="libreFlag" value="1" <?php if(isset($rencCustom['libreFlag']))echo 'checked'; ?>></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Only photo in unconnected homepage', 'rencontre'); ?></label></th>
					<td><input type="checkbox" name="librePhoto" value="1" <?php if(isset($rencCustom['librePhoto']))echo 'checked'; ?>></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Change relation type', 'rencontre'); ?><strong style="color:#500"> *</strong></label></th>
					<td>
						<input type="checkbox" name="relation" onChange="if(this.checked){document.getElementById('relationU').style.display='block';document.getElementById('relationT').style.display='none';}else{document.getElementById('relationU').style.display='none';document.getElementById('relationT').style.display='block';}" value="1" <?php if(isset($rencCustom['relation'])) echo 'checked'; ?>><br />
						<p id="relationT" style="font-size:.8em;<?php if(isset($rencCustom['relation'])) echo 'display:none'; ?>">
							<?php for($v=0;$v<3;++$v) echo '-&nbsp;'.$rencOpt['for'][$v].(($v!=(isset($rencCustom['relation'])?count($rencOpt['for']):3)-1)?'<br />':'') ;?>
						</p>
						<p id="relationU" <?php if(!isset($rencCustom['relation'])) echo 'style="display:none"'; ?>>
						<?php
						$c = 0;
						while(isset($rencCustom['relationL'.$c]) || $c==0)
							{
							echo '<input type="text" id="relationL'.$c.'" name="relationL'.$c.'" value="'.(isset($rencCustom['relationL'.$c])?stripslashes($rencCustom['relationL'.$c]):'').'" />';
							if(isset($rencCustom['relationL'.$c]) && $rencCustom['relationL'.$c]) echo '<span class="rencSupp" onClick="document.forms[\'customForm\'].elements[\'a2\'].value=\'relationS'.$c.'\';document.forms[\'customForm\'].submit();" title="'.__('Remove','rencontre').'"></span>';
							if(!isset($rencCustom['relationL'.($c+1)]) && isset($rencCustom['relationL'.$c]) && $rencCustom['relationL'.$c]) echo '<span class="rencPlus" onClick="document.forms[\'customForm\'].elements[\'a2\'].value=\'relationP\';document.forms[\'customForm\'].submit();" title="'.__('Add Value','rencontre').'"></span>';
							echo '<br />';
							++$c;
							}
						?>
						</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Change sex values', 'rencontre'); ?><strong style="color:#500"> *</strong></label></th>
					<td>
						<input type="checkbox" name="sex" onChange="if(this.checked){document.getElementById('sexU').style.display='block';document.getElementById('sexT').style.display='none';}else{document.getElementById('sexU').style.display='none';document.getElementById('sexT').style.display='block';}" value="1" <?php if(isset($rencCustom['sex'])) echo 'checked'; ?>><br />
						<p id="sexT" style="font-size:.8em;<?php if(isset($rencCustom['sex'])) echo 'display:none'; ?>">
							<?php for($v=0;$v<2;++$v) echo '-&nbsp;'.$rencOpt['iam'][$v].(($v!=(isset($rencCustom['sex'])?count($rencOpt['iam']):2)-1)?'<br />':'') ;?>
						</p>
						<p id="sexU" <?php if(!isset($rencCustom['sex'])) echo 'style="display:none"'; ?>>
						<?php
						$c = 0;
						while(isset($rencCustom['sexL'.$c]) || $c==0)
							{
							echo '<input type="text" id="sexL'.$c.'" name="sexL'.$c.'" value="'.(isset($rencCustom['sexL'.$c])?stripslashes($rencCustom['sexL'.$c]):'').'" />';
							if(isset($rencCustom['sexL'.$c]) && $rencCustom['sexL'.$c]) echo '<span class="rencSupp" onClick="document.forms[\'customForm\'].elements[\'a2\'].value=\'sexS'.$c.'\';document.forms[\'customForm\'].submit();" title="'.__('Remove','rencontre').'"></span>';
							if(!isset($rencCustom['sexL'.($c+1)]) && isset($rencCustom['sexL'.$c]) && $rencCustom['sexL'.$c]) echo '<span class="rencPlus" onClick="document.forms[\'customForm\'].elements[\'a2\'].value=\'sexP\';document.forms[\'customForm\'].submit();" title="'.__('Add Value','rencontre').'"></span>';
							echo '<br />';
							++$c;
							}
						?>
						</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('Allow multi choice in relation and sex', 'rencontre'); ?></label></th>
					<td><input type="checkbox" name="multiSR" value="1" <?php if(isset($rencCustom['multiSR']))echo 'checked'; ?>></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php echo __('Change register link on click a portrait', 'rencontre').'<br /><i>'.__('(empty => default WP)', 'rencontre').'</i>'; ?></label></th>
					<td><input type="text" name="reglink" style="width:400px;" value="<?php if(isset($rencCustom['reglink']) && $rencCustom['reglink']) echo $rencCustom['reglink']; else echo $rencDiv['siteurl'].'/wp-login.php?action=register'; ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php echo __('No \'delete account\' button', 'rencontre').'<br /><i>('.__('legal ?', 'rencontre').')</i>'; ?></label></th>
					<td><input type="checkbox" name="unreg" value="1" <?php if(isset($rencCustom['unreg']))echo 'checked'; ?>></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label><?php _e('No \'no email\' button', 'rencontre'); ?></label></th>
					<td><input type="checkbox" name="unmail" value="1" <?php if(isset($rencCustom['unmail']))echo 'checked'; ?>></td>
				</tr>
			</table>
			<p><strong style="color:#500">* </strong><em><?php echo __('Be careful, already registered members will be in a category that no longer exists. They should update their account.', 'rencontre'); ?></em></p>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save','rencontre') ?>" />
			</p>
		</form>
		<hr />
	</div>
	<?php
	}
//
function rencTabWor()
	{
	global $rencCustom; ?>
	
	<form method="post" name="customForm" action="admin.php?page=renccustom&renctab=wor">
		<input type="hidden" name="a1" value="custom" />
		<input type="hidden" name="a2" value="" />
		<table class="form-table" style="max-width:600px;clear:none;z-index:5;">
			<tr valign="top">
				<th scope="row"><label><?php _e('Change welcome text for new user', 'rencontre'); ?></label></th>
				<td>
					<input type="checkbox" name="new" onChange="if(this.checked){document.getElementById('newText').style.display='inline';document.getElementById('newT').style.display='none';}else{document.getElementById('newText').style.display='none';document.getElementById('newT').style.display='block';}" value="1" <?php if(isset($rencCustom['new'])) echo 'checked'; ?>><br />
					<p id="newT" style="font-size:.8em;<?php if(isset($rencCustom['new'])) echo 'display:none'; ?>">
						<?php echo __('You will access all the possibilities offered by the site in few minutes.','rencontre').' ';
						echo __('Before that, you need to provide some information requested below.','rencontre').'<br />';
						echo __('We would like to inform you that we do not use your personal data outside of this site.','rencontre').' ';
						echo __('Deleting your account on your part or ours, causes the deletion of all your data.','rencontre').' ';
						echo __('This also applies to messages that you have sent to other members as well as those they have sent to you.','rencontre').'<br />';
						echo __('We wish you nice encounters.','rencontre'); ?>
					</p>
					<textarea id="newText" name="newText" <?php if(!isset($rencCustom['new'])) echo 'style="display:none"'; ?>><?php if(!empty($rencCustom['newText'])) echo stripslashes($rencCustom['newText']); ?></textarea>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php _e('Change warning account blocked', 'rencontre'); ?></label></th>
				<td>
					<input type="checkbox" name="blocked" onChange="if(this.checked){document.getElementById('blockedText').style.display='inline';document.getElementById('blockedT').style.display='none';}else{document.getElementById('blockedText').style.display='none';document.getElementById('blockedT').style.display='block';}" value="1" <?php if(isset($rencCustom['blocked'])) echo 'checked'; ?>><br />
					<p id="blockedT" style="font-size:.8em;<?php if(isset($rencCustom['blocked'])) echo 'display:none'; ?>">
						<?php echo __('Your account is blocked. You are invisible. Change your profile.','rencontre'); ?>
					</p>
					<input type="text" id="blockedText" name="blockedText" placeholder="<?php echo __('Your account is blocked. You are invisible. Change your profile.','rencontre'); ?>" value="<?php if(!empty($rencCustom['blockedText'])) echo stripslashes($rencCustom['blockedText']); ?>" <?php if(!isset($rencCustom['blocked'])) echo 'style="display:none"'; ?> />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php _e('Change warning empty profile', 'rencontre'); ?></label></th>
				<td>
					<input type="checkbox" name="empty" onChange="if(this.checked){document.getElementById('emptyText').style.display='inline';document.getElementById('emptyT').style.display='none';}else{document.getElementById('emptyText').style.display='none';document.getElementById('emptyT').style.display='block';}" value="1" <?php if(isset($rencCustom['empty'])) echo 'checked'; ?>><br />
					<p id="emptyT" style="font-size:.8em;<?php if(isset($rencCustom['empty'])) echo 'display:none'; ?>">
						<?php echo __('Your profile is empty. To take advantage of the site and being more visible, thank you to complete it.','rencontre'); ?>
					</p>
					<input type="text" id="emptyText" name="emptyText" placeholder="<?php echo __('Your profile is empty. To take advantage of the site and being more visible, thank you to complete it.','rencontre'); ?>" value="<?php if(!empty($rencCustom['emptyText'])) echo stripslashes($rencCustom['emptyText']); ?>" <?php if(!isset($rencCustom['empty'])) echo 'style="display:none"'; ?> />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php _e('Change the quarantine warning', 'rencontre'); ?></label></th>
				<td>
					<input type="checkbox" name="jail" onChange="if(this.checked){document.getElementById('jailText').style.display='inline';document.getElementById('jailT').style.display='none';}else{document.getElementById('jailText').style.display='none';document.getElementById('jailT').style.display='block';}" value="1" <?php if(isset($rencCustom['jail'])) echo 'checked'; ?>><br />
					<p id="jailT" style="font-size:.8em;<?php if(isset($rencCustom['jail'])) echo 'display:none'; ?>">
						<?php echo __('Your email address is currently in quarantine. Sorry','rencontre'); ?>
					</p>
					<input type="text" id="jailText" name="jailText" placeholder="<?php echo __('Your email address is currently in quarantine. Sorry','rencontre'); ?>" value="<?php if(!empty($rencCustom['jailText'])) echo stripslashes($rencCustom['jailText']); ?>" <?php if(!isset($rencCustom['jail'])) echo 'style="display:none"'; ?> />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php _e('Change the no-photo warning', 'rencontre'); ?></label></th>
				<td>
					<input type="checkbox" name="noph" onChange="if(this.checked){document.getElementById('nophText').style.display='inline';document.getElementById('nophT').style.display='none';}else{document.getElementById('nophText').style.display='none';document.getElementById('nophT').style.display='block';}" value="1" <?php if(isset($rencCustom['noph'])) echo 'checked'; ?>><br />
					<p id="nophT" style="font-size:.8em;<?php if(isset($rencCustom['noph'])) echo 'display:none'; ?>">
						<?php _e("To be more visible and to view photos of other members, you should add one to your profile.","rencontre"); ?>
					</p>
					<input type="text" id="nophText" name="nophText" placeholder="<?php _e("To be more visible and to view photos of other members, you should add one to your profile.","rencontre"); ?>" value="<?php if(!empty($rencCustom['nophText'])) echo stripslashes($rencCustom['nophText']); ?>" <?php if(!isset($rencCustom['noph'])) echo 'style="display:none"'; ?> />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php _e('Change the follow-up email', 'rencontre'); ?></label></th>
				<td>
					<input type="checkbox" name="relanc" onChange="if(this.checked){document.getElementById('relancText').style.display='inline';document.getElementById('relancT').style.display='none';}else{document.getElementById('relancText').style.display='none';document.getElementById('relancT').style.display='block';}" value="1" <?php if(isset($rencCustom['relanc'])) echo 'checked'; ?>><br />
					<p id="relancT" style="font-size:.8em;<?php if(isset($rencCustom['relanc'])) echo 'display:none'; ?>">
						<?php 
						echo __('You registered on our website but you did not complete the procedure. You\'ll miss a date. That\'s too bad.','rencontre').'<br />';
						echo __('Thus take two minutes to finish the registration. You should not be disappointed.','rencontre').'<br />';
						echo __('Regards,','rencontre');	?>
					</p>
					<textarea id="relancText" name="relancText" <?php if(!isset($rencCustom['relanc'])) echo 'style="display:none"'; ?>><?php if(!empty($rencCustom['relancText'])) echo stripslashes($rencCustom['relancText']); ?></textarea>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php echo __('Change Smile word', 'rencontre').'<br /><i>'.__('(empty => no change)', 'rencontre').'</i>' ?></label></th>
				<td>
					<input type="checkbox" name="smiw" onChange="if(this.checked){jQuery('.smiwF').show();jQuery('.smiwT').hide();}else{jQuery('.smiwT').show();jQuery('.smiwF').hide();}" value="1" <?php if(isset($rencCustom['smiw'])) echo 'checked'; ?>><br />
					<p>
						<span class="smiwT" style="font-size:.8em;<?php if(isset($rencCustom['smiw'])) echo 'display:none'; ?>"><?php echo __('Smile','rencontre'); ?></span>
						<input type="text" class="smiwF" id="smiw1" name="smiw1" placeholder="<?php echo __('Smile','rencontre'); ?>" value="<?php if(!empty($rencCustom['smiw1'])) echo stripslashes($rencCustom['smiw1']); ?>" <?php if(!isset($rencCustom['smiw'])) echo 'style="display:none"'; ?> />
					</p>
					<p>
						<span class="smiwT" style="font-size:.8em;<?php if(isset($rencCustom['smiw'])) echo 'display:none'; ?>"><?php echo __('Who I smiled ?','rencontre'); ?></span>
						<input type="text" class="smiwF" id="smiw2" name="smiw2" placeholder="<?php echo __('Who I smiled ?','rencontre'); ?>" value="<?php if(!empty($rencCustom['smiw2'])) echo stripslashes($rencCustom['smiw2']); ?>" <?php if(!isset($rencCustom['smiw'])) echo 'style="display:none"'; ?> />
					</p>
					<p>
						<span class="smiwT" style="font-size:.8em;<?php if(isset($rencCustom['smiw'])) echo 'display:none'; ?>"><?php echo __('I smiled at','rencontre'); ?></span>
						<input type="text" class="smiwF" id="smiw3" name="smiw3" placeholder="<?php echo __('I smiled at','rencontre'); ?>" value="<?php if(!empty($rencCustom['smiw3'])) echo stripslashes($rencCustom['smiw3']); ?>" <?php if(!isset($rencCustom['smiw'])) echo 'style="display:none"'; ?> />
					</p>
					<p>
						<span class="smiwT" style="font-size:.8em;<?php if(isset($rencCustom['smiw'])) echo 'display:none'; ?>"><?php echo __('You have received a smile from','rencontre'); ?></span>
						<input type="text" class="smiwF" id="smiw4" name="smiw4" placeholder="<?php echo __('You have received a smile from','rencontre'); ?>" value="<?php if(!empty($rencCustom['smiw4'])) echo stripslashes($rencCustom['smiw4']); ?>" <?php if(!isset($rencCustom['smiw'])) echo 'style="display:none"'; ?> />
					</p>
					<p>
						<span class="smiwT" style="font-size:.8em;<?php if(isset($rencCustom['smiw'])) echo 'display:none'; ?>"><?php echo __('Smile already sent','rencontre'); ?></span>
						<input type="text" class="smiwF" id="smiw5" name="smiw5" placeholder="<?php echo __('Smile already sent','rencontre'); ?>" value="<?php if(!empty($rencCustom['smiw5'])) echo stripslashes($rencCustom['smiw5']); ?>" <?php if(!isset($rencCustom['smiw'])) echo 'style="display:none"'; ?> />
					</p>
					<p>
						<span class="smiwT" style="font-size:.8em;<?php if(isset($rencCustom['smiw'])) echo 'display:none'; ?>"><?php echo __('Smile sent','rencontre'); ?></span>
						<input type="text" class="smiwF" id="smiw6" name="smiw6" placeholder="<?php echo __('Smile sent','rencontre'); ?>" value="<?php if(!empty($rencCustom['smiw6'])) echo stripslashes($rencCustom['smiw6']); ?>" <?php if(!isset($rencCustom['smiw'])) echo 'style="display:none"'; ?> />
					</p>
					<p>
						<span class="smiwT" style="font-size:.8em;<?php if(isset($rencCustom['smiw'])) echo 'display:none'; ?>"><?php echo __('I got a smile from','rencontre'); ?></span>
						<input type="text" class="smiwF" id="smiw7" name="smiw7" placeholder="<?php echo __('I got a smile from','rencontre'); ?>" value="<?php if(!empty($rencCustom['smiw7'])) echo stripslashes($rencCustom['smiw7']); ?>" <?php if(!isset($rencCustom['smiw'])) echo 'style="display:none"'; ?> />
					</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php echo __('Change Look word', 'rencontre').'<br /><i>'.__('(empty => no change)', 'rencontre').'</i>'; ?></label></th>
				<td>
					<input type="checkbox" name="loow" onChange="if(this.checked){jQuery('.loowF').show();jQuery('.loowT').hide();}else{jQuery('.loowT').show();jQuery('.loowF').hide();}" value="1" <?php if(isset($rencCustom['loow'])) echo 'checked'; ?>><br />
					<p>
						<span class="loowT" style="font-size:.8em;<?php if(isset($rencCustom['loow'])) echo 'display:none'; ?>"><?php echo __('Look','rencontre'); ?></span>
						<input type="text" class="loowF" id="loow1" name="loow1" placeholder="<?php echo __('Look','rencontre'); ?>" value="<?php if(!empty($rencCustom['loow1'])) echo stripslashes($rencCustom['loow1']); ?>" <?php if(!isset($rencCustom['loow'])) echo 'style="display:none"'; ?> />
					</p>
					<p>
						<span class="loowT" style="font-size:.8em;<?php if(isset($rencCustom['loow'])) echo 'display:none'; ?>"><?php echo __('I was watched by','rencontre'); ?></span>
						<input type="text" class="loowF" id="loow2" name="loow2" placeholder="<?php echo __('I was watched by','rencontre'); ?>" value="<?php if(!empty($rencCustom['loow2'])) echo stripslashes($rencCustom['loow2']); ?>" <?php if(!isset($rencCustom['loow'])) echo 'style="display:none"'; ?> />
					</p>
				</td>
			</tr>
		</table>
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save','rencontre') ?>" />
		</p>
	</form>
	
	<?php
	}
//
function rencTabSea()
	{
	global $rencCustom; global $rencDiv; global $wpdb; ?>
	
	<form method="post" name="customForm" action="admin.php?page=renccustom&renctab=sea">
		<input type="hidden" name="a1" value="custom" />
		<input type="hidden" name="a2" value="" />
		<table class="form-table" style="max-width:600px;clear:none;z-index:5;">
			<tr valign="top">
				<th scope="row"><label><?php _e('Add relation type in quick search', 'rencontre'); ?></label></th>
				<td><input type="checkbox" name="relationQ" value="1" <?php if(isset($rencCustom['relationQ']))echo 'checked'; ?>></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php _e('No ad in search results', 'rencontre'); ?></label></th>
				<td><input type="checkbox" name="searchAd" value="1" <?php if(isset($rencCustom['searchAd']))echo 'checked'; ?>></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php _e('Add a profile element in quick search', 'rencontre'); ?> - 1</label></th>
				<td>
					<select name="profilQS1">
						<?php echo '<option value="" '.((isset($rencCustom['profilQS1']) && $rencCustom['profilQS1']=='')?'selected':'').'>-&nbsp;'.__('No', 'rencontre').'&nbsp;-</option>';
						$p3 = $wpdb->get_results("SELECT id, c_categ, c_label, i_type FROM ".$wpdb->prefix."rencontre_profil WHERE c_lang='".substr($rencDiv['lang'],0,2)."' and i_type IN (3,4,5) ORDER BY i_categ,i_label");
						if($p3) foreach($p3 as $r)
							{
							echo '<option value="'.$r->id.'" '.((isset($rencCustom['profilQS1']) && $rencCustom['profilQS1']==$r->id)?' selected':'').'>'.$r->c_categ.' : '.$r->c_label.' ('.($r->i_type==3?'select':($r->i_type==4?'check':'num select')).')</option>';
							}
						?>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label><?php _e('Add a profile element in quick search', 'rencontre'); ?> - 2</label></th>
				<td>
					<select name="profilQS2">
						<?php echo '<option value="" '.((isset($rencCustom['profilQS2']) && $rencCustom['profilQS2']=='')?'selected':'').'>-&nbsp;'.__('No', 'rencontre').'&nbsp;-</option>';
						if($p3) foreach($p3 as $r)
							{
							echo '<option value="'.$r->id.'" '.((isset($rencCustom['profilQS2']) && $rencCustom['profilQS2']==$r->id)?' selected':'').'>'.$r->c_categ.' : '.$r->c_label.' ('.($r->i_type==3?'select':($r->i_type==4?'check':'num select')).')</option>';
							}
						?>
					</select>
				</td>
			</tr>
			<?php $ho = false; if(has_filter('rencProfilSaP', 'f_rencProfilSaP')) $ho = apply_filters('rencProfilSaP', $p3); if($ho) echo $ho; else echo '<tr><td colspan=2><em>'.__('Add also numerous profile elements in search ? Get the kit Premium.', 'rencontre').'</em></td></tr>'; ?>
		</table>
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save','rencontre') ?>" />
		</p>
	</form>
	
	<?php
	}
function rencTabTem()
	{ ?>
	
	<h2 style="margin-left:15px;"><?php _e('Custom templates in your theme','rencontre') ?></h2>
	<table class="tem">
		<tr>
			<th><?php _e('Template file','rencontre') ?></th>
			<th><?php _e('Plugin version','rencontre') ?></th>
			<th><?php _e('Theme version','rencontre') ?></th>
		</tr>
	<?php if(is_dir(dirname(__FILE__).'/../templates') && $h=opendir(dirname(__FILE__).'/../templates'))
			{
			$d = array();
			while(false!==($f=readdir($h)))
				{
				if($f!='.' && $f!='..' && is_file(dirname(__FILE__).'/../templates/'.$f)) $d[] = $f;
				}
			closedir($h);
			}		
		sort($d);
		foreach($d as $r)
			{
			$q = file_get_contents(dirname(__FILE__).'/../templates/'.$r, false, null, 0, 300);
			$a = '?'; $b = '/';
			$a1 = strpos($q, '* Last Change :');
			if($a1)
				{
				$a2 = strpos(substr($q,$a1+15,30), '*');
				if($a2) $a = trim(substr($q,$a1+15,$a2));
				}
			if(file_exists(get_stylesheet_directory().'/templates/'.$r))
				{
				$q = file_get_contents(get_stylesheet_directory().'/templates/'.$r, false, null, 0, 300);
				$a1 = strpos($q, '* Last Change :');
				if($a1)
					{
					$a2 = strpos(substr($q,$a1+15,30), '*');
					if($a2)
						{
						$b = trim(substr($q,$a1+15,$a2));
						if($b!=$a) $b = '<span style="color:brown;">'.$b.'</span>';
						}
					}
				} ?>
			
		<tr>
			<td><?php echo $r; ?></td>
			<td><?php echo $a; ?></td>
			<td><?php echo $b; ?></td>
		</tr>
		<?php }
		$q = file_get_contents(dirname(__FILE__).'/../css/rencontre.css', false, null, 0, 300);
		$a = '?'; $b = '/';
		$a1 = strpos($q, '* Last Change :');
		if($a1)
			{
			$a2 = strpos(substr($q,$a1+15,30), '*');
			if($a2) $a = trim(substr($q,$a1+15,$a2));
			}
		if(file_exists(get_stylesheet_directory().'/templates/rencontre.css'))
			{
			$q = file_get_contents(get_stylesheet_directory().'/templates/rencontre.css', false, null, 0, 300);
			$a1 = strpos($q, '* Last Change :');
			if($a1)
				{
				$a2 = strpos(substr($q,$a1+15,30), '*');
				if($a2)
					{
					$b = trim(substr($q,$a1+15,$a2));
					if($b!=$a) $b = '<span style="color:brown;">'.$b.'</span>';
					}
				}
			} ?>
			
		<tr>
			<td>rencontre.css</td>
			<td><?php echo $a; ?></td>
			<td><?php echo $b; ?></td>
		</tr>
		<?php $ho = false; if(has_filter('rencTempListaP', 'f_rencTempListaP')) $ho = apply_filters('rencTempListaP', $ho); if($ho) echo $ho; ?>
	
	</table>
	<?php
	}
//
// *****************************************
// **** AUTRES
// *****************************************
function f_update_custom($f)
	{
	global $rencOpt; global $rencCustom;
	$a = $rencCustom; $relationL = 0; $sexL = 0;
	if(empty($_GET['renctab']))
		{
		if(isset($f['country'])) $a['country'] = $f['country']; else unset($a['country']);
		if(isset($f['region'])) $a['region'] = $f['region']; else unset($a['region']);
		if(isset($f['place'])) $a['place'] = $f['place']; else unset($a['place']);
		if(isset($f['born'])) $a['born'] = $f['born']; else unset($a['born']);
		if(isset($f['agemin'])) $a['agemin'] = $f['agemin']; else unset($a['agemin']);
		if(isset($f['agemax'])) $a['agemax'] = $f['agemax']; else unset($a['agemax']);
		if(isset($f['smile'])) $a['smile'] = $f['smile']; else unset($a['smile']);
		if(isset($f['weight'])) $a['weight'] = $f['weight']; else unset($a['weight']);
		if(isset($f['weightu'])) $a['weightu'] = $f['weightu']; else unset($a['weightu']);
		if(isset($f['size'])) $a['size'] = $f['size']; else unset($a['size']);
		if(isset($f['sizeu'])) $a['sizeu'] = $f['sizeu']; else unset($a['sizeu']);
		if(isset($f['report'])) $a['report'] = $f['report']; else unset($a['report']);
		if(isset($f['emot'])) $a['emot'] = $f['emot']; else unset($a['emot']);
		if(isset($f['menu'])) $a['menu'] = $f['menu']; else unset($a['menu']);
		if(isset($f['side'])) $a['side'] = $f['side']; else unset($a['side']);
		if(isset($f['libreAd'])) $a['libreAd'] = $f['libreAd']; else unset($a['libreAd']);
		if(isset($f['libreFlag'])) $a['libreFlag'] = $f['libreFlag']; else unset($a['libreFlag']);
		if(isset($f['librePhoto'])) $a['librePhoto'] = $f['librePhoto']; else unset($a['librePhoto']);
		if(isset($f['relation'])) $a['relation'] = $f['relation']; else unset($a['relation']);
		if(isset($f['sex'])) $a['sex'] = $f['sex']; else unset($a['sex']);
		if(isset($f['multiSR'])) $a['multiSR'] = $f['multiSR']; else unset($a['multiSR']);
		if(isset($f['reglink'])) $a['reglink'] = $f['reglink']; else unset($a['reglink']);
		if(isset($f['unreg'])) $a['unreg'] = $f['unreg']; else unset($a['unreg']);
		if(isset($f['unmail'])) $a['unmail'] = $f['unmail']; else unset($a['unmail']);
		foreach($f as $k=>$v)
			{
			if(strpos($k,'relationL')!==false) 
				{
				if(!(substr($f['a2'],0,9)=='relationS' && $k=='relationL'.substr($f['a2'],9)))
					{
					$a['relationL'.$relationL] = $v; ++$relationL;  // MOVE
					}
				}
			else if(strpos($k,'sexL')!==false) 
				{
				if(!(substr($f['a2'],0,4)=='sexS' && $k=='sexL'.substr($f['a2'],4)))
					{
					$a['sexL'.$sexL] = $v; ++$sexL;  // MOVE
					}
				}
			}
		if($f['a2']=='relationP') $a['relationL'.$relationL] = ''; // ADD
		if(substr($f['a2'],0,9)=='relationS') unset($a['relationL'.$relationL]); // DEL
		if($f['a2']=='sexP') $a['sexL'.$sexL] = ''; // ADD
		if(substr($f['a2'],0,4)=='sexS') unset($a['sexL'.$sexL]); // DEL
		if(!isset($a['relationL0'])) unset($a['relation']);
		if(!isset($a['sexL0'])) unset($a['sex']);
		}
	else if($_GET['renctab']=='wor')
		{
		if(isset($f['new'])) $a['new'] = $f['new']; else unset($a['new']);
		if(isset($f['newText'])) $a['newText'] = $f['newText']; else unset($a['newText']);
		if(isset($f['blocked'])) $a['blocked'] = $f['blocked']; else unset($a['blocked']);
		if(isset($f['blockedText'])) $a['blockedText'] = $f['blockedText']; else unset($a['blockedText']);
		if(isset($f['empty'])) $a['empty'] = $f['empty']; else unset($a['empty']);
		if(isset($f['emptyText'])) $a['emptyText'] = $f['emptyText']; else unset($a['emptyText']);
		if(isset($f['jail'])) $a['jail'] = $f['jail']; else unset($a['jail']);
		if(isset($f['jailText'])) $a['jailText'] = $f['jailText']; else unset($a['jailText']);
		if(isset($f['noph'])) $a['noph'] = $f['noph']; else unset($a['noph']);
		if(isset($f['nophText'])) $a['nophText'] = $f['nophText']; else unset($a['nophText']);
		if(isset($f['relanc'])) $a['relanc'] = $f['relanc']; else unset($a['relanc']);
		if(isset($f['relancText'])) $a['relancText'] = $f['relancText']; else unset($a['relancText']);
		if(isset($f['smiw'])) $a['smiw'] = $f['smiw']; else unset($a['smiw']);
		if(isset($f['smiw1'])) $a['smiw1'] = $f['smiw1']; else unset($a['smiw1']);
		if(isset($f['smiw2'])) $a['smiw2'] = $f['smiw2']; else unset($a['smiw2']);
		if(isset($f['smiw3'])) $a['smiw3'] = $f['smiw3']; else unset($a['smiw3']);
		if(isset($f['smiw4'])) $a['smiw4'] = $f['smiw4']; else unset($a['smiw4']);
		if(isset($f['smiw5'])) $a['smiw5'] = $f['smiw5']; else unset($a['smiw5']);
		if(isset($f['smiw6'])) $a['smiw6'] = $f['smiw6']; else unset($a['smiw6']);
		if(isset($f['smiw7'])) $a['smiw7'] = $f['smiw7']; else unset($a['smiw7']);
		if(isset($f['loow'])) $a['loow'] = $f['loow']; else unset($a['loow']);
		if(isset($f['loow1'])) $a['loow1'] = $f['loow1']; else unset($a['loow1']);
		if(isset($f['loow2'])) $a['loow2'] = $f['loow2']; else unset($a['loow2']);
		}
	else if($_GET['renctab']=='sea')
		{
		if(isset($f['relationQ'])) $a['relationQ'] = $f['relationQ']; else unset($a['relationQ']);
		if(isset($f['searchAd'])) $a['searchAd'] = $f['searchAd']; else unset($a['searchAd']);
		if(isset($f['profilQS1'])) $a['profilQS1'] = $f['profilQS1']; else unset($a['profilQS1']);
		if(isset($f['profilQS2'])) $a['profilQS2'] = $f['profilQS2']; else unset($a['profilQS2']);
		if(has_filter('rencProfilS2aP', 'f_rencProfilS2aP')) $a = apply_filters('rencProfilS2aP', $f, $a);
		}
	$rencOpt['custom'] = json_encode($a);
	$for = $rencOpt['for']; $iam = $rencOpt['iam'];
	unset($rencOpt['for']); unset($rencOpt['iam']);
	update_option('rencontre_options',$rencOpt);
	$rencOpt['for'] = $for; $rencOpt['iam'] = $iam;
	$rencCustom = json_decode($rencOpt['custom'],true);
	}
function f_userPrison($f)
	{
	// $f : id table rencontre_prison
	if(!is_admin()) exit;
	global $wpdb;
	$wpdb->delete($wpdb->prefix.'rencontre_prison', array('id'=>$f));
	}
function sauvProfilAdm($in,$id)
	{
	// Copie de la fonction dans rencontre_widget avec POST au lieu de GET
	// entree : Sauvegarde du profil
	// sortie bdd : [{"i":10,"v":"Sur une ile deserte avec mon amoureux."},{"i":35,"v":0},{"i":53,"v":[0,4,6]}]
	$u = "";
	if($in) foreach ($in as $r=>$r1) 
		{
		switch ($r1[0])
			{
			case 1:
				if($_POST['text'.$r]!="") $u.='{"i":'.$r.',"v":"'.str_replace('"','',strip_tags(stripslashes($_POST['text'.$r]))).'"},';
			break;
			case 2:
				if($_POST['area'.$r]!="") $u.='{"i":'.$r.',"v":"'.str_replace('"','',strip_tags(stripslashes($_POST['area'.$r]))).'"},';
			break;
			case 3:
				if($_POST['select'.$r]>0) $u.='{"i":'.$r.',"v":'.(strip_tags($_POST['select'.$r]-1)).'},';
			break;
			case 4:
				if(!empty($_POST['check'.$r]))
					{
					$u.='{"i":'.$r.',"v":[';
					foreach($_POST['check'.$r] as $r2) $u.=$r2.',';
					$u=substr($u, 0, -1).']},';
					}
			break;
			case 5:
				if($_POST['ns'.$r]>0) $u.='{"i":'.$r.',"v":'.(strip_tags($_POST['ns'.$r]-1)).'},';
			break;
			}
		}
	global $wpdb; global $rencCustom;
	// Rencontre_users_profil
	$a = array();
	if(isset($_POST['titre'])) $a['t_titre'] = strip_tags(stripslashes($_POST['titre']));
	if(isset($_POST['annonce'])) $a['t_annonce'] = strip_tags(stripslashes($_POST['annonce']));
	$a['t_profil'] = '['.substr($u, 0, -1).']';
	if(isset($_POST['resetact']))
		{
		if(isset($_POST['nomail']) && $_POST['nomail']) $a['t_action'] = '{"option":",nomail,"}';
		else $a['t_action'] = '[]';
		}
	if(isset($_POST['resetsig'])) $a['t_signal'] = '[]';
	$wpdb->update($wpdb->prefix.'rencontre_users_profil', $a, array('user_id'=>$id));
	// Rencontre_users
	$a = array();
	if(isset($_POST['pays'])) $a['c_pays'] = strip_tags($_POST['pays']);
	if(isset($_POST['region']))
		{
		$region = $wpdb->get_var("SELECT c_liste_valeur FROM ".$wpdb->prefix."rencontre_liste WHERE id='".strip_tags($_POST['region'])."'");
		$a['c_region'] = $region;
		}
	if(isset($_POST['ville'])) $a['c_ville'] = strip_tags($_POST['ville']);
	if(isset($_POST['resetgps']))
		{
		$a['e_lat'] = 0;
		$a['e_lon'] = 0;
		}
	if(isset($_POST['sex'])) $a['i_sex'] = strip_tags($_POST['sex']);
	else $a['i_sex'] = 0;
	if(isset($_POST['annee']) && isset($_POST['mois']) && isset($_POST['jour'])) $a['d_naissance'] = strip_tags($_POST['annee']).'-'.strip_tags($_POST['mois']).'-'.strip_tags($_POST['jour']);
	if(isset($_POST['taille'])) $a['i_taille'] = strip_tags($_POST['taille']);
	if(isset($_POST['poids'])) $a['i_poids'] = strip_tags($_POST['poids']);
	if(isset($_POST['zageMin'])) $a['i_zage_min'] = strip_tags($_POST['zageMin']);
	if(isset($_POST['zageMax'])) $a['i_zage_max'] = strip_tags($_POST['zageMax']);
	if(!isset($rencCustom['multiSR']))
		{
		if(isset($_POST['zsex']))
			{
			$a['i_zsex'] = strip_tags($_POST['zsex']);
			$a['c_zsex'] = ',';
			}
		if(isset($_POST['zrelation']))
			{
			$a['i_zrelation'] = strip_tags($_POST['zrelation']);
			$a['c_zrelation'] = ',';
			}
		}
	else
		{
		$czs = ','; $czr = ',';
		if(isset($_POST['zsex'])) foreach($_POST['zsex'] as $r) $czs .= $r . ',';
		$a['i_zsex'] = '99';
		$a['c_zsex'] = $czs;
		if(isset($_POST['zrelation'])) foreach($_POST['zrelation'] as $r) $czr .= $r . ',';
		$a['i_zrelation'] = '99';
		$a['c_zrelation'] = $czr;
		}
	if(!empty($_POST['confmail']))
		{
		$a['i_status'] = 0;
		$wpdb->delete($wpdb->prefix.'usermeta', array('user_id'=>$id, 'meta_key'=>'rencontre_confirm_email'));
		}
	$wpdb->update($wpdb->prefix.'rencontre_users', $a, array('user_id'=>$id));
	// *********** Patch install before V1.7 : Remove double (miss UNIQUE in table creation) *************
	$q = $wpdb->get_results("SELECT user_id FROM ".$wpdb->prefix."rencontre_users WHERE user_id=".$id);
	if(count($q)>1)
		{
		$q = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."rencontre_users WHERE user_id=".$id." LIMIT 1");
		$wpdb->delete($wpdb->prefix.'rencontre_users', array('user_id'=>$id));
		$wpdb->insert($wpdb->prefix.'rencontre_users', array(
			'user_id'=>$id,
			'c_ip'=>($q->c_ip?$q->c_ip:'127.0.0.1'),
			'c_pays'=>$q->c_pays,
			'c_region'=>$q->c_region,
			'c_ville'=>$q->c_ville,
			'e_lat'=>$q->e_lat,
			'e_lon'=>$q->e_lon,
			'i_sex'=>$q->i_sex,
			'd_naissance'=>$q->d_naissance,
			'i_taille'=>$q->i_taille,
			'i_poids'=>$q->i_poids,
			'i_zsex'=>$q->i_zsex,
			'i_zage_min'=>$q->i_zage_min,
			'i_zage_max'=>$q->i_zage_max,
			'i_zrelation'=>$q->i_zrelation,
			'i_photo'=>$q->i_photo,
			'd_session'=>$q->d_session)
			);
		}
	// ****************************************************************
	}
function renc_encodeImg($f=1)
	{
	// Encode or Decode all img
	// $f = 1 => encode ; $f = 0 => decode
	global $rencDiv; global $rencOpt;
	$size = array("","-mini","-grande","-libre","Blur","-miniBlur","-grandeBlur","-libreBlur");
	if(has_filter('rencImgName', 'f_rencImgName')) $size = apply_filters('rencImgName', $size);
	if($f) // ENCODE
		{
		$a = renc_list_files($rencDiv['basedir'].'/portrait/');
		foreach($a as $r)
			{
			if(strpos($r,'z')===false) // allready encoded
				{
				$r0 = substr($r,0,strrpos($r,'/')+1); // folder
				if($r0!="" && ctype_digit(substr($r0,0,-1))) // only numeric folder
					{
					$r1 = substr($r,strrpos($r,'/')+1,-4); // name
					$r2 = Rencontre::f_img($r1,1); // encoded name
					if(copy($rencDiv['basedir'].'/portrait/'.$r, $rencDiv['basedir'].'/portrait/'.$r0.$r2.'.jpg')) unlink($rencDiv['basedir'].'/portrait/'.$r);
					}
				}
			}
		$rencOpt['imcode'] = 1;
		}
	else // DECODE
		{
		global $wpdb;
	//	$min = $wpdb->get_var("SELECT MIN(user_id) FROM ".$wpdb->prefix."rencontre_users");
		$min = 0;
		$max = $wpdb->get_var("SELECT MAX(user_id) FROM ".$wpdb->prefix."rencontre_users");
		for($v=$min; $v<=$max; ++$v)
			{
			$r0 = floor($v/1000).'/'; // folder
			$b = 0;
			if(!file_exists($rencDiv['basedir'].'/portrait/'.$r0.Rencontre::f_img(($v*10),1).'.jpg')) $b = 1;
			for($w=0;$w<10;++$w)
				{
				foreach($size as $s)
					{
					if(file_exists($rencDiv['basedir'].'/portrait/'.$r0.Rencontre::f_img((($v*10)+$w).$s,1).'.jpg'))
						{
						if($b) unlink($rencDiv['basedir'].'/portrait/'.$r0.Rencontre::f_img((($v*10)+$w).$s,1).'.jpg');
						else if(copy($rencDiv['basedir'].'/portrait/'.$r0.Rencontre::f_img((($v*10)+$w).$s,1).'.jpg', $rencDiv['basedir'].'/portrait/'.$r0.(($v*10)+$w).$s.'.jpg')) unlink($rencDiv['basedir'].'/portrait/'.$r0.Rencontre::f_img((($v*10)+$w).$s,1).'.jpg');
						}
					}
				}
			$rencOpt['imcode'] = 0;
			}
		}
	$e = rencImEncoded();
	if($e!==false) $rencOpt['imcode'] = $e; // false = no photo (no member ?) to check
	update_rencontre_options($rencOpt);
	}
function rencImEncoded()
	{
	global $wpdb; global $rencDiv;
	$i = $wpdb->get_var("SELECT user_id FROM ".$wpdb->prefix."rencontre_users WHERE i_photo>0 LIMIT 1");
	if($i!==null && file_exists($rencDiv['basedir'].'/portrait/'.floor($i/1000).'/'.Rencontre::f_img(($i*10),1).'.jpg')) return 1;
	else if($i!==null && file_exists($rencDiv['basedir'].'/portrait/'.floor($i/1000).'/'.($i*10).'.jpg')) return 0;
	else return false; // no image, no member ?
	}
function renc_list_files($dir)
	{
	$root = scandir($dir);
	$result = array();
	foreach($root as $value)
		{
		if($value === '.' || $value === '..') {continue;}
		if(is_file($dir.'/'.$value)) {$result[]=$value; continue;}
		if(strpos($value,'libre')===false) foreach(renc_list_files($dir.'/'.$value) as $value2) $result[]=$value.'/'.$value2;
		}
	return $result;
	}
function f_rencStat()
	{
	global $wpdb;
	$an=date('Y-m-d H:i:s', mktime(0, 0, 0, date("m"), date("d")-7, date("Y")-1));
	$q = $wpdb->get_results("SELECT U.ID, U.user_registered, R.d_session
		FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users R 
		WHERE 
			R.c_ip!='' and 
			U.user_registered>'".$an."' and 
			U.ID=R.user_id
		ORDER BY U.ID asc
		");
	$o = array();
	foreach($q as $r)
		{
		$d = false;
		if($r->user_registered) $d = round((mktime(0, 0, 0, date("m"), date("d"), date("Y")) - mktime(0, 0, 0, substr($r->user_registered,5,7)+0, substr($r->user_registered,8,10)+0, substr($r->user_registered,0,4)+0)) / 86400);
		$t = mktime(0, 0, 0, substr($r->user_registered,5,7)+0, substr($r->user_registered,8,10)+0, substr($r->user_registered,0,4)+0);
		$s = mktime(0, 0, 0, substr($r->d_session,5,7)+0, substr($r->d_session,8,10)+0, substr($r->d_session,0,4)+0);
		if($s!=$t && $s>time()-7862400) $act = 1; // 3 months
		else $act = 0;
		$o[] = array(
			'id'=>$r->ID,
			'tme'=>$t,
			'act'=>$act,
			'day'=>$d,
			'h'=>substr($r->d_session,11,16),
			's'=>date("w", mktime(0, 0, 0, substr($r->d_session,5,7)+0, substr($r->d_session,8,10)+0, substr($r->d_session,0,4)+0))
			);
		}
	echo json_encode($o);
//	file_put_contents(dirname(__FILE__).'/donnees.txt', json_encode($o));
//	$a = file_get_contents(dirname(__FILE__).'/donnees.txt');
//	echo $a;
	}
function f_newMember()
	{
	// New member from wordpress
	global $wpdb; global $rencOpt;
	$i = strip_tags($_POST['id']);
	$wpdb->delete($wpdb->prefix.'rencontre_users', array('user_id'=>$i)); // suppression si existe deja
	$wpdb->delete($wpdb->prefix.'rencontre_users_profil', array('user_id'=>$i)); // suppression si existe deja
	$wpdb->insert($wpdb->prefix.'rencontre_users', array(
		'user_id'=>$i,
		'c_ip'=>'127.0.0.90',
		'c_pays'=>(empty($rencOpt['pays'])?'FR':$rencOpt['pays']), // default - custom no localisation
		'd_session'=>current_time("mysql"),
		'i_photo'=>0));
	$wpdb->insert($wpdb->prefix.'rencontre_users_profil', array(
		'user_id'=>$i,
		'd_modif'=>current_time("mysql"),
		't_titre'=>'',
		't_annonce'=>'',
		't_profil'=>'[]'));
	if(empty($rencOpt['rol'])) $wpdb->delete($wpdb->prefix.'usermeta', array('user_id'=>$i)); // suppression des roles WP
	}
function rencMetaMenu()
	{
	// Menu rencontre - Rencontre.php with add_action('admin_init','rencMetaMenu');
	add_meta_box(
		'rencontre-metaMenu',
		'Rencontre',
		'rencMetaMenuContent',
		'nav-menus',
		'side',
		'low');
	}
function rencMetaMenuContent()
	{
	global $rencOpt;
	$o = '<div id="posttype-rencontre" class="posttypediv">';
	$o .= '<div id="tabs-panel-rencontre" class="tabs-panel tabs-panel-active">';
	$o .= '<ul id ="rencontre-checklist" class="categorychecklist form-no-clear">'."\r\n";
	$u = 'http://'.$_SERVER['HTTP_HOST']; $u1 = explode("?",$_SERVER['REQUEST_URI']); $u .= $u1[0];
	$u = (!empty($rencOpt['home'])?$rencOpt['home']:$u);
	$a = array(	// title, url, CSS class, sub-level
		"-1"=>array(__('My homepage','rencontre'),$u,'',0),
		"-2"=>array(__('My card','rencontre'),'#rencnav#card#','',1),
		"-3"=>array(__('Edit My Profile','rencontre'),'#rencnav#edit#','',1),
		"-4"=>array(__('Messaging','rencontre'),'#rencnav#msg#','',1),
		"-5"=>array(__('Search','rencontre'),'#rencnav#gsearch#','',1),
		"-6"=>array(__('My Account','rencontre'),'#rencnav#account#','',1),
		"-7"=>array(__('Log in'),'#rencloginout#','rencLoginout',0),
		"-8"=>array(__('Register'),'#rencregister#','rencRegister',0));
	$n = 0;
	foreach($a as $k=>$v)
		{
		$oi = ''; $of = '';
		if($n==0 && $v[3]==1)
			{
			$oi = "\r\n".'<ul>';
			$n = 1;
			}
		else if($n==1 && $v[3]==0)
			{
			$oi = '</li>'."\r\n".'</ul></li>';
			$n = 0;
			}
		else if($k!='-1') $o .= '</li>'."\r\n";
		$o .= $oi . '<li>';
		$o .= '<label class="menu-item-title"><input type="checkbox" class="menu-item-checkbox" name="menu-item['.$k.'][menu-item-object-id]" value="'.$k.'" />'.$v[0].'</label>';
		$o .= '<input type="hidden" class="menu-item-type" name="menu-item['.$k.'][menu-item-type]" value="custom" />';
		$o .= '<input type="hidden" class="menu-item-title" name="menu-item['.$k.'][menu-item-title]" value="'.$v[0].'" />';
		$o .= '<input type="hidden" class="menu-item-url" name="menu-item['.$k.'][menu-item-url]" value="'.$v[1].'" />';
		$o .= '<input type="hidden" class="menu-item-classes" name="menu-item['.$k.'][menu-item-classes]" value="'.$v[2].'">';
		}
	if($n==1) $o .= '</li></ul>';
	$o .= '</li></ul></div>'."\r\n";
	$o .= '<p class="button-controls">';
	$o .= '<span class="list-controls"><a href="'.site_url().'/wp-admin/nav-menus.php?rencontre=all&amp;selectall=1#rencontre-metaMenu" class="select-all">Select All</a></span>';
	$o .= '<span class="add-to-menu"><input type="submit" class="button-secondary submit-add-to-menu right" value="Add to Menu" name="add-post-type-menu-item" id="submit-posttype-rencontre"><span class="spinner"></span></span>';
	$o .= '</p></div><!-- #posttype-rencontre -->'."\r\n";
	echo $o;
	}
//
//
?>
