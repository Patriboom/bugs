<?php 
	$prefixe = "";
	while (!file_exists($prefixe."config.app.php")) {
		$prefixe .= "../";
	}
	$Lng = require_once($prefixe."app/application/language/en/install.php"); 
	if ( file_exists($prefixe."app/application/language/".\Auth::user()->language."/install.php") && \Auth::user()->language != 'en') {
		$LnT = require_once ($prefixe."app/application/language/".\Auth::user()->language."/install.php");
		$LngSRV = array_merge($Lng, $LnT);
	} else {
		$LngSRV = $Lng;
	}

	echo '<h3>';
	echo __('tinyissue.administration');
	echo '.<span>'.__('tinyissue.administration_description').'</span>';
	echo '</h3>';
?>

<div class="pad">
	<div class="pad2">
		<table class="table" width="60%">
			<tr>
				<th style="border-top: 1px solid #ddd;"><a href="administration/users"><?php echo __('tinyissue.total_users'); ?></a></th>
				<td style="border-top: 1px solid #ddd;"><b><?php echo $users; ?></b></td>
			</tr>
			<tr>
				<th><a href="<?php echo URL::to('roles'); ?>"><?php echo __('tinyissue.role'); ?>s</a></th>
				<td><b><?php echo @$roles; ?></b></td>
			</tr>
			<tr>
				<th><a href="projects"><?php echo __('tinyissue.projects'); ?></a>
				<div class="adminListe">
					<?php echo ($active_projects < 2) ? __('tinyissue.active_project') : __('tinyissue.active_projects'); ?><br />
					<?php echo ($archived_projects < 2) ? __('tinyissue.archived_project') : __('tinyissue.archived_projects'); ?><br />
				</div>
				</th>
				<td>
					<b><?php echo ($active_projects + $archived_projects); ?></b><br />
					<?php echo ($active_projects == 0) ? __('tinyissue.no_one') : $active_projects; ?><br />
					<?php echo ($archived_projects == 0) ? __('tinyissue.no_one') : $archived_projects; ?><br />
				</td>
			</tr>
			<tr>
			</tr>

			<tr>
				<th><a href="<?php echo URL::to('tags'); ?>"><?php echo __('tinyissue.tags'); ?></a></th>
				<td><b><?php echo $tags; ?></b></td>
			</tr>
			<tr>
				<th><a href="user/issues"><?php echo __('tinyissue.issues'); ?></a>
					<div class="adminListe">
						<?php echo __('tinyissue.open_issues'); ?><br />
						<?php echo __('tinyissue.closed_issues'); ?><br />
					</div>
				</th>
				<td><b><?php echo ($issues['open']+$issues['closed']); ?></b><br />
				<?php echo $issues['open']; ?><br />
				<?php echo $issues['closed']; ?><br />
				</td>
			</tr>
			<tr>
				<th><a href="https://github.com/pixeline/bugs/" target="_blank"><?php echo __('tinyissue.version'); ?></a>
					<div class="adminListe">
					<?php echo __('tinyissue.version'); ?><br />
					<?php echo __('tinyissue.version_release_numb'); ?><br />
					<?php echo __('tinyissue.version_release_date'); ?><br />
					</div>
				</th>
				<td>
					<b><?php echo Config::get('tinyissue.version').Config::get('tinyissue.release'); ?></b><br />
					<?php echo Config::get('tinyissue.version'); ?><br />
					<?php echo Config::get('tinyissue.release'); ?><br />
					<?php echo $release_date = Config::get('tinyissue.release_date'); ?><br />
				</td>
			</tr>
		</table>
	</div>
	<div class="pad2">
		<br />
		<?php
			include "application/libraries/checkVersion.php";
			echo '<h4><b>'.__('tinyissue.version_check').'</b> : ';
			echo '<br /><br />';
			echo __('tinyissue.version_actuelle');
			echo ' : '.$verActu.'<br />'.__('tinyissue.version_release_numb').' : '.Config::get('tinyissue.release');
			echo '<br /><br />';
			if ($verActu == $verNum) {
				echo '<a name="Apprécions">'.__('tinyissue.version_good').'!</a>';
				echo '<br /></h4>';
			} else if ($verNum == 0) {
				echo __('tinyissue.version_offline');
				echo '<br /></h4>';
				echo '<a href="https://github.com/pixeline/bugs/" target="_blank">https://github.com/pixeline/bugs/</a>';
			} else if ($verNum < $verActu) {
				echo '<h4><b>'.__('tinyissue.version_ahead').'</b></h4>';
				echo __('tinyissue.version_disp').' : '.$verNum.'<br />';
				echo __('tinyissue.version_commit').' : '.$verCommit.'<br />';
				echo '<br />';
				echo '<a href="https://github.com/pixeline/bugs/releases" target="_blank">'.__('tinyissue.version_details').'</a> <br />';
			} else {
				echo '<h4><a href="javascript: agissons.submit();">'.__('tinyissue.version_need').'.</a></h4>';
				echo __('tinyissue.release_disp').' : '.$verNum.'<br />';
				echo __('tinyissue.version_commit').' : '.$verCommit.'<br /><br />';
				echo __('tinyissue.version_disp').' : '.$verCod.'<br />';
				echo '<a href="https://github.com/pixeline/bugs/releases" target="_blank">'.__('tinyissue.version_details').'</a> <br />';
				echo '<form action="'.URL::to('administration/update').'" method="post" id="agissons">';
				echo '<input type="hidden" name="Etape" value="1" />';
				echo '<input type="hidden" name="versionYour" value="'.$verActu.'" />';
				echo '<input type="hidden" name="versionDisp" value="'.$verNum.'" />';
				echo '<input type="hidden" name="versionComm" value="'.$verCommit.'" />';
				echo '<br /><br />';
				echo '<input type="submit" value="'.__('tinyissue.updating').'" class="button	primary"/>';
				echo Form::token();
				echo '</form>';
			}
		?>
	</div>
</div>
	<br />
	<div class="pad" style="border-top-style: solid; border-bottom-style: solid; border-color: grey; border-width: 2px;">
		<?php $Conf = Config::get('application.mail'); ?>
		<details id="details_email_head" open="open">
			<summary><?php echo __('tinyissue.email_head'); ?></summary>
			<br />
			<div class="pad2">
				<?php echo __('tinyissue.email_from').' : '.__('tinyissue.email_from_name'); ?> :<input name="email_from_name" id="input_email_from_name" value="<?php echo $Conf["from"]["name"]; ?>" onkeyup="this.style.backgroundColor = 'yellow';" /><br />
				<?php echo __('tinyissue.email_from').' : '.__('tinyissue.email_from_email'); ?> : <input name="email_from_email" id="input_email_from_email" value="<?php echo $Conf["from"]["email"]; ?>" onkeyup="this.style.backgroundColor = 'yellow';" /><br /><br />
				<?php echo __('tinyissue.email_intro'); ?> : <input name="email_from" id="input_email_intro" value="<?php echo $Conf["intro"]; ?>" onkeyup="this.style.backgroundColor = 'yellow';" /><br /><br />
				<?php echo __('tinyissue.email_bye'); ?> : <input name="email_from" id="input_email_bye" value="<?php echo $Conf["bye"]; ?>" onkeyup="this.style.backgroundColor = 'yellow';" /><br /><br />
				<div style="text-align: center;"><input type="button" value="Test" onclick="javascript: AppliquerTest(<?php echo Auth::user()->id; ?>);" class="button1"/></div>
				<br />
			</div>
			<div class="pad2">
				<?php echo __('tinyissue.email_replyto').' : '.__('tinyissue.email_from_name'); ?> : <input name="input_email_replyto_name" id="input_email_replyto_name" value="<?php echo $Conf["replyTo"]["name"]; ?>" onkeyup="this.style.backgroundColor = 'yellow';" /><br />
				<?php echo __('tinyissue.email_replyto').' : '.__('tinyissue.email_from_email'); ?> : <input name="input_email_replyto_email" id="input_email_replyto_email" value="<?php echo $Conf["replyTo"]["email"]; ?>" onkeyup="this.style.backgroundColor = 'yellow';" /><br /><br />
				<br />
				<?php echo __('tinyissue.first_name'); ?> : <b>{first}</b> ex.: <?php echo Auth::user()->firstname; ?><br /><br />
				<?php echo __('tinyissue.last_name'); ?> : <b>{last}</b> ex.: <?php echo Auth::user()->lastname; ?><br /><br />
				<?php echo __('tinyissue.name').' ( '.__('tinyissue.first_name'). ' '.__('tinyissue.last_name').' ) '; ?> : <b>{full}</b> ex.: <?php echo Auth::user()->firstname; ?>  <?php echo Auth::user()->lastname; ?><br />
				<br />
				<div style="text-align: center;"><input type="button" value="<?php echo __('tinyissue.updating'); ?>" onclick="javascript: AppliquerCourriel();" class="button2"/></div>
			</div>
		</details>
		<details id="details_email_head2">
			<summary><?php echo __('tinyissue.email_head2'); ?></summary>
			<br />
			<div class="pad2">
			<select name="ChxTxt" id="select_ChxTxt" onchange="ChangeonsText(this.value, <?php echo "'".\Auth::user()->language."','".__('tinyissue.following_email')."'"; ?>);" class="sombre">
			<?php
				$LesOptions = array(
					"assigned" 	=> __('tinyissue.following_email_assigned_tit'),
					"attached" 	=> __('tinyissue.following_email_attached_tit'),
					"comment" 	=> __('tinyissue.following_email_comment_tit'),
					"issue" 		=> __('tinyissue.following_email_issue_tit'),
					"issueproject" => __('tinyissue.following_email_issueproject_tit'),
					"project" 	=> __('tinyissue.following_email_project_tit'),
					"projectdel"=> __('tinyissue.following_email_projectdel_tit'),
					"projectmod"=> __('tinyissue.following_email_projectmod_tit'),
					"status" 	=> __('tinyissue.following_email_status_tit'),
					"tagsADD" 	=> __('tinyissue.following_email_tagsADD_tit'),
					"tagsOTE" 	=> __('tinyissue.following_email_tagsOTE_tit')
				);
				asort($LesOptions, SORT_LOCALE_STRING );
				foreach ($LesOptions as $ind => $val) {
					echo '<option value="'.$ind.'" '.(($ind == 'comment') ? ' selected="selected"' : '').'>'.$val.'</option>';
				}
			?>
			</select>
			&nbsp;&nbsp;&nbsp;<?php echo __('tinyissue.title'); ?> : <input name="TitreMsg" id="input_TitreMsg" value="<?php
				if (file_exists("../uploads/attached_tit.html")) {
					$f = file_get_contents("../uploads/attached_tit.html");
					echo $f;
				} else {
					echo  __('tinyissue.tinyissue.following_email_attached_tit');
				}
			?>" size="50" />
			</div>
			<div class="pad2"style="font-size: 125%; padding-top: 15px;">
				{first}, {last}, {full}, {project}, {issue}
			</div>
			<br />
			<textarea id="txt_contenu" name="contenu" >
			<?php
				if (file_exists("../uploads/attached.html")) {
					$f = file_get_contents("../uploads/attached.html");
					echo $f;
				} else {
					echo  __('tinyissue.tinyissue.following_email_attached');
				}
			?>
			</textarea>
			<input name="Modifies" type="hidden" id="input_modifies" value="0" />
			<br />
			<div style="text-align: center;"><input type="button" value="<?php echo __('tinyissue.updating'); ?>" onclick="javascript: ChangeonsText(document.getElementById('select_ChxTxt').value, '<?php echo \Auth::user()->language; ?>', 'OUI');" class="button2"/></div>
		</details>
		<details id="details_emailserver_head" open>
			<summary><?php echo $LngSRV['UpdateConfigFile']; ?></summary>
			<br />
			<div class="pad2">
				<?php echo $LngSRV["Email_transport"]; ?> : <select name="Email_transport" id="select_Email_transport">
					<option value="smtp" <?php echo ($Conf['transport'] == 'smtp') ? 'selected="selected"' : ''; ?>>smtp</option>
					<option value="mail" <?php echo ($Conf['transport'] != 'smtp') ? 'selected="selected"' : ''; ?>>mail</option>
					</select> <br />
				<?php echo $LngSRV["Email_sendmail_path"]; ?> : <input name="email_sendmail_path" id="input_email_sendmail_path" value="<?php echo $Conf["sendmail"]["path"]; ?>" onkeyup="this.style.backgroundColor = 'yellow';" /><br /><br />
				<?php echo $LngSRV["Email_plainHTML"]; ?> : <select name="Email_plainHTML" id="select_Email_plainHTML">
					<option value="text/plain" <?php echo ($Conf['plainHTML'] == 'text/plain') ? 'selected="selected"' : ''; ?>>text/plain</option>
					<option value="html" <?php echo ($Conf['plainHTML'] == 'html') ? 'selected="selected"' : ''; ?>>html</option>
					<option value="multipart/mixed" <?php echo ($Conf['plainHTML'] == 'multipart/mixed') ? 'selected="selected"' : ''; ?>>multipart/mixed</option>
					</select> <br />
				<?php echo $LngSRV["Email_encoding"]; ?> : <input name="email_encoding" id="input_email_encoding" value="<?php echo $Conf["encoding"]; ?>" onkeyup="this.style.backgroundColor = 'yellow';" /><br /><br />
				<?php echo $LngSRV["Email_linelenght"]; ?> : <input name="email_linelenght" id="input_email_linelenght" type="number" max="1000" min="25" size="6" value="<?php echo $Conf["linelenght"]; ?>" onkeyup="this.style.backgroundColor = 'yellow';" /><br /><br />
				<br />
			</div>
			<div class="pad2">
				<?php echo $LngSRV["Email_server"]; ?> : <input name="email_server" id="input_email_server" value="<?php echo $Conf["smtp"]["server"]; ?>" onkeyup="this.style.backgroundColor = 'yellow';" /><br />
				<?php echo $LngSRV["Email_port"]; ?> : <input name="email_port" id="input_email_port" value="<?php echo $Conf["smtp"]["port"]; ?>" onkeyup="this.style.backgroundColor = 'yellow';" /><br />
				<?php echo $LngSRV["Email_encryption"]; ?> : <input name="email_encryption" id="input_email_encryption" value="<?php echo $Conf["smtp"]["encryption"]; ?>" onkeyup="this.style.backgroundColor = 'yellow';" /><br />
				<?php echo $LngSRV["Email_username"]; ?> : <input name="email_username" id="input_email_username" value="<?php echo $Conf["smtp"]["username"]; ?>" onkeyup="this.style.backgroundColor = 'yellow';" /><br />
				<?php echo $LngSRV["Email_password"]; ?> : <input name="email_password" id="input_email_password" value="<?php echo $Conf["smtp"]["password"]; ?>" onkeyup="this.style.backgroundColor = 'yellow';" /><br />
				<br />
				<input type="button" value="<?php echo __('tinyissue.updating'); ?>" onclick="javascript: AppliquerCourriel();" class="button2"/>
			</div>
		</details>
	<br />
	</div>

<script type="text/javascript" src="app/assets/js/admin.js" async ></script>
<script type="text/javascript" >
<?php
	$wysiwyg = Config::get('application.editor');
	if (trim(@$wysiwyg['directory']) != '') {
		if (file_exists($wysiwyg['directory']."/Bugs_code/showeditor.js")) {
			include_once $wysiwyg['directory']."/Bugs_code/showeditor.js";
			if ($wysiwyg['name'] == 'ckeditor') {
				echo "
				setTimeout(function() {
					showckeditor ('contenu', 9);
				} , 567);
				";
			}
		}
	}
?>
</script>
