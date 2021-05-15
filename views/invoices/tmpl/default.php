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
<div class="crbc crbc-page crbc-invoices-page<?php echo $this->pageclass_sfx != '' ? ' ' . $this->pageclass_sfx : ''; ?>">
    
    <div class="crbc-invoices-title page-header">
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
    $amount_invoices = count($this->invoices);
    $last_order = '';
    $i = 0;
    foreach($this->invoices As $invoice){
        
        // check if an invoice has been created.
        // if not, provide a generation url first,
        // then replace it with a download link
        
        $invoice_folder = JPATH_SITE . DS . 'media' . DS . 'breezingcommerce' . DS . 'invoice';
        
        $pdfname = $invoice_folder.DS.'invoices'.DS.$invoice->invoice_name.'.pdf';
        
        // regular download link
        
        $the_link = JRoute::_('index.php?option=com_breezingcommerce&controller=invoices&task=download&Itemid=0&format=raw&order_id='.$invoice->id);
        $the_label = JText::_('COM_BREEZINGCOMMERCE_DOWNLOAD');
        $the_class = 'crbc-fa-download';
        
        // in case of non-existing invoice, replace by offering to generate it first
        
        if( !$invoice->invoice_created || $invoice->invoice_number == '' 
                || 
                ( $invoice->invoice_created && !JFile::exists($pdfname) )
                || 
                ( $invoice->invoice_number != '' && !JFile::exists($pdfname) ) ){
            
           
            $the_link = 'javascript:crbc_invoices.generate(\''.JRoute::_('index.php?option=com_breezingcommerce&controller=invoices&task=generate_invoice&format=json&Itemid=0&order_id='.$invoice->id, false).'\', \''.$the_link.'\',\'crbc-order-id-'.$invoice->id.'\');void(0);';
            $the_label = JText::_('COM_BREEZINGCOMMERCE_GENERATE');
            $the_class = 'crbc-fa-gears';
        }
        
        if($i == 0){
            echo '<div class="crbc-row row-fluid">';
        }
    ?>
        <div class="crbc-invoice crbc-well crbc-span4 well span4">

            <div class="crbc-invoice-controls">
            
                <h4><?php echo JText::_('COM_BREEZINGCOMMERCE_INVOICE_FOR_ORDER_NUMBER') . ':<br /> ' . $invoice->order_number;?></h4>
                <?php
                if( $invoice->paid ){
                ?>
                <h4><?php echo JText::_('COM_BREEZINGCOMMERCE_PAYMENT_DATE') . ':<br /> ' . JHTML::_('date',  $invoice->payment_date,  JText::_('DATE_FORMAT_LC2') );?></h4>
                <?php
                }
                else if(!$invoice->paid && $invoice->checked_out && $invoice->checkout_date != '0000-00-00 00:00:00'){
                ?>
                <h4><?php echo JText::_('COM_BREEZINGCOMMERCE_CHECKOUT_DATE') . ':<br /> ' . JHTML::_('date',  $invoice->checkout_date,  JText::_('DATE_FORMAT_LC2') );?></h4>
                <?php
                }
                if($invoice->paid){
                ?>
                <div class="crbc-badge crbc-badge-info badge badge-success">
                    <?php echo JText::_('COM_BREEZINGCOMMERCE_PAYMENT_STATUS_PAID'); ?>
                </div>
                <?php
                }else{
                ?>
                <div class="crbc-badge crbc-badge-warning badge badge-warning">
                    <?php echo JText::_('COM_BREEZINGCOMMERCE_PAYMENT_STATUS_UNPAID'); ?>
                </div>
                <?php
                }
                ?>
                <div class="crbc-invoice-download-link">
                    <a class="btn" id="crbc-order-id-<?php echo $invoice->id; ?>" href="<?php echo $the_link; ?>"><i class="crbc-fa <?php echo $the_class; ?>"></i> <span id="crbc-label-crbc-order-id-<?php echo $invoice->id; ?>"><?php echo $the_label; ?></span></a>
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