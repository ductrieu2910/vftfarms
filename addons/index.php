<?php
require_once(ABSPATH . 'api/Product.php');


$page = "home";
$lang = "vi";
$page_id = '';

if ($uri != '/' && $uri != '') {
    $items = explode("/", $uri);
    if (count($items) == 1) {
        $page = $items[0];
    }
    if (count($items) > 0) {
        $page_id = $items[count($items) - 1];
    }
	if($page_id == "lang")
	{
		
		if (count($items) > 2)
		{
			//$lang_id = $items[count($items) - 2];
			
			//$appSession->getConfig()->setProperty("lang_id", $lang_id);
			//$appSession->getConfig()->save();
		}
		$page_id = $items[0];
	}
}
$search = "";
$arr = $appSession->getTool()->split($PARAMS, '&');

for($i =0; $i<count($arr); $i++)
{
	$index = $appSession->getTool()->indexOf($arr[$i], '=');
	if($index != -1)
	{
		
		$k = substr($arr[$i], 0, $index);
		$v = substr($arr[$i], $index + 1);
		
		if($k == "search")
		{
			$search = $v;
			
		}
	}
}

$langList =["vi", "en", "ru", "cn", "jp", "kr"];
$lang_id = "vi";
for($i=0; $i<count($langList); $i++)
{
	if($appSession->getConfig()->getProperty("lang_id") == $langList[$i])
	{
		$lang_id = $langList[$i];
		break;
	}
}

include( ABSPATH .'app/lang/'.$lang_id.'.php');
foreach($langs as $key => $item)
{
	$appSession->getLang()->setProperty($key, $item);				
}


$msg = $appSession->getTier()->createMessage();

$product = new Product($appSession);
$dt_category = $product->categoryList();

$page = "home";

if($search != "")
{
	$page = "category";
}

$selected_id = "";
$page_title = META_TITLE;
$page_keywords = META_KEYWORD;
$page_description = META_DESCRIPTION;
$document_id = "";
$document_ext = "";
$index = $appSession->getTool()->indexOf($uri, "/c-");
if ($index != -1) {
    $page = "category";
    $code = $appSession->getTool()->substring($uri, $index + 3);
    $code = $appSession->getTool()->replace($code, "#", "");
	$code = $appSession->getTool()->replace($code, "/", "");
	
    $sql = "SELECT d1.id, d1.name, d2.keywords, d2.description FROM product_category d1 LEFT OUTER JOIN meta d2 ON(d1.id = d2.rel_id AND d2.status=0 AND d2.publish=1) WHERE d1.code='" . $code . "' AND d1.status =0";

    $msg->add("query", $sql);
    $arr = $appSession->getTier()->getArray($msg);
    if (count($arr) > 0) {
        $selected_id = $arr[0][0];
        if ($arr[0][1] != "") {
            $page_title = META_TITLE . " - " . $arr[0][1];
        }
        if ($arr[0][2] != "") {
            $page_keywords = $arr[0][2];
        }
        if ($arr[0][3] != "") {
            $page_description = $arr[0][3];
        }
    }
}
$index = $appSession->getTool()->indexOf($uri, "/g-");
if ($index != -1) {
    $page = "group";
    $code = $appSession->getTool()->substring($uri, $index + 3);
    $code = $appSession->getTool()->replace($code, "#", "");
	$code = $appSession->getTool()->replace($code, "/", "");
    $sql = "SELECT d1.id, d1.name, d2.keywords, d2.description FROM product_group d1 LEFT OUTER JOIN meta d2 ON(d1.id = d2.rel_id AND d2.status=0 AND d2.publish=1) WHERE d1.code='" . $code . "'";
	
    $msg->add("query", $sql);
    $arr = $appSession->getTier()->getArray($msg);
    if (count($arr) > 0) {
        $selected_id = $arr[0][0];
        if ($arr[0][1] != "") {
            $page_title = META_TITLE . " - " . $arr[0][1];
        }
        if ($arr[0][2] != "") {
            $page_keywords = $arr[0][2];
        }
        if ($arr[0][3] != "") {
            $page_description = $arr[0][3];
        }
    }
}
$index = $appSession->getTool()->indexOf($uri, "/p-");
$price_id = "";
if ($index != -1) {
    $page = "product";
    $price_id = $appSession->getTool()->substring($uri, $index + 3);
 
    $sql = "SELECT d2.id, d2.name, d3.keywords, d3.description, d8.document_id, d9.document_id, doc1.ext, doc2.ext, d1.id AS price_id FROM product_price d1 LEFT OUTER JOIN product d2 ON(d1.product_id = d2.id) LEFT OUTER JOIN meta d3 ON(d2.id = d3.rel_id AND d3.status=0 AND d3.publish=1)  LEFT OUTER JOIN poster d8 ON(d1.id = d8.rel_id AND d8.publish=1 AND d8.status =0) LEFT OUTER JOIN poster d9 ON(d2.id = d9.rel_id AND d9.publish=1 AND d9.status =0) LEFT OUTER JOIN document doc1 ON(d8.document_id = doc1.id) LEFT OUTER JOIN document doc2 ON(d9.document_id = doc2.id) WHERE (d1.id ='" . $price_id . "' OR d1.code='".$price_id."')";
	
    $msg->add("query", $sql);
    $arr = $appSession->getTier()->getArray($msg);
    if (count($arr) > 0) {
        $selected_id = $arr[0][0];
        if ($arr[0][1] != "") {
            $page_title = $arr[0][1]. " - ".META_TITLE ;
        }
        if ($arr[0][2] != "") {
            $page_keywords = $arr[0][2];
        }
        if ($arr[0][3] != "") {
            $page_description = $arr[0][3];
        }
		$document_id = $arr[0][4];
		$document_ext = $arr[0][6];
		if($document_id == "")
		{
			$document_id = $arr[0][5];
			$document_ext = $arr[0][7];
		}
    }
}
$index = $appSession->getTool()->indexOf($uri, "blog");
$post_category_id = "";
if ($index != -1) {
	 $page = "blog";
	 $arr = explode("/", $uri);
	 $post_category_id = $arr[count($arr)-1];
}
$index = $appSession->getTool()->indexOf($uri, "post");
$post_id = "";
if ($index != -1) {
	 $page = "post";
	 $arr = explode("/", $uri);
	 $post_id = $arr[count($arr)-1];
}

$uri = $appSession->getTool()->replace($uri, "/", "");

if ($uri == "card") {
    $page = "card";
} else if ($uri == "category") {
    $page = "category";
} else if ($uri == "checkout") {
    $page = "checkout";
} else if ($uri == "about") {
    $page = "about";
} else if ($uri == "contact") {
    $page = "contact";
} else if ($uri == "wishlist") {
    $page = "wishlist";
} else if ($uri == "paid") {
    $page = "paid";
} else if ($uri == "account") {
    $page = "account";
} else if ($uri == "order") {
    $page = "order";
} else if ($uri == "loyalty") {
    $page = "loyalty";
} else if ($uri == "profile") {
    $page = "profile";
} else if ($uri == "login") {
    $page = "login";
} else if ($uri == "signup") {
    $page = "signup";
} else if ($uri == "signup-success") {
    $page = "signup-success";
} else if ($uri == "blog") {
    $page = "blog";
} else if ($uri == "account") {
    $page = "account";
}else if ($uri == "delivery") {
    $page = "track-order";
}else if ($uri == "sale") {
    $page = "sale";
}
else if ($uri == "signin") {
    $page = "signin";
}else if ($uri == "sale_rule") {
    $page = "sale_rule";
}else if ($uri == "sale_security") {
    $page = "sale_security";
}else if ($uri == "sale_support") {
    $page = "sale_support";
}else if ($uri == "sale_refund") {
    $page = "sale_refund";
}else if ($uri == "sale_agreement") {
    $page = "sale_agreement";
}else if ($uri == "sale_shipping") {
    $page = "sale_shipping";
}else if ($uri == "sale_payment") {
    $page = "sale_payment";
}else if ($uri == "chinh_sach") {
    $page = "chinh_sach";
}else if ($uri == "privacy") {
    $page = "privacy";
}

 $sql = "SELECT d1.id, d1.name FROM post_category d1  WHERE d1.status =0 AND d1.type='HOME' ORDER BY d1.sequence ASC";

 $msg->add("query", $sql);
 $post_category = $appSession->getTier()->getArray($msg);

require_once(ABSPATH . 'api/Sale.php');
$sale = new Sale($appSession);
$productList = $sale->productList();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $page_title; ?></title>
	<meta content="<?php echo $page_description ?>" name="description">
    <meta content="<?php echo $page_keywords ?>" name="keywords">
	<?php
	if( $document_id != "")
	{
		if($document_ext == "svg")
		{
			$document_ext  = "svg+xml";
		}else if($document_ext == "png"){
			$document_ext  = "png";
		}else if($document_ext == "avif"){
			$document_ext  = "avif";
		}else{
			$document_ext  = "jpeg";
		}
	?>
	<meta property="og:image:type" content="image/<?php echo $document_ext;?>">
	<meta property="og:image" content="<?php echo IMAGE_URL; ?>document/?id=<?php echo $document_id; ?>&h=220">
	
	<?php
	}
	?>
	<meta property="og:title" content="<?php echo $page_title; ?>">
	<meta property="og:description" content="<?php echo $page_title ?>">
    <link rel="shortcut icon" href="<?php echo URL; ?>favicon.ico" />
    <link rel="stylesheet" href="<?php echo URL; ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo URL; ?>assets/css/all.min.css">
    <link rel="stylesheet" href="<?php echo URL; ?>assets/css/animate.css">
    <link rel="stylesheet" href="<?php echo URL; ?>assets/css/swiper.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo URL; ?>assets/css/slick.css">
    <link rel="stylesheet" type="text/css" href="<?php echo URL; ?>assets/css/slick-theme.css">
    <link rel="stylesheet" href="<?php echo URL; ?>assets/css/custom-select.css">
    <link rel="stylesheet" href="<?php echo URL; ?>assets/css/style.css">
	<script src='<?php echo URL; ?>assets/js/jquery.min.js'></script>
    <script type="text/javascript" src="<?php echo URL; ?>assets/js/controller.js"></script>
    <script type="text/javascript" src="<?php echo URL; ?>assets/js/tool.js"></script>
	<script type="text/javascript" src="<?php echo URL; ?>assets/js/sha1.js"></script>
	<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-ZPNECBZVPW"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-ZPNECBZVPW');
</script>
</head>

<body id="top-page">
    <a class="position-absolute" href="javascript:void(0)" onClick="cartopen()">
        <div id="sitebar-drawar" class="sitebar-drawar" style="display:none">
            <div class="cart-count d-flex align-items-center">
                <i class="fas fa-shopping-basket"></i>
                <span id="itemCount">0</span>
            </div>
            <div class="total-price" id="totalPrice">0</div>
        </div>
    </a>
     <!--search Modal -->
     <div class="modal fade" id="search-select-id" tabindex="-1" aria-labelledby="search-select-id" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="select-search-option">
                        <div class="flux-custom-select">
                            <select onChange="">
								<option value="0"><?php echo $appSession->getLang()->find("Select Catagory");?></option>
							  <?php
							  for($i =0; $i<$dt_category->getRowCount(); $i++)
							  {
								  if($dt_category->getString($i, "parent_id") == "")
								  {
									   $category_code = $dt_category->getString($i, "code");
										$category_name = $dt_category->getString($i, "name_lg");
										if ($category_name == "") {
											$category_name = $dt_category->getString($i, "name");
										}
							  ?>
                              
                              <option value="<?php echo $category_code?>"><?php echo $category_name;?></option>
							  <?php
								  }
							  }
							  ?>
							  
                        
                            </select>
                        </div>
                        <form action="#" class="search-form">
                            <input type="text" name="search" value="<?php echo $search;?>" placeholder="Search for Products">
                            <button class="submit-btn"><i class="fas fa-search"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- sidebar-cart -->
    <div id="sitebar-cart" class="sitebar-cart">
        
    </div>
    <!-- header section start -->
    <header class="header">
        <div class="header-top">
            <div class="mobile-header d-flex justify-content-between align-items-center d-xl-none">
              

                <div class="all-catagory-option mobile-device">
                    <a class="bar-btn"><i class="fas fa-bars"></i><span class="ml-2 d-none d-md-inline"><?php echo $appSession->getLang()->find("All Catagories");?></span></a>
                    <a class="close-btn"><i class="fas fa-times"></i><span class="ml-2 d-none d-md-inline"><?php echo $appSession->getLang()->find("All Catagories");?></span></a>
                </div>
                <a href="<?php echo URL;?>" class="logo"><img src="<?php echo URL; ?>assets/images/logo.png" alt="<?php echo $page_title;?>"></a>

                <!-- search select -->
                <div class="text-center mobile-search">
                    <button type="button" data-toggle="modal" data-target="#search-select-id"><i class="fas fa-search"></i></button>
                </div>

                <!-- menubar -->
                <div>
                    <button class="menu-bar" type="button" data-toggle="modal" data-target="#menu-id">
                        <?php echo $appSession->getLang()->find("Menu");?><i class="fas fa-caret-down"></i>
                    </button>
                </div>

            </div>
            <div class="d-none d-xl-flex row align-items-center">
                <div class="col-5 col-md-2">
                    <a href="<?php echo URL;?>" class="logo"><img src="<?php echo URL;?>assets/images/logo.png" alt="<?php echo $page_title;?>"></a>
                </div>
                <div class="col-5 col-md-9 col-lg-5">
                   
                    <div class="select-search-option d-none d-md-flex">
                        <div class="flux-custom-select">
                            <select id="editsearch-category">
                              <option value=""><?php echo $appSession->getLang()->find("Select Catagory");?></option>
							  <?php
							  for($i =0; $i<$dt_category->getRowCount(); $i++)
							  {
								  if($dt_category->getString($i, "parent_id") == "")
								  {
										$category_id = $dt_category->getString($i, "id");
									    $category_code = $dt_category->getString($i, "code");
										$category_name = $dt_category->getString($i, "name_lg");
										if ($category_name == "") {
											$category_name = $dt_category->getString($i, "name");
										}
							  ?>
                              
                              <option value="<?php echo $appSession->getTool()->validUrl($category_name); ?>/c-<?php echo $category_code; ?>" <?php if($category_id == $selected_id){ echo " selected ";}?>><?php echo $category_name;?></option>
							  <?php
								  }
							  }
							  ?>
			
                            </select>
                        </div>
                        <form class="search-form" onSubmit="return false">
                            <input type="text" id="editsearch" placeholder="<?php echo $appSession->getLang()->find("Search for Products");?>" value="<?php echo $search;?>"  onkeydown = "if (event.keyCode == 13)
                       doSearch()" >
                            <button class="submit-btn" onClick="doSearch()";><i class="fas fa-search"></i></button>
                        </form>
                    </div>
                </div>
                <div class="col-2 col-md-1 col-lg-5">
                    <ul class="site-action d-none d-lg-flex align-items-center justify-content-between  ml-auto">
                        <li class="site-phone"><a href="tel:<?php echo CONTACT_TEL;?>"><i class="fas fa-phone"></i> <?php echo CONTACT_TEL;?></a></li>
                       
                        
                        
						<li class="my-account"><a class="dropdown-toggle" href="#" role="button" id="myaccount" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-user mr-1"></i><?php 
								if($appSession->getUserInfo()->getId() == "")
								{
								?><?php echo $appSession->getLang()->find("Account");?>
								<?php
								}else
								{
								?>
								<?php echo $appSession->getConfig()->getProperty("user_name");?>
								<?php
								}
								?>
								</a>
                            <ul class="submenu dropdown-menu" aria-labelledby="myaccount">
								<?php 
								if($appSession->getUserInfo()->getId() == "")
								{
								?>
								<li><a href="<?php echo URL;?>signin"><?php echo $appSession->getLang()->find("Sign In");?></a></li>
                                <li><a href="<?php echo URL;?>signup"><?php echo $appSession->getLang()->find("Sign Up");?></a></li>
								
								<?php
								}else{
								?>
								<li><a href="<?php echo URL;?>account/?type=profile"><?php echo $appSession->getLang()->find("Profile");?></a></li>
								
								<?php
								}
								?>
								<li><a href="<?php echo URL;?>account/?type=order"><?php echo $appSession->getLang()->find("Orders");?></a></li>
								<?php 
								if($appSession->getUserInfo()->getId() != "")
								{
								?>
								<li><a href="javascript:doLogout()"><?php echo $appSession->getLang()->find("Sign Out");?></a></li>
								<?php
								}
								?>
                                
                            </ul>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
        <hr>
        <div class="header-bottom">
            <div class="row m-0 align-items-center mega-menu-relative">
                <div class="col-md-2 p-0 d-none d-xl-block">
                    <div class="all-catagory-option">
                        <a class="bar-btn"><i class="fas fa-bars"></i><?php echo $appSession->getLang()->find("All Catagories");?></a>
                        <a class="close-btn"><i class="fas fa-times"></i><?php echo $appSession->getLang()->find("All Catagories");?></a>
                    </div>
                </div>
                <div class="col-md-10">
                    <div class="menu-area d-none d-xl-flex justify-content-between align-items-center">
                        <ul class="menu d-xl-flex flex-wrap list-unstyled">
							<li class="item-has-children"><a href="<?php echo URL;?>"><?php echo $appSession->getLang()->find("Home");?></a></li>
                            
                            <li class="item-has-mega-menu"><a href="#"><?php echo $appSession->getLang()->find("Products");?> <i class="fas fa-angle-down"></i></a>
                                 <div class="mega-menu-container">
                                    <div class="row m-0">
                                        <div class="col-lg-12">
                                            <div class="row m-0">
												<?php
							  for($i =0; $i<$dt_category->getRowCount(); $i++)
							  {
								  if($dt_category->getString($i, "parent_id") == "")
								  {
										 $id = $dt_category->getString($i, "id");
									   $category_code = $dt_category->getString($i, "code");
										$category_name = $dt_category->getString($i, "name_lg");
										if ($category_name == "") {
											$category_name = $dt_category->getString($i, "name");
										}
										$hasChild = false;
									for($m =0; $m<$dt_category->getRowCount(); $m++)
									  {
										  if($dt_category->getString($m, "parent_id") == $id)
										  {
											  $hasChild = true;
											  break;
										  }
									  }
							  ?>
                              
                            
									<div class="col-lg-4">
										<div class="ctagory-item">
											<h6 class="title"><a href="<?php echo URL; ?><?php echo $appSession->getTool()->validUrl($category_name); ?>/c-<?php echo $category_code; ?>"><?php echo $category_name;?></a></h6>
											<ul>
												<?php
												 for($m =0; $m<$dt_category->getRowCount(); $m++)
												  {
													  if($dt_category->getString($m, "parent_id") == $id)
													  {
														  $category_code2 = $dt_category->getString($m, "code");
															$category_name2 = $dt_category->getString($m, "name_lg");
															if ($category_name2 == "") {
																$category_name2 = $dt_category->getString($m, "name");
															}
									  ?>
												<li><a href="<?php echo URL; ?><?php echo $appSession->getTool()->validUrl($category_name2); ?>/c-<?php echo $category_code2; ?>"><?php echo $category_name2;?></a></li>
												<?php
													  }
												  }
													  ?>
											</ul>
										</div>
									</div>
							  <?php
								  }
							  }
							  ?>
                                                
                                            </div>
                                        </div>
                                       
                                    </div>
                                </div>
                            </li>
							 <li class="item-has-children"><a data-toggle="collapse" href="#mainmenuid3" role="button" aria-expanded="false" aria-controls="mainmenuid3"><span>Chính sách</span> <i class="fas fa-angle-down"></i></a>
                            <ul class="submenu collapse" id="mainmenuid3">
                                <li><a href="<?php echo URL; ?>sale_support">Cam kết dịch vụ</a></li>
											<li><a href="<?php echo URL; ?>chinh_sach">Chính sách bán hàng</a></li>
											<li><a href="<?php echo URL; ?>sale_payment">Hình thức và chính sách thanh toán</a></li>
											<li><a href="<?php echo URL; ?>sale_refund">Chính sách đổi trả hàng</a></li>
											<li><a href="<?php echo URL; ?>sale_shipping">Chính sách giao hàng</a></li>
											<li><a href="<?php echo URL; ?>sale_security">Chính sách bảo mật</a></li>
							
                            </ul>
                        </li>
						 <li class="item-has-children"><a data-toggle="collapse" href="#mainmenuid3" role="button" aria-expanded="false" aria-controls="mainmenuid3"><span>Giới thiệu</span> <i class="fas fa-angle-down"></i></a>
                            <ul class="submenu collapse" id="mainmenuid3">
                               <li><a href="https://vifotec.com/" target="_blank">Giới thiệu</a></li>
											<li><a href="<?php echo URL;?>assets/VFT.pdf" target="_blank">Catalog </a></li>
											<li><a href="<?php echo URL;?>assets/HOSONANG_LUC_CONG_TY_VIFOTEC.pdf" target="_blank">Hồ sơ năng lực của công ty</a></li>
                                            <li><a href="<?php echo URL; ?>product"><?php echo $appSession->getLang()->find("Products");?></a></li>
                                            <li><a href="<?php echo URL; ?>faq"><?php echo $appSession->getLang()->find("FAQ");?></a></li>
                                            <li><a href="<?php echo URL; ?>contact"><?php echo $appSession->getLang()->find("Contact");?></a></li>
											
											<li><a href="https://www.facebook.com/vfscfood" target="_blank">Chuỗi an toàn thực phẩm Việt Nam</a></li>
                            </ul>
                        </li>
						<?php 
						if(count($post_category)>0){
							?>
						<li class="item-has-children"><a data-toggle="collapse" href="#mainmenuid3" role="button" aria-expanded="false" aria-controls="mainmenuid3"><span>Tin tức</span> <i class="fas fa-angle-down"></i></a>
                            <ul class="submenu collapse" id="mainmenuid3">
								<?php
								for($i =0; $i<count($post_category); $i++)
								{
									
								?>
                                <li><a href="<?php echo URL;?>blog/<?php echo $appSession->getTool()->validUrl($post_category[$i][1]);?>/<?php echo $post_category[$i][0];?>"><?php echo $post_category[$i][1];?></a></li>
								<?php
								}
								?>
                            </ul>
                        </li>
						<?php
						}
						?>
							<li><a href="<?php echo URL; ?>contact"><?php echo $appSession->getLang()->find("Contact Us");?></a></li>
                        </ul>
                        <ul class="menu-action d-none d-lg-block">
                            <li class="cart-option">
                                <a onClick="cartopen()" href="#">
                                    <span class="cart-icon">
                                    <i class="fas fa-shopping-cart"></i>
                                    <span class="count" id="itemCountCard">0</span>
                                    </span> 
                                <span class="cart-amount" id="totalPriceCard"></span></a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- header section end -->


    <div class="page-layout">
        <div class="catagory-sidebar-area">
            <div class="catagory-sidebar-area-inner">
                <div class="catagory-sidebar all-catagory-option">
                    <ul class="catagory-submenu">
                        <?php
                        for ($i = 0; $i < $dt_category->getRowCount(); $i++) {
                            $id = $dt_category->getString($i, "id");
                            $parent_id = $dt_category->getString($i, "parent_id");
                            if ($parent_id == "") {
                                $category_code = $dt_category->getString($i, "code");
                                $category_name = $dt_category->getString($i, "name_lg");
                                if ($category_name == "") {
                                    $category_name = $dt_category->getString($i, "name");
                                }
                        ?>
                        <li><a data-toggle="collapse" href="#catagory-widget<?php echo $i ?>" role="button"
                                aria-expanded="false"
                                aria-controls="catagory-widget<?php echo $i ?>"><?php echo $category_name; ?><i
                                    class="fas fa-angle-down"></i></a>
                            <ul class="catagory-submenu collapse <?php if ($i == 1) { ?> show<?php } ?>"
                                id="catagory-widget<?php echo $i ?>">
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
                            }
                        }
                        ?>
						
                    </ul>
                </div>
            </div>
        </div>
        <div class="main-content-area">
            
            <?php
            include(ABSPATH . "addons/" . $page . '/index.php');
            ?>

            <!-- footer section -->
            <footer class="footer">
                <div class="container">
                    <div class="footer-top">
                        <div class="row">
                            <div class="col-md-6 col-lg-3">
                                <div class="footer-widget">
                                    <a href="<?php echo URL; ?>" class="logo"><img
                                            src="<?php echo URL; ?>assets/images/logo.png" alt="<?php echo $page_title;?>"></a>
                                    <p style="margin-top:5px">
								  <h3>CÔNG TY CỔ PHẦN VIFOTEC</h3><br>
										Địa chỉ: <b><br>- Tầng 1, số 130 Nguyễn Đức Cảnh, phường Tương Mai, quận Hoàng Mai, TP. Hà Nội, VN<br>
										- 489 Hoàng Quốc Việt, Cổ Nhuế, Cầu Giấy, Hà Nội</b><br>
										Số điện thoại: <b>0243 202 9199 - 0935 386 788</b><br>
										Địa chỉ Email: <b>info@vifotec.com</b><br>
										Mã số thuế: <b>0109385090</b><br>
										Số tài khoản: <b>3666699999 - Ngân hàng TMCP Ngoại thương Việt Nam - Chi nhánh Nam Hà Nội</b><br>
										Zalo: <a href="https://zalo.me/0935386788" target="_blank">0935386788</a>
										
                                    </p>
                                    <ul class="social-media-list d-flex flex-wrap">
                                        <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                                        <li><a href="#"><i class="fab fa-twitter"></i></a></li>
                                        <li><a href="#"><i class="fab fa-vimeo-v"></i></a></li>
                                        <li><a href="#"><i class="fab fa-pinterest-p"></i></a></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-3">
                                <div class="footer-widget">
                                    <h5 class="footer-title">Chính sách</h5>
                                    <div class="widget-wrapper">
                                        <ul>
											 <li><a href="<?php echo URL; ?>sale_support">Cam kết dịch vụ</a></li>
											<li><a href="<?php echo URL; ?>chinh_sach">Chính sách bán hàng</a></li>
											<li><a href="<?php echo URL; ?>sale_payment">Hình thức và chính sách thanh toán</a></li>
											<li><a href="<?php echo URL; ?>sale_refund">Chính sách đổi trả hàng</a></li>
											<li><a href="<?php echo URL; ?>sale_shipping">Chính sách giao hàng</a></li>
								
                                          
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-3">
                                <div class="footer-widget">
                                    <h5 class="footer-title"><?php echo $appSession->getLang()->find("About Us");?></h5>
                                    <div class="widget-wrapper">
                                        <ul>
                                            <li><a href="https://vifotec.com/" target="_blank">Giới thiệu</a></li>
											<li><a href="<?php echo URL;?>assets/VFT.pdf" target="_blank">Catalog </a></li>
											<li><a href="<?php echo URL;?>assets/HO_SO_NANG_LUC_CONG_TY_VIFOTEC.pdf" target="_blank">Hồ sơ năng lực của công ty</a></li>
                                            <li><a href="<?php echo URL; ?>product"><?php echo $appSession->getLang()->find("Products");?></a></li>
                                            <li><a href="<?php echo URL; ?>faq"><?php echo $appSession->getLang()->find("FAQ");?></a></li>
                                            <li><a href="<?php echo URL; ?>contact"><?php echo $appSession->getLang()->find("Contact");?></a></li>
											
											<li><a href="https://www.facebook.com/vfscfood" target="_blank">Chuỗi an toàn thực phẩm Việt Nam</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-3">
                                <div class="footer-widget">
                                    <h5 class="footer-title">Tải ứng dụng</h5>
									<div class="widget-wrapper">
									<img src="<?php echo URL;?>assets/images/download.png" width="80"/>
									</div>
                                    <div class="widget-wrapper">
                                        <div class="apps-store">
                                            <a href="https://apps.apple.com/vn/app/fosacha-food/id1641123976?l=vi" target="_blank"><img src="<?php echo URL; ?>assets/images/apple.png"
                                                    alt="app"></a>
                                            <a href="https://play.google.com/store/apps/details?id=fosacha.vn.shop" target="_blank"><img src="<?php echo URL; ?>assets/images/google.png"
                                                    alt="app"></a>
                                        </div>
                                        <div class="payment-method d-flex flex-wrap">
                                            <a href="#"><img src="<?php echo URL; ?>assets/images/visa.png"
                                                    alt="payment"></a>
                                            <a href="#"><img src="<?php echo URL; ?>assets/images/paypal.png"
                                                    alt="payment"></a>
                                            <a href="#"><img src="<?php echo URL; ?>assets/images/master.png"
                                                    alt="payment"></a>
                                            <a href="#"><img src="<?php echo URL; ?>assets/images/discover.png"
                                                    alt="payment"></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="footer-bottom">
                        <div class="row">
                            <div class="col-md-6 text-center text-md-left mb-3 mb-md-0">
                                <p class="copyright">Copyright &copy; 2021 <a href="">FOSACHA</a>. All Rights Reserved.
                                </p>
                            </div>

                            <div class="col-md-6 d-flex justify-content-center justify-content-md-end">
                                <ul class="footer-menu d-flex flex-wrap">
                                    <li><a href="#">Privacy policies Terms and Conditions Agreement</a></li>
                                
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
            <!-- footer section -->
        </div>
    </div>
	<!-- menu modal -->
     <div class="modal fade" id="menu-id" tabindex="-1" aria-labelledby="menu-id" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <ul class="menu d-xl-flex flex-wrap pl-0 list-unstyled">
                       <li><a href="<?php echo URL;?>"><?php echo $appSession->getLang()->find("Home");?></a></li>
                      <?php 
					if($appSession->getUserInfo()->getId() == "")
					{
					?>
					<li><a href="<?php echo URL;?>signin"><?php echo $appSession->getLang()->find("Sign In");?></a></li>
					<li><a href="<?php echo URL;?>signup"><?php echo $appSession->getLang()->find("Sign Up");?></a></li>
					
					<?php
					}else{
					?>
					<li><a href="<?php echo URL;?>account/?type=profile"><?php echo $appSession->getLang()->find("Profile");?></a></li>
					
					<?php
					}
					?>
					<li><a href="<?php echo URL;?>account/?type=order"><?php echo $appSession->getLang()->find("Orders");?></a></li>
					<?php 
					if($appSession->getUserInfo()->getId() != "")
					{
					?>
					<li><a href="javascript:doLogout()"><?php echo $appSession->getLang()->find("Sign Out");?></a></li>
					<?php
					}
					?>
					   <li><a href="<?php echo URL; ?>post"><?php echo $appSession->getLang()->find("News");?></a></li>
						<li><a href="<?php echo URL; ?>contact"><?php echo $appSession->getLang()->find("Contact Us");?></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    
    <!-- mobile-footer -->
    <div class="mobile-footer d-flex justify-content-between align-items-center d-xl-none" style="z-index:3000">
        <a class="info" href="<?php URL;?>"><i class="fas fa-home"></i></a>

        <div class="footer-cart">
            <a onClick="cartopen()" href="#" class="d-flex align-items-center"><span class="cart-icon"><i class="fas fa-shopping-cart"></i><span class="count" id="itemCountMobile">0</span></span> <span class="cart-amount ml-2" id="totalPriceMobile"></span></a>
        </div>

        <div class="footer-admin-area">
            <!-- <span class="user-admin">
                <i class="fas fa-user"></i>
            </span> -->
            <a class="user-admin" href="<?php echo URL;?>account" ><i class="fas fa-user"></i></a>
        </div>
    </div>
    <!-- mobile-footer -->
	
     <div id="pnFullDialog" class="modal" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" id="closeFullDialog" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					 <h4 class="modal-title" id="pnFullTitle"></h4> 
				</div>
				<div class="modal-body" >
					<div id="pnFullDialogContent">
					</div>
				</div>
				
			</div>
		</div>
	</div>


<a href="#top-page" class="to-top js-scroll-trigger"><span><i class="fas fa-arrow-up"></i></span></a>
    
    <script src='<?php echo URL; ?>assets/js/bootstrap.bundle.min.js'></script>
    <script src='<?php echo URL; ?>assets/js/swiper.min.js'></script>
    <script src="<?php echo URL; ?>assets/js/slick.js"></script>
    <script src='<?php echo URL; ?>assets/js/jquery-easeing.min.js'></script>
    <script src='<?php echo URL; ?>assets/js/scroll-nav.js'></script>
    <script src="<?php echo URL; ?>assets/js/jquery.elevatezoom.js"></script>
    <script src='<?php echo URL; ?>assets/js/price-range.js'></script>
    <script src='<?php echo URL; ?>assets/js/custom-select.js'></script>
    <script src='<?php echo URL; ?>assets/js/multi-countdown.js'></script>
    <script src='<?php echo URL; ?>assets/js/fly-cart.js'></script>
    <script src='<?php echo URL; ?>assets/js/theia-sticky-sidebar.js'></script>
    <script src='<?php echo URL; ?>assets/js/functions.js?v=1'></script>
	<script>
    var BASE_URL = '<?php echo URL; ?>';

    

    function doLoginAccount(user_name, password, oncompleted) {
		
        var _url = BASE_URL + "api/account/?ac=login_web";
        _url = _url + "&user=" + encodeURIComponent(user_name);
        _url = _url + "&password=" + encodeURIComponent(password);
		console.log(_url);
        loadPage('contentView', _url, function(status, message) {
            if (status == 0) {
                if (oncompleted != null) {
                    oncompleted(status, message);
                }
            } else {
                console.log(message);
            }

        }, true);
    }

    function doLogout() {
        var _url = BASE_URL + "api/account/?ac=logout";

        loadPage('contentView', _url, function(status, message) {
            if (status == 0) {
                document.location.reload();
            } else {
                console.log(message);
            }

        }, true);
    }

	function loadCard()
	{
		var _url =BASE_URL + "api/sale_action/?ac=viewCardCount";
		
		loadPage('contentView', _url, function(status, message)
		{
			
			if(status== 0)
			{
				var index = message.indexOf(';');
				if(index != -1)
				{
					var ctr = document.getElementById('sitebar-drawar');
					
					var itemCount = message.substring(0, index);
					var totalPrice = message.substring(index + 1);
					if(itemCount == "0")
					{
						ctr.style.display = "none";
					}else{
						ctr.style.display = "block";
					}
					ctr = document.getElementById('itemCount');
					if(ctr != null)
					{
						ctr.innerHTML = itemCount;
					}
					ctr = document.getElementById('itemCountMobile');
					if(ctr != null)
					{
						ctr.innerHTML = itemCount;
					}
					ctr = document.getElementById('totalPrice');
					if(ctr != null)
					{
						ctr.innerHTML = totalPrice;
					}
					ctr = document.getElementById('totalPriceMobile');
					if(ctr != null)
					{
						ctr.innerHTML = totalPrice;
					}
					
					ctr = document.getElementById('itemCountCard');
					if(ctr != null)
					{
						ctr.innerHTML = itemCount;
					}
					ctr = document.getElementById('totalPriceCard');
					if(ctr != null)
					{
						if(itemCount == "0")
						{
							ctr.innerHTML = "";
						}else{
							ctr.innerHTML = totalPrice;
						}
						
					}
				}
			}
			
		}, true);	
		
	}
		
    function addProduct(product_id, currency_id, unit_id, attribute_id, quantity, unit_price, second_unit_id, factor, description, company_id, price_id, type_id, oncompleted) {
		if(factor == "" || factor == "0")
		{
			factor = 1;
		}
        var _url = BASE_URL + "api/sale_action/?ac=addProduct";
        _url = _url + "&product_id=" + product_id;
        _url = _url + "&currency_id=" + currency_id;
        _url = _url + "&unit_id=" + unit_id;
		_url = _url + "&attribute_id=" + attribute_id;
        _url = _url + "&quantity=" + quantity;
        _url = _url + "&unit_price=" + encodeURIComponent(unit_price);
		_url = _url + "&second_unit_id=" + second_unit_id;
		_url = _url + "&factor=" + factor;
        _url = _url + "&description=" + encodeURIComponent(description);
		_url = _url + "&company_id=" + company_id;
		_url = _url + "&rel_id=" + price_id;
		_url = _url + "&type_id=" + type_id;
		
        loadPage('contentView', _url, function(status, message) {
            if (status == 0) {
                if (message.length == 36) {
                    if (oncompleted != null) {
                        oncompleted(status, message);
						
                    }
					loadCardContent();
                } else {
                    console.log(message);
                }

            }

        }, true);
    }

    function removeCard(id, oncompleted) {
		var result = confirm('<?php echo $appSession->getLang()->find("Are you sure to remove?");?>');
		if (!result) {
			return;
		}
				var _url = BASE_URL + "api/sale_action/?ac=removeCard";
        _url = _url + "&id=" + id;
		
        loadPage('contentView', _url, function(status, message) {
            if (status == 0) {
                if (message.length == 36) {
                    if (oncompleted != null) {
                        oncompleted(status, message);
                    }
                } else {
                    console.log(message);
                }

            }

        }, true);
    }

    function updateCard(id, quantity, oncompleted) {
        var _url = BASE_URL + "api/product/?ac=sale_product_update_quantity";
        _url = _url + "&id=" + id + "&quantity=" + quantity;
		
        loadPage('contentView', _url, function(status, message) {
            if (status == 0) {
                if (message.length == 36) {
                    if (oncompleted != null) {
                        oncompleted(status, message);
                    }
                } else {
                    console.log(message);
                }

            }

        }, true);
    }

    function loadWishList() {
        var _url = BASE_URL + "api/sale_action/?ac=viewWishListCount";

        loadPage('contentView', _url, function(status, message) {

            if (status == 0) {

            }

        }, true);

    }

    function addToWishList(product_id) {
        var _url = BASE_URL + "api/sale_action/?ac=addToWishList";
        _url = _url + "&product_id=" + product_id;
        loadPage('contentView', _url, function(status, message) {
            if (status == 0) {
                if (message.length == 36) {
                    loadWishList();
                } else {
                    console.log(message);
                }

            }

        }, true);
    }

    function removeToWishList(product_id) {
        var _url = BASE_URL + "api/sale_action/?ac=removeToWishList";
        _url = _url + "&product_id=" + product_id;
        loadPage('contentView', _url, function(status, message) {
            if (status == 0) {
                if (message.length == 36) {
                    document.location.reload();
                } else {
                    console.log(message);
                }

            }

        }, true);
    }

    function sendEmailContact(name, phone, email, title, message, oncompleted) {
        var _url = BASE_URL + "api/contact/?ac=send";
        _url = _url + "&name=" + encodeURIComponent(name);
        _url = _url + "&phone=" + encodeURIComponent(phone);
        _url = _url + "&email=" + encodeURIComponent(email);
        _url = _url + "&title=" + encodeURIComponent(title);
        _url = _url + "&message=" + encodeURIComponent(message);
		
        loadPage('contentView', _url, function(status, message) {
            if (status == 0) {
                if (message.indexOf("OK") != -1) {
                    if (oncompleted != null) {
                        oncompleted(status, message);
                    }
                } else {
                    console.log(message);
                }
            }
        }, true);
    }
	function loadCardContent()
	{
		var _url = BASE_URL + "addons/card/";
        loadPage('sitebar-cart', _url, function(status, message) {
            if (status == 0) {
                
            }
        }, false);
	}
	function cartopen() {
		
		var _url = BASE_URL + "addons/card/";
        loadPage('sitebar-cart', _url, function(status, message) {
            if (status == 0) {
                document.getElementById("sitebar-cart").classList.add('open-cart');
				document.getElementById("sitebar-drawar").classList.add('hide-drawer');
            }
        }, false);
		
		
		
	}

	function cartclose() {
		document.getElementById("sitebar-cart").classList.remove('open-cart');
		document.getElementById("sitebar-drawar").classList.remove('hide-drawer');
	}

	function openModalProduct(id)
	{
		
		var _url = '<?php echo URL;?>addons/product_popup/?id=' + id;
		openPopup(_url);
	}
	function openPopup(_url, oncompleted)
	{
	
		loadPage('pnFullDialogContent', _url, function(status, message)
		{
			if(status== 0)
			{
				$("#pnFullDialog").modal();
				 if (oncompleted != null) {
                        oncompleted(status, message);
                    }
					
			}
			
		}, false);
	}
	function closePopup()
	{
		var p = document.getElementById('closeFullDialog');
		p.click();
	}
	function doSearch()
	{
		var ctr = document.getElementById('editsearch-category');
		var category = ctr.value;
		ctr = document.getElementById('editsearch');
		var search = ctr.value;
		var _url = "<?php echo URL;?>";
		if(category != "")
		{
			_url = _url  + category + "/?search=" + encodeURIComponent(search);
		}else{
			_url = _url + "?search=" + encodeURIComponent(search);
		}
		document.location.href= _url;
	}
	loadCard();
    </script>
	

</body>
</html>