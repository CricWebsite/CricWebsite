<?php
/**
 * @package     BreezingCommerce
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/

// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

class BreezingcommerceControllerProduct extends JControllerLegacy
{
    function __construct()
    {
        parent::__construct();
        JHtml::_('behavior.modal');
        require_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcCategoriesHelper.php' );
    }
    
    function apply_display_plugins(){
        
        if(JFactory::getApplication()->input->getWord('format') == 'json'){
            
            JRequest::setVar('format','json');
            JRequest::setVar('layout','json_apply_display_plugins');
            JRequest::setVar('tmpl', JRequest::getWord('tmpl',null));
            JRequest::setVar('view', 'product');
            parent::display();
            
        }else{
            throw new CrBcCartException('apply_display_plugins is only available in json format!');
        }
    }
    
    function get_product_price(){
        
        if(JFactory::getApplication()->input->getWord('format') == 'json'){
            
            JRequest::setVar('format','json');
            JRequest::setVar('layout','json_get_product_price');
            JRequest::setVar('tmpl', JRequest::getWord('tmpl',null));
            JRequest::setVar('view', 'product');
            parent::display();
            
        }else{
            throw new CrBcCartException('get_item_price is only available in json format!');
        }
    }

    function display($cachable = false, $urlparams = array())
    {
        if(JFactory::getApplication()->input->getWord('format') == 'json'){
            JRequest::setVar('layout','json_product');
        }
        
        JRequest::setVar('tmpl', JRequest::getWord('tmpl',null));
        JRequest::setVar('layout', JRequest::getWord('layout',null));
        JRequest::setVar('view', 'product');
        parent::display();
    }
}
