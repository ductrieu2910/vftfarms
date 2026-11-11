<?php
$sql = "SELECT name FROM post_category WHERE id='".$post_category_id."'";
$post_category_name = "";
for($i =0; $i<count($post_category); $i++)
{
	if($post_category[$i][0] == $post_category_id)
	{
		$post_category_name = $post_category[$i][1];
	}
}
$sql = "SELECT d1.id, d1.name, d1.create_date, p.document_id, d1.description FROM post d1 LEFT OUTER JOIN poster p ON(d1.id = p.rel_id AND p.status =0 AND p.publish=1) WHERE d1.publish =1 AND d1.status =0 AND d1.category_id='".$post_category_id."' ORDER BY d1.create_date DESC";
$msg->add("query", $sql);
$dt_post = $appSession->getTier()->getTable($msg);
 
?>
<!-- page-header-section start -->
<div class="page-header-section">
    <div class="container">
        <div class="row">
            <div class="col-12 d-flex justify-content-between justify-content-md-end">
                <ul class="breadcrumb">
                    <li><a href="<?php echo URL; ?>">Trang chủ </a></li>
                    <li><span>/</span></li>
                    <li><?php echo $post_category_name;?></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- page-header-section end -->
<!-- about section start -->
<section class="blog-section section-ptb">
	<div class="container">
		<div class="row align-items-center">
		<?php
		for($i =0; $i<$dt_post->getRowCount(); $i++)
		{
			$create_date = $dt_post->getString($i, "create_date");
			$document_id = $dt_post->getString($i, "document_id");
			$create_date = date("d/m/y", strtotime($create_date) );
		?>
			<div class="col-md-6 col-lg-4 mb--30">
				<div class="post-item">
					<div class="post-thumb">
						<a href="<?php echo URL;?>post/<?php echo $dt_post->getString($i, "id");?>"><img src="<?php echo IMAGE_URL; ?>document/?id=<?php echo $document_id;?>" alt="thumb"></a>
					</div>
					<div class="post-content border-effect">
						<ul class="meta-post list-unstyled pl-0 d-flex justify-content-between">
							<li>
								<span class="icon"><i class="far fa-clock"></i></span>
								<span class="meta-content"><?php echo $create_date;?></span>
							</li>
							
						</ul>
						<h5 class="title mb-3"><a href="<?php echo URL;?>post/<?php echo $dt_post->getString($i, "id");?>"><?php echo $dt_post->getString($i, "name");?></a></h5>
						
						<?php echo $dt_post->getString($i, "description");?>
						<br>
						<a href="<?php echo URL;?>post/<?php echo $dt_post->getString($i, "id");?>" class="blog-btn">Chi tiết</a>
					</div>
				</div>
			</div>
		<?php
		}
		?>
		</div>
	</div>
</section>
<!-- about section end -->