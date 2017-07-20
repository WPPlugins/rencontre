<?php
/*
 * Plugin : Rencontre
 * Template : Search Result (grande recherche)
 * Last Change : Rencontre 2.0
*/
?>

	<?php if(empty($rencCustom['searchAd'])) { ?>
	
	<div class="rencBox">
		<?php if(isset($u->d_session)) { ?>
		
		<div class="rencDate"><?php _e('online','rencontre'); ?>&nbsp;:&nbsp;<?php echo substr($u->d_session,8,2).'.'.substr($u->d_session,5,2).'.'.substr($u->d_session,0,4); ?></div>
		<?php } ?>
	<?php } ?>
				
		<div class=fl">
		<?php RencontreWidget::f_miniPortrait($u->user_id,(($rencOpt['onlyphoto'] && !$mephoto)?1:0)); ?>
		
		</div>
	<?php if(empty($rencCustom['searchAd'])) { ?>
	
		<div class="maxiBox rel">
			<div class="annonce">
				<?php echo stripslashes($u->t_annonce); ?>
				<?php if($hoastro && $u->score) { ?>
				
				<div class="affinity"><?php _e('Astrological affinity','rencontre'); ?>&nbsp;:&nbsp;<span><?php echo $u->score; ?> / 5</span>
					<img style="margin:-5px 0 0 5px;" src="<?php echo plugins_url($hoastro.'/img/astro'.$u->score.'.png'); ?>" alt="astro" />
				</div>
				<?php } else if($hoprofil && $u->score) { ?>
				
				<div class="affinity"><?php _e('Affinity with my profile','rencontre'); ?>&nbsp;:&nbsp;<span><?php echo $u->score; ?></span>&nbsp;<?php _e('points','rencontre'); ?>.</div>
				<?php } ?>
				
			</div><!-- .annonce -->
		</div><!-- .maxiBox -->
		<div class="detail">
			<div class="looking firstMaj">
				<?php 
				if($u->i_zsex!=99) {
					if(isset($rencOpt['iam'][$u->i_zsex])) echo __('I\'m looking for','rencontre').'&nbsp;<span>'.$rencOpt['iam'][$u->i_zsex].'</span><br />';
					if(!isset($rencCustom['born']) && $u->i_zage_min) echo __('between','rencontre').'&nbsp;<span>'.$u->i_zage_min.'</span>&nbsp;'.__('and','rencontre').'&nbsp;<span>'.$u->i_zage_max.'</span>&nbsp;'.__('years','rencontre').'<br />';
					if(isset($rencOpt['for'][$u->i_zrelation])) echo __('for','rencontre').'&nbsp;<span>'.$rencOpt['for'][$u->i_zrelation].'</span>'; 
				} else {
					$a = explode(',', $u->c_zsex); $as = '';
					foreach($a as $a1) if(isset($rencOpt['iam'][$a1])) $as .= $rencOpt['iam'][$a1] . ', ';
					echo __('I\'m looking for','rencontre').'&nbsp;<span>'.substr($as,0,-2).'</span><br />';
					if(!isset($rencCustom['born']) && $u->i_zage_min) echo __('between','rencontre').'&nbsp;<span>'.$u->i_zage_min.'</span>&nbsp;'.__('and','rencontre').'&nbsp;<span>'.$u->i_zage_max.'</span>&nbsp;'.__('years','rencontre').'<br />';
					$a = explode(',', $u->c_zrelation); $as = '';
					foreach($a as $a1) if(isset($rencOpt['for'][$a1])) $as .= $rencOpt['for'][$a1] . ', ';
					echo __('for','rencontre').'&nbsp;<span>'.substr($as,0,-2).'</span>'; 
				}
				?>
				
			</div><!-- .looking -->
		<?php if(!$bl1) { ?>
			<?php $ho = false; if(has_filter('rencSendP', 'f_rencSendP')) $ho = apply_filters('rencSendP', $ho); ?>
			<?php if($rencOpt['fastreg']>1) { ?>
			
			<div class="button right rencLiOff">
				<a href="javascript:void(0)"><?php _e('Send a message','rencontre'); ?></a>
			</div>
			<?php } else if(!$ho && !$pacam && !$rencBlock) { ?>
			
			<div class="button right">
				<a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['renc'].value='ecrire';document.forms['rencMenu'].elements['id'].value='<?php echo $u->user_id; ?>';document.forms['rencMenu'].submit();"><?php _e('Send a message','rencontre');?></a>
			</div>
			<?php } else { ?>
			
			<div class="button right rencLiOff">
				<a href="javascript:void(0)" <?php echo ($ho?'title="'.$ho.'"':
				(($pacam)?'title="'.((!isset($rencCustom['noph']) || empty($rencCustom['nophText']))?addslashes(__("To be more visible and to view photos of other members, you should add one to your profile.","rencontre")):stripslashes($rencCustom['nophText'])).'"':'')
				); ?>><?php _e('Send a message','rencontre'); ?></a>
			</div>
			<?php } ?>
			<?php if(!isset($rencCustom['smile'])) { ?>
				<?php $ho = false; if(has_filter('rencSmileP', 'f_rencSmileP')) $ho = apply_filters('rencSmileP', $ho); ?>
				<?php if($rencOpt['fastreg']>1) { ?>
				
			<div class="button right rencLiOff">
				<a href="javascript:void(0)"><?php _e('Smile','rencontre'); ?></a>
			</div>
				<?php } else if(!$ho && !$rencBlock) { ?>
				
			<div class="button right">
				<a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['renc'].value='sourire';document.forms['rencMenu'].elements['id'].value='<?php echo $u->user_id; ?>';document.forms['rencMenu'].submit();">
					<?php if(!empty($rencCustom['smiw']) && !empty($rencCustom['smiw1'])) echo stripslashes($rencCustom['smiw1']);
					else _e('Smile','rencontre'); ?>

				</a>
			</div>
				<?php } else { ?>
				
			<div class="button right rencLiOff">
				<a href="javascript:void(0)" <?php if($ho) echo 'title="'.$ho.'"'; ?>>
					<?php if(!empty($rencCustom['smiw']) && !empty($rencCustom['smiw1'])) echo stripslashes($rencCustom['smiw1']);
					else _e('Smile','rencontre'); ?>
				
				</a>
			</div>
				<?php } ?>
			<?php } ?>
		<?php } else { ?>
		
			<div class="button right rencLiOff">
				<a href="javascript:void(0)"><?php __('Send a message','rencontre'); ?></a>
			</div>
			<div class="button right rencLiOff">
				<a href="javascript:void(0)">
				<?php if(!empty($rencCustom['smiw']) && !empty($rencCustom['smiw1'])) echo stripslashes($rencCustom['smiw1']);
				else _e('Smile','rencontre'); ?>
				
				</a>
			</div>
		<?php } ?>
		
			<div class="button right">
				<a href="javascript:void(0)" onClick="document.forms['rencMenu'].elements['renc'].value='portrait';document.forms['rencMenu'].elements['id'].value='<?php echo $u->user_id; ?>';document.forms['rencMenu'].submit();"><?php _e('Profile','rencontre');?></a>
			</div>
			<div class="clear"></div>
		</div><!-- .detail -->
		<div class="clear"></div>
	</div><!-- .rencBox -->
	<?php }
