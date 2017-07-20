<?php
/*
 * Plugin : Rencontre
 * Template : Mini Portrait
 * Last Change : Rencontre 2.0
 * Custom This File ? : wp-content/themes/name-of-my-theme/templates/rencontre_mini_portrait.php
 * $u : ID, display_name, c_pays, c_ville, d_naissance, i_photo, t_titre, online, miniPhoto
*/
?>

	<div class="miniPortrait miniBox <?php if($highlight) echo 'highlight'; ?>">
		<a href="javascript:void(0)" onClick="<?php echo $onClick['profile']; ?>">
		<?php if($u->i_photo!=0) { ?>
		
			<img class="tete" src="<?php echo $u->miniPhoto; ?>'." alt="<?php echo $u->display_name; ?>" <?php echo $title['thumb']; ?> />
		<?php } else { ?>
		
			<img class="tete" src="<?php echo plugins_url('rencontre/images/no-photo60.jpg'); ?>" alt="<?php echo $u->display_name; ?>" />
		<?php } ?>
		
		</a>
		<div>
			<h3>
				<a href="javascript:void(0)" onClick="<?php echo $onClick['profile']; ?>"><?php echo $u->display_name; ?></a>
			</h3>
			<?php if(!isset($rencCustom['born']) && strpos($u->d_naissance,'0000')===false) { ?>
			
			<div class="monAge"><?php echo Rencontre::f_age($u->d_naissance); ?>&nbsp;<?php _e('years','rencontre'); ?></div>
			<?php } ?>
			<?php if(!isset($rencCustom['place'])) { ?>
			
			<div class="maVille"><?php echo $u->c_ville; ?></div>
			<?php } ?>
			
		</div>
		<div style="clear:both"></div>
		<?php if($u->c_pays!='' && !isset($rencCustom['country'])) { ?>
		
		<img class="flag" src="<?php echo plugins_url('rencontre/images/drapeaux/').$rencDrap[$u->c_pays]; ?>" alt="<?php echo $rencDrapNom[$u->c_pays]; ?>" title="<?php echo $rencDrapNom[$u->c_pays]; ?>" />
		
		<?php } ?>
		
		<p>
			<?php echo stripslashes($u->t_titre); ?>
		</p>
		<?php if($u->online) { ?>
		
		<span class="rencInL"><?php _e('online','rencontre'); ?></span>
		<?php } else { ?>
		
		<span class="rencOutL"><?php _e('offline','rencontre'); ?></span>
		<?php } ?>

	</div><!-- .miniPortrait -->
