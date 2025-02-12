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
<?php if ($videos) { ?>
<div class="o-flag-list">
	<?php foreach ($videos as $video) { ?>
	<div class="o-flag">
		<div class="o-flag__image o-flag--top">
			<img src="<?php echo $video->getThumbnail();?>" alt="<?php echo $this->html('string.escape', $video->getTitle());?>" width="68" />
		</div>

		<div class="o-flag__body">
			<a href="<?php echo $video->getPermalink();?>"><?php echo $video->getTitle();?></a>

			<?php if ($showDuration) { ?>
			<div class="t-text--muted"><?php echo $video->getDuration();?></div>
			<?php } ?>
		</div>
	</div>
	<?php } ?>
</div>
<?php } else { ?>
<div class="t-text--muted">
	<?php echo $emptyMessage; ?>
</div>
<?php } ?>
