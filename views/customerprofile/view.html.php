<?php
/**
 * @package     BreezingCommerce
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/

// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class BreezingcommerceViewCustomerprofile extends JViewLegacy
{
    function display($tpl = null)
    {
        if( JRequest::getVar('task') == 'update_billing_information' && JFactory::getApplication()->input->getWord('format') == 'json' && JRequest::getVar('layout') == 'json_billing_result' ){
            
            $result = JRequest::getVar('update_billing_result', array());
            $this->assignRef('update_billing_result', $result);
            parent::display($tpl);
            
        } else {
            
            $ajax_cart_add = true;

            require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcCart.php');

            JHtml::_('behavior.keepalive');

            CrBcHelpers::addCssFile(Juri::root(true).'/components/com_breezingcommerce/css/font-awesome/css/font-awesome.min.css');
            JFactory::getDocument()->addScript(Juri::root(true).'/components/com_breezingcommerce/js/scrollto.js');
            JFactory::getDocument()->addScript(JURI::root(true).'/components/com_breezingcommerce/js/breezingcommerce.js');
            JFactory::getDocument()->addScript(Juri::root(true).'/components/com_breezingcommerce/js/customerprofile.js');
            JFactory::getDocument()->addScriptDeclaration(
            '
            var crbc_wait_msg = '.CrBcHelpers::jsonEncode(JText::_('COM_BREEZINGCOMMERCE_WAIT_MESSAGE')).';
            var crbc_item_id = '.CrBcHelpers::jsonEncode(JRequest::getInt('Itemid', 0)).';
            var crbc_customerprofile = new CrBcCustomerprofile('.CrBcHelpers::jsonEncode($ajax_cart_add).', '.CrBcHelpers::jsonEncode(JUri::getInstance()->toString()).', '.CrBcHelpers::jsonEncode(JFactory::getUser()->get('id', 0) > 0 ? true : false).');
            ');

            $item = $this->get('Data');

            $this->assignRef('item', $item);

            $menu = JFactory::getApplication()->getMenu();
            $active = $menu->getActive();

            if(is_object($active)){

                $heading = $active->params->get('page_heading');

                if(!$heading){

                    $heading = JText::_('COM_BREEZINGCOMMERCE_CUSTOMER_PROFILE');
                    JFactory::getDocument()->setTitle($heading);
                }

                $this->assignRef('heading', $heading);

                $pageclass_sfx = $active->params->get('pageclass_sfx');
                $this->assignRef('pageclass_sfx', $pageclass_sfx);

            } else {

                $heading = JText::_('COM_BREEZINGCOMMERCE_CUSTOMER_PROFILE');
                JFactory::getDocument()->setTitle($heading);

                $empty = '';
                $this->assignRef('heading', $empty);
                $this->assignRef('pageclass_sfx', $empty);
            }
            
            CrBcHelpers::setPageTitle(JFactory::getDocument()->getTitle());

            parent::display($tpl);
        }
    }
}
