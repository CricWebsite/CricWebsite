<?php
/**
 * @package     BreezingCommerce
 * @author      Markus Bopp
 * @link        https://crosstec.org
 * @license     GNU/GPL
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.version');


function BreezingcommerceGetCategorySegments($category_id, $remove_tail = true){
    
	$segments = array();
	
    $entries = CrBcHelpers::getCategoryPath($category_id);

	if( count($entries) ){

        unset($entries[0]);
        $entries = array_merge($entries);

    }
	
    if( count($entries) && $remove_tail ){

        unset($entries[count($entries)-1]);
        $entries = array_merge($entries);
    }

    if( count($entries) ){

        $entries_length = count($entries);

        for($i = 0; $i < $entries_length; $i++){

            $segments[] = $entries[$i]->alias;
        }
    }
    
    return $segments;
}

function BreezingcommerceBuildRoute(&$query) {
    
    $segments = array();

    if(isset($query['controller'])){
        
        switch($query['controller']){

            case 'category':
                
                if (isset($query['alias'])) {
                    
                    if(!isset($query['perm']) || $query['perm'] != 1){
                        
                        $segments = BreezingcommerceGetCategorySegments($query['category_id']);
                        
                    }else{
                        
                        if(isset($query['perm'])){
                            unset($query['perm']);
                        }
                    }
                    
                    $segments[count($segments)] = '1'.$query['category_id'].':'.$query['alias'];
                    unset($query['alias']);
                    unset($query['category_id']);
                }
                
            break;
            case 'categories':
                
                if (isset($query['alias'])) {
                    
                    if(!isset($query['perm']) || $query['perm'] != 1){
                        $segments = BreezingcommerceGetCategorySegments($query['parent_category_id']);
                    }
                    else
                    {
                        if(isset($query['perm'])){
                            unset($query['perm']);
                        }
                    }
                    
                    $segments[count($segments)] = '2'.$query['parent_category_id'].':'.$query['alias'];
                    unset($query['alias']);
                    unset($query['parent_category_id']);
                }
                
            break;
            case 'product':
                
                if (isset($query['alias'])) {
                    
                    if(!isset($query['perm']) || $query['perm'] != 1){
                    
                        $db = JFactory::getDbo();
                        $db->setQuery("Select * From #__breezingcommerce_product_categories Where product_id = " . $db->quote($query['product_id']) . " Order By ordering Limit 1");
                        $product_category = $db->loadObject();

                        $segments = array();
                        if($product_category){
                            $segments = BreezingcommerceGetCategorySegments($product_category->category_id, false);
                        }

                    }
                    else
                    {
                        if(isset($query['perm'])){
                            unset($query['perm']);
                        }
                    }
                    
                    $segments[count($segments)] = '3'.$query['product_id'].':'.$query['alias'];
                    unset($query['alias']);
                    unset($query['product_id']);
                }
                
            break;
            default:
                
                $pfx = JText::_('COM_BREEZINGCOMMERCE_ROUTER_DEFAULT_PREFIX');
                
                switch($query['controller']){
                    case 'cart';
                        $pfx = 'cart';
                        break;
                    case 'checkout';
                        $pfx = 'checkout';
                        break;
                }
                
                $segments[0] = $pfx . ':'.$query['controller'];
                
        }
        
    }
    
    if(isset($query['view'])){
        unset($query['view']);
    }
    
    if(isset($query['controller'])){
        unset($query['controller']);
    }
    
    return $segments;
}

function BreezingcommerceParseRoute($segments) {
    
    if(count($segments) == 0){
        
        return array();
    }
    
    $vars = array();
    
    $exploded = explode(':',$segments[count($segments)-1], 2);
    
    if( $exploded[0] != '' ){
    
        $_char1 = substr($exploded[0],0,1);

        switch($_char1){
            case '1':
                $vars['controller'] = 'category';
                $vars['category_id'] = substr($exploded[0],1);
                if(isset($exploded[1])){
                    $vars['alias'] = isset($exploded[1]) ? $exploded[1] : '';
                }
                break;
            case '2':
                $vars['controller'] = 'categories';
                $vars['parent_category_id'] = substr($exploded[0],1);
                if(isset($exploded[1])){
                    $vars['alias'] = isset($exploded[1]) ? $exploded[1] : '';
                }
                break;
            case '3':
                $vars['controller'] = 'product';
                $vars['product_id'] = substr($exploded[0],1);
                if(isset($exploded[1])){
                    $vars['alias'] = isset($exploded[1]) ? $exploded[1] : '';
                }
                break;
            default:
                if(!isset($exploded[1])){
                    $vars['controller'] = $exploded[0];
                }else{
                    $vars['controller'] = $exploded[1];
                }
        }

    }
    
    return $vars;
}
