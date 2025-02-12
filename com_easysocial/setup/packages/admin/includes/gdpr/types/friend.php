<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialGdprFriend extends SocialGdprAbstract
{
	public $type = 'friend';

	/**
	 * Main function to process user comment data for GDPR download.
	 *
	 * @since 2.1
	 * @access public
	 */
	public function execute(SocialGdprSection &$section)
	{
		$tab = $section->createTab($this);
		$ids = $tab->getProcessedIds();

		// data from db.
		$model = ES::model('friends');
		$data = $model->getFriendsGDPR($this->user->id, $ids, $this->getLimit());

		if (!$data) {
			$tab->finalize();
			return;
		}

		foreach ($data as $row) {

			$target = ES::user($row->id);

			$template = $this->getTemplate($row->id, $this->type);

			$template->title = $row->friend;
			$template->created = $row->created;
			$template->intro = $this->getIntro($row);

			$tab->addItem($template);
		}
	}

	public function getIntro($item)
	{
		$date = ES::date($item->created);

		ob_start();
		?>
		<div class="gdpr-item__meta">
			<?php echo $date->format($this->getDateFormat());?>
		</div>
		<div class="gdpr-item__label">
			<span class="gdpr-label">
			<?php if ($item->state == SOCIAL_FRIENDS_STATE_PENDING) { ?>
				<?php echo JText::_('COM_ES_AWAITING_APPROVAL'); ?>
			<?php } ?>

			<?php if ($item->state == SOCIAL_FRIENDS_STATE_REJECTED) { ?>
				<?php echo JText::_('COM_ES_REJECTED');?>
			<?php } ?>

			<?php if ($item->state == SOCIAL_FRIENDS_STATE_FRIENDS) { ?>
				<?php echo JText::_('COM_ES_FRIENDS'); ?>
			<?php } ?>
			</span>
		</div>
		<?php
		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}
}
