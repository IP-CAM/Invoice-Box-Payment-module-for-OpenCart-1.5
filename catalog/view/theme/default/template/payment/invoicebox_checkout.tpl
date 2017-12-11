<?php if ($testmode) { ?>
  <div class="warning"><img src="catalog/view/theme/default/image/close.png" alt="" class="close"><?php echo $text_testmode; ?></div>
<?php } ?>
<form action="<?php echo $action; ?>" method="post">

 <input type="hidden" name="itransfer_participant_id" value="<?php echo $invoicebox_participant_id; ?>" />
   <input type="hidden" name="itransfer_participant_ident" value="<?php echo $invoicebox_participant_ident; ?>" />
   <input type="hidden" name="itransfer_participant_sign" value="<?php echo $invoicebox_sign; ?>" />
   <input type="hidden" name="itransfer_order_id" value="<?php echo $order_id; ?>" />
   <input type="hidden" name="itransfer_order_amount" value="<?php echo $total; ?>" />
   <input type="hidden" name="itransfer_order_quantity" value="<?php echo $quantity; ?>" />
   <input type="hidden" name="itransfer_testmode" value="<?php echo $testmode; ?>" />
   <input type="hidden" name="itransfer_order_currency_ident" value="<?php echo $currency_code; ?>" />
   <input type="hidden" name="itransfer_order_description" value="<?php echo $invoice; ?>" />
   <input type="hidden" name="itransfer_person_name" value="<?php echo $first_name.' '.$last_name; ?>" />
   <input type="hidden" name="itransfer_person_email" value="<?php echo $email; ?>" />
   <input type="hidden" name="itransfer_person_phone" value="<?php echo $phone; ?>" />
   <input type="hidden" name="itransfer_body_type" value="PRIVATE" />
   <input type="hidden" name="itransfer_url_return" value="<?php echo $return; ?>" />
   <input type="hidden" name="itransfer_url_returnsuccess" value="<?php echo $returnsuccess; ?>" />
   <input type="hidden" name="itransfer_url_notify" value="<?php echo $notify_url; ?>" />
   <input name="itransfer_cms_name" value="Opencart15" type="hidden">
   <?php 
   $i=0;
   foreach ($products as $product) { 
   $i++;?>
   <input type="hidden" name="itransfer_item<?php echo $i; ?>_name" value="<?php echo $product['name']; ?>" />
   <input type="hidden" name="itransfer_item<?php echo $i; ?>_quantity" value="<?php echo $product['quantity']; ?>" />
   <input type="hidden" name="itransfer_item<?php echo $i; ?>_price" value="<?php echo $product['price']; ?>" />
   <input type="hidden" name="itransfer_item<?php echo $i; ?>_vatrate" value="<?php echo $product['vatrate']; ?>" />
   <input type="hidden" name="itransfer_item<?php echo $i; ?>_measure" value="шт." />
   <?php } ?>
   <div class="buttons">
    <div class="pull-right">
      <input type="submit" value="<?php echo $button_confirm; ?>" class="btn btn-primary" />
    </div>
  </div>
</form>
   
   
  
