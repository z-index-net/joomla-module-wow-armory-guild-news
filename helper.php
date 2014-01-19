<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2013 - 2014 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

final class ModWowArmoryGuildNewsHelper
{
    private $params = null;

    private function __construct(JRegistry &$params)
    {
        if (version_compare(JVERSION, 3, '>=')) {
            $params->set('guild', rawurlencode(JString::strtolower($params->get('guild'))));
            $params->set('realm', rawurlencode(JString::strtolower($params->get('realm'))));
        } else {
            $params->set('realm', str_replace(array('%20', ' '), '-', $params->get('realm')));
            $params->set('guild', str_replace(array('%20', ' '), '%2520', $params->get('guild')));
        }

        $params->set('region', JString::strtolower($params->get('region')));
        $params->set('lang', JString::strtolower($params->get('lang', 'en')));
        $params->set('link', $params->get('link', 'battle.net'));

        $this->params = $params;
    }

    public static function getAjax()
    {
        $module = JModuleHelper::getModule('mod_' . JFactory::getApplication()->input->get('module'));

        if (empty($module)) {
            return false;
        }

        JFactory::getLanguage()->load($module->module);

        $params = new JRegistry($module->params);
        $params->set('ajax', 0);

        ob_start();

        require(dirname(__FILE__) . '/' . $module->module . '.php');

        return ob_get_clean();
    }

    public static function getData(JRegistry &$params)
    {
        if ($params->get('ajax')) {
            return;
        }

        $instance = new self($params);

        return $instance->createData();
    }

    private function createData()
    {
        $url = 'http://' . $this->params->get('region') . '.battle.net/wow/' . $this->params->get('lang') . '/guild/' . $this->params->get('realm') . '/' . $this->params->get('guild') . '/news';

        $cache = JFactory::getCache('wow', 'output');
        $cache->setCaching(1);
        $cache->setLifeTime($this->params->get('cache_time', 60));

        $key = md5($url);

        if (!$result = $cache->get($key)) {
            try {
                $http = JHttpFactory::getHttp();
                $http->setOption('userAgent', 'Joomla! ' . JVERSION . '; Wow Armory Guild News Module; php/' . phpversion());

                $result = $http->get($url, null, $this->params->get('timeout', 10));
            } catch (Exception $e) {
                return $e->getMessage();
            }

            $cache->store($result, $key);
        }

        if ($result->code != 200) {
            return __CLASS__ . ' HTTP-Status ' . JHtml::_('link', 'http://wikipedia.org/wiki/List_of_HTTP_status_codes#' . $result->code, $result->code, array('target' => '_blank'));
        }

        if (strpos($result->body, '<div id="news-list">') === false) {
            return JText::_('MOD_WOW_ARMORY_GUILD_NEWS_NO_NEWS');
        }

        // get only news data
        preg_match('#<ul class="activity-feed activity-feed-wide">(.+?)</ul>#si', $result->body, $result->body);

        $result->body = $result->body[1];

        // remove unnecessary whitespaces
        $search[] = '#\s{2,10}#';
        $replace[] = '';

        // would disable wowhead tooltips?!
        $search[] = '#rel="np"#';
        $replace[] = '';

        // remove unnecessary li object
        $search[] = '#<li data-id="[0-9]+" class="item-looted.*?">#';
        $replace[] = '';

        // add link target
        $search[] = '#href="#';
        $replace[] = 'target="_blank" href="';

        // replace item icon with img tag
        $search[] = '#<span.*?style=\'background-image: url\("(.*?)"\);\'.*?>.*?</span>#i';
        $replace[] = '<img src="$1" width="18" height="18" alt="" />';

        $result->body = preg_replace($search, $replace, $result->body);

        $links[] = '#/wow/' . $this->params->get('lang') . '/character/[^/]+/[^/]+/(achievement)\#([[:digit:]:a]+)#i';
        $links[] = '#/wow/' . $this->params->get('lang') . '/(item)/(\d+)#i';
        $links[] = '#/wow/' . $this->params->get('lang') . '/guild/[^/]+/[^/]+/(achievement)\#([[:digit:]:a]+)#i';
        $links[] = '#/wow/' . $this->params->get('lang') . '/(character)/[^/]+/(\S[[:graph:]]+)/"#i';

        $result->body = preg_replace_callback($links, array(&$this, 'link'), $result->body);

        // at last split data at </li>
        $result->body = explode('</li>', $result->body);

        if (empty($result->body)) {
            return JText::_('MOD_WOW_ARMORY_GUILD_NEWS_NO_NEWS');
        }

        $result->body = array_filter($result->body); // remove empty items

        if ($filter = $this->params->get('filter')) {
            $filter = array_filter(array_map('trim', explode(';', $filter)));
            if (!empty($filter)) {
                foreach ($result->body as $key => $row) {
                    foreach ($filter as $search) {
                        if (strpos($row, $search) !== false) {
                            unset($result->body[1][$key]);
                        }
                    }
                }
            }
        }

        return array_slice($result->body, 0, $this->params->get('rows'));
    }

    private function link($matches)
    {
        $sites['item']['battle.net'] = 'http://' . $this->params->get('region') . '.battle.net' . $matches[0];
        $sites['item']['wowhead.com'] = 'http://' . $this->params->get('lang') . '.wowhead.com/item=' . $matches[2];
        $sites['item']['wowdb.com'] = 'http://www.wowdb.com/items/' . $matches[2];
        $sites['item']['buffed.de'] = 'http://wowdata.buffed.de/?i=' . $matches[2];

        if ($matches[1] == 'achievement') {
            $achievement = substr($matches[2], strpos($matches[2], ':a') + 2);
            $sites['achievement']['battle.net'] = 'http://' . $this->params->get('region') . '.battle.net' . $matches[0];
            $sites['achievement']['wowhead.com'] = 'http://' . $this->params->get('lang') . '.wowhead.com/achievement=' . $achievement;
            $sites['achievement']['wowdb.com'] = 'http://www.wowdb.com/achievements/' . $achievement;
            $sites['achievement']['buffed.de'] = 'http://wowdata.buffed.de/?a=' . $achievement;
        }

        $sites['character']['battle.net'] = 'http://' . $this->params->get('region') . '.battle.net' . $matches[0];
        $sites['character']['wowhead.com'] = 'http://' . $this->params->get('lang') . '.wowhead.com/profile=' . $this->params->get('region') . '.' . $this->params->get('realm') . '.' . $matches[2] . '"';

        return isset($sites[$matches[1]][$this->params->get('link')]) ? $sites[$matches[1]][$this->params->get('link')] : $sites[$matches[1]]['battle.net'];
    }
}