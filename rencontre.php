<?php
/*
Plugin Name: Rencontre
Author: Jacques Malgrange
Text Domain: rencontre
Domain Path: /lang
Plugin URI: http://www.boiteasite.fr/fiches/site_rencontre_wordpress.html
Description: A free powerful and exhaustive dating plugin with private messaging, webcam chat, search by profile and automatic sending of email. No third party.
Version: 2.1
Author URI: http://www.boiteasite.fr
*/
// __('A free powerful and exhaustive dating plugin with private messaging, webcam chat, search by profile and automatic sending of email. No third party.','rencontre'); // Description
$rencVersion = '2.1';
// **********************************************************************************
// INSTALLATION DU PLUGIN - Creation des tables en BDD
// **********************************************************************************
register_activation_hook ( __FILE__, 'rencontre_activation');
require(dirname(__FILE__).'/inc/rencontre_filter.php');
function rencontre_activation()
	{
	global $wpdb;
	$rencOpt = get_option('rencontre_options');
	if(!$rencOpt)
		{
		$rencOpt = array('facebook'=>'','fblog'=>'','fastreg'=>0,'passw'=>1,'rol'=>1,'rolu'=>0,'home'=>'','logredir'=>0,'pays'=>'FR','limit'=>20,'tchat'=>0,'map'=>0,'hcron'=>3,'mailmois'=>0,'msgdel'=>3,'textmail'=>'','mailsmile'=>0,'mailanniv'=>0,'mailph'=>0,'textanniv'=>'','qmail'=>25,'npa'=>12,'rlibre'=>0,'jlibre'=>3,'prison'=>30,'anniv'=>1,'ligne'=>1,'mailsupp'=>1,'avatar'=>0,'onlyphoto'=>1,'photoz'=>0,'pacamsg'=>0,'pacasig'=>0,'imnb'=>4,'imcrypt'=>0,'imcopyright'=>1,'txtcopyright'=>'','custom'=>'');
		$nu = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."users");
		if($nu<10) unset($rencOpt['rol']);
		update_option('rencontre_options', $rencOpt);
		}
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' ); // pour utiliser dbDelta()
	//
	if(!empty($wpdb->charset)) $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
	if(!empty($wpdb->collate)) $charset_collate .= " COLLATE $wpdb->collate";
	$nom = $wpdb->prefix . 'rencontre_dbip';
	if($wpdb->get_var("SHOW TABLES LIKE '$nom'")!=$nom)
		{
		$sql = "CREATE TABLE ".$nom." (
			`ip_start` bigint unsigned NOT NULL,
			`ip_end` bigint unsigned NOT NULL,
			`country` char(2) NOT NULL,
			PRIMARY KEY (`ip_start`)
			) $charset_collate;";
		dbDelta($sql); // necessite wp-admin/includes/upgrade.php
		}
	$nom = $wpdb->prefix . 'rencontre_profil';
	if($wpdb->get_var("SHOW TABLES LIKE '$nom'")!=$nom)
		{
		$sql = "CREATE TABLE ".$nom." (
			`id` smallint unsigned NOT NULL auto_increment,
			`i_categ` tinyint unsigned NOT NULL,
			`i_label` tinyint unsigned NOT NULL,
			`c_categ` varchar(50) NOT NULL,
			`c_label` varchar(100) NOT NULL,
			`t_valeur` text,
			`i_type` tinyint NOT NULL,
			`i_poids` tinyint NOT NULL,
			`c_lang` varchar(2) NOT NULL,
			`c_genre` varchar(255) DEFAULT 0,
			INDEX (`id`)
			) $charset_collate;";
		dbDelta($sql);
		}
	$nom = $wpdb->prefix . 'rencontre_users';
	if($wpdb->get_var("SHOW TABLES LIKE '$nom'")!=$nom)
		{
		$sql = "CREATE TABLE ".$nom." (
			`user_id` bigint(20) unsigned UNIQUE NOT NULL,
			`c_ip` varchar(50) NOT NULL,
			`c_pays` varchar(50) NOT NULL,
			`c_region` varchar(50) NOT NULL,
			`c_ville` varchar(50) NOT NULL,
			`e_lat` decimal(10,5) NOT NULL,
			`e_lon` decimal(10,5) NOT NULL,
			`i_sex` tinyint NOT NULL,
			`d_naissance` date NOT NULL,
			`i_taille` tinyint unsigned NOT NULL,
			`i_poids` tinyint unsigned NOT NULL,
			`i_zsex` tinyint NOT NULL,
			`c_zsex` varchar(50) NOT NULL,
			`i_zage_min` tinyint unsigned NOT NULL,
			`i_zage_max` tinyint unsigned NOT NULL,
			`i_zrelation` tinyint NOT NULL,
			`c_zrelation` varchar(50) NOT NULL,
			`i_photo` bigint(20) unsigned NOT NULL,
			`d_session` datetime NOT NULL,
			`i_status` tinyint unsigned NOT NULL DEFAULT 0,
			PRIMARY KEY (`user_id`)
			) $charset_collate;";
		dbDelta($sql);
		}
	$nom = $wpdb->prefix . 'rencontre_users_profil';
	if($wpdb->get_var("SHOW TABLES LIKE '$nom'")!=$nom)
		{
		$sql = "CREATE TABLE ".$nom." (
			`user_id` bigint(20) unsigned UNIQUE NOT NULL,
			`d_modif` datetime NULL,
			`t_titre` tinytext,
			`t_annonce` text,
			`t_profil` text,
			`t_action` text,
			`t_signal` text,
			PRIMARY KEY (`user_id`)
			) $charset_collate;";
		dbDelta($sql);
		}
	$nom = $wpdb->prefix . 'rencontre_liste';
	if($wpdb->get_var("SHOW TABLES LIKE '$nom'")!=$nom)
		{
		$sql = "CREATE TABLE ".$nom." (
			`id` smallint unsigned NOT NULL auto_increment,
			`c_liste_categ` varchar(50) NOT NULL,
			`c_liste_valeur` varchar(50) NOT NULL,
			`c_liste_iso` varchar(2) NOT NULL,
			`c_liste_lang` varchar(2) NOT NULL,
			PRIMARY KEY (`id`)
			) $charset_collate;";
		dbDelta($sql);
		}
	$nom = $wpdb->prefix . 'rencontre_msg';
	if($wpdb->get_var("SHOW TABLES LIKE '$nom'")!=$nom)
		{
		$sql = "CREATE TABLE ".$nom." (
			`id` bigint(20) NOT NULL auto_increment,
			`content` text NOT NULL,
			`sender` varchar(60) NOT NULL,
			`recipient` varchar(60) NOT NULL,
			`date` datetime NOT NULL,
			`read` tinyint(1) NOT NULL,
			`deleted` tinyint(1) NOT NULL,
			PRIMARY KEY (`id`)
			) $charset_collate;";
		dbDelta($sql);
		}
	$nom = $wpdb->prefix . 'rencontre_prison';
	if($wpdb->get_var("SHOW TABLES LIKE '$nom'")!=$nom)
		{
		$sql = "CREATE TABLE ".$nom." (
			`id` smallint unsigned NOT NULL auto_increment,
			`d_prison` datetime NOT NULL,
			`c_mail` varchar(100) NOT NULL,
			`c_ip` varchar(50) NOT NULL,
			`i_type` tinyint NOT NULL,
			PRIMARY KEY (`id`)
			) $charset_collate;";
		dbDelta($sql);
		}
	//
	$n = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."rencontre_liste");
	if(!$n && file_exists(dirname(__FILE__).'/inc/rencontre_liste_defaut.txt'))
		{
		$f = file_get_contents(dirname(__FILE__).'/inc/rencontre_liste_defaut.txt');
		global $wpdb;
		$wpdb->query("ALTER TABLE ".$wpdb->prefix."rencontre_liste AUTO_INCREMENT = 1");
		$wpdb->query("INSERT INTO ".$wpdb->prefix."rencontre_liste (c_liste_categ, c_liste_valeur, c_liste_iso, c_liste_lang) VALUES ".$f);
		}
	}
//
// **********************************************************************************
// CLASSE Rencontre
// **********************************************************************************
if(is_admin()) require(dirname(__FILE__).'/inc/base.php');
new Rencontre();
class Rencontre
	{
	function __construct()
		{
		// Variables globale Rencontre
		global $rencOpt; global $rencDiv; global $wpdb; global $rencCustom;
		if(!load_plugin_textdomain('rencontre', false, dirname(plugin_basename( __FILE__ )).'/lang/')) // language
			{
			$a = get_locale();
			$lo = array(
				'da_DK'=>'da_DK',
				'es_AR'=>'es_ES',
				'es_CL'=>'es_ES',
				'es_CO'=>'es_ES',
				'es_ES'=>'es_ES',
				'es_GT'=>'es_ES',
				'es_MX'=>'es_ES',
				'es_PE'=>'es_ES',
				'es_PR'=>'es_ES',
				'es_VE'=>'es_ES',
				'fr_BE'=>'fr_FR',
				'fr_CA'=>'fr_FR',
				'fr_FR'=>'fr_FR',
				'pt_BR'=>'pt_PT',
				'pt_PT'=>'pt_PT',
				'zh_CN'=>'zh_CN',
				'zh_HK'=>'zh_CN',
				'zh_TW'=>'zh_CN');
			if(isset($lo[$a])) load_textdomain('rencontre',WP_PLUGIN_DIR.'/rencontre/lang/rencontre-'.$lo[$a].'.mo');
			}
		$upl = wp_upload_dir();
		$rencDiv['basedir'] = $upl['basedir'];
		$rencDiv['baseurl'] = $upl['baseurl'];
		$rencDiv['blogname'] = get_option('blogname');
		$rencDiv['admin_email'] = get_option('admin_email');
		$rencDiv['siteurl'] = site_url();
		$rencDiv['lang'] = ((defined('WPLANG')&&WPLANG)?WPLANG:get_locale());
		if(!file_exists(dirname(__FILE__).'/inc/patch.php')) $q = $wpdb->get_var("SELECT id FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_lang='".substr($rencDiv['lang'],0,2)."' LIMIT 1"); // class executed before activation function => header error : table not exists
		if(empty($q)) $rencDiv['lang'] = "en_US";
		if(!empty($rencOpt['home']) && strpos($rencOpt['home'],'page_id')!==false) $rencOpt['page_id'] = substr($rencOpt['home'],strpos($rencOpt['home'],'page_id')+8);
		$rencCustom = (isset($rencOpt['custom'])?json_decode($rencOpt['custom'],true):array());
		$rencOpt['for'][0] = __('Serious relationship','rencontre');
		$rencOpt['for'][1] = __('Open relationship','rencontre');
		$rencOpt['for'][2] = __('Friendship','rencontre');
		if(isset($rencCustom['relation']))
			{
			$c = 0;
			while(isset($rencCustom['relationL'.$c]))
				{
				$rencOpt['for'][$c+3] = $rencCustom['relationL'.$c];
				++$c;
				}
			}
		$rencOpt['iam'][0] = __('a man','rencontre');
		$rencOpt['iam'][1] = __('a woman','rencontre');
		if(isset($rencCustom['sex']))
			{
			$c = 0;
			while(isset($rencCustom['sexL'.$c]))
				{
				$rencOpt['iam'][$c+2] = $rencCustom['sexL'.$c];
				++$c;
				}
			}
		add_action('widgets_init', array($this, 'rencwidget')); // WIDGET
		if(is_admin())
			{
			add_action('admin_menu', array($this, 'admin_menu_link')); // Menu admin
			add_action('admin_print_scripts', array($this, 'adminCSS')); // CSS pour le bouton du menu
			if(file_exists(dirname(__FILE__).'/inc/patch.php') && $wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix."rencontre_users' ")==$wpdb->prefix."rencontre_users") include(dirname(__FILE__).'/inc/patch.php'); // VERSIONS PATCH - ONLY ONCE - NOT DURING ACTIVATION
			global $pagenow;
			if('nav-menus.php'===$pagenow) add_action('admin_init','rencMetaMenu'); // Rencontre menu items in admin menu tab - base.php
			}
		}
	//
	function admin_menu_link()
		{
		if(is_admin())
			{
			add_menu_page('Rencontre', 'Rencontre', 'manage_options', basename(__FILE__), array(&$this, 'menu_general'), 'div'); // ajoute un menu Rencontre (et son premier sous-menu)
			add_submenu_page('rencontre.php', __('Rencontre - General','rencontre'), __('General','rencontre'), 'manage_options', 'rencontre.php', array(&$this, 'menu_general')); // repete le premier sous-menu (pour changer le nom)
			add_submenu_page('rencontre.php', __('Rencontre - Members','rencontre'), __('Members','rencontre'), 'manage_options', 'rencmembers', array(&$this, 'menu_membres'));
			add_submenu_page('rencontre.php', __('Rencontre - Jail','rencontre'), __('Jail','rencontre'), 'manage_options', 'rencjail', array(&$this, 'menu_prison'));
			add_submenu_page('rencontre.php', __('Rencontre - Profile','rencontre'), __('Profile','rencontre'), 'manage_options', 'rencprofile', array(&$this, 'menu_profil'));
			add_submenu_page('rencontre.php', __('Rencontre - Countries','rencontre'), __('Country','rencontre'), 'manage_options', 'renccountry', array(&$this, 'menu_pays'));
			add_submenu_page('rencontre.php', __('Rencontre - Custom','rencontre'), __('Custom','rencontre'), 'manage_options', 'renccustom', array(&$this, 'menu_custom'));
			}
		}
	//
	function menu_general() {rencMenuGeneral();} // base.php include if is_admin
	function menu_membres() {rencMenuMembres();}
	function menu_prison() {rencMenuPrison();}
	function menu_profil() {rencMenuProfil();}
	function menu_pays() {rencMenuPays();}
	function menu_custom() {rencMenuCustom();}
	//
	function rencwidget()
		{
		global $rencOpt; global $rencDiv; global $wpdb;
		if(!isset($_SESSION)) session_start();
		if(!empty($rencOpt['rlibre'])) // Reload Unconnected HomePage every...
			{
			$t = time();
			$a = $rencDiv['basedir'].'/portrait/cache/cache_portraits_accueil.html';
			if(file_exists($a) && $t>filemtime($a)+$rencOpt['rlibre']) unlink($a);
			$a = $rencDiv['basedir'].'/portrait/cache/cache_portraits_accueil1.html';
			if(file_exists($a) && $t>filemtime($a)+$rencOpt['rlibre']) unlink($a);
			$a = $rencDiv['basedir'].'/portrait/cache/cache_portraits_accueilgirl.html';
			if(file_exists($a) && $t>filemtime($a)+$rencOpt['rlibre']) unlink($a);
			$a = $rencDiv['basedir'].'/portrait/cache/cache_portraits_accueilmen.html';
			if(file_exists($a) && $t>filemtime($a)+$rencOpt['rlibre']) unlink($a);
			}
		if(empty($rencOpt['home']))
			{
			$a = explode("?",$_SERVER['REQUEST_URI']);
			$rencOpt['home'] = 'http://'.$_SERVER['HTTP_HOST'] . $a[0];
			}
		if(isset($_GET["rencfastreg"]) && isset($_GET["rencoo"]) && isset($_GET["rencii"]) && AUTH_KEY)
			{
			$clair = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, substr(AUTH_KEY,0,16), base64_decode($_GET["rencoo"]), MCRYPT_MODE_CBC, base64_decode($_GET["rencii"]));
			$c = explode('|', $clair);
			if(is_array($c) && count($c)==4)
				{
				if($c[2]!='confirm') wp_set_password(substr($c[2],1),$c[0]);
				wp_clear_auth_cookie();
				wp_set_current_user($c[0], $c[1]);
				wp_set_auth_cookie($c[0]);
				do_action('wp_login', $c[1]);
				if(is_user_logged_in())
					{
					if($c[2]=='confirm')
						{
						$wpdb->delete($wpdb->prefix.'usermeta', array('user_id'=>$c[0], 'meta_key'=>'rencontre_confirm_email'));
						echo "<script language='JavaScript'>document.location.href='".(isset($rencOpt['home'])?$rencOpt['home']:'')."?rencidfm=rencfastreg';</script>";
						}
					else echo "<script language='JavaScript'>document.location.href='".(isset($rencOpt['home'])?$rencOpt['home']:'')."';</script>";
					}
				}
			}
		//
		if(is_admin())
			{
			require(dirname (__FILE__) . '/inc/rencontre_widget.php');
			register_widget("RencontreWidget"); // class
			register_widget("RencontreSidebarWidget"); // class
			}
		else if(is_user_logged_in())
			{
			global $current_user; global $RencMid; global $rencCustom;
			wp_get_current_user();
			$rencMid['id'] = $current_user->ID;
			$rencMid['login'] = $current_user->user_login;
			if(!isset($_SESSION['rencontre']) || !$_SESSION['rencontre']) $_SESSION['rencontre']='mini,accueil,menu';
			if(isset($_GET["rencidfm"]))
				{ // acces a la fiche d un membre depuis un lien email
				if(substr($_GET["rencidfm"],0,1)=='c') $_SESSION['rencontre']='card,menu,demcont';
				else if(substr($_GET["rencidfm"],0,1)=='m') $_SESSION['rencontre']='write,accueil,menu';
				else if(substr($_GET["rencidfm"],0,1)=='p') $_SESSION['rencontre']='card,menu';
				else if(substr($_GET["rencidfm"],0,1)=='s') $_SESSION['rencontre']='card,menu,sourire';
				else if(substr($_GET["rencidfm"],0,1)=='r') $_SESSION['rencontre']='msg,accueil,menu';
				}
			$ip = $wpdb->get_var("SELECT c_ip FROM ".$wpdb->prefix."rencontre_users WHERE user_id='".$current_user->ID."' LIMIT 1");
			$spot = (!empty($_POST['renc'])?$_POST['renc']:(!empty($_GET['renc'])?$_GET['renc']:''));
			if(!$ip && (!isset($_POST['nouveau']) || $_POST['nouveau']!='OK'))
				{
				if(!isset($_POST['nouveau']))
					{
					$q = $wpdb->get_row("SELECT
							c_ville,
							c_zsex
						FROM
							".$wpdb->prefix."rencontre_users
						WHERE
							user_id='".$rencMid['id']."'
						LIMIT 1
						");
					if($q && $q->c_zsex) $_SESSION['rencontre']='nouveau3';
					else if(($q && $q->c_ville) || (isset($rencCustom['place']) && $q)) $_SESSION['rencontre']='nouveau2';
					else if($q) $_SESSION['rencontre']='nouveau1';
					else $_SESSION['rencontre']='nouveau';
					}
				else
					{
					if($_POST['nouveau']=='1' && isset($rencCustom['place'])) $_SESSION['rencontre']='nouveau2';
					else if($_POST['nouveau']=='1') $_SESSION['rencontre']='nouveau1';
					else if($_POST['nouveau']=='2') $_SESSION['rencontre']='nouveau2';
					else if($_POST['nouveau']=='3') $_SESSION['rencontre']='nouveau3';
					}
				}
			else if(empty($spot) && !isset($_GET["rencidfm"])) $_SESSION['rencontre']='mini,accueil,menu';
			else if($spot=='paswd') $_SESSION['rencontre']='mini,accueil,menu,paswd';
			else if($spot=='fin')
				{
				f_userSupp($current_user->ID,$current_user->user_login,0);
				if(!empty($rencOpt['mailsupp']))
					{
					$q = $wpdb->get_var("SELECT user_email FROM ".$wpdb->prefix."users WHERE ID='".$current_user->ID."' LIMIT 1");
					$objet  = wp_specialchars_decode($rencDiv['blogname'], ENT_QUOTES).' - '.__('Account deletion','rencontre');
					$message  = __('Your account has been deleted','rencontre');
					@wp_mail($q, $objet, $message);
					}
				}
			else if($spot=='card') $_SESSION['rencontre']='card,menu';
			else if($spot=='sourire') $_SESSION['rencontre']='card,menu,sourire';
			else if($spot=='demcont') $_SESSION['rencontre']='card,menu,demcont';
			else if($spot=='signale') $_SESSION['rencontre']='card,menu,signale';
			else if($spot=='bloque') $_SESSION['rencontre']='card,menu,bloque';
			else if($spot=='edit') $_SESSION['rencontre']='edit,menu';
			else if($spot=='qsearch') $_SESSION['rencontre']='qsearch,accueil,menu';
			else if($spot=='gsearch') $_SESSION['rencontre']='gsearch,accueil,menu';
			else if($spot=='liste') $_SESSION['rencontre']='gsearch,liste,accueil,menu';
			else if($spot=='msg') $_SESSION['rencontre']='msg,accueil,menu';
			else if($spot=='write') $_SESSION['rencontre']='write,accueil,menu';
			else if($spot=='account') $_SESSION['rencontre']='account,accueil,menu';
			else if($spot=='c1') $_SESSION['rencontre']='custom1,accueil,menu';
			else if($spot=='c2') $_SESSION['rencontre']='custom2,accueil,menu';
			$ho = false; if($_SESSION['rencontre']!='nouveau' && has_filter('rencGateP', 'f_rencGateP')) $ho = apply_filters('rencGateP', $ho);
			if($ho) $_SESSION['rencontre'] = 'gate';
			require(dirname (__FILE__) . '/inc/rencontre_widget.php');
			if(!empty($_POST['nouveau']) && !empty($_POST['pass1'])) RencontreWidget::f_changePass($current_user->ID,$_POST['pass1']);
			if(!empty($rencOpt['fastreg'])) // 0 or 1
				{
				$rencOpt['fastreg'] = 1; // string to int
				$q = $wpdb->get_row("SELECT
						R.i_sex,
						R.i_status,
						IFNULL(M.umeta_id,0) AS umeta_id
					FROM
						".$wpdb->prefix."rencontre_users R
					LEFT JOIN
						".$wpdb->prefix."usermeta M
					ON
						R.user_id=M.user_id and
						M.meta_key='rencontre_confirm_email'
					WHERE
						R.user_id=".$current_user->ID."
					LIMIT 1
					");
				if($q->umeta_id) $rencOpt['fastreg'] += 2; // Email not confirmed (password not changed) : +2
				if($q->i_sex=="98") $rencOpt['fastreg'] += 1; // 98 : Fastreg account not completed : +1
				if($rencOpt['fastreg']<2 && rencistatus($q->i_status,2))
					{
					$st = rencistatusSet($q->i_status,2,0);
					$wpdb->update($wpdb->prefix.'rencontre_users', array('i_status'=>$st), array('user_id'=>$current_user->ID)); // 4 : fastreg
					}
				}
			register_widget("RencontreWidget"); // class
			register_widget("RencontreSidebarWidget"); // class
			}
		// not connected
		else if(isset($_GET["rencoo"]) && isset($_GET["rencii"]) && AUTH_KEY)
			{ // autoconnect
			$clair = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, substr(AUTH_KEY,0,16), base64_decode($_GET["rencoo"]), MCRYPT_MODE_CBC, base64_decode($_GET["rencii"]));
			$c = explode('|', $clair);
			if(is_array($c) && count($c)==3 && $c[2]>time()-3024000) // Validity : 35 days
				{
				wp_set_current_user($c[0], $c[1]);
				wp_set_auth_cookie($c[0]);
				do_action('wp_login', $c[1]);
				if(is_user_logged_in())
					{
					$d = '';
					if(isset($_GET["rencidfm"]) && $_GET["rencidfm"]) $d .= 'rencidfm='.$_GET["rencidfm"];
					echo "<script language='JavaScript'>document.location.href='".$rencOpt['home'].($d?'?'.$d:'')."';</script>";
					}
				}
			}
		}
	//
	function adminCSS()
		{
		echo '<style type="text/css">
			#toplevel_page_rencontre .wp-menu-image {background:transparent url('.plugin_dir_url(__FILE__).'/images/menu.png) no-repeat scroll 3px -30px;}
			#toplevel_page_rencontre:hover .wp-menu-image {background-position:3px 3px;}
			</style>';
		}
	//
	static function f_age($naiss=0) // transforme une date (TIME) en age
		{
		if($naiss==0) return "-";
		list($annee, $mois, $jour) = explode('-', $naiss);
		$today['mois'] = date('n');
		$today['jour'] = date('j');
		$today['annee'] = date('Y');
		$age = $today['annee'] - $annee;
		if($today['mois'] <= $mois) {if($mois == $today['mois']) {if($jour > $today['jour'])$age--;}else$age--;}
		return $age;
		}
	//
	static function f_ficheLibre($a=array(),$ret=0) // Creation du fichier HTML de presentation des membres en libre acces pour la page d accueil
		{
		global $rencDiv;
		$atts = shortcode_atts(array('gen'=>''),$a);
		$out = '';
		if(!wp_style_is('rencontre'))
			{
			if(file_exists(get_stylesheet_directory().'/templates/rencontre.css')) $out .= "\r\n".'<link rel="stylesheet" href="'.get_stylesheet_directory_uri().'/templates/rencontre.css" />'."\r\n";
			else $out .= "\r\n".'<link rel="stylesheet" href="'.plugins_url('rencontre/css/rencontre.css').'" />'."\r\n";
			}
		if(!file_exists($rencDiv['basedir'].'/portrait/cache/cache_portraits_accueil'.($atts['gen']?$atts['gen']:'').'.html'))
			{
			$ho = false; if(has_filter('rencAds3P', 'f_rencAds3P')) $ho = apply_filters('rencAds3P', $ho);
			if($ho) $out .= $ho;
		//	$out .= '<script type="text/javascript" src="'.plugins_url('rencontre/js/rencontre-libre.js').'"></script>'."\r\n"; // Zoom automatique sur chaque personne
			$out .= '<div id="widgRenc" class="widgRenc ficheLibre">'."\r\n";
			global $wpdb; global $rencOpt; global $rencCustom;
			if(!is_dir($rencDiv['basedir'].'/portrait/libre/')) mkdir($rencDiv['basedir'].'/portrait/libre/');
			if(!isset($rencCustom['libreFlag']) || !$rencCustom['libreFlag'])
				{
				$q = $wpdb->get_results("SELECT
						c_liste_categ,
						c_liste_valeur,
						c_liste_iso
					FROM
						".$wpdb->prefix."rencontre_liste
					WHERE
						c_liste_categ='d'
						or
						(c_liste_categ='p' and c_liste_lang='".substr($rencDiv['lang'],0,2)."')
					");
				$rencDrap=''; $rencDrapNom='';
				foreach($q as $r)
					{
					if($r->c_liste_categ=='d') $rencDrap[$r->c_liste_iso] = $r->c_liste_valeur;
					else if($r->c_liste_categ=='p')$rencDrapNom[$r->c_liste_iso] = $r->c_liste_valeur;
					}
				}
			if($atts['gen']=='mix') // repartition homogene hommes / femmes
				{
				$qh = $wpdb->get_results("SELECT
						U.ID,
						U.display_name,
						U.user_registered,
						R.i_sex,
						R.i_zsex,
						R.c_pays,
						R.c_ville,
						R.d_naissance,
						R.i_photo,
						P.t_titre,
						P.t_annonce
					FROM 
						".$wpdb->prefix."users U,
						".$wpdb->prefix."rencontre_users R,
						".$wpdb->prefix."rencontre_users_profil P 
					WHERE 
						R.i_status=0 
						and R.i_photo!=0 
						and R.i_sex=0 
						and R.user_id=P.user_id 
						and R.user_id=U.ID 
						and TO_DAYS(NOW())-TO_DAYS(U.user_registered)>=".(isset($rencOpt['jlibre'])?$rencOpt['jlibre']:0)." 
						and CHAR_LENGTH(P.t_titre)>4 
						and CHAR_LENGTH(P.t_annonce)>30
					ORDER BY U.user_registered DESC
					LIMIT ".(isset($rencOpt['npa'])?$rencOpt['npa']:10));
				$qf = $wpdb->get_results("SELECT
						U.ID,
						U.display_name,
						U.user_registered,
						R.i_sex,
						R.i_zsex,
						R.c_pays,
						R.c_ville,
						R.d_naissance,
						R.i_photo,
						P.t_titre,
						P.t_annonce
					FROM 
						".$wpdb->prefix."users U,
						".$wpdb->prefix."rencontre_users R,
						".$wpdb->prefix."rencontre_users_profil P 
					WHERE 
						R.i_status=0 
						and R.i_photo!=0 
						and R.i_sex=1 
						and R.user_id=P.user_id 
						and R.user_id=U.ID 
						and TO_DAYS(NOW())-TO_DAYS(U.user_registered)>=".(isset($rencOpt['jlibre'])?$rencOpt['jlibre']:0)." 
						and CHAR_LENGTH(P.t_titre)>4 
						and CHAR_LENGTH(P.t_annonce)>30
					ORDER BY U.user_registered DESC
					LIMIT ".(isset($rencOpt['npa'])?$rencOpt['npa']:10));
				reset($qh); reset($qf); $ch=0; $cf=0; $q=array(); $c=0;
				do
					{
					if(mt_rand(0,1) && $cf-$ch<5) // femme
						{
						if($cf==0 && $qf) {$q[]=current($qf); ++$cf; ++$c;}
						else if(next($qf)!==false) {$q[]=current($qf); ++$cf; ++$c;}
						else $ch=-10; // Fin
						}
					else if($ch-$cf<5) // homme
						{
						if($ch==0 && $qh) {$q[]=current($qh); ++$ch; ++$c;}
						else if(next($qh)!==false) {$q[]=current($qh); ++$ch; ++$c;}
						else $cf=-10; // Fin
						}
					}while(($ch+$cf)>-15 && $c<(isset($rencOpt['npa'])?$rencOpt['npa']:10)); // false = stop
				}
			else $q = $wpdb->get_results("SELECT
						U.ID,
						U.display_name,
						U.user_registered,
						R.i_sex,
						R.i_zsex,
						R.c_pays,
						R.c_ville,
						R.d_naissance,
						R.i_photo,
						P.t_titre,
						P.t_annonce
					FROM
						".$wpdb->prefix."users U,
						".$wpdb->prefix."rencontre_users R,
						".$wpdb->prefix."rencontre_users_profil P 
					WHERE 
						R.i_status=0 
						and R.i_photo!=0 
						and R.user_id=P.user_id 
						and R.user_id=U.ID 
						and TO_DAYS(NOW())-TO_DAYS(U.user_registered)>=".(isset($rencOpt['jlibre'])?$rencOpt['jlibre']:0)." 
						and CHAR_LENGTH(P.t_titre)>4 
						and CHAR_LENGTH(P.t_annonce)>30
						".(($atts['gen']==='girl')?"and R.i_sex=1":"")."
						".(($atts['gen']==='men')?"and R.i_sex=0":"")."
					ORDER BY U.user_registered DESC
					LIMIT ".(isset($rencOpt['npa'])?$rencOpt['npa']:10));
			$c = 0;
			if($q) foreach($q as $u)
				{ 
				$ad = substr(stripslashes($u->t_annonce),0,180);
				preg_match('`\w(?:[-_.]?\w)*@\w(?:[-_.]?\w)*\.(?:[a-z]{2,4})`', $ad, $m);
				$m[0] = (isset($m[0])?$m[0]:'');
				$ad = str_replace(array($m[0]), array(''), $ad);
				$ad = str_replace(', ', ',', $ad);
				$ad = str_replace(',', ', ', $ad);
				$ad = strtr($ad, "0123456789#(){[]}", ".................");
				$u->annonce = mb_substr($ad,0,150,'UTF-8').'...';
				$ca = stripslashes($u->t_titre);
				preg_match('`\w(?:[-_.]?\w)*@\w(?:[-_.]?\w)*\.(?:[a-z]{2,4})`', $ca, $m);
				$m[0] = (isset($m[0])?$m[0]:'');
				$ca = str_replace(array($m[0]), array(''), $ca);
				$ca = str_replace(', ', ',', $ca);
				$ca = str_replace(',', ', ', $ca);
				$u->title = strtr($ca, "0123456789#(){[]}", ".................");
				$u->miniPhoto = $rencDiv['baseurl'].'/portrait/libre/'.($u->ID*10).'-mini.jpg';
				$u->librePhoto = $rencDiv['baseurl'].'/portrait/libre/'.($u->ID*10).'-libre.jpg';
				$u->libreID = $c;
				$u->genre='girl';
				if($u->i_sex==0 && $u->i_zsex==1) $u->genre='men';
				else if($u->i_sex==1 && $u->i_zsex==1) $u->genre='gaygirl';
				else if($u->i_sex==0 && $u->i_zsex==0) $u->genre='gaymen';
				if(!file_exists($rencDiv['basedir'].'/portrait/libre/'.($u->ID*10).'-libre.jpg')) @copy($rencDiv['basedir'].'/portrait/'.floor(($u->ID)/1000).'/'.self::f_img((($u->ID)*10).'-libre',2).'.jpg', $rencDiv['basedir'].'/portrait/libre/'.($u->ID*10).'-libre.jpg');
				if(!isset($rencCustom['librePhoto']))
					{
					if(!file_exists($rencDiv['basedir'].'/portrait/libre/'.($u->ID*10).'-mini.jpg')) @copy($rencDiv['basedir'].'/portrait/'.floor(($u->ID)/1000).'/'.self::f_img((($u->ID)*10).'-mini',2).'.jpg', $rencDiv['basedir'].'/portrait/libre/'.($u->ID*10).'-mini.jpg');
					if($u->c_pays!="" && !isset($rencCustom['country']) && !isset($rencCustom['place']) && (!isset($rencCustom['libreFlag']) || !$rencCustom['libreFlag']))
						{
						$pays = strtr(utf8_decode($u->c_pays), 'ÁÀÂÄÃÅÇÉÈÊËÍÏÎÌÑÓÒÔÖÕÚÙÛÜÝ', 'AAAAAACEEEEEIIIINOOOOOUUUUY');
						$pays = strtr($pays, 'áàâäãåçéèêëíìîïñóòôöõúùûüýÿ ', 'aaaaaaceeeeiiiinooooouuuuyy_');
						$pays = str_replace("'", "", $pays);
						$cpays = str_replace("'", "&#39;", $u->c_pays);
						}
					}
				$onClick = array(
					"zoomIn"=>"f_tete_zoom(this,'".$rencDiv['baseurl']."/portrait/libre/".($u->ID*10)."-libre.jpg');",
					"zoomOut"=>"f_tete_normal(this,'".$rencDiv['baseurl']."/portrait/libre/".($u->ID*10)."-mini.jpg');"
					);
				// ****** TEMPLATE ********
				ob_start();
				if(file_exists(get_stylesheet_directory().'/templates/rencontre_libre_portrait.php')) include(get_stylesheet_directory().'/templates/rencontre_libre_portrait.php');
				else include(dirname( __FILE__ ).'/templates/rencontre_libre_portrait.php');
				$out .= ob_get_clean();
				// ************************
				++$c;
				}
			$out .= "\r\n\t".'<div class="clear">&nbsp;</div>'."\r\n";
			$ho = false; if(has_filter('rencAds4P', 'f_rencAds4P')) $ho = apply_filters('rencAds4P', $ho);
			if($ho) $out .= $ho;
			$out .= '</div><!-- #widgRenc -->'."\r\n";
			file_put_contents($rencDiv['basedir'].'/portrait/cache/cache_portraits_accueil'.$atts['gen'].'.html', $out);
			if(!$ret) echo $out;
			else return $out; // SHORTCODE
			}
		else if($ret) // SHORTCODE
			{
			$out .= file_get_contents($rencDiv['basedir'].'/portrait/cache/cache_portraits_accueil'.$atts['gen'].'.html');
			return $out; 
			}
		else
			{
			if(!wp_style_is('rencontre'))
				{
				if(file_exists(get_stylesheet_directory().'/templates/rencontre.css')) echo '<link rel="stylesheet" href="'.get_stylesheet_directory_uri().'/templates/rencontre.css" />';
				else echo '<link rel="stylesheet" href="'.plugins_url('rencontre/css/rencontre.css').'" />';
				}
			include($rencDiv['basedir'].'/portrait/cache/cache_portraits_accueil'.$atts['gen'].'.html');
			}
		}
	//
	static function f_rencontreSearch($ret=0,$a=array()) // SHORTCODE [rencontre_search nb=6]
		{
		global $wpdb; global $rencOpt; global $rencDiv; global $rencCustom;
		$atts = shortcode_atts(array('nb'=>6),$a);
		$out = '';
		if(!wp_style_is('rencontre'))
			{
			if(file_exists(get_stylesheet_directory().'/templates/rencontre.css')) $out .= "\r\n".'<link rel="stylesheet" href="'.get_stylesheet_directory_uri().'/templates/rencontre.css" />'."\r\n";
			else $out .= "\r\n".'<link rel="stylesheet" href="'.plugins_url('rencontre/css/rencontre.css').'" />'."\r\n";
			}
		$onClick = array(
			"zagemin"=>"f_min(this.options[this.selectedIndex].value,'rencSearch','zageMin','zageMax');",
			"zagemax"=>"f_max(this.options[this.selectedIndex].value,'rencSearch','zageMin','zageMax');"
			);
		$out = "\r\n".'<script>';
		$out .= 'function f_min(f,x,y,z){var c=0,d=document.forms[x][y],e=document.forms[x][z];f=parseInt(f);for(v=0;v<e.length;v++){if(parseInt(d.options[v].value)==f)c=v;if(parseInt(e.options[v].value)<=f)e.options[v].disabled=true;else e.options[v].disabled=false;}if(f>parseInt(e.options[e.selectedIndex].value))e.selectedIndex=c;};';
		$out .= 'function f_max(f,x,y,z){var c=0,d=document.forms[x][z],e=document.forms[x][y];f=parseInt(f);for(v=0;v<e.length;v++){if(parseInt(d.options[v].value)==f)c=v;if(parseInt(e.options[v].value)>=f)e.options[v].disabled=true;else e.options[v].disabled=false;}if(f<parseInt(e.options[e.selectedIndex].value))e.selectedIndex=c;};';
		$out .= '</script>'."\r\n";
		// ****** TEMPLATE ********
		ob_start();
		if(file_exists(get_stylesheet_directory().'/templates/rencontre_libre_search.php')) include(get_stylesheet_directory().'/templates/rencontre_libre_search.php');
		else include(dirname( __FILE__ ).'/templates/rencontre_libre_search.php');
		$out .= ob_get_clean();
		// ************************
		// RESULT
		if(isset($_GET['renc']) && $_GET['renc']=='searchLibre')
			{
			$q = $wpdb->get_results("SELECT
					c_liste_categ,
					c_liste_valeur,
					c_liste_iso
				FROM
					".$wpdb->prefix."rencontre_liste
				WHERE
					c_liste_categ='d'
					or
					(c_liste_categ='p' and c_liste_lang='".substr($rencDiv['lang'],0,2)."')
				");
			$rencDrap=''; $rencDrapNom='';
			foreach($q as $r)
				{
				if($r->c_liste_categ=='d') $rencDrap[$r->c_liste_iso] = $r->c_liste_valeur;
				else if($r->c_liste_categ=='p')$rencDrapNom[$r->c_liste_iso] = $r->c_liste_valeur;
				}
			$out .= '<div id="rencResultLibre" class="rencBox rencResultLibre">';
			$ses =date("Y-m-d H:i:s",mktime(0, 0, 0, date("m"), date("d"), date("Y"))-2592000); // 30 days
			$s = "SELECT
					U.ID,
					U.display_name,
					R.i_sex,
					R.c_pays,
					R.c_ville,
					R.d_naissance,
					R.i_photo,
					P.t_titre
				FROM 
					".$wpdb->prefix."users U,
					".$wpdb->prefix."rencontre_users R,
					".$wpdb->prefix."rencontre_users_profil P 
				WHERE 
					R.i_status=0 
					and R.user_id=U.ID 
					and R.user_id=P.user_id 
					and R.i_photo!=0 
					and R.i_sex=".$_GET['zsex']."
					and CHAR_LENGTH(P.t_titre)>4 
					and CHAR_LENGTH(P.t_annonce)>30
					and R.d_session>'".$ses."'";
			if(isset($_GET['zageMin']) && $_GET['zageMin']>18)
				{
				$zmin=date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"), date("Y")-$_GET['zageMin']));
				$s.=" and R.d_naissance<'".$zmin."'";
				}
			if(isset($_GET['zageMax']) && $_GET['zageMax'] && $_GET['zageMax']<99)
				{
				$zmax=date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"), date("Y")-$_GET['zageMax']));
				$s.=" and R.d_naissance>'".$zmax."'";
				}
			$s .= "ORDER BY CHAR_LENGTH(P.t_action) DESC,U.ID DESC LIMIT ".$atts['nb'];			
			$q = $wpdb->get_results($s);
			$c = 0;
			foreach($q as $u)
				{
				$b = stripslashes($u->t_titre);
				preg_match('`\w(?:[-_.]?\w)*@\w(?:[-_.]?\w)*\.(?:[a-z]{2,4})`', $b, $m);
				$m[0] = (isset($m[0])?$m[0]:'');
				$b = str_replace(array($m[0]), array(''), $b);
				$b = str_replace(', ', ',', $b); $b = str_replace(',', ', ', $b);
				$u->title = strtr($b, "0123456789#(){[]}", ".................");
				$u->miniPhoto = $rencDiv['baseurl'].'/portrait/libre/'.($u->ID*10).'-mini.jpg';
				if(!file_exists($rencDiv['basedir'].'/portrait/libre/'.($u->ID*10).'-mini.jpg')) @copy($rencDiv['basedir'].'/portrait/'.floor(($u->ID)/1000).'/'.self::f_img((($u->ID)*10).'-mini',2).'.jpg', $rencDiv['basedir'].'/portrait/libre/'.($u->ID*10).'-mini.jpg');
				if($u->c_pays!="" && !isset($rencCustom['country']) && !isset($rencCustom['place']) && (!isset($rencCustom['libreFlag']) || !$rencCustom['libreFlag']))
					{
					$pays = strtr(utf8_decode($u->c_pays), 'ÁÀÂÄÃÅÇÉÈÊËÍÏÎÌÑÓÒÔÖÕÚÙÛÜÝ', 'AAAAAACEEEEEIIIINOOOOOUUUUY');
					$pays = strtr($pays, 'áàâäãåçéèêëíìîïñóòôöõúùûüýÿ ', 'aaaaaaceeeeiiiinooooouuuuyy_');
					$pays = str_replace("'", "", $pays);
					$cpays = str_replace("'", "&#39;", $u->c_pays);
					}
				$onClick = array(
					"zoomIn"=>"f_tete_zoom(this,'".$rencDiv['baseurl']."/portrait/libre/".($u->ID*10)."-libre.jpg');",
					"zoomOut"=>"f_tete_normal(this,'".$rencDiv['baseurl']."/portrait/libre/".($u->ID*10)."-mini.jpg');"
					);
				// ****** TEMPLATE ********
				ob_start();
				if(file_exists(get_stylesheet_directory().'/templates/rencontre_libre_search_portrait.php')) include(get_stylesheet_directory().'/templates/rencontre_libre_search_portrait.php');
				else include(dirname( __FILE__ ).'/templates/rencontre_libre_search_portrait.php');
				$out .= ob_get_clean();
				// ************************
				++$c;
				}
			$out .= '<div style="clear:both;"></div>'."\r\n";
			$out .= '</div><!-- .rencResultLibre -->'."\r\n";
			}
		if(!$ret) echo $out;
		else return $out; // SHORTCODE
		}
	//
	static function f_nbMembre($a=array()) // Nombre de membres inscrits sur le site
		{
		global $wpdb;
		$atts = shortcode_atts(array('gen'=>'','ph'=>0),$a);
		$nm = $wpdb->get_var("
			SELECT
				COUNT(*)
			FROM
				".$wpdb->prefix."rencontre_users
			WHERE
				".($atts['gen']=='girl'?"i_sex=1":"")."
				".($atts['gen']=='men'?"i_sex=0":"")."
				".($atts['gen']!='girl'&&$atts['gen']!='men'?"i_sex!=98":"")."
				".($atts['ph']==1?" and i_photo!=0":"")
			);
		return $nm;
		}
	//
	static function f_login($fb=false,$ret=false) // SHORTCODE [rencontre_login]
		{
		global $rencOpt; global $rencDiv; global $rencCustom;
		$o = '<div id="log">'."\r\n";
		if($fb=='fb') $o .= Rencontre::f_loginFB(1);
		$o .= wp_loginout(esc_url(home_url('?page_id='.(isset($rencOpt['page_id'])?$rencOpt['page_id']:''))),false)."\r\n";
		if(!is_user_logged_in())
			{
			if(empty($rencCustom['reglink']) || !empty($rencOpt['fastreg'])) $o .= '<a href="'.$rencDiv['siteurl'].'/wp-login.php?action=register">'.__('Register').'</a>'."\r\n";
			else $o .= '<a href="'.$rencCustom['reglink'].'">'.__('Register').'</a>'."\r\n";
			}
		$o .= '</div><!-- #log -->'."\r\n";
		if(!$ret) echo $o;
		else return $o; // SHORTCODE
		}
	//
	static function f_loginFB($ret=false) // connexion via Facebook
		{
		if(!is_user_logged_in())
			{
			global $rencOpt; global $rencDiv;
			if(isset($rencOpt['fblog']) && strlen($rencOpt['fblog'])>2)
				{
				$o = '<form action="" name="reload"></form>'."\r\n";
				$o .= '<script>'."\r\n";
				$o .= 'function checkLoginState(){FB.getLoginStatus(function(r){logfb(r);});};'."\r\n";
				$o .= 'function logfb(r){if(r.status===\'connected\'){FB.api(\'/me?fields=email,first_name,id\',function(r){jQuery(document).ready(function(){jQuery.post(\''.admin_url('admin-ajax.php').'\',{\'action\':\'fbok\',\'fb\':r},function(re){document.forms[\'reload\'].submit();});});});}};'."\r\n";
				$o .= 'window.fbAsyncInit=function(){FB.init({appId:\''.$rencOpt['fblog'].'\',cookie:true,xfbml:true,version:\'v2.7\'});};'."\r\n";
				$o .= '(function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(d.getElementById(id))return;js=d.createElement(s);js.id=id;js.src="http://connect.facebook.net/'.get_locale().'/sdk.js";fjs.parentNode.insertBefore(js,fjs);}(document,\'script\',\'facebook-jssdk\'));'."\r\n";
				$o .= '</script>'."\r\n";
				$o .= '<fb:login-button scope="public_profile,email" onlogin="checkLoginState();" data-auto-logout-link="true"></fb:login-button>'."\r\n";
				if(!$ret) echo $o;
				else return $o; // SHORTCODE
				}
			}
		}
	//
	static function f_img($img,$f=0)
		{ // $f = 1 : ENCODE or DECODE ALL in base - $f = 2 : No Filter
		global $rencOpt;
		$ho = false;
		if(!$f && has_filter('rencImgP', 'f_rencImgP')) $ho = apply_filters('rencImgP',$img);
		if($ho) return $ho;
		//
		if($f==1 || !empty($rencOpt['imcode']))
			{
			$t = md5($img);
			return substr($t,4,17) . 'z' . substr($t,25,6); // 'z' is used to know if it's encoded or not
			}
		else return $img;
		}
	static function f_rencontreImgReg($atts=array())
		{
		// Shortcode [rencontre_imgreg title= selector= left= top=]
		// Registration Form on image (home page)
		global $rencCustom; global $rencOpt;
		$a = shortcode_atts(array(
			'title' => __('Register'),
			'selector' => '', // jQuery selector of the image ex: '.site-header .wp-custom-header img'
			'left' => '20',
			'top' => '15'
			),$atts);
			
		$o = '<div id="imgreg" class="imgreg" style="position:absolute;"><h2>'.$a['title'].'</h2>';
		$o .= '<form name="registerform" id="registerform" action="'.esc_url(site_url('wp-login.php?action=register','login_post')).'" method="post" novalidate="novalidate">';
		$o .= '<p><label for="user_login">'.__('Username').'<br /><input type="text" name="user_login" id="user_login" class="input" value="" size="20" /></label></p>';
		$o .= '<p><label for="user_email">'.__('Email').'<br /><input type="email" name="user_email" id="user_email" class="input" value="" size="25" /></label></p>';
		if(!empty($rencOpt['fastreg']))
			{
			$o .= '<p><label for="pssw">'.__('Password').'<br /><input type="password" name="pssw" id="pssw" class="input" value="" size="25" /></label></p>';
			$o .= '<p><label for="zsex">'.__('I\'m looking for','rencontre').'<br /><select name="zsex">';
			for($v=(isset($rencCustom['sex'])?2:0);$v<(isset($rencCustom['sex'])?count($rencOpt['iam']):2);++$v) $o .= '<option value="'.$v.'">'.$rencOpt['iam'][$v].'</option>';
			$o .= '</select></p>';
			}
		else $o .= '<p id="reg_passmail">'.__('Registration confirmation will be emailed to you.').'</p>';
		$o .= '<input type="hidden" name="redirect_to" value="" />';
		$o .= '<p class="submit"><input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="'.esc_attr__('Register').'" /></p>';
		$o .= '</form>';
		$o .= '</div>';
		$o .= "\r\n".'<script>jQuery(document).ready(function(){var p=jQuery("'.$a['selector'].'").parent(),h=parseInt((jQuery("'.$a['selector'].'").height())*0.'.$a['top'].'),w=parseInt((jQuery("'.$a['selector'].'").width())*0.'.$a['left'].');jQuery("#imgreg").appendTo(p);document.getElementById("imgreg").style.top=h+"px";document.getElementById("imgreg").style.left=w+"px";jQuery(p).css("position","relative");});</script>';
		return $o;
		}
	//
	} // END CLASS
// *****************************************************************************************
?>
