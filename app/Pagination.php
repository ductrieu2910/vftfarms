<?php
// Phân trang cho danh sách sản phẩm (DataTable) theo tham số ?page=N trên URL

if (!defined('PRODUCT_PER_PAGE')) {
	define('PRODUCT_PER_PAGE', 12);
}

/**
 * Cắt DataTable chỉ còn các dòng của trang hiện tại.
 * Trả về [trang hiện tại, tổng số trang, tổng số dòng]
 */
function paginateTable($dt, $page, $perPage = PRODUCT_PER_PAGE)
{
	$rows = $dt->getData();
	$total = count($rows);
	$totalPages = max(1, (int)ceil($total / $perPage));
	$page = (int)$page;
	if ($page < 1) {
		$page = 1;
	}
	if ($page > $totalPages) {
		$page = $totalPages;
	}
	$dt->setData(array_values(array_slice($rows, ($page - 1) * $perPage, $perPage)));
	return [$page, $totalPages, $total];
}

/**
 * Tạo URL của trang $page, giữ nguyên đường dẫn và các tham số khác (vd: search)
 */
function paginationUrl($page)
{
	// Lấy query từ REQUEST_URI (không dùng QUERY_STRING vì rewrite có thể làm thay đổi nó)
	$uri = $_SERVER['REQUEST_URI'];
	$pos = strpos($uri, '?');
	$path = $pos === false ? $uri : substr($uri, 0, $pos);
	$qs = $pos === false ? '' : substr($uri, $pos + 1);
	$query = [];
	foreach (explode('&', $qs) as $part) {
		if ($part == '') {
			continue;
		}
		$kv = explode('=', $part, 2);
		if ($kv[0] == 'page') {
			continue;
		}
		$query[] = $part;
	}
	if ($page > 1) {
		$query[] = 'page=' . (int)$page;
	}
	return $path . (count($query) > 0 ? '?' . implode('&', $query) : '');
}

/**
 * In thanh phân trang (dùng style .pagination của theme)
 */
function renderPagination($page, $totalPages)
{
	if ($totalPages <= 1) {
		return;
	}
	// Hiển thị trang đầu, trang cuối và 1 trang liền kề trang hiện tại (vd: 1 2 ... 7)
	$pages = [];
	for ($p = 1; $p <= $totalPages; $p++) {
		if ($p == 1 || $p == $totalPages || abs($p - $page) <= 1) {
			$pages[] = $p;
		}
	}
	?>
	<style>
		.product-pagination { flex-wrap: wrap; align-items: center; gap: 10px; }
		.product-pagination li,
		.product-pagination li:first-child,
		.product-pagination li:last-child { margin: 0; }
		.product-pagination li a,
		.product-pagination li:first-child a,
		.product-pagination li:last-child a {
			display: flex; align-items: center; justify-content: center;
			width: 48px; height: 48px; line-height: 1;
			border-radius: 12px; border: 1px solid #e3e8ef;
			background-color: #fff; color: #222d39;
			font-size: 16px; font-weight: 500;
			box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04);
		}
		.product-pagination li a i { font-size: 15px; }
		.product-pagination li a:hover { border-color: #59b828; background-color: #fff; color: #59b828; }
		.product-pagination li a.active,
		.product-pagination li a.active:hover { background-color: #59b828; border-color: #59b828; color: #fff; font-weight: 600; }
		.product-pagination li span.dots { display: block; width: 24px; text-align: center; color: #6b7785; font-size: 16px; letter-spacing: 1px; }
		@media (max-width: 575px) {
			.product-pagination { gap: 8px; }
			.product-pagination li a, .product-pagination li:first-child a, .product-pagination li:last-child a { width: 40px; height: 40px; border-radius: 10px; font-size: 15px; }
		}
	</style>
	<ul class="pagination product-pagination justify-content-center">
		<?php if ($page > 1) { ?>
			<li><a href="<?php echo htmlspecialchars(paginationUrl($page - 1)); ?>" aria-label="Previous"><i class="fas fa-chevron-left"></i></a></li>
		<?php } ?>
		<?php
		$prev = 0;
		foreach ($pages as $p) {
			if ($prev != 0 && $p - $prev > 1) {
				echo '<li><span class="dots">...</span></li>';
			}
			$prev = $p;
		?>
			<li><a href="<?php echo htmlspecialchars(paginationUrl($p)); ?>"<?php if ($p == $page) { echo ' class="active"'; } ?>><?php echo $p; ?></a></li>
		<?php } ?>
		<?php if ($page < $totalPages) { ?>
			<li><a href="<?php echo htmlspecialchars(paginationUrl($page + 1)); ?>" aria-label="Next"><i class="fas fa-chevron-right"></i></a></li>
		<?php } ?>
	</ul>
	<?php
}
