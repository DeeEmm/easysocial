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

require_once(__DIR__ . '/app.php');

class SocialStoreApp extends EasySocial
{
    public $table = null;

    public function __construct($id = null)
    {
        parent::__construct();
        
        $this->table = ES::table('Store');

        // Load the record if it's an integer
        if (!is_null($id) && is_int($id)) {
            $this->table->load($id);
        }

        // Assign the table if it's a table instance
        if ($id instanceof SocialTableStore) {
            $this->table = $id;
        }

        // If it is an array, just bind it back
        if (!is_null($id) && (is_array($id) || is_object($id))) {
            $this->table->bind($id);
        }
    }

    public function __get($key)
    {
        if (isset($this->table->$key)) {
            return $this->table->$key;
        }

        if (isset($this->$key)) {
            return $this->$key;
        }

        return $this->table->$key;
    }

    /**
     * Retrieve the app logo
     *
     * @since   2.0
     * @access  public
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Retrieves the app price
     *
     * @since   2.0
     * @access  public
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Retrieves the raw params
     *
     * @since   2.0
     * @access  public
     */
    public function getParams()
    {
        $params = json_decode($this->raw);

        return $params;
    }

    /**
     * Retrieves the app title
     *
     * @since   2.0
     * @access  public
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Retrieves the permalink to the app item internally
     *
     * @since   2.0
     * @access  public
     */
    public function getPermalink()
    {
        $url = JRoute::_('index.php?option=com_easysocial&view=apps&layout=item&id=' . $this->id);

        return $url;
    }

    /**
     * Retrieves the external link of an app
     *
     * @since   2.0
     * @access  public
     */
    public function getExternalPermalink()
    {
        return $this->permalink;
    }

    /**
     * Retrieves the screenshots for this app
     *
     * @since   2.0
     * @access  public
     */
    public function getScreenshots()
    {
        $params = $this->getParams();

        $screenshots = $params->screenshots;

        return $screenshots;
    }

    /**
     * Gets the total number of screenshots
     *
     * @since   2.0
     * @access  public
     */
    public function getTotalScreenshots()
    {
        $screenshots = $this->getScreenshots();

        return count($screenshots);
    }

    /**
     * Retrieves the app type
     *
     * @since   2.0
     * @access  public
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Retrieves the app type css class associatin
     *
     * @since   2.0
     * @access  public
     */
    public function getTypeClass()
    {
        if ($this->type == 'templates') {
            return 'tpl';
        }

        if ($this->type == 'fields') {
            return 'ctf';
        }

        if ($this->type == 'apps') {
            return 'app';
        }

        if ($this->type == 'plugins') {
            return 'plg';
        }

        if ($this->type == 'modules') {
            return 'mod';
        }
    }

    /**
     * Retrieves the app type
     *
     * @since   2.0
     * @access  public
     */
    public function getTypeLabel()
    {
        $type = $this->getType();

        if (!$type) {
            return false;
        }

        $text = 'COM_EASYSOCIAL_APPS_STORE_TYPE_' . strtoupper($type);
        $text = JText::_($text);

        return $text;
    }

    /**
     * Retrieves the app description
     *
     * @since   2.0
     * @access  public
     */
    public function getDescription($truncate = true, $length = 180)
    {
        if ($truncate) {
            $content = strip_tags($this->info);
            $content = ESJString::substr($this->info, 0, $length) . JText::_('COM_EASYSOCIAL_ELLIPSIS');

            return $content;
        } 

        $content = nl2br($this->info);

        return $content;
    }

    /**
     * Determines if this app has payment support
     *
     * @since   2.0
     * @access  public
     */
    public function hasPaymentSupport()
    {
        if ($this->payment) {
            return true;
        }

        return false;
    }

    /**
     * Determines if this app can be downloaded via the API
     *
     * @since   2.0
     * @access  public
     */
    public function isDownloadableFromApi()
    {
        if ($this->download_api) {
            return true;
        }

        return false;
    }

    /**
     * Determines if this app is an external download
     *
     * @since   2.0
     * @access  public
     */
    public function isExternal()
    {
        if ($this->isDownloadableFromApi()) {
            return false;
        }

        return true;
    }

    /**
     * Determines if this app can be downloaded
     *
     * @since   2.0
     * @access  public
     */
    public function isDownloadable()
    {
        if ($this->download) {
            return true;
        }

        return false;
    }

    /**
     * Determines if this item has been installed on the site
     *
     * @since   2.0
     * @access  public
     */
    public function isInstalled()
    {
        // If we don't have these data, there is no way for us to track if it has been installed
        if (!$this->element || !$this->group || !$this->type) {
            return false;
        }

        // Try to load the app and see if it exists
        $app = ES::table('App');
        $exists = $app->load(array('type' => $this->type, 'element' => $this->element, 'group' => $this->group));

        if ($exists) {
            return true;
        }

        return false;
    }

    /**
     * Determines if this item has been installed on the site
     *
     * @since   2.0
     * @access  public
     */
    public function isFree()
    {
        if ($this->price == 0) {
            return true;
        }

        return false;
    }

    /**
     * Retrieves the score of an app
     *
     * @since   2.0
     * @access  public
     */
    public function getScore()
    {
        return $this->ratings;
    }
}