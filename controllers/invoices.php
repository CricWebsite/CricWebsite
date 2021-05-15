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

class BreezingcommerceControllerInvoices extends JControllerLegacy
{
    function __construct()
    {
        parent::__construct();
        
        if(JFactory::getUser()->get('id', 0) <= 0){
            JFactory::getApplication()->enqueueMessage(JText::_('COM_BREEZINGCOMMERCE_LOGIN_TO_VIEW_INVOICES'), 'info');
            JFactory::getApplication()->redirect('index.php');
        }
    }
    
    function generate_invoice(){
        
        if(JFactory::getApplication()->input->getWord('format') == 'json'){

            $invoices = $this->getModel('invoices');
        
            if( !$invoices->isCustomerDataComplete() ){

                JRequest::setVar('address_incomplete', 1);
                JRequest::setVar('href', JRoute::_('index.php?option=com_breezingcommerce&controller=customerprofile', false));
            
            }
            
            JRequest::setVar('format','json');
            JRequest::setVar('layout','generate');

            JRequest::setVar('tmpl', JRequest::getWord('tmpl',null));
            JRequest::setVar('layout', JRequest::getWord('layout',null));
            JRequest::setVar('view', 'invoices');
            
            parent::display();
            
        }else{
            
            throw new Exception('This is a json-only task', 500);
        }
    }
    
    function download(){
        
        $invoices = $this->getModel('invoices');
        
        if( !$invoices->isCustomerDataComplete() ){
            
            JFactory::getApplication()->enqueueMessage(JText::_('COM_BREEZINGCOMMERCE_ADDRESS_INCOMPLETE_FILL_OUT_NOW'), 'info');
            JFactory::getApplication()->redirect(JRoute::_('index.php?option=com_breezingcommerce&controller=customerprofile', false));
            
        } else {
        
            if(JFactory::getApplication()->input->getWord('format') == 'raw'){

                JRequest::setVar('format','raw');
                JRequest::setVar('layout','download');

            }

            JRequest::setVar('tmpl', JRequest::getWord('tmpl',null));
            JRequest::setVar('layout', JRequest::getWord('layout',null));
            JRequest::setVar('view', 'invoices');
            
            parent::display();
        
        }
    }
    
    function display($cachable = false, $urlparams = array())
    {
        
        JRequest::setVar('tmpl', JRequest::getWord('tmpl',null));
        JRequest::setVar('layout', JRequest::getWord('layout',null));
        JRequest::setVar('view', 'invoices');
        parent::display();
    }
}
