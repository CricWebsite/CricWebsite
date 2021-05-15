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

$this->price['gross_formatted'] = $this->cart_instance->formatPrice($this->price['gross']);
$this->price['net_formatted'] = $this->cart_instance->formatPrice($this->price['net']);

$this->price['sale_price']['gross_formatted'] = $this->cart_instance->formatPrice($this->price['sale_price']['gross']);
$this->price['sale_price']['net_formatted'] = $this->cart_instance->formatPrice($this->price['sale_price']['net']);

$this->price['price_group'] = $this->price_group;

echo json_encode($this->price);

JFactory::getApplication()->close();