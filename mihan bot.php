<?php
$admins = array( "132895158","49428560","141168657");
$hostname_excal = "localhost";
						$database_excal = "taximiha_mihan";
						$username_excal = "taximiha_mohamad";
						$password_excal = "D*+,)mO4fM%w";
						
header('Content-Type: text/html; charset=utf-8');
$message= file_get_contents("php://input");
$arrayMessage= json_decode($message, true);
$token= "383863488:AAHM_qTyobcl8R-ZyePHzYlx-VUC2sybXRg";
$chat_id= $arrayMessage['message']['from']['id'];
$command= $arrayMessage['message']['text'];
        
if(isset($_POST['codee'])){
    $excal = mysqli_connect($hostname_excal, $username_excal, $password_excal,$database_excal);
    $query ="update cars set telegram_id=''  WHERE numberr =".$_POST['codee'];
    $rsPackages = mysqli_query( $excal,$query);
}
else if($command == ''&&isset($_POST['querys'])){
    $excal = mysqli_connect($hostname_excal, $username_excal, $password_excal,$database_excal);
    $excal->set_charset("utf8");
    
    //save telegram_id
    $query2 = "SELECT telegram_id,numberr,dateemodifie FROM cars where telegram_id<>''";
    $rsPackages2 = mysqli_query( $excal,$query2) ;
    $listOfTelegramId;
    $listOfDateModifie;
    while ($row_rsPackages = mysqli_fetch_array($rsPackages2)) 
    {
        $listOfTelegramId[$row_rsPackages["numberr"]]=$row_rsPackages["telegram_id"];
        $listOfDateModifie[$row_rsPackages["numberr"]]=$row_rsPackages["dateemodifie"];
    }
    
							
    
    $query = explode("#", $_POST['querys']);
    $rsPackages = mysqli_query( $excal,"delete from holiday");
    $rsPackages = mysqli_query( $excal,"delete from cars");
    $rsPackages = mysqli_query( $excal,"delete from days");
    $rsPackages = mysqli_query( $excal,"delete from groups");
    $rsPackages = mysqli_query( $excal,"delete from shifts");
    for ($i=1;$i<count($query);$i++) 
    {
        $qq=str_replace("[","",$query[$i]);
        $qq=str_replace("]","",$qq);
        $rsPackages = mysqli_query( $excal,$qq);
    }
    
    //load telegram id
    foreach($listOfTelegramId as $x=>$x_value)
    {
        $query ="update cars set telegram_id='".$x_value."' , dateemodifie='".$listOfDateModifie[$x]."'  WHERE numberr =".$x;
        $rsPackages = mysqli_query( $excal,$query);
        echo "Key=" . $x . ", Value=" . $x_value;
    }
    echo "complete";
}
else if($command == ''&&isset($_POST['publicmessage'])){
    $excal = mysqli_connect($hostname_excal, $username_excal, $password_excal,$database_excal);
    $query = "select * from cars where telegram_id<>'' order by numberr";
    $rsPackages = mysqli_query( $excal,$query);
    while ($reader=mysqli_fetch_assoc($rsPackages))
    {
        sendMessage(urlencode($_POST['publicmessage']),$reader['telegram_id']);
    }
}
else if($command == ''&&isset($_POST['specialmessage'])){
    $messages = explode("#", $_POST['specialmessage']);
    $excal = mysqli_connect($hostname_excal, $username_excal, $password_excal,$database_excal);
    $query = "select telegram_id from cars where numberr='".$messages[0]."'";
    $rsPackages = mysqli_query( $excal,$query);
    $reader=mysqli_fetch_assoc($rsPackages);
    sendMessage(urlencode($messages[1]),$reader['telegram_id']);
}
else if($command == '/start'){
    $text= "سلام، به ربات ما خوش آمدید".urlencode("\n")."حال میتوانید با وارد کردن کد تاکسی خود".urlencode("\n")." برنامه کاری 30 روز اینده خود را دریافت کنید";
    sendMessage($text,$chat_id);
}
else if($command == '/help'){
    $text= " میتوانید با وارد کردن کد تاکسی خود".urlencode("\n")." برنامه کاری 30 روز اینده خود را دریافت کنید";
    sendMessage($text,$chat_id);
}
else if($command == '/mihaninmap'){
    $url= "https://api.telegram.org/bot".$token."/sendLocation?chat_id=".$chat_id."&latitude=31.910688&longitude=54.338028"."&caption=آدرس تاکسی بی سیم میهن";
    file_get_contents($url);
}
else if($command == '/signupcondition'){
    $url= "https://api.telegram.org/bot".$token."/sendDocument?chat_id=".$chat_id."&caption=شرایط ثبت نام به عنوان راننده در تاکسی میهن";
    $post = array(
        'document'     => new CURLFile(realpath("signupcondition.pdf"))
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_exec($ch);
}
else if($command == '/loancondition'){
    $url= "https://api.telegram.org/bot".$token."/sendDocument?chat_id=".$chat_id."&caption=شرایط دریافت وام";
    $post = array(
        'document'     => new CURLFile(realpath("loancondition.pdf"))
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_exec($ch);
}
else if($command == '/today'){
    switch(date("w"))
    {
        case 6:$text="شنبه";break;
        case 0:$text="یک شنبه";break;
        case 1:$text="دو شنبه";break;
        case 2:$text="سه شنبه";break;
        case 3:$text="چهار شنبه";break;
        case 4:$text="پنج شنبه";break;
        case 5:$text="جمعه";break;
    }
    $text.="     ".gregorian_to_jalali(getdate()['year'],getdate()['mon'],getdate()['mday'],' / ');
    sendMessage($text,$chat_id);
}
else if($command == '/mynext30daysshifts'){
    $text= "";
    $excal = mysqli_connect($hostname_excal, $username_excal, $password_excal,$database_excal);
    $query = "select numberr,groupp from cars WHERE telegram_id ='".$chat_id."' LIMIT 1" ;
    $rsPackages = mysqli_query( $excal,$query);
    $recordinfo=mysqli_fetch_assoc($rsPackages);
    $carGroup=$recordinfo['groupp'];
    if($carGroup=='')
        $text="لطفا اول کد تاکسی خود را به زبان انگلیسی وارد کنید";
    else
    {   
        $number=$recordinfo['numberr']; 
		$now= gregorian_to_jalali(getdate()['year'],getdate()['mon'],getdate()['mday']);  
        //update dateemodifie
        $query ="update cars set dateemodifie='".$now[1]."/".$now[2]."  ".getdate()['hours'].":".getdate()['minutes']."'  WHERE numberr =".$number;
        $rsPackages = mysqli_query( $excal,$query);
            
            
           
        $shifts="";
        $query ="select * from shifts ";
        $rsPackages = mysqli_query( $excal,$query);
        $morningnormal = ""; $eveningnormal = ""; $morningholiday = ""; $eveningholiday = ""; $nightholiday = ""; $nighttime="";$morninglady="";$eveninglady="";
        while ($reader=mysqli_fetch_assoc($rsPackages))
        {
            switch ($reader["namee"])
            {
                case "morninglady": $morninglady = urlencode("\t\t\t")."از ساعت".urlencode("\t").$reader["start"] . "  تا ساعت " . $reader["end"]; break;
                case "eveninglady": $eveninglady = urlencode("\t\t\t")."از ساعت".urlencode("\t").$reader["start"] . "  تا ساعت " . $reader["end"]; break;
                case "morningholiday": $morningholiday = urlencode("\t\t\t")."از ساعت".urlencode("\t").$reader["start"] . "  تا ساعت " . $reader["end"]; break;
                case "eveningholiday": $eveningholiday = urlencode("\t\t\t")."از ساعت".urlencode("\t").$reader["start"] . "  تا ساعت " . $reader["end"]; break;
                case "nightholiday": $nightholiday = urlencode("\t\t\t")."از ساعت".urlencode("\t").$reader["start"] . "  تا ساعت " . $reader["end"]; break;
                case "morningnormal": $morningnormal = urlencode("\t\t\t")."از ساعت".urlencode("\t").$reader["start"] . "  تا ساعت " . $reader["end"]; break;
                case "eveningnormal": $eveningnormal = urlencode("\t\t\t")."از ساعت".urlencode("\t").$reader["start"] . "  تا ساعت " . $reader["end"]; break;
                case "night": $nighttime = urlencode("\t\t\t")."از ساعت".urlencode("\t").$reader["start"] . "  تا ساعت " . $reader["end"]; break;
            }
        }
            
        $query = "select * from days";
    	$rsPackages = mysqli_query( $excal,$query);
    	if (is_numeric($carGroup))
    	    $shifts = $shifts."شما کد ".$number." هستید و عضو گروه ".$carGroup." می باشید".urlencode("\n\n");

    	else
    	    $shifts = $shifts."شما کد ".$number." هستید و عضو گروه "."بانوان"." می باشید".urlencode("\n\n");
    	$shifts = $shifts."گزارش سی روز آینده شما به شرح زیر می باشد:".urlencode("\n");  		
		while($reader=mysqli_fetch_assoc($rsPackages))
    	{
    		if ($reader['monthh'] < $now[1] ||($reader['monthh'] == $now[1]&&$reader['dayy'] < $now[2]) )
                continue;
            else if ($reader['monthh'] > ($now[1]%12)+1 ||($reader['monthh'] == ($now[1]%12)+1&&$reader['dayy'] > $now[2]))
                break;
    		else
            {
                if(is_numeric($carGroup))
                    {
                        if(!strcmp((string) $reader['morning'],(string) $carGroup)&& $reader["isholiday"] == "true")
                            $shifts = $shifts.(string)$reader["monthh"] . "/" . (string)$reader["dayy"] . $morningholiday.urlencode("\n");
                        if(!strcmp((string) $reader['morning'],(string) $carGroup)&& $reader["isholiday"] == "false")
                            $shifts = $shifts.(string)$reader["monthh"] . "/" . (string)$reader["dayy"] . $morningnormal.urlencode("\n");
                        if (!strcmp((string) $reader['evening'] , (string) $carGroup)&& $reader["isholiday"] == "true")
                            $shifts = $shifts.(string)$reader["monthh"] . "/" . (string)$reader["dayy"] . $eveningholiday .urlencode("\n");
                        if (!strcmp((string) $reader['evening'] , (string) $carGroup) && $reader["isholiday"] == "false" && $reader["noon"] == "")
        				    $shifts = $shifts.(string)$reader["monthh"] . "/" . (string)$reader["dayy"] . $eveningnormal .urlencode("\n");
        			    if (!strcmp((string) $reader['evening'] , (string) $carGroup)&& $reader["isholiday"] == "false" && $reader["noon"] != "")
    					    $shifts = $shifts.(string)$reader["monthh"] . "/" . (string)$reader["dayy"] . $nightholiday .urlencode("\n");
                        if (!strcmp((string) $reader['noon'] , (string) $carGroup))
                            $shifts = $shifts.(string)$reader["monthh"] . "/" . (string)$reader["dayy"] . $eveningnormal.urlencode("\n");   
                        $nightshift = $reader["night"];
        				$nightshifts = explode(",", $nightshift);
        				for ($i=0;$i<count($nightshifts)-1;$i++) 
        					if(!strcmp((string) $nightshifts[$i],(string) $number))
        						$shifts = $shifts.(string)$reader["monthh"] . "/" . (string)$reader["dayy"] . $nighttime.urlencode("\n");
                    }else 
                    {
                        
                        $morningshiift = $reader["morninglady"];
        				$morningshiift = explode(",", $morningshiift);
        				for ($i=0;$i<count($morningshiift)-1;$i++) 
        					if(!strcmp((string) $morningshiift[$i],(string) $number))
        						$shifts = $shifts.(string)$reader["monthh"] . "/" . (string)$reader["dayy"] . $morninglady.urlencode("\n");
        						
        				$eveningshiift = $reader["eveninglady"];
        				$eveningshiift = explode(",", $eveningshiift);
        				for ($i=0;$i<count($eveningshiift)-1;$i++) 
        					if(!strcmp((string) $eveningshiift[$i],(string) $number))
        						$shifts = $shifts.(string)$reader["monthh"] . "/" . (string)$reader["dayy"] . $eveninglady.urlencode("\n");
                    }
            }
    	}
    	$text=$shifts;   
    }
		
    sendMessage($text,$chat_id);
}
else if(in_array($chat_id, $admins)&&is_numeric($command))
{
    $number = $command;
    $excal = mysqli_connect($hostname_excal, $username_excal, $password_excal,$database_excal);
        
    $query = "select numberr,groupp,telegram_id from cars WHERE numberr =".$number." LIMIT 1" ;
    $rsPackages = mysqli_query( $excal,$query);
    $recordinfo=mysqli_fetch_assoc($rsPackages);
    $carGroup=$recordinfo['groupp'];
    
    if($carGroup!='') 
    {
        $shifts="";
        $query ="select * from shifts ";
        $rsPackages = mysqli_query( $excal,$query);
        $morningnormal = ""; $eveningnormal = ""; $morningholiday = ""; $eveningholiday = ""; $nightholiday = ""; $nighttime="";$morninglady="";$eveninglady="";
        while ($reader=mysqli_fetch_assoc($rsPackages))
        {
            switch ($reader["namee"])
            {
                case "morninglady": $morninglady = urlencode("\t\t\t")."از ساعت".urlencode("\t").$reader["start"] . "  تا ساعت " . $reader["end"]; break;
                case "eveninglady": $eveninglady = urlencode("\t\t\t")."از ساعت".urlencode("\t").$reader["start"] . "  تا ساعت " . $reader["end"]; break;
                case "morningholiday": $morningholiday = urlencode("\t\t\t")."از ساعت".urlencode("\t").$reader["start"] . "  تا ساعت " . $reader["end"]; break;
                case "eveningholiday": $eveningholiday = urlencode("\t\t\t")."از ساعت".urlencode("\t").$reader["start"] . "  تا ساعت " . $reader["end"]; break;
                case "nightholiday": $nightholiday = urlencode("\t\t\t")."از ساعت".urlencode("\t").$reader["start"] . "  تا ساعت " . $reader["end"]; break;
                case "morningnormal": $morningnormal = urlencode("\t\t\t")."از ساعت".urlencode("\t").$reader["start"] . "  تا ساعت " . $reader["end"]; break;
                case "eveningnormal": $eveningnormal = urlencode("\t\t\t")."از ساعت".urlencode("\t").$reader["start"] . "  تا ساعت " . $reader["end"]; break;
                case "night": $nighttime = urlencode("\t\t\t")."از ساعت".urlencode("\t").$reader["start"] . "  تا ساعت " . $reader["end"]; break;
            }
        }
            
        $query = "select * from days";
    	$rsPackages = mysqli_query( $excal,$query);
    	if (is_numeric($carGroup))
    	    $shifts = $shifts."شماره کد این تاکسی ".$number."   و عضو گروه ".$carGroup."  است.".urlencode("\n\n");
    	else
    	    $shifts = $shifts."شماره کد این تاکسی ".$number."   و عضو گروه "."بانوان"."  است.".urlencode("\n\n");
        $shifts = $shifts."گزارش سی روز آینده این تاکسی به شرح زیر می باشد:".urlencode("\n");
    	$now= gregorian_to_jalali(getdate()['year'],getdate()['mon'],getdate()['mday']);
    	$ggg=0;
    	while($reader=mysqli_fetch_assoc($rsPackages))
		{
		    $ggg=$ggg+1;
    		if ($reader['monthh'] < $now[1] ||($reader['monthh'] == $now[1]&&$reader['dayy'] < $now[2]) )
                continue; 
            else if ($reader['monthh'] > ($now[1]%12)+1 ||($reader['monthh'] == ($now[1]%12)+1&&$reader['dayy'] > $now[2]))
                break;
    		else
            {
                if(is_numeric($carGroup))
                    {
                        if(!strcmp((string) $reader['morning'],(string) $carGroup)&& $reader["isholiday"] == "true")
                            $shifts = $shifts.(string)$reader["monthh"] . "/" . (string)$reader["dayy"] . $morningholiday.urlencode("\n");
                        if(!strcmp((string) $reader['morning'],(string) $carGroup)&& $reader["isholiday"] == "false")
                            $shifts = $shifts.(string)$reader["monthh"] . "/" . (string)$reader["dayy"] . $morningnormal.urlencode("\n");
                        if (!strcmp((string) $reader['evening'] , (string) $carGroup)&& $reader["isholiday"] == "true")
                            $shifts = $shifts.(string)$reader["monthh"] . "/" . (string)$reader["dayy"] . $eveningholiday .urlencode("\n");
                        if (!strcmp((string) $reader['evening'] , (string) $carGroup) && $reader["isholiday"] == "false" && $reader["noon"] == "")
        				    $shifts = $shifts.(string)$reader["monthh"] . "/" . (string)$reader["dayy"] . $eveningnormal .urlencode("\n");
        			    if (!strcmp((string) $reader['evening'] , (string) $carGroup)&& $reader["isholiday"] == "false" && $reader["noon"] != "")
    					    $shifts = $shifts.(string)$reader["monthh"] . "/" . (string)$reader["dayy"] . $nightholiday .urlencode("\n");
                        if (!strcmp((string) $reader['noon'] , (string) $carGroup))
                            $shifts = $shifts.(string)$reader["monthh"] . "/" . (string)$reader["dayy"] . $eveningnormal.urlencode("\n");   
                        $nightshift = $reader["night"];
        				$nightshifts = explode(",", $nightshift);
        				for ($i=0;$i<count($nightshifts)-1;$i++) 
        					if(!strcmp((string) $nightshifts[$i],(string) $number))
        						$shifts = $shifts.(string)$reader["monthh"] . "/" . (string)$reader["dayy"] . $nighttime.urlencode("\n");
                    }else 
                    {
                        
                        $morningshiift = $reader["morninglady"];
        				$morningshiift = explode(",", $morningshiift);
        				for ($i=0;$i<count($morningshiift)-1;$i++) 
        					if(!strcmp((string) $morningshiift[$i],(string) $number))
        						$shifts = $shifts.(string)$reader["monthh"] . "/" . (string)$reader["dayy"] . $morninglady.urlencode("\n");
        						
        				$eveningshiift = $reader["eveninglady"];
        				$eveningshiift = explode(",", $eveningshiift);
        				for ($i=0;$i<count($eveningshiift)-1;$i++) 
        					if(!strcmp((string) $eveningshiift[$i],(string) $number))
        						$shifts = $shifts.(string)$reader["monthh"] . "/" . (string)$reader["dayy"] . $eveninglady.urlencode("\n");
                    }
            }
    			
    	}
    	$text=$shifts;   
    }else $text="این شماره کد در سامانه موجود نیست";
    sendMessage($text,$chat_id);
}
else if(is_numeric($command)){
    $number = $command;
    $excal = mysqli_connect($hostname_excal, $username_excal, $password_excal,$database_excal);
        
    
    $query = "select groupp from cars WHERE telegram_id ='".$chat_id."' LIMIT 1" ;
    $rsPackages = mysqli_query( $excal,$query);
    if(mysqli_num_rows($rsPackages)==0) 
    {
        $query = "select numberr,groupp,telegram_id from cars WHERE numberr =".$number." LIMIT 1" ;
        $rsPackages = mysqli_query( $excal,$query);
        $recordinfo=mysqli_fetch_assoc($rsPackages);
        $carGroup=$recordinfo['groupp'];
        if($carGroup!=''&&$recordinfo['telegram_id']=='')
        {
            $now= gregorian_to_jalali(getdate()['year'],getdate()['mon'],getdate()['mday']);
            //set this telegram_id for this car's number
            $query ="update cars set telegram_id='".$chat_id."' , dateemodifie='".$now[1]."/".$now[2]."  ".getdate()['hours'].":".getdate()['minutes']."'  WHERE numberr =".$number;
            $rsPackages = mysqli_query( $excal,$query);
            
            $shifts="";
            $query ="select * from shifts ";
            $rsPackages = mysqli_query( $excal,$query);
            $morningnormal = ""; $eveningnormal = ""; $morningholiday = ""; $eveningholiday = ""; $nightholiday = ""; $nighttime="";$morninglady="";$eveninglady="";
            while ($reader=mysqli_fetch_assoc($rsPackages))
            {
                switch ($reader["namee"])
                {
                    case "morninglady": $morninglady = urlencode("\t\t\t")."از ساعت".urlencode("\t").$reader["start"] . "  تا ساعت " . $reader["end"]; break;
                    case "eveninglady": $eveninglady = urlencode("\t\t\t")."از ساعت".urlencode("\t").$reader["start"] . "  تا ساعت " . $reader["end"]; break;
                    case "morningholiday": $morningholiday = urlencode("\t\t\t")."از ساعت".urlencode("\t").$reader["start"] . "  تا ساعت " . $reader["end"]; break;
                    case "eveningholiday": $eveningholiday = urlencode("\t\t\t")."از ساعت".urlencode("\t").$reader["start"] . "  تا ساعت " . $reader["end"]; break;
                    case "nightholiday": $nightholiday = urlencode("\t\t\t")."از ساعت".urlencode("\t").$reader["start"] . "  تا ساعت " . $reader["end"]; break;
                    case "morningnormal": $morningnormal = urlencode("\t\t\t")."از ساعت".urlencode("\t").$reader["start"] . "  تا ساعت " . $reader["end"]; break;
                    case "eveningnormal": $eveningnormal = urlencode("\t\t\t")."از ساعت".urlencode("\t").$reader["start"] . "  تا ساعت " . $reader["end"]; break;
                    case "night": $nighttime = urlencode("\t\t\t")."از ساعت".urlencode("\t").$reader["start"] . "  تا ساعت " . $reader["end"]; break;
                }
            }
            
            $query = "select * from days";
    		$rsPackages = mysqli_query( $excal,$query);
    		$shifts = $shifts."شما کد ".$number." هستید و عضو گروه ".$carGroup." می باشید".urlencode("\n\n");
    		$shifts = $shifts."گزارش سی روز آینده شما به شرح زیر می باشد:".urlencode("\n");
    		while($reader=mysqli_fetch_assoc($rsPackages))
    		{
    			if ($reader['monthh'] < $now[1] ||($reader['monthh'] == $now[1]&&$reader['dayy'] < $now[2]) )
                    continue;
                else if ($reader['monthh'] > ($now[1]%12)+1 ||($reader['monthh'] == ($now[1]%12)+1&&$reader['dayy'] > $now[2]))
                    break;
    			else
                {
                    if(is_numeric($carGroup))
                    {
                        if(!strcmp((string) $reader['morning'],(string) $carGroup)&& $reader["isholiday"] == "true")
                            $shifts = $shifts.(string)$reader["monthh"] . "/" . (string)$reader["dayy"] . $morningholiday.urlencode("\n");
                        if(!strcmp((string) $reader['morning'],(string) $carGroup)&& $reader["isholiday"] == "false")
                            $shifts = $shifts.(string)$reader["monthh"] . "/" . (string)$reader["dayy"] . $morningnormal.urlencode("\n");
                        if (!strcmp((string) $reader['evening'] , (string) $carGroup)&& $reader["isholiday"] == "true")
                            $shifts = $shifts.(string)$reader["monthh"] . "/" . (string)$reader["dayy"] . $eveningholiday .urlencode("\n");
                        if (!strcmp((string) $reader['evening'] , (string) $carGroup) && $reader["isholiday"] == "false" && $reader["noon"] == "")
        				    $shifts = $shifts.(string)$reader["monthh"] . "/" . (string)$reader["dayy"] . $eveningnormal .urlencode("\n");
        			    if (!strcmp((string) $reader['evening'] , (string) $carGroup)&& $reader["isholiday"] == "false" && $reader["noon"] != "")
    					    $shifts = $shifts.(string)$reader["monthh"] . "/" . (string)$reader["dayy"] . $nightholiday .urlencode("\n");
                        if (!strcmp((string) $reader['noon'] , (string) $carGroup))
                            $shifts = $shifts.(string)$reader["monthh"] . "/" . (string)$reader["dayy"] . $eveningnormal.urlencode("\n");   
                        $nightshift = $reader["night"];
        				$nightshifts = explode(",", $nightshift);
        				for ($i=0;$i<count($nightshifts)-1;$i++) 
        					if(!strcmp((string) $nightshifts[$i],(string) $number))
        						$shifts = $shifts.(string)$reader["monthh"] . "/" . (string)$reader["dayy"] . $nighttime.urlencode("\n");
                    }else 
                    {
                        
                        $morningshiift = $reader["morninglady"];
        				$morningshiift = explode(",", $morningshiift);
        				for ($i=0;$i<count($morningshiift)-1;$i++) 
        					if(!strcmp((string) $morningshiift[$i],(string) $number))
        						$shifts = $shifts.(string)$reader["monthh"] . "/" . (string)$reader["dayy"] . $morninglady.urlencode("\n");
        						
        				$eveningshiift = $reader["eveninglady"];
        				$eveningshiift = explode(",", $eveningshiift);
        				for ($i=0;$i<count($eveningshiift)-1;$i++) 
        					if(!strcmp((string) $eveningshiift[$i],(string) $number))
        						$shifts = $shifts.(string)$reader["monthh"] . "/" . (string)$reader["dayy"] . $eveninglady.urlencode("\n");
                    }
                }
    			
    		}
    		$text=$shifts; 
        }else $text="این شماره کد در سامانه وجود ندارد یا متعلق به شما نیست";
    }else $text="لطفا از دستور   "."/mynext30daysshifts" ."   استفاده کنید";
    sendMessage($text,$chat_id);
}
else if(strtolower($command) == 'how many'&&in_array($chat_id, $admins)){
    $excal = mysqli_connect($hostname_excal, $username_excal, $password_excal,$database_excal);
    $query = "select * from cars ";
    $rsPackages = mysqli_query( $excal,$query);
	$taxitotal=mysqli_num_rows($rsPackages);
	$query = "select * from cars where telegram_id<>'' order by numberr";
    $rsPackages = mysqli_query( $excal,$query);
    $text="تاکنون ".mysqli_num_rows($rsPackages)."  از  ".$taxitotal."  تاکسی از این ربات استفاده کردند".urlencode("\n")."که لیست آنها به شرح زیر می باشد:".urlencode("\n");
    while($reader=mysqli_fetch_assoc($rsPackages))
    {
		$text=$text.$reader['numberr']."          ".$reader['dateemodifie'].urlencode("\n");
	}
    sendMessage($text,$chat_id);
}
else if(strtolower($command) == 'unsign taxi'&&in_array($chat_id, $admins)){
    $excal = mysqli_connect($hostname_excal, $username_excal, $password_excal,$database_excal);
    $query = "select * from cars ";
    $rsPackages = mysqli_query( $excal,$query);
	$taxitotal=mysqli_num_rows($rsPackages);
	$query = "select * from cars where telegram_id='' order by numberr";
    $rsPackages = mysqli_query( $excal,$query);
    $text="هنوز ".mysqli_num_rows($rsPackages)."  از  ".$taxitotal."  تاکسی از این ربات استفاده نکردند".urlencode("\n")."که لیست آنها به شرح زیر می باشد:".urlencode("\n");
    while($reader=mysqli_fetch_assoc($rsPackages))
    {
		$text=$text.$reader['numberr'].urlencode("\n");
	}
    sendMessage($text,$chat_id);
}else if(strtolower($command) == 'hello'){
    sendMessage($text,$chat_id);
}
else{
    $text= "لطفا کد تاکسی خود را به زبان انگلیسی وارد کنید";
    sendMessage($text,$chat_id);
}



function gregorian_to_jalali($gy,$gm,$gd,$mod=''){
 $g_d_m=array(0,31,59,90,120,151,181,212,243,273,304,334);
 if($gy>1600){
  $jy=979;
  $gy-=1600;
 }else{
  $jy=0;
  $gy-=621;
 }
 $gy2=($gm>2)?($gy+1):$gy;
 $days=(365*$gy) +((int)(($gy2+3)/4)) -((int)(($gy2+99)/100)) +((int)(($gy2+399)/400)) -80 +$gd +$g_d_m[$gm-1];
 $jy+=33*((int)($days/12053)); 
 $days%=12053;
 $jy+=4*((int)($days/1461));
 $days%=1461;
 if($days > 365){
  $jy+=(int)(($days-1)/365);
  $days=($days-1)%365;
 }
 $jm=($days < 186)?1+(int)($days/31):7+(int)(($days-186)/30);
 $jd=1+(($days < 186)?($days%31):(($days-186)%30));
 return($mod=='')?array($jy,$jm,$jd):$jy.$mod.$jm.$mod.$jd;
}


function jalali_to_gregorian($jy,$jm,$jd,$mod=''){
 if($jy>979){
  $gy=1600;
  $jy-=979;
 }else{
  $gy=621;
 }
 $days=(365*$jy) +(((int)($jy/33))*8) +((int)((($jy%33)+3)/4)) +78 +$jd +(($jm<7)?($jm-1)*31:(($jm-7)*30)+186);
 $gy+=400*((int)($days/146097));
 $days%=146097;
 if($days > 36524){
  $gy+=100*((int)(--$days/36524));
  $days%=36524;
  if($days >= 365)$days++;
 }
 $gy+=4*((int)($days/1461));
 $days%=1461;
 if($days > 365){
  $gy+=(int)(($days-1)/365);
  $days=($days-1)%365;
 }
 $gd=$days+1;
 foreach(array(0,31,(($gy%4==0 and $gy%100!=0) or ($gy%400==0))?29:28 ,31,30,31,30,31,31,30,31,30,31) as $gm=>$v){
  if($gd<=$v)break;
  $gd-=$v;
 }
 return($mod=='')?array($gy,$gm,$gd):$gy.$mod.$gm.$mod.$gd; 
} 
function makeRecoverySQL($table, $id)
{
    // get the record          
    $selectSQL = "SELECT * FROM `" . $table . "` WHERE `id` = " . $id . ';';

    $result = mysql_query($selectSQL, $YourDbHandle);
    $row = mysql_fetch_assoc($result); 

    $insertSQL = "INSERT INTO `" . $table . "` SET ";
    foreach ($row as $field => $value) {
        $insertSQL .= " `" . $field . "` = '" . $value . "', ";
    }
    $insertSQL = trim($insertSQL, ", ");

    return $insertSQL;
}
function sendMessage($text,$chat_id)
{
    $token= "383863488:AAHM_qTyobcl8R-ZyePHzYlx-VUC2sybXRg";
    $url= "https://api.telegram.org/bot".$token."/sendMessage?chat_id=".$chat_id."&text=".$text;
    $ch = curl_init();

// set URL and other appropriate options
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 0);

// grab URL and pass it to the browser
curl_exec($ch);

// close cURL resource, and free up system resources
curl_close($ch);
}

?>
