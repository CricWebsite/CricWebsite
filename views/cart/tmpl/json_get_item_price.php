<?php
/**
 * @package     BreezingCommerce
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// index.php?option=com_breezingcommerce&controller=cart&product_id=501&task=get_item_price&format=json&layout=json_get_item_price

ob_end_clean();

header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
header('Content-Type: application/json');

if(isset($this->price->price_single_tax_list)){
    if(!count($this->price->price_single_tax_list)){
        $this->price->price_single_tax_list = new stdClass();
    }
}

// determine tax rounding errors
if(isset($this->price->price_tax_list)){
    
    $difftax = 0;

    foreach($this->price->price_tax_list As $key => $value){

        $difftax += CrBcCart::round($this->price->price_tax_list[$key]['tax']);
    }

    // find out if there are rounding errors against the glbally rounded tax
    $rounderror = CrBcCart::round($this->price->price_gross - $this->price->price_net) - CrBcCart::round($difftax);

    // if there is a rounding error, add the error to the very next item from the tax list
    if($rounderror != 0){

        foreach($this->price->price_tax_list As $key => $value){

            $this->price->price_tax_list[$key]['tax'] += CrBcCart::round($rounderror);
            //$this->order_info->taxes += CrBcCart::round($rounderror);
            break;
        }
    }

    $price_tax_list_formatted = $this->price->price_tax_list;

    foreach($price_tax_list_formatted As $key => $info){
        $price_tax_list_formatted[$key]['tax_name'] = $info['name'] . ' ('.CrBcCart::formatNumber($info['rate']).'%)';
        $price_tax_list_formatted[$key]['tax'] = $this->cart_instance->formatPrice($info['tax']);
    }

    // json doesn't like to treat empty arrays when they are accessed like objects, so we return an empty object
    $this->price->price_tax_list_formatted = count($price_tax_list_formatted) ? $price_tax_list_formatted : new stdClass();

}

echo json_encode($this->price);

JFactory::getApplication()->close();