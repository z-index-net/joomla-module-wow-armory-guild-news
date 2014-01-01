<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2013 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('radio');

class JFormFieldAjax extends JFormFieldRadio
{

    public $type = 'Ajax';

    protected function getInput()
    {
        if (JPluginHelper::isEnabled('system', 'wow')) {
            return parent::getInput();
        }

        return '';
    }

    protected function getLabel()
    {
        if (JPluginHelper::isEnabled('system', 'wow')) {
            return parent::getLabel();
        }

        return '';
    }

    protected function getOptions()
    {
        $options[] = JHtml::_('select.option', 0, JText::_('JNO'));
        $options[] = JHtml::_('select.option', 1, JText::_('JYES'));

        return $options;
    }
}