<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialGdprConversation extends SocialGdprAbstract
{
	public $type = 'conversation';
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

		$conversations = $this->getItems();

		// Nothing else to process, finalize it now.
		if (!$conversations) {
			return $this->tab->finalize();
		}

		foreach ($conversations as $conversation) {

			$item = $this->getTemplate($conversation->id, $this->type);

			$item->created = $conversation->created;

			$title = $conversation->getTitle();

			$item->title = $title;
			$item->intro = $this->getIntro($conversation);
			$item->view = true;
			$item->content = $this->getContent($conversation);

			$this->tab->addItem($item);
		}
	}

	/**
	 * Generates the intro text of the conversation
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function getIntro($conversation)
	{
		$date = ES::date($conversation->created);

		ob_start();
		?>
		<div class="gdpr-item__meta">
			<?php echo $date->format($this->getDateFormat());?>
		</div>
		<?php
		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}

	/**
	 * Generates the content text of the conversation used in sub page
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function getContent($conversation)
	{
		// Get messages for this particular conversation
		$messages = $conversation->getMessages();

		ob_start();
		?>

		<?php foreach ($messages as $message) { ?>
			<?php $date = ES::date($message->created); ?>

			<div class="gdpr-item__desc">
				<?php echo $message->message; ?>
			</div>
			<div class="gdpr-item__meta">
				<?php echo $date->format($this->getDateFormat());?>
			</div>
		<?php } ?>
		<?php

		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}

	/**
	 * Retrieves conversations that needs to be processed
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

		$model = ES::model('Conversations');
		$items = $model->getConversations($this->user->id, $options);

		return $items;
	}
}
