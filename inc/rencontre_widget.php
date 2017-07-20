<?php
//
class RencontreWidget extends WP_widget
	{
 	function __construct()
		{
		parent::__construct('rencontre-widget','Rencontre',array('description'=>__('Widget to integrate the dating website', 'rencontre'),));
		}
	//
	function widget($arguments, $data) // Partie Site
		{
		if(current_user_can("administrator")) return;
		global $current_user; global $wpdb; global $rencBlock; global $pacam;
		global $rencDrap; global $rencDrapNom; global $rencOpt; global $rencDiv; global $rencCustom;
		if(!wp_style_is('rencontre')) { ?>
		
		<link rel="stylesheet" href="<?php echo plugins_url('rencontre/css/rencontre.css'); ?>" />
		<?php }
		if(!wp_script_is('rencontre')) wp_enqueue_script('rencontre');
		//
		$rencidfm = (isset($_GET["rencidfm"])?$_GET["rencidfm"]:''); // lien direct vers la fiche d un membre depuis un mail
		$mid = $current_user->ID; // Mon id
		$istatus = $wpdb->get_var("SELECT i_status FROM ".$wpdb->prefix."rencontre_users WHERE user_id=".$mid." LIMIT 1"); // only needed above & in para 8 (msg)
		$rencBlock = rencistatus($istatus,0); // (($istatus==1||$istatus==3)?1:0); // blocked
		$r = $rencDiv['basedir'].'/portrait';if(!is_dir($r)) mkdir($r);
		if(!isset($rencDrap) || !$rencDrap)
			{
			$q = $wpdb->get_results("SELECT
					c_liste_categ,
					c_liste_valeur,
					c_liste_iso
				FROM 
					".$wpdb->prefix."rencontre_liste 
				WHERE 
					c_liste_categ='d' or
					(c_liste_categ='p' and c_liste_lang='".substr($rencDiv['lang'],0,2)."') ");
			$rencDrap=''; $rencDrapNom='';
			foreach($q as $r)
				{
				if($r->c_liste_categ=='d') $rencDrap[$r->c_liste_iso] = $r->c_liste_valeur;
				else if($r->c_liste_categ=='p')$rencDrapNom[$r->c_liste_iso] = $r->c_liste_valeur;
				}
			}
		if(isset($_POST['nouveau']) && isset($_POST['a1']) && $_POST['a1']==$mid)
			{
			if($_POST['nouveau']=='update') RencontreWidget::f_updateMember($mid);
			else RencontreWidget::f_registerMember($mid,strip_tags($_POST['nouveau']));
			}
		// *****************************************************************************************************************
		// 0. Partie menu
		require(dirname (__FILE__) . '/../lang/rencontre-js-lang.php');
		$lang += array('mid'=>$current_user->ID,'ajaxchat'=>plugins_url('rencontre/inc/rencontre_tchat.php'),'wpajax'=>admin_url('admin-ajax.php'),'tchaton'=>(isset($rencOpt['tchat'])?$rencOpt['tchat']:0));
		wp_localize_script('rencontre', 'rencobjet', $lang);
		if(!isset($rencOpt['fastreg'])) $rencOpt['fastreg'] = 0;
		if(!isset($rencOpt['facebook'])) $rencOpt['facebook'] = 0;
		$fantome = $wpdb->get_var("SELECT
				user_id 
			FROM
				".$wpdb->prefix."rencontre_users_profil
			WHERE
				user_id='".$mid."' and
				CHAR_LENGTH(t_titre)>4 and
				CHAR_LENGTH(t_annonce)>30
			LIMIT 1");
		if(!empty($rencOpt['pacamsg']) || !empty($rencOpt['pacasig']))
			{
			if(!$fantome) $paca = true; // paca : photo & attention-catcher & ad ; false => OK
			else $paca = (($wpdb->get_var("SELECT i_photo FROM ".$wpdb->prefix."rencontre_users WHERE user_id='".$mid."' LIMIT 1"))?false:true);
			$pacam = (!empty($rencOpt['pacamsg']))?$paca:false;
			$pacas = (!empty($rencOpt['pacasig']))?$paca:false;
			}
		else
			{
			$pacam = false;
			$pacas = false;
			}
		?>

		<div id="widgRenc" class="widgRenc">
			<script type="text/javascript"><?php 
				// JS VAR rencUrl is defined here. Checked in rencontre.js => rencWidg : if undefined, this widget is not loaded !
				echo "var rencUrl='".$rencDiv['siteurl']."',";
				echo "noEmot=".(empty($rencCustom['emot'])?0:1).";";
				$blockSearch = false; if(has_filter('rencSearchP', 'f_rencSearchP')) $blockSearch = apply_filters('rencSearchP', $blockSearch);
				$m = "{edit:".((isset($rencOpt['fastreg'])&&$rencOpt['fastreg']>1)?0:1).",msg:".((isset($rencOpt['fastreg'])&&$rencOpt['fastreg']>1)?0:1).",search:".($blockSearch?0:1)."}";
				$d = 0;
				$c = array(
					"card"=>"rencMenuCard",
					"edit"=>"rencMenuEdit",
					"msg"=>"rencMenuMsg",
					"write"=>"rencMenuMsg",
					"gsearch"=>"rencMenuSearch",
					"liste"=>"rencMenuSearch",
					"qsearch"=>"rencMenuSearch",
					"account"=>"rencMenuAccount",
					"c1"=>"rencMenuC1",
					"c2"=>"rencMenuC2"
					);
				if(isset($_GET['renc']) && isset($c[$_GET['renc']])) $d = $c[$_GET['renc']];
				if(isset($_GET['renc']) && isset($_GET['id']) && $_GET['renc']=="card" && $_GET['id']!=$mid) $d = 0;
				echo 'jQuery(document).ready(function(){f_renc_menu('.$m.','.$mid.',"'.$d.'");';
				if(isset($_SESSION["tchat"]))
					{
					$a = $wpdb->get_var("SELECT display_name FROM ".$wpdb->prefix."users WHERE ID=".$_SESSION["tchat"]." LIMIT 1");
					echo 'f_tchat_veille('.$_SESSION["tchat"].',\''.$a.'\');';
					}
				echo '});';
			?></script>
			<div id="rencTchat" class="rencTchat"></div>
			<form name='rencMenu' method='get' action=''>
				<input type='hidden' name='renc' value='' />
				<input type='hidden' name='id' value='<?php echo $mid; ?>' />
				<?php if(!empty($rencOpt['page_id'])) echo '<input type="hidden" name="page_id" value="'.$rencOpt['page_id'].'" />'; ?>
			<?php
			$ho = false; if(has_filter('rencCamP', 'f_rencCamP')) $ho = apply_filters('rencCamP', $ho);
			if(!$ho) { ?>
				
				<div id="rencCam" class="rencCam"></div>
				<div id="rencCam2" class="rencCam2"></div>
			<?php }
			$currentHome = '';
			if(strstr($_SESSION['rencontre'],'mini')) $currentHome = 'class="current"';
			$fbLike = (!empty($rencOpt['facebook'])?$rencOpt['facebook']:'');
			if(strpos($fbLike,'locale=')===false) $fbLike = str_replace('.php?','.php?locale='.get_locale().'&',$fbLike);
			if(empty($rencCustom['menu']))
				{ 
				$link = array('home'=>$rencOpt['home']); // rencontre.php rencwidget();
				// ****** TEMPLATE ********
				if(file_exists(get_stylesheet_directory().'/templates/rencontre_menu.php')) include(get_stylesheet_directory().'/templates/rencontre_menu.php');
				else include(dirname( __FILE__ ).'/../templates/rencontre_menu.php');
				// ************************
				} ?>
			</form>
			<div id="rencAdsM" class="rencAds">
			<?php $ho = false; if(has_filter('rencAdsMP', 'f_rencAdsMP')) $ho = apply_filters('rencAdsMP', $ho); if($ho) echo $ho; ?>
			</div><!-- .rencAds -->
			<?php if($rencBlock)
				{
				if(!isset($rencCustom['blocked']) || !isset($rencCustom['blockedText']) || $rencCustom['blockedText']=='') echo '<div class="rencBlock">'.__('Your account is blocked. You are invisible. Change your profile.','rencontre').'</div>';
				else echo stripslashes($rencCustom['blockedText']);
				}
		if(isset($_SESSION['rencontre']) && $_SESSION['rencontre']=='gate') self::rencGate(); // Entry screening
		//
		// 1. Nouveau visiteur
		else if(strstr($_SESSION['rencontre'],'nouveau'))
			{
			$q = $wpdb->get_var("SELECT
					S.id
				FROM
					".$wpdb->prefix."users U,
					".$wpdb->prefix."rencontre_prison S
				WHERE
					U.ID='".$mid."' and
					S.c_mail=U.user_email
				LIMIT 1");
			if($q)
				{ 
				// ****** TEMPLATE ********
				if(file_exists(get_stylesheet_directory().'/templates/rencontre_registration_jail.php')) include(get_stylesheet_directory().'/templates/rencontre_registration_jail.php');
				else include(dirname( __FILE__ ).'/../templates/rencontre_registration_jail.php');
				// ************************
				}
			else if($_SESSION['rencontre']=='nouveau')
				{ 
				$onClick = array("save"=>"f_nouveau(".$mid.",'".admin_url('admin-ajax.php')."',0)");
				$y = current_time('Y');
				$oldmax = $y-(isset($rencCustom['agemax'])?intval($rencCustom['agemax']):99)-1;
				$oldmin = $y-(isset($rencCustom['agemin'])?intval($rencCustom['agemin']):18)+1;
				// ****** TEMPLATE ********
				if(file_exists(get_stylesheet_directory().'/templates/rencontre_registration_part1.php')) include(get_stylesheet_directory().'/templates/rencontre_registration_part1.php');
				else include(dirname( __FILE__ ).'/../templates/rencontre_registration_part1.php');
				// ************************
				}
			else if($_SESSION['rencontre']=='nouveau1')
				{ 
				$onClick = array(
					"country"=>"f_region_select(this.options[this.selectedIndex].value,'".admin_url('admin-ajax.php')."','regionSelect1')",
					"save"=>"f_nouveau(".$mid.",'".admin_url('admin-ajax.php')."',1)"
					);
				// ****** TEMPLATE ********
				if(file_exists(get_stylesheet_directory().'/templates/rencontre_registration_part2.php')) include(get_stylesheet_directory().'/templates/rencontre_registration_part2.php');
				else include(dirname( __FILE__ ).'/../templates/rencontre_registration_part2.php');
				// ************************
				}
			else if($_SESSION['rencontre']=='nouveau2')
				{ 
				$onClick = array(
					"agemin"=>"f_min(this.options[this.selectedIndex].value,'formNouveau','zageMin','zageMax')",
					"agemax"=>"f_max(this.options[this.selectedIndex].value,'formNouveau','zageMin','zageMax')",
					"save"=>"f_nouveau(".$mid.",'".admin_url('admin-ajax.php')."',2)"
					);
				// ****** TEMPLATE ********
				if(file_exists(get_stylesheet_directory().'/templates/rencontre_registration_part3.php')) include(get_stylesheet_directory().'/templates/rencontre_registration_part3.php');
				else include(dirname( __FILE__ ).'/../templates/rencontre_registration_part3.php');
				// ************************
				}
			else if($_SESSION['rencontre']=='nouveau3')
				{ 
				$onClick = array("save"=>"f_nouveau(".$mid.",'".admin_url('admin-ajax.php')."',3)");
				$passNopass = "aQwZsXeDc";
				// ****** TEMPLATE ********
				if(file_exists(get_stylesheet_directory().'/templates/rencontre_registration_part4.php')) include(get_stylesheet_directory().'/templates/rencontre_registration_part4.php');
				else include(dirname( __FILE__ ).'/../templates/rencontre_registration_part4.php');
				// ************************
				$ho = false; if(has_filter('rencAdwC', 'f_rencAdwC')) $ho = apply_filters('rencAdwC', $ho); if($ho) echo $ho;
				}
			}
		//
		// 2. Partie portrait
		else if(strstr($_SESSION['rencontre'],'card'))
			{
			$id = ($rencidfm)?substr($rencidfm,1):(!empty($_GET["id"])?intval($_GET["id"]):$mid);
			if(isset($_POST["a1"]) && $_POST["a1"]=="plusImg" && isset($_POST["rnd"]) && isset($_SESSION["rnd"]) && $_POST["rnd"]==$_SESSION["rnd"])
				{
				RencontreWidget::plusImg(strip_tags($_POST["a2"]),$mid,$_POST["rotate"]);
				$mephoto = 1;
				}
			else $mephoto = $wpdb->get_var("SELECT i_photo FROM ".$wpdb->prefix."rencontre_users WHERE user_id='".$mid."' LIMIT 1"); // different des autres car zoom
			$rencidfm = 0; unset($_SESSION['rencidfm']); // RAZ du lien messagerie
			$u0 = new StdClass();
			$u0->ID = $mid;
			$u = $wpdb->get_row("SELECT
					U.ID,
					U.display_name,
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
					R.e_lat,
					R.e_lon,
					R.d_session,
					P.t_titre,
					P.t_annonce,
					P.t_profil,
					P.t_action
				FROM
					".$wpdb->prefix."users U,
					".$wpdb->prefix."rencontre_users R,
					".$wpdb->prefix."rencontre_users_profil P
				WHERE
					R.user_id=".$id." and
					R.user_id=P.user_id and
					R.user_id=U.ID and
					(R.i_status=0 or U.ID=".$mid.")
				LIMIT 1
				");
			$u->online = RencontreWidget::f_enLigne($id); // true : en ligne - false : hors ligne
			$u->blocked = new StdClass();
			$u->blocked->me = false;
			$u->blocked->he = false;
			$u->t_annonce = nl2br($u->t_annonce);
			if(strstr($_SESSION['rencontre'],'bloque')) RencontreWidget::f_bloque($id);
			$hop = false; 
			if($mid!=$id)
				{
				RencontreWidget::f_visite($id); // visite du profil - enregistrement sur ID
				$u->blocked->he = RencontreWidget::f_etat_bloque($id); // je l ai bloque ? - lecture de MID
				if(has_filter('rencNbProfP', 'f_rencNbProfP')) $hop = apply_filters('rencNbProfP', $hop);
				if($hop) echo $hop;
				}
			if($u->ID && !$hop)
				{
				$u->blocked->me = RencontreWidget::f_etat_bloque1($id,$u->t_action); // je suis bloque ?
				if(strtotime($u->d_session)) $u->session = RencontreWidget::format_date($u->d_session);
				else $u->session = 0;
				$homax = false; if(has_filter('rencNbPhotoExtP', 'f_rencNbPhotoExtP')) $homax = apply_filters('rencNbPhotoExtP', $id);
				$u->maxPhoto = ($homax!==false?min($homax,(isset($rencOpt['imnb']))?$rencOpt['imnb']:4):(isset($rencOpt['imnb'])?$rencOpt['imnb']:4));
				$u->photoUrl = $rencDiv['baseurl'].'/portrait/'.floor($id/1000).'/';
				$u->photo = new StdClass();
				$u->photo->full = array();
				$u->photo->mini = array();
				$u->photo->grande = array();
				$u->photo->over = array();
				$title = array(
					"thumb"=>"",
					"send"=>"",
					"smile"=>"",
					"contact"=>"",
					"chat"=>"",
					"block"=>"",
					"report"=>"",
					"zoombox"=>""
					);
				$disable = array(
					"thumb"=>0,
					"send"=>0,
					"smile"=>0,
					"contact"=>0,
					"chat"=>0,
					"block"=>0,
					"report"=>0
					);
				$onClick = array(
					"thumb"=>"",
					"send"=>"document.forms['rencMenu'].elements['renc'].value='write';document.forms['rencMenu'].elements['id'].value='".$u->ID."';document.forms['rencMenu'].submit();",
					"smile"=>"document.forms['rencMenu'].elements['renc'].value='sourire';document.forms['rencMenu'].elements['id'].value='".$u->ID."';document.forms['rencMenu'].submit();",
					"contact"=>"document.forms['rencMenu'].elements['renc'].value='demcont';document.forms['rencMenu'].elements['id'].value='".$u->ID."';document.forms['rencMenu'].submit();",
					"chat"=>"f_tchat(".$mid.",".$id.",'".plugins_url('rencontre/inc/rencontre_tchat.php')."',1,'".$u->display_name."')",
					"block"=>"document.forms['rencMenu'].elements['renc'].value='bloque';document.forms['rencMenu'].elements['id'].value='".$u->ID."';document.forms['rencMenu'].submit();",
					"report"=>"document.forms['rencMenu'].elements['renc'].value='signale';document.forms['rencMenu'].elements['id'].value='".$u->ID."';document.forms['rencMenu'].submit();"
					);
				$ho = false; if(has_filter('rencThumbP', 'f_rencThumbP')) $ho = apply_filters('rencThumbP', $ho);
				$hob = false; if(has_filter('rencBlurP', 'f_rencBlurP')) $hob = apply_filters('rencBlurP', $hob);
				if(($u->i_photo)!=0)
					{
					if(!empty($rencOpt['photoz']) && !$mephoto)
						{
						$disable['thumb'] = 1;
						$onClick['thumb'] = 'onClick="f_rencNoPhoto()"';
						}
					if($ho)
						{
						$title['thumb'] = stripslashes($ho); // only thumbnail
						$title['zoombox'] = stripslashes($ho);
						}
					else if(!empty($rencOpt['photoz']) && !$mephoto) // no photo
						{
						if(!isset($rencCustom['noph']) || empty($rencCustom['nophText'])) $title['thumb'] = addslashes(__("To be more visible and to view photos of other members, you should add one to your profile.","rencontre"));
						else $title['thumb'] = stripslashes($rencCustom['nophText']);
						}
					}
				else $disable['thumb'] = 1;
				for($v=0;$v<$u->maxPhoto;++$v)
					{
					if(($u->ID)*10+$v <= $u->i_photo) 
						{
						$u->photo->mini[$v] = Rencontre::f_img((($u->ID)*10+$v).'-mini').'.jpg?r='.rand();
						if(empty($rencOpt['photoz']) || $mephoto || $hob)
							{
							$u->photo->grande[$v] = Rencontre::f_img((($u->ID)*10+$v).'-grande').'.jpg?r='.rand();
							$u->photo->over[$v] = "f_vignette(".(($u->ID)*10+$v).",'".Rencontre::f_img((($u->ID)*10+$v)."-grande")."')";
							$u->photo->full[$v] = Rencontre::f_img((($u->ID)*10+$v)).'.jpg?r='.rand();
							}
						else $u->photo->over[$v] = "";
						}
					}
				//
				$blocked = '';
				if($u->blocked->he) $blocked = '<span style="font-weight:bold;color:red;text-transform:uppercase;">&nbsp;'.__('(blocked)','rencontre').'</span>';
				// Send a message - Smile - Ask for a contact - Chat - Block - Report	
				// 1. Send a message
				$ho = false; 
				if($u->blocked->me || (isset($rencOpt['fastreg']) && $rencOpt['fastreg']>1) || $rencBlock || $pacam) $disable['send'] = 1;
				else if(has_filter('rencSendP', 'f_rencSendP')) $ho = apply_filters('rencSendP', $ho);
				if($ho)
					{
					$disable['send'] = 1;
					$title['send'] = $ho;
					}
				// 2. Smile
				if(!isset($rencCustom['smile']))
					{
					$ho = false; 
					if($u->blocked->me || (isset($rencOpt['fastreg']) && $rencOpt['fastreg']>1) || $rencBlock) $disable['smile'] = 1;
					else if(has_filter('rencSmileP', 'f_rencSmileP')) $ho = apply_filters('rencSmileP', $ho);
					if($ho)
						{
						$disable['smile'] = 1;
						$title['smile'] = $ho;
						}
					}
				else $disable['smile'] = 1; // securite
				// 3. Ask for a contact
				$ho = false; 
				if($u->blocked->me || (isset($rencOpt['fastreg']) && $rencOpt['fastreg']>1) || $rencBlock) $disable['contact'] = 1;
				else if(has_filter('rencContactReqP', 'f_rencContactReqP')) $ho = apply_filters('rencContactReqP', $ho);
				if($ho)
					{
					$disable['contact'] = 1;
					$title['contact'] = $ho;
					}
				// 4. Chat
				if(!empty($rencOpt['tchat']))
					{
					$ho = false; 
					if($u->blocked->me || (isset($rencOpt['fastreg']) && $rencOpt['fastreg']>1) || $rencBlock || !$u->online) $disable['chat'] = 1;
					else if(has_filter('rencChatP', 'f_rencChatP')) $ho = apply_filters('rencChatP', $id);
					if($ho) $disable['chat'] = 1;
					}
				else $disable['chat'] = 1; // securite
				// 5. Block .. nothing
				// 6. Report
				if(isset($rencCustom['report']) || $pacas || $u->blocked->me) $disable['report'] = 1;
				//
				$ho = false; if(has_filter('rencAstro2P', 'f_rencAstro2P') && !isset($rencCustom['born'])) $ho = apply_filters('rencAstro2P', $u->d_naissance);
				if($ho) $portraitAdd1 = $ho;
				else $portraitAdd1 = '<div>&nbsp;</div>';
				//
				$u->looking = '';
				$u->forwhat = '';
				if($u->i_zsex!=99)
					{
					// looking
					if(isset($rencOpt['iam'][$u->i_zsex])) $u->looking = $rencOpt['iam'][$u->i_zsex];
					if($u->i_zsex==$u->i_sex && !isset($rencCustom['sex'])) $u->looking .= '&nbsp;'.__('gay','rencontre');
					// forwhat
					if(isset($rencOpt['for'][$u->i_zrelation])) $u->forwhat = '&nbsp;'.__('for','rencontre').'&nbsp;'.$rencOpt['for'][$u->i_zrelation];
					}
				else
					{
					// looking
					$a = explode(',', $u->c_zsex);
					$as = '';
					foreach($a as $a1) if(isset($rencOpt['iam'][$a1])) $as .= $rencOpt['iam'][$a1] . ', ';
					$u->looking = substr($as,0,-2);
					// forwhat
					$a = explode(',', $u->c_zrelation);
					$as = '';
					foreach($a as $a1) if(isset($rencOpt['for'][$a1])) $as .= $rencOpt['for'][$a1] . ', ';
					$u->forwhat = '&nbsp;'.__('for','rencontre').'&nbsp;'.substr($as,0,-2);
					}
				// looking
				if(!isset($rencCustom['born']) && $u->i_zage_min) $u->looking .= '&nbsp;'.__('between','rencontre').'&nbsp;'.$u->i_zage_min.'&nbsp;'.__('and','rencontre').'&nbsp;'.$u->i_zage_max.'&nbsp;'.__('years','rencontre');
				//
				$infochange = false;
				if(strstr($_SESSION['rencontre'],'sourire')) $infochange = RencontreWidget::f_sourire($id);
				else if(strstr($_SESSION['rencontre'],'signale')) $infochange = RencontreWidget::f_signal($id);
				else if(strstr($_SESSION['rencontre'],'demcont')) $infochange = RencontreWidget::f_demcont($id);
				// profil
				$u->profil = array();
				$ho = false; if($mid!=$id && has_filter('rencViewpP', 'f_rencViewpP')) $ho = apply_filters('rencViewpP', $ho);
				if(!$ho)
					{
					$profil = json_decode($u->t_profil,true);
					$out = ''; $l = '';
					if($profil)
						{
						foreach($profil as $h) $l .= $h['i'].',';
						$l = substr($l,0,-1);
						$q = $wpdb->get_results("SELECT
								id,
								c_categ,
								c_label,
								t_valeur,
								i_type,
								c_genre
							FROM
								".$wpdb->prefix."rencontre_profil
							WHERE
								id IN (".$l.") and
								c_lang='".substr($rencDiv['lang'],0,2)."' and
								c_categ!='' and
								c_label!='' and
								i_poids<5
							"); // Ordre inutile (get_row)
						if($q)
							{
							$l = array();
							foreach($q as $r) $l[$r->id] = $r;
							foreach($profil as $h)
								{
								$i = $h['i']; // more simple
								if($l[$i]->c_genre==='0' || strpos($l[$i]->c_genre,','.$u->i_sex.',')!==false)
									{
									if($l[$i]->i_type<3) $u->profil[$l[$i]->c_categ][$l[$i]->c_label] = $h['v'];
									else
										{
										$val = json_decode($l[$i]->t_valeur);
										if($l[$i]->i_type==3) $u->profil[$l[$i]->c_categ][$l[$i]->c_label] = $val[$h['v']];
										elseif($l[$i]->i_type==4) 
											{
											$tmp="";
											foreach ($h['v'] as $pv) { $tmp.=$val[$pv].", "; }
											$u->profil[$l[$i]->c_categ][$l[$i]->c_label] = substr($tmp, 0, -2);
											}
										else if($l[$i]->i_type==5) $u->profil[$l[$i]->c_categ][$l[$i]->c_label] = ($val[0]+$h['v']*$val[2]) . ' '.$val[3];
										}
									}
								}
							}
						}
					}
				$_SESSION['rnd'] = md5(rand(0,10000000));
				$o = '<form class="portraitPhotoPop" name="portraitPhotoPop" method="post" enctype="multipart/form-data" action="">';
				$o .= '<div style="padding:5px 0 20px;text-align:left;">';
				$o .= '<div style="text-align:center;font-weight:700;font-size:22px;margin:0 0 10px;line-height:1.2em;">'.addslashes(__("You have no photo on your profile ?","rencontre")).'</div>';
				$o .= '<p style="font-size:16px;line-height:1em;">';
				if(!isset($rencCustom['noph']) || empty($rencCustom['nophText'])) $o .= addslashes(__("To be more visible and to view photos of other members, you should add one to your profile.","rencontre"));
				else $o .= $rencCustom['nophText'];
				$o .= '</p>';
				$o .= '<input type="hidden" name="a1"value="" /><input type="hidden" name="a2" value="" /><input type="hidden" name="renc" value="" /><input type="hidden" name="rotate" value="" />';
				$o .= '<input type="hidden" name="rnd" value="'.$_SESSION['rnd'].'" />';
				$o .= '<div style="margin:0 auto;text-align:center">';
				$o .= '<a href="javascript:void(0)" class="button right" onClick="f_plus_photoPop_submit('.$mephoto.')" title="'.addslashes(__('Add this photo','rencontre')).'">'.addslashes(__('Add this photo','rencontre')).'</a>';
				$o .= '</div>';
				$o .= '<input type="file" name="plusPhoto" size="18" onchange="f_photoPop_display(this)">';
				$o .= '<div style=clear:both;"></div>';
				$o .= '</div>';
				if(!empty($rencOpt['fblog']) && strlen($rencOpt['fblog'])>2)
					{
					$o .= '<div style="padding:20px 0 0;border-top:1px solid #333;">';
					$o .= '<a href="javascript:void(0)" class="button btn-fb left" onClick="f_FBLogin('.$mephoto.');">'.addslashes(__('Facebook Profile Photo','rencontre')).'</a>';
					$o .= '</div>';
					}
				$o .= '<div id="popPhoto"></div>';
				$o .= '</form>';
				$script = '<script type="text/javascript" src="'.plugins_url('rencontre/js/zoombox-min.js').'"></script>'."\r\n";
				$script .= '<script type="text/javascript" src="'.plugins_url('rencontre/js/jqueryRotate-min.js').'"></script>'."\r\n";
				$script .= '<script type="text/javascript">function f_rencNoPhoto(){var w=Math.min(jQuery(window).width()-35,600);';
				$script .= 'jQuery.zoombox.html(\''.$o.'\',{width:w,height:(w<600?340:280)});return false;};'."\r\n";
				$script .= 'jQuery(document).ready(function(){jQuery(\'a.rencZoombox\').zoombox();});'."\r\n";
				if(!empty($rencOpt['fblog']) && strlen($rencOpt['fblog'])>2)
					{
					$script .= '(function(d){var js,id=\'facebook-jssdk\',ref=d.getElementsByTagName(\'script\')[0];if(d.getElementById(id))return;js=d.createElement(\'script\');js.id=id;js.async=true;js.src="//connect.facebook.net/en_US/all.js";ref.parentNode.insertBefore(js,ref);}(document));';
					$script .= 'window.fbAsyncInit=function(){FB.init({appId:\''.$rencOpt['fblog'].'\',status:true,cookie:true,xfbml:true,version:\'v2.5\'});};'."\r\n";
					}
				$script .= '</script>'."\r\n";
				// ****** TEMPLATE ********
				if(file_exists(get_stylesheet_directory().'/templates/rencontre_portrait.php')) include(get_stylesheet_directory().'/templates/rencontre_portrait.php');
				else include(dirname( __FILE__ ).'/../templates/rencontre_portrait.php');
				// ************************
				}
			}
		//
		// 3. Partie Changement du portrait
		else if(strstr($_SESSION['rencontre'],'edit') && (empty($rencOpt['fastreg']) || $rencOpt['fastreg']<2))
			{
			// recuperation de la table profil
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
				ORDER BY
					P.i_categ,
					P.i_label
				");
			$s = $wpdb->get_row("SELECT
					R.i_sex,
					P.t_profil
				FROM
					".$wpdb->prefix."rencontre_users R,
					".$wpdb->prefix."rencontre_users_profil P
				WHERE
					R.user_id=".$mid." and
					R.user_id=P.user_id
				LIMIT 1
				");
			//
			if(isset($_POST["a1"]) && isset($_POST["rnd"]) && isset($_SESSION["rnd"]) && $_POST["rnd"]==$_SESSION["rnd"])
				{
				if($_POST["a1"]=="suppImg") RencontreWidget::suppImg(strip_tags($_POST["a2"]),$mid);
				if($_POST["a1"]=="plusImg") RencontreWidget::plusImg(strip_tags($_POST["a2"]),$mid,$_POST["rotate"]);
				if($_POST["a1"]=="suppImgAll") RencontreWidget::suppImgAll($mid);
				}
			if(isset($_POST["a1"]) && $_POST["a1"]=="sauvProfil")
				{
				$in = array();
				$p = json_decode($s->t_profil,true);
				foreach($q as $r)
					{
					if($r->c_genre==='0' || strpos($r->c_genre,','.$s->i_sex.',')!==false)
						{
						$in[$r->id][0] = $r->i_type;
						$in[$r->id][1] = $r->c_categ;
						$in[$r->id][2] = $r->c_label;
						$in[$r->id][3] = $r->t_valeur;
						}
					}
				RencontreWidget::sauvProfil($in,$mid);
				}
			//
			$u0 = $wpdb->get_row("SELECT
					U.ID,
					U.display_name,
					R.c_pays,
					R.c_ville,
					R.i_sex,
					R.i_photo,
					P.t_titre,
					P.t_annonce,
					P.t_profil
				FROM
					".$wpdb->prefix."users U,
					".$wpdb->prefix."rencontre_users R,
					".$wpdb->prefix."rencontre_users_profil P
				WHERE
					R.user_id=".$mid." and
					R.user_id=P.user_id and
					R.user_id=U.ID
				LIMIT 1
				");
			$obj = array();
			$p = json_decode($u0->t_profil,true);
			foreach($q as $r)
				{
				if($r->c_genre==='0' || strpos($r->c_genre,','.$s->i_sex.',')!==false)
					{
					$a = new StdClass();
					$a->id = $r->id;
					$a->label = $r->c_label;
					$a->type = $r->i_type;
					$a->active = '';
					if($r->i_type==3 || $r->i_type==4) $a->valeur = json_decode($r->t_valeur);
					else if($r->i_type==5)
						{
						$b = json_decode($r->t_valeur);
						$a->valeur = array();
						for($v=$b[0]; $v<=$b[1]; $v+=$b[2]) $a->valeur[] = $v.' '.$b[3];
						}
					if($p) foreach($p as $v)
						{
						if($v['i']==$r->id)
							{
							if(is_array($v['v'])) // type 4
								{
								$a->active = ',';
								foreach($v['v'] as $v1) $a->active .= $v1.',';
								}
							else $a->active = $v['v'];
							}
						}
					$obj[$r->c_categ][] = $a;
					}
				}
			$u0->profil = $obj;
			if($u0->ID)
				{
				for($v=$u0->ID*10;$v<=$u0->i_photo;++$v) // cleaning
					{
					if(!file_exists($rencDiv['basedir'].'/portrait/'.floor($u0->ID/1000).'/'.Rencontre::f_img(($v).'-mini').'.jpg')) RencontreWidget::suppImg($v,$u0->ID);
					}
				$ho = false; if(has_filter('rencNbPhotoP', 'f_rencNbPhotoP')) $ho = apply_filters('rencNbPhotoP', $ho);
				$u0->maxPhoto = ($ho!==false?min($ho,(isset($rencOpt['imnb']))?$rencOpt['imnb']:4):(isset($rencOpt['imnb']))?$rencOpt['imnb']:4);
				$u0->photoUrl = $rencDiv['baseurl'].'/portrait/'.floor($u0->ID/1000).'/';
				$u0->photo = new StdClass();
				$u0->photo->full = array();
				$u0->photo->mini = array();
				$u0->photo->grande = array();
				$u0->photo->over = array();
				for($v=0;$v<(isset($rencOpt['imnb'])?$rencOpt['imnb']:4);++$v)
					{
					if(($u0->ID)*10+$v <= $u0->i_photo) 
						{
						$u0->photo->mini[$v] = Rencontre::f_img((($u0->ID)*10+$v).'-mini').'.jpg?r='.rand();
						$u0->photo->grande[$v] = Rencontre::f_img((($u0->ID)*10+$v).'-grande').'.jpg?r='.rand();
						$u0->photo->over[$v] = "f_vignette_change(".($u0->ID*10+$v).",'".Rencontre::f_img((($u0->ID)*10+$v)."-grande")."')";
						$u0->photo->full[$v] = Rencontre::f_img((($u0->ID)*10+$v)).'.jpg?r='.rand();
						}
					}
				$onClick = array(
					"add"=>"f_plus_photoPop(".$u0->i_photo.")",
					"deleteAll"=>"f_suppAll_photo()",
					"sauv"=>"f_sauv_profil(".$u0->ID.")"
					);
				for($v=0;$v<$rencOpt['imnb'];++$v)
					{
					$onClick['delete'.$v] = "f_supp_photo(".($u0->ID*10+$v).")";
					}
				//
				$infochange = false;
				if(isset($_POST["a1"]) && $_POST["a1"]=="sauvProfil") $infochange = __('Done','rencontre').'&nbsp;';
				else if($pacam) $infochange = __('You should complete your profile and add a photo to send messages.','rencontre').'&nbsp;';
				//
				$_SESSION['rnd'] = md5(rand(0,10000000));
				$o = '<form class="portraitPhotoPop" name="portraitPhotoPop" method="post" enctype="multipart/form-data" action="">';
				$o .= '<div style="padding:5px 0 20px;text-align:left;">';
				$o .= '<div style="font-size:2em;font-weight:700;text-align:center;line-height:1.2em;">'.addslashes(__('Add a photo','rencontre')).'</div>';
				$o .= '<input type="hidden" name="a1"value="" /><input type="hidden" name="a2" value="" /><input type="hidden" name="renc" value="" /><input type="hidden" name="rotate" value="" />';
				$o .= '<input type="hidden" name="rnd" value="'.$_SESSION['rnd'].'" />';
				$o .= '<div style="margin:0 auto;text-align:center">';
				$o .= '<a href="javascript:void(0)" class="button right" onClick="f_plus_photoPop_submit('.$u0->i_photo.')" title="'.addslashes(__('Add this photo','rencontre')).'">'.addslashes(__('Add this photo','rencontre')).'</a>';
				$o .= '</div>';
				$o .= '<input type="file" name="plusPhoto" size="18" onchange="f_photoPop_display(this)">';
				$o .= '<div style=clear:both;"></div>';
				$o .= '</div>';
				if(!empty($rencOpt['fblog']) && strlen($rencOpt['fblog'])>2)
					{
					$o .= '<div style="padding:20px 0 0;border-top:1px solid #333;">';
					$o .= '<a href="javascript:void(0)" class="button btn-fb left" onClick="f_FBLogin('.$u0->i_photo.');">'.addslashes(__('Facebook Profile Photo','rencontre')).'</a>';
					$o .= '</div>';
					}
				$o .= '<div id="popPhoto"></div>';
				$o .= '</form>';
				$script = '<script type="text/javascript" src="'.plugins_url('rencontre/js/zoombox-min.js').'"></script>'."\r\n";
				$script .= '<script type="text/javascript" src="'.plugins_url('rencontre/js/jqueryRotate-min.js').'"></script>'."\r\n";
				$script .= '<script>function f_plus_photoPop(f){var w=Math.min(jQuery(window).width()-35,600);';
				$script .= 'jQuery.zoombox.html(\''.$o.'\',{width:w,height:220});}'."\r\n";
				$script .= 'jQuery(document).ready(function(){jQuery(\'a.rencZoombox\').zoombox();});';
				if(!empty($rencOpt['fblog']) && strlen($rencOpt['fblog'])>2)
					{					
					$script .= "\r\n".'(function(d){var js,id=\'facebook-jssdk\',ref=d.getElementsByTagName(\'script\')[0];if(d.getElementById(id))return;js=d.createElement(\'script\');js.id=id;js.async=true;js.src="//connect.facebook.net/en_US/all.js";ref.parentNode.insertBefore(js,ref);}(document));';
					$script .= 'window.fbAsyncInit=function(){FB.init({appId:\''.$rencOpt['fblog'].'\',status:true,cookie:true,xfbml:true,version:\'v2.5\'});};';
					}
				$script .= "\r\n".'</script>';
				// ****** TEMPLATE ********
				if(file_exists(get_stylesheet_directory().'/templates/rencontre_portrait_edit.php')) include(get_stylesheet_directory().'/templates/rencontre_portrait_edit.php');
				else include(dirname( __FILE__ ).'/../templates/rencontre_portrait_edit.php');
				// ************************
				}
			}
		//
		// 4. Partie Mon Accueil
		else
			{
			if(strstr($_SESSION['rencontre'],'accueil'))
				{
				$s = $wpdb->get_row("SELECT
						U.ID,
						U.display_name,
						U.user_login,
						R.c_ip,
						R.c_pays,
						R.c_ville,
						R.i_sex,
						R.d_naissance,
						R.i_zsex,
						R.c_zsex,
						R.i_zage_min,
						R.i_zage_max,
						R.i_zrelation,
						R.c_zrelation,
						R.i_photo,
						P.t_action 
					FROM
						".$wpdb->prefix."users U,
						".$wpdb->prefix."rencontre_users R,
						".$wpdb->prefix."rencontre_users_profil P 
					WHERE
						R.user_id=".$mid." and
						R.user_id=P.user_id and
						R.user_id=U.ID
					LIMIT 1
					"); // data used in other part
				if(!isset($rencCustom['side']) || !$rencCustom['side'])
					{
					$renc = new RencontreSidebarWidget;
					$renc->widget(0,$s); // data send to limit sql request & remove clear end
					}
				if($s->i_zsex!=99) $zsex=$s->i_zsex;
				else $zsex='('.substr($s->c_zsex,1,-1).')';
				$homo=(($s->i_sex==$s->i_zsex)?1:0); // seulement si genre sans custom
				if($s->i_zage_min) $zmin=date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"), date("Y")-$s->i_zage_min)); else $zmin=0;
				if($s->i_zage_max) $zmax=date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"), date("Y")-$s->i_zage_max)); else $zmax=0;
				}
			//
			// 5. Partie mini portrait
			if(strstr($_SESSION['rencontre'],'mini')) // mini toujours avec accueil
				{
				$ho = false; if(has_filter('rencWssP', 'f_rencWssP')) $ho = apply_filters('rencWssP', $ho);
				if($ho) $mephoto = $wpdb->get_var("SELECT i_photo FROM ".$wpdb->prefix."rencontre_users WHERE user_id='".$mid."' LIMIT 1");
				else $mephoto = 1;
				if(!isset($zsex)) // test actuellement inutile car deja fait (voir plus haut)
					{
					$q = $wpdb->get_row("SELECT
							i_sex,
							i_zsex,
							c_zsex,
							i_zage_min,
							i_zage_max
						FROM
							".$wpdb->prefix."rencontre_users
						WHERE
							user_id='".$mid."'
						LIMIT 1
						");
					if($q>i_zsex!=99) $zsex=$q->i_zsex;
					else $zsex='('.substr($q->c_zsex,1,-1).')';
					$homo=(($s->i_sex==$s->i_zsex)?1:0);
					if($q->i_zage_min) $zmin=date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"), date("Y")-$q->i_zage_min)); else $zmin=0;
					if($q->i_zage_max) $zmax=date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"), date("Y")-$q->i_zage_max)); else $zmax=0;
					}
				// Selection par le sex
				if(strpos($zsex,')')===false)
					{
					$sexQuery = " AND R.i_sex=".$zsex." ";
					if(!isset($rencCustom['sex'])) $sexQuery .= " AND R.i_zsex".(($homo)?'='.$zsex:'!='.$zsex)." ";
					}
				else
					{
					$sexQuery = " AND R.i_sex IN ".$zsex." ";
					}
				?>
				
				<div class="<?php echo ((isset($rencCustom['side']) && $rencCustom['side'])?'pleineBox':'grandeBox left'); ?>">
				<?php 
				$uFeatProf = $wpdb->get_results("SELECT DISTINCT(R.user_id) 
					FROM 
						".$wpdb->prefix."rencontre_users R,
						".$wpdb->prefix."rencontre_users_profil P 
					WHERE 
						R.i_status=0 
						AND R.user_id=P.user_id 
						".$sexQuery."
						".((!isset($rencCustom['born']) && $zmax && $zmin)?"
						AND R.d_naissance>'".$zmax."' 
						AND R.d_naissance<'".$zmin."'":" ")."
						".(!empty($rencOpt['onlyphoto'])?" AND R.i_photo>0 ":" ")."
						AND CHAR_LENGTH(P.t_titre)>4 
						AND CHAR_LENGTH(P.t_annonce)>30 
						AND R.user_id!=".$mid." 
					ORDER BY RAND() LIMIT 8");
				if(!empty($rencOpt['anniv']) && !isset($rencCustom['born']))
					{
					$uBirthday = $wpdb->get_results("SELECT R.user_id 
						FROM ".$wpdb->prefix."rencontre_users R
						WHERE 
							R.d_naissance LIKE '%".current_time('m-d')."' 
							".$sexQuery."
							".((!isset($rencCustom['born']) && $zmax && $zmin)?"AND R.d_naissance>'".$zmax."' AND R.d_naissance<'".$zmin."'":"")."
							AND R.user_id!=".$mid."
							AND R.i_status=0
						ORDER BY RAND() LIMIT 4");
					}
				if(!empty($rencOpt['ligne']))
					{
					$tab=''; $d=$rencDiv['basedir'].'/session/';
					if($dh=opendir($d))
						{
						while(($file = readdir($dh))!==false)
							{
							if($file!='.' && $file!='..' && filemtime($d.$file)>time()-180) $tab .= "'".basename($file, ".txt")."',";
							}
						closedir($dh);
						}
					$uLine = $wpdb->get_results("SELECT R.user_id 
						FROM ".$wpdb->prefix."rencontre_users R
						WHERE 
							R.user_id IN (".substr($tab,0,-1).") 
							".$sexQuery."
							AND R.user_id!=".$mid."
							AND R.i_status=0
						ORDER BY RAND() LIMIT 16"); // AND d_naissance>'".$zmax."' AND d_naissance<'".$zmin."' ?>
						
					<form name='rencLine' method='get' action=''>
						<?php if(!empty($rencOpt['page_id'])) echo '<input type="hidden" name="page_id" value="'.$rencOpt['page_id'].'" />'; ?>
						
						<input type='hidden' name='renc' value='qsearch' />
						<input type='hidden' name='obj' value='enligne' />
						<input type='hidden' name='zsex' value='<?php echo $zsex; ?>' />
						<input type='hidden' name='homo' value='<?php echo $homo; ?>' />
					</form>
				<?php } ?>
				<?php $uNew = $wpdb->get_results("SELECT R.user_id 
					FROM 
						".$wpdb->prefix."rencontre_users R,
						".$wpdb->prefix."rencontre_users_profil P 
					WHERE 
						R.i_status=0 
						AND R.user_id=P.user_id 
						".$sexQuery."
						".(!empty($rencOpt['onlyphoto'])?" AND R.i_photo>0 ":" ")."
						AND CHAR_LENGTH(P.t_titre)>4 
						AND CHAR_LENGTH(P.t_annonce)>30 
						AND R.user_id!=".$mid." 
					ORDER BY R.user_id DESC LIMIT 12");
				$u0 = new StdClass();
				$u0->ID = $mid;
				// ****** TEMPLATE ********
				if(file_exists(get_stylesheet_directory().'/templates/rencontre_my_home.php')) include(get_stylesheet_directory().'/templates/rencontre_my_home.php');
				else include(dirname( __FILE__ ).'/../templates/rencontre_my_home.php');
				// ************************
				?>

				</div><!-- .<?php echo ((isset($rencCustom['side']) && $rencCustom['side'])?'pleineBox':'grandeBox'); ?> .left -->
			<?php }
			//
			// 6. Partie recherche rapide
			$hom = false; if(has_filter('rencNbSearchP', 'f_rencNbSearchP')) $hom = apply_filters('rencNbSearchP', $hom);
			if(strstr($_SESSION['rencontre'],'qsearch') && $hom) echo $hom;
			else if(strstr($_SESSION['rencontre'],'qsearch')) // cherche toujours avec accueil
				{
				$ho = false; if(has_filter('rencWssP', 'f_rencWssP')) $ho = apply_filters('rencWssP', $ho);
				if($ho) $mephoto = $wpdb->get_var("SELECT i_photo FROM ".$wpdb->prefix."rencontre_users WHERE user_id='".$mid."' LIMIT 1");
				else $mephoto = 1;
				$q = false;
				$pagine = (isset($_GET['pagine'])?$_GET['pagine']:0);
				$suiv = 1;
				?> 
				<form name='rencPagine' method='get' action=''>
					<?php if(!empty($rencOpt['page_id'])) echo '<input type="hidden" name="page_id" value="'.$rencOpt['page_id'].'" />'; ?>
					<input type='hidden' name='renc' value='qsearch' />
					<input type='hidden' name='id' value='<?php echo (isset($_GET['id'])?$_GET['id']:''); ?>' />
					<input type='hidden' name='zsex' value='<?php echo (isset($_GET['zsex'])?$_GET['zsex']:''); ?>' />
					<input type='hidden' name='homo' value='<?php echo (isset($_GET['homo'])?$_GET['homo']:''); ?>' />
					<input type='hidden' name='pagine' value='<?php echo $pagine; ?>' />
					<input type='hidden' name='ageMin' value='<?php echo (isset($_GET['ageMin'])?$_GET['ageMin']:''); ?>' />
					<input type='hidden' name='ageMax' value='<?php echo (isset($_GET['ageMax'])?$_GET['ageMax']:''); ?>' />
					<input type='hidden' name='pays' value='<?php echo (isset($_GET['pays'])?$_GET['pays']:''); ?>' />
					<input type='hidden' name='region' value='<?php echo (isset($_GET['region'])?$_GET['region']:''); ?>' />
					<input type='hidden' name='obj' value='<?php echo (isset($_GET['obj'])?$_GET['obj']:''); ?>' />
					<input type='hidden' name='profilQS1' value='<?php echo (isset($_GET['profilQS1'])?$_GET['profilQS1']:''); ?>' />
					<input type='hidden' name='profilQS2' value='<?php echo (isset($_GET['profilQS2'])?$_GET['profilQS2']:''); ?>' />
					<input type='hidden' name='relation' value='<?php echo (isset($_GET['relation'])?$_GET['relation']:''); ?>' />
				</form>
				<div class="<?php echo ((isset($rencCustom['side']) && $rencCustom['side'])?'pleineBox':'grandeBox left'); ?>">
				<?php if(isset($_GET['zsex']) && $_GET['zsex']!='' && (!isset($_GET['obj']) || $_GET['obj']==''))
					{
					$s="SELECT
							R.user_id,
							R.i_zsex,
							R.c_zsex,
							R.i_zage_min,
							R.i_zage_max,
							R.i_zrelation,
							R.c_zrelation,
							R.d_session,
							P.t_annonce,
							P.t_action
						FROM 
							".$wpdb->prefix."rencontre_users_profil P,
							".$wpdb->prefix."rencontre_users R
						WHERE P.user_id=R.user_id 
							and R.i_status=0 
							and R.user_id!=".$mid;
					if(isset($_GET['region']) && $_GET['region']) $s.=" and R.c_region LIKE '".addslashes($wpdb->get_var("SELECT c_liste_valeur FROM ".$wpdb->prefix."rencontre_liste WHERE id='".strip_tags($_GET['region'])."' LIMIT 1"))."'";
					if(isset($_GET['pays']) && $_GET['pays']) $s.=" and R.c_pays='".$_GET['pays']."'";
					// zsex : si parenthese => c_zsex au format IN : (a,b,g) au lieu de ,a,b,g,
					// sinon => i_zsex : a
					if(strpos($_GET['zsex'],')')===false)
						{
						$s.=" and R.i_sex='".strip_tags($_GET['zsex'])."'";
						if(!isset($rencCustom['sex'])) $s.=" and R.i_zsex".((strip_tags($_GET['homo']))?'=':'!=').strip_tags($_GET['zsex']);
						}
					else $s.=" and R.i_sex IN ".strip_tags($_GET['zsex']);
					if(!isset($rencCustom['born']))
						{
						if(isset($_GET['ageMin']) && $_GET['ageMin']>(isset($rencCustom['agemin'])?intval($rencCustom['agemin']):18)) // 18 & 99 => no limit
							{
							$zmin=date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"), date("Y")-$_GET['ageMin']));
							$s.=" and R.d_naissance<'".$zmin."'";
							}
						if(isset($_GET['ageMax']) && $_GET['ageMax'] && $_GET['ageMax']<(isset($rencCustom['agemax'])?intval($rencCustom['agemax']):99))
							{
							$zmax=date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"), date("Y")-$_GET['ageMax']));
							$s.=" and R.d_naissance>'".$zmax."'";
							}
						}
					if(isset($_GET['relation']) && isset($rencCustom['relationQ']) && $_GET['relation']!='')
						{
						$s.=" and (R.i_zrelation='".strip_tags($_GET['relation'])."' or R.c_zrelation LIKE '%,".strip_tags($_GET['relation']).",%')";
						}
					if(isset($_GET['profilQS1']) && isset($rencCustom['profilQS1']) && $_GET['profilQS1']!='')
						{
						$t = $wpdb->get_var("SELECT i_type FROM ".$wpdb->prefix."rencontre_profil WHERE id=".$rencCustom['profilQS1']." LIMIT 1");
						if($t==3 || $t==4) $s.=' and P.t_profil REGEXP \'(\{"i":'.$rencCustom['profilQS1'].',)[^\{]+[\[,:]'.$_GET['profilQS1'].'[\],\}]\' ';
						else if($t==5) $s.=' and P.t_profil LIKE \'%{"i":'.$rencCustom['profilQS1'].',"v":'.($_GET['profilQS1']-1).'\}%\' ';
						}
					if(isset($_GET['profilQS2']) && isset($rencCustom['profilQS2']) && $_GET['profilQS2']!='')
						{
						$t = $wpdb->get_var("SELECT i_type FROM ".$wpdb->prefix."rencontre_profil WHERE id=".$rencCustom['profilQS2']." LIMIT 1");
						if($t==3 || $t==4) $s.=' and P.t_profil REGEXP \'(\{"i":'.$rencCustom['profilQS2'].',)[^\{]+[\[,:]'.$_GET['profilQS2'].'[\],\}]\' ';
						else if($t==5) $s.=' and P.t_profil LIKE \'%{"i":'.$rencCustom['profilQS2'].',"v":'.($_GET['profilQS2']-1).'\}%\' ';
						}
					if(!empty($rencOpt['onlyphoto'])) $s.=" and CHAR_LENGTH(P.t_titre)>4 and CHAR_LENGTH(P.t_annonce)>30 and R.i_photo>0";
					$s.=" ORDER BY R.d_session DESC, P.d_modif DESC LIMIT ".($pagine*(isset($rencOpt['limit'])?$rencOpt['limit']:10)).", ".((isset($rencOpt['limit'])?$rencOpt['limit']:10)+1); // LIMIT indice du premier, nombre de resultat
					$q = $wpdb->get_results($s);
					if($wpdb->num_rows<=(isset($rencOpt['limit'])?$rencOpt['limit']:10)) $suiv=0;
					else array_pop($q); // supp le dernier ($rencOpt['limit']+1) qui sert a savoir si page suivante
					}
				else if(isset($_GET['id']) && $_GET['id']=='sourireOut')
					{
					echo '<h3 style="text-align:center;">';
					if(isset($rencCustom['smiw']) && isset($rencCustom['smiw3']) && $rencCustom['smiw'] && $rencCustom['smiw3']) echo stripslashes($rencCustom['smiw3']);
					else echo __('I smiled at','rencontre');
					echo '&nbsp;...</h3>';
					$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$mid."' LIMIT 1");
					$action= json_decode($q,true);
					$action['sourireOut']=(isset($action['sourireOut'])?$action['sourireOut']:null);
					$q = ''; $c = 0; $n = 0; $suiv = 0;
					if($action['sourireOut'])
						{
						krsort($action['sourireOut']);
						foreach ($action['sourireOut'] as $r)
							{
							if($c<=(isset($rencOpt['limit'])?$rencOpt['limit']:10))
								{
								$q[$c]=$wpdb->get_row("SELECT 
										R.user_id,
										R.i_zsex,
										R.c_zsex,
										R.i_zage_min,
										R.i_zage_max,
										R.i_zrelation,
										R.c_zrelation,
										P.t_annonce,
										P.t_action 
									FROM 
										".$wpdb->prefix."rencontre_users R,
										".$wpdb->prefix."rencontre_users_profil P
									WHERE 
										R.user_id='".$r['i']."' and 
										P.user_id=R.user_id and 
										R.i_status=0
									LIMIT 1
									");
								if($q[$c]) ++$n;
								if($q[$c] && $n>$pagine*(isset($rencOpt['limit'])?$rencOpt['limit']:10))
									{
									if($c<(isset($rencOpt['limit'])?$rencOpt['limit']:10)) $q[$c]->dataction=$r['d'];
									else {$suiv=1;array_pop($q);}
									++$c;
									}
								else unset($q[$c]);
								}
							}
						}
					}
				else if(isset($_GET['id']) && $_GET['id']=='sourireIn')
					{
					echo '<h3 style="text-align:center;">';
					if(isset($rencCustom['smiw']) && isset($rencCustom['smiw7']) && $rencCustom['smiw'] && $rencCustom['smiw7']) echo stripslashes($rencCustom['smiw7']);
					else echo __('I got a smile from','rencontre');
					echo '&nbsp;...</h3>';
					$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$mid."' LIMIT 1");
					$action= json_decode($q,true);
					$action['sourireIn']=(isset($action['sourireIn'])?$action['sourireIn']:null);
					$q = ''; $c = 0; $n = 0; $suiv = 0;
					if($action['sourireIn'])
						{
						krsort($action['sourireIn']);
						foreach ($action['sourireIn'] as $r)
							{
							if($c<=(isset($rencOpt['limit'])?$rencOpt['limit']:10))
								{
								$q[$c]=$wpdb->get_row("SELECT 
										R.user_id,
										R.i_zsex,
										R.c_zsex,
										R.i_zage_min,
										R.i_zage_max,
										R.i_zrelation,
										R.c_zrelation,
										P.t_annonce,
										P.t_action 
									FROM 
										".$wpdb->prefix."rencontre_users R,
										".$wpdb->prefix."rencontre_users_profil P
									WHERE 
										R.user_id='".$r['i']."' and 
										P.user_id=R.user_id and 
										R.i_status=0
									LIMIT 1
									");
								if($q[$c]) ++$n;
								if($q[$c] && $n>$pagine*(isset($rencOpt['limit'])?$rencOpt['limit']:10))
									{
									if($c<(isset($rencOpt['limit'])?$rencOpt['limit']:10)) $q[$c]->dataction=$r['d'];
									else {$suiv=1;array_pop($q);}
									++$c;
									}
								else unset($q[$c]);
								}
							}
						}
					}
				else if(isset($_GET['id']) && $_GET['id']=='contactOut')
					{
					echo '<h3 style="text-align:center;">'.__('I asked a contact','rencontre').'&nbsp;...</h3>';
					$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$mid."' LIMIT 1");
					$action= json_decode($q,true);
					$action['contactOut']=(isset($action['contactOut'])?$action['contactOut']:null);
					$q = ''; $c = 0; $n = 0; $suiv = 0;
					if($action['contactOut'])
						{
						krsort($action['contactOut']);
						foreach ($action['contactOut'] as $r)
							{
							if($c<=(isset($rencOpt['limit'])?$rencOpt['limit']:10))
								{
								$q[$c]=$wpdb->get_row("SELECT 
										R.user_id,
										R.i_zsex,
										R.c_zsex,
										R.i_zage_min,
										R.i_zage_max,
										R.i_zrelation,
										R.c_zrelation,
										P.t_annonce,
										P.t_action 
									FROM 
										".$wpdb->prefix."rencontre_users R,
										".$wpdb->prefix."rencontre_users_profil P
									WHERE 
										R.user_id='".$r['i']."' and 
										P.user_id=R.user_id and 
										R.i_status=0
									LIMIT 1
									");
								if($q[$c]) ++$n;
								if($q[$c] && $n>$pagine*(isset($rencOpt['limit'])?$rencOpt['limit']:10))
									{
									if($c<(isset($rencOpt['limit'])?$rencOpt['limit']:10)) $q[$c]->dataction=$r['d'];
									else {$suiv=1;array_pop($q);}
									++$c;
									}
								else unset($q[$c]);
								}
							}
						}
					}
				else if(isset($_GET['id']) && $_GET['id']=='contactIn')
					{
					echo '<h3 style="text-align:center;">'.__('I had a contact request from','rencontre').'&nbsp;...</h3>';
					$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$mid."' LIMIT 1");
					$action= json_decode($q,true);
					$action['contactIn']=(isset($action['contactIn'])?$action['contactIn']:null);
					$q = '';$c = 0; $n = 0; $suiv = 0;
					if($action['contactIn'])
						{
						krsort($action['contactIn']);
						foreach ($action['contactIn'] as $r)
							{
							if($c<=(isset($rencOpt['limit'])?$rencOpt['limit']:10))
								{
								$q[$c]=$wpdb->get_row("SELECT 
										R.user_id,
										R.i_zsex,
										R.c_zsex,
										R.i_zage_min,
										R.i_zage_max,
										R.i_zrelation,
										R.c_zrelation,
										P.t_annonce,
										P.t_action 
									FROM
										".$wpdb->prefix."rencontre_users R,
										".$wpdb->prefix."rencontre_users_profil P
									WHERE 
										R.user_id='".$r['i']."' and 
										P.user_id=R.user_id and 
										R.i_status=0
									LIMIT 1
									");
								if($q[$c]) ++$n;
								if($q[$c] && $n>$pagine*(isset($rencOpt['limit'])?$rencOpt['limit']:10))
									{
									if($c<(isset($rencOpt['limit'])?$rencOpt['limit']:10)) $q[$c]->dataction=$r['d'];
									else {$suiv=1;array_pop($q);}
									++$c;
									}
								else unset($q[$c]);
								}
							}
						}
					}
				else if(isset($_GET['id']) && $_GET['id']=='visite')
					{
					echo '<h3 style="text-align:center;">';
					if(isset($rencCustom['loow']) && isset($rencCustom['loow2']) && $rencCustom['loow'] && $rencCustom['loow2']) echo stripslashes($rencCustom['loow2']);
					else echo __('I was watched by','rencontre');
					echo '&nbsp;...</h3>';
					$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$mid."' LIMIT 1");
					$action= json_decode($q,true);
					$action['visite']=(isset($action['visite'])?$action['visite']:null);
					$q = ''; $c = 0; $n = 0; $suiv = 0;
					if($action['visite'])
						{
						krsort($action['visite']);
						foreach ($action['visite'] as $r)
							{
							if($c<=(isset($rencOpt['limit'])?$rencOpt['limit']:10))
								{
								$q[$c]=$wpdb->get_row("SELECT 
										R.user_id,
										R.i_zsex,
										R.c_zsex,
										R.i_zage_min,
										R.i_zage_max,
										R.i_zrelation,
										R.c_zrelation,
										P.t_annonce,
										P.t_action,
										R.i_status 
									FROM 
										".$wpdb->prefix."rencontre_users R,
										".$wpdb->prefix."rencontre_users_profil P
									WHERE 
										R.user_id='".$r['i']."' and 
										P.user_id=R.user_id and 
										R.i_status=0
									LIMIT 1");
								if($q[$c]) ++$n;
								if($q[$c] && $n>$pagine*(isset($rencOpt['limit'])?$rencOpt['limit']:10))
									{
									if($c<(isset($rencOpt['limit'])?$rencOpt['limit']:10)) $q[$c]->dataction=$r['d'];
									else {$suiv=1;array_pop($q);}
									++$c;
									}
								else unset($q[$c]);
								}
							}
						}
					}
				else if(isset($_GET['id']) && $_GET['id']=='bloque')
					{
					echo '<h3 style="text-align:center;">'.__('I locked','rencontre').'&nbsp;...</h3>';
					$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$mid."' LIMIT 1");
					$action= json_decode($q,true);
					$action['bloque']=(isset($action['bloque'])?$action['bloque']:null);
					$q = ''; $c = 0; $n = 0; $suiv = 0;
					if($action['bloque'])
						{
						krsort($action['bloque']);
						foreach ($action['bloque'] as $r)
							{
							if($c<=(isset($rencOpt['limit'])?$rencOpt['limit']:10))
								{
								$q[$c]=$wpdb->get_row("SELECT 
										R.user_id,
										R.i_zsex,
										R.c_zsex,
										R.i_zage_min,
										R.i_zage_max,
										R.i_zrelation,
										R.c_zrelation,
										P.t_annonce,
										P.t_action 
									FROM 
										".$wpdb->prefix."rencontre_users R,
										".$wpdb->prefix."rencontre_users_profil P
									WHERE 
										R.user_id='".$r['i']."' and 
										P.user_id=R.user_id and 
										R.i_status=0
									LIMIT 1
									");
								if($q[$c]) ++$n;
								if($q[$c] && $n>$pagine*(isset($rencOpt['limit'])?$rencOpt['limit']:10))
									{
									if($c<(isset($rencOpt['limit'])?$rencOpt['limit']:10)) $q[$c]->dataction=$r['d'];
									else {$suiv=1;array_pop($q);}
									++$c;
									}
								else unset($q[$c]);
								}
							}
						}
					}
				else if(isset($_GET['obj']) && $_GET['obj']=='enligne')
					{
					echo '<h3 style="text-align:center;">'.__('Online now','rencontre').'</h3>';
					$tab=''; $d=$rencDiv['basedir'].'/session/';
					if($dh=opendir($d))
						{
						while(($file=readdir($dh))!==false)
							{
							if($file!='.' && $file!='..' && (filemtime($d.$file)>time()-180)) $tab.="'".basename($file, ".txt")."',";
							}
						closedir($dh);
						}
					// Selection par le sex
					$zsex = strip_tags($_GET['zsex']);
					if(strpos($zsex,')')===false)
						{
						$sexQuery = " AND R.i_sex=".$zsex." ";
						if(!isset($rencCustom['sex'])) $sexQuery .= " AND R.i_zsex".((strip_tags($_GET['homo']))?'='.$zsex:'!='.$zsex)." ";
						}
					else
						{
						$sexQuery = " AND R.i_sex IN ".$zsex." ";
						}
					$q = $wpdb->get_results("SELECT 
							R.user_id,
							R.i_zsex,
							R.c_zsex,
							R.i_zage_min,
							R.i_zage_max,
							R.i_zrelation,
							R.c_zrelation,
							P.t_annonce,
							P.t_action 
						FROM 
							".$wpdb->prefix."rencontre_users R, 
							".$wpdb->prefix."rencontre_users_profil P
						WHERE 
							R.user_id IN (".substr($tab,0,-1).") 
							".$sexQuery."
							AND R.user_id!=".$mid." 
							AND P.user_id=R.user_id 
							AND R.i_status=0 
						LIMIT ".($pagine*(isset($rencOpt['limit'])?$rencOpt['limit']:10)).", ".((isset($rencOpt['limit'])?$rencOpt['limit']:10)+1)); // LIMIT indice du premier, nombre de resultat
					if($wpdb->num_rows<=(isset($rencOpt['limit'])?$rencOpt['limit']:10)) $suiv=0;
					else array_pop($q); // supp le dernier ($rencOpt['limit']+1) qui sert a savoir si page suivante
					}
				if(!empty($rencCustom['searchAd'])) { ?>
				
			<div class="rencBox">
				<?php }
				if($q) foreach($q as $u)
					{
					$u->blocked = new StdClass();
					$u->blocked->me = RencontreWidget::f_etat_bloque1($u->user_id,$u->t_action); // je suis bloque ?
					$title = array(
						"send"=>"",
						"smile"=>"",
						"profile"=>""
						);
					$disable = array(
						"send"=>0,
						"smile"=>0,
						"profile"=>0
						);
					$onClick = array(
						"send"=>"document.forms['rencMenu'].elements['renc'].value='write';document.forms['rencMenu'].elements['id'].value='".$u->user_id."';document.forms['rencMenu'].submit();",
						"smile"=>"document.forms['rencMenu'].elements['renc'].value='sourire';document.forms['rencMenu'].elements['id'].value='".$u->user_id."';document.forms['rencMenu'].submit();",
						"profile"=>"document.forms['rencMenu'].elements['renc'].value='card';document.forms['rencMenu'].elements['id'].value='".$u->user_id."';document.forms['rencMenu'].submit();"
						);
					$u->looking = '';
					$u->forwhat = '';
					if(isset($u->dataction)) $u->date = substr($u->dataction,8,2).'.'.substr($u->dataction,5,2).'.'.substr($u->dataction,0,4);
					else $u->date = '';
					if(isset($u->d_session) && substr($u->d_session,0,4)!=0) $u->online = RencontreWidget::format_date($u->d_session);
					else $u->online = '';
					if(!empty($rencOpt['onlyphoto']) && !$mephoto) $u->hidephoto = 1;
					else $u->hidephoto = 0;
					//
					$searchAdd1 = ''; // not in quick search
					//
					if($u->i_zsex!=99)
						{ 
						if(isset($rencOpt['iam'][$u->i_zsex])) $u->looking = $rencOpt['iam'][$u->i_zsex];
						if(isset($rencOpt['for'][$u->i_zrelation])) $u->forwhat = $rencOpt['for'][$u->i_zrelation];
						}
					else
						{
						// looking
						$a = explode(',', $u->c_zsex);
						$as = '';
						foreach($a as $a1) if(isset($rencOpt['iam'][$a1])) $as .= $rencOpt['iam'][$a1] . ', ';
						$u->looking = substr($as,0,-2);
						// forwhat
						$a = explode(',', $u->c_zrelation);
						$as = '';
						foreach($a as $a1) if(isset($rencOpt['for'][$a1])) $as .= $rencOpt['for'][$a1] . ', ';
						$u->forwhat = substr($as,0,-2);
						}
					// Send a message - Smile - Profile	
					// 1. Send a message
					$ho = false; 
					if($u->blocked->me || (isset($rencOpt['fastreg']) && $rencOpt['fastreg']>1) || $rencBlock || $pacam) $disable['send'] = 1;
					else if(has_filter('rencSendP', 'f_rencSendP')) $ho = apply_filters('rencSendP', $ho);
					if($ho)
						{
						$disable['send'] = 1;
						$title['send'] = $ho;
						}
					// 2. Smile
					if(!isset($rencCustom['smile']))
						{
						$ho = false; 
						if($u->blocked->me || (isset($rencOpt['fastreg']) && $rencOpt['fastreg']>1) || $rencBlock) $disable['smile'] = 1;
						else if(has_filter('rencSmileP', 'f_rencSmileP')) $ho = apply_filters('rencSmileP', $ho);
						if($ho)
							{
							$disable['smile'] = 1;
							$title['smile'] = $ho;
							}
						}
					else $disable['smile'] = 1; // securite
					// 3. Profile
						// empty
					// ****** TEMPLATE ********
					if(file_exists(get_stylesheet_directory().'/templates/rencontre_search_result.php')) include(get_stylesheet_directory().'/templates/rencontre_search_result.php');
					else include(dirname( __FILE__ ).'/../templates/rencontre_search_result.php');
					// ************************
					}
				if(!empty($rencCustom['searchAd'])) { ?>

			</div><!-- .rencBox -->
				<?php }
				if($pagine||$suiv)
					{
					echo '<div class="rencPagine">';
					if(($pagine+0)>0) echo "<a href=\"javascript:void(0)\" onClick=\"document.forms['rencPagine'].elements['pagine'].value=".$pagine."-1;document.forms['rencPagine'].submit();\">".__('Previous page','rencontre')."</a>";
					for($v=max(0, $pagine-4); $v<$pagine; ++$v)
						{
						echo "<a href=\"javascript:void(0)\" onClick=\"document.forms['rencPagine'].elements['pagine'].value='".$v."';document.forms['rencPagine'].submit();\">".$v."</a>";
						}
					echo "<span>".$pagine."</span>";
					if($suiv) echo "<a href=\"javascript:void(0)\" onClick=\"document.forms['rencPagine'].elements['pagine'].value=".$pagine."+1;document.forms['rencPagine'].submit();\">".__('Next Page','rencontre')."</a>";
					echo '</div>';
					}
				?>
				</div>
			<?php }
			//
			// 7. Partie recherche plus
			if(strstr($_SESSION['rencontre'],'gsearch'))
				{
				?> 
				
				<div class="<?php echo ((isset($rencCustom['side']) && $rencCustom['side'])?'pleineBox':'grandeBox left'); ?>">
					<div id="rencTrouve">
					<?php
					$ho = false; if(has_filter('rencSearchP', 'f_rencSearchP')) $ho = apply_filters('rencSearchP', $ho);
					if(!$ho) RencontreWidget::f_cherchePlus($mid); ?>
					
					</div><!-- #rencTrouve -->
				</div><!-- .grandeBox .left -->
			<?php }
			//
			// 8. Messagerie
			if(strstr($_SESSION['rencontre'],'msg') && !$rencBlock && (empty($rencOpt['fastreg']) || $rencOpt['fastreg']<2))
				{ ?>
				
				<div class="<?php echo (!empty($rencCustom['side'])?'pleineBox':'grandeBox left'); ?>">
				<?php if(isset($_POST['msg']) && $_POST['msg']=='msgdel' && !empty($_POST['id']))
					{
					$all = $wpdb->get_var("SELECT user_login FROM ".$wpdb->prefix."users WHERE ID=".$_POST['id']." LIMIT 1");
					RencontreWidget::f_suppMsg($current_user->user_login,$all);
					}
				if(!empty($_POST["contenu"]))
					{
					echo '<em id="rencSend" style="color:red">';
					$ho = false; if(has_filter('rencAnswerP', 'f_rencAnswerP')) $ho = apply_filters('rencAnswerP', $ho);
					if(!$ho && !rencistatus($istatus,1) && !$pacam)
						{
						echo RencontreWidget::f_envoiMsg($current_user->user_login);
						}
					else if(rencistatus($istatus,1)) echo __('Not sent','rencontre').'.&nbsp;'.__('You are no longer allowed to send messages.','rencontre');
					else if($pacam) echo __('Not sent','rencontre').'.&nbsp;'.__('You should complete your profile and add a photo to send messages.','rencontre');
					else _e('Not sent','rencontre');
					echo '</em><script>window.setTimeout(\'document.getElementById("rencSend").innerHTML=""\',3000);</script>';
					}
				$out = $wpdb->get_results("SELECT 
						M.id,
						M.sender,
						M.recipient,
						M.date,
						M.read,
						U.ID
					FROM
						".$wpdb->prefix."rencontre_msg M,
						".$wpdb->prefix."users U
					WHERE 
						U.user_login=M.sender and
						M.recipient='".$current_user->user_login."' and 
						M.deleted!=1"); // delete=1 : supp par dest
				$in = $wpdb->get_results("SELECT 
						M.id,
						M.sender,
						M.recipient,
						M.date,
						M.read,
						U.ID
					FROM
						".$wpdb->prefix."rencontre_msg M,
						".$wpdb->prefix."users U
					WHERE 
						U.user_login=M.recipient and
						M.sender='".$current_user->user_login."' and 
						M.deleted!=2"); // delete=2 : supp par writter
				$q1 = array_merge($out,$in);
				usort($q1, function($a,$b){return strcmp($b->date,$a->date);});
				$a = ','; $inbox = array();
				foreach($q1 as $k=>$v)
					{ // only one line by sender
					if($v->sender!=$current_user->user_login && strpos($a,','.$v->sender.',')===false)
						{
						$a .= $v->sender.',';
						$q1[$k]->member = $v->sender;
						$v->date = RencontreWidget::format_dateTime($v->date);
						$v->type = 'msgin';
						$inbox[] = $v;
						}
					else if($v->recipient!=$current_user->user_login && strpos($a,','.$v->recipient.',')===false)
						{
						$a .= $v->recipient.',';
						$q1[$k]->member = $v->recipient;
						$v->date = RencontreWidget::format_dateTime($v->date);
						$v->type = 'msgout';
						$inbox[] = $v;
						}
					}
				$hoAns = false; if(has_filter('rencAnswerP', 'f_rencAnswerP')) $hoAns = apply_filters('rencAnswerP', $hoAns);
				$onClick = array(
					'look'=>"f_voir_msg(id,'".admin_url("admin-ajax.php")."','".$current_user->user_login."','".$hoAns."');",
					'del'=>"document.forms['formEcrire'].elements['renc'].value='msg';document.forms['formEcrire'].elements['msg'].value='msgdel';document.forms['formEcrire'].elements['id'].value=id;document.forms['formEcrire'].submit();"
					);
				$u0 = new StdClass();
				$u0->user_login = $current_user->user_login;
				// ****** TEMPLATE ********
				if(file_exists(get_stylesheet_directory().'/templates/rencontre_message inbox.php')) include(get_stylesheet_directory().'/templates/rencontre_message_inbox.php');
				else include(dirname( __FILE__ ).'/../templates/rencontre_message_inbox.php');
				// ************************
				?>
				
				</div><!-- <?php echo (!empty($rencCustom['side'])?'.pleineBox':'.grandeBox .left'); ?> -->
			<?php }
			//
			// 9. Ecrire message
			if(strstr($_SESSION['rencontre'],'write') && !$rencBlock && (empty($rencOpt['fastreg']) || $rencOpt['fastreg']<2))
				{ ?>
				
				<div class="<?php echo ((!empty($rencCustom['side']))?'pleineBox':'grandeBox left'); ?>">
				<?php if(!empty($rencidfm)) $id = substr($rencidfm,1);
				else if(isset($_POST['msg']) && isset($_POST['id'])) $id = strip_tags($_POST["id"]);
				else if(!empty($_GET['id']) && $_GET['id']!=$mid) $id = $_GET['id'];
				else $id = 0;
				if($id)
					{
					$u = $wpdb->get_row("SELECT
							U.user_login,
							R.i_photo
						FROM 
							".$wpdb->prefix."users U,
							".$wpdb->prefix."rencontre_users R
						WHERE
							U.ID='".$id."' and
							R.user_id=U.ID
						LIMIT 1
						");
					$u->user_id = $id;
					$u->photo = $rencDiv['baseurl'].'/portrait/'.floor(($u->i_photo)/10000).'/'.Rencontre::f_img((floor(($u->i_photo)/10)*10).'-mini').'.jpg?r='.rand();
					$onClick = array(
						'profile'=>"document.forms['rencMenu'].elements['renc'].value='card';document.forms['rencMenu'].elements['id'].value='".$id."';document.forms['rencMenu'].submit();",
						'send'=>"document.forms['formEcrire'].elements['renc'].value='msg';document.forms['formEcrire'].elements['id'].value='".$id."';document.forms['formEcrire'].submit();"
						);
					$u0 = new StdClass();
					$u0->user_login = $current_user->user_login;
					// ****** TEMPLATE ********
					if(file_exists(get_stylesheet_directory().'/templates/rencontre_message write.php')) include(get_stylesheet_directory().'/templates/rencontre_message_write.php');
					else include(dirname( __FILE__ ).'/../templates/rencontre_message_write.php');
					// ************************
					}
				?>
				
				</div><!-- .<?php echo (!empty($rencCustom['side'])?'pleineBox':'grandeBox'); ?> .left -->
			<?php }
			//
			// 10. Compte
			if(strstr($_SESSION['rencontre'],'account'))
				{
				?> 
				
				<div class="<?php echo ((isset($rencCustom['side']) && $rencCustom['side'])?'pleineBox':'grandeBox left'); ?>">
					<div class="rencCompte rencBox">
					<?php RencontreWidget::f_compte($mid); ?>
					</div>
				</div>
			<?php } 
			//
			// 11. Custom Page
			else if(strstr($_SESSION['rencontre'],'custom1'))
				{
				// ****** TEMPLATE ********
				if(file_exists(get_stylesheet_directory().'/templates/rencontre_custom_page1.php')) include(get_stylesheet_directory().'/templates/rencontre_custom_page1.php');
				// ************************
				}
			else if(strstr($_SESSION['rencontre'],'custom2'))
				{
				// ****** TEMPLATE ********
				if(file_exists(get_stylesheet_directory().'/templates/rencontre_custom_page2.php')) include(get_stylesheet_directory().'/templates/rencontre_custom_page2.php');
				// ************************
				}
			//
			// OTHER
			if(isset($_GET['rencidfm']) && $_GET['rencidfm']=='rencfastreg')
				{ ?>
				<div id="rencFantome">
					<div class="rencFantome">
						<span onClick="f_fantome();"><?php _e('Close','rencontre');?></span>
						<?php _e('Your email is validated','rencontre'); ?>
					</div>
				</div>
				<?php }
			else if(!$fantome && !isset($_COOKIE["rencfantome"]) && !current_user_can("administrator"))
				{
				if(isset($rencOpt['fastreg']) && $rencOpt['fastreg']>1)
					{ ?>
				<div id="rencFantome">
					<div class="rencFantome">
					<span onClick="f_fantome();"><?php _e('Close','rencontre');?></span>
					<?php printf( __('Welcome to %s. You will stay hidden without interaction to other members as long as your account is not completed.','rencontre'), get_bloginfo('name')); 
					if($rencOpt['fastreg']>2) echo '&nbsp;'.__('You have 24 hours to confirm your email.','rencontre'); ?>
					</div>
				</div>
				<?php $ho = false; if(has_filter('rencAdwC', 'f_rencAdwC') && $rencOpt['fastreg']>2) $ho = apply_filters('rencAdwC', $ho); if($ho) echo $ho; ?>
				<?php } 
				else 
					{ ?>
				<div id="rencFantome">
					<div class="rencFantome">
					<span onClick="f_fantome();"><?php _e('Close','rencontre');?></span>
					<?php if(!isset($rencCustom['empty']) || !isset($rencCustom['emptyText']) || $rencCustom['emptyText']=='') _e('Your profile is empty. To take advantage of the site and being more visible, thank you to complete it.','rencontre');
					else echo stripslashes($rencCustom['emptyText']); ?>
					</div>
				</div><?php 
					}
				}
			} ?>
			
			<div style="clear:both;">&nbsp;</div>
			<div id="rencAdsB" class="rencAds">
			<?php $ho = false; if(has_filter('rencAdsBP', 'f_rencAdsBP')) $ho = apply_filters('rencAdsBP', $ho); if($ho) echo $ho; ?>
			</div><!-- .rencAds -->
		</div><!-- #widgRenc -->
		<?php
		}
//
// *************** FUNCTION ********************
//
	static function suppImg($im,$id)
		{
		// entree : nom de la photo (id * 10 + 1 ou 2 ou 3...)
		global $rencDiv;
		$r = $rencDiv['basedir'].'/portrait/'.floor($id/1000).'/';
		$a = array();
		$a[] = Rencontre::f_img($im) . '.jpg';
		$a[] = Rencontre::f_img($im.'-mini') . '.jpg';
		$a[] = Rencontre::f_img($im.'-grande') . '.jpg';
		$a[] = Rencontre::f_img($im.'-libre') . '.jpg';
		foreach($a as $b) if(file_exists($r.$b)) unlink($r.$b);
		global $wpdb;
		$q = $wpdb->get_var("SELECT i_photo FROM ".$wpdb->prefix."rencontre_users WHERE user_id='".$id."' LIMIT 1");
		if(floor($q/10)*10==$q) $p=0; // plus de photo
		else $p=$q-1;
		$wpdb->update($wpdb->prefix.'rencontre_users', array('i_photo'=>$p), array('user_id'=>$id));
		$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('d_modif'=>current_time("mysql")), array('user_id'=>$id));
		$c=0;
		for($v=$im; $v<$q; ++$v)
			{
			rename($r.Rencontre::f_img(($v+1)).'.jpg', $r.Rencontre::f_img($v).'.jpg');
			rename($r.Rencontre::f_img(($v+1).'-mini').'.jpg', $r.Rencontre::f_img($v.'-mini').'.jpg');
			rename($r.Rencontre::f_img(($v+1).'-grande').'.jpg', $r.Rencontre::f_img($v.'-grande').'.jpg');
			rename($r.Rencontre::f_img(($v+1).'-libre').'.jpg', $r.Rencontre::f_img($v.'-libre').'.jpg');
			}
		if(has_filter('rencBlurDelP', 'f_rencBlurDelP'))
			{
			$ho = new StdClass();
			$ho->id = $id;
			$ho->v = $im - ($id * 10);
			$ho->rename = $q;
			apply_filters('rencBlurDelP', $ho);
			}
		}
	//
	static function plusImg($nim,$id,$rot=0)
		{
		// entree : $s->i_photo (id * 10 + nombre de photo)
		global $rencDiv;
		$r = $rencDiv['basedir'].'/tmp/';
		if(!is_dir($r)) mkdir($r);
		if(strpos($nim,'|')===false)
			{
			if($nim==0) $p=$id*10; // premiere photo
			else $p=$nim+1;
			$cible = $r . basename($_FILES['plusPhoto']['tmp_name']);
			if(move_uploaded_file($_FILES['plusPhoto']['tmp_name'], $cible)) 
				{
				RencontreWidget::f_photo($p,$cible,$rot);
				global $wpdb;
				$wpdb->update($wpdb->prefix.'rencontre_users', array('i_photo'=>$p), array('user_id'=>$id));
				$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('d_modif'=>current_time("mysql")), array('user_id'=>$id));
				if(file_exists($cible)) unlink($cible);
				}
			else echo "rate";
			}
		else // FB
			{
			$a = explode('|', $nim);
			if($a[0]==0) $p=$id*10; // premiere photo
			else $p=$a[0]+1;
			$cible = $r.$id.'.jpg';
			file_put_contents($cible, file_get_contents($a[1]));
			RencontreWidget::f_photo($p,$cible,$rot);
			global $wpdb;
			$wpdb->update($wpdb->prefix.'rencontre_users', array('i_photo'=>$p), array('user_id'=>$id));
			$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('d_modif'=>current_time("mysql")), array('user_id'=>$id));
			if(file_exists($cible)) unlink($cible);
			}
		}
	//
	static function suppImgAll($id,$upd=true)
		{
		// entree : id
		global $rencDiv;
		$r = $rencDiv['basedir'].'/portrait/'.floor($id/1000).'/';
		for($v=0;$v<9;++$v)
			{
			$a = array();
			$a[] = Rencontre::f_img($id.$v) . '.jpg';
			$a[] = Rencontre::f_img($id.$v.'-mini') . '.jpg';
			$a[] = Rencontre::f_img($id.$v.'-grande') . '.jpg';
			$a[] = Rencontre::f_img($id.$v.'-libre') . '.jpg';
			foreach($a as $b) if(file_exists($r.$b)) unlink($r.$b);
			if(has_filter('rencBlurDelP', 'f_rencBlurDelP'))
				{
				$ho = new StdClass();
				$ho->id = $id;
				$ho->v = $v;
				$ho->rename = false;
				apply_filters('rencBlurDelP', $ho);
				}
			}
		global $wpdb;
		if($upd) $wpdb->update($wpdb->prefix.'rencontre_users', array('i_photo'=>0), array('user_id'=>$id));
		}
	//
	static function sauvProfil($in,$id)
		{
		// entree : Sauvegarde du profil
		// sortie bdd : [{"i":10,"v":"Sur une ile deserte avec mon amoureux."},{"i":35,"v":0},{"i":53,"v":[0,4,6]}]
		$u = "";
		if($in) foreach($in as $k=>$v) 
			{
			switch ($v[0])
				{
				case 1:
					if(!empty($_POST['text'.$k])) $u .= '{"i":'.$k.',"v":"'.str_replace('"','',strip_tags(stripslashes($_POST['text'.$k]))).'"},';
				break;
				case 2:
					if(!empty($_POST['area'.$k])) $u .= '{"i":'.$k.',"v":"'.str_replace('"','',strip_tags(stripslashes($_POST['area'.$k]))).'"},';
				break;
				case 3:
					if(!empty($_POST['select'.$k])) $u .= '{"i":'.$k.',"v":'.(strip_tags($_POST['select'.$k]-1)).'},';
				break;
				case 4:
					if(!empty($_POST['check'.$k]))
						{
						$u .= '{"i":'.$k.',"v":[';
						foreach($_POST['check'.$k] as $r) $u .= $r.',';
						$u = substr($u, 0, -1).']},';
						}
				break;
				case 5:
					if(!empty($_POST['ns'.$k])) $u .= '{"i":'.$k.',"v":'.(strip_tags($_POST['ns'.$k]-1)).'},';
				break;
				}
			}
		global $wpdb;
		if(strlen(strip_tags(stripslashes($_POST['titre'])))<2 && strlen(strip_tags(stripslashes($_POST['annonce'])))>10)
			{
			$tit = substr(strip_tags(stripslashes($_POST['annonce'])),0,50);
			$tit = trim(preg_replace('/\s\s+/', ' ', $tit));
			preg_match('`\w(?:[-_.]?\w)*@\w(?:[-_.]?\w)*\.(?:[a-z]{2,4})`', $tit, $m);
			$m[0] = (isset($m[0])?$m[0]:'');
			$tit = str_replace(array($m[0]), array(''), $tit);
			$tit = str_replace(', ', ',', $tit); $tit = str_replace(',', ', ', $tit);
			$tit = strtr($tit, "0123456789#(){[]}", ".................");
			$tit = substr($tit,0,25).'...';
			}
		else $tit = strip_tags(stripslashes($_POST['titre']));
		$wpdb->update($wpdb->prefix.'rencontre_users_profil',
			array('d_modif'=>current_time("mysql"),
				't_titre'=>$tit,
				't_annonce'=>strip_tags(stripslashes($_POST['annonce'])),
				't_profil'=>'['.substr($u, 0, -1).']'),
			array('user_id'=>$id));
		}
	//
	static function f_photo($im,$rim,$rot=0)
		{
		// im : user_id *10 + numero de photo a partir de 0
		global $rencOpt; global $rencDiv;
		$r = $rencDiv['basedir'].'/portrait/'.floor($im/10000);
		if($rot) $rot = floor((((floatval($rot)+45)/360)-floor((floatval($rot)+45)/360))*4)*(-90);
		if(!is_dir($r)) mkdir($r);
		$sim = getimagesize($rim);
		$exif = exif_read_data($rim);
		if(isset($exif['Orientation']))
			{
			if($exif['Orientation']==3) $rot -= 180; // 180
			else if($exif['Orientation']==6) $rot -= 90; // 90
			else if($exif['Orientation']==8) $rot -= 270; // 270
			if($rot+360<0) $rot += 360;
			}
		if($sim[2]==2 || $sim[2]==3)
			{
			if($sim[2]==2) $in = imagecreatefromjpeg($rim); // jpg
			if($sim[2]==3) $in = imagecreatefrompng($rim); // png
			if($rot) $in = imagerotate($in, $rot, 0);
			if(abs($rot)==90 || abs($rot)==270) // permut H & V
				{
				$a=$sim[1];
				$sim[1]=$sim[0];
				$sim[0]=$a;
				}
			$wim=$sim[0];
			$him=$sim[1];
			if($sim[1]/$sim[0]>.75) // verticale
				{
				if($sim[1]>480)
					{
					$wim=($sim[0]/$sim[1]*480);
					$him=480;
					}
				}
			else // horizontale
				{
				if($sim[0]>640)
					{
					$him=($sim[1]/$sim[0]*640);
					$wim=640;
					}
				}
			if($sim[1]/$sim[0]>1) // position pour decoupe carre
				{
				$himi=($sim[1]-$sim[0])/8;
				$wimi=0;
				$carre=$sim[0];
				}
			else
				{
				$wimi=($sim[0]-$sim[1])/2;
				$himi=0;
				$carre=$sim[1];
				} 
			if($sim[1]/$sim[0]>(108/141)) // verticale ou leger horizontale
				{
				$hi4=($sim[1]-($sim[0])*108/141)/4;
				$wi4=0;
				$wf4=$sim[0];
				$hf4=$wf4*108/141;
				}
			else // tres horizontale
				{
				$wi4=($sim[0]-($sim[1]*141/108))/2;
				$hi4=0;
				$hf4=$sim[1];
				$wf4=$hf4*141/108;
				}
			$out1 = imagecreatetruecolor ($wim, $him); // max : 640x480
			$out2 = imagecreatetruecolor (60, 60);
			$out3 = imagecreatetruecolor (250, 250);
			$out4 = imagecreatetruecolor (141, 108);
			imagecopyresampled ($out1, $in, 0, 0, 0, 0, $wim, $him, $sim[0], $sim[1]); 
			imagecopyresampled ($out2, $in, 0, 0, $wimi, $himi, 60, 60, $carre, $carre); 
			imagecopyresampled ($out3, $in, 0, 0, $wimi, $himi, 250, 250, $carre, $carre); 
			imagecopyresampled ($out4, $in, 0, 0, $wi4, $hi4, 141, 108, $wf4, $hf4);
			// imagecopyresampled(sortie, entree, position sur sortie X, Y, position entree X, Y, larg haut sur sortie, larg haut sur entree)
			$imco = (!empty($rencOpt['imcopyright'])?$rencOpt['imcopyright']:0);
			$txco = (!empty($rencOpt['txtcopyright'])?$rencOpt['txtcopyright']:'');
			imagejpeg(RencontreWidget::f_imcopyright($out1,$imco,$txco), $r."/".Rencontre::f_img($im).".jpg", 75);
			imagejpeg(RencontreWidget::f_imcopyright($out2,$imco,$txco), $r."/".Rencontre::f_img($im."-mini").".jpg", 75);
			imagejpeg(RencontreWidget::f_imcopyright($out3,$imco,$txco), $r."/".Rencontre::f_img($im."-grande").".jpg", 75);
			imagejpeg(RencontreWidget::f_imcopyright($out4,$imco,$txco), $r."/".Rencontre::f_img($im."-libre").".jpg", 75);
			if(has_filter('rencBlurCreaP', 'f_rencBlurCreaP'))
				{
				$ho = new StdClass();
				$ho->im = $im;
				$ho->out1 = $out1;
				$ho->out2 = $out2;
				$ho->out3 = $out3;
				$ho->out4 = $out4;
				apply_filters('rencBlurCreaP', $ho);
				}
			imagedestroy($in); imagedestroy($out1); imagedestroy($out2); imagedestroy($out3); imagedestroy($out4);
			}
		if(has_filter('rencModerP', 'f_rencModerP')) apply_filters('rencModerP', $im);
		}
	//
	static function f_imcopyright($imc,$right,$txtc)
		{
		if($right)
			{
			$sx = imagesx($imc);
			$sy = imagesy($imc);
			if($txtc=="") $Text=site_url();
			else $Text=$txtc;
			if(current_user_can("administrator")) $Font="../wp-content/plugins/rencontre/inc/arial.ttf";
			else $Font="wp-content/plugins/rencontre/inc/arial.ttf";
			$FontColor = imagecolorallocate($imc,255,255,255);
			$FontShadow = imagecolorallocate($imc,0,0,0);
			if($right=="2") $Rotation = -30;
			else $Rotation = 30;
			/* Make a copy image */
			$OriginalImage = imagecreatetruecolor($sx,$sy);
			imagecopy($OriginalImage,$imc,0,0,0,0,$sx,$sy);
			/* Iterate to get the size up */
			$FontSize=1;
			do
				{
				$FontSize *= 1.1;
				$Box = @imagettfbbox($FontSize,0,$Font,$Text);
				$TextWidth = abs($Box[4] - $Box[0]);
				$TextHeight = abs($Box[5] - $Box[1]);
				}
			while ($TextWidth < $sx*0.75);
			/*  Awkward maths to get the origin of the text in the right place */
			$x = $sx/2 - cos(deg2rad($Rotation))*$TextWidth/2;
			$y = $sy/2 + sin(deg2rad($Rotation))*$TextWidth/2 + cos(deg2rad($Rotation))*$TextHeight/2;
			/* Make shadow text first followed by solid text */
			imagettftext($imc,$FontSize,$Rotation,$x+4,$y+4,$FontShadow,$Font,$Text);
			imagettftext($imc,$FontSize,$Rotation,$x,$y,$FontColor,$Font,$Text);
			/* merge original image into version with text to show image through text */
			imagecopymerge($imc,$OriginalImage,0,0,0,0,$sx,$sy,85);
			}
		return $imc;
		}
	//
	static function f_pays($f='FR',$indif=0)
		{
		if(!$indif) echo '<option value="">- '.__('Immaterial','rencontre').' -</option>';
		if(strlen($f)!=2) $f = 'FR';
		global $wpdb; global $rencDiv;
		$q = $wpdb->get_results("SELECT
				c_liste_valeur,
				c_liste_iso
			FROM
				".$wpdb->prefix."rencontre_liste
			WHERE
				c_liste_categ='p' and
				c_liste_lang='".substr($rencDiv['lang'],0,2)."'
			ORDER BY c_liste_valeur
			");
		foreach($q as $r)
			{
			echo '<option value="'.$r->c_liste_iso.'"'.(($r->c_liste_iso==$f)?' selected':'').'>'.$r->c_liste_valeur.'</option>';
			}
		}
	//
	static function f_regionBDD($f=1,$g=0)
		{
		global $rencOpt;
		if(!$g) $g = (!empty($rencOpt['pays'])?$rencOpt['pays']:'FR');
		// Regions francaises par defaut
		// Copie de la version pour ajax dans rencontre.php
		echo '<option value="">- '.__('Immaterial','rencontre').' -</option>';
		if($f)
			{
			global $wpdb; 
			$q = $wpdb->get_results("SELECT
					id,
					c_liste_valeur
				FROM
					".$wpdb->prefix."rencontre_liste
				WHERE
					c_liste_iso='".$g."' and
					c_liste_categ='r'
				ORDER BY c_liste_valeur
				");
			foreach($q as $r)
				{
				echo '<option value="'.$r->id.'"'.(($r->c_liste_valeur==$f)?' selected':'').'>'.$r->c_liste_valeur.'</option>';
				}
			}
		}
	//
	static function f_miniPortrait($user_id, $t=0)
		{
		// entree : user_id
		// sortie : code HTML avec le mini portrait
		global $wpdb; global $rencDrap; global $rencDrapNom; global $rencDiv; global $rencCustom;
		$highlight = false; if(has_filter('rencHighlightP', 'f_rencHighlightP')) $highlight = apply_filters('rencHighlightP', $user_id);
		$u = $wpdb->get_row("SELECT
				U.ID,
				U.display_name,
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
				R.i_status=0 and 
				R.user_id=".$user_id." and 
				R.user_id=P.user_id and 
				R.user_id=U.ID
			LIMIT 1
			");
		if($u!=false)
			{
			$onClick = array(
				"profile"=>"document.forms['rencMenu'].elements['renc'].value='card';document.forms['rencMenu'].elements['id'].value='".$user_id."';document.forms['rencMenu'].submit();"
				);
			$title = array("thumb"=>"");
			if($t)
				{
				if(!isset($rencCustom['noph']) || empty($rencCustom['nophText'])) $title['thumb'] = addslashes(__("To be more visible and to view photos of other members, you should add one to your profile.","rencontre"));
				else $title['thumb'] = stripslashes($rencCustom['nophText']);
				}
			$u->online = RencontreWidget::f_enLigne($user_id);
			$u->miniPhoto = $rencDiv['baseurl'].'/portrait/'.floor(($user_id)/1000).'/'.Rencontre::f_img(($user_id*10).'-mini').'.jpg?r='.rand();
			// ****** TEMPLATE ********
			if(file_exists(get_stylesheet_directory().'/templates/rencontre_mini_portrait.php')) include(get_stylesheet_directory().'/templates/rencontre_mini_portrait.php');
			else include(dirname( __FILE__ ).'/../templates/rencontre_mini_portrait.php');
			// ************************
			}
		}
	//
	static function f_miniPortrait2($user_id)
		{
		// miniPortrait2 : pour la fenetre du TCHAT
		// entree : user_id
		// sortie : code HTML avec le mini portrait
		global $wpdb; global $rencDiv; global $rencCustom;
		$u = $wpdb->get_row("SELECT
				U.display_name,
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
				R.user_id=".$user_id." and 
				R.user_id=P.user_id and 
				R.user_id=U.ID
			LIMIT 1
			");
		if($u)
			{
			$rencDrap1 = $wpdb->get_var("SELECT c_liste_valeur FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='d' and c_liste_iso='".$u->c_pays."' LIMIT 1");
			$rencDrapNom1 = $wpdb->get_var("SELECT c_liste_valeur FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='p' and c_liste_iso='".$u->c_pays."' and c_liste_lang='".substr($rencDiv['lang'],0,2)."' LIMIT 1");
			echo substr($u->display_name,0,20)."|"; // pour f_tchat_dem : permet d'afficher le pseudo - memoire JS dans la variable 'ps' - limitation a 20 caracteres
			// ****** TEMPLATE ********
			if(file_exists(get_stylesheet_directory().'/templates/rencontre_mini_portrait_chat.php')) include(get_stylesheet_directory().'/templates/rencontre_mini_portrait.php');
			else include(dirname( __FILE__ ).'/../templates/rencontre_mini_portrait_chat.php');
			// ************************
			}
		}
	//
	static function f_enLigne($f)
		{
		global $rencDiv;
		if(is_file($rencDiv['basedir'].'/session/'.$f.'.txt') && time()-filemtime($rencDiv['basedir'].'/session/'.$f.'.txt')<180) return true;
		else return false;
		}
	//
	static function f_count_inbox($f)
		{
		// Message dans ma boite ?
		global $wpdb;
		$n = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."rencontre_msg M WHERE M.recipient='".$f."' and M.read=0 and M.deleted!=1");
		if($n) return '<span>'.$n.'</span>';
		else return;
		}
	//
	static function f_voirMsg($f,$a,$hoAns=false) // retour AJAX
		{
		// entree : $f = id message - $a = mon alias
		// read = 1 => lu
		// read = 2 => repondu
		global $wpdb;
		// $u : other than me (sender or recipient)
		$u = $wpdb->get_row("SELECT
				U.ID,
				U.user_login,
				R.i_photo
			FROM
				".$wpdb->prefix."users U,
				".$wpdb->prefix."rencontre_users R,
				".$wpdb->prefix."rencontre_msg M
			WHERE
				M.id='".$f."' and
				U.user_login!='".$a."' and
				(U.user_login=M.recipient or U.user_login=M.sender) and
				U.ID=R.user_id
			LIMIT 1
			");
		if(!$hoAns) $hoAns = RencontreWidget::f_etat_bloque1($u->ID);
		RencontreWidget::f_conversation($a,$u->ID,$u->user_login,$u->i_photo,$hoAns);
		}
	//
	static function f_conversation($a1,$id2,$a2,$ph2,$hoAns)
		{
		// 1:me, 2:you - a:alias, id: user_id, ph:i_photo
		// hoans=99 => deja dans le formulaire de reponse 
		// function used by template rencontre_message_write.php
		global $wpdb; global $rencDiv; global $rencCustom;
		$conversation = $wpdb->get_results("SELECT
				M.id,
				M.content,
				M.sender,
				M.recipient,
				M.date,
				M.read
			FROM ".$wpdb->prefix."rencontre_msg M 
			WHERE
				(M.recipient='".$a2."' and
				M.sender='".$a1."' and
				M.deleted!=2)
				or 
				(M.recipient='".$a1."' and
				M.sender='".$a2."' and
				M.deleted!=1)
			ORDER BY M.date DESC");
		if($conversation)
			{
			$u0 = new StdClass();
			$u0->user_login = $a1;
			$u0->login = substr($a1,0,20);
			$u = new StdClass();
			$u->user_login = $a2;
			$u->login = substr($a2,0,20);
			$u->i_photo = $ph2;
			$u->photo = $rencDiv['baseurl'].'/portrait/'.floor(($ph2)/10000).'/'.Rencontre::f_img((floor(($ph2)/10)*10).'-mini').'.jpg?r='.rand();
			$onClick = array(
				'profile'=>"document.forms['rencMenu'].elements['renc'].value='card';document.forms['rencMenu'].elements['id'].value='".$id2."';document.forms['rencMenu'].submit();",
				'write'=>"document.forms['formEcrire'].elements['renc'].value='write';document.forms['formEcrire'].elements['id'].value='".$id2."';document.forms['formEcrire'].submit();",
				'del'=>"document.forms['formEcrire'].elements['renc'].value='msg';document.forms['formEcrire'].elements['msg'].value='msgdel';document.forms['formEcrire'].elements['id'].value='".$id2."';document.forms['formEcrire'].submit();",
				'inbox'=>"document.forms['rencMenu'].elements['renc'].value='msg';document.forms['rencMenu'].submit();"
				);
			foreach($conversation as $k=>$m)
				{
				$conversation[$k]->date = RencontreWidget::format_dateTime($m->date);
				if($m->read==0 && $m->sender!=$a1) $wpdb->update($wpdb->prefix.'rencontre_msg', array('read'=>1), array('id'=>$m->id));
				}
			// ****** TEMPLATE ********
			if(file_exists(get_stylesheet_directory().'/templates/rencontre_message_conversation.php')) include(get_stylesheet_directory().'/templates/rencontre_message_conversation.php');
			else include(dirname( __FILE__ ).'/../templates/rencontre_message_conversation.php');
			// ************************
			}
		}
	//
	static function f_suppMsg($a,$all)
		{
		// entree : $a:alias moi
		// Destinataire supp => deleted=1 - si delete etait 2 => supp reel
		// Emmeteur supp => deleted=2 - si delete etait 1 => supp reel
		// $all : suppression de toute la conversation : alias autre
		global $wpdb;
		$q = $wpdb->get_results("SELECT M.id, M.sender, M.recipient, M.deleted
			FROM ".$wpdb->prefix."rencontre_msg M 
			WHERE
				(M.sender='".$a."' and M.recipient='".$all."') or
				(M.sender='".$all."' and M.recipient='".$a."') ");
		foreach($q as $r)
			{
			if($r->sender==$a) // mon msg
				{
				if($r->deleted==1) $wpdb->delete($wpdb->prefix.'rencontre_msg', array('id'=>$r->id)); // suppression reelle de mon msg
				else if($r->deleted==0) $wpdb->update($wpdb->prefix.'rencontre_msg', array('deleted'=>2), array('id'=>$r->id));
				}
			else if($r->recipient==$a) // msg recu
				{
				if($r->deleted==2) $wpdb->delete($wpdb->prefix.'rencontre_msg', array('id'=>$r->id)); // suppression reelle du msg re�u
				else if($r->deleted==0) $wpdb->update($wpdb->prefix.'rencontre_msg', array('deleted'=>1), array('id'=>$r->id));
				}
			}
		}
	//
	static function f_envoiMsg($f)
		{
		// entree : mon alias
		global $wpdb; global $rencDiv; global $current_user;
		$co = strip_tags(stripslashes($_POST["contenu"]));
		$a = $wpdb->get_var("SELECT user_login FROM ".$wpdb->prefix."users WHERE ID='".strip_tags($_POST["id"])."' LIMIT 1");
		$q = $wpdb->get_var("SELECT id FROM ".$wpdb->prefix."rencontre_msg 
			WHERE 
				content = '".addslashes($co)."' and 
				sender='".$f."' and 
				recipient='".$a."'
			LIMIT 1
			");
		if(!$q) // not already send
			{
			// count msg/day
			$p = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$current_user->ID."' LIMIT 1");
			$action = json_decode($p,true);
			if(isset($action['msg']['n']) && isset($action['msg']['d']) && $action['msg']['d']==date("z")) $action['msg']=array('d'=>date("z"),'n'=>($action['msg']['n']+1));
			else $action['msg']=array('d'=>date("z"),'n'=>1);
			$p = json_encode($action);
			$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('t_action'=>$p), array('user_id'=>$current_user->ID));
			// msg in db
			$wpdb->insert($wpdb->prefix.'rencontre_msg', array('content'=>$co, 'sender'=>$f, 'recipient'=>$a, 'date'=>current_time('mysql'), 'read'=>0, 'deleted'=>0));
			$wpdb->query("UPDATE ".$wpdb->prefix."rencontre_msg M
				SET M.read=2 
				WHERE M.sender='".$a."' and M.recipient='".$f."'
				ORDER BY M.id DESC 
				LIMIT 1 ");
			// memo pour mail CRON
			if(!is_dir($rencDiv['basedir'].'/portrait/cache/cron_liste/')) mkdir($rencDiv['basedir'].'/portrait/cache/cron_liste/');
			if(!file_exists($rencDiv['basedir'].'/portrait/cache/cron_liste/'.strip_tags($_POST["id"]).'.txt'))
				{
				$t=fopen($rencDiv['basedir'].'/portrait/cache/cron_liste/'.strip_tags($_POST["id"]).'.txt', 'w');
				fclose($t);
				}
			_e('Message sent','rencontre');
			}
		else _e('Not sent','rencontre');
		}
	//
	static function f_cherchePlus($f)
		{
		// formulaire de la recherche plus
		global $wpdb; global $rencOpt; global $rencCustom;
		$u0 = $wpdb->get_row("SELECT
				i_sex,
				i_zsex,
				c_zsex,
				e_lat,
				e_lon
			FROM
				".$wpdb->prefix."rencontre_users
			WHERE
				user_id='".$f."'
			LIMIT 1
			");
		$u0->ID = $f;
		$u0->zsex = (($u0->i_zsex!=99)?$u0->i_zsex:'('.substr($u0->c_zsex,1,-1).')');
		$u0->homo = (($u0->i_sex==$u0->i_zsex)?1:0);
		//
		$filtermap = false; if(has_filter('rencMapP', 'f_rencMapP')) $filtermap = apply_filters('rencMapP', $filtermap);
		if(!$filtermap && !empty($rencOpt['map']) && function_exists('wpGeonames') && (($u0->e_lon!=0 && $u0->e_lat!=0) || isset($rencCustom['country']))) $map = "gmapGeoname"; // GoogleMap With WP_GEONAMES plugin
		else if(!$filtermap && !empty($rencOpt['map']) && (($u0->e_lon!=0 && $u0->e_lat!=0) || isset($rencCustom['country']))) $map = "gmap"; // GoogleMap no WP_GEONAMES
		else $map = 0; // NO MAP
		$city = "";
		if($map)
			{
			$city .= "document.getElementById('renctrMap1').style.display='';document.getElementById('renctrMap2').style.display='';";
			$city .= "if(!rmap)f_cityMap(this.value,";
			if(isset($rencCustom['country'])) $city .= "'".(!empty($rencOpt['pays'])?$rencOpt['pays']:'FR')."',";
			else $city .= "document.getElementById('rencPays').options[document.getElementById('rencPays').selectedIndex].text,";
			$city .= "'0',1);"; 
			if($map=='gmapGeoname')
				{
				$city .= "f_city(this.value,'".admin_url('admin-ajax.php')."',";
				if(isset($rencCustom['country'])) $city .= "'".(!empty($rencOpt['pays'])?$rencOpt['pays']:'FR')."',";
				else $city .= "document.getElementById('rencPays').options[document.getElementById('rencPays').selectedIndex].value,";
				$city .= "1);";
				}
			}
		$hom = false; if(has_filter('rencNbSearchP', 'f_rencNbSearchP')) $hom = apply_filters('rencNbSearchP', $hom);
		$find = array(
			"class"=>($hom?" rencLiOff":""),
			"title"=>($hom?$hom:""));
		$onClick = array(
			"agemin"=>"f_min(parseInt(this.options[this.selectedIndex].value),'formTrouve','ageMin','ageMax');",
			"agemax"=>"f_max(parseInt(this.options[this.selectedIndex].value),'formTrouve','ageMin','ageMax');",
			"sizemin"=>"f_min(this.options[this.selectedIndex].value,'formTrouve','tailleMin','tailleMax');",
			"sizemax"=>"f_max(this.options[this.selectedIndex].value,'formTrouve','tailleMin','tailleMax');",
			"weightmin"=>"f_min(this.options[this.selectedIndex].value,'formTrouve','poidsMin','poidsMax');",
			"weightmax"=>"f_max(this.options[this.selectedIndex].value,'formTrouve','poidsMin','poidsMax');",
			"country"=>"f_region_select(this.options[this.selectedIndex].value,'<?php echo admin_url('admin-ajax.php'); ?>','regionSelect2');",
			"city"=>"onkeyup=\"".$city."\"",
			"validate"=>"f_cityOk();f_cityKm(document.getElementById('rencKm').value);",
			"find"=>"onClick=\"f_trouve('.$hom.');\"",
			);
		//
		$moreSearch1 = '';
		$ho = false; if(has_filter('rencProfilSP', 'f_rencProfilSP')) $ho = apply_filters('rencProfilSP', $ho); if($ho) $moreSearch1 .= $ho;
		$ho = false; if(has_filter('rencProfilOkP', 'f_rencProfilOkP')) $ho = apply_filters('rencProfilOkP', $ho);
		if($ho)
			{
			$moreSearch1 .= "\r\n".'<tr><td>'.__('Affinity with my profile','rencontre').'&nbsp;</td>';
			$moreSearch1 .= '<td colspan="2"><input type="checkbox" name="profil" value="1"></td></tr>';
			}
		$ho = false; if(has_filter('rencAstroOkP', 'f_rencAstroOkP') && !isset($rencCustom['born'])) $ho = apply_filters('rencAstroOkP', $ho);
		if($ho)
			{
			$moreSearch1 .= "\r\n".'<tr><td>'.__('Astrological affinity','rencontre').'&nbsp;</td>';
			$moreSearch1 .= '<td colspan="2"><input type="checkbox" name="astro" value="1"></td></tr>';
			}
		if($moreSearch1) $moreSearch1 .= "\r\n";
		//
		if(!strstr($_SESSION['rencontre'],'liste')) // nouvelle recherche
			{
			// ****** TEMPLATE ********
			if(file_exists(get_stylesheet_directory().'/templates/rencontre_search.php')) include(get_stylesheet_directory().'/templates/rencontre_search.php');
			else include(dirname( __FILE__ ).'/../templates/rencontre_search.php');
			// ************************
			if(!isset($rencCustom['place'])) echo '<script type="text/javascript" src="//maps.googleapis.com/maps/api/js?key='.(!empty($rencOpt['mapapi'])?$rencOpt['mapapi']:'').'&sensor=false"></script>';
			}
		else RencontreWidget::f_trouver(); // resultat apres Submit
		}
	//
	static function f_trouver()
		{
		// Resultat de la recherche plus
		global $wpdb; global $rencOpt; global $rencDiv; global $rencBlock; global $rencCustom; global $pacam;
		$hom = false; if(has_filter('rencNbSearchP', 'f_rencNbSearchP')) $hom = apply_filters('rencNbSearchP', $hom);
		if($hom) { echo $hom; return; }
		$ho = false; if(has_filter('rencWssP', 'f_rencWssP')) $ho = apply_filters('rencWssP', $ho);
		if($ho && isset($_GET['id'])) $mephoto = $wpdb->get_var("SELECT i_photo FROM ".$wpdb->prefix."rencontre_users WHERE user_id='".strip_tags($_GET['id'])."' LIMIT 1");
		else $mephoto = 1;
		$pagine = (isset($_GET['pagine'])?$_GET['pagine']:0);
		$suiv = 1;
		?> 
		
		<form name='rencPagine' method='get' action=''>
			<?php if(isset($rencOpt['page_id'])) echo '<input type="hidden" name="page_id" value="'.$rencOpt['page_id'].'" />'; ?>
			<input type='hidden' name='renc' value='liste' />
			<input type='hidden' name='pays' value='<?php echo (isset($_GET['pays'])?$_GET['pays']:''); ?>' />
			<input type='hidden' name='region' value='<?php echo (isset($_GET['region'])?$_GET['region']:''); ?>' />
			<input type='hidden' name='ville' value='<?php echo (isset($_GET['ville'])?$_GET['ville']:''); ?>' />
			<input type='hidden' name='gps' value='<?php echo (isset($_GET['gps'])?$_GET['gps']:''); ?>' />
			<input type='hidden' name='km' value='<?php echo (isset($_GET['km'])?$_GET['km']:''); ?>' />
			<input type='hidden' name='pseudo' value='<?php echo (isset($_GET['pseudo'])?$_GET['pseudo']:''); ?>' />
			<input type='hidden' name='zsex' value='<?php echo (isset($_GET['zsex'])?$_GET['zsex']:''); ?>' />
			<?php if(isset($_GET['z2sex'])) echo "<input type='hidden' name='z2sex' value='".$_GET['z2sex']."' />"; ?>
			<input type='hidden' name='homo' value='<?php echo (isset($_GET['homo'])?$_GET['homo']:''); ?>' />
			<input type='hidden' name='ageMin' value='<?php echo (isset($_GET['ageMin'])?$_GET['ageMin']:''); ?>' />
			<input type='hidden' name='ageMax' value='<?php echo (isset($_GET['ageMax'])?$_GET['ageMax']:''); ?>' />
			<input type='hidden' name='tailleMin' value='<?php echo (isset($_GET['tailleMin'])?$_GET['tailleMin']:''); ?>' />
			<input type='hidden' name='tailleMax' value='<?php echo (isset($_GET['tailleMax'])?$_GET['tailleMax']:''); ?>' />
			<input type='hidden' name='poidsMin' value='<?php echo (isset($_GET['poidsMin'])?$_GET['poidsMin']:''); ?>' />
			<input type='hidden' name='poidsMax' value='<?php echo (isset($_GET['poidsMax'])?$_GET['poidsMax']:''); ?>' />
			<input type='hidden' name='mot' value='<?php echo (isset($_GET['mot'])?$_GET['mot']:''); ?>' />
			<input type='hidden' name='photo' value='<?php echo (isset($_GET['photo'])?$_GET['photo']:''); ?>' />
			<input type='hidden' name='profil' value='<?php echo (isset($_GET['profil'])?$_GET['profil']:''); ?>' />
			<input type='hidden' name='astro' value='<?php echo (isset($_GET['astro'])?$_GET['astro']:''); ?>' />
			<input type='hidden' name='relation' value='<?php echo (isset($_GET['relation'])?$_GET['relation']:''); ?>' />
			<input type='hidden' name='id' value='<?php echo (isset($_GET['id'])?$_GET['id']:''); ?>' />
			<input type='hidden' name='pagine' value='<?php echo $pagine; ?>' />
			<?php $ho = false; if(has_filter('rencProfilSrP', 'f_rencProfilSrP')) $ho = apply_filters('rencProfilSrP', 1); if($ho) echo $ho; ?>
		</form>
		<?php
		$hoprofil = false; $hoastro = false;
		// Selection par le sex
		$zsex = strip_tags($_GET['zsex']); $sexQuery = '';
		if(strpos($zsex,')')===false)
			{
			if(!isset($_GET['z2sex'])) $sexQuery .= " and R.i_zsex".((strip_tags($_GET['homo']))?'='.$zsex:'!='.$zsex)." ";
			if(!isset($_GET['z2sex']) || $_GET['z2sex']!="") $sexQuery .=" and R.i_sex=".(isset($_GET['z2sex'])?strip_tags($_GET['z2sex']):$zsex);
			}
		else
			{
			if(isset($_GET['z2sex']) && $_GET['z2sex']!="") $sexQuery .=" and R.i_sex=".strip_tags($_GET['z2sex']);
			else if(!isset($_GET['z2sex'])) $sexQuery .=" and R.i_sex IN ".$zsex;
			}
		if($_GET['pseudo']) $s="SELECT 
				R.user_id,
				R.i_zsex,
				R.c_zsex,
				R.i_zage_min,
				R.i_zage_max,
				R.i_zrelation,
				R.c_zrelation,
				P.t_annonce,
				P.t_action 
			FROM 
				".$wpdb->prefix."rencontre_users_profil P,
				".$wpdb->prefix."rencontre_users R,
				".$wpdb->prefix."users U 
			WHERE 
				U.user_login LIKE '%".strip_tags($_GET['pseudo'])."%'
				".$sexQuery."
				and U.ID=R.user_id 
				and P.user_id=R.user_id 
				and R.i_status=0";
		else
			{
			$s="SELECT 
					U.user_login,
					R.user_id,
					".((isset($_GET['astro']) && strip_tags($_GET['astro']))?'R.d_naissance, ':'')."
					R.i_zsex,
					R.c_zsex,
					R.i_zage_min,
					R.i_zage_max,
					R.i_zrelation,
					R.c_zrelation,
					R.i_photo,
					R.e_lat,
					R.e_lon,
					R.d_session,
					P.t_annonce,
					".((isset($_GET['profil']) && strip_tags($_GET['profil']))?'P.t_profil, ':'')."
					P.t_action 
				FROM 
					".$wpdb->prefix."users U,
					".$wpdb->prefix."rencontre_users_profil P,
					".$wpdb->prefix."rencontre_users R 
				WHERE 
					U.ID=R.user_id 
					and R.i_status=0 
					and P.user_id=R.user_id
					and R.user_id!=".strip_tags($_GET['id']);
			$s .= $sexQuery;
			if(isset($_GET['ageMin']) && $_GET['ageMin']>(isset($rencCustom['agemin'])?intval($rencCustom['agemin']):18)) // pas de filtre si min
				{
				$zmin=date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"), date("Y")-strip_tags($_GET['ageMin'])));
				$s.=" and R.d_naissance<'".$zmin."'";
				}
			if(isset($_GET['ageMax']) && $_GET['ageMax'] && $_GET['ageMax']<(isset($rencCustom['agemax'])?intval($rencCustom['agemax']):99)) // pas de filtre si maw
				{
				$zmax=date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"), date("Y")-strip_tags($_GET['ageMax'])));
				$s.=" and R.d_naissance>'".$zmax."'";
				}
			if(isset($_GET['tailleMin']) && $_GET['tailleMin']>140) $s.=" and R.i_taille>='".strip_tags($_GET['tailleMin'])."'";
			if(isset($_GET['tailleMax']) && $_GET['tailleMax'] && $_GET['tailleMax']<220) $s.=" and R.i_taille<='".strip_tags($_GET['tailleMax'])."'";
			if(isset($_GET['poidsMin']) && $_GET['poidsMin']>140) $s.=" and R.i_poids>='".(strip_tags($_GET['poidsMin'])-100)."'";
			if(isset($_GET['poidsMax']) && $_GET['poidsMax'] && $_GET['poidsMax']<240) $s.=" and R.i_poids<='".(strip_tags($_GET['poidsMax'])-100)."'";
			if(isset($_GET['gps']) && $_GET['gps'] && $_GET['km'])
				{
				$gps = explode('|',strip_tags($_GET['gps']));
				if(isset($gps[1]))
					{
					$dlat = (strip_tags($_GET['km']) / 1.852 / 60);
					$dlon = (strip_tags($_GET['km']) / 1.852 / 60 / cos($gps[0] * 0.0174533));
					$s.=" and ((R.e_lat<".($gps[0]+$dlat)." and R.e_lat>".($gps[0]-$dlat)." and R.e_lon<".($gps[1]+$dlon)." and R.e_lon>".($gps[1]-$dlon).")";
					if($_GET['ville']) $s.=" or R.c_ville LIKE '".strip_tags($_GET['ville'])."'";
					$s .= ")";
					}
				}
			else if(isset($_GET['ville']) && $_GET['ville']) $s.=" and R.c_ville LIKE '".strip_tags($_GET['ville'])."'";
			if(isset($_GET['pays']) && $_GET['pays']) $s.=" and R.c_pays='".$_GET['pays']."'";
			if(isset($_GET['region']) && $_GET['region']) $s.=" and R.c_region LIKE '".addslashes($wpdb->get_var("SELECT c_liste_valeur FROM ".$wpdb->prefix."rencontre_liste WHERE id='".$_GET['region']."' LIMIT 1"))."'";
			if($_GET['mot']) $s.=" and (P.t_annonce LIKE '%".$_GET['mot']."%' or P.t_titre LIKE '%".strip_tags($_GET['mot'])."%')";
			if(isset($_GET['photo']) && $_GET['photo']=='1') $s.=" and R.i_photo>0";
			if(isset($_GET['relation']) && $_GET['relation']!='')
				{
				$s.=" and (R.i_zrelation='".strip_tags($_GET['relation'])."' or R.c_zrelation LIKE '%,".strip_tags($_GET['relation']).",%')";
				}
			if(isset($_GET['astro']) && $_GET['astro'] && has_filter('rencAstroOkP', 'f_rencAstroOkP')) $hoastro = apply_filters('rencAstroOkP', $hoastro);
			else if(isset($_GET['profil']) && $_GET['profil'] && has_filter('rencProfilOkP', 'f_rencProfilOkP')) $hoprofil = apply_filters('rencProfilOkP', $hoprofil);
			$ho = false; if(has_filter('rencProfilSrP', 'f_rencProfilSrP')) $ho = apply_filters('rencProfilSrP', 0); if($ho) $s.=$ho;
			}
		if(!$hoastro && !$hoprofil)
			{
			$s.=" ORDER BY R.d_session DESC, P.d_modif DESC LIMIT ".($pagine*(isset($rencOpt['limit'])?$rencOpt['limit']:10)).", ".((isset($rencOpt['limit'])?$rencOpt['limit']:10)+1); // LIMIT indice du premier, nombre de resultat
			$q = $wpdb->get_results($s);
			if($wpdb->num_rows<=(isset($rencOpt['limit'])?$rencOpt['limit']:10)) $suiv=0;
			else array_pop($q); // supp le dernier ($rencOpt['limit']+1) qui sert a savoir si page suivante
			}
		else
			{
			$q = array(); $c = 0; $suiv = 0;
			if($hoastro) $q1 = apply_filters('rencAstroP', $s); // full search - no pagination
			else if($hoprofil) $q1 = apply_filters('rencProfilP', $s);
			foreach($q1 as $r)
				{
				if($c>=($pagine*(isset($rencOpt['limit'])?$rencOpt['limit']:10))+(isset($rencOpt['limit'])?$rencOpt['limit']:10))
					{
					$suiv = 1;
					break;
					}
				else if($c>=($pagine*(isset($rencOpt['limit'])?$rencOpt['limit']:10))) $q[] = $r;
				++$c;
				}
			}
		if(isset($gps[1]))
			{
			echo '<div id="rencMap2" style="display:block;"></div>'."\r\n";
			echo '<script type="text/javascript" src="//maps.googleapis.com/maps/api/js?key='.(!empty($rencOpt['mapapi'])?$rencOpt['mapapi']:'').'&sensor=false"></script>'."\r\n";
			echo '<script type="text/javascript">var lat='.$gps[0].',lon='.$gps[1].',gps=[';
			if($q) foreach ($q as $k=>$r)
				{
				if($k) echo',';
				echo '['.$r->e_lat.','.$r->e_lon.',"'.$r->i_photo.'","'.$r->user_login.'","'.$r->user_id.'","'.(($r->i_photo)?Rencontre::f_img((($r->user_id)*10).'-mini'):0).'"]';
				}
			echo '];'."\r\n".'jQuery(document).ready(function(){f_mapCherche(gps,lat,lon,"'.$rencDiv['siteurl'].'");});</script>'."\r\n";
			}
		if(!empty($rencCustom['searchAd'])) echo '<div class="rencBox">';
		if($q) foreach($q as $u)
			{ 
			$u->blocked = new StdClass();
			$u->blocked->me = RencontreWidget::f_etat_bloque1($u->user_id,$u->t_action); // je suis bloque ?
			$title = array(
				"send"=>"",
				"smile"=>"",
				"profile"=>""
				);
			$disable = array(
				"send"=>0,
				"smile"=>0,
				"profile"=>0
				);
			$onClick = array(
				"send"=>"document.forms['rencMenu'].elements['renc'].value='write';document.forms['rencMenu'].elements['id'].value='".$u->user_id."';document.forms['rencMenu'].submit();",
				"smile"=>"document.forms['rencMenu'].elements['renc'].value='sourire';document.forms['rencMenu'].elements['id'].value='".$u->user_id."';document.forms['rencMenu'].submit();",
				"profile"=>"document.forms['rencMenu'].elements['renc'].value='card';document.forms['rencMenu'].elements['id'].value='".$u->user_id."';document.forms['rencMenu'].submit();"
				);
			$u->looking = '';
			$u->forwhat = '';
			$u->date = '';
			if(isset($u->d_session) && substr($u->d_session,0,4)!=0) $u->online = $u->online = RencontreWidget::format_date($u->d_session);
			else $u->online = '';
			if(!empty($rencOpt['onlyphoto']) && !$mephoto) $u->hidephoto = 1;
			else $u->hidephoto = 0;
			//
			$searchAdd1 = '';
			if($hoastro && $u->score)
				{ 				
				$searchAdd1 = '<div class="affinity">'.__('Astrological affinity','rencontre').'&nbsp;:&nbsp;<span>'.$u->score.' / 5</span>';
				$searchAdd1 .= '<img style="margin:-5px 0 0 5px;" src="'.plugins_url($hoastro.'/img/astro'.$u->score.'.png').'" alt="astro" /></div>';
				}
			else if($hoprofil && $u->score)
				{
				$searchAdd1 = '<div class="affinity">'.__('Affinity with my profile','rencontre').'&nbsp;:&nbsp;<span>'.$u->score.'</span>&nbsp;'.__('points','rencontre').'.</div>';
				}
			//
			if($u->i_zsex!=99)
				{ 
				if(isset($rencOpt['iam'][$u->i_zsex])) $u->looking = $rencOpt['iam'][$u->i_zsex];
				if(isset($rencOpt['for'][$u->i_zrelation])) $u->forwhat = $rencOpt['for'][$u->i_zrelation];
				}
			else
				{
				// looking
				$a = explode(',', $u->c_zsex);
				$as = '';
				foreach($a as $a1) if(isset($rencOpt['iam'][$a1])) $as .= $rencOpt['iam'][$a1] . ', ';
				$u->looking = substr($as,0,-2);
				// forwhat
				$a = explode(',', $u->c_zrelation);
				$as = '';
				foreach($a as $a1) if(isset($rencOpt['for'][$a1])) $as .= $rencOpt['for'][$a1] . ', ';
				$u->forwhat = substr($as,0,-2);
				}
			// Send a message - Smile - Profile	
			// 1. Send a message
			$ho = false; 
			if($u->blocked->me || (isset($rencOpt['fastreg']) && $rencOpt['fastreg']>1) || $rencBlock || $pacam) $disable['send'] = 1;
			else if(has_filter('rencSendP', 'f_rencSendP')) $ho = apply_filters('rencSendP', $ho);
			if($ho)
				{
				$disable['send'] = 1;
				$title['send'] = $ho;
				}
			// 2. Smile
			if(!isset($rencCustom['smile']))
				{
				$ho = false; 
				if($u->blocked->me || (isset($rencOpt['fastreg']) && $rencOpt['fastreg']>1) || $rencBlock) $disable['smile'] = 1;
				else if(has_filter('rencSmileP', 'f_rencSmileP')) $ho = apply_filters('rencSmileP', $ho);
				if($ho)
					{
					$disable['smile'] = 1;
					$title['smile'] = $ho;
					}
				}
			else $disable['smile'] = 1; // securite
			// 3. Profile
				// empty
			// ****** TEMPLATE ********
			if(file_exists(get_stylesheet_directory().'/templates/rencontre_search_result.php')) include(get_stylesheet_directory().'/templates/rencontre_search_result.php');
			else include(dirname( __FILE__ ).'/../templates/rencontre_search_result.php');
			// ************************
			}
		if(!empty($rencCustom['searchAd'])) echo '</div><!-- .rencBox -->';
		if($pagine||$suiv)
			{
			echo '<div class="rencPagine">';
			if(($pagine+0)>0) echo "<a href=\"javascript:void(0)\" onClick=\"document.forms['rencPagine'].elements['pagine'].value=".$pagine."-1;document.forms['rencPagine'].submit();\">".__('Previous page','rencontre')."</a>";
			for($v=max(0, $pagine-4); $v<$pagine; ++$v)
				{
				echo "<a href=\"javascript:void(0)\" onClick=\"document.forms['rencPagine'].elements['pagine'].value='".$v."';document.forms['rencPagine'].submit();\">".$v."</a>";
				}
			echo "<span>".$pagine."</span>";
			if($suiv) echo "<a href=\"javascript:void(0)\" onClick=\"document.forms['rencPagine'].elements['pagine'].value=".$pagine."+1;document.forms['rencPagine'].submit();\">".__('Next Page','rencontre')."</a>";
			echo '</div>';
			}
		}
	//
	static function f_registerMember($f,$g)
		{
		// $f : ID
		// $g : 1, 2, 3, OK
		global $wpdb; global $rencOpt;
		if(has_action('rencontre_registration')) do_action('rencontre_registration', $f, $g);
		else
			{
			if($g=="1")
				{
				if(isset($_POST['annee']) && isset($_POST['mois']) && isset($_POST['jour'])) $nais = $_POST['annee'].'-'.((strlen($_POST['mois'])<2)?'0'.$_POST['mois']:$_POST['mois']).'-'.((strlen($_POST['jour'])<2)?'0'.$_POST['jour']:$_POST['jour']);
				$wpdb->delete($wpdb->prefix.'rencontre_users', array('user_id'=>$f)); // suppression si existe deja
				$wpdb->delete($wpdb->prefix.'rencontre_users_profil', array('user_id'=>$f)); // suppression si existe deja
				$wpdb->insert($wpdb->prefix.'rencontre_users', array(
					'user_id'=>$f,
					'c_pays'=>(!empty($rencOpt['pays'])?$rencOpt['pays']:'FR'), // default - custom no localisation
					'i_sex'=>strip_tags($_POST['sex']),
					'd_naissance'=>(isset($nais)?strip_tags($nais):''),
					'i_taille'=>(isset($_POST['taille'])?strip_tags($_POST['taille']):''),
					'i_poids'=>(isset($_POST['poids'])?strip_tags($_POST['poids']):''),
					'd_session'=>current_time("mysql"),
					'i_photo'=>0));
				$wpdb->insert($wpdb->prefix.'rencontre_users_profil', array('user_id'=>$f, 'd_modif'=>current_time("mysql"),'t_titre'=>'', 't_annonce'=>'', 't_profil'=>'[]'));
				}
			else if($g=="2")
				{
				if(isset($_POST['region'])) $region = $wpdb->get_var("SELECT c_liste_valeur FROM ".$wpdb->prefix."rencontre_liste WHERE id='".strip_tags($_POST['region'])."' LIMIT 1");
				if(isset($_POST['gps'])) $gps=explode("|",strip_tags($_POST['gps']."|0|0"));
				$wpdb->update($wpdb->prefix.'rencontre_users', array(
					'c_pays'=>((isset($_POST['pays']) && $_POST['pays'])?$_POST['pays']:(!empty($rencOpt['pays'])?$rencOpt['pays']:'FR')),
					'c_region'=>(isset($region)?$region:''),
					'c_ville'=>(isset($_POST['ville'])?strip_tags($_POST['ville']):''),
					'e_lat'=>(isset($_POST['gps'])?round($gps[0],5):''),
					'e_lon'=>(isset($_POST['gps'])?round($gps[1],5):'')),
					array('user_id'=>$f));
				}
			else if($g=="3")
				{
				global $rencCustom;
				if(!isset($rencCustom['multiSR']) || !$rencCustom['multiSR'] || !is_array($_POST['zsex']) || !is_array($_POST['zrelation']))
					{
					$wpdb->update($wpdb->prefix.'rencontre_users', array(
						'i_zsex'=>strip_tags($_POST['zsex']),
						'c_zsex'=>',',
						'i_zage_min'=>(isset($_POST['zageMin'])?strip_tags($_POST['zageMin']):''),
						'i_zage_max'=>(isset($_POST['zageMax'])?strip_tags($_POST['zageMax']):''),
						'i_zrelation'=>strip_tags($_POST['zrelation']),
						'c_zrelation'=>','),
						array('user_id'=>$f));
					}
				else
					{
					$czs = ','; $czr = ',';
					foreach($_POST['zsex'] as $r) $czs .= $r . ',';
					foreach($_POST['zrelation'] as $r) $czr .= $r . ',';
					$wpdb->update($wpdb->prefix.'rencontre_users', array(
						'i_zsex'=>99,
						'c_zsex'=>$czs,
						'i_zage_min'=>(isset($_POST['zageMin'])?strip_tags($_POST['zageMin']):''),
						'i_zage_max'=>(isset($_POST['zageMax'])?strip_tags($_POST['zageMax']):''),
						'i_zrelation'=>99,
						'c_zrelation'=>$czr),
						array('user_id'=>$f));
					}
				}
			}
		}
	//
	static function f_updateMember($f)
		{
		// $f : ID
		global $wpdb; global $rencOpt; global $rencCustom;
		if(has_action('rencontre_account')) do_action('rencontre_account', $f);
		else
			{
			if(isset($_POST['annee']) && isset($_POST['mois']) && isset($_POST['jour'])) $nais = $_POST['annee'].'-'.((strlen($_POST['mois'])<2)?'0'.$_POST['mois']:$_POST['mois']).'-'.((strlen($_POST['jour'])<2)?'0'.$_POST['jour']:$_POST['jour']);
			if(isset($_POST['region'])) $region = $wpdb->get_var("SELECT c_liste_valeur FROM ".$wpdb->prefix."rencontre_liste WHERE id='".strip_tags($_POST['region'])."' LIMIT 1");
			if(isset($_POST['gps'])) $gps=explode("|",strip_tags($_POST['gps']."|0|0"));
			if(!isset($rencCustom['multiSR']) || !$rencCustom['multiSR'])
				{
				$wpdb->update($wpdb->prefix.'rencontre_users', array(
					'c_pays'=>(isset($_POST['pays'])?$_POST['pays']:(!empty($rencOpt['pays'])?$rencOpt['pays']:'FR')),
					'c_region'=>(isset($region)?$region:''),
					'c_ville'=>(isset($_POST['ville'])?strip_tags($_POST['ville']):''),
					'e_lat'=>(isset($_POST['gps'])?round($gps[0],5):''),
					'e_lon'=>(isset($_POST['gps'])?round($gps[1],5):''),
					'i_sex'=>(isset($_POST['sex'])?strip_tags($_POST['sex']):''),
					'd_naissance'=>(isset($nais)?strip_tags($nais):''),
					'i_taille'=>(isset($_POST['taille'])?strip_tags($_POST['taille']):''),
					'i_poids'=>(isset($_POST['poids'])?strip_tags($_POST['poids']):''),
					'i_zsex'=>(isset($_POST['zsex'])?strip_tags($_POST['zsex']):''),
					'c_zsex'=>',',
					'i_zage_min'=>(isset($_POST['zageMin'])?strip_tags($_POST['zageMin']):''),
					'i_zage_max'=>(isset($_POST['zageMax'])?strip_tags($_POST['zageMax']):''),
					'i_zrelation'=>(isset($_POST['zrelation'])?strip_tags($_POST['zrelation']):''), 
					'c_zrelation'=>',',
					'd_session'=>current_time("mysql")),
					array('user_id'=>$f));
				}
			else
				{
				$czs = ','; $czr = ',';
				foreach($_POST['zsex'] as $r) $czs .= $r . ',';
				foreach($_POST['zrelation'] as $r) $czr .= $r . ',';
				$wpdb->update($wpdb->prefix.'rencontre_users', array(
					'c_pays'=>(isset($_POST['pays'])?$_POST['pays']:(!empty($rencOpt['pays'])?$rencOpt['pays']:'FR')),
					'c_region'=>(isset($region)?$region:''),
					'c_ville'=>(isset($_POST['ville'])?strip_tags($_POST['ville']):''),
					'e_lat'=>(isset($_POST['gps'])?round($gps[0],5):''),
					'e_lon'=>(isset($_POST['gps'])?round($gps[1],5):''),
					'i_sex'=>(isset($_POST['sex'])?strip_tags($_POST['sex']):''),
					'd_naissance'=>(isset($nais)?strip_tags($nais):''),
					'i_taille'=>(isset($_POST['taille'])?strip_tags($_POST['taille']):''),
					'i_poids'=>(isset($_POST['poids'])?strip_tags($_POST['poids']):''),
					'i_zsex'=>99,
					'c_zsex'=>$czs,
					'i_zage_min'=>(isset($_POST['zageMin'])?strip_tags($_POST['zageMin']):''),
					'i_zage_max'=>(isset($_POST['zageMax'])?strip_tags($_POST['zageMax']):''),
					'i_zrelation'=>99,
					'c_zrelation'=>$czr,
					'd_session'=>current_time("mysql")),
					array('user_id'=>$f));
				}
			// options
			$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$f."' LIMIT 1");
			$action = json_decode($q,true);
			if(!isset($action['option'])) $action['option'] = ',';
			$b = 0;
			if(strpos($action['option'],',nomail,')===false && isset($_POST['nomail']))
				{
				$action['option'] .= 'nomail,';
				$b = 1;
				}
			else if(strpos($action['option'],',nomail,')!==false && !isset($_POST['nomail']))
				{
				$b = 1;
				$action['option'] = str_replace(',nomail,',',',$action['option']);
				}
			if($b)
				{
				$out = json_encode($action);
				$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('t_action'=>$out), array('user_id'=>$f));
				}
			}
		}
	//
	static function f_changePass($f,$p) // Nouveau membre
		{
		if($p!="aQwZsXeDc") wp_set_password($p,$f); // changement MdP
		wp_clear_auth_cookie();
		wp_set_auth_cookie($f); // cookie pour rester connecte
		}

	//
	static function f_compte($mid)
		{
		// Fenetre de modification du compte
		global $wpdb; global $rencOpt; global $rencDrapNom; global $rencCustom;
		$u0 = $wpdb->get_row("SELECT 
				U.user_email,
				U.user_login,
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
				R.e_lat,
				R.e_lon,
				P.t_action
			FROM
				".$wpdb->prefix."users U,
				".$wpdb->prefix."rencontre_users R,
				".$wpdb->prefix."rencontre_users_profil P 
			WHERE
				U.ID=".$mid."
				and U.ID=R.user_id
				and U.ID=P.user_id
			LIMIT 1
			");
		$u0->ID = $mid;
		$onClick = array(
			"change"=>"f_password(document.forms['formPass'].elements['pass0'].value,document.forms['formPass'].elements['pass1'].value,document.forms['formPass'].elements['pass2'].value,".$u0->ID.",'".admin_url('admin-ajax.php')."')",
			"country"=>"f_region_select(this.options[this.selectedIndex].value,'".admin_url('admin-ajax.php')."','regionSelect2');",
			"validate"=>"f_cityOk();",
			"agemin"=>"f_min(this.options[this.selectedIndex].value,'formNouveau','zageMin','zageMax');",
			"agemax"=>"f_max(this.options[this.selectedIndex].value,'formNouveau','zageMin','zageMax');",
			"save"=>"f_mod_nouveau(".$u0->ID.")",
			"delete"=>"f_fin(document.forms['formFin'].elements['id'].value,".$u0->ID.")");
			$y = current_time('Y');
			$oldmax = $y-(isset($rencCustom['agemax'])?intval($rencCustom['agemax']):99)-1;
			$oldmin = $y-(isset($rencCustom['agemin'])?intval($rencCustom['agemin']):18)+1;
		$scriptMap = '';
		if(!empty($rencOpt['map']))
			{
			if($u0->e_lat!=0 && $u0->e_lon!=0) $scriptMap = '<script type="text/javascript">jQuery(document).ready(function(){f_cityMap("'.$u0->c_ville.'","'.$u0->e_lat.'","'.$u0->e_lon.'",1);});</script>';
			else $scriptMap = '<script type="text/javascript">jQuery(document).ready(function(){f_cityMap("'.$u0->c_ville.'","'.$rencDrapNom[$u0->c_pays].'","0",1);});</script>';
			}
		list($Y, $m, $j) = explode('-', $u0->d_naissance);
		if(isset($rencOpt['fastreg']) && $rencOpt['fastreg']>1)
			{
			echo '<div id="fastregInfo" class="fastregInfo">';
			if($rencOpt['fastreg']==2 || $rencOpt['fastreg']==4) echo '<p>'.__('You are currently hidden to other members and you can\'t communicate because your account is not complete. You should promptly update this page otherwise your access will be canceled.','rencontre').'</p>';
			if($rencOpt['fastreg']>2)
				{
				echo '<p>'.__('You also need to confirm your email address by clicking the link that you received. You have 24 hours. Do not delay.','rencontre').'</p>';
				echo '<div style="text-align:right;"><a href="javascript:void(0)" onClick="f_fastregMail(\''.admin_url('admin-ajax.php').'\')">'.__('Email not received','rencontre').'</a></div>';
				}
			echo '</div>';
			} ?>
			
			<div id="rencAlert1"></div>
			<?php
			$ho = false; if(has_filter('rencCheckoutP', 'f_rencCheckoutP')) $ho = apply_filters('rencCheckoutP', 1);
			if($ho) echo $ho;
			// ****** TEMPLATE ********
			if(file_exists(get_stylesheet_directory().'/templates/rencontre_account.php')) include(get_stylesheet_directory().'/templates/rencontre_account.php');
			else include(dirname( __FILE__ ).'/../templates/rencontre_account.php');
			// ************************
			?>

			<script type="text/javascript" src="//maps.googleapis.com/maps/api/js?key=<?php echo (!empty($rencOpt['mapapi'])?$rencOpt['mapapi']:''); ?>&sensor=false"></script>
		<?php }
	//
	static function f_sourire($f)
		{
		// envoi un sourire a ID=$f
		global $wpdb; global $current_user; global $rencOpt; global $rencCustom; global $rencDiv;
		// 1. mon compte : sourireOut
		$q = $wpdb->get_row("SELECT
				R.i_photo,
				P.t_action
			FROM
				".$wpdb->prefix."rencontre_users R,
				".$wpdb->prefix."rencontre_users_profil P
			WHERE
				R.user_id='".$current_user->ID."'
				AND R.user_id=P.user_id
			LIMIT 1
			");
		$mephoto = $q->i_photo;
		$action= json_decode($q->t_action,true);
		$action['sourireOut']=(isset($action['sourireOut'])?$action['sourireOut']:null);
		$c = count($action['sourireOut']);
		if($c)
			{
			foreach($action['sourireOut'] as $r)
				{
				if($r['i']==$f)
					{
					if(isset($rencCustom['smiw']) && isset($rencCustom['smiw5']) && $rencCustom['smiw'] && $rencCustom['smiw5']) return stripslashes($rencCustom['smiw5']);
					else return __('Smile already sent','rencontre');
					}
				}
			} // deja souri
		$action['sourireOut'][$c]['i'] = ($f+0);
		$action['sourireOut'][$c]['d'] = current_time("Y-m-d");
		$out = json_encode($action);
		$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('t_action'=>$out), array('user_id'=>$current_user->ID));
		// 2. son compte : sourireIn
		$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$f."' LIMIT 1");
		$action= json_decode($q,true);
		$action['sourireIn']=(isset($action['sourireIn'])?$action['sourireIn']:null);
		$c = count($action['sourireIn']);
		$action['sourireIn'][$c]['i'] = ($current_user->ID+0);
		$action['sourireIn'][$c]['d'] = current_time("Y-m-d");
		$out = json_encode($action);
		$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('t_action'=>$out), array('user_id'=>$f));
		// memo pour mail CRON
		if(!is_dir($rencDiv['basedir'].'/portrait/cache/cron_liste/')) mkdir($rencDiv['basedir'].'/portrait/cache/cron_liste/');
		if(!file_exists($rencDiv['basedir'].'/portrait/cache/cron_liste/'.$f.'.txt') && !empty($rencOpt['mailsmile']))
			{
			if($mephoto || empty($rencOpt['mailph']))
				{
				$t=fopen($rencDiv['basedir'].'/portrait/cache/cron_liste/'.$f.'.txt', 'w');
				fclose($t);
				}
			}
		if(isset($rencCustom['smiw']) && isset($rencCustom['smiw6']) && $rencCustom['smiw'] && $rencCustom['smiw6']) return stripslashes($rencCustom['smiw6']);
		else return __('Smile sent','rencontre');
//	[	{"a":"sourireIn","v":[{"i":10,"d":"2013-12-15"},{"i":32,"d":"2013-12-15"}]},
//		{"a":"sourireOut","v":[{"i":15,"d":"2013-12-15"},{"i":28,"d":"2013-12-15"},{"i":41,"d":"2013-12-15"}]},
//		{"a":"contactIn","v":[{"i":8,"d":"2013-12-15"}]},
//		{"a":"contactOut","v":[{"i":17,"d":"2013-12-15"},{"i":18,"d":"2013-12-15"},{"i":19,"d":"2013-12-15"}]},
//		{"a":"visite","v":[{"i":25,"d":"2013-12-15"}]},
//		{"a":"bloque","v":[{"i":50,"d":"2013-12-15"},{"i":51,"d":"2013-12-15"}]}
//	]
//
		}
	//
	static function f_demcont($f)
		{
		// demander un contact a ID=$f
		global $wpdb; global $current_user; global $rencOpt; global $rencDiv;
		// 1. mon compte : contactOut
		$q = $wpdb->get_row("SELECT
				R.i_photo,
				P.t_action 
			FROM 
				".$wpdb->prefix."rencontre_users R,
				".$wpdb->prefix."rencontre_users_profil P
			WHERE 
				R.user_id='".$current_user->ID."'
				AND R.user_id=P.user_id
			LIMIT 1
			");
		$mephoto = $q->i_photo;
		$action= json_decode($q->t_action,true);
		$action['contactOut']=(isset($action['contactOut'])?$action['contactOut']:null);
		$c = count($action['contactOut']);
		if($c)
			{
			foreach ($action['contactOut'] as $r)
				{
				if($r['i']==$f) return __('Contact already requested','rencontre');
				}
			} // deja demande
		$action['contactOut'][$c]['i'] = ($f+0);
		$action['contactOut'][$c]['d'] = current_time("Y-m-d");
		$out = json_encode($action);
		$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('t_action'=>$out), array('user_id'=>$current_user->ID));
		// 2. son compte : contactIn
		$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$f."' LIMIT 1");
		$action= json_decode($q,true);
		$action['contactIn']=(isset($action['contactIn'])?$action['contactIn']:null);
		$c = count($action['contactIn']);
		$action['contactIn'][$c]['i'] = ($current_user->ID+0);
		$action['contactIn'][$c]['d'] = current_time("Y-m-d");
		$out = json_encode($action);
		$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('t_action'=>$out), array('user_id'=>$f));
		// memo pour mail CRON
		if(!is_dir($rencDiv['basedir'].'/portrait/cache/cron_liste/')) mkdir($rencDiv['basedir'].'/portrait/cache/cron_liste/');
		if(!file_exists($rencDiv['basedir'].'/portrait/cache/cron_liste/'.$f.'.txt'))
			{
			if($mephoto || empty($rencOpt['mailph']))
				{
				$t=fopen($rencDiv['basedir'].'/portrait/cache/cron_liste/'.$f.'.txt', 'w');
				fclose($t);
				}
			}
		return __('Contact request sent','rencontre');
		}
	//
	static function f_signal($f)
		{
		// envoi un signalement sur ID=$f
		global $wpdb; global $current_user;
		// 1. mon compte : sourireOut
		$q = $wpdb->get_var("SELECT t_signal FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$f."' LIMIT 1");
		$signal= json_decode($q,true);
		$c = count($signal);
		if($c)
			{
			foreach ($signal as $r)
				{
				if($r['i']==$current_user->ID) return __('Reporting already done','rencontre');
				}
			} // deja signale par mid
		$signal[$c]['i'] = ($current_user->ID+0);
		$signal[$c]['d'] = current_time("Y-m-d");
		$out = json_encode($signal);
		$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('t_signal'=>$out), array('user_id'=>$f));
		return __('Thank you for your report','rencontre');
		}
	//
	static function f_bloque($f)
		{
		// bloque ou debloque ID=$f
		global $wpdb; global $current_user;
		$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$current_user->ID."' LIMIT 1");
		$action= json_decode($q,true);
		$action['bloque']=(isset($action['bloque'])?$action['bloque']:null);
		$c = count($action['bloque']); $c1=0;
		if($c) {foreach ($action['bloque'] as $r)
			{
			if($r['i']==$f) // deja bloque : on debloque
				{
				unset($action['bloque'][$c1]['i']);unset($action['bloque'][$c1]['d']);
				$action['bloque']=array_filter($action['bloque']);
				$out = json_encode($action);
				$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('t_action'=>$out), array('user_id'=>$current_user->ID));
				return;
				}
			++$c1;
			}}
		// pas bloque : on bloque
		$action['bloque'][$c]['i'] = ($f+0);
		$action['bloque'][$c]['d'] = current_time("Y-m-d");
		$out = json_encode($action);
		$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('t_action'=>$out), array('user_id'=>$current_user->ID));
		}
	//
	static function f_etat_bloque($f)
		{
		// regarde si un membre est bloque
		global $wpdb; global $current_user;
		$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$current_user->ID."' LIMIT 1");
		$action= json_decode($q,true);
		$action['bloque']=(isset($action['bloque'])?$action['bloque']:null);
		$c = count($action['bloque']); if($c) {foreach ($action['bloque'] as $r){if($r['i']==$f) return true; }} // est bloque
		else return false;
		}
	//
	static function f_etat_bloque1($f,$action=0)
		{
		// regarde si un membre m a bloque
		global $current_user;
		if($action==0)
			{
			global $wpdb;
			$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$f."' LIMIT 1");
			$action= json_decode($q,true);
			}
		$action['bloque']=(isset($action['bloque'])?$action['bloque']:null);
		$c = count($action['bloque']); if($c) {foreach ($action['bloque'] as $r){if($r['i']==$current_user->ID) return true; }} // est bloque
		else return false;
		}
	//
	static function f_visite($f)
		{
		// id : MID visite F - sauvegarde chez F
		global $wpdb; global $current_user;
		$q = $wpdb->get_var("SELECT t_action FROM ".$wpdb->prefix."rencontre_users_profil WHERE user_id='".$f."' LIMIT 1");
		$action= json_decode($q,true);
		$action['visite']=(isset($action['visite'])?$action['visite']:null);
		$c = count($action['visite']);
		if($c>60) RencontreWidget::f_menage_action($f,$action);
		if($c) {foreach ($action['visite'] as $r) { if($r['i']==$current_user->ID) return; }}
		// pas encore vu
		$action['visite'][$c]['i'] = ($current_user->ID+0);
		$action['visite'][$c]['d'] = current_time("Y-m-d");
		$out = json_encode($action);
		$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('t_action'=>$out), array('user_id'=>$f));
		}
	//
	static function f_distance($lat,$lon)
		{
		// distance from me
		global $wpdb; global $current_user; global $rencCustom;
		$q = $wpdb->get_row("SELECT
				e_lat,
				e_lon
			FROM
				".$wpdb->prefix."rencontre_users
			WHERE
				user_id='".$current_user->ID."'
			LIMIT 1
			");
		if($q->e_lat!=0 && $q->e_lon!=0 && $lat!=0 && $lon!=0 && $lat!=$q->e_lat && $lon!=$q->e_lon)
			{
			$d = (floor(sqrt(pow(($q->e_lat-$lat)*60*1.852,2)+pow(($q->e_lon-$lon)*60*1.852*cos(($lat+$q->e_lat) / 2 * 0.0174533),2))));
			echo '<em>('.(!empty($rencCustom['sizeu'])?floor($d*0.62137).' mi ':$d.' km ').__('from my position','rencontre').')</em>';
			}
		return;
		}

	static function f_menage_action($f,$action)
		{
		// fait le menage dans le json action - limite a 50 elements par item
		$a = array("sourireIn","sourireOut","contactIn","contactOut","visite","bloque");
		for ($v=0; $v<count($a); ++$v)
			{
			$c = count($action[$a[$v]]);
			for ($w=0; $w<$c-50; ++$w) 
				{
				unset($action[$a[$v]][$w]['i']);
				unset($action[$a[$v]][$w]['d']);
				}
			if($action[$a[$v]]) $action[$a[$v]]=array_filter($action[$a[$v]]);
			if($action[$a[$v]]) $action[$a[$v]]=array_splice($action[$a[$v]],0); // remise en ordre avec de nouvelles clefs
			}
		$out = json_encode($action);
		global $wpdb;
		$wpdb->update($wpdb->prefix.'rencontre_users_profil', array('t_action'=>$out), array('user_id'=>$f));
		}
	static function rencGate()
		{
		// Entry screening
		$ho = false; if(has_filter('rencCheckoutP', 'f_rencCheckoutP')) $ho = apply_filters('rencCheckoutP', $ho);
		echo $ho;
		}
	static function format_date($f)
		{
		$d = get_option('date_format');
		if($d && strtotime($f)) return date_i18n($d, strtotime($f));
		else return $f;		
		}
	static function format_dateTime($f)
		{
		$d = get_option('date_format');
		$h = get_option('time_format');
		if($d && $h && strtotime($f)) return date_i18n($d, strtotime($f)).' - '.date_i18n($h, strtotime($f));
		else return $f;		
		}
	} // CLASSE RencontreWidget
//
//
//
class RencontreSidebarWidget extends WP_widget
	{
 	function __construct()
		{
		parent::__construct('rencontre-sidebar-widget','Rencontre Sidebar',array('description'=>__('Widget to integrate the Rencontre sidebar in your theme sidebar', 'rencontre'),));
		}
	//
	function widget($arguments, $data) // Partie Site
		{
		if(current_user_can("administrator")) return;
		global $wpdb; global $rencDiv; global $rencOpt; global $rencCustom; global $current_user; global $rencDrap; global $rencDrapNom; global $post;
		if(!empty($rencOpt['home']) && str_replace('/','',get_permalink($post->ID))!=str_replace('/','',$rencOpt['home'])) return;
		$mid = $current_user->ID;
		if(isset($data->ID)) $u0 = $data;
		else $u0 = $wpdb->get_row("SELECT
				U.ID,
				U.display_name,
				U.user_login,
				R.c_ip,
				R.c_pays,
				R.c_ville,
				R.i_sex,
				R.d_naissance,
				R.i_zsex,
				R.c_zsex,
				R.i_zage_min,
				R.i_zage_max,
				R.i_zrelation,
				R.c_zrelation,
				R.i_photo,
				P.t_action 
			FROM 
				".$wpdb->prefix."users U,
				".$wpdb->prefix."rencontre_users R,
				".$wpdb->prefix."rencontre_users_profil P 
			WHERE
				R.user_id=".$mid." and
				R.user_id=P.user_id and
				R.user_id=U.ID
			LIMIT 1
			");
		if(empty($u0->c_ip)) return;
		$action = json_decode($u0->t_action,true);
		$u0->sourireIn = (isset($action['sourireIn'])?$action['sourireIn']:null);
		$u0->visite = (isset($action['visite'])?$action['visite']:null);
		$u0->contactIn = (isset($action['contactIn'])?$action['contactIn']:null);
		if($u0->i_zsex!=99) $u0->zsex = $u0->i_zsex;
		else $u0->zsex = '('.substr($u0->c_zsex,1,-1).')';
		$u0->homo = (($u0->i_sex==$u0->i_zsex)?1:0); // seulement si genre sans custom
		if($u0->i_zage_min) $zmin = date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"), date("Y")-$u0->i_zage_min)); else $zmin = 0;
		if($u0->i_zage_max) $zmax = date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"), date("Y")-$u0->i_zage_max)); else $zmax = 0;
		if(!isset($rencDrap) || !$rencDrap)
			{
			$q = $wpdb->get_results("SELECT c_liste_categ, c_liste_valeur, c_liste_iso FROM ".$wpdb->prefix."rencontre_liste WHERE c_liste_categ='d' or (c_liste_categ='p' and c_liste_lang='".substr($rencDiv['lang'],0,2)."') ");
			$rencDrap=''; $rencDrapNom='';
			foreach($q as $r)
				{
				if($r->c_liste_categ=='d') $rencDrap[$r->c_liste_iso] = $r->c_liste_valeur;
				else if($r->c_liste_categ=='p')$rencDrapNom[$r->c_liste_iso] = $r->c_liste_valeur;
				}
			}
		$u0->looking = '';
		$u0->forwhat = '';
		if($u0->i_zsex!=99)
			{ 
			if(isset($rencOpt['iam'][$u0->i_zsex])) $u0->looking = $rencOpt['iam'][$u0->i_zsex];
			if(isset($rencOpt['for'][$u0->i_zrelation])) $u0->forwhat = $rencOpt['for'][$u0->i_zrelation];
			}
		else
			{
			// looking
			$a = explode(',', $u0->c_zsex);
			$as = '';
			foreach($a as $a1) if(isset($rencOpt['iam'][$a1])) $as .= $rencOpt['iam'][$a1] . ', ';
			$u0->looking = substr($as,0,-2);
			// forwhat
			$a = explode(',', $u0->c_zrelation);
			$as = '';
			foreach($a as $a1) if(isset($rencOpt['for'][$a1])) $as .= $rencOpt['for'][$a1] . ', ';
			$u0->forwhat = substr($as,0,-2);
			}
		$hom = false; if(has_filter('rencNbSearchP', 'f_rencNbSearchP')) $hom = apply_filters('rencNbSearchP', $hom);
		$find = array(
			"class"=>($hom?" rencLiOff":""),
			"title"=>($hom?$hom:""));
		$onClick = array(
			"edit"=>"document.forms['rencMenu'].elements['renc'].value='edit';document.forms['rencMenu'].elements['id'].value='".$u0->ID."';document.forms['rencMenu'].submit();",
			"sourireIn"=>"document.forms['rencMenu'].elements['renc'].value='qsearch';document.forms['rencMenu'].elements['id'].value='sourireIn';document.forms['rencMenu'].submit();",
			"visite"=>"document.forms['rencMenu'].elements['renc'].value='qsearch';document.forms['rencMenu'].elements['id'].value='visite';document.forms['rencMenu'].submit();",
			"contactIn"=>"document.forms['rencMenu'].elements['renc'].value='qsearch';document.forms['rencMenu'].elements['id'].value='contactIn';document.forms['rencMenu'].submit();",
			"sourireOut"=>"document.forms['rencMenu'].elements['renc'].value='qsearch';document.forms['rencMenu'].elements['id'].value='sourireOut';document.forms['rencMenu'].submit();",
			"contactOut"=>"document.forms['rencMenu'].elements['renc'].value='qsearch';document.forms['rencMenu'].elements['id'].value='contactOut';document.forms['rencMenu'].submit();",
			"bloque"=>"document.forms['rencMenu'].elements['renc'].value='qsearch';document.forms['rencMenu'].elements['id'].value='bloque';document.forms['rencMenu'].submit();",
			"agemin"=>"f_min(parseInt(this.options[this.selectedIndex].value),'formMonAccueil','ageMin','ageMax');",
			"agemax"=>"f_max(parseInt(this.options[this.selectedIndex].value),'formMonAccueil','ageMin','ageMax');",
			"country"=>"f_region_select(this.options[this.selectedIndex].value,'<?php echo admin_url('admin-ajax.php'); ?>','regionSelect1');",
			"find"=>"onClick=\"f_quickTrouve(".$hom.");\"");
		if(!isset($data->ID)) echo '<div class="widgRencSide">'."\r\n"; // external
		?>
		<div class="petiteBox right">
			<?php if(strstr($_SESSION['rencontre'],'paswd')) { ?>
			
			<div id="infoChange">
				<div class="rencBox"><em><?php _e('Password changed !','rencontre'); ?></em></div>
			</div>
			<?php }
			// ****** TEMPLATE ********
			if(file_exists(get_stylesheet_directory().'/templates/rencontre_sidebar_top.php')) include(get_stylesheet_directory().'/templates/rencontre_sidebar_top.php');
			else include(dirname( __FILE__ ).'/../templates/rencontre_sidebar_top.php');
			// ************************
			$profilQuickSearch1 = 0;
			$profilQuickSearch2 = 0;
			if(isset($rencCustom['profilQS1']))
				{
				$q = $wpdb->get_row("SELECT
						c_label,
						t_valeur,
						i_type
					FROM
						".$wpdb->prefix."rencontre_profil
					WHERE
						id=".$rencCustom['profilQS1']." and
						c_lang='".substr($rencDiv['lang'],0,2)."'
					LIMIT 1
					");
				if($q)
					{
					$profilQuickSearch1 = '<div class="rencItem">'.$q->c_label.'&nbsp;:<select id="profilQS1" name="profilQS1">';
					$s = json_decode($q->t_valeur);
					$c = 0;
					$profilQuickSearch1 .= '<option value="">-</option>';
					if($q->i_type==3 || $q->i_type==4)
						{
						foreach($s as $ss)
							{
							$profilQuickSearch1 .= '<option value="'.$c.'">'.$ss.'</option>';
							++$c;
							}
						}
					else if($q->i_type==5)
						{
						for($v=$s[0]; $v<=$s[1]; $v+=$s[2])
							{
							$profilQuickSearch1 .= '<option value="'.($c+1).'">'.$v.' '.$s[3].'</option>';
							++$c;
							}
						}
					$profilQuickSearch1 .= '</select></div>';
					}
				}
			if(isset($rencCustom['profilQS2']))
				{
				$q = $wpdb->get_row("SELECT
						c_label,
						t_valeur,
						i_type
					FROM
						".$wpdb->prefix."rencontre_profil
					WHERE
						id=".$rencCustom['profilQS2']." and
						c_lang='".substr($rencDiv['lang'],0,2)."'
					LIMIT 1
					");
				if($q)
					{
					$profilQuickSearch2 = '<div class="rencItem">'.$q->c_label.'&nbsp;:<select id="profilQS2" name="profilQS2">';
					$s = json_decode($q->t_valeur);
					$c = 0;
					$profilQuickSearch2 .= '<option value="">-</option>';
					if($q->i_type==3 || $q->i_type==4)
						{
						foreach($s as $ss)
							{
							$profilQuickSearch2 .= '<option value="'.$c.'">'.$ss.'</option>';
							++$c;
							}
						}
					else if($q->i_type==5)
						{
						for($v=$s[0]; $v<=$s[1]; $v+=$s[2])
							{
							$profilQuickSearch2 .= '<option value="'.($c+1).'">'.$v.' '.$s[3].'</option>';
							++$c;
							}
						}
					$profilQuickSearch2 .= '</select></div>';
					}
				}
			// ****** TEMPLATE ********
			if(file_exists(get_stylesheet_directory().'/templates/rencontre_sidebar_quick_search.php')) include(get_stylesheet_directory().'/templates/rencontre_sidebar_quick_search.php');
			else include(dirname( __FILE__ ).'/../templates/rencontre_sidebar_quick_search.php');
			// ************************
			?>
			
			<div id="rencAdsL" class="rencAds">
			<?php $ho = false; if(has_filter('rencAdsLP', 'f_rencAdsLP')) $ho = apply_filters('rencAdsLP', $ho); if($ho) echo $ho; ?>
			</div><!-- .rencAds -->
		</div><!-- .petiteBox .right -->
		<?php
		if(!isset($data->ID)) echo '</div><!-- .widgRencSide -->'."\r\n".'<div class="clear"></div>'; // external
		}
	//
	} // CLASSE RencontreSidebarWidget
//
