<?php
$sql = "SELECT name, content FROM post WHERE id='".$post_id."'";
$msg->add("query", $sql);
$dt_post = $appSession->getTier()->getTable($msg);

if($dt_post->getRowCount()>0)
{
 
?>
<!-- page-header-section start -->
<div class="page-header-section">
    <div class="container">
        <div class="row">
            <div class="col-12 d-flex justify-content-between justify-content-md-end">
                <ul class="breadcrumb">
                    <li><a href="<?php echo URL; ?>">Trang chủ </a></li>
                    <li><span>/</span></li>
                    <li><?php echo $dt_post->getString(0, "name");?></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- page-header-section end -->
<!-- about section start -->
<section class="blog-section section-ptb">
	<div class="container">
		 <div class="row">
			<div class="col-lg-9 main-content">
				<div class="entry-wrapper">
					<div class="entry-single">
					<?php
						echo $dt_post->getString(0, "content");
					?>
					</div>
				</div>
			</div>
			<div class="col-lg-3 sidebar blog-sidebar">
				<div class="widget">
					<h5 class="widget-title">Nhóm</h5>
					<div class="widget-wrapper1">
						<ul class="catagory-list list-unstyled">
							<?php
								for($i =0; $i<count($post_category); $i++)
								{
									
								?>
							<li><a class="d-flex align-items-center justify-content-between" href="<?php echo URL;?>blog/<?php echo $appSession->getTool()->validUrl($post_category[$i][1]);?>/<?php echo $post_category[$i][0];?>">
								<span class="text"><?php echo $post_category[$i][1];?></span>
								
							</a></li>
							<?php
								}
								?>
							
						</ul>
					</div>
				</div>
				
			</div>
		</div>
								
		
	</div>
</section>
<!-- about section end -->
<?php
}
?>