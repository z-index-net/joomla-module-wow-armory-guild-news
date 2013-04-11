<?php

/**
 * WoW Armory Guild News Module
 *
 * @author     Branko Wilhelm <bw@z-index.net>
 * @link       http://www.z-index.net
 * @copyright  (c) 2011 - 2013 Branko Wilhelm
 * @package    mod_wow_armory_guild_news
 * @license    GNU General Public License v3
 * @version    $Id$
 */

defined('_JEXEC') or die;

JFactory::getDocument()->addStyleSheet(JURI::base(true) . '/modules/' . $module->module . '/tmpl/stylesheet.css');
?>
<div class="mod_wow_armory_guild_news">
    <?php foreach ($news as $row) { ?>
        <?php echo $row; ?>
    <?php } ?>
</div>