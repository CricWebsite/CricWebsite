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
<div class="crbc crbc-page crbc-files-page<?php echo $this->pageclass_sfx != '' ? ' ' . $this->pageclass_sfx : ''; ?>">
    
    <div class="crbc-files-title page-header">
        <h1><?php echo $this->escape( $this->heading );?></h1>
    </div>
    
    <?php 
    if( $this->pagination->pagesTotal > 1 ){
    ?>
    <div class="crbc-pull-left pull-left">
        <span class="label"><?php echo $this->pagination->getPagesCounter(); ?></span>
    </div>
    <?php
    }
    ?>

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

    <div class="crbc-clearfix clearfix"></div>
    
    <?php
    $amount_files = count($this->files);
    $last_order = '';
    $i = 0;
    foreach($this->files As $file){
        
        if($last_order != $file->order_id){
            
            if($i == 1 || $i == 2){
                echo '</div><hr />';
            }
        ?>

            <div class="crbc-file-header crbc-row row-fluid">
                <div class="crbc-span12 span12">
                    <h3><?php echo $this->escape( $file->product_title );?></h3>
                    <h4><?php echo JText::_('COM_BREEZINGCOMMERCE_PAYMENT_DATE') . ': ' . JHTML::_('date',  $file->payment_date,  JText::_('DATE_FORMAT_LC2') );?></h4>
                    <span class="crbc-label crbc-label-info label label-info"><?php echo JText::_('COM_BREEZINGCOMMERCE_ORDER_NUMBER') . ': ' . $file->order_number;?></span>
                </div>
            </div>

        <?php
        
            $last_order = $file->order_id;
            $i = 0;
        } 
        
        if($i == 0){
            echo '<div class="crbc-row row-fluid">';
        }
    ?>
        <div class="crbc-file crbc-well crbc-span4 well span4">

            <div class="crbc-file-title">
                <h2><?php echo $file->name; ?></h2>
            </div>

            <div class="crbc-file-description">
            <?php echo $file->description; ?>
            </div>
            
            <div class="crbc-clearfix clearfix clear"></div>
            
            <?php if( $file->verification_download_tries > 0 && $file->download_tries >= $file->verification_download_tries ): ?>
            <p>
                <i>
                    <?php echo JText::_('COM_BREEZINGCOMMERCE_MAXIMUM_DOWNLOADS_MESSAGE'); ?>
                </i>
            </p>
            <?php endif;?>
            
            <div class="crbc-file-controls">
            
                <?php if( $file->verification_download_tries > 0 && $file->download_tries >= $file->verification_download_tries ): ?>
                    
                <?php else: ?>
                <div class="crbc-file-download-link">
                    <a class="btn" href="<?php echo JRoute::_('index.php?option=com_breezingcommerce&controller=files&itemid=0&task=download&format=raw&order_item_file_id='.$file->order_item_file_id); ?>"><i class="crbc-fa crbc-fa-download"></i> <?php echo JText::_('COM_BREEZINGCOMMERCE_DOWNLOAD'); ?></a>
                </div>
                <?php endif;?>
                
                <div class="crbc-file-size">
                    <div class="crbc-label crbc-label-info label label-info">
                    <?php 
                        $size = number_format($file->filesize/(1024*1024),2) . ' ' . JText::_('COM_BREEZINGCOMMERCE_MEGABYTES');
                        if(!floatval($size)){
                            $size = number_format($file->filesize/1024,2) . ' '.JText::_('COM_BREEZINGCOMMERCE_KILOBYTES');
                        }
                        echo JText::_('COM_BREEZINGCOMMERCE_FILESIZE').': '.$size;
                    ?>
                    </div>
                </div>
                
                <div class="crbc-file-valid">
                    <?php 
                        $initial_date = JFactory::getDate($file->payment_date,'UTC');
                        $start_date = JFactory::getDate('now','UTC');
                        $end_date = JFactory::getDate($file->valid,'UTC');
                        
                        $diff = $start_date->diff($end_date);
                        $diff->format('%R');
                        
                        $initial_diff = $initial_date->diff($end_date);
                        $initial_diff->format('%R');
                        
                        $t1_diff = $initial_date->diff($end_date);
                        $t2_diff = $start_date->diff($end_date);
                        
                        $p = 0;
                        
                        if($t1_diff->days > 0){
                            $p = round($t2_diff->days/$t1_diff->days, 2);
                        }
                        
                        $days = '';
                        $badge = 'success';
                        
                        if($file->forever == 0 && $file->verification_days > 0){
                        
                            if($end_date->toUnix() - $start_date->toUnix() >= 86400){
                                $days = $diff->days;
                            }
                            else if($end_date->toUnix() - $start_date->toUnix() > 0 && $end_date->toUnix() - $start_date->toUnix() < 86400){
                                $days = JText::_('COM_BREEZINGCOMMERCE_LESS_THAN_A_DAY');
                            }
                            else
                            {
                                $days = JText::_('COM_BREEZINGCOMMERCE_FILE_AVAILABLE_NONE');
                            }
                            
                            if($p <= 0.9 && $p > 0.25){
                                $badge = 'warning'; 
                            }
                            else if($p <= 0.25)
                            {
                                $badge = 'error'; 
                            }
                        
                        } else {
                            
                            $days = JText::_('COM_BREEZINGCOMMERCE_UNLIMITED');
                        }
                    ?>
                    <div class="crbc-label crbc-label-<?php echo $badge; ?> label label-<?php echo $badge; ?>">
                    <?php echo JText::_('COM_BREEZINGCOMMERCE_DAYS_LEFT') . ': ' . $days; ?>
                    </div>
                </div>
                
            </div>

        </div>
        <?php
        $last_closed = false;
        if($i == 2){
            $last_closed = true;
        ?>
            </div>
        <?php
        }
        $i++;
        if($i > 2){
            $i = 0;
        }
    }
    
    if(!$last_closed){
        echo '</div>';
    }
    ?>
    
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

    <div class="crbc-clearfix clearfix"></div>
    
</div>