<?php
    
//******************************************************************************
//	Name		:	Withings Modul.php
//	Aufruf		:	
//	Info		:	
//
//******************************************************************************


	//**********************************************************************
	//	
	//**********************************************************************
	class Withings extends IPSModule
	{
	//**********************************************************************
	//
	//**********************************************************************    
	public function Create()
		{
		//Never delete this line!
		parent::Create();

		$this->RegisterPropertyInteger("Intervall", 3600);  
		$this->RegisterPropertyBoolean("BodyMeasures", false);
		$this->RegisterPropertyBoolean("BodyPuls", false);  
		$this->RegisterPropertyBoolean("BloodMeasures", false);  
		$this->RegisterPropertyString("Username", "XXX");         	// in V3.0 ist das die UserID
		$this->RegisterPropertyString("Userpassword", "123456");  	// in V3.0 ist das das Access Token
		$this->RegisterPropertyString("User", "XXX");             	// in V3.0 ist das das Refresh Token
		$this->RegisterPropertyBoolean("Logging", false);  
		$this->RegisterPropertyBoolean("Modulaktiv", true);  
		$this->RegisterTimer("WIT_UpdateTimer", 3600000, 'WIT_Update($_IPS["TARGET"]);');
		$this->RegisterPropertyBoolean("BloodLogging", false);    	// in V3.0 ist das  Sleep aktiv
		$this->RegisterPropertyBoolean("BloodVisible", false);  
		$this->RegisterPropertyBoolean("BodyLogging" , false);  	// in V3.0 ist das  Activity aktiv
		$this->RegisterPropertyBoolean("BodyVisible" , false);  	// in V4.0 ist das Notify aktiv
		
        $this->RegisterPropertyBoolean("CheckBoxMeas" , false);
        $this->RegisterPropertyBoolean("CheckBoxSleepSummary" , false);
        $this->RegisterPropertyBoolean("CheckBoxActivity" , false);
        $this->RegisterPropertyBoolean("CheckBoxIntradayactivity" , false);
                
        $this->RegisterPropertyString("AccessToken", "");
        $this->RegisterPropertyString("RefreshToken", "");
        $this->RegisterPropertyString("UserID", "");
		$this->RegisterPropertyBoolean("Activityaktiv" , false);  	// in V3.0 ist das  Activity aktiv
		$this->RegisterPropertyBoolean("Notifyaktiv" , false);  	// in V4.0 ist das Notify aktiv
		
		$this->RegisterPropertyInteger("Notify1", 0);
        $this->RegisterPropertyInteger("Notify4", 0);
        $this->RegisterPropertyInteger("Notify16", 0);
        $this->RegisterPropertyInteger("Notify44", 0);
		$this->RegisterPropertyInteger("Notify46", 0);
		$this->RegisterPropertyInteger("NotifyAlarm", 0);
		$this->RegisterPropertyString("CallbackURL", "");
                
                }

	//******************************************************************************
	// Register alle Profile
	//******************************************************************************
	protected function RegisterAllProfile()
		{
			
		$this->RegisterProfile(1,"WITHINGS_M_Groesse"  ,"Gauge"  ,""," cm");
		$this->RegisterProfile(1,"WITHINGS_M_Puls"     ,"Graph"  ,""," bpm");
    	$this->RegisterProfile(1,"WITHINGS_M_Atmung"   ,"Graph"  ,""," Atemzuege/Minute");
		$this->RegisterProfile(2,"WITHINGS_M_Kilo"     ,""       ,""," kg",false,false,false,1);
		$this->RegisterProfile(2,"WITHINGS_M_Prozent"  ,""       ,""," %",false,false,false,1);
		$this->RegisterProfile(2,"WITHINGS_M_BMI"      ,""       ,""," kg/m²",false,false,false,1);
		$this->RegisterProfile(1,"WITHINGS_M_Blutdruck","",""," mmHg");

		$this->RegisterProfileGender("WITHINGS_M_Gender", "", "", "", Array(
										Array(0, "maennlich",  "", 0x0000FF),
										Array(1, "weiblich",   "", 0xFF0000)
										));

		$this->RegisterProfileBatterie("WITHINGS_M_Batterie", "", "", "", Array(
										Array(0, "Schwach < 30%",  "", 0xFF0000),
										Array(1, "Mittel > 30%",   "", 0xFFFF00),
										Array(2, "Gut > 75%",      "", 0x00FF00)
										));

		$this->RegisterProfile(1,"WITHINGS_M_Minuten","",""," Minuten");

		$this->RegisterProfile(1,"WITHINGS_M_Schritte","",""," Schritte");
		$this->RegisterProfile(1,"WITHINGS_M_Anzahl","","","");
		
		$this->RegisterProfile(2,"WITHINGS_M_Kalorien","",""," kcal",false,false,false,2);
		$this->RegisterProfile(2,"WITHINGS_M_Meter","",""," Meter",false,false,false,2);
		$this->RegisterProfile(1,"WITHINGS_M_SPO2","",""," %");
										 

		}


	//**************************************************************************
	//	Aenderungen der Einstellungen uebernehmen
	//**************************************************************************    
	public function ApplyChanges()
		{
		//Never delete this line!
		parent::ApplyChanges();

		$this->RegisterAllProfile();

		$id = $this->RegisterVariableString( "name"      , "Name"      ,"",0);
		$id = $this->RegisterVariableInteger("gender"    , "Geschlecht","WITHINGS_M_Gender",2);
		$id = $this->RegisterVariableString( "birthdate" , "Geburtstag","",1);
		$id = $this->RegisterVariableInteger("height"    , "Groesse"   ,"WITHINGS_M_Groesse" ,3);

		//Timer stellen
		$interval = $this->ReadPropertyInteger("Intervall") * 1000 ;
		$this->SetTimerInterval("WIT_UpdateTimer", $interval);

		if ( $this->ReadPropertyBoolean("Modulaktiv") == false )
			{
			$this->SetStatus(104);	
			return;
			}
		else
			$this->SetStatus(102);

		}

	//**************************************************************************
	// Authentifizierung ueber OAuth2
	//**************************************************************************    
	public function Authentifizierung()
		{
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Starte Webseite zum einloggen bei Withings",0);	
		$url = "https://oauth.ipmagic.de/authorize/withings?username=".urlencode(IPS_GetLicensee());
		$this->RegisterOAuth('withings');
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',$url,0);

		return $url;
		}
		
	//**************************************************************************
	// manuelles Holen der Daten oder ueber Timer
	//**************************************************************************
	public function Update()
		{
		if ( $this->ReadPropertyBoolean("Modulaktiv") == false )
        	{
			$this->SetStatus(104);	
            return;
            }
		else
			$this->SetStatus(102);
		
		//set_time_limit (5 * 60);	// disabled wegen 5.6
         
		$NotifiyListArray = array();

        $starttime = time();            
		
		$this->Logging("Update");
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"",0);
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Update Data START",0);
		
		if ( $this->RefreshTokens() == FALSE )
            {
			$this->RunAlarmScript($this->InstanceID,"Kein Zugang zu Withings-Server");
            return;
            }
		
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Update Data Get Device",0);
		$this->GetDevice();   
                
        $this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Update Data Get Meas",0);
		$this->GetMeas();
		
        $this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Update Data Get Sleep",0);
		$this->GetSleepSummary();

        $this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Update Data Get Activity",0);
		$this->GetActivity();

        $this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Update Data Get Intra",0);
		$this->GetIntradayactivity();
		         
		$this->SubscribeHook();

		$this->GetNotifyList(1);
    	$this->GetNotifyList(2);
		$this->GetNotifyList(4);
		$this->GetNotifyList(16);
		$this->GetNotifyList(44);
		$this->GetNotifyList(46);
    	$this->GetNotifyList(50);
    	$this->GetNotifyList(51);
		
		if ( $this->ReadPropertyBoolean("Notifyaktiv") == false )
			{
			$this->DoNotifyRevokeAll();	
			}
		else
			$this->GetNotifySubscribe();	

		// $this->GetNonce();

		$endtime = time();
		
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Update Data Laufzeit ".($endtime - $starttime) . " Sekunden" ,0);
        
		}

	//******************************************************************************
	//	Erstelle Hook
	//******************************************************************************
	protected function SubscribeHook()
		{
		$WebHook = "/hook/Withings".$this->InstanceID;

		$ids = IPS_GetInstanceListByModuleID('{015A6EB8-D6E5-4B93-B496-0D3F77AE9FE1}');
		if (count($ids) > 0) 
			{
			$hooks = json_decode(IPS_GetProperty($ids[0], 'Hooks'), true);
			$found = false;
			foreach ($hooks as $index => $hook) 
				{
				if ($hook['Hook'] == $WebHook) 
					{
					if ($hook['TargetID'] == $this->InstanceID) 
						{
						// $this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Hook bereits vorhanden : ". $hook['TargetID'], 0);
						return;		// bereits vorhanden
						}
					$hooks[$index]['TargetID'] = $this->InstanceID;
					$found = true;
					}
				}
				
				if (!$found) 
					{
					$hooks[] = ['Hook' => $WebHook, 'TargetID' => $this->InstanceID];
					}
				$this->SendDebug(__FUNCTION__.'['.__LINE__.']', $WebHook ." erstellt" , 0);
				IPS_SetProperty($ids[0], 'Hooks', json_encode($hooks));
				IPS_ApplyChanges($ids[0]);
			}
		}

	//******************************************************************************
	// Hook loeschen
	//******************************************************************************
	protected function UnregisterHook()
		{
		$WebHook = "/hook/Withings".$this->InstanceID;

		$ids = IPS_GetInstanceListByModuleID('{015A6EB8-D6E5-4B93-B496-0D3F77AE9FE1}');
		if (count($ids) > 0) 
			{
			$hooks = json_decode(IPS_GetProperty($ids[0], 'Hooks'), true);
			$found = false;
			foreach ($hooks as $index => $hook) 
				{
				if ($hook['Hook'] == $WebHook) 
					{
					$found = $index;
					break;
					}
				}
	
			if ($found !== false) 
				{
				array_splice($hooks, $index, 1);
				IPS_SetProperty($ids[0], 'Hooks', json_encode($hooks));
				IPS_ApplyChanges($ids[0]);
				}
			}
		}


	//******************************************************************************
	//	Getdevice
	//******************************************************************************
	protected function GetDevice()
		{

		$access_token = $this->ReadPropertyString("Userpassword");

		$url = "https://wbsapi.withings.net/v2/user?action=getdevice&access_token=".$access_token;

		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',$url,0);
		$this->Logging("GetDevice");
		$this->Logging($url);

		$output = $this->DoCurl($url);

		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',$output,0);

		$this->Logging($output);
		
		$this->LoggingExt($output,"device.log",false,false);

		$data = json_decode($output,TRUE); 

		if ( !array_key_exists('status',$data) == TRUE )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Status: unbekannt",0);
			return;
			}

		$id = $this->GetIDForIdent("name");

		$ModulID = IPS_GetParent($id);

		$DoData = $data['body'];

		$this->DoDevice($ModulID,$DoData);

		}

	//******************************************************************************
	//	Getmeas
	//******************************************************************************
	protected function GetMeas()
		{

		if ( $this->ReadPropertyBoolean("BodyMeasures") == false AND $this->ReadPropertyBoolean("BloodMeasures") == false)
        	{   
            return;
            }

		$access_token = $this->ReadPropertyString("Userpassword");

		$category = 1;

		$tage = 5;
        $startdate = time()- 24*60*60*$tage;
		$enddate = time();

		$url = "https://wbsapi.withings.net/measure?action=getmeas&access_token=".$access_token."&category=".$category."&startdate=".$startdate."&enddate=".$enddate;

		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',$url,0);

		$this->Logging("GetMeas");
		$this->Logging($url);

		$output = $this->DoCurl($url);
		
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',$output,0);

		$this->Logging($output);
		$this->LoggingExt($output,"meas.log",false,false);

		$data = json_decode($output,TRUE); 

		if ( !array_key_exists('status',$data) == TRUE )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Status: unbekannt",0);
			return;
			}

		$status = $data['status'];

		if ( $status != 0)
        	{
            return;
            }

		$id = $this->GetIDForIdent("name");

		$ModulID = IPS_GetParent($id);

		$DoData = $data['body'];

		$this->DoMeas($ModulID,$DoData);

		}

	//******************************************************************************
	//  GetActivity
	//******************************************************************************
	protected function GetActivity()
		{

		if ( $this->ReadPropertyBoolean("BodyLogging") == false )
        	{
            return;
            }
                    
		$access_token = $this->ReadPropertyString("Userpassword");

		$tage = 5;
		$startdate = date("Y-m-d",time() - 24*60*60*$tage);
		$enddate   = date("Y-m-d",time());

		$url = "https://wbsapi.withings.net/v2/measure?action=getactivity&access_token=".$access_token."&startdateymd=".$startdate."&enddateymd=".$enddate;

		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',$url,0);

		$output = $this->DoCurl($url);

		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',$output,0);

		$this->LoggingExt($output);
		$this->LoggingExt($output,"activity.log",false,false);

		$data = json_decode($output,TRUE);

		if ( !array_key_exists('status',$data) == TRUE )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Status: unbekannt",0);
			return;
			}

		$status = $data['status'];


		if ( $status != 0)
            {
            return;
            }

		$id = $this->GetIDForIdent("name");

		$ModulID = IPS_GetParent($id);

		$DoData = $data['body'];

		$this->DoActivity($ModulID,$DoData);

		}

	//******************************************************************************
	//  Getintradayactivity
	//******************************************************************************
	protected function GetIntradayactivity()
		{

		if ( $this->ReadPropertyBoolean("BodyLogging") == false )
        	{
            return;
            }

		$access_token = $this->ReadPropertyString("Userpassword");

		$tage = 1;
		$startdate = time()- (24*60*60)*$tage  ;
		$enddate = time();

		$url = "https://wbsapi.withings.net/v2/measure?action=getintradayactivity&access_token=".$access_token."&startdate=".$startdate."&enddate=".$enddate;

		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',$url,0);
        $this->SendDebug(__FUNCTION__.'['.__LINE__.']',date('d.m.Y H:i:s ',$startdate)." - ".date('d.m.Y H:i:s ',$enddate),0);

		$output = $this->DoCurl($url);

		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',$output,0);

		$this->Logging($output);

		$this->LoggingExt($output,"intradayactivity.log",false,false);

		$data = json_decode($output,TRUE);

		if ( !array_key_exists('status',$data) == TRUE )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Status: unbekannt",0);
			return;
			}

		$status = $data['status'];

		if ( $status != 0)
        	{
            return;
            }
                    
		$id = $this->GetIDForIdent("name");

		$ModulID = IPS_GetParent($id);

		$data = $data['body'];

		$this->DoGetintradayactivity($ModulID,$data);

		}


	//******************************************************************************
	//	GetSleepSummary
	//******************************************************************************
	protected function GetSleepSummary()
		{

		if ( $this->ReadPropertyBoolean("BloodLogging") == false )
			return;

		$access_token = $this->ReadPropertyString("Userpassword");

		$tage = 5;
		$startdate = time() - 24*60*60*$tage;
		$enddate   = time();

		$startdate = date("Y-m-d",$startdate);
		$enddate   = date("Y-m-d",$enddate);

		$datafields = "wakeupduration,lightsleepduration,deepsleepduration,remsleepduration,wakeupcount,durationtosleep,durationtowakeup,hr_average,hr_min,hr_max,rr_average,rr_min,rr_max";

		$url = "https://wbsapi.withings.net/v2/sleep?action=getsummary&access_token=".$access_token."&startdateymd=".$startdate."&enddateymd=".$enddate."&data_fields=".$datafields;

		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',$url,0);

		$output = $this->DoCurl($url);

		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',$output,0);

		$this->Logging($output);
		$this->LoggingExt($output,"sleep.log",false,false);

		$data = json_decode($output,TRUE); 

		if ( !array_key_exists('status',$data) == TRUE )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Status: unbekannt",0);
			return;
			}

		$status = $data['status'];

		$id = $this->GetIDForIdent("name");

		$ModulID = IPS_GetParent($id);

		$data = $data['body'];

		$this->DoSleepSummary($ModulID,$data);

		}

	//**************************************************************************
	// manuelles Refresh der Tokens
	// benutzt gespeichertes RefreshToken um neues Access/Refresh Token zu holen
	//**************************************************************************    
	public function RefreshTokens()
		{
		$status = $this->FetchAccessToken();
		return $status;
		}

	//**************************************************************************
	//	Instanz loeschen
	//**************************************************************************    
	public function Destroy()
		{
		$this->UnregisterTimer("WIT_UpdateTimer");

		$this->UnregisterHook();

		//Never delete this line!
		parent::Destroy();
		}

	//**************************************************************************
	//	Gender Profil erstellen
	//**************************************************************************    
	protected function RegisterProfileGender($Name, $Icon, $Prefix, $Suffix, $Associations) 
		{
		if ( sizeof($Associations) === 0 )
			{
			$MinValue = 0;
			$MaxValue = 0;
			}
		else 
			{
			$MinValue = $Associations[0][0];
			$MaxValue = $Associations[sizeof($Associations)-1][0];
			}

		$this->RegisterProfile(1,$Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, 0);

		foreach($Associations as $Association) 
			{
			IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
			}

		}

	//**************************************************************************
	//	Batterie Profil erstellen 
	//**************************************************************************    
	protected function RegisterProfileBatterie($Name, $Icon, $Prefix, $Suffix, $Associations) 
		{
		if ( sizeof($Associations) === 0 )
			{
			$MinValue = 0;
			$MaxValue = 0;
			}
		else 
			{
			$MinValue = $Associations[0][0];
			$MaxValue = $Associations[sizeof($Associations)-1][0];
			}

		$this->RegisterProfile(1,$Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, 0);

		foreach($Associations as $Association) 
			{
			IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
			}

		}

	//**************************************************************************
	//  Create Dummy Instance
	//**************************************************************************
	private function CreateDummyInstance($Name,$Ident,$DeviceTyp)
		{
		
		$ModulID = $this->InstanceID;
		$InsID = IPS_CreateInstance("{485D0419-BE97-4548-AA9C-C083EB82E61E}");
		IPS_SetName($InsID, $Name);
		IPS_SetParent($InsID, $ModulID);
		IPS_SetIdent ( $InsID, $Ident );
		IPS_SetInfo ( $InsID, $DeviceTyp );
		IPS_ApplyChanges($InsID);

		return $InsID ;
		}


	//**************************************************************************
	//  Create Kategorie / ueberprueft ob Kategorie besteht
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
	//  erweitertes Logging
	//**************************************************************************
	private function LoggingExt($Text,$file="WithingsExt.log",$delete=false,$date=true)
		{
		if ( $this->ReadPropertyBoolean("Logging") == false )
			return;

		$ordner = IPS_GetLogDir() . "Withings";
		if ( !is_dir ( $ordner ) )
			mkdir($ordner);

		if ( !is_dir ( $ordner ) )
			return;
                
		$time = date("d.m.Y H:i:s");
		$logdatei = IPS_GetLogDir() . "Withings/".$file;
		
                if ( $delete == true )
                    @unlink($logdatei);
                
				$datei = fopen($logdatei,"a+");
		
		if ( $date == true )		
			fwrite($datei, $time ." ". $Text . chr(13));
		else
			fwrite($datei,$Text . chr(13));
		
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
				{
				IPS_Logmessage("Withingsmodul","Profil falsch : " . $Name);
				//throw new Exception("Variable profile type does not match for profile ".$Name);

				}
			}

		IPS_SetVariableProfileIcon($Name, $Icon);
		IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
		IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
 
		if ( $Typ == 2 )
			IPS_SetVariableProfileDigits($Name, $Digits);
		}

	
	//**************************************************************************
	//	Timer loeschen
	//**************************************************************************    
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

	//**************************************************************************
	//
	//**************************************************************************    
	protected function SetTimerIntervalOld($Name, $Interval)
		{

		return;

		}

	//**************************************************************************
	//	Auswertung SleepSummary
	//**************************************************************************
	 protected function DoSleepSummary($ModulID,$data)
		{
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Schlaf wird ausgewertet.",0);
		
		$InstanceIDSleep = @$this->GetIDForIdent("SleepMonitor");
		if ( $InstanceIDSleep === false )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"InstanceID Sleep nicht vorhanden. Abbruch",0);
			return;	
			}
		
		$data 		= @$data['series'];
		//$deviceid	= @$device['deviceid'];

		if ( $data == false )
			return;

		if ( count($data) == 0 )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Keine Schlafdaten gefunden. Abbruch",0);				
			return false;
			}
		else
			{
			$measuregrpsstart =  @$data[0]['date'];
			$measuregrpsstart = $this->DateToTimestamp($measuregrpsstart);
			$measuregrpsstart = $this->TimestampToDate($measuregrpsstart);	
			$measuregrpsende = @$data[count($data)-1]['date'];
			$measuregrpsende = $this->DateToTimestamp($measuregrpsende);
			$measuregrpsende = $this->TimestampToDate($measuregrpsende);	
				
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Anzahl der Measuregrps : ".count($data) . " von " . $measuregrpsstart. " bis " . $measuregrpsende,0);	
			
			}

		$RequestReAggregation = false;

		// Bei diesen Daten ist der neueste als letztes
		foreach($data as $sleep)
			{
			$sleepmodel      = @$sleep['model'];
			$sleepstartdate  = @$sleep['startdate'];
			$sleependdate    = @$sleep['enddate'];
			$sleepmodified   = @$sleep['modified'];
			$sleepdauer      = $sleependdate -$sleepstartdate;


			$sleepwakeupduration    = @$sleep['data']['wakeupduration'];
			$sleeplightsleepduration= @$sleep['data']['lightsleepduration'];
			$sleepdeepsleepduration = @$sleep['data']['deepsleepduration'];
			$sleepwakeupcount       = @$sleep['data']['wakeupcount'];
			$sleepdurationtosleep   = @$sleep['data']['durationtosleep'];
			$sleepdurationtowakeup  = @$sleep['data']['durationtowakeup'];
			$sleepremduration       = @$sleep['data']['remsleepduration'];
			$sleephraverage         = @$sleep['data']['hr_average'];
			$sleephrmin         	= @$sleep['data']['hr_min'];
			$sleephrmax             = @$sleep['data']['hr_max'];
			$sleeprraverage         = @$sleep['data']['rr_average'];
			$sleeprrmin         	= @$sleep['data']['rr_min'];
			$sleeprrmax             = @$sleep['data']['rr_max'];



/*
		$this->SendDebug("DoSleepSummary","Modell : ".$sleepmodel,0);
		$this->SendDebug("DoSleepSummary","Startdatum : ".date("d-m-Y H:i:s",$sleepstartdate),0);
		$this->SendDebug("DoSleepSummary","Enddatum : "  .date("d-m-Y H:i:s",$sleependdate),0);
		$this->SendDebug("DoSleepSummary","Modifieddatum : "  .date("d-m-Y H:i:s",$sleepmodified),0);
		$this->SendDebug("DoSleepSummary","Schlafdauer : "  . $this->FormatTimeMinuten($sleepdauer),0);
		$this->SendDebug("DoSleepSummary","Wachphasen : "           .$this->FormatTimeMinuten($sleepwakeupduration). " Minuten",0);
		$this->SendDebug("DoSleepSummary","Leichtschlafphasen : "   .$this->FormatTimeMinuten($sleeplightsleepduration)." Minuten",0);
		$this->SendDebug("DoSleepSummary","Tiefschlafphasen : "     .$this->FormatTimeMinuten($sleepdeepsleepduration) . " Minuten",0);
		$this->SendDebug("DoSleepSummary","Schlafunterbrechungen : ".$sleepwakeupcount,0);
		$this->SendDebug("DoSleepSummary","Einschlafzeit : "        .$this->FormatTimeMinuten($sleepdurationtosleep) . " Minuten",0);
		$this->SendDebug("DoSleepSummary","Aufstehzeit : "          .$this->FormatTimeMinuten($sleepdurationtowakeup) . " Minuten",0);
	*/
		  }
		  

		// Nicht asynchron schreiben  
		if(isset($sleepstartdate))			$this->SetValueToVariable($InstanceIDSleep,"Startzeit"              ,$sleepstartdate                ,"~UnixTimestamp"       ,1  ,false,false,"startzeit");
		if(isset($sleependdate))			$this->SetValueToVariable($InstanceIDSleep,"Endezeit"               ,$sleependdate                  ,"~UnixTimestamp"       ,2  ,false,false,"endezeit");
		if(isset($sleepmodified))			$this->SetValueToVariable($InstanceIDSleep,"Updatezeit"             ,$sleepmodified                 ,"~UnixTimestamp"       ,0 	,false,false,"timestamp");
		if(isset($sleepdauer))				$this->SetValueToVariable($InstanceIDSleep,"Schlafdauer"            ,$sleepdauer/60                 ,"WITHINGS_M_Minuten"   ,3  ,false,false,"schlafdauer");
		if(isset($sleepwakeupduration))		$this->SetValueToVariable($InstanceIDSleep,"Wachphasen"             ,$sleepwakeupduration/60        ,"WITHINGS_M_Minuten"   ,8  ,false,false,"wachphasen");
		if(isset($sleeplightsleepduration))	$this->SetValueToVariable($InstanceIDSleep,"Leichtschlafphasen"     ,$sleeplightsleepduration/60	,"WITHINGS_M_Minuten"   ,6  ,false,false,"leichtschlafphasen");
		if(isset($sleepdeepsleepduration))	$this->SetValueToVariable($InstanceIDSleep,"Tiefschlafphasen"       ,$sleepdeepsleepduration/60     ,"WITHINGS_M_Minuten"   ,7  ,false,false,"tiefschlafphasen");
		if(isset($sleepwakeupcount))		$this->SetValueToVariable($InstanceIDSleep,"Schlafunterbrechungen"  ,$sleepwakeupcount              ,"WITHINGS_M_Anzahl"    ,9  ,false,false,"schlafunterbrechungen");
		if(isset($sleepdurationtosleep))	$this->SetValueToVariable($InstanceIDSleep,"Einschlafzeit"          ,$sleepdurationtosleep/60       ,"WITHINGS_M_Minuten"   ,4  ,false,false,"einschlafzeit");
		if(isset($sleepdurationtowakeup))	$this->SetValueToVariable($InstanceIDSleep,"Aufstehzeit"            ,$sleepdurationtowakeup/60      ,"WITHINGS_M_Minuten"   ,5  ,false,false,"aufstehzeit");
		if(isset($sleepremduration))		$this->SetValueToVariable($InstanceIDSleep,"REMschlafphasen"        ,$sleepremduration/60      		,"WITHINGS_M_Minuten"   ,7	,false,false,"remschlafphasen");
		if(isset($sleephraverage))			$this->SetValueToVariable($InstanceIDSleep,"Herzschlag Durchschnitt",$sleephraverage      			,"WITHINGS_M_Puls"   	,10 ,false,false,"herzschlagdurchschnitt");
		if(isset($sleephrmin))				$this->SetValueToVariable($InstanceIDSleep,"Herzschlag Minimal"     ,$sleephrmin      				,"WITHINGS_M_Puls"   	,11 ,false,false,"herzschlagminimal");
		if(isset($sleephrmax))				$this->SetValueToVariable($InstanceIDSleep,"Herzschlag Maximal"     ,$sleephrmax      				,"WITHINGS_M_Puls"   	,12 ,false,false,"herzschlagmaximal");
		if(isset($sleeprraverage))			$this->SetValueToVariable($InstanceIDSleep,"Atmung Durchschnitt"    ,$sleeprraverage      			,"WITHINGS_M_Atmung"   	,15 ,false,false,"atemzuegedurchschnitt");
		if(isset($sleeprrmin))				$this->SetValueToVariable($InstanceIDSleep,"Atmung Minimal"        	,$sleeprrmin      				,"WITHINGS_M_Atmung"   	,16 ,false,false,"atemzuegeminimal");
		if(isset($sleeprrmax))				$this->SetValueToVariable($InstanceIDSleep,"Atmung Maximal"        	,$sleeprrmax      				,"WITHINGS_M_Atmung"   	,17 ,false,false,"atemzuegemaximal");

		
		if ( $RequestReAggregation == true )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"RequestReAggregation erforderlich",0);
			}	
		else
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"RequestReAggregation nicht erforderlich",0);
			}			


	}


	//**************************************************************************
	//	Auswertung Activity
	//**************************************************************************
	 protected function DoActivity($ModulID,$data)
		{
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Aktivitaeten werden ausgewertet.",0);

		// "Activity" isdas Ident fuer externe Daten in Withings deviceid = null
		$InstanceIDActivity = @$this->GetIDForIdent("Activity");
		if ( $InstanceIDActivity === FALSE )
			$InstanceIDActivity = $this->CreateDummyInstance("Activity","Activity","Daten von externen APPs");

		if ( $InstanceIDActivity === false )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"InstanceID Activity nicht vorhanden. Abbruch",0);
			return;
			}

		$data 		= @$data['activities'];
		//$deviceid	= @$device['deviceid'];

		if ( @count($data) == 0 )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Keine Activity gefunden. Abbruch",0);
			return false;
			}
		else
			{

			$measuregrpsstart =  @$data[0]['date'];
			$measuregrpsstart = $this->DateToTimestamp($measuregrpsstart);
			$measuregrpsstart = $this->TimestampToDate($measuregrpsstart);	
			$measuregrpsende = @$data[count($data)-1]['date'];
			$measuregrpsende = $this->DateToTimestamp($measuregrpsende);
			$measuregrpsende = $this->TimestampToDate($measuregrpsende);	
					
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Anzahl der Measuregrps : ".count($data) . " von " . $measuregrpsstart. " bis " . $measuregrpsende,0);	
			}
		
		$RequestReAggregation = false;	
			
		// Bei diesen Daten ist der neueste als letztes
		foreach($data as $activity)
			{
			$activitydate			= @$activity['date'];
			$activitysteps			= @$activity['steps'];
			$activitydistance		= @$activity['distance'];
			$activityelevation		= @$activity['elevation'];
			$activitysoft			= @$activity['soft'];
			$activitymoderate		= @$activity['moderate'];
			$activityintense		= @$activity['intense'];
			$activitycalories		= @$activity['calories'];
			$activitytotalcalories  = @$activity['totalcalories'];
			$activitybrand			= @$activity['brand'];

            /*
			$this->SendDebug("DoActivity","Datum : ".		$activitydate,0);
			$this->SendDebug("DoActivity","Schritte : ".		$activitysteps,0);
			$this->SendDebug("DoActivity","Distanze : ".		$activitydistance,0);
			$this->SendDebug("DoActivity","Hoehe : ".		$activityelevation,0);
			$this->SendDebug("DoActivity","Soft : ".		$activitysoft,0);
			$this->SendDebug("DoActivity","Moderate : ".		$activitymoderate,0);
			$this->SendDebug("DoActivity","Intense : ".		$activityintense,0);
			$this->SendDebug("DoActivity","Kalorien : ".		$activitycalories,0);
			$this->SendDebug("DoActivity","Gesamtkalorien : ".	$activitytotalcalories,0);
			$this->SendDebug("DoActivity","Brand : ".		$activitybrand,0);
            */

			$timestamp = intval(strtotime ($activitydate));

			// Daten asynchron in Datenbank schreiben
			/*
			if(isset($activitydate))					$this->SetValueToVariable($InstanceIDActivity,"Updatezeit"					,intval(strtotime ($activitydate))		,"~UnixTimestamp"				,1	,true,$timestamp);
			if(isset($activitysteps))					$this->SetValueToVariable($InstanceIDActivity,"Schritte"						,intval($activitysteps)								,"WITHINGS_M_Schritte"	,2	,true,$timestamp);
			if(isset($activitydistance))			$this->SetValueToVariable($InstanceIDActivity,"Distanze"						,intval($activitydistance)						,"WITHINGS_M_Meter"			,3	,true,$timestamp);
			if(isset($activityelevation))			$this->SetValueToVariable($InstanceIDActivity,"Hoehenmeter"					,intval($activityelevation)						,"WITHINGS_M_Meter"			,4	,true,$timestamp);
			if(isset($activitycalories))			$this->SetValueToVariable($InstanceIDActivity,"Aktivitaetskalorien"	,intval($activitycalories)						,"WITHINGS_M_Kalorien"	,10	,true,$timestamp);
			if(isset($activitytotalcalories))	$this->SetValueToVariable($InstanceIDActivity,"Gesamtkalorien"			,intval($activitytotalcalories)				,"WITHINGS_M_Kalorien"	,11	,true,$timestamp);
			if(isset($activitysoft))					$this->SetValueToVariable($InstanceIDActivity,"Geringe Aktivitaet"	,intval($activitysoft/60 )						,"WITHINGS_M_Minuten"		,20	,true,$timestamp);
			if(isset($activitymoderate))			$this->SetValueToVariable($InstanceIDActivity,"Mittlere Aktivitaet"	,intval($activitymoderate/60 )				,"WITHINGS_M_Minuten"		,21	,true,$timestamp);
			if(isset($activityintense))				$this->SetValueToVariable($InstanceIDActivity,"Hohe Aktivitaet"			,intval($activityintense/60 )					,"WITHINGS_M_Minuten"		,22	,true,$timestamp);
			*/
			}

		// letzte Daten in Variable schreiben nicht asynchron
		if(isset($activitydate))                $this->SetValueToVariable($InstanceIDActivity,"Updatezeit"			,intval(strtotime ($activitydate))  ,"~UnixTimestamp"       ,1  ,false,false,"timestamp"	);
		if(isset($activitysteps))               $this->SetValueToVariable($InstanceIDActivity,"Schritte"			,intval($activitysteps)             ,"WITHINGS_M_Schritte"  ,2	,false,false,"schritte");
		if(isset($activitydistance))            $this->SetValueToVariable($InstanceIDActivity,"Distanze"			,floatval($activitydistance)        ,"WITHINGS_M_Meter"     ,3	,false,false,"distanze");
		if(isset($activityelevation))           $this->SetValueToVariable($InstanceIDActivity,"Hoehenmeter"			,floatval($activityelevation)       ,"WITHINGS_M_Meter"     ,4	,false,false,"hoehenmeter");
		if(isset($activitycalories))            $this->SetValueToVariable($InstanceIDActivity,"Aktivitaetskalorien"	,floatval($activitycalories)        ,"WITHINGS_M_Kalorien"  ,10	,false,false,"aktivitaetskalorien");
		if(isset($activitytotalcalories))       $this->SetValueToVariable($InstanceIDActivity,"Gesamtkalorien"		,floatval($activitytotalcalories)   ,"WITHINGS_M_Kalorien"  ,11	,false,false,"gesamtkalorien");
		if(isset($activitysoft))                $this->SetValueToVariable($InstanceIDActivity,"Geringe Aktivitaet"	,intval($activitysoft/60 )          ,"WITHINGS_M_Minuten"   ,20	,false,false,"geringeaktivitaet");
		if(isset($activitymoderate))            $this->SetValueToVariable($InstanceIDActivity,"Mittlere Aktivitaet"	,intval($activitymoderate/60 )      ,"WITHINGS_M_Minuten"   ,21	,false,false,"mittlereaktivitaet");
		if(isset($activityintense))             $this->SetValueToVariable($InstanceIDActivity,"Hohe Aktivitaet"		,intval($activityintense/60 )       ,"WITHINGS_M_Minuten"   ,22	,false,false,"hoheaktivitaet");


    	// $this->Reaggregieren($InstanceIDActivity);

		if ( $RequestReAggregation == true )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"RequestReAggregation erforderlich",0);
			}	
		else
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"RequestReAggregation nicht erforderlich",0);
			}			



	}

	//**************************************************************************
	//	Auswertung Intradayactivity
	//**************************************************************************
	 protected function DoGetintradayactivity($ModulID,$data)
		{
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Aktivitaeten werden ausgewertet.",0);

		// "IntraDayActivity" isdas Ident fuer externe Daten in Withings deviceid = null
		$InstanceIDActivity = @$this->GetIDForIdent ("IntraDayActivity");
		if ( $InstanceIDActivity === FALSE )
			$InstanceIDActivity = $this->CreateDummyInstance("IntraDayActivity","IntraDayActivity","Daten von externen APPs");

		if ( $InstanceIDActivity === false )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"InstanceID IntraDayActivity nicht vorhanden. Abbruch",0);
			return;
			}


		$data 		= @$data['series'];

		if ( @count($data) == 0 )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Keine Activity gefunden. Abbruch",0);
			return false;
			}
		else
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Anzahl der Serien : ".count($data),0);

		$RequestReAggregation = false;

		// Bei diesen Daten ist der neueste als letztes ???
		$keys = array_keys($data);
                
        $this->LoggingExt("Start",$file="WithingsExt2.log",true );
                 
		foreach($keys as $key)
			{ 
			// $data[$key]['spo2_auto'] = 10;
			$activitydate = date('d.m.Y H:i:s ',$key);;
                        
			$activitycalories	= @$data[$key]['calories'];
			$activitydistance	= @$data[$key]['distance'];
			$activityduration	= @$data[$key]['duration'];
			$activityelevation	= @$data[$key]['elevation'];
			$activitysteps		= @$data[$key]['steps'];
			$activitystroke		= @$data[$key]['stroke'];
			$activitypoollap	= @$data[$key]['pool_lap'];
			$activityheartrate	= @$data[$key]['heart_rate'];
			$activityspo2		= @$data[$key]['spo2_auto'];

			/*
			if ( isset($data[$key]['spo2_auto']) == false )
				$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"spo2_auto: Not set",0);
			else	
				$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"spo2_auto: Set ".$data[$key]['spo2_auto'] ,0);
			*/
			/*
			$this->SendDebug("DoGetintradayactivity","Datum : ".					$activitydate,0);
			$this->SendDebug("DoGetintradayactivity","Schritte : ".				$activitysteps,0);

			$this->SendDebug("DoGetintradayactivity","Kalorien : ".				$activitycalories,0);
			$this->SendDebug("DoGetintradayactivity","Distanze : ".				$activitydistance,0);
			$this->SendDebug("DoGetintradayactivity","Duration : ".				$activityduration,0);
			$this->SendDebug("DoGetintradayactivity","Hoehe : ".					$activityelevation,0);
			$this->SendDebug("DoGetintradayactivity","Stroke : ".					$activitystroke,0);
			$this->SendDebug("DoGetintradayactivity","Pool Lap : ".				$activitypoollap,0);
            */
                        
			$Text =$activitydate."-".$activitysteps;
            
            $this->LoggingExt($Text,$file="WithingsExt2.log");

			// if(isset($activitydate))	$this->SetValueToVariable($InstanceIDActivity,"Updatezeit"	,intval($key)                       ,"~UnixTimestamp"		,1	,true,$key,"timestamp");
			if(isset($activitycalories))	$this->SetValueToVariable($InstanceIDActivity,"Kalorien"	,floatval($activitycalories)    ,"WITHINGS_M_Kalorien"	,10	,true,$key,"kalorien");
			if(isset($activitydistance))	$this->SetValueToVariable($InstanceIDActivity,"Distanze"	,floatval($activitydistance)    ,"WITHINGS_M_Meter"     ,3	,true,$key,"distanze");
			if(isset($activityelevation))	$this->SetValueToVariable($InstanceIDActivity,"Hoehenmeter"	,floatval($activityelevation)   ,"WITHINGS_M_Meter"		,4	,true,$key,"hoehenmeter");
			if(isset($activitysteps))		$this->SetValueToVariable($InstanceIDActivity,"Schritte"	,intval($activitysteps)         ,"WITHINGS_M_Schritte"	,11	,true,$key,"schritte");
			if(isset($activityheartrate))	$this->SetValueToVariable($InstanceIDActivity,"Puls"		,intval($activityheartrate)     ,"WITHINGS_M_Puls"		,12	,true,$key,"puls");
			if(isset($activityspo2))		$this->SetValueToVariable($InstanceIDActivity,"SpO2"		,intval($activityspo2)     		,"WITHINGS_M_SPO2"			,12	,true,$key,"spo2");
					

			}

		// letzte Daten in Variable schreiben ( besser vielleicht nicht) besser ohne Logging machen
                
		if(isset($activitydate))		$this->SetValueToVariable($InstanceIDActivity,"Updatezeit"	,intval(strtotime ($activitydate))    ,"~UnixTimestamp"			,1	,false,false,"timestamp");
		//if(isset($activitycalories))	$this->SetValueToVariable($InstanceIDActivity,"Kalorien"	,floatval($activitycalories)          ,"WITHINGS_M_Kalorien"	,10	,false,false,"kalorien");
		//if(isset($activitydistance))	$this->SetValueToVariable($InstanceIDActivity,"Distanze"	,floatval($activitydistance)          ,"WITHINGS_M_Meter"		,3	,false,false,"distanze");
		//if(isset($activityelevation))	$this->SetValueToVariable($InstanceIDActivity,"Hoehenmeter"	,floatval($activityelevation)         ,"WITHINGS_M_Meter"		,4	,false,false,"hoehenmeter");
		//if(isset($activitysteps))		$this->SetValueToVariable($InstanceIDActivity,"Schritte"	,intval($activitysteps)               ,"WITHINGS_M_Schritte"	,11	,false,false,"schritte");
                
		//$this->Reaggregieren($InstanceIDActivity);
		
		if ( $RequestReAggregation == true )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"RequestReAggregation erforderlich",0);
			}	
		else
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"RequestReAggregation nicht erforderlich",0);
			}			

		}

	//**************************************************************************
	// Auswertung Geraeteinfos
	//**************************************************************************
	protected function DoDevice($ModulID,$data)
		{
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Devices werden ausgewertet.",0);

		$data = @$data['devices'];
		if ( count($data) == 0  )
			{
			$this->Logging("Fehler bei DoDevice ".count($data));
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Keine Geraete gefunden. Abbruch",0);
			return;
			}
		
		foreach($data as $device)
			{
			$devicetyp		= @$device['type'];
			$devicemodel	= @$device['model'];
			$devicebattery	= @$device['battery'];
			$deviceid		= @$device['deviceid'];
			$devicetimezone	= @$device['timezone'];

			if ( is_null($deviceid) )
				$deviceid = "null";
			
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"[Deviceid : ".$deviceid."][Type : ".$devicetyp ."][Modell : ".$devicemodel."]",0);

			if ( $devicetyp == "Sleep Monitor")
				{
				$deviceid 	= str_replace(" ","",$devicetyp);
				$devicetyp 	= @$device['deviceid'];	
				}

			$ObjektID = @$this->GetIDForIdent ( $deviceid );
			if ( $ObjektID === FALSE )
				$ObjektID = $this->CreateDummyInstance($devicemodel,$deviceid,$devicetyp);
				
			if ( $devicebattery == 'low' OR $devicebattery == 'medium' OR $devicebattery == 'high')
				{
				$name = "batterie";

				$id = @IPS_GetObjectIDByIdent( $name, $ObjektID ) ;
                                                                
				if ( $id === FALSE )
					{
					$id = $this->RegisterVariableInteger("batterie", $name,"WITHINGS_M_Batterie",0);
					IPS_SetParent($id, $ObjektID);
					IPS_SetPosition($id, 1);
					}

				if ( $id > 0 )
					{
						
					IPS_SetPosition($id, 1);	
						
					if ( $devicebattery == 'low' )
						{
						$v =  GetValueInteger($id);
						if ( $v != 0)
							SetValueInteger($id,0);
						}
					if ( $devicebattery == 'medium' )
						{
						$v =  GetValueInteger($id);
						if ( $v != 1)
							SetValueInteger($id,1);
						}
					if ( $devicebattery == 'high' )
						{
						$v =  GetValueInteger($id);
						if ( $v != 2)
							SetValueInteger($id,2);
						}
					}
				}
			}
		}
	
	//**************************************************************************
	// Auswertung der Meas-Daten
	//**************************************************************************
	protected  function DoMeas($ModulID,$data)
		{

		$this->Logging("MeasDaten werden ausgewertet.");
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"MeasDaten werden ausgewertet.",0);

		$CatIdWaage = @IPS_GetCategoryIDByName("Waage",$ModulID);
		if ( $CatIdWaage === false )
			{
			$this->Logging("CatID ist auf FALSE!");
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"CatID Waage nicht vorhanden.",0);
			$CatIdWaage = $this->CreateKategorie("Waage",$ModulID);
			if ( $CatIdWaage === false )
				throw new Exception("Kategorie Waage nicht definiert");
			}

		$CatIdBlutdruck = @IPS_GetCategoryIDByName("Blutdruck",$ModulID);
		if ( $CatIdBlutdruck === false )
			{
			$this->Logging("CatID ist auf FALSE!");
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"CatID Blutdruck nicht vorhanden.",0);
			$CatIdBlutdruck = $this->CreateKategorie("Blutdruck",$ModulID);
			if ( $CatIdBlutdruck === false )
				throw new Exception("Kategorie Blutdruck nicht definiert");
			}

		$measuregrps = @$data['measuregrps'];

		if ( count($measuregrps) == 0 )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Keine Messgruppen gefunden. Abbruch",0);
			return false;
			}
		else
			{
			$measuregrpsstart =  @$measuregrps[count($measuregrps)-1]['date'];
			$measuregrpsstart = $this->TimestampToDate($measuregrpsstart);	
			$measuregrpsende = @$measuregrps[0]['date'];
			$measuregrpsende = $this->TimestampToDate($measuregrpsende);	
			
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Anzahl der Measuregrps : ".count($measuregrps) . " von " . $measuregrpsstart. " bis " . $measuregrpsende,0);	
			}

		$RequestReAggregation = true;

		// Neueste nach hinten
		$measuregrps = array_reverse ( $measuregrps  );

		$text = "Start";
		$this->LoggingExt($text,"Messungen.log",true);
		$arraydatas = array();

		// Alle Messgruppen durchgehen
		foreach($measuregrps as $daten)
			{
      		
			$time 		= @$daten['date'];
			$deviceid 	= @$daten['deviceid'];
			$messungen 	= @$daten['measures'];
      		$model 	    = @$daten['model'];

			$timestring = date("d.m.Y H:i:s",$time);
                                    
      		$InstanceIDDeviceID = @$this->GetIDForIdent($deviceid);

			if ($InstanceIDDeviceID == false )
				{
         		$this->SendDebug(__FUNCTION__.'['.__LINE__.']'," Keine ID gefunden Daten Handeingabe oder Withings API-Fehler : " . $deviceid . "--".$model." - ".$timestring ,0);
				continue ;
				}
			else
				{
			   // $this->SendDebug("DoMeas","ID gefunden : " . $InstanceIDDeviceID . "--".$model,0);
				}
				
			if ( @count($messungen) == 0 )
				{
				$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Keine Messungen gefunden. Weiter",0);
				continue;
				}

			// $this->SendDebug("DoMeas","DeviceID .: ".$timestring." - " . $deviceid,0);
			// Alle Messungen durchgehen
			// Neuste Messung am Ende
			
			foreach($messungen as $key => $messung)
				{
				
				$lastkey = false;	
				if ($key === array_key_last($messungen))
					$lastkey = true;	// fuer asynchron

				//if ( $lastkey == true )
				//	$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Messung Last : ".$messung['type'].date('l jS \of F Y h:i:s A',$time),0);

				$val = floatval ( $messung['value'] ) * floatval ( "1e".$messung['unit'] );
				
				//$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Messung Type : ".$messung['type']." : " .$val ."-".date('l jS \of F Y h:i:s A',$time),0);
				$text = date('d.m.Y H:i:s',$time) ." Messung Type : ".$messung['type']." : " .$val ;
				$this->LoggingExt($text,"Messungen.log");

				$arraydatas[$messung['type']][$InstanceIDDeviceID] = round($val,2);
				$arraytimes[$messung['type']][$InstanceIDDeviceID] = $time;

				switch ($messung['type'])
					{ 
					case 1 :	$gewicht = round ($val,2);
								$deviceIDwaage = $InstanceIDDeviceID;
								$TimestampWaage = $time;
								break;
					case 4 :	$groesse = round ($val,2);
								break;
					case 5 :	$fettfrei = round ($val,2);
								$deviceIDwaage = $InstanceIDDeviceID;
								$TimestampWaage = $time;
								break;
					case 6 :	$fettprozent = round ($val,2);
								$deviceIDwaage = $InstanceIDDeviceID;
								$TimestampWaage = $time;
								break;
					case 8 :	$fettanteil = round ($val,2);
								$deviceIDwaage = $InstanceIDDeviceID;
								$TimestampWaage = $time;
								break;
					case 9 :	$diastolic = $val;
								$deviceIDblutdruck  = $InstanceIDDeviceID;
								$TimestampBlutdruck = $time;
								break;
					case 10 :	$systolic = $val;
                                $deviceIDblutdruck  = $InstanceIDDeviceID;
								$TimestampBlutdruck = $time;
								break;
					case 11:	$puls = $val;
								break;
					case 12 :	$temperatur = round ($val,2);
								$deviceIDthermo = $InstanceIDDeviceID;
								$TimestampThermo = $time; 
								break;
					case 54 :	$sp02 = round ($val,2);
								$deviceIDwaage = $InstanceIDDeviceID;
								$TimestampWaage = $time;
								break;
                    case 71 :	$koerpertemperatur = round ($val,2); 
								$deviceIDthermo = $InstanceIDDeviceID; 
								$TimestampThermo = $time;
								break;
					case 73 :	$hauttemperatur = round ($val,2);
								$deviceIDthermo = $InstanceIDDeviceID;
								$TimestampThermo = $time; 
								break;
					case 76 :	$muskelmasse = round ($val,2);
								$deviceIDwaage = $InstanceIDDeviceID;
								$TimestampWaage = $time;
								break;
					case 77 :	$hydration = round ($val,2);
								$deviceIDwaage = $InstanceIDDeviceID;
								$TimestampWaage = $time;
								break;
					case 88 :	$knochenmasse = round ($val,2);
								$deviceIDwaage = $InstanceIDDeviceID;
								$TimestampWaage = $time;
								break;
					case 91 :	$pulswellen = round ($val,2);
								$deviceIDwaage = $InstanceIDDeviceID;
								$TimestampWaage = $time;
								break;
					default:	$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Messungstyp nicht vorhanden : ".$messung['type']."-".$val,0);
					}
				}
			}

		$id = @IPS_GetVariableIDByName("Groesse",$ModulID);
		$groesse = 0; 
		if ( $id > 0 )
			$groesse = GetValueInteger($id);

                foreach($arraydatas as $key => $arraydata)
                    {
                    foreach($arraydata as $deviceID => $arraydata2)
                        {
                        $value = $arraydatas[$key][$deviceID];
                    	$updatetime = $arraytimes[$key][$deviceID];
                            
                        switch ($key)
                            { 
                            case 1 :	$gewicht = $value;
                                        $ID = $this->CheckOldVersionCatID("weight",$CatIdWaage,$deviceID);
                                        $this->SetValueToVariable($ID,"Gewicht" ,floatval($gewicht) ,"WITHINGS_M_Kilo"  ,10,true,$TimestampWaage,"weight");
										if ( $lastkey == true )
											$this->SetValueToVariable($ID,"Gewicht" ,floatval($gewicht) ,"WITHINGS_M_Kilo"  ,10,false,$TimestampWaage,"weight");
										
										if ( $groesse !=  0)
											{
											$bmi = @round($gewicht/(($groesse/100)*($groesse/100)),2);
 											$ID = $this->CheckOldVersionCatID("bmi",$CatIdWaage,$deviceID);
                                       		$this->SetValueToVariable($ID,"BMI" ,floatval($bmi) ,"WITHINGS_M_BMI" ,true,true,$TimestampWaage,"bmi");
											if ( $lastkey == true )
												$this->SetValueToVariable($ID,"BMI" ,floatval($bmi) ,"WITHINGS_M_BMI" ,true,false,$TimestampWaage,"bmi");
											
											}
										$ID = $this->CheckOldVersionCatID("timestamp",$CatIdWaage,$deviceID);
                                        $this->SetValueToVariable($ID,"Updatezeit" ,intval($updatetime) ,"~UnixTimestamp"  ,0,false,$TimestampWaage,"timestamp");
                										
										break;
										
                            case 4 :	$groesse = $value;
										break;
                            
                            case 5 :	$fettfrei = $value;
                                        $ID = $this->CheckOldVersionCatID("fatfree",$CatIdWaage,$deviceID);
                                        $this->SetValueToVariable($ID,"Fettfrei Anteil" ,floatval($fettfrei) ,"WITHINGS_M_Kilo" ,10,true,$TimestampWaage,"fatfree");
										if ( $lastkey == true )
											$this->SetValueToVariable($ID,"Fettfrei Anteil" ,floatval($fettfrei) ,"WITHINGS_M_Kilo" ,10,false,$TimestampWaage,"fatfree");
                                        break;
                                    
                            case 6 :	$fettprozent = $value;
                                        $ID = $this->CheckOldVersionCatID("fatradio",$CatIdWaage,$deviceID);
                                        $this->SetValueToVariable($ID,"Fett Prozent" ,floatval($fettprozent) ,"WITHINGS_M_Prozent" ,10,true,$TimestampWaage,"fatradio");
										if ( $lastkey == true )
								        	$this->SetValueToVariable($ID,"Fett Prozent" ,floatval($fettprozent) ,"WITHINGS_M_Prozent" ,10,false,$TimestampWaage,"fatradio");
								
                						break;
                                        
                    		case 8 :	$fettanteil = $value;
										$ID = $this->CheckOldVersionCatID("fatmassweight",$CatIdWaage,$deviceID);
                                        $this->SetValueToVariable($ID,"Fett Anteil" ,floatval($fettanteil) ,"WITHINGS_M_Kilo" ,10,true,$TimestampWaage,"fatmassweight");
										if ( $lastkey == true )
											$this->SetValueToVariable($ID,"Fett Anteil" ,floatval($fettanteil) ,"WITHINGS_M_Kilo" ,10,false,$TimestampWaage,"fatmassweight");
										
										
										break;

							case 9 :	$diastolic = $value;
										$ID = $this->CheckOldVersionCatID("diastolicblood",$CatIdBlutdruck,$deviceID);
										$this->SetValueToVariable($ID,"Diastolic" ,intval($diastolic) ,"WITHINGS_M_Blutdruck" ,10,true,$TimestampBlutdruck,"diastolicblood");
										if ( $lastkey == true )
											$this->SetValueToVariable($ID,"Diastolic" ,intval($diastolic) ,"WITHINGS_M_Blutdruck" ,10,false,$TimestampBlutdruck,"diastolicblood");
										
										break;
							
							case 10 :	$systolic = $value;
                                		$ID = $this->CheckOldVersionCatID("systolicblood",$CatIdBlutdruck,$deviceID);
                                		$this->SetValueToVariable($ID,"Systolic" ,intval($systolic) ,"WITHINGS_M_Blutdruck" ,10,true,$TimestampBlutdruck,"systolicblood");
										if ( $lastkey == true )
											$this->SetValueToVariable($ID,"Systolic" ,intval($systolic) ,"WITHINGS_M_Blutdruck" ,10,false,$TimestampBlutdruck,"systolicblood");
										
										$ID = $this->CheckOldVersionCatID("timestamp",$CatIdBlutdruck,$deviceID);
                                        $this->SetValueToVariable($ID,"Updatezeit" ,intval($updatetime) ,"~UnixTimestamp"  ,0,false,0,"timestamp");
                                		break;
							
							case 11:	$puls = $value;
										$ID = $this->CheckOldVersionCatID("heartpulse",$CatIdWaage,$deviceID);
										$this->SetValueToVariable($ID,"Puls" ,intval($puls) ,"WITHINGS_M_Puls" ,10,true,$TimestampWaage,"heartpulse");
										if ( $lastkey == true )
											$this->SetValueToVariable($ID,"Puls" ,intval($puls) ,"WITHINGS_M_Puls" ,10,false,$TimestampWaage,"heartpulse");
										
										break;
							
							case 12 :	$temperatur = $value;
										$this->SetValueToVariable($deviceID,"Temperatur" ,floatval($temperatur) ,"~Temperature" ,2,true,$TimestampThermo,"temperatur");
										if ( $lastkey == true )
											$this->SetValueToVariable($deviceID,"Temperatur" ,floatval($temperatur) ,"~Temperature" ,2,false,$TimestampThermo,"temperatur");
									
										$this->SetValueToVariable($deviceID,"Updatezeit" ,intval($updatetime) ,"~UnixTimestamp"  ,0,false,0,"timestamp");
                						break;
							
							case 54 :	$sp02 = $value;
										// $ID = $this->CheckOldVersionCatID("",$CatIdWaage,$deviceID);
										break;
                    		
                    		case 71 :	$koerpertemperatur = $value; 
                    					$this->SetValueToVariable($deviceID,"Koerpertemperatur" ,floatval($koerpertemperatur) ,"~Temperature" ,3,true,$TimestampThermo,"koerpertemperatur");
										if ( $lastkey == true )
	                    					$this->SetValueToVariable($deviceID,"Koerpertemperatur" ,floatval($koerpertemperatur) ,"~Temperature" ,3,false,$TimestampThermo,"koerpertemperatur");
										break;
							
							case 73 :	$hauttemperatur = $value;
										$this->SetValueToVariable($deviceID,"Hauttemperatur" ,floatval($hauttemperatur) ,"~Temperature" ,4,true,$TimestampThermo,"hauttemperatur");
										if ( $lastkey == true )
	                    					$this->SetValueToVariable($deviceID,"Hauttemperatur" ,floatval($hauttemperatur) ,"~Temperature" ,4,false,$TimestampThermo,"hauttemperatur");
										break;
							
							case 76 :	$muskelmasse = $value;
										$ID = $this->CheckOldVersionCatID("muskelmasse",$CatIdWaage,$deviceID);
										$this->SetValueToVariable($ID,"Muskelmasse" ,floatval($muskelmasse) ,"WITHINGS_M_Kilo" ,10,true,$TimestampWaage,"muskelmasse");
                						if ( $lastkey == true )
	                    					$this->SetValueToVariable($ID,"Muskelmasse" ,floatval($muskelmasse) ,"WITHINGS_M_Kilo" ,10,false,$TimestampWaage,"muskelmasse");
                						
										break;
							
							case 77 :	$hydration = $value;
										$ID = $this->CheckOldVersionCatID("wasseranteil",$CatIdWaage,$deviceID);
										$this->SetValueToVariable($ID,"Wasseranteil" ,floatval($hydration) ,"WITHINGS_M_Prozent" ,10,true,$TimestampWaage,"wasseranteil");
        								if ( $lastkey == true )
	                    					$this->SetValueToVariable($ID,"Wasseranteil" ,floatval($hydration) ,"WITHINGS_M_Prozent" ,10,false,$TimestampWaage,"wasseranteil");
        								
										break;
							
							case 88 :	$knochenmasse = $value;
										$ID = $this->CheckOldVersionCatID("bonemass",$CatIdWaage,$deviceID);
										$this->SetValueToVariable($ID,"Knochenmasse" ,floatval($knochenmasse) ,"WITHINGS_M_Kilo" ,10,true,$TimestampWaage,"bonemass");
                						if ( $lastkey == true )
	                    					$this->SetValueToVariable($ID,"Knochenmasse" ,floatval($knochenmasse) ,"WITHINGS_M_Kilo" ,10,false,$TimestampWaage,"bonemass");
                						
										break;
							
							case 91 :	$pulswellen = $value;
										$ID = $this->CheckOldVersionCatID("pulswave",$CatIdWaage,$deviceID);
										$this->SetValueToVariable($ID,"Pulswellengeschwindigkeit" ,floatval($pulswellen) ,"~WindSpeed.ms" ,1,true,$TimestampWaage,"pulswave");
                						if ( $lastkey == true )
	                    					$this->SetValueToVariable($ID,"Pulswellengeschwindigkeit" ,floatval($pulswellen) ,"~WindSpeed.ms" ,1,false,$TimestampWaage,"pulswave");
                						
										break;
					                    
                                                            
                            default:	$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Messungstyp nicht vorhanden : ".$key."-".$value,0);
					
                            }
                    
							
                        }
					
						// $this->SendDebug(__FUNCTION__.'['.__LINE__.']',"RequestReAggregation erforderlich  :".$ID,0);
					}

                
				
		if ( $RequestReAggregation == true )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"RequestReAggregation erforderlich:".$InstanceIDDeviceID,0);
			$this->Reaggregieren($InstanceIDDeviceID);
			}	
		else
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"RequestReAggregation nicht erforderlich",0);
			}			

		}



	//******************************************************************************
	// checken ob noch alte Version verwendet wird	
	//******************************************************************************
	private function CheckOldVersionCatID($ident,$CatId,$DeviceID) 
		{
        $VariableID = @IPS_GetObjectIDByIdent($ident,$CatId);
        if ( $VariableID == true )
        	{
            $this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Variable noch in alter Kategorie : ".$ident."-".$CatId,0);   
            $ID = $CatId;
            }
        else
            {
            // $this->SendDebug("CheckOldVersionCatID","Variable nicht mehr alter Kategorie : ".$ident."-".$CatId,0);
            $ID = $DeviceID;
            }
		
		return $ID;    
        }
	
	//******************************************************************************
	//	Register OAuth in IPSymcon
	//******************************************************************************
	private function RegisterOAuth($WebOAuth) 
		{
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']','', 0);
	
		// WebOauth im Tree
		$ids = IPS_GetInstanceListByModuleID("{F99BF07D-CECA-438B-A497-E4B55F139D37}");
		if(sizeof($ids) > 0) 
			{
			$clientIDs = json_decode(IPS_GetProperty($ids[0], "ClientIDs"), true);
			$found = false;
			foreach($clientIDs as $index => $clientID) 
				{
				if($clientID['ClientID'] == $WebOAuth) 
					{
					if($clientID['TargetID'] == $this->InstanceID)
						return;
					$clientIDs[$index]['TargetID'] = $this->InstanceID;
					$found = true;
					}
				}
			if(!$found) 
				{
				$clientIDs[] = Array("ClientID" => $WebOAuth, "TargetID" => $this->InstanceID);
				}
			IPS_SetProperty($ids[0], "ClientIDs", json_encode($clientIDs));

			$this->SendDebug(__FUNCTION__.'['.__LINE__.']', $ids[0], 0);
			IPS_ApplyChanges($ids[0]);
			}
		}

	//****************************************************************************
	// Wird von OAuth control aufgerufen
	//****************************************************************************
	protected function ProcessOAuthData() 
		{
		if($_SERVER['REQUEST_METHOD'] == "GET") 
			{
			if(!isset($_GET['code']))
				{
				$this->SendDebug(__FUNCTION__.'['.__LINE__.']', "Authorization Code expected", 0);
					die("Authorization Code expected");
				}
			$code = $_GET['code'];  
			
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']', "code: ".$code, 0);
			$this->FetchRefreshToken($code);
			} 
		else 
			{	
			echo file_get_contents("php://input");	
			}
		}

	//****************************************************************************
	// Token holen
	//****************************************************************************
 	private function FetchRefreshToken($code) 
 		{
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']', "Mit Authentication Code Refresh Token holen ! Code : ".$code, 0);
			
		$options = array(
						'http' => array(
										'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
										'method'  => "POST",
										'content' => http_build_query(Array("code" => $code))
										)
						);
		$context = stream_context_create($options);

		$result = file_get_contents("https://oauth.ipmagic.de/access_token/withings", false, $context);

		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Tokens : ".$result,0);
		
		$data = json_decode($result);

		if(!isset($data->token_type) || $data->token_type != "Bearer") 
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Bearer Token expected",0 );
			return false;
			}

		$token = $data->refresh_token;	
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']', "OK! Speichere Refresh Token . ".$token, 0);
		IPS_SetProperty($this->InstanceID, "User", $token);
		IPS_ApplyChanges($this->InstanceID);

		$this->FetchAccessToken($data->access_token, time() + $data->expires_in);
		
		}

	//******************************************************************************
	//	
	//******************************************************************************
	private function FetchAccessToken($Token = "", $Expires = 0) 
		{
			
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']', "Benutze Refresh Token um neuen Access Token zu holen : " . $Token, 0);
		$this->Logging("Benutze Refresh Token um neuen Access Token zu holen : " . $this->ReadPropertyString("User"));
			
		$options = array(
					"http" => array(
						"header" => "Content-Type: application/x-www-form-urlencoded\r\n",
						"method"  => "POST",
						"content" => http_build_query(Array("refresh_token" => $this->ReadPropertyString("User")))
									)
							);
							
		$context = stream_context_create($options);

		$result = @file_get_contents("https://oauth.ipmagic.de/access_token/withings", false, $context);
		$data = json_decode($result);

		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',$result,0 );

		if(!isset($data->token_type) || $data->token_type != "Bearer") 
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Bearer Token expected",0 );
			return false;
			}

		$Expires = time() + $data->expires_in;
				
		//Update Refresh Token wenn vorhanden
		if(isset($data->refresh_token)) 
			{
			$token = $data->refresh_token;		

			// $this->SendDebug(__FUNCTION__.'['.__LINE__.']', "Neuer Refresh Token erhalten", 0);
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']', "OK! Speichere Refresh Token . ".$token, 0);
			IPS_SetProperty($this->InstanceID , "User", $token );
			IPS_ApplyChanges($this->InstanceID);
			}
				
		$token = $data->access_token;
		$scope = $data->scope;
		$userid = $data->userid;
		$VarID = @$this->GetIDForIdent("userid");
		if ( $VarID === false )
			$VarID = $this->RegisterVariableInteger("userid", "User ID"   ,"" ,0);
		if ( $VarID == true )
		    SetValue($VarID,$userid);
		    
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']', "Neuer Access Token ist gueltig bis ".date("d.m.y H:i:s", $Expires)."[".$token."]", 0);
		// $this->SendDebug(__FUNCTION__.'['.__LINE__.']', "OK! Speichere Access Token . ".$token, 0);
		
    	$this->SendDebug(__FUNCTION__.'['.__LINE__.']', "scope: ".$scope, 0);

		$this->Logging("UserID : " .$userid );
		$this->Logging("Access Token : " .$token . " " ."Neuer Access Token ist gueltig bis ".date("d.m.y H:i:s", $Expires) );
		
		
		IPS_SetProperty($this->InstanceID , "Userpassword", $token);
		IPS_ApplyChanges($this->InstanceID);
		
		return true;
		
		}

	//******************************************************************************
	//	
	//******************************************************************************
	private function FormatTimeMinuten($time) 
		{
		$hour = intval($time/3600);
		$minuten = intval(($time-($hour*3600))/60);
		$time = $hour."H".$minuten."M";

        return $time;
                
		}

	//******************************************************************************
	//	wandelt Timestamp in Datum/Uhrzeit
	//******************************************************************************
	protected function TimestampToDate($time)
		{
		return date('d.m.Y H:i:s',$time);			
		}

	//******************************************************************************
	//	wandelt Datum/Uhrzeit in Timestamp  
	//******************************************************************************
	protected function DateToTimestamp($time)
		{
		return strtotime($time);			
		}


	//******************************************************************************
	//	Wert in Variable schreiben
	//******************************************************************************
	protected function SetValueToVariable($CatID,$name,$value,$profil=false,$position=0 ,$asynchron=false,$Timestamp=0,$VarIdent=false,$NoLogging=false)
		{

		// $this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Start :".$name."-".$this->TimestampToDate($Timestamp),0);	

		$Reaggieren = false;

		if ( $profil != false )
        	{
            if (IPS_VariableProfileExists($profil) == false )
            	{
                $this->RegisterAllProfile();
                }
            }

		if ( $VarIdent != false )
			{
            $VariableID = @IPS_GetObjectIDByIdent($VarIdent,$CatID );
			if ($VariableID == true )
            	{
                //$this->SendDebug("SetValueToVariable","Variable Ident gefunden : ".$CatID."-".$name."-".$VarIdent."-".$VariableID,0);
                }                 
            else 
                {
                $this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Variable Ident nicht gefunden : ".$CatID."-".$name."-".$VarIdent."-".$VariableID,0);
                }
                            
            }
		else
            {
            $this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Variable Ident nicht definiert : ".$CatID."-".$name."-".$VarIdent."-".$VariableID,0);
            //$VariableID = @IPS_GetVariableIDByName($name,$CatID );
            return false;
            }
                
		if ($VariableID === false)
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"VariableID nicht vorhanden : ".$CatID."-".$name."-".$profil,0);

			$profiltype = IPS_GetVariableProfile($profil);
      		$profiltype = $profiltype['ProfileType'];

			IPS_Logmessage("Withings Modul","Variable wird angelegt Typ : " . $profiltype." - " .$profil);

			if ( is_int($value) == true )
				{
				if ( $profiltype == 1 )
					$VariableID = $this->RegisterVariableInteger( $VarIdent, $name,$profil,$position);
				else
					IPS_Logmessage("Withings Modul","Variablentyp falsch : " . $profiltype . " - 1 ".$profil);
				}

			if ( is_string($value) == true )
				{
				if ( $profiltype == 3 )
					$VariableID = $this->RegisterVariableString($VarIdent, $name,$profil,$position);
				else
					IPS_Logmessage("Withings Modul","Variablentyp falsch : " . $profiltype . " - 3 ".$profil);
				}

			if ( is_float($value) == true )
				{
				if ( $profiltype == 2 )
					$VariableID = $this->RegisterVariableFloat( $VarIdent, $name,$profil,$position);
				else
					IPS_Logmessage("Withings Modul","Variablentyp falsch : " . $profiltype . " - 2 ".$profil);
				}

			if ( is_bool($value) == true )
				{
				if ( $profiltype == 0 )
					$VariableID = $this->RegisterVariableBool( $VarIdent, $name,$profil,$position);
				else
					IPS_Logmessage("Withings Modul","Variablentyp falsch : " . $profiltype . " - 0 ".$profil);
				}

			if ( $VariableID == true )
      			{
				IPS_SetParent($VariableID,$CatID);
        		}
                   
			}
		
		if ($VariableID === false)
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"VariableID nicht vorhanden : ".$CatID."-".$name."-".$profil,0);
            return;
            }

        IPS_SetPosition($VariableID, $position);
                        
		if ( $asynchron == true )
        	{
			$Reaggieren = $this->SaveDataToDatabase($VariableID,$Timestamp,$value,$name);
			//$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Reaggieren : ".$Reaggieren,0);
            

            }
		else
            {
			if ( $VariableID > 0 )
            	{
				// $this->SendDebug(__FUNCTION__.'['.__LINE__.']',"VariableID Asynchron : ".$VariableID,0);
                SetValue($VariableID,$value);
                }

        	}
				
		return $Reaggieren;
				
	}
		

	//******************************************************************************
	//	Wert in Archiv schreiben (asynchron) 
	//******************************************************************************
	protected function SaveDataToDatabase($Variable,$Timestamp,$Value,$name = false)
		{
		
		// $this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Start:".$Timestamp,0);
		
        $Reaggregieren = false;
                
        $archiveID = $this->GetArchivID();
		
		if ( $archiveID == false )
        	{   
            return false;
            }

        $status = AC_GetLoggingStatus ($archiveID, $Variable);
			
		if ( $status == TRUE )
            {
            // $this->SendDebug("SaveDataToDatabase","Variable wird geloggt -> " . $Variable,0);
            }
        else 
            {
            $this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Variable wird nicht geloggt -> " . $Variable,0);
            return;
            }
                    
        $parent = IPS_GetParent($Variable);
        $LastTimestampID = @IPS_GetObjectIDByIdent("timestamp",$parent );
        $LastTimestamp = @GetValue($LastTimestampID);
                	
		if (!function_exists('AC_AddLoggedValues'))
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"!function_exists('AC_AddLoggedValues').",0);
			}
		else
			{	
				                
			$datas = @AC_GetLoggedValues($archiveID, $Variable, $Timestamp,$Timestamp, 1);
			$anzahl = count($datas);

			if ( $anzahl == 0 )	// nicht vorhanden
				{	
                $status = @AC_AddLoggedValues($archiveID, $Variable,[['TimeStamp' => $Timestamp, 'Value' 	=> $Value]]);
				$Reaggregieren = true;

				if ( $status == true )
                	$statustext = "OK";
                else 
                	{
                    $statustext = "NOK";
                    }
                $Text = date('d.m.Y H:i:s ',$Timestamp) . $Variable . " - ".$Value." Status : ".$statustext;
                $this->LoggingExt($Text,"WithingsDataToDatabase.log");
				}
			else
				{
				// $this->SendDebug("SaveDataToDatabase","Datensatz schon vorhanden :".$Variable ,0);
            			
				}		

			}
			
		return	$Reaggregieren ;

		}

	//**************************************************************************
	// Getnonce
	//**************************************************************************
	protected function GetNonce()
		{
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Start",0);
		
		$action = "getnonce";
		$client_id = "2166af8c652a1b251db8b954cfdc2e24c82f1493d15d03b4dddc422f342c7ffd";
		$timestamp = time();
		$string = $action.",".$client_id.",".$timestamp;

		$signature = "b22edf210b6a67b583c9ef1bd39481383e7c2d1b7ac198ddeeb0ea68a7ecd2f6";
		$signature = hash_hmac("sha256",$string,$signature,false);	

		$url = "https://wbsapi.withings.net/v2/signature?action=".$action."&client_id=".$client_id."&timestamp=".$timestamp."&signature=".$signature;

		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',$url,0);

		$output = $this->DoCurl($url);		
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',$output,0);
		
		$data = json_decode($output,TRUE);
		$data = @$data['body'];
		// $data = $this->DoNotifyList($data);
		
		// return $data;
		
		}


	//**************************************************************************
	// Notify List
	//**************************************************************************
	protected function GetNotifyList($appli="")
		{
		
		$access_token = $this->ReadPropertyString("Userpassword");

		$url = "https://wbsapi.withings.net/notify?action=list&access_token=".$access_token."&appli=".$appli;
		$output = $this->DoCurl($url);		
    	$this->SendDebug(__FUNCTION__.'['.__LINE__.']',$output,0);
		$data = json_decode($output,TRUE);
		$data = @$data['body'];
		$data = $this->DoNotifyList($data);
		
		return $data;
		
		}


	//******************************************************************************
	//	Abfrage welche Benachrichtigungen aktiv
	//******************************************************************************
	function DoNotifyList($data)
		{
		
		Global $NotifiyListArray;

			

		if ( $data == false )
			return false ;
			
		$data = @$data['profiles'];

		if ( @count($data) == 0 )
			{
			// $this->SendDebug(__FUNCTION__,"Keine Profile gefunden. Abbruch",0);
			return false;
			}
		else
			{
			//$this->SendDebug(__FUNCTION__,"Anzahl der Profile : ".count($data),0);
			}

		foreach($data as $profil )
			{
			$applikation = @$profil['appli'];	
			$callbackurl = @$profil['callbackurl'];	
			$comment     = @$profil['comment'];	
				
			$applikation = intval($applikation);

			$s = "[".$applikation."][".$callbackurl."][".$comment."]";
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',$s,0);
			$NotifiyListArray[$applikation] = $callbackurl;
			
			}
			
		return ( $data );
			
		}
		
	//**************************************************************************
	//	
	//**************************************************************************
	public function DoNotifyRevokeAll()
		{
		$data = $this->GetNotifyList();
		if ( $data == false )
			return;

		foreach($data as $profil )
			{
			$applikation = @$profil['appli'];	
			$callbackurl = @$profil['callbackurl'];	
			$comment     = @$profil['comment'];	
				
			$s = "[".$applikation."][".$callbackurl."][".$comment."]";
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',$s,0);
			
			$this->GetRevokeNotifyList($applikation,$callbackurl);
			}
			
		}
	

	//**************************************************************************
	// Revoke Notify List
	//**************************************************************************
	protected function GetRevokeNotifyList($applikation,$callbackurl)
		{
		
		$access_token = $this->ReadPropertyString("Userpassword");

		$url = "https://wbsapi.withings.net/notify?action=revoke&access_token=".$access_token."&appli=".$applikation."&callbackurl=".$callbackurl;
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',$url,0);

		$curl = curl_init($url);

		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);

		$output = curl_exec($curl);

		curl_close($curl);

		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',$output,0);
		
		$data = json_decode($output,TRUE);
		
		$data = @$data['body'];
		
		
		}
		
	//**************************************************************************
	// Notify Subscribe
	//**************************************************************************
	protected function GetNotifySubscribe()
		{
		
		Global $NotifiyListArray;

		$access_token = $this->ReadPropertyString("Userpassword");

		$startdate = date("Y-m-d",time() - 24*60*60*5);
		$enddate   = date("Y-m-d",time());
		
		$ipsymconconnectid = IPS_GetInstanceListByModuleID("{9486D575-BE8C-4ED8-B5B5-20930E26DE6F}")[0]; 
		$connectinfo = CC_GetUrl($ipsymconconnectid);
		
		
		$manURL = $this->ReadPropertyString("CallbackURL");
		if ( $manURL != "" )
			{
			$callbackurl = $manURL ."/hook/Withings".$this->InstanceID ;
			}
		else
			{
			$callbackurl = $connectinfo."/hook/Withings".$this->InstanceID."/";
			}
				
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"CallbackURL:".$callbackurl,0);
		

		// Wenn CallbackURL schon gesetzt dann ueberspringen
		$SubscribeArray = array( 
			1 => "SubscribeWeight" ,
			2 => "SubscribeHeart" ,
			4 => "SubscribeHeart" ,
			16 => "SubscribeActivity" ,
			44 => "SubscribeSleep" ,
			46 => "SubscribeUser" ,
			50 => "SubscribeSleep" ,
			51 => "SubscribeSleep" ,
			
			);

		foreach($SubscribeArray as $appli => $comment )
    		{
			if ( isset($NotifiyListArray[$appli]) == true AND $NotifiyListArray[$appli] == $callbackurl)
				{ 
				$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"CallbackURL:".$callbackurl." schon gesetzt appli=".$appli,0);
				}
			else
				{
				$url = "https://wbsapi.withings.net/notify?action=subscribe&access_token=".$access_token."&callbackurl=".$callbackurl."&appli=".$appli."&comment=".$comment."";
				$output = $this->DoCurl($url,true);
				$data = json_decode($output,TRUE);
				$status = @$data['status'];
				if ( $status != 0 )
					$this->SetStatus(293);
				}
	
			}	

		}
		
	//******************************************************************************
	//	Curl Abfrage ausfuehren
	//******************************************************************************
	function DoCurl($url,$debug=false)
		{
		if($debug == true)
			$this->SendDebug(__FUNCTION__,$url,0);

		$curl = curl_init($url);

		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);

		$output = curl_exec($curl);

		curl_close($curl);

		if($debug == true)
			$this->SendDebug(__FUNCTION__,$output,0);
			
		return $output;
					
		}	
		
	//**************************************************************************
	// Hook Data auswerten
	// Wird von Withings aufgerufen
	//**************************************************************************
	protected function ProcessHookData()
		{
		GLOBAL $_IPS;

		// IPS_sleep(1800);

		header("HTTP/1.1 200 OK");
			
		http_response_code(200);
		echo $this->InstanceID;	
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"",0);

		if ( $this->ReadPropertyBoolean("Notifyaktiv") == false )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Notifications disabled",0);
			return;
			}
			
		//IPS_LogMessage("WebHook GET", print_r($_GET, true));
		//IPS_LogMessage("WebHook POST", print_r($_POST, true));
		//IPS_LogMessage("WebHook IPS", print_r($_IPS, true));
		//IPS_LogMessage("WebHook RAW", file_get_contents("php://input"));


		if ( isset($_POST['userid']) )	
			$userid = $_POST['userid'];
		else
			$userid = false;
		if ( isset($_POST['startdate']) )	
			$startdate = $_POST['startdate'];
		else
			$startdate = false;
		if ( isset($_POST['enddate']) )	
			$enddate = $_POST['enddate'];
		else
			$enddate = false;
		if ( isset($_POST['appli']) )	
			$appli = $_POST['appli'];
		else
			$appli = false;

		//$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"UserID:".$userid,0);
		//$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Startdate:".$startdate,0);
		//$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Enddate:".$enddate,0);
		//$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Appli:".$appli,0);
			

		$parameter=array('userid' => $userid,'startdate' => $startdate,'enddate' => $enddate,'appli' => $appli,);	

		if ( $appli == 1 or $appli == 2)
			{
			$this->SendDebug("Update Data","Update Data Get Meas     -> ".date('d.m.Y H:i:s ',time() ),0);
			$this->GetMeas();
		
			$id = $this->ReadPropertyInteger("Notify1");	
			if ( @IPS_ScriptExists ($id) )
				{
				IPS_RunScriptEx ($id,$parameter);
				}
			}	

		if ( $appli == 4 )	
			{
			$this->SendDebug("Update Data","Update Data Get Meas     -> ".date('d.m.Y H:i:s ',time() ),0);
			$this->GetMeas();
	
			$id = $this->ReadPropertyInteger("Notify4");	
			if ( @IPS_ScriptExists ($id) )
				{
				IPS_RunScriptEx ($id,$parameter);
				}
			}	

		if ( $appli == 16 )	
			{
			$this->SendDebug("Update Data","Update Data Get Activity -> ".date('d.m.Y H:i:s ',time() ),0);
			$this->GetActivity();
		
			$id = $this->ReadPropertyInteger("Notify16");	
			if ( @IPS_ScriptExists ($id) )
				{
				IPS_RunScriptEx ($id,$parameter);
				}
			}	

		if ( $appli == 44 or $appli == 50 or $appli == 51)
			{
			$this->SendDebug("Update Data","Update Data Get Sleep    -> ".date('d.m.Y H:i:s ',time() ),0);
			$this->GetSleepSummary();
			
			$id = $this->ReadPropertyInteger("Notify44");	
			if ( @IPS_ScriptExists ($id) )
				{
				IPS_RunScriptEx ($id,$parameter);
				}
			}	

		if ( $appli == 46 )	
			{
			$id = $this->ReadPropertyInteger("Notify46");	
			if ( @IPS_ScriptExists ($id) )
				{
				IPS_RunScriptEx ($id,$parameter);
				}
			}	
			
		}

	//**************************************************************************
	//	ID des Archivs zurueckgeben
	//**************************************************************************
	protected function GetArchivID()
		{
		$guid = "{43192F0B-135B-4CE7-A0A7-1475603F3060}";
		$array = IPS_GetInstanceListByModuleID($guid);
		$archive_id =  @$array[0];
		if ( !isset($archive_id) )
			{
			$this->SendDebug("GetArchivID","Archive Control nicht gefunden!",0);
			return false;
			}
		return $archive_id;
		}

	//**************************************************************************
	//	Alarmfunction ( Offline )
	//**************************************************************************
	public function RunAlarmScript($id,$string)
		{

		$script = $this->ReadPropertyInteger("NotifyAlarm");
		$parameter = array();
		$parameter['ALARM'] = true;
		$parameter['WITHINGSINSTANZ'] = $id;
		$parameter['STRING'] = $string;

		if ( @IPS_ScriptExists ($script) )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Starte Alarmscript:".$script,0);
			IPS_RunScriptEx ($script,$parameter);
			}
		else	
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Alarmscript nicht vorhanden: ".$script,0);

		}


	//**************************************************************************
	//	Reaggregieren der uebergebenen Instanz
	//**************************************************************************
	 protected function Reaggregieren($Instanze)
		{

		if ( $Instanze == false )
			return;

		$version = (float)IPS_GetKernelVersion();

        $this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Reaggregiere Data in Database : " . $version." - ".$Instanze,0);
                
        $childs = IPS_GetChildrenIDs($Instanze);
                
        $array = array();
                
        foreach($childs as $child )
        	{
			// wenn geloggte Variable zum Array hinzufuegen
			$status = AC_GetLoggingStatus($this->GetArchivID(),$child);
            if ( $status == true )
            	{
                $array[] = $child;        
                }
            }

        $count = @count($array);

		if ( $count == false )
			{
        	$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Count = FALSE",0);
			return;
			}
			
		$childs = $array;

		if ( $version < 5.5 )	// Kein gleichzeitiges Reaggregieren moeglich
			{	
        	$random = rand(0,$count-1);    
        	$child = $childs[$random];
                
            $status = @AC_ReAggregateVariable ($this->GetArchivID(), $child );

            if ( $status )
            	{
                $this->SendDebug("Reaggregieren","Erfolgreich -> [".$random."]" .$child ,0);
                }
            else 
                {
                $this->SendDebug("Reaggregieren","Fehlgeschlagen -> [".$random."]" .$child ,0);
                }    
			}		
		else
			{
			
			foreach($childs as $child)
				{
				
				$status = @AC_ReAggregateVariable ($this->GetArchivID(), $child );

				if ( $status )
            		{
                	$this->SendDebug("Reaggregieren","Erfolgreich -> " .$child ,0);
                	}
            	else 
                	{
                	$this->SendDebug("Reaggregieren","Fehlgeschlagen -> " .$child ,0);
                	}    

				}

			}	
			
		}

	}


