<?php
/*
 * Plugin : Rencontre
 * Template : Portrait Edit
 * Last Change : Rencontre 2.1
 * Custom This File ? : wp-content/themes/name-of-my-theme/templates/rencontre_portrait_edit.php
 * $u0 : U.ID, display_name, c_pays, c_ville, i_sex, i_photo, t_titre, t_annonce, t_profil
*/
?>

	<div class="rencPortrait">
		<h3><?php _e('Edit My Profile','rencontre');?></h3>
		<?php if($infochange) { ?>
		
		<div id="infoChange">
			<div class="rencBox">
				<em><?php echo $infochange; ?></em>
			</div><!-- .rencBox -->
		</div><!-- .infoChange -->
		<?php } ?>
		
		<form name="portraitPhoto" method="post" enctype="multipart/form-data" action="">
			<input type="hidden" name="renc" value="" />
			<input type="hidden" name="a1" value="" />
			<input type="hidden" name="a2" value="" />
			<input type="hidden" name="rnd" value="<?php echo $_SESSION['rnd']; ?>" />
			<div class="petiteBox portraitPhoto left">
				<div class="rencBox">
					<?php if($u0->i_photo) { ?>
					
					<img id="portraitGrande" src="<?php echo $u0->photoUrl.$u0->photo->grande[0]; ?>" alt="" />
					<?php } else { ?>

					<img id="portraitGrande" src="<?php echo plugins_url('rencontre/images/no-photo600.jpg'); ?>" alt="" />
					<?php } ?>
					
					<div class="rencBlocimg">
					<?php for($v=0;$v<$rencOpt['imnb'];++$v) { ?>
						<?php if($u0->i_photo >= $u0->ID*10+$v) { ?>
						
						<a href="javascript:void(0)" onClick="<?php echo $onClick['delete'.$v]; ?>">
							<img onMouseOver="<?php echo $u0->photo->over[$v]; ?>" class="portraitMini" src="<?php echo $u0->photoUrl.$u0->photo->mini[$v]; ?>" alt="<?php _e('Click to delete','rencontre'); ?>" title="<?php _e('Click to delete','rencontre'); ?>" />
						</a>
						<img style="display:none;" src="<?php echo $u0->photoUrl.$u0->photo->grande[$v]; ?>" />
						<?php } else {  ?>
							<?php if($v < $u0->maxPhoto) { ?>
							
						<a href="javascript:void(0)" onClick="<?php echo $onClick['add']; ?>">
							<img class="portraitMini" src="<?php echo plugins_url('rencontre/images/no-photo60.jpg'); ?>" alt="<?php _e('Click to add a photo','rencontre'); ?>" title="<?php _e('Click to add a photo','rencontre'); ?>" />
						</a>
							<?php } else { ?>
							
						<img class="portraitMini" src="<?php echo plugins_url('rencontre/images/no-photo60.jpg'); ?>" alt="<?php _e('You are limited','rencontre'); ?>" title="<?php _e('You are limited','rencontre'); ?>" />
							<?php } ?>
						<?php } ?>
					<?php } ?>
						
					</div><!-- .rencBlocimg -->
					<div id="changePhoto"></div>
					<div class="rencInfo"><?php _e('Click the photo','rencontre');?></div>
					<div>
						<a href="javascript:void(0)" onClick="<?php echo $onClick['deleteAll']; ?>"><?php _e('Delete all photos','rencontre');?></a>
					</div>
				</div><!-- .rencBox -->
			</div><!-- .petiteBox .portraitPhoto -->
		</form>
		<form name='portraitChange' method='post' action=''>
			<?php if(isset($rencOpt['page_id'])) { ?>
			
			<input type="hidden" name="page_id" value="<?php echo $rencOpt['page_id']; ?>" />
			<?php } ?>
			
			<input type='hidden' name='renc' value='' />
			<input type='hidden' name='a1' value='' />
			<input type='hidden' name='a2' value='' />
			<div class="grandeBox right">
				<div class="rencBox">
					<div class="flag">
					<?php if($u0->c_pays!="" && !isset($rencCustom['country']) && !isset($rencCustom['place'])) { ?>
					
						<img src="<?php echo plugins_url('rencontre/images/drapeaux/').$rencDrap[$u0->c_pays]; ?>" alt="<?php echo $rencDrapNom[$u0->c_pays]; ?>" title="<?php echo $rencDrapNom[$u0->c_pays]; ?>" />
					<?php } ?>
					
					</div>
					<div class="grid_10">
						<h3><?php echo $u0->display_name; ?></h3>
					<?php if(!isset($rencCustom['place'])) { ?>
					
						<div class="ville"><?php echo $u0->c_ville; ?></div>
					<?php } ?>
					
						<label><?php _e('My attention-catcher','rencontre');?></label>
						<br />
						<input type="text" name="titre" value="<?php echo stripslashes($u0->t_titre); ?>" />
						<br /><br />
						<label><?php _e('My ad','rencontre');?></label>
						<br />
						<textarea name="annonce" rows="10" style="width:95%;"><?php echo stripslashes($u0->t_annonce); ?></textarea>
					</div>
				</div><!-- .rencBox -->
			</div><!-- .grandeBox -->
			<div id="portraitSauv">
				<span onClick="<?php echo $onClick['sauv']; ?>"><?php _e('Save profile','rencontre');?></span>
			</div>
			<div class="clear"></div>
			<div class="pleineBox portraitProfil">
				<div class="rencBox">
					<div class="br"></div>
				<?php $i = 0; foreach($u0->profil as $key=>$value) { ?>
					
					<span class="portraitOnglet<?php if($i==0) echo ' rencTab'; ?>" id="portraitOnglet<?php echo $i; ?>" onclick="javascript:f_onglet(<?php echo $i; ?>);"><?php echo $key; ?></span>
					<?php ++$i; ?>
				<?php } ?>
				<?php $i = 0; foreach($u0->profil as $key=>$value) { ?>

					<table <?php if($i==0) echo 'style="display:table;"'; ?> id="portraitTable<?php echo $i; ?>" border="0">
					<?php foreach($value as $v) { ?>
					
						<tr>
							<td><?php echo $v->label; ?></td>
							<td>
							<?php if($v->type==1) { ?>
								
								<input type="text" name="text<?php echo $v->id; ?>" value="<?php echo $v->active; ?>" />
							<?php } else if($v->type==2) { ?>
							
								<textarea name="area<?php echo $v->id; ?>" rows="4" cols="50"><?php echo $v->active; ?></textarea>
							<?php } else if($v->type==3) { ?>
							
								<select name="select<?php echo $v->id; ?>">
									<option value="0">&nbsp;</option>
								<?php $j = 0; foreach($v->valeur as $valeur) { ?>
									
									<option value="<?php echo ($j+1); ?>"<?php if($v->active===$j) echo ' selected'; ?>><?php echo $valeur; ?></option>
									<?php ++$j; ?>
								<?php } ?>
								
								</select>
							<?php } else if($v->type==4) { ?>
								<?php $j = 0; foreach($v->valeur as $valeur) { ?>
									
								<label><?php echo $valeur; ?>&nbsp;:&nbsp;<input type="checkbox" name="check<?php echo $v->id; ?>[]" value="<?php echo $j; ?>"<?php if(strpos($v->active,','.$j.',')!==false) echo ' checked'; ?> /></label>
									<?php ++$j; ?>
								<?php } ?>
							<?php } else if($v->type==5) { ?>
								
								<select name="ns<?php echo $v->id; ?>">
									<option value="0">&nbsp;</option>';
								<?php $j = 0; foreach($v->valeur as $valeur) { ?>
									
									<option value="<?php echo ($j+1); ?>"<?php if($v->active===$j) echo ' selected'; ?>><?php echo $valeur; ?></option>
									<?php ++$j; ?>
								<?php } ?>
								
								</select>
							<?php } ?>
						
							</td>
						</tr>
					
					<?php } ?>
						
					</table>
					<?php ++$i; ?>
				<?php } ?>

				</div><!-- .rencBox -->
			</div><!-- .pleineBox .portraitProfil -->
		</form>
	</div><!-- .rencPortrait -->
	<?php echo $script; ?>
