<?php
/**
 * @package     BreezingCommerce
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<div class="<?php echo !$this->display_category_image? 'crbc-noimage ' : '' ?><?php echo !$this->display_category_text ? 'crbc-notext ' : '' ?>crbc crbc-page crbc-categories-page<?php echo $this->pageclass_sfx != '' ? ' ' . $this->pageclass_sfx : ''; ?>">

        <?php if($this->show_page_heading):?>
        <div class="crbc-categories-title page-header">
            <h1><?php echo $this->escape( $this->page_heading != '' ? $this->page_heading : $this->menu_title );?><?php echo $this->this_category ? ' ' . JText::_('COM_BREEZINGCOMMERCE_IN') . ' ' . $this->escape($this->this_category->title) : ''; ?></h1>
        </div>
        <?php endif; ?>

        <?php 
        if( $this->pagination->pagesTotal > 1 ){
        ?>
        <div class="crbc-pull-left pull-left">
            <span class="label"><?php echo $this->pagination->getPagesCounter(); ?></span>
        </div>
    
        
        <form class="form-horizontal" action="<?php echo JUri::getInstance()->toString(); ?>" method="post">
            
            <div class="crbc-pull-right pull-right">
                
                <div class="control-group">
                    <label class="control-label" for="limit"></label>
                    <div class="controls">
                      <?php echo $this->pagination->getLimitBox(); ?>
                    </div>
                </div>
            </div>
        </form>
        
        <?php
        }
        ?>
        
        <div class="crbc-clearfix clear clearfix"></div>
    
        <?php
        $size = count($this->categories);
        $i = 0;
        foreach($this->categories As $category){
            
            $alias = $category->alias == '' ? CrBcHelpers::getSlug($category->title) : $category->alias;
            $category_url = JRoute::_('index.php?option=com_breezingcommerce&controller=category&category_id='.$category->id.'&Itemid='.JRequest::getInt('Itemid', 0).'&alias='.$alias);
            
            $image_tag = CrBcImage::getCategoryImageTag($category->image_physical_name, 'medium', $category->id, $category->image_alt_name);
            
            $cols = intval($this->col_break);
            if($i == 0){
                echo '<div class="crbc-categories-category crbc-row row-fluid">';
            }
        ?>

            <div class="crbc-categories-category-entry crbc-span<?php echo 12 / $cols; ?> crbc-well span<?php echo 12 / $cols; ?> well well-small">

                <div class="crbc-categories-category-title">
                    <?php if(!$this->link_category_title || !$category->has_products): ?>
                    <h2><?php echo $this->escape( $category->title );?></h2>
                    <?php else: ?>
                    <h2><a title="<?php echo $this->escape(JText::_('COM_BREEZINGCOMMERCE_DETAILS')); ?>" href="<?php echo $category_url; ?>"><?php echo $this->escape( $category->title );?></a></h2>
                    <?php endif;?>
                </div>
                
                <?php if($this->display_category_image): ?>
                <div class="crbc-categories-image-container crbc-polaroid img-polaroid">
                   <?php if(trim($image_tag) != ''){ ?>
                    <?php if($category->has_products): ?><a title="<?php echo $this->escape(JText::_('COM_BREEZINGCOMMERCE_DETAILS')); ?>" href="<?php echo $category_url; ?>"><?php else: ?><a href="javascript:void(0);"><?php endif; ?><?php echo trim($image_tag); ?></a>
                    <?php } else if($category->has_products){ ?>
                    <a title="<?php echo $this->escape(JText::_('COM_BREEZINGCOMMERCE_DETAILS')); ?>" href="<?php echo $category_url; ?>"><i class="crbc-image-placeholder crbc-fa crbc-fa-image"></i></a>
                    <?php } else { ?>
                    <a href="javascript:void(0);"><i class="crbc-image-placeholder crbc-fa crbc-fa-image"></i></a>
                    <?php } ?>
                </div>
                <?php endif; ?>
                
                <?php if($this->display_category_text):?>
                <div>
                    <div class="crbc-category-description">
                        <!-- product description -->
                        <?php echo $category->introtext;?>
                    </div>
                    <div class="crbc-fadeout"></div>
                </div>
                <?php endif; ?>
                
                <div class="cbrc-categories-button-container">
                
                    <?php
                    if( $category->has_products ){
                    ?>
                    <div class="crbc-categories-category-details-button">
                        <a href="<?php echo $category_url; ?>" class="crbc-btn btn btn-primary"><i class="crbc-fa crbc-fa-list"></i> <?php echo JText::_('COM_BREEZINGCOMMERCE_VIEW_CATEGORY_PRODUCTS'); ?></a>
                    </div>
                    <?php
                    }
                    ?>

                    <?php
                    if( $category->has_subcategories ){
                    ?>
                    <div class="crbc-categories-more-button">
                        <a href="<?php echo JRoute::_('index.php?option=com_breezingcommerce&controller=categories&alias='.$alias.'&parent_category_id='.$category->id.'&Itemid='.JRequest::getInt('Itemid', 0)); ?>" class="crbc-btn btn btn-primary"><i class="crbc-fa crbc-fa-eye"></i> <?php echo JText::_('COM_BREEZINGCOMMERCE_MORE_CATEGORIES'); ?></a>
                    </div>
                    <?php
                    }
                    ?>
                    
                </div>
                
            </div>
            <?php
            if($i + 1 < $size && $i % intval($this->col_break) == intval($this->col_break) - 1){
                echo '</div>';
                echo '<div class="crbc-clearfix clearfix"></div>';
                echo '<div class="crbc-categories-category crbc-row row-fluid">';
            } else if( $i + 1 >= $size ) {
            ?>
            </div>
            <?php
            }
            $i++;
        }
        ?>    
</div>

<?php
if( $this->pagination->pagesTotal > 1 ){
?>
<div class="crbc-text-center text-center">
    <form action="<?php echo JUri::getInstance()->toString(); ?>" method="post" name="adminForm" id="adminForm">

        <?php echo $this->pagination->getListFooter(); ?>
    </form>
</div>
<?php
}
?>