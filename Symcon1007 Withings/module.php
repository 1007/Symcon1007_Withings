<?
//******************************************************************************
//	Name		:	module.php
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
      	$this->RegisterPropertyBoolean("BloodLogging", false);  
      	$this->RegisterPropertyBoolean("BloodVisible", false);  
      	$this->RegisterPropertyBoolean("BodyLogging" , false);  
      	$this->RegisterPropertyBoolean("BodyVisible" , false);  
		}
    

    //**************************************************************************
    //
    //**************************************************************************    
	public function ApplyChanges()
		{
		//Never delete this line!
		parent::ApplyChanges();

	  $ArchivID = IPS_GetInstanceListByModuleID("{43192F0B-135B-4CE7-A0A7-1475603F3060}");
    $ArchivID = $ArchivID[0];

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

		$id = $this->RegisterVariableString("name"       , "Name"      ,"",0);
		$id = $this->RegisterVariableInteger("gender"    , "Geschlecht","WITHINGS_M_Gender",2);
		$id = $this->RegisterVariableString("birthdate"  , "Geburtstag","",1);
		$id = $this->RegisterVariableInteger("height"    , "Groesse"   ,"WITHINGS_M_Groesse" ,3);
 
    $parent = IPS_GetParent($id);
      
    $CatID = false;

    // Bettsensor
    /*
        	$CatID = $this->CreateKategorie("Bettsensor",$parent);
        	if ( $CatID === false )
          		throw new Exception("Kategorie Bettsensor nicht definiert");
        	
          $VariablenID = @IPS_GetVariableIDByName("Schlafdauer",$CatID);  
        	if ($VariablenID === false)
          		{
          		$id = $this->RegisterVariableInteger("Schlafdauer", "Schlafdauer","",2);
          		IPS_SetParent($id,$CatID);
          		}
          $VariablenID = @IPS_GetVariableIDByName("Startdatum",$CatID);  
        	if ($VariablenID === false)
          		{
          		$id = $this->RegisterVariableInteger("Startdatum", "Startdatum","",2);
          		IPS_SetParent($id,$CatID);
          		}
          $VariablenID = @IPS_GetVariableIDByName("Enddatum",$CatID);  
        	if ($VariablenID === false)
          		{
          		$id = $this->RegisterVariableInteger("Enddatum", "Enddatum","",2);
          		IPS_SetParent($id,$CatID);
          		}
          $VariablenID = @IPS_GetVariableIDByName("Aktualisierungsdatum",$CatID);  
        	if ($VariablenID === false)
          		{
          		$id = $this->RegisterVariableInteger("Aktualisierungsdatum", "Aktualisierungsdatum","",2);
          		IPS_SetParent($id,$CatID);
          		}
          $VariablenID = @IPS_GetVariableIDByName("Wachphasen",$CatID);  
        	if ($VariablenID === false)
          		{
          		$id = $this->RegisterVariableInteger("Wachphasen", "Wachphasen","",2);
          		IPS_SetParent($id,$CatID);
          		}
          $VariablenID = @IPS_GetVariableIDByName("Leichtschlafphasen",$CatID);  
        	if ($VariablenID === false)
          		{
          		$id = $this->RegisterVariableInteger("Leichtschlafphasen", "Leichtschlafphasen","",2);
          		IPS_SetParent($id,$CatID);
          		}
          $VariablenID = @IPS_GetVariableIDByName("Tiefschlafphasen",$CatID);  
        	if ($VariablenID === false)
          		{
          		$id = $this->RegisterVariableInteger("Tiefschlafphasen", "Tiefschlafphasen","",2);
          		IPS_SetParent($id,$CatID);
          		}
          $VariablenID = @IPS_GetVariableIDByName("Schlafunterbrechungen",$CatID);  
        	if ($VariablenID === false)
          		{
          		$id = $this->RegisterVariableInteger("Schlafunterbrechungen", "Schlafunterbrechungen","",2);
          		IPS_SetParent($id,$CatID);
          		}
          $VariablenID = @IPS_GetVariableIDByName("Einschlafzeit",$CatID);  
        	if ($VariablenID === false)
          		{
          		$id = $this->RegisterVariableInteger("Einschlafzeit", "Einschlafzeit","",2);
          		IPS_SetParent($id,$CatID);
          		}
          $VariablenID = @IPS_GetVariableIDByName("Aufstehzeit",$CatID);  
        	if ($VariablenID === false)
          		{
          		$id = $this->RegisterVariableInteger("Aufstehzeit", "Aufstehzeit","",2);
          		IPS_SetParent($id,$CatID);
          		}

      */


     //
     
      
      	if ( $this->ReadPropertyBoolean("BloodMeasures") == true )
        	{
            
        	$CatID = $this->CreateKategorie("Blutdruck",$parent);
        	if ( $CatID === false )
          		throw new Exception("Kategorie Blutdruck nicht definiert");
      
        	$VariablenID = @IPS_GetVariableIDByName("Diastolic",$CatID);  
        	if ($VariablenID === false)
          		{
          		$id = $this->RegisterVariableInteger("diastolicblood", "Diastolic","WITHINGS_M_Blutdruck",2);
          		IPS_SetParent($id,$CatID);
          		}
        
        	$VariablenID = @IPS_GetVariableIDByName("Systolic",$CatID);  
        	if ($VariablenID === false)
          		{
          		$id = $this->RegisterVariableInteger("systolicblood", "Systolic","WITHINGS_M_Blutdruck",1);
         	 	IPS_SetParent($id,$CatID);
          		}
        
        	$VariablenID = @IPS_GetVariableIDByName("Puls",$CatID);  
        	if ($VariablenID === false)
          		{
          		$id = $this->RegisterVariableInteger("heartpulse", "Puls","WITHINGS_M_Puls",3);
          		IPS_SetParent($id,$CatID);
          		}
        
        	$VariablenID = @IPS_GetVariableIDByName("DatumUhrzeit",$CatID);  
        	if ($VariablenID === false)
          		{
          		$id = $this->RegisterVariableInteger("timestamp", "DatumUhrzeit","~UnixTimestamp",0);
          		IPS_SetParent($id,$CatID);
          		}
      		}
      
      	$logging = $this->ReadPropertyBoolean("BloodLogging");
      	
      	if ( $CatID )
        	{
        	if ( $logging )
          		$this->Logging("BloodLogging wird eingeschaltet");
        	else
          		$this->Logging("BloodLogging wird ausgeschaltet");

        	$id = IPS_GetVariableIDByName("Diastolic",$CatID);        
        	if ( AC_GetLoggingStatus($ArchivID,$id) != $logging )
          		{
          		AC_SetLoggingStatus($ArchivID,$id,$logging);
          		IPS_ApplyChanges($ArchivID);
          		}
          
        	$id = IPS_GetVariableIDByName("Systolic",$CatID);        
        	if ( AC_GetLoggingStatus($ArchivID,$id) != $logging )
          		{
          		AC_SetLoggingStatus($ArchivID,$id,$logging);
          		IPS_ApplyChanges($ArchivID);
          		}
          
        	$id = IPS_GetVariableIDByName("Puls",$CatID);
        	if ( AC_GetLoggingStatus($ArchivID,$id) != $logging )
          		{
          		AC_SetLoggingStatus($ArchivID,$id,$logging);
          		IPS_ApplyChanges($ArchivID);
          		}
          
        	$status = $this->ReadPropertyBoolean("BloodVisible");
        	$this->KategorieEnable($parent,"Blutdruck",$status);
        	}

		$CatID = false;

      	if ( $this->ReadPropertyBoolean("BodyMeasures") == true )
			{
               
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
          		$id = $this->RegisterVariableFloat("weight", "Gewicht","WITHINGS_M_Kilo",1);
          		IPS_SetParent($id,$CatID);
         	 	}
         	 	
        	$VariablenID = @IPS_GetVariableIDByName("Fettfrei Anteil",$CatID);  
        	if ($VariablenID === false)
          		{
          		$id = $this->RegisterVariableFloat("fatfree", "Fettfrei Anteil","WITHINGS_M_Kilo",3);
          		IPS_SetParent($id,$CatID);
          		}
          		
        	$VariablenID = @IPS_GetVariableIDByName("Fett Anteil",$CatID);  
        	if ($VariablenID === false)
          		{
          		$id = $this->RegisterVariableFloat("fatmassweight", "Fett Anteil","WITHINGS_M_Kilo",2);
          		IPS_SetParent($id,$CatID);
          		}
          		
        	$VariablenID = @IPS_GetVariableIDByName("Fett Prozent",$CatID);  
        	if ($VariablenID === false)
          		{
          		$id = $this->RegisterVariableFloat("fatradio", "Fett Prozent","WITHINGS_M_Prozent",4);
          		IPS_SetParent($id,$CatID);
          		}
          		
        	$VariablenID = @IPS_GetVariableIDByName("BMI",$CatID);          
        	if ($VariablenID === false)
          		{
          		$id = $this->RegisterVariableFloat("bmi", "BMI","WITHINGS_M_BMI",5);
          		IPS_SetParent($id,$CatID);
          		}
        	else
          		{
          		@IPS_SetVariableCustomProfile($VariablenID,"WITHINGS_M_BMI");
          		}
 
        	if ( $this->ReadPropertyBoolean("BodyPuls") == true )
          		{
          		$VariablenID = @IPS_GetVariableIDByName("Puls",$CatID);  
          		if ($VariablenID === false)
            		{
            		$id = $this->RegisterVariableInteger("heartpulse", "Puls","WITHINGS_M_Puls",3);
            		IPS_SetParent($id,$CatID);
            		} 

          		}
      		}

		$logging = $this->ReadPropertyBoolean("BodyLogging");

      	if ( $CatID )
        	{
        	if ( $logging )
          		$this->Logging("BodyLogging wird eingeschaltet");
        	else
          		$this->Logging("BodyLogging wird ausgeschaltet");

        	$id = IPS_GetVariableIDByName("Gewicht",$CatID);
        	if ( AC_GetLoggingStatus($ArchivID,$id) != $logging )
          		{
          		AC_SetLoggingStatus($ArchivID,$id,$logging);
          		IPS_ApplyChanges($ArchivID);
          		}
          
        	$id = IPS_GetVariableIDByName("Fettfrei Anteil",$CatID);
        	if ( AC_GetLoggingStatus($ArchivID,$id) != $logging )
          		{
          		AC_SetLoggingStatus($ArchivID,$id,$logging);
          		IPS_ApplyChanges($ArchivID);
          		}
          
        	$id = IPS_GetVariableIDByName("Fett Anteil",$CatID);
        	if ( AC_GetLoggingStatus($ArchivID,$id) != $logging )
          		{
          		AC_SetLoggingStatus($ArchivID,$id,$logging);
          		IPS_ApplyChanges($ArchivID);
          		}
          
        	$id = IPS_GetVariableIDByName("Fett Prozent",$CatID);
        	if ( AC_GetLoggingStatus($ArchivID,$id) != $logging )
          		{
          		AC_SetLoggingStatus($ArchivID,$id,$logging);
          		IPS_ApplyChanges($ArchivID);
          		}
                 
        	$id = IPS_GetVariableIDByName("BMI",$CatID);
        	if ( AC_GetLoggingStatus($ArchivID,$id) != $logging )
          		{
          		AC_SetLoggingStatus($ArchivID,$id,$logging);
          		IPS_ApplyChanges($ArchivID);
          		}
          
        	$id = @IPS_GetVariableIDByName("Puls",$CatID);
        	if ( $id > 0 )
          		{
          		if ( AC_GetLoggingStatus($ArchivID,$id) != $logging )
            		{
            		AC_SetLoggingStatus($ArchivID,$id,$logging);
            		IPS_ApplyChanges($ArchivID);
            		}
          		}
          
        	}
        
      	$status = $this->ReadPropertyBoolean("BodyVisible");
      	$this->KategorieEnable($parent,"Waage",$status);
     
	    //Timer erstellen
      	$this->SetTimerInterval("WIT_UpdateTimer", $this->ReadPropertyInteger("Intervall"));

      	//Update
     	//$this->Update();

	}

    //**************************************************************************
    //	veraltet
    //**************************************************************************    
    private function GetRedirectURL()
		{
      
      	$id = $this->GetIDForIdent("name");
      	$ModulID = IPS_GetParent($id);
      	$WebHook = $this->GetWebHook();
      	$ipsymconconnectid = IPS_GetInstanceListByModuleID("{9486D575-BE8C-4ED8-B5B5-20930E26DE6F}")[0]; 
      	$redirect_uri = CC_GetUrl($ipsymconconnectid);  

      	$redirect = $redirect_uri.$WebHook;
      
      	return $redirect;
      
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
        
      	//$this->UpdateUserData();
      	if ( $this->RefreshTokens() == FALSE )
        	return;
      
        
      	$this->GetDevice();   
        
      	$this->GetMeas();
      	       
      	//$this->GetSleepSummary();
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
  	function GetMeas()
		{
  		$access_token = $this->ReadPropertyString("Userpassword");;
	
  		$meastype = 0 ;
  		$category = 1;
  		$startdate = time()- 24*60*60;
      
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
 
  		$id = $this->GetIDForIdent("name");
  
  		$ModulID = IPS_GetParent($id);
  
  		$data = $data['body'];
  	
  		$this->DoGewicht($ModulID,$data);
  		$this->DoBlutdruck($ModulID,$data);
		}


  	//******************************************************************************
  	//	GetSleepSummary
  	//******************************************************************************
  	function GetSleepSummary()
		{
  		$access_token = $this->ReadPropertyString("Userpassword");;
	
  		$meastype = 0 ;
  		$category = 1;
  		$startdate = time()- 24*60*60;
  		$enddate = time();
  
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
    // Prozess Hook Data  ( veraltet )
    //**************************************************************************    
    protected function ProcessHookData() 
    	{
      	//Never delete this line!
		//parent::ProcessHookData();
      
      	$this->SendDebug("ProcessHookData","ProcessHookData",0);
      
      	$Data =  $_SERVER['REQUEST_METHOD'];
      	$Data =  $_SERVER['QUERY_STRING'];
      
      	$this->SendDebug('Withings4IPSymcon', $Data, 0);
      
      	$data = $_GET;
	
      	if ( isset($data['code']) AND isset($data['state'] ))
			if ( $data['state'] == "Withings4IPSymcon" )
          		$this->GetAccessToken($data['code']);
      
      	}
      
    //******************************************************************************
    //	Get Access Token
    //	Code ist 4 Stunden gueltig
    //******************************************************************************
    private function GetAccessToken($code)
    	{
      	$this->SendDebug("Get Access Token",$code,0);
      
      	$redirect = $this->GetRedirectURL();
      
      	$url = "https://account.withings.com/oauth2/token";

      	//$this->SendDebug("Get Access Token","Client ID:".$this->client_id,0);
      	//$this->SendDebug("Get Access Token","Client Secret:".$this->client_secret,0);
      	$this->SendDebug("GetAccessToken","Code:".$code,0);
      	//$this->SendDebug("Get Access Token","Redirect URL:".$redirect,0);

	
      	$data_string = "grant_type=authorization_code&client_id=".$this->client_id."&client_secret=".$this->client_secret."&code=".$code."&redirect_uri=".$redirect;

      	//$this->SendDebug("Get Access Token",$data_string,0);
	    
      	$url = "https://oauth.ipmagic.de/access_token/withings";
       
      	$curl = curl_init($url);
	          
	    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
	    curl_setopt($curl, CURLOPT_POST, true);
      	curl_setopt($curl, CURLOPT_VERBOSE, true);
     	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	   	$output = curl_exec($curl);
	
	   	$info = curl_getinfo($curl);
	   
	   	curl_close($curl);
	
     	$this->SendDebug("GetAccessToken","Output:".$output,0);
    
	   	$output = json_decode ( $output , TRUE );

     	if ( isset($output['access_token'])  AND isset($output['refresh_token']) )
      		{
      		$accesstoken =  $output['access_token'];
      		$refreshtoken =  $output['refresh_token'];
      
      		$this->SetValue("accesstoken", $accesstoken);
      		$this->SetValue("refreshtoken",$refreshtoken);
      
      		echo "Authentifizierung erfolgreich";
      		}
     
		}      
      
    //**************************************************************************
    // veraltet
    //**************************************************************************    
    private function GetWebHook()
    	{
      	$id = $this->GetIDForIdent("name");
      	$ModulID = IPS_GetParent($id);
      	$WebHook = "/hook/Withings4IPSymcon".$ModulID;
      	return $WebHook;
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
    private function KategorieEnable($Parent,$Name,$status)
    	{      
      	if ( $Parent == 0 OR $Parent == false )
        	return false;
        	
      	$id = @IPS_GetCategoryIDByName($Name,$Parent);  
      		if ( $id === false)
        		{}
      		else
        		{
        		if ( $status == true )
          			IPS_SetHidden($id, false);
        		else
          			IPS_SetHidden($id, true);
          
        		return $id;           
        		}
      	}


    //**************************************************************************
    // veraltet
    //**************************************************************************    
    protected function UpdateUserData()
    	{
      	$id = $this->GetIDForIdent("name");
      	$ModulID = IPS_GetParent($id);
              
 	  	$Username 		= $this->ReadPropertyString("Username");
      	$Userpassword   = $this->ReadPropertyString("Userpassword");
      	$User           = $this->ReadPropertyString("User");     
      
      	$this->API_AccountGetuserslist ( $Username, $Userpassword, $users );

      	if ( !$users )
	    	{
	     	$this->Logging("Fehler beim Holen der Username und Passwort ueberpruefen");
       		$this->SetStatus(202);
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
       			$this->SetStatus(202);
       			return;
	     		}

	     	$startdate 	= 0;     // Startdatum
	     	$enddate 	= 0;     // Endedatum
       		$this->SetStatus(102);
	     	// User
	     	$this->DoUser($ModulID,$data);

	     	// Groesse
	     	$limit      = 1;     
	     	$meastype   = 4;
	     	$devtype   	= 1;
	     	$this->API_MeasureGetmeas ( $personid, $publickey, $data, $startdate,$enddate,$meastype,$devtype,$limit);
	     	$this->DoGroesse($ModulID,$data);


        	if ( $this->ReadPropertyBoolean("BodyMeasures") == true )
          		{
          		$this->Logging("BodyMeasures Daten werden geholt.");

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
	
    //**************************************************************************
    //
    //**************************************************************************    
	protected function DoGroesse($ModulID,$data)
		{
		$data = @$data['measuregrps'][0]['measures'][0];
		if ( count($data) != 3 )
	   		{
	   		$this->Logging("Fehler bei DoGroesse ".count($data));
	   		return;
			}
		$Groesse = $data['value'];
  		$Unit = $data['unit'];
  		$Groesse = $Groesse * pow(10,$Unit) * 100;

		$id = @IPS_GetVariableIDByName("Groesse",$ModulID);
		if ( $id > 0 )
    		{
     		$v =  GetValueInteger($id);
     		if ( $v != $Groesse)
	     		SetValueInteger($id,$Groesse);
    		}
		}

    //**************************************************************************
    //
    //**************************************************************************    
	 protected function DoSleepSummary($ModulID,$data)
		{
    $this->SendDebug("DoSleepSummary","Schlaf wird ausgewertet.",0);

		$data = @$data['series'];
		if ( count($data) != 2 )
	     {
	   		$this->Logging("Fehler bei DoSleepSummary ".count($data));
        $this->SendDebug("DoSleepSummary","Fehler bei DoSleepSummary ".count($data),0);

	   		return;
		  }
		
    foreach($data as $sleep)
      {
       $sleepmodel      = @$sleep['model'];
       $sleepstartdate  = @$sleep['startdate'];
       $sleependdate    = @$sleep['enddate'];
       $sleepmodified   = @$sleep['modified'];
       $sleepdauer      = $sleependdate -$sleepstartdate;
      
      $this->SendDebug("DoSleepSummary","Modell : ".$sleepmodel,0);
      $this->SendDebug("DoSleepSummary","Startdatum : ".date("d-m-Y H:i:s",$sleepstartdate),0);
      $this->SendDebug("DoSleepSummary","Enddatum : "  .date("d-m-Y H:i:s",$sleependdate),0);
      $this->SendDebug("DoSleepSummary","Modifieddatum : "  .date("d-m-Y H:i:s",$sleepmodified),0);
      $this->SendDebug("DoSleepSummary","Schlafdauer : "  . $this->FormatTimeMinuten($sleepdauer),0);
      
      
      $sleepwakeupduration    = @$sleep['data']['wakeupduration'];
      $sleeplightsleepduration= @$sleep['data']['lightsleepduration'];
      $sleepdeepsleepduration = @$sleep['data']['deepsleepduration'];
      $sleepwakeupcount       = @$sleep['data']['wakeupcount'];
      $sleepdurationtosleep   = @$sleep['data']['durationtosleep'];
      $sleepdurationtowakeup  = @$sleep['data']['durationtowakeup'];

      $this->SendDebug("DoSleepSummary","Wachphasen : "           .$this->FormatTimeMinuten($sleepwakeupduration). " Minuten",0);
      $this->SendDebug("DoSleepSummary","Leichtschlafphasen : "   .$this->FormatTimeMinuten($sleeplightsleepduration)." Minuten",0);
      $this->SendDebug("DoSleepSummary","Tiefschlafphasen : "     .$this->FormatTimeMinuten($sleepdeepsleepduration) . " Minuten",0);
      $this->SendDebug("DoSleepSummary","Schlafunterbrechungen : ".$sleepwakeupcount,0);
      $this->SendDebug("DoSleepSummary","Einschlafzeit : "        .$this->FormatTimeMinuten($sleepdurationtosleep) . " Minuten",0);
      $this->SendDebug("DoSleepSummary","Aufstehzeit : "          .$this->FormatTimeMinuten($sleepdurationtowakeup) . " Minuten",0);
      
      }

		$CatID = @IPS_GetCategoryIDByName("Bettsensor",$ModulID);
	
		if ( $CatID === false )
    		{
     		$this->Logging("CatID ist auf FALSE!");
        $this->SendDebug("DoSleepSummary","CatID FALSE ! ",0);

	   		return;
    		}

 		$id = @IPS_GetVariableIDByName("Schlafdauer",$CatID);
		if ( $id > 0 )
	   		SetValueInteger($id,$sleepdauer/60);
 		$id = @IPS_GetVariableIDByName("Startdatum",$CatID);
		if ( $id > 0 )
	   		SetValueInteger($id,$sleepstartdate);
 		$id = @IPS_GetVariableIDByName("Enddatum",$CatID);
		if ( $id > 0 )
	   		SetValueInteger($id,$sleependdate);
 		$id = @IPS_GetVariableIDByName("Aktualisierungsdatum",$CatID);
		if ( $id > 0 )
	   		SetValueInteger($id,$sleepmodified);
 		$id = @IPS_GetVariableIDByName("Wachphasen",$CatID);
		if ( $id > 0 )
	   		SetValueInteger($id,$sleepwakeupduration);
 		$id = @IPS_GetVariableIDByName("Leichtschlafphasen",$CatID);
		if ( $id > 0 )
	   		SetValueInteger($id,$sleeplightsleepduration);
 		$id = @IPS_GetVariableIDByName("Tiefschlafphasen",$CatID);
		if ( $id > 0 )
	   		SetValueInteger($id,$sleepdeepsleepduration);
 		$id = @IPS_GetVariableIDByName("Schlafunterbrechungen",$CatID);
		if ( $id > 0 )
	   		SetValueInteger($id,$sleepwakeupcount);
 		$id = @IPS_GetVariableIDByName("Einschlafzeit",$CatID);
		if ( $id > 0 )
	   		SetValueInteger($id,$sleepdurationtosleep);
 		$id = @IPS_GetVariableIDByName("Aufstehzeit",$CatID);
		if ( $id > 0 )
	   		SetValueInteger($id,$sleepdurationtowakeup);

      
    }

    //**************************************************************************
    //
    //**************************************************************************    
	 protected function DoDevice($ModulID,$data)
		{
    $this->SendDebug("DoDevice","Devices werden ausgewertet.",0);

		$data = @$data['devices'];
		if ( count($data) != 3 )
	     {
	   		$this->Logging("Fehler bei DoDevice ".count($data));
        $this->SendDebug("DoDevice","Fehler bei DoDevice ".count($data),0);

	   		//return;
		  }
		
    foreach($data as $device)
      {
        
        $devicetyp      = @$device['type'];
        $devicemodel    = @$device['model'];
        $devicebattery  = @$device['battery'];
        $deviceid       = @$device['deviceid'];
        $devicetimezone = @$device['timezone'];
        
        $this->SendDebug("DoDevice : ","Type     : ".$devicetyp,0);
        $this->SendDebug("DoDevice : ","Modell   : ".$devicemodel,0);
        $this->SendDebug("DoDevice : ","Batterie : ".$devicebattery,0);
        $this->SendDebug("DoDevice : ","Deviceid : ".$deviceid,0);
        $this->SendDebug("DoDevice : ","Zeitzone : ".$devicetimezone,0);
      
        if ( $devicebattery == 'low' OR $devicebattery == 'medium' OR $devicebattery == 'high')
          {
          
          $name = "Batterie " . $devicemodel;
          
          $this->SendDebug("DoDevice",$name."-".$devicebattery,0);
          $id = $this->RegisterVariableInteger($deviceid   , $name ,"WITHINGS_M_Batterie",2);      
          
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
      
      
    return;
    $Groesse = $data['value'];
  	$Unit = $data['unit'];
  	$Groesse = $Groesse * pow(10,$Unit) * 100;

		$id = @IPS_GetVariableIDByName("Groesse",$ModulID);
		if ( $id > 0 )
    		{
     		$v =  GetValueInteger($id);
     		if ( $v != $Groesse)
	     		SetValueInteger($id,$Groesse);
    		}
		}
	
    //**************************************************************************
    //
    //**************************************************************************    
	protected  function DoGewicht($ModulID,$data)
		{
		$gewichtdatum 	= false;
		$gewicht       = 0;
		$fettfrei      = 0;
		$fettanteil    = 0;
		$fettprozent   = 0;
		$bmi           = 0;
		$groesse       = 0;
		$puls          = 0;
  
  	$this->Logging("Gewichtsdaten werden ausgewertet.");
    $this->SendDebug("DoGewicht","Gewichtsdaten werden ausgewertet.",0);
    
		$id = @IPS_GetVariableIDByName("Groesse",$ModulID);
		if ( $id > 0 )
	   		$groesse = GetValueInteger($id);

		$CatID = @IPS_GetCategoryIDByName("Waage",$ModulID);
	
		if ( $CatID === false )
    		{
     		$this->Logging("CatID ist auf FALSE!");
	   		return;
    		}
    
		$data = $data['measuregrps'];
	
		$time = @$data[0]['date'];

    $this->Logging("Zeitstempel der Daten : ".date('d.m.Y H:i:s',$time));
    $this->SendDebug("DoGewicht","Zeitstempel der Daten : ".date('d.m.Y H:i:s',$time),0);
 
  	$this->Logging("Anzahl der Messgruppen : ".count($data));
    $this->SendDebug("DoGewicht","Anzahl der Messgruppen : ".count($data),0);
 
    if ( count($data) == 0 )
      {
      $this->SendDebug("DoGewicht","Keine Messgruppen gefunden. Abbruch",0);
      return false;
      
      }
 
		foreach($data as $d)
	   {
	   	$daten = $d['measures'];
    	$this->Logging("Anzahl der Messungen : ".count($daten));
	   
			 $id = @IPS_GetVariableIDByName("DatumUhrzeit",$CatID);
		
			if ( $id > 0 )
	   			{
	   			$old = GetValueInteger($id);
      			$this->Logging("Alte Daten : ".date('d.m.Y H:i:s',$old));
      			$this->Logging("Neue Daten : ".date('d.m.Y H:i:s',$time));
	   			if ( $old == $time )
        			{
	       			$this->Logging("Keine neuen Daten : ".date('d.m.Y H:i:s',$old));
              $this->SendDebug("DoGewicht","Keine neuen Daten : ".date('d.m.Y H:i:s',$old),0);

        			return false;
        
        			}
          else
	   			 SetValueInteger($id,$time);
				}

			foreach($daten as $messung)
	   	 {
 	   	
			 $val = floatval ( $messung['value'] ) * floatval ( "1e".$messung['unit'] );

      $this->Logging("Messung Type : ".$messung['type']." : " .$val);
      $this->SendDebug("DoGewicht","Messung Type : ".$messung['type']." : " .$val,0);
       

				if ( $messung['type'] == 1 AND $gewicht == 0)
					{
					$gewicht = round ($val,2);
					}
				if ( $messung['type'] == 5 AND $fettfrei == 0)
					{
					$fettfrei = round ($val,2);
					}
				if ( $messung['type'] == 6 AND $fettprozent == 0)
					{
					$fettprozent = round ($val,2);
					}
				if ( $messung['type'] == 8 AND $fettanteil == 0)
					{
					$fettanteil = round ($val,2);
					}
				if ( $messung['type'] == 11 AND $puls == 0)
					{
					$puls = round ($val,2);
					}
	   			}
			}

    if ( $groesse !=  0)
		  $bmi = @round($gewicht/(($groesse/100)*($groesse/100)),2);
    else
      $bmi = 0;
      
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
	
		$id = @IPS_GetVariableIDByName("Puls",$CatID);
		if ( $id > 0 )
	   		SetValueInteger($id,$puls);

		}
	
    //**************************************************************************
    //
    //**************************************************************************    
	protected  function DoBlutdruck($ModulID,$data)
		{
		$diastolic     = 0;
		$systolic      = 0;
		$pulse         = 0;
  
		$CatID = @IPS_GetCategoryIDByName("Blutdruck",$ModulID);

		if ( $CatID === false )
	   		return;
   	
		$time = @$data['measuregrps'][0]['date'];
	
		$data = @$data['measuregrps'][0]['measures'];

    if ( @count($data) == 0 )
      {
      $this->SendDebug("DoBlutdruck","Keine Messgruppen gefunden. Abbruch",0);
      return false;
      
      }


		if ( @count($data) != 3 ) 
	   		{
	   		$this->Logging("Fehler bei DoBlutdruck ".count($data));
     		$this->SendDebug("DoBlutdruck","Fehler bei DoBlutdruck.Anzahl ungleich 3 (".count($data).")",0);
	   		//return;
			}



		$id = @IPS_GetVariableIDByName("DatumUhrzeit",$CatID);
		if ( $id > 0 )
	   		{
	   		$old = GetValueInteger($id);

      		$this->Logging("Alte Daten : ".date('d.m.Y H:i:s',$old));
      		$this->Logging("Neue Daten : ".date('d.m.Y H:i:s',$time));

	   		if ( $old == $time )    // keine neue Daten
        		{
        		$this->Logging("Keine neuen Daten : ".date('d.m.Y H:i:s',$old));
            $this->SendDebug("DoBlutdruck","Keine neuen Daten : ".date('d.m.Y H:i:s',$old),0);
	      		return false;
        		}
        else
	   		  SetValueInteger($id,$time);
			}

		foreach($data as $messung)
	   		{
			$val = $messung['value'];
    	$val = floatval ( $messung['value'] ) * floatval ( "1e".$messung['unit'] ); 
      $this->SendDebug("DoBlutdruck","Messung Type : ".$messung['type']." : " .$val,0);

			 
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
	
    //**************************************************************************
    //
    //**************************************************************************    
	protected function	API_MeasureGetmeas ( $userid, $publickey , &$measuregrps, $startdate=0, $enddate=0, $meastype = false ,$devtype=false, $limit=false )
		{

		$string="measure?action=getmeas&userid=".$userid."&publickey=".$publickey;
		$string="measure?action=getmeas&userid=".$userid."&publickey=".$publickey;

		if ( $meastype )
  			$string.="&meastype=".$meastype;

		if ( $devtype )
  			$string.="&devtype=".$devtype;

		if ( $limit )
			$string.="&limit=".$limit;

		if ( $this->CurlCall ( $string,$result)===false)
			return ( false );

		$measuregrps = $result['body'];

		return (true);
		}

    //**************************************************************************
    //
    //**************************************************************************    
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

    //**************************************************************************
    //
    //**************************************************************************    
	protected  function CurlCall ( $service , &$result=null )
		{
	
		$APIURL = 'https://wbsapi.withings.net/v2/';
  		//$APIURL = 'https://api.health.nokia.com/';
		$s = curl_init();
		curl_setopt($s,CURLOPT_URL,$APIURL.$service);
   		curl_setopt($s,CURLOPT_POST,false);
   		curl_setopt($s, CURLOPT_RETURNTRANSFER, 1);
		$this->Logging($APIURL.$service);
		$output = curl_exec($s);
   		curl_close($s);
   
  		$this->Logging($output);

		$result = json_decode ( $output , TRUE );

		if (!is_array($result))
			return (false);
		if (!key_exists('status',$result))
			return (false);
		if ($result['status']!=0)
			return (false);

		return ( true );

		}

    //**************************************************************************
    // Register Hook ( veraltet )
    //**************************************************************************    
    protected function RegisterHook($WebHook)
    	{
      	$this->SendDebug('Register Hook', $WebHook, 0);
    
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
                        return;
                    	}
                    $hooks[$index]['TargetID'] = $this->InstanceID;
                    $found = true;
                	}
            	}
            if (!$found) 
            	{
                $hooks[] = ['Hook' => $WebHook, 'TargetID' => $this->InstanceID];
            	}
            $this->SendDebug('hook', $WebHook, 0);
            IPS_SetProperty($ids[0], 'Hooks', json_encode($hooks));
            IPS_ApplyChanges($ids[0]);
        	}
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
			//Just print raw post data!
			echo file_get_contents("php://input");	
			}
      
		}

  	//****************************************************************************
	//* Token holen
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
			die("Bearer Token expected");
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
				
			
		$Token = $data->access_token;
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

		
	}

?>
