<?
//******************************************************************************
//	Name		:	Withings Modul.php
//	Aufruf		:	
//	Info		:	
//	Funktionen	:	
//
//******************************************************************************


	//******************************************************************************
	//	
	//******************************************************************************
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
		$this->RegisterPropertyBoolean("BodyMeasures", false);
		$this->RegisterPropertyBoolean("BodyPuls", false);  
		$this->RegisterPropertyBoolean("BloodMeasures", false);  
		$this->RegisterPropertyString("Username", "XXX");         // in V3.0 ist das die UserID
		$this->RegisterPropertyString("Userpassword", "123456");  // in V3.0 ist das das Access Token
		$this->RegisterPropertyString("User", "XXX");             // in V3.0 ist das das Refresh Token
		$this->RegisterPropertyBoolean("Logging", false);  
		$this->RegisterPropertyBoolean("Modulaktiv", true);  
		$this->RegisterTimer("WIT_UpdateTimer", 0, 'WIT_Update($_IPS[\'TARGET\']);');
		$this->RegisterPropertyBoolean("BloodLogging", false);    // in V3.0 ist das  Sleep aktiv
		$this->RegisterPropertyBoolean("BloodVisible", false);  
		$this->RegisterPropertyBoolean("BodyLogging" , false);  // in V3.0 ist das  Activity aktiv
		$this->RegisterPropertyBoolean("BodyVisible" , false);  
		}

	//******************************************************************************
	// Register alle Profile
	//******************************************************************************
	protected function RegisterAllProfile()
		{
		$this->RegisterProfile(1,"WITHINGS_M_Groesse"  ,"Gauge"  ,""," cm");
		$this->RegisterProfile(1,"WITHINGS_M_Puls"     ,"Graph"  ,""," bpm");
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
		$this->RegisterProfile(1,"WITHINGS_M_Kalorien","",""," kcal");
		$this->RegisterProfile(1,"WITHINGS_M_Meter","",""," Meter");



		}


	//**************************************************************************
	//
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

		//Timer erstellen
		$this->SetTimerInterval("WIT_UpdateTimer", $this->ReadPropertyInteger("Intervall"));

		//Update
		//$this->Update();

		}

	//**************************************************************************
	// Authentifizierung ueber OAuth2
	//**************************************************************************    
	public function Authentifizierung()
		{
		$this->SendDebug("Authentifizierung:","Starte Webseite zum einloggen bei Withings",0);
		$url = "https://oauth.ipmagic.de/authorize/withings?username=".urlencode(IPS_GetLicensee());
		$this->RegisterOAuth('withings');
		$this->SendDebug("Authentifizierung:",$url,0);
		return $url;
		}
		
	//**************************************************************************
	// manuelles Holen der Daten oder ueber Timer
	//**************************************************************************
	public function Update()
		{
		if ( $this->ReadPropertyBoolean("Modulaktiv") == false )
			return;

		$this->Logging("Update");
		$this->SendDebug("Update Data","Update Data",0);
		
		if ( $this->RefreshTokens() == FALSE )
			return;
			
		$this->GetDevice();   

		$this->GetMeas();
		
		$this->GetSleepSummary();

		$this->GetActivity();

		}

	//******************************************************************************
	//	Getdevice
	//******************************************************************************
	protected function GetDevice()
		{

		$access_token = $this->ReadPropertyString("Userpassword");

		$url = "https://wbsapi.withings.net/v2/user?action=getdevice&access_token=".$access_token;

		$this->SendDebug("GetDevice:",$url,0);

		$curl = curl_init($url);

		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);

		$output = curl_exec($curl);
	
		$info = curl_getinfo($curl);

		curl_close($curl);

		$this->SendDebug("Answer:",$output,0);

		$this->Logging($output);

		$data = json_decode($output,TRUE); 

		if ( !array_key_exists('status',$data) == TRUE )
			{
			$this->SendDebug("GetDevice","Status: unbekannt",0);
			return;
			}

		$status = $data['status'];

		$this->SendDebug("GetDevice","Status:".$status,0);

		$id = $this->GetIDForIdent("name");

		$ModulID = IPS_GetParent($id);

		$data = $data['body'];

		$this->DoDevice($ModulID,$data);

		}

	//******************************************************************************
	//	Getmeas
	//******************************************************************************
	protected function GetMeas()
		{

		if ( $this->ReadPropertyBoolean("BodyMeasures") == false AND $this->ReadPropertyBoolean("BloodMeasures") == false)
			return;

		$access_token = $this->ReadPropertyString("Userpassword");;

		$meastype = 0 ;
		$category = 1;
		$startdate = time()- 24*60*60*5;
		$enddate = time();

		$url = "https://wbsapi.withings.net/measure?action=getmeas&access_token=".$access_token."&category=".$category."&startdate=".$startdate."&enddate=".$enddate;

		$this->SendDebug("GetMeas:",$url,0);

		$curl = curl_init($url);

		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);

		$output = curl_exec($curl);

		$info = curl_getinfo($curl);

		curl_close($curl);

		$this->SendDebug("Answer:",$output,0);

		$this->Logging($output);

		$data = json_decode($output,TRUE); 

		if ( !array_key_exists('status',$data) == TRUE )
			{
			$this->SendDebug("GetMeas","Status: unbekannt",0);
			return;
			}

		$status = $data['status'];

		$this->SendDebug("GetMeas","Status:".$status,0);

		if ( $status != 0)
			return;

		$id = $this->GetIDForIdent("name");

		$ModulID = IPS_GetParent($id);

		$data = $data['body'];

		$this->DoMeas($ModulID,$data);

		}

	//******************************************************************************
	//  GetActivity
	//******************************************************************************
	protected function GetActivity()
		{

		if ( $this->ReadPropertyBoolean("BodyLogging") == false )
			return;

		$access_token = $this->ReadPropertyString("Userpassword");;

		$meastype = 0 ;
		$category = 1;
		$startdate = time()- 24*60*60*5;
		$enddate = time();

		$startdate = date("Y-m-d",$startdate);
		$enddate   = date("Y-m-d",$enddate);

		$url = "https://wbsapi.withings.net/v2/measure?action=getactivity&access_token=".$access_token."&startdateymd=".$startdate."&enddateymd=".$enddate;

		$this->SendDebug("Getactivity:",$url,0);

		$curl = curl_init($url);

		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);

		$output = curl_exec($curl);

		$info = curl_getinfo($curl);

		curl_close($curl);

		$this->SendDebug("Answer:",$output,0);

		$this->Logging($output);

		$data = json_decode($output,TRUE);

		if ( !array_key_exists('status',$data) == TRUE )
			{
			$this->SendDebug("Getactivity","Status: unbekannt",0);
			return;
			}

		$status = $data['status'];

		$this->SendDebug("Getactivity","Status:".$status,0);

		if ( $status != 0)
			return;

		$id = $this->GetIDForIdent("name");

		$ModulID = IPS_GetParent($id);

		$data = $data['body'];

		$this->DoActivity($ModulID,$data);

		}

	//******************************************************************************
	//	GetSleepSummary
	//******************************************************************************
	protected function GetSleepSummary()
		{

		if ( $this->ReadPropertyBoolean("BloodLogging") == false )
			return;

		$access_token = $this->ReadPropertyString("Userpassword");
	
		$startdate = time()- 24*60*60*5;
		$enddate   = time();

		$startdate = date("Y-m-d",$startdate);
		$enddate   = date("Y-m-d",$enddate);

		$url = "https://wbsapi.withings.net/v2/sleep?action=getsummary&access_token=".$access_token."&startdateymd=".$startdate."&enddateymd=".$enddate;

		$this->SendDebug("GetSleepSummary:",$url,0);

		$curl = curl_init($url);

		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);

		$output = curl_exec($curl);

		$info = curl_getinfo($curl);

		curl_close($curl);

		$this->SendDebug("Answer:",$output,0);

		$this->Logging($output);

		$data = json_decode($output,TRUE); 

		if ( !array_key_exists('status',$data) == TRUE )
			{
			$this->SendDebug("GetSleepSummary","Status: unbekannt",0);
			return;
			}

		$status = $data['status'];

		$this->SendDebug("GetSleepSummary","Status:".$status,0);

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
	//
	//**************************************************************************    
	public function Destroy()
		{
		$this->UnregisterTimer("WIT_UpdateTimer");

		//Never delete this line!
		parent::Destroy();
		}

	//**************************************************************************
	//
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
	// 
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
		$id = $this->GetIDForIdent("WIT_UpdateTimer");
		$ModulID = IPS_GetParent($id);
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

	//**************************************************************************
	//
	//**************************************************************************    
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

	//**************************************************************************
	//
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

	//**************************************************************************
	//
	//**************************************************************************
	 protected function DoSleepSummary($ModulID,$data)
		{
		$this->SendDebug("DoSleepSummary","Schlaf wird ausgewertet.",0);
		
		$InstanceIDSleep = @$this->GetIDForIdent("SleepMonitor");
		if ( $InstanceIDSleep === false )
			{
			$this->SendDebug("DoSleepSummary","InstanceID Sleep nicht vorhanden. Abbruch",0);
			return;	
			}
		
		$data 		= @$data['series'];
		$deviceid	= @$device['deviceid'];

		if ( count($data) == 0 )
			{
			$this->SendDebug("DoSleepSummary","Keine Schlafdaten gefunden. Abbruch",0);				
			return false;
			}
		else
			$this->SendDebug("DoSleepSummary","Anzahl der Serien : ".count($data),0);
			

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


			}

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


		if(isset($sleepstartdate))			$this->SetValueToVariable($InstanceIDSleep,"Startzeit"	,$sleepstartdate,"~UnixTimestamp",1 );
		if(isset($sleependdate))			$this->SetValueToVariable($InstanceIDSleep,"Endezeit"	,$sleependdate,"~UnixTimestamp",2 );
		if(isset($sleepmodified))			$this->SetValueToVariable($InstanceIDSleep,"Updatezeit"	,$sleepmodified,"~UnixTimestamp",2 );
		if(isset($sleepdauer))				$this->SetValueToVariable($InstanceIDSleep,"Schlafdauer"	,$sleepdauer/60,"WITHINGS_M_Minuten",3 );
		if(isset($sleepwakeupduration))		$this->SetValueToVariable($InstanceIDSleep,"Wachphasen"	,$sleepwakeupduration/60,"WITHINGS_M_Minuten",8 );
		if(isset($sleeplightsleepduration))	$this->SetValueToVariable($InstanceIDSleep,"Leichtschlafphasen"	,$sleeplightsleepduration/60,"WITHINGS_M_Minuten",6 );
		if(isset($sleepdeepsleepduration))	$this->SetValueToVariable($InstanceIDSleep,"Tiefschlafphasen"	,$sleepdeepsleepduration/60,"WITHINGS_M_Minuten",7 );
		if(isset($sleepwakeupcount))		$this->SetValueToVariable($InstanceIDSleep,"Schlafunterbrechungen"	,$sleepwakeupcount,"",9 );
		if(isset($sleepdurationtosleep))	$this->SetValueToVariable($InstanceIDSleep,"Einschlafzeit"	,$sleepdurationtosleep/60 ,"WITHINGS_M_Minuten",4 );
		if(isset($sleepdurationtowakeup))	$this->SetValueToVariable($InstanceIDSleep,"Aufstehzeit"	,$sleepdurationtowakeup/60 ,"WITHINGS_M_Minuten",5 );


	}


	//**************************************************************************
	//
	//**************************************************************************
	 protected function DoActivity($ModulID,$data)
		{
		$this->SendDebug("DoActivity","Aktivitaeten werden ausgewertet.",0);

		// "Activity" isdas Ident fuer externe Daten in Withings deviceid = null
		$InstanceIDActivity = @$this->GetIDForIdent ("Activity");
		if ( $InstanceIDActivity === FALSE )
			$InstanceIDActivity = $this->CreateDummyInstance("Activity","Activity","Daten von externen APPs");

		if ( $InstanceIDActivity === false )
			{
			$this->SendDebug("DoActivity","InstanceID Activity nicht vorhanden. Abbruch",0);
			return;
			}

		$data 		= @$data['activities'];
		$deviceid	= @$device['deviceid'];

		if ( @count($data) == 0 )
			{
			$this->SendDebug("DoActivity","Keine Activity gefunden. Abbruch",0);
			return false;
			}
		else
			$this->SendDebug("DoActivity","Anzahl der Serien : ".count($data),0);

		// Bei diesen Daten ist der neueste als letztes
		foreach($data as $activity)
			{
			$activitydate				= @$activity['date'];
			$activitysteps				= @$activity['steps'];
			$activitydistance			= @$activity['distance'];
			$activityelevation			= @$activity['elevation'];
			$activitysoft				= @$activity['soft'];
			$activitymoderate			= @$activity['moderate'];
			$activityintense			= @$activity['intense'];
			$activitycalories			= @$activity['calories'];
			$activitytotalcalories		= @$activity['totalcalories'];
			$activitybrand				= @$activity['brand'];

			

		$this->SendDebug("DoActivity","Datum : ".				$activitydate,0);
		$this->SendDebug("DoActivity","Schritte : ".			$activitysteps,0);
		$this->SendDebug("DoActivity","Distanze : ".			$activitydistance,0);
		$this->SendDebug("DoActivity","Hoehe : ".				$activityelevation,0);
		$this->SendDebug("DoActivity","Soft : ".				$activitysoft,0);
		$this->SendDebug("DoActivity","Moderate : ".			$activitymoderate,0);
		$this->SendDebug("DoActivity","Intense : ".				$activityintense,0);
		$this->SendDebug("DoActivity","Kalorien : ".			$activitycalories,0);
		$this->SendDebug("DoActivity","Gesamtkalorien : ".		$activitytotalcalories,0);
		$this->SendDebug("DoActivity","Brand : ".				$activitybrand,0);
}
		if(isset($activitydate))			$this->SetValueToVariable($InstanceIDActivity,"Updatezeit"			,intval(strtotime ($activitydate)),"~UnixTimestamp",1 );
		if(isset($activitysteps))			$this->SetValueToVariable($InstanceIDActivity,"Schritte"			,intval($activitysteps),"WITHINGS_M_Schritte",2 );
		if(isset($activitydistance))		$this->SetValueToVariable($InstanceIDActivity,"Distanze"			,intval($activitydistance),"WITHINGS_M_Meter",3 );
		if(isset($activityelevation))		$this->SetValueToVariable($InstanceIDActivity,"Hoehenmeter"			,intval($activityelevation),"WITHINGS_M_Meter",4 );
		if(isset($activitycalories))		$this->SetValueToVariable($InstanceIDActivity,"Aktivitaetskalorien"	,intval($activitycalories),"WITHINGS_M_Kalorien",10 );
		if(isset($activitytotalcalories))	$this->SetValueToVariable($InstanceIDActivity,"Gesamtkalorien"		,intval($activitytotalcalories),"WITHINGS_M_Kalorien",11 );
		if(isset($activitysoft))			$this->SetValueToVariable($InstanceIDActivity,"Geringe Aktivitaet"	,intval($activitysoft/60 ),"WITHINGS_M_Minuten",20 );
		if(isset($activitymoderate))		$this->SetValueToVariable($InstanceIDActivity,"Mittlere Aktivitaet"	,intval($activitymoderate/60 ),"WITHINGS_M_Minuten",21 );
		if(isset($activityintense))			$this->SetValueToVariable($InstanceIDActivity,"Hohe Aktivitaet"		,intval($activityintense/60 ),"WITHINGS_M_Minuten",22 );



	}

	//**************************************************************************
	// Auswertung Geraeteinfos
	//**************************************************************************
	protected function DoDevice($ModulID,$data)
		{
		$this->SendDebug("DoDevice","Devices werden ausgewertet.",0);

		$data = @$data['devices'];
		if ( count($data) == 0  )
			{
			$this->Logging("Fehler bei DoDevice ".count($data));
			$this->SendDebug("DoDevice","Keine Geraete gefunden. Abbruch",0);
			return;
			}
		
		foreach($data as $device)
			{
			$devicetyp		= @$device['type'];
			$devicemodel	= @$device['model'];
			$devicebattery	= @$device['battery'];
			$deviceid		= @$device['deviceid'];
			$devicetimezone	= @$device['timezone'];

			$this->SendDebug("DoDevice : ","Type     : ".$devicetyp,0);
			$this->SendDebug("DoDevice : ","Modell   : ".$devicemodel,0);
			$this->SendDebug("DoDevice : ","Batterie : ".$devicebattery,0);
			$this->SendDebug("DoDevice : ","Deviceid : ".$deviceid,0);
			// $this->SendDebug("DoDevice : ","Zeitzone : ".$devicetimezone,0);

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
				$name = "Batterie";

				$this->SendDebug("DoDevice",$name."-".$devicebattery,0);
				$id = @IPS_GetVariableIDByName ( $name, $ObjektID ) ;
				if ( $id === FALSE )
					{
					$id = $this->RegisterVariableInteger("Batterie", $name,"WITHINGS_M_Batterie",0);
					IPS_SetParent($id, $ObjektID);
					}

				if ( $id > 0 )
					{
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
		$this->SendDebug("DoMeas","MeasDaten werden ausgewertet.".$ModulID."]",0);

		$CatIdWaage = @IPS_GetCategoryIDByName("Waage",$ModulID);
		if ( $CatIdWaage === false )
			{
			$this->Logging("CatID ist auf FALSE!");
			$this->SendDebug("DoMeas","CatID Waage nicht vorhanden.",0);
			$CatIdWaage = $this->CreateKategorie("Waage",$ModulID);
			if ( $CatIdWaage === false )
				throw new Exception("Kategorie Waage nicht definiert");
			}

		$CatIdBlutdruck = @IPS_GetCategoryIDByName("Blutdruck",$ModulID);
		if ( $CatIdBlutdruck === false )
			{
			$this->Logging("CatID ist auf FALSE!");
			$this->SendDebug("DoMeas","CatID Blutdruck nicht vorhanden.",0);
			$CatIdBlutdruck = $this->CreateKategorie("Blutdruck",$ModulID);
			if ( $CatIdBlutdruck === false )
				throw new Exception("Kategorie Blutdruck nicht definiert");
			}

		$measuregrps = @$data['measuregrps'];

		if ( count($measuregrps) == 0 )
			{
			$this->SendDebug("DoMeas","Keine Messgruppen gefunden. Abbruch",0);
			return false;
			}

		// Neueste nach hinten
		$measuregrps = array_reverse ( $measuregrps  );

		// Alle Messgruppen durchgehen
		foreach($measuregrps as $daten)
			{
			$time 		  = @$daten['date'];
			$deviceid 	= @$daten['deviceid'];
			$messungen 	= @$daten['measures'];

			if ( @count($messungen) == 0 )
				{
				$this->SendDebug("DoMeas","Keine Messungen gefunden. Weiter",0);
				continue;
				}

			$this->SendDebug("DoMeas","DeviceID .: ".$deviceid,0);
			// Alle Messungen durchgehen
			foreach($messungen as $messung)
				{
				$val = floatval ( $messung['value'] ) * floatval ( "1e".$messung['unit'] );
				$this->SendDebug("DoMeas","Messung Type : ".$messung['type']." : " .$val ."-".date('l jS \of F Y h:i:s A',$time),0);

				switch ($messung['type'])
					{
					case 1 :	$gewicht = round ($val,2);
										$TimestampWaage = $time;
								break;
					case 4 :	$groesse = round ($val,2);
								break;
					case 5 :	$fettfrei = round ($val,2);
								break;
					case 6 :	$fettprozent = round ($val,2);
								break;
					case 8 :	$fettanteil = round ($val,2);
								break;
					case 9 :	$diastolic = $val;
								break;
					case 10 :	$systolic = $val;
          					$TimestampBlutdruck = $time;
								break;
					case 11:	$puls = $val;
								break;
					case 12 :	$temperatur = round ($val,2);
								break;
					case 54 :	$sp02 = round ($val,2);
								break;
					case 71 :	$koerpertemperatur = round ($val,2);
								break;
					case 73 :	$hauttemperatur = round ($val,2);
								break;
					case 76 :	$muskelmasse = round ($val,2);
								break;
					case 77 :	$hydration = round ($val,2);
								break;
					case 88 :	$knochenmasse = round ($val,2);
								break;
					case 91 :	$pulswellen = round ($val,2);
								break;
					default:	$this->SendDebug("DoMeas","Messungstyp nicht vorhanden : ".$messung['type']."-".$val,0);
					}
				}
			}

		$id = @IPS_GetVariableIDByName("Groesse",$ModulID);
		if ( $id > 0 )
			$groesse = GetValueInteger($id);
		if ( $groesse !=  0)
			$bmi = @round($gewicht/(($groesse/100)*($groesse/100)),2);

		//				$id = $this->RegisterVariableInteger("timestamp", "DatumUhrzeit","~UnixTimestamp",0);

		if(isset($TimestampWaage))$this->SetValueToVariable($CatIdWaage,"DatumUhrzeit"							,intval($TimestampWaage)				,"~UnixTimestamp");
		if(isset($gewicht))				$this->SetValueToVariable($CatIdWaage,"Gewicht"										,floatval($gewicht)				,"WITHINGS_M_Kilo");
		if(isset($fettfrei))			$this->SetValueToVariable($CatIdWaage,"Fettfrei Anteil"						,floatval($fettfrei)			,"WITHINGS_M_Kilo");
		if(isset($fettprozent))		$this->SetValueToVariable($CatIdWaage,"Fett Prozent"							,floatval($fettprozent)		,"WITHINGS_M_Prozent");
		if(isset($fettanteil))		$this->SetValueToVariable($CatIdWaage,"Fett Anteil"								,floatval($fettanteil)		,"WITHINGS_M_Kilo");
		if(isset($puls))					$this->SetValueToVariable($CatIdWaage,"Puls"											,intval($puls)						,"WITHINGS_M_Puls");
		if(isset($bmi))						$this->SetValueToVariable($CatIdWaage,"BMI"												,floatval($bmi)							,"WITHINGS_M_BMI");
		if(isset($pulswellen))		$this->SetValueToVariable($CatIdWaage,"Pulswellengeschwindigkeit"	,intval($pulswellen));
		if(isset($knochenmasse))	$this->SetValueToVariable($CatIdWaage,"Knochenmasse"							,floatval($knochenmasse)	,"WITHINGS_M_Kilo");

		if(isset($TimestampBlutdruck))$this->SetValueToVariable($CatIdBlutdruck,"DatumUhrzeit"							,intval($TimestampBlutdruck)				,"~UnixTimestamp");
		if(isset($diastolic))			$this->SetValueToVariable($CatIdBlutdruck,"Diastolic"							,intval($diastolic)				,"WITHINGS_M_Blutdruck");
		if(isset($systolic))			$this->SetValueToVariable($CatIdBlutdruck,"Systolic"							,intval($systolic)				,"WITHINGS_M_Blutdruck");
		if(isset($puls))					$this->SetValueToVariable($CatIdBlutdruck,"Puls"									,intval($puls)						,"WITHINGS_M_Puls");

		}

	//******************************************************************************
	//	
	//******************************************************************************
	private function RegisterOAuth($WebOAuth) 
		{
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
				$this->SendDebug("ProcessOAuthData", "Authorization Code expected", 0);
					die("Authorization Code expected");
				}
			$code = $_GET['code'];  
			$this->SendDebug("ProcessOAuthData", "code: ".$code, 0);
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
		$this->SendDebug("FetchRefreshToken", "Mit Authentication Code Refresh Token holen ! Code : ".$code, 0);
			
		$options = array(
			'http' => array(
					'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
					'method'  => "POST",
					'content' => http_build_query(Array("code" => $code))
							)
						);
		$context = stream_context_create($options);

		$result = file_get_contents("https://oauth.ipmagic.de/access_token/withings", false, $context);

		$this->SendDebug("FetchRefreshToken","Tokens : ".$result,0);
		$data = json_decode($result);

		if(!isset($data->token_type) || $data->token_type != "Bearer") 
			{
			$this->SendDebug("FetchRefreshToken","Bearer Token expected",0 );
			return false;
			}

		$token = $data->refresh_token;	
		$this->SendDebug("FetchRefreshToken", "OK! Speichere Refresh Token . ".$token, 0);
		IPS_SetProperty($this->InstanceID, "User", $token);
		IPS_ApplyChanges($this->InstanceID);

		$this->FetchAccessToken($data->access_token, time() + $data->expires_in);
		
		}

	//******************************************************************************
	//	
	//******************************************************************************
	private function FetchAccessToken($Token = "", $Expires = 0) 
		{
		$this->SendDebug("FetchAccessToken", "Benutze Refresh Token um neuen Access Token zu holen !", 0);
			
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

		$this->SendDebug("FetchAccessToken",$result,0 );

		if(!isset($data->token_type) || $data->token_type != "Bearer") 
			{
			$this->SendDebug("FetchAccessToken","Bearer Token expected",0 );
			return false;
			}

		$Expires = time() + $data->expires_in;
				
		//Update Refresh Token wenn vorhanden
		if(isset($data->refresh_token)) 
			{
			$token = $data->refresh_token;		

			$this->SendDebug("FetchAccessToken", "Neuer Refresh Token erhalten", 0);
			$this->SendDebug("FetchAccessToken", "OK! Speichere Refresh Token . ".$token, 0);
			IPS_SetProperty($this->InstanceID , "User", $token);
			IPS_ApplyChanges($this->InstanceID);
			}
				
		$token = $data->access_token;
		$userid = $data->userid;		
		$VarID = @$this->GetIDForIdent("userid");
		if ( $VarID === false )
			$VarID = $this->RegisterVariableInteger("userid", "User ID"   ,"" ,0);
		if ( $VarID == true )
		    SetValue($VarID,$userid);
		    
		$this->SendDebug("FetchAccessToken", "Neuer Access Token ist gueltig bis ".date("d.m.y H:i:s", $Expires), 0);
		$this->SendDebug("FetchAccessToken", "OK! Speichere Access Token . ".$token, 0);
		
			
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
	//
	//******************************************************************************
	protected function SetValueToVariable($CatID,$name,$value,$profil=false,$position=0 )
		{

		if ( $profil != false )
			if (IPS_VariableProfileExists($profil) == false )
				$this->RegisterAllProfile();

		$ident = str_replace(" ","",$name);

		$VariableID = @IPS_GetVariableIDByName($name,$CatID );
		if ($VariableID === false)
			{
			$this->SendDebug("SetValueToVariable","VariableID nicht vorhanden : ".$CatID."-".$name."-".$profil,0);
			if ( is_int($value) == true )
				$VariableID = $this->RegisterVariableInteger( $ident, $name,$profil,$position);
			if ( is_string($value) == true )
				$VariableID = $this->RegisterVariableString($ident, $name,$profil,$position);
			if ( is_float($value) == true )
				$VariableID = $this->RegisterVariableFloat( $ident, $name,$profil,$position);
			if ( is_bool($value) == true )
				$VariableID = $this->RegisterVariableBool( $ident, $name,$profil,$position);
			if ( isset($VariableID) )
				IPS_SetParent($VariableID,$CatID);
			}

		// $array = $this->GetVariable ( $VariablenID );
		// $array = @IPS_GetObjectIDByIdent($ident,$CatID);
		// print_r($array);
		// $oldprofil = $array['VariableProfile'];
		$oldprofil = "?";
		// if ( $oldprofil != $profil )
			{
			$this->SendDebug("SetValueToVariable","Variableprofil hat sich geandert : ".$VariableID."-".$oldprofil."->".$profil,0);			
			// $this->RegisterAllProfile();
			IPS_SetVariableCustomProfile($VariableID,$profil);	
			}
		
		if ( $value > 0 AND $VariableID > 0 )
			SetValue($VariableID,$value);
		}


		
	}

?>
