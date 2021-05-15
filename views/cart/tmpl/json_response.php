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

// return empty object for empty carts
if(isset($this->empty_cart)){
    $obj->null = null; // keeping JSON standards, blank null is not allowed
    echo json_encode($obj);
    JFactory::getApplication()->close();
}

// TODO: apply the off reductions into the lookup!
// apply a quick price lookup for a single product without the need to add something to a cart
if($this->lookup_item_id > 0){
    
    // reset all taxes
    foreach($this->order_info->price_tax_list as $key => $value){
        $this->order_info->price_tax_list[$key]['tax'] = 0;
    }
    
    // reset some order info
    $this->order_info->price_net = 0;
    $this->order_info->price_gross = 0;
    $this->order_info->grand_total = 0;
    $this->order_info->taxes = 0;
    
    $i = 0;
    foreach($this->items As $item){
        
        // reset the price for the lookuped product
        if($item->order_item_id == $this->lookup_item_id){
            $this->items[$i]->price_net = $item->price_single_net * $this->lookup_amount;
            $this->items[$i]->price_gross = 0;
            $this->items[$i]->price_taxes = 0;
        }
        
        // re-add single product taxes as well as restore the amount of general taxes
        foreach($item->price_tax_list As $key => $value){
            
            // calculating the item's price tax list
            //$this->items[$i]->price_tax_list[$key]['tax'] = ( $this->items[$i]->price_net / 100 ) * $value['rate'];
            
            // adding the taxes to the looked up product
            if($item->order_item_id == $this->lookup_item_id){
                //$this->items[$i]->price_gross += $this->items[$i]->price_tax_list[$key]['tax'];
                $this->items[$i]->price_taxes += $this->items[$i]->price_tax_list[$key]['tax'];
            }
            
            // updating the global tax list
            $this->order_info->price_tax_list[$key]['tax'] += $this->items[$i]->price_tax_list[$key]['tax'];
            
            // add up all applied taxes to the order info
            $this->order_info->taxes += $this->items[$i]->price_tax_list[$key]['tax'];
        }
        
        // adding the net on the re-calculated taxes for the lookuped product, to receive the gross
        if($item->order_item_id == $this->lookup_item_id){
            $this->items[$i]->price_gross = $this->items[$i]->price_single_gross * $this->lookup_amount;
        }
        
        // refill the order info
        $this->order_info->price_net += $this->items[$i]->price_net;
        $this->order_info->price_gross += $this->items[$i]->price_gross;
        
        $i++;
    }
    
    $this->order_info->grand_total = $this->order_info->price_gross + $this->order_info->shipping_costs;
}

//print_r($this->items);

$obj->items = $this->items;

// can't clone an array of objects
$items_formatted = $this->items;

// but can clone single objects and reassing at the original array index
$i = 0;
foreach($items_formatted As $item){
    $item2 = clone $item;
    $item2->price_single_net = $this->cart_instance->formatPrice($item->price_single_net);
    $item2->price_net = $this->cart_instance->formatPrice($item->price_net);
    $item2->price_single_gross = $this->cart_instance->formatPrice($item->price_single_gross);
    $item2->price_gross = $this->cart_instance->formatPrice($item->price_gross);
    $items_formatted[$i] = $item2;
    $i++;
}

$obj->items_formatted = $items_formatted;

// determine tax rounding errors

$difftax = 0;

foreach($this->order_info->price_tax_list As $key => $value){

    $difftax += CrBcCart::round($this->order_info->price_tax_list[$key]['tax']);
}

// find out if there are rounding errors against the glbally rounded tax
$rounderror = CrBcCart::round($this->order_info->price_gross - $this->order_info->price_net) - CrBcCart::round($difftax);

// if there is a rounding error, add the error to the very next item from the tax list
if($rounderror != 0){

    foreach($this->order_info->price_tax_list As $key => $value){

        $this->order_info->price_tax_list[$key]['tax'] += CrBcCart::round($rounderror);
        //$this->order_info->taxes += CrBcCart::round($rounderror);
        break;
    }
}

$this->order_info->taxes = $this->order_info->price_gross - $this->order_info->price_net;

$obj->price_tax_list = count($this->order_info->price_tax_list) ? $this->order_info->price_tax_list : new stdClass();
$price_tax_list_formatted = $this->order_info->price_tax_list;


foreach($price_tax_list_formatted As $key => $info){
    $price_tax_list_formatted[$key]['tax_name'] = $info['name'] . ' ('.CrBcCart::formatNumber($info['rate']).'%)';
    $price_tax_list_formatted[$key]['tax'] = $this->cart_instance->formatPrice($info['tax']);
}

// json doesn't like to treat empty arrays when they are accessed like objects, so we return an empty object
$obj->price_tax_list_formatted = count($price_tax_list_formatted) ? $price_tax_list_formatted : new stdClass();

$obj->price_net = $this->order_info->price_net;
$obj->price_net_formatted = $this->cart_instance->formatPrice($this->order_info->price_net);
$obj->price_gross = $this->order_info->price_gross;
$obj->price_gross_formatted = $this->cart_instance->formatPrice($this->order_info->price_gross);
$obj->shipping_costs = $this->order_info->shipping_costs;
$obj->shipping_costs_formatted = $this->cart_instance->formatPrice($this->order_info->shipping_costs);
$obj->grand_total = $this->order_info->grand_total;
$obj->grand_total_formatted = $this->cart_instance->formatPrice($this->order_info->grand_total);
$obj->return_url = $this->return_url;

echo json_encode($obj);

JFactory::getApplication()->close();