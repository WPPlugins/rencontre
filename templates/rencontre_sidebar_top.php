<?php
/*
 * Plugin : Rencontre
 * Template : Sidebar Top
 * Last Change : Rencontre 2.0.1
 * Custom This File ? : wp-content/themes/name-of-my-theme/templates/rencontre_sidebar_top.php
 * $u0 : ID, display_name, user_login, c_ip, c_pays, c_ville, i_sex, d_naissance, i_zsex, c_zsex, i_zage_min, i_zage_max, i_zrelation, c_zrelation, i_photo, t_action, sourireIn, contactIn, visite, looking, forwhat, homo, zsex
*/
?>

	<div class="rencBox">
		<?php if($u0->i_photo!=0) { ?>
		
		<img class="maPhoto" src="<?php echo $rencDiv['baseurl'].'/portrait/'.floor(($u0->ID)/1000).'/'.Rencontre::f_img(($u0->ID*10).'-mini').'.jpg?r='.rand(); ?>" alt="<?php echo $u0->display_name; ?>"/>
		<?php } else { ?>
		
		<img class="maPhoto" src="<?php echo plugins_url('rencontre/images/no-photo60.jpg'); ?>" alt="<?php echo $u0->display_name; ?>" />
		<?php } ?>
		
		<?php echo $u0->user_login; ?>
		
		<?php if($u0->c_pays!="" && !isset($rencCustom['country']) && !isset($rencCustom['place'])) { ?>
		
		<img class="monFlag" src="<?php echo plugins_url('rencontre/images/drapeaux/').$rencDrap[$u0->c_pays]; ?>" alt="<?php echo $rencDrapNom[$u0->c_pays]; ?>" title="<?php echo $rencDrapNom[$u0->c_pays]; ?>" />
		<?php } ?>
		<?php if(!isset($rencCustom['born'])) { ?>
		
		<div class="monAge"><?php _e('Age','rencontre'); ?>&nbsp;:&nbsp;<?php echo Rencontre::f_age($u0->d_naissance); ?>&nbsp;<?php _e('years','rencontre'); ?></div>
		<?php } ?>
		<?php if(!isset($rencCustom['place'])) { ?>
		
		<div class="maVille"><?php _e('City','rencontre'); ?>&nbsp;:&nbsp;<?php echo $u0->c_ville; ?></div>
		<?php } ?>
		
		<div id="tauxProfil"></div>
		<div class="maRecherche  firstMaj">
		<?php if($u0->looking) { ?>
			<?php _e('I\'m looking for','rencontre'); ?>&nbsp;<em><?php echo $u0->looking; ?></em>
		<?php } ?>
		<?php if($u0->forwhat) { ?>
			&nbsp;<?php _e('for','rencontre'); ?>&nbsp;<em><?php echo $u0->forwhat; ?></em>
		<?php } ?>
		
		</div>
		<?php if(!isset($rencCustom['smile'])) { ?>
		
		<div class="mesSourire">
			<a href="javascript:void(0)" onClick="<?php echo $onClick['sourireIn']; ?>">
				<?php if(!empty($rencCustom['smiw']) && !empty($rencCustom['smiw1'])) echo stripslashes($rencCustom['smiw1']);
				else _e('Smile','rencontre'); ?>
				<?php if(count($u0->sourireIn)>49) { ?>
				
				:&nbsp;>50
				<?php } else { ?>
				
				:&nbsp;<?php echo count($u0->sourireIn); ?>
				<?php } ?>
				
			</a>
		</div>
		<?php } ?>
		
		<div class="mesSourire">
			<a href="javascript:void(0)" onClick="<?php echo $onClick['visite']; ?>">
				<?php if(!empty($rencCustom['loow']) && !empty($rencCustom['loow1'])) echo stripslashes($rencCustom['loow1']);
				else _e('Look','rencontre'); ?>
				<?php if(count($u0->visite)>49) { ?>
				
				:&nbsp;>50
				<?php } else { ?>
				
				:&nbsp;<?php echo count($u0->visite); ?>
				<?php } ?>
				
			</a>
		</div>
		
		<div class="mesSourire">
			<a href="javascript:void(0)" onClick="<?php echo $onClick['contactIn']; ?>">
				<?php _e('Contact requests','rencontre'); ?>
				<?php if(count($u0->contactIn)>49) { ?>
				
				:&nbsp;>50
				<?php } else { ?>
				
				:&nbsp;<?php echo count($u0->contactIn); ?>
				<?php } ?>
				
			</a>
		</div>
	</div><!-- .rencBox -->
	
	<div class="rencBox">
		<?php if(!isset($rencCustom['smile'])) { ?>
		
		<div class="rencItem">
			<a href="javascript:void(0)" onClick="<?php echo $onClick['sourireOut']; ?>">
				<?php if(!empty($rencCustom['smiw']) && !empty($rencCustom['smiw2'])) echo stripslashes($rencCustom['smiw2']);
				else _e('Who I smiled ?','rencontre'); ?>
				
			</a>
		</div>
		<?php } ?>
		
		<div class="rencItem">
			<a href="javascript:void(0)" onClick="<?php echo $onClick['contactOut']; ?>"><?php _e('Who I asked for a contact ?','rencontre');?></a>
		</div>
		<div class="rencItem">
			<a href="javascript:void(0)" onClick="<?php echo $onClick['bloque']; ?>"><?php _e('Who I\'ve blocked ?','rencontre');?></a>
		</div>
	</div><!-- .rencBox -->
