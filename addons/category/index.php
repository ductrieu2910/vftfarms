<?php
require_once(ABSPATH . 'api/Product.php');

function findChild($dt, $parent_id)
{
    $ids = "";
    for ($i = 0; $i < $dt->getRowCount(); $i++) {
        if ($dt->getString($i, "parent_id") == $parent_id) {
            if ($ids != "") {
                $ids = $ids . ",";
            }
            $ids = $ids . $dt->getString($i, "id");
            $s1 = findChild($dt, $dt->getString($i, "id"));
            if ($s1 != "") {
                $ids = $ids . "," . $s1;
            }
        }
    }
    return $ids;
}
$ids = findChild($dt_category, $selected_id);
if ($ids != "") {
    $ids = $ids . "," . $selected_id;
} else {
    $ids = $selected_id;
}
$arr = $appSession->getTool()->split($ids, ',');
$ids = "";
for ($i = 0; $i < count($arr); $i++) {
    if ($ids != "") {
        $ids = $ids . " OR ";
    }
    $ids = $ids . " d1.category_id='" . $arr[$i] . "'";
}
$productList = null;
$sql = "SELECT d1.id, d1.category_id, d5.unit_id, d5.attribute_id,	d5.type_id, d1.code, d1.name, lg.description AS name_lg, d2.document_id, d5.unit_price, d5.old_price, d6.name AS unit_name, d5.currency_id";
$sql = $sql.", d9.name AS attribute_name, d10.name attribute_category_name, d5.factor"; 
$sql = $sql.", d5.company_id, d7.commercial_name, d7.name AS company_name, d5.id AS price_id, d8.document_id AS price_document_id, d5.description, d11.name AS type_name, 0.0 AS unit_in_stock, d12.name AS sticker";
$sql = $sql." FROM product d1";
$sql = $sql." LEFT OUTER JOIN poster d2 ON(d1.id = d2.rel_id AND d2.publish=1 AND d2.status =0)";
$sql = $sql." LEFT OUTER JOIN res_lang_line lg ON(lg.lang_id='".$appSession->getConfig()->getProperty("lang_id")."' AND lg.rel_id = d1.id AND lg.name='product_name' AND lg.status =0)";
$sql = $sql." LEFT OUTER JOIN product_price d5 ON(d1.id = d5.product_id AND d5.status =0 AND d5.publish=1) LEFT OUTER JOIN product_unit d6 ON(d5.unit_id = d6.id)";
$sql = $sql." LEFT OUTER JOIN res_company d7 ON(d5.company_id = d7.id)";
$sql = $sql." LEFT OUTER JOIN poster d8 ON(d5.id = d8.rel_id AND d8.publish=1 AND d8.status =0)";
$sql = $sql." LEFT OUTER JOIN attribute d9 ON(d5.attribute_id = d9.id)";
$sql = $sql." LEFT OUTER JOIN attribute_category d10 ON(d9.category_id = d10.id)";
$sql = $sql." LEFT OUTER JOIN product_type d11 ON(d5.type_id = d11.id)";
$sql = $sql." LEFT OUTER JOIN res_meta d12 ON(d1.id = d12.rel_id AND d12.type='Sticker' AND d12.status =0)";
$sql = $sql." WHERE d1.status =0 AND d1.publish = 1";
$sql = $sql." AND (".$ids.")";
if($search != "")
{
	$sql = $sql." AND (".$appSession->getTier()->buildSearch(["d1.code", "d1.name", "lg.description"], $search).")";
}

$sql1 = $sql." AND d5.type='PRODUCT'";
$sql1 = $sql1." ORDER BY d1.name ASC, d1.sequence ASC, d5.sequence ASC";
$msg->add("query", $sql1);

$productList = $appSession->getTier()->getTable($msg);

// Phân trang: 12 sản phẩm / trang (cắt trước khi đếm tồn kho để chỉ truy vấn sản phẩm của trang này)
require_once(ABSPATH . 'app/Pagination.php');
list($page_number, $total_pages) = paginateTable($productList, $page_number);

$product = new Product($appSession);

$productList = $product->countProduct($productList);

?>

<!-- page-header-section start -->
<div class="page-header-section">
    <div class="container">
        <div class="row">
            <div class="col-12 d-flex justify-content-between justify-content-md-end">
                <ul class="breadcrumb">
                    <li><a href="<?php echo URL; ?>"><?php echo $appSession->getLang()->find("Home");?> </a></li>
                    <li><span>/</span></li>
                    <li><a href="<?php echo URL; ?>category"><?php echo $appSession->getLang()->find("Category product");?></a></li>
					<?php
					if($selected_id != "")
					{
						$category_name = "";
						$category_code = "";
						
						for($i =0; $i<$dt_category->getRowCount(); $i++)
						{
						 if($dt_category->getString($i, "id") == $selected_id)
						  {
							   $category_code = $dt_category->getString($i, "code");
								$category_name = $dt_category->getString($i, "name_lg");
								if ($category_name == "") {
									$category_name = $dt_category->getString($i, "name");
								}
						  }
						}
					?>
					 <li><span>/</span></li>
                    <li><a href="<?php echo URL; ?><?php echo $appSession->getTool()->validUrl($category_name); ?>/c-<?php echo $category_code; ?>"><?php echo $category_name;?></a></li>
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
                                        for ($i = 0; $i < $dt_category->getRowCount(); $i++) {
                                            $id = $dt_category->getString($i, "id");
                                            $parent_id = $dt_category->getString($i, "parent_id");
                                            if ($parent_id == "") {
												$hasItem = false;
												$hasCurrent = false;
                                                $category_code = $dt_category->getString($i, "code");
                                                $category_name = $dt_category->getString($i, "name_lg");
                                                if ($category_name == "") {
                                                    $category_name = $dt_category->getString($i, "name");
                                                }
												 for ($n = 0; $n < $dt_category->getRowCount(); $n++) 
												 {
													 if($dt_category->getString($n, "parent_id") == $id)
													 {
														 $hasItem = true;
														 if($dt_category->getString($n, "id") == $selected_id)
														 {
															 $hasCurrent = true;
														 }
														 
													 }
												 }
                                        ?>
										<?php
										if( $hasItem == true)
										{
											
										?>
										 <li>
										 
                                        <a class="" data-toggle="collapse" href="#catagory-widget-s<?php echo $i ?>"
                                            role="button" aria-expanded="false"
                                            aria-controls="catagory-widget-s<?php echo $i ?>"><?php echo $category_name; ?><span
                                                class="plus-minus"></span></a>

                                        <ul class="catagory-submenu collapse<?php if ($hasCurrent == true) { ?> show<?php } ?>"
                                            id="catagory-widget-s<?php echo $i ?>">

                                            <?php

                                                    for ($n = 0; $n < $dt_category->getRowCount(); $n++) {
                                                        $parent_id2 = $dt_category->getString($n, "parent_id");
                                                        if ($parent_id2 == $id) {
                                                            $category_code2 = $dt_category->getString($n, "code");
                                                            $category_name2 = $dt_category->getString($n, "name_lg");

                                                            if ($category_name2 == "") {
                                                                $category_name2 = $dt_category->getString($n, "name");
                                                            }
                                                    ?>
                                            <li><a
                                                    href="<?php echo URL; ?><?php echo $appSession->getTool()->validUrl($category_name2); ?>/c-<?php echo $category_code2; ?>"><?php echo $category_name2; ?></a>
                                            </li>
                                            <?php
                                                        }
                                                    }
                                                    ?>


                                        </ul>
										 </li>
                                        <?php
												}else
												{
												?>
												<li><a
                                                    href="<?php echo URL; ?><?php echo $appSession->getTool()->validUrl($category_name); ?>/c-<?php echo $category_code; ?>"><?php echo $category_name; ?></a>
												</li>
												<?php
												}
                                            }
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
                        $unit_id = $productList->getString($i, "unit_id");
                        $currency_id = $productList->getFloat($i, "currency_id");
						$unit_name = $productList->getString($i, "unit_name");
						$price_id = $productList->getString($i, "price_id");
						
						$attribute_category_name = $productList->getString($i, "attribute_category_name");
						$attribute_id = $productList->getString($i, "attribute_id");
						$type_id = $productList->getString($i, "type_id");
						$attribute_name = $productList->getString($i, "attribute_name");
						$attribute_code = $productList->getString($i, "attribute_code");
						$unit_in_stock = $productList->getFloat($i, "unit_in_stock");
						$second_unit_id = $productList->getString($i, "second_unit_id");
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
                    <div class="col-sm-6 col-xl-4">
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