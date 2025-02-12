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
<div es-suggest-wrapper>
	<div es-suggest-body>
		<?php if ($suggestions) { ?>
		<div class="o-flag-list">
			<?php foreach ($suggestions as $suggestion) { ?>
			<div class="o-flag">
				<div class="o-flag__image o-flag--top">
					<?php echo $this->html('avatar.user', $suggestion->user, 'md'); ?>
				</div>

				<div class="o-flag__body">
					<a href="<?php echo $suggestion->user->getPermalink();?>"><?php echo $suggestion->user->getName();?></a>
					
					<?php if ($suggestion->mutual) { ?>
					<div class="t-text--muted"><?php echo $suggestion->mutual; ?></div>
					<?php } ?>

					<div class="t-lg-mt--md">
						<?php echo $this->html('user.friends', $suggestion->user); ?>
					</div>
				</div>
			</div>
			<?php } ?>
		</div>
		<?php } else { ?>
			<?php echo $this->html('widget.emptyBlock', 'APP_FRIENDS_SUGGEST_FRIENDS_NO_FRIENDS_SUGGESTION'); ?>
		<?php } ?>

		<?php if ($suggestions && $showMore) { ?>
			<?php echo $this->html('widget.viewAll', 'COM_ES_VIEW_ALL', ESR::friends(array('filter' => 'suggest'))); ?>
		<?php } ?>
	</div>

	<div class="main-content is-loading" hidden es-suggest-loading>
		<div class="o-loader o-loader--sm o-loader--inline with-text"><?php echo JText::_('APP_FRIENDS_SUGGEST_FRIENDS_REFRESH_SUGGESTIONS_LIST'); ?></div>
	</div>
</div>
