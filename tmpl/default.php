<?php
/**
 * WoW Armory Guild News
 *
 * @author     Branko Wilhelm <bw@z-index.net>
 * @link       http://www.z-index.net
 * @copyright  2011 Branko Wilhelm
 * @package    mod_wow_armory_guild_news
 * @license    GNU Public License <http://www.gnu.org/licenses/gpl.html>
 * @version    $Id$
 * @link       www.z-index.net
 */
// no direct access
defined('_JEXEC') or die;
?>
<div class="mod_wow_armory_guild_news<?php echo $params->get('moduleclass_sfx'); ?>">
    <ul>
        <?php foreach ($news as $row) { ?>
            <li><?php echo $row; ?></li>
        <?php } ?>
    </ul>
</div>
