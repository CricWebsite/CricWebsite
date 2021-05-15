<?php
/**
 * @package     BreezingCommerce
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// index.php/en/single-product.html?controller=cart&task=add&product_id=501&format=json&layout=json_response

ob_end_clean();

header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
header('Content-Type: application/json');

$obj = new stdClass();

$obj->update_billing_result = $this->update_billing_result;

echo json_encode($obj);

JFactory::getApplication()->close();