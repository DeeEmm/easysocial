<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<ul class="g-list-unstyled">
<?php foreach ($value as $v) {?>
	<li>
		<?php
			$advsearchLink = '';

			$advGroups = array(SOCIAL_FIELDS_GROUP_GROUP, SOCIAL_FIELDS_GROUP_USER, SOCIAL_FIELDS_GROUP_EVENT, SOCIAL_FIELDS_GROUP_PAGE);

			if (isset($field) && in_array($field->type, $advGroups) && $field->searchable && $v) {
				$params = array( 'layout' => 'advanced' );

				if ($field->type != SOCIAL_FIELDS_GROUP_USER) {
					$params['type'] = $field->type;
					$params['uid'] = $field->uid;
				}

				$params['criterias[]'] = $field->unique_key . '|' . $field->element;
				$params['operators[]'] = 'contain';
				$params['conditions[]'] = $v;

				$advsearchLink = FRoute::search($params);
			}
		?>
		<?php echo (isset($advsearchLink) && $advsearchLink) ? '<a href="' . $advsearchLink . '">' : ''; ?>
		<?php echo $v; ?>
		<?php echo (isset($advsearchLink) && $advsearchLink) ? '</a>' : ''; ?>
	</li>
<?php } ?>
</ul>