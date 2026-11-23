<?php
// Thẻ sản phẩm dùng chung (trang chủ, danh mục, nhóm, sản phẩm liên quan).
// Bố cục lấy ý tưởng từ ProductCard của sanpham.chohaosang.vn (D:\xampp\htdocs\code\addons\layout\ocop-cart.php):
// ảnh 4:3 / nhóm hàng + tình trạng kho / tên / giá / nút giỏ hàng + "Liên hệ mua hàng".
// Màu sắc, font theo vftfarms — xem assets/css/vft-card.css.

// Tên danh mục theo id (bảng danh mục đã tải ở addons/index.php)
function vft_category_name($dt_category, $category_id)
{
	static $names = null;
	if ($names === null) {
		$names = [];
		if ($dt_category != null) {
			for ($i = 0; $i < $dt_category->getRowCount(); $i++) {
				$name = $dt_category->getString($i, "name_lg");
				$names[$dt_category->getString($i, "id")] = $name != "" ? $name : $dt_category->getString($i, "name");
			}
		}
	}
	return isset($names[$category_id]) ? $names[$category_id] : "";
}

function vft_js($value)
{
	return htmlspecialchars(addslashes((string)$value), ENT_QUOTES, 'UTF-8');
}

/**
 * In ra một thẻ sản phẩm.
 * $p: product_id, price_id, code, name, document_id, unit_price, old_price, currency_id, unit_id, unit_name,
 *     attribute_id, attribute_name, attribute_category_name, type_id, type_name, sticker, unit_in_stock,
 *     second_unit_id, factor, company_id, category_id, wish_id (tuỳ chọn: hiện nút yêu thích khi đã đăng nhập),
 *     cart_price_id (tuỳ chọn: price_id dùng khi thêm giỏ, khác price_id của link — giá theo khách hàng / nhóm khách)
 */
function vft_product_card($appSession, $dt_category, $p)
{
	$get = function ($key, $default = "") use ($p) {
		return isset($p[$key]) ? $p[$key] : $default;
	};
	$name = $get("name");
	$price_id = $get("price_id");
	$unit_price = floatval($get("unit_price", 0));
	$old_price = floatval($get("old_price", 0));
	$currency_id = $get("currency_id");
	$unit_name = $get("unit_name");
	$in_stock = floatval($get("unit_in_stock", 0)) > 0;
	$factor = $get("factor", "1");
	if ($factor == "" || $factor == "0") {
		$factor = "1";
	}

	$url = URL . $appSession->getTool()->validUrl($name) . "/p-" . $price_id;
	$image = IMAGE_URL . "document/?id=" . $get("document_id") . "&h=300";

	// Dòng nhóm hàng: danh mục sản phẩm, không có thì dùng thuộc tính (vd: Size : 40-50)
	$group_label = vft_category_name($dt_category, $get("category_id"));
	if ($group_label == "" && $get("attribute_category_name") != "") {
		$group_label = $get("attribute_category_name") . " : " . $get("attribute_name");
	}

	$price_text = "Liên hệ";
	if ($unit_price > 0) {
		$price_text = $appSession->getCurrency()->format($currency_id, $unit_price);
	}
	$price_display = $price_text . ($unit_price > 0 && $unit_name != "" ? " / " . $unit_name : "");

	$discount = 0;
	if ($old_price > 0 && $unit_price > 0 && $old_price > $unit_price) {
		$discount = intval((($old_price - $unit_price) / $old_price) * 100);
	}

	// Dữ liệu cho form "Liên hệ mua hàng" (assets/js/vft-contact.js)
	$contact_attr = htmlspecialchars(json_encode([
		"id" => (string)$price_id,
		"code" => (string)$get("code"),
		"name" => (string)$name,
		"unit" => (string)$unit_name,
		"spec" => (string)$get("attribute_name"),
		"priceDisplay" => $price_display,
	], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');

	$cart_js = "javascript:addProduct('" . vft_js($get("product_id")) . "', '" . vft_js($currency_id) . "', '" . vft_js($get("unit_id")) . "', '"
		. vft_js($get("attribute_id")) . "', 1, " . $unit_price . ", '" . vft_js($get("second_unit_id")) . "', " . floatval($factor) . ", '', '"
		. vft_js($get("company_id")) . "', '" . vft_js($get("cart_price_id", $price_id)) . "', '" . vft_js($get("type_id")) . "', function(status, message){ loadCard(); })";

	$name_html = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
	?>
	<div class="product-item vft-card<?php if (!$in_stock) { echo " is-out"; } ?>">
		<div class="product-thumb vft-card-thumb">
			<?php if ($get("sticker") != "") { ?>
			<span class="vft-card-sticker"><?php echo htmlspecialchars($get("sticker")); ?></span>
			<?php } elseif ($discount > 0) { ?>
			<span class="vft-card-sticker is-sale">-<?php echo $discount; ?>%</span>
			<?php } ?>
			<?php if ($get("type_name") != "") { ?>
			<span class="vft-card-type"><?php echo htmlspecialchars($get("type_name")); ?></span>
			<?php } ?>
			<a href="<?php echo $url; ?>" class="vft-card-img"><img src="<?php echo $image; ?>" alt="<?php echo $name_html; ?>" loading="lazy"></a>
			<?php if ($get("wish_id") != "" && $appSession->getUserInfo()->getId() != "") { ?>
			<a class="vft-card-wish" href="javascript:addToWishList('<?php echo vft_js($get("wish_id")); ?>')" aria-label="Yêu thích" title="Yêu thích"><i class="fa fa-heart"></i></a>
			<?php } ?>
		</div>
		<div class="product-content vft-card-body">
			<div class="vft-card-meta">
				<span class="vft-card-group"><?php echo htmlspecialchars($group_label); ?></span>
				<span class="vft-card-stock<?php if (!$in_stock) { echo " is-out"; } ?>"><span class="vft-card-dot" aria-hidden="true"></span><?php echo $in_stock ? "Còn hàng" : "Hết hàng"; ?></span>
			</div>
			<h6 class="vft-card-name"><a href="<?php echo $url; ?>" class="product-title"><?php echo $name_html; ?></a></h6>
			<div class="vft-card-price">
				<?php echo $price_text; ?><?php if ($unit_price > 0 && $unit_name != "") { ?><span class="vft-card-unit"> / <?php echo htmlspecialchars($unit_name); ?></span><?php } ?>
				<?php if ($discount > 0) { ?><del><?php echo $appSession->getCurrency()->format($currency_id, $old_price); ?></del><?php } ?>
			</div>
			<div class="vft-card-actions">
				<?php if ($in_stock) { ?>
				<a class="vft-card-cart" href="<?php echo $cart_js; ?>" aria-label="Thêm vào giỏ hàng" title="Thêm vào giỏ hàng"><i class="fas fa-shopping-cart"></i></a>
				<?php } ?>
				<button type="button" class="vft-card-contact<?php if (!$in_stock) { echo " is-out"; } ?>" data-vft-contact="<?php echo $contact_attr; ?>" data-vft-source="product_card">
					<i class="fas fa-phone-alt" aria-hidden="true"></i>
					<?php if ($in_stock) { ?>
					<span class="vft-card-long">Liên hệ mua hàng</span><span class="vft-card-short">Liên hệ</span>
					<?php } else { ?>
					<span>Hỏi hàng</span>
					<?php } ?>
				</button>
			</div>
		</div>
	</div>
	<?php
}
?>
