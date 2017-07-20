<?php
/*
 * Plugin : Rencontre
 * Template : Libre SEARCH Portrait
 * Last Change : Rencontre 2.0
 * Custom This File ? : wp-content/themes/name-of-my-theme/templates/rencontre_libre_search_portrait.php
 * $u : U.ID, display_name, i_sex, c_pays, c_ville, d_naissance, i_photo, t_titre, title, miniPhoto
*/
?>

	<div class="miniPortrait miniBox">
		<?php if(!empty($rencCustom['reglink'])) { ?>
	
		<a href="<?php echo $rencCustom['reglink']; ?>">
		<?php } else { ?>

		<a href="<?php echo $rencDiv['siteurl'].'/wp-login.php?action=register'; ?>">
		<?php } ?>

			<img id="tete" class="tete" src="<?php echo $u->miniPhoto; ?>" alt="<?php echo $u->display_name; ?>" />
		</a>
		<div>
			<h3><?php echo $u->display_name; ?></h3>
			<?php if(!isset($rencCustom['born'])) { ?>
			
			<div class="monAge"><?php echo Rencontre::f_age($u->d_naissance); ?>&nbsp;<?php _e('years','rencontre'); ?></div>
			<?php } ?>
			<?php if(!isset($rencCustom['place'])) { ?>
			
			<div class="maVille"><?php echo $u->c_ville; ?></div>
			<?php } ?>
			
		</div>
		<div style="clear:both"></div>
		<?php if($u->c_pays!="" && !isset($rencCustom['country']) && !isset($rencCustom['place']) && (!isset($rencCustom['libreFlag']) || !$rencCustom['libreFlag'])) { ?>

		<img class="flag" src="<?php echo plugins_url('rencontre/images/drapeaux/').$rencDrap[$u->c_pays]; ?>" alt="<?php echo $rencDrapNom[$u->c_pays]; ?>" title="<?php echo $rencDrapNom[$u->c_pays]; ?>" />
		<?php } ?>
		
		<p><?php echo $u->title; ?></p>
	</div><!-- .miniPortrait -->
