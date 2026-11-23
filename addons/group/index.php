<?php
require_once(ABSPATH . 'api/Product.php');
$dt = $product->productGroupApp();
$productList = $product->productByGroupById($appSession, $selected_id, -1);

// Phân trang: 12 sản phẩm / trang
require_once(ABSPATH . 'app/Pagination.php');
list($page_number, $total_pages) = paginateTable($productList, $page_number);
?>
<!-- page-header-section start -->
<div class="page-header-section">
    <div class="container">
        <div class="row">
            <div class="col-12 d-flex justify-content-between justify-content-md-end">
                <ul class="breadcrumb">
                    <li><a href="<?php echo URL; ?>"><?php echo $appSession->getLang()->find("Home");?> </a></li>
                 
          
					<?php
					if($selected_id != "")
					{
						$category_name = "";
						$category_code = "";
						
						for($i =0; $i<$dt->getRowCount(); $i++)
						{
						 if($dt->getString($i, "id") == $selected_id)
						  {
							   $category_code = $dt->getString($i, "code");
								$category_name = $dt->getString($i, "name_lg");
								if ($category_name == "") {
									$category_name = $dt->getString($i, "name");
								}
						  }
						}
					?>
					 <li><span>/</span></li>
                    <li><a href="<?php echo URL; ?><?php echo $appSession->getTool()->validUrl($category_name); ?>/g-<?php echo $category_code; ?>"><?php echo $category_name;?></a></li>
					<?php
					}
					?>
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- page-header-section end -->

<!-- page-content -->
<section class="page-content section-ptb-90">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 sidebar">
                <div class="theiaStickySidebar">
                    <div class="widget-callapse" id="filtermain">
                        <div class="widget">
                            <h4 class="widget-title d-none d-lg-block"><?php echo $appSession->getLang()->find("Category product");?></h4>
                            <a class="widget-title d-lg-none" data-toggle="collapse" href="#scatagory-widget01"
                                role="button" aria-expanded="false" aria-controls="scatagory-widget01"><?php echo $appSession->getLang()->find("Category product");?><i class="fas fa-angle-down"></i></a>

                            <div class="widget-wrapper" id="scatagory-widget01">
                                <ul class="catagory-menu collapse show" id="catagory-main">
                                     
								<?php
								
								for($c =0; $c<$dt->getRowCount(); $c++)
								{

									$code = $dt->getString($c, "code");
									$name = $dt->getString($c, "name_lg");
									if($name == "")
									{
										$name = $dt->getString($c, "name");
									}
									  
								  ?>
								<li><a
									href="<?php echo URL; ?><?php echo $appSession->getTool()->validUrl($name); ?>/g-<?php echo $code; ?>"><?php echo $name; ?></a>
								</li>
								<?php
								}        
									   
								?>


							</ul>
                                        
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-9">
                <div class="row product-list">
					<?php
					if($productList->getRowCount() == 0){
						echo "<h2>".$appSession->getLang()->find("Product is empty")."</h2>";
					}
					?>
                    <?php
                    for ($i = 0; $i < $productList->getRowCount(); $i++) {

                       $product_id = $productList->getString($i, "id");
									$code = $productList->getString($i, "code");
									$document_id = $productList->getString($i, "price_document_id");
									if($document_id == "")
									{
										$document_id = $productList->getString($i, "document_id");
									}
									$name = $productList->getString($i, "name_lg");

									if ($name == "") {
										$name = $productList->getString($i, "name");
									}
									$unit_price = $productList->getFloat($i, "unit_price");
									$old_price = $productList->getFloat($i, "old_price");
									$unit_id = $productList->getFloat($i, "unit_id");
									$currency_id = $productList->getFloat($i, "currency_id");
									$unit_name = $productList->getString($i, "unit_name");
									$price_id = $productList->getString($i, "price_id");
									if($price_id == "")
									{
										$price_id = $product_id;
									}
									$attribute_category_name = $productList->getString($i, "attribute_category_name");
									$attribute_id = $productList->getString($i, "attribute_id");
									$attribute_name = $productList->getString($i, "attribute_name");
									$attribute_code = $productList->getString($i, "attribute_code");
									$unit_in_stock = $productList->getFloat($i, "unit_in_stock");
									$second_unit_id = $productList->getFloat($i, "second_unit_id");
									$factor = $productList->getString($i, "factor");
									$company_id = $productList->getString($i, "company_id");
									$commercial_name = $productList->getString($i, "commercial_name");		
									if($commercial_name == "")
									{
										$commercial_name = $productList->getString($i, "company_name");
									}
									if($factor == "" || $factor == "0")
									{
										$factor = "1";
									}
									$product_type_name = $productList->getString($i, "type_name");		
									$product_price_id = $price_id;
						
									$price_id_cust = $productList->getString($i, "price_id_cust");
									if($price_id_cust != ""){
										$price_id = $price_id_cust;
										$unit_id = $productList->getString($i, "unit_id_cust");
										$currnecy_id = $productList->getString($i, "currency_id_cust");
										$unit_price = $productList->getString($i, "unit_price_cust");
									}
									
									$price_id_cat = $productList->getString($i, "price_id_cat");
									if($price_id_cat != ""){
										$price_id = $price_id_cat;
										$currnecy_id = $productList->getString($i, "currency_id_cat");
										$unit_id = $productList->getString($i, "unit_id_cat");
										$unit_price = $productList->getString($i, "unit_price_cat");
									}
                    ?>

                    <div class="col-sm-6 col-xl-4">
                        <?php vft_product_card($appSession, $dt_category, [
                            "product_id" => $product_id,
                            "price_id" => $product_price_id,
                            "code" => $code,
                            "name" => $name,
                            "document_id" => $document_id,
                            "unit_price" => $unit_price,
                            "old_price" => $old_price,
                            "currency_id" => $currency_id,
                            "unit_id" => $unit_id,
                            "unit_name" => $unit_name,
                            "attribute_id" => $attribute_id,
                            "attribute_name" => $attribute_name,
                            "attribute_category_name" => $attribute_category_name,
                            "type_id" => $productList->getString($i, "type_id"),
                            "type_name" => $product_type_name,
                            "sticker" => $productList->getString($i, "sticker"),
                            "unit_in_stock" => $unit_in_stock,
                            "second_unit_id" => $second_unit_id,
                            "factor" => $factor,
                            "company_id" => $company_id,
                            "category_id" => $productList->getString($i, "category_id"),
                            "cart_price_id" => $price_id,
                            "wish_id" => $product_price_id,
                        ]); ?>
                    </div>
                    <?php
                    }
                    ?>

                    <div class="col-12 text-center mt-4">
                        <?php renderPagination($page_number, $total_pages); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- page-content -->