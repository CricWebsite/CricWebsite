<?php
/**
 * @package     BreezingCommerce
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/

defined('_JEXEC') or die('Restricted access');

$multiple = '';
?>
<style type="text/css">
    .crbc-category-filter-image{
        width: 200px;
    }  
</style>
<script type="text/javascript">
<!--
Joomla.tableOrdering = function( order, dir, task ) {
	var form = document.adminForm;
        //form.limitstart.value = 0;
	document.adminForm.submit( task );
};
function listItemTask( id, task ) {
   
    var f = document.adminForm;
    //f.limitstart.value = 0;
    cb = eval( "f." + id );

    if (cb) {
        for (i = 0; true; i++) {
            cbx = eval("f.cb"+i);
            if (!cbx) break;
            cbx.checked = false;
        } // for
        cb.checked = true;
        f.boxchecked.value = 1;

        submitbutton(task);
    }
    return false;
}
if( typeof Joomla != "undefined" ){
    Joomla.listItemTask = listItemTask;
}
// remove the hard-coded size attribute from joomla's state select list
jQuery(document).ready(function(){
    jQuery('#filter_state').removeAttr('size');
    jQuery('#filter_state').removeClass('inputbox');
    jQuery('#filter_state').addClass('chzn-done');
});

<?php
if(JRequest::getVar('select','') == 'multiple'){
?>
    
    crbc_categoriespicker_list = [];
    
    function crbc_collect_checked(form_id, element_name){
        
        var collected = [];
        var selected  = [];
                
        jQuery("#"+form_id).find("input[name='"+element_name+"']:checked").each(function(){
            selected.push(jQuery(this).val());
        });
        
        for(var i in crbc_categoriespicker_list){
            
            var obj = crbc_categoriespicker_list[i];
            
            if( jQuery.inArray( obj.id, selected ) != -1 ){
                
                collected.push(obj);
            }
        }
        
        parent[<?php echo trim(CrBcHelpers::jsonEncode(JRequest::getVar('picker_callback'))); ?>](collected);
    }
<?php
}
?>
//-->
</script>

<div class="crbc crbc-page">

<h1 class="crbc-categoriespicker-title"><?php echo JText::_('COM_BREEZINGCOMMERCE_PICK_A_CATEGORY'); ?></h1>

<div class="crbc-clearfix clear clearfix"></div>

<div class="crbc-categoriespicker-pages-counter crbc-pull-left pull-left">
    <span class="label"><?php echo $this->pagination->getPagesCounter(); ?></span>
</div>


<form class="crbc-categoriespicker-per-page form-horizontal" action="<?php echo JUri::getInstance()->toString(); ?>" method="post">

    <div class="crbc-pull-right pull-right">

        <div class="control-group">
            <label class="control-label" for="limit"></label>
            <div class="controls">
              <?php echo JText::_('COM_BREEZINGCOMMERCE_SHOW_PER_PAGE'); ?>: <?php echo $this->pagination->getLimitBox(); ?>
            </div>
        </div>
    </div>

</form>

<div class="crbc-clearfix clear clearfix"></div>

<form class="crbc-categoriespicker-form" action="index.php" method="post" name="adminForm" id="adminForm">

    <input type="hidden" name="category_search_by" value="fulltext" />

    <table class="table table-striped">
    <thead>
        <tr>
            <?php
            if(JRequest::getWord('select','') != 'single'){
            ?>
            <th id="crbc-categoriespicker-select-all">
              <input id="crbc-check-all" type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
            </th>
            <?php
            }
            ?>
            <th>
                <span id="crbc-categoriespicker-select-all-button" onclick="jQuery(jQuery('#crbc-check-all').get(0)).trigger('click')"><i class="crbc-fa crbc-fa-long-arrow-left"></i> <?php echo JText::_( 'COM_BREEZINGCOMMERCE_SELECT_ALL' ); ?></span>
                |
                <span id="crbc-categoriespicker-add-to-categories-button" onclick="crbc_collect_checked('adminForm','cid[]'); return false;"><?php echo JText::_('COM_BREEZINGCOMMERCE_ADD_TO_CATEGORIES');?></span>
                
                <div id="crbc-categoriespicker-searchbar" class="btn-wrapper input-append">
                    <label for="category_search" class="element-invisible"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_SEARCH' ); ?></label>
                    <input type="text" name="category_search" id="category_search" value="<?php echo $this->lists['category_search'];?>" onchange="document.adminForm.submit();" placeholder="<?php echo JText::_( 'COM_BREEZINGCOMMERCE_FILTER' ); ?>"/>
                    <button class="btn" data-original-title="<?php echo JText::_( 'COM_BREEZINGCOMMERCE_GO' ); ?>" onclick="this.form.submit();"><i class="icon-search"></i></button>

                    <button type="button" class="btn js-stools-btn-clear" onclick="document.getElementById('category_search').value='';this.form.submit();"><?php echo JText::_( 'COM_BREEZINGCOMMERCE_RESET' ); ?></button>        

                </div>
                
            </th>
        </tr>
    </thead>
    <?php
    $k = 0;
    $n = count( $this->items );
    for ($i=0; $i < $n; $i++)
    {
        $row = $this->items[$i];
        $checked    = JHTML::_( 'grid.id', $i, $row->id );
        
        $link = '';
        
        if( ( JRequest::getWord('select','') == 'single' || JRequest::getWord('select','') == 'multiple' ) && JRequest::getVar('picker_callback','') != ''){
            
            $link = 'javascript:parent['.trim(json_encode(JRequest::getVar('picker_callback'))).']([{"id":'.json_encode($row->id).',"title":'.json_encode($row->title).'}]);void(0);';
        
            if( JRequest::getWord('select','') == 'multiple' ){
                $multiple .= '<script type="text/javascript">crbc_categoriespicker_list.push({"title":'.json_encode($row->title).', "id": '.json_encode($row->id).'})</script>';
                $link = 'javascript:parent['.trim(json_encode(JRequest::getVar('picker_callback'))).']([{"id":'.json_encode($row->id).',"title":'.json_encode($row->title).'}]);void(0);';
            } else {
                $link = 'javascript:parent['.trim(json_encode(JRequest::getVar('picker_callback'))).']('.json_encode($row->id).','.json_encode($row->title).');void(0);';
            }
            
        } else {
        
            $link = JRoute::_( 'index.php?option=com_breezingcommerce&controller=categories&task=edit&cid[]='. $row->id .'&limitstart=' . $this->lists['limitstart'] );
        
        }
        
        ?>
        <tr class="<?php echo "row$k"; ?>">
            
            <?php
            if(JRequest::getWord('select','') != 'single'){
            ?>
            <td style="vertical-align: middle">
              <?php echo $checked; ?>
            </td>
            <?php
            }
            ?>
            <td>
                <?php
                $desc = $row->description;
                $introtext = '';
                $fulltext  = '';
                $desc = str_replace('<br>', '<br />', $desc);

                // Search for the {readmore} tag and split the text up accordingly.
                $pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
                $tagPos = preg_match($pattern, $desc);

                if ($tagPos == 0) {
                    $introtext = $desc;
                } else {
                    list($introtext, $fulltext) = preg_split($pattern, $desc, 2);
                }
                ?>
                
                <h3><?php echo str_repeat('<span class="crbc-fa crbc-fa-chevron-right"></span> ', $row->level-1 < 0 ? 0 : $row->level-1); ?> <?php echo $row->title; ?></h3>

                <div class="crbc-categoriespicker-block">
                    
                    <a class="crbc-categoriespicker-image" href='<?php echo $link; ?>'>
                    <?php require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcImage.php'); ?>
                    <?php echo CrBcImage::getCategoryImageTagByCategoryId($row->id, 'medium'); ?>
                    </a>
                
                </div>
                
                <a href='<?php echo $link; ?>'><?php echo $introtext; ?></a>
            </td>
        </tr>
        <?php
        $k = 1 - $k;
    }
    ?>
        <tfoot>
            <tr>
                
                <td colspan="999">
                    <div class="crbc-text-center text-center">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </div>
                </td>
            </tr>
        </tfoot>

    </table>
<?php 
echo $multiple;
?>
<input type="hidden" name="option" value="com_breezingcommerce" />
<input type="hidden" name="task" value="" />
<?php
if(JRequest::getVar('tmpl','') != '' ){
?>
<input type="hidden" name="tmpl" value="<?php echo $this->escape(JRequest::getVar('tmpl'));?>" />
<?php
}
?>
<input type="hidden" name="picker_callback" value="<?php echo $this->escape(JRequest::getWord('picker_callback',''));?>" />
<input type="hidden" name="select" value="<?php echo $this->escape(JRequest::getWord('select',''));?>" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="controller" value="categoriespicker" />
<input type="hidden" name="filter_order" value="<?php echo $this->escape($this->lists['order']); ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape($this->lists['order_Dir']); ?>" />

</form>

</div>
    


