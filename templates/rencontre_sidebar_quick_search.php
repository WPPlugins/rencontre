<?php
/*
 * Plugin : Rencontre
 * Template : Sidebar Quick Search
 * Last Change : Rencontre 2.0
 * Custom This File ? : wp-content/themes/name-of-my-theme/templates/rencontre_sidebar_quick_search.php
 * $u0 : ID, display_name, user_login, c_ip, c_pays, c_ville, i_sex, d_naissance, i_zsex, c_zsex, i_zage_min, i_zage_max, i_zrelation, c_zrelation, i_photo, t_action, sourireIn, contactIn, visite, looking, forwhat, homo, zsex
*/
?>

	<div class="rencBox">
		<h3><?php _e('Quick Search','rencontre');?></h3>
		<form name='formMonAccueil' method='get' action=''>
		<?php if(isset($rencOpt['page_id'])) { ?>
		
			<input type="hidden" name="page_id" value="<?php echo $rencOpt['page_id']; ?>" />
		<?php } ?>
			
			<input type='hidden' name='renc' value='' />
			<input type='hidden' name='zsex' value='<?php echo $u0->zsex; ?>' />
			<input type='hidden' name='homo' value='<?php echo $u0->homo; ?>' />
			<input type='hidden' name='pagine' value='0' />
		<?php if(!isset($rencCustom['born'])) { ?>
			
			<div class="rencItem"><?php _e('Age','rencontre');?>&nbsp;
				<span><?php _e('from','rencontre');?>&nbsp;
					<select name="ageMin" onChange="<?php echo $onClick['agemin']; ?>">
						<?php for($v=(isset($rencCustom['agemin'])?intval($rencCustom['agemin']):18);$v<=(isset($rencCustom['agemax'])?intval($rencCustom['agemax']):99);++$v) { ?>
						
						<option value="<?php echo $v; ?>"><?php echo $v; ?>&nbsp;<?php _e('years','rencontre'); ?></option>
						<?php } ?>
						
					</select>
				</span>
				<span>&nbsp;<?php _e('to','rencontre');?>&nbsp;
					<select name="ageMax" onChange="<?php echo $onClick['agemax']; ?>">
						<?php for($v=(isset($rencCustom['agemin'])?intval($rencCustom['agemin']):18);$v<(isset($rencCustom['agemax'])?intval($rencCustom['agemax']):99);++$v) { ?>
						
						<option value="<?php echo $v; ?>"><?php echo $v; ?>&nbsp;<?php _e('years','rencontre'); ?></option>
						<?php } ?>
						
						<option value="<?php echo (isset($rencCustom['agemax'])?intval($rencCustom['agemax']):99); ?>" selected><?php echo (isset($rencCustom['agemax'])?intval($rencCustom['agemax']):99); ?>&nbsp;<?php _e('years','rencontre');?></option>
					</select>
				</span>
			</div>
		<?php } ?>
		<?php if(!isset($rencCustom['country'])) { ?>
		
			<div class="rencItem"><?php _e('Country','rencontre');?>&nbsp;:
				<select name="pays" onChange="<?php echo $onClick['country']; ?>">
					<?php RencontreWidget::f_pays($rencOpt['pays']); ?>
					
				</select>
			</div>
		<?php } ?>
		<?php if(!isset($rencCustom['place']) && !isset($rencCustom['region'])) { ?>

			<div class="rencItem"><?php _e('Region','rencontre');?>&nbsp;:
				<select id="regionSelect1" name="region">
					<?php RencontreWidget::f_regionBDD(1,$rencOpt['pays']); ?>
					
				</select>
			</div>
		<?php } ?>
		<?php if(isset($rencCustom['relationQ']) && isset($rencOpt['for'])) { ?>
			
			<div class="rencItem"><?php _e('Relation','rencontre'); ?>&nbsp;:
				<select name="relation">
					<option value="">-</option>
				<?php for($v=(isset($rencCustom['relation'])?3:0);$v<(isset($rencCustom['relation'])?count($rencOpt['for']):3);++$v) { ?>
				
					<option value="<?php echo $v; ?>"><?php echo $rencOpt['for'][$v]; ?></option>
				<?php } ?>
				
				</select>
			</div>
		<?php } ?>
			<?php if($profilQuickSearch1) echo $profilQuickSearch1; ?>
			
			<?php if($profilQuickSearch2) echo $profilQuickSearch2; ?>

			<div class="button<?php echo $find['class']; ?>">
				<a href="javascript:void(0)" <?php echo $onClick['find']; ?> title="<?php echo $find['title']; ?>"><?php _e('Find','rencontre'); ?></a>
			</div>
			<div class="clear"></div>
		</form>
	</div><!-- .rencBox -->
