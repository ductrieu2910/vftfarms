<?php
require_once(ABSPATH . 'api/Product.php');
include( ABSPATH .'app/lang/'.$appSession->getConfig()->getProperty("lang_id").'.php');
foreach($langs as $key => $item)
{
	$appSession->getLang()->setProperty($key, $item);				
}
$msg = $appSession->getTier()->createMessage();

$product = new Product($appSession);

$price_id = "";
if(isset($_REQUEST['id']))
{
	$price_id = $_REQUEST['id'];
}

$sql = "SELECT d1.id, d1.category_id, d1.code, d1.name, lg.description AS name_lg, d2.document_id, d3.unit_price, d3.old_price, d4.name AS unit_name, d5.code AS currency_code, d3.unit_id, d3.currency_id, d3.attribute_id, d7.name AS attribute_name, d8.name attribute_category_name, (SELECT SUM(product_count.quantity) FROM product_count WHERE product_count.status =0 AND product_count.rel_id = d3.id) AS unit_in_stock, d7.quantity AS factor, d7.unit_id AS second_unit_id";
$sql = $sql . " FROM product d1";
$sql = $sql . " LEFT OUTER JOIN poster d2 ON(d1.id = d2.rel_id AND d2.publish=1)";
$sql = $sql . " LEFT OUTER JOIN res_lang_line lg ON(lg.lang_id='" . $appSession->getConfig()->getProperty("lang_id") . "' AND lg.rel_id = d1.id AND lg.name='product_name' AND lg.status =0) LEFT OUTER JOIN product_price d3 ON(d1.id = d3.product_id AND d3.publish=1) LEFT OUTER JOIN product_unit d4 ON(d3.unit_id = d4.id) LEFT OUTER JOIN res_currency d5 ON(d3.currency_id = d5.id)";
$sql = $sql." LEFT OUTER JOIN attribute d7 ON(d3.attribute_id = d7.id)";
$sql = $sql." LEFT OUTER JOIN attribute_category d8 ON(d7.category_id = d8.id)";
$sql = $sql . " WHERE (d3.id='" . $price_id . "' OR d1.id='".$price_id."')";



$msg->add("query", $sql);
$productList = $appSession->getTier()->getTable($msg);
$product_id = "";
if($productList->getRowCount()>0)
{
	$product_id = $productList->getString(0, "id");
}

$sql = "SELECT d1.document_id, d2.name FROM document_rel d1 LEFT OUTER JOIN document d2 ON(d1.document_id = d2.id) WHERE d1.status =0 AND d1.publish=1 AND d1.rel_id='" . $product_id . "'";
$sql = $sql . " ORDER BY d1.create_date ASC";

$msg->add("query", $sql);
$photos = $appSession->getTier()->getArray($msg);

if ($productList->getRowCount() > 0) {
    $product_product_id = $productList->getString(0, "id");
    $product_code = $productList->getString(0, "code");
    $product_product_name = $productList->getString(0, "name_lg");

    if ($product_product_name == "") {
        $product_product_name = $productList->getString(0, "name");
    }
    $product_unit_price = $productList->getFloat(0, "unit_price");
	if($product_unit_price == "")
	{
		$product_unit_price = 0;
	}
    $product_old_price = $productList->getFloat(0, "old_price");
    $product_unit_id = $productList->getFloat(0, "unit_id");
    $product_unit_name = $productList->getString(0, "unit_name");
    $product_currency_id = $productList->getFloat(0, "currency_id");
	$product_attribute_category_name = $productList->getString(0, "attribute_category_name");
	$product_attribute_id = $productList->getString(0, "attribute_id");
	$product_attribute_name = $productList->getString(0, "attribute_name");
	$product_attribute_code = $productList->getString(0, "attribute_code");
	$product_unit_in_stock = $productList->getFloat(0, "unit_in_stock");	
	$product_document_id = $productList->getString(0, "document_id");
	$product_second_unit_id = $productList->getString(0, "second_unit_id");
	$product_factor = $productList->getString(0, "factor");	
	$product_company_id = $productList->getString(0, "company_id");
	if($product_factor == "" || $product_factor == "0")
	{
		$product_factor = "1";
	}
							

?>
<!-- product-zoom-info section start -->
<section class="product-zoom-info-section section-ptb">
    <div class="container">
        <div class="product-zoom-info-container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="product-zoom-area">
                        <?php
                            if ($product_old_price != 0) {
                                $p = -100;
                                if ($product_unit_price != 0) {
                                    $p = $product_old_price - $product_unit_price;
                                    $p = ($p / $product_old_price) * 100;
                                }
                            ?>
                        <span class="batch"><?php echo $p; ?>%</span>
                        <?php
                            }
                            ?>

                        <div class="product-slick">
                            <?php
							if(count($photos)>0)
							{
                                for ($i = 0; $i < count($photos); $i++) {
                                    $document_id = $photos[$i][0];
                                    $name = $photos[$i][1];
                                ?>
                            <div><img src="<?php echo IMAGE_URL; ?>document/?id=<?php echo $document_id; ?>"
                                    alt="<?php echo $name; ?>"
                                    class="img-fluid blur-up lazyload image_zoom_cls-<?php echo $i ?>"></div>
                            <?php
                                }
							}else{
								if($product_document_id != "")
								{
									
								?>
								 <div><img src="<?php echo IMAGE_URL; ?>document/?id=<?php echo $product_document_id; ?>"
                                    alt="<?php echo $product_product_name; ?>"
                                    class="img-fluid blur-up lazyload image_zoom_cls-0"></div>
								<?php
								}
							}
                                ?>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="slider-nav">
                                    <?php
                                        for ($i = 0; $i < count($photos); $i++) {
                                            $document_id = $photos[$i][0];
                                            $name = $photos[$i][1];
                                        ?>
                                    <div><img src="<?php echo IMAGE_URL; ?>document/?id=<?php echo $document_id; ?>"
                                            alt="<?php echo $product_product_name; ?>" class="img-fluid blur-up lazyload"></div>
                                    <?php
                                        }
                                        ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="product-details-content">
                        <a class="wish-link"
                            href="<?php echo URL; ?><?php echo  $appSession->getTool()->validUrl($product_product_name); ?>/p-<?php echo $code; ?>">
                            <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="heart"
                                class="svg-inline--fa fa-heart fa-w-16" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 512 512">
                                <path
                                    d="M462.3 62.6C407.5 15.9 326 24.3 275.7 76.2L256 96.5l-19.7-20.3C186.1 24.3 104.5 15.9 49.7 62.6c-62.8 53.6-66.1 149.8-9.9 207.9l193.5 199.8c12.5 12.9 32.8 12.9 45.3 0l193.5-199.8c56.3-58.1 53-154.3-9.8-207.9z">
                                </path>
                            </svg>
                        </a>
                        <h2><?php echo $product_product_name; ?></h2>

						<?php
							 if($product_attribute_category_name != "")
							 {
							?>
							 <p class="quantity"><?php echo $product_attribute_category_name;?> : <?php echo $product_attribute_name;?></p>
							 <?php
							 }
							 ?>
							 
						 <p class="quantity"><?php echo $appSession->getLang()->find("Stock");?> : <?php if ($product_unit_in_stock == 0) {
							echo $appSession->getLang()->find("Out of stock");
						} else {
							echo $product_unit_in_stock;
						}  ?></p>
                        
                        <h3 class="price">
						<?php if ($product_old_price != 0) { ?>
						<del><?php echo $product_old_price; ?></del>
						
						<?php
						}
						?>
						 <?php echo $appSession->getCurrency()->format($product_currency_id, $product_unit_price); ?>/ <?php echo $product_unit_name ?>
						</h3>
                      
                        <div class="price-increase-decrese-group d-flex">
                            <span class="decrease-btn">
                                <button type="button" class="btn quantity-left-minus" data-type="minus" data-field="">-
                                </button>
                            </span>
                            <input type="text" class="form-controls input-number" value="1" id="qty" name="qty"
                                minlength="0" maxlength="100">
                            <span class="increase">
                                <button type="button" class="btn quantity-right-plus" data-type="plus" data-field="">+
                                </button>
                            </span>
                        </div>
                        <textarea type="text" class="form-controls" id="editdescription" name="editdescription" placeholder="Ghi chú"
                            style="width: 100%;"></textarea>
                        <div class="d-flex justify-content-end">
							<?php if($product_unit_in_stock>0)
										{
										?>
							 <a href="javascript:addingProduct('card')" class="buy-now"><?php echo $appSession->getLang()->find("Add to card");?></a>&nbsp;&nbsp;
                            <a href="javascript:addingProduct('checkout')" class="check-out"><?php echo $appSession->getLang()->find("Buy Now");?></a>
							<?php
							}
							?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- product-zoom-info section end -->

<?php

    $sql = "SELECT d1.id, d1.name, description, d1.content, d1.category_id, d2.name FROM post d1 LEFT OUTER JOIN post_category d2 ON(d1.category_id = d2.id) WHERE d1.rel_id='" . $product_id . "' AND d1.status =0 AND d1.publish = 1";
    $sql = $sql . " ORDER BY d2.sequence ASC";
    $msg->add("query", $sql);

    $posts = $appSession->getTier()->getArray($msg);
    $post_categories = $appSession->getTool()->selectDistinct($posts, [4, 5]);

    ?>
<section class="description-review-area section-ptb">
    <div class="container">
        <div class="description-review-wrapper">
            <div class="description-review-topbar nav">
                <?php
                    for ($i = 0; $i < count($post_categories); $i++) {
                        $category_id = $post_categories[$i][0];
                        $category_name = $post_categories[$i][1];
                    ?>
                <a <?php if ($i == 0) { ?>class="active" <?php } ?> data-toggle="tab"
                    href="#<?php echo $category_id; ?>"><?php echo $category_name; ?></a>

                <?php
                    }
                    ?>
            </div>
            <div class="tab-content description-review-bottom">
                <?php
                    for ($i = 0; $i < count($post_categories); $i++) {
                        $category_id = $post_categories[$i][0];

                    ?>
                <div id="<?php echo $category_id; ?>" class="tab-pane <?php if ($i == 0) { ?> active<?php } ?>">
                    <div class="product-description-wrapper">
                        <?php
                                for ($j = 0; $j < count($posts); $j++) {
                                    if ($posts[$j][4] == $category_id) {
                                        $post_name = $posts[$j][1];
                                        $post_content = $posts[$j][3];
                                ?>
                        <h1><?php echo $post_name; ?></h1>
                        <p><?php echo $post_content; ?></p>
                        <?php
                                    }
                                }
                                ?>
                    </div>
                </div>
                <?php
                    }
                    ?>
            </div>
        </div>
    </div>
</section>



<?php
    $productList = $product->productByGroup("RECOMMENDATION");
    if ($productList->getRowCount() > 0) {
    ?>
<!-- trending product-section start -->
<section class="trending-product-section">
    <div class="container">
        <div class="section-heading">
            <h4 class="heading-title"><span class="heading-circle"></span> Sản phẩm gợi ý </h4>
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
                        x="0px" y="0px" viewBox="0 0 512.002 512.002" style="enable-background:new 0 0 512.002 512.002;"
                        xml:space="preserve">
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

                                    $id = $productList->getString($i, "id");
                                    $code = $productList->getString($i, "code");
                                    $document_id = $productList->getString($i, "document_id");
                                    $name = $productList->getString($i, "name_lg");
                                    $unit_price = $productList->getFloat($i, "unit_price");
                                    $unit_id = $productList->getFloat($i, "unit_id");
									$unit_name = $productList->getFloat($i, "unit_name");
                                    $currency_id = $productList->getFloat($i, "currency_id");
                                    $old_price = $productList->getFloat($i, "old_price");
                                    if ($name == "") {
                                        $name = $productList->getString($i, "name");
                                    }
									$attribute_category_name = $productList->getString($i, "attribute_category_name");
									$attribute_id = $productList->getString($i, "attribute_id");
									$attribute_name = $productList->getString($i, "attribute_name");
									$attribute_code = $productList->getString($i, "attribute_code");
									$unit_in_stock = $productList->getFloat($i, "unit_in_stock");
									$second_unit_id = $productList->getString($i, "second_unit_id");
									$company_id = $productList->getString($i, "company_id");
									$commercial_name = $productList->getString($i, "commercial_name");
									if($commercial_name == "")
									{
										$commercial_name = $productList->getString($i, "company_name");
									}
									$factor = $productList->getString($i, "factor");	
									if($factor == "" || $factor == "0")
									{
										$factor = "1";
									}
									

                                ?>
                        <div class="swiper-slide">
                            <div class="product-item">
                                <div class="product-thumb">
                                    <a onclick="openModal()"><img
                                            src="<?php echo IMAGE_URL; ?>document/?id=<?php echo $document_id; ?>&w=270&h=230"
                                            alt="<?php echo $name;?>"></a>
                                    <?php
                                                if ($old_price != 0) {
                                                    $p = -100;
                                                    if ($unit_price != 0) {
                                                        $p = $old_price - $unit_price;
                                                        $p = ($p / $old_price) * 100;
                                                    }

                                                ?>
                                    <span class="batch sale"><?php echo intval($p); ?>%</span>
                                    <?php
                                                }
                                                ?>
									<?php
									if($appSession->getUserInfo()->getId() != "")
									{
									?>
                                    <a class="wish-link"
                                        href="<?php echo URL; ?><?php echo  $appSession->getTool()->validUrl($name); ?>/p-<?php echo $code; ?>">
                                      
                                    </a>
									<?php
									}
									?>
                                </div>
                                <div class="product-content">
                                     <h6><a href="<?php echo URL; ?><?php echo  $appSession->getTool()->validUrl($name); ?>/<?php if($attribute_name != ""){ echo $appSession->getTool()->validUrl($attribute_name)."/";} ?>p-<?php echo $code; ?><?php if($attribute_code != ""){ echo ".".$attribute_code;} ?>"
                                        class="product-title"><?php echo $name ?></a>
                                     </h6>
									<?php
									 if($attribute_category_name != "")
									 {
									?>
									 <p class="quantity"><?php echo $attribute_category_name;?> : <?php echo $attribute_name;?></p>
									 <?php
									 }
									 ?>
																			
                                     <p class="quantity"><?php echo $appSession->getLang()->find("Stock");?> : <?php if ($unit_in_stock == "") {
                                                                                echo "0";
                                                                            } else {
                                                                                echo $unit_in_stock;
                                                                            }  ?></p>
                                    <!-- <p class="quantity">1 </p> -->
									<p class="quantity"><?php echo $appSession->getLang()->find("Company Name");?> : <?php echo $commercial_name;?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="price">
                                             <?php echo $appSession->getCurrency()->format($currency_id, $appSession->getTool()->toDouble($unit_price)); ?>
                                             / <?php echo $unit_name ?>
                                        </div>

                                         <a
                                             href="javascript:addProduct('<?php echo $id; ?>', '<?php echo $currency_id; ?>', '<?php echo $unit_id; ?>', '<?php echo $attribute_id; ?>', 1, <?php echo $unit_price; ?>, '<?php echo $second_unit_id; ?>', <?php echo $factor; ?>, '', '<?php echo $company_id;?>', function(status, message){ loadCard(); })">
                                             <span class="cart-btn"><i class="fas fa-shopping-cart"></i><?php echo $appSession->getLang()->find("Add");?></span>

                                         </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php
                                }
                                ?>


                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- trending product-section end -->
<?php } ?>
<script>
function addingProduct(type) {
    var ctr = document.getElementById('qty');
    if (ctr.value == '') {
        alert('Chọn số lượng');
        ctr.focus();
        return;
    }
    var quantity = parseFloat(ctr.value);
    if (quantity <= 0) {
        alert("Số lượng phải lớn hơn 0");
        ctr.focus();
        return;
    }
	ctr = document.getElementById('editdescription');
	var description = ctr.value;
    addProduct('<?php echo $product_product_id; ?>', '<?php echo $product_currency_id; ?>', '<?php echo $product_unit_id; ?>', '<?php echo $product_attribute_id; ?>',
        quantity, <?php echo $product_unit_price; ?>, '<?php echo $second_unit_id; ?>', <?php echo $product_factor; ?>, description, '<?php echo $product_company_id;?>',
        function(status, message) {
			 closePopup();
            if (type == 'card') {
               loadCard();
            } else if (type == 'checkout') {
                document.location.href = '<?php echo URL; ?>checkout'
            }

        }
	);

}
</script>
<?php
}
?>