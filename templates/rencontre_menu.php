<?php
/*
 * Plugin : Rencontre
 * Template : Menu
 * Last Change : Rencontre 2.0
 * Custom This File ? : wp-content/themes/name-of-my-theme/templates/rencontre_menu.php
*/
?>

	<div class="rencMenu pleineBox">
		<div class="rencBox">
			<div class="rencBonjour"><?php _e('Hello','rencontre'); ?>&nbsp;<?php echo $current_user->user_login; ?></div>
			<ul id="rencMenuList">
				<a id="rencMenuHome" href="<?php echo $link['home']; ?>">
					<li <?php echo $currentHome; ?>><?php _e('My homepage','rencontre');?></li>
				</a>
				
				<a id="rencMenuCard" href="javascript:void(0)" class="rencMenuCard">
					<li ><?php _e('My card','rencontre');?></li>
				</a>
			<?php if($rencOpt['fastreg']>1) { ?>
			
				<a id="rencMenuEdit" href="javascript:void(0)">
					<li class="rencLiOff"><?php _e('Edit My Profile','rencontre'); ?></li>
				</a>
			<?php } else { ?>
			
				<a id="rencMenuEdit" href="javascript:void(0)" class="rencMenuEdit">
					<li <?php if(!$fantome) echo 'class="boutonred"'; ?>><?php _e('Edit My Profile','rencontre'); ?></li>
				</a>
			<?php } ?>
			<?php if($rencOpt['fastreg']>1) { ?>
			
				<a id="rencMenuMsg" href="javascript:void(0)">
					<li class="rencLiOff"><?php _e('Messaging','rencontre'); ?></li>
				</a>
			<?php } else { ?>
			
				<a id="rencMenuMsg" href="javascript:void(0)" class="rencMenuMsg">
					<li><?php _e('Messaging','rencontre'); ?><?php echo RencontreWidget::f_count_inbox($current_user->user_login); ?></li>
				</a>
			<?php } ?>
			<?php if(!$blockSearch) { ?>
			
				<a id="rencMenuSearch" href="javascript:void(0)" class="rencMenuSearch">
					<li><?php _e('Search','rencontre');?></li>
				</a>
			<?php } else { ?>

				<a id="rencMenuSearch" href="javascript:void(0)" title="<?php echo $blockSearch; ?>">
					<li class="rencLiOff"><?php _e('Search','rencontre'); ?></li>
				</a>
			<?php } ?>
		
				<a id="rencMenuAccount" href="javascript:void(0)" class="rencMenuAccount">
				<?php if(strstr($_SESSION['rencontre'],'compte')) { ?>
				
					<li class="current"><?php _e('My Account','rencontre');?></li>
				<?php } else if($rencOpt['fastreg']>1) { ?>
				
					<li class="boutonred"><?php _e('My Account','rencontre');?></li>
				<?php } else { ?>
				
					<li><?php _e('My Account','rencontre');?></li>
				<?php } ?>

				</a>
			<?php if($rencOpt['facebook']) { ?>
				
				<span class="rencFBlike"><?php echo $fbLike; ?></span>
			<?php } ?>
			
			</ul>
		</div><!-- .rencBox -->
	</div><!-- .rencMenu -->
<?php 
/***** ADD ONE OR TWO CUSTOM ITEMS IN THE MENU FOR CUSTOM PAGES (TEMPLATE rencontre_custom_page1 or 2.php) ******
 * Copy this 3 lines in the HTML structure - For a second button : Use "rencMenuC2" in ID and CLASS

				<a id="rencMenuC1" href="javascript:void(0)" class="rencMenuC1">
					<li>The name for my button 1</li>
				</a>

*/
?>
