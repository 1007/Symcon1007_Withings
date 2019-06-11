<?php
    
//******************************************************************************
//	Name		:	Withings Modul.php
//	Aufruf		:	
//	Info		:	
//	Funktionen	:	
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
		
                $this->RegisterPropertyBoolean("CheckBoxMeas" , false);
                $this->RegisterPropertyBoolean("CheckBoxSleepSummary" , false);
                $this->RegisterPropertyBoolean("CheckBoxActivity" , false);
                $this->RegisterPropertyBoolean("CheckBoxIntradayactivity" , false);
                
                $this->RegisterPropertyString("AccessToken", "");
                
                }

	//******************************************************************************
	// Register alle Profile
	//******************************************************************************
	protected function RegisterAllProfile()
		{
		$this->RegisterProfile(1,"WITHINGS_M_Groesse"  ,"Gauge"  ,""," cm");
		$this->RegisterProfile(1,"WITHINGS_M_Puls"     ,"Graph"  ,""," bpm");
    $this->RegisterProfile(1,"WITHINGS_M_Atmung"     ,"Graph"  ,""," Atemzuege/Minute");
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
                    {
                    return;
                    }
                set_time_limit (5 * 60);
                
                $starttime = time();            
		$this->Logging("Update");
		$this->SendDebug("Update Data","Update Data START        -> ".date('d.m.Y H:i:s ',time() ),0);
		
		if ( $this->RefreshTokens() == FALSE )
                    {
                    return;
                    }
                $this->SendDebug("Update Data","Update Data Get Device   -> ".date('d.m.Y H:i:s ',time() ),0);
		$this->GetDevice();   
                
                $this->SendDebug("Update Data","Update Data Get Meas     -> ".date('d.m.Y H:i:s ',time() ),0);
		$this->GetMeas();
		
                $this->SendDebug("Update Data","Update Data Get Sleep    -> ".date('d.m.Y H:i:s ',time() ),0);
		$this->GetSleepSummary();

                $this->SendDebug("Update Data","Update Data Get Activity -> ".date('d.m.Y H:i:s ',time() ),0);
		$this->GetActivity();

                $this->SendDebug("Update Data","Update Data Get Intra    -> ".date('d.m.Y H:i:s ',time() ),0);
		$this->GetIntradayactivity();
		
                $this->SendDebug("Update Data","Update Data ENDE         -> ".date('d.m.Y H:i:s ',time()),0);
                $endtime = time();
                
                $this->SendDebug("Update Data","Update Data Laufzeit         -> ".($endtime - $starttime) ,0);
                
		// $this->SubscribeHook();
		
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

                $startdate = time()- 24*60*60*5;
		$enddate = time();

		$url = "https://wbsapi.withings.net/measure?action=getmeas&access_token=".$access_token."&category=".$category."&startdate=".$startdate."&enddate=".$enddate;

		$this->SendDebug("GetMeas:",$url,0);

		$curl = curl_init($url);

		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);

		$output = curl_exec($curl);

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

		$startdate = date("Y-m-d",time() - 24*60*60*5);
		$enddate   = date("Y-m-d",time());

		$url = "https://wbsapi.withings.net/v2/measure?action=getactivity&access_token=".$access_token."&startdateymd=".$startdate."&enddateymd=".$enddate;

		$this->SendDebug("Getactivity:",$url,0);

		$curl = curl_init($url);

		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);

		$output = curl_exec($curl);

		curl_close($curl);

		$this->SendDebug("Answer:",$output,0);

		$this->LoggingExt($output);

		$data = json_decode($output,TRUE);

		if ( !array_key_exists('status',$data) == TRUE )
			{
			$this->SendDebug("Getactivity","Status: unbekannt",0);
			return;
			}

		$status = $data['status'];

		$this->SendDebug("Getactivity","Status:".$status,0);

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

		$startdate = time()- (24*60*60)*1  ;
		$enddate = time();

		$url = "https://wbsapi.withings.net/v2/measure?action=getintradayactivity&access_token=".$access_token."&startdate=".$startdate."&enddate=".$enddate;

		$this->SendDebug("Getintradayactivity:",$url,0);
                $this->SendDebug("Getintradayactivity:",date('d.m.Y H:i:s ',$startdate)." - ".date('d.m.Y H:i:s ',$enddate),0);

		$curl = curl_init($url);

		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);

		$output = curl_exec($curl);

		$this->LoggingExt($output,"Intraday.log");

		curl_close($curl);

		$this->SendDebug("Answer:",$output,0);

		$this->Logging($output);

		$data = json_decode($output,TRUE);

		if ( !array_key_exists('status',$data) == TRUE )
			{
			$this->SendDebug("Getintradayactivity","Status: unbekannt",0);
			return;
			}

		$status = $data['status'];

		$this->SendDebug("Getintradayactivity","Status:".$status,0);

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
	
		$startdate = time() - 24*60*60*5;
		$enddate   = time();

		$startdate = date("Y-m-d",$startdate);
		$enddate   = date("Y-m-d",$enddate);

		$datafields = "wakeupduration,lightsleepduration,deepsleepduration,remsleepduration,wakeupcount,durationtosleep,durationtowakeup,hr_average,hr_min,hr_max,rr_average,rr_min,rr_max";

		$url = "https://wbsapi.withings.net/v2/sleep?action=getsummary&access_token=".$access_token."&startdateymd=".$startdate."&enddateymd=".$enddate."&data_fields=".$datafields;

		$this->SendDebug("GetSleepSummary:",$url,0);

		$curl = curl_init($url);

		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);

		$output = curl_exec($curl);

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
	//  Logging
	//**************************************************************************
	private function LoggingExt($Text,$file="WithingsExt.log",$delete=false)
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
                    unlink($logdatei);
                
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
			$sleepremduration       = @$sleep['data']['remsleepduration'];
			$sleephraverage         = @$sleep['data']['hr_average'];
			$sleephrmin         		= @$sleep['data']['hr_min'];
			$sleephrmax             = @$sleep['data']['hr_max'];
			$sleeprraverage         = @$sleep['data']['rr_average'];
			$sleeprrmin         		= @$sleep['data']['rr_min'];
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
		if(isset($sleepstartdate))		$this->SetValueToVariable($InstanceIDSleep,"Startzeit"              ,$sleepstartdate                ,"~UnixTimestamp"       ,1  ,false,false,"startzeit");
		if(isset($sleependdate))		$this->SetValueToVariable($InstanceIDSleep,"Endezeit"               ,$sleependdate                  ,"~UnixTimestamp"       ,2  ,false,false,"endezeit");
		if(isset($sleepmodified))		$this->SetValueToVariable($InstanceIDSleep,"Updatezeit"             ,$sleepmodified                 ,"~UnixTimestamp"       ,0 ,false,false,"timestamp");
		if(isset($sleepdauer))			$this->SetValueToVariable($InstanceIDSleep,"Schlafdauer"            ,$sleepdauer/60                 ,"WITHINGS_M_Minuten"   ,3  ,false,false,"schlafdauer");
		if(isset($sleepwakeupduration))		$this->SetValueToVariable($InstanceIDSleep,"Wachphasen"             ,$sleepwakeupduration/60        ,"WITHINGS_M_Minuten"   ,8  ,false,false,"wachphasen");
		if(isset($sleeplightsleepduration))	$this->SetValueToVariable($InstanceIDSleep,"Leichtschlafphasen"     ,$sleeplightsleepduration/60    ,"WITHINGS_M_Minuten"   ,6  ,false,false,"leichtschlafphasen");
		if(isset($sleepdeepsleepduration))	$this->SetValueToVariable($InstanceIDSleep,"Tiefschlafphasen"       ,$sleepdeepsleepduration/60     ,"WITHINGS_M_Minuten"   ,7  ,false,false,"tiefschlafphasen");
		if(isset($sleepwakeupcount))		$this->SetValueToVariable($InstanceIDSleep,"Schlafunterbrechungen"  ,$sleepwakeupcount              ,"WITHINGS_M_Anzahl"                     ,9  ,false,false,"schlafunterbrechungen");
		if(isset($sleepdurationtosleep))	$this->SetValueToVariable($InstanceIDSleep,"Einschlafzeit"          ,$sleepdurationtosleep/60       ,"WITHINGS_M_Minuten"   ,4  ,false,false,"einschlafzeit");
		if(isset($sleepdurationtowakeup))	$this->SetValueToVariable($InstanceIDSleep,"Aufstehzeit"            ,$sleepdurationtowakeup/60      ,"WITHINGS_M_Minuten"   ,5  ,false,false,"aufstehzeit");
		if(isset($sleepremduration))		$this->SetValueToVariable($InstanceIDSleep,"REMschlafphasen"        ,$sleepremduration/60      		,"WITHINGS_M_Minuten"   ,7  ,false,false,"remschlafphasen");
		if(isset($sleephraverage))		$this->SetValueToVariable($InstanceIDSleep,"Herzschlag Durchschnitt"        ,$sleephraverage      		,"WITHINGS_M_Puls"   ,10  ,false,false,"herzschlagdurchschnitt");
		if(isset($sleephrmin))		$this->SetValueToVariable($InstanceIDSleep,"Herzschlag Minimal"        ,$sleephrmin      		,"WITHINGS_M_Puls"   ,11  ,false,false,"herzschlagminimal");
		if(isset($sleephrmax))		$this->SetValueToVariable($InstanceIDSleep,"Herzschlag Maximal"        ,$sleephrmax      		,"WITHINGS_M_Puls"   ,12  ,false,false,"herzschlagmaximal");
		if(isset($sleeprraverage))		$this->SetValueToVariable($InstanceIDSleep,"Atmung Durchschnitt"        ,$sleeprraverage      		,"WITHINGS_M_Atmung"   ,15  ,false,false,"atemzuegedurchschnitt");
		if(isset($sleeprrmin))		$this->SetValueToVariable($InstanceIDSleep,"Atmung Minimal"        ,$sleeprrmin      		,"WITHINGS_M_Atmung"   ,16  ,false,false,"atemzuegeminimal");
		if(isset($sleeprrmax))		$this->SetValueToVariable($InstanceIDSleep,"Atmung Maximal"        ,$sleeprrmax      		,"WITHINGS_M_Atmung"   ,17  ,false,false,"atemzuegemaximal");



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
			$activitydate		= @$activity['date'];
			$activitysteps		= @$activity['steps'];
			$activitydistance	= @$activity['distance'];
			$activityelevation	= @$activity['elevation'];
			$activitysoft		= @$activity['soft'];
			$activitymoderate	= @$activity['moderate'];
			$activityintense	= @$activity['intense'];
			$activitycalories	= @$activity['calories'];
			$activitytotalcalories  = @$activity['totalcalories'];
			$activitybrand		= @$activity['brand'];

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

		// letzte Daten in Variable schreiben
		if(isset($activitydate))                $this->SetValueToVariable($InstanceIDActivity,"Updatezeit"		,intval(strtotime ($activitydate))  ,"~UnixTimestamp"       ,1  ,false,false,"timestamp"	);
		if(isset($activitysteps))               $this->SetValueToVariable($InstanceIDActivity,"Schritte"		,intval($activitysteps)             ,"WITHINGS_M_Schritte"  ,2	,false,false,"schritte");
		if(isset($activitydistance))            $this->SetValueToVariable($InstanceIDActivity,"Distanze"		,floatval($activitydistance)          ,"WITHINGS_M_Meter"     ,3	,false,false,"distanze");
		if(isset($activityelevation))           $this->SetValueToVariable($InstanceIDActivity,"Hoehenmeter"		,floatval($activityelevation)         ,"WITHINGS_M_Meter"     ,4	,false,false,"hoehenmeter");
		if(isset($activitycalories))            $this->SetValueToVariable($InstanceIDActivity,"Aktivitaetskalorien"	,floatval($activitycalories)          ,"WITHINGS_M_Kalorien"  ,10	,false,false,"aktivitaetskalorien");
		if(isset($activitytotalcalories))       $this->SetValueToVariable($InstanceIDActivity,"Gesamtkalorien"		,floatval($activitytotalcalories)     ,"WITHINGS_M_Kalorien"  ,11	,false,false,"gesamtkalorien");
		if(isset($activitysoft))                $this->SetValueToVariable($InstanceIDActivity,"Geringe Aktivitaet"	,intval($activitysoft/60 )          ,"WITHINGS_M_Minuten"   ,20	,false,false,"geringeaktivitaet");
		if(isset($activitymoderate))            $this->SetValueToVariable($InstanceIDActivity,"Mittlere Aktivitaet"	,intval($activitymoderate/60 )      ,"WITHINGS_M_Minuten"   ,21	,false,false,"mittlereaktivitaet");
		if(isset($activityintense))             $this->SetValueToVariable($InstanceIDActivity,"Hohe Aktivitaet"		,intval($activityintense/60 )       ,"WITHINGS_M_Minuten"   ,22	,false,false,"hoheaktivitaet");


    // $this->Reaggregieren($InstanceIDActivity);

	}

	//**************************************************************************
	//
	//**************************************************************************
	 protected function DoGetintradayactivity($ModulID,$data)
		{
		$this->SendDebug("DoGetintradayactivity","Aktivitaeten werden ausgewertet.",0);

		// "IntraDayActivity" isdas Ident fuer externe Daten in Withings deviceid = null
		$InstanceIDActivity = @$this->GetIDForIdent ("IntraDayActivity");
		if ( $InstanceIDActivity === FALSE )
			$InstanceIDActivity = $this->CreateDummyInstance("IntraDayActivity","IntraDayActivity","Daten von externen APPs");

		if ( $InstanceIDActivity === false )
			{
			$this->SendDebug("DoGetintradayactivity","InstanceID IntraDayActivity nicht vorhanden. Abbruch",0);
			return;
			}


		$data 		= @$data['series'];

		if ( @count($data) == 0 )
			{
			$this->SendDebug("DoGetintradayactivity","Keine Activity gefunden. Abbruch",0);
			return false;
			}
		else
			$this->SendDebug("DoGetintradayactivity","Anzahl der Serien : ".count($data),0);

		// Bei diesen Daten ist der neueste als letztes ???
		$keys = array_keys($data);
                
                 $this->LoggingExt("Start",$file="WithingsExt2.log",true );
                 
		foreach($keys as $key)
			{
			$activitydate = date('d.m.Y H:i:s ',$key);;
                        
			$activitycalories	= @$data[$key]['calories'];
			$activitydistance	= @$data[$key]['distance'];
			$activityduration	= @$data[$key]['duration'];
			$activityelevation	= @$data[$key]['elevation'];
			$activitysteps		= @$data[$key]['steps'];
			$activitystroke		= @$data[$key]['stroke'];
			$activitypoollap	= @$data[$key]['pool_lap'];

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
                        //echo "\n".$Text ;
                        $this->LoggingExt($Text,$file="WithingsExt2.log");

			// if(isset($activitydate))	$this->SetValueToVariable($InstanceIDActivity,"Updatezeit"	,intval($key)                       ,"~UnixTimestamp"		,1	,true,$key,"timestamp");
			if(isset($activitycalories))	$this->SetValueToVariable($InstanceIDActivity,"Kalorien"	,floatval($activitycalories)          ,"WITHINGS_M_Kalorien"	,10	,true,$key,"kalorien");
			if(isset($activitydistance))	$this->SetValueToVariable($InstanceIDActivity,"Distanze"	,floatval($activitydistance)          ,"WITHINGS_M_Meter"       ,3	,true,$key,"distanze");
			if(isset($activityelevation))	$this->SetValueToVariable($InstanceIDActivity,"Hoehenmeter"	,floatval($activityelevation)         ,"WITHINGS_M_Meter"	,4	,true,$key,"hoehenmeter");
			if(isset($activitysteps))	$this->SetValueToVariable($InstanceIDActivity,"Schritte"	,intval($activitysteps)               ,"WITHINGS_M_Schritte"	,11	,true,$key,"schritte");

			}

		// letzte Daten in Variable schreiben ( besser vielleicht nicht) besser ohne Logging machen
                
		if(isset($activitydate))		$this->SetValueToVariable($InstanceIDActivity,"Updatezeit"	,intval(strtotime ($activitydate))    ,"~UnixTimestamp"		,1	,false,false,"timestamp");
		if(isset($activitycalories))		$this->SetValueToVariable($InstanceIDActivity,"Kalorien"	,floatval($activitycalories)          ,"WITHINGS_M_Kalorien"	,10	,false,false,"kalorien");
		if(isset($activitydistance))		$this->SetValueToVariable($InstanceIDActivity,"Distanze"	,floatval($activitydistance)          ,"WITHINGS_M_Meter"	,3	,false,false,"distanze");
		if(isset($activityelevation))		$this->SetValueToVariable($InstanceIDActivity,"Hoehenmeter"	,floatval($activityelevation)         ,"WITHINGS_M_Meter"	,4	,false,false,"hoehenmeter");
		if(isset($activitysteps))		$this->SetValueToVariable($InstanceIDActivity,"Schritte"	,intval($activitysteps)               ,"WITHINGS_M_Schritte"	,11	,false,false,"schritte");
                

                $this->Reaggregieren($InstanceIDActivity);

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
			$devicetyp	= @$device['type'];
			$devicemodel	= @$device['model'];
			$devicebattery	= @$device['battery'];
			$deviceid	= @$device['deviceid'];
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
				$name = "batterie";

				$this->SendDebug("DoDevice",$name."-".$devicebattery,0);
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
			$time 		= @$daten['date'];
			$deviceid 	= @$daten['deviceid'];
			$messungen 	= @$daten['measures'];
                        
                        
                        $InstanceIDDeviceID = @$this->GetIDForIdent($deviceid);

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
				// $this->SendDebug("DoMeas","Messung Type : ".$messung['type']." : " .$val ."-".date('l jS \of F Y h:i:s A',$time),0);

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
					default:	$this->SendDebug("DoMeas","Messungstyp nicht vorhanden : ".$messung['type']."-".$val,0);
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
                                        $this->SetValueToVariable($ID,"Gewicht" ,floatval($gewicht) ,"WITHINGS_M_Kilo"  ,10,false,0,"weight");
										if ( $groesse !=  0)
											{
											$bmi = @round($gewicht/(($groesse/100)*($groesse/100)),2);
 											$ID = $this->CheckOldVersionCatID("bmi",$CatIdWaage,$deviceID);
                                       		$this->SetValueToVariable($ID,"BMI" ,floatval($bmi) ,"WITHINGS_M_BMI" ,10,false,0,"bmi");
											}
										$ID = $this->CheckOldVersionCatID("timestamp",$CatIdWaage,$deviceID);
                                        $this->SetValueToVariable($ID,"Updatezeit" ,intval($updatetime) ,"~UnixTimestamp"  ,0,false,0,"timestamp");
                
										
										break;
										
                            case 4 :	$groesse = $value;
										break;
                            
                            case 5 :	$fettfrei = $value;
                                        $ID = $this->CheckOldVersionCatID("fatfree",$CatIdWaage,$deviceID);
                                        $this->SetValueToVariable($ID,"Fettfrei Anteil" ,floatval($fettfrei) ,"WITHINGS_M_Kilo" ,10,false,0,"fatfree");
                                        break;
                                    
                            case 6 :	$fettprozent = $value;
                                        $ID = $this->CheckOldVersionCatID("fatradio",$CatIdWaage,$deviceID);
                                        $this->SetValueToVariable($ID,"Fett Prozent" ,floatval($fettprozent) ,"WITHINGS_M_Prozent" ,10,false,0,"fatradio");
                						break;
                                        
                    		case 8 :	$fettanteil = $value;
										$ID = $this->CheckOldVersionCatID("fatmassweight",$CatIdWaage,$deviceID);
                                        $this->SetValueToVariable($ID,"Fett Anteil" ,floatval($fettanteil) ,"WITHINGS_M_Kilo" ,10,false,0,"fatmassweight");
										break;

							case 9 :	$diastolic = $value;
										$ID = $this->CheckOldVersionCatID("diastolicblood",$CatIdBlutdruck,$deviceID);
										$this->SetValueToVariable($ID,"Diastolic" ,intval($diastolic) ,"WITHINGS_M_Blutdruck" ,10,false,0,"diastolicblood");
										break;
							
							case 10 :	$systolic = $value;
                                		$ID = $this->CheckOldVersionCatID("systolicblood",$CatIdBlutdruck,$deviceID);
                                		$this->SetValueToVariable($ID,"Systolic" ,intval($systolic) ,"WITHINGS_M_Blutdruck" ,10,false,0,"systolicblood");
										$ID = $this->CheckOldVersionCatID("timestamp",$CatIdBlutdruck,$deviceID);
                                        $this->SetValueToVariable($ID,"Updatezeit" ,intval($updatetime) ,"~UnixTimestamp"  ,0,false,0,"timestamp");
                                		break;
							
							case 11:	$puls = $value;
										$ID = $this->CheckOldVersionCatID("heartpulse",$CatIdWaage,$deviceID);
										$this->SetValueToVariable($ID,"Puls" ,intval($puls) ,"WITHINGS_M_Puls" ,10,false,0,"heartpulse");
										break;
							
							case 12 :	$temperatur = $value;
										$this->SetValueToVariable($deviceID,"Temperatur" ,floatval($temperatur) ,"~Temperature" ,2,false,0,"temperatur");
                                        $this->SetValueToVariable($deviceID,"Updatezeit" ,intval($updatetime) ,"~UnixTimestamp"  ,0,false,0,"timestamp");
                						break;
							
							case 54 :	$sp02 = $value;
										// $ID = $this->CheckOldVersionCatID("",$CatIdWaage,$deviceID);
										break;
                    		
                    		case 71 :	$koerpertemperatur = $value; 
                    					$this->SetValueToVariable($deviceID,"Koerpertemperatur" ,floatval($koerpertemperatur) ,"~Temperature" ,3,false,0,"koerpertemperatur");
										break;
							
							case 73 :	$hauttemperatur = $value;
										$this->SetValueToVariable($deviceID,"Hauttemperatur" ,floatval($hauttemperatur) ,"~Temperature" ,4,false,0,"hauttemperatur");
										break;
							
							case 76 :	$muskelmasse = $value;
										$ID = $this->CheckOldVersionCatID("muskelmasse",$CatIdWaage,$deviceID);
										$this->SetValueToVariable($ID,"Muskelmasse" ,floatval($muskelmasse) ,"WITHINGS_M_Kilo" ,10,false,0,"muskelmasse");
                						break;
							
							case 77 :	$hydration = $value;
										$ID = $this->CheckOldVersionCatID("wasseranteil",$CatIdWaage,$deviceID);
										$this->SetValueToVariable($ID,"Wasseranteil" ,floatval($hydration) ,"WITHINGS_M_Prozent" ,10,false,0,"wasseranteil");
        								break;
							
							case 88 :	$knochenmasse = $value;
										$ID = $this->CheckOldVersionCatID("bonemass",$CatIdWaage,$deviceID);
										$this->SetValueToVariable($ID,"Knochenmasse" ,floatval($knochenmasse) ,"WITHINGS_M_Kilo" ,10,false,0,"bonemass");
                						break;
							
							case 91 :	$pulswellen = $value;
										$ID = $this->CheckOldVersionCatID("pulswave",$CatIdWaage,$deviceID);
										$this->SetValueToVariable($ID,"Pulswellengeschwindigkeit" ,floatval($pulswellen) ,"~WindSpeed.ms" ,1,false,99,"pulswave");
                						break;
					                    
                                                            
                            default:	$this->SendDebug("DoMeas","Messungstyp nicht vorhanden : ".$key."-".$value,0);
					
                            }
                    
                    
                        }
                    }

                // if(isset($TimestampWaage))      {$this->SetValueToVariable($CatIdWaage,"Updatezeit"                  ,intval($TimestampWaage)        ,"~UnixTimestamp"       ,1,false,0,"timestamp");}
                
                //if(isset($gewicht))             {$this->SetValueToVariable($CatIdWaage,"Gewicht"                     ,floatval($gewicht)             ,"WITHINGS_M_Kilo"      ,1,false,0,"weight");}
                //if(isset($fettfrei))            {$this->SetValueToVariable($CatIdWaage,"Fettfrei Anteil"             ,floatval($fettfrei)            ,"WITHINGS_M_Kilo"      ,1,false,0,"fatfree");}
                //if(isset($fettprozent))         {$this->SetValueToVariable($CatIdWaage,"Fett Prozent"                ,floatval($fettprozent)         ,"WITHINGS_M_Prozent"   ,1,false,0,"fatradio");}
                // if(isset($fettanteil))          {$this->SetValueToVariable($CatIdWaage,"Fett Anteil"                 ,floatval($fettanteil)          ,"WITHINGS_M_Kilo"      ,1,false,0,"fatmassweight");}
                // if(isset($puls))                {$this->SetValueToVariable($CatIdWaage,"Puls"                        ,intval($puls)                  ,"WITHINGS_M_Puls"      ,1,false,0,"heartpulse");}
                // if(isset($bmi))                 {$this->SetValueToVariable($CatIdWaage,"BMI"                         ,floatval($bmi)                 ,"WITHINGS_M_BMI"       ,1,false,0,"bmi");}
                // if(isset($pulswellen))          {$this->SetValueToVariable($CatIdWaage,"Pulswellengeschwindigkeit"   ,intval($pulswellen)            ,""                     ,1,false,0,"pulswave");}
                // if(isset($knochenmasse))        {$this->SetValueToVariable($CatIdWaage,"Knochenmasse"                ,floatval($knochenmasse)        ,"WITHINGS_M_Kilo"      ,1,false,0,"bonemass");}
                // if(isset($muskelmasse))         {$this->SetValueToVariable($CatIdWaage,"Muskelmasse"                 ,floatval($muskelmasse)         ,"WITHINGS_M_Kilo"      ,1,false,0,"muskelmasse");}
                // if(isset($hydration))           {$this->SetValueToVariable($CatIdWaage,"Wasseranteil"                ,floatval($hydration)           ,"WITHINGS_M_Prozent"   ,1,false,0,"wasseranteil");}
        
                // if(isset($TimestampBlutdruck))  {$this->SetValueToVariable($CatIdBlutdruck,"Updatezeit"              ,intval($TimestampBlutdruck)    ,"~UnixTimestamp"       ,1,false,0,"timestamp");}
                // if(isset($diastolic))           {$this->SetValueToVariable($CatIdBlutdruck,"Diastolic"               ,intval($diastolic)             ,"WITHINGS_M_Blutdruck" ,1,false,0,"diastolicblood");}
                // if(isset($systolic))            {$this->SetValueToVariable($CatIdBlutdruck,"Systolic"                ,intval($systolic)              ,"WITHINGS_M_Blutdruck" ,1,false,0,"systolicblood");}
                // if(isset($puls))                {$this->SetValueToVariable($CatIdBlutdruck,"Puls"                    ,intval($puls)                  ,"WITHINGS_M_Puls"      ,1,false,0,"heartpulse");}

                
                // if(isset($TimestampThermo))     {$this->SetValueToVariable($deviceIDthermo,"Updatezeit"              ,intval($TimestampThermo)       ,"~UnixTimestamp"       ,1,false,0,"timestamp");}
                // if(isset($temperatur))          {$this->SetValueToVariable($deviceIDthermo,"Temperatur"              ,floatval($temperatur)          ,"~Temperature"         ,2,false,0,"temperatur");}
                // if(isset($koerpertemperatur))   {$this->SetValueToVariable($deviceIDthermo,"Koerpertemperatur"       ,floatval($koerpertemperatur)   ,"~Temperature"         ,3,false,0,"koerpertemperatur");}
                // if(isset($hauttemperatur))      {$this->SetValueToVariable($deviceIDthermo,"Hauttemperatur"          ,floatval($hauttemperatur)      ,"~Temperature"         ,4,false,0,"hauttemperatur");}
      
		}
	//******************************************************************************
	//	
	//******************************************************************************
	private function CheckOldVersionCatID($ident,$CatId,$DeviceID) 
		{
                $VariableID = @IPS_GetObjectIDByIdent($ident,$CatId);
                if ( $VariableID == true )
                    {
                    $this->SendDebug("CheckOldVersionCatID","Variable noch in alter Kategorie : ".$ident."-".$CatId,0);   
                    $ID = $CatId;
                    }
                else
                    {
                    $this->SendDebug("CheckOldVersionCatID","Variable nicht mehr alter Kategorie : ".$ident."-".$CatId,0);
                    $ID = $DeviceID;
                    }
                return $ID;    
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
		$this->SendDebug("FetchAccessToken", "Benutze Refresh Token um neuen Access Token zu holen : " . $Token, 0);
			
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
	protected function SetValueToVariable($CatID,$name,$value,$profil=false,$position=0 ,$asynchron=false,$Timestamp=0,$VarIdent=false,$NoLogging=false)
		{

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
                            $this->SendDebug("SetValueToVariable","Variable Ident nicht gefunden : ".$CatID."-".$name."-".$VarIdent."-".$VariableID,0);
                            
                            
                            }
                            
                        }
		else
                    {
                    $this->SendDebug("SetValueToVariable","Variable Ident nicht definiert : ".$CatID."-".$name."-".$VarIdent."-".$VariableID,0);
                    //$VariableID = @IPS_GetVariableIDByName($name,$CatID );
                    return false;
                    }
                    
                    
         
                
		if ($VariableID === false)
			{
			$this->SendDebug("SetValueToVariable","VariableID nicht vorhanden : ".$CatID."-".$name."-".$profil,0);

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

		// $array = $this->GetVariable ( $VariablenID );
		// $array = @IPS_GetObjectIDByIdent($ident,$CatID);
		// print_r($array);
		// $oldprofil = $array['VariableProfile'];
		
		// if ( $oldprofil != $profil )


			{
			//$this->SendDebug("SetValueToVariable","Variableprofil hat sich geandert : ".$VariableID."-".$oldprofil."->".$profil,0);
			// $this->RegisterAllProfile();
			//IPS_SetVariableCustomProfile($VariableID,$profil);
			}
		
		if ($VariableID === false)
			{
			$this->SendDebug("SetValueToVariable","VariableID nicht vorhanden : ".$CatID."-".$name."-".$profil,0);
                        return;
                        }

			// $this->SendDebug("SetValueToVariable","Set Position : ".$CatID."-".$name."-".$position,0);
        
                        IPS_SetPosition($VariableID, $position);
                        
		if ( $asynchron == true )
                    {
                    $Reaggieren = $this->SaveDataToDatabase($VariableID,$Timestamp,$value);
                    }
		else
                    {
                    if ( $value > 0 AND $VariableID > 0 )
                        {
                        SetValue($VariableID,$value);
                        }

			
                    }
                return $Reaggieren;
                }
		


	//******************************************************************************
	//
	//******************************************************************************
	protected function SaveDataToDatabase($Variable,$Timestamp,$Value)
		{
//                 return;
		//$this->SendDebug("SaveDataToDatabase","SaveDataToDatabase",0);
                $Reaggregieren = false;
                
                $archiveID = $this->GetArchivID();
		if ( $archiveID == false )
                    {   
                    return;
                    }

                $status = AC_GetLoggingStatus ($archiveID, $Variable);
                if ( $status == TRUE )
                    {
                    // $this->SendDebug("SaveDataToDatabase","Variable wird geloggt -> " . $Variable,0);
                    }
                else 
                    {
                    // $this->SendDebug("SaveDataToDatabase","Variable wird nicht geloggt -> " . $Variable,0);
                    return;
                    }
                    
                $parent = IPS_GetParent($Variable);
                $LastTimestampID = @IPS_GetObjectIDByIdent("timestamp",$parent );
                $LastTimestamp = GetValue($LastTimestampID);
                
                 /*   
                 return;
                 
		$datas = @AC_GetLoggedValues($archiveID, $Variable, $Timestamp, time(), 100);

		if ( $datas == false )
                    {
                    return;
                    }

                return;
		    
		$counter = 0;
		$existcounter = 0;
		foreach ( $datas as $data )
			{
			$DataTimestamp = $data['TimeStamp'];
			//$time = date('d.m.Y H:i:s ',$DataTimestamp);


			if ( $DataTimestamp == $Timestamp )
				{
				$existcounter = $existcounter + 1;
				}
			else
				{
				}
			$counter = $counter + 1;
			}
                */

		// if ( $existcounter == 0 );
                
			{
			if (!function_exists('AC_AddLoggedValues'))
				{
				$this->SendDebug("SaveDataToDatabase","!function_exists('AC_AddLoggedValues').",0);
				}
			else
				{
                                
				//$this->SendDebug("SaveDataToDatabase","Neue Daten werden asynchron geschrieben:".$Variable."-".date('d.m.Y h:i:s ',$Timestamp)."-".$Value,0);
                                //$this->SendDebug("SaveDataToDatabase","Update Data Start    -> ".date('d.m.Y H:i:s ',time() ),0);
                                
                                //$datas = @AC_GetLoggedValues($archiveID, $Variable, time(), time(), 1);
                                
                                // $this->SendDebug("SaveDataToDatabase","Lasttimestamp  -> ".date('d.m.Y H:i:s ',$LastTimestamp ),0);
                                
                                
                                if ( $LastTimestamp >= $Timestamp )
                                    {
                                    //$this->SendDebug("SaveDataToDatabase","Lasttimestamp  -> ".date('d.m.Y H:i:s ',$LastTimestamp) ." Timestamp -> ".date('d.m.Y H:i:s ',$Timestamp),0);
                                    
                                    }
                                else 
                                    {
                                    $this->SendDebug("SaveDataToDatabase","Lasttimestamp  -> ".date('d.m.Y H:i:s ',$LastTimestamp) ." Timestamp -> ".date('d.m.Y H:i:s ',$Timestamp),0);
                                    // $this->SendDebug("SaveDataToDatabase","Lasttimestamp  -> ".date('d.m.Y H:i:s ',$LastTimestamp ),0);
                                    
                                    $status = @AC_AddLoggedValues($archiveID, $Variable,[['TimeStamp' => $Timestamp, 'Value' 	=> $Value]]);
                                    if ( $status == true )
                                        $statustext = "OK";
                                    else 
                                        {
                                        $statustext = "NOK";
                                        }
                                    $Text = date('d.m.Y H:i:s ',$Timestamp) . $Variable . " - ".$Value." Status : ".$statustext;
                                    $this->LoggingExt($Text,"WithingsDataToDatabase.log");
                                    } 
                                
				//AC_AddLoggedValues($archiveID, $Variable,[['TimeStamp' => $Timestamp, 'Value' 	=> $Value]]);
				
                                // $this->SendDebug("SaveDataToDatabase","Update Data Ende   -> ".date('d.m.Y H:i:s ',time() ),0);
                                
                                $Reaggregieren = true;

				}
			}

		return	$Reaggregieren ;

		}
	
	//**************************************************************************
	// Subscribe Hook
	//**************************************************************************
	protected function SubscribeHook()
		{
		
		$access_token = $this->ReadPropertyString("Userpassword");

		$startdate = date("Y-m-d",time() - 24*60*60*5);
		$enddate   = date("Y-m-d",time());
		$callbackurl = urlencode("https://5618f6766a2fe6943e9ec98ddeac4bc4.ipmagic.de/hook/Withings4IPSymcon13906");

		// $callbackurl = urlencode("https://home.inisnet.de:82");


		$url = "https://wbsapi.withings.net/v2/measure?action=getactivity&access_token=".$access_token."&startdateymd=".$startdate."&enddateymd=".$enddate;
		$url = "https://wbsapi.withings.net/notify?action=subscribe&access_token=".$access_token."&callbackurl=".$callbackurl."&appli=44&comment=Kommentar";
		$this->SendDebug("Subscribe:",$url,0);

		$curl = curl_init($url);

		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);

		$output = curl_exec($curl);

		curl_close($curl);

		$this->SendDebug("Answer:",$output,0);


		
			
		}
		
	//**************************************************************************
	// Hook Data auswerten
	//**************************************************************************
	protected function ProcessHookData()
		{
		header("HTTP/1.1 200 OK");
		http_response_code(200);

		
		// print_r($_IPS);
		$s = "Webhook";
		IPS_LogMessage(basename(__FILE__),$s);
		}

	//**************************************************************************
	//
	//**************************************************************************
	 protected function GetArchivID()
	{
	$guid = "{43192F0B-135B-4CE7-A0A7-1475603F3060}";
	$array = IPS_GetInstanceListByModuleID($guid);
	$archive_id =  @$array[0];
	if ( !isset($archive_id) )
		{
		$this->SendDebug("GetArchivID","Archive Control nicht gefunden!");
		return false;
		}
	return $archive_id;
	}

	//**************************************************************************
	//
	//**************************************************************************
	 protected function Reaggregieren($Instanze)
		{
//		return;
                $this->SendDebug("Reaggregieren","Reaggregiere Data in Database.",0);
                
                $childs = IPS_GetChildrenIDs($Instanze);
                
                $array = array();
                
                foreach($childs as $child )
                    {
                    $status = AC_GetLoggingStatus($this->GetArchivID(),$child);
                    if ( $status == true )
                        {
                        $array[] = $child;
                        
                        }
                        
                    }

                $count = @count($array);

								if ( $count == false )
									{
                  $this->SendDebug("Reaggregieren","Count = FALSE",0);
									return;
									}

                $childs = $array;
                $random = rand(0,$count-1);
                
                $child = $childs[$random];
                
                
                //foreach($childs as $child )
                    {
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
		}

	}


