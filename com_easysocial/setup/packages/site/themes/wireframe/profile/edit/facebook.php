<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="tab-content__item" data-profile-edit-fields-content data-id="oauth-<?php echo $client->getType(); ?>">
	<?php echo $this->html('html.snackbar', 'COM_EASYSOCIAL_OAUTH_FACEBOOK_INTEGRATIONS'); ?>
	
	<p><?php echo JText::_('COM_EASYSOCIAL_OAUTH_FACEBOOK_INTEGRATIONS_ASSOCIATED');?></p>

	<div class="t-lg-mt--xl">
		<?php echo $client->getClient()->getRevokeButton(ESR::profile(array('layout' => 'edit' , 'external' => true)));?>
		
		<div class="t-lg-mt--xl"><?php echo JText::_('COM_EASYSOCIAL_OAUTH_FACEBOOK_DELETE_ASSOCIATION_NOTE'); ?></div>
	</div>
</div>