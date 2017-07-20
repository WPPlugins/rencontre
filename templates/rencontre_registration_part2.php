<?php
/*
 * Plugin : Rencontre
 * Template : Registration Part 2/4
 * Last Change : Rencontre 2.1
 * Custom This File ? : wp-content/themes/name-of-my-theme/templates/rencontre_registration_part2.php
*/
?>

	<div class="pleineBox">
		<div class="rencBox">
			<div class="rencNouveau">
				<h3><?php _e('Hello','rencontre'); ?>&nbsp;<?php echo $current_user->user_login; ?>,&nbsp;<?php _e('welcome to the site','rencontre'); ?>&nbsp;<?php echo bloginfo('name'); ?></h3>
				<div class="rencEvol">
					<div class="rencEvol50">2 / 4</div>
					<div style="clear:left;"></div>
				</div>
				<form name="formNouveau" method='post' action=''>
					<input type='hidden' name='nouveau' value='2' />
					<input type='hidden' name='a1' value='' />
					<table>
						<tr>
							<td>
							<?php if(!isset($rencCustom['country'])) { ?>
						
								<div class="th"><?php _e('My country','rencontre');?></div>
								<select id="rencPays" name="pays" onChange="<?php echo $onClick['country']; ?>">
									<?php RencontreWidget::f_pays($rencOpt['pays']); ?>
									
								</select>
							<?php } ?>

							</td>
							<td>
							<?php if(!isset($rencCustom['region'])) { ?>
						
								<div class="th"><?php _e('My region','rencontre');?></div>
								<select id="regionSelect1" name="region">
									<?php RencontreWidget::f_regionBDD(1,$rencOpt['pays']); ?>
									
								</select>
							<?php } ?>
						
							</td>
						</tr>
						<tr>
							<td colspan = "2">
								<div class="th"><?php _e('My city','rencontre');?></div>
								<input id="rencVille" name="ville" type="text" autocomplete="off" size="12" <?php
									if(function_exists('wpGeonames')) echo 'onkeyup="if(!rmap)f_cityMap(this.value,'.(isset($rencCustom['country'])?"'".$rencOpt['pays']."'":'document.getElementById(\'rencPays\').options[document.getElementById(\'rencPays\').selectedIndex].text').',\'0\',1);f_city(this.value,\''.admin_url('admin-ajax.php').'\',document.getElementById(\'rencPays\').options[document.getElementById(\'rencPays\').selectedIndex].value,0);"'; 
									else echo 'onkeyup="if(!rmap)f_cityMap(this.value,document.getElementById(\'rencPays\').options[document.getElementById(\'rencPays\').selectedIndex].text,\'0\',1);"'; 
									?> />
								<input id="gps" name="gps" type="hidden" />
								<div class="rencCity" id="rencCity"></div>
							<?php if(!empty($rencOpt['map'])) { ?>
								
								<div class="rencTMap" id="rencTMap"><?php _e('Adjust the location by moving / zooming the map.','rencontre'); ?>
									<br />
									<?php _e('Clicking on the map will place the cursor.','rencontre'); ?>
									<br /><br />
									<div class="button" onClick="f_cityOk();"><?php _e('Validate the position','rencontre'); ?></div>
								</div>
								<div class="clear" style="height:5px;"></div>					
								<div id="rencMap"></div>
							<?php } ?>
							
							</td>
						</tr>
					</table>
					<div id="buttonPass" class="button">
						<a href="javascript:void(0)" onClick="<?php echo $onClick['save']; ?>"><?php _e('Send','rencontre');?></a>
					</div>
				</form>
				<div id="rencAlert"></div>
			</div><!-- .rencNouveau -->
		</div><!-- .rencBox -->
	</div><!-- .pleineBox -->
	<script type="text/javascript" src="//maps.googleapis.com/maps/api/js?key=<?php if(!empty($rencOpt['mapapi'])) echo $rencOpt['mapapi']; ?>&sensor=false"></script>
