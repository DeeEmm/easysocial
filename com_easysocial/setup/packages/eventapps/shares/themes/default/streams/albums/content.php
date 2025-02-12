<?php
/**
* @package        EasySocial
* @copyright    Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license        GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="stream-repost">
    <?php if ($message) { ?>
    <div class="stream-repost-text"><?php echo $message; ?></div>
    <?php } ?>

    <div class="stream-meta">
        <div class="meta-title">
            <a href="<?php echo $album->getPermalink(); ?>"><?php echo $album->get('title'); ?></a>
        </div>
        <div class="meta-content">
            <p>
                <img alt="<?php echo $this->html('string.escape', $album->getCoverObject()->get('title')); ?>" src="<?php echo $album->getCover('square'); ?>" align="left" class="mr-10 mb-10" />
                <?php echo $album->get('caption'); ?>
            </p>
            <div class="mt-10">
                <a href="<?php echo $album->getPermalink(); ?>" class="btn btn-es-primary btn-medium"><?php echo JText::_('APP_SHARES_VIEW_ALBUM'); ?></a>
            </div>
        </div>
    </div>
</div>
