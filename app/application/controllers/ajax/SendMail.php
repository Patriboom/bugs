<?php
	include_once "db.php";
	//Préférences de l'usager
	$dir = $prefixe.$config['attached']['directory'];
	$SkipUser = isset($SkipUser) ? $SkipUser : false;
	$Type = isset($_GET["Type"]) ? $_GET["Type"] : 'Issue';
	$Type = isset($Type) ? $Type : $Type;
	$UserID = 1;
//	$UserID = isset(Auth::user()->id) ? Auth::user()->id : $UserID;
	$UserID = isset($_GET["User"]) ? $_GET["User"] : $UserID;
	$UserID = isset($User) ? $User : $UserID;

	if ($Type == 'User') {
		$resu = Requis("SELECT * FROM users WHERE email = '".$UserID."'");
	} else {
		$resu = Requis("SELECT * FROM users WHERE id = ".$UserID);
	}
	$QuelUser = Fetche($resu);

	//Chargement des fichiers linguistiques
	$emailLng = require ($prefixe."app/application/language/en/tinyissue.php");
	$emailLnE = require ($prefixe."app/application/language/en/email.php");
	if ( file_exists($prefixe."app/application/language/".$QuelUser["language"]."/tinyissue.php") && $QuelUser["language"] != 'en') {
		$LnT = require ($prefixe."app/application/language/".$QuelUser["language"]."/tinyissue.php");
		$LnE = require ($prefixe."app/application/language/".$QuelUser["language"]."/email.php");
		$Lng['tinyissue'] = array_merge($emailLng, $LnT);
		$Lng['email'] = array_merge($emailLnE, $LnE);
	} else {
		$Lng['tinyissue'] = $emailLng;
		$Lng['email'] = $emailLnE;
	}
	$optMail = $config["mail"];
	$ProjectID = isset($ProjectID) ? $ProjectID : 0;
	$IssueID = isset($IssueID) ? $IssueID : 0;

	//Titre et corps du message selon les configurations choisies par l'administrateur
	$message  = (file_exists($dir."intro.html")) ? file_get_contents($dir."intro.html") : $config['mail']['intro']; 
	if (is_array(@$contenu)) {
		$subject = (file_exists($dir.$contenu[0].'_tit.html')) ? file_get_contents($dir.$contenu[0].'_tit.html') : $Lng[$src[0]]['following_email_'.$contenu[0].'_tit'];
		foreach ($contenu as $ind => $val) {
			if ($src[$ind] == 'value') {
				$message .= $val;
			} else {
				if (file_exists($dir.$val.'.html')) {
					$message .= file_get_contents($dir.$val.'.html');
				} else {
					$message .= $Lng[$src[$ind]]['following_email_'.$val];
				}
			}
		}
	} else if ($Type == 'TestonsSVP') {
		$message .= $Lng['tinyissue']["email_test"].$config['my_bugs_app']['name'].').';
	} else {
		$message = @$contenu;
	}
	$message .= (file_exists($dir."bye.html")) ? file_get_contents($dir."bye.html") : $config['mail']['bye']; 
	$subject = isset($subject) ? $subject : 'BUGS';

		//Select email addresses
	if ($Type == 'User') {
		$query  = "SELECT DISTINCT 0 AS project, 1 AS attached, 1 AS tages, USR.email, USR.firstname AS first, USR.lastname as last, CONCAT(USR.firstname, ' ', USR.lastname) AS user, USR.language, 'Welcome on BUGS' AS name, 'Welcome' AS title ";
		$query .= "FROM users AS USR WHERE ";
		$query .= (is_numeric($UserID)) ? "USR.id = ".$UserID : "USR.email = '".$UserID."' "; 
	} else if ($Type == 'TestonsSVP') {
		$query  = "SELECT DISTINCT 0 AS project, 1 AS attached, 1 AS tages, USR.email, USR.firstname AS first, USR.lastname as last, CONCAT(USR.firstname, ' ', USR.lastname) AS user, USR.language, 'Testing mail for any project' AS name, 'Test' AS title ";
		$query .= "FROM users AS USR WHERE USR.id = ".$UserID;
		$subject = $Lng['tinyissue']["email_test_tit"];
		echo $Lng['tinyissue']["email_test_tit"];
	} else {
		$query  = "SELECT DISTINCT FAL.project, FAL.attached, FAL.tags, USR.email, USR.firstname AS first, USR.lastname as last, CONCAT(USR.firstname, ' ', USR.lastname) AS user, USR.language, PRO.name, TIK.title ";
		$query .= "FROM following AS FAL ";
		$query .= "LEFT JOIN users AS USR ON USR.id = FAL.user_id "; 
		$query .= "LEFT JOIN projects AS PRO ON PRO.id = FAL.project_id ";
		$query .= "LEFT JOIN projects_issues AS TIK ON TIK.id = FAL.issue_id ";
		$query .= "WHERE FAL.project_id = ".$ProjectID." ";
		if ($Type == 'Issue') {
			$query .= "AND FAL.project = 0 AND issue_id = ".$IssueID." ";
			$query .= ($SkipUser) ? "AND FAL.user_id NOT IN (".$UserID.") " : "";
			$query .= "AND FAL.project = 0 ";
		} else if ($Type == 'Project') {
			$query .= "AND FAL.project = 1 ";
		}
	}
	$followers = Requis($query);

	if (Nombre($followers) > 0) {
		while ($follower = Fetche($followers)) {
			$subject = wildcards($subject, $follower,$ProjectID, $IssueID);
			$passage_ligne = (!preg_match("#^[a-z0-9._-]+@(hotmail|live|msn).[a-z]{2,4}$#", $follower["email"])) ? "\r\n" : "\n";
			$message = str_replace('"', "``", $message);
			$message = stripslashes($message);
			$message = str_replace("'", "`", $message);

			if ($optMail['transport'] == 'mail') {
				$boundary = md5(uniqid(microtime(), TRUE));
				$headers = 'From: "'.$optMail['from']['name'].'" <'.$optMail['from']['email'].'>'.$passage_ligne;
				$headers .= 'Reply-To: "'.$optMail['replyTo']['name'].'" <'.$optMail['replyTo']['email'].'>'.$passage_ligne;
				$headers .= 'Mime-Version: 1.0'.$passage_ligne;
				$headers .= 'Content-Type: multipart/mixed; charset="'.$optMail['encoding'].'"; boundary="'.$boundary.'"';
				$headers .= $passage_ligne;

				$body = strip_tags( nl2br(str_replace("</p>", "<br /><br />", $message)));
				$body .= $passage_ligne;
				$body .= $passage_ligne;
				$body .= '--'.$boundary.''.$passage_ligne;
				$body .= 'Content-Type: text/html; charset="'.$optMail['encoding'].'"'.$passage_ligne;
//				$body .= $passage_ligne;
//				$body .= '<p>'.$optMail['intro'].'</p>';
				$body .= $passage_ligne;
				$body .= '<p>'.$message.'</p>';
//				$body .= $passage_ligne;
//				$body .= '<p>'.$optMail['bye'].'</p>';
				$body .= $passage_ligne.'';
				$body = wildcards ($body, $follower,$ProjectID, $IssueID);
				mail($follower["email"], $subject, $body, $headers);
			} else {
				require_once $prefixe.'app/application/libraries/PHPmailer/class.phpmailer.php';
				switch ($optMail['transport']) {
					case 'PHP':
						require_once  $prefixe.'app/application/libraries/PHPmailer/class.phpmailer.php';
						break;
					case 'sendmail':
						require_once $prefixe.'app/application/libraries/PHPmailer/class.phpmaileroauth.php';
						break;
					case 'gmail':
						require_once $prefixe.'app/application/libraries/PHPmailer/class.phpmaileroauthgoogle.php';
						break;
					case 'POP3':
						require_once $prefixe.'app/application/libraries/PHPmailer/class.pop3.php';
						break;
					default:															//smtp is the second default value after "mail" which has its own code up
						require_once $prefixe.'app/application/libraries/PHPmailer/class.smtp.php';
						break;
				}
				$mail = new PHPMailer();
				$mail->Mailer = $optMail['transport'];
				$mail->SMTPDebug = $optMail['mailerrormsg'];				// 0 = no output, 1 = errors and messages, 2 = messages only.
				if (trim($optMail['smtp']['encryption']) != '') {
					$mail->SMTPSecure = $optMail['smtp']['encryption'];// sets the prefix to the server
				}
				$mail->SMTPAuth = true;											// enable SMTP authentication
				$mail->Host = $optMail['smtp']['server'];
				$mail->Port = $optMail['smtp']['port'];
				$mail->Username = $optMail['smtp']['username'];
				$mail->Password = $optMail['smtp']['password'];

				$mail->CharSet = isset($optMail['encoding']) ? $optMail['encoding'] : 'UTF-8';
				$mail->SetFrom ($optMail['from']['email'], $optMail['from']['name']);
				$mail->Subject = $subject;
				$mail->ContentType = isset($optMail['plainHTML']) ? $optMail['plainHTML'] : 'text/plain';
				$body  = $optMail['intro'];
				$body .= '<br /><br />';
				$body .= $message;
				$body .= '<br /><br />';
				$body .= $optMail['bye'];
				$body = wildcards ($body, $follower,$ProjectID, $IssueID);
				if ($mail->ContentType == 'html') {
					$mail->IsHTML(true);
					$mail->WordWrap = (isset($optMail['linelenght'])) ? $optMail['linelenght'] : 80;
					$mail->Body = $body;
					$mail->AltBody = strip_tags($body);
				} else {
					$mail->IsHTML(false);
					$mail->Body = strip_tags($body);
				}
				$mail->AddAddress ($follower["email"]);
				//$result = $mail->Send() ? "Successfully sent!" : "Mailer Error: " . $mail->ErrorInfo;
				echo ($mail->Send()) ? "Successfully sent!" : "Mailer Error: " . $mail->ErrorInfo;
			}
		}
	}
	
function wildcards ($body, $follower,$ProjectID, $IssueID) {
	global $prefixe;
	$link = (isset($_SERVER['HTTPS']) && @$_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$link = substr($link, 0, strrpos($link, "/"));
	$body = str_replace('src="uploads/', 'src="'.$prefixe.'uploads/', $body);
	$body = str_replace('%60', "", $body);
	$body = str_replace('``', '"', $body);
	$body = str_replace('http:/', 'http://', $body);
	$body = str_replace('https:/', 'https://', $body);
	$body = str_replace('http:////', 'http://', $body);
	$body = str_replace('https:////', 'https://', $body);
	$body = str_replace('http:///', 'http://', $body);
	$body = str_replace('https:///', 'https://', $body);
	if ( $_SERVER['SERVER_NAME'] != "127.0.0.1"  && $_SERVER['SERVER_NAME'] != "localhost"  ) {
		$body = str_replace('src="', 'src="'.((isset($_SERVER['HTTPS']) && @$_SERVER['HTTPS'] === 'on') ? 's' : '').'://'.$_SERVER['SERVER_NAME'].'/', $body);
	}
	$body = str_replace('{first}', ucwords($follower["first"]), $body);
	$body = str_replace('{last}', ucwords($follower["last"]), $body);
	$body = str_replace('{full}', ucwords($follower["user"]), $body);
	$body = str_replace('{First}', ucwords($follower["first"]), $body);
	$body = str_replace('{Last}', ucwords($follower["last"]), $body);
	$body = str_replace('{Full}', ucwords($follower["user"]), $body);
	$body = str_replace('{projet}', '<a href="'.(str_replace("issue/new", "issues?tag_id=1", $link)).'">'.$follower["name"].'</a>', $body);
	$body = str_replace('{project}', '<a href="'.(str_replace("issue/new", "issues?tag_id=1", $link)).'">'.$follower["name"].'</a>', $body);
	$body = str_replace('{projets}', '<a href="'.(str_replace("issue/new", "issues?tag_id=1", $link)).'">'.$follower["name"].'</a>', $body);
	$body = str_replace('{projects}', '<a href="'.(str_replace("issue/new", "issues?tag_id=1", $link)).'">'.$follower["name"].'</a>', $body);
	$body = str_replace('{Projet}', '<a href="'.(str_replace("issue/new", "issues?tag_id=1", $link)).'">'.$follower["name"].'</a>', $body);
	$body = str_replace('{Project}', '<a href="'.(str_replace("issue/new", "issues?tag_id=1", $link)).'">'.$follower["name"].'</a>', $body);
	$body = str_replace('{Projets}', '<a href="'.(str_replace("issue/new", "issues?tag_id=1", $link)).'">'.$follower["name"].'</a>', $body);
	$body = str_replace('{Projects}', '<a href="'.(str_replace("issue/new", "issues?tag_id=1", $link)).'">'.$follower["name"].'</a>', $body);
	$body = str_replace('{issue}', '<a href="'.(str_replace("issue/new", "issue/".$IssueID, $link)).'">'.$follower["title"].'</a>', $body);
	return $body;
}
?>
