<?php
    
//******************************************************************************
//	Name		:	Withings Modul.php
//	Aufruf		:	
//	Info		:	
//
//******************************************************************************

define("DATA_TO_VARIABLE",false);
define("DATA_TO_DATABASE",true);

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


		$this->RegisterPropertyString("SetUserName", "");             
		$this->RegisterPropertyInteger("SetUserGender", 0);
		$this->RegisterPropertyString("SetUserBirthday", "1.1.1970");
		$this->RegisterPropertyInteger("SetUserHeight", 0);

		
		$this->RegisterPropertyBoolean("Logging", false);  
		$this->RegisterPropertyBoolean("ExtDebug", false); 
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
		$this->RegisterPropertyBoolean("CheckBoxBodyGoals" , false);

        $this->RegisterPropertyString("AccessToken", "");
        $this->RegisterPropertyString("RefreshToken", "");
        $this->RegisterPropertyString("UserID", "");
		$this->RegisterPropertyBoolean("Activityaktiv" , false);  	// in V3.0 ist das  Activity aktiv
		$this->RegisterPropertyBoolean("Notifyaktiv" , false);  	// in V4.0 ist das Notify aktiv
		
		$this->RegisterPropertyInteger("Notify1", 0);
		$this->RegisterPropertyInteger("Notify2", 0);
        $this->RegisterPropertyInteger("Notify4", 0);
        $this->RegisterPropertyInteger("Notify16", 0);
        $this->RegisterPropertyInteger("Notify44", 0);
		$this->RegisterPropertyInteger("Notify46", 0);
		$this->RegisterPropertyInteger("Notify50", 0);
		$this->RegisterPropertyInteger("Notify51", 0);
		$this->RegisterPropertyInteger("Notify52", 0);
		$this->RegisterPropertyInteger("Notify53", 0);
		$this->RegisterPropertyInteger("Notify54", 0);
		$this->RegisterPropertyInteger("Notify55", 0);

		$this->RegisterPropertyString("UpdateDateTimeStart", 0);
		$this->RegisterPropertyString("UpdateDateTimeEnde", 0);
                
		$this->RegisterPropertyInteger("NotifyAlarm", 0);
		$this->RegisterPropertyString("CallbackURL", "");



		// Neue Variablen fuer OAuth ueber Modul ( N = New)
		$this->RegisterPropertyString("Nuserid","");
		$this->RegisterPropertyString("Naccess_token","");
		$this->RegisterPropertyString("Nrefresh_token","");
		$this->RegisterPropertyString("Nscope","");
		$this->RegisterPropertyString("Nexpires_in","");
		$this->RegisterPropertyString("Ntoken_type","");
		

                }

	//******************************************************************************
	// Register alle Profile
	//******************************************************************************
	protected function RegisterAllProfile()
		{
			
		$this->RegisterProfile(1,"WITHINGS_M_Groesse"  ,""  ,""," cm");
		$this->RegisterProfile(1,"WITHINGS_M_Puls"     ,""  ,""," bpm");
		$this->RegisterProfile(1,"WITHINGS_M_IntProzent"     ,""  ,""," %");
    	$this->RegisterProfile(1,"WITHINGS_M_Atmung"   ,""  ,""," Atemzuege/Minute");
		$this->RegisterProfile(2,"WITHINGS_M_Kilo"     ,""       ,""," kg",false,false,false,1);
		$this->RegisterProfile(2,"WITHINGS_M_Temperatur"     ,""       ,""," °C",false,false,false,1);
		$this->RegisterProfile(2,"WITHINGS_M_Pulswelle"     ,""       ,""," m/s",false,false,false,2);
		$this->RegisterProfile(2,"WITHINGS_M_Prozent"  ,""       ,""," %",false,false,false,1);
		$this->RegisterProfile(2,"WITHINGS_M_BMI"      ,""       ,""," kg/m²",false,false,false,1);
		$this->RegisterProfile(1,"WITHINGS_M_Blutdruck","",""," mmHg");
		$this->RegisterProfile(1,"WITHINGS_M_Stockwerke","",""," Stockwerke");

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
		$name = $this->ReadPropertyString("SetUserName");
		SetValueString($id,$name);
		$id = $this->RegisterVariableInteger("gender"    , "Geschlecht","WITHINGS_M_Gender",2);
		$gender = $this->ReadPropertyInteger("SetUserGender");
		SetValueInteger($id,$gender);
		$id = $this->RegisterVariableString( "birthdate" , "Geburtstag","",1);
		$birthday = $this->ReadPropertyString("SetUserBirthday");
		$s = json_decode($birthday,TRUE);
		if ( isset($s['day']) AND isset($s['month']) AND isset($s['year'])) 
			{
			$birthday = $s['day'].".".$s['month'].".".$s['year'];	
			SetValueString($id,$birthday);
			}
		$id = $this->RegisterVariableInteger("height"    , "Groesse"   ,"WITHINGS_M_Groesse" ,3);
		$height = $this->ReadPropertyInteger("SetUserHeight");
		SetValueInteger($id,$height);

		//Timer stellen
		$interval = $this->ReadPropertyInteger("Intervall") * 1000 ;
		$this->SetTimerInterval("WIT_UpdateTimer", $interval);



		if ( $this->ReadPropertyBoolean("Notifyaktiv") == false )
			{
			$this->DoNotifyRevokeAll();	
			// $this->GetNotifyList();	
			}
		else
			{
			$this->DoNotifySubscribe();
			$this->GetNotifyList();
			}



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
	// manuelles Holen der Daten ueber Script (Tage)
	// 
	// 		Activity
	//   	Meas
	// 		Sleep
	// 		Intradayactivity
	// 
	//**************************************************************************
	public function UpdateDataForDays(string $measure="",int $days=0)
		{
		
		$measure = strtoupper($measure);

		if ( $days == 0 OR $days > 365 OR $days < 0)
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']','Tage Bereich NOK:'.$days.' Tage',0);
			return;	
			}
		else
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']','Messung:'.$measure.' - '.$days.' Tage',0);

		$OnlineOK = false;

		switch($measure)
			{

			case "DEVICE"						:	
												$OnlineOK = $this->GetDevice();
												break;

			case "MEAS"						:
												$OnlineOK = $this->GetMeas($days);
												break;

			case "SLEEPSUMMARY"					:
												$OnlineOK = $this->GetSleepSummary($days);
												break;

			case "ACTIVITY"					:
												$OnlineOK = $this->GetActivity($days);
												break;
							
			case "INTRADAYACTIVITY"			:
												$OnlineOK = $this->GetIntradayactivity($days);
												break;
											
			default							:
												$OnlineOK = $this->GetDevice();
												$OnlineOK = $this->GetMeas($days);
												$OnlineOK = $this->GetSleepSummary($days);
												$OnlineOK = $this->GetActivity($days);
												$OnlineOK = $this->GetIntradayactivity($days);
												


												// $this->SendDebug(__FUNCTION__.'['.__LINE__.']','Messung:'.$measure.' nicht definiert',0);
												return;
					
			}


		return $OnlineOK;	

		}

	//**************************************************************************
	// manuelles Holen der Daten ueber Script (Zeit)
	// 
	// 		Activity
	//   	Meas
	// 		Sleep
	// 		Intradayactivity
	// 
	//**************************************************************************
	public function UpdateDataForTime(string $measure="",int $starttime=0,int $endtime=0)
		{
		
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']','------------------------------------------------------------------------',0);	

		$measure = strtoupper($measure);

		$OnlineOK = false;

		if ( is_string($starttime) == true )
			$starttime = $this->DateToTimestamp($starttime);
		if ( is_string($endtime) == true )
			$endtime = $this->DateToTimestamp($endtime);	

		$this->SendDebug(__FUNCTION__.'['.__LINE__.']','Starttime:'.$this->TimestampToDate($starttime) ." - " .$this->TimestampToDate($endtime),0);		
		 	
		if ( $starttime == 0 OR $endtime == 0)
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']','StartTime oder EndTime = 0',0);
			return;	
			}

		if ( $starttime > $endtime )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']','StartTime > EndTime',0);
			return;	
			}

		$difftime = $endtime - $starttime;
		if ( $difftime > (365*24*60*60))
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']','DiffTime > 1 Jahr',0);
			return;		
			}	

		$s = $this->TimestampToDate($starttime);
		//$this->SendDebug(__FUNCTION__.'['.__LINE__.']','Starttime:'.$s.' Sekunden '.$starttime,0);	

		$s = $this->TimestampToDate($endtime);
		//$this->SendDebug(__FUNCTION__.'['.__LINE__.']','Endtime:'.$s.' Sekunden '.$endtime,0);	


		// $this->SendDebug(__FUNCTION__.'['.__LINE__.']','Difftime:'.$difftime.' Sekunden',0);	
		$days = ceil($difftime/(24*60*60));


		if ( $days == 0 OR $days > 365 OR $days < 0)
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']','Tage Bereich NOK:'.$days.' Tag(e)',0);
			return;	
			}
		else
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']','Messung:'.$measure.' = '.$days.' Tag(e)',0);

		
		switch($measure)
			{

			case "DEVICE"					:
												$OnlineOK = $this->GetDevice();
												break;

			case "MEAS"						:
												$OnlineOK = $this->GetMeas($days,$starttime);
												break;

			case "SLEEPSUMMARY"				:
												$OnlineOK = $this->GetSleepSummary($days,$starttime);
												break;

			case "ACTIVITY"					:
												$OnlineOK = $this->GetActivity($days,$starttime);
												break;
							
			case "INTRADAYACTIVITY"			:
												$OnlineOK = $this->GetIntradayactivity($days,$starttime);
												break;
											
			default							:
												$OnlineOK = $this->GetDevice();
												$OnlineOK = $this->GetMeas($days,$starttime);
												$OnlineOK = $this->GetSleepSummary($days,$starttime);
												$OnlineOK = $this->GetActivity($days,$starttime);
												$OnlineOK = $this->GetIntradayactivity($days,$starttime);
												
												// $this->SendDebug(__FUNCTION__.'['.__LINE__.']','Messung:'.$measure.' nicht definiert',0);
												return;
					
			}

		return $OnlineOK;	

		}		

	//**************************************************************************
	// manuelles Holen der Daten fuer bestimmten Zeitraum
	//**************************************************************************
	public function UpdateZeitraum()
		{
		if ( $this->ReadPropertyBoolean("Modulaktiv") == false )
        	{
			$this->SetStatus(104);	
            return;
            }
		else
			$this->SetStatus(102);

		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Update Zeitraum START",0);

		$start = $this->ReadPropertyString("UpdateDateTimeStart");
		$ende = $this->ReadPropertyString("UpdateDateTimeEnde");

		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"".$start." - ".$ende,0);

		$s = json_decode($start,TRUE);
		if ( is_array($s) == false )
			return false;
		$e = json_decode($ende,TRUE);
		if ( is_array($e) == false )
				return false;
	
		$startzeit = $s['day'].".".$s['month'].".".$s['year']." ".$s['hour'].":".$s['minute'].":".$s['second'];		
		$endezeit  = $e['day'].".".$e['month'].".".$e['year']." ".$e['hour'].":".$e['minute'].":".$e['second'];		
		
		if (($timestampstart = strtotime($startzeit)) === false) 
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Start Zeit ist fehlerhaft : ".$start,0);				
			} 
		else 
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Start Zeit ist : ".$this->TimestampToDate($timestampstart),0);
			}

		if (($timestampende = strtotime($endezeit)) === false) 
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Ende Zeit ist fehlerhaft : ".$start,0);				
			} 
		else 
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Ende Zeit ist : ".$this->TimestampToDate($timestampende),0);
			}

		if ( $timestampende < $timestampstart )	
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Ende Zeit ist < Start Zeit",0);
			}
	
		$this->UpdateDataForTime("",$timestampstart,$timestampende);

		}
 

	//**************************************************************************
	// Extern Token abrufen
	//**************************************************************************
	public function GetAccessToken()
		{
	
		if ( $this->RefreshTokens() == FALSE )	// Token konnte nicht aktualisiert werden
            {
			return FALSE;	
			}
		$access_token = IPS_GetProperty($this->InstanceID, "Naccess_token");
		return $access_token;
			
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
	
         
		$NotifiyListArray = array();

        $starttime = time();            
		
		
		$this->Debug("Update Data -------------------------------------------------------------",__FUNCTION__,__LINE__,true);
		
		if ( $this->RefreshTokens() == FALSE )	// Token konnte nicht aktualisiert werden
            {
			$this->Debug("Update Token NOK",__FUNCTION__,__LINE__,false);	
			$this->RunAlarmScript($this->InstanceID,"Kein Zugang zu Withings-Server");
            return;
            }
		
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Update Data Get Device",0);         
		$this->GetDevice();
		
		if ( $this->ReadPropertyBoolean("CheckBoxMeas") == false )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Update Data Get Meas deaktiviert",0);
			}
		else
			{		
        	$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Update Data Get Meas",0);
			$this->GetMeas();
			}
		if ( $this->ReadPropertyBoolean("CheckBoxBodyGoals") == false )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Update Data Get Goals deaktiviert",0);
			}
		else
			{		
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Update Data Get Goals",0);
			$this->GetGoals();
			}
		if ( $this->ReadPropertyBoolean("CheckBoxSleepSummary") == false )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Update Data Get SleepSummary deaktiviert",0);
			}
		else
			{		
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Update Data Get SleepSummary",0);
			$this->GetSleepSummary(5);		// 5 Tage
			}
		if ( $this->ReadPropertyBoolean("CheckBoxActivity") == false )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Update Data Get Activity deaktiviert",0);
			}
		else
			{		
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Update Data Get Activity",0);
			$this->GetActivity();
			}
		if ( $this->ReadPropertyBoolean("CheckBoxActivity") == false )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Update Data Get IntradayActivity deaktiviert",0);
			}
		else
			{		
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Update Data Get IntradayActivity",0);
			// Null Uhr
			$startdate = mktime(0,0,0,date("n"),date("j"),date("Y")) - (60*60*24*4) ;	// 5 Tage
			$this->GetIntradayactivity(5,$startdate);
			}






		
		$this->GetNotifyList();

		$this->CleanDatabase();

		$endtime = time();
		
		$this->Debug("Update Data Laufzeit ".($endtime - $starttime) . " Sekunden" ,__FUNCTION__,__LINE__,true);


		return;
		
        $this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Update Data Get SleepSummary",0);
		$this->GetSleepSummary();

        $this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Update Data Get Activity",0);
		$this->GetActivity();

        $this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Update Data Get Intra",0);
		
		// Gestern Null Uhr
		$startdate = mktime(0,0,0,date("n"),date("j"),date("Y")) - (60*60*24*1);
		$this->GetIntradayactivity(1,$startdate);
		$this->GetIntradayactivity();	// Heute

		$this->SubscribeHook();

		
		if ( $this->ReadPropertyBoolean("Notifyaktiv") == false )
			{
			$this->DoNotifyRevokeAll();	
			}
		else
			$this->GetNotifySubscribe();	

		// $this->GetNonce();

		$this->CleanDatabase();

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
	//	Getgoals
	//******************************************************************************
	protected function GetGoals()
		{

		if ( $this->ReadPropertyBoolean("CheckBoxBodyGoals") == false )
        	{
            return false;
            }

		$access_token = IPS_GetProperty($this->InstanceID, "Naccess_token");
		$header = 'Authorization: Bearer ' . $access_token;
	
		// $this->SendDebug(__FUNCTION__.'['.__LINE__.']', "Access Token : ".$access_token , 0);
		// $this->SendDebug(__FUNCTION__.'['.__LINE__.']', "Header : ".$header , 0);
	
		$ch = curl_init();
curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,0);
curl_setopt($ch,CURLOPT_TIMEOUT,10);

		curl_setopt($ch, CURLOPT_URL, "https://wbsapi.withings.net/v2/user");

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		curl_setopt($ch, CURLOPT_HTTPHEADER, [ $header 
			]);
			
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([ 
				'action' => 'getgoals'
				]));

		$result = curl_exec($ch);
		curl_close($ch);

		$this->Debug($result,__FUNCTION__,__LINE__, true);


		$data = json_decode($result,TRUE); 

		if ( $this->CheckStatus($data) == false )
			return false;

		if ( $this->CheckBody($data) == false )
			return false;

		$ModulID = $this->InstanceID;

		$body = $data['body'];
	
		$this->DoGoals($ModulID,$body);

		}

	//******************************************************************************
	//	Getdevice
	//******************************************************************************
	protected function GetDevice()
		{

		$access_token = IPS_GetProperty($this->InstanceID, "Naccess_token");
		$header = 'Authorization: Bearer ' . $access_token;

		// $this->SendDebug(__FUNCTION__.'['.__LINE__.']', "Access Token : ".$access_token , 0);
		
		$ch = curl_init();
curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,0);
curl_setopt($ch,CURLOPT_TIMEOUT,10);

		curl_setopt($ch, CURLOPT_URL, "https://wbsapi.withings.net/v2/user");
			
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			
		curl_setopt($ch, CURLOPT_HTTPHEADER, [ $header 
				
			]);
			
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([ 
				'action' => 'getdevice'
			]));
			
		$result = curl_exec($ch);
		curl_close($ch);

		
		$this->Debug($result,__FUNCTION__,__LINE__,true);
		
		$data = json_decode($result,TRUE); 

		if ( $this->CheckStatus($data) == false )
			return false;

		$ModulID = $this->InstanceID;
		
		$body = $data['body'];

		$this->DoDevice($ModulID,$body);

		}
		
		
	//******************************************************************************
	//	New Getmeas
	//	Wenn $start nicht 0 dann kein LastData
	//******************************************************************************
	protected function GetMeas($tage = 5,$start=0)
		{

		$access_token = IPS_GetProperty($this->InstanceID, "Naccess_token");
		$header = 'Authorization: Bearer ' . $access_token;
	
		$category = 1;	// for real measures
		$meastypes = "";

        $startdate = time()- 24*60*60*$tage;
		$enddate = time();

		if ( $start != 0 )
			{
			$startdate = $start;
			$enddate = $start + ( $tage*24*60*60);	
			}
		
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Startdate : " . $this->TimestampToDate($startdate) . " Enddate : ".$this->TimestampToDate($enddate),0);	
			
		$ch = curl_init();
curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,0);
curl_setopt($ch,CURLOPT_TIMEOUT,10);

		curl_setopt($ch, CURLOPT_URL, "https://wbsapi.withings.net/measure");
			
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			
		curl_setopt($ch, CURLOPT_HTTPHEADER, [ $header ]);
			
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([ 
				'action' => 'getmeas',
				'category' => $category,
				'meastypes' => $meastypes,
				'startdate' => $startdate,
				'enddate' => $enddate
				
			]));
			
		$result = curl_exec($ch);
		curl_close($ch);

		$this->SendDebug(__FUNCTION__.'['.__LINE__.']', $result , 0);

		$data = json_decode($result,TRUE);
		
		if ( $this->CheckStatus($data) == false )
			return false;

		$ModulID = $this->InstanceID;

		$body = $data['body'];

		if ( $start == 0 )
			$NoLastData = false;
		else
			$NoLastData = true;
		
		$this->DoMeas($ModulID,$body,$NoLastData);

		return true;

		}



	//******************************************************************************
	//  GetActivity
	//******************************************************************************
	protected function GetActivity($tage = 5,$start=0)
		{

		if ( $this->ReadPropertyBoolean("BodyLogging") == false )
        	{
            return false;
            }
			
		$access_token = IPS_GetProperty($this->InstanceID, "Naccess_token");
		$header = 'Authorization: Bearer ' . $access_token;

		$startdate = time()- 24*60*60*$tage;
		$enddate = time();
	
		if ( $start != 0 )
			{
			$startdate = $start;
			$enddate = $start + ( $tage*24*60*60);	
			}
			
		$startdate = date("Y-m-d",time() - 24*60*60*$tage);
		$enddate   = date("Y-m-d",time());
		$enddate   = $this->GetYesterdayString();

		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Startdate : " . ($startdate) . " Enddate : ".($enddate),0);	
				
		$ch = curl_init();
curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,0);
curl_setopt($ch,CURLOPT_TIMEOUT,10);
	
		curl_setopt($ch, CURLOPT_URL, "https://wbsapi.withings.net/v2/measure");

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		
		curl_setopt($ch, CURLOPT_HTTPHEADER, [ $header ]);
		
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([ 
			'action' => 'getactivity',
			'startdateymd' => $startdate,
			'enddateymd' => $enddate
		]));
		
		$result = curl_exec($ch);
		curl_close($ch);
			

		$this->SendDebug(__FUNCTION__.'['.__LINE__.']', $result , 0);

		$data = json_decode($result,TRUE);
		
		if ( $this->CheckStatus($data) == false )
			return false;

		$ModulID = $this->InstanceID;

		$body = $data['body'];

		if ( $start == 0 )
			$NoLastData = false;
		else
			$NoLastData = true;	


		$this->DoActivity($ModulID,$body,$NoLastData);

		return true;

		}

	//******************************************************************************
	//  Getintradayactivity
	//  $tage = Anzahl der Tage
	//  $start = Start Timestamp
	//******************************************************************************
	protected function GetIntradayactivity($tage = 1,$start=0)
		{

		if ( $this->ReadPropertyBoolean("BodyLogging") == false )
        	{
            return false;
            }


		$access_token = IPS_GetProperty($this->InstanceID, "Naccess_token");
		$header = 'Authorization: Bearer ' . $access_token;
	
		$datafields = "steps,elevation,calories,distance,stroke,pool_lap,duration,heart_rate,spo2_auto";
		$datafields = "";
		

		for($tag=0;$tag<$tage;$tag++)
			{		


			if ( $start != 0 )
				{
				$startdate = $start + ( $tag*24*60*60); 
				$enddate = $start + ( ($tag+1)*24*60*60) - 1;	
				}
			else
				{

				// Heute Null Uhr
				$startdate = mktime(0,0,0,date("n"),date("j"),date("Y"));
				$startdate = $startdate - (24*60*60*$tag);
				$enddate = $startdate + (24*60*60);

				}	

			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Tag:".$tag."-".date('d.m.Y H:i:s ',$startdate)." - ".date('d.m.Y H:i:s ',$enddate),0);

			$ch = curl_init();
curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,0);
curl_setopt($ch,CURLOPT_TIMEOUT,10);
		
			curl_setopt($ch, CURLOPT_URL, "https://wbsapi.withings.net/v2/measure");
		
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				
			curl_setopt($ch, CURLOPT_HTTPHEADER, [ $header ]);
				
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([ 
					'action' => 'getintradayactivity',
					'startdate' => $startdate,
					'enddate' => $enddate,
					'data_fields' => $datafields
				]));
				
			$result = curl_exec($ch);
			curl_close($ch);
							
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']', $result , 0);
	
			$data = json_decode($result,TRUE);
		
			if ( $this->CheckStatus($data) == false )
				return false;
	
			$ModulID = $this->InstanceID;
	
			$body = $data['body'];
	
			if ( $start == 0 )
				$NoLastData = false;
			else
				$NoLastData = true;	
	
			$this->DoGetintradayactivity($ModulID,$body,$NoLastData);
	
			}

		}

	//******************************************************************************
	//	GetSleepSummary
	//******************************************************************************
	protected function GetSleepSummary($tage = 1,$start=0)
		{

		if ( $this->ReadPropertyBoolean("BloodLogging") == false )
			return false;

		$access_token = IPS_GetProperty($this->InstanceID, "Naccess_token");
		$header = 'Authorization: Bearer ' . $access_token;
	
		// $this->SendDebug(__FUNCTION__.'['.__LINE__.']', "Access Token : ".$access_token , 0);

		$datafields = "nb_rem_episodes,sleep_efficiency,sleep_latency,total_sleep_time,total_timeinbed,wakeup_latency,waso,apnea_hypopnea_index,breathing_disturbances_intensity,";
		$datafields = $datafields . "asleepduration,deepsleepduration,durationtosleep,durationtowakeup,lightsleepduration,night_events,out_of_bed_count,remsleepduration,sleep_score,";
		$datafields = $datafields . "snoring,snoringepisodecount,wakeupcount,wakeupduration,";
		$datafields = $datafields . "hr_average,hr_min,hr_max,rr_average,rr_min,rr_max";

		$action = "getsummary";

		$startdate = time() - 24*60*60*$tage;
		$enddate   = time();

		$startdate = date("Y-m-d",$startdate);
		$enddate   = date("Y-m-d",$enddate);

		if ( $start != 0 )
			{
			$startdate = date("Y-m-d",$start);
			$enddate = date("Y-m-d",($start + ( $tage*24*60*60)));	
			}
		
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Startdate : " . $startdate. " Enddate : ".$enddate,0);


		$ch = curl_init();
curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,0);
curl_setopt($ch,CURLOPT_TIMEOUT,10);

		curl_setopt($ch, CURLOPT_URL, "https://wbsapi.withings.net/v2/sleep");
			
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			
		curl_setopt($ch, CURLOPT_HTTPHEADER, [ $header ]);
			
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([ 
				'action' => $action,
				'startdateymd' => $startdate,
				'enddateymd' => $enddate,
				'data_fields' => $datafields
			]));
		
		$result = curl_exec($ch);
  
		curl_close($ch);
			
		$this->Debug($result ,__FUNCTION__,__LINE__, true);
		
		$data = json_decode($result,TRUE); 

		if ( $this->CheckStatus($data) == false )
			return false;
		
		$ModulID = $this->InstanceID;

		$data = $data['body'];

		if ( $start == 0 )
			$NoLastData = false;
		else
			$NoLastData = true;

		$this->DoSleepSummary($ModulID,$data,$NoLastData);

		return true;

		}
		
		


	//******************************************************************************
	//	GetSleepSummary Old
	//******************************************************************************
	protected function GetSleepSummaryOld($tage = 1,$start=0)
		{

		if ( $this->ReadPropertyBoolean("BloodLogging") == false )
			return false;

		$access_token = $this->ReadPropertyString("Userpassword");

		
		$startdate = time() - 24*60*60*$tage;
		$enddate   = time();

		$startdate = date("Y-m-d",$startdate);
		$enddate   = date("Y-m-d",$enddate);

		if ( $start != 0 )
			{
			$startdate = date("Y-m-d",$start);
			$enddate = date("Y-m-d",($start + ( $tage*24*60*60)));	
			}
		
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Startdate : " . ($startdate) . " Enddate : ".($enddate),0);	
			

		$datafields = "wakeupduration,lightsleepduration,deepsleepduration,remsleepduration,wakeupcount,durationtosleep,durationtowakeup,hr_average,hr_min,hr_max,rr_average,rr_min,rr_max";

		$url = "https://wbsapi.withings.net/v2/sleep?action=getsummary&access_token=".$access_token."&startdateymd=".$startdate."&enddateymd=".$enddate."&data_fields=".$datafields;

		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',$url,0);

		$output = $this->DoCurl($url);

		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',$output,0);

		$this->Logging($output);
		// $this->LoggingExt($output,"sleep.log",false,false);

		$data = json_decode($output,TRUE); 

		if ( !array_key_exists('status',$data) == TRUE )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Status: unbekannt",0);
			return false;
			}

		$status = $data['status'];

		if ( $status != 0)
        	{
            return false;
            }

		$id = $this->GetIDForIdent("name");

		$ModulID = IPS_GetParent($id);

		$data = $data['body'];

		$this->DoSleepSummary($ModulID,$data);


		return true;
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

		$ProfilAsso = IPS_GetVariableProfile($Name);
		$ProfilAsso = $ProfilAsso['Associations'];
		
		if ( $ProfilAsso == false )
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

		$ProfilAsso = IPS_GetVariableProfile($Name);
		$ProfilAsso = $ProfilAsso['Associations'];
		
		if ( $ProfilAsso == false )
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
	//  Save Access Token
	//**************************************************************************
	private function SaveAccessToken($token)
		{
		
		$ordner = IPS_GetLogDir() . "Withings";
		if ( !is_dir ( $ordner ) )
			mkdir($ordner);

		if ( !is_dir ( $ordner ) )
			return;

		$logdatei = IPS_GetLogDir() . "Withings/Access.Token";
		$datei = fopen($logdatei,"a+");
		fwrite($datei, $token .chr(13));
		fclose($datei);
		}

	//**************************************************************************
	//  Save Refresh Token
	//**************************************************************************
	private function SaveRefreshToken($token)
	{
	
	$ordner = IPS_GetLogDir() . "Withings";
	if ( !is_dir ( $ordner ) )
		mkdir($ordner);

	if ( !is_dir ( $ordner ) )
		return;

	$logdatei = IPS_GetLogDir() . "Withings/Refresh.Token";
	$datei = fopen($logdatei,"a+");
	fwrite($datei, $token .chr(13));
	fclose($datei);
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
				if ( $this->ReadPropertyBoolean("Logging") == true )
					IPS_Logmessage("Withingsmodul","Profiltyp falsch : " . $Name . " Ist : ".$profile['ProfileType']. " Soll : ".$Typ);
				//throw new Exception("Variable profile type does not match for profile ".$Name);

				}
			}

		
		$ProfilArray = IPS_GetVariableProfile ($Name);
		$AktIcon = $ProfilArray['Icon'];

	

		if ( $AktIcon == false )	// Icon bereits definiert, nicht ueberschreiben
			{
			IPS_SetVariableProfileIcon($Name, $Icon);
			}
		
		IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
		IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
 
		if ( $Typ == 2 )
			{
				
			IPS_SetVariableProfileDigits($Name, $Digits);

			}
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
	 protected function DoSleepSummary($ModulID,$data,$NoLastData=false)
		{
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Schlaf wird ausgewertet.",0);
		
		$InstanceIDSleep = @$this->GetIDForIdent("SleepMonitor");
		/*
		if ( $InstanceIDSleep === false )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"InstanceID Sleep nicht vorhanden. Abbruch",0);
			// return;	
			}
		*/ 

		$data 		= @$data['series'];
		
		if ( $data == false )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Keine Schlafdaten gefunden. Abbruch",0);				
			return;
			}

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

		$ReaggregationsArray = array();		// Array fuer Reaggregation
		$RequestReAggregation = false;

		// print_r($data);
		$l = count($data) -1;
		$datas = @$data[$l]['data'];
		$y = 0;
		foreach($datas as $key => $x)
			{
			// $this->SendDebug(__FUNCTION__.'['.__LINE__.']',"[".$y."] Data : " . $key ." [".$x."]",0);	
			$y++;	

			}

		$TypeArrayData = array();

		$count = count($data)-1;
		foreach($data as $key => $sleep)
			{
				
			$sleepmodel      = @$sleep['model'];
			$sleepstartdate  = @$sleep['startdate'];
			$deviceid  		 = @$sleep['hash_deviceid'];
			$timestamp       = @$sleep['date'];
			$sleependdate    = @$sleep['enddate'];
			$sleepmodified   = @$sleep['modified'];
			$sleep_dauer      = intval($sleependdate -$sleepstartdate)/60;


			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',$timestamp. " - ".$this->TimestampToDate($sleepstartdate). " - ".$this->TimestampToDate($sleependdate),0);

			$time = strtotime($timestamp);



			// Count 27
			$sleep_wakeupduration    				= intval  (@$sleep['data']['wakeupduration'])/60;				// * Time spent awake (in seconds)
			$sleep_wakeupcount       				= intval  (@$sleep['data']['wakeupcount']);						// * Number of times the user woke up while in bed. Does not include the number of times the user got out of bed
			$sleep_durationtosleep   				= intval  (@$sleep['data']['durationtosleep'])/60;				// Time to sleep (in seconds). (deprecated)	
			$sleep_remsleepduration  				= intval  (@$sleep['data']['remsleepduration'])/60;				// * Duration in state REM sleep (in seconds)
			$sleep_durationtowakeup  				= intval  (@$sleep['data']['durationtowakeup'])/60;				// Time to wake up (in seconds). (deprecated)
			$sleep_totaltimeinbed    				= intval  (@$sleep['data']['total_timeinbed'])/60;				// * Total time spent in bed
			$sleep_totalsleeptime    				= intval  (@$sleep['data']['total_sleep_time'])/60;				// * Total time spent asleep. Sum of light, deep and rem durations
			$sleep_sleep_efficiency        			= intval  (@$sleep['data']['sleep_efficiency']*100);			// * Ratio of the total sleep time over the time spent in bed
			$sleep_sleep_latency           			= intval  (@$sleep['data']['sleep_latency'])/60;				// Time spent in bed before falling asleep
			$sleep_wakeuplatency     				= intval  (@$sleep['data']['wakeup_latency']);					// Time spent in bed after waking up
			$sleep_waso              				= intval  (@$sleep['data']['waso'])/60;							// Time spent awake in bed after falling asleep for the 1st time during the night
			$sleep_nbremepisodes     				= intval  (@$sleep['data']['nb_rem_episodes']);					// * Count of the REM sleep phases
			$sleep_outofbedcount     				= intval  (@$sleep['data']['out_of_bed_count']);				// * Number of times the user got out of bed during the night
			$sleep_lightsleepduration				= intval  (@$sleep['data']['lightsleepduration'])/60;			// * Duration in state light sleep (in seconds)		
			$sleep_deepsleepduration 				= intval  (@$sleep['data']['deepsleepduration'])/60;			// * Duration in state deep sleep (in seconds)			
			$sleep_hraverage         				= intval  (@$sleep['data']['hr_average']);						// * Average heart rate
			$sleep_hrmin         					= intval  (@$sleep['data']['hr_min']);							// * Minimal heart rate
			$sleep_hrmax             				= intval  (@$sleep['data']['hr_max']);							// * Maximal heart rate
			$sleep_rraverage         				= intval  (@$sleep['data']['rr_average']);						// * Average respiration rate
			$sleep_rrmin         					= intval  (@$sleep['data']['rr_min']);							// * Minimal respiration rate
			$sleep_rrmax             				= intval  (@$sleep['data']['rr_max']);							// * Maximal respiration rate
			$sleep_breathing_disturbances			= intval  (@$sleep['data']['breathing_disturbances_intensity']);// * Wellness metric, available for all Sleep and Sleep Analyzer devices. Intensity of
			$sleep_snoring             				= intval  (@$sleep['data']['snoring'])/60;						// * Total snoring time
			$sleep_snoringepisodecount             	= intval  (@$sleep['data']['snoringepisodecount']);				// * Numbers of snoring episodes of at least one minute
			$sleep_sleep_score             			= intval  (@$sleep['data']['sleep_score']);						// * Sleep score
			$sleep_nightevents             			= intval  (@$sleep['data']['night_events']);					// Events list happened during the night
			$sleep_apneahypopneaindex             	= intval  (@$sleep['data']['apnea_hypopnea_index']);			// * Medical grade AHI. Only available for devices purchased in Europe and Australia


			$lastdata = false;
			if ( $key == $count)
				$lastdata = true;

			if ( $NoLastData == true )
				$lastdata = false;
	
			$datass = array(); $datas = array();
				
			// nicht in datas, errechnet
			$datass = ['timestamp'=>$time,'value'=>$sleep_dauer,					'deviceid'=>$deviceid,'ident'=>'schlafdauer',			'profil'=>'WITHINGS_M_Minuten',	'name'=>'Start bis Ende',				'lastdata'=>$lastdata];	array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();	
			$datass = ['timestamp'=>$time,'value'=>$sleepstartdate,					'deviceid'=>$deviceid,'ident'=>'startzeit',				'profil'=>'~UnixTimestamp',		'name'=>'Startzeit',				'lastdata'=>$lastdata];	array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();	
			$datass = ['timestamp'=>$time,'value'=>$sleependdate,					'deviceid'=>$deviceid,'ident'=>'endezeit',				'profil'=>'~UnixTimestamp',		'name'=>'Endezeit',				'lastdata'=>$lastdata];	array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();	


			// 
			$datass = ['timestamp'=>$time,'value'=>$sleep_sleep_latency,			'deviceid'=>$deviceid,'ident'=>'einschlafzeit',			'profil'=>'WITHINGS_M_Minuten',	'name'=>'Zeit zum Schlafen',			'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();	

			$datass = ['timestamp'=>$time,'value'=>$sleep_wakeuplatency,			'deviceid'=>$deviceid,'ident'=>'leichtschlafphasen',	'profil'=>'WITHINGS_M_Minuten',	'name'=>'Leichtschlafphasen',		'lastdata'=>$lastdata];	array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();	
			$datass = ['timestamp'=>$time,'value'=>$sleep_remsleepduration,			'deviceid'=>$deviceid,'ident'=>'remschlafphasen',		'profil'=>'WITHINGS_M_Minuten',	'name'=>'REM Schlafphasen',			'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();
			$datass = ['timestamp'=>$time,'value'=>$sleep_deepsleepduration,		'deviceid'=>$deviceid,'ident'=>'tiefschlafphasen',		'profil'=>'WITHINGS_M_Minuten',	'name'=>'Tief Schlafphasen',			'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();
			$datass = ['timestamp'=>$time,'value'=>$sleep_lightsleepduration,		'deviceid'=>$deviceid,'ident'=>'leichtschlafphasen',	'profil'=>'WITHINGS_M_Minuten',	'name'=>'Leicht Schlafphasen',		'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();

			$datass = ['timestamp'=>$time,'value'=>$sleep_durationtowakeup,			'deviceid'=>$deviceid,'ident'=>'aufstehzeit',			'profil'=>'WITHINGS_M_Minuten',	'name'=>'Zeit zum Aufstehen',				'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();

			$datass = ['timestamp'=>$time,'value'=>$sleep_waso,						'deviceid'=>$deviceid,'ident'=>'waso',					'profil'=>'WITHINGS_M_Minuten',	'name'=>'Wachzeit nach Schlafbeginn',				'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();

			$datass = ['timestamp'=>$time,'value'=>$sleep_wakeupduration,			'deviceid'=>$deviceid,'ident'=>'wachphasen',			'profil'=>'WITHINGS_M_Minuten',	'name'=>'Wachphasen',				'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();
			$datass = ['timestamp'=>$time,'value'=>$sleep_wakeupcount,				'deviceid'=>$deviceid,'ident'=>'schlafunterbrechungen',	'profil'=>'WITHINGS_M_Anzahl',	'name'=>'Schlafunterbrechungen',	'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();
			$datass = ['timestamp'=>$time,'value'=>$sleep_hraverage,				'deviceid'=>$deviceid,'ident'=>'herzschlagdurchschnitt','profil'=>'WITHINGS_M_Puls',	'name'=>'Durchschnittliche Herzfrequenz',	'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();
			$datass = ['timestamp'=>$time,'value'=>$sleep_hrmin,					'deviceid'=>$deviceid,'ident'=>'herzschlagminimal',		'profil'=>'WITHINGS_M_Puls',	'name'=>'Minimal Herzfrequenz',		'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();
			$datass = ['timestamp'=>$time,'value'=>$sleep_hrmax,					'deviceid'=>$deviceid,'ident'=>'herzschlagmaximal',		'profil'=>'WITHINGS_M_Puls',	'name'=>'Maximale Herzfrequenz',		'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();
			$datass = ['timestamp'=>$time,'value'=>$sleep_rraverage,				'deviceid'=>$deviceid,'ident'=>'atemzuegedurchschnitt',	'profil'=>'WITHINGS_M_Atmung',	'name'=>'Durchschnittliche Atemfrequenz',		'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();
			$datass = ['timestamp'=>$time,'value'=>$sleep_rrmin,					'deviceid'=>$deviceid,'ident'=>'atemzuegeminimal',		'profil'=>'WITHINGS_M_Atmung',	'name'=>'Minimale Atemfrequenz',			'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();
			$datass = ['timestamp'=>$time,'value'=>$sleep_rrmax,					'deviceid'=>$deviceid,'ident'=>'atemzuegemaximal',		'profil'=>'WITHINGS_M_Atmung',	'name'=>'Maximale Atemfrequenz',			'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();
			$datass = ['timestamp'=>$time,'value'=>$sleep_sleep_efficiency,			'deviceid'=>$deviceid,'ident'=>'schlafeffienz',			'profil'=>'WITHINGS_M_IntProzent',	'name'=>'Schlafeffienz',							'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();

			$datass = ['timestamp'=>$time,'value'=>$sleep_totaltimeinbed,			'deviceid'=>$deviceid,'ident'=>'totalezeitimbett',		'profil'=>'WITHINGS_M_Minuten',	'name'=>'Im Bett',		'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();
			$datass = ['timestamp'=>$time,'value'=>$sleep_totalsleeptime,			'deviceid'=>$deviceid,'ident'=>'totalschlafdauer',		'profil'=>'WITHINGS_M_Minuten',	'name'=>'Schlafdauer Gesamt',		'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();
			$datass = ['timestamp'=>$time,'value'=>$sleep_snoring,					'deviceid'=>$deviceid,'ident'=>'schnarchen',			'profil'=>'WITHINGS_M_Minuten',	'name'=>'Schnarchen',		'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();
			$datass = ['timestamp'=>$time,'value'=>$sleep_snoringepisodecount,		'deviceid'=>$deviceid,'ident'=>'schnarchepisoden',		'profil'=>'WITHINGS_M_Anzahl',	'name'=>'Schnarchepisoden',			'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();
			$datass = ['timestamp'=>$time,'value'=>$sleep_sleep_score ,				'deviceid'=>$deviceid,'ident'=>'schlafscore',			'profil'=>'WITHINGS_M_Anzahl',	'name'=>'Schlaf Bewertung',			'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();

			$datass = ['timestamp'=>$time,'value'=>$sleep_apneahypopneaindex ,		'deviceid'=>$deviceid,'ident'=>'schlafapnoe',			'profil'=>'WITHINGS_M_Anzahl',	'name'=>'Medizinischer Grad AHI',			'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();
			$datass = ['timestamp'=>$time,'value'=>$sleep_breathing_disturbances ,	'deviceid'=>$deviceid,'ident'=>'atmungsstoerung',		'profil'=>'WITHINGS_M_Anzahl',	'name'=>'Atmungsstoerungen',			'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();

			$datass = ['timestamp'=>$time,'value'=>$sleep_nbremepisodes,			'deviceid'=>$deviceid,'ident'=>'remepisoden',			'profil'=>'WITHINGS_M_Anzahl',	'name'=>'Anzahl REM Schlafphasen',	'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();
			$datass = ['timestamp'=>$time,'value'=>$sleep_outofbedcount,			'deviceid'=>$deviceid,'ident'=>'aufstehanzahl',			'profil'=>'WITHINGS_M_Anzahl',	'name'=>'Aufstehanzahl',	'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();


			}


		$x = 0 ;	
		$last_position = count($TypeArrayData) -1 ;
		foreach($TypeArrayData as $key => $Sleepdata)
			{
			
			$InstanceIDDeviceID = $Sleepdata['deviceid']; 
			$lastdata = $Sleepdata['lastdata']; 
			$name = $Sleepdata['name']; 
			$value = $Sleepdata['value'];
			$profil = $Sleepdata['profil'];
			$ident = $Sleepdata['ident'];
			$timestamp = $Sleepdata['timestamp'];
			$deviceid = $Sleepdata['deviceid'];
			$InstanceIDDeviceID = @$this->GetIDForIdent($deviceid);

			if ( $InstanceIDDeviceID == false )
				continue;	


			if (  $key == $lastdata  AND $key != 0 )
				{
				//if ( $this->ReadPropertyBoolean("ExtDebug") == true )
				//	$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"[".$x."]".$key." : ".$InstanceIDDeviceID.":".$this->TimestampToDate($timestamp) ." - - ".$deviceid." - ".$value." - ". $ident." -  - ".$profil." - ".$name." - ".$lastdata,0);
				$x++;
				}

			$ID = $InstanceIDDeviceID;
			if ( $lastdata == true )
				{
					/*
				$status = $this->CheckDataYesterdayExist($timestamp);
				if ( $status == false )
					$value = 0;
				else 
				*/
					$this->SetValueToVariable($ID,$name ,$value ,$profil ,10,DATA_TO_VARIABLE,$profil,$ident);
				}
			else
				{
				$RequestReAggregation = false;
				$RequestReAggregation = $this->SetValueToVariable($ID,$name ,$value ,$profil ,10,DATA_TO_DATABASE,$timestamp,$ident);
				// $RequestReAggregation = true;
				if ( $RequestReAggregation == true )
					{
					$VariableID = @IPS_GetObjectIDByIdent($ident,$ID );
					array_push($ReaggregationsArray,$VariableID);
					}
				}	

			if ( $key == $last_position )
				{
				$this->SetValueToVariable($ID,"Updatezeit" ,time() ,"~UnixTimestamp"  ,0,DATA_TO_VARIABLE,false,"timestamp");
				}
				
				
			}
			
		if ( count($ReaggregationsArray) > 0 )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"RequestReAggregation erforderlich:",0);
			$this->Reaggregieren($ReaggregationsArray);
			}	
		else
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"RequestReAggregation nicht erforderlich",0);
			}			

	}


	//**************************************************************************
	//	Auswertung Activity
	//**************************************************************************
	 protected function DoActivity($ModulID,$data,$NoLastData=false)
		{
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Aktivitaeten werden ausgewertet.",0);

		// "Activity" ist das Ident fuer externe Daten in Withings deviceid = null
		$InstanceIDActivity = @$this->GetIDForIdent("Activity");
		if ( $InstanceIDActivity === FALSE )
			$InstanceIDActivity = $this->CreateDummyInstance("Activity","Activity","Daten von externen APPs");

		if ( $InstanceIDActivity === false )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"InstanceID Activity nicht vorhanden. Abbruch",0);
			return;
			}

		$daten = @$data['activities'];
		
		$TypeArrayData = array();

		$count = count($daten)-1;
		$y = 0;
		foreach($daten as $key => $x)
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"[".$y."] Data : " . $key ." " . $x['date'],0);	
			$y++;	
			}


		if ( @count($daten) == 0 )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Keine Activity gefunden. Abbruch",0);
			return false;
			}
		else
			{

			$measuregrpsstart =  @$daten[0]['date'];
			$measuregrpsstart = $this->DateToTimestamp($measuregrpsstart);
			$measuregrpsstart = $this->TimestampToDate($measuregrpsstart);	
			$measuregrpsende = @$daten[count($daten)-1]['date'];
			$measuregrpsende = $this->DateToTimestamp($measuregrpsende);
			$measuregrpsende = $this->TimestampToDate($measuregrpsende);	
					
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Anzahl der Measuregrps : ".count($daten) . " von " . $measuregrpsstart. " bis " . $measuregrpsende,0);	
			}
		
		$ReaggregationsArray = array();		// Array fuer Reaggregation
		$RequestReAggregation = false;

		$datass = array(); $datas = array();

	
		foreach($daten as $key => $activity)
			{

			$lastdata = false;
			if ( $key == $count)
				$lastdata = true;
		
			if ( $NoLastData == true )
				$lastdata = false;
		

			$activitydate = @$activity['date'];
			$time = strtotime($activitydate);

			// print_r($activity);
			$activitysteps			= intval(@$activity['steps']);
			$activitydistance		= floatval(@$activity['distance']);
			$activityelevation		= intval(@$activity['elevation']);
			$activitysoft			= intval(@$activity['soft']/60);
			$activitymoderate		= intval(@$activity['moderate']/60);
			$activityintense		= intval(@$activity['intense']/60);
			$activitycalories		= floatval(@$activity['calories']);
			$activitytotalcalories  = floatval(@$activity['totalcalories']);
			$activitybrand			= strval(@$activity['brand']);
			$activityistracker		= boolval(@$activity['is_tracker']);
			$activityactive 		= intval(@$activity['active']/60);
		
			
			$datass = ['timestamp'=>$time,'value'=>$activitysteps, 			'ident'=>'schritte',			'profil'=>'WITHINGS_M_Schritte',	'name'=>'Schritte',							'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();	
			$datass = ['timestamp'=>$time,'value'=>$activitydistance, 		'ident'=>'distanze',			'profil'=>'WITHINGS_M_Meter',		'name'=>'Distanze',							'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();	
			$datass = ['timestamp'=>$time,'value'=>$activityelevation, 		'ident'=>'hoehenmeter',			'profil'=>'WITHINGS_M_Stockwerke',	'name'=>'Stockwerke',						'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();	
			$datass = ['timestamp'=>$time,'value'=>$activitysoft, 			'ident'=>'geringeaktivitaet',	'profil'=>'WITHINGS_M_Minuten',		'name'=>'Geringe Aktivitaet',				'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();	
			$datass = ['timestamp'=>$time,'value'=>$activitymoderate, 		'ident'=>'mittlereaktivitaet',	'profil'=>'WITHINGS_M_Minuten',		'name'=>'Mittlere Aktivitaet',				'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();	
			$datass = ['timestamp'=>$time,'value'=>$activityintense, 		'ident'=>'hoheaktivitaet',		'profil'=>'WITHINGS_M_Minuten',		'name'=>'Hohe Aktivitaet',					'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();	
			$datass = ['timestamp'=>$time,'value'=>$activitycalories, 		'ident'=>'aktivitaetskalorien',	'profil'=>'WITHINGS_M_Kalorien',	'name'=>'Verbrannte Kalorien',				'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();	
			$datass = ['timestamp'=>$time,'value'=>$activitytotalcalories, 	'ident'=>'gesamtkalorien',		'profil'=>'WITHINGS_M_Kalorien',	'name'=>'Gesamt Kalorien',					'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();	
			$datass = ['timestamp'=>$time,'value'=>$activitybrand, 			'ident'=>'brand',				'profil'=>'~TextBox',				'name'=>'Marke',							'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();	
			$datass = ['timestamp'=>$time,'value'=>$activityistracker, 		'ident'=>'istracker',			'profil'=>'~Switch',				'name'=>'Ist Tracker',						'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();	
			$datass = ['timestamp'=>$time,'value'=>$activityactive, 		'ident'=>'active',				'profil'=>'WITHINGS_M_Minuten',		'name'=>'Summe Hohe/Mittlere Aktivitaet',	'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();	
			$datass = ['timestamp'=>$time,'value'=>$time, 					'ident'=>'last_session_date',	'profil'=>'~UnixTimestamp',			'name'=>'Letzte Verbindung',				'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();	

		
			}	


			// print_r($TypeArrayData);
			$this->LoggingExt("=====",$file="Activity.log",true );

			$x = 0 ;	
			$last_position = count($TypeArrayData) -1 ;
			
			foreach($TypeArrayData as $key => $Activitydata)
				{
	 
				$lastdata = $Activitydata['lastdata']; 
				$name = $Activitydata['name']; 
				$value = $Activitydata['value'];
				$profil = $Activitydata['profil'];
				$ident = $Activitydata['ident'];
				$timestamp = $Activitydata['timestamp'];
				$deviceid = 'Activity';
				$InstanceIDDeviceID = @$this->GetIDForIdent($deviceid);
				$ID = $InstanceIDDeviceID;
		
				$DebugText = "[".$x."]".$key." : ".$InstanceIDDeviceID.":".$this->TimestampToDate($timestamp) ." - ".$deviceid." - ".$value." - ". $ident." - ".$profil." - ".$name." - ".$lastdata;
				
				if ( $key < 12 )
					{
						// $this->LoggingExt("Start",$file="Activity-".$ident.".log",true );
				
					}
				//$this->LoggingExt($DebugText,$file="Activity-".$ident.".log" );
				//$this->LoggingExt($DebugText,$file="Activity.log" );

				
				if (  ( $key == $lastdata  AND $key != 0 ) )
					{
					if ( $this->ReadPropertyBoolean("ExtDebug") == true )
						$this->SendDebug(__FUNCTION__.'['.__LINE__.']',$DebugText,0);
					$x++;
					}
	

				if ( $lastdata == true )
					{
					
					$status = $this->CheckDataYesterdayExist($timestamp);
					if ( $status == true ) 
						$this->SetValueToVariable($ID,$name ,$value ,$profil ,10,DATA_TO_VARIABLE,$profil,$ident);
					}
				else
					{
					
					$RequestReAggregation = false;
					$RequestReAggregation = $this->SetValueToVariable($ID,$name ,$value ,$profil ,10,DATA_TO_DATABASE,$timestamp,$ident);
					
					if ( $RequestReAggregation == true )
						{ 
						$VariableID = @IPS_GetObjectIDByIdent($ident,$ID );
						array_push($ReaggregationsArray,$VariableID);
						}
					}	
				
				if ( $key == $last_position )
					{
					$this->SetValueToVariable($ID,"Updatezeit" ,time() ,"~UnixTimestamp"  ,0,DATA_TO_VARIABLE,false,"timestamp");
					}
				
				}
				
			// print_r($ReaggregationsArray);

			if ( count($ReaggregationsArray) > 0 )
				{
				$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"RequestReAggregation erforderlich:",0);
				$this->Reaggregieren($ReaggregationsArray);
				}	
			else
				{
				$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"RequestReAggregation nicht erforderlich",0);
				}			

		return;

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
			$this->SendDebug("DoActivity","Datum : ".			$activitydate,0);
			$this->SendDebug("DoActivity","Schritte : ".		$activitysteps,0);
			$this->SendDebug("DoActivity","Distanze : ".		$activitydistance,0);
			$this->SendDebug("DoActivity","Hoehe : ".			$activityelevation,0);
			$this->SendDebug("DoActivity","Soft : ".			$activitysoft,0);
			$this->SendDebug("DoActivity","Moderate : ".		$activitymoderate,0);
			$this->SendDebug("DoActivity","Intense : ".			$activityintense,0);
			$this->SendDebug("DoActivity","Kalorien : ".		$activitycalories,0);
			$this->SendDebug("DoActivity","Gesamtkalorien : ".	$activitytotalcalories,0);
			$this->SendDebug("DoActivity","Brand : ".			$activitybrand,0);
            */

			$timestamp = intval(strtotime ($activitydate));

			// Daten asynchron in Datenbank  ( Testweise )

			//if(isset($activitydate))			$RequestReAggregation = $this->SetValueToVariable($InstanceIDActivity,"Updatezeit"			,intval(strtotime ($activitydate))	,"~UnixTimestamp"		,1	,true,$timestamp,"timestamp");
			if(isset($activitysteps))			$RequestReAggregation = $this->SetValueToVariable($InstanceIDActivity,"Schritte"			,intval($activitysteps)				,"WITHINGS_M_Schritte"	,2	,true,$timestamp,"schritte");
			if(isset($activitydistance))		$RequestReAggregation = $this->SetValueToVariable($InstanceIDActivity,"Distanze"			,intval($activitydistance)			,"WITHINGS_M_Meter"		,3	,true,$timestamp,"distanze");
			if(isset($activityelevation))		$RequestReAggregation = $this->SetValueToVariable($InstanceIDActivity,"Hoehenmeter"			,intval($activityelevation)			,"WITHINGS_M_Meter"		,4	,true,$timestamp,"hoehenmeter");
			if(isset($activitycalories))		$RequestReAggregation = $this->SetValueToVariable($InstanceIDActivity,"Aktivitaetskalorien"	,intval($activitycalories)			,"WITHINGS_M_Kalorien"	,10	,true,$timestamp,"aktivitaetskalorien");
			if(isset($activitytotalcalories))	$RequestReAggregation = $this->SetValueToVariable($InstanceIDActivity,"Gesamtkalorien"		,intval($activitytotalcalories)		,"WITHINGS_M_Kalorien"	,11	,true,$timestamp,"gesamtkalorien");
			if(isset($activitysoft))			$RequestReAggregation = $this->SetValueToVariable($InstanceIDActivity,"Geringe Aktivitaet"	,intval($activitysoft/60 )			,"WITHINGS_M_Minuten"	,20	,true,$timestamp,"geringeaktivitaet");
			if(isset($activitymoderate))		$RequestReAggregation = $this->SetValueToVariable($InstanceIDActivity,"Mittlere Aktivitaet"	,intval($activitymoderate/60 )		,"WITHINGS_M_Minuten"	,21	,true,$timestamp,"mittlereaktivitaet");
			if(isset($activityintense))			$RequestReAggregation = $this->SetValueToVariable($InstanceIDActivity,"Hohe Aktivitaet"		,intval($activityintense/60 )		,"WITHINGS_M_Minuten"	,22	,true,$timestamp,"hoheaktivitaet");
			

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
	 protected function DoGetintradayactivity($ModulID,$data,$NoLastData=false)
		{
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Aktivitaeten werden ausgewertet.",0);

		// "IntraDayActivity" ist das Ident fuer externe Daten in Withings deviceid = null

		$InstanceIDActivity = @$this->GetIDForIdent ("IntraDayActivity");
		if ( $InstanceIDActivity === FALSE )
			$InstanceIDActivity = $this->CreateDummyInstance("IntraDayActivity","IntraDayActivity","Daten von externen APPs");

		if ( $InstanceIDActivity === false )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"InstanceID IntraDayActivity nicht vorhanden. Abbruch",0);
			return;
			}

		$data = @$data['series'];
		$keys = array_keys($data);
            
		$counter = 0;
		foreach($keys as $key )
			{ 
			if ( $counter == 0 )
				$measuregrpsstart = date('d.m.Y H:i:s ',$key);
			
			$measuregrpsende = date('d.m.Y H:i:s ',$key);
			$counter++;	
			}	
		
		if ( @count($data) == 0 )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',$InstanceIDActivity." Keine Activity gefunden. Abbruch",0);
			return false;
			}
		else
			{
			
			// 	$this->SendDebug(__FUNCTION__.'['.__LINE__.']',$InstanceIDActivity."Anzahl der Serien : ".count($data),0);
			
			// $measuregrpsstart =  @$data[0];
			$measuregrpsstart = $this->DateToTimestamp($measuregrpsstart);
			$measuregrpsstart = $this->TimestampToDate($measuregrpsstart);	
			// $measuregrpsende = @$data[count($data)-1]['date'];
			$measuregrpsende = $this->DateToTimestamp($measuregrpsende);
			$measuregrpsende = $this->TimestampToDate($measuregrpsende);	
					
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Anzahl der Measuregrps : ".count($data) . " von " . $measuregrpsstart. " bis " . $measuregrpsende,0);	

			}

		$RequestReAggregation = false;
		$ReaggregationsArray = array();		// Array fuer Reaggregation

		$TypeArrayData = array();
		$datass = array(); $datas = array();

		$keys = array_keys($data);
                 
		foreach($keys as $key)
			{ 


			$lastdata = false;

			$activitydatestart 	= intval(@$key);
			$activityduration	= intval(@$data[$key]['duration']);
			$activitydateend 	= $activitydatestart + $activityduration;
			
			$activitysteps		= intval(@$data[$key]['steps']);
			$activitymodel  	= strval(@$data[$key]['model']);
			$activitymodelid	= intval(@$data[$key]['model_id']);
			$activitycalories	= floatval(@$data[$key]['calories']);
			$activitydistance	= floatval(@$data[$key]['distance']);
			$activityelevation	= intval(@$data[$key]['elevation']);
			$activitystroke		= intval(@$data[$key]['stroke']);
			$activitypoollap	= intval(@$data[$key]['pool_lap']);
			$activityheartrate	= intval(@$data[$key]['heart_rate']);
			$activityspo2		= intval(@$data[$key]['spo2_auto']);


			if ( $activitysteps > 0 )
				{
				$datass = ['timestamp'=>$activitydatestart,'duration'=>$activityduration,'value'=>$activitysteps, 		'ident'=>'schritte',	'profil'=>'WITHINGS_M_Schritte',	'name'=>'Schritte',	'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();	
				}	
			if ( $activityheartrate > 0 )
				{
				$datass = ['timestamp'=>$activitydatestart,'duration'=>$activityduration,'value'=>$activityheartrate, 	'ident'=>'puls',		'profil'=>'WITHINGS_M_Puls',		'name'=>'Puls',	'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();	
				}	
			if ( $activitycalories > 0 )
				{
				$datass = ['timestamp'=>$activitydatestart,'duration'=>$activityduration,'value'=>$activitycalories, 	'ident'=>'kalorien',	'profil'=>'WITHINGS_M_Kalorien',	'name'=>'Kalorien',	'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();	
				}	
			if ( $activitydistance > 0 )
				{
				$datass = ['timestamp'=>$activitydatestart,'duration'=>$activityduration,'value'=>$activitydistance, 	'ident'=>'distanze',	'profil'=>'WITHINGS_M_Meter',		'name'=>'Distanze',	'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();	
				}	
			if ( $activityelevation > 0 )
				{
				$datass = ['timestamp'=>$activitydatestart,'duration'=>$activityduration,'value'=>$activityelevation, 	'ident'=>'hoehenmeter',	'profil'=>'WITHINGS_M_Stockwerke',	'name'=>'Stockwerke',	'lastdata'=>$lastdata]; array_push($datas,$datass); $TypeArrayData = array_merge($TypeArrayData,$datas); $datass = array(); $datas = array();	
				}	

			// $this->SendDebug(__FUNCTION__.'['.__LINE__.']',$activitydatestart."-".$this->TimestampToDate($activitydatestart)." - ".$activityduration . " - ".$this->TimestampToDate($activitydateend)." - ".$activitymodel." - ".$activitymodelid." - ".$activitycalories."-".$activitydistance."-".$activityelevation."-".$activitysteps."-".$activitystroke."-".$activityheartrate,0);
			

			}

			
		$this->LoggingExt("=====",$file="IntraDayActivity.log",true );	
		$last_position = count($TypeArrayData) -1 ;
		$x = 0 ;	
		foreach($TypeArrayData as $key => $Activitydata)
			{

			$lastdata = $Activitydata['lastdata']; 
			$name = $Activitydata['name']; 
			$value = $Activitydata['value'];
			$profil = $Activitydata['profil'];
			$ident = $Activitydata['ident'];
			$timestamp = $Activitydata['timestamp'];
			$deviceid = 'IntraDayActivity';
			$InstanceIDDeviceID = @$this->GetIDForIdent($deviceid);
			$ID = $InstanceIDDeviceID;
		
			$DebugText = "[".$key."] : ".$InstanceIDDeviceID.":".$this->TimestampToDate($timestamp) ." - ".$deviceid." - ".$value." - ". $ident." - ".$profil." - ".$name." - ".$lastdata;


			$this->LoggingExt($DebugText,$file="IntraDayActivity.log" );

			}

		// Auswertung Pulsdaten
		foreach($TypeArrayData as $key => $Activitydata)
			{

			$lastdata = $Activitydata['lastdata']; 
			$name = $Activitydata['name']; 
			$value = $Activitydata['value'];
			$profil = $Activitydata['profil'];
			$ident = $Activitydata['ident'];
			$timestamp = $Activitydata['timestamp'];
			$deviceid = 'IntraDayActivity';
			$duration = $Activitydata['duration'];
			$InstanceIDDeviceID = @$this->GetIDForIdent($deviceid);
			$ID = $InstanceIDDeviceID;
		
			if ( $ident == 'puls' )
				{
					
				$DebugText = "[".$key."] : ".$InstanceIDDeviceID.":".$this->TimestampToDate($timestamp) ." - ".$deviceid." - ".$value." - ". $ident." - ".$profil." - ".$name." - ".$lastdata;
			
				//if ( $this->ReadPropertyBoolean("ExtDebug") == true )
				//$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Pulsdaten : ".$DebugText,0);

				$RequestReAggregation = false;
				$RequestReAggregation = $this->SetValueToVariable($ID,$name ,$value ,$profil ,10,DATA_TO_DATABASE,$timestamp,$ident);
			
				if ( $RequestReAggregation == true )
					{ 
					$VariableID = @IPS_GetObjectIDByIdent($ident,$ID );
					array_push($ReaggregationsArray,$VariableID);
					}
				}

			if ( $ident == 'kalorien' )
				{
					
				$DebugText = "[".$key."] : ".$InstanceIDDeviceID.":".$this->TimestampToDate($timestamp) ." - ".$deviceid." - ".$value." - ". $ident." - ".$profil." - ".$name." - ".$lastdata;
			
				if ( $this->ReadPropertyBoolean("ExtDebug") == true )
					$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Kaloriendaten : ".$DebugText,0);
				$this->LoggingExt($DebugText,$file="IntraDayActivityKalorien.log" );

				$RequestReAggregation = false;
				$RequestReAggregation = $this->SetValueToVariable($ID,$name ,$value ,$profil ,10,DATA_TO_DATABASE,$timestamp,$ident);
				
				if ( $RequestReAggregation == true )
					{ 
					$VariableID = @IPS_GetObjectIDByIdent($ident,$ID );
					array_push($ReaggregationsArray,$VariableID);
					}

				}

			if ( $ident == 'distanze' )
				{
					
				$DebugText = "[".$key."] : ".$InstanceIDDeviceID.":".$this->TimestampToDate($timestamp) ." - ".$deviceid." - ".$value." - ". $ident." - ".$profil." - ".$name." - ".$lastdata;
			
				if ( $this->ReadPropertyBoolean("ExtDebug") == true )
					$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Distanzdaten : ".$DebugText,0);
				$this->LoggingExt($DebugText,$file="IntraDayActivityDistanze.log" );

				$RequestReAggregation = false;
				$RequestReAggregation = $this->SetValueToVariable($ID,$name ,$value ,$profil ,10,DATA_TO_DATABASE,$timestamp,$ident);
				
				if ( $RequestReAggregation == true )
					{ 
					$VariableID = @IPS_GetObjectIDByIdent($ident,$ID );
					array_push($ReaggregationsArray,$VariableID);
					}

				}

			if ( $ident == 'schritte' )
				{
					
				$tt = "000000".$key;	
				$tt = substr($tt,-5);
				$DebugText = "[".$tt."] : ".$InstanceIDDeviceID.":".$this->TimestampToDate($timestamp) ." - ".$duration." - ".$deviceid." - ".$value." - ". $ident." - ".$profil." - ".$name." - ".$lastdata;
			
				if ( $this->ReadPropertyBoolean("ExtDebug") == true )
					$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Schrittdaten : ".$DebugText,0);
				$this->LoggingExt($DebugText,$file="IntraDayActivitySchritte.log" );

				$RequestReAggregation = false;
				$RequestReAggregation = $this->SetValueToVariable($ID,$name ,$value ,$profil ,10,DATA_TO_DATABASE,$timestamp,$ident);
				
				if ( $RequestReAggregation == true )
					{ 
					$VariableID = @IPS_GetObjectIDByIdent($ident,$ID );
					array_push($ReaggregationsArray,$VariableID);
					}

				}

			
			}






		$this->SetValueToVariable($ID,"Updatezeit" ,time() ,"~UnixTimestamp"  ,0,DATA_TO_VARIABLE,false,"timestamp");

		if ( $RequestReAggregation == true )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"RequestReAggregation erforderlich",0);
			}	
		else
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"RequestReAggregation nicht erforderlich",0);
			}			







		return ;
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
	// Auswertung Ziele
	//**************************************************************************
	protected function DoGoals($ModulID,$data)
		{
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Ziele werden ausgewertet.",0);

		$data = @$data['goals'];

		$steps = @$data['steps'];

		$weightvalue = @$data['weight']['value'];
		$weightunit   = @$data['weight']['unit'];

		$weight = floatval ( $weightvalue ) * floatval ( "1e".$weightunit );
		$weight = round($weight,2);
		
		$InstanzeName = "Ziele";
		$InstanzeIdent = "getgoals";
		$InstanzeInfo = "User Ziele";
		$ObjektID = @$this->GetIDForIdent ( $InstanzeIdent );
		if ( $ObjektID === FALSE )
			$ObjektID = $this->CreateDummyInstance($InstanzeName,$InstanzeIdent,$InstanzeInfo);
			
		$id = @IPS_GetObjectIDByIdent( "steps", $ObjektID ) ;                                                    
		if ( $id === FALSE )
			{
			$id = $this->RegisterVariableInteger("steps","Schritt pro Tag","WITHINGS_M_Schritte",0);
			IPS_SetParent($id, $ObjektID);
			IPS_SetPosition($id, 1);
			}
		SetValueInteger($id,$steps);

		$id = @IPS_GetObjectIDByIdent( "weight", $ObjektID ) ;                                                    
		if ( $id === FALSE )
			{
			$id = $this->RegisterVariableFloat("weight","Gewicht","WITHINGS_M_Kilo",0);
			IPS_SetParent($id, $ObjektID);
			IPS_SetPosition($id, 1);
			}
		SetValueFloat($id,$weight);

		}
		

	//**************************************************************************
	// Auswertung Geraeteinfos
	//**************************************************************************
	protected function DoDevice($ModulID,$data)
		{
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Devices werden ausgewertet.",0);

		$data = @$data['devices'];

		if ( $data == false  )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Keine Geraete gefunden. Abbruch",0);
			return;
			}
		
		foreach($data as $device)
			{
			$devicetyp			= @$device['type'];
			$devicemodel		= @$device['model'];
			$devicebattery		= @$device['battery'];
			$deviceid			= @$device['deviceid'];
			$devicetimezone		= @$device['timezone'];
			$last_session_date	= @$device['last_session_date'];
			
			if ( is_null($deviceid) )
				$deviceid = "null";
			
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"[Deviceid : ".$this->TimestampToDate($last_session_date)." - ".$deviceid."][Type : ".$devicetyp ."][Modell : ".$devicemodel."]"."[Batterie:".$devicebattery."]",0);

			$ObjektID = @$this->GetIDForIdent ( $deviceid );
			if ( $ObjektID === FALSE )
				$ObjektID = $this->CreateDummyInstance($devicemodel,$deviceid,$devicetyp);
				
			$name = 'Letzte Verbindung';
			$ident = 'last_session_date';

			$id = @IPS_GetObjectIDByIdent( $ident, $ObjektID ) ;                                                    
			if ( $id === FALSE )
				{
				$id = $this->RegisterVariableInteger($ident, $name,"~UnixTimestamp",0);
				IPS_SetParent($id, $ObjektID);
				IPS_SetPosition($id, 1);
				}
			SetValueInteger($id,$last_session_date);

			if ( $devicebattery == 'low' OR $devicebattery == 'medium' OR $devicebattery == 'high')
				{
				$name = "Batterie";
				$ident = "batterie";

				$id = @IPS_GetObjectIDByIdent( $ident, $ObjektID ) ;
                                                                
				if ( $id === FALSE )
					{
					$id = $this->RegisterVariableInteger($ident, $name,"WITHINGS_M_Batterie",0);
					IPS_SetParent($id, $ObjektID);
					IPS_SetPosition($id, 1);
					}

				if ( $id > 0 )
					{
							
					if ( $devicebattery == 'low' )
						{
						$v =  GetValueInteger($id);
						if ( $v != 0);
							SetValueInteger($id,0);
						}
					if ( $devicebattery == 'medium' )
						{
						$v =  GetValueInteger($id);
						if ( $v != 1);
							SetValueInteger($id,1);
						}
					if ( $devicebattery == 'high' )
						{
						$v =  GetValueInteger($id);
						if ( $v != 2);
							SetValueInteger($id,2);
						}
					}
				}
			}
		}

	//**************************************************************************
	// Auswertung Status auf Fehler / 0=OK
	//**************************************************************************
	protected  function CheckStatus($data)
		{

		if ( isset($data) == false )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Data nicht vorhanden ",0);
			return false;
			}
		
		if ( $data == false )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Data false ",0);			
			return false;
			}

		if ( array_key_exists('status',$data) == FALSE )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Status: unbekannt",0);
			return false;
			}		
		
		if ( $data['status'] != 0 )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Status Fehler : ".$data['status'],0);
			return false;	
			}
			
		$statusstatus = true;
		
		$bodystatus = $this->CheckBody($data);

		if ( $statusstatus == true AND $bodystatus == true)
			{
			// $this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Status OK - Body OK",0);	
			return true;
			}
		else
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Status/Body NOK",0);
			return false;
			}
		}

	//**************************************************************************
	// Auswertung Body auf Fehler / 0=OK
	//**************************************************************************
	protected  function CheckBody($data)
		{

		if ( isset($data) == false )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Data nicht vorhanden ",0);
			return false;
			}
		
		if ( $data == false )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Data false ",0);			
			return false;
			}

		if ( array_key_exists('body',$data) == FALSE )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Body: unbekannt",0);
			return false;
			}		
		
		return true;
		
		}

	//**************************************************************************
	// Auswertung der Meas-Daten
	//**************************************************************************
	protected  function DoMeas($ModulID,$data,$NoLastData=false)
		{
	
		// $this->Logging("MeasDaten werden ausgewertet.");
		$this->Debug("MeasDaten werden ausgewertet.",__FUNCTION__,__LINE__,true);


		/* 
		if ( $NoLastData == true )
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"No Last Data.",0);
		else
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Last Data.",0);
		*/ 
		
		$ReaggregationsArray = array();		// Array fuer Reaggregation

		$CatIdWaage = @IPS_GetCategoryIDByName("Waage",$ModulID);
		if ( $CatIdWaage === false )
			{
			//$this->Logging("CatID ist auf FALSE!");
			//$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"CatID Waage nicht vorhanden.",0);
			// $CatIdWaage = $this->CreateKategorie("Waage",$ModulID);
			$CatIdWaage = false;
			//if ( $CatIdWaage === false )
			//	throw new Exception("Kategorie Waage nicht definiert");
			}

		$CatIdBlutdruck = @IPS_GetCategoryIDByName("Blutdruck",$ModulID);
		if ( $CatIdBlutdruck === false )
			{
			//$this->Logging("CatID ist auf FALSE!");
			//$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"CatID Blutdruck nicht vorhanden.",0);
			//$CatIdBlutdruck = $this->CreateKategorie("Blutdruck",$ModulID);
			//if ( $CatIdBlutdruck === false )
			//	throw new Exception("Kategorie Blutdruck nicht definiert");
			$CatIdBlutdruck = false;	
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

		$RequestReAggregation = false;
		$TypeArrayData = array();
		$TypeArrayDataBMI = array();

		// Neueste nach hinten
		$measuregrps = array_reverse ( $measuregrps  );

		$text = "Start";
		// $this->LoggingExt($text,"Messungen.log",true);
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

				// zur BMI Berechnung
				$id = @IPS_GetVariableIDByName("Groesse",$ModulID);
				$groesse = 0; 
				if ( $id > 0 )
					$groesse = GetValueInteger($id);

			// $this->SendDebug("DoMeas","DeviceID .: ".$timestring." - " . $deviceid,0);
			// Alle Messungen durchgehen
			// Neuste Messung am Ende
			
			foreach($messungen as $key => $messung)
				{
				
				$val = floatval ( $messung['value'] ) * floatval ( "1e".$messung['unit'] );

				$text = date('d.m.Y H:i:s',$time) ." Messung Type : ".$messung['type']." : " .$val ;
				
				$file = "Messungen ".$messung['type'].".log";
				$this->LoggingExt($text,$file);

				$arraydatas[$messung['type']][$InstanceIDDeviceID] = round($val,2);
				$arraytimes[$messung['type']][$InstanceIDDeviceID] = $time;


				switch ($messung['type'])
					{ 
					case 1 :	$value = floatval(round ($val,2));
								$data = ['type' => $messung['type'],'timestamp'=> $time,'value'=> $value,'deviceid'=>$deviceid,'ident'=>'weight','oldcat'=> $CatIdWaage,'profil'=>'WITHINGS_M_Kilo','name'=>'Gewicht'];
								array_push($data,$data);
								$TypeArrayData = array_merge($TypeArrayData,$data);
								// BMI
								if ( $groesse > 0 )
									{
									$value =  @round($value/(($groesse/100)*($groesse/100)),2);
									}
								else
									$value = 0;
								$data = ['type' => 149,'timestamp'=> $time,'value'=> $value,'deviceid'=>$deviceid,'ident'=>'bmi','oldcat'=> $CatIdWaage,'profil'=>'WITHINGS_M_BMI','name'=>'BMI'];
								array_push($data,$data);
								$TypeArrayData = array_merge($TypeArrayData,$data);
								break;
					case 4 :	$groesse = round ($val,2);
								break;
					case 5 :	$value = floatval(round ($val,2));
								$data = ['type' => $messung['type'],'timestamp'=> $time,'value'=> $value,'deviceid'=>$deviceid,'ident'=>'fatfree','oldcat'=> $CatIdWaage,'profil'=>'WITHINGS_M_Kilo','name'=>'Fettfrei Anteil'];
								array_push($data,$data);
								$TypeArrayData = array_merge($TypeArrayData,$data);
								break;
					case 6 :	$value = floatval(round ($val,2));
								$data = ['type' => $messung['type'],'timestamp'=> $time,'value'=> $value,'deviceid'=>$deviceid,'ident'=>'fatradio','oldcat'=> $CatIdWaage,'profil'=>'WITHINGS_M_Prozent','name'=>'Fett Prozent'];
								array_push($data,$data);
								$TypeArrayData = array_merge($TypeArrayData,$data);
								break;
					case 8 :	$value = floatval(round ($val,2));
								$data = ['type' => $messung['type'],'timestamp'=> $time,'value'=> $value,'deviceid'=>$deviceid,'ident'=>'fatmassweight','oldcat'=> $CatIdWaage,'profil'=>'WITHINGS_M_Kilo','name'=>'Fett Anteil'];
								array_push($data,$data);
								$TypeArrayData = array_merge($TypeArrayData,$data);
								break;
					case 9 :	$value = intval($val);
								$data = ['type' => $messung['type'],'timestamp'=> $time,'value'=> $value,'deviceid'=>$deviceid,'ident'=>'diastolicblood','oldcat'=> $CatIdBlutdruck,'profil'=>'WITHINGS_M_Blutdruck','name'=>'Diastolic'];
								array_push($data,$data);
								$TypeArrayData = array_merge($TypeArrayData,$data);
								break;
					case 10 :	$value = intval($val);
								$data = ['type' => $messung['type'],'timestamp'=> $time,'value'=> $value,'deviceid'=>$deviceid,'ident'=>'systolicblood','oldcat'=> $CatIdBlutdruck,'profil'=>'WITHINGS_M_Blutdruck','name'=>'Systolic'];
								array_push($data,$data);
								$TypeArrayData = array_merge($TypeArrayData,$data);
								break;
					case 11:	$value = intval($val);
								$data = ['type' => $messung['type'],'timestamp'=> $time,'value'=> $value,'deviceid'=>$deviceid,'ident'=>'heartpulse','oldcat'=> $CatIdWaage,'profil'=>'WITHINGS_M_Puls','name'=>'Puls'];
								array_push($data,$data);
								$TypeArrayData = array_merge($TypeArrayData,$data);
								break;
					case 12 :	$value = floatval(round ($val,2));
								$data = ['type' => $messung['type'],'timestamp'=> $time,'value'=> $value,'deviceid'=>$deviceid,'ident'=>'temperatur','oldcat'=> 0,'profil'=>'WITHINGS_M_Temperatur','name'=>'Temperatur'];
								array_push($data,$data);
								$TypeArrayData = array_merge($TypeArrayData,$data);
								break;
					case 54 :	$value = intval(round ($val));
								$data = ['type' => $messung['type'],'timestamp'=> $time,'value'=> $value,'deviceid'=>$deviceid,'ident'=>'spo2','oldcat'=> 0,'profil'=>'WITHINGS_M_Prozent','name'=>'Sauerstoffsättigung'];
								array_push($data,$data);
								$TypeArrayData = array_merge($TypeArrayData,$data);
								break;
                    case 71 :	$value = floatval(round ($val,2)); 
								$data = ['type' => $messung['type'],'timestamp'=> $time,'value'=> $value,'deviceid'=>$deviceid,'ident'=>'koerpertemperatur','oldcat'=> 0,'profil'=>'WITHINGS_M_Temperatur','name'=>'Koerpertemperatur'];
								array_push($data,$data);
								$TypeArrayData = array_merge($TypeArrayData,$data);
								break;
					case 73 :	$value = floatval(round ($val,2));
								$data = ['type' => $messung['type'],'timestamp'=> $time,'value'=> $value,'deviceid'=>$deviceid,'ident'=>'hauttemperatur','oldcat'=> 0,'profil'=>'WITHINGS_M_Temperatur','name'=>'Hauttemperatur'];
								array_push($data,$data);
								$TypeArrayData = array_merge($TypeArrayData,$data);
								break;
					case 76 :	$value = floatval(round ($val,2));
								$data = ['type' => $messung['type'],'timestamp'=> $time,'value'=> $value,'deviceid'=>$deviceid,'ident'=>'muskelmasse','oldcat'=> $CatIdWaage,'profil'=>'WITHINGS_M_Kilo','name'=>'Muskelmasse'];
								array_push($data,$data);
								$TypeArrayData = array_merge($TypeArrayData,$data);
								break;
					case 77 :	$value = floatval(round ($val,2));
								$data = ['type' => $messung['type'],'timestamp'=> $time,'value'=> $value,'deviceid'=>$deviceid,'ident'=>'wasseranteil','oldcat'=> $CatIdWaage,'profil'=>'WITHINGS_M_Kilo','name'=>'Wasseranteil'];
								array_push($data,$data);
								$TypeArrayData = array_merge($TypeArrayData,$data);
								break;
					case 88 :	$value = floatval(round ($val,2));
								$data = ['type' => $messung['type'],'timestamp'=> $time,'value'=> $value,'deviceid'=>$deviceid,'ident'=>'bonemass','oldcat'=> $CatIdWaage,'profil'=>'WITHINGS_M_Kilo','name'=>'Knochenmasse'];
								array_push($data,$data);
								$TypeArrayData = array_merge($TypeArrayData,$data);
								break;
					case 91 :	$value = floatval(round ($val,2));
								$data = ['type' => $messung['type'],'timestamp'=> $time,'value'=> $value,'deviceid'=>$deviceid,'ident'=>'pulswave','oldcat'=> $CatIdWaage,'profil'=>'WITHINGS_M_Pulswelle','name'=>'Pulswellengeschwindigkeit'];
								array_push($data,$data);
								$TypeArrayData = array_merge($TypeArrayData,$data);
								break;
					default:	$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Messungstyp nicht vorhanden : ".$messung['type']."-".$val,0);
					}
				}
			}

			

		for($x=1;$x<150;$x++)
			{

			$array = array();
			$this->GetTypeDataArray($TypeArrayData,$x,$array);	
			$count = count($array);
			if ( $count == 0 )	// keine Daten fuer diesen Typ
				continue;
			
			$last_position = count($array) -1 ;

			foreach( $array as $key => $data )	
				{
				$type = $data['type'];
				$profil = $data['profil'];
				$value = $data['value'];
				$timestamp = $data['timestamp'];
				$deviceid = $data['deviceid'];	
				$ident = $data['ident'];
				$InstanceIDDeviceID = @$this->GetIDForIdent($deviceid);
				$oldcat = $data['oldcat'];
				$profil = $data['profil'];
				$name = $data['name'];
				
				$last = false;
				if ( $key == $last_position )
					{
					$last = true;
					}
				if ( $NoLastData == true )
					$last = false;

				 if ( $key == $last_position )
					if ( $this->ReadPropertyBoolean("ExtDebug") == true )
						$this->SendDebug(__FUNCTION__.'['.__LINE__.']',$key." : ".$InstanceIDDeviceID.":".$this->TimestampToDate($timestamp) ." - ".$type." - ".$deviceid." - ".$value." - ". $ident." - ".$oldcat." - ".$profil." - ".$name." - ".$last,0);

				$InstanceIDDeviceID = @$this->GetIDForIdent($deviceid);
				
				$ID = $this->CheckOldVersionCatID($ident,$oldcat,$InstanceIDDeviceID);	// $ID = ID der Instanz oder Kategorie
				
				if ( $last == true )
					{
					$this->SetValueToVariable($ID,$name ,$value ,$profil ,10,DATA_TO_VARIABLE,$profil,$ident);
					

					}
				else
					{
					$RequestReAggregation = false;
					$RequestReAggregation = $this->SetValueToVariable($ID,$name ,$value ,$profil ,10,DATA_TO_DATABASE,$timestamp,$ident);
					// $RequestReAggregation = true;
					if ( $RequestReAggregation == true )
						{
						$VariableID = @IPS_GetObjectIDByIdent($ident,$ID );
						array_push($ReaggregationsArray,$VariableID);
						}
					}

				if ( $key == $last_position )
					{
					$ID = $this->CheckOldVersionCatID("timestamp",$oldcat,$InstanceIDDeviceID);
					$this->SetValueToVariable($ID,"Updatezeit" ,time() ,"~UnixTimestamp"  ,0,DATA_TO_VARIABLE,false,"timestamp");
					}
				
				}

			}

		if ( count($ReaggregationsArray) > 0 )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"RequestReAggregation erforderlich:",0);
			$this->Reaggregieren($ReaggregationsArray);
			}	
		else
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"RequestReAggregation nicht erforderlich",0);
			}			

		}

	private function GetTypeDataArray($TypeArrayData,$type_request,&$array)
		{

		foreach($TypeArrayData as $data)
			{
			$type =@$data['type'];
			if ( is_int($type) == false )
				continue;
			$timestamp = @$data['timestamp'];
			if ( is_int($timestamp) == false )
				continue;
			$value = @$data['value'];
			$deviceid = @$data['deviceid'];
			$ident = @$data['ident'];
			$oldcat = @$data['oldcat'];
			$profil = @$data['profil'];
			$name = @$data['name'];
			if ( $type != $type_request )
				continue;
			
			$data = ['type' => $type,'timestamp'=> $timestamp,'value'=> $value,'deviceid'=>$deviceid,'ident'=>$ident,'oldcat'=>$oldcat,'profil'=>$profil,'name'=>$name];
			array_push($array,$data);	
			
			// $this->SendDebug(__FUNCTION__.'['.__LINE__.']'," : ".$type ."-".$this->TimestampToDate($timestamp)." - " . $value . " - " . $deviceid,0);
			}

		}

	//******************************************************************************
	// checken ob noch alte Version verwendet wird	
	// Ist die Variable mit $ident noch in $CatId Kategorie vorhanden
	//******************************************************************************
	private function CheckOldVersionCatID($ident,$CatId,$DeviceID) 
		{
    	
        $VariableID = @IPS_GetObjectIDByIdent($ident,$CatId);

        if ( $VariableID == true )
        	{
			$ID = $CatId;
            $this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Variable noch in alter Kategorie : ".$ident."-".$CatId." - " . $ID,0);   
            }
        else
            {
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

		// $result = file_get_contents("https://oauth.ipmagic.de/access_token/withings", false, $context);

		$ch = curl_init();
curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,0);
curl_setopt($ch,CURLOPT_TIMEOUT,10);

		curl_setopt($ch, CURLOPT_URL, "https://wbsapi.withings.net/v2/oauth2");
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([ 
			'action' 		=> 'requesttoken',
			'grant_type' 	=> 'authorization_code',
			'client_id' 	=> $this->GetClientID(),
			'client_secret' => $this->GetClientSecret(),
			'code' 			=> $code,
			'redirect_uri' 	=> 'https://oauth.ipmagic.de/forward/withings'
		]));
		
		$result = curl_exec($ch);
		curl_close($ch);

		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Tokens : ".$result,0);
		
		$data = json_decode($result);
		$arraydata = json_decode($result,true);

		if ( isset($arraydata['status']) == true )
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Status OK",0 );
		else
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Status NOK",0 );
			return false;	
			}
		if ( isset($arraydata['body']) == true )
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Body OK",0 );
		else
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Body NOK",0 );
			return false;	
			}

		$access_token 	= $arraydata['body']['access_token'];
		$expires_in 	= $arraydata['body']['expires_in'];
		$token_type 	= $arraydata['body']['token_type'];
		$scope 			= $arraydata['body']['scope'];
		$refresh_token 	= $arraydata['body']['refresh_token'];
		$userid 		= $arraydata['body']['userid'];
				
		IPS_SetProperty($this->InstanceID, "Naccess_token", $access_token); 
		IPS_SetProperty($this->InstanceID, "Nexpires_in", $expires_in); 
		IPS_SetProperty($this->InstanceID, "Ntoken_type", $token_type); 
		IPS_SetProperty($this->InstanceID, "Nscope", $scope); 
		IPS_SetProperty($this->InstanceID, "Nuserid", $userid); 
		IPS_SetProperty($this->InstanceID, "Nrefresh_token", $refresh_token); 
		IPS_ApplyChanges($this->InstanceID);
			

		/* 	
		if(!isset($data->token_type) || $data->token_type != "Bearer") 
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Bearer Token expected",0 );
			return false;
			}
		*/	

		// $token = $data->refresh_token;
		// $token = $arraydata['refresh_token'];

		$this->SendDebug(__FUNCTION__.'['.__LINE__.']', "OK! Speichere Refresh Token . ".$refresh_token, 0);
		IPS_SetProperty($this->InstanceID, "User", $refresh_token);
		IPS_SetProperty($this->InstanceID, "RefreshToken", $refresh_token);
		IPS_ApplyChanges($this->InstanceID);

		// $this->FetchAccessToken($data->access_token, time() + $data->expires_in);
		$this->FetchAccessToken($access_token, time() + $expires_in);
		

		

		}

	//******************************************************************************
	//	
	//******************************************************************************
	private function RefreshAccessToken() 
		{

		
		$refresh_token = $this->ReadPropertyString("Nrefresh_token");

		$ch = curl_init();
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,0);
		curl_setopt($ch,CURLOPT_TIMEOUT,10);

		curl_setopt($ch, CURLOPT_URL, "https://wbsapi.withings.net/v2/oauth2");
			
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([ 
				'action' => 'requesttoken',
				'grant_type' => 'refresh_token',
				'client_id' 	=> $this->GetClientID(),
				'client_secret' => $this->GetClientSecret(),
				'refresh_token' => $refresh_token
			]));
				
		$result = curl_exec($ch);
		curl_close($ch);

		$this->SendDebug(__FUNCTION__.'['.__LINE__.']', "Result : " . $result, 0);

		$data = json_decode($result,true);

		if ( $this->CheckStatus($data) == false )
			return false;

		if ( $this->CheckBody($data) == false )
			return false;

		$access_token 	= @$data['body']['access_token'];
		$expires_in 	= @$data['body']['expires_in'];
		$token_type 	= @$data['body']['token_type'];
		$scope 			= @$data['body']['scope'];
		$refresh_token 	= @$data['body']['refresh_token'];
		$userid 		= @$data['body']['userid'];

		$this->SaveAccessToken($access_token);
		$this->SaveRefreshToken($refresh_token);

		if( $userid == true )
			$this->SetUserID($userid);
				
		if (IPS_GetProperty($this->InstanceID, "Naccess_token")  != $access_token )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Access_Token neu : ".IPS_GetProperty($this->InstanceID, "Naccess_token")." -> ".$access_token,0 );
			IPS_SetProperty($this->InstanceID, "Naccess_token", $access_token); 
			}	
		if (IPS_GetProperty($this->InstanceID, "Nrefresh_token")  != $refresh_token )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Refresh_Token neu : ".IPS_GetProperty($this->InstanceID, "Nrefresh_token")." -> ".$refresh_token,0 );
			IPS_SetProperty($this->InstanceID, "Nrefresh_token", $refresh_token); 
			}	
		if (IPS_GetProperty($this->InstanceID, "Nexpires_in")  != $expires_in )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Expires in neu : ".IPS_GetProperty($this->InstanceID, "Nexpires_in")." -> ".$expires_in,0 );
			IPS_SetProperty($this->InstanceID, "Nexpires_in", $expires_in); 
			}	
		if (IPS_GetProperty($this->InstanceID, "Ntoken_type")  != $token_type )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Token Type neu : ".IPS_GetProperty($this->InstanceID, "Ntoken_type")." -> ".$token_type,0 );
			IPS_SetProperty($this->InstanceID, "Ntoken_type", $token_type); 
			}	
		if (IPS_GetProperty($this->InstanceID, "Nscope")  != $scope )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Scope neu : ".IPS_GetProperty($this->InstanceID, "Nscope")." -> ".$scope,0 );
			IPS_SetProperty($this->InstanceID, "Nscope", $scope); 
			}	
		if (IPS_GetProperty($this->InstanceID, "Nuserid")  != $userid )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"UserID neu : ".IPS_GetProperty($this->InstanceID, "Nuserid")." -> ".$userid,0 );
			IPS_SetProperty($this->InstanceID, "Nuserid", $userid); 
			}	

		$Expires = time() + $expires_in;

		$this->Debug("Neuer Access Token ist gueltig bis ".date("d.m.y H:i:s", $Expires),__FUNCTION__,__LINE__,true);

		IPS_ApplyChanges($this->InstanceID);

		return true;	

		}	

	//******************************************************************************
	//	
	//******************************************************************************
	private function GetClientID()
		{
		return '2166af8c652a1b251db8b954cfdc2e24c82f1493d15d03b4dddc422f342c7ffd';
		}

	//******************************************************************************
	//	
	//******************************************************************************
	private function GetClientSecret()
		{		
		return 'b22edf210b6a67b583c9ef1bd39481383e7c2d1b7ac198ddeeb0ea68a7ecd2f6';
		}


	private function SetUserID($userid)
		{

		$VarID = @$this->GetIDForIdent("userid");
		if ( $VarID === false )
			$VarID = $this->RegisterVariableInteger("userid", "User ID"   ,"" ,0);
		if ( $VarID == true )
			SetValue($VarID,$userid);
		
		}	


	//******************************************************************************
	//	
	//******************************************************************************
	private function FetchAccessToken($Token = "", $Expires = 0) 
		{
			
		$refresh_token = $this->ReadPropertyString("RefreshToken");	
		// $this->SendDebug(__FUNCTION__.'['.__LINE__.']', "Benutze Refresh Token um neuen Access Token zu holen : " . $Token, 0);
		// $this->SendDebug(__FUNCTION__.'['.__LINE__.']', "Benutze Refresh Token um neuen Access Token zu holen : " . $refresh_token, 0);
		
		
		// $this->SendDebug(__FUNCTION__.'['.__LINE__.']', "Access Token : " . $this->ReadPropertyString("Naccess_token"), 0);
		// $this->SendDebug(__FUNCTION__.'['.__LINE__.']', "Refresh Token : " . $this->ReadPropertyString("Nrefresh_token"), 0);
		
		$this->Debug("Access Token : " . $this->ReadPropertyString("Naccess_token"),__FUNCTION__,__LINE__,true);

		$status = $this->RefreshAccessToken();
		if ( $status == false )
			return false;
		else
			return true;	

		// Ab hier alte Version

		$options = array(
					"http" => array(
						"header" => "Content-Type: application/x-www-form-urlencoded\r\n",
						"method"  => "POST",
						"content" => http_build_query(Array("refresh_token" => $refresh_token))
									)
							);
							
		$context = stream_context_create($options);

		$result = @file_get_contents("https://oauth.ipmagic.de/access_token/withings", false, $context);
		$data = json_decode($result);

		$arraydata = json_decode($result,true);
		// print_r($arraydata);

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
			$refresh_token = $data->refresh_token;		

			// $this->SendDebug(__FUNCTION__.'['.__LINE__.']', "Neuer Refresh Token erhalten", 0);
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']', "OK! Speichere Refresh Token . ".$token, 0);
			IPS_SetProperty($this->InstanceID , "User", $token );
			IPS_SetProperty($this->InstanceID, "RefreshToken", $refresh_token);
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
	protected function FormatTimeMinuten($time) 
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

	protected function GetYesterdayString()
		{
	
		$DayYesterday = date("Y-m-d",strtotime("-1 day")); 	
	
		return $DayYesterday;
		}    


	//******************************************************************************
	//	Check Timestamp auf gestern
	// 	LastData von Gestern ?
	//******************************************************************************
	protected function CheckDataYesterdayExist($Unixtimestamp)
		{
		$status = false;
		$DayData = date("d",$Unixtimestamp); 
		$DayYesterday = date("d",strtotime("-1 day")); 	
		
		if( $DayData == $DayYesterday)
			{	
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']','Daten sind von Gestern : '.$DayData . ' - '.$DayYesterday,0);
			$status = true;
			}
		else
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']','Daten sind nicht von Gestern : '.$DayData . ' - '.$DayYesterday,0);
			
		return $status;	
		}
	

	//******************************************************************************
	//	wandelt Datum/Uhrzeit in Timestamp  
	//******************************************************************************
	protected function DateToTimestamp($time)
		{
		return strtotime($time);			
		}


	protected function CheckProfil($CatID,$name,$profilsoll)
		{

		$VarID = @IPS_GetVariableIDByName ($name, $CatID);
		
		if ( $VarID == false )
			return;

		$array = IPS_GetVariable ($VarID);
		
		if ( isset($array['VariableProfile']) == false )
		 	return;
		if ( isset($array['VariableCustomProfile']) == false )
		 	return;

		$profilist = $array['VariableProfile'];
		$customprofilist = $array['VariableCustomProfile'];

		if ( $profilsoll !=  $customprofilist)
			{
				  
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Profile :". $VarID . " - " .$name."-" . $profilsoll . " - " . $customprofilist . " - ".$profilist,0);	

			$VarTypIst = $array['VariableType'];
			
			$status = @IPS_SetVariableCustomProfile($VarID,$profilsoll);
		
			if ( $status == false )	
				$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Profile Typ Aenderung NOK:",0);	
			
			}

		// Profil leer
		if ( $profilist == '' )
			{
			//$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Profil leer CatID :".$CatID. " - ". $VarID . " - " .$name."-" . $profilsoll . " - " . $customprofilist,0);	
			if ( $customprofilist != $profilsoll  )
				{
				//$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"CatID :".$CatID. " - ". $VarID . " - " .$name."-" . $profilsoll . " - " . $customprofilist,0);	
				}
			}
		else	// CustomProfil ist gesetzt
			{
			// $this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Profil nicht leer CatID :".$CatID. " - ". $VarID . " - " .$name."-" . $profilsoll . " - " . $customprofilist,0);	
				if ( $customprofilist != $profilsoll  )
					{
					$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Profil falsch CatID :".$CatID. " - ". $VarID . " - " .$name."- Soll:" . $profilsoll . " - Ist:" . $customprofilist,0);	
					@IPS_SetVariableCustomProfile ($VarID, $profilsoll);
					}

			}	

		}


	//******************************************************************************
	//	Wert in Variable schreiben
	// Position 1 - KategorieID / InstanzID
	// Position 2 - Name der Variablen ( zum erstellen )
	// Position 3 - Wert
	// Position 4 - Profil der Variablen ( zum erstellen )
	// Position 5 - Position der Variablen ( zum erstellen )
	// Position 6 -	Werte in Variable schreiben oder gleich in Archiv
	// Position 7 - Zeitstempel der Variablen ( testen ob schon vorhanden )
	// Position 8 - Ident der Variablen ( zum erstellen )
	// Position 9 - Logging
	// Position 10 - Check auch Variablenwert
	//******************************************************************************
	protected function SetValueToVariable($CatID,$name,$value,$profil=false,$position=0 ,$asynchron=false,$Timestamp=0,$VarIdent=false,$NoLogging=false,$CheckValue=false)
		{


		$profilarray = IPS_GetVariableProfile ($profil) ;
		$profiltype = $profilarray['ProfileType'];	

			// $this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Variable Typ : ".$CatID."-".$name."-".$VarIdent."-".$profiltype."-".$profil,0);	
			
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
					{
					// IPS_Logmessage("Withings Modul","Variablentyp falsch : " . $profiltype . " - 2 ".$profil);
					$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Variable falsch : " . $profiltype." - 2 " .$profil,0);

					}
			}

			if ( is_bool($value) == true )
				{
				if ( $profiltype == 0 )
					$VariableID = $this->RegisterVariableBoolean( $VarIdent, $name,$profil,$position);
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
 
		$this->CheckProfil($CatID,$name,$profil);
		
		if ( is_nan(floatval($value)) == true)
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Variable ist NaN : ".($Timestamp). " : ".$VariableID."-".$name."-".$value,0);
			return;
			}

		if ( is_infinite(floatval($value) )== true)
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Variable ist is_infinite : ".($Timestamp). " : ".$VariableID."-".$name."-".$value,0);
			return;
			}

		$IstName = IPS_GetName($VariableID);
		if ( $IstName != $name )
			IPS_SetName($VariableID,$name);

		// $asynchron bedeutet gleich in Database
		if ( $asynchron == true )
        	{
			$Reaggieren = $this->SaveDataToDatabase($VariableID,$Timestamp,$value,$name,$CheckValue);
            }
		else
            {
			if ( $VariableID > 0 )
            	{
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
		
		// $this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Start : ".$this->TimestampToDate($Timestamp) . " ".$name,0);
		
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
				  
			// $this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Starttime: " . $Timestamp,0);
			$datas = AC_GetLoggedValues($archiveID, $Variable, $Timestamp,$Timestamp, 1);
			$anzahl = count($datas);
				
			if ( $anzahl == 0 )	// Datensatz noch nicht vorhanden
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
                // $this->LoggingExt($Text,"WithingsDataToDatabase.log");
				}
			else
				{
				$IstValue = @$datas[0]['Value'];
				$IstTime  = @$datas[0]['TimeStamp'];	
				// $this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Datensatz schon vorhanden : ".$Variable ."[".$IstValue."][".$Value."][".$IstTime."[".$Timestamp."]",0);
            			
				}		

			}
		
		if ( $Reaggregieren == true )
			if ( $this->ReadPropertyBoolean("ExtDebug") == true )
				$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Reaggregieren('AC_AddLoggedValues'). Variable : ".$Variable,0);	

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
	protected function GetNotifyList($appli="",$aktiv=true)
		{
		
		if ( $this->ReadPropertyBoolean("Notifyaktiv") == false )
        	{
            // return false;
            }

		$access_token = IPS_GetProperty($this->InstanceID, "Naccess_token");
		$header = 'Authorization: Bearer ' . $access_token;
	
		$this->Debug("Access Token : ".$access_token,__FUNCTION__,__LINE__,FALSE);
		
		$ch = curl_init();
curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,0);
curl_setopt($ch,CURLOPT_TIMEOUT,10);

		curl_setopt($ch, CURLOPT_URL, "https://wbsapi.withings.net/notify");
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		
		curl_setopt($ch, CURLOPT_HTTPHEADER, [ $header 
			]);
		
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([ 
			'action' => 'list',
			'appli' => $appli
		]));
		
		$result = curl_exec($ch);
		curl_close($ch);

		$this->SendDebug(__FUNCTION__.'['.__LINE__.']', $result , 0);

		$data = json_decode($result,TRUE); 

		if ( $this->CheckStatus($data) == false )
			return false;

		$ModulID = $this->InstanceID;

		$body = $data['body'];

		$NotifiyListArray = $this->DoNotifyList($body);

		foreach($NotifiyListArray as $key => $Notify)
			{
			
			$appli = @$key;
			$url = @$Notify;
			// if ( $this->ReadPropertyBoolean("ExtDebug") == true )
			//	$this->SendDebug(__FUNCTION__.'['.__LINE__.']', $key ." - ".$url, 0);

			}
		
		return $NotifiyListArray;

		}


	//******************************************************************************
	//	Abfrage welche Benachrichtigungen aktiv
	//******************************************************************************
	protected function DoNotifyList($data)
		{
		
		$NotifiyListArray = array();
			
		$data = @$data['profiles'];

		if ( @count($data) == 0 )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Keine Profile gefunden. Abbruch",0);
			return $NotifiyListArray;
			}
		else
			{
			// $this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Anzahl der Profile : ".count($data),0);
			}

		foreach($data as $profil )
			{
			$applikation = @$profil['appli'];	
			$callbackurl = @$profil['callbackurl'];	
			$expires = @$profil['expires'];	
			$comment     = @$profil['comment'];	
				
			$applikation = intval($applikation);

			// $this->SendDebug(__FUNCTION__.'['.__LINE__.']',"[".$applikation."][".$callbackurl."][".$expires."][".$comment."]",0);
			$NotifiyListArray[$applikation] = $callbackurl;
			
			}
	
		return 	$NotifiyListArray;

		}
		
	//**************************************************************************
	//	Alle Callbacks von GetNotifyList loeschen
	//**************************************************************************
	protected function DoNotifyRevokeAll()
		{
		
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"",0);
	
		$data = $this->GetNotifyList();

		if ( $data == false )
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"GetNotifyList ist leer",0);
			return;
			}

		foreach($data as $key => $profil )
			{
			$applikation = $key;	
			$callbackurl = $profil;	
			
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Loesche Benachrichtigung [".$key."][".$profil."]",0);
			
			$this->GetRevokeNotifyList($applikation,$callbackurl);
			}
			
		}
	

	//**************************************************************************
	// Revoke Notify List
	//**************************************************************************
	protected function GetRevokeNotifyList($applikation,$callbackurl)
		{
			$access_token = IPS_GetProperty($this->InstanceID, "Naccess_token");
			$header = 'Authorization: Bearer ' . $access_token;
	
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']', "Access Token : ".$access_token , 0);
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']', "Header : ".$header , 0);
	
			$ch = curl_init();
curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,0);
curl_setopt($ch,CURLOPT_TIMEOUT,10);
	
			curl_setopt($ch, CURLOPT_URL, "https://wbsapi.withings.net/notify    ");
				
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				
			curl_setopt($ch, CURLOPT_HTTPHEADER, [ $header 
					
				]);
				
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([ 
				'action' => 'revoke',
				'callbackurl' => $callbackurl,
				'appli' => $applikation
				]));
				
			$result = curl_exec($ch);
			curl_close($ch);
	
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']', $result , 0);
			
			$data = json_decode($result,TRUE); 
	
			if ( $this->CheckStatus($data) == false )
				return false;
	
			if ( $this->CheckBody($data) == false )
				return false;
	
			$ModulID = $this->InstanceID;
			
			$body = $data['body'];
		
		
		}
		
	//**************************************************************************
	// Notify Subscribe
	//**************************************************************************
	protected function DoNotifySubscribe()
		{
		$access_token = IPS_GetProperty($this->InstanceID, "Naccess_token");
		$header = 'Authorization: Bearer ' . $access_token;
	
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']', "Access Token : ".$access_token , 0);
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']', "Header : ".$header , 0);
		
		$ipsymconconnectid = IPS_GetInstanceListByModuleID("{9486D575-BE8C-4ED8-B5B5-20930E26DE6F}")[0]; 
		$connectinfo = CC_GetUrl($ipsymconconnectid);
		
		
		$manURL = $this->ReadPropertyString("CallbackURL");
		if ( $manURL != "" )
			{
			$callbackurl = $manURL ."/hook/Withings".$this->InstanceID."/";
			}
		else
			{
			$callbackurl = $connectinfo."/hook/Withings".$this->InstanceID."/";
			}
				
		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"CallbackURL:".$callbackurl,0);
		

		// Wenn CallbackURL schon gesetzt dann ueberspringen
		$SubscribeArray = array( 
			1 => "Subscribe 1" ,
			2 => "Subscribe 2" ,
			4 => "Subscribe 4" ,
			16 => "Subscribe 16" ,
			44 => "Subscribe 44" ,
			46 => "Subscribe 46" ,
			50 => "Subscribe 50" ,
			51 => "Subscribe 51" ,
			52 => "Subscribe 52" ,
			54 => "Subscribe 54" ,
			55 => "Subscribe 55" ,
			);

		foreach($SubscribeArray as $appli => $comment )
    		{
			if ( isset($NotifiyListArray[$appli]) == true AND $NotifiyListArray[$appli] == $callbackurl)
				{ 
				$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"CallbackURL:".$callbackurl." schon gesetzt appli=".$appli,0);
				}
			else
				{

				if ( $this->ReadPropertyBoolean("ExtDebug") == true )
					$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"CallbackURL:".$callbackurl." setze appli=".$appli,0);	

				$ch = curl_init();
curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,0);
curl_setopt($ch,CURLOPT_TIMEOUT,10);

				curl_setopt($ch, CURLOPT_URL, "https://wbsapi.withings.net/notify");

				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

				curl_setopt($ch, CURLOPT_HTTPHEADER, [ $header
							]);

				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([ 
						'action' => 'subscribe',
						'callbackurl' => $callbackurl,
						'appli' => $appli,
						'signature' => 'signature',
						'nonce' => 'string',
						'client_id' => $this->GetClientID(),
						'comment' => $comment
						]));

				$result = curl_exec($ch);
				curl_close($ch);

				if ( $this->ReadPropertyBoolean("ExtDebug") == true )
					{
					$this->SendDebug(__FUNCTION__.'['.__LINE__.']', $result , 0);
			
					$data = json_decode($result,TRUE); 
		
					$this->CheckStatus($data);
			
					$this->CheckBody($data);
					}
				
				}
	
			}	

		}
		
	//******************************************************************************
	//	Globale Debug Funktion
	// wenn $status true dann trotzdem debuggen
	//******************************************************************************
	protected function Debug($text,$function,$line,$status=false)
		{

		if ( $status == false )	
			if ( $this->ReadPropertyBoolean("ExtDebug") == false )
				return false;

		$this->SendDebug($function.'['.$line.']', $text , 0);
		
		}
		
	//******************************************************************************
	//	Curl Abfrage ausfuehren
	//******************************************************************************
	protected function DoCurl($url,$debug=false)
		{
		if($debug == true)
			$this->SendDebug(__FUNCTION__,$url,0);

		$curl = curl_init($url);

		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

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

		$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"",0);
		
		// IPS_sleep(1800);
		
		header("HTTP/1.1 200 OK");
			
		print_r($_POST);
		http_response_code(200);
		echo $this->InstanceID;	
		

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
	public function RunAlarmScript(int $id,string $string)
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
	//	Reaggregieren der uebergebenen Variablen
	//**************************************************************************
	 protected function Reaggregieren($varArray)
		{
		
		$varArray = array_unique ( $varArray );			// doppelte Eintraege loeschen

		$version = (float)IPS_GetKernelVersion();
		
		if ( $version < 5.5 )	// Kein gleichzeitiges Reaggregieren moeglich
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Kein gleichzeitiges Reaggregieren moeglich Version : " . $version,0);
			// zufaellige Variable in allen Instanzen auswaehlen
			$InstanzeArray = array();
			$ChildsArray = array();
			foreach($varArray as $variable)
				{
				$Instanze = IPS_GetParent($variable);
				array_push($InstanzeArray,$Instanze);
				}
			$InstanzeArray = array_unique($InstanzeArray);
				
			foreach($InstanzeArray as $Instanze)
				{
				$childs = IPS_GetChildrenIDs($Instanze);
				
				foreach($childs as $child)
					{
					// wenn geloggte Variable, dann zum Array hinzufuegen
					$status = AC_GetLoggingStatus($this->GetArchivID(),$child);
					if ( $status == true )
						array_push($ChildsArray,$child);
					}
				}

			$count = count($ChildsArray);
			if ( $count == false )
				{
				return;
				}
			$random = rand(0,$count-1);    
			$child = $ChildsArray[$random];
			
			$status = @AC_ReAggregateVariable ($this->GetArchivID(), $child );
			
			if ( $status == true )
				{
				$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Erfolgreich gestartet-> [".$random."]" .$child ,0);
				}
			else 
				{	
				$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Start Fehlgeschlagen -> [".$random."]" .$child ,0);
				}    
			}
		else
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Gleichzeitiges Reaggregieren moeglich Version : " . $version,0);
			// Alle Variablen im Array reaggregieren 
		
			foreach($varArray as $variable)
				{
				
				$status = AC_GetLoggingStatus($this->GetArchivID(),$variable);
				if ( $status == false )
					{
					$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Variable wird nicht geloggt : " . $child,0);
					return false;
					}
				
				$status = @AC_ReAggregateVariable ($this->GetArchivID(), $variable );
			
				if ( $status == true )
					{
					$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Erfolgreich gestartet " .$variable ,0);
					}
				else 
					{	
					$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Start Fehlgeschlagen " . $variable ,0);
					}  	

				}


			}	


       	
		}

	

	//**************************************************************************
	//	Reaggregieren der uebergebenen Instanz wenn IsValid
	//**************************************************************************
	protected function CheckAggregationNecessary($ParentID)
    	{

		if(IPS_ObjectExists ($ParentID) == false)
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"ParentID nicht vorhanden :" .$ParentID ,0);
			return false;	
			}
		
		$version = (float)IPS_GetKernelVersion();

		if ( $version < 5.5 )	// Kein gleichzeitiges Reaggregieren moeglich
			{
			$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Version < 5.5 "  ,0);
			return false;	
			}
		
		// Erstelle ein Array mit IsValid = false
		$isUnvalid = array();
		$arr = AC_GetAggregationVariables ($this->GetArchivID(), false);
		foreach($arr as $array )
			{
			if ( isset($array['IsValid']) )
				{
				if ( $array['IsValid'] == false )
					{
					$VariableID =  $array['VariableID'] ;  
					$isUnvalid[$VariableID] = true;
					}
				}
			else
				{	
				$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"IsValid nicht vorhanden :" .$array['VariableID'] ,0);
				}	
			}

		// $isValid Array enthaelt IDs welche invalid

		$childs = IPS_GetChildrenIDs($ParentID);
        foreach($childs as $child)
            {
            // Ist Variable invalid und muss aggregriert werden
            if ( array_key_exists($child,$isUnvalid ) )
                {
				// Variable muss aggregiert werden
				$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Variable muss aggregiert werden :" .$child ,0);
				
				$status = @AC_ReAggregateVariable ($this->GetArchivID(), $child );

				if ( $status )
            		{
                	//$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Erfolgreich -> " .$child ,0);
                	}
            	else 
                	{
                	$this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Fehlgeschlagen -> " .$child ,0);
                	}    
				

				}

            }

		}		


	//**************************************************************************
	//	Clean Database
	//**************************************************************************
	protected function CleanDatabase()
		{
		$instanceID = $this->InstanceID;

		$this->Debug("Instanze :".$instanceID ,__FUNCTION__,__LINE__,true);
		
		$childs = IPS_GetChildrenIDs($instanceID);

		foreach($childs as $child)
			{

			$object = IPS_GetObject($child);
			$typ = $object['ObjectType'];

			if ( $typ == 0 OR $typ == 1 )		// Kategorie / Instanze
				{
				// $this->SendDebug(__FUNCTION__.'['.__LINE__.']',"Child :".$child ." Typ : ".$typ,0);
				$this->CheckAggregationNecessary($child);
				}

			}

		}

	}
