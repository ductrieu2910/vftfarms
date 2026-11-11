<div class="row">
	<div class="col m12" id="pncomment_<?php echo $name;?>">
	</div>
</div>
<script>
	function viewComment(parent_id)
	{
		var _url = '<?php echo URL;?>admin/module/comment/?rel_id=<?php echo $value;?>&name=<?php echo $name;?>&parent_id=' + parent_id;
		loadPage('pncomment_<?php echo $name;?>', _url, function(status, message)
		{
		}, false);
	}
	viewComment('');
	
</script>
