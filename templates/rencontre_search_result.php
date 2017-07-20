<?php
/*
 * Plugin : Rencontre
 * Template : Search Result
 * Last Change : Rencontre 2.0
 * Custom This File ? : wp-content/themes/name-of-my-theme/templates/rencontre_search_result.php
 * $u0 : user_login, user_id, d_naissance, i_zsex, c_zsex, i_zage_min, i_zage_max, i_zrelation, c_zrelation, i_photo, e_lat, e_lon, d_session, t_annonce, t_profil, t_action, looking, forwhat, hidephoto
*/
?>

	<?php if(empty($rencCustom['searchAd'])) { ?>
	
	<div class="rencBox">
		<?php if($u->date) { ?>
		
		<div class="rencDate"><?php _e('The','rencontre'); ?>&nbsp;<?php echo $u->date; ?></div>
		<?php } ?>
		<?php if($u->online) { ?>
		
		<div class="rencDate"><?php _e('online','rencontre'); ?>&nbsp;:&nbsp;<?php echo $u->online; ?></div>
		<?php } ?>
	<?php } ?>
		
		<div class="fl">
		<?php RencontreWidget::f_miniPortrait($u->user_id,$u->hidephoto); ?>
		
		</div>
	<?php if(empty($rencCustom['searchAd'])) { ?>

		<div class="maxiBox rel">
			<div class="annonce">
				<?php echo stripslashes($u->t_annonce); ?>
				<?php echo $searchAdd1; ?>
				
			</div><!-- .annonce -->
		</div><!-- .maxiBox -->
		<div class="detail">
			<div class="looking firstMaj">
		<?php if($u->i_zsex!=99) { ?>
			<?php if($u->looking) { ?>
				<?php _e('I\'m looking for','rencontre'); ?>&nbsp;<span><?php echo $u->looking; ?></span>
			
				<br />
			<?php } ?>
			<?php if(!isset($rencCustom['born']) && $u->i_zage_min) { ?>
				<?php _e('between','rencontre'); ?>&nbsp;<span><?php echo $u->i_zage_min; ?></span>&nbsp;<?php _e('and','rencontre'); ?>&nbsp;<span><?php echo $u->i_zage_max; ?></span>&nbsp;<?php _e('years','rencontre')?>
			
				<br />
			<?php } ?>
			<?php if($u->forwhat) { ?>
				<?php _e('for','rencontre'); ?>&nbsp;<span><?php echo $u->forwhat; ?></span>
			<?php } ?>
		<?php } ?>
				
			</div><!-- .looking -->

		<?php if(!$disable['send']) { ?>
			
			<div class="button right">
				<a href="javascript:void(0)" onClick="<?php echo $onClick['send']; ?>"><?php _e('Send a message','rencontre');?></a>
			</div>
		<?php } else { ?>
			
			<div class="button right rencLiOff">
				<a href="javascript:void(0)" title="<?php echo $title['send']; ?>"><?php _e('Send a message','rencontre'); ?></a>
			</div>
		<?php } ?>
		<?php if(!isset($rencCustom['smile'])) { ?>
			<?php if(!$disable['smile']) { ?>
			
			<div class="button right">
				<a href="javascript:void(0)" onClick="<?php echo $onClick['smile']; ?>">
					<?php if(!empty($rencCustom['smiw']) && !empty($rencCustom['smiw1'])) echo stripslashes($rencCustom['smiw1']);
					else _e('Smile','rencontre'); ?>
					
				</a>
			</div>
			<?php } else { ?>

			<div class="button right rencLiOff">
				<a href="javascript:void(0)" title="<?php echo $title['smile']; ?>">
					<?php if(!empty($rencCustom['smiw']) && !empty($rencCustom['smiw1'])) echo stripslashes($rencCustom['smiw1']);
					else _e('Smile','rencontre'); ?>
					
				</a>
			</div>
			<?php } ?>
		<?php } ?>
		<?php if(!$disable['profile']) { ?>
		
			<div class="button right">
				<a href="javascript:void(0)" onClick="<?php echo $onClick['profile']; ?>"><?php _e('Profile','rencontre');?></a>
			</div>
		<?php } else { ?>
			
			<div class="button right rencLiOff">
				<a href="javascript:void(0)" title="<?php echo $title['profile']; ?>"><?php _e('Profile','rencontre'); ?></a>
			</div>
		<?php } ?>
			
			<div class="clear"></div>
		</div><!-- .detail -->
		<div class="clear"></div>
	</div><!-- .rencBox -->
	<?php } ?>
