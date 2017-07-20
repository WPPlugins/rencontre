<?php
$rencOpt = get_option('rencontre_options');
// Filtres / Action : General
add_filter('show_admin_bar' , 'rencAdminBar'); // Visualisation barre admin
add_action('init', 'rencPreventAdminAccess', 0); // bloque acces au tableau de bord
add_action('init', 'rencInLine', 1); // session
add_action('wp_logout', 'rencOutLine'); // session
add_filter('login_redirect', 'rencLogRedir', 10, 3); // redirection after login
// add_filter('random_password', 'f_length_pass'); function f_length_pass($pass) {$pass = substr($pass,0,3); return $pass;}
add_action('admin_bar_menu', 'f_admin_menu', 999);
if(!empty($rencOpt['avatar'])) add_filter('get_avatar', 'rencAvatar', 1, 5);
if(!empty($rencOpt['fastreg']))
	{
	add_action('register_form', 'rencFastreg_form');
	add_filter('registration_errors', 'rencFastreg_errors', 10, 3);
	add_action('user_register', 'rencFastreg', 10, 1);
	}
add_shortcode('rencontre_libre', 'f_shortcode_rencontre_libre');
add_shortcode('rencontre_nbmembre', 'f_shortcode_rencontre_nbmembre');
add_shortcode('rencontre_search', 'f_shortcode_rencontre_search');
add_shortcode('rencontre_login', 'f_shortcode_rencontre_login');
add_shortcode('rencontre_loginFB', 'f_shortcode_rencontre_loginFB');
add_shortcode('rencontre_imgreg', 'f_shortcode_rencontre_imreg'); // [rencontre_imgreg title= selector= left= top=]
function f_shortcode_rencontre_libre($a) {if(!is_user_logged_in()) return Rencontre::f_ficheLibre($a,1);} // shortcode : [rencontre_libre gen=mix/girl/men]
function f_shortcode_rencontre_nbmembre($a) {return Rencontre::f_nbMembre($a);} // shortcode : [rencontre_nbmembre gen=girl/men ph=1]
function f_shortcode_rencontre_search($a) {if(!is_user_logged_in()) return Rencontre::f_rencontreSearch(1,$a);} // shortcode : [rencontre_search nb=8] - nb:number of result
function f_shortcode_rencontre() {if(is_user_logged_in()) {$renc=new RencontreWidget; ob_start(); $renc->widget(0,0); $a = ob_get_contents(); ob_end_clean(); return $a;}} // shortcode : [rencontre]
function f_shortcode_rencontre_login() {return Rencontre::f_login(0,1);} // shortcode : [rencontre_login]
function f_shortcode_rencontre_loginFB() {return Rencontre::f_loginFB(1);} // shortcode : [rencontre_loginFB]
function f_shortcode_rencontre_imreg($a) {if(!is_user_logged_in()) return Rencontre::f_rencontreImgReg($a);} // shortcode : [rencontre_imgreg title= selector='.site-header .wp-custom-header img' left=20 top=20] - left & top in purcent
if(isset($_COOKIE['lang']) && strlen($_COOKIE['lang'])==5) add_filter('locale', 'set_locale2'); function set_locale2() { return $_COOKIE['lang']; }
// Mail
//add_filter ('retrieve_password_message', 'retrieve_password_message2', 10, 2);
// AJAX
add_action('wp_ajax_regionBDD', 'f_regionBDD'); // AJAX - retour des regions dans le select
add_action('wp_ajax_sourire', 'f_sourire'); function f_sourire() {}
add_action('wp_ajax_voirMsg', 'f_voirMsg'); function f_voirMsg() {RencontreWidget::f_voirMsg($_POST['msg'],$_POST['alias'],(isset($_POST['ho'])?$_POST['ho']:false));}
add_action('wp_ajax_testPseudo', 'rencTestPseudo');
add_action('wp_ajax_iniUser', 'rencIniUser'); // premiere connexion - changement eventuel pseudo
add_action('wp_ajax_testPass', 'rencTestPass'); // changement du mot de passe
add_action('wp_ajax_fbok', 'rencFbok'); add_action('wp_ajax_nopriv_fbok', 'rencFbok'); // connexion via FB
add_action('wp_ajax_miniPortrait2', 'f_miniPortrait2'); function f_miniPortrait2() {RencontreWidget::f_miniPortrait2($_POST['id']);}
add_action('wp_ajax_fastregMail', 'f_fastregMail'); function f_fastregMail() {$u = wp_get_current_user(); Rencontre::fastreg_email($u,1);}
add_action('wp_ajax_addCountSearch', 'f_addCountSearch'); // +1 dans action search si meme jour
if(is_admin())
	{
	add_action('wp_ajax_iso', 'rencontreIso'); // Test si le code ISO est libre (Partie ADMIN)
	add_action('wp_ajax_drap', 'rencontreDrap'); // SELECT avec la liste des fichiers drapeaux (Partie ADMIN)
	add_action('wp_ajax_exportCsv', 'f_exportCsv'); // Export CSV (Partie ADMIN)
	add_action('wp_ajax_importCsv', 'f_importCsv'); // Import CSV (Partie ADMIN)
	add_action('wp_ajax_updown', 'f_rencUpDown'); // Modif Profil : move Up / Down
	add_action('wp_ajax_profilA', 'f_rencProfil'); // Modif Profil : plus & edit
	add_action('wp_ajax_stat', 'f_rencStat'); // Members - Registration statistics
	add_action('wp_ajax_newMember', 'f_newMember'); // Add new Rencontre Members from WP Users - Members Tab
	}
// CRON
add_action('init', 'f_cron');
function f_cron()
	{
	// Filters after "init"
	add_action('wp_enqueue_scripts', 'rencCssJs'); // add rencontre.css in header & rencontre.js in footer if needed
	add_shortcode('rencontre', 'f_shortcode_rencontre');
	if(function_exists('wpGeonames')) add_action('wp_ajax_city', 'rencontreCity');
	add_filter('wp_setup_nav_menu_item', 'rencMetaMenuItem', 1);
	//if(!is_user_logged_in()) add_filter('wp_get_nav_menu_items', 'rencHideMenu', null, 3); // hide rencontre items in WP menu
	//
	global $rencDiv;
	if(!is_dir($rencDiv['basedir'].'/portrait/')) mkdir($rencDiv['basedir'].'/portrait/');
	if(!is_dir($rencDiv['basedir'].'/portrait/cache/')) mkdir($rencDiv['basedir'].'/portrait/cache/');
	if(!is_dir($rencDiv['basedir'].'/portrait/cache/cron_liste/')) mkdir($rencDiv['basedir'].'/portrait/cache/cron_liste/');
	$d = $rencDiv['basedir'].'/portrait/cache/rencontre_cron.txt';
	$d1 = $rencDiv['basedir'].'/portrait/cache/rencontre_cronOn.txt';
	$d2 = $rencDiv['basedir'].'/portrait/cache/rencontre_cronListe.txt'; if(!file_exists($d2)) {$t=@fopen($d2,'w'); @fwrite($t,'0'); @fclose($t);}
	$d3 = $rencDiv['basedir'].'/portrait/cache/rencontre_cronListeOn.txt';
	$d4 = $rencDiv['basedir'].'/portrait/cache/rencontre_cronBis.txt';
	global $rencOpt;
	$t = current_time('timestamp',0); // timestamp local
	$gmt = time(); // timestamp GMT
	$hcron = (isset($rencOpt['hcron'])?$rencOpt['hcron']+0:3);
	$u1 = date("G",$t-3600*$hcron); // heure actuelle(UTC) - heure creuse (+24 si <0) ; ex il est 15h23Z (15), Hcreuse:4h (4) => $u = 15 - 4 = 11;
	// u1 progresse 21, 22, 23 puis 0 lorsqu'il est l'heure creuse (donc<12). Il reste alors 12 heures pour qu"un visiteur provoque le CRON.
	if(!file_exists($d) || (date("j",filemtime($d))!=date("j",$gmt) && $u1<12) && $gmt>filemtime($d)+7200) // !existe ou (jour different et dans les 12 heures qui suivent hcron et plus de 2 heures apres precedent)
		{
		if(!file_exists($d1) || $gmt>filemtime($d1)+120)
			{
			$t=fopen($d1, 'w'); fclose($t); // CRON une seule fois
			f_cron_on(0);
			}
		}
	else if(file_exists($d4) && $u1<12 && $gmt>filemtime($d)+3661)
		{
		if(!file_exists($d1) || $gmt>filemtime($d1)+120)
			{
			$t=fopen($d1, 'w'); fclose($t); // CRON BIS une seule fois, une heure apres CRON
			f_cron_on(1); // second passage (travail sur deux passages)
			}
		}
	else if($gmt>filemtime($d)+3661 && $gmt>filemtime($d2)+3661 && $u1<23 && (!file_exists($d3) || $gmt>filemtime($d3)+120))
		{
		$t=fopen($d3, 'w'); fclose($t); // CRON LISTE une seule fois
		f_cron_liste($d2); // MSG ACTION
		}
	 //f_cron_on(); // mode force
	 //f_cron_liste($d2); // mode force
	}
//
function set_html_content_type(){ return 'text/html'; }
//
function f_cron_on($cronBis=0)
	{
	// NETTOYAGE QUOTIDIEN
	global $wpdb; global $rencOpt; global $rencDiv; global $rencCustom;
	$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_RAND);
	$ii = base64_encode($iv);
	$bn = get_bloginfo('name');
	$s1 = ""; // (synthese admin)
	$cm = 0; // compteur de mail
	if(!$cronBis)
		{
		// 1. Efface les _transient dans wp_option
		$wpdb->query("DELETE FROM ".$wpdb->prefix."options WHERE option_name like '\_transient\_namespace\_%' OR option_name like '\_transient\_timeout\_namespace\_%' ");
		// 2. Suppression fichiers anciens dans UPLOADS/PORTRAIT/LIBRE/ : > 2.9 jours
		if(!is_dir($rencDiv['basedir'].'/portrait/libre/')) @mkdir($rencDiv['basedir'].'/portrait/libre/');
		else
			{
			$tab=''; $d=$rencDiv['basedir'].'/portrait/libre/';
			if($dh=opendir($d))
				{
				while (($file = readdir($dh))!==false) { if($file!='.' && $file!='..') $tab[]=$d.$file; }
				closedir($dh);
				if($tab!='') foreach ($tab as $r){if(filemtime($r)<time()-248400) unlink($r);} // 69 heures
				}
			}
		// 3. Supprime le cache portraits page d'accueil. Remise a jour a la premiere visite (fiches libre)
		if(file_exists($rencDiv['basedir'].'/portrait/cache/cache_portraits_accueil.html')) @unlink($rencDiv['basedir'].'/portrait/cache/cache_portraits_accueil.html');
		if(file_exists($rencDiv['basedir'].'/portrait/cache/cache_portraits_accueilmix.html')) @unlink($rencDiv['basedir'].'/portrait/cache/cache_portraits_accueilmix.html');
		if(file_exists($rencDiv['basedir'].'/portrait/cache/cache_portraits_accueilgirl.html')) @unlink($rencDiv['basedir'].'/portrait/cache/cache_portraits_accueilgirl.html');
		if(file_exists($rencDiv['basedir'].'/portrait/cache/cache_portraits_accueilmen.html')) @unlink($rencDiv['basedir'].'/portrait/cache/cache_portraits_accueilmen.html');
		// 4. Suppression des utilisateur sans compte rencontre fini
		$d = date("Y-m-d H:i:s", mktime(0,0,0,date("m"),date("d"),date("Y"))-216000); // 60 heures
		if(!empty($rencOpt['fastreg'])) // email not confirmed ?
			{
			$q = $wpdb->get_results("SELECT 
					U.ID, 
					R.i_photo 
				FROM
					".$wpdb->prefix."users U,
					".$wpdb->prefix."rencontre_users R,
					".$wpdb->prefix."usermeta M 
				WHERE 
					R.user_id=U.ID and 
					M.user_id=U.ID and 
					(M.meta_key='rencontre_confirm_email' or R.i_sex='98') and 
					U.user_registered<'".$d."' 
				");
			if($q) foreach($q as $r)
				{
				if($r->i_photo) f_suppImgAll($r->ID);
				$wpdb->delete($wpdb->prefix.'rencontre_users', array('user_id'=>$r->ID));
				$wpdb->delete($wpdb->prefix.'rencontre_users_profil', array('user_id'=>$r->ID));
				$wpdb->delete($wpdb->prefix.'usermeta', array('user_id'=>$r->ID));
				$wpdb->delete($wpdb->prefix.'users', array('ID'=>$r->ID));
				}
			}
		if(!empty($rencOpt['rol']))
			{ // Uniquement les comptes Rencontre non finis. Utiliseur maintenue
			$q = $wpdb->get_results("SELECT R.user_id, R.i_photo FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users R WHERE R.user_id=U.ID and (R.c_ip='' or R.i_sex='98') and U.user_registered<'".$d."' ");
			if($q) foreach($q as $r)
				{
				if($r->i_photo) RencontreWidget::suppImgAll($r->ID,false);
				$wpdb->delete($wpdb->prefix.'rencontre_users', array('user_id'=>$r->user_id));
				$wpdb->delete($wpdb->prefix.'rencontre_users_profil', array('user_id'=>$r->user_id));
				}
			}
		else // general case
			{
			$q = $wpdb->get_results("SELECT U.ID FROM ".$wpdb->prefix."users U LEFT OUTER JOIN ".$wpdb->prefix."rencontre_users R ON U.ID=R.user_id WHERE R.user_id IS NULL or R.c_ip='' or R.i_sex='98'");
			if($q) foreach($q as $r)
				{
				$s = $wpdb->get_var("SELECT ID FROM ".$wpdb->prefix."users WHERE ID='".$r->ID."' and user_registered<'".$d."' LIMIT 1");
				if($s && !user_can($s,'edit_posts'))
					{
					$wpdb->delete($wpdb->prefix.'usermeta', array('user_id'=>$r->ID));
					$wpdb->delete($wpdb->prefix.'users', array('ID'=>$r->ID));
					}
				}
			}
		// 5. Delete the users in rencontre and not in WP
		$q = $wpdb->get_results("SELECT R.user_id 
			FROM ".$wpdb->prefix."rencontre_users R
			WHERE R.user_id NOT IN (SELECT U.ID FROM ".$wpdb->prefix."users U) 
			");
		if($q) foreach($q as $r)
			{
			if(!empty($r->user_id))
				{
				$wpdb->delete($wpdb->prefix.'rencontre_users', array('user_id'=>$r->user_id));
				$wpdb->delete($wpdb->prefix.'rencontre_users_profil', array('user_id'=>$r->user_id));
				}
			}

		// 6. Fastreg décoché en admin : recadrer les nouveaux membres en fastreg - cas particulier
		if(isset($rencOpt['fastreg']) && !$rencOpt['fastreg'])
			{
			$q = $wpdb->get_results("SELECT U.ID FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users R WHERE R.user_id=U.ID and R.i_status=4 ");
			if($q) foreach($q as $r)
				{
				$p = md5(mt_rand());
				$wpdb->update($wpdb->prefix.'users', array('user_pass'=>$p), array('ID'=>$r->ID)); // confirmation email par demande de nouveau password
				$wpdb->update($wpdb->prefix.'rencontre_users', array('c_ip'=>'', 'i_status'=>0), array('user_id'=>$r->ID)); // procedure inscription 1 a 4
				}
			}
		// 7. Mail de relance (uniquement enregistrement classique)
		if(empty($rencOpt['fastreg']))
			{
			$q = $wpdb->get_results("SELECT U.ID, U.user_login, U.user_email, U.user_registered FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users R WHERE R.user_id=U.ID and R.c_ip='' ");
			$o = '';
			if($q) foreach($q as $r)
				{
				$oo = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, substr(AUTH_KEY,0,16), $r->ID . '|' . $r->user_login . '|' . time(), MCRYPT_MODE_CBC, $iv));
				$s = "<div style='text-align:left;margin:5px 5px 5px 10px;font-size:13px;font-family:\"Helvetica Neue\",Helvetica;'>".__('Hello','rencontre')." ".$r->user_login.","."\n";
				if(isset($rencCustom['relanc']) && isset($rencCustom['relancText']) && $rencCustom['relanc']) $s .= "<br />".nl2br(stripslashes($rencCustom['relancText']))."\n";
				else
					{
					$s .= '<p>'.__('You registered on our website but you did not complete the procedure. You\'ll miss a date. That\'s too bad.','rencontre').'</p>';
					$s .= '<p>'.__('Thus take two minutes to finish the registration. You should not be disappointed.','rencontre').'</p>';
					$s .= '<p>'.__('Regards,','rencontre').'</p>';
					}
				$s .= '<a href="'.htmlspecialchars((empty($rencOpt['home'])?site_url():$rencOpt['home']).'?rencoo='.urlencode($oo).'&rencii='.urlencode($ii)).'">'.__('Login','rencontre').'</a>';
				$s .= "</div>\n";
				$he = '';
				if(!has_filter('wp_mail_content_type'))
					{
					$he[] = 'From: '.$bn.' <'.$rencDiv['admin_email'].'>';
					$he[] = 'Content-type: text/html; charset=UTF-8';
					$s = '<html><head></head><body>'.$s.'</body></html>';
					}
				@wp_mail($r->user_email, $bn." - ".__('Registration','rencontre'), $s, $he);
				++$cm;
				}
			}
		// 8. Suppression fichiers anciens dans UPLOADS/SESSION/ et UPLOADS/TCHAT/ et des exports CSV UPLOADS/TMP
		if(!is_dir($rencDiv['basedir'].'/session/')) mkdir($rencDiv['basedir'].'/session/');
		else
			{
			$tab=''; $d=$rencDiv['basedir'].'/session/';
			if($dh=opendir($d))
				{
				while (($file = readdir($dh))!==false) { if($file!='.' && $file!='..') $tab[]=$d.$file; }
				closedir($dh);
				if($tab!='') foreach ($tab as $r){if(filemtime($r)<time()-1296000) unlink($r);} // 15 jours
				}
			}
		if(!is_dir($rencDiv['basedir'].'/tchat/')) mkdir($rencDiv['basedir'].'/tchat/');
		else
			{
			$tab=''; $d=$rencDiv['basedir'].'/tchat/';
			if($dh=opendir($d))
				{
				while (($file = readdir($dh))!==false) { if($file!='.' && $file!='..') $tab[]=$d.$file; }
				closedir($dh);
				if($tab!='') foreach ($tab as $r){if(filemtime($r)<time()-86400) unlink($r);} // 24 heures
				}
			}
		if(is_dir($rencDiv['basedir'].'/tmp/'))
			{
			$a=array();
			if($h=opendir($rencDiv['basedir']."/tmp/"))
				{
				while (($file=readdir($h))!==false)
					{
					$ext=explode('.',$file);
					$ext=$ext[count($ext)-1];
					if($ext=='csv' && $file!='.' && $file!='..' && strpos($file,"rencontre")!==false) $a[]=$rencDiv['basedir']."/tmp/".$file;
					}
				closedir($h);
				}
			// ************************
			if(is_array($a)) array_map('unlink', $a);
			}
		// 9. Sortie de prison
		$free=date("Y-m-d",mktime(0, 0, 0, date("m"), date("d")-(isset($rencOpt['prison'])?$rencOpt['prison']:7), date("Y")));
		$wpdb->query("DELETE FROM ".$wpdb->prefix."rencontre_prison WHERE d_prison<'".$free."' ");
		// 10. anniversaire du jour
		if(!empty($rencOpt['mailanniv']))
			{
			$q = $wpdb->get_results("SELECT U.ID, U.user_login, U.user_email, R.user_id 
				FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users R 
				WHERE 
					R.d_naissance LIKE '%".current_time('m-d')."' 
					AND U.ID=R.user_id 
					".(!empty($rencOpt['mailph'])?'AND R.i_photo>0':'')."
				LIMIT ".floor(max(0, (isset($rencOpt['qmail'])?$rencOpt['qmail']:0)*.1)) );
			foreach($q as $r)
				{
				$s = "<div style='font-family:\"Helvetica Neue\",Helvetica;font-size:13px;text-align:left;margin:5px 5px 5px 10px;'>".__('Hello','rencontre')." ".$r->user_login.","."\n";
				if(!empty($rencOpt['textanniv']) && strlen($rencOpt['textanniv'])>10) $s .= "<br />".nl2br(stripslashes($rencOpt['textanniv']))."\n";
				$s .= "</div>\n";
				$he = '';
				if(!has_filter('wp_mail_content_type'))
					{
					$he[] = 'From: '.$bn.' <'.$rencDiv['admin_email'].'>';
					$he[] = 'Content-type: text/html; charset=UTF-8';
					$s = '<html><head></head><body>'.$s.'</body></html>';
					}
				@wp_mail($r->user_email, $bn." - ".__('Happy Birthday','rencontre'), $s, $he);
				++$cm;
				$s1 .= $s;
				}
			}
		// 11. Efface une fois par semaine les statistiques du nombre de mail par heure
		if(current_time("N")=="1")  // le lundi
			{
			$t=@fopen($rencDiv['basedir'].'/portrait/cache/rencontre_cronListe.txt','w'); @fwrite($t,'0'); @fclose($t);
			$t=@fopen($rencDiv['basedir'].'/portrait/cache/rencontre_cron.txt','w'); @fwrite($t,$cm); @fclose($t);
			}
		}
	// 12. Mail vers les membres et nettoyage des comptes actions (suppression comptes inexistants)
	$j = floor((floor(time()/86400)/60 - floor(floor(time()/86400)/60)) * 60 +.00001); // horloge de jour de 0 à 59 (temps unix) - ex : aujourd'hui -> 4
	if(isset($rencOpt['mailmois']) && $rencOpt['mailmois']==2)
		{
		$j0 = floor(($j/15-floor($j/15)) * 15 + .00001); // horloge de jour de 0 a 14
		if(!$cronBis) // CRON (H)
			{
			$max = floor(max(0, (isset($rencOpt['qmail'])?$rencOpt['qmail']:0)*.85)); // 85% du max - heure creuse - 15% restant pour inscription nouveaux membres et anniv
			$j1=$j0+15;
			}
		else // CRON BIS (H+1)
			{
			$max = floor(max(0, (isset($rencOpt['qmail'])?$rencOpt['qmail']:0)*.95)); // 95% du max - heure creuse - 5% restant pour inscription nouveaux membres
			$j2=$j0+30; $j3=$j0+45;
			}
		}
	else if(isset($rencOpt['mailmois']) && $rencOpt['mailmois']==3)
		{
		$j0 = floor(($j/7-floor($j/7)) * 7 + .00001); // horloge de jour de 0 a 6
		if(!$cronBis) // CRON (H)
			{
			$max = floor(max(0, (isset($rencOpt['qmail'])?$rencOpt['qmail']:0)*.85)); // 85% du max - heure creuse - 15% restant pour inscription nouveaux membres et anniv
			$j1=$j0+7; $j2=$j0+14; $j3=$j0+21;
			}
		else // CRON BIS (H+1)
			{
			$max = floor(max(0, (isset($rencOpt['qmail'])?$rencOpt['qmail']:0)*.95)); // 95% du max - heure creuse - 5% restant pour inscription nouveaux membres
			$j4=$j0+28; $j5=$j0+35; $j6=$j0+42; $j7=$j0+49; $j8=$j0+56;
			}
		}
	else
		{
		$jj = ($j>29)?$j-30:$j+30; // aujourd'hui : 34
		if(!$cronBis) $max = floor(max(0, (isset($rencOpt['qmail'])?$rencOpt['qmail']:0)*.85)); // 85% du max - heure creuse - 15% restant pour inscription nouveaux membres et anniv
		else $max = floor(max(0, (isset($rencOpt['qmail'])?$rencOpt['qmail']:0)*.95)); // 95% du max - heure creuse - 5% restant pour inscription nouveaux membres
		}
		// 12.1 selection des membres
	$rencDrap='';
	if(!isset($rencCustom['place']))
		{
		$q = $wpdb->get_results("SELECT c_liste_categ, c_liste_valeur, c_liste_iso 
			FROM ".$wpdb->prefix."rencontre_liste 
			WHERE 
				c_liste_categ='d' or (c_liste_categ='p' and c_liste_lang='".substr($rencDiv['lang'],0,2)."') ");
		foreach($q as $r)
			{
			if($r->c_liste_categ=='d') $rencDrap[$r->c_liste_iso] = $r->c_liste_valeur;
			}
		}
	$q=0;
	$qq1 = "SELECT 
			U.ID,
			U.user_login,
			U.user_email,
			P.t_action,
			R.i_sex,
			R.i_zsex,
			R.c_zsex,
			R.i_zage_min,
			R.i_zage_max,
			R.i_zrelation,
			R.c_zrelation
		FROM 
			".$wpdb->prefix."users U,
			".$wpdb->prefix."rencontre_users_profil P,
			".$wpdb->prefix."rencontre_users R ";
	$qq2 = " AND U.ID=P.user_id
			AND U.ID=R.user_id
			AND R.i_sex!='98'
			AND R.i_status!=4
			AND (P.t_action NOT LIKE '%,nomail,%' OR P.t_action IS NULL)
		ORDER BY P.d_modif DESC
		LIMIT ".$max;
	if(!$cronBis && isset($rencOpt['mailmois']) && $rencOpt['mailmois']==2) $q = $wpdb->get_results($qq1." WHERE SECOND(U.user_registered) IN (".$j0.",".$j1.") ".$qq2);
	else if($cronBis && isset($rencOpt['mailmois']) && $rencOpt['mailmois']==2) $q = $wpdb->get_results($qq1." WHERE SECOND(U.user_registered) IN (".$j2.",".$j3.") ".$qq2);
	else if(!$cronBis && isset($rencOpt['mailmois']) && $rencOpt['mailmois']==3) $q = $wpdb->get_results($qq1." WHERE SECOND(U.user_registered) IN (".$j0.",".$j1.",".$j2.",".$j3.") ".$qq2);
	else if($cronBis && isset($rencOpt['mailmois'])  && $rencOpt['mailmois']==3) $q = $wpdb->get_results($qq1." WHERE SECOND(U.user_registered) IN (".$j4.",".$j5.",".$j6.",".$j7.",".$j8.") ".$qq2);
	else if(!$cronBis) $q = $wpdb->get_results($qq1." WHERE SECOND(U.user_registered)='".$j."' ".$qq2);
	else if($cronBis) $q = $wpdb->get_results($qq1." WHERE SECOND(U.user_registered)='".$jj."' ".$qq2);
	if(isset($rencOpt['mailmois']) && $rencOpt['mailmois']==1) $ti = 2592000; // 30j
	else if(isset($rencOpt['mailmois']) && $rencOpt['mailmois']==2) $ti = 1296000; // 15j
	else $ti = 604800; // 7j
	
		// 12.2 boucle de mail
	$ct=0;
	if($q) foreach($q as $r)
		{
		++$ct;
		$action = json_decode($r->t_action,true);
		if(!empty($rencOpt['mailmois']) && $ct<=$max)
			{
			$b = 0;
			// Connect_link
			$oo = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, substr(AUTH_KEY,0,16), $r->ID . '|' . $r->user_login . '|' . time(), MCRYPT_MODE_CBC, $iv));
			// BONJOUR
			$s = "<div style='font-family:\"Helvetica Neue\",Helvetica;font-size:13px;text-align:left;margin:5px 5px 5px 10px;'>".__('Hello','rencontre')."&nbsp;".$r->user_login.",";
			if(!empty($rencOpt['textmail']) && strlen($rencOpt['textmail'])>10) $s .= "<br />".nl2br(stripslashes($rencOpt['textmail']))."\n";
			// NBR VISITES
			if(isset($action['visite'])) $s .= "<p style='font-weight:700;font-size:.9em;'>".__('Your profile has been visited','rencontre')."&nbsp;<span style='color:red;'>".count($action['visite'])."&nbsp;".__('time','rencontre')."</span>.\n</p>";
			// PROPOSITIONS
			$zmin=date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"), date("Y")-$r->i_zage_min));
			$zmax=date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"), date("Y")-$r->i_zage_max));
			// Selection par le sex
			if($r->i_sex==$r->i_zsex) $homo=1; else $homo=0;
			if($r->i_zsex!=99)
				{
				$sexQuery = " AND R.i_sex=".$r->i_zsex." ";
				if(!isset($rencCustom['sex'])) $sexQuery .= " AND R.i_zsex".(($homo)?'='.$r->i_zsex:'!='.$r->i_zsex)." ";
				}
			else $sexQuery = " AND R.i_sex IN (".substr($r->c_zsex,1,-1).") ";
			if($r->i_zrelation!=99) $relQuery = " AND (R.i_zrelation=".$r->i_zrelation." OR  R.c_zrelation LIKE '%,".$r->i_zrelation.",%') ";
			else $relQuery = ''; // pas de else - trop complique sans boucle - ,1,3,6, IN/LIKE/= ,2,3,5,
			//
			$q1 = $wpdb->get_results("SELECT 
					U.ID, 
					U.user_login, 
					R.d_naissance, 
					R.c_pays, 
					R.c_ville, 
					P.t_titre
				FROM 
					".$wpdb->prefix."users U, 
					".$wpdb->prefix."rencontre_users R, 
					".$wpdb->prefix."rencontre_users_profil P 
				WHERE 
					U.ID=R.user_id 
					AND P.user_id=R.user_id 
					".$sexQuery."
					".$relQuery."
					AND R.d_naissance<'".$zmin."' 
					AND R.d_naissance>'".$zmax."' 
					AND U.ID!='".$r->ID."'
					".((!empty($rencOpt['onlyphoto']) || !empty($rencOpt['mailph']))?" AND R.i_photo>0 ":" ")."
				ORDER BY U.user_registered DESC
				LIMIT 4");
			if($q1)
				{
				$b = 1;
				$s .= "<p style='font-weight:700;font-size:.9em;'>".__('Here\'s a selection of members that may interest you','rencontre')." :</p>";
				$s .= "<table><tr>";
				$c = 0;
				foreach($q1 as $r1)
					{
					++$c;
					$s .= rencMailBox($r1,$rencDrap,$oo,$ii);
					if($c>1)
						{
						$c = 0;
						$s .= "</tr><tr>";
						}
					}
				$s .= "</tr></table>"."\n";
				}
			// SOURIRES
			if(isset($action['sourireIn']) && count($action['sourireIn']))
				{
				$t = "<p style='font-weight:700;font-size:.9em;'>";
				if(isset($rencCustom['smiw']) && isset($rencCustom['smiw4']) && $rencCustom['smiw'] && $rencCustom['smiw4']) $t .= stripslashes($rencCustom['smiw4']);
				else $t .= __('You have received a smile from','rencontre');
				$t .= " :\n</p><table><tr>";
				$c = 0;
				for($v=0; $v<count($action['sourireIn']);++$v)
					{
					if(isset($action['contactIn'][$v]['d']) && strtotime($action['contactIn'][$v]['d'])>current_time('timestamp',0)-$ti) // only new before last mail
						{
						$r1 = $wpdb->get_row("SELECT
								U.ID,
								U.user_login,
								R.d_naissance,
								R.c_pays,
								R.c_ville,
								P.t_titre
							FROM
								".$wpdb->prefix."users U,
								".$wpdb->prefix."rencontre_users R,
								".$wpdb->prefix."rencontre_users_profil P 
							WHERE 
								R.user_id='".$action['sourireIn'][$v]['i']."'
								AND U.ID=R.user_id 
								AND P.user_id=R.user_id 
								".((!empty($rencOpt['onlyphoto']) || !empty($rencOpt['mailph']))?" AND R.i_photo>0 ":" ")."
							ORDER BY R.d_session DESC
							LIMIT 1
							");
						if($r1)
							{
							$b = 1;
							++$c;
							$s .= $t . rencMailBox($r1,$rencDrap,$oo,$ii);
							$t = '';
							if($c>1)
								{
								$c = 0;
								$s .= "</tr><tr>";
								}
							}
						}
					}
				if($t=="") $s .= "</tr></table>"."\n";
				}
			// DEMANDES DE CONTACT
			if(isset($action['contactIn']) && count($action['contactIn']))
				{
				$t = "<p style='font-weight:700;font-size:.9em;'>".__('You have received a contact request from','rencontre')." :\n</p><table><tr>";
				$c = 0;
				for ($v=0; $v<count($action['contactIn']);++$v)
					{
					if(isset($action['contactIn'][$v]['d']) && strtotime($action['contactIn'][$v]['d'])>current_time('timestamp',0)-$ti) // only new before last mail
						{
						$r1 = $wpdb->get_row("SELECT
								U.ID,
								U.user_login,
								R.d_naissance,
								R.c_pays,
								R.c_ville,
								P.t_titre
							FROM
								".$wpdb->prefix."users U,
								".$wpdb->prefix."rencontre_users R,
								".$wpdb->prefix."rencontre_users_profil P 
							WHERE 
								R.user_id='".$action['contactIn'][$v]['i']."'
								AND U.ID=R.user_id 
								AND P.user_id=R.user_id 
								".((!empty($rencOpt['onlyphoto']) || !empty($rencOpt['mailph']))?" AND R.i_photo>0 ":" ")."
							ORDER BY R.d_session DESC
							LIMIT 1
							");
						if($r1)
							{
							++$c;
							$s .= $t . rencMailBox($r1,$rencDrap,$oo,$ii);
							$t = '';
							if($c>1)
								{
								$b = 1;
								$c = 0;
								$s .= "</tr><tr>";
								}
							}
						}
					}
				if($t=="") $s .= "</tr></table>";
				}
			// MESSAGES
			$n = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."rencontre_msg M WHERE M.recipient='".$r->user_login."' and M.read=0 and M.deleted=0");
			if($n)
				{
				$b = 1;
				$s .= "<p style='font-weight:700;font-size:.9em;'>".__('You have','rencontre')."&nbsp;<span style='color:red;'>".$n."&nbsp;".(($n>1)?__('messages','rencontre'):__('message','rencontre'))."</span>&nbsp;".__('in your inbox.','rencontre')."\n</p>";
				}
			// MOT DE LA FIN
			$s .= "<p>".__("Do not hesitate to send us your comments.",'rencontre')."\n</p><br />".__('Regards,','rencontre')."<br />".$bn;
			if($b) $s .= "<div style='margin:8px 0 0;text-align:center;'><a style='display:block;text-decoration:none;text-align:center;background:#e8e5ce;background:-moz-linear-gradient(top,#e8e5ce,#a9a796);background:-webkit-linear-gradient(top,#e8e5ce,#a9a796);background:linear-gradient(top,#e8e5ce,#a9a796);border:1px solid #aaa;border-top:1px solid #ccc;border-left:1px solid #ccc;border-radius:3px;color:#444;font-size:11px;font-weight:bold;text-decoration:none;text-shadow:0 1px rgba(255,255,255,.75);padding:5px 1px;margin:2px auto;max-width:300px;' href='".htmlspecialchars((empty($rencOpt['home'])?site_url():$rencOpt['home'])."?rencoo=".urlencode($oo)."&rencii=".urlencode($ii))."' target='_blank'> ".__('Login','rencontre')."\n</a></div>";
			$s .= "</div>";
			//
			$s1 .= $s;
			if($b)
				{
				$he = '';
				if(!has_filter('wp_mail_content_type'))
					{
					$he[] = 'From: '.$bn.' <'.$rencDiv['admin_email'].'>';
					$he[] = 'Content-type: text/html; charset=UTF-8';
					$s = '<html><head></head><body>'.$s.'</body></html>';
					}
				@wp_mail($r->user_email, $bn, $s, $he);
				++$cm;
				}
			if(file_exists($rencDiv['basedir'].'/portrait/cache/cron_liste/'.$r->ID.'.txt')) @unlink($rencDiv['basedir'].'/portrait/cache/cron_liste/'.$r->ID.'.txt');
			}
		// 12.3 *********** Nettoyage des comptes action *********
		// {"sourireIn":[{"i":992,"d":"2015-09-01"},{"i":75,"d":"2015-09-01"}],"contactIn":[{"i":992,"d":"2015-09-01"}]}
		$ac = array("sourireIn","sourireOut","contactIn","contactOut","visite","bloque");
		$x = 0;
		for ($v=0; $v<count($ac); ++$v)
			{
			if(isset($action[$ac[$v]]))
				{
				$c = count($action[$ac[$v]]);
				for ($w=0; $w<$c; ++$w)
					{
					if(isset($action[$ac[$v]][$w]['i']))
						{
						$q1 = $wpdb->get_var("SELECT user_id 
							FROM ".$wpdb->prefix."rencontre_users 
							WHERE user_id='".$action[$ac[$v]][$w]['i']."'
							LIMIT 1"); // compte suprime ?
						if(!$q1)
							{
							if(!$x) $x = 1;
							unset($action[$ac[$v]][$w]['i']); 
							unset($action[$ac[$v]][$w]['d']);
							}
						}
					}
				if($action[$ac[$v]]) $action[$ac[$v]]=array_filter($action[$ac[$v]]);
				if($action[$ac[$v]]) $action[$ac[$v]] = array_splice($action[$ac[$v]], 0); // remise en ordre avec de nouvelles clefs
				}
			}
		if($x)
			{
			$out = json_encode($action);
			$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('t_action'=>$out), array('user_id'=>$r->ID));
			}
		// 13. Suppression des msg anciens
		if(!empty($rencOpt['msgdel']))
			{
			$d = array(1=>7884000, 2=>15768000, 3=>31536000, 4=>2592000);
			if(isset($d[$rencOpt['msgdel']]))
				{
				$d1 = date('Y-m-d H:i:s', time()-$d[$rencOpt['msgdel']]);
				$wpdb->query("DELETE FROM ".$wpdb->prefix."rencontre_msg WHERE date<'".$d1."' ");
				}
			}
		// ***************************************************
		}
	//
	if(current_time("N")!="1")$t=@fopen($rencDiv['basedir'].'/portrait/cache/rencontre_cron.txt', 'w'); @fwrite($t,max((file_get_contents($rencDiv['basedir'].'/portrait/cache/rencontre_cron.txt')+0),$cm)); @fclose($t);
	if($cronBis) @unlink($rencDiv['basedir'].'/portrait/cache/rencontre_cronBis.txt'); // CRON BIS effectue
	else {$t=@fopen($rencDiv['basedir'].'/portrait/cache/rencontre_cronBis.txt', 'w'); @fclose($t);} // CRON BIS a faire
	@unlink($rencDiv['basedir'].'/portrait/cache/rencontre_cronOn.txt');
	@unlink($rencDiv['basedir'].'/portrait/cache/rencontre_cronListeOn.txt');
	clearstatcache();
	}
//
function f_cron_liste($d2)
	{
	// Envoi Mail Horaire en respectant quota
	global $wpdb; global $rencOpt; global $rencDiv; global $rencCustom;
	$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_RAND);
	$ii = base64_encode($iv);
	$rencDrap='';
	if(!isset($rencCustom['place']))
		{
		$q = $wpdb->get_results("SELECT c_liste_categ, c_liste_valeur, c_liste_iso 
			FROM ".$wpdb->prefix."rencontre_liste 
			WHERE 
				c_liste_categ='d' or (c_liste_categ='p' and c_liste_lang='".substr($rencDiv['lang'],0,2)."') ");
		foreach($q as $r)
			{
			if($r->c_liste_categ=='d') $rencDrap[$r->c_liste_iso] = $r->c_liste_valeur;
			}
		}
	$max = floor(max(0, (isset($rencOpt['qmail'])?$rencOpt['qmail']:0)*.8)); // 80% du max - 20% restant pour inscription nouveaux membres
	$u2 = file_get_contents($d2);
	$cm = 0; // compteur de mail
	// 1. listing des USERS en attente
	if($dh = @opendir($rencDiv['basedir'].'/portrait/cache/cron_liste/'))
		{
		$bn = get_bloginfo('name');
		$lis = '(';
		$fi = Array();
		$c = 0;
		while (($file = readdir($dh))!==false)
			{
			$lid=explode('.',$file);
			if($file!='.' && $file!='..')
				{
				if(!preg_match('/^[0-9]+$/',$lid[0])) @unlink($dh.$file);
				else
					{
					$fi[$c][0] = filemtime($rencDiv['basedir'].'/portrait/cache/cron_liste/'.$file); // date - en premier pour le sort
					$fi[$c][1] = $lid[0]; // nom
					++$c;
					}
				}
			}
		sort($fi); // les plus ancien en premier
		$c = 0;
		foreach ($fi as $r)
			{
			++$c;
			if($c>$max) break;
			if($r[1]) $lis .= $r[1].","; else --$c;
			}
		if(strlen($lis)>2) $lis = substr($lis,0,-1) . ')'; else $lis='(0)';
		closedir($dh);
		$q = $wpdb->get_results("SELECT
				U.ID,
				U.user_login,
				U.user_email,
				P.t_action 
			FROM
				".$wpdb->prefix."users U,
				".$wpdb->prefix."rencontre_users_profil P,
				".$wpdb->prefix."rencontre_users R
			WHERE 
				U.ID IN ".$lis." 
				AND U.ID=P.user_id 
				AND U.ID=R.user_id
				AND (P.t_action NOT LIKE '%,nomail,%' OR P.t_action IS NULL)
				".((!empty($rencOpt['mailph']))?" AND R.i_photo>0 ":" ")."
			LIMIT ".$max); // clause IN : WHERE U.ID IN ( 250, 220, 170 );
		$las = 0;
		if($q) foreach($q as $r)
			{ // {"sourireIn":[{"i":992,"d":"2015-09-01"},{"i":75,"d":"2015-09-01"}],"contactIn":[{"i":992,"d":"2015-09-01"}]}
			$b = 0;
			$oo = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, substr(AUTH_KEY,0,16), $r->ID . '|' . $r->user_login . '|' . time(), MCRYPT_MODE_CBC, $iv));
			$action= json_decode($r->t_action,true);
			$s = "<div style='font-family:\"Helvetica Neue\",Helvetica;font-size:13px;text-align:left;margin:5px 5px 5px 10px;color:#000;'>".__('Hello','rencontre')."&nbsp;".$r->user_login.",";
			if(isset($action['contactIn']) && count($action['contactIn']))
				{
				$v = count($action['contactIn'])-1;
				if(isset($action['contactIn'][$v]['d']) && strtotime($action['contactIn'][$v]['d'])>current_time('timestamp',0)-64800) // 18h
					{
					$c = 0;
					$t = "<p>".__('You have received a contact request from','rencontre')."\n</p><table><tr>";
					$r1 = $wpdb->get_row("SELECT
							U.ID,
							U.user_login,
							R.d_naissance,
							R.c_pays,
							R.c_ville,
							P.t_titre
						FROM
							".$wpdb->prefix."users U,
							".$wpdb->prefix."rencontre_users R,
							".$wpdb->prefix."rencontre_users_profil P 
						WHERE 
							R.user_id='".$action['contactIn'][$v]['i']."'
							and U.ID=R.user_id 
							and P.user_id=R.user_id 
							".((!empty($rencOpt['mailph']))?" and R.i_photo>0 ":" ")."
						LIMIT 1
						");
					if($r1)
						{
						$b = 1;
						++$c;
						$s .= $t . rencMailBox($r1,$rencDrap,$oo,$ii);
						$t = '';
						if($c>1)
							{
							$c = 0;
							$s .= "</tr><tr>";
							}
						}
					if($t=="") $s .= "</tr></table>";
					}
				}
			if(isset($action['sourireIn']) && count($action['sourireIn']))
				{
				$v = count($action['sourireIn'])-1;
				if(isset($action['sourireIn'][$v]['d']) && strtotime($action['sourireIn'][$v]['d'])>current_time('timestamp',0)-64800) // 18h
					{
					$c = 0;
					$t = "<p>";
					if(isset($rencCustom['smiw']) && isset($rencCustom['smiw4']) && $rencCustom['smiw'] && $rencCustom['smiw4']) $t .= stripslashes($rencCustom['smiw4']);
					else $t .= __('You have received a smile from','rencontre');
					$t .= "</p><table><tr>";
					$r1 = $wpdb->get_row("SELECT
							U.ID,
							U.user_login,
							R.d_naissance,
							R.c_pays,
							R.c_ville,
							P.t_titre
						FROM
							".$wpdb->prefix."users U,
							".$wpdb->prefix."rencontre_users R,
							".$wpdb->prefix."rencontre_users_profil P 
						WHERE 
							R.user_id='".$action['sourireIn'][$v]['i']."'
							and U.ID=R.user_id 
							and P.user_id=R.user_id 
							".((!empty($rencOpt['mailph']))?" and R.i_photo>0 ":" ")."
						LIMIT 1
						");
					if($r1)
						{
						$b = 1;
						++$c;
						$s .= $t . rencMailBox($r1,$rencDrap,$oo,$ii);
						$t = '';
						if($c>1)
							{
							$c = 0;
							$s .= "</tr><tr>";
							}
						}
					if($t=="") $s .= "</tr></table>";
					}
				}
			$n = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."rencontre_msg M WHERE M.recipient='".$r->user_login."' and M.read=0 and M.deleted=0");
			if($n)
				{
				$b = 1;
				$s .= "\n<p>".__('You have','rencontre')."&nbsp;";
				$s .= "<a href='".htmlspecialchars((empty($rencOpt['home'])?site_url():$rencOpt['home'])."?rencidfm=r0&rencoo=".urlencode($oo)."&rencii=".urlencode($ii))."' target='_blank'>".$n."&nbsp;".(($n>1)?__('messages','rencontre'):__('message','rencontre'))."</a>";
				$s .= "&nbsp;".__('in your inbox.','rencontre')."\n</p>";
				}
			$s .= "<br /><br />".__('Regards,','rencontre')."<br />".$bn;
			if($b) $s .= "<div style='margin:8px 0 0;text-align:center;'><a style='display:block;text-decoration:none;text-align:center;background:#e8e5ce;background:-moz-linear-gradient(top,#e8e5ce,#a9a796);background:-webkit-linear-gradient(top,#e8e5ce,#a9a796);background:linear-gradient(top,#e8e5ce,#a9a796);border:1px solid #aaa;border-top:1px solid #ccc;border-left:1px solid #ccc;border-radius:3px;color:#444;font-size:11px;font-weight:bold;text-decoration:none;text-shadow:0 1px rgba(255,255,255,.75);padding:5px 1px;margin:2px auto;max-width:300px;' href='".htmlspecialchars((empty($rencOpt['home'])?site_url():$rencOpt['home'])."?rencoo=".urlencode($oo)."&rencii=".urlencode($ii))."' target='_blank'> ".__('Login','rencontre')."\n</a></div>";
			$s .= "</div>";
			if($b)
				{
				$he = '';
				if(!has_filter('wp_mail_content_type'))
					{
					$he[] = 'From: '.$bn.' <'.$rencDiv['admin_email'].'>';
					$he[] = 'Content-type: text/html; charset=UTF-8';
					$s = '<html><head></head><body>'.$s.'</body></html>';
					}
				@wp_mail($r->user_email, $bn." - ".__('A member contact you','rencontre'), $s, $he);
				++$cm;
				}
			$d = filemtime($rencDiv['basedir'].'/portrait/cache/cron_liste/'.$r->ID.'.txt');
			if($d>$las) $las = $d;
			@unlink($rencDiv['basedir'].'/portrait/cache/cron_liste/'.$r->ID.'.txt');
			}
		foreach ($fi as $r)
			{
			if($r[0]>$las) break;
			else if(file_exists($rencDiv['basedir'].'/portrait/cache/cron_liste/'.$r[1].".txt")) @unlink($rencDiv['basedir'].'/portrait/cache/cron_liste/'.$r[1].".txt");  // suppression non traite car ID inexistant
			}
		}
	$t=@fopen($d2,'w'); @fwrite($t,max(($u2+0),$cm)); @fclose($t);
	@unlink($rencDiv['basedir'].'/portrait/cache/rencontre_cronListeOn.txt');
	@unlink($rencDiv['basedir'].'/portrait/cache/rencontre_cronOn.txt');
	clearstatcache();
	}
//
function rencMailBox($u,$rencDrap,$oo,$ii)
	{
	global $rencDiv; global $rencOpt; global $rencCustom;
	if(file_exists($rencDiv['basedir']."/portrait/".floor($u->ID/1000)."/".Rencontre::f_img(($u->ID*10)."-libre").".jpg")) $u->photoUrl = $rencDiv['baseurl']."/portrait/".floor($u->ID/1000)."/".Rencontre::f_img(($u->ID*10)."-libre").".jpg";
	else $u->photoUrl = plugins_url('rencontre/images/no-photo60.jpg');
	$age = 0;
	if(!isset($rencCustom['born']))
		{
		list($annee, $mois, $jour) = explode('-', $u->d_naissance);
		$today['mois'] = current_time('n');
		$today['jour'] = current_time('j');
		$today['annee'] = current_time('Y');
		$age = $today['annee'] - $annee;
		if($today['mois']<=$mois)
			{
			if($mois==$today['mois'])
				{
				if($jour>$today['jour']) --$age;
				}
			else --$age;
			}
		}
	$u->name = substr($u->user_login,0,10);
	$u->age = $age;
	$u->title = substr($u->t_titre,0,60);
	$u->link = new StdClass();
	$a = $u->ID."&rencoo=".urlencode($oo)."&rencii=".urlencode($ii);
	$u->link->contact = htmlspecialchars((empty($rencOpt['home'])?site_url():$rencOpt['home'])."?rencidfm=c".$a);
	$u->link->smile = htmlspecialchars((empty($rencOpt['home'])?site_url():$rencOpt['home'])."?rencidfm=s".$a);
	$u->link->message = htmlspecialchars((empty($rencOpt['home'])?site_url():$rencOpt['home'])."?rencidfm=m".$a);
	$u->link->profile = htmlspecialchars((empty($rencOpt['home'])?site_url():$rencOpt['home'])."?rencidfm=p".$a);
	$buttonCSS = "display:block;letter-spacing:-.5px;text-decoration:none;text-align:center;background:#e8e5ce;background:-moz-linear-gradient(top,#e8e5ce,#a9a796);background:-webkit-linear-gradient(top,#e8e5ce,#a9a796);background:linear-gradient(top,#e8e5ce,#a9a796);border:1px solid #aaa;border-top:1px solid #ccc;border-left:1px solid #ccc;border-radius:3px;color:#444;font-size:11px;font-weight:bold;text-decoration:none;text-shadow:0 1px rgba(255,255,255,.75);padding:5px 1px;margin:2px;";
	// ****** TEMPLATE ********
	ob_start();
	if(file_exists(get_stylesheet_directory().'/templates/rencontre_mail_regular.php')) include(get_stylesheet_directory().'/templates/rencontre_mail_regular.php');
	else include(dirname( __FILE__ ).'/../templates/rencontre_mail_regular.php');
	$o = ob_get_clean();
	// ************************
	$o = trim(preg_replace('/\t+/', '', $o)); // remove tab
	$o = preg_replace('/^\s+|\n|\r|\s+$/m', '', $o); // remove line break
	return $o;
	}
//
function rencCssJs()
	{
	global $post;
	// JS
	wp_register_script('rencontre', plugins_url('rencontre/js/rencontre.js'),array(),false,true); // true : footer
	// CSS
	if(file_exists(get_stylesheet_directory().'/templates/rencontre.css')) wp_register_style('rencontre', get_stylesheet_directory_uri().'/templates/rencontre.css');
	else wp_register_style('rencontre', plugins_url('rencontre/css/rencontre.css'));
	// Enqueue if SHORTCODE
    if(is_a($post,'WP_Post'))
		{
		if(is_user_logged_in() && has_shortcode($post->post_content,'rencontre'))
			{
			wp_enqueue_style('rencontre');
			wp_enqueue_script('rencontre');
			}
		if(!is_user_logged_in() && has_shortcode($post->post_content,'rencontre_imgreg')) wp_enqueue_style('rencontre');
		}
	// Enqueue if WIDGET
	if(is_active_widget('', '', 'rencontre'))
		{
		wp_enqueue_style('rencontre');
		wp_enqueue_script('rencontre');
		}
	}
//
function f_admin_menu ($wp_admin_bar)
	{
	$args = array(
		'id'=>'rencontre',
		'title'=>'<img src="'.plugins_url('rencontre/images/rencontre.png').'" />',
		'href'=>admin_url('admin.php?page=rencmembers'),
		'meta'=>array('class'=>'rencontre',
		'title'=>'Rencontre'));
	$wp_admin_bar->add_node($args);
	}
//
function rencLogRedir($to,$req,$u)
	{
	global $rencOpt;
	if(isset($u->roles) && is_array($u->roles) && in_array('administrator',$u->roles)) return admin_url();
	else if(!empty($rencOpt['logredir']) && !empty($rencOpt['home'])) return $rencOpt['home'];
	else return $to;
	}
//
function rencMetaMenuItem($menu)
	{
	if(is_admin()) return $menu;
	if($menu->url=='#rencloginout#') // URL in metaMenu Rencontre : base.php
		{
		if(is_user_logged_in())
			{
			$menu->url = wp_logout_url(get_permalink());
			$menu->title = __('Log out');
			}
		else
			{
			$menu->url = wp_login_url(get_permalink());
			$menu->title = __('Log in');
			}
		}
	else if($menu->url=='#rencregister#')
		{
		if(is_user_logged_in()) $menu->_invalid = true; // hide
		else
			{
			$menu->url = wp_registration_url();
			$menu->title = __('Register');
			}
		}
	else if(strpos($menu->url,'#rencnav#')!==false)
		{
		global $rencOpt;
		if(is_user_logged_in())
			{
			$a = explode('#',$menu->url);
			if(!empty($rencOpt['home']))
				{
				if(strpos($rencOpt['home'],'?')!==false && strpos($rencOpt['home'],'=')!==false) $menu->url = $rencOpt['home'].'&renc='.$a[2];
				else $menu->url = $rencOpt['home'].'?renc='.$a[2]; // 'javascript:void(0)';
				}
			else $menu->url = site_url();
			}
		else $menu->url = wp_logout_url();
		}
	return $menu;
	}
//
function rencHideMenu($items,$m,$a)
	{
	foreach($items as $k=>$i) if(in_array('rencNav',$i->classes)) unset($items[$k]);
	return $items;
	}
//
function rencInLine()
	{
	if(is_user_logged_in())
		{
		if(!session_id()) session_start();
		global $current_user; global $rencDiv; global $wpdb; 
		if(!is_dir($rencDiv['basedir'].'/tchat/')) mkdir($rencDiv['basedir'].'/tchat/');
		if(!is_dir($rencDiv['basedir'].'/session/')) mkdir($rencDiv['basedir'].'/session/');
		$t = fopen($rencDiv['basedir'].'/session/'.$current_user->ID.'.txt', 'w') or die();
		fclose($t);
		$wpdb->update($wpdb->prefix.'rencontre_users', array('d_session'=>current_time("mysql")), array('user_id'=>$current_user->ID));
		}
	}
//
function rencOutLine()
	{
	global $current_user; global $rencDiv;
	if(file_exists($rencDiv['basedir'].'/session/'.$current_user->ID.'.txt')) unlink($rencDiv['basedir'].'/session/'.$current_user->ID.'.txt');
	session_destroy();
	}
//
function rencPreventAdminAccess()
	{
	global $rencDiv;
	$a=strtolower($_SERVER['REQUEST_URI']);
	if(strpos($a,'/wp-admin')!==false && strpos($a,'admin-ajax.php')==false && !current_user_can("edit_posts"))
		{
		wp_redirect($rencDiv['siteurl']);
		exit;
		}
	}
function rencAdminBar($content)
	{
	return(current_user_can("edit_posts"))?$content:false;
	}
function f_regionBDD()
	{ 
	echo '<option value="">- '.__('Immaterial','rencontre').' -</option>';
	global $wpdb; 
	$iso = strip_tags($_POST['pays']);
	$q = $wpdb->get_results("SELECT id, c_liste_valeur FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_iso='".$iso."' and c_liste_categ='r' ");
	foreach($q as $r) { echo '<option value="'.$r->id.'">'.$r->c_liste_valeur.'</option>'; }
	}
//
function rencTestPseudo()
	{ // test si pseudo libre (premiere connexion)
	// register_new_user() in user.php
	global $current_user;
	if(strip_tags($_POST['name'])==$current_user->user_login || (validate_username(strip_tags($_POST['name'])) && !username_exists(sanitize_user(strip_tags($_POST['name']))))) echo 'ok';
	else return; // already exist
	}
//
function rencTestPass() // modif compte uniquement
	{
	global $wpdb;
	$q = $wpdb->get_var("SELECT user_pass FROM ".$wpdb->prefix."users WHERE ID='".strip_tags($_POST['id'])."' LIMIT 1");
	if(wp_check_password($_POST['pass'],$q,$_POST['id']))
		{
		wp_set_password($_POST['nouv'],$_POST['id']); // changement MdP
		wp_set_auth_cookie($_POST['id']); // cookie pour rester connecte
		echo 'ok';
		}
	else return; // bad password
	}
//
function rencIniUser() // premiere connexion - changement eventuel pseudo
	{
	global $wpdb; global $current_user; global $rencOpt;
	$q = $wpdb->get_var("SELECT
			U.ID
		FROM
			".$wpdb->prefix."users U
		WHERE
			user_login='".strip_tags($_POST['pseudo'])."'
			and user_email!='".$current_user->user_email."'
		LIMIT 1");
	if(!$q)
		{
		$wpdb->update($wpdb->prefix.'users', array(
			'user_login'=>strip_tags($_POST['pseudo']),
			'user_nicename'=>strip_tags($_POST['pseudo']),
			'display_name'=>strip_tags($_POST['pseudo']),
			'user_email'=>$current_user->user_email),
			array('ID'=>$current_user->ID));
		$wpdb->update($wpdb->prefix.'rencontre_users', array(
			'c_ip'=>$_SERVER['REMOTE_ADDR']),
			array('user_id'=>$current_user->ID)); // IP => Ce n est plus un nouveau
		if(empty($rencOpt['rol'])) $wpdb->delete($wpdb->prefix.'usermeta', array('user_id'=>$current_user->ID)); // suppression des roles WP
		RencontreWidget::f_changePass($current_user->ID,$_POST['pass1']);
		wp_clear_auth_cookie();
		wp_set_current_user($current_user->ID, strip_tags($_POST['pseudo']));
		wp_set_auth_cookie($current_user->ID);
		do_action('wp_login', strip_tags($_POST['pseudo'])); // connexion
		}
	}
//
function rencFbok() // Facebook connect
	{
	if(!is_user_logged_in())
		{
		$_SESSION['rencFB']="1";
		$m = $_POST['fb'];
		if(isset($m['first_name']) && isset($m['email']) && isset($m['id']))
			{
			global $wpdb;
			$u = $wpdb->get_var("SELECT
					user_login
				FROM
					".$wpdb->prefix."users
				WHERE
					user_email='".strip_tags($m['email'])."'
				LIMIT 1");
			if(!$u) // unknow email => create user
				{
				$u = rencFbokName($m['first_name'],substr($m['id'],5,4)); // get available login
				$pw = wp_generate_password($length=5, $include_standard_special_chars=false);
				$user_id = wp_create_user($u,$pw,$m['email']);
				}
			$user = get_user_by('login',$u);
			wp_set_current_user($user->ID, $u);
			wp_set_auth_cookie($user->ID);
			do_action('wp_login', $u); // connect
			}
		}
	}
//
function rencFbokName($u,$i,$c=0)
	{
	$o = $u.$i;
	if(validate_username($o) && !username_exists(sanitize_user($o))) return $o;
	else
		{
		$i = mt_rand(100000,999999);
		++$c;
		if($c>100) return $u.md5($u.$i.mt_rand()); // this one will be ok !!!
		else return rencFbokName($u,$i,$c);
		}
	}
//
function f_addCountSearch() // Ajax
	{
	// +1 in count search/day
	global $wpdb; global $current_user;
	$p = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$current_user->ID."' LIMIT 1");
	$action = json_decode($p,true);
	if(isset($action['search']['n']) && isset($action['search']['d']) && $action['search']['d']==date("z")) $action['search']=array('d'=>date("z"),'n'=>($action['search']['n']+1));
	else $action['search']=array('d'=>date("z"),'n'=>1);
	$p = json_encode($action);
	$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('t_action'=>$p), array('user_id'=>$current_user->ID));
	}
//
function rencontreIso()
	{
	if($_POST && isset($_POST['iso']))
		{
		global $wpdb;
		$q = $wpdb->get_var("SELECT id FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_iso='".$_POST['iso']."' and c_liste_categ='p' LIMIT 1");
		if(!$q) echo true;
		else echo false;
		}
	}
//
function rencontreDrap()
	{
	if($_POST && isset($_POST['action']) && $_POST['action']=='drap')
		{
		if($dh=opendir(dirname(__FILE__).'/../images/drapeaux/'))
			{
			$tab='';
			while (($file = readdir($dh))!==false) { if($file!='.' && $file!='..') $tab[]=$file; }
			closedir($dh);
			sort($tab);
			foreach($tab as $r) { echo "<option value='".$r."'>".$r."</option>"; }
			}
		}
	}
//
function rencontreCity() // plugin WP-GeoNames
	{
	global $wpdb;
	$s = $wpdb->get_results("SELECT
			name,
			latitude,
			longitude
		FROM
			".$wpdb->prefix."geonames
		WHERE
			country_code='".strip_tags($_POST["iso"])."'
			and feature_class='P'
			and name LIKE '".strip_tags($_POST["city"])."%'
		ORDER BY name
		LIMIT 10");
	foreach($s as $t)
		{
		echo '<div onClick=\'f_cityMap("'.$t->name.'","'.$t->latitude.'","'.$t->longitude.'",'.($_POST["ch"]?'1':'0').');\'>'.$t->name.'</div>';
		}
	}
//
function f_userSupp($f,$a,$b)
	{
	$r = 'wp-content/uploads/portrait/'.floor($f/1000);
	for ($v=0; $v<6; $v++)
		{
		if(file_exists($r."/".Rencontre::f_img($f.$v).".jpg")) unlink($r."/".Rencontre::f_img($f.$v).".jpg");
		if(file_exists($r."/".Rencontre::f_img($f.$v."-mini").".jpg")) unlink($r."/".Rencontre::f_img($f.$v."-mini").".jpg");
		if(file_exists($r."/".Rencontre::f_img($f.$v."-grande").".jpg")) unlink($r."/".Rencontre::f_img($f.$v."-grande").".jpg");
		if(file_exists($r."/".Rencontre::f_img($f.$v."-libre").".jpg")) unlink($r."/".Rencontre::f_img($f.$v."-libre").".jpg");
		}
	if(!is_admin()) wp_logout();
	global $wpdb; global $rencOpt;
	if($b) // prison
		{
		$ip = 0;
		if(substr($f,0,2)=='IP')
			{
			$ip = 2;
			$f = substr($f,2);
			}
		$q = $wpdb->get_row("SELECT
				U.user_email,
				R.c_ip
			FROM
				".$wpdb->prefix."users U,
				".$wpdb->prefix."rencontre_users R
			WHERE
				U.ID=".$f." and
				U.ID=R.user_id
			LIMIT 1
			");
		$wpdb->query("INSERT INTO ".$wpdb->prefix."rencontre_prison (d_prison,c_mail,c_ip,i_type) VALUES('".current_time('mysql')."','".$q->user_email."','".$q->c_ip."',$ip)");
		}
	$wpdb->delete($wpdb->prefix.'rencontre_users_profil', array('user_id'=>$f));
	$wpdb->delete($wpdb->prefix.'rencontre_msg', array('sender'=>$a));
	$wpdb->delete($wpdb->prefix.'rencontre_msg', array('recipient'=>$a));
	$wpdb->delete($wpdb->prefix.'rencontre_users', array('user_id'=>$f));
	if(empty($rencOpt['rol']) || (!empty($rencOpt['rol']) && empty($rencOpt['rolu'])))
		{
		$wpdb->delete($wpdb->prefix.'users', array('ID'=>$f));
		$wpdb->delete($wpdb->prefix.'usermeta', array('user_id'=>$f));
		}
	if(!is_admin()) { wp_redirect(home_url()); exit; }
	}
//
function f_suppImgAll($id)
	{
	global $rencDiv;
	$r = $rencDiv['basedir'].'/portrait/'.floor($id/1000).'/';
	for($v=0;$v<9;++$v)
		{
		if(file_exists($r.Rencontre::f_img($id.$v).'.jpg')) unlink($r.Rencontre::f_img($id.$v).'.jpg');
		if(file_exists($r.Rencontre::f_img($id.$v.'-mini').'.jpg')) unlink($r.Rencontre::f_img($id.$v.'-mini').'.jpg');
		if(file_exists($r.Rencontre::f_img($id.$v.'-grande').'.jpg')) unlink($r.Rencontre::f_img($id.$v.'-grande').'.jpg');
		if(file_exists($r.Rencontre::f_img($id.$v.'-libre').'.jpg')) unlink($r.Rencontre::f_img($id.$v.'-libre').'.jpg');
		if(has_filter('rencBlurDelP', 'f_rencBlurDelP'))
			{
			$ho = new StdClass();
			$ho->id = $id;
			$ho->v = $v;
			$ho->rename = false;
			apply_filters('rencBlurDelP', $ho);
			}
		}
	}
//
function rencAvatar($avatar, $id_or_email, $size, $default, $alt)
	{
	$upl = wp_upload_dir();
	$id = false;
	if(is_numeric($id_or_email)) $id = (int)$id_or_email;
	else if(is_object($id_or_email) && !empty($id_or_email->user_id)) $id = (int)$id_or_email->user_id;
	if($id!==false)
		{
		$r = '/portrait/'.floor($id/1000).'/'.Rencontre::f_img(intval($id*10).'-'.($size<61?'mini':'grande'),2).'.jpg';
		if(file_exists($upl['basedir'].$r))
			{
			$avatar = '<img alt="'.$alt.'" src="'.$upl['baseurl'].$r.'" class="avatar avatar-'.$size.' photo" height="'.$size.'" width="'.$size.'" />';
			}
		}
	return $avatar;
	}
//
function rencFastreg_form()
	{
	global $rencCustom; global $rencOpt;
	$zsex = (!empty($_POST['zsex']))?esc_attr(wp_unslash(trim($_POST['zsex']))):'';
	$pssw = (!empty($_POST['pssw']))?esc_attr(wp_unslash(trim($_POST['pssw']))):'';
	$o = '<p>';
	$o .= '<label for="pssw">'.__('Password').'<br />';
	$o .= '<input type="password" name="pssw" id="pssw" class="input" value="'.$pssw.'" size="25" /></label>';
	$o .= '</p>';
	$o .= '<p>';
	$o .= '<label for="zsex">'.__('I\'m looking for','rencontre').'<br />';
	$o .= '<select name="zsex">';
	for($v=(isset($rencCustom['sex'])?2:0);$v<(isset($rencCustom['sex'])?count($rencOpt['iam']):2);++$v) $o .= '<option value="'.$v.'" '.($v==$zsex?'selected':'').'>'.$rencOpt['iam'][$v].'</option>';
	$o .= '</select>';
	$o .= '</p><br />';
	echo $o;
	}
function rencFastreg_errors($errors, $sanitized_user_login, $user_email)
	{
	global $wpdb;
	if(empty($_POST['pssw']) || strlen(trim($_POST['pssw']))<6)
		{
		$errors->add('pssw_error', __('<strong>ERROR</strong>: Invalid password (6 characters min).', 'rencontre'));
		}
	$q1 = $wpdb->get_var("SELECT id FROM ".$wpdb->prefix."rencontre_prison WHERE c_mail='".$user_email."' LIMIT 1"); // email in jail ?
	$q2 = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."users U, ".$wpdb->prefix."rencontre_users R 
		WHERE
			U.ID=R.user_id and 
			R.c_ip='".$_SERVER['REMOTE_ADDR']."' and 
			R.i_status=4
		"); // Count same IP in NEW REGISTRANT ONLY (robot registration)
	if($q1 || $q2>10)
		{
		$errors->add('user_email_error', __('Your email address is currently in quarantine. Sorry','rencontre'));
		}
	return $errors;
	}
function rencFastreg($user_id)
	{
	global $wpdb; global $rencOpt; global $rencDiv;
	// 1. Prepare element for connection
	$u = get_user_by('id', $user_id);
//	wp_set_password($user_id,$_POST['pssw']); // changement MdP
	$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_RAND);
	$ii = base64_encode($iv);
	$oo = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, substr(AUTH_KEY,0,16), $u->ID . '|' . $u->user_login . '|z'.$_POST['pssw'].'|' . time(), MCRYPT_MODE_CBC, $iv));
	// 2. Creation in Rencontre
	$wpdb->delete($wpdb->prefix.'rencontre_users', array('user_id'=>$user_id)); // suppression si existe deja
	$wpdb->delete($wpdb->prefix.'rencontre_users_profil', array('user_id'=>$user_id)); // suppression si existe deja
	$wpdb->insert($wpdb->prefix.'rencontre_users', array(
		'user_id'=>$user_id,
		'c_ip'=>($_SERVER['REMOTE_ADDR']?$_SERVER['REMOTE_ADDR']:'127.0.0.1'),
		'c_pays'=>(isset($rencOpt['pays'])?$rencOpt['pays']:'FR'), // default - custom no localisation
		'i_sex'=>98, // code for this case
		'i_zsex'=>strip_tags($_POST['zsex']),
		'c_zsex'=>',',
		'd_session'=>current_time("mysql"),
		'i_photo'=>0,
		'i_status'=>4)); // 4 : fastreg
	$wpdb->insert($wpdb->prefix.'rencontre_users_profil', array('user_id'=>$user_id, 'd_modif'=>current_time("mysql"),'t_titre'=>'', 't_annonce'=>'', 't_profil'=>'[]'));
	if(empty($rencOpt['rol'])) $wpdb->delete($wpdb->prefix.'usermeta', array('user_id'=>$user_id)); // suppression des roles WP
	add_user_meta($user_id, 'rencontre_confirm_email',0);
	// 3. Send confirm email
	rencFastreg_email($u);
	// 4. Access with auto connection
	wp_redirect((empty($rencOpt['home'])?site_url():$rencOpt['home']).'?rencfastreg=1&rencoo='.urlencode($oo).'&rencii='.urlencode($ii)); exit; // exit needed after wp_redirect
	}
function rencFastreg_email($u,$other=0)
	{
	global $rencOpt; global $rencDiv;
	if($other && isset($_COOKIE["rencfastregMail"])) echo __('Confirmation email already sent','rencontre');
	else
		{
		$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_RAND);
		$ii = base64_encode($iv);
		$oo = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, substr(AUTH_KEY,0,16), $u->ID . '|' . $u->user_login . '|confirm|' . time(), MCRYPT_MODE_CBC, $iv));
		$he = '';
		$t = get_bloginfo('name');
		$s = __('Hello','rencontre').'&nbsp;'.$u->user_login.', <br />'.__('You should confirm your email with this link','rencontre').' : <br />';
		$s .= '<a href="'.htmlspecialchars((empty($rencOpt['home'])?site_url():$rencOpt['home']).'?rencfastreg=1&rencoo='.urlencode($oo).'&rencii='.urlencode($ii)).'" target="_blank">'.htmlspecialchars((empty($rencOpt['home'])?site_url():$rencOpt['home']).'?rencfastreg='.$u->ID.'&rencoo='.urlencode($oo).'&rencii='.urlencode($ii)).'</a> <br />';
		$s .= '<br />'.__('Regards,','rencontre').'<br /><br />'.$t;
		if(!has_filter('wp_mail_content_type'))
			{
			$he[] = 'From: '.$t.' <'.$rencDiv['admin_email'].'>';
			$he[] = 'Content-type: text/html; charset=UTF-8';
			$s = '<html><head></head><body>'.$s.'</body></html>';
			}
		@wp_mail($u->user_email, $t.' - '.__('Confirmation email','rencontre'), $s, $he);
		if($other==1)
			{
			echo __('Confirmation email sent','rencontre');
			}
		}
	}
function rencistatus($f,$g)
	{
	// $f : i_status value
	// $g : capability - 0=>blocked , 1=>mail blocked , 2=>fastreg not completed , 3=> ...
	$a = "00000000".decbin($f);
	if(strlen($a)<$g+1 || $g>7) return false;
	else if(substr($a,(-1-$g),1)=='1') return 1;
	else return 0;
	}
function rencistatusSet($f,$g,$h)
	{
	// $f : i_status value
	// $g : capability
	// $h : value to set (0 / 1)
	$a = "00000000".decbin($f);
	$a = substr($a,0,strlen($a)-$g-1) . $h . substr($a,strlen($a)-$g);
	return bindec($a);
	}
//
// Partie ADMIN dans base.php
//
?>
