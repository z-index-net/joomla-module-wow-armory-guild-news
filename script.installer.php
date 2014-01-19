<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2013 - 2014 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

class mod_wow_armory_guild_newsInstallerScript
{
    private $required = '2.5.16';

    public function preflight()
    {
        if (!version_compare(JVERSION, $this->required, 'ge')) {
            $link = JHtml::link('index.php?option=com_joomlaupdate', $this->required);
            JFactory::getApplication()->enqueueMessage(sprintf('You need Joomla! %s or later to install this extension', $link), 'error');
            return false;
        }

        return true;
    }
}