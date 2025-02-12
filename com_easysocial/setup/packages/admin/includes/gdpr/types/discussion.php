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

class SocialGdprDiscussion extends SocialGdprAbstract
{
	public $type = 'discussion';
	private $tab = null;

	/**
	 * Process video downloads in accordance to GDPR rules
	 *
	 * @since	2.1.11
	 * @access	public
	 */
	public function execute(SocialGdprSection &$section)
	{
		$this->tab = $section->createTab($this);

		$discussions = $this->getItems();

		// Nothing else to process, finalize it now.
		if (!$discussions) {
			return $this->tab->finalize();
		}

		foreach ($discussions as $discussion) {
			$item = $this->getTemplate($discussion->id, $this->type);

			$item->created = $discussion->created;

			// $item->title = ($discussion->isReply()) ? 'RE' .  : $discussion->title;

			$title = $discussion->title;

			if ($discussion->isReply()) {
				$parent = $discussion->getParent();
				$title = JText::_('COM_ES_REPLY_PREFIX') . $parent->title;
			}

			$item->title = $title;
			$item->intro = $this->getIntro($discussion);
			$item->view = true;
			$item->content = $this->getContent($discussion);

			$this->tab->addItem($item);
		}
	}

	/**
	 * Generates the intro text of the discussion
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function getIntro($discussion)
	{
		$date = ES::date($discussion->created);

		ob_start();
		?>
		<div class="gdpr-item__meta">
			<?php echo $date->format($this->getDateFormat());?>
		</div>
		<div class="gdpr-item__label">
			<span class="gdpr-label"><?php echo $discussion->isReply() ? JText::_('COM_ES_REPLY') : JText::_('COM_ES_QUESTION');?></span>
		</div>
		<?php

		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}

	/**
	 * Generates the content text of the discussion used in sub page
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function getContent($discussion)
	{
		$date = ES::date($discussion->created);
		ob_start();
		?>
		<div class="gdpr-item__desc">
			<?php echo $discussion->getContent(); ?>
		</div>
		<div class="gdpr-item__meta">
			<?php echo $date->format($this->getDateFormat());?>
		</div>
		<?php

		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}

	/**
	 * Retrieves discussions that needs to be processed
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function getItems()
	{
		$ids = $this->tab->getProcessedIds();

		$options = array();
		$options['exclusion'] = $ids;
		$options['limit'] = $this->getLimit();

		$model = ES::model('Discussions');
		$items = $model->getDiscussionsGdpr($this->user->id, $options);

		return $items;
	}
}
