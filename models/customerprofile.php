<?php
/**
 * @package     BreezingCommerce
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcCart.php');

class BreezingcommerceModelCustomerprofile extends JModelLegacy
{
    function  __construct($config)
    {
        parent::__construct();
        
        
    }
    
    function updateBillingInformation($shipping = false){
        
        $errors = array();
        
        $config = CrBcHelpers::getBcConfig();
        
        if($shipping){
            $shipping = 'shipping_';
        } else {
            $shipping = '';
        }
        
        $db = JFactory::getDbo();
        
        $data = JRequest::get( 'post' );
        
        if(!isset($data[$shipping.'firstname']) || trim($data[$shipping.'firstname']) == ''){
            $errors[] = array('field' => $shipping.'firstname', 'type' => 'missing', 'message' => JText::_('COM_BREEZINGCOMMERCE_FIRSTNAME_REQUIRED'));
        }
        
        if(!isset($data[$shipping.'lastname']) || trim($data[$shipping.'lastname']) == ''){
            $errors[] = array('field' => $shipping.'lastname', 'type' => 'missing', 'message' => JText::_('COM_BREEZINGCOMMERCE_LASTNAME_REQUIRED'));
        }

        require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_breezingcommerce' . DS . 'classes' . DS . 'CrBcHelpers.php');

        if(!isset($data[$shipping.'email']) || !CrBcHelpers::isEmail($data[$shipping.'email'])){
            $errors[] = array('field' => $shipping.'email', 'type' => 'invalid', 'message' => JText::_('COM_BREEZINGCOMMERCE_MISSING_OR_INVALID_EMAIL_ADDRESS'));
        }
		
		if(!isset($data[$shipping.'email_repeat']) || !isset($data[$shipping.'email']) || $data[$shipping.'email'] != $data[$shipping.'email_repeat']){
            $errors[] = array('field' => $shipping.'email_repeat', 'type' => 'invalid', 'message' => JText::_('COM_BREEZINGCOMMERCE_EMAIL_REPEAT_NOMATCH'));
			$errors[] = array('field' => $shipping.'email', 'type' => 'invalid', 'message' => JText::_('COM_BREEZINGCOMMERCE_EMAIL_REPEAT_NOMATCH'));
        }
        
        $country_error = false;
        if(!isset($data[$shipping.'country_id']) || trim($data[$shipping.'country_id']) == ''){
            $country_error = true;
            $errors[] = array('field' => $shipping.'country_id', 'type' => 'missing', 'message' => JText::_('COM_BREEZINGCOMMERCE_COUNTRY_REQUIRED'));
        }
        
        $db->setQuery("Select count(id) From #__breezingcommerce_country_regions Where country_id = " . intval($data[$shipping.'country_id']));
        $amount_regions = $db->loadResult();
        
        if($amount_regions > 0 || $country_error){
            if(!isset($data[$shipping.'region_id']) || trim($data[$shipping.'region_id']) == '' || trim($data[$shipping.'region_id']) == '*' || trim($data[$shipping.'region_id']) == '0'){
                $errors[] = array('field' => $shipping.'region_id', 'type' => 'missing', 'message' => JText::_('COM_BREEZINGCOMMERCE_REGION_REQUIRED'));
            }
        }
        
        if(!isset($data[$shipping.'address']) || trim($data[$shipping.'address']) == ''){
            $errors[] = array('field' => $shipping.'address', 'type' => 'missing', 'message' => JText::_('COM_BREEZINGCOMMERCE_ADDRESS_REQUIRED'));
        }

        if(!isset($data[$shipping.'city']) || trim($data[$shipping.'city']) == ''){
            $errors[] = array('field' => $shipping.'city', 'type' => 'missing', 'message' => JText::_('COM_BREEZINGCOMMERCE_CITY_REQUIRED'));
        }

        if(!isset($data[$shipping.'zip']) || trim($data[$shipping.'zip']) == ''){
            $errors[] = array('field' => $shipping.'zip', 'type' => 'missing', 'message' => JText::_('COM_BREEZINGCOMMERCE_ZIP_REQUIRED'));
        }

        //if(!isset($data[$shipping.'phone']) || trim($data[$shipping.'phone']) == ''){
        //    $errors[] = array('field' => $shipping.'phone', 'type' => 'missing', 'message' => JText::_('COM_BREEZINGCOMMERCE_PHONE_REQUIRED'));
        //}
        
        // user data
        
        if($shipping == '') {
        
            JFactory::getLanguage()->load('com_users', JPATH_SITE);

            $user = new JUser;
            $user->load(JFactory::getUser()->get('id', 0));

            $user_data = array();

            if (count($errors) == 0 && isset($data['password']) &&  ( trim($data['password']) != '' || trim($data['password2']) != '' ) ) {

                if (function_exists('mb_strlen')) {

                    if (mb_strlen($data['password']) < 8) {
                        $errors[] = array('field' => 'password', 'type' => 'invalid', 'message' => JText::_('COM_BREEZINGCOMMERCE_PASSWORD_LENGTH_INFO'));
                    }

                } else {

                    if (strlen($data['password']) < 8) {
                        $errors[] = array('field' => 'password', 'type' => 'invalid', 'message' => JText::_('COM_BREEZINGCOMMERCE_PASSWORD_LENGTH_INFO'));
                    }
                }

                if($data['password'] != $data['password2']) {
                    $errors[] = array('field' => 'password2', 'type' => 'invalid', 'message' => JText::_('COM_BREEZINGCOMMERCE_PASSWORD_NO_MATCH'));
                }

                if( count($errors) == 0 ){

                    $user_data['password'] = $data['password'];
                    $user_data['password2'] = $data['password2'];
                }
            }

            if(count($errors)){

                return $errors;
            }

            $user_data['email'] = JStringPunycode::emailToPunycode($data[$shipping.'email']);

            // Bind the data.
            if (!$user->bind($user_data))
            {
                $errors[] = array('field' => '__registration', 'type' => 'error', 'message' => JText::sprintf('COM_USERS_REGISTRATION_BIND_FAILED', $user->getError()));
            }

            // Load the users plugin group.
            JPluginHelper::importPlugin('user');

            // Store the data.
            if (!$user->save())
            {
                $errors[] = array('field' => '__registration', 'type' => 'error', 'message' => JText::sprintf('COM_USERS_REGISTRATION_SAVE_FAILED', $user->getError()));
            }            
        }
        
        if(count($errors)){

            return $errors;
        }
        
        if( $shipping == '' ){
            $data['use_shipping_address'] = 0;
            $data['address_complete'] = 1;
        }
        else {
           $data['use_shipping_address'] = 1;
        }
        
        require_once(JPATH_SITE . '/administrator/components/com_breezingcommerce/tables/customer.php');
        $row = new TableCustomer($db);
        $row->load(array('userid' => JFactory::getUser()->get('id', 0)));
        
        if (!$row->bind($data)) {
            throw new Exception('Error binding '.($shipping == '' ? 'billing' : 'shipping').' data');
        }

        if (!$row->check()) {
            throw new Exception('Error checking '.($shipping == '' ? 'billing' : 'shipping').' data');
        }

        if (!$row->store()) {
            throw new Exception('Error storing '.($shipping == '' ? 'billing' : 'shipping').' data');
        }
        
        return array();
    }
    
    function getData()
    {
        if(JFactory::getUser()->get('id', 0) <= 0){
            
            return array();
        }
        
        $this->_db->setQuery("Select * From #__breezingcommerce_customers Where userid = " . intval(JFactory::getUser()->get('id', 0)) . " Limit 1");
        $this->_data = $this->_db->loadObject();
        
        if (!$this->_data) {
            
            $this->_data = new stdClass();
            $this->_data->id = 0;
            
            $this->_data->customergroup_id = 0;
            
            $this->_data->username = 0;
            $this->_data->userid = 0;
            $this->_data->firstname = null;
            $this->_data->lastname = null;
            $this->_data->company = null;
            $this->_data->email = null;
            $this->_data->address = null;
            $this->_data->address2 = null;
            $this->_data->city = null;
            $this->_data->zip = null;
            $this->_data->region_id = null;
            $this->_data->country_id = null;
            $this->_data->phone = null;
            $this->_data->mobile = null;
            $this->_data->fax = null;

            $this->_data->shipping_firstname = null;
            $this->_data->shipping_lastname = null;
            $this->_data->shipping_company = null;
            $this->_data->shipping_email = null;
            $this->_data->shipping_address = null;
            $this->_data->shipping_address2 = null;
            $this->_data->shipping_city = null;
            $this->_data->shipping_zip = null;
            $this->_data->shipping_region_id = null;
            $this->_data->shipping_country_id = null;
            $this->_data->shipping_phone = null;
            $this->_data->shipping_mobile = null;
            $this->_data->shipping_fax = null;
            
            $this->_data->use_shipping_address = false;
            $this->address_complete = 0;
        }
        
        $query = ' Select * From #__breezingcommerce_countries Where published = 1';
        $this->_db->setQuery($query);
        $this->_data->countries = $this->_db->loadObjectList();
        
        $this->_data->countries = CrBcHelpers::populateTranslation($this->_data->countries, 'country', array('title' => 'name'));
        
        foreach ($this->_data->countries As $country) {
            $country->regions = array();
            $query = ' Select * From #__breezingcommerce_country_regions Where country_id = ' . $country->id;
            $this->_db->setQuery($query);
            $regions = $this->_db->loadObjectList();
            
            foreach ($regions As $region) {
                $country->regions[] = $region;
            }
            
            $country->regions = CrBcHelpers::populateTranslation($country->regions, 'country_region_' . $country->id, array('title' => 'name'));
            
        }
        
        return $this->_data;
    }
}
