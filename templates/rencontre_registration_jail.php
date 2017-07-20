<?php
/*
 * Plugin : Rencontre
 * Template : Registration Jail
 * Last Change : Rencontre 2.0
 * Custom This File ? : wp-content/themes/name-of-my-theme/templates/rencontre_registration_jail.php
 * $u0 : 
*/
?>

	<div class="pleineBox">
		<div class="rencBox">
			<div class="rencNouveau">
				<h3><?php if(isset($rencCustom['jail']) && !empty($rencCustom['jailText'])) echo stripslashes($rencCustom['jailText']);
				else _e('Your email address is currently in quarantine. Sorry','rencontre'); ?>
				</h3>
			</div>
		</div>
	</div>
