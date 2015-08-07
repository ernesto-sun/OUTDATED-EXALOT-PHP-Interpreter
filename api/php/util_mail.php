<?php

/**
 *  EXALOT digital language for all agents
 *
 *  util_file.php contains some mailing-helper-functions (UNUSED!!)
 * 
 *  @see <http://exalot.com>
 *  
 *  @author  Ernesto Sun <contact@ernesto-sun.com>
 *  @version 20150112-eto
 *  @since 20150112-eto
 * 
 *  @copyright (C) 2014-2015 Ing. Ernst Johann Peterec <http://ernesto-sun.com>
 *  @license AGPL <http://www.gnu.org/licenses/agpl.txt>
 *
 *  EXALOT is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  EXALOT is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with EXALOT. If not, see <http://www.gnu.org/licenses/agpl.txt>.
 *
 */


/**
 *
*/


if (!isset($GLOBALS["indexOk"]) || !$GLOBALS["indexOk"]) die();

$gnmail=0;
function initMail($account="db@ecovillage.org",
                $password="",
                $host="mail.ecovillage.org",
                $fromReply="GEN-IS (Information Service)")
{
	global $gnmail;
	require_once('3p/phpmailer/class.phpmailer.php');

	$gnmail= new PHPMailer();

	$gnmail->IsSMTP(); // telling the class to use SMTP
	$gnmail->SMTPDebug  = 1;                     // enables SMTP debug information (for testing)
																						// 1 = errors and messages
																						// 2 = messages only
	$gnmail->SMTPAuth   = true;                  // enable SMTP authentication
	$gnmail->SMTPSecure = "tls";

	$gnmail->Host       = $host; // sets the SMTP server
	$gnmail->Port       = 587;                    // set the SMTP port for the GMAIL server
	$gnmail->Username   = $account;         // SMTP account username
	$gnmail->Password   = $password;        // SMTP account password

	$gnmail->SetFrom($account, $fromReply);

	$gnmail->AddReplyTo($account,$fromReply);

	$gnmail->ContentType = 'text/plain'; 
	$gnmail->IsHTML(false);
}

//---------------------------------------------------------------
function sendMail($receiver,$subject,$content)
{
	global $gnmail;

	$gnmail->Subject = $subject;
        $gnmail->Body=$content;
	$gnmail->ClearAddresses();
	$gnmail->AddAddress($receiver);

	if(!$gnmail->Send()) 
	{
		echo "Mailer Error to ".$receiver.": " . $receiver->ErrorInfo;
	} 
	usleep(400000);
}


?>