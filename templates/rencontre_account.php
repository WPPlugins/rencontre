<?php
/*
 * Plugin : Rencontre
 * Template : Account
 * Last Change : Rencontre 2.1
 * Custom This File ? : wp-content/themes/name-of-my-theme/templates/rencontre_account.php
 * $u0 : ID, user_email, user_login, c_pays, c_region, c_ville, i_sex, d_naissance, i_taille, i_poids, i_zsex, c_zsex, i_zage_min, i_zage_max, i_zrelation, c_zrelation, e_lat, e_lon, t_action
 * Filter : do_action('rencontre_account', $f, $g) - see below
*/
?>

	<h2><?php _e('Change password','rencontre');?></h2>
	<form name="formPass" method='post' action=''>
	<input type='hidden' name='renc' value='' />
	<input type='hidden' name='id' value='' />
	<table>
		<tr>
			<td>
				<div class="th"><?php _e('Former','rencontre');?></div>
				<input name="pass0" type="password" size="9">
			</td>
			<td>
				<div class="th"><?php _e('New','rencontre');?></div>
				<input name="pass1" type="password" size="9">
			</td>
			<td>
				<div class="th"><?php _e('Retype the new','rencontre');?></div>
				<input name="pass2" type="password" size="9">
			</td>
		</tr>
		<tr>
			<td colspan="3" style="border:none">
				<div id="buttonPass" class="button"><a href="javascript:void(0)" onClick="<?php echo $onClick['change']; ?>"><?php _e('Change','rencontre'); ?></a></div>
			</td>
		</tr>
	</table>
	</form>
	<div id="rencAlert"></div>
	<h2><?php _e('My Account','rencontre'); ?><span style="font-size:16px;font-weight:400;margin-left:10px;">(<?php echo $u0->user_email; ?>)</span></h2>
	<form name="formNouveau" method='post' action=''>
		<input type='hidden' name='nouveau' value='update' />
		<input type='hidden' name='a1' value='' />
		<input type='hidden' name='pseudo' value='<?php echo $u0->user_login; ?>' />
		<table style="border-bottom:none;margin-bottom:0;">
			<tr>
				<td>
					<div class="th"><?php _e('I am','rencontre');?></div>
					<select name="sex">
					<?php for($v=(isset($rencCustom['sex'])?2:0);$v<(isset($rencCustom['sex'])?count($rencOpt['iam']):2);++$v) { ?>
					
						<option value="<?php echo $v; ?>"<?php if($u0->i_sex==$v) echo ' selected'; ?>><?php echo $rencOpt['iam'][$v]; ?></option>
					<?php } ?>
					
					</select>
				</td>
				<td>
				<?php if(!isset($rencCustom['born'])) { ?>
				
					<div class="th"><?php _e('Born','rencontre'); ?></div>
					<select name="jour">
						<?php for($v=1;$v<32;++$v) { ?>
						
						<option value="<?php echo $v; ?>"<?php if($v==$j) echo ' selected'; ?>><?php echo $v; ?></option>
						<?php } ?>
						
					</select>
					<select name="mois">
						<?php for($v=1;$v<13;++$v) { ?>
						
						<option value="<?php echo $v; ?>"<?php if($v==$m) echo ' selected'; ?>><?php echo $v; ?></option>
						<?php } ?>
						
					</select>
					<select name="annee">
						<?php for($v=$oldmax;$v<$oldmin;++$v) { ?>
						
						<option value="<?php echo $v; ?>"<?php if($v==$Y) echo ' selected'; ?>><?php echo $v; ?></option>
						<?php } ?>
						
					</select>
				<?php } ?>
				
				</td>
			</tr>
			<?php if(!isset($rencCustom['place'])) { ?>
			
			<tr>
				<td>
				<?php if(!isset($rencCustom['country'])) { ?>
				
					<div class="th"><?php _e('My country','rencontre'); ?></div>
					<select id="rencPays" name="pays" onChange="<?php echo $onClick['country']; ?>">
						<?php RencontreWidget::f_pays($u0->c_pays); ?>
						
					</select>
				<?php } ?>
				
				</td>
				<td>
				<?php if(!isset($rencCustom['region'])) { ?>
				
					<div class="th"><?php _e('My region','rencontre'); ?></div>
					<select id="regionSelect2" name="region">
						<?php if($u0->c_region) RencontreWidget::f_regionBDD($u0->c_region,$u0->c_pays);
						else RencontreWidget::f_regionBDD(1,$u0->c_pays); ?>
						
					</select>
				<?php } ?>
				
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<div class="th"><?php _e('My city','rencontre'); ?></div>
					<input id="rencVille" name="ville" type="text" autocomplete="off" size="18" value="<?php echo $u0->c_ville; ?>" <?php if(function_exists('wpGeonames')) echo 'onkeyup="f_city(this.value,\''.admin_url('admin-ajax.php').'\','.(!isset($rencCustom['country'])?'document.getElementById(\'rencPays\').options[document.getElementById(\'rencPays\').selectedIndex].value':'\''.$u0->c_pays.'\'').',0);"'; ?> />
					<input id="gps" name="gps" type="hidden" value="<?php echo $u0->e_lat.'|'.$u0->e_lon; ?>" />
					<div class="rencCity" id="rencCity"></div>
					<?php if(!empty($rencOpt['map'])) { ?>
					
					<div class="rencTMap" id="rencTMap"><?php _e('Adjust the location by moving / zooming the map.','rencontre'); ?>
						<br />
						<?php _e('Clicking on the map will place the cursor.','rencontre'); ?>
						<br /><br />
						<div class="button" onClick="<?php echo $onClick['validate']; ?>"><?php _e('Validate the position','rencontre'); ?></div>
					</div>
					<div class="clear" style="height:5px;"></div>					
					<div id="rencMap"></div>
					<?php } ?>
				
				</td>
			</tr>
					
				<?php if(!empty($rencOpt['map'])) echo $scriptMap; ?>
			
			<?php } ?>
			<?php if(empty($rencCustom['weight']) || empty($rencCustom['size'])) { ?>

			<tr>
				<td>
				<?php if(empty($rencCustom['size'])){ ?>
				
					<div class="th"><?php _e('My size','rencontre'); ?></div>
					<select name="taille">
					<?php for($v=140;$v<221;++$v) { ?>
						<?php if(empty($rencCustom['sizeu'])) { ?>
						
						<option value="<?php echo $v; ?>"<?php if($v==$u0->i_taille) echo ' selected'; ?>><?php echo $v.'&nbsp;'.__('cm','rencontre'); ?></option>
						<?php } else { ?>
						
						<option value="<?php echo $v; ?>"<?php if($v==$u0->i_taille) echo ' selected'; ?>><?php echo (floor($v/24-1.708)).'&nbsp;'.__('ft','rencontre').'&nbsp;'.(round(((($v/24-1.708)-floor($v/24-1.708))*12),1)).'&nbsp;'.__('in','rencontre'); ?></option>
						<?php } ?>
					<?php } ?>
					
					</select>
				<?php } ?>
				
				</td>
				<td>
				<?php if(empty($rencCustom['weight'])) { ?>

					<div class="th"><?php _e('My weight','rencontre'); ?></div>
					<select name="poids">
					<?php for($v=40;$v<140;++$v) { ?>
						<?php if(empty($rencCustom['weightu'])) { ?>
						
						<option value="<?php echo $v; ?>"<?php if($v==$u0->i_poids) echo ' selected'; ?>><?php echo $v.'&nbsp;'.__('kg','rencontre'); ?></option>
						<?php } else { ?>
						
						<option value="<?php echo $v; ?>"<?php if($v==$u0->i_poids) echo ' selected'; ?>><?php echo ($v*2+10).'&nbsp;'.__('lbs','rencontre'); ?></option>
						<?php } ?>
					<?php } ?>
					
					</select>
				<?php } ?>
				
				</td>
			</tr>
			<?php } ?>
				
			<tr>
				<td>
					<div class="th"><?php _e('I\'m looking for','rencontre'); ?></div>
				<?php if(empty($rencCustom['multiSR'])) { ?>
					
					<select name="zsex">
					<?php for($v=(isset($rencCustom['sex'])?2:0);$v<(isset($rencCustom['sex'])?count($rencOpt['iam']):2);++$v) { ?>
					
						<option value="<?php echo $v; ?>"<?php if($u0->i_zsex==$v) echo ' selected'; ?>><?php echo $rencOpt['iam'][$v]; ?></option>
					<?php } ?>
					
					</select>
				<?php }	else { ?>
					<?php for($v=(isset($rencCustom['sex'])?2:0);$v<(isset($rencCustom['sex'])?count($rencOpt['iam']):2);++$v) { ?>

					<?php echo $rencOpt['iam'][$v]; ?>&nbsp;<input type="checkbox" name="zsex[]" value="<?php echo $v; ?>" <?php if(strpos($u0->c_zsex,','.$v.',')!==false) echo 'checked'; ?> />
					<?php } ?>
				<?php } ?>
						
				</td>
				<td>
				<?php if(!isset($rencCustom['born'])) { ?>
				
					<div class="th"><?php _e('Age min/max','rencontre'); ?></div>
					<select name="zageMin" onChange="<?php echo $onClick['agemin']; ?>">
					<?php for($v=(isset($rencCustom['agemin'])?intval($rencCustom['agemin']):18);$v<(isset($rencCustom['agemax'])?intval($rencCustom['agemax']):99);++$v) { ?>
						
						<option value="<?php echo $v; ?>"<?php if($v==$u0->i_zage_min) echo ' selected'; ?>><?php echo $v.'&nbsp;'.__('years','rencontre'); ?></option>
					<?php } ?>
						
					</select>
					<select name="zageMax" onChange="<?php echo $onClick['agemax']; ?>">
					<?php for($v=(isset($rencCustom['agemin'])?intval($rencCustom['agemin']):18)+1;$v<(isset($rencCustom['agemax'])?intval($rencCustom['agemax']):99)+1;++$v) { ?>
						
						<option value="<?php echo $v; ?>"<?php if($v==$u0->i_zage_max) echo ' selected'; ?>><?php echo $v.'&nbsp;'.__('years','rencontre'); ?></option>
					<?php } ?>
						
					</select>
				<?php } ?>

				</td>
			</tr>
			<tr>
				<td>
					<div class="th"><?php _e('For','rencontre'); ?></div>
				<?php if(empty($rencCustom['multiSR'])) { ?>
					
					<select name="zrelation">
					<?php for($v=(isset($rencCustom['relation'])?3:0);$v<(isset($rencCustom['relation'])?count($rencOpt['for']):3);++$v) { ?>
					
						<option value="<?php echo $v; ?>"<?php if($u0->i_zrelation==$v) echo ' selected'; ?>><?php echo $rencOpt['for'][$v]; ?></option>
					<?php } ?>
					
					</select>
				<?php } else { ?>
					<?php for($v=(isset($rencCustom['relation'])?3:0);$v<(isset($rencCustom['relation'])?count($rencOpt['for']):3);++$v) { ?>
					
						<?php echo $rencOpt['for'][$v]; ?>'&nbsp;<input type="checkbox" name="zrelation[]" value="<?php echo $v; ?>"<?php if(strpos($u0->c_zrelation,','.$v.',')!==false) echo ' checked'; ?> />
					<?php } ?>
				<?php } ?>
						
				</td>
				<td>
					<div class="button">
						<a href="javascript:void(0)" onClick="<?php echo $onClick['save']; ?>"><?php _e('Save','rencontre'); ?></a>
					</div>
				<?php if(empty($rencCustom['unmail'])) { ?>
				
					<p style="clear:both;font-size:.8em;color:#777;padding-top:15px;text-align:right;">
						<?php _e('No email from this site','rencontre'); ?>
						&nbsp;<input type="checkbox" name="nomail"<?php if(strpos($u0->t_action,",nomail,")!==false) echo ' checked'; ?> />
					</p>
				<?php } ?>
					
				</td>
			</tr>
		</table>
	</form>
	<?php if(empty($rencCustom['unreg'])) { ?>
	
	<h2><?php _e('Account deletion','rencontre');?></h2>
	<form name="formFin" method='post' action=''>
		<input type='hidden' name='renc' value='' />
		<input type='hidden' name='id' value='' />
		<table>
			<tr>
				<td>
					<div class="th"><?php _e('This action will result in the complete deletion of your account and everything about you from our server. We do not keep historical accounts.','rencontre');?></div>
					<strong><?php _e('Please note that this action is irreversible !','rencontre');?></strong>
					<div id="buttonPass" class="button">
						<a href="javascript:void(0)" onClick="<?php echo $onClick['delete']; ?>"><?php _e('Delete Account','rencontre');?></a>
					</div>
				</td>
			</tr>
		</table>
	</form>
	<?php } ?>

<?php
/*
 * Data from this Template (POST) are treated in rencontre_widget.php, static function f_updateMember($f).
 * You can replace this function by adding this example in the functions.php file of your theme :
 * 
 *		add_action('rencontre_account','my-account-save',10,1);
 *		function my-account-save($f)
 *			{
 *			// $f : my user_id
 *			global $wpdb; global $rencOpt;
 *			... see static function f_updateMember($f) for your code creation.
 *			...
 *			}
*/
?>
