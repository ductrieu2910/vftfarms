 <?php
    $product = new Product($appSession);
    $dt_category_home = $product->categoryList();

    $dt_product_group = $product->productGroup();
    $hasHome = false;


    for ($i = 0; $i < $dt_product_group->getRowCount(); $i++) {
        if ($dt_product_group->getString($i, "group_category_name") == "HOME") {
            $hasHome = true;
            break;
        }
    }
    ?>

 <?php
    if ($hasHome == true) {
    ?>
 <!-- banner-section start -->
 <section class="slider-banner">
     <div class="banner-slider-container">
         <div class="swiper-wrapper">
             <?php
                    for ($i = 0; $i < $dt_product_group->getRowCount(); $i++) {
                        if ($dt_product_group->getString($i, "group_category_name") == "HOME") {
                            $id = $dt_product_group->getString($i, "id");
                            $code = $dt_product_group->getString($i, "code");
                            $name = $dt_product_group->getString($i, "name_lg");
                            $document_id = $dt_product_group->getString($i, "document_id");
                            if ($name == "") {
                                $name = $dt_product_group->getString($i, "name");
                            }

                    ?>
             <div class="swiper-slide">
                 <div class="banner-content-area">
                     <div class="slider-image slider-image2"
                         style="background-image: url(<?php echo IMAGE_URL; ?>document/?id=<?php echo $document_id; ?>&h=540);">
                     </div>
                     <div class="container">
                         <div class="banner-content text-center text-lg-left">
                             <h6></h6>
                             <h2><?php echo $name ?></h2>
                             <a href="<?php echo URL; ?><?php echo  $appSession->getTool()->validUrl($name); ?>/g-<?php echo $code; ?>"
                                 class="banner-btn"><?php echo $appSession->getLang()->find("View");?></a>
                         </div>
                     </div>
                 </div>
             </div>
             <?php
                        }
                    }
                    ?>

         </div>
         <div class="swiper-pagination"></div>
     </div>
 </section>
 <!-- banner-section end -->
 <?php
    }
    ?>
 <section class="category-section">
     <div class="category-container2">
         <div class="swiper-wrapper">
             <?php
                for ($i = 0; $i < $dt_category_home->getRowCount(); $i++) {
                    $parent_id = $dt_category_home->getString($i, "parent_id");


                    if ($parent_id == "880f58b6-9840-4e2b-9e50-b0244f4f210b") {

                        $document_id = $dt_category_home->getString($i, "document_id");

                        $category_code = $dt_category_home->getString($i, "code");
                        $category_name = $dt_category_home->getString($i, "name_lg");
                        if ($category_name == "") {
                            $category_name = $dt_category_home->getString($i, "name");
                        }

                ?>
             <div class="swiper-slide">
                 <a href="<?php echo URL; ?><?php echo $appSession->getTool()->validUrl($category_name); ?>/c-<?php echo $category_code; ?>"
                     class="category-item">
                     <img src="<?php echo IMAGE_URL; ?>document/?id=<?php echo $document_id; ?>&w=120&h=94" alt="<?php echo $category_name ?>">
                     <p><?php echo $category_name ?></p>
                 </a>
             </div>
             <?php
                    }
                }
                ?>
         </div>
     </div>
 </section>
 <?php
	
	$dt = $product->productGroupApp();
	for($c =0; $c<$dt->getRowCount(); $c++)
	{

		$group_code = $dt->getString($c, "code");
		$group_id  = $dt->getString($c, "id");
		$group_name = $dt->getString($c, "name_lg");
		if($group_name == "")
		{
			$group_name = $dt->getString($c, "name");
		}
		
    $productList = $product->productByGroupById($appSession, $group_id, 10);
    if ($productList->getRowCount() > 0) {
    ?>
 <!-- trending product-section start -->
 <section class="trending-product-section pt-2">
     <div class="container">
         <div class="section-heading">
             <h4 class="heading-title"><span class="heading-circle"></span><?php echo $group_name;?></h4>
         </div>

         <div class="section-wrapper">
             <!-- Add Arrows -->
             <div class="slider-btn-group">
                 <div class="slider-btn-prev trending-slider-prev">
                     <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                         x="0px" y="0px" viewBox="0 0 443.52 443.52" style="enable-background:new 0 0 443.52 443.52;"
                         xml:space="preserve">
                         <g>
                             <path d="M143.492,221.863L336.226,29.129c6.663-6.664,6.663-17.468,0-24.132c-6.665-6.662-17.468-6.662-24.132,0l-204.8,204.8
                        c-6.662,6.664-6.662,17.468,0,24.132l204.8,204.8c6.78,6.548,17.584,6.36,24.132-0.42c6.387-6.614,6.387-17.099,0-23.712
                        L143.492,221.863z" />
                         </g>
                     </svg>
                 </div>
                 <div class="slider-btn-next trending-slider-next">
                     <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                         x="0px" y="0px" viewBox="0 0 512.002 512.002"
                         style="enable-background:new 0 0 512.002 512.002;" xml:space="preserve">
                         <g>
                             <path d="M388.425,241.951L151.609,5.79c-7.759-7.733-20.321-7.72-28.067,0.04c-7.74,7.759-7.72,20.328,0.04,28.067l222.72,222.105
                        L123.574,478.106c-7.759,7.74-7.779,20.301-0.04,28.061c3.883,3.89,8.97,5.835,14.057,5.835c5.074,0,10.141-1.932,14.017-5.795
                        l236.817-236.155c3.737-3.718,5.834-8.778,5.834-14.05S392.156,245.676,388.425,241.951z" />
                         </g>
                     </svg>
                 </div>
             </div>
             <div class="mlr-20">
                 <div class="trending-product-container">
                     <div class="swiper-wrapper">
                         <?php
                                for ($i = 0; $i < $productList->getRowCount(); $i++) {
                                    $product_id = $productList->getString($i, "id");
									$price_id = $productList->getString($i, "price_id");
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
									$unit_id = $productList->getString($i, "unit_id");
									$currency_id = $productList->getFloat($i, "currency_id");
									$unit_name = $productList->getString($i, "unit_name");
									$price_id = $productList->getString($i, "price_id");
									if($price_id == "")
									{
										$price_id = $product_id;
									}
									$attribute_category_name = $productList->getString($i, "attribute_category_name");
									$attribute_id = $productList->getString($i, "attribute_id");
									$type_id = $productList->getString($i, "type_id");
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
									$sticker = $productList->getString($i, "sticker");
						
                                ?>
                         <div class="col-sm-6 col-xl-3">
                        <?php vft_product_card($appSession, $dt_category, [
                            "product_id" => $product_id,
                            "price_id" => $price_id,
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
                            "type_id" => $type_id,
                            "type_name" => $product_type_name,
                            "sticker" => $sticker,
                            "unit_in_stock" => $unit_in_stock,
                            "second_unit_id" => $second_unit_id,
                            "factor" => $factor,
                            "company_id" => $company_id,
                            "category_id" => $productList->getString($i, "category_id"),
                            "wish_id" => $price_id,
                        ]); ?>
                    </div>

                         <?php
                                }
                                ?>
                     </div>
                 </div>
                 <div class="text-center pt-3">
                     <a href="<?php echo URL; ?><?php echo $appSession->getTool()->validUrl($name);?>/g-<?php echo $group_code;?>" class="more-product-btn"><?php echo $appSession->getLang()->find("More");?></a>
                 </div>
             </div>
         </div>
     </div>
 </section>
 <!-- trending product-section end -->
 <?php
   }
}
 ?>
 
