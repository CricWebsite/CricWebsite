<?php
/**
 * @package     BreezingCommerce
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


ob_end_clean();

header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
header('Content-Type: application/json');

$obj = new stdClass();

// return empty object for empty carts
if(isset($this->empty)){
    $obj->null = null; // keeping JSON standards, blank null is not allowed
    $obj->info = JText::_('COM_BREEZINGCOMMERCE_CHECKOUT_ERROR_MESSAGE');
    $obj->return_url = JRoute::_('index.php');
    echo json_encode($obj);
    JFactory::getApplication()->close();
}

$obj->shipping_method_result['errors']   = array();
$obj->shipping_method_result['shipping'] = array();
$obj->shipping_method_result['errors']   = isset($this->shipping_method_result['errors']) ? $this->shipping_method_result['errors'] : array();
$obj->shipping_method_result['shipping'] = isset($this->shipping_method_result['shipping']) ? $this->shipping_method_result['shipping']:  array();

echo json_encode($obj);

JFactory::getApplication()->close();