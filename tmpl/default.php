<?php
/**
 * WoW Armory Guild News Module
 *
 * @author     Branko Wilhelm <bw@z-index.net>
 * @link       http://www.z-index.net
 * @copyright  (c) 2011 Branko Wilhelm
 * @package    mod_wow_armory_guild_news
 * @license    GNU Public License <http://www.gnu.org/licenses/gpl.html>
 * @version    $Id$
 */
// no direct accesss
defined('_JEXEC') or die;

JFactory::getDocument()->addStyleSheet(JURI::base(true) . '/modules/mod_wow_armory_guild_news/tmpl/stylesheet.css');
?>
<div class="mod_wow_armory_guild_news">
    <?php foreach ($news as $row) { ?>
        <div><?php echo $row; ?></div>
    <?php } ?>
</div>
