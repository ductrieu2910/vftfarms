#foreach($saleInfo in $field.Find("sale.info").GetLines())
	#set( $guest_number = $tool.GetRowDoubleValue($saleInfo.SaleRow, "guest_number"))
	#set( $table_name = $tool.GetRowStringValue($saleInfo.SaleRow, "table_name"))
	#set( $sale_type_no = $tool.GetRowStringValue($saleInfo.SaleRow, "sale_type_no"))
	#set($parent_id = $tool.GetRowStringValue($saleInfo.SaleRow, "parent_id"))
	#set($table_number = $tool.GetRowIntValue($saleInfo.SaleRow, "table_number"))
<ticket>
<line>
    <text align="center" font-style="bold" font-size="20" color ="#0000FF">CANCEL - $orderInfo.location_name</text>
</line>
<line>
    <text> </text>
</line>
<line>
    <text align ="left" width="20%">No.:</text>
	<text align ="left" font-style="bold" width="80%">$orderInfo.receipt_no_order</text>
</line>
<line>
 	<text align ="left" width="20%">Table:</text>
	<text align ="left" font-size="18" width="80%">$tool.GetRowStringValue($saleInfo.SaleRow, "table_name") #if($sale_type_no != "" ) $sale_type_no #end
	#if($table_number>0)
	#set($stable_number = $tool.GetTableNumber($parent_id))
	#if($stable_number != "")
	.$stable_number
	#end
	.$table_number
	#end
	</text>
</line>
<line>
    <text align ="left" width="100%">Order :$orderInfo.order_user_name</text>
</line>
<line>
    <text align ="left" width="100%">Cancel: $orderInfo.cancel_user_name</text>
</line>
<line>
    <text align ="left" width="100%">Date: $userInfo.Format.DATE.formatValue($orderInfo.print_order_date) $orderInfo.print_order_date.ToString("HH:mm")</text>
</line>
<line>
   <text hr = " - "></text>
</line>
	 #foreach($item in $saleInfo.PrintDeletes().Rows)
		 #set( $quantity = $tool.GetRowDoubleValue($item, "quantity"))
		 #set( $sale_detail_id = $tool.GetRowStringValue($item, "sale_detail_id"));
		 #set( $reason_cancel = $tool.GetRowStringValue($item, "reason_cancel"))
		 #set( $product_name = $tool.GetRowStringValue($item, "product_name"))
		 #set( $product_name_other = $tool.GetRowStringValue($item, "product_name_other"))
		 #set( $description = $tool.GetRowStringValue($item, "description"))
		<line>
			<text align ="left"  width="15%" font-style="bold" font-size="12">$userInfo.Format.DOUBLE.formatValue($quantity).</text>
			<text align ="left"  width="85%" font-style="bold" font-size="12">$tool.Escape($product_name) $tool.Escape($product_name_other) #if($description != "") $description #end</text>	  
		</line>
		#if($reason_cancel != "")
		<line>
			<text align ="left"  width="15%"></text>
			<text align ="left"  width="85%" font-style="italic">$tool.Escape($reason_cancel)</text>	  
		</line>
		#end
	#end

</ticket>
#end