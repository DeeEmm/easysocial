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
<li>
    <a href="<?php echo ESR::pages(array('layout' => 'edit', 'id' => $page->getAlias()));?>">
        <?php echo ($page->isDraft()) ? JText::_('COM_EASYSOCIAL_PAGES_REVIEW_PAGE') : JText::_('COM_EASYSOCIAL_PAGES_EDIT_PAGE');?>
    </a>
</li>

<?php if ($this->my->isSiteAdmin() && $page->isFeatured() && !$page->isDraft()) { ?>
<li>
    <a href="javascript:void(0);" data-es-pages-unfeature data-id="<?php echo $page->id;?>" data-return="<?php echo $returnUrl;?>"><?php echo JText::_('COM_EASYSOCIAL_PAGES_REMOVE_FEATURED');?></a>
</li>
<?php } ?>

<?php if ($this->my->isSiteAdmin() && !$page->isFeatured() && !$page->isDraft()) { ?>
<li>
    <a href="javascript:void(0);" data-es-pages-feature data-id="<?php echo $page->id;?>" data-return="<?php echo $returnUrl;?>"><?php echo JText::_('COM_EASYSOCIAL_PAGES_SET_FEATURED');?></a>
</li>
<?php } ?>

<?php if (($this->my->isSiteAdmin() || $page->isOwner() || $page->isAdmin()) && !$page->isDraft()) { ?>
    <li class="divider"></li>

    <?php echo $this->render('widgets', 'page', 'pages', 'pageAdminStart', array($page)); ?>
    <?php echo $this->render('widgets', 'page', 'pages', 'pageAdminEnd', array($page)); ?>
<?php } ?>


 <?php if (!$page->isDraft()) { ?>
 <li class="divider"></li>
    <li>
        <?php if ($page->isPublished() && $page->canUnpublish()) { ?>
            <a href="javascript:void(0);" data-es-pages-unpublish data-id="<?php echo $page->id;?>"><?php echo JText::_('COM_EASYSOCIAL_PAGES_UNPUBLISH_PAGE');?></a>
        <?php } else if ($page->canPublish()) { ?>
            <a href="javascript:void(0);" data-es-pages-publish data-id="<?php echo $page->id;?>"><?php echo JText::_('COM_EASYSOCIAL_PAGES_PUBLISH_PAGE');?></a>
        <?php } ?>
    </li>
<?php } ?>

<?php if ($page->canDelete()){ ?>
    <li>
        <a href="javascript:void(0);" data-es-pages-delete data-id="<?php echo $page->id;?>"><?php echo JText::_('COM_EASYSOCIAL_PAGES_DELETE_PAGE');?></a>
    </li>
<?php } ?>
