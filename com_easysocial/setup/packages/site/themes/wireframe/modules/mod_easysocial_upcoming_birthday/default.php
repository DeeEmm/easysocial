<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="t-lg-mt--md">
	<div class="o-flag-list">
		<?php foreach ($birthdays as $item) { ?>
		<div class="o-flag">
			<div class="o-flag__image o-flag--top">
				<?php echo $this->html('avatar.user', $item->user, 'md'); ?>
			</div>

			<div class="o-flag__body">
				<?php echo $this->html('html.user', $item->user); ?>

				<div class="t-text--muted">
					<?php echo $item->display; ?>

					<?php if ($this->my->getPrivacy()->validate('profiles.post.message', $item->user->id) && $this->config->get('conversations.enabled')) { ?>
						<?php echo $this->html('user.conversation', $item->user, 'xs'); ?>
					<?php } ?>
				</div>
			</div>
		</div>
		<?php } ?>
	</div>
</div>
