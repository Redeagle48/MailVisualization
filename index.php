<?php
    
	include 'parse.php';
	global $days_array;	
	global $months_array;
	global $years_array;
        global $username;
        
            
	set_time_limit(999999999);
            
	header("Content-Type: text/html; charset=ISO-8859-1",true);
            
    function gmail_login_page()
    {
?>
<html>
    <head><title>Gmail login</title>
        <style type="text/css">body { font-family: arial, sans-serif; margin: 40px; font-size: 100%}</style>
    </head>
    <body>
        <p font-family="gill sans";>Upload the file with your locations here, if you have one.</p>
        <div>
            <form action="index.php" method="POST" enctype="multipart/form-data">
                <label for="file">Choose a file to upload:</label> <input type="file" name="uploaded_file"/>
                <input type="submit" value="Upload">
				<input type="hidden" value="'.$user.'" name="user">
				<input type="hidden" value="'.$password.'" name="password">
            </form>
        </div> 
        <hr/><br>
        <p font-family="gill sans";>Login with your <strong>Gmail credentials</strong> to see a few stats about your emails. Your password won't be stored.</p><br>
       
        <div>
            <form action="index.php" method="POST">
                <p>Your email:  <input type="text" name="user"></p>
                <p>Your password:  <input type="password" name="password"></p>
                <input type="submit" value="Login">
            </form>
        </div>
        <hr><br>
    </body>
</html>
    
<?php
    }
        
    function gmail_summary_page($user, $password)
    {
echo '
    
<html>
    <head><title>Gmail info for '.$user.'</title>
    <style type="text/css">body { font-family: arial, sans-serif; margin: 40px;}</style>
    </head>
<body>
     <!-- <p font-family="gill sans";>Upload the file with your locations here.</p>
        <div>
            <form action="index.php" method="POST" enctype="multipart/form-data">
                <label for="file">Choose a file to upload:</label> <input type="file" name="uploaded_file"/>
                <input type="submit" value="Upload">
				<input type="hidden" value="'.$user.'" name="user">
				<input type="hidden" value="'.$password.'" name="password">
            </form>
        </div> -->
            
            
';
    
	if(isset($_FILES['uploaded_file']['tmp_name'])) {
		$upload_dir = '.';
		$tmp_name = $_FILES["uploaded_file"]["tmp_name"];
		$name = "locs";
		if(move_uploaded_file($tmp_name, "$upload_dir/$name") != false) {
			echo "The file was successfully uploaded!<br>";
		}
	}
            
	$hostname = '{imap.gmail.com:993/imap/ssl}';
            
            
    display_mail_summary($hostname, $user, $password);
?>
    
</body>
</html>
    
<?php
    }
        
	// compares mail, hi e hf hours
	function compare_hours($hour1, $hour2, $hour3){ 
		return compare($hour1, $hour2) && !compare($hour1, $hour3);
	}
            
	function compare($hour1, $hour2){ 
		//hours
		if(($hour1[0] - $hour2[0]) >= 0) { 
			return true;
		} 
		elseif(($hour1[0] - $hour2[0]) < 0) {
			return false;
		} //min 
		else {
			return ($hour1[1] - $hour2[1]) >= 0;
		}
	}
            
    function display_mail_summary($hostname, $imapuser, $imappassword) {
	
	global $days_array;	
	global $months_array;
	global $years_array;
        global $username;
            
        // variables declaration (and attribution)
        $email_id = 1;
        $field_sep = "_*F*_";
        $header_sep = "_*H*_";
            
        
        ///////////////////////////////////////////////////////
        //Ver os meus mails para descontar no all mails
        ///////////////////////////////////////////////////////
        
       /* $connSent = imap_open ($hostname, $imapuser, $imappassword)
            or die("Can't connect as user '" . $imapuser . 
            "': " . imap_last_error()); */
        $connSent = imap_open ($hostname, $imapuser, $imappassword)
            or die("");
                
            $boxes = imap_list($connSent, $hostname, '*');
            
            //var_dump($boxes);
            
            $iter = 0;
            $hostSent = "";
            while($iter <= (count($boxes)-1)) {
                    if(strpos($boxes[$iter], "Correio enviado") !== false) {
                            $hostSent = $boxes[$iter];
                    }
                    else if(strpos($boxes[$iter], "Sent Messages") !== false) {
                            $hostSent = $boxes[$iter];
                    }
                    $iter++;
            }
                    
	imap_close($connSent);
        
        $connectionSent = imap_open ($hostSent, $imapuser, $imappassword)
            or die("Can't connect as user '" . $imapuser . 
            "': " . imap_last_error());
        
        $num_emailsSent = imap_num_msg($connectionSent);
        //print_r($num_emailsSent);
        
        $myEmailsAddresses = ""; //os meus emails addresses
        
        while($email_id <= $num_emailsSent) {
            $header = imap_headerinfo($connectionSent, $email_id);
            if(strpos($myEmailsAddresses,$header->fromaddress) !== false) {
                //Nao precisa de fazer nada
            } else {
               $myEmailsAddresses .= $header->fromaddress;
               $myEmailsAddresses .= "_*H*_";
            }
            $email_id++;
        }
        //print_r($myEmailsAddresses);
        $email_id = 1;
        
//        echo '</br>';
//        var_dump($myEmailsAddresses);
        
//        $file_handle1 = fopen("./" . "meusMails", "w+") or exit("Unable to open file!");
//            fwrite($file_handle1, iconv_mime_decode($myEmailsAddresses, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, "ISO-8859-1")); //UTF-8
//            //fwrite($file_handle, $result);
//            fclose($file_handle1);
        
        imap_close($connectionSent);
        
        
        ///////////////////////////////////////////////////////
        //Acabou de ver os meus mails para descontar no all mails
        ///////////////////////////////////////////////////////
        
        
            
        $conn = imap_open ($hostname, $imapuser, $imappassword)
            or die("Can't connect as user '" . $imapuser . 
            "': " . imap_last_error());
                
		$boxes = imap_list($conn, $hostname, '*');
                    
		$iter = 0;
		$host = "";
		while($iter <= (count($boxes)-1)) {
			if(strpos($boxes[$iter], "Todo") !== false) {
				$host = $boxes[$iter];
			}
			else if(strpos($boxes[$iter], "All") !== false) {
				$host = $boxes[$iter];
			}
			$iter++;
		}
                    
	imap_close($conn);
                    
	$connection = imap_open ($host, $imapuser, $imappassword)
            or die("Can't connect as user '" . $imapuser . 
            "': " . imap_last_error());
		
		// page
		//echo "<h1>Gmail information for <u>" . $imapuser ."</h1></u>";
                    
        $num_emails = imap_num_msg($connection); //echo "I have <b>" . $num_emails . "</b> email(s).";
            
        // file
        $username = explode("@", $imapuser);
        if(!file_exists("./" . $username[0])) {
            $mySentEmails = "";
            $result = "";//$header_sep;
            while($email_id <= $num_emails) {
                $header = imap_headerinfo($connection, $email_id);
                if($header->fromaddress !== ''){
                    if(strpos($myEmailsAddresses,$header->fromaddress) == false) {
                        if(isset($header->fromaddress)) {
                                                $result .= $header->fromaddress; //0
                                        }
                                        else {
                                                $result .= "[Undefined]";
                                        }
                        $result .= $field_sep . $header->udate; //1
                                      /*  if(isset($header->subject)) {
                                                $result .= $field_sep . $header->subject; //2
                                        }
                                        else {
                                                $result .= $field_sep . "[No Subject]";
                                        } */
                                        $result .= $field_sep . $header->Answered; //3
                                        
                                        
                                        $result .= $field_sep . $header->toaddress; //4
                        $result .= $header_sep;
                        $email_id++;
                    } else { //my sent emails
                        $mySentEmails .= $header->fromaddress; //0
                        $mySentEmails .= $field_sep . $header->udate; //1
                                      /*  if(isset($header->subject)) {
                                                $mySentEmails .= $field_sep . $header->subject; //2
                                        }
                                        else {
                                                $mySentEmails .= $field_sep . "[No Subject]";
                                        } */
                                        $mySentEmails .= $field_sep . $header->Answered; //3
                                        if(isset($header->toaddress)) {
                                                $mySentEmails .= $field_sep . $header->toaddress; //4
                                        }
                                        else {
                                                $mySentEmails .= $field_sep . "[Undefined]";
                                        }
                        $mySentEmails .= $header_sep;
                        $email_id++;
                    }
                }
            }
                
            $file_handle = fopen("./" . $username[0], "w+") or exit("Unable to open file!");
            fwrite($file_handle, iconv_mime_decode($result, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, "UTF-8")); //ISO-8859-1
            //fwrite($file_handle, $result);
            fclose($file_handle);
            
            $file_handleSent = fopen("./" . 'sent-' . $username[0], "w+") or exit("Unable to open file!");
            fwrite($file_handleSent, iconv_mime_decode($mySentEmails, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, "UTF-8")); //UTF-8
            //fwrite($file_handle, $result);
            fclose($file_handleSent);
            
           
            
        }
            
        include 'toJson.php';
        // close connection
        imap_close($connection);
       
    }
        
    $user = @$_POST["user"];
    $password = @$_POST["password"];
        
    if (!$user or !$password)
        gmail_login_page();
    else
        gmail_summary_page($user, $password);
            
?>