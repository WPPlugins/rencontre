<?php
/*
 * Plugin : Rencontre
 * Template : Portrait
 * Last Change : Rencontre 2.0.2
 * Custom This File ? : wp-content/themes/name-of-my-theme/templates/rencontre_portrait.php
 * $u : ID, display_name, c_pays, c_region, c_ville, i_sex, d_naissance, i_taille, i_poids, i_zsex, c_zsex, i_zage_min, i_zage_max, i_zrelation, c_zrelation, i_photo, e_lat, e_lon, d_session, t_titre, t_annonce, t_profil, t_action, maxPhoto, photo (object), looking, forwhat, session, profil, online
 * $u0 (myself) : ID
*/
?>

	<div class="rencPortrait">
		<?php if($infochange) { ?>
		
		<div id="infoChange">
			<div class="rencBox">
				<em><?php echo $infochange; ?></em>
			</div><!-- .rencBox -->
		</div><!-- .infoChange -->
		<?php } ?>
		<div class="petiteBox left">
			<div class="rencBox calign">
			<?php if(!empty($u->photo->grande[0])) { ?>
			
				<img id="portraitGrande" alt="" src="<?php echo $u->photoUrl.$u->photo->grande[0]; ?>" <?php echo $onClick['thumb']; ?> title="<?php echo $title['thumb']; ?>" />
			<?php } else { ?>

				<img id="portraitGrande" alt="" src="<?php echo plugins_url('rencontre/images/no-photo600.jpg'); ?>" <?php echo $onClick['thumb']; ?> title="<?php echo $title['thumb']; ?>" />
			<?php } ?>
			
				<div class="rencBlocimg">
			<?php for($v=0;$v<$u->maxPhoto;++$v) { ?>
				<?php if(($u->ID)*10+$v <= $u->i_photo) { ?>
					<?php if(!$disable['thumb']) { ?>
						
					<a class="rencZoombox zgallery1" title="<?php echo $title['zoombox']; ?>" href="<?php echo $u->photoUrl.$u->photo->full[$v]; ?>">
					<?php } else { ?>
						
					<a href="javascript:void(0)" <?php echo $onClick['thumb']; ?>>
					<?php } ?>

						<img alt="" class="portraitMini" onMouseOver="<?php echo $u->photo->over[$v]; ?>" src="<?php echo $u->photoUrl.$u->photo->mini[$v]; ?>" title="<?php echo $title['thumb']; ?>" />
					</a>
					<?php if(!empty($u->photo->grande[$v])) { ?>
					
					<img alt="" style="display:none;" src="<?php echo $u->photoUrl.$u->photo->grande[$v]; ?>" />
					<?php } ?>
					<?php if(!empty($u->photo->full[$v])) { ?>
					
					<img alt="" style="display:none;" src="<?php echo $u->photoUrl.$u->photo->full[$v]; ?>" />
					<?php } ?>
				<?php } else { ?>
					
					<img alt="" class="portraitMini" src="<?php echo plugins_url('rencontre/images/no-photo60.jpg'); ?>" />
				<?php } ?>
			<?php } ?>
					
				</div><!-- .rencBlocimg -->
			</div><!-- .rencBox -->
		</div><!-- .petiteBox -->
		
		<div class="grandeBox right">
			<div class="rencBox">
				<div class="flag">
				<?php if($u->c_pays!="" && !isset($rencCustom['country']) && !isset($rencCustom['place'])) { ?>
				
					<img src="<?php echo plugins_url('rencontre/images/drapeaux/').$rencDrap[$u->c_pays]; ?>" alt="<?php echo $rencDrapNom[$u->c_pays]; ?>" title="<?php echo $rencDrapNom[$u->c_pays]; ?>" />
					<br />
				<?php } ?>
				<?php if($u->online) { ?>
					
					<span class="rencInline"><?php _e('online','rencontre'); ?></span>
				<?php } else { ?>
					
					<span class="rencOutline"><?php _e('offline','rencontre'); ?></span>
				<?php } ?>
				</div>
				<div class="grid_10">
					<h3><?php echo $u->display_name.$blocked; ?></h3>
				<?php if(!isset($rencCustom['place'])) { ?>
					
					<div class="ville">
						<?php echo $u->c_ville; ?>
						<?php if($u->c_region && !isset($rencCustom['region'])) { ?>
						
						<em>(<?php echo $u->c_region; ?>)</em>
						<?php } ?>
						&nbsp;<?php RencontreWidget::f_distance($u->e_lat,$u->e_lon); ?>
					</div>
				<?php } ?>
					
					<div class="renc1 firstMaj">
					<?php if(isset($rencOpt['iam'][$u->i_sex])) { ?>
						<?php echo $rencOpt['iam'][$u->i_sex]; ?>
					<?php } ?>
					<?php if(!isset($rencCustom['born']) && strpos($u->d_naissance,'0000')===false) { ?>
					
					&nbsp;&nbsp;-&nbsp;&nbsp;<?php echo Rencontre::f_age($u->d_naissance); ?>&nbsp;<?php _e('years','rencontre'); ?>
					<?php } ?>
					<?php if(!isset($rencCustom['size'])) { ?>
					
					&nbsp;&nbsp;-&nbsp;&nbsp;<?php echo (empty($rencCustom['sizeu'])?$u->i_taille.' '.__('cm','rencontre'):floor($u->i_taille/24-1.708).' '.__('ft','rencontre').' '.round(((($u->i_taille/24-1.708)-floor($u->i_taille/24-1.708))*12),1).' '.__('in','rencontre')); ?>
					<?php } ?>
					<?php if(!isset($rencCustom['weight'])) { ?>
					
					&nbsp;&nbsp;-&nbsp;&nbsp;<?php echo (empty($rencCustom['weightu'])?$u->i_poids.' '.__('kg','rencontre'):($u->i_poids*2+10).' '.__('lbs','rencontre')) ?>
					<?php } ?>
					
					</div>
					<div class="titre"><?php echo stripslashes($u->t_titre); ?></div>
				</div>
				<p><?php echo stripslashes($u->t_annonce); ?></p>
				<?php echo $portraitAdd1; ?>
				
				<div class="looking firstMaj">
					<?php _e('I\'m looking for','rencontre'); ?>&nbsp;<?php echo $u->looking . $u->forwhat; ?>

				</div>
				<?php if(!empty($u->session) && $u0->ID!=$u->ID) { ?>
				
				<div class="rencDate" style="width:auto;">
					<?php _e('online','rencontre'); ?>&nbsp;:&nbsp;<?php echo $u->session; ?>
				
				</div>
				<?php } ?>
				
			</div><!-- .rencBox -->
			<?php if($u0->ID!=$u->ID) { ?>
			
			<div class="rencBox">
				<ul>
				<?php if(!$disable['send']) { ?>
					
					<a href="javascript:void(0)" onClick="<?php echo $onClick['send']; ?>">
						<li><?php _e('Send a message','rencontre');?></li>
					</a>
				<?php } else { ?>
					
					<a href="javascript:void(0)" title="<?php echo $title['send']; ?>">
						<li class="rencLiOff"><?php _e('Send a message','rencontre'); ?></li>
					</a>
				<?php } ?>
				<?php if(!isset($rencCustom['smile'])) { ?>
					<?php if(!$disable['smile']) { ?>
					
					<a href="javascript:void(0)" onClick="<?php echo $onClick['smile']; ?>">
						<li>
						<?php if(!empty($rencCustom['smiw']) && !empty($rencCustom['smiw1'])) echo stripslashes($rencCustom['smiw1']);
						else _e('Smile','rencontre'); ?>
						
						</li>
					</a>
					<?php } else { ?>

					<a href="javascript:void(0)" title="<?php echo $title['smile']; ?>">
						<li class="rencLiOff">
						<?php if(!empty($rencCustom['smiw']) && !empty($rencCustom['smiw1'])) echo stripslashes($rencCustom['smiw1']);
						else _e('Smile','rencontre'); ?>
						
						</li>
					</a>
					<?php } ?>
				<?php } ?>
				<?php if(!$disable['contact']) { ?>
					
					<a href="javascript:void(0)" onClick="<?php echo $onClick['contact']; ?>">
						<li><?php _e('Ask for a contact','rencontre'); ?></li>
					</a>
				<?php } else { ?>

					<a href="javascript:void(0)" title="<?php echo $title['contact']; ?>">
						<li class="rencLiOff"><?php _e('Ask for a contact','rencontre'); ?></li>
					</a>
				<?php } ?>
				<?php if($rencOpt['tchat']==1) { ?>
					<?php if(!$disable['chat']) { ?>
					
					<a href="javascript:void(0)" onClick="<?php echo $onClick['chat']; ?>">
						<li><?php _e('Chat','rencontre'); ?></li>
					</a>
					<?php } else { ?>

					<a href="javascript:void(0)" title="<?php echo $title['chat']; ?>">
						<li class="rencLiOff"><?php _e('Chat','rencontre'); ?></li>
					</a>
					<?php } ?>
				<?php } ?>
				<?php if(!$disable['block']) { ?>

					<a href="javascript:void(0)" onClick="<?php echo $onClick['block']; ?>">
						<li>
						<?php if(!$u->blocked->he) _e('Block','rencontre');
						else _e('Unblock','rencontre'); ?>
						
						</li>
					</a>
				<?php } else { ?>

					<a href="javascript:void(0)" title="<?php echo $title['block']; ?>">
						<li class="rencLiOff"><?php _e('Block','rencontre'); ?></li>
					</a>
				<?php } ?>
				<?php if(!$disable['report']) { ?>
					
					<a href="javascript:void(0)" onClick="<?php echo $onClick['report']; ?>" title="<?php _e('Report a fake profile or inappropriate content','rencontre'); ?>">
						<li><?php _e('Report','rencontre'); ?></li>
					</a>
				<?php } else { ?>

					<a href="javascript:void(0)" title="<?php echo $title['report']; ?>">
						<li class="rencLiOff"><?php _e('Report','rencontre'); ?></li>
					</a>
				<?php } ?>
				<?php if($u->blocked->me) { ?>
					
					<span style="position:absolute;left:80px;top:12px;font-size:120%;color:red;"><?php _e('You are blocked !','rencontre'); ?></span>
				<?php } ?>

				</ul>
			</div><!-- .rencBox -->
			<?php } ?>
			
			<div class="rencBox">
				<div class="br"></div>
			<?php $i = 0; foreach($u->profil as $key=>$value) { ?>
				
				<span class="portraitOnglet<?php if($i==0) echo ' rencTab'; ?>" id="portraitOnglet<?php echo $i; ?>" onclick="javascript:f_onglet(<?php echo $i; ?>);"><?php echo $key; ?></span>
				<?php ++$i; ?>
			<?php } ?>
			<?php $i = 0; foreach($u->profil as $key=>$value) { ?>

				<table <?php if($i==0) echo 'style="display:table;"'; ?> id="portraitTable<?php echo $i; ?>" border="0">
				<?php foreach($value as $k=>$v) { ?>
					
					<tr>
						<td><?php echo $k; ?></td>
						<td><?php echo $v; ?></td>
					</tr>
				<?php } ?>
					
				</table>
				<?php ++$i; ?>
			<?php } ?>

			</div><!-- .rencBox -->
		</div><!-- .grandeBox -->
	</div><!-- .rencPortrait -->
	<?php echo $script; ?>
