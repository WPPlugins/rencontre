<?php
/*
 * Plugin : Rencontre
 * Template : Libre Portrait
 * Last Change : Rencontre 2.0
 * Custom This File ? : wp-content/themes/name-of-my-theme/templates/rencontre_libre_portrait.php
 * $u : ID, display_name, user_registered, i_sex, i_zsex, c_pays, c_ville, d_naissance, i_photo, t_titre, t_annonce, titre, annonce, libreID, librePhoto, miniPhoto, genre
*/
?>
	<?php if(isset($rencCustom['librePhoto'])) { ?>
	
	<div class="rencBox photo <?php echo $u->genre; ?>">
		<?php if(!empty($rencCustom['reglink'])) { ?>
	
		<a href="<?php echo $rencCustom['reglink']; ?>">
		<?php } else { ?>

		<a href="<?php echo $rencDiv['siteurl'].'/wp-login.php?action=register'; ?>">
		<?php } ?>
	
			<div class="miniPortrait">
				<img src="<?php echo $rencDiv['baseurl']; ?>/portrait/libre/<?php echo ($u->ID*10); ?>-libre.jpg" />
			</div>
			<div class="clear"></div>
		</a>
	</div><!-- .rencBox -->
	
	<?php } else { ?>

	<div class="rencBox <?php if(!isset($rencCustom['libreAd'])) echo 'ad '; ?><?php echo $u->genre; ?>">
		<?php if(!empty($rencCustom['reglink'])) { ?>
	
		<a href="<?php echo $rencCustom['reglink']; ?>">
		<?php } else { ?>

		<a href="<?php echo $rencDiv['siteurl'].'/wp-login.php?action=register'; ?>">
		<?php } ?>
	
			<div class="miniPortrait miniBox">
				<img id="tete<?php echo $u->libreID; ?>" class="tete" onMouseOver="<?php echo $onClick['zoomIn']; ?>" onMouseOut="<?php echo $onClick['zoomOut']; ?>" src="<?php echo $u->miniPhoto; ?>" alt="<?php echo $u->display_name; ?>" />
				<img style="display:none;" src="<?php echo $u->librePhoto; ?>" />
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
			</div>
			<?php if(!isset($rencCustom['libreAd'])) { ?>
			
			<div class="rencAd"><?php echo $u->annonce; ?></div>
			<?php } ?>
				
			<div class="clear"></div>
		</a>
	</div><!-- .rencBox -->
	<?php } ?>
