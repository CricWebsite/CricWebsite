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

class BreezingcommerceControllerCart extends JControllerLegacy
{
    function __construct()
    {
        parent::__construct();
    }
    
    function remove(){
        
        $item_ids = JRequest::getVar('order_item_ids', array(), 'REQUEST', 'ARRAY');
        JArrayHelper::toInteger($item_ids);
        
        $cart = $this->getModel('cart');
        
        $cart->remove($item_ids);
        
        if(JFactory::getApplication()->input->getWord('format') == 'json'){
            
            JRequest::setVar('format','json');
            JRequest::setVar('layout','json_response');
            
        }
        
        JRequest::setVar('tmpl', JRequest::getWord('tmpl',null));
        JRequest::setVar('view', 'cart');
        parent::display();
    }
    
    function update_amount(){
        
        $cart = $this->getModel('cart');
        
        $cart->updateAmount();
        
        if(JFactory::getApplication()->input->getWord('format') == 'json'){
            
            JRequest::setVar('format','json');
            JRequest::setVar('layout','json_response');
            
        }
        
        JRequest::setVar('tmpl', JRequest::getWord('tmpl',null));
        JRequest::setVar('view', 'cart');
        parent::display();
    }
    
    function get_item_price(){
        
        if(JFactory::getApplication()->input->getWord('format') == 'json'){
            
            JRequest::setVar('format','json');
            JRequest::setVar('layout','json_get_item_price');
            JRequest::setVar('tmpl', JRequest::getWord('tmpl',null));
            JRequest::setVar('view', 'cart');
            parent::display();
            
        }else{
            throw new CrBcCartException('get_item_price is only available in json format!');
        }
    }
    
    function add(){
        
        // call the default cart view if non-json or the json response if an ajax cart add happens
        $cart = $this->getModel('cart');
        
        // no, we are not redirecting as joomla might use javascript based redirects and this would break json responses
        // instead we keep it as it is and call the appropriate view, layout and format
        if(JFactory::getApplication()->input->getWord('format') == 'json'){
            
            if( JRequest::getInt('immediate_checkout_singleton', 0) ){

                $cart->clear();
            }
            
            $return = $cart->add();
            
            JRequest::setVar('format','json');
			
			if($return == ''){
				
				JRequest::setVar('layout','json_response');
			}
			else{
			
				JRequest::setVar('error',$return);
				JRequest::setVar('layout','json_error');
			}
        
        }else{
            // in case of regular views, we redirect to default page with an error message if there has been an add-to-cart issue
            
			if( JRequest::getInt('immediate_checkout_singleton', 0) ){

				$cart->clear();
			}

			$return = $cart->add();

			if($return == ''){

				if( JRequest::getInt('immediate_checkout', 0) ){
					JFactory::getApplication()->redirect(JRoute::_('index.php?option=com_breezingcommerce&controller=checkout'));
				}

			}
			else{

				JFactory::getApplication()->enqueueMessage($return, 'error');

				$return_url = '';
				if(trim(JFactory::getApplication()->input->get('return_url', '')) != ''){
					$return_url = trim(JFactory::getApplication()->input->get('return_url', ''));
					$return_url = CrBcHelpers::bSixtyFourDecode($return_url);
					if(JUri::isInternal($return_url)){
						JFactory::getApplication()->redirect($return_url);
					}
				}
			}
        }
        
        JRequest::setVar('tmpl', JRequest::getWord('tmpl',null));
        JRequest::setVar('view', 'cart');
        parent::display();
    }

    function display($cachable = false, $urlparams = array())
    {
        JRequest::setVar('tmpl', JRequest::getWord('tmpl',null));
        JRequest::setVar('layout', JRequest::getWord('layout',null));
        JRequest::setVar('view', 'cart');
        
        parent::display();
    }
}
