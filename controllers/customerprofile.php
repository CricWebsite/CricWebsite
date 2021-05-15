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

class BreezingcommerceControllerCustomerprofile extends JControllerLegacy
{
    function __construct()
    {
        parent::__construct();
        
        if(JFactory::getUser()->get('id', 0) <= 0){
            JFactory::getApplication()->enqueueMessage(JText::_('COM_BREEZINGCOMMERCE_LOGIN_TO_VIEW_PROFILE'), 'info');
            JFactory::getApplication()->redirect('index.php');
        }
    }
    
    function update_billing_information(){
       
        $profile = $this->getModel('customerprofile');
        
        try{
        
            $result = $profile->updateBillingInformation( JFactory::getApplication()->input->getBool('shipping') );
            
        }catch(Exception $e){
            
            $result = array(array('field' => '', 'type' => 'error', 'message' => $e));
        }
        
        if(JFactory::getApplication()->input->getWord('format') == 'json'){
            
            JRequest::setVar('format','json');
            JRequest::setVar('layout','json_billing_result');
            
        }
        
        JRequest::setVar('update_billing_result', $result);
        JRequest::setVar('tmpl', JRequest::getWord('tmpl',null));
        JRequest::setVar('view', 'customerprofile');
        parent::display();
    }
    
    function display($cachable = false, $urlparams = array())
    {
        
        JRequest::setVar('tmpl', JRequest::getWord('tmpl',null));
        JRequest::setVar('layout', JRequest::getWord('layout',null));
        JRequest::setVar('view', 'customerprofile');
        parent::display();
    }
}
