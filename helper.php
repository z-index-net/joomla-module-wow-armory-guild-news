<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2011 - 2015 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

class ModWowArmoryGuildNewsHelper extends WoWModuleAbstract
{
    protected function getInternalData()
    {
        try {
            $result = WoW::getInstance()->getAdapter('BattleNET')->getData('guild_news');
        } catch (Exception $e) {
            return $e->getMessage();
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
        $search[] = '#<li data-id="[0-9]+" class=".*?">#';
        $replace[] = '';

        // add link target
        $search[] = '#href="#';
        $replace[] = 'target="_blank" href="';

        // replace item icon with img tag
        $search[] = '#<span.*?style=\'background-image: url\("(.*?)"\);\'.*?>.*?</span>#i';
        $replace[] = '<img src="$1" width="18" height="18" alt="" />';

        $result->body = preg_replace($search, $replace, $result->body);

        $links[] = '#/wow/' . $this->params->global->get('locale') . '/character/[^/]+/[^/]+/(achievement)\#([[:digit:]:a]+)#i';
        $links[] = '#/wow/' . $this->params->global->get('locale') . '/(item)/(\d+)#i';
        $links[] = '#/wow/' . $this->params->global->get('locale') . '/guild/[^/]+/[^/]+/(achievement)\#([[:digit:]:a]+)#i';
        $links[] = '#/wow/' . $this->params->global->get('locale') . '/(character)/[^/]+/(\S[[:graph:]]+)/"#i';

        $result->body = preg_replace_callback($links, array(&$this, 'link'), $result->body);

        // at last split data at </li>
        $result->body = explode('</li>', $result->body);

        if (empty($result->body)) {
            return JText::_('MOD_WOW_ARMORY_GUILD_NEWS_NO_NEWS');
        }

        $result->body = array_filter($result->body); // remove empty items

        if ($filter = $this->params->module->get('filter')) {
            $filter = array_filter(array_map('trim', explode(';', $filter)));
            if (!empty($filter)) {
                foreach ($result->body as $key => $row) {
                    foreach ($filter as $search) {
                        if (strpos($row, $search) !== false) {
                            unset($result->body[$key]);
                        }
                    }
                }
            }
        }

        return array_slice($result->body, 0, $this->params->module->get('rows'));
    }

    private function link($matches)
    {
        $sites['item']['battle.net'] = 'http://' . $this->params->global->get('region') . '.battle.net' . $matches[0];
        $sites['item']['wowhead.com'] = 'http://' . $this->params->global->get('locale') . '.wowhead.com/item=' . $matches[2];

        if ($matches[1] == 'achievement') {
            $achievement = substr($matches[2], strpos($matches[2], ':a') + 2);
            $sites['achievement']['battle.net'] = 'http://' . $this->params->global->get('region') . '.battle.net' . $matches[0];
            $sites['achievement']['wowhead.com'] = 'http://' . $this->params->global->get('locale') . '.wowhead.com/achievement=' . $achievement;
        }

        $sites['character']['battle.net'] = 'http://' . $this->params->global->get('region') . '.battle.net' . $matches[0];
        $sites['character']['wowhead.com'] = 'http://' . $this->params->global->get('locale') . '.wowhead.com/profile=' . $this->params->global->get('region') . '.' . $this->params->global->get('realm') . '.' . $matches[2] . '"';

        return isset($sites[$matches[1]][$this->params->global->get('link')]) ? $sites[$matches[1]][$this->params->global->get('link')] : $sites[$matches[1]]['battle.net'];
    }
}