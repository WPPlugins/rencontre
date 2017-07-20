<?php
/*
 * Plugin : Rencontre
 * Template : Mini Portrait Chat - Chat only
 * Last Change : Rencontre 2.0
 * Custom This File ? : wp-content/themes/name-of-my-theme/templates/rencontre_mini_portrait_chat.php
 * $u : display_name, c_pays, c_ville, d_naissance, i_photo, t_titre 
*/
?>

	<div class="miniPortrait miniBox">
		<?php if($u->i_photo!=0) echo '<img class="tete" src="'.$rencDiv['baseurl'].'/portrait/'.floor(($user_id)/1000).'/'.Rencontre::f_img(($user_id*10).'-mini').'.jpg?r='.rand().'" alt="'.$u->display_name.'" />';
		else echo '<img class="tete" src="'.plugins_url('rencontre/images/no-photo60.jpg').'" alt="'.$u->display_name.'" />'; ?>
		
		<div>
			<h3><?php echo $u->display_name; ?></h3>
			<?php if(!isset($rencCustom['born']) && strpos($u->d_naissance,'0000')===false) echo '<div class="monAge">'.Rencontre::f_age($u->d_naissance).'&nbsp;'.__('years','rencontre').'</div>';
			if(!isset($rencCustom['place'])) echo '<div class="maVille">'.$u->c_ville.'</div>'; ?>
		</div>
		<p><?php echo stripslashes($u->t_titre); ?></p>
		<?php 
		if($u->c_pays!="" && !isset($rencCustom['country'])) echo '<img class="flag" src="'.plugins_url('rencontre/images/drapeaux/').$rencDrap1.'" alt="'.$rencDrapNom1.'" title="'.$rencDrapNom1.'" />'; ?>
		
	</div><!-- .miniPortrait -->
