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

class SocialGdprNote extends SocialGdprAbstract
{
	public $type = 'note';

	/**
	 * Process user note downloads in accordance to GDPR rules
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function execute(SocialGdprSection &$section)
	{
		$this->tab = $section->createTab($this);

		$notes = $this->getNotes();

		// Nothing else to process, finalize it now.
		if (!$notes) {
			$this->tab->finalize();
			return;
		}

		foreach ($notes as $note) {
			$item = $this->getTemplate($note->id, $this->type);

			$item->created = $note->created;
			$item->title = $note->title;
			$item->intro = $this->getIntro($note);
			$item->view = true;
			$item->content = $this->getContent($note);

			$this->tab->addItem($item);
		}
	}

	/**
	 * Display the intro content on the first page 
	 *
	 * @since  2.2
	 * @access public
	 */
	public function getIntro($note)
	{
		$date = ES::date($note->created);

		ob_start();
		?>
		<div class="gdpr-item__meta">
			<?php echo $date->format($this->getDateFormat()); ?>
		</div>
		<?php
		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}

	/**
	 * Display the content on the sub page 
	 *
	 * @since  2.2
	 * @access public
	 */
	public function getContent($note)
	{
		$date = ES::date($note->created);

		ob_start();
		?>
		<div class="gdpr-item__desc">
			<?php echo $note->content;?>
		</div>
		<div class="gdpr-item__meta">
			<?php echo $date->format($this->getDateFormat()); ?>
		</div>
		<?php
		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}

	/**
	 * Retrieves user notes that needs to be processed
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function getNotes()
	{
		$file = JPATH_ROOT . '/media/com_easysocial/apps/user/notes/models/notes.php';

		if (!JFile::exists($file)) {
			return false;
		}

		require_once($file);
		
		$ids = $this->tab->getProcessedIds();

		$options = array();
		$options['userid'] = $this->user->id;
		$options['exclusion'] = $ids;
		$options['limit'] = $this->getLimit();

		$notesModel = new NotesModel;
		$notes = $notesModel->getNotesGDPR($options);

		return $notes;
	}
}
