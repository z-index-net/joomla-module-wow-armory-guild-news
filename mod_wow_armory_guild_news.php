<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2013 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

JLoader::register('ModWowArmoryGuildNewsHelper', dirname(__FILE__) . '/helper.php');

$news = ModWowArmoryGuildNewsHelper::getData($params);

if (!$params->get('ajax') && !is_array($news)) {
    echo $news;
    return;
}

require JModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default'));