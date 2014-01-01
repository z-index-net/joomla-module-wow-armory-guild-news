<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2013 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

if (version_compare(JVERSION, 3, '>=')) {
    JHtml::_('jquery.framework');
}

JFactory::getDocument()->addStyleSheet(JUri::base(true) . '/modules/' . $module->module . '/tmpl/default.css');
?>
<?php if ($params->get('ajax')) : ?>
    <div class="mod_wow_armory_guild_news ajax"></div>
<?php else: ?>
    <div class="mod_wow_armory_guild_news">
        <?php foreach ($news as $row) : ?>
            <?php echo $row; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>