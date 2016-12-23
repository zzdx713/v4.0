<?php

	###################################################
	### Name: telephonyinbound.php 	   ###
	### Functions: Manage Inbound, IVR & DID  	   ###
	### Copyright: GOAutoDial Ltd. (c) 2011-2016	   ###
	### Version: 4.0 	   ###
	### Written by: Alexander Jim H. Abenoja	   ###
	### License: AGPLv2	   ###
	###################################################

	require_once('./php/UIHandler.php');
	require_once('./php/CRMDefaults.php');
    require_once('./php/LanguageHandler.php');
    include('./php/Session.php');

	$ui = \creamy\UIHandler::getInstance();
	$lh = \creamy\LanguageHandler::getInstance();
	$user = \creamy\CreamyUser::currentUser();
	
	$perm = $ui->goGetPermissions('inbound,ivr,did', $_SESSION['usergroup']);
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Inbound</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        
        <?php print $ui->standardizedThemeCSS(); ?>
    	
    	<!-- DATA TABLES -->
        <link href="css/datatables/dataTables.bootstrap.css" rel="stylesheet" type="text/css" />
       
        <?php print $ui->creamyThemeCSS(); ?>
		
		<!-- Bootstrap Color Picker -->
  		<link rel="stylesheet" href="adminlte/colorpicker/bootstrap-colorpicker.min.css">
		
		<!-- Data Tables -->
        <script src="js/plugins/datatables/jquery.dataTables.js" type="text/javascript"></script>
        <script src="js/plugins/datatables/dataTables.bootstrap.js" type="text/javascript"></script>

     	<!-- bootstrap color picker -->
		<script src="adminlte/colorpicker/bootstrap-colorpicker.min.js"></script>

     	<!-- SELECT2-->
   		<link rel="stylesheet" href="theme_dashboard/select2/dist/css/select2.css">
   		<link rel="stylesheet" href="theme_dashboard/select2-bootstrap-theme/dist/select2-bootstrap.css">
   		<!-- SELECT2-->
   		<script src="theme_dashboard/select2/dist/js/select2.js"></script>
    </head>
    
    <?php print $ui->creamyBody(); ?>

        <div class="wrapper">	
        <!-- header logo: style can be found in header.less -->
		<?php print $ui->creamyHeader($user); ?>
            <!-- Left side column. contains the logo and sidebar -->
			<?php print $ui->getSidebar($user->getUserId(), $user->getUserName(), $user->getUserRole(), $user->getUserAvatar()); ?>

            <!-- Right side column. Contains the navbar and content of the page -->
            <aside class="right-side">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <h1>
                        <?php $lh->translateText("telephony"); ?>
                        <small><?php $lh->translateText("inbound_management"); ?></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="./index.php"><i class="fa fa-phone"></i> <?php $lh->translateText("home"); ?></a></li>
                        <li><?php $lh->translateText("telephony"); ?></li>
						<li class="active"><?php $lh->translateText("inbound"); ?>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">
                <?php if ($perm->inbound->inbound_read !== 'N' || $perm->ivr->ivr_read !== 'N' || $perm->did->did_read !== 'N') { ?>

<?php
	/*
	 * APIs used
	 */

	$ingroup = $ui->API_getInGroups();

	$ivr = $ui->API_getIVR();
	
	$phonenumber = $ui->API_getPhoneNumber();

?>
			<div class="panel panel-default">
				<div class="panel-body">
					<legend>Inbound: <small>Ingroups, Call Menus, Phone Numbers</small> </legend>

		            <div role="tabpanel">
						
						<ul role="tablist" class="nav nav-tabs nav-justified">

						<!-- In-group panel tabs-->
						<?php
						$toggleInbound = ' class="active"';
						$toggleIVR = '';
						$toggleDID = '';
						$activeInbound = ' active';
						$activeIVR = '';
						$activeDID = '';
						if ($perm->inbound->inbound_read === 'N') {
							$toggleInbound = ' class="hidden"';
							$activeInbound = '';
						}
						if ($perm->ivr->ivr_read === 'N') { $toggleIVR = ' class="hidden"'; }
						if ($perm->ivr->ivr_read !== 'N' && $perm->inbound->inbound_read === 'N') {
							$toggleIVR = ' class="active"';
							$activeIVR = ' active';
						}
						if ($perm->did->did_read === 'N') { $toggleDID = ' class="hidden"'; }
						if ($perm->did->did_read !== 'N' && ($perm->inbound->inbound_read === 'N' && $perm->ivr->ivr_read === 'N')) {
							$toggleDID = ' class="active"';
							$activeDID = ' active';
						}
						?>
							 <li role="presentation"<?=$toggleInbound?>>
								<a href="#T_ingroup" aria-controls="T_ingroup" role="tab" data-toggle="tab" class="bb0">
								    In-Groups</a>
							 </li>
						<!-- IVR panel tab -->
							 <li role="presentation"<?=$toggleIVR?>>
								<a href="#T_ivr" aria-controls="T_ivr" role="tab" data-toggle="tab" class="bb0">
								    Interactive Voice Response (IVR) Menus </a>
							 </li>
						<!-- DID panel tab -->
							 <li role="presentation"<?=$toggleDID?>>
								<a href="#T_phonenumber" aria-controls="T_phonenumber" role="tab" data-toggle="tab" class="bb0">
								    Phone Numbers (DIDs/TFNs) </a>
							 </li>
						  </ul>
						  
						<!-- Tab panes-->
						<div class="tab-content bg-white">

							<!--==== In-group ====-->
							<div id="T_ingroup" role="tabpanel" class="tab-pane<?=$activeInbound?>">
								<table class="table table-striped table-bordered table-hover" id="table_ingroup">
								   <thead>
									  <tr>
                                         <th style="color: white;">Pic</th>
										 <th>In-Group</th>
										 <th class='hide-on-low hide-on-medium'>Descriptions</th>
										 <th class='hide-on-low hide-on-medium'>Priority</th>
										 <th class='hide-on-low'>Status</th>
										 <th class='hide-on-low hide-on-medium'>Time</th>
										 <th>Action</th>
									  </tr>
								   </thead>
								   <tbody>
									   	<?php
									   		for($i=0;$i < count($ingroup->group_id);$i++){
							
												if($ingroup->active[$i] == "Y"){
													$ingroup->active[$i] = "Active";
												}else{
													$ingroup->active[$i] = "Inactive";
												}

											$action_INGROUP = $ui->getUserActionMenuForInGroups($ingroup->group_id[$i], $perm);

									   	?>	
											<tr>
                                                <td><avatar username='<?php echo $ingroup->group_name[$i];?>' :size='36'></avatar></td>
												<td><strong><?php if ($perm->inbound->inbound_update !== 'N') { echo '<a class="edit-ingroup" data-id="'.$ingroup->group_id[$i].'">'; } ?><?php echo $ingroup->group_id[$i];?><?php if ($perm->inbound->inbound_update !== 'N') { echo '</a>'; } ?></strong></td>
												<td class='hide-on-low hide-on-medium'><?php echo $ingroup->group_name[$i];?></td>
												<td class='hide-on-low hide-on-medium'><?php echo $ingroup->queue_priority[$i];?></td>
												<td class='hide-on-low'><?php echo $ingroup->active[$i];?></td>
												<td class='hide-on-low hide-on-medium'><?php echo $ingroup->call_time_id[$i];?></td>
												<td><?php echo $action_INGROUP;?></td>
											</tr>
										<?php
											}
										?>
								   </tbody>
								</table>
							</div>
							
							<!--==== IVR ====-->
							<div id="T_ivr" role="tabpanel" class="tab-pane<?=$activeIVR?>">
								<table class="table table-striped table-bordered table-hover" id="table_ivr">
								   <thead>
									  <tr>
                                         <th style="color: white;">Pic</th>
										 <th>Menu ID</th>
										 <th class='hide-on-medium hide-on-low'>Descriptions</th>
										 <th class='hide-on-medium hide-on-low'>Prompt</th>
										 <th class='hide-on-medium hide-on-low'>Timeout</th>
										 <th>Action</th>
									  </tr>
								   </thead>
								   <tbody>
									   	<?php
									   		for($i=0;$i < count($ivr->menu_id);$i++){

											$action_IVR = $ui->ActionMenuForIVR($ivr->menu_id[$i], $ivr->menu_name[$i], $perm);

									   	?>	
											<tr>
                                                <td><avatar username='<?php echo $ivr->menu_name[$i];?>' :size='36'></avatar></td>
												<td><strong><?php if ($perm->ivr->ivr_update !== 'N') { echo '<a class="edit-ivr" data-id="'.$ivr->menu_id[$i].'">'; } ?><?php echo $ivr->menu_id[$i];?><?php if ($perm->ivr->ivr_update !== 'N') { echo '</a>'; } ?></strong></td>
												<td class='hide-on-medium hide-on-low'><?php echo $ivr->menu_name[$i];?></td>
												<td class='hide-on-medium hide-on-low'><?php echo $ivr->menu_prompt[$i];?></td>
												<td class='hide-on-medium hide-on-low'><?php echo $ivr->menu_timeout[$i];?></td>
												<td><?php echo $action_IVR;?></td>
											</tr>
										<?php
											}
										?>
								   </tbody>
								</table>
							</div>

							<!--==== phonenumber / DID ====-->
							<div id="T_phonenumber" class="tab-pane<?=$activeDID?>">
								<table class="table table-striped table-bordered table-hover" id="table_did">
								   <thead>
									  <tr>
                                         <th style="color: white;">Pic</th>
										 <th>Phone Numbers</th>
										 <th class='hide-on-medium hide-on-low'>Description</th>
										 <th class='hide-on-medium hide-on-low'>Status</th>
										 <th class='hide-on-medium hide-on-low'>Route</th>
										 <th>Action</th>
									  </tr>
								   </thead>
								   <tbody>
									   	<?php
									   		for($i=0;$i < count($phonenumber->did_pattern);$i++){

									   			if($phonenumber->active[$i] == "Y"){
													$phonenumber->active[$i] = "Active";
												}else{
													$phonenumber->active[$i] = "Inactive";
												}

												if($phonenumber->did_route[$i] == "IN_GROUP"){
													$phonenumber->did_route[$i] = "IN-GROUP";
												}
												if($phonenumber->did_route[$i] == "EXTENSION"){
													$phonenumber->did_route[$i] = "CUSTOM EXTENSIONSION";
												}

											$action_DID = $ui->getUserActionMenuForDID($phonenumber->did_id[$i], $phonenumber->did_description[$i], $perm);

									   	?>	
											<tr>
                                                <td><avatar username='<?php echo $phonenumber->did_description[$i];?>' :size='36'></avatar></td>
												<td><strong><?php if ($perm->did->did_update !== 'N') { echo '<a class="edit-phonenumber" data-id="'.$phonenumber->did_id[$i].'">'; } ?><?php echo $phonenumber->did_pattern[$i];?><?php if ($perm->inbound->inbound_update !== 'N') { echo '</a>'; } ?></strong></td>
												<td class='hide-on-medium hide-on-low'><?php echo $phonenumber->did_description[$i];?></td>
												<td class='hide-on-medium hide-on-low'><?php echo $phonenumber->active[$i];?></td>
												<td class='hide-on-medium hide-on-low'><?php echo $phonenumber->did_route[$i];?></td>
												<td><?php echo $action_DID;?></td>
											</tr>
										<?php
											}
										?>
								   </tbody>
								</table>
							</div>

						</div><!-- END tab content-->

							<!-- /fila con acciones, formularios y demás -->
							<?php
								} else {
									print $ui->calloutErrorMessage($lh->translationFor("you_dont_have_permission"));
								}
							?>
							
						<div class="bottom-menu skin-blue<?php if ($perm->inbound->inbound_create == 'N' && $perm->ivr->ivr_create == 'N' && $perm->did->did_create == 'N') { echo " hidden"; } ?>">
							<div class="action-button-circle" data-toggle="modal">
								<?php print $ui->getCircleButton("inbound", "plus"); ?>
							</div>
							<div class="fab-div-area" id="fab-div-area">
								<?php
								$menu = 3;
								$menuHeight = '250px';
								$hideInbound = '';
								$hideIVR = '';
								$hideDID = '';
								if ($perm->inbound->inbound_create === 'N') {
									$menu--;
									$hideInbound = ' hidden';
								}
								if ($perm->ivr->ivr_create === 'N') {
									$menu--;
									$hideIVR = ' hidden';
								}
								if ($perm->did->did_create === 'N') {
									$menu--;
									$hideDID = ' hidden';
								}
								if ($menu < 3) { $menuHeight = '170px'; }
								if ($menu < 2) { $menuHeight = '110px'; }
								?>
								<ul class="fab-ul" style="height: <?=$menuHeight?>;">
									<li class="li-style<?=$hideInbound?>"><a class="fa fa-users fab-div-item" data-toggle="modal" data-target="#add_ingroups" title="Create an Ingroup"></a></li><?php if ($hideInbound === '') { echo '<br/>'; } ?>
									<li class="li-style<?=$hideIVR?>"><a class="fa fa-volume-control-phone fab-div-item" data-toggle="modal" aria-hidden="true" data-target="#add_ivr" title="Add an Interactive Voice Recording"></a></li><?php if ($hideIVR === '') { echo '<br/>'; } ?>
									<li class="li-style<?=$hideDID?>"><a class="fa fa-phone-square fab-div-item" data-toggle="modal" data-target="#add_phonenumbers" title="Add a Phone Number / DID / TFN"> </a></li>
								</ul>
							</div>
						</div>
					</div>
				</div><!-- /. body -->
			</div><!-- /. panel -->
        </section><!-- /.content -->
    </aside><!-- /.right-side -->
	<?php print $ui->getRightSidebar($user->getUserId(), $user->getUserName(), $user->getUserAvatar()); ?>
</div><!-- ./wrapper -->

<?php
	/*
	 * APIs for getting lists for the some of the forms
	 */
	$users = $ui->API_goGetAllUserLists();
	$user_groups = $ui->API_goGetUserGroupsList();
	$ingroups = $ui->API_getInGroups();
	$voicemails = $ui->API_goGetVoiceMails();
	$phones = $ui->API_getPhonesList();
	$ivr = $ui->API_getIVR();
	$scripts = $ui->API_goGetAllScripts();
	$voicefiles = $ui->API_GetVoiceFilesList();
	$calltimes = $ui->getCalltimes();
?>


<!-- TELEPHONY INBOUND MODALS -->

	<!-- ADD INGROUP MODAL -->
		<div class="modal fade" id="add_ingroups" aria-labelledby="ingroup_modal" >
        <div class="modal-dialog" role="document">
            <div class="modal-content">

			<!-- Header -->
				<div class="modal-header">
					<h4 class="modal-title animated bounceInRight" id="ingroup_modal">
						<i class="fa fa-info-circle" title="A step by step wizard that allows you to create ingroups."></i> 
						<b>In-Group Wizard » Create New Ingroup</b>
						<button type="button" class="close" data-dismiss="modal" aria-label="close_ingroup"><span aria-hidden="true">&times;</span></button>
					</h4>
				</div>
				<div class="modal-body wizard-content">
				
				<form action="AddTelephonyIngroup.php" method="POST" id="create_ingroup" role="form">
					<div class="row">
						<h4>Group Details
                           <br>
                           <small>Fill up group details then assign to a user group.</small>
                        </h4>
                        <fieldset>
							<div class="form-group mt">
								<label class="col-sm-3 control-label" for="groupid">Group ID:</label>
								<div class="col-sm-9 mb">
									<input type="text" name="groupid" id="groupid" class="form-control" placeholder="Group ID (Mandatory)" title="Must be 2-20 characters in length." maxlength="20" minlength="2" required>
								</div>
							</div>
							<div class="form-group">		
								<label class="col-sm-3 control-label" for="groupname">Group Name</label>
								<div class="col-sm-9 mb">
									<input type="text" name="groupname" id="groupname" class="form-control" placeholder="Group Name (Mandatory)" title="Must be 2-20 characters in length." maxlength="20" minlength="2" required>
								</div>
							</div>
							<div class="form-group">		
								<label class="col-sm-3 control-label" for="color">Group Color</label>
								<div class="col-sm-9 mb">
						            <input type="text" class="form-control colorpicker" name="color" id="color" value="#fffff">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label" for="active">Active</label>
								<div class="col-sm-9 mb">
									<select name="active" id="active" class="form-control">
										<option value="Y" selected>Yes</option>
										<option value="N">No</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label" for="web_form">Web Form</label>
								<div class="col-sm-9 mb">
									<input type="url" name="web_form" id="web_form" class="form-control" placeholder="Place a valid URL here... ">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label" for="user_group">User Group</label>
								<div class="col-sm-9 mb">
									<select id="user_group" class="form-control select2-1" name="user_group" style="width:100%;">
										<?php
											for($i=0;$i<count($user_groups->user_group);$i++){
										?>
											<option value="<?php echo $user_groups->user_group[$i];?>">  <?php echo $user_groups->user_group[$i]." - ".$user_groups->group_name[$i];?>  </option>
										<?php
											}
										?>
									</select>
								</div>
							</div>
						</fieldset>
						<h4>Other Settings
                           <br>
                           <small>Settings for the created group</small>
                        </h4>
                        <fieldset>
							<div class="form-group mt">
								<label class="col-sm-3 control-label" for="ingroup_voicemail">Voicemail</label>
								<div class="col-sm-9 mb">	
									<select name="ingroup_voicemail" id="ingroup_voicemail" class="form-control select2-1" style="width:100%;">
										<?php
											if($voicemails == NULL){
										?>
											<option value="" selected>--No Voicemails Available--</option>
										<?php
											}else{
											for($i=0;$i<count($voicemails->voicemail_id);$i++){
										?>
												<option value="<?php echo $voicemails->voicemail_id[$i];?>">
													<?php echo $voicemails->voicemail_id[$i].' - '.$voicemails->fullname[$i];?>
												</option>									
										<?php
												}
											}
										?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label" for="next_agent_call">Next Agent Call</label>
								<div class="col-sm-9 mb">	
									<select name="next_agent_call" id="next_agent_call" class="form-control">
											<option value="random"> Random </option>
											<option value="oldest_call_start"> Oldest Call Start </option>
											<option value="oldest_call_finish"> Oldest Call Finish </option>
											<option value="overall_user_level"> Overall User Lever </option>
											<option value="inbound_group_rank"> Inbound Group Rank </option>
											<option value="campaign_rank"> Campaign Rank </option>
											<option value="fewest_calls"> Fewest Calls </option>
											<option value="fewest_calls_campaign"> Fewest Calls Campaign </option>
											<option value="longest_wait_time"> Longest Wait Time </option>
											<option value="ring_all"> Ring All </option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label" for="display">Fronter Display</label>
								<div class="col-sm-9 mb">
									<select name="display" id="display" class="form-control">
										<option value="N" selected>No</option>
										<option value="Y">Yes</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label" for="script">Script</label>
								<div class="col-sm-9 mb">	
									<select name="script" id="script" class="form-control select2-1" style="width:100%;">
										<option value="NONE">--- NONE --- </option>
										<?php
											for($i=0;$i<count($scripts->script_id);$i++){
										?>
											<option value="<?php echo $scripts->script_id[$i];?>">
												<?php echo $scripts->script_id[$i].' - '.$scripts->script_name[$i];?>
											</option>									
										<?php
											}
										?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label" for="call_launch">Get Call Launch</label>
								<div class="col-sm-9 mb">	
									<select name="call_launch" id="call_launch" class="form-control">
											<option value="NONE"> NONE </option>
											<option value="SCRIPT"> SCRIPT </option>
											<option value="WEBFORM"> WEBFORM </option>
											<option value="WEBFORMTWO"> WEBFORMTWO </option>
											<option value="FORM"> FORM </option>
											<option value="EMAIL"> EMAIL </option>
									</select>
								</div>
							</div>
						</fieldset>
					</div><!-- end of step -->
				
				</form>

				</div> <!-- end of modal body -->
			</div>
		</div>
	</div><!-- end of modal -->
	
	<!-- ADD IVR MODAL -->
		<div class="modal fade" id="add_ivr" aria-labelledby="ivr_modal" >
        <div class="modal-dialog modal-lg" role="document" style="height:90%;">
            <div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title animated bounceInRight" id="ivr_modal">
						<i class="fa fa-info-circle" title="A step by step wizard that allows you to create IVR."></i> 
						<b>Call Menu Wizard » Create New Call Menu</b>
						<button type="button" class="close" data-dismiss="modal" aria-label="close_did"><span aria-hidden="true">&times;</span></button>
					</h4>
				</div>
				<div class="modal-body wizard-content">
				
				<form action="AddTelephonyIVR.php" method="POST" id="create_ivr" role="form">
					<div class="row">
					<h4>Call Menu Details
					   <br>
					   <small>Enter Call Menu Details</small>
					</h4>
					<fieldset>
						<div class="form-group mt">
							<label class="col-sm-3 control-label" for="menu_id">Menu ID:</label>
							<div class="col-sm-8 mb">
								<input type="text" name="menu_id" id="menu_id" class="form-control" placeholder="Menu ID (Mandatory)" minlength="4" title="Minimum of 4 characters" required>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label" for="menu_name">Menu Name</label>
							<div class="col-sm-8 mb">
								<input type="text" name="menu_name" id="menu_name" class="form-control" placeholder="Menu Name (Mandatory)" required>
							</div>
						</div>
						
						<div class="form-group">
							<label class="col-sm-3 control-label" for="menu_prompt">Menu Greeting</label>
							<div class="col-sm-8 mb">
								<select name="menu_prompt" id="menu_prompt" class="form-control select2-1" style="width:100%;">
									<option value="goWelcomeIVR" selected>-- Default Value --</option>
									<?php
										for($i=0;$i<count($voicefiles->file_name);$i++){
											$file = substr($voicefiles->file_name[$i], 0, -4);
									?>
										<option value="<?php echo $file;?>"><?php echo $file;?></option>		
									<?php
										}
									?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label" for="menu_timeout">Menu Timeout</label>
							<div class="col-sm-8 mb">
								<input type="number" name="menu_timeout" id="menu_timeout" class="form-control" value="10" min="0" required>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label" for="menu_timeout_prompt">Timeout Greeting</label>
							<div class="col-sm-8 mb">
								<select name="menu_timeout_prompt " id="menu_timeout_prompt" class="form-control select2-1" style="width:100%;">
									<option value="" selected>-- Default Value --</option>
									<?php
										for($i=0;$i<count($voicefiles->file_name);$i++){
											$file = substr($voicefiles->file_name[$i], 0, -4);
									?>
										<option value="<?php echo $file;?>"><?php echo $file;?></option>		
									<?php
										}
									?>				
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label" for="menu_invalid_prompt">Invalid Greeting</label>
							<div class="col-sm-8 mb">
								<select name="menu_invalid_prompt" id="menu_invalid_prompt" class="form-control select2-1" style="width:100%;">
									<option value="" selected>-- Default Value --</option>	
									<?php
										for($i=0;$i<count($voicefiles->file_name);$i++){
											$file = substr($voicefiles->file_name[$i], 0, -4);
									?>
										<option value="<?php echo $file;?>"><?php echo $file;?></option>		
									<?php
										}
									?>				
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label" for="menu_repeat">Menu Repeat</label>
							<div class="col-sm-8 mb">
								<input type="number" name="menu_repeat" id="menu_repeat" class="form-control" value="2" min="0" required>
							</div>
						</div>
						
						<div class="form-group" style="display:none;">
							<label class="col-sm-3 control-label" for="menu_time_check">Menu Time Check</label>
							<div class="col-sm-8 mb">
								<select name="menu_time_check" id="menu_time_check" class="form-control">
									<option value="0" > NO </option>
									<option value="1" > YES </option>		
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label" for="call_time_id">Call Time: </label>
							<div class="col-sm-8 mb">
								<select name="call_time_id" id="call_time_id" class="form-control select2-1" style="width:100%;">
									<?php
										for($x=0; $x<count($calltimes->call_time_id);$x++){
									?>
											<option value="<?php echo $calltimes->call_time_id[$x];?>"> <?php echo $calltimes->call_time_id[$x].' - '.$calltimes->call_time_name[$x]; ?> </option>
									<?php
										}
									?>
								</select>
							</div>
						</div>
						<div class="form-group" style="display:none;">
							<label class="col-sm-3 control-label" for="track_in_vdac">Track call in realtime report: </label>
							<div class="col-sm-8 mb"> 
								<select name="track_in_vdac" id="track_in_vdac" class="form-control">
									<option value="0" >0 - No Realtime Tracking</option>
									<option value="1" selected>1 - Realtime Tracking</option>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label" for="tracking_group">Tracking Group</label>
							<div class="col-sm-8 mb">
								<select name="tracking_group" id="tracking_group" class="form-control select2-1" style="width:100%;">
									<option value="CALLMENU">CALLMENU - Default</option>
								<?php
									for($i=0;$i<count($ingroups->group_id);$i++){
								?>
									<option value="<?php echo $ingroups->group_id[$i];?>">
										<?php echo $ingroups->group_id[$i].' - '.$ingroups->group_name[$i];?>
									</option>									
								<?php
									}
								?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label" for="user_groups">User Groups</label>
							<div class="col-sm-8 mb">
								<select name="user_groups" id="user_groups" class="form-control select2-1" style="width:100%;">
										<option value="---ALL---"> - - - ALL USER GROUPS - - - </option>
									<?php
										for($i=0;$i<count($user_groups->user_group);$i++){
									?>
										<option value="<?php echo $user_groups->user_group[$i];?>">  <?php echo $user_groups->group_name[$i];?>  </option>
									<?php
										}
									?>		
								</select>
							</div>
						</div>
					</fieldset>
					
					<!-- STEP 2 -->
					<h4>Call Menu Entry
					   <br>
					   <small>Set Default Call Menu Entry</small>
					</h4>
					<fieldset>
						<div class="form-group">
							<div class="col-lg-4"><hr/></div>
							<div class="col-lg-4 mt mb">
								<center><strong>Add New Call Menu Options</strong></center>
							</div>
							<div class="col-lg-4"><hr/></div>
						</div>
						<div id="static_div">
							<div class="option_div_0">
								<div class="form-group">
									<div class="col-lg-12">
										<div class="col-lg-2">
											Option:
											<select class="form-control route_option" name="option[]">
												<option selected></option>
												<?php
													for($i=0; $i <= 9; $i++){
														echo '<option value="'.$i.'">'.$i.'</option>';
													}
												?>
												<option value="#">#</option>
												<option value="*">*</option>
												<option value="TIMECHECK">TIMECHECK</option>
												<option value="INVALID">INVALID</option>
											</select>
										</div>
										<div class="col-lg-7">
											Desription: 
											<input type="text" name="route_desc[]" id="" class="form-control route_desc_0" placeholder="Description"/>
										</div>
										<div class="col-lg-3">
											Route:
											<select class="form-control route_menu_0" name="route_menu[]">
												<option selected value=""></option>
												<option value="CALLMENU">Call Menu / IVR</option>
												<option value="INGROUP">In-group</option>
												<option value="DID">DID</option>
												<option value="HANGUP">Hangup</option>
												<option value="EXTENSION">Custom Extension</option>
												<option value="PHONE">Phone</option>
												<option value="VOICEMAIL">Voicemail</option>
												<option value="AGI">AGI</option>
											</select>
										</div>
										<div class="col-lg-1 btn-remove"></div>
									</div>
								</div>
								<div class="form-group">
									<div class="col-lg-12 option_menu_0 mb mt">
										<!-- CALL MENU -->
											<div class="route_callmenu_0" style="display:none;">
												<label class="col-sm-3 control-label">Call Menu: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($ivr->menu_id);$i++){
															echo "<option value=".$ivr->menu_id[$i].">".$ivr->menu_id[$i]." - ".$ivr->menu_name[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- IN GROUP -->
											<div class="route_ingroup_0" style="display:none;">
												<label class="col-sm-3 control-label">In Group: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($ingroup->group_id);$i++){
															echo "<option value=".$ingroup->group_id[$i].">".$ingroup->group_id[$i]." - ".$ingroup->group_name[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- DID -->
											<div class="route_did_0" style="display:none;">
												<label class="col-sm-3 control-label">DID: </label>
												<div class="col-sm-6">
													<select class="col-sm-6 select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($phonenumber->did_pattern);$i++){
															echo "<option value=".$phonenumber->did_id[$i].">".$phonenumber->did_pattern[$i]." - ".$phonenumber->did_description[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- HANGUP -->
											<div class="route_hangup_0" style="display:none;">
												<label class="col-sm-3 control-label">Audio File: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i<count($voicefiles->file_name);$i++){
															$file = substr($voicefiles->file_name[$i], 0, -4);
															echo "<option value=".$file.">".$file."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- EXTENSION -->
											<div class="route_exten_0" style="display:none;">
												<div class="col-sm-6">
													<label class="col-sm-3 control-label">Extension: </label>
													<div class="col-sm-9">
														<input type="text" class="form-control" name="option_route_value[]" value="" id="option_route_value_0" />
													</div>
												</div>
												<div class="col-sm-6">
													<label class="col-sm-3 control-label">Context: </label>
													<div class="col-sm-9">
														<input type="text" class="form-control" name="option_route_value_context[]" value="" id="option_route_value_context_0" />
													</div>
												</div>
											</div>
										<!-- PHONE -->
											<div class="route_phone_0" style="display:none;">
												<label class="col-sm-3 control-label">Phone: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($phones->extension);$i++){
															echo "<option value=".$phones->extension[$i].">".$phones->extension[$i]." - ".$phones->server_ip[$i]." - ".$phones->dialplan_number[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- VOICEMAIL -->
											<div class="route_voicemail_0" style="display:none;">
												<label class="col-sm-3 control-label">Voicemail Box: </label>
												<div class="col-sm-6">
													<select class="col-sm-6 select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($voicemails->voicemail_id);$i++){
															echo "<option value=".$voicemails->voicemail_id[$i].">".$voicemails->voicemail_id[$i]." - ".$voicemails->full_name[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- AGI -->
											<div class="route_agi_0" style="display:none;">
												<label class="col-sm-3 control-label">AGI: </label>
												<div class="col-sm-6">
													<input type="text" class="form-control" name="option_route_value[]" value="" maxlength="255" size="50">
												</div>
											</div>
									</div>
								</div>
							</div>
							<div class="option_div_1">
								<div class="form-group">
									<div class="col-lg-12">
										<div class="col-lg-2">
											Option:
											<select class="form-control route_option" name="option[]">
												<option selected></option>
												<?php
													for($i=0; $i <= 9; $i++){
														echo '<option value="'.$i.'">'.$i.'</option>';
													}
												?>
												<option value="#">#</option>
												<option value="*">*</option>
												<option value="TIMECHECK">TIMECHECK</option>
												<option value="INVALID">INVALID</option>
											</select>
										</div>
										<div class="col-lg-7">
											Desription: 
											<input type="text" name="route_desc[]" id="" class="form-control route_desc_1" placeholder="Description"/>
										</div>
										<div class="col-lg-3">
											Route:
											<select class="form-control route_menu_1" name="route_menu[]">
												<option selected value=""></option>
												<option value="CALLMENU">Call Menu / IVR</option>
												<option value="INGROUP">In-group</option>
												<option value="DID">DID</option>
												<option value="HANGUP">Hangup</option>
												<option value="EXTENSION">Custom Extension</option>
												<option value="PHONE">Phone</option>
												<option value="VOICEMAIL">Voicemail</option>
												<option value="AGI">AGI</option>
											</select>
										</div>
										<div class="col-lg-1 btn-remove"></div>
									</div>
								</div>
								<div class="form-group">
									<div class="col-lg-12 option_menu_1 mb mt">
										<!-- CALL MENU -->
											<div class="route_callmenu_1" style="display:none;">
												<label class="col-sm-3 control-label">Call Menu: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($ivr->menu_id);$i++){
															echo "<option value=".$ivr->menu_id[$i].">".$ivr->menu_id[$i]." - ".$ivr->menu_name[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- IN GROUP -->
											<div class="route_ingroup_1" style="display:none;">
												<label class="col-sm-3 control-label">In Group: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($ingroup->group_id);$i++){
															echo "<option value=".$ingroup->group_id[$i].">".$ingroup->group_id[$i]." - ".$ingroup->group_name[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- DID -->
											<div class="route_did_1" style="display:none;">
												<label class="col-sm-3 control-label">DID: </label>
												<div class="col-sm-6">
													<select class="col-sm-6 select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($phonenumber->did_pattern);$i++){
															echo "<option value=".$phonenumber->did_id[$i].">".$phonenumber->did_pattern[$i]." - ".$phonenumber->did_description[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- HANGUP -->
											<div class="route_hangup_1" style="display:none;">
												<label class="col-sm-3 control-label">Audio File: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i<count($voicefiles->file_name);$i++){
															$file = substr($voicefiles->file_name[$i], 0, -4);
															echo "<option value=".$file.">".$file."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- EXTENSION -->
											<div class="route_exten_1" style="display:none;">
												<div class="col-sm-6">
													<label class="col-sm-3 control-label">Extension: </label>
													<div class="col-sm-9">
														<input type="text" class="form-control" name="option_route_value[]" value="" id="option_route_value_0" />
													</div>
												</div>
												<div class="col-sm-6">
													<label class="col-sm-3 control-label">Context: </label>
													<div class="col-sm-9">
														<input type="text" class="form-control" name="option_route_value_context[]" value="" id="option_route_value_context_0" />
													</div>
												</div>
											</div>
										<!-- PHONE -->
											<div class="route_phone_1" style="display:none;">
												<label class="col-sm-3 control-label">Phone: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($phones->extension);$i++){
															echo "<option value=".$phones->extension[$i].">".$phones->extension[$i]." - ".$phones->server_ip[$i]." - ".$phones->dialplan_number[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- VOICEMAIL -->
											<div class="route_voicemail_1" style="display:none;">
												<label class="col-sm-3 control-label">Voicemail Box: </label>
												<div class="col-sm-6">
													<select class="col-sm-6 select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($voicemails->voicemail_id);$i++){
															echo "<option value=".$voicemails->voicemail_id[$i].">".$voicemails->voicemail_id[$i]." - ".$voicemails->full_name[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- AGI -->
											<div class="route_agi_1" style="display:none;">
												<label class="col-sm-3 control-label">AGI: </label>
												<div class="col-sm-6">
													<input type="text" class="form-control" name="option_route_value[]" value="" maxlength="255" size="50">
												</div>
											</div>
									</div>
								</div>
							</div>
							<div class="option_div_2">
								<div class="form-group">
									<div class="col-lg-12">
										<div class="col-lg-2">
											Option:
											<select class="form-control route_option" name="option[]">
												<option selected></option>
												<?php
													for($i=0; $i <= 9; $i++){
														echo '<option value="'.$i.'">'.$i.'</option>';
													}
												?>
												<option value="#">#</option>
												<option value="*">*</option>
												<option value="TIMECHECK">TIMECHECK</option>
												<option value="INVALID">INVALID</option>
											</select>
										</div>
										<div class="col-lg-7">
											Desription: 
											<input type="text" name="route_desc[]" id="" class="form-control route_desc_2" placeholder="Description"/>
										</div>
										<div class="col-lg-3">
											Route:
											<select class="form-control route_menu_2" name="route_menu[]">
												<option selected value=""></option>
												<option value="CALLMENU">Call Menu / IVR</option>
												<option value="INGROUP">In-group</option>
												<option value="DID">DID</option>
												<option value="HANGUP">Hangup</option>
												<option value="EXTENSION">Custom Extension</option>
												<option value="PHONE">Phone</option>
												<option value="VOICEMAIL">Voicemail</option>
												<option value="AGI">AGI</option>
											</select>
										</div>
										<div class="col-lg-1 btn-remove"></div>
									</div>
								</div>
								<div class="form-group">
									<div class="col-lg-12 option_menu_2 mb mt">
										<!-- CALL MENU -->
											<div class="route_callmenu_2" style="display:none;">
												<label class="col-sm-3 control-label">Call Menu: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($ivr->menu_id);$i++){
															echo "<option value=".$ivr->menu_id[$i].">".$ivr->menu_id[$i]." - ".$ivr->menu_name[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- IN GROUP -->
											<div class="route_ingroup_2" style="display:none;">
												<label class="col-sm-3 control-label">In Group: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($ingroup->group_id);$i++){
															echo "<option value=".$ingroup->group_id[$i].">".$ingroup->group_id[$i]." - ".$ingroup->group_name[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- DID -->
											<div class="route_did_2" style="display:none;">
												<label class="col-sm-3 control-label">DID: </label>
												<div class="col-sm-6">
													<select class="col-sm-6 select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($phonenumber->did_pattern);$i++){
															echo "<option value=".$phonenumber->did_id[$i].">".$phonenumber->did_pattern[$i]." - ".$phonenumber->did_description[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- HANGUP -->
											<div class="route_hangup_2" style="display:none;">
												<label class="col-sm-3 control-label">Audio File: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i<count($voicefiles->file_name);$i++){
															$file = substr($voicefiles->file_name[$i], 0, -4);
															echo "<option value=".$file.">".$file."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- EXTENSION -->
											<div class="route_exten_2" style="display:none;">
												<div class="col-sm-6">
													<label class="col-sm-3 control-label">Extension: </label>
													<div class="col-sm-9">
														<input type="text" class="form-control" name="option_route_value[]" value="" id="option_route_value_0" />
													</div>
												</div>
												<div class="col-sm-6">
													<label class="col-sm-3 control-label">Context: </label>
													<div class="col-sm-9">
														<input type="text" class="form-control" name="option_route_value_context[]" value="" id="option_route_value_context_0" />
													</div>
												</div>
											</div>
										<!-- PHONE -->
											<div class="route_phone_2" style="display:none;">
												<label class="col-sm-3 control-label">Phone: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($phones->extension);$i++){
															echo "<option value=".$phones->extension[$i].">".$phones->extension[$i]." - ".$phones->server_ip[$i]." - ".$phones->dialplan_number[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- VOICEMAIL -->
											<div class="route_voicemail_2" style="display:none;">
												<label class="col-sm-3 control-label">Voicemail Box: </label>
												<div class="col-sm-6">
													<select class="col-sm-6 select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($voicemails->voicemail_id);$i++){
															echo "<option value=".$voicemails->voicemail_id[$i].">".$voicemails->voicemail_id[$i]." - ".$voicemails->full_name[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- AGI -->
											<div class="route_agi_2" style="display:none;">
												<label class="col-sm-3 control-label">AGI: </label>
												<div class="col-sm-6">
													<input type="text" class="form-control" name="option_route_value[]" value="" maxlength="255" size="50">
												</div>
											</div>
									</div>
								</div>
							</div>
							<div class="option_div_3">
								<div class="form-group">
									<div class="col-lg-12">
										<div class="col-lg-2">
											Option:
											<select class="form-control route_option" name="option[]">
												<option selected></option>
												<?php
													for($i=0; $i <= 9; $i++){
														echo '<option value="'.$i.'">'.$i.'</option>';
													}
												?>
												<option value="#">#</option>
												<option value="*">*</option>
												<option value="TIMECHECK">TIMECHECK</option>
												<option value="INVALID">INVALID</option>
											</select>
										</div>
										<div class="col-lg-7">
											Desription: 
											<input type="text" name="route_desc[]" id="" class="form-control route_desc_3" placeholder="Description"/>
										</div>
										<div class="col-lg-3">
											Route:
											<select class="form-control route_menu_3" name="route_menu[]">
												<option selected value=""></option>
												<option value="CALLMENU">Call Menu / IVR</option>
												<option value="INGROUP">In-group</option>
												<option value="DID">DID</option>
												<option value="HANGUP">Hangup</option>
												<option value="EXTENSION">Custom Extension</option>
												<option value="PHONE">Phone</option>
												<option value="VOICEMAIL">Voicemail</option>
												<option value="AGI">AGI</option>
											</select>
										</div>
										<div class="col-lg-1 btn-remove"></div>
									</div>
								</div>
								<div class="form-group">
									<div class="col-lg-12 option_menu_3 mb mt">
										<!-- CALL MENU -->
											<div class="route_callmenu_3" style="display:none;">
												<label class="col-sm-3 control-label">Call Menu: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($ivr->menu_id);$i++){
															echo "<option value=".$ivr->menu_id[$i].">".$ivr->menu_id[$i]." - ".$ivr->menu_name[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- IN GROUP -->
											<div class="route_ingroup_3" style="display:none;">
												<label class="col-sm-3 control-label">In Group: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($ingroup->group_id);$i++){
															echo "<option value=".$ingroup->group_id[$i].">".$ingroup->group_id[$i]." - ".$ingroup->group_name[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- DID -->
											<div class="route_did_3" style="display:none;">
												<label class="col-sm-3 control-label">DID: </label>
												<div class="col-sm-6">
													<select class="col-sm-6 select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($phonenumber->did_pattern);$i++){
															echo "<option value=".$phonenumber->did_id[$i].">".$phonenumber->did_pattern[$i]." - ".$phonenumber->did_description[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- HANGUP -->
											<div class="route_hangup_3" style="display:none;">
												<label class="col-sm-3 control-label">Audio File: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i<count($voicefiles->file_name);$i++){
															$file = substr($voicefiles->file_name[$i], 0, -4);
															echo "<option value=".$file.">".$file."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- EXTENSION -->
											<div class="route_exten_3" style="display:none;">
												<div class="col-sm-6">
													<label class="col-sm-3 control-label">Extension: </label>
													<div class="col-sm-9">
														<input type="text" class="form-control" name="option_route_value[]" value="" id="option_route_value_0" />
													</div>
												</div>
												<div class="col-sm-6">
													<label class="col-sm-3 control-label">Context: </label>
													<div class="col-sm-9">
														<input type="text" class="form-control" name="option_route_value_context[]" value="" id="option_route_value_context_0" />
													</div>
												</div>
											</div>
										<!-- PHONE -->
											<div class="route_phone_3" style="display:none;">
												<label class="col-sm-3 control-label">Phone: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($phones->extension);$i++){
															echo "<option value=".$phones->extension[$i].">".$phones->extension[$i]." - ".$phones->server_ip[$i]." - ".$phones->dialplan_number[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- VOICEMAIL -->
											<div class="route_voicemail_3" style="display:none;">
												<label class="col-sm-3 control-label">Voicemail Box: </label>
												<div class="col-sm-6">
													<select class="col-sm-6 select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($voicemails->voicemail_id);$i++){
															echo "<option value=".$voicemails->voicemail_id[$i].">".$voicemails->voicemail_id[$i]." - ".$voicemails->full_name[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- AGI -->
											<div class="route_agi_3" style="display:none;">
												<label class="col-sm-3 control-label">AGI: </label>
												<div class="col-sm-6">
													<input type="text" class="form-control" name="option_route_value[]" value="" maxlength="255" size="50">
												</div>
											</div>
									</div>
								</div>
							</div>
							<div class="option_div_4">
								<div class="form-group">
									<div class="col-lg-12">
										<div class="col-lg-2">
											Option:
											<select class="form-control route_option" name="option[]">
												<option selected></option>
												<?php
													for($i=0; $i <= 9; $i++){
														echo '<option value="'.$i.'">'.$i.'</option>';
													}
												?>
												<option value="#">#</option>
												<option value="*">*</option>
												<option value="TIMECHECK">TIMECHECK</option>
												<option value="INVALID">INVALID</option>
											</select>
										</div>
										<div class="col-lg-7">
											Desription: 
											<input type="text" name="route_desc[]" id="" class="form-control route_desc_4" placeholder="Description"/>
										</div>
										<div class="col-lg-3">
											Route:
											<select class="form-control route_menu_4" name="route_menu[]">
												<option selected value=""></option>
												<option value="CALLMENU">Call Menu / IVR</option>
												<option value="INGROUP">In-group</option>
												<option value="DID">DID</option>
												<option value="HANGUP">Hangup</option>
												<option value="EXTENSION">Custom Extension</option>
												<option value="PHONE">Phone</option>
												<option value="VOICEMAIL">Voicemail</option>
												<option value="AGI">AGI</option>
											</select>
										</div>
										<div class="col-lg-1 btn-remove"></div>
									</div>
								</div>
								<div class="form-group">
									<div class="col-lg-12 option_menu_4 mb mt">
										<!-- CALL MENU -->
											<div class="route_callmenu_1" style="display:none;">
												<label class="col-sm-3 control-label">Call Menu: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($ivr->menu_id);$i++){
															echo "<option value=".$ivr->menu_id[$i].">".$ivr->menu_id[$i]." - ".$ivr->menu_name[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- IN GROUP -->
											<div class="route_ingroup_4" style="display:none;">
												<label class="col-sm-3 control-label">In Group: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($ingroup->group_id);$i++){
															echo "<option value=".$ingroup->group_id[$i].">".$ingroup->group_id[$i]." - ".$ingroup->group_name[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- DID -->
											<div class="route_did_4" style="display:none;">
												<label class="col-sm-3 control-label">DID: </label>
												<div class="col-sm-6">
													<select class="col-sm-6 select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($phonenumber->did_pattern);$i++){
															echo "<option value=".$phonenumber->did_id[$i].">".$phonenumber->did_pattern[$i]." - ".$phonenumber->did_description[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- HANGUP -->
											<div class="route_hangup_4" style="display:none;">
												<label class="col-sm-3 control-label">Audio File: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i<count($voicefiles->file_name);$i++){
															$file = substr($voicefiles->file_name[$i], 0, -4);
															echo "<option value=".$file.">".$file."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- EXTENSION -->
											<div class="route_exten_4" style="display:none;">
												<div class="col-sm-6">
													<label class="col-sm-3 control-label">Extension: </label>
													<div class="col-sm-9">
														<input type="text" class="form-control" name="option_route_value[]" value="" id="option_route_value_0" />
													</div>
												</div>
												<div class="col-sm-6">
													<label class="col-sm-3 control-label">Context: </label>
													<div class="col-sm-9">
														<input type="text" class="form-control" name="option_route_value_context[]" value="" id="option_route_value_context_0" />
													</div>
												</div>
											</div>
										<!-- PHONE -->
											<div class="route_phone_4" style="display:none;">
												<label class="col-sm-3 control-label">Phone: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($phones->extension);$i++){
															echo "<option value=".$phones->extension[$i].">".$phones->extension[$i]." - ".$phones->server_ip[$i]." - ".$phones->dialplan_number[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- VOICEMAIL -->
											<div class="route_voicemail_4" style="display:none;">
												<label class="col-sm-3 control-label">Voicemail Box: </label>
												<div class="col-sm-6">
													<select class="col-sm-6 select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($voicemails->voicemail_id);$i++){
															echo "<option value=".$voicemails->voicemail_id[$i].">".$voicemails->voicemail_id[$i]." - ".$voicemails->full_name[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- AGI -->
											<div class="route_agi_4" style="display:none;">
												<label class="col-sm-3 control-label">AGI: </label>
												<div class="col-sm-6">
													<input type="text" class="form-control" name="option_route_value[]" value="" maxlength="255" size="50">
												</div>
											</div>
									</div>
								</div>
							</div>
							<div class="option_div_5">
								<div class="form-group">
									<div class="col-lg-12">
										<div class="col-lg-2">
											Option:
											<select class="form-control route_option" name="option[]">
												<option selected></option>
												<?php
													for($i=0; $i <= 9; $i++){
														echo '<option value="'.$i.'">'.$i.'</option>';
													}
												?>
												<option value="#">#</option>
												<option value="*">*</option>
												<option value="TIMECHECK">TIMECHECK</option>
												<option value="INVALID">INVALID</option>
											</select>
										</div>
										<div class="col-lg-7">
											Desription: 
											<input type="text" name="route_desc[]" id="" class="form-control route_desc_5" placeholder="Description"/>
										</div>
										<div class="col-lg-3">
											Route:
											<select class="form-control route_menu_5" name="route_menu[]">
												<option selected value=""></option>
												<option value="CALLMENU">Call Menu / IVR</option>
												<option value="INGROUP">In-group</option>
												<option value="DID">DID</option>
												<option value="HANGUP">Hangup</option>
												<option value="EXTENSION">Custom Extension</option>
												<option value="PHONE">Phone</option>
												<option value="VOICEMAIL">Voicemail</option>
												<option value="AGI">AGI</option>
											</select>
										</div>
										<div class="col-lg-1 btn-remove"></div>
									</div>
								</div>
								<div class="form-group">
									<div class="col-lg-12 option_menu_5 mb mt">
										<!-- CALL MENU -->
											<div class="route_callmenu_5" style="display:none;">
												<label class="col-sm-3 control-label">Call Menu: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($ivr->menu_id);$i++){
															echo "<option value=".$ivr->menu_id[$i].">".$ivr->menu_id[$i]." - ".$ivr->menu_name[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- IN GROUP -->
											<div class="route_ingroup_5" style="display:none;">
												<label class="col-sm-3 control-label">In Group: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($ingroup->group_id);$i++){
															echo "<option value=".$ingroup->group_id[$i].">".$ingroup->group_id[$i]." - ".$ingroup->group_name[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- DID -->
											<div class="route_did_5" style="display:none;">
												<label class="col-sm-3 control-label">DID: </label>
												<div class="col-sm-6">
													<select class="col-sm-6 select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($phonenumber->did_pattern);$i++){
															echo "<option value=".$phonenumber->did_id[$i].">".$phonenumber->did_pattern[$i]." - ".$phonenumber->did_description[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- HANGUP -->
											<div class="route_hangup_5" style="display:none;">
												<label class="col-sm-3 control-label">Audio File: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i<count($voicefiles->file_name);$i++){
															$file = substr($voicefiles->file_name[$i], 0, -4);
															echo "<option value=".$file.">".$file."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- EXTENSION -->
											<div class="route_exten_5" style="display:none;">
												<div class="col-sm-6">
													<label class="col-sm-3 control-label">Extension: </label>
													<div class="col-sm-9">
														<input type="text" class="form-control" name="option_route_value[]" value="" id="option_route_value_0" />
													</div>
												</div>
												<div class="col-sm-6">
													<label class="col-sm-3 control-label">Context: </label>
													<div class="col-sm-9">
														<input type="text" class="form-control" name="option_route_value_context[]" value="" id="option_route_value_context_0" />
													</div>
												</div>
											</div>
										<!-- PHONE -->
											<div class="route_phone_5" style="display:none;">
												<label class="col-sm-3 control-label">Phone: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($phones->extension);$i++){
															echo "<option value=".$phones->extension[$i].">".$phones->extension[$i]." - ".$phones->server_ip[$i]." - ".$phones->dialplan_number[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- VOICEMAIL -->
											<div class="route_voicemail_5" style="display:none;">
												<label class="col-sm-3 control-label">Voicemail Box: </label>
												<div class="col-sm-6">
													<select class="col-sm-6 select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($voicemails->voicemail_id);$i++){
															echo "<option value=".$voicemails->voicemail_id[$i].">".$voicemails->voicemail_id[$i]." - ".$voicemails->full_name[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- AGI -->
											<div class="route_agi_5" style="display:none;">
												<label class="col-sm-3 control-label">AGI: </label>
												<div class="col-sm-6">
													<input type="text" class="form-control" name="option_route_value[]" value="" maxlength="255" size="50">
												</div>
											</div>
									</div>
								</div>
							</div>
							<div class="option_div_6">
								<div class="form-group">
									<div class="col-lg-12">
										<div class="col-lg-2">
											Option:
											<select class="form-control route_option" name="option[]">
												<option selected></option>
												<?php
													for($i=0; $i <= 9; $i++){
														echo '<option value="'.$i.'">'.$i.'</option>';
													}
												?>
												<option value="#">#</option>
												<option value="*">*</option>
												<option value="TIMECHECK">TIMECHECK</option>
												<option value="INVALID">INVALID</option>
											</select>
										</div>
										<div class="col-lg-7">
											Desription: 
											<input type="text" name="route_desc[]" id="" class="form-control route_desc_6" placeholder="Description"/>
										</div>
										<div class="col-lg-3">
											Route:
											<select class="form-control route_menu_6" name="route_menu[]">
												<option selected value=""></option>
												<option value="CALLMENU">Call Menu / IVR</option>
												<option value="INGROUP">In-group</option>
												<option value="DID">DID</option>
												<option value="HANGUP">Hangup</option>
												<option value="EXTENSION">Custom Extension</option>
												<option value="PHONE">Phone</option>
												<option value="VOICEMAIL">Voicemail</option>
												<option value="AGI">AGI</option>
											</select>
										</div>
										<div class="col-lg-1 btn-remove"></div>
									</div>
								</div>
								<div class="form-group">
									<div class="col-lg-12 option_menu_6 mb mt">
										<!-- CALL MENU -->
											<div class="route_callmenu_6" style="display:none;">
												<label class="col-sm-3 control-label">Call Menu: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($ivr->menu_id);$i++){
															echo "<option value=".$ivr->menu_id[$i].">".$ivr->menu_id[$i]." - ".$ivr->menu_name[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- IN GROUP -->
											<div class="route_ingroup_6" style="display:none;">
												<label class="col-sm-3 control-label">In Group: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($ingroup->group_id);$i++){
															echo "<option value=".$ingroup->group_id[$i].">".$ingroup->group_id[$i]." - ".$ingroup->group_name[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- DID -->
											<div class="route_did_6" style="display:none;">
												<label class="col-sm-3 control-label">DID: </label>
												<div class="col-sm-6">
													<select class="col-sm-6 select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($phonenumber->did_pattern);$i++){
															echo "<option value=".$phonenumber->did_id[$i].">".$phonenumber->did_pattern[$i]." - ".$phonenumber->did_description[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- HANGUP -->
											<div class="route_hangup_6" style="display:none;">
												<label class="col-sm-3 control-label">Audio File: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i<count($voicefiles->file_name);$i++){
															$file = substr($voicefiles->file_name[$i], 0, -4);
															echo "<option value=".$file.">".$file."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- EXTENSION -->
											<div class="route_exten_6" style="display:none;">
												<div class="col-sm-6">
													<label class="col-sm-3 control-label">Extension: </label>
													<div class="col-sm-9">
														<input type="text" class="form-control" name="option_route_value[]" value="" id="option_route_value_0" />
													</div>
												</div>
												<div class="col-sm-6">
													<label class="col-sm-3 control-label">Context: </label>
													<div class="col-sm-9">
														<input type="text" class="form-control" name="option_route_value_context[]" value="" id="option_route_value_context_0" />
													</div>
												</div>
											</div>
										<!-- PHONE -->
											<div class="route_phone_6" style="display:none;">
												<label class="col-sm-3 control-label">Phone: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($phones->extension);$i++){
															echo "<option value=".$phones->extension[$i].">".$phones->extension[$i]." - ".$phones->server_ip[$i]." - ".$phones->dialplan_number[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- VOICEMAIL -->
											<div class="route_voicemail_6" style="display:none;">
												<label class="col-sm-3 control-label">Voicemail Box: </label>
												<div class="col-sm-6">
													<select class="col-sm-6 select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($voicemails->voicemail_id);$i++){
															echo "<option value=".$voicemails->voicemail_id[$i].">".$voicemails->voicemail_id[$i]." - ".$voicemails->full_name[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- AGI -->
											<div class="route_agi_6" style="display:none;">
												<label class="col-sm-3 control-label">AGI: </label>
												<div class="col-sm-6">
													<input type="text" class="form-control" name="option_route_value[]" value="" maxlength="255" size="50">
												</div>
											</div>
									</div>
								</div>
							</div>
							<div class="option_div_7">
								<div class="form-group">
									<div class="col-lg-12">
										<div class="col-lg-2">
											Option:
											<select class="form-control route_option" name="option[]">
												<option selected></option>
												<?php
													for($i=0; $i <= 9; $i++){
														echo '<option value="'.$i.'">'.$i.'</option>';
													}
												?>
												<option value="#">#</option>
												<option value="*">*</option>
												<option value="TIMECHECK">TIMECHECK</option>
												<option value="INVALID">INVALID</option>
											</select>
										</div>
										<div class="col-lg-7">
											Desription: 
											<input type="text" name="route_desc[]" id="" class="form-control route_desc_7" placeholder="Description"/>
										</div>
										<div class="col-lg-3">
											Route:
											<select class="form-control route_menu_7" name="route_menu[]">
												<option selected value=""></option>
												<option value="CALLMENU">Call Menu / IVR</option>
												<option value="INGROUP">In-group</option>
												<option value="DID">DID</option>
												<option value="HANGUP">Hangup</option>
												<option value="EXTENSION">Custom Extension</option>
												<option value="PHONE">Phone</option>
												<option value="VOICEMAIL">Voicemail</option>
												<option value="AGI">AGI</option>
											</select>
										</div>
										<div class="col-lg-1 btn-remove"></div>
									</div>
								</div>
								<div class="form-group">
									<div class="col-lg-12 option_menu_7 mb mt">
										<!-- CALL MENU -->
											<div class="route_callmenu_7" style="display:none;">
												<label class="col-sm-3 control-label">Call Menu: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($ivr->menu_id);$i++){
															echo "<option value=".$ivr->menu_id[$i].">".$ivr->menu_id[$i]." - ".$ivr->menu_name[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- IN GROUP -->
											<div class="route_ingroup_7" style="display:none;">
												<label class="col-sm-3 control-label">In Group: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($ingroup->group_id);$i++){
															echo "<option value=".$ingroup->group_id[$i].">".$ingroup->group_id[$i]." - ".$ingroup->group_name[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- DID -->
											<div class="route_did_7" style="display:none;">
												<label class="col-sm-3 control-label">DID: </label>
												<div class="col-sm-6">
													<select class="col-sm-6 select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($phonenumber->did_pattern);$i++){
															echo "<option value=".$phonenumber->did_id[$i].">".$phonenumber->did_pattern[$i]." - ".$phonenumber->did_description[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- HANGUP -->
											<div class="route_hangup_7" style="display:none;">
												<label class="col-sm-3 control-label">Audio File: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i<count($voicefiles->file_name);$i++){
															$file = substr($voicefiles->file_name[$i], 0, -4);
															echo "<option value=".$file.">".$file."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- EXTENSION -->
											<div class="route_exten_7" style="display:none;">
												<div class="col-sm-6">
													<label class="col-sm-3 control-label">Extension: </label>
													<div class="col-sm-9">
														<input type="text" class="form-control" name="option_route_value[]" value="" id="option_route_value_0" />
													</div>
												</div>
												<div class="col-sm-6">
													<label class="col-sm-3 control-label">Context: </label>
													<div class="col-sm-9">
														<input type="text" class="form-control" name="option_route_value_context[]" value="" id="option_route_value_context_0" />
													</div>
												</div>
											</div>
										<!-- PHONE -->
											<div class="route_phone_7" style="display:none;">
												<label class="col-sm-3 control-label">Phone: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($phones->extension);$i++){
															echo "<option value=".$phones->extension[$i].">".$phones->extension[$i]." - ".$phones->server_ip[$i]." - ".$phones->dialplan_number[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- VOICEMAIL -->
											<div class="route_voicemail_7" style="display:none;">
												<label class="col-sm-3 control-label">Voicemail Box: </label>
												<div class="col-sm-6">
													<select class="col-sm-6 select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($voicemails->voicemail_id);$i++){
															echo "<option value=".$voicemails->voicemail_id[$i].">".$voicemails->voicemail_id[$i]." - ".$voicemails->full_name[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- AGI -->
											<div class="route_agi_7" style="display:none;">
												<label class="col-sm-3 control-label">AGI: </label>
												<div class="col-sm-6">
													<input type="text" class="form-control" name="option_route_value[]" value="" maxlength="255" size="50">
												</div>
											</div>
									</div>
								</div>
							</div>
							<div class="option_div_8">
								<div class="form-group">
									<div class="col-lg-12">
										<div class="col-lg-2">
											Option:
											<select class="form-control route_option" name="option[]">
												<option selected></option>
												<?php
													for($i=0; $i <= 9; $i++){
														echo '<option value="'.$i.'">'.$i.'</option>';
													}
												?>
												<option value="#">#</option>
												<option value="*">*</option>
												<option value="TIMECHECK">TIMECHECK</option>
												<option value="INVALID">INVALID</option>
											</select>
										</div>
										<div class="col-lg-7">
											Desription: 
											<input type="text" name="route_desc[]" id="" class="form-control route_desc_8" placeholder="Description"/>
										</div>
										<div class="col-lg-3">
											Route:
											<select class="form-control route_menu_8" name="route_menu[]">
												<option selected value=""></option>
												<option value="CALLMENU">Call Menu / IVR</option>
												<option value="INGROUP">In-group</option>
												<option value="DID">DID</option>
												<option value="HANGUP">Hangup</option>
												<option value="EXTENSION">Custom Extension</option>
												<option value="PHONE">Phone</option>
												<option value="VOICEMAIL">Voicemail</option>
												<option value="AGI">AGI</option>
											</select>
										</div>
										<div class="col-lg-1 btn-remove"></div>
									</div>
								</div>
								<div class="form-group">
									<div class="col-lg-12 option_menu_8 mb mt">
										<!-- CALL MENU -->
											<div class="route_callmenu_8" style="display:none;">
												<label class="col-sm-3 control-label">Call Menu: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($ivr->menu_id);$i++){
															echo "<option value=".$ivr->menu_id[$i].">".$ivr->menu_id[$i]." - ".$ivr->menu_name[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- IN GROUP -->
											<div class="route_ingroup_8" style="display:none;">
												<label class="col-sm-3 control-label">In Group: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($ingroup->group_id);$i++){
															echo "<option value=".$ingroup->group_id[$i].">".$ingroup->group_id[$i]." - ".$ingroup->group_name[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- DID -->
											<div class="route_did_8" style="display:none;">
												<label class="col-sm-3 control-label">DID: </label>
												<div class="col-sm-6">
													<select class="col-sm-6 select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($phonenumber->did_pattern);$i++){
															echo "<option value=".$phonenumber->did_id[$i].">".$phonenumber->did_pattern[$i]." - ".$phonenumber->did_description[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- HANGUP -->
											<div class="route_hangup_8" style="display:none;">
												<label class="col-sm-3 control-label">Audio File: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i<count($voicefiles->file_name);$i++){
															$file = substr($voicefiles->file_name[$i], 0, -4);
															echo "<option value=".$file.">".$file."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- EXTENSION -->
											<div class="route_exten_8" style="display:none;">
												<div class="col-sm-6">
													<label class="col-sm-3 control-label">Extension: </label>
													<div class="col-sm-9">
														<input type="text" class="form-control" name="option_route_value[]" value="" id="option_route_value_0" />
													</div>
												</div>
												<div class="col-sm-6">
													<label class="col-sm-3 control-label">Context: </label>
													<div class="col-sm-9">
														<input type="text" class="form-control" name="option_route_value_context[]" value="" id="option_route_value_context_0" />
													</div>
												</div>
											</div>
										<!-- PHONE -->
											<div class="route_phone_8" style="display:none;">
												<label class="col-sm-3 control-label">Phone: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($phones->extension);$i++){
															echo "<option value=".$phones->extension[$i].">".$phones->extension[$i]." - ".$phones->server_ip[$i]." - ".$phones->dialplan_number[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- VOICEMAIL -->
											<div class="route_voicemail_8" style="display:none;">
												<label class="col-sm-3 control-label">Voicemail Box: </label>
												<div class="col-sm-6">
													<select class="col-sm-6 select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($voicemails->voicemail_id);$i++){
															echo "<option value=".$voicemails->voicemail_id[$i].">".$voicemails->voicemail_id[$i]." - ".$voicemails->full_name[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- AGI -->
											<div class="route_agi_8" style="display:none;">
												<label class="col-sm-3 control-label">AGI: </label>
												<div class="col-sm-6">
													<input type="text" class="form-control" name="option_route_value[]" value="" maxlength="255" size="50">
												</div>
											</div>
									</div>
								</div>
							</div>
							<div class="option_div_9">
								<div class="form-group">
									<div class="col-lg-12">
										<div class="col-lg-2">
											Option:
											<select class="form-control route_option" name="option[]">
												<option selected></option>
												<?php
													for($i=0; $i <= 9; $i++){
														echo '<option value="'.$i.'">'.$i.'</option>';
													}
												?>
												<option value="#">#</option>
												<option value="*">*</option>
												<option value="TIMECHECK">TIMECHECK</option>
												<option value="INVALID">INVALID</option>
											</select>
										</div>
										<div class="col-lg-7">
											Desription: 
											<input type="text" name="route_desc[]" id="" class="form-control route_desc_9" placeholder="Description"/>
										</div>
										<div class="col-lg-3">
											Route:
											<select class="form-control route_menu_9" name="route_menu[]">
												<option selected value=""></option>
												<option value="CALLMENU">Call Menu / IVR</option>
												<option value="INGROUP">In-group</option>
												<option value="DID">DID</option>
												<option value="HANGUP">Hangup</option>
												<option value="EXTENSION">Custom Extension</option>
												<option value="PHONE">Phone</option>
												<option value="VOICEMAIL">Voicemail</option>
												<option value="AGI">AGI</option>
											</select>
										</div>
										<div class="col-lg-1 btn-remove"></div>
									</div>
								</div>
								<div class="form-group">
									<div class="col-lg-12 option_menu_9 mb mt">
										<!-- CALL MENU -->
											<div class="route_callmenu_9" style="display:none;">
												<label class="col-sm-3 control-label">Call Menu: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($ivr->menu_id);$i++){
															echo "<option value=".$ivr->menu_id[$i].">".$ivr->menu_id[$i]." - ".$ivr->menu_name[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- IN GROUP -->
											<div class="route_ingroup_9" style="display:none;">
												<label class="col-sm-3 control-label">In Group: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($ingroup->group_id);$i++){
															echo "<option value=".$ingroup->group_id[$i].">".$ingroup->group_id[$i]." - ".$ingroup->group_name[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- DID -->
											<div class="route_did_9" style="display:none;">
												<label class="col-sm-3 control-label">DID: </label>
												<div class="col-sm-6">
													<select class="col-sm-6 select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($phonenumber->did_pattern);$i++){
															echo "<option value=".$phonenumber->did_id[$i].">".$phonenumber->did_pattern[$i]." - ".$phonenumber->did_description[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- HANGUP -->
											<div class="route_hangup_9" style="display:none;">
												<label class="col-sm-3 control-label">Audio File: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i<count($voicefiles->file_name);$i++){
															$file = substr($voicefiles->file_name[$i], 0, -4);
															echo "<option value=".$file.">".$file."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- EXTENSION -->
											<div class="route_exten_9" style="display:none;">
												<div class="col-sm-6">
													<label class="col-sm-3 control-label">Extension: </label>
													<div class="col-sm-9">
														<input type="text" class="form-control" name="option_route_value[]" value="" id="option_route_value_0" />
													</div>
												</div>
												<div class="col-sm-6">
													<label class="col-sm-3 control-label">Context: </label>
													<div class="col-sm-9">
														<input type="text" class="form-control" name="option_route_value_context[]" value="" id="option_route_value_context_0" />
													</div>
												</div>
											</div>
										<!-- PHONE -->
											<div class="route_phone_9" style="display:none;">
												<label class="col-sm-3 control-label">Phone: </label>
												<div class="col-sm-6">
													<select class="select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($phones->extension);$i++){
															echo "<option value=".$phones->extension[$i].">".$phones->extension[$i]." - ".$phones->server_ip[$i]." - ".$phones->dialplan_number[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- VOICEMAIL -->
											<div class="route_voicemail_9" style="display:none;">
												<label class="col-sm-3 control-label">Voicemail Box: </label>
												<div class="col-sm-6">
													<select class="col-sm-6 select2-1 form-control" name="option_route_value[]" style="width:100%;">
														<option value="" selected> - - - NONE - - - </option>
													<?php
														for($i=0;$i < count($voicemails->voicemail_id);$i++){
															echo "<option value=".$voicemails->voicemail_id[$i].">".$voicemails->voicemail_id[$i]." - ".$voicemails->full_name[$i]."</option>";
														}
													?>
													</select>
												</div>
											</div>
										<!-- AGI -->
											<div class="route_agi_9" style="display:none;">
												<label class="col-sm-3 control-label">AGI: </label>
												<div class="col-sm-6">
													<input type="text" class="form-control" name="option_route_value[]" value="" maxlength="255" size="50">
												</div>
											</div>
									</div>
								</div>
							</div>
						</div><!--static div -->
					</fieldset>
					</div><!-- End of Step -->
				</form>
				</div> <!-- end of modal body -->
				
			</div>
		</div>
	</div><!-- end of modal -->

	
	<!-- ADD DID MODAL -->
	<div class="modal fade" id="add_phonenumbers" aria-labelledby="did_modal" >
        <div class="modal-dialog" role="document">
            <div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title animated bounceInRight" id="did_modal">
						<i class="fa fa-info-circle" title="A step by step wizard that allows you to create DID/TFN."></i> 
						<b>DID Wizard » Create new DID</b>
						<button type="button" class="close" data-dismiss="modal" aria-label="close_did"><span aria-hidden="true">&times;</span></button>
					</h4>
				</div>
				<div class="modal-body wizard-content">
				
				<form action="AddTelephonyPhonenumber.php" method="POST" id="create_phonenumber" role="form">
					<div class="row">
						<!-- STEP 1 -->
						<h4>DID Details
                           <br>
                           <small>Enter the basic details of your DID then assign it to a user group</small>
                        </h4>
                        <fieldset>
							<div class="form-group mt">
								<label class="col-sm-4 control-label" for="did_exten">DID Extention:</label>
								<div class="col-sm-8 mb">
									<input type="text" name="did_exten" id="did_exten" class="form-control" placeholder="DID Extention (Mandatory)" maxlength="20" minlength="2" required title="Must be 2-20 characters in length." />
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" for="desc">DID Description</label>
								<div class="col-sm-8 mb">
									<input type="text" name="desc" id="desc" class="form-control" placeholder="DID Description (Mandatory)" maxlength="20" minlength="2" title="Must be  2-20 characters in length"  required />
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" for="active">Active</label>
								<div class="col-sm-8 mb">
									<select name="active" id="active" class="form-control">
										<option value="Y" selected>Yes</option>
										<option value="N">No</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" for="route" >DID Route</label>
								<div class="col-sm-8 mb">
									<select class="form-control" id="route" name="route">
										<option value="AGENT"> Agent </option>
										<option value="IN_GROUP"> In-group </option>
										<option value="PHONE"> Phone </option>
										<option value="CALLMENU"> Call Menu / IVR </option>
										<option value="VOICEMAIL"> Voicemail </option>
										<option value="EXTENSION"> Custom Extension </option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label" for="user_groups">User Groups</label>
								<div class="col-sm-8 mb">
									<select name="user_groups" id="user_groups" class="form-control select2-1" style="width:100%;">
										<?php
											for($i=0;$i<count($user_groups->user_group);$i++){
										?>
											<option value="<?php echo $user_groups->user_group[$i];?>">  <?php echo $user_groups->user_group[$i]." - ".$user_groups->group_name[$i];?>  </option>
										<?php
											}
										?>
									</select>
								</div>
							</div>
						</fieldset>
						<h4>Route Settings
                           <br>
                           <small>Fill up details needed for the chosen route.</small>
                        </h4>
                        <fieldset>
						<!-- IF DID ROUTE = AGENT-->

							<div id="form_route_agent">
								<div class="form-group">
									<label class="col-sm-4 control-label" for="route_agentid">Agent ID</label>
									<div class="col-sm-8 mb">
										<select name="route_agentid" id="route_agentid" class="form-control select2-1" style="width:100%;">
											<option value="" > -- NONE -- </option>
											<?php
												for($i=0;$i<count($users->user);$i++){
											?>
												<option value="<?php echo $users->user[$i];?>">
													<?php echo $users->user[$i].' - '.$users->full_name[$i];?>
												</option>									
											<?php
												}
											?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label" for="route_unavail">Agent Unavailable Action</label>
									<div class="col-sm-8 mb">
										<select name="route_unavail" id="route_unavail" class="form-control">
											<option value="VOICEMAIL" > Voicemail </option>
											<option value="PHONE" > Phone </option>
											<option value="IN_GROUP" > In-group </option>
											<option value="EXTENSION" > Custom Extension </option>
										</select>
									</div>
								</div>
							</div><!-- end of div agent-->
							
						<!-- IF DID ROUTE = IN-GROUP-->
						
							<div id="form_route_ingroup" style="display: none;">
								<label class="col-sm-4 control-label" for="route_ingroupid">In-Group ID</label>
								<div class="col-sm-8 mb">
									<select name="route_ingroupid" id="route_ingroupid" class="form-control select2-1" style="width:100%;">
										<?php
											for($i=0;$i<count($ingroups->group_id);$i++){
										?>
											<option value="<?php echo $ingroups->group_id[$i];?>">
												<?php echo $ingroups->group_id[$i].' - '.$ingroups->group_name[$i];?>
											</option>									
										<?php
											}
										?>
									</select>
								</div>
							</div><!-- end of ingroup div -->
							
						<!-- IF DID ROUTE = PHONE -->

							<div id="form_route_phone" style="display: none;">
								<div class="form-group">
									<label class="col-sm-4 control-label" for="route_phone_exten">Phone Extension</label>
									<div class="col-sm-8 mb">
										<select name="route_phone_exten" id="route_phone_exten" class="form-control select2-1" style="width:100%;">
											<?php
												for($i=0;$i<count($phones->extension);$i++){
											?>
												<option value="<?php echo $phones->extension[$i];?>">
													<?php echo $phones->extension[$i].' - '.$phones->server_ip[$i].' - '.$phones->dialplan_number[$i];?>
												</option>									
											<?php
												}
											?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label" for="route_phone_server">Server IP</label>
									<div class="col-sm-8 mb">
										<select name="route_phone_server" id="route_phone_server" class="form-control select2-1" style="width:100%;">
											<option value="" > -- NONE -- </option>
											<?php
												for($i=0;$i < 1;$i++){
											?>
												<option value="<?php echo $phones->server_ip[$i];?>">
													<?php echo 'GOautodial - '.$phones->server_ip[$i];?>
												</option>									
											<?php
												}
											?>
										</select>
									</div>
								</div>
							</div><!-- end of phone div -->
							
						<!-- IF DID ROUTE = IVR -->

							<div id="form_route_callmenu" style="display: none;">
								<div class="form-group">
									<label class="col-sm-4 control-label" for="route_ivr">Call Menu</label>
									<div class="col-sm-8 mb">
										<select name="route_ivr" id="route_ivr" class="form-control select2-1" style="width:100%;">
											<?php
											if(count($ivr->menu_id) > 0){
												for($i=0;$i<count($ivr->menu_id);$i++){
											?>
												<option value="<?php echo $ivr->menu_id[$i];?>">
													<?php echo $ivr->menu_id[$i].' - '.$ivr->menu_name[$i];?>
												</option>									
											<?php
												}
											}else{
											?>
												<option value="">- - - No Available Call Menu - - - </option>
											<?php
											}
											?>
										</select>
									</div>
								</div>
							</div><!-- end of ivr div -->
							
						<!-- IF DID ROUTE = VoiceMail -->

							<div id="form_route_voicemail" style="display: none;">
								<div class="form-group">
									<label class="col-sm-4 control-label" for="route_voicemail">Voicemail Box</label>
									<div class="col-sm-8 mb">
										<select name="route_voicemail" id="route_voicemail" class="form-control select2-1" style="width:100%;">
											
											<?php
												for($i=0;$i<count($voicemails->voicemail_id);$i++){
											?>
												<option value="<?php echo $voicemails->voicemail_id[$i];?>">
													<?php echo $voicemails->voicemail_id[$i].' - '.$voicemails->fullname[$i];?>
												</option>									
											<?php
												}
											?>
											
										</select>
									</div>
								</div>
							</div><!-- end of voicemail div -->
							
							<!-- IF DID ROUTE = Custom Extension -->

							<div id="form_route_exten" style="display: none;">
								<div class="form-group">
									<label class="col-sm-4 control-label" for="route_exten">Extension</label>
									<div class="col-sm-8 mb">
										<input type="text" name="route_exten" id="route_exten" placeholder="Extension" class="form-control" required>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label" for="route_exten_context">Extension Context</label>
									<div class="col-sm-8 mb">
										<input type="text" name="route_exten_context" id="route_exten_context" placeholder="Extension Context" class="form-control" required>
									</div>
								</div>
							</div><!-- end of custom extension div -->
						</fieldset>
					</div><!-- End of Step -->
				
				

				</div> <!-- end of modal body -->
				</form>
			</div>
		</div>
	</div><!-- end of modal -->

<!-- END OF TELEPHONY INBOUND MODALS -->

		<?php print $ui->standardizedThemeJS(); ?>
        <!-- JQUERY STEPS-->
  		<script src="theme_dashboard/js/jquery.steps/build/jquery.steps.js"></script>
	    

 <script type="text/javascript">
	$(document).ready(function() {

		/*******************
		** INITIALIZATIONS
		*******************/
			// loads the fixed action button
				$(".bottom-menu").on('mouseenter mouseleave', function () {
				  $(this).find(".fab-div-area").stop().slideToggle({ height: 'toggle', opacity: 'toggle' }, 'slow');
				});

			//loads datatable functions
				$('#table_ingroup').dataTable({
					"aaSorting": [[ 1, "asc" ]],
					"aoColumnDefs": [{
						"bSearchable": false,
						"aTargets": [ 0, 6 ]
					},{
						"bSortable": false,
						"aTargets": [ 0, 6 ]
					}]
				});
				$('#table_ivr').dataTable({
					"aaSorting": [[ 1, "asc" ]],
					"aoColumnDefs": [{
						"bSearchable": false,
						"aTargets": [ 0, 5 ]
					},{
						"bSortable": false,
						"aTargets": [ 0, 5 ]
					}]
				});
				$('#table_did').dataTable({
					"aaSorting": [[ 1, "asc" ]],
					"aoColumnDefs": [{
						"bSearchable": false,
						"aTargets": [ 0, 5 ]
					},{
						"bSortable": false,
						"aTargets": [ 0, 5 ]
					}]
				});

			//reloads page when modal closes
			/*
				$('#add_ingroups').on('hidden.bs.modal', function () {
					window.location = window.location.href;
				});

				$('#add_ivr').on('hidden.bs.modal', function () {
					window.location = window.location.href;
				});

				$('#add_phonenumbers').on('hidden.bs.modal', function () {
					window.location = window.location.href;
				});
			*/
			//-----------
		
		/*******************
		** INBOUND EVENTS
		*******************/

			/*********
			** INIT WIZARD
			*********/
				var ingroup_form = $("#create_ingroup"); // init form wizard 

			    ingroup_form.validate({
			        errorPlacement: function errorPlacement(error, element) { element.after(error); }
			    });
			    ingroup_form.children("div").steps({
			        headerTag: "h4",
			        bodyTag: "fieldset",
			        transitionEffect: "slideLeft",
			        onStepChanging: function (event, currentIndex, newIndex)
			        {
			        	// Allways allow step back to the previous step even if the current step is not valid!
				        if (currentIndex > newIndex) {
				            return true;
				        }

						// Clean up if user went backward before
					    if (currentIndex < newIndex)
					    {
					        // To remove error styles
					        $(".body:eq(" + newIndex + ") label.error", ingroup_form).remove();
					        $(".body:eq(" + newIndex + ") .error", ingroup_form).removeClass("error");
					    }

			            ingroup_form.validate().settings.ignore = ":disabled,:hidden";
			            return ingroup_form.valid();
			        },
			        onFinishing: function (event, currentIndex)
			        {
			            ingroup_form.validate().settings.ignore = ":disabled,:hidden";
			            return ingroup_form.valid();
			        },
			        onFinished: function (event, currentIndex)
			        {

			        	$('#finish').text("Loading...");
			        	$('#finish').attr("disabled", true);

			        	/*********
						** ADD EVENT 
						*********/
				            // Submit form via ajax
					            $.ajax({
									url: "./php/AddTelephonyIngroup.php",
									type: 'POST',
									data: $("#create_ingroup").serialize(),
									success: function(data) {
									  // console.log(data);
										  if(data == "success"){
												swal("Success!", "Ingroup Successfully Created!", "success");
										  		window.setTimeout(function(){location.reload()},1000);

										  		$('#finish').text("Submit");
												$('#finish').attr("disabled", false);
										  }
										  else{
											  sweetAlert("Oops...", "Something went wrong! "+data, "error");

											  $('#finish').text("Submit");
											  $('#finish').attr("disabled", false);
										  }
									}
								});
			        }
			    }); // end of wizard
			
			/*********
			** EDIT INGROUP
			*********/
				$(document).on("click",".edit-ingroup",function(e) {
					e.preventDefault();
					var url = './edittelephonyinbound.php';
					var form = $('<form action="' + url + '" method="post"><input type="hidden" name="groupid" value="' + $(this).attr('data-id') + '" /></form>');
					//$('body').append(form);  // This line is not necessary
					$(form).submit();
				});

			/*********
			** DELETE INGROUP
			*********/
				$(document).on('click','.delete-ingroup',function() {
				 	var id = $(this).attr('data-id');
	                swal({   
	                	title: "Are you sure?",   
	                	text: "This action cannot be undone.",   
	                	type: "warning",   
	                	showCancelButton: true,   
	                	confirmButtonColor: "#DD6B55",   
	                	confirmButtonText: "Yes, delete this inbound!",   
	                	cancelButtonText: "No, cancel please!",   
	                	closeOnConfirm: false,   
	                	closeOnCancel: false 
	                	}, 
	                	function(isConfirm){   
	                		if (isConfirm) { 
	                			$.ajax({
									url: "./php/DeleteTelephonyInbound.php",
									type: 'POST',
									data: { 
										groupid:id,
									},
									success: function(data) {
									console.log(data);
								  		if(data == 1){
											swal({title: "Success!",text: "Inbound Successfully Deleted!",type: "success"},function(){window.location.href = 'telephonyinbound.php';});
										}else{
											sweetAlert("Oops...", "Something went wrong! "+data, "error");
										}
									}
								});
							} else {     
		                			swal("Cancelled", "No action has been done :)", "error");   
		                	} 
	                	}
	                );
				});
		
		//-------------------- end of main ingroup events

		/*******************
		** IVR EVENTS
		*******************/

			/*********
			** INIT WIZARD
			*********/
				var ivr_form = $("#create_ivr"); // init form wizard 
				
			    ivr_form.validate({
			        errorPlacement: function errorPlacement(error, element) { element.after(error); }
			    });
			    ivr_form.children("div").steps({
			        headerTag: "h4",
			        bodyTag: "fieldset",
			        transitionEffect: "slideLeft",
			        onStepChanging: function (event, currentIndex, newIndex)
			        {
			        	// Allways allow step back to the previous step even if the current step is not valid!
				        if (currentIndex > newIndex) {
				            return true;
				        }

						// Clean up if user went backward before
					    if (currentIndex < newIndex)
					    {
					        // To remove error styles
					        $(".body:eq(" + newIndex + ") label.error", ivr_form).remove();
					        $(".body:eq(" + newIndex + ") .error", ivr_form).removeClass("error");
					    }
						
						$("#create_ivr").find( ".content.clearfix" ).css( "height", "75%" );
						
			            ivr_form.validate().settings.ignore = ":disabled,:hidden";
			            return ivr_form.valid();
			        },
			        onFinishing: function (event, currentIndex)
			        {
			            ivr_form.validate().settings.ignore = ":hidden";
			            return ivr_form.valid();
			        },
			        onFinished: function (event, currentIndex)
			        {
					$('select option').prop('disabled', false);
					
			        	$('#finish').text("Loading...");
			        	$('#finish').attr("disabled", true);

			        	/*********
						** ADD EVENT 
						*********/
				            // Submit form via ajax
					            $.ajax({
									url: "./php/AddIVR.php",
									type: 'POST',
									data: $("#create_ivr").serialize(),
									success: function(data) {
									  // console.log(data);
								  		$('#finish').text("Submit");
										$('#finish').attr("disabled", false);
										if(data == "success"){
												swal({title: "Success!",text: "IVR Successfully Created!",type: "success"},function(){window.location.href = 'telephonyinbound.php';});
										}
										else{
											  sweetAlert("Oops...", "Something went wrong! "+data, "error");
										}
									}
								});
							
			        }
			    }); // end of wizard
			
			//$(document).on("click","#next",function(e) {
			//	$("#create_ivr").find( ".content.clearfix" ).css( "height", "75%" );
			//});
			/*********
			** EDIT IVR
			*********/
				$(document).on("click",".edit-ivr",function(e) {
					e.preventDefault();
					var url = './edittelephonyinbound.php';
					var form = $('<form action="' + url + '" method="post"><input type="hidden" name="ivr" value="' + $(this).attr('data-id') + '" /></form>');
					//$('body').append(form);  // This line is not necessary
					$(form).submit();
				});

			/*********
			** DELETE IVR
			*********/

				$(document).on('click','.delete-ivr',function() {
				 	var id = $(this).attr('data-id');
	                swal({   
	                	title: "Are you sure?",   
	                	text: "This action cannot be undone.",   
	                	type: "warning",   
	                	showCancelButton: true,   
	                	confirmButtonColor: "#DD6B55",   
	                	confirmButtonText: "Yes, delete this IVR!",   
	                	cancelButtonText: "No, cancel please!",   
	                	closeOnConfirm: false,   
	                	closeOnCancel: false 
	                	}, 
	                	function(isConfirm){   
	                		if (isConfirm) { 
	                			$.ajax({
									url: "./php/DeleteTelephonyInbound.php",
									type: 'POST',
									data: { 
										ivr:id,
									},
									success: function(data) {
									console.log(data);
								  		if(data == 1){
											swal({title: "Success!",text: "IVR Successfully Deleted!",type: "success"},function(){window.location.href = 'telephonyinbound.php';});
										}else{
											sweetAlert("Oops...", "Something went wrong! "+data, "error");
										}
									}
								});
							} else {     
		                			swal("Cancelled", "No action has been done :)", "error");   
		                	} 
	                	}
	                );
				});
			
		//-------------------- end of main ivr events

		/*******************
		** DID EVENTS
		*******************/

			/*********
			** DID WIZARD
			*********/
				var did_form = $("#create_phonenumber"); // init form wizard 

			    did_form.validate({
			        errorPlacement: function errorPlacement(error, element) { element.after(error); }
			    });
			    did_form.children("div").steps({
			        headerTag: "h4",
			        bodyTag: "fieldset",
			        transitionEffect: "slideLeft",
			        onStepChanging: function (event, currentIndex, newIndex)
			        {
			        	// Allways allow step back to the previous step even if the current step is not valid!
				        if (currentIndex > newIndex) {
				            return true;
				        }

						// Clean up if user went backward before
					    if (currentIndex < newIndex)
					    {
					        // To remove error styles
					        $(".body:eq(" + newIndex + ") label.error", did_form).remove();
					        $(".body:eq(" + newIndex + ") .error", did_form).removeClass("error");
					    }

			            did_form.validate().settings.ignore = ":disabled,:hidden";
			            return did_form.valid();
			        },
			        onFinishing: function (event, currentIndex)
			        {
			            did_form.validate().settings.ignore = ":disabled,:hidden";
			            return did_form.valid();
			        },
			        onFinished: function (event, currentIndex)
			        {

			        	$('#finish').text("Loading...");
			        	$('#finish').attr("disabled", true);

			        	/*********
						** ADD EVENT 
						*********/
				            $.ajax({
								url: "./php/AddTelephonyPhonenumber.php",
								type: 'POST',
								data: $("#create_phonenumber").serialize(),
								success: function(data) {
								   console.log(data);
								   $('#submit_did').val("Submit");
											$('#submit_did').attr("disabled", false);
									  if(data == 1){
									  		swal({title: "Success!",text: "Phone Number Successfully Created!",type: "success"},function(){window.location.href = 'telephonyinbound.php';});
									  }else{
											sweetAlert("Oops...", "Something went wrong! "+data, "error");
									  }
								}
							});
							
			        }
			    }); // end of wizard
			
			//------------------------

			/*********
			** EDIT DID
			*********/
	
				$(document).on('click','.edit-phonenumber',function() {
					var url = './edittelephonyinbound.php';
					var form = $('<form action="' + url + '" method="post"><input type="hidden" name="did" value="' + $(this).attr('data-id') + '" /></form>');
					//$('body').append(form);  // This line is not necessary
					$(form).submit();
				});

			/*********
			** DELETE DID
			*********/

				$(document).on('click','.delete-phonenumber',function() {
				 	var id = $(this).attr('data-id');
	                swal({
	                	title: "Are you sure?",   
	                	text: "This action cannot be undone.",   
	                	type: "warning",   
	                	showCancelButton: true,   
	                	confirmButtonColor: "#DD6B55",   
	                	confirmButtonText: "Yes, delete this phonenumber!",   
	                	cancelButtonText: "No, cancel please!",   
	                	closeOnConfirm: false,   
	                	closeOnCancel: false 
					}, 
	                	function(isConfirm){   
	                		if (isConfirm) { 
	                			$.ajax({
									url: "./php/DeleteTelephonyInbound.php",
									type: 'POST',
									data: { 
										modify_did:id,
									},
									
									success: function(data) {
									//console.log(modify_did);
									console.log(data);
								  		if(data == 1){
											swal({title: "Success!",text: "Phone Number Successfully Deleted!",type: "success"},function(){window.location.href = 'telephonyinbound.php';});
										}else{
											sweetAlert("Oops...", "Something went wrong! "+data, "error");
										}
									}
								});
	                		} else {     
		                			swal("Cancelled", "No action has been done :)", "error");   
		                	} 
	                	}
	                );
				});
		
		//-------------------- end of main did events

		/*******************
		** OTHER TRIGGER EVENTS and FILTERS
		*******************/
			/* loads colorpicker */
    			$(".colorpicker").colorpicker();

    		/* initialize select2 */
				$('.select2-1').select2({
			        theme: 'bootstrap'
			    });
				
			/*** INGROUP ***/
				// disable special characters on Ingroup ID
					$('#groupid').bind('keypress', function (event) {
					    var regex = new RegExp("^[a-zA-Z0-9]+$");
					    var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
					    if (!regex.test(key)) {
					       event.preventDefault();
					       return false;
					    }
					});
				// disable special characters on Ingroup Name
					$('#groupname').bind('keypress', function (event) {
					    var regex = new RegExp("^[a-zA-Z0-9 ]+$");
					    var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
					    if (!regex.test(key)) {
					       event.preventDefault();
					       return false;
					    }
					});

			/*** IVR ***/
				// disable special characters on Ingroup ID
					$('#menu_id').bind('keypress', function (event) {
					    var regex = new RegExp("^[a-zA-Z0-9]+$");
					    var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
					    if (!regex.test(key)) {
					       event.preventDefault();
					       return false;
					    }
					});
					$('#menu_name').bind('keypress', function (event) {
					    var regex = new RegExp("^[a-zA-Z0-9 ]+$");
					    var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
					    if (!regex.test(key)) {
					       event.preventDefault();
					       return false;
					    }
					});
				$(document).on('change', '.route_option',function(){
					//alert(this.value);
					var id = this.value;
					var old = $(this).attr('id');
					var object;
					if(typeof old != 'undefined'){
						$(this).attr('id', "option_"+id).attr('data-old', "option_"+old);
						
						object = "option_"+id;
					}else{
						$(this).attr('id', 'option_'+id);
						old = "option_";
					}
					
					showhide_option(object, id, old);
					
				});
				
				function showhide_option(object, id, old){
					//var getId = object.attr('id');
					var lastChar;
					var old_lastChar;
					
					if (typeof object != 'undefined')
						lastChar = object[object.length -1];
					
					if (typeof old != 'undefined')
						old_lastChar = old[old.length -1];
					
					if(old_lastChar != "_"){
						$(".route_option option[value="+old_lastChar+"]").attr("disabled", false).css({"background-color": "white", "color": "#3a3f51"});
					}else{
						$(".route_option option[value="+id+"]").attr("disabled", true).css({"background-color": "#c1c1c1", "color": "white"});
					}
					
				}
				
				$(document).on('change', '.route_menu_0',function(){
					if(this.value == "CALLMENU") {
						$('.route_callmenu_0').show();
						$(".route_callmenu_0 :input").prop('required',true);
						
						$('.route_ingroup_0').hide();
							$(".route_ingroup_0 :input").prop('required',false);
						$('.route_did_0').hide();
							$(".route_did_0 :input").prop('required',false);
						$('.route_hangup_0').hide();
							$(".route_hangup_0 :input").prop('required',false);
						$('.route_exten_0').hide();
							$(".route_exten_0 :input").prop('required',false);
						$('.route_phone_0').hide();
							$(".route_phone_0 :input").prop('required',false);
						$('.route_voicemail_0').hide();
							$(".route_voicemail_0 :input").prop('required',false);
						$('.route_agi_0').hide();
							$(".route_agi_0 :input").prop('required',false);
					
					}if(this.value == "INGROUP") {
						$('.route_ingroup_0').show();
						$(".route_ingroup_0 :input").prop('required',true);

						$('.route_callmenu_0').hide();
							$(".route_callmenu_0 :input").prop('required',false);
						$('.route_did_0').hide();
							$(".route_did_0 :input").prop('required',false);
						$('.route_hangup_0').hide();
							$(".route_hangup_0 :input").prop('required',false);
						$('.route_exten_0').hide();
							$(".route_exten_0 :input").prop('required',false);
						$('.route_phone_0').hide();
							$(".route_phone_0 :input").prop('required',false);
						$('.route_voicemail_0').hide();
							$(".route_voicemail_0 :input").prop('required',false);
						$('.route_agi_0').hide();
							$(".route_agi_0 :input").prop('required',false);
						
					}if(this.value == "DID") {
						$('.route_did_0').show();
						$(".route_did_0 :input").prop('required',true);

						$('.route_callmenu_0').hide();
							$(".route_callmenu_0 :input").prop('required',false);
						$('.route_ingroup_0').hide();
							$(".route_ingroup_0 :input").prop('required',false);
						$('.route_hangup_0').hide();
							$(".route_hangup_0 :input").prop('required',false);
						$('.route_exten_0').hide();
							$(".route_exten_0 :input").prop('required',false);
						$('.route_phone_0').hide();
							$(".route_phone_0 :input").prop('required',false);
						$('.route_voicemail_0').hide();
							$(".route_voicemail_0 :input").prop('required',false);
						$('.route_agi_0').hide();
							$(".route_agi_0 :input").prop('required',false);
						
					}if(this.value == "HANGUP") {
						$('.route_hangup_0').show();
						$(".route_hangup_0 :input").prop('required',true);
						
						$('.route_callmenu_0').hide();
							$(".route_callmenu_0 :input").prop('required',false);
						$('.route_ingroup_0').hide();
							$(".route_ingroup_0 :input").prop('required',false);
						$('.route_did_0').hide();
							$(".route_did_0 :input").prop('required',false);
						$('.route_exten_0').hide();
							$(".route_exten_0 :input").prop('required',false);
						$('.route_phone_0').hide();
							$(".route_phone_0 :input").prop('required',false);
						$('.route_voicemail_0').hide();
							$(".route_voicemail_0 :input").prop('required',false);
						$('.route_agi_0').hide();
							$(".route_agi_0 :input").prop('required',false);
						
					}if(this.value == "EXTENSION") {
						$('.route_exten_0').show();
						$(".route_exten_0 :input").prop('required',true);
						
						$('.route_callmenu_0').hide();
							$(".route_callmenu_0 :input").prop('required',false);
						$('.route_ingroup_0').hide();
							$(".route_ingroup_0 :input").prop('required',false);
						$('.route_did_0').hide();
							$(".route_did_0 :input").prop('required',false);
						$('.route_hangup_0').hide();
							$(".route_hangup_0 :input").prop('required',false);
						$('.route_phone_0').hide();
							$(".route_phone_0 :input").prop('required',false);
						$('.route_voicemail_0').hide();
							$(".route_voicemail_0 :input").prop('required',false);
						$('.route_agi_0').hide();
							$(".route_agi_0 :input").prop('required',false);
						
					}if(this.value == "PHONE") {
						$('.route_phone_0').show();
						$(".route_phone_0 :input").prop('required',true);
						
						$('.route_callmenu_0').hide();
							$(".route_callmenu_0 :input").prop('required',false);
						$('.route_ingroup_0').hide();
							$(".route_ingroup_0 :input").prop('required',false);
						$('.route_did_0').hide();
							$(".route_did_0 :input").prop('required',false);
						$('.route_hangup_0').hide();
							$(".route_hangup_0 :input").prop('required',false);
						$('.route_exten_0').hide();
							$(".route_exten_0 :input").prop('required',false);
						$('.route_voicemail_0').hide();
							$(".route_voicemail_0 :input").prop('required',false);
						$('.route_agi_0').hide();
							$(".route_agi_0 :input").prop('required',false);
						
					}if(this.value == "VOICEMAIL") {
						$('.route_voicemail_0').show();
						$(".route_voicemail_0 :input").prop('required',true);
						
						$('.route_callmenu_0').hide();
							$(".route_callmenu_0 :input").prop('required',false);
						$('.route_ingroup_0').hide();
							$(".route_ingroup_0 :input").prop('required',false);
						$('.route_did_0').hide();
							$(".route_did_0 :input").prop('required',false);
						$('.route_hangup_0').hide();
							$(".route_hangup_0 :input").prop('required',false);
						$('.route_exten_0').hide();
							$(".route_exten_0 :input").prop('required',false);
						$('.route_phone_0').hide();
							$(".route_phone_0 :input").prop('required',false);
						$('.route_agi_0').hide();
							$(".route_agi_0 :input").prop('required',false);
						
					}if(this.value == "AGI") {
						$('.route_agi_0').show();
						$(".route_agi_0 :input").prop('required',true);
						
						$('.route_callmenu_0').hide();
							$(".route_callmenu_0 :input").prop('required',false);
						$('.route_ingroup_0').hide();
							$(".route_ingroup_0 :input").prop('required',false);
						$('.route_did_0').hide();
							$(".route_did_0 :input").prop('required',false);
						$('.route_hangup_0').hide();
							$(".route_hangup_0 :input").prop('required',false);
						$('.route_exten_0').hide();
							$(".route_exten_0 :input").prop('required',false);
						$('.route_phone_0').hide();
							$(".route_phone_0 :input").prop('required',false);
						$('.route_voicemail_0').hide();
							$(".route_voicemail_0 :input").prop('required',false);
					}
					if(this.value == "") {
						$('.route_callmenu_0').hide();
							$(".route_callmenu_0 :input").prop('required',false);
						$('.route_ingroup_0').hide();
							$(".route_ingroup_0 :input").prop('required',false);
						$('.route_did_0').hide();
							$(".route_did_0 :input").prop('required',false);
						$('.route_hangup_0').hide();
							$(".route_hangup_0 :input").prop('required',false);
						$('.route_exten_0').hide();
							$(".route_exten_0 :input").prop('required',false);
						$('.route_phone_0').hide();
							$(".route_phone_0 :input").prop('required',false);
						$('.route_voicemail_0').hide();
							$(".route_voicemail_0 :input").prop('required',false);
						$('.route_agi_0').hide();
							$(".route_agi_0 :input").prop('required',false);
					}
				});
				$(document).on('change', '.route_menu_1',function(){
					if(this.value == "CALLMENU") {
						$('.route_callmenu_1').show();
						$(".route_callmenu_1 :input").prop('required',true);
						
						$('.route_ingroup_1').hide();
							$(".route_ingroup_1 :input").prop('required',false);
						$('.route_did_1').hide();
							$(".route_did_1 :input").prop('required',false);
						$('.route_hangup_1').hide();
							$(".route_hangup_1 :input").prop('required',false);
						$('.route_exten_1').hide();
							$(".route_exten_1 :input").prop('required',false);
						$('.route_phone_1').hide();
							$(".route_phone_1 :input").prop('required',false);
						$('.route_voicemail_1').hide();
							$(".route_voicemail_1 :input").prop('required',false);
						$('.route_agi_1').hide();
							$(".route_agi_1 :input").prop('required',false);
					
					}if(this.value == "INGROUP") {
						$('.route_ingroup_1').show();
						$(".route_ingroup_1 :input").prop('required',true);

						$('.route_callmenu_1').hide();
							$(".route_callmenu_1 :input").prop('required',false);
						$('.route_did_1').hide();
							$(".route_did_1 :input").prop('required',false);
						$('.route_hangup_1').hide();
							$(".route_hangup_1 :input").prop('required',false);
						$('.route_exten_1').hide();
							$(".route_exten_1 :input").prop('required',false);
						$('.route_phone_1').hide();
							$(".route_phone_1 :input").prop('required',false);
						$('.route_voicemail_1').hide();
							$(".route_voicemail_1 :input").prop('required',false);
						$('.route_agi_1').hide();
							$(".route_agi_1 :input").prop('required',false);
						
					}if(this.value == "DID") {
						$('.route_did_1').show();
						$(".route_did_1 :input").prop('required',true);

						$('.route_callmenu_1').hide();
							$(".route_callmenu_1 :input").prop('required',false);
						$('.route_ingroup_1').hide();
							$(".route_ingroup_1 :input").prop('required',false);
						$('.route_hangup_1').hide();
							$(".route_hangup_1 :input").prop('required',false);
						$('.route_exten_1').hide();
							$(".route_exten_1 :input").prop('required',false);
						$('.route_phone_1').hide();
							$(".route_phone_1 :input").prop('required',false);
						$('.route_voicemail_1').hide();
							$(".route_voicemail_1 :input").prop('required',false);
						$('.route_agi_1').hide();
							$(".route_agi_1 :input").prop('required',false);
						
					}if(this.value == "HANGUP") {
						$('.route_hangup_1').show();
						$(".route_hangup_1 :input").prop('required',true);
						
						$('.route_callmenu_1').hide();
							$(".route_callmenu_1 :input").prop('required',false);
						$('.route_ingroup_1').hide();
							$(".route_ingroup_1 :input").prop('required',false);
						$('.route_did_1').hide();
							$(".route_did_1 :input").prop('required',false);
						$('.route_exten_1').hide();
							$(".route_exten_1 :input").prop('required',false);
						$('.route_phone_1').hide();
							$(".route_phone_1 :input").prop('required',false);
						$('.route_voicemail_1').hide();
							$(".route_voicemail_1 :input").prop('required',false);
						$('.route_agi_1').hide();
							$(".route_agi_1 :input").prop('required',false);
						
					}if(this.value == "EXTENSION") {
						$('.route_exten_1').show();
						$(".route_exten_1 :input").prop('required',true);
						
						$('.route_callmenu_1').hide();
							$(".route_callmenu_1 :input").prop('required',false);
						$('.route_ingroup_1').hide();
							$(".route_ingroup_1 :input").prop('required',false);
						$('.route_did_1').hide();
							$(".route_did_1 :input").prop('required',false);
						$('.route_hangup_1').hide();
							$(".route_hangup_1 :input").prop('required',false);
						$('.route_phone_1').hide();
							$(".route_phone_1 :input").prop('required',false);
						$('.route_voicemail_1').hide();
							$(".route_voicemail_1 :input").prop('required',false);
						$('.route_agi_1').hide();
							$(".route_agi_1 :input").prop('required',false);
						
					}if(this.value == "PHONE") {
						$('.route_phone_1').show();
						$(".route_phone_1 :input").prop('required',true);
						
						$('.route_callmenu_1').hide();
							$(".route_callmenu_1 :input").prop('required',false);
						$('.route_ingroup_1').hide();
							$(".route_ingroup_1 :input").prop('required',false);
						$('.route_did_1').hide();
							$(".route_did_1 :input").prop('required',false);
						$('.route_hangup_1').hide();
							$(".route_hangup_1 :input").prop('required',false);
						$('.route_exten_1').hide();
							$(".route_exten_1 :input").prop('required',false);
						$('.route_voicemail_1').hide();
							$(".route_voicemail_1 :input").prop('required',false);
						$('.route_agi_1').hide();
							$(".route_agi_1 :input").prop('required',false);
						
					}if(this.value == "VOICEMAIL") {
						$('.route_voicemail_1').show();
						$(".route_voicemail_1 :input").prop('required',true);
						
						$('.route_callmenu_1').hide();
							$(".route_callmenu_1 :input").prop('required',false);
						$('.route_ingroup_1').hide();
							$(".route_ingroup_1 :input").prop('required',false);
						$('.route_did_1').hide();
							$(".route_did_1 :input").prop('required',false);
						$('.route_hangup_1').hide();
							$(".route_hangup_1 :input").prop('required',false);
						$('.route_exten_1').hide();
							$(".route_exten_1 :input").prop('required',false);
						$('.route_phone_1').hide();
							$(".route_phone_1 :input").prop('required',false);
						$('.route_agi_1').hide();
							$(".route_agi_1 :input").prop('required',false);
						
					}if(this.value == "AGI") {
						$('.route_agi_1').show();
						$(".route_agi_1 :input").prop('required',true);
						
						$('.route_callmenu_1').hide();
							$(".route_callmenu_1 :input").prop('required',false);
						$('.route_ingroup_1').hide();
							$(".route_ingroup_1 :input").prop('required',false);
						$('.route_did_1').hide();
							$(".route_did_1 :input").prop('required',false);
						$('.route_hangup_1').hide();
							$(".route_hangup_1 :input").prop('required',false);
						$('.route_exten_1').hide();
							$(".route_exten_1 :input").prop('required',false);
						$('.route_phone_1').hide();
							$(".route_phone_1 :input").prop('required',false);
						$('.route_voicemail_1').hide();
							$(".route_voicemail_1 :input").prop('required',false);
					}
					if(this.value == "") {
						$('.route_callmenu_1').hide();
							$(".route_callmenu_1 :input").prop('required',false);
						$('.route_ingroup_1').hide();
							$(".route_ingroup_1 :input").prop('required',false);
						$('.route_did_1').hide();
							$(".route_did_1 :input").prop('required',false);
						$('.route_hangup_1').hide();
							$(".route_hangup_1 :input").prop('required',false);
						$('.route_exten_1').hide();
							$(".route_exten_1 :input").prop('required',false);
						$('.route_phone_1').hide();
							$(".route_phone_1 :input").prop('required',false);
						$('.route_voicemail_1').hide();
							$(".route_voicemail_1 :input").prop('required',false);
						$('.route_agi_1').hide();
							$(".route_agi_1 :input").prop('required',false);
					}
				});
				$(document).on('change', '.route_menu_2',function(){
					if(this.value == "CALLMENU") {
						$('.route_callmenu_2').show();
						$(".route_callmenu_2 :input").prop('required',true);
						
						$('.route_ingroup_2').hide();
							$(".route_ingroup_2 :input").prop('required',false);
						$('.route_did_2').hide();
							$(".route_did_2 :input").prop('required',false);
						$('.route_hangup_2').hide();
							$(".route_hangup_2 :input").prop('required',false);
						$('.route_exten_2').hide();
							$(".route_exten_2 :input").prop('required',false);
						$('.route_phone_2').hide();
							$(".route_phone_2 :input").prop('required',false);
						$('.route_voicemail_2').hide();
							$(".route_voicemail_2 :input").prop('required',false);
						$('.route_agi_2').hide();
							$(".route_agi_2 :input").prop('required',false);
					
					}if(this.value == "INGROUP") {
						$('.route_ingroup_2').show();
						$(".route_ingroup_2 :input").prop('required',true);

						$('.route_callmenu_2').hide();
							$(".route_callmenu_2 :input").prop('required',false);
						$('.route_did_2').hide();
							$(".route_did_2 :input").prop('required',false);
						$('.route_hangup_2').hide();
							$(".route_hangup_2 :input").prop('required',false);
						$('.route_exten_2').hide();
							$(".route_exten_2 :input").prop('required',false);
						$('.route_phone_2').hide();
							$(".route_phone_2 :input").prop('required',false);
						$('.route_voicemail_2').hide();
							$(".route_voicemail_2 :input").prop('required',false);
						$('.route_agi_2').hide();
							$(".route_agi_2 :input").prop('required',false);
						
					}if(this.value == "DID") {
						$('.route_did_2').show();
						$(".route_did_2 :input").prop('required',true);

						$('.route_callmenu_2').hide();
							$(".route_callmenu_2 :input").prop('required',false);
						$('.route_ingroup_2').hide();
							$(".route_ingroup_2 :input").prop('required',false);
						$('.route_hangup_2').hide();
							$(".route_hangup_2 :input").prop('required',false);
						$('.route_exten_2').hide();
							$(".route_exten_2 :input").prop('required',false);
						$('.route_phone_2').hide();
							$(".route_phone_2 :input").prop('required',false);
						$('.route_voicemail_2').hide();
							$(".route_voicemail_2 :input").prop('required',false);
						$('.route_agi_2').hide();
							$(".route_agi_2 :input").prop('required',false);
						
					}if(this.value == "HANGUP") {
						$('.route_hangup_2').show();
						$(".route_hangup_2 :input").prop('required',true);
						
						$('.route_callmenu_2').hide();
							$(".route_callmenu_2 :input").prop('required',false);
						$('.route_ingroup_2').hide();
							$(".route_ingroup_2 :input").prop('required',false);
						$('.route_did_2').hide();
							$(".route_did_2 :input").prop('required',false);
						$('.route_exten_2').hide();
							$(".route_exten_2 :input").prop('required',false);
						$('.route_phone_2').hide();
							$(".route_phone_2 :input").prop('required',false);
						$('.route_voicemail_2').hide();
							$(".route_voicemail_2 :input").prop('required',false);
						$('.route_agi_2').hide();
							$(".route_agi_2 :input").prop('required',false);
						
					}if(this.value == "EXTENSION") {
						$('.route_exten_2').show();
						$(".route_exten_2 :input").prop('required',true);
						
						$('.route_callmenu_2').hide();
							$(".route_callmenu_2 :input").prop('required',false);
						$('.route_ingroup_2').hide();
							$(".route_ingroup_2 :input").prop('required',false);
						$('.route_did_2').hide();
							$(".route_did_2 :input").prop('required',false);
						$('.route_hangup_2').hide();
							$(".route_hangup_2 :input").prop('required',false);
						$('.route_phone_2').hide();
							$(".route_phone_2 :input").prop('required',false);
						$('.route_voicemail_2').hide();
							$(".route_voicemail_2 :input").prop('required',false);
						$('.route_agi_2').hide();
							$(".route_agi_2 :input").prop('required',false);
						
					}if(this.value == "PHONE") {
						$('.route_phone_2').show();
						$(".route_phone_2 :input").prop('required',true);
						
						$('.route_callmenu_2').hide();
							$(".route_callmenu_2 :input").prop('required',false);
						$('.route_ingroup_2').hide();
							$(".route_ingroup_2 :input").prop('required',false);
						$('.route_did_2').hide();
							$(".route_did_2 :input").prop('required',false);
						$('.route_hangup_2').hide();
							$(".route_hangup_2 :input").prop('required',false);
						$('.route_exten_2').hide();
							$(".route_exten_2 :input").prop('required',false);
						$('.route_voicemail_2').hide();
							$(".route_voicemail_2 :input").prop('required',false);
						$('.route_agi_2').hide();
							$(".route_agi_2 :input").prop('required',false);
						
					}if(this.value == "VOICEMAIL") {
						$('.route_voicemail_2').show();
						$(".route_voicemail_2 :input").prop('required',true);
						
						$('.route_callmenu_2').hide();
							$(".route_callmenu_2 :input").prop('required',false);
						$('.route_ingroup_2').hide();
							$(".route_ingroup_2 :input").prop('required',false);
						$('.route_did_2').hide();
							$(".route_did_2 :input").prop('required',false);
						$('.route_hangup_2').hide();
							$(".route_hangup_2 :input").prop('required',false);
						$('.route_exten_2').hide();
							$(".route_exten_2 :input").prop('required',false);
						$('.route_phone_2').hide();
							$(".route_phone_2 :input").prop('required',false);
						$('.route_agi_2').hide();
							$(".route_agi_2 :input").prop('required',false);
						
					}if(this.value == "AGI") {
						$('.route_agi_2').show();
						$(".route_agi_2 :input").prop('required',true);
						
						$('.route_callmenu_2').hide();
							$(".route_callmenu_2 :input").prop('required',false);
						$('.route_ingroup_2').hide();
							$(".route_ingroup_2 :input").prop('required',false);
						$('.route_did_2').hide();
							$(".route_did_2 :input").prop('required',false);
						$('.route_hangup_2').hide();
							$(".route_hangup_2 :input").prop('required',false);
						$('.route_exten_2').hide();
							$(".route_exten_2 :input").prop('required',false);
						$('.route_phone_2').hide();
							$(".route_phone_2 :input").prop('required',false);
						$('.route_voicemail_2').hide();
							$(".route_voicemail_2 :input").prop('required',false);
					}
					if(this.value == "") {
						$('.route_callmenu_2').hide();
							$(".route_callmenu_2 :input").prop('required',false);
						$('.route_ingroup_2').hide();
							$(".route_ingroup_2 :input").prop('required',false);
						$('.route_did_2').hide();
							$(".route_did_2 :input").prop('required',false);
						$('.route_hangup_2').hide();
							$(".route_hangup_2 :input").prop('required',false);
						$('.route_exten_2').hide();
							$(".route_exten_2 :input").prop('required',false);
						$('.route_phone_2').hide();
							$(".route_phone_2 :input").prop('required',false);
						$('.route_voicemail_2').hide();
							$(".route_voicemail_2 :input").prop('required',false);
						$('.route_agi_2').hide();
							$(".route_agi_2 :input").prop('required',false);
					}
				});
				$(document).on('change', '.route_menu_3',function(){
					if(this.value == "CALLMENU") {
						$('.route_callmenu_3').show();
						$(".route_callmenu_3 :input").prop('required',true);
						
						$('.route_ingroup_3').hide();
							$(".route_ingroup_3 :input").prop('required',false);
						$('.route_did_3').hide();
							$(".route_did_3 :input").prop('required',false);
						$('.route_hangup_3').hide();
							$(".route_hangup_3 :input").prop('required',false);
						$('.route_exten_3').hide();
							$(".route_exten_3 :input").prop('required',false);
						$('.route_phone_3').hide();
							$(".route_phone_3 :input").prop('required',false);
						$('.route_voicemail_3').hide();
							$(".route_voicemail_3 :input").prop('required',false);
						$('.route_agi_3').hide();
							$(".route_agi_3 :input").prop('required',false);
					
					}if(this.value == "INGROUP") {
						$('.route_ingroup_3').show();
						$(".route_ingroup_3 :input").prop('required',true);

						$('.route_callmenu_3').hide();
							$(".route_callmenu_3 :input").prop('required',false);
						$('.route_did_3').hide();
							$(".route_did_3 :input").prop('required',false);
						$('.route_hangup_3').hide();
							$(".route_hangup_3 :input").prop('required',false);
						$('.route_exten_3').hide();
							$(".route_exten_3 :input").prop('required',false);
						$('.route_phone_3').hide();
							$(".route_phone_3 :input").prop('required',false);
						$('.route_voicemail_3').hide();
							$(".route_voicemail_3 :input").prop('required',false);
						$('.route_agi_3').hide();
							$(".route_agi_3 :input").prop('required',false);
						
					}if(this.value == "DID") {
						$('.route_did_3').show();
						$(".route_did_3 :input").prop('required',true);

						$('.route_callmenu_3').hide();
							$(".route_callmenu_3 :input").prop('required',false);
						$('.route_ingroup_3').hide();
							$(".route_ingroup_3 :input").prop('required',false);
						$('.route_hangup_3').hide();
							$(".route_hangup_3 :input").prop('required',false);
						$('.route_exten_3').hide();
							$(".route_exten_3 :input").prop('required',false);
						$('.route_phone_3').hide();
							$(".route_phone_3 :input").prop('required',false);
						$('.route_voicemail_3').hide();
							$(".route_voicemail_3 :input").prop('required',false);
						$('.route_agi_3').hide();
							$(".route_agi_3 :input").prop('required',false);
						
					}if(this.value == "HANGUP") {
						$('.route_hangup_3').show();
						$(".route_hangup_3 :input").prop('required',true);
						
						$('.route_callmenu_3').hide();
							$(".route_callmenu_3 :input").prop('required',false);
						$('.route_ingroup_3').hide();
							$(".route_ingroup_3 :input").prop('required',false);
						$('.route_did_3').hide();
							$(".route_did_3 :input").prop('required',false);
						$('.route_exten_3').hide();
							$(".route_exten_3 :input").prop('required',false);
						$('.route_phone_3').hide();
							$(".route_phone_3 :input").prop('required',false);
						$('.route_voicemail_3').hide();
							$(".route_voicemail_3 :input").prop('required',false);
						$('.route_agi_3').hide();
							$(".route_agi_3 :input").prop('required',false);
						
					}if(this.value == "EXTENSION") {
						$('.route_exten_3').show();
						$(".route_exten_3 :input").prop('required',true);
						
						$('.route_callmenu_3').hide();
							$(".route_callmenu_3 :input").prop('required',false);
						$('.route_ingroup_3').hide();
							$(".route_ingroup_3 :input").prop('required',false);
						$('.route_did_3').hide();
							$(".route_did_3 :input").prop('required',false);
						$('.route_hangup_3').hide();
							$(".route_hangup_3 :input").prop('required',false);
						$('.route_phone_3').hide();
							$(".route_phone_3 :input").prop('required',false);
						$('.route_voicemail_3').hide();
							$(".route_voicemail_3 :input").prop('required',false);
						$('.route_agi_3').hide();
							$(".route_agi_3 :input").prop('required',false);
						
					}if(this.value == "PHONE") {
						$('.route_phone_3').show();
						$(".route_phone_3 :input").prop('required',true);
						
						$('.route_callmenu_3').hide();
							$(".route_callmenu_3 :input").prop('required',false);
						$('.route_ingroup_3').hide();
							$(".route_ingroup_3 :input").prop('required',false);
						$('.route_did_3').hide();
							$(".route_did_3 :input").prop('required',false);
						$('.route_hangup_3').hide();
							$(".route_hangup_3 :input").prop('required',false);
						$('.route_exten_3').hide();
							$(".route_exten_3 :input").prop('required',false);
						$('.route_voicemail_3').hide();
							$(".route_voicemail_3 :input").prop('required',false);
						$('.route_agi_3').hide();
							$(".route_agi_3 :input").prop('required',false);
						
					}if(this.value == "VOICEMAIL") {
						$('.route_voicemail_3').show();
						$(".route_voicemail_3 :input").prop('required',true);
						
						$('.route_callmenu_3').hide();
							$(".route_callmenu_3 :input").prop('required',false);
						$('.route_ingroup_3').hide();
							$(".route_ingroup_3 :input").prop('required',false);
						$('.route_did_3').hide();
							$(".route_did_3 :input").prop('required',false);
						$('.route_hangup_3').hide();
							$(".route_hangup_3 :input").prop('required',false);
						$('.route_exten_3').hide();
							$(".route_exten_3 :input").prop('required',false);
						$('.route_phone_3').hide();
							$(".route_phone_3 :input").prop('required',false);
						$('.route_agi_3').hide();
							$(".route_agi_3 :input").prop('required',false);
						
					}if(this.value == "AGI") {
						$('.route_agi_3').show();
						$(".route_agi_3 :input").prop('required',true);
						
						$('.route_callmenu_3').hide();
							$(".route_callmenu_3 :input").prop('required',false);
						$('.route_ingroup_3').hide();
							$(".route_ingroup_3 :input").prop('required',false);
						$('.route_did_3').hide();
							$(".route_did_3 :input").prop('required',false);
						$('.route_hangup_3').hide();
							$(".route_hangup_3 :input").prop('required',false);
						$('.route_exten_3').hide();
							$(".route_exten_3 :input").prop('required',false);
						$('.route_phone_3').hide();
							$(".route_phone_3 :input").prop('required',false);
						$('.route_voicemail_3').hide();
							$(".route_voicemail_3 :input").prop('required',false);
					}
					if(this.value == "") {
						$('.route_callmenu_3').hide();
							$(".route_callmenu_3 :input").prop('required',false);
						$('.route_ingroup_3').hide();
							$(".route_ingroup_3 :input").prop('required',false);
						$('.route_did_3').hide();
							$(".route_did_3 :input").prop('required',false);
						$('.route_hangup_3').hide();
							$(".route_hangup_3 :input").prop('required',false);
						$('.route_exten_3').hide();
							$(".route_exten_3 :input").prop('required',false);
						$('.route_phone_3').hide();
							$(".route_phone_3 :input").prop('required',false);
						$('.route_voicemail_3').hide();
							$(".route_voicemail_3 :input").prop('required',false);
						$('.route_agi_3').hide();
							$(".route_agi_3 :input").prop('required',false);
					}
				});
				$(document).on('change', '.route_menu_4',function(){
					if(this.value == "CALLMENU") {
						$('.route_callmenu_4').show();
						$(".route_callmenu_4 :input").prop('required',true);
						
						$('.route_ingroup_4').hide();
							$(".route_ingroup_4 :input").prop('required',false);
						$('.route_did_4').hide();
							$(".route_did_4 :input").prop('required',false);
						$('.route_hangup_4').hide();
							$(".route_hangup_4 :input").prop('required',false);
						$('.route_exten_4').hide();
							$(".route_exten_4 :input").prop('required',false);
						$('.route_phone_4').hide();
							$(".route_phone_4 :input").prop('required',false);
						$('.route_voicemail_4').hide();
							$(".route_voicemail_4 :input").prop('required',false);
						$('.route_agi_4').hide();
							$(".route_agi_4 :input").prop('required',false);
					
					}if(this.value == "INGROUP") {
						$('.route_ingroup_4').show();
						$(".route_ingroup_4 :input").prop('required',true);

						$('.route_callmenu_4').hide();
							$(".route_callmenu_4 :input").prop('required',false);
						$('.route_did_4').hide();
							$(".route_did_4 :input").prop('required',false);
						$('.route_hangup_4').hide();
							$(".route_hangup_4 :input").prop('required',false);
						$('.route_exten_4').hide();
							$(".route_exten_4 :input").prop('required',false);
						$('.route_phone_4').hide();
							$(".route_phone_4 :input").prop('required',false);
						$('.route_voicemail_4').hide();
							$(".route_voicemail_4 :input").prop('required',false);
						$('.route_agi_4').hide();
							$(".route_agi_4 :input").prop('required',false);
						
					}if(this.value == "DID") {
						$('.route_did_4').show();
						$(".route_did_4 :input").prop('required',true);

						$('.route_callmenu_4').hide();
							$(".route_callmenu_4 :input").prop('required',false);
						$('.route_ingroup_4').hide();
							$(".route_ingroup_4 :input").prop('required',false);
						$('.route_hangup_4').hide();
							$(".route_hangup_4 :input").prop('required',false);
						$('.route_exten_4').hide();
							$(".route_exten_4 :input").prop('required',false);
						$('.route_phone_4').hide();
							$(".route_phone_4 :input").prop('required',false);
						$('.route_voicemail_4').hide();
							$(".route_voicemail_4 :input").prop('required',false);
						$('.route_agi_4').hide();
							$(".route_agi_4 :input").prop('required',false);
						
					}if(this.value == "HANGUP") {
						$('.route_hangup_4').show();
						$(".route_hangup_4 :input").prop('required',true);
						
						$('.route_callmenu_4').hide();
							$(".route_callmenu_4 :input").prop('required',false);
						$('.route_ingroup_4').hide();
							$(".route_ingroup_4 :input").prop('required',false);
						$('.route_did_4').hide();
							$(".route_did_4 :input").prop('required',false);
						$('.route_exten_4').hide();
							$(".route_exten_4 :input").prop('required',false);
						$('.route_phone_4').hide();
							$(".route_phone_4 :input").prop('required',false);
						$('.route_voicemail_4').hide();
							$(".route_voicemail_4 :input").prop('required',false);
						$('.route_agi_4').hide();
							$(".route_agi_4 :input").prop('required',false);
						
					}if(this.value == "EXTENSION") {
						$('.route_exten_4').show();
						$(".route_exten_4 :input").prop('required',true);
						
						$('.route_callmenu_4').hide();
							$(".route_callmenu_4 :input").prop('required',false);
						$('.route_ingroup_4').hide();
							$(".route_ingroup_4 :input").prop('required',false);
						$('.route_did_4').hide();
							$(".route_did_4 :input").prop('required',false);
						$('.route_hangup_4').hide();
							$(".route_hangup_4 :input").prop('required',false);
						$('.route_phone_4').hide();
							$(".route_phone_4 :input").prop('required',false);
						$('.route_voicemail_4').hide();
							$(".route_voicemail_4 :input").prop('required',false);
						$('.route_agi_4').hide();
							$(".route_agi_4 :input").prop('required',false);
						
					}if(this.value == "PHONE") {
						$('.route_phone_4').show();
						$(".route_phone_4 :input").prop('required',true);
						
						$('.route_callmenu_4').hide();
							$(".route_callmenu_4 :input").prop('required',false);
						$('.route_ingroup_4').hide();
							$(".route_ingroup_4 :input").prop('required',false);
						$('.route_did_4').hide();
							$(".route_did_4 :input").prop('required',false);
						$('.route_hangup_4').hide();
							$(".route_hangup_4 :input").prop('required',false);
						$('.route_exten_4').hide();
							$(".route_exten_4 :input").prop('required',false);
						$('.route_voicemail_4').hide();
							$(".route_voicemail_4 :input").prop('required',false);
						$('.route_agi_4').hide();
							$(".route_agi_4 :input").prop('required',false);
						
					}if(this.value == "VOICEMAIL") {
						$('.route_voicemail_4').show();
						$(".route_voicemail_4 :input").prop('required',true);
						
						$('.route_callmenu_4').hide();
							$(".route_callmenu_4 :input").prop('required',false);
						$('.route_ingroup_4').hide();
							$(".route_ingroup_4 :input").prop('required',false);
						$('.route_did_4').hide();
							$(".route_did_4 :input").prop('required',false);
						$('.route_hangup_4').hide();
							$(".route_hangup_4 :input").prop('required',false);
						$('.route_exten_4').hide();
							$(".route_exten_4 :input").prop('required',false);
						$('.route_phone_4').hide();
							$(".route_phone_4 :input").prop('required',false);
						$('.route_agi_4').hide();
							$(".route_agi_4 :input").prop('required',false);
						
					}if(this.value == "AGI") {
						$('.route_agi_4').show();
						$(".route_agi_4 :input").prop('required',true);
						
						$('.route_callmenu_4').hide();
							$(".route_callmenu_4 :input").prop('required',false);
						$('.route_ingroup_4').hide();
							$(".route_ingroup_4 :input").prop('required',false);
						$('.route_did_4').hide();
							$(".route_did_4 :input").prop('required',false);
						$('.route_hangup_4').hide();
							$(".route_hangup_4 :input").prop('required',false);
						$('.route_exten_4').hide();
							$(".route_exten_4 :input").prop('required',false);
						$('.route_phone_4').hide();
							$(".route_phone_4 :input").prop('required',false);
						$('.route_voicemail_4').hide();
							$(".route_voicemail_4 :input").prop('required',false);
					}
					if(this.value == "") {
						$('.route_callmenu_4').hide();
							$(".route_callmenu_4 :input").prop('required',false);
						$('.route_ingroup_4').hide();
							$(".route_ingroup_4 :input").prop('required',false);
						$('.route_did_4').hide();
							$(".route_did_4 :input").prop('required',false);
						$('.route_hangup_4').hide();
							$(".route_hangup_4 :input").prop('required',false);
						$('.route_exten_4').hide();
							$(".route_exten_4 :input").prop('required',false);
						$('.route_phone_4').hide();
							$(".route_phone_4 :input").prop('required',false);
						$('.route_voicemail_4').hide();
							$(".route_voicemail_4 :input").prop('required',false);
						$('.route_agi_4').hide();
							$(".route_agi_4 :input").prop('required',false);
					}
				});
				$(document).on('change', '.route_menu_5',function(){
					if(this.value == "CALLMENU") {
						$('.route_callmenu_5').show();
						$(".route_callmenu_5 :input").prop('required',true);
						
						$('.route_ingroup_5').hide();
							$(".route_ingroup_5 :input").prop('required',false);
						$('.route_did_5').hide();
							$(".route_did_5 :input").prop('required',false);
						$('.route_hangup_5').hide();
							$(".route_hangup_5 :input").prop('required',false);
						$('.route_exten_5').hide();
							$(".route_exten_5 :input").prop('required',false);
						$('.route_phone_5').hide();
							$(".route_phone_5 :input").prop('required',false);
						$('.route_voicemail_5').hide();
							$(".route_voicemail_5 :input").prop('required',false);
						$('.route_agi_5').hide();
							$(".route_agi_5 :input").prop('required',false);
					
					}if(this.value == "INGROUP") {
						$('.route_ingroup_5').show();
						$(".route_ingroup_5 :input").prop('required',true);

						$('.route_callmenu_5').hide();
							$(".route_callmenu_5 :input").prop('required',false);
						$('.route_did_5').hide();
							$(".route_did_5 :input").prop('required',false);
						$('.route_hangup_5').hide();
							$(".route_hangup_5 :input").prop('required',false);
						$('.route_exten_5').hide();
							$(".route_exten_5 :input").prop('required',false);
						$('.route_phone_5').hide();
							$(".route_phone_5 :input").prop('required',false);
						$('.route_voicemail_5').hide();
							$(".route_voicemail_5 :input").prop('required',false);
						$('.route_agi_5').hide();
							$(".route_agi_5 :input").prop('required',false);
						
					}if(this.value == "DID") {
						$('.route_did_5').show();
						$(".route_did_5 :input").prop('required',true);

						$('.route_callmenu_5').hide();
							$(".route_callmenu_5 :input").prop('required',false);
						$('.route_ingroup_5').hide();
							$(".route_ingroup_5 :input").prop('required',false);
						$('.route_hangup_5').hide();
							$(".route_hangup_5 :input").prop('required',false);
						$('.route_exten_5').hide();
							$(".route_exten_5 :input").prop('required',false);
						$('.route_phone_5').hide();
							$(".route_phone_5 :input").prop('required',false);
						$('.route_voicemail_5').hide();
							$(".route_voicemail_5 :input").prop('required',false);
						$('.route_agi_5').hide();
							$(".route_agi_5 :input").prop('required',false);
						
					}if(this.value == "HANGUP") {
						$('.route_hangup_5').show();
						$(".route_hangup_5 :input").prop('required',true);
						
						$('.route_callmenu_5').hide();
							$(".route_callmenu_5 :input").prop('required',false);
						$('.route_ingroup_5').hide();
							$(".route_ingroup_5 :input").prop('required',false);
						$('.route_did_5').hide();
							$(".route_did_5 :input").prop('required',false);
						$('.route_exten_5').hide();
							$(".route_exten_5 :input").prop('required',false);
						$('.route_phone_5').hide();
							$(".route_phone_5 :input").prop('required',false);
						$('.route_voicemail_5').hide();
							$(".route_voicemail_5 :input").prop('required',false);
						$('.route_agi_5').hide();
							$(".route_agi_5 :input").prop('required',false);
						
					}if(this.value == "EXTENSION") {
						$('.route_exten_5').show();
						$(".route_exten_5 :input").prop('required',true);
						
						$('.route_callmenu_5').hide();
							$(".route_callmenu_5 :input").prop('required',false);
						$('.route_ingroup_5').hide();
							$(".route_ingroup_5 :input").prop('required',false);
						$('.route_did_5').hide();
							$(".route_did_5 :input").prop('required',false);
						$('.route_hangup_5').hide();
							$(".route_hangup_5 :input").prop('required',false);
						$('.route_phone_5').hide();
							$(".route_phone_5 :input").prop('required',false);
						$('.route_voicemail_5').hide();
							$(".route_voicemail_5 :input").prop('required',false);
						$('.route_agi_5').hide();
							$(".route_agi_5 :input").prop('required',false);
						
					}if(this.value == "PHONE") {
						$('.route_phone_5').show();
						$(".route_phone_5 :input").prop('required',true);
						
						$('.route_callmenu_5').hide();
							$(".route_callmenu_5 :input").prop('required',false);
						$('.route_ingroup_5').hide();
							$(".route_ingroup_5 :input").prop('required',false);
						$('.route_did_5').hide();
							$(".route_did_5 :input").prop('required',false);
						$('.route_hangup_5').hide();
							$(".route_hangup_5 :input").prop('required',false);
						$('.route_exten_5').hide();
							$(".route_exten_5 :input").prop('required',false);
						$('.route_voicemail_5').hide();
							$(".route_voicemail_5 :input").prop('required',false);
						$('.route_agi_5').hide();
							$(".route_agi_5 :input").prop('required',false);
						
					}if(this.value == "VOICEMAIL") {
						$('.route_voicemail_5').show();
						$(".route_voicemail_5 :input").prop('required',true);
						
						$('.route_callmenu_5').hide();
							$(".route_callmenu_5 :input").prop('required',false);
						$('.route_ingroup_5').hide();
							$(".route_ingroup_5 :input").prop('required',false);
						$('.route_did_5').hide();
							$(".route_did_5 :input").prop('required',false);
						$('.route_hangup_5').hide();
							$(".route_hangup_5 :input").prop('required',false);
						$('.route_exten_5').hide();
							$(".route_exten_5 :input").prop('required',false);
						$('.route_phone_5').hide();
							$(".route_phone_5 :input").prop('required',false);
						$('.route_agi_5').hide();
							$(".route_agi_5 :input").prop('required',false);
						
					}if(this.value == "AGI") {
						$('.route_agi_5').show();
						$(".route_agi_5 :input").prop('required',true);
						
						$('.route_callmenu_5').hide();
							$(".route_callmenu_5 :input").prop('required',false);
						$('.route_ingroup_5').hide();
							$(".route_ingroup_5 :input").prop('required',false);
						$('.route_did_5').hide();
							$(".route_did_5 :input").prop('required',false);
						$('.route_hangup_5').hide();
							$(".route_hangup_5 :input").prop('required',false);
						$('.route_exten_5').hide();
							$(".route_exten_5 :input").prop('required',false);
						$('.route_phone_5').hide();
							$(".route_phone_5 :input").prop('required',false);
						$('.route_voicemail_5').hide();
							$(".route_voicemail_5 :input").prop('required',false);
					}
					if(this.value == "") {
						$('.route_callmenu_5').hide();
							$(".route_callmenu_5 :input").prop('required',false);
						$('.route_ingroup_5').hide();
							$(".route_ingroup_5 :input").prop('required',false);
						$('.route_did_5').hide();
							$(".route_did_5 :input").prop('required',false);
						$('.route_hangup_5').hide();
							$(".route_hangup_5 :input").prop('required',false);
						$('.route_exten_5').hide();
							$(".route_exten_5 :input").prop('required',false);
						$('.route_phone_5').hide();
							$(".route_phone_5 :input").prop('required',false);
						$('.route_voicemail_5').hide();
							$(".route_voicemail_5 :input").prop('required',false);
						$('.route_agi_5').hide();
							$(".route_agi_5 :input").prop('required',false);
					}
				});
				$(document).on('change', '.route_menu_6',function(){
					if(this.value == "CALLMENU") {
						$('.route_callmenu_6').show();
						$(".route_callmenu_6 :input").prop('required',true);
						
						$('.route_ingroup_6').hide();
							$(".route_ingroup_6 :input").prop('required',false);
						$('.route_did_6').hide();
							$(".route_did_6 :input").prop('required',false);
						$('.route_hangup_6').hide();
							$(".route_hangup_6 :input").prop('required',false);
						$('.route_exten_6').hide();
							$(".route_exten_6 :input").prop('required',false);
						$('.route_phone_6').hide();
							$(".route_phone_6 :input").prop('required',false);
						$('.route_voicemail_6').hide();
							$(".route_voicemail_6 :input").prop('required',false);
						$('.route_agi_6').hide();
							$(".route_agi_6 :input").prop('required',false);
					
					}if(this.value == "INGROUP") {
						$('.route_ingroup_6').show();
						$(".route_ingroup_6 :input").prop('required',true);

						$('.route_callmenu_6').hide();
							$(".route_callmenu_6 :input").prop('required',false);
						$('.route_did_6').hide();
							$(".route_did_6 :input").prop('required',false);
						$('.route_hangup_6').hide();
							$(".route_hangup_6 :input").prop('required',false);
						$('.route_exten_6').hide();
							$(".route_exten_6 :input").prop('required',false);
						$('.route_phone_6').hide();
							$(".route_phone_6 :input").prop('required',false);
						$('.route_voicemail_6').hide();
							$(".route_voicemail_6 :input").prop('required',false);
						$('.route_agi_6').hide();
							$(".route_agi_6 :input").prop('required',false);
						
					}if(this.value == "DID") {
						$('.route_did_6').show();
						$(".route_did_6 :input").prop('required',true);

						$('.route_callmenu_6').hide();
							$(".route_callmenu_6 :input").prop('required',false);
						$('.route_ingroup_6').hide();
							$(".route_ingroup_6 :input").prop('required',false);
						$('.route_hangup_6').hide();
							$(".route_hangup_6 :input").prop('required',false);
						$('.route_exten_6').hide();
							$(".route_exten_6 :input").prop('required',false);
						$('.route_phone_6').hide();
							$(".route_phone_6 :input").prop('required',false);
						$('.route_voicemail_6').hide();
							$(".route_voicemail_6 :input").prop('required',false);
						$('.route_agi_6').hide();
							$(".route_agi_6 :input").prop('required',false);
						
					}if(this.value == "HANGUP") {
						$('.route_hangup_6').show();
						$(".route_hangup_6 :input").prop('required',true);
						
						$('.route_callmenu_6').hide();
							$(".route_callmenu_6 :input").prop('required',false);
						$('.route_ingroup_6').hide();
							$(".route_ingroup_6 :input").prop('required',false);
						$('.route_did_6').hide();
							$(".route_did_6 :input").prop('required',false);
						$('.route_exten_6').hide();
							$(".route_exten_6 :input").prop('required',false);
						$('.route_phone_6').hide();
							$(".route_phone_6 :input").prop('required',false);
						$('.route_voicemail_6').hide();
							$(".route_voicemail_6 :input").prop('required',false);
						$('.route_agi_6').hide();
							$(".route_agi_6 :input").prop('required',false);
						
					}if(this.value == "EXTENSION") {
						$('.route_exten_6').show();
						$(".route_exten_6 :input").prop('required',true);
						
						$('.route_callmenu_6').hide();
							$(".route_callmenu_6 :input").prop('required',false);
						$('.route_ingroup_6').hide();
							$(".route_ingroup_6 :input").prop('required',false);
						$('.route_did_6').hide();
							$(".route_did_6 :input").prop('required',false);
						$('.route_hangup_6').hide();
							$(".route_hangup_6 :input").prop('required',false);
						$('.route_phone_6').hide();
							$(".route_phone_6 :input").prop('required',false);
						$('.route_voicemail_6').hide();
							$(".route_voicemail_6 :input").prop('required',false);
						$('.route_agi_6').hide();
							$(".route_agi_6 :input").prop('required',false);
						
					}if(this.value == "PHONE") {
						$('.route_phone_6').show();
						$(".route_phone_6 :input").prop('required',true);
						
						$('.route_callmenu_6').hide();
							$(".route_callmenu_6 :input").prop('required',false);
						$('.route_ingroup_6').hide();
							$(".route_ingroup_6 :input").prop('required',false);
						$('.route_did_6').hide();
							$(".route_did_6 :input").prop('required',false);
						$('.route_hangup_6').hide();
							$(".route_hangup_6 :input").prop('required',false);
						$('.route_exten_6').hide();
							$(".route_exten_6 :input").prop('required',false);
						$('.route_voicemail_6').hide();
							$(".route_voicemail_6 :input").prop('required',false);
						$('.route_agi_6').hide();
							$(".route_agi_6 :input").prop('required',false);
						
					}if(this.value == "VOICEMAIL") {
						$('.route_voicemail_6').show();
						$(".route_voicemail_6 :input").prop('required',true);
						
						$('.route_callmenu_6').hide();
							$(".route_callmenu_6 :input").prop('required',false);
						$('.route_ingroup_6').hide();
							$(".route_ingroup_6 :input").prop('required',false);
						$('.route_did_6').hide();
							$(".route_did_6 :input").prop('required',false);
						$('.route_hangup_6').hide();
							$(".route_hangup_6 :input").prop('required',false);
						$('.route_exten_6').hide();
							$(".route_exten_6 :input").prop('required',false);
						$('.route_phone_6').hide();
							$(".route_phone_6 :input").prop('required',false);
						$('.route_agi_6').hide();
							$(".route_agi_6 :input").prop('required',false);
						
					}if(this.value == "AGI") {
						$('.route_agi_6').show();
						$(".route_agi_6 :input").prop('required',true);
						
						$('.route_callmenu_6').hide();
							$(".route_callmenu_6 :input").prop('required',false);
						$('.route_ingroup_6').hide();
							$(".route_ingroup_6 :input").prop('required',false);
						$('.route_did_6').hide();
							$(".route_did_6 :input").prop('required',false);
						$('.route_hangup_6').hide();
							$(".route_hangup_6 :input").prop('required',false);
						$('.route_exten_6').hide();
							$(".route_exten_6 :input").prop('required',false);
						$('.route_phone_6').hide();
							$(".route_phone_6 :input").prop('required',false);
						$('.route_voicemail_6').hide();
							$(".route_voicemail_6 :input").prop('required',false);
					}
					if(this.value == "") {
						$('.route_callmenu_6').hide();
							$(".route_callmenu_6 :input").prop('required',false);
						$('.route_ingroup_6').hide();
							$(".route_ingroup_6 :input").prop('required',false);
						$('.route_did_6').hide();
							$(".route_did_6 :input").prop('required',false);
						$('.route_hangup_6').hide();
							$(".route_hangup_6 :input").prop('required',false);
						$('.route_exten_6').hide();
							$(".route_exten_6 :input").prop('required',false);
						$('.route_phone_6').hide();
							$(".route_phone_6 :input").prop('required',false);
						$('.route_voicemail_6').hide();
							$(".route_voicemail_6 :input").prop('required',false);
						$('.route_agi_6').hide();
							$(".route_agi_6 :input").prop('required',false);
					}
				});
				$(document).on('change', '.route_menu_7',function(){
					if(this.value == "CALLMENU") {
						$('.route_callmenu_7').show();
						$(".route_callmenu_7 :input").prop('required',true);
						
						$('.route_ingroup_7').hide();
							$(".route_ingroup_7 :input").prop('required',false);
						$('.route_did_7').hide();
							$(".route_did_7 :input").prop('required',false);
						$('.route_hangup_7').hide();
							$(".route_hangup_7 :input").prop('required',false);
						$('.route_exten_7').hide();
							$(".route_exten_7 :input").prop('required',false);
						$('.route_phone_7').hide();
							$(".route_phone_7 :input").prop('required',false);
						$('.route_voicemail_7').hide();
							$(".route_voicemail_7 :input").prop('required',false);
						$('.route_agi_7').hide();
							$(".route_agi_7 :input").prop('required',false);
					
					}if(this.value == "INGROUP") {
						$('.route_ingroup_7').show();
						$(".route_ingroup_7 :input").prop('required',true);

						$('.route_callmenu_7').hide();
							$(".route_callmenu_7 :input").prop('required',false);
						$('.route_did_7').hide();
							$(".route_did_7 :input").prop('required',false);
						$('.route_hangup_7').hide();
							$(".route_hangup_7 :input").prop('required',false);
						$('.route_exten_7').hide();
							$(".route_exten_7 :input").prop('required',false);
						$('.route_phone_7').hide();
							$(".route_phone_7 :input").prop('required',false);
						$('.route_voicemail_7').hide();
							$(".route_voicemail_7 :input").prop('required',false);
						$('.route_agi_7').hide();
							$(".route_agi_7 :input").prop('required',false);
						
					}if(this.value == "DID") {
						$('.route_did_7').show();
						$(".route_did_7 :input").prop('required',true);

						$('.route_callmenu_7').hide();
							$(".route_callmenu_7 :input").prop('required',false);
						$('.route_ingroup_7').hide();
							$(".route_ingroup_7 :input").prop('required',false);
						$('.route_hangup_7').hide();
							$(".route_hangup_7 :input").prop('required',false);
						$('.route_exten_7').hide();
							$(".route_exten_7 :input").prop('required',false);
						$('.route_phone_7').hide();
							$(".route_phone_7 :input").prop('required',false);
						$('.route_voicemail_7').hide();
							$(".route_voicemail_7 :input").prop('required',false);
						$('.route_agi_7').hide();
							$(".route_agi_7 :input").prop('required',false);
						
					}if(this.value == "HANGUP") {
						$('.route_hangup_7').show();
						$(".route_hangup_7 :input").prop('required',true);
						
						$('.route_callmenu_7').hide();
							$(".route_callmenu_7 :input").prop('required',false);
						$('.route_ingroup_7').hide();
							$(".route_ingroup_7 :input").prop('required',false);
						$('.route_did_7').hide();
							$(".route_did_7 :input").prop('required',false);
						$('.route_exten_7').hide();
							$(".route_exten_7 :input").prop('required',false);
						$('.route_phone_7').hide();
							$(".route_phone_7 :input").prop('required',false);
						$('.route_voicemail_7').hide();
							$(".route_voicemail_7 :input").prop('required',false);
						$('.route_agi_7').hide();
							$(".route_agi_7 :input").prop('required',false);
						
					}if(this.value == "EXTENSION") {
						$('.route_exten_7').show();
						$(".route_exten_7 :input").prop('required',true);
						
						$('.route_callmenu_7').hide();
							$(".route_callmenu_7 :input").prop('required',false);
						$('.route_ingroup_7').hide();
							$(".route_ingroup_7 :input").prop('required',false);
						$('.route_did_7').hide();
							$(".route_did_7 :input").prop('required',false);
						$('.route_hangup_7').hide();
							$(".route_hangup_7 :input").prop('required',false);
						$('.route_phone_7').hide();
							$(".route_phone_7 :input").prop('required',false);
						$('.route_voicemail_7').hide();
							$(".route_voicemail_7 :input").prop('required',false);
						$('.route_agi_7').hide();
							$(".route_agi_7 :input").prop('required',false);
						
					}if(this.value == "PHONE") {
						$('.route_phone_7').show();
						$(".route_phone_7 :input").prop('required',true);
						
						$('.route_callmenu_7').hide();
							$(".route_callmenu_7 :input").prop('required',false);
						$('.route_ingroup_7').hide();
							$(".route_ingroup_7 :input").prop('required',false);
						$('.route_did_7').hide();
							$(".route_did_7 :input").prop('required',false);
						$('.route_hangup_7').hide();
							$(".route_hangup_7 :input").prop('required',false);
						$('.route_exten_7').hide();
							$(".route_exten_7 :input").prop('required',false);
						$('.route_voicemail_7').hide();
							$(".route_voicemail_7 :input").prop('required',false);
						$('.route_agi_7').hide();
							$(".route_agi_7 :input").prop('required',false);
						
					}if(this.value == "VOICEMAIL") {
						$('.route_voicemail_7').show();
						$(".route_voicemail_7 :input").prop('required',true);
						
						$('.route_callmenu_7').hide();
							$(".route_callmenu_7 :input").prop('required',false);
						$('.route_ingroup_7').hide();
							$(".route_ingroup_7 :input").prop('required',false);
						$('.route_did_7').hide();
							$(".route_did_7 :input").prop('required',false);
						$('.route_hangup_7').hide();
							$(".route_hangup_7 :input").prop('required',false);
						$('.route_exten_7').hide();
							$(".route_exten_7 :input").prop('required',false);
						$('.route_phone_7').hide();
							$(".route_phone_7 :input").prop('required',false);
						$('.route_agi_7').hide();
							$(".route_agi_7 :input").prop('required',false);
						
					}if(this.value == "AGI") {
						$('.route_agi_7').show();
						$(".route_agi_7 :input").prop('required',true);
						
						$('.route_callmenu_7').hide();
							$(".route_callmenu_7 :input").prop('required',false);
						$('.route_ingroup_7').hide();
							$(".route_ingroup_7 :input").prop('required',false);
						$('.route_did_7').hide();
							$(".route_did_7 :input").prop('required',false);
						$('.route_hangup_7').hide();
							$(".route_hangup_7 :input").prop('required',false);
						$('.route_exten_7').hide();
							$(".route_exten_7 :input").prop('required',false);
						$('.route_phone_7').hide();
							$(".route_phone_7 :input").prop('required',false);
						$('.route_voicemail_7').hide();
							$(".route_voicemail_7 :input").prop('required',false);
					}
					if(this.value == "") {
						$('.route_callmenu_7').hide();
							$(".route_callmenu_7 :input").prop('required',false);
						$('.route_ingroup_7').hide();
							$(".route_ingroup_7 :input").prop('required',false);
						$('.route_did_7').hide();
							$(".route_did_7 :input").prop('required',false);
						$('.route_hangup_7').hide();
							$(".route_hangup_7 :input").prop('required',false);
						$('.route_exten_7').hide();
							$(".route_exten_7 :input").prop('required',false);
						$('.route_phone_7').hide();
							$(".route_phone_7 :input").prop('required',false);
						$('.route_voicemail_7').hide();
							$(".route_voicemail_7 :input").prop('required',false);
						$('.route_agi_7').hide();
							$(".route_agi_7 :input").prop('required',false);
					}
				});
				$(document).on('change', '.route_menu_8',function(){
					if(this.value == "CALLMENU") {
						$('.route_callmenu_8').show();
						$(".route_callmenu_8 :input").prop('required',true);
						
						$('.route_ingroup_8').hide();
							$(".route_ingroup_8 :input").prop('required',false);
						$('.route_did_8').hide();
							$(".route_did_8 :input").prop('required',false);
						$('.route_hangup_8').hide();
							$(".route_hangup_8 :input").prop('required',false);
						$('.route_exten_8').hide();
							$(".route_exten_8 :input").prop('required',false);
						$('.route_phone_8').hide();
							$(".route_phone_8 :input").prop('required',false);
						$('.route_voicemail_8').hide();
							$(".route_voicemail_8 :input").prop('required',false);
						$('.route_agi_8').hide();
							$(".route_agi_8 :input").prop('required',false);
					
					}if(this.value == "INGROUP") {
						$('.route_ingroup_8').show();
						$(".route_ingroup_8 :input").prop('required',true);

						$('.route_callmenu_8').hide();
							$(".route_callmenu_8 :input").prop('required',false);
						$('.route_did_8').hide();
							$(".route_did_8 :input").prop('required',false);
						$('.route_hangup_8').hide();
							$(".route_hangup_8 :input").prop('required',false);
						$('.route_exten_8').hide();
							$(".route_exten_8 :input").prop('required',false);
						$('.route_phone_8').hide();
							$(".route_phone_8 :input").prop('required',false);
						$('.route_voicemail_8').hide();
							$(".route_voicemail_8 :input").prop('required',false);
						$('.route_agi_8').hide();
							$(".route_agi_8 :input").prop('required',false);
						
					}if(this.value == "DID") {
						$('.route_did_8').show();
						$(".route_did_8 :input").prop('required',true);

						$('.route_callmenu_8').hide();
							$(".route_callmenu_8 :input").prop('required',false);
						$('.route_ingroup_8').hide();
							$(".route_ingroup_8 :input").prop('required',false);
						$('.route_hangup_8').hide();
							$(".route_hangup_8 :input").prop('required',false);
						$('.route_exten_8').hide();
							$(".route_exten_8 :input").prop('required',false);
						$('.route_phone_8').hide();
							$(".route_phone_8 :input").prop('required',false);
						$('.route_voicemail_8').hide();
							$(".route_voicemail_8 :input").prop('required',false);
						$('.route_agi_8').hide();
							$(".route_agi_8 :input").prop('required',false);
						
					}if(this.value == "HANGUP") {
						$('.route_hangup_8').show();
						$(".route_hangup_8 :input").prop('required',true);
						
						$('.route_callmenu_8').hide();
							$(".route_callmenu_8 :input").prop('required',false);
						$('.route_ingroup_8').hide();
							$(".route_ingroup_8 :input").prop('required',false);
						$('.route_did_8').hide();
							$(".route_did_8 :input").prop('required',false);
						$('.route_exten_8').hide();
							$(".route_exten_8 :input").prop('required',false);
						$('.route_phone_8').hide();
							$(".route_phone_8 :input").prop('required',false);
						$('.route_voicemail_8').hide();
							$(".route_voicemail_8 :input").prop('required',false);
						$('.route_agi_8').hide();
							$(".route_agi_8 :input").prop('required',false);
						
					}if(this.value == "EXTENSION") {
						$('.route_exten_8').show();
						$(".route_exten_8 :input").prop('required',true);
						
						$('.route_callmenu_8').hide();
							$(".route_callmenu_8 :input").prop('required',false);
						$('.route_ingroup_8').hide();
							$(".route_ingroup_8 :input").prop('required',false);
						$('.route_did_8').hide();
							$(".route_did_8 :input").prop('required',false);
						$('.route_hangup_8').hide();
							$(".route_hangup_8 :input").prop('required',false);
						$('.route_phone_8').hide();
							$(".route_phone_8 :input").prop('required',false);
						$('.route_voicemail_8').hide();
							$(".route_voicemail_8 :input").prop('required',false);
						$('.route_agi_8').hide();
							$(".route_agi_8 :input").prop('required',false);
						
					}if(this.value == "PHONE") {
						$('.route_phone_8').show();
						$(".route_phone_8 :input").prop('required',true);
						
						$('.route_callmenu_8').hide();
							$(".route_callmenu_8 :input").prop('required',false);
						$('.route_ingroup_8').hide();
							$(".route_ingroup_8 :input").prop('required',false);
						$('.route_did_8').hide();
							$(".route_did_8 :input").prop('required',false);
						$('.route_hangup_8').hide();
							$(".route_hangup_8 :input").prop('required',false);
						$('.route_exten_8').hide();
							$(".route_exten_8 :input").prop('required',false);
						$('.route_voicemail_8').hide();
							$(".route_voicemail_8 :input").prop('required',false);
						$('.route_agi_8').hide();
							$(".route_agi_8 :input").prop('required',false);
						
					}if(this.value == "VOICEMAIL") {
						$('.route_voicemail_8').show();
						$(".route_voicemail_8 :input").prop('required',true);
						
						$('.route_callmenu_8').hide();
							$(".route_callmenu_8 :input").prop('required',false);
						$('.route_ingroup_8').hide();
							$(".route_ingroup_8 :input").prop('required',false);
						$('.route_did_8').hide();
							$(".route_did_8 :input").prop('required',false);
						$('.route_hangup_8').hide();
							$(".route_hangup_8 :input").prop('required',false);
						$('.route_exten_8').hide();
							$(".route_exten_8 :input").prop('required',false);
						$('.route_phone_8').hide();
							$(".route_phone_8 :input").prop('required',false);
						$('.route_agi_8').hide();
							$(".route_agi_8 :input").prop('required',false);
						
					}if(this.value == "AGI") {
						$('.route_agi_8').show();
						$(".route_agi_8 :input").prop('required',true);
						
						$('.route_callmenu_8').hide();
							$(".route_callmenu_8 :input").prop('required',false);
						$('.route_ingroup_8').hide();
							$(".route_ingroup_8 :input").prop('required',false);
						$('.route_did_8').hide();
							$(".route_did_8 :input").prop('required',false);
						$('.route_hangup_8').hide();
							$(".route_hangup_8 :input").prop('required',false);
						$('.route_exten_8').hide();
							$(".route_exten_8 :input").prop('required',false);
						$('.route_phone_8').hide();
							$(".route_phone_8 :input").prop('required',false);
						$('.route_voicemail_8').hide();
							$(".route_voicemail_8 :input").prop('required',false);
					}
					if(this.value == "") {
						$('.route_callmenu_8').hide();
							$(".route_callmenu_8 :input").prop('required',false);
						$('.route_ingroup_8').hide();
							$(".route_ingroup_8 :input").prop('required',false);
						$('.route_did_8').hide();
							$(".route_did_8 :input").prop('required',false);
						$('.route_hangup_8').hide();
							$(".route_hangup_8 :input").prop('required',false);
						$('.route_exten_8').hide();
							$(".route_exten_8 :input").prop('required',false);
						$('.route_phone_8').hide();
							$(".route_phone_8 :input").prop('required',false);
						$('.route_voicemail_8').hide();
							$(".route_voicemail_8 :input").prop('required',false);
						$('.route_agi_8').hide();
							$(".route_agi_8 :input").prop('required',false);
					}
				});
				$(document).on('change', '.route_menu_9',function(){
					if(this.value == "CALLMENU") {
						$('.route_callmenu_9').show();
						$(".route_callmenu_9 :input").prop('required',true);
						
						$('.route_ingroup_9').hide();
							$(".route_ingroup_9 :input").prop('required',false);
						$('.route_did_9').hide();
							$(".route_did_9 :input").prop('required',false);
						$('.route_hangup_9').hide();
							$(".route_hangup_9 :input").prop('required',false);
						$('.route_exten_9').hide();
							$(".route_exten_9 :input").prop('required',false);
						$('.route_phone_9').hide();
							$(".route_phone_9 :input").prop('required',false);
						$('.route_voicemail_9').hide();
							$(".route_voicemail_9 :input").prop('required',false);
						$('.route_agi_9').hide();
							$(".route_agi_9 :input").prop('required',false);
					
					}if(this.value == "INGROUP") {
						$('.route_ingroup_9').show();
						$(".route_ingroup_9 :input").prop('required',true);

						$('.route_callmenu_9').hide();
							$(".route_callmenu_9 :input").prop('required',false);
						$('.route_did_9').hide();
							$(".route_did_9 :input").prop('required',false);
						$('.route_hangup_9').hide();
							$(".route_hangup_9 :input").prop('required',false);
						$('.route_exten_9').hide();
							$(".route_exten_9 :input").prop('required',false);
						$('.route_phone_9').hide();
							$(".route_phone_9 :input").prop('required',false);
						$('.route_voicemail_9').hide();
							$(".route_voicemail_9 :input").prop('required',false);
						$('.route_agi_9').hide();
							$(".route_agi_9 :input").prop('required',false);
						
					}if(this.value == "DID") {
						$('.route_did_9').show();
						$(".route_did_9 :input").prop('required',true);

						$('.route_callmenu_9').hide();
							$(".route_callmenu_9 :input").prop('required',false);
						$('.route_ingroup_9').hide();
							$(".route_ingroup_9 :input").prop('required',false);
						$('.route_hangup_9').hide();
							$(".route_hangup_9 :input").prop('required',false);
						$('.route_exten_9').hide();
							$(".route_exten_9 :input").prop('required',false);
						$('.route_phone_9').hide();
							$(".route_phone_9 :input").prop('required',false);
						$('.route_voicemail_9').hide();
							$(".route_voicemail_9 :input").prop('required',false);
						$('.route_agi_9').hide();
							$(".route_agi_9 :input").prop('required',false);
						
					}if(this.value == "HANGUP") {
						$('.route_hangup_9').show();
						$(".route_hangup_9 :input").prop('required',true);
						
						$('.route_callmenu_9').hide();
							$(".route_callmenu_9 :input").prop('required',false);
						$('.route_ingroup_9').hide();
							$(".route_ingroup_9 :input").prop('required',false);
						$('.route_did_9').hide();
							$(".route_did_9 :input").prop('required',false);
						$('.route_exten_9').hide();
							$(".route_exten_9 :input").prop('required',false);
						$('.route_phone_9').hide();
							$(".route_phone_9 :input").prop('required',false);
						$('.route_voicemail_9').hide();
							$(".route_voicemail_9 :input").prop('required',false);
						$('.route_agi_9').hide();
							$(".route_agi_9 :input").prop('required',false);
						
					}if(this.value == "EXTENSION") {
						$('.route_exten_9').show();
						$(".route_exten_9 :input").prop('required',true);
						
						$('.route_callmenu_9').hide();
							$(".route_callmenu_9 :input").prop('required',false);
						$('.route_ingroup_9').hide();
							$(".route_ingroup_9 :input").prop('required',false);
						$('.route_did_9').hide();
							$(".route_did_9 :input").prop('required',false);
						$('.route_hangup_9').hide();
							$(".route_hangup_9 :input").prop('required',false);
						$('.route_phone_9').hide();
							$(".route_phone_9 :input").prop('required',false);
						$('.route_voicemail_9').hide();
							$(".route_voicemail_9 :input").prop('required',false);
						$('.route_agi_9').hide();
							$(".route_agi_9 :input").prop('required',false);
						
					}if(this.value == "PHONE") {
						$('.route_phone_9').show();
						$(".route_phone_9 :input").prop('required',true);
						
						$('.route_callmenu_9').hide();
							$(".route_callmenu_9 :input").prop('required',false);
						$('.route_ingroup_9').hide();
							$(".route_ingroup_9 :input").prop('required',false);
						$('.route_did_9').hide();
							$(".route_did_9 :input").prop('required',false);
						$('.route_hangup_9').hide();
							$(".route_hangup_9 :input").prop('required',false);
						$('.route_exten_9').hide();
							$(".route_exten_9 :input").prop('required',false);
						$('.route_voicemail_9').hide();
							$(".route_voicemail_9 :input").prop('required',false);
						$('.route_agi_9').hide();
							$(".route_agi_9 :input").prop('required',false);
						
					}if(this.value == "VOICEMAIL") {
						$('.route_voicemail_9').show();
						$(".route_voicemail_9 :input").prop('required',true);
						
						$('.route_callmenu_9').hide();
							$(".route_callmenu_9 :input").prop('required',false);
						$('.route_ingroup_9').hide();
							$(".route_ingroup_9 :input").prop('required',false);
						$('.route_did_9').hide();
							$(".route_did_9 :input").prop('required',false);
						$('.route_hangup_9').hide();
							$(".route_hangup_9 :input").prop('required',false);
						$('.route_exten_9').hide();
							$(".route_exten_9 :input").prop('required',false);
						$('.route_phone_9').hide();
							$(".route_phone_9 :input").prop('required',false);
						$('.route_agi_9').hide();
							$(".route_agi_9 :input").prop('required',false);
						
					}if(this.value == "AGI") {
						$('.route_agi_9').show();
						$(".route_agi_9 :input").prop('required',true);
						
						$('.route_callmenu_9').hide();
							$(".route_callmenu_9 :input").prop('required',false);
						$('.route_ingroup_9').hide();
							$(".route_ingroup_9 :input").prop('required',false);
						$('.route_did_9').hide();
							$(".route_did_9 :input").prop('required',false);
						$('.route_hangup_9').hide();
							$(".route_hangup_9 :input").prop('required',false);
						$('.route_exten_9').hide();
							$(".route_exten_9 :input").prop('required',false);
						$('.route_phone_9').hide();
							$(".route_phone_9 :input").prop('required',false);
						$('.route_voicemail_9').hide();
							$(".route_voicemail_9 :input").prop('required',false);
					}
					if(this.value == "") {
						$('.route_callmenu_9').hide();
							$(".route_callmenu_9 :input").prop('required',false);
						$('.route_ingroup_9').hide();
							$(".route_ingroup_9 :input").prop('required',false);
						$('.route_did_9').hide();
							$(".route_did_9 :input").prop('required',false);
						$('.route_hangup_9').hide();
							$(".route_hangup_9 :input").prop('required',false);
						$('.route_exten_9').hide();
							$(".route_exten_9 :input").prop('required',false);
						$('.route_phone_9').hide();
							$(".route_phone_9 :input").prop('required',false);
						$('.route_voicemail_9').hide();
							$(".route_voicemail_9 :input").prop('required',false);
						$('.route_agi_9').hide();
							$(".route_agi_9 :input").prop('required',false);
					}
				});
				
			/*** DID ***/
				// disable special characters on DID Exten
					$('#did_exten').bind('keypress', function (event) {
					    var regex = new RegExp("^[a-zA-Z0-9]+$");
					    var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
					    if (!regex.test(key)) {
					       event.preventDefault();
					       return false;
					    }
					});
				// disable special characters on DID Desc
					$('#desc').bind('keypress', function (event) {
					    var regex = new RegExp("^[a-zA-Z0-9 ]+$");
					    var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
					    if (!regex.test(key)) {
					       event.preventDefault();
					       return false;
					    }
					});

				//route change
					$('#route').on('change', function() {
						if(this.value == "AGENT") {
						  $('#form_route_agent').show();
						  
						  $('#form_route_ingroup').hide();
						  $('#form_route_phone').hide();
						  $('#form_route_callmenu').hide();
						  $('#form_route_voicemail').hide();
						  $('#form_route_exten').hide();
						}if(this.value == "IN_GROUP") {
						  $('#form_route_ingroup').show();
						  
						  $('#form_route_agent').hide();
						  $('#form_route_phone').hide();
						  $('#form_route_callmenu').hide();
						  $('#form_route_voicemail').hide();
						  $('#form_route_exten').hide();
						}if(this.value == "PHONE") {
						  $('#form_route_phone').show();
						  
						  $('#form_route_agent').hide();
						  $('#form_route_ingroup').hide();
						  $('#form_route_callmenu').hide();
						  $('#form_route_voicemail').hide();
						  $('#form_route_exten').hide();
						}if(this.value == "CALLMENU") {
						  $('#form_route_callmenu').show();
						  
						  $('#form_route_agent').hide();
						  $('#form_route_ingroup').hide();
						  $('#form_route_phone').hide();
						  $('#form_route_voicemail').hide();
						  $('#form_route_exten').hide();
						}if(this.value == "VOICEMAIL") {
						  $('#form_route_voicemail').show();
						  
						  $('#form_route_agent').hide();
						  $('#form_route_ingroup').hide();
						  $('#form_route_phone').hide();
						  $('#form_route_callmenu').hide();
						  $('#form_route_exten').hide();
						}if(this.value == "EXTENSION") {
						  $('#form_route_exten').show();
						  
						  $('#form_route_agent').hide();
						  $('#form_route_ingroup').hide();
						  $('#form_route_phone').hide();
						  $('#form_route_voicemail').hide();
						  $('#form_route_callmenu').hide();
						}
						
					});
	});
</script>
		
		<?php print $ui->creamyFooter(); ?>
    </body>
</html>
