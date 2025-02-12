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

jimport('joomla.filesystem.file');

class SocialGdprFile extends SocialGdprAbstract
{
	public $type = 'file';
	private $tab = null;

	/**
	 * Process the photos
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function execute(SocialGdprSection &$section)
	{
		$this->tab = $section->createTab($this);

		$files = $this->getFiles();

		if (!$files) {
			$this->tab->finalize();
			return;
		}

		foreach ($files as $file) {
			$item = $this->getTemplate($file->id, $this->type);

			if (! $file->hash) {
				// most likely this record is invalid. skip it.
				$this->tab->markItemProcessed($item);
				continue;
			}

			// check if the file exits or not. if not, we skip it.
			$storage = $file->getStoragePath();
			$filePath = $storage . '/' . $file->hash;

			// If the file no longer exists, throw a 404
			if (!JFile::exists($filePath)) {
				$this->tab->markItemProcessed($item);
				continue;
			}

			$item->created = $file->created;
			$item->title =  $file->name;
			$item->intro = $this->getIntro($file);
			$item->content = $this->getContent($file);
			$item->view = true;

			$relativePath = $file->getStoragePath(true);
			$item->source = $file->storage . ':' . $relativePath . '/' . $file->hash;
			$item->sourceFilename = $file->name;

			$this->tab->addItem($item);
		}
	}

	/**
	 * Retrieve the photos related to gdpr
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function getFiles()
	{
		$exIds = $this->tab->getProcessedIds();

		$model = ES::model("Files");

		// Get the photos
		$files = $model->getGdprFiles($this->user->id, $exIds, $this->getLimit());

		return $files;
	}

	public function getIntro(SocialTableFile $file)
	{
		$date = ES::date($file->created);

		ob_start();
		?>
		<div class="gdpr-item__desc">
			<?php echo $file->name; ?>
		</div>
		<div class="gdpr-item__meta">
			<?php echo $date->format($this->getDateFormat());?>
		</div>
		<div class="gdpr-item__label">
			<span class="gdpr-label"><?php echo strtoupper($file->type);?></span>
		</div>
		<?php
		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}

	public function getContent(SocialTableFile $file)
	{
		$date = ES::date($file->created);

		ob_start();
		?>
		<div class="gdpr-item__desc">
			<?php echo $file->name; ?><br />
			<?php if (stristr($file->mime, 'image/') !== false) { ?>
			<figure>
				<img src="{%MEDIA%}" height="auto" width="100%"></img>
			</figure>
			<?php } else { ?>
			<a href="{%MEDIA%}" target="_BLANK">Download File</a>
			<?php } ?>
		</div>
		<div class="gdpr-item__meta">
			<?php echo $date->format($this->getDateFormat());?>
		</div>
		<?php
		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}
}
