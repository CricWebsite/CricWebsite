<?php
/**
 * @package     BreezingCommerce
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

@error_reporting(E_ALL ^ E_DEPRECATED);

jimport( 'joomla.application.component.helper' );
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

if(!defined('DS')){
    define('DS','/');
}

require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_breezingcommerce' . DS . 'classes' . DS . 'CrBcCartException.php');
require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_breezingcommerce' . DS . 'classes' . DS . 'CrBcOutOfStockException.php');
require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_breezingcommerce' . DS . 'classes' . DS . 'CrBcAttributesOutOfStockException.php');
require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_breezingcommerce' . DS . 'classes' . DS . 'CrBcCache.php');

JHtml::_('behavior.framework', true);

// force jquery to be loaded after mootools but before any other js (since J! 3.4)
JHtml::_('jquery.framework');

JHtml::_('bootstrap.framework');

jimport('joomla.version');
$version = new JVersion();

define('CRBCOLDJ', false);

// Require the base controller
require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_breezingcommerce' . DS . 'classes' . DS . 'CrBcHelpers.php');
$libpath = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_breezingcommerce' . DS . 'classes' . DS . 'plugin' . DS;
require_once($libpath . 'CrBcPlugin.php');
require_once( JPATH_COMPONENT.DS.'controller.php' );

// Require specific controller if requested
// Due to the view and controller name equality, we ask for the view to determine the controller
$controller = JRequest::getWord('view', 'product');

// if we need a specific controller override, we are doing it here
if(JRequest::getWord('controller', '') != ''){
    
    $controller = JRequest::getWord('controller', '');
}

$_controller = $controller;

JFactory::getDocument()->addScript(JUri::root(true).'/components/com_breezingcommerce/js/breezingcommerce.js');

// THEMING
// Note: the use of addCssFile and addCss is order sensitive, unlike joomla's addStyleSheet and addStyle declaration
// But it gives us the ability to override template styles, such as Bootstrap

CrBcHelpers::addCssFile(JUri::root(true).'/components/com_breezingcommerce/css/system/global.css');

if( JFile::exists(JPATH_SITE.'/components/com_breezingcommerce/css/system/'.$_controller.'.css') ){
    CrBcHelpers::addCssFile(JUri::root(true).'/components/com_breezingcommerce/css/system/'.$_controller.'.css');
}
if( JFile::exists(JPATH_SITE.'/components/com_breezingcommerce/js/system/'.$_controller.'.js') ){
    JFactory::getDocument()->addScript(JUri::root(true).'/components/com_breezingcommerce/js/system/'.$_controller.'.js');
}

// THEMING END

if($controller) {
    $path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
    if (file_exists($path)) {
        require_once $path;
    }
}

if( !class_exists('BreezingcommerceController'.ucfirst( $controller )) ){
    throw new Exception('Controller '.$controller.' not found!', 404);
}

$config = CrBcHelpers::getBcConfig();

// remember the selected currency
if( $config->get('allow_currency_selection', 0) && JRequest::getInt('crbc_set_currency', 0) != 0 ){
    JFactory::getDbo()->setQuery("Select id From #__breezingcommerce_currencies Where id = " . JRequest::getInt('crbc_set_currency', 0) . " And published = 1");
    if( JFactory::getDbo()->loadResult() ){
        JFactory::getSession()->set('com_breezingcommerce.currency', JRequest::getInt('crbc_set_currency', 0));
    }
}else if($config->get('allow_currency_selection', 0) != 1){
    JFactory::getSession()->clear('com_breezingcommerce.currency');
}

// Create the controller
$classname    = 'BreezingcommerceController'.ucfirst( $controller );
$controller   = new $classname( );

// Loading the user location plugins one-by-one.
// if one doesn't work properly, try the next one
$form = CrBcHelpers::getBcConfig();

if( ( $form->get('enable_location_services', 0) == 1 || $form->get('location_based_tax_rules', 0) == 1) && JFactory::getSession()->get('com_breezingcommerce.userlocation', null) === null ){
    
    if(!CrBcHelpers::isBot()){

        $plugins = CrBcPlugin::getPlugins('userlocation');

        foreach($plugins As $plugin){
            $plugin_instance = CrBcPlugin::getPluginInstance($plugin->name, $plugin->type, 'site');
            if($plugin_instance instanceof CrBcUserlocationSitePlugin){
                try{
                    $plugin_instance->init();
                    if(!$plugin_instance->hasError()){
                        $userlocation = new stdClass();
                        $userlocation->country = $plugin_instance->getCountry();
                        $userlocation->country_code = $plugin_instance->getCountryCode();
                        $userlocation->region = $plugin_instance->getRegion();
                        $userlocation->region_code = $plugin_instance->getRegionCode();
                        $userlocation->city = $plugin_instance->getCity();
                        $userlocation->city_code = $plugin_instance->getCityCode();
                        $userlocation->zip_code = $plugin_instance->getZipCode();
                        $userlocation->longitude = $plugin_instance->getLongitude();
                        $userlocation->latitude = $plugin_instance->getLatitude();
                        $userlocation->continent = $plugin_instance->getContinent();
                        $userlocation->continent_code = $plugin_instance->getContinentCode();
                        $userlocation->time_zone = $plugin_instance->getTimeZone();
                        JFactory::getSession()->set('com_breezingcommerce.userlocation', $userlocation);
                        break;
                    }
                }catch(Exception $e){ /* we are quiet here to give other plugins a chance */ }
            }
        }
    }
    
} else if( $form->get('enable_location_services', 0) == 0 && $form->get('location_based_tax_rules', 0) == 0 ){
    
    JFactory::getSession()->clear('com_breezingcommerce.userlocation');
}

// Perform the Request task
$controller->execute( JRequest::getWord( 'task' ) );

// Redirect if set by the controller
$controller->redirect();

$plugins = CrBcPlugin::getPlugins('theme');

foreach($plugins As $plugin){
    
    $plugin_instance = CrBcPlugin::getPluginInstance($plugin->name, $plugin->type, 'site');
    
    if($plugin_instance instanceof CrBcThemeSitePlugin){
        
        $plugin_instance->wireUp();
    }
    
    // we only need the first one, therefore we need to take a break...
    break;
}
