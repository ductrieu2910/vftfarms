<?php
$position = strpos($uri, "?"); 
$PARAMS = "";
if($position == true)
{
	$PARAMS = substr($uri, $position + 1);
	$uri = substr($uri, 0, $position);
}
$page = "home";
$lang = "vi";
$page_id = '';
if($uri !='/' && $uri != '')
{
	$items = explode("/", $uri);
	if(count($items) ==1)
	{
		$page = $items[0];
	}
	if(count($items)>0)
	{
		$page_id = $items[count($items)-1];
	}
}

$session_id = "";
if(isset($_REQUEST['session_id']))
{
	$session_id = $_REQUEST['session_id'];
}
$survey_id = "";
if(isset($_REQUEST['id']))
{
	$survey_id = $_REQUEST['id'];
}
$user_id = "";
if(isset($_REQUEST['user_id']))
{
	$user_id = $_REQUEST['user_id'];
}
$rel_id = "";
if(isset($_REQUEST['rel_id']))
{
	$rel_id = $_REQUEST['rel_id'];
}
$company_code = "";
$description = "";
if(isset($_REQUEST['description']))
{
	$description = $_REQUEST['description'];
}
$msg = $appSession->getTier()->createMessage();


$sql = "SELECT d1.id, d1.company_id FROM survey d1 WHERE d1.id='".$survey_id."' AND d1.status =0";
$msg->add("query", $sql);

$surveys = $appSession->getTier()->getArray($msg);


?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title><?php echo META_TITLE;?></title>
<meta http-equiv="x-ua-compatible" content="ie=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="shortcut icon" href="<?php echo URL;?>favicon.ico" type="image/png"/>
<link href="https://fonts.googleapis.com/css?family=Lato:300,400,400i,700|Poppins:300,400,500,600,700|PT+Serif:400,400i&display=swap" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="<?php echo URL;?>survey/assets/css/bootstrap.css" type="text/css" />
<link rel="stylesheet" href="<?php echo URL;?>survey/assets/css/style.css" type="text/css" />
<link rel="stylesheet" href="<?php echo URL;?>survey/assets/css/dark.css" type="text/css" />
<link rel="stylesheet" href="<?php echo URL;?>survey/assets/css/font-icons.css" type="text/css" />
<link rel="stylesheet" href="<?php echo URL;?>survey/assets/css/animate.css" type="text/css" />
<link rel="stylesheet" href="<?php echo URL;?>survey/assets/css/magnific-popup.css" type="text/css" />

<link rel="stylesheet" href="<?php echo URL;?>assets/css/custom.css" type="text/css" />

<script src="<?php echo URL;?>survey/assets/js/jquery.js"></script>
<script src="<?php echo URL;?>survey/assets/js/controller.js"></script>
<script src="<?php echo URL;?>survey/assets/js/tool.js"></script>
<script src="<?php echo URL;?>survey/assets/js/back_to_top.js"></script>
<style>
@media (max-width: 992px) {
	.blocks-faqs-3 .sticky-section { position: relative !important; }
}

.blocks-faqs-3 .faqs .toggle {
	background-color: #FFF;
	border-radius: 3px;
	box-shadow: 0 2px 3px rgba(0, 0, 0, 0.085);
}

.blocks-faqs-3 .faqs .toggle-header {
	padding: 15px 20px;
	margin: 0;
}

.blocks-faqs-3 .faqs .toggle-content {
	border-top: 1px solid #EEE;
	padding: 20px 25px;

</style>
</head>
<body>
<?php

if(count($surveys) == 0)
{
	echo $appSession->getLang()->find("Survey code is invalid");
}else
{
	$survey_id = $surveys[0][0];
	$company_id = $surveys[0][1];
	$lang_id = "";
	
	
	$sql = "SELECT d1.id, d1.lang_id FROM survey_log d1 WHERE d1.rel_id='".$appSession->getTool()->replace($session_id, "'", "''")."' AND d1.survey_id='".$survey_id."'";
	$msg->add("query", $sql);
	$sessions = $appSession->getTier()->getArray($msg);
	$survey_log_id = $appSession->getTool()->getId();
	if(count($sessions) == 0)
	{
		$sql = "SELECT lang_id FROM res_lang_rel WHERE rel_id='".$survey_id."' AND status=0 ORDER BY sequence ASC LIMIT 1";
		$msg->add("query", $sql);
		
		$lang_id = $appSession->getTier()->getValue($msg);
		
		
		
		$sql = "INSERT INTO survey_log(id, create_date, write_date, status, company_id, survey_id, rel_id, lang_id, description, proccess, receipt_no, receipt_date) VALUES(";
		$sql = $sql."'".$survey_log_id."'";
		$sql = $sql.", ".$appSession->getTier()->getDateString();
		$sql = $sql.", ".$appSession->getTier()->getDateString();
		$sql = $sql.", 0";
		$sql = $sql.", '".$company_id."'";
		$sql = $sql.", '".$survey_id."'";
		$sql = $sql.", '".$session_id."'";
		$sql = $sql.", '".$lang_id."'";
		$sql = $sql.", '".$appSession->getTool()->replace($description, "'", "''")."'";
		$sql = $sql.", 0";
		$sql = $sql.", '".$appSession->getTool()->findReceiptNo($appSession->getTier(), COMPANY_ID, "survey_log")."'";
		$sql = $sql.", ".$appSession->getTier()->getDateString();
		$sql = $sql.")";
		$msg->add("query", $sql);
		$appSession->getTier()->exec($msg);
		
	}else
	{
		$survey_log_id = $sessions[0][0];
		$lang_id = $sessions[0][1];
		
		
	}
	if($lang_id  == "")
	{
		$sql = "SELECT lang_id FROM res_lang_rel WHERE rel_id='".$survey_id."' AND status=0 ORDER BY sequence ASC LIMIT 1";
		$msg->add("query", $sql);
		$lang_id = $appSession->getTier()->getValue($msg);
	}
	if($lang_id  == "")
	{
		$lang_id = LANGUAGE;
	}
	if(isset($_REQUEST['lang_id']))
	{
		$lang_id = $_REQUEST['lang_id'];
		$sql = "UPDATE survey_log SET lang_id='".$lang_id."' WHERE id='".$survey_log_id."'";
		$msg->add("query", $sql);
		$appSession->getTier()->exec($msg);
		
	}
	
	$sql = "SELECT d1.lang_id, d2.name, lg.description FROM res_lang_rel d1 LEFT OUTER JOIN res_lang d2 ON(d1.lang_id= d2.id) LEFT OUTER JOIN res_lang_line lg ON(d2.id = lg.rel_id AND lg.name='name' AND lg.lang_id='".$lang_id."') WHERE d1.rel_id='".$survey_id."'";
	$msg->add("query", $sql);
	
	$langs = $appSession->getTier()->getArray($msg);
	
	?>
	<body class="stretched">

	<!-- Document Wrapper
	============================================= -->
	<div id="wrapper" class="clearfix">

		<!-- Content
		============================================= -->
		<section id="content">
			<div class="content-wrap">
				<div class="container clearfix">
					<div class="row justify-content-center">

						<div class="col-lg-8 col-xl-7">
							<div class="p-5 rounded-xxl shadow-lg">
								<div class="d-flex flex-column flex-sm-row mb-8">
									
									<div class="ml-sm-auto align-bottom">
										<h3>
										<?php 
										$hasLang = false;
										$lang_name = "";
										for($l =0; $l<count($langs); $l++)
										{
											if($langs[$l][0] == $lang_id)
											{
												$lang_name = $langs[$l][2];
												if($lang_name == "")
												{
													
													$lang_name = $langs[$l][1];
												}
												$hasLang = true;
												break;
											}
										?>
										<?php
										}
										if(count($langs)>0 && $hasLang == false)
										{
											$lang_id = $langs[0][0];
											$lang_name = $langs[0][2];
											if($lang_name == "")
											{
												
												$lang_name = $langs[0][1];
											}
										}
										?>
										
										
										<?php
										
										for($l =0; $l<count($langs); $l++)
										{
											
												$lang_name = $langs[$l][2];
												if($lang_name == "")
												{
													
													$lang_name = $langs[$l][1];
												}
												if($l>0)
												{
												?>
												<small class="text-smaller text-black-50"> / </small>
												<?php
												}
												?>
												<?php
												if($langs[$l][0] != $lang_id)
												{
												?>
												<small class="text-smaller text-black-50"> 
												<?php
												}
												?>
												<a href="<?php echo URL;?>?survey/session_id=<?php echo $session_id;?>&id=<?php echo $survey_id;?>&company_code=<?php echo $company_code;?>&lang_id=<?php echo $langs[$l][0];?>"><?php echo $lang_name;?></a>
												<?php
												if($langs[$l][0] != $lang_id)
												{
												?>
												</small>
												<?php
												}
												?>
												
												<?php
											
										}
										?>
										
										</h3>
									</div>
								</div>
								<hr>
								<?php
								$log_line_id = "";
								
								if(isset($_REQUEST['log_line_id']))
								{
									$log_line_id = $_REQUEST['log_line_id'];
								}else
								{
									$sql = "SELECT id FROM survey_log_line WHERE status =0 AND log_id='".$survey_log_id."' AND proccess =0";
									$msg->add("query", $sql);
									$log_lines = $appSession->getTier()->getArray($msg);
									if(count($log_lines)>0)
									{
										$log_line_id = $log_lines[0][0];
									}
								}
								$sql = "SELECT d1.id, d2.description, lg.description FROM survey_log d1 LEFT OUTER JOIN survey d2 ON(d1.survey_id = d2.id) LEFT OUTER JOIN res_lang_line lg ON(d2.id = lg.rel_id AND lg.name='description' AND lg.lang_id='".$lang_id."') WHERE d1.status =0 AND d1.id='".$survey_log_id."' AND d1.proccess =1";
								$msg->add("query", $sql);
								$logs = $appSession->getTier()->getArray($msg);
								
								if(count($logs)>0)
								{
									$description = $logs[0][2];
									if($description == "")
									{
										$description = $logs[0][1];
									}
									echo $description;
									
									$sql = "SELECT d2.code, d4.name, lg.description FROM voucher_log d1 LEFT OUTER JOIN voucher_line d2 ON(d1.voucher_line_id = d2.id) LEFT OUTER JOIN voucher d3 ON(d2.voucher_id = d3.id) LEFT OUTER JOIN voucher_category d4 ON(d3.category_id = d4.id) LEFT OUTER JOIN res_lang_line lg ON(d4.id = lg.rel_id AND lg.name='description' AND lg.lang_id='".$lang_id."') WHERE d1.status =0 AND d1.rel_id='".$survey_log_id."'";
									
									$msg->add("query", $sql);
									$vouchers = $appSession->getTier()->getArray($msg);
									for($i=0; $i<count($vouchers); $i++)
									{
										$promotion_name = $vouchers[$i][2];
										if($promotion_name == "")
										{
											$promotion_name = $vouchers[$i][1];
										}
										$promotion_code = $vouchers[$i][0];
										echo "<hr>";
										echo $promotion_name.": <b>".$promotion_code."</b>";
									}
									echo "HOÀN THANH";
									
								
								}else
								{
									
									if($log_line_id == "")
									{
										
										$sql = "SELECT  d1.name, lg.description FROM survey d1 LEFT OUTER JOIN res_lang_line lg ON(d1.id = lg.rel_id AND lg.name='name' AND lg.lang_id='".$lang_id."') WHERE d1.id='".$survey_id."'";
										$msg->add("query", $sql);
										$surveys = $appSession->getTier()->getArray($msg);
										$survey_name = "";
										$numbers_question = 0;
										if(count($surveys)>0)
										{
											$survey_name = $surveys[0][1];
											if($survey_name == "")
											{
												$survey_name =$surveys[0][0];
											}
											
										}
										echo $survey_name;
										
										
										
										
									
									?>
									<hr>
									<input type="button" id="btnDone" name="btnDone" onclick="findQuestion('');" class="button button-3d" value="<?php echo $appSession->getLang()->find("Start");?>">
									
									<?php
									}else
									{
										$sql = "SELECT  d1.id, d2.name, lg.description FROM survey_line d1 LEFT OUTER JOIN survey_question d2 ON(d1.question_id = d2.id) LEFT OUTER JOIN res_lang_line lg ON(d2.id = lg.rel_id AND lg.name='name' AND lg.lang_id='".$lang_id."') WHERE d1.survey_id='".$survey_id."' AND d1.status =0 ORDER BY d1.sequence ASC, d1.create_date ASC";
										$msg->add("query", $sql);
										
										$questionList = $appSession->getTier()->getArray($msg);
										
										echo '<div id="faqs" class="faqs">';
										
										$sql = "SELECT d3.id AS question_id, d4.code AS type_code,  d3.name, lg.description, d3.description, lg1.description, d1.survey_line_id FROM survey_log_line d1 LEFT JOIN survey_line d2 ON(d1.survey_line_id = d2.id) LEFT OUTER JOIN survey_question d3 ON(d2.question_id = d3.id) LEFT OUTER JOIN survey_question_type d4 ON(d3.type_id = d4.id)";
										$sql = $sql." LEFT OUTER JOIN res_lang_line lg ON(d3.id = lg.rel_id AND lg.name='name' AND lg.lang_id='".$lang_id."')";
										$sql = $sql." LEFT OUTER JOIN res_lang_line lg1 ON(d3.id = lg1.rel_id AND lg1.name='description' AND lg1.lang_id='".$lang_id."')";
										
										$sql = $sql." WHERE d1.id ='".$log_line_id."'";
										$msg->add("query", $sql);
										$questions = $appSession->getTier()->getArray($msg);
										
										$question_numbers = count($questionList);
										$survey_line_id = "";
										if(count($questions)>0)
										{
											$question_id = $questions[0][0];
											$question_type = $questions[0][1];
											$question_name = $questions[0][3];
											
											
											if($question_name == "")
											{
												$question_name = $questions[0][2];
											}
											
											$question_description = $questions[0][5];
											if($question_description == "")
											{
												$question_description = $questions[0][4];
											}
											$survey_line_id = $questions[0][6];
											
											$sql = "SELECT d1.id, d1.name, lg.description, d1.is_description FROM survey_question_line d1";
											$sql = $sql." LEFT OUTER JOIN res_lang_line lg ON(d1.id = lg.rel_id AND lg.name='name' AND lg.lang_id='".$lang_id."')";
											$sql = $sql." WHERE d1.status =0 AND d1.question_id='".$question_id."'";
											$sql = $sql." ORDER BY d1.sequence ASC, d1.create_date ASC";
											$msg->add("query", $sql);
											
											
											$question_lines = $appSession->getTier()->getArray($msg);
											
											
											$sql = "SELECT d1.question_line_id, d1.description FROM survey_log_line_response d1";
											$sql = $sql."  WHERE d1.log_line_id ='".$log_line_id."' AND d1.status =0";
											$msg->add("query", $sql);
											$responses = $appSession->getTier()->getArray($msg);
											$no = 0;
											for($i=0; $i<count($question_lines); $i++)
											{
												
												$question_line_id = $question_lines[$i][0];
												
											}
											for($q = 0; $q<count($questionList); $q++)
											{
												$no = $no + 1;
												if($questionList[$q][0] == $survey_line_id)
												{
													break;
												}
											}
											
											echo "<b>".$question_name."<b>";
											if($question_description != "")
											{
												echo "<br>";
												echo '<span style="font-size:10px"><i>('.$question_description.')</i></span>';
											}
											echo "<br><br>";
											
											
											
											for($i=0; $i<count($question_lines); $i++)
											{
												
												$question_line_id = $question_lines[$i][0];
												$question_line_name = $question_lines[$i][2];
												if($question_line_name == "")
												{
													$question_line_name = $question_lines[$i][1];
												}
												$is_description = $question_lines[$i][3];
												$checked = "";
												$response_description = "";
												for($j=0; $j<count($responses); $j++)
												{
													if($responses[$j][0] == $question_line_id)
													{
														$checked = " checked ";
														$response_description = $responses[$j][1];
													}
												}
												?>
												<?php
												if($question_type == "radio" || $question_type == "check")
												{
												?>
												<div class="toggle faq pb-0 mb-3">
													<div class="toggle-header">
														<div class="toggle-icon">
															<?php
															if($question_type == "radio")
															{
															?>
															<input <?php echo $checked;?> class="form-check-input" onchange="stateChanged(this, '<?php echo $log_line_id;?>')" type="radio" id="edit<?php echo $i;?>" name="question" value="<?php echo $question_line_id;?>">
															<?php
															}else{
															?>
															<input <?php echo $checked;?> class="form-check-input" type="checkbox" onchange="stateChanged(this, '<?php echo $log_line_id;?>')" id="edit<?php echo $i;?>" name="edit<?php echo $i;?>" value="<?php echo $question_line_id;?>">
															<?php
															}
															?>
														</div>
														<div class="toggle-title pl-1">
															<label  for="edit<?php echo $i;?>"><?php echo $question_line_name;?> </label>
														</div>
								
													</div>
													
												</div>
												<?php 
												if($is_description == "1")
												{
												?>
												<div class="toggle faq pb-0 mb-3" id="desc_<?php echo $question_line_id;?>" style="<?php if($checked == ""){?> display:none<?php } ?>">
													<textarea id="<?php echo $question_line_id;?>" onblur="updateDescription(this, '<?php echo $log_line_id;?>')" rows="2" maxlength="250" class="form-control"><?php echo $response_description;?></textarea>
												</div>
												<?php
												}
												?>
												<?php
												}else if($question_type == "text")
												{
												?>
												<div class="toggle faq pb-0 mb-3">
													<div class="toggle-header">
														<div class="toggle-icon">
															
															<label  for="edit<?php echo $i;?>"><?php echo $question_line_name;?>: </label>
														</div>
														<div class="toggle-title pl-1">
															<input id="<?php echo $question_line_id;?>" maxlength="250" type="text" class="form-control" onblur="stateChanged(this, '<?php echo $log_line_id;?>')" id="<?php echo $question_line_id;?>">
														</div>
													</div>
												</div>
												<?php
												}
												
											}
										}
										
										echo '</div>';
										$caption = $appSession->getLang()->find("Next");
										if($no == $question_numbers)
										{
											$caption = $appSession->getLang()->find("Completed");
										}
										?>
										<div class="row">
											<div class="col-6">
												<input type="button" id="btnDone" name="btnDone" onclick="findQuestion('<?php echo $log_line_id;?>');" class="button button-3d" value="<?php echo $caption;?>">
											</div>
											<div class="col-6" style="text-align:right">
												<div width="100%" >
												<?php echo $no;?> <?php echo $appSession->getLang()->find("of");?> <?php echo $question_numbers;?>
												</div>
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
			</div>
		</section><!-- #content end -->


	</div><!-- #wrapper end -->

	<!-- Go To Top
	============================================= -->
	<div id="gotoTop" class="icon-angle-up"></div>

	<!-- JavaScripts
	============================================= -->
	
	<script src="<?php echo URL;?>survey/assets/js/plugins.min.js"></script>

	<!-- Footer Scripts
	============================================= -->
	<script src="<?php echo URL;?>survey/assets/js/functions.js"></script>
	<script>
		function findQuestion(id)
		{
			if(id == "")
			{
				findId("");
			}else{
				var _url = '<?php echo URL?>survey/action/?ac=checkResponse&survey_id=<?php echo $survey_id;?>&log_id=<?php echo $survey_log_id;?>&lang_id=<?php echo $lang_id;?>&log_line_id=' + id;
		
				loadPage('contentView', _url, function(status, message)
				{
					if(status== 0)
					{
						if(message.indexOf("OK") != -1)
						{
							findId(id);
							
						}else if(message == "NONE")
						{
							alert('<?php echo $appSession->getLang()->find("Please check at least one option");?>');
						}else{
							alert(message);
						}
						
					}
					
				}, true);
			}
			
		}
		function findId(id)
		{
			var _url = '<?php echo URL?>survey/action/?ac=findQuestion&survey_id=<?php echo $survey_id;?>&log_id=<?php echo $survey_log_id;?>&lang_id=<?php echo $lang_id;?>&log_line_id=' + id;
		
			loadPage('contentView', _url, function(status, message)
			{
				if(status== 0)
				{
					if(message == "DONE")
					{
						document.location.href='<?php echo URL;?>survey/?session_id=<?php echo $session_id;?>&id=<?php echo $survey_id;?>&company_code=<?php echo $company_code;?>'
						
					}else if(message.length == 36)
					{
						document.location.href='<?php echo URL;?>survey/?session_id=<?php echo $session_id;?>&id=<?php echo $survey_id;?>&company_code=<?php echo $company_code;?>&log_line_id=' + message;
					}else{
						alert(message);
					}
					
				}
				
			}, true);
		}
		function stateChanged(theInput, log_line_id)
		{
			var selected = 1;
			var description = "";
			var question_line_id = '';
			var type = theInput.type;
			if(theInput.type == 'radio' || theInput.type=='checkbox')
			{
				var ctr =document.getElementById('desc_' + theInput.value);
				
				if(theInput.checked)
				{
					selected = 0;
					if(ctr != null)
					{
						ctr.style.display="block";
					}
				}else{
					if(ctr != null)
					{
						ctr.style.display="none";
					}
				}
				question_line_id = theInput.value;
			}else
			{
				if(theInput.value != "")
				{
					selected = 0;
					
				}
				description = theInput.value;
				question_line_id = theInput.id;
			}
			var _url = '<?php echo URL?>survey/action/?ac=answer&survey_id=<?php echo $survey_id;?>&log_id=<?php echo $survey_log_id;?>&lang_id=<?php echo $lang_id;?>&question_line_id=' + question_line_id + '&log_line_id=' + log_line_id + '&status=' + selected;
			
			_url = _url + '&description=' + encodeURIComponent(description);
			_url = _url + '&type=' + type;
			
			loadPage('contentView', _url, function(status, message)
			{
				if(status== 0)
				{
					//alert(message);
				}
				
			}, true);
		}
		function updateDescription(theInput, log_line_id)
		{
			var selected = 0;
			var description = "";
			var question_line_id = '';
			description = theInput.value;
			question_line_id = theInput.id;
				
			var _url = '<?php echo URL?>survey/action/?ac=answer&survey_id=<?php echo $survey_id;?>&log_id=<?php echo $survey_log_id;?>&lang_id=<?php echo $lang_id;?>&question_line_id=' + question_line_id + '&log_line_id=' + log_line_id + '&status=' + selected;
			_url = _url + '&description=' + encodeURIComponent(description);
			
			loadPage('contentView', _url, function(status, message)
			{
				if(status== 0)
				{
				}
				
			}, true);
		}
		document.addEventListener('keypress', function(e) {
		 var keynum = e.keyCode||e.which;
			if(keynum == 13) {
			  var ctr = document.getElementById('btnDone');
			  
			  if(ctr != null)
			  {
				
				  ctr.onclick();
			  }
			}
		});
	</script>
	<?php
	
}
?>
</body>
</html>
