<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL)
*/
/**
	The MIT License (MIT)
	
	Copyright (c) 2015 Ignacio Nieto Carvajal
	
	Permission is hereby granted, free of charge, to any person obtaining a copy
	of this software and associated documentation files (the "Software"), to deal
	in the Software without restriction, including without limitation the rights
	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	copies of the Software, and to permit persons to whom the Software is
	furnished to do so, subject to the following conditions:
	
	The above copyright notice and this permission notice shall be included in
	all copies or substantial portions of the Software.
	
	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	THE SOFTWARE.
*/

// check if Creamy has been installed.
require_once('./php/CRMDefaults.php');
if (!file_exists(CRM_INSTALLED_FILE)) { // check if already installed 
	header("location: ./install.php");
	die();
}

// Try to get the authenticated user.
require_once('./php/Session.php');
try {
	$user = \creamy\CreamyUser::currentUser();	
} catch (\Exception $e) {
	header("location: ./logout.php");
	die();
}

//proper user redirects
if($user->getUserRole() != CRM_DEFAULTS_USER_ROLE_ADMIN){
	if($user->getUserRole() == CRM_DEFAULTS_USER_ROLE_AGENT){
		header("location: agent.php");
	}
}

// initialize session and DDBB handler
include_once('./php/UIHandler.php');
require_once('./php/LanguageHandler.php');
require_once('./php/DbHandler.php');
$ui = \creamy\UIHandler::getInstance();
$lh = \creamy\LanguageHandler::getInstance();
//$colors = $ui->generateStatisticsColors();

// calculate number of statistics and customers
$db = new \creamy\DbHandler();
$statsOk = $db->weHaveSomeValidStatistics();
$custsOk = $db->weHaveAtLeastOneCustomerOrContact();

?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Admin Dashboard</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
     
		<!--<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css" />-->
		
        <!-- Creamy style -->
        <link href="css/creamycrm_test.css" rel="stylesheet" type="text/css" />
        <?php print $ui->creamyThemeCSS(); ?>

        <!-- DATA TABLES -->
        <link href="css/datatables/dataTables.bootstrap.css" rel="stylesheet" type="text/css" />
		
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="js/html5shiv.js"></script>
          <script src="js/respond.min.js"></script>
        <![endif]-->

		<!-- javascript -->
        <script src="js/jquery.min.js"></script>
        <script src="js/bootstrap.min.js" type="text/javascript"></script>    
        <script src="js/jquery-ui.min.js" type="text/javascript"></script>
        <script src="js/jquery.validate.min.js" type="text/javascript"></script>
        
            <!-- dashboard status boxes -->
        <script src="js/bootstrap-editable.js" type="text/javascript"></script> 
        <script src="theme_dashboard/moment/min/moment-with-locales.min.js" type="text/javascript"></script>
        <script src="js/modules/now.js" type="text/javascript"></script>         
	    <!-- ChartJS 1.0.1 -->
	    <script src="js/plugins/chartjs/Chart.min.js" type="text/javascript"></script>
		
        <!-- Creamy App -->
        <script src="js/app.min.js" type="text/javascript"></script>
		
		<!-- Data Tables -->
        <script src="js/plugins/datatables/jquery.dataTables.js" type="text/javascript"></script>
        <script src="js/plugins/datatables/dataTables.bootstrap.js" type="text/javascript"></script>

         <!-- theme_dashboard folder -->
					<!-- FONT AWESOME-->
			<link rel="stylesheet" href="theme_dashboard/fontawesome/css/font-awesome.min.css">
					<!-- SIMPLE LINE ICONS-->
			<link rel="stylesheet" href="theme_dashboard/simple-line-icons/css/simple-line-icons.css">
					<!-- ANIMATE.CSS-->
			<link rel="stylesheet" href="theme_dashboard/animate.css/animate.min.css">
					<!-- WHIRL (spinners)-->
			<link rel="stylesheet" href="theme_dashboard/whirl/dist/whirl.css">
				<!-- =============== PAGE VENDOR STYLES ===============-->
					<!-- WEATHER ICONS-->
			<link rel="stylesheet" href="theme_dashboard/weather-icons/css/weather-icons.min.css">
				<!-- =============== BOOTSTRAP STYLES ===============-->
			<link rel="stylesheet" href="theme_dashboard/css/bootstrap.css" id="bscss">
				<!-- =============== APP STYLES ===============-->
			<link rel="stylesheet" href="theme_dashboard/css/app.css" id="maincss">

		<!-- preloader -->
        <link rel="stylesheet" href="css/customizedLoader.css">

        <script type="text/javascript">
			$(window).ready(function() {
				$(".preloader").fadeOut("slow");
			})
		</script>
    </head>
    <?php print $ui->creamyBody(); ?>
        <div data-ui-view="" data-autoscroll="false" class="wrapper ng-scope">
	        <!-- header logo: style can be found in header.less -->
			<?php print $ui->creamyHeader($user); ?>

            <!-- Left side column. contains the logo and sidebar -->
			<?php print $ui->getSidebar($user->getUserId(), $user->getUserName(), $user->getUserRole(), $user->getUserAvatar()); ?>

            <!-- Right side column. Contains the navbar and content of the page -->
            <aside class="content-wrapper">
                <!-- Content Header (Page header) -->
                <section class="content-heading">
<?php
/*
 * APIs FOR FILTER LIST
 */

$campaign = $ui->API_getListAllCampaigns();
//var_dump($campaign);
$ingroup = $ui->API_getInGroups();
//$usersinfo = $ui->API_goGetUserInfo($user_id);

/*
 * API for call statistics - Demian
*/
 

$dropped_calls_today = $ui->API_goGetTotalDroppedCalls();
$dropped_percentage = $ui->API_goGetDroppedPercentage();
$calls_incoming_queue = $ui->API_goGetIncomingQueue();


//<<<<<<< HEAD

//=======
//if(is_null($ui->API_getRealtimeAgent()) {
	//$realtimeAgents = "";
//} else {
	$realtimeAgents = $ui->API_getRealtimeAgent();
//}
//var_dump($dropped_calls_today);
//die("dd");
/*
 * get API data for chart from UIHandler.php
*/
//>>>>>>> 787a99cc6b9f5c49bbfae4e43ee10ad9623028dd

$callsperhour = $ui->API_goGetCallsPerHour();

	$max = 0;
	
	$callsperhour = explode(";",trim($callsperhour, ';'));
	 foreach ($callsperhour AS $temp) {
	   $temp = explode("=",$temp);
	   $results[$temp[0]] = $temp[1];  

         }

        $outbound_calls = max($results["Hour8o"],$results["Hour9o"], $results["Hour10o"], $results["Hour11o"], $results["Hour12o"], $results["Hour13o"], $results["Hour14o"], $results["Hour15o"], $results["Hour16o"], $results["Hour17o"], $results["Hour18o"], $results["Hour19o"], $results["Hour20o"], $results["Hour21o"]);		
	$inbound_calls = max($results["Hour8"],$results["Hour9"], $results["Hour10"], $results["Hour11"], $results["Hour12"], $results["Hour13"], $results["Hour14"], $results["Hour15"], $results["Hour16"], $results["Hour17"], $results["Hour18"], $results["Hour19"], $results["Hour20"], $results["Hour21"]);	
	$dropped_calls = max($results["Hour8d"],$results["Hour9d"], $results["Hour10d"], $results["Hour11d"], $results["Hour12d"], $results["Hour13d"], $results["Hour14d"], $results["Hour15d"], $results["Hour16d"], $results["Hour17d"], $results["Hour18d"], $results["Hour19d"], $results["Hour20d"], $results["Hour21d"]);
	
	$max = max($inbound_calls, $outbound_calls, $dropped_calls);
	
	if($max <= 5){
		$max = 5;
	}
	if($outbound_calls == NULL || $outbound_calls == 0){
		$outbound_calls = 0;
	}
        if($outbound_calls_today == NULL || $outbound_calls_today == 0){
		$outbound_calls_today = 0;
	}	
	if($inbound_calls == NULL || $inbound_calls == 0){
		$inbound_calls = 0;
	}
	if($calls_incoming_queue == NULL || $calls_incoming_queue == 0){
		$calls_incoming_queue = 0;
	}	
	if($dropped_calls == NULL || $dropped_calls == 0){
		$dropped_calls = 0;
	}
	if($dropped_calls_today == NULL || $dropped_calls_today == 0){
		$dropped_calls_today = 0;
	}	
//print_r($answered_calls_today);
//die("dd");	
?>		
                        <!-- Page title -->
                        <?php
                                $lh->translateText("Dashboard");
                        ?>
                        <small class="ng-binding animated fadeInUpShort">Welcome to Goautodial  !</small>
						
					<!--
                    <ol class="breadcrumb">
                        <li><a href="./index.php"><i class="fa fa-bar-chart-o"></i> <?php $lh->translateText("home"); ?></a></li>
                    </ol>
					--> 	
                </section>

                <!-- Main content -->

                <section class="content">
					
	<!--====== STATUS BOXES =======-->
			<div class="row">
				<div class="col-lg-3 col-sm-6 animated fadeInUpShort">
					<a href="#" data-toggle="modal" data-target="#agent_monitoring" data-id="" style="text-decoration : none">
						<div class="panel widget bg-purple" style="height: 95px;">
							<div class="row status-box">
								<div class="col-xs-4 text-center bg-purple-dark pv-md animated fadeInUpShort">
									<em class="icon-earphones fa-3x"></em>
								</div>
								<div class="col-xs-8 pv-lg" style="padding-top:10px !important;">
									<div class="h2 mt0"><span class="text-lg" id="refresh_totalagentscall"></span></div>
									<div class="text-sm">Agent(s) On Call</div>
								</div>
							</div>
						</div>
					</a>
				</div>
				<div class="col-lg-3 col-md-6 animated fadeInUpShort">
					<a href="#" data-toggle="modal" data-target="#agent_monitoring" data-id="" style="text-decoration : none">
						<div class="panel widget bg-purple" style="height: 95px;">
							<div class="row status-box">
								<div class="col-xs-4 text-center bg-purple-dark pv-md animated fadeInUpShort">
									<em class="icon-clock fa-3x"></em>
								</div>
								<div class="col-xs-8 pv-lg" style="padding-top:10px !important;">
									<div class="h2 mt0"><span class="text-lg" id="refresh_totalagentswaitcalls"></span></div>
									<div class="text-sm">Agent(s) Waiting</div>
								</div>
							</div>
						</div>
					</a>
				</div>
               <div class="col-lg-3 col-md-6 col-sm-12 animated fadeInUpShort">
                  	<a href="#" data-toggle="modal" data-target="#agent_monitoring" data-id="" style="text-decoration : none">
		                <div class="panel widget bg-green" style="height: 95px;">
		                	<div class="row status-box">
		                        <div class="col-xs-4 text-center bg-gray-dark pv-md animated fadeInUpShort">
		                           	<em class="icon-hourglass fa-3x"></em>
		                        </div>
                                                <div class="col-xs-8 pv-lg" style="padding-top:10px !important;">		                        
		                        	<div class="h2 mt0"><span class="text-lg" id="refresh_totalagentspaused"></span></div>
									<div class="text-sm">Agent(s) On Paused</div>
		                        </div>
		                    </div>
		                </div>
              		</a>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12 animated fadeInUpShort">
					<!-- date widget    -->
                                <div class="panel widget" style="height: 95px;">
                                        <div class="row status-box">
                                                <div class="col-xs-4 text-center bg-green pv-lg">
                                                <!-- See formats: https://docs.angularjs.org/api/ng/filter/date -->
                                                <!-- <div class="text-sm"><?php echo date("F", time());?></div>
						<div class="h2 mt0"><?php echo date("d", time());?></div> --> 
                                                        <div data-now="" data-format="MMMM" class="text-sm"></div>
                                                        <br>
                                                        <div data-now="" data-format="D" class="h2 mt0"></div> 
                                                </div>
						<div class="col-xs-8 pv-lg">
                                                <!-- <div class="text-uppercase"><?php echo date("l", time());?></div>
						<div class="h3 mt0"><?php echo date("h:i", time());?> 
						<span class="text-muted text-sm"><?php echo date("A", time());?></span>
						</div> -->
                                                        <div data-now="" data-format="dddd" class="text-uppercase"></div>
                                                        <br>
                                                        <div data-now="" data-format="h:mm" class="h2 mt0"></div>
                                                        <div data-now="" data-format="a" class="text-muted text-sm"></div>
                                                        </div>
						</div>
					</div>
					<!-- END date widget    -->
				</div>
            </div>
					
					<!-- Update (if needed) -->
                    <?php
						require_once('./php/Updater.php');
						$upd = \creamy\Updater::getInstance();
						$currentVersion = $upd->getCurrentVersion();
						if (!$upd->CRMIsUpToDate()) {
					?>

                    <div class="row">
                        <section class="col-lg-12">
                            <!-- version -->
                            <div class="box box-danger">
                                <div class="box-header">
                                    <i class="fa fa-refresh"></i>
                                    <h3 class="box-title"><?php print $lh->translationFor("version")." ".number_format($currentVersion, 1); ?></h3>
                                </div>
                                <div class="box-body">
									<?php
									if ($upd->canUpdateFromVersion($currentVersion)) { // update needed
										$contentText = $lh->translationFor("you_need_to_update");
										print $ui->formWithContent(
											"update_form", 						// form id
											$contentText, 						// form content
											$lh->translationFor("update"), 		// submit text
											CRM_UI_STYLE_DEFAULT,				// submit style
											CRM_UI_DEFAULT_RESULT_MESSAGE_TAG,	// resulting message tag
											"update.php");						// form PHP action URL.
									} else { // we cannot update?
										$lh->translateText("crm_update_impossible");
									}
									?>
                                </div>
                            </div>
                        </section>
                    </div>   <!-- /.row -->
					<?php } ?>   


			<div class="row"> <!-- ROW FOR THE REST -->
				<div class="col-lg-9" id="row_for_rest">
	<!--===== CALLS PER HOUR CHART =======--> 
                <div class="row">
	              <!-- START widget-->
	              <div id="panelChart9" ng-controller="FlotChartController" class="panel panel-default">
	                 <div class="panel-heading">
	                    <div class="panel-title">Calls Per Hour</div>
	                 </div>
	                 <div collapse="panelChart9" class="panel-wrapper">
	                    <div class="panel-body">
	                       <div class="chart-splinev3 flot-chart"></div> <!-- data is in JS -> demo-flot.js -> search (Overall/Home/Pagkain)--> 
	                    </div>
	                 </div>
	              </div>
	            </div>
	            <!-- END widget-->

	<!--===== Today's Phone Calls =======--> 
	            <div class="row">
	            	<div class="col-lg-12" style="padding: 0px;">
	                	<!-- demian -->                	
				<div class="panel widget col-md-2 col-sm-3 col-xs-6 br text-center bg-info pv-xl info_sun_boxes">
					<em class="fa fa-sun-o fa-3x"></em><div class="h2 m0"><span class="text-lg"></span></div>
                                                                <div class="text">Today's Phone Calls</div>
                                </div>
	                	<div class="panel widget col-md-2 col-sm-3 col-xs-6 br text-center info_sun_boxes">
	                		<div class="h2 m0"><span class="text-lg" id="refresh_RingingCalls"></span></div>
								<div class="text-muted">Ringing Calls</div>
	                	</div>
	                	<div class="panel widget col-md-2 col-sm-3 col-xs-6 br text-center info_sun_boxes">
	                		<div class="h2 m0"><span class="text-lg" id="refresh_IncomingQueue"></span></div>
								<div class="text-muted">Incoming Calls</div>
	                	</div>	                	
	                	<div class="panel widget col-md-2 col-sm-3 col-xs-6 br text-center info_sun_boxes">
	                		<div class="h2 m0"><span class="text-lg" id="refresh_AnsweredCalls"></span></div>
								<div class="text-muted">Answered Calls</div>
	                	</div>
	                	<div class="panel widget col-md-2 col-sm-3 col-xs-6 br text-center info_sun_boxes">
	                		<div class="h2 m0"><span class="text-lg" id="refresh_DroppedCalls"></span></div>
								<div class="text-muted">Dropped Calls</div>
	                	</div>	                	
	                	<div class="panel widget col-md-2 col-sm-3 col-xs-6 br text-center info_sun_boxes">
	                		<div class="h2 m0"><span class="text-lg" id="refresh_TotalCalls"></span></div>
								<div class="text-muted" style="font-size: small;">Total Calls</div>
	                	</div>
	                </div>
                </div>
        <!-- ====== CLUSTER STATUS ======= -->
                <div class="row">
                	<div role="tabpanel" class="panel panel-transparent">
					  <ul role="tablist" class="nav nav-tabs nav-justified">
					  
					  <!-- Nav task panel tabs-->
						 <li role="presentation" class="active">
							<a href="#cluster_status" aria-controls="home" role="tab" data-toggle="tab" class="bb0">
							   <em class="fa fa-bar-chart-o fa-fw"></em>Cluster Status</a>
						 </li>
					  </ul>
				<?php
					$cluster = $ui->API_goGetClusterStatus();
				?>
						<!-- Tab panes-->
						<div class="tab-content p0 bg-white">
						   <div id="cluster_status" role="tabpanel" class="tab-pane active">
							<!-- Cluster Status -->
							<div class="table-responsive">
								<table class="table table-striped table-bordered table-hover">
								   <thead>
									  <tr>
										 <th>SERVER ID</th>
										 <th>SERVER IP</th>
										 <th>STATUS</th>
										 <th>LOAD</th>
										 <th>CHANNELS</th>
										 <th>DISK</th>
										 <th>TIME</th>
									  </tr>
								   </thead>
								   <tbody>
										<?php
											for($i=0;$i < count($cluster->server_id);$i++){
												if($cluster->active[$i] == "Y"){
													$cluster->active[$i] = "<font color='green'><i>Active</i></font>";
												}else{
													$cluster->active[$i] = "<font color='red'><i>Inactive</i></font>";
												}
										?>
										<tr>
											<td><?php echo $cluster->server_id[$i];?></td>
											<td><?php echo $cluster->server_ip[$i];?></td>
											<td><?php echo $cluster->active[$i];?></td>
											<td><?php echo $cluster->sysload[$i]."% - ".$cluster->cpu[$i];?></td>
											<td style="padding-left:20px;"><?php echo $cluster->channel[$i];?></td>
											<td><?php echo $cluster->disk_usage[$i]."%";?></td>
											<td><?php echo $cluster->systemtime[$i];?></td>
										</tr>
									<!--
										<tr>
											<td><span id="refresh_server_id"></span></td>
											<td><span id="refresh_server_ip"></span></td>
											<td><span id="refresh_active"></td>
											<td><span id="refresh_sysload"></span> - <span id="refresh_cpu"></span></td>
											<td><center><span id="refresh_channels_total"></span></center></td>
											<td><center><span id="refresh_disk_usage"></span></center></td>
											<td><span id="refresh_s_time"></span></td>
										</tr>
									-->
										<?php
											}
										?>
											<td>&nbsp;</td>
											<td>&nbsp;</td>
											<td><b>PHP Time</b></td>
											<td>&nbsp;</td>
											<td>&nbsp;</td>
											<td>&nbsp;</td>
											<td><span id="refresh_php_time"></span></td>
										</tr>
										<tr>
											<td>&nbsp;</td>
											<td>&nbsp;</td>
											<td><b>DB Time</b></td>
											<td>&nbsp;</td>
											<td>&nbsp;</td>
											<td>&nbsp;</td>
											<td><span id="refresh_db_time"></span></td>
										</tr>
								   </tbody>
								</table>
							</div>
							<div class="panel-footer text-right">&nbsp;</div>
						 	</div>
						</div>
					</div>
                </div>

            	</div><!-- END OF COLUMN 9 -->


            	<aside class="col-lg-3">

        <!--==== SERVICE LEVEL AGREEMENT ==== -->
	            		<div class="panel panel-default">
						   <div class="panel-body">
								<div class="text-primary">Dropped Calls Percentage</div>
								<center>
									<div width="200" height="200" style="margin-top: 40px;margin-bottom: 40px;">
										<input type="text"
										class="knob" value="<?php echo $dropped_percentage; ?>" data-width="150" data-height="150" data-padding="21px"
										data-fgcolor="#0073b7" data-readonly="true" readonly="readonly"
										style="
											width: 49px;
											height: 100px;
											position: absolute;
											margin-top: 45px;
											margin-left: -98px;
											vertical-align: middle;
											border: 0px;
											font-style: normal;
											font-variant: normal;
											font-weight: bold;
											/* font-stretch: normal; */
											font-size: 30px;
											line-height: normal;
											font-family: Arial;
											text-align: center;
											color: #0073b7;
											padding: 0px;
											-webkit-appearance: none;
											background: none;
										">
									</div>
								</center>
							   <div class="panel-footer">
								  <p class="text-muted">
									 <em class="fa fa-upload fa-fw"></em>
									 <span>Total Dropped Percentage</span>
									 <span class="text-dark"><?php echo $dropped_percentage; ?></span>
								  </p>
							   </div>
							</div>
							<!-- END loader widget-->
						</div>

			<!-- ==== TASK ACTIVITIES ===== -->
						<div class="panel panel-default">
		                     <div class="panel-heading">
		                        <div class="panel-title">Campaign Leads Resources</div>
		                     </div>         
		                     <!-- START list group-->
		                     <!--<span id="refresh_db_time">-->
		                     <div class="list-group">
		                        <!-- START list group item-->
		                        <div class="list-group-item">
		                           <div class="media-box">
		                              <div class="pull-left">
		                                 <span class="fa-stack">
		                                    <em class="fa fa-circle fa-stack-2x text-purple"></em>
		                                    <em class="fa fa-cloud-upload fa-stack-1x fa-inverse text-white"></em>
		                                 </span>
		                              </div>                                           
		                              <div class="media-box-body clearfix">
		                              <span id="refresh_campaign_name">                                            
                                              </span>
                                              </div>
		                           </div>
		                        </div>
		                        <!-- END list group item-->
		                        <!-- START list group item-->
		                        <!--<div class="list-group-item">
		                           <div class="media-box">
		                              <div class="pull-left">
		                                 <span class="fa-stack">
		                                    <em class="fa fa-circle fa-stack-2x text-info"></em>
		                                    <em class="fa fa-file-text-o fa-stack-1x fa-inverse text-white"></em>
		                                 </span>
		                              </div>
		                              <div class="media-box-body clearfix">
		                                 <small class="text-muted pull-right ml">2h</small>
		                                 <div class="media-box-heading"><a href="#" class="text-info m0">NEW DOCUMENT</a>
		                                 </div>
		                                 <p class="m0">
		                                    <small><a href="#">Bootstrap.doc</a>
		                                    </small>
		                                 </p>
		                              </div>
		                           </div>
		                        </div>-->
		                        <!-- END list group item-->
		                        <!-- START list group item-->
		                        <!--<div class="list-group-item">
		                           <div class="media-box">
		                              <div class="pull-left">
		                                 <span class="fa-stack">
		                                    <em class="fa fa-circle fa-stack-2x text-danger"></em>
		                                    <em class="fa fa-exclamation fa-stack-1x fa-inverse text-white"></em>
		                                 </span>
		                              </div>
		                              <div class="media-box-body clearfix">
		                                 <small class="text-muted pull-right ml">5h</small>
		                                 <div class="media-box-heading"><a href="#" class="text-danger m0">BROADCAST</a>
		                                 </div>
		                                 <p class="m0"><a href="#">Read</a>
		                                 </p>
		                              </div>
		                           </div>
		                        </div>-->
		                        <!-- END list group item-->
		                        <!-- START list group item-->
		                        <!--<div class="list-group-item">
		                           <div class="media-box">
		                              <div class="pull-left">
		                                 <span class="fa-stack">
		                                    <em class="fa fa-circle fa-stack-2x text-success"></em>
		                                    <em class="fa fa-clock-o fa-stack-1x fa-inverse text-white"></em>
		                                 </span>
		                              </div>
		                              <div class="media-box-body clearfix">
		                                 <small class="text-muted pull-right ml">15h</small>
		                                 <div class="media-box-heading"><a href="#" class="text-success m0">NEW MEETING</a>
		                                 </div>
		                                 <p class="m0">
		                                    <small>On
		                                       <em>10/12/2015 09:00 am</em>
		                                    </small>
		                                 </p>
		                              </div>
		                           </div>
		                        </div>-->
		                        <!-- END list group item-->
		                        <!-- START list group item-->
		                        <!--<div class="list-group-item">
		                           <div class="media-box">
		                              <div class="pull-left">
		                                 <span class="fa-stack">
		                                    <em class="fa fa-circle fa-stack-2x text-warning"></em>
		                                    <em class="fa fa-tasks fa-stack-1x fa-inverse text-white"></em>
		                                 </span>
		                              </div>
		                              <div class="media-box-body clearfix">
		                                 <small class="text-muted pull-right ml">1w</small>
		                                 <div class="media-box-heading"><a href="#" class="text-warning m0">TASKS COMPLETION</a>
		                                 </div>
		                                 <div class="progress progress-xs m0">
		                                    <div role="progressbar" aria-valuenow="22" aria-valuemin="0" aria-valuemax="100" style="width: 22%" class="progress-bar progress-bar-warning progress-bar-striped">
		                                       <span class="sr-only">22% Complete</span>
		                                    </div>
		                                 </div>
		                              </div>
		                           </div>
		                        </div>-->
		                     </div>
		                     <!--</span>-->
		                     <div class="panel-footer clearfix">
		                        <a href="#" class="pull-left">
		                           <small>Load more</small>
		                        </a>
		                     </div>		                     
		                </div>
            	</aside><!-- END OF COLUMN 3 -->


            </div><!-- END OF ROW -->
           
	
							<!--
							<div class="panel panel-default">
								<div class="panel-heading text-blue">Service Level Agreement</div>
								<div class="panel-body text-center">
									<div data-label="90%" class="radial-bar radial-bar-100 radial-bar-lg"></div>
								</div>
								<div class="panel-footer">
								  <p class="text-muted">
									 <em class="fa fa-upload fa-fw"></em>
									 <span>Service Level Agreement Percentage</span>
									 <span class="text-dark">90% Gb</span>
								  </p>
							   </div>
							</div>
							-->

					<div class="row">
						<!-- Agent Monitoring Summary -->
						<div class="col-lg-3">
							<div class="panel panel-default">
							   <div class="panel-heading">
								  <div class="panel-title">Agent Monitoring Summary</div>
							   </div>
							   <!-- START list group-->
							   <div data-height="230" data-scrollable="yes" class="list-group">
								  <!-- START list group item-->
								  <a href="#" class="list-group-item">
									 <div class="media-box">
										<div class="pull-left">
										   <img src="<?php echo $_SESSION['avatar'];?>" alt="Image" class="media-box-object img-circle thumb32">
										</div>
										<div class="media-box-body clearfix">
                                                                                <span id="refresh_online_agents">
                                                                                </span>
										</div>
									 </div>
								  </a>
								  <!-- END list group item-->
								  <!-- <a href="#" class="list-group-item">
									 <div class="media-box">
										<div class="pull-left">
										   <img src="theme_dashboard/img/user/03.jpg" alt="Image" class="media-box-object img-circle thumb32">
										</div>
										<div class="media-box-body clearfix">
										   <strong class="media-box-heading text-primary">
											  <span class="circle circle-success circle-lg text-left"></span>Jessica Silva</strong>
											<br/>
											<strong class=""style="padding-left:20px;">CS HOTLINE</strong>
											<small class="text-muted pull-right ml" style="padding-right:20px;">1:49</small>
										</div>
									 </div>
								  </a>-->
								  <!-- END list group item-->
								  <!-- START list group item-->
								  <!-- <a href="#" class="list-group-item">
									 <div class="media-box">
										<div class="pull-left">
										   <img src="theme_dashboard/img/user/09.jpg" alt="Image" class="media-box-object img-circle thumb32">
										</div>
										<div class="media-box-body clearfix">
										   <strong class="media-box-heading text-primary">
											  <span class="circle circle-danger circle-lg text-left"></span>Jessie Wells</strong>
											<br/>
											<strong class=""style="padding-left:20px;">CS HOTLINE</strong>
											<small class="text-muted pull-right ml" style="padding-right:20px;">1:49</small>
										</div>
									 </div>
								  </a>-->
								  <!-- END list group item-->
								  <!-- START list group item-->
								  <!-- <a href="#" class="list-group-item">
									 <div class="media-box">
										<div class="pull-left">
										   <img src="theme_dashboard/img/user/12.jpg" alt="Image" class="media-box-object img-circle thumb32">
										</div>
										<div class="media-box-body clearfix">
										   <strong class="media-box-heading text-primary">
											  <span class="circle circle-danger circle-lg text-left"></span>Rosa Burke</strong>
											<br/>
											<strong class=""style="padding-left:20px;">CS HOTLINE</strong>
											<small class="text-muted pull-right ml" style="padding-right:20px;">1:49</small>
										</div>
									 </div>
								  </a> -->
								  <!-- END list group item-->
								  <!-- START list group item-->
								  <!-- <a href="#" class="list-group-item">
									 <div class="media-box">
										<div class="pull-left">
										   <img src="theme_dashboard/img/user/10.jpg" alt="Image" class="media-box-object img-circle thumb32">
										</div>
										<div class="media-box-body clearfix">
										   <strong class="media-box-heading text-primary">
											  <span class="circle circle-danger circle-lg text-left"></span>Michelle Lane</strong>
											<br/>
											<strong class=""style="padding-left:20px;">CS HOTLINE</strong>
											<small class="text-muted pull-right ml" style="padding-right:20px;">1:49</small>
										</div>
									 </div>
								  </a> -->
								  <!-- END list group item-->
							   </div>
							   <!-- END list group-->
							   <!-- START panel footer-->
							   <div class="panel-footer clearfix">
								  	<a href="#" data-toggle="modal" data-target="#agent_monitoring" class="pull-right">
		                           		<medium>View more</medium> <em class="fa fa-arrow-right"></em>
		                        	</a>
							   </div>
							   <!-- END panel-footer-->
							</div>
						</div><!-- end team messages -->
			<!--==== VECTOR MAP LOADER ======-->
						<div ng-controller="VectorMapController" class="col-lg-9">
							<div class="panel panel-transparent">
							   <div data-vector-map="" data-height="450" data-scale='0' data-map-name="world_mill_en"></div>
							</div>
						 </div>

					</div>
					
					<?php print $ui->hooksForDashboard(); ?>
					
				
                </section><!-- /.content -->
				
            </aside><!-- /.right-side -->
			
            <?php print $ui->creamyFooter(); ?>
        </div><!-- ./wrapper -->

<!--================= MODALS =====================-->
	<!-- agents monitoring -->
	<div class="modal fade" id="agent_monitoring" tabindex="-1" aria-labelledby="agent_monitoring">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
			<!-- Header -->
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="close_ingroup"><span aria-hidden="true">&times;</span></button>
					
				<!--===== FILTER LIST =======-->
					<!--<div class="agent_monitor_filter pull-right">
						<small>Filter: </small> -->
					<!-- == INGROUP == -->
						<!--<span class="tenant_filter_agentmonitoring">
						    <select id="tenant_dropdown_agent_monitoring">
						   			<option selected> --- All Tenants --- </option> -->
						   		<?php
							//	 	for($i=0;$i < count($ingroup->group_id);$i++){
							//	 		echo "<option>".$ingroup->group_name[$i]."</option>";
							//	 	}
								?>
						    <!-- </select>
						</span> -->
					<!-- == TENANT milo == -->
						<!-- <span class="campaign_filter_agentmonitoring"> -->
							<!--
							   <div class="btn-group">
								  <button type="button" data-toggle="dropdown" id="campaign_dropdown" class="btn btn-default"> - - - All Campaigns - - - </button>
								  <ul role="menu" class="dropdown-menu dropdown-menu-right animated fadeInUpShort">
									 <?php/*
									 	for($i=0;$i < count($campaign->campaign_id);$i++){
									 		echo "<li><a href='#'>".$campaign->campaign_name[$i]."</a></li>";
									 	}*/
									 ?>
								  </ul>
							   </div>
							-->
							<!-- <select id="campaign_dropdown_agentmonitoring">
						   			<option selected> --- All Campaigns --- </option> -->
						   		<?php
					//			 	for($i=0;$i < count($campaign->campaign_id);$i++){
						//			 	echo "<option>".$campaign->campaign_name[$i]."</option>";
							//		}
								?>
						    <!--</select>
						</span>
					</div> -->
							<!-- END FILTER list milo   -->
					<h4 class="modal-title" id="agent_monitoring">Monitoring</h4>
					<?php
						//var_dump($_SESSION);
						//var_dump($realtimeAgents);
						
						/* for($i=0;$i < count($realtimeAgents->session_id);$i++){
							$goconf_exten = $realtimeAgents->session_id[$i];
							$goconf_server = $realtimeAgents->server_ip[$i];
						}
						echo $goconf_exten.'----'.$goconf_server; */
					?>

				
				</div>
				<div class="modal-body">
					<table class="table table-striped table-bordered table-hover" id="agent_monitoring_table">
						<thead>
							<tr>
								<th>Agent</th>
								<th>Status</th>
								<th>MM:SS</th>
							</tr>
						</thead>
						<tbody>
							<?php
							
							if(is_null($realtimeAgents->session_id)) {
							?>
							<tr>
								<td>No Agents Live</td>
							</tr>
							<?php
							
							} else {
								for($i=0;$i < count($realtimeAgents->session_id);$i++){
								
							?>
							<tr>
								<td>
								  <!-- START list group item-->
									<div class="media-box">
										<!-- <div class="pull-left">
										   <img src="<?php //echo $_SESSION['avatar'];?>" alt="Image" class="media-box-object img-circle thumb32">
										</div> -->
										<div class="media-box-body clearfix">
										   <strong class="media-box-heading text-primary">
											  <span class="circle circle-success circle-lg text-left"></span><?php echo $realtimeAgents->user[$i]; ?></strong>
										</div>
									</div>
								</td>
								
								<td>
									<?php echo $realtimeAgents->status[$i]; ?>
								</td>

								<td>
									<?php echo $realtimeAgents->last_state_change[$i]; ?>
								</td>
							</tr>
							<?php
								} //end for
							} //end if
							?>

						</tbody>
					</table>
				</div> <!-- end of modal body -->
				
			</div>
		</div>
	</div>
	<!-- End of modal -->

<?php
	/*
	 * Modal Dialogs
	*/
		include_once ("./php/ModalPasswordDialogs.php");
?>
<script>
	/*
	 * JQuery Knob = need for dropped calls percentage and other pie loader
	*/
		$(function () {
		  /* jQueryKnob */
	  
		  $(".knob").knob({
			draw: function () {
	  
			  // "tron" case
			  if (this.$.data('skin') == 'tron') {
	  
				var a = this.angle(this.cv)  // Angle
					, sa = this.startAngle          // Previous start angle
					, sat = this.startAngle         // Start angle
					, ea                            // Previous end angle
					, eat = sat + a                 // End angle
					, r = true;
	  
				this.g.lineWidth = this.lineWidth;
	  
				this.o.cursor
				&& (sat = eat - 0.3)
				&& (eat = eat + 0.3);
	  
				if (this.o.displayPrevious) {
				  ea = this.startAngle + this.angle(this.value);
				  this.o.cursor
				  && (sa = ea - 0.3)
				  && (ea = ea + 0.3);
				  this.g.beginPath();
				  this.g.strokeStyle = this.previousColor;
				  this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sa, ea, false);
				  this.g.stroke();
				}
	  
				this.g.beginPath();
				this.g.strokeStyle = r ? this.o.fgColor : this.fgColor;
				this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sat, eat, false);
				this.g.stroke();
	  
				this.g.lineWidth = 2;
				this.g.beginPath();
				this.g.strokeStyle = this.o.fgColor;
				this.g.arc(this.xy, this.xy, this.radius - this.lineWidth + 1 + this.lineWidth * 2 / 3, 0, 2 * Math.PI, false);
				this.g.stroke();
	  
				return false;
			  }
			}
		  });
		  /* END JQUERY KNOB */
		});
</script>
	
<!--========== REFRESH DIVS ==============-->
	<script src="theme_dashboard/js/demo/demo-vector-map.js"></script>
	<script src="js/load_statusboxes.js"></script>
        <script src="js/load_hopperleadswarning.js"></script>
	<script src="js/load_clusterstatus.js"></script>

	<script>
	/*
	 * Inbound and Outbound Calls Per Hour Data
	*/
		(function(window, document, $, undefined){
			$(function(){
				var datav3 = [
					{
					"label": "Outbound Calls",
					"color": "#009688",
					"data": [
					<?php
					if($results["result"] == "success" && isset($results["result"]) && isset($results["Hour9o"]) && $outbound_calls != 0){
						echo '["9 AM",'.$results["Hour9o"].'],';
						echo '["10 AM",'.$results["Hour10o"].'],';
						echo '["11 AM",'.$results["Hour11o"].'],';
						echo '["12 NN",'.$results["Hour12o"].'],';
						echo '["1 PM",'.$results["Hour13o"].'],';
						echo '["2 PM",'.$results["Hour14o"].'],';
						echo '["3 PM",'.$results["Hour15o"].'],';
						echo '["4 PM",'.$results["Hour16o"].'],';
						echo '["5 PM",'.$results["Hour17o"].'],';
						echo '["6 PM",'.$results["Hour18o"].'],';
						echo '["7 PM",'.$results["Hour19o"].'],';
						echo '["8 PM",'.$results["Hour20o"].'],';
						echo '["9 PM",'.$results["Hour21o"].']';
					}else{
						echo '["9 AM", 0],';
						echo '["10 AM", 0],';
						echo '["11 AM", 0],';
						echo '["12 NN", 0],';
						echo '["1 PM", 0],';
						echo '["2 PM", 0],';
						echo '["3 PM", 0],';
						echo '["4 PM", 0],';
						echo '["5 PM", 0],';
						echo '["6 PM", 0],';
						echo '["7 PM", 0],';
						echo '["8 PM", 0],';
						echo '["9 PM", 0]';
					}
					?>]
					},{
						"label": "Inbound Calls",
						"color": "#23b7e5",
						"data": [
						<?php
						if($results["result"] == "success" && isset($results["result"]) && isset($results["Hour9"]) && $inbound_calls != 0){
							echo '["9 AM",'.$results["Hour9"].'],';
							echo '["10 AM",'.$results["Hour10"].'],';
							echo '["11 AM",'.$results["Hour11"].'],';
							echo '["12 NN",'.$results["Hour12"].'],';
							echo '["1 PM",'.$results["Hour13"].'],';
							echo '["2 PM",'.$results["Hour14"].'],';
							echo '["3 PM",'.$results["Hour15"].'],';
							echo '["4 PM",'.$results["Hour16"].'],';
							echo '["5 PM",'.$results["Hour17"].'],';
							echo '["6 PM",'.$results["Hour18"].'],';
							echo '["7 PM",'.$results["Hour19"].'],';
							echo '["8 PM",'.$results["Hour20"].'],';
							echo '["9 PM",'.$results["Hour21"].']';
						}else{
							echo '["9 AM", 0],';
							echo '["10 AM", 0],';
							echo '["11 AM", 0],';
							echo '["12 NN", 0],';
							echo '["1 PM", 0],';
							echo '["2 PM", 0],';
							echo '["3 PM", 0],';
							echo '["4 PM", 0],';
							echo '["5 PM", 0],';
							echo '["6 PM", 0],';
							echo '["7 PM", 0],';
							echo '["8 PM", 0],';
							echo '["9 PM", 0]';
						}
						?>]
					},{
					"label": "Dropped Calls",
					"color": "#512e90",
					"data": [
					<?php
						if($results["result"] == "success" && isset($results["result"]) && isset($results["Hour9d"]) && $dropped_calls != 0){
							echo '["9 AM",'.$results["Hour9d"].'],';
							echo '["10 AM",'.$results["Hour10d"].'],';
							echo '["11 AM",'.$results["Hour11d"].'],';
							echo '["12 NN",'.$results["Hour12d"].'],';
							echo '["1 PM",'.$results["Hour13d"].'],';
							echo '["2 PM",'.$results["Hour14d"].'],';
							echo '["3 PM",'.$results["Hour15d"].'],';
							echo '["4 PM",'.$results["Hour16d"].'],';
							echo '["5 PM",'.$results["Hour17d"].'],';
							echo '["6 PM",'.$results["Hour18d"].'],';
							echo '["7 PM",'.$results["Hour19d"].'],';
							echo '["8 PM",'.$results["Hour20d"].'],';
							echo '["9 PM",'.$results["Hour21d"].']';
						}else{
							echo '["9 AM", 0],';
							echo '["10 AM", 0],';
							echo '["11 AM", 0],';
							echo '["12 NN", 0],';
							echo '["1 PM", 0],';
							echo '["2 PM", 0],';
							echo '["3 PM", 0],';
							echo '["4 PM", 0],';
							echo '["5 PM", 0],';
							echo '["6 PM", 0],';
							echo '["7 PM", 0],';
							echo '["8 PM", 0],';
							echo '["9 PM", 0]';
						}
						?>]
					}];
					
				var options = {
					series: {
						lines: {
							show: false
						},
						points: {
							show: true,
							radius: 4
						},
						splines: {
							show: true,
							tension: 0.4,
							lineWidth: 1,
							fill: 0.5
						}
					},
					grid: {
						borderColor: '#eee',
						borderWidth: 1,
						hoverable: true,
						backgroundColor: '#fcfcfc'
					},
					tooltip: true,
					tooltipOpts: {
						content: function (label, x, y) { return y + ' ' + label + ' around ' + x; }
					},
					xaxis: {
						tickColor: '#fcfcfc',
						mode: 'categories'
					},
					yaxis: {
						min: 0,
						max: <?php echo $max;?>, // optional: use it for a clear represetation
						tickColor: '#eee',
						//position: 'right' or 'left',
						tickFormatter: function (v) {
							return v/* + ' visitors'*/;
						}
					},
					shadowSize: 0
				  };
				  var chartv3 = $('.chart-splinev3');
				  if(chartv3.length)
					$.plot(chartv3, datav3, options);
			});
		})(window, document, window.jQuery);

// Clear user information
function clear_agent_form(){

    $('#modal-userid').html("");
    $('#modal-user').html("");
    $('#modal-active').html("");
    $('#modal-email').html("");
    $('#modal-usergroup').html("");     
    $('#modal-userlevel').html("");
    $('#modal-phonelogin').html("");
    $('#modal-phonepass').html("");
    $('#modal-voicemail').html("");   
}

// Clear campaign information
 function clear_campaign_form(){

    $('#modal-campaignid').html("");
    $('#modal-campaignname').html("");
    $('#modal-campaigndesc').html("");
    $('#modal-callrecordings').html("");
    //$('#modal-amd').html("");
    $('#modal-campaigncid').html("");
    $('#modal-localcalltime').html("");
}
		//demian
		$(document).ready(function(){
		
                    // Clear previous agent info
                    $('#view-agent-modal').on('hidden.bs.modal', function () {

                        clear_agent_form();

                    });
                    // Get user information                     
                    $(document).on('click','#onclick-userinfo',function(){
                        var userid = $(this).attr('data-id');
                        $.ajax({
                            type: 'POST',
                            url: "./php/ViewUserInfo.php",
                            data: {user_id: userid},
                            cache: true,
                            //dataType: 'json',
                                success: function(data){ 
                                    //console.log(data);
                                    var JSONString = data;
                                    var JSONObject = JSON.parse(JSONString);
                                    //var JSONObject = $.parseJSON(JSONString);
                                    console.log(JSONObject);      // Dump all data of the Object in the console
                                        $('#modal-userid').append(JSONObject.data.user);
                                        $('#modal-user').append(JSONObject.data.full_name);
                                        $('#modal-active').append(JSONObject.data.active);
                                        $('#modal-email').append(JSONObject.data.email);
                                        $('#modal-usergroup').append(JSONObject.data.user_group);     
                                        $('#modal-userlevel').append(JSONObject.data.user_level);                                        
                                        $('#modal-phonelogin').append(JSONObject.data.phone_login);
                                        $('#modal-phonepass').append(JSONObject.data.phone_pass);
                                        $('#modal-voicemail').append(JSONObject.data.voicemail_id);                                    
                                }
                        });
                    });

                    // Clear previous agent info
                    $('#view-campaign-modal').on('hidden.bs.modal', function () {

                        clear_campaign_form();

                    });
                    
                    // Get campaign information 
                    $(document).on('click','#onclick-campaigninfo',function(){
                        var campid = $(this).attr('data-id');
                        $.ajax({
                            type: 'POST',
                            url: "./php/ViewCampaign.php",
                            data: {campaign_id: campid},
                            cache: true,
                            //dataType: 'json',
                                success: function(campaigndata){ 
                                    //console.log(campaigndata);
                                    var JSONStringcampaign = campaigndata;
                                    var JSONObjectcampaign = JSON.parse(JSONStringcampaign);
                                    //console.log(JSONObjectcampaign);      // Dump all campaigndata of the Object in the console
                                    console.log(JSONObjectcampaign.data.campaign_id);
                                    console.log(JSONObjectcampaign);
                                        $('#modal-campaignid').append(JSONObjectcampaign.data.campaign_id);
                                        $('#modal-campaignname').append(JSONObjectcampaign.data.campaign_name);
                                        $('#modal-campaigndesc').append(JSONObjectcampaign.data.campaign_description);
                                        $('#modal-callrecordings').append(JSONObjectcampaign.data.campaign_recording);
                                        $('#modal-campaigncid').append(JSONObjectcampaign.data.campaign_cid);                                        
                                        $('#modal-localcalltime').append(JSONObjectcampaign.data.local_call_time);                                       
                                }
                         });                        
                     });
                 
	// ---- loads datatable functions
				$('#agent_monitoring_table').dataTable({bFilter: false, bInfo: false});

	// ---- Fixed Action Button
			$(".bottom-menu").on('mouseenter mouseleave', function () {
			  $(this).find(".fab-div-area").stop().slideToggle({ height: 'toggle', opacity: 'toggle' }, 'slow');
			});
			
	// ---- status boxes
		// ---- agents 
			load_totalagentscall(); 
			load_totalagentspaused();
			load_totalagentswaitingcall();
		// ---- sales	
			load_totalSales();
			load_INSalesHour();
			load_OUTSalesPerHour();
		// ---- leads
			load_TotalActiveLeads();
			load_LeadsinHopper();
			load_TotalDialableLeads();
		// ---- calls
			load_RingingCalls();
			load_IncomingQueue();
			load_AnsweredCalls();
			load_DroppedCalls();
			load_TotalCalls();
			load_LiveOutbound();
                            
	// ---- clusterstatus table
		// ---- server 
			load_server_id(); 
			load_server_ip();
			load_active();
			load_sysload();
			load_cpu();
			load_channels_total();
			load_disk_usage();
			load_s_time();
		// ---- PHP TIME
			load_php_time();
		// ---- DB TIME
			load_db_time();
			
        // ---- agent and campaign resources
                        load_campaign_name();
                        load_online_agents();
                
		});

		
	//Refresh functions() after 5000 millisecond
		// ... status boxes ...
		setInterval(load_totalagentscall,5000);
		setInterval(load_totalagentspaused,5000);
		setInterval(load_totalagentswaitingcall,5000);
		
		setInterval(load_totalSales,5000);
		setInterval(load_INSalesHour,5000);
		setInterval(load_OUTSalesPerHour,5000);
		
		setInterval(load_TotalActiveLeads,5000);
		setInterval(load_LeadsinHopper,5000);
		setInterval(load_TotalDialableLeads,5000);
		
		setInterval(load_RingingCalls,5000);
		setInterval(load_IncomingQueue,5000);
		setInterval(load_AnsweredCalls,5000);
		setInterval(load_DroppedCalls,5000);
		setInterval(load_TotalCalls,5000);
		setInterval(load_LiveOutbound,5000);
		
		// ... cluster status table ...
		setInterval(load_server_id,5000);
		setInterval(load_server_ip,5000);
		setInterval(load_active,5000);
		setInterval(load_sysload,5000);
		setInterval(load_cpu,5000);
		setInterval(load_channels_total,5000);
		setInterval(load_disk_usage,5000);
		setInterval(load_php_time,5000);
		setInterval(load_db_time,5000);
		
		// ... agent and campaign resources ...
		setInterval(load_campaign_name,5000);
		setInterval(load_online_agents,5000);				
		
	</script>
	
    <!-- =============== VENDOR SCRIPTS ===============-->
   <!-- JQUERY EASING-->
   <script src="theme_dashboard/js/jquery.easing/js/jquery.easing.js"></script>
   <!-- ANIMO-->
   <script src="theme_dashboard/js/animo.js/animo.js"></script>
   <!-- SLIMSCROLL-->
   <script src="theme_dashboard/js/slimScroll/jquery.slimscroll.min.js"></script>
   <!-- SCREENFULL-->
   <script src="theme_dashboard/js/screenfull/dist/screenfull.js"></script>
   <!-- LOCALIZE-->
   <script src="theme_dashboard/js/jquery-localize-i18n/dist/jquery.localize.js"></script>
   <!-- RTL demo-->
   <script src="theme_dashboard/js/demo/demo-rtl.js"></script>
   <!-- =============== PAGE VENDOR SCRIPTS ===============-->
   <!-- SPARKLINE-->
   <script src="theme_dashboard/js/sparkline/index.js"></script>
   <!-- FLOT CHART-->
   <script src="theme_dashboard/js/Flot/jquery.flot.js"></script>
   <script src="theme_dashboard/js/flot.tooltip/js/jquery.flot.tooltip.min.js"></script>
   <script src="theme_dashboard/js/Flot/jquery.flot.resize.js"></script>
   <script src="theme_dashboard/js/Flot/jquery.flot.time.js"></script>
   <script src="theme_dashboard/js/Flot/jquery.flot.categories.js"></script>
   <script src="theme_dashboard/js/flot-spline/js/jquery.flot.spline.min.js"></script>
   <!-- VECTOR MAP-->
   <script src="theme_dashboard/js/ika.jvectormap/jquery-jvectormap-1.2.2.min.js"></script>
   <script src="theme_dashboard/js/ika.jvectormap/jquery-jvectormap-world-mill-en.js"></script>
   <script src="theme_dashboard/js/ika.jvectormap/jquery-jvectormap-us-mill-en.js"></script>
   
   <!-- CLASSY LOADER-->
   <script src="theme_dashboard/js/jquery-classyloader/js/jquery.classyloader.min.js"></script>
   
   <!-- =============== APP SCRIPTS ===============-->
    <script src="theme_dashboard/js/app.js"></script>
	<script src="theme_dashboard/js/jquery-knob/dist/jquery.knob.min.js"></script>

    </body>
</html>
