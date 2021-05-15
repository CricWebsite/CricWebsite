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

if(isset($this->address_incomplete)){
    $obj->address_incomplete = true;
    $obj->info = JText::_('COM_BREEZINGCOMMERCE_ADDRESS_INCOMPLETE_FILL_OUT_NOW');
    $obj->href = $this->href;
    echo json_encode($obj);
    JFactory::getApplication()->close();
}

if(isset($this->empty)){
    $obj->null = null;
    $obj->info = JText::_('COM_BREEZINGCOMMERCE_INVOICE_GENERATE_ERROR_MESSAGE');
    echo json_encode($obj);
    JFactory::getApplication()->close();
}

echo json_encode($this->success);

exit;



