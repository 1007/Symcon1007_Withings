<?
	class Withings extends IPSModule
	{
		


    //**************************************************************************
    //
    //**************************************************************************    
    public function Create()
      {
      //Never delete this line!
      parent::Create();
    
      $this->RegisterPropertyInteger("Intervall", 21600);  
      $this->RegisterPropertyBoolean("BodyMeasures", true);  
      $this->RegisterPropertyBoolean("BloodMeasures", false);  
      $this->RegisterPropertyString("Username", "user@user.de");  
      $this->RegisterPropertyString("Userpassword", "123456");  
      $this->RegisterPropertyString("User", "XXX");  
      $this->RegisterPropertyBoolean("Logging", false);  
      $this->RegisterPropertyBoolean("Modulaktiv", true);  
      $this->RegisterTimer("WIT_UpdateTimer", 0, 'WIT_Update($_IPS[\'TARGET\']);');
        
      }
    

    //**************************************************************************
    //
    //**************************************************************************    
		public function ApplyChanges()
		  {
			//Never delete this line!
			parent::ApplyChanges();

      $this->RegisterProfile(1,"WITHINGS_M_Groesse"  ,"Gauge"  ,""," cm");
      $this->RegisterProfile(1,"WITHINGS_M_Puls"     ,"Graph"  ,""," bpm");
      $this->RegisterProfile(2,"WITHINGS_M_Kilo"     ,""       ,""," kg",false,false,false,1);
      $this->RegisterProfile(2,"WITHINGS_M_Prozent"  ,""       ,""," %",false,false,false,1);
      $this->RegisterProfile(1,"WITHINGS_M_Blutdruck","",""," mmHg");
      $this->RegisterProfileGender("WITHINGS_M_Gender", "", "", "", Array(
                                             Array(0, "maennlich",  "", 0x0000FF),
                                             Array(1, "weiblich",   "", 0xFF0000)
                                            ));


			$id = $this->RegisterVariableString("name"       , "Name"      ,"~String",0);
			$id = $this->RegisterVariableInteger("gender"    , "Geschlecht","WITHINGS_Gender",2);
			$id = $this->RegisterVariableString("birthdate"  , "Geburtstag","~String",1);
			$id = $this->RegisterVariableInteger("height"    , "Groesse"   ,"WITHINGS_Groesse" ,3);

      $parent = IPS_GetParent($id);
            
      $CatID = $this->CreateKategorie("Blutdruck",$parent);
      if ( $CatID === false )
        throw new Exception("Kategorie Blutdruck nicht definiert");
      
      $VariablenID = @IPS_GetVariableIDByName("Diastolic",$CatID);  
			if ($VariablenID === false)
        {
        $id = $this->RegisterVariableInteger("diastolicblood", "Diastolic","WITHINGS_Blutdruck",1);
        IPS_SetParent($id,$CatID);
        }
      $VariablenID = @IPS_GetVariableIDByName("Systolic",$CatID);  
			if ($VariablenID === false)
        {
        $id = $this->RegisterVariableInteger("systolicblood", "Systolic","WITHINGS_Blutdruck",2);
        IPS_SetParent($id,$CatID);
        }
      $VariablenID = @IPS_GetVariableIDByName("Puls",$CatID);  
			if ($VariablenID === false)
        {
        $id = $this->RegisterVariableInteger("heartpulse", "Puls","WITHINGS_Puls",3);
        IPS_SetParent($id,$CatID);
        }
      $VariablenID = @IPS_GetVariableIDByName("DatumUhrzeit",$CatID);  
			if ($VariablenID === false)
        {
        $id = $this->RegisterVariableInteger("timestamp", "DatumUhrzeit","~UnixTimestamp",0);
        IPS_SetParent($id,$CatID);
        }
               
      $CatID = $this->CreateKategorie("Waage",$parent); 
      if ( $CatID === false )
        throw new Exception("Kategorie Waage nicht definiert");
 
      $VariablenID = @IPS_GetVariableIDByName("DatumUhrzeit",$CatID);  
			if ($VariablenID === false)
        {
        $id = $this->RegisterVariableInteger("timestamp", "DatumUhrzeit","~UnixTimestamp",0);
        IPS_SetParent($id,$CatID);
        }
      $VariablenID = @IPS_GetVariableIDByName("Gewicht",$CatID);  
			if ($VariablenID === false)
        {
        $id = $this->RegisterVariableFloat("weight", "Gewicht","WITHINGS_Kilo",1);
        IPS_SetParent($id,$CatID);
        }
      $VariablenID = @IPS_GetVariableIDByName("Fettfrei Anteil",$CatID);  
			if ($VariablenID === false)
        {
        $id = $this->RegisterVariableFloat("fatfree", "Fettfrei Anteil","WITHINGS_Kilo",3);
        IPS_SetParent($id,$CatID);
        }
      $VariablenID = @IPS_GetVariableIDByName("Fett Anteil",$CatID);  
			if ($VariablenID === false)
        {
        $id = $this->RegisterVariableFloat("fatmassweight", "Fett Anteil","WITHINGS_Kilo",2);
        IPS_SetParent($id,$CatID);
        }
      $VariablenID = @IPS_GetVariableIDByName("Fett Prozent",$CatID);  
			if ($VariablenID === false)
        {
        $id = $this->RegisterVariableFloat("fatradio", "Fett Prozent","WITHINGS_Prozent",4);
        IPS_SetParent($id,$CatID);
        }
      $VariablenID = @IPS_GetVariableIDByName("BMI",$CatID);          
			if ($VariablenID === false)
        {
        $id = $this->RegisterVariableFloat("bmi", "BMI","WITHINGS_Prozent",5);
        IPS_SetParent($id,$CatID);
        }




			//Lets register a variable with action   ????
			//$this->RegisterVariableInteger("Withings", "Test", "~Intensity.100");
			//$this->EnableAction("Withings");
      
	    //Timer erstellen
      $this->SetTimerInterval("WIT_UpdateTimer", $this->ReadPropertyInteger("Intervall"));
 
      //Update
     	$this->Update();
		  }

    //**************************************************************************
    //
    //**************************************************************************    
    public function Update()
      {
      if ( $this->ReadPropertyBoolean("Modulaktiv") == false )
        return;

      $this->Logging("Update");

      $this->UpdateUserData();
                
      return true;
      }
      
      
    //**************************************************************************
    //
    //**************************************************************************    
    public function Destroy()
      {
      $this->UnregisterTimer("WIT_UpdateTimer");
        
      //Never delete this line!
      parent::Destroy();
      }



    protected function UpdateUserData()
      {
      $id = $this->GetIDForIdent("name");
      $ModulID = IPS_GetParent($id);
      
                  
 	    $Username 		  = $this->ReadPropertyString("Username");
      $Userpassword   = $this->ReadPropertyString("Userpassword");
      $User           = $this->ReadPropertyString("User");     
      
      $this->API_AccountGetuserslist ( $Username, $Userpassword, $users );

      if ( !$users )
	     {
	     $this->Logging("Fehler beim Holen der Username und Passwort ueberpruefen");
       return;
	     }

      $gefunden = false;
	
      foreach( $users as $user )
	     {
	     if ( $user['shortname'] == $User )
        {
	      $gefunden = true;
			  $personid = $user['id'];
			  $publickey = $user['publickey'];
			  $data = $user;
			 }
	     }
	   
	   if ( !$gefunden )
	     {
	     $this->Logging("User Shortname ".$User." nicht gefunden.");
       return;
	     }

	     $startdate 	= 0;     // Startdatum
	     $enddate 	= 0;     // Endedatum

	     // User
	     $this->DoUser($ModulID,$data);

        if ( $this->ReadPropertyBoolean("BodyMeasures") == true )
          {
          $this->Logging("BodyMeasures Daten werden geholt.");

	       // Groesse
	       $limit      = 1;     
	       $meastype   = 4;
	       $devtype   	= 1;
	       $this->API_MeasureGetmeas ( $personid, $publickey, $data, $startdate,$enddate,$meastype,$devtype,$limit);
	       $this->DoGroesse($ModulID,$data);

	       // Gewicht
	       $limit      = 4;
	       $meastype   = false;
	       $devtype   	= 1;
	       $this->API_MeasureGetmeas ( $personid, $publickey, $data, $startdate,$enddate,$meastype,$devtype,$limit);
	       $this->DoGewicht($ModulID,$data);
         }

        if ( $this->ReadPropertyBoolean("BloodMeasures") == true )
          {
          $this->Logging("BloodMeasures Daten werden geholt.");

	       // Blutdruck
	       $limit      = 3;
	       $meastype   = false;
	       $devtype   	= 4;
	       $this->API_MeasureGetmeas ( $personid, $publickey, $data, $startdate,$enddate,$meastype,$devtype,$limit);
	       $this->DoBlutdruck($ModulID,$data);
         }
         
      }

    //**************************************************************************
    //
    //**************************************************************************    
		public function RequestAction($Ident, $Value)
		  {
			
			switch($Ident) 
        {
				case "Withings":
					SetValue($this->GetIDForIdent($Ident), $Value);
					break;
				default:
					throw new Exception("Invalid ident");
			   }
		
		  }


    protected function RegisterProfileGender($Name, $Icon, $Prefix, $Suffix, $Associations) {
        if ( sizeof($Associations) === 0 ){
            $MinValue = 0;
            $MaxValue = 0;
        } else {
            $MinValue = $Associations[0][0];
            $MaxValue = $Associations[sizeof($Associations)-1][0];
        }

        $this->RegisterProfile(1,$Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, 0);

        foreach($Associations as $Association) {
            IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
        }

    }

    //**************************************************************************
    //  Create    Kategorie
    //**************************************************************************    
    private function CreateKategorie($Name,$Parent)
      {
      if ( $Parent == 0 OR $Parent == false )
        return false;
      $id = @IPS_GetCategoryIDByName($Name,$Parent);  
      if ( $id === false)
        {}
      else
        return $id;
        
      $id = IPS_CreateCategory ();
      if ( $id === false )
        return false;
      IPS_SetParent($id,$Parent);  
      IPS_SetName ($id,$Name);
      
      return $id;
      }
		
    //**************************************************************************
    //  Logging
    //**************************************************************************    
    private function Logging($Text)
      {
      if ( $this->ReadPropertyBoolean("Logging") == false )
        return;
      $ordner = IPS_GetLogDir() . "Withings";
      if ( !is_dir ( $ordner ) )
		    mkdir($ordner);

      if ( !is_dir ( $ordner ) )
	     return;

      $time = date("d.m.Y H:i:s");
      $logdatei = IPS_GetLogDir() . "Withings/Withings.log";
      $datei = fopen($logdatei,"a+");
      fwrite($datei, $time ." ". $Text . chr(13));
      fclose($datei);
      }

    //**************************************************************************
    //  0 - Bool
    //  1 - Integer
    //  2 - Float
    //  3 - String
    //**************************************************************************    
    protected function RegisterProfile($Typ, $Name, $Icon, $Prefix, $Suffix, $MinValue=false, $MaxValue=false, $StepSize=false, $Digits=0) 
      {
      if(!IPS_VariableProfileExists($Name)) 
        {
        IPS_CreateVariableProfile($Name, $Typ);  
        } 
      else 
        {
        $profile = IPS_GetVariableProfile($Name);
        if($profile['ProfileType'] != $Typ)
          throw new Exception("Variable profile type does not match for profile ".$Name);
        }
        
      IPS_SetVariableProfileIcon($Name, $Icon);
      IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
      IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
		  if ( $Typ == 2 )
			 IPS_SetVariableProfileDigits($Name, $Digits);
      }
    
    
    
    protected function RegisterTimer($Name, $Interval, $Script,$Position = 99)
      {
      $id = @IPS_GetObjectIDByIdent($Name, $this->InstanceID);
      if ($id === false)
        $id = 0;
      if ($id > 0)
        {
          if (!IPS_EventExists($id))
            throw new Exception("Ident with name " . $Name . " is used for wrong object type", E_USER_WARNING);
          if (IPS_GetEvent($id)['EventType'] <> 1)
            {
            IPS_DeleteEvent($id);
            $id = 0;
            }
        }
      if ($id == 0)
        {
        $id = IPS_CreateEvent(1);
        IPS_SetParent($id, $this->InstanceID);
        IPS_SetIdent($id, $Name);
        }
      IPS_SetName($id, $Name);
      IPS_SetHidden($id, true);
      IPS_SetEventScript($id, $Script);
      IPS_SetPosition($id,$Position);
      if ($Interval > 0)
        {
        IPS_SetEventCyclic($id, 0, 0, 0, 0, 1, $Interval);
        IPS_SetEventActive($id, true);
        } 
      else
        {
        IPS_SetEventCyclic($id, 0, 0, 0, 0, 1, 1);
        IPS_SetEventActive($id, false);
        }
      }
      
    protected function UnregisterTimer($Name)
      {
      $id = @IPS_GetObjectIDByIdent($Name, $this->InstanceID);
      if ($id > 0)
        {
        if (!IPS_EventExists($id))
          throw new Exception('Timer not present', E_USER_NOTICE);
        IPS_DeleteEvent($id);
        }
      }
    
    protected function SetTimerInterval($Name, $Interval)
      {
      $id = @IPS_GetObjectIDByIdent($Name, $this->InstanceID);
      if ($id === false)
        throw new Exception('Timer not present', E_USER_WARNING);
      if (!IPS_EventExists($id))
        throw new Exception('Timer not present', E_USER_WARNING);
      $Event = IPS_GetEvent($id);
      if ($Interval < 1)
        {
        if ($Event['EventActive'])
          IPS_SetEventActive($id, false);
        }
      else
        {
        if ($Event['CyclicTimeValue'] <> $Interval)
          IPS_SetEventCyclic($id, 0, 0, 0, 0, 1, $Interval);
        if (!$Event['EventActive'])
          IPS_SetEventActive($id, true);
        }
      }
	

  
  //****************************************************************************
  
protected  function DoUser($ModulID,$data)
	{
	$Tage = array("Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag");
	
	$Vorname  	= $data['firstname'];
	$Nachname 	= $data['lastname'];
	$Name       = $Vorname . " " . $Nachname;
	$Geschlecht = $data['gender'];
	$Geburtstag = date('d.m.Y',$data['birthdate']);
	$Tag        = $Tage[date("w",$data['birthdate'])];
	$Geburtstag = $Tag . " " . $Geburtstag;
	
	$id = @IPS_GetVariableIDByName("Name",$ModulID);
	if ( $id > 0 )
    {
     $v =  GetValueString($id);
     if ( $v != $Name)
	     SetValueString($id,$Name);
    }

	$id = @IPS_GetVariableIDByName("Geburtstag",$ModulID);
	if ( $id > 0 )
    {
     $v =  GetValueString($id);
     if ( $v != $Geburtstag)
	     SetValueString($id,$Geburtstag);
    }

	  

	$id = @IPS_GetVariableIDByName("Geschlecht",$ModulID);
	if ( $id > 0 )
    {
     $v =  GetValueInteger($id);
     if ( $v != $Geschlecht)
	     SetValueInteger($id,$Geschlecht);
    }

	}
	
	
protected function DoGroesse($ModulID,$data)
	{
	$data = @$data['measuregrps'][0]['measures'][0];
	if ( count($data) != 3 )
	   {
	   $this->Logging("Fehler bei DoGroesse ".count($data));
	   //return;
		}
	$Groesse = $data['value'];

	$id = @IPS_GetVariableIDByName("Groesse",$ModulID);
	if ( $id > 0 )
    {
     $v =  GetValueInteger($id);
     if ( $v != $Groesse)
	     SetValueInteger($id,$Groesse);
    }

	}
	
protected  function DoGewicht($ModulID,$data)
	{
	$gewichtdatum 	= false;
	$gewicht       = 0;
	$fettfrei      = 0;
	$fettanteil    = 0;
	$fettprozent   = 0;
	$bmi           = 0;
	$groesse       = 0;
	
	$id = @IPS_GetVariableIDByName("Groesse",$ModulID);
	if ( $id > 0 )
	   $groesse = GetValueInteger($id);


	$CatID = @IPS_GetCategoryIDByName("Waage",$ModulID);
	
	if ( $CatID === false )
	   return;

	$time = @$data['measuregrps'][0]['date'];

	$data = @$data['measuregrps'][0]['measures'];

	if ( count($data) != 4 )
	   {
	   $this->Logging("Fehler bei DoGewicht ".count($data));
	   //return;
		}

	$id = @IPS_GetVariableIDByName("DatumUhrzeit",$CatID);
	if ( $id > 0 )
	   {
	   $old = GetValueInteger($id);
	   if ( $old == $time )    // keine neue Daten
	      return false;
	   SetValueInteger($id,$time);
		}

	foreach($data as $messung)
	   {
		$val = floatval ( $messung['value'] ) * floatval ( "1e".$messung['unit'] );

		if ( $messung['type'] == 1 )  $gewicht 		= round ($val,2);
		if ( $messung['type'] == 5 )  $fettfrei 		= round ($val,2);
		if ( $messung['type'] == 6 )  $fettprozent 	= round ($val,2);
		if ( $messung['type'] == 8 )  $fettanteil  	= round ($val,2);

	   }

   $bmi = round($gewicht/(($groesse/100)*($groesse/100)),2);

	$id = IPS_GetVariableIDByName("Gewicht",$CatID);
	if ( $id > 0 )
	   SetValueFloat($id,$gewicht);
	$id = IPS_GetVariableIDByName("Fett Anteil",$CatID);
	if ( $id > 0 )
	   SetValueFloat($id,$fettanteil);
	$id = IPS_GetVariableIDByName("Fettfrei Anteil",$CatID);
	if ( $id > 0 )
	   SetValueFloat($id,$fettfrei);
	$id = IPS_GetVariableIDByName("Fett Prozent",$CatID);
	if ( $id > 0 )
	   SetValueFloat($id,$fettprozent);
	$id = IPS_GetVariableIDByName("BMI",$CatID);
	if ( $id > 0 )
	   SetValueFloat($id,$bmi);

	}
	
protected  function DoBlutdruck($ModulID,$data)
	{
	$diastolic     = 0;
	$systolic      = 0;
	$puls          = 0;

	$CatID = @IPS_GetCategoryIDByName("Blutdruck",$ModulID);

	if ( $CatID === false )
	   return;
   	
	$time = @$data['measuregrps'][0]['date'];
	
	$data = @$data['measuregrps'][0]['measures'];

	if ( count($data) != 3 )
	   {
	   $this->Logging("Fehler bei DoBlutdruck ".count($data));
	   //return;
		}

	$id = @IPS_GetVariableIDByName("DatumUhrzeit",$CatID);
	if ( $id > 0 )
	   {
	   $old = GetValueInteger($id);
	   if ( $old == $time )    // keine neue Daten
	      return false;
	   SetValueInteger($id,$time);
		}

	foreach($data as $messung)
	   {
		$val = $messung['value'];

		if ( $messung['type'] == 9 )  $diastolic 		= $val;
		if ( $messung['type'] == 10 ) $systolic 		= $val;
		if ( $messung['type'] == 11 ) $pulse 			= $val;

	   }

	$id = IPS_GetVariableIDByName("Diastolic",$CatID);
	if ( $id > 0 )
	   SetValueInteger($id,$diastolic);
	$id = IPS_GetVariableIDByName("Systolic",$CatID);
	if ( $id > 0 )
	   SetValueInteger($id,$systolic);
	$id = IPS_GetVariableIDByName("Puls",$CatID);
	if ( $id > 0 )
	   SetValueInteger($id,$pulse);

	}
	
protected function	API_MeasureGetmeas ( $userid, $publickey , &$measuregrps, $startdate=0, $enddate=0, $meastype = false ,$devtype=false, $limit=false )
	{

	$string="measure?action=getmeas&userid=".$userid."&publickey=".$publickey;
	$string="measure?action=getmeas&userid=".$userid."&publickey=".$publickey;

	if ( $meastype );
  		$string.="&meastype=".$meastype;

	if ( $devtype );
  		$string.="&devtype=".$devtype;

	if ( $limit )
		$string.="&limit=".$limit;

	if ( $this->CurlCall ( $string,$result)===false)
		return ( false );

	$measuregrps = $result['body'];

	return (true);
	}

protected  function API_AccountGetuserslist ( $email, $password , &$userslist )
	{
	$userslist = Array ();

	if ( $this->CurlCall ( "once?action=get", $result)===false)
		return (false);

	$once = $result['body']['once'];
	$hash = md5 ( $email.":".md5($password).":".$once);

	if ( $this->CurlCall ( "account?action=getuserslist&email=".$email."&hash=".$hash, $result)===false)
		return (false);
	
	$userslist = $result['body']['users'];

	return (true);

	}

protected  function CurlCall ( $service , &$result=null )
	{
	
	$APIURL = 'http://wbsapi.withings.net/';

	$s = curl_init();
	curl_setopt($s,CURLOPT_URL,$APIURL.$service);
   curl_setopt($s,CURLOPT_POST,false);
   curl_setopt($s, CURLOPT_RETURNTRANSFER, 1);
	$this->Logging($APIURL.$service);
	$output = curl_exec($s);
   curl_close($s);

	$result = json_decode ( $output , TRUE );

	if (!is_array($result))
		return (false);
	if (!key_exists('status',$result))
		return (false);
	if ($result['status']!=0)
		return (false);

	return ( true );

	}

}

    
  
?>
