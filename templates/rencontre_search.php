<?php
/*
 * Plugin : Rencontre
 * Template : Search
 * Last Change : Rencontre 2.1
 * Custom This File ? : wp-content/themes/name-of-my-theme/templates/rencontre_search.php
 * $u0 : ID, i_sex, i_zsex, c_zsex, e_lat, e_lon, zsex, homo
*/
?>

	<div class="rencBox">
		<h3><?php _e('Search','rencontre'); ?></h3>
		<form id="formTrouve" name='formTrouve' method='get' action=''>
		<?php if(isset($rencOpt['page_id'])) { ?>
			
			<input type="hidden" name="page_id" value="<?php echo $rencOpt['page_id']; ?>" />
		<?php } ?>
			
			<input type='hidden' name='renc' value='' />
			<input type='hidden' name='id' value='<?php echo $u0->ID; ?>' />
			<input type='hidden' name='zsex' value='<?php echo $u0->zsex; ?>' />
			<input type='hidden' name='homo' value='<?php echo $u0->homo; ?>' />
			<input type='hidden' name='pagine' value='0' />
			<table>
			<?php if(!isset($rencCustom['born'])) { ?>
			
				<tr>
					<td><?php _e('Age','rencontre');?>&nbsp;:</td>
					<td colspan="2">
						<span><?php _e('from','rencontre');?>&nbsp;
							<select name="ageMin" onChange="<?php echo $onClick['agemin']; ?>">
								<?php for($v=(isset($rencCustom['agemin'])?intval($rencCustom['agemin']):18);$v<=(isset($rencCustom['agemax'])?intval($rencCustom['agemax']):99);++$v) { ?>
								
								<option value="<?php echo $v; ?>"><?php echo $v?>&nbsp;<?php _e('years','rencontre'); ?></option>
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
					</td>
				</tr>
			<?php } ?>
			<?php if(!isset($rencCustom['size'])) { ?>
			
				<tr>
					<td><?php _e('Size','rencontre');?>&nbsp;:</td>
					<td colspan="2">
						<span><?php _e('from','rencontre');?>&nbsp;
							<select name="tailleMin" onChange="<?php echo $onClick['sizemin']; ?>">
								<?php for($v=140;$v<221;++$v) { ?>
									<?php if(empty($rencCustom['sizeu'])) { ?>
									
									<option value="<?php echo $v; ?>"><?php echo $v; ?>&nbsp;<?php _e('cm','rencontre'); ?></option>
									<?php } else { ?>
									
									<option value="<?php echo $v; ?>"><?php echo (floor($v/24-1.708)); ?>&nbsp;<?php _e('ft','rencontre'); ?>&nbsp;<?php echo (round(((($v/24-1.708)-floor($v/24-1.708))*12),1)); ?>&nbsp;<?php _e('in','rencontre'); ?></option>
									<?php } ?>
								<?php } ?>
								
							</select>
						</span>
						<span>&nbsp;<?php _e('to','rencontre');?>&nbsp;
							<select name="tailleMax" onChange="<?php echo $onClick['sizemax']; ?>">
								<?php for($v=140;$v<221;++$v) { ?>
									<?php if(empty($rencCustom['sizeu'])) { ?>
									
									<option value="<?php echo $v; ?>" <?php if($v==220) echo 'selected'; ?>><?php echo $v; ?>&nbsp;<?php _e('cm','rencontre')?></option>
									<?php } else { ?>
									
									<option value="<?php echo $v; ?>" <?php if($v==220) echo 'selected'; ?>><?php echo (floor($v/24-1.708)); ?>&nbsp;<?php _e('ft','rencontre'); ?>&nbsp;<?php echo (round(((($v/24-1.708)-floor($v/24-1.708))*12),1)); ?>&nbsp;<?php _e('in','rencontre'); ?></option>
									<?php } ?>
								<?php } ?>
								
							</select>
						</span>
					</td>
				</tr>
			<?php } ?>
			<?php if(!isset($rencCustom['weight'])) { ?>
			
				<tr>
					<td><?php _e('Weight','rencontre');?>&nbsp;:</td>
					<td colspan="2">
						<span><?php _e('from','rencontre');?>&nbsp;
							<select name="poidsMin" onChange="<?php echo $onClick['weightmin']; ?>">
								<?php for($v=40;$v<141;++$v) { ?>
									<?php if(empty($rencCustom['weightu'])) { ?>
									
									<option value="<?php echo ($v+100); ?>"><?php echo $v; ?>&nbsp;<?php _e('kg','rencontre'); ?></option>
									<?php } else { ?>
									
									<option value="<?php echo ($v+100); ?>"><?php echo ($v*2+10); ?>&nbsp;<?php _e('lbs','rencontre'); ?></option>
									<?php } ?>
								<?php } ?>
												
							</select>
						</span>
						<span>&nbsp;<?php _e('to','rencontre');?>&nbsp;
							<select name="poidsMax" onChange="<?php echo $onClick['weightmax']; ?>">
								<?php for($v=40;$v<141;++$v) { ?>
									<?php if(empty($rencCustom['weightu'])) { ?>
									
									<option value="<?php echo ($v+100); ?>"<?php if($v==140) echo ' selected'; ?>><?php echo $v; ?>&nbsp;<?php _e('kg','rencontre'); ?></option>
									<?php } else { ?>
									
									<option value="<?php echo ($v+100); ?>"<?php if($v==140) echo ' selected'; ?>><?php echo ($v*2+10); ?>&nbsp;<?php _e('lbs','rencontre'); ?></option>
									<?php } ?>
								<?php } ?>
								
							</select>
						</span>
					</td>
				</tr>
			<?php } ?>
			<?php if(isset($rencCustom['sex'])) { ?>
			
				<tr>
					<td><?php _e('Gender','rencontre');?>&nbsp;:</td>
					<td colspan="2">
						<select name="z2sex">
							<option value="">-</option>
							<?php for($v=2;$v<count($rencOpt['iam']);++$v) { ?>
							
							<option value="<?php echo $v; ?>"<?php if($v==$u0->i_zsex) echo ' selected'; ?>><?php echo $rencOpt['iam'][$v]; ?></option>
							<?php } ?>
						</select>
					</td>
				</tr>
			<?php } ?>
			<?php if(!isset($rencCustom['country'])) { ?>
			
				<tr>
					<td><?php _e('Country','rencontre');?>&nbsp;:</td>
					<td colspan="2">
						<select id="rencPays" name="pays" onChange="<?php echo $onClick['country']; ?>">
						<?php RencontreWidget::f_pays($rencOpt['pays']); ?>
						
						</select>
					</td>
				</tr>
			<?php } ?>
			<?php if(!isset($rencCustom['place'])) { ?>
				<?php if(!isset($rencCustom['region'])) { ?>
				
				<tr>
					<td><?php _e('Region','rencontre');?>&nbsp;:</td>
					<td colspan="2">
						<select id="regionSelect2" name="region">
						<?php RencontreWidget::f_regionBDD(1,$rencOpt['pays']); ?>
						
						</select>
					</td>
				</tr>
				<?php } ?>
				
				<tr>
					<td><?php _e('City','rencontre');?>&nbsp;:</td>
					<td colspan="2">
						<input id="rencVille" name="ville" type="text" size="12" value="" <?php if($map) echo $onClick['city']; ?>/>
						<div style="text-align:right;float:right;color:#888;font-size:80%;padding-top:4px;">
							<?php if(!$map && !$filtermap && $rencOpt['map'] && $rencOpt['pays']!='') _e('Incomplete account: no GoogleMap','rencontre');?>
	
						</div>
						<input id="gps" name="gps" type="hidden" />
						<input id="rencKm" name="km" type="hidden" />
					</td>
				</tr>
				<?php if($map) { ?>

				<tr id="renctrMap1" style="display:none">
					<td><?php _e('Max range (km)','rencontre');?>&nbsp;:</td>
					<td colspan="2">
						<input id="rencKm" name="km" type="text" size="5" onkeyup="f_cityKm(this.value);" />
						<div class="rencCity" id="rencCity"></div>
						<div class="rencTMap" id="rencTMap">
							<?php _e('Adjust the location by moving / zooming the map.','rencontre');?>
							<br />
							<?php _e('Clicking on the map will place the cursor.','rencontre');?>
							<br /><br />
							<div class="button" onClick="<?php echo $onClick['validate']; ?>"><?php _e('Validate the position','rencontre');?></div>
						</div>
					</td>
				</tr>
				<tr id="renctrMap2"  style="display:none">
					<td colspan="3">
						<div id="rencMap"></div>
					</td>
				</tr>
				<?php } ?>
			<?php } ?>
			
				<tr>
					<td><?php _e('Only with picture','rencontre');?>&nbsp;</td>
					<td colspan="2">
						<input type="checkbox" name="photo" value="1" />
					</td>
				</tr>
				<tr>
					<td><?php _e('Relation','rencontre');?>&nbsp;:</td>
					<td colspan="2">
						<select name="relation">
							<option value="" selected>-</option>
							<?php for($v=(isset($rencCustom['relation'])?3:0);$v<(isset($rencCustom['relation'])?count($rencOpt['for']):3);++$v) { ?>
							
							<option value="<?php echo $v; ?>"><?php echo $rencOpt['for'][$v]; ?></option>
							<?php } ?>
							
						</select>
					</td>
				</tr>
			<?php echo $moreSearch1; ?>
			
				<tr>
					<td><?php _e('Word in the ad','rencontre');?>&nbsp;:</td>
					<td colspan="2">
						<input type="text" name="mot" />
					</td>
				</tr>
				<tr>
					<td><?php _e('Alias','rencontre');?>&nbsp;:</td>
					<td colspan="2">
						<input type="text" name="pseudo" />
					</td>
				</tr>
				<tr style="background-color:inherit">
					<td></td>
					<td colspan="2">
						<div class="button<?php echo $find['class']; ?>">
							<a href="javascript:void(0)" <?php echo $onClick['find']; ?> title="<?php echo $find['title']; ?>"><?php _e('Find','rencontre'); ?></a>
						</div>
					</td>
				</tr>
			</table>
		</form>
	</div><!-- .rencBox -->
