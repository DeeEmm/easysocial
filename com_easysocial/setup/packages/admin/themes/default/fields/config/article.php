<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
$article = false;

if ($value) {
	$db = ES::db();
	$query = 'SELECT `id`, `title` FROM ' . $db->qn('#__content') . ' WHERE `id`=' . $db->Quote($value);

	$db->setQuery($query);
	$article = $db->loadObjectList();

	if ($article) {
		$article = $article[0];
	}
}
?>
<div class="textboxlist controls disabled" data-article-suggest>
	<?php if ($article) { ?>
	<div class="textboxlist-item" data-id="<?php echo $article->id; ?>" data-title="<?php echo $article->title; ?>" data-textboxlist-item>
		<span class="textboxlist-itemContent" data-textboxlist-itemContent>
			<i class="fa fa-file-text"></i> <?php echo $article->title; ?>
			<input type="hidden" name="config_<?php echo $name;?>" value="<?php echo $article->id; ?>" />
		</span>
		<div class="textboxlist-itemRemoveButton" data-textboxlist-itemRemoveButton>
			<i class="fa fa-times"></i>
		</div>
	</div>
	<?php } ?>

	<input type="text" autocomplete="off" disabled class="participants textboxlist-textField o-form-control" data-textboxlist-textField
		placeholder="Type in keyword to search for article ..." />

	<input type="hidden" data-name="<?php echo $name;?>"  name="config_<?php echo $name;?>" id="config_<?php echo $name;?>" value="<?php echo $value;?>" data-fields-config-param data-fields-config-param-field-<?php echo $name;?> />
</div>