<!-- page-header-section start -->
<div class="page-header-section">
    <div class="container">
        <div class="row">
            <div class="col-12 d-flex justify-content-between justify-content-md-end">
                <ul class="breadcrumb">
                    <li><a href="<?php echo URL; ?>"><?php echo $appSession->getLang()->find("Home");?></a></li>
                    <li><span>/</span></li>
                    <li><?php echo $appSession->getLang()->find("Check Out");?></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- page-header-section end -->
<!-- Product order success section start -->
<section class="product-order-success-section section-ptb">
    <div class="container">
        <div class="product-order-success-container">
            <div class="product-order-success">
                <div class="iconimage">
                    <img src="<?php echo URL;?>assets/images/checkicon1.png" alt="icon">
                </div>
                <h2>Cảm ơn bạn đã mua hàng của FOSACHA!</h2>
                <p>Cảm ơn bạn đã mua hàng tại FOSACHA. Đơn hàng của bạn đã được ghi nhận, nhân viên tư vấn của chúng tôi sẽ liên hệ với bạn trong thời gian sớm nhất để xác nhận đơn hàng!</p>
                <a href="<?php echo URL; ?>" class="order-btn"><?php echo $appSession->getLang()->find("Continue Shop");?></a>
            </div>
            
        </div>
    </div>
</section>
<!-- Product order success section end -->