<?php

namespace Gerco\Data ;

use Gerco\Data\DataObjects;

class Resolutions extends DataObjects
{
	protected $imputations ;
	protected $owners ;
	protected $generalMeetings ;
	
	public function __construct ()
		{
		    parent::__construct();
		$this->setPrimaryKey("resolution") ;
		}

	public function setImputations ($imputations)
		{
		$this->imputations = $imputations ;
		}
	public function setOwners ($owners)
		{
		$this->owners = $owners ;
		}
	public function setGeneralMeetings ($generalMeetings)
		{
		$this->generalMeetings = $generalMeetings ;
		}
		
	public function displayResolutions ()
		{
		foreach ($this->objects as $index => $resolution)
			{
			$this->displayResolution($resolution) ;
			}
		}

	public function displayResolution ($resolution)
		{
		if (array_key_exists("majority", $resolution))
			$majority = $resolution["majority"] ;
		else
			$majority = "" ;
		if (array_key_exists("key", $resolution))
			$imputation = $resolution["key"] ;
		else
			$imputation = "" ;	
		$label = $resolution["label"] ;
		$index = $resolution["resolution"] ;
		printf("%8s\t%6s\t%12s\t%s\n", $index, $majority, $imputation, $label) ;
		}


	public function calculateVotingResult ($resolutionIndex)		
		{
		$resolution = $this->getObjectWithKey($resolutionIndex) ;
		if ( $resolution == NULL )
			return ;
		
		if ( array_key_exists ("novote", $resolution))
			return ;

		if (array_key_exists ("key", $resolution))
			$imputationKey = $resolution["key"] ;
		else
			{
			print ("La clé de répartion n'est pas définie pour la résolution n°$resolutionIndex\n") ;
			return ;
			}
				
		$imputation = $this->imputations -> getObjectWithKey ($imputationKey) ;
		if ( $imputation == NULL )
			{
			print ("La clé de répartion $imputationKey n'est pas définie pour la résolution n°$resolutionIndex.\n") ;
			return ;
			}
		
		$registered    = array () ;
		$unrepresented = array () ;
		$yes           = array () ;
		$no            = array () ;
		$abs           = array () ;

		$sumRegistered    = 0 ;
		$sumUnrepresented = 0 ;
		$sumRepresented   = 0 ;
		$sumExprims       = 0 ;
		$sumYes           = 0 ;
		$sumNo            = 0 ;
		$sumAbs           = 0 ;

		$meeting = $this->generalMeetings->getMeeting() ;
		$presents = $meeting["presentsData"] ;
		$presentsKeys = array_keys ($presents) ;
		

		$inFavour    = (array_key_exists("inFavour"   , $resolution)) ? $resolution ["inFavour"]    : array () ;
		$against     = (array_key_exists("against"    , $resolution)) ? $resolution ["against"]     : array () ;
		$abstention  = (array_key_exists("abstention" , $resolution)) ? $resolution ["abstention"]  : array () ;
		

		if ( in_array("all", $inFavour)   && count($inFavour)   == 1 )	$inFavour   = $presentsKeys ;
		if ( in_array("all", $against)    && count($against)    == 1 )	$against    = $presentsKeys ;
		if ( in_array("all", $abstention) && count($abstention) == 1 )	$abstention = $presentsKeys ;


		if ( in_array("none", $inFavour   ) && count($inFavour)   == 1 )	$inFavour    = array () ;
		if ( in_array("none", $against    ) && count($against)    == 1 )	$against     = array () ;
		if ( in_array("none", $abstention ) && count($abstention) == 1 )	$abstention  = array () ;


		if ( count ($inFavour ) == 0 )
		       $inFavour = array_diff ($presentsKeys, $against, $abstention) ;
		if ( count ($against ) == 0 )
		       $against = array_diff ($presentsKeys, $inFavour, $abstention) ;
		if ( count ($abstention ) == 0 )
		       $abstention = array_diff ($presentsKeys, $inFavour, $against) ;

		foreach ( $this->owners->getObjects() as $ownerKey => $ownerData)
			{
			if ( array_key_exists("closed", $ownerData) )
				continue ;
				
			$lotData = $ownerData["lotData"] ;

			if ( array_key_exists($imputationKey,$lotData["imputations"]) /*&& $lotData["imputations"][$imputationKey] > 0*/ )
				{
				$lastname  = $ownerData ["lastname"] ;
				$firstname = $ownerData ["firstname"] ;
				$general   = $ownerData ["general"] ;
				$pseudo    = $ownerData ["pseudo"] ;
				$batiment  = $lotData ["batiment"] ;
				$value     = $lotData ["imputations"][$imputationKey] ;
				$code      = $lotData ["lot"] ;
					
				$registered[$pseudo] = array ("lastname" => $lastname,
					"firstname" => $firstname,
					"general"   => $general, 
					"batiment"  => $batiment,
					"value"     => $value,
					"code"      => $code,
					"pseudo"    => $pseudo ) ;
						
				$sumRegistered += $value ;
				
//				print_r($registered[$pseudo]) ;

				if ( array_key_exists ($pseudo, $presents) )
					{
					if ( in_array ($pseudo, $inFavour) )
						{
						$yes[$ownerKey] = $registered[$pseudo] ;
						$sumYes     += $value ;	
						$sumExprims += $value ;	
						}
					elseif ( in_array ($pseudo, $against) )	
						{
						$no[$ownerKey] = $registered[$pseudo] ;
						$sumNo      += $value ;	
						$sumExprims += $value ;	
						}
					elseif ( in_array ($pseudo, $abstention) )	
						{
						$abs[$ownerKey] = $registered[$pseudo] ;
						$sumAbs += $value ;	
						}
					$sumRepresented += $value ;
					}
				else
					{
					$unrepresented[$pseudo] = $registered[$pseudo] ;
					$sumUnrepresented += $value ;
					}	
				}
			}


		
		uasort ($registered   , function ($a,$b) {return strcmp($a["pseudo"],$b["pseudo"]);}) ;
		uasort ($unrepresented, function ($a,$b) {return strcmp($a["pseudo"],$b["pseudo"]);}) ;
		uasort ($yes          , function ($a,$b) {return strcmp($a["pseudo"],$b["pseudo"]);}) ;
		uasort ($no           , function ($a,$b) {return strcmp($a["pseudo"],$b["pseudo"]);}) ;
		uasort ($abs          , function ($a,$b) {return strcmp($a["pseudo"],$b["pseudo"]);}) ;
		

		$this->objects[$resolutionIndex]["registered"]       = $registered ;
		$this->objects[$resolutionIndex]["unrepresented"]    = $unrepresented ;
		$this->objects[$resolutionIndex]["yes"]              = $yes ;
		$this->objects[$resolutionIndex]["no"]               = $no ;
		$this->objects[$resolutionIndex]["abs"]              = $abs ;
		$this->objects[$resolutionIndex]["sumRegistered"]    = $sumRegistered ;
		$this->objects[$resolutionIndex]["sumRepresented"]   = $sumRepresented ;
		$this->objects[$resolutionIndex]["sumUnrepresented"] = $sumUnrepresented ;
		$this->objects[$resolutionIndex]["sumYes"]           = $sumYes ;
		$this->objects[$resolutionIndex]["sumNo"]            = $sumNo ;
		$this->objects[$resolutionIndex]["sumAbs"]           = $sumAbs ;
		$this->objects[$resolutionIndex]["sumExprims"]       = $sumYes + $sumNo ;
		$this->objects[$resolutionIndex]["ratioRepreRegis"]  = $sumRepresented/$sumRegistered * 100 ;
		$this->objects[$resolutionIndex]["ratioExpriRegis"]  = $sumExprims/$sumRegistered * 100 ;
		$this->objects[$resolutionIndex]["ratioAbsRegis"]    = $sumAbs/$sumRegistered * 100 ;
		$this->objects[$resolutionIndex]["ratioYesRegis"]    = $sumYes/$sumRegistered * 100 ;
		$this->objects[$resolutionIndex]["ratioNoRegis"]     = $sumNo/$sumRegistered * 100 ;
		$this->objects[$resolutionIndex]["ratioYesExprim"]   = $sumYes/$sumExprims * 100 ;
		$this->objects[$resolutionIndex]["ratioNoExprim"]    = $sumNo/$sumExprims * 100 ;
		$this->objects[$resolutionIndex]["yesCount"]         = count($yes) ;
		$this->objects[$resolutionIndex]["registeredCount"]  = count($registered) ;
		}


	public function displayVotingResult ($resolutionIndex)	
		{
		$resolution = $this->getObjectWithKey($resolutionIndex) ;
		if ( $resolution == NULL )
			return ;

		if (array_key_exists ("key", $resolution))
			$imputationKey = $resolution["key"] ;
		else
			return ;

		$imputation = $this->imputations -> getObjectWithKey ($imputationKey) ;
		if ( $imputation == NULL )
			return ;
		$imputationLabel = $imputation["label"] ;

		printf("\n\n") ;
		printf("\033[1mRésolution %s\033[0m\n", $resolutionIndex) ;
		printf("\tMajorité requise    \t : %s\n", $resolution["majority"]) ;
		printf("\tIntitulé            \t : %s\n", $resolution["label"]) ;
		printf ("\tClé de répartition \t : %s (%s)\n", $imputationLabel, $imputationKey) ;

		print ("\n\n") ;
		printf ("\033[1mVote pour:\033[0m\t\t\t\t\t\t\t%% des inscrits\t\t\t%% des exprimés\n") ;
		
		foreach ($resolution["yes"] as $pseudo => $ownerData)
			{
			printf ("\t%-20s\t%-20s\t%10d/%-7d (%5.2f%%) \t %10d/%-7d (%5.2f%%)\n", 
				$ownerData["lastname"], $ownerData["firstname"],
				$ownerData["value"],
				$resolution["sumRegistered"], 
				$ownerData["value"]/$resolution["sumRegistered"]*100, 
				$ownerData["value"],
				$resolution["sumExprims"], 
				$ownerData["value"]/$resolution["sumExprims"]*100 ) ;
			}
		printf("Total\t%-20s\t%-20s\t%10d/%-7d \033[1m(%5.2f%%)\033[0m \t %10d/%-7d \033[1m(%5.2f%%)\033[0m\n", 
			"--", "--", 
			$resolution["sumYes"], 
			$resolution["sumRegistered"], 
			$resolution["ratioYesRegis"],
			$resolution["sumYes"], 
			$resolution["sumExprims"] , 
			$resolution["ratioYesExprim"] ) ;

		printf ("\n\033[1mVote contre:\033[0m\n") ;
		foreach ($resolution["no"] as $pseudo => $ownerData)
			{
			printf ("\t%-20s\t%-20s\t%10d/%-7d (%5.2f%%) \t %10d/%-7d (%5.2f%%)\n", 
				$ownerData["lastname"], $ownerData["firstname"],
				$ownerData["value"],
				$resolution["sumRegistered"], 
				$ownerData["value"]/$resolution["sumRegistered"]*100, 
				$ownerData["value"],
				$resolution["sumExprims"], 
				$ownerData["value"]/$resolution["sumExprims"]*100 ) ;
			}
		printf("Total\t%-20s\t%-20s\t%10d/%-7d \033[1m(%5.2f%%)\033[0m \t %10d/%-7d \033[1m(%5.2f%%)\033[0m\n", 
			"--", "--", 
			$resolution["sumNo"], 
			$resolution["sumRegistered"], 
			$resolution["ratioNoRegis"],
			$resolution["sumNo"], 
			$resolution["sumExprims"] , 
			$resolution["ratioNoExprim"] ) ;

		printf ("\n\033[1mS'abstient:\033[0m\n") ;
		foreach ($resolution["abs"] as $pseudo => $ownerData)
			{
			printf ("\t%-20s\t%-20s\t%10d/%-7d (%5.2f%%) \n", 
				$ownerData["lastname"], $ownerData["firstname"],
				$ownerData["value"],
				$resolution["sumRegistered"], 
				$ownerData["value"]/$resolution["sumRegistered"]*100 ) ;

			}
		printf("Total\t%-20s\t%-20s\t%10d/%-7d \033[1m(%5.2f%%)\033[0m \n", 
			"--", "--", 
			$resolution["sumAbs"], 
			$resolution["sumRegistered"], 
			$resolution["ratioAbsRegis"] ) ;

		
		print ("\n\n") ;
		printf ("\033[1mInscrits    \033[0m\t%5.0f tantièmes\n", 
			$resolution["sumRegistered"] ) ;
		printf ("\033[1mReprésentés \033[0m\t%5.0f tantièmes \t %5.2f %% des inscrits\n", 
			$resolution["sumRepresented"], 
			$resolution["ratioRepreRegis"] ) ;
		printf ("\033[1mExprimés  \033[0m\t%5.0f tantièmes \t %5.2f %% des inscrits\n", 
			$resolution["sumExprims"], 
			$resolution["ratioExpriRegis"] ) ;
		printf ("\033[1mAbstention \033[0m\t%5.0f tantièmes \t %5.2f %% des inscrits\n", 
			$resolution["sumAbs"], 
			$resolution["ratioAbsRegis"] ) ;
		printf ("\033[1mPour      \033[0m\t%5.0f tantièmes \t %5.2f %% des inscrits\t %5.2f %% des exprimés\n", 
			$resolution["sumYes"], 
			$resolution["ratioYesRegis"] , 
			$resolution["ratioYesExprim"]) ;
		printf ("\033[1mContre     \033[0m\t%5.0f tantièmes \t %5.2f %% des inscrits\t %5.2f %% des exprimés\n", 
			$resolution["sumNo"], 
			$resolution["ratioNoRegis"] , 
			$resolution["ratioNoExprim"]) ;

		printf("\n") ;
		if ($resolution["majority"] == 26)
			{
			$ratioYesHead = $resolution["yesCount"]/$resolution["registeredCount"] ;
			if ( $resolution["ratioExpriRegis"] < 200/3 &&  $ratioYesHead < 2/3 )
				printf("\033[1mDouble majorité (article 26) : la résolution est rejetée.\033[0m\n") ;
			else
				printf("\033[1mDouble majorité (article 26) : la résolution est adoptée.\033[0m\n") ;
			printf("\t Voix pour / Inscrits           \t : %.2f %%\n", $resolution["ratioExpriRegis"] ) ;
			printf("\t Copropriétaire pour / Inscrits \t : %.2f %%\n"    , $ratioYesHead * 100 ) ;
			}
		elseif ($resolution["majority"] == 25)	
			{
			if ( $resolution["ratioYesExprim"] < 50.0 )
				printf("\033[1mMajorité absolue (article 25) : la résolution est rejetée.\033[0m\n") ;
			else
				printf("\033[1mMajorité absolue (article 25) : la résolution est adoptée.\033[0m\n") ;
			printf("\t Voix pour / Inscrits           \t : %.2f %%\n", $resolution["ratioYesRegis"] ) ;
			}
		elseif ($resolution["majority"] == 24)	
			{
			if ( $resolution["sumYes"] < $resolution["sumNo"] )
				printf("\033[1mMajorité simple (article 24) : la résolution est rejetée.\033[0m\n") ;
			else	
				printf("\033[1mMajorité simple (article 24) : la résolution est adoptée.\033[0m\n") ;
			printf("\t Voix pour   : %6d tantièmes \t %.2f %%\n", $resolution["sumYes"] , $resolution["ratioYesExprim"] ) ;
			printf("\t Voix contre : %6d tantièmes \t %.2f %%\n", $resolution["sumNo"]  , $resolution["ratioNoExprim"] ) ;
			}

//		print_r($resolution) ;
		}


	
	public function calculateVotingResults ()		
		{
		foreach ($this->objects as $index => $resolution)
			{
			if ( array_key_exists ("novote", $resolution))
				continue ;

//			if ( $index != 25 )
//				continue ;
			
			$this->calculateVotingResult ($index) ;	
			}
		}

	public function displayVotingResults ()		
		{
		foreach ($this->objects as $index => $resolution)
			{
			if ( array_key_exists ("novote", $resolution))
				continue ;

//			if ( $index != 25 )
//				continue ;
			
			$this->displayVotingResult ($index) ;	
			}
		}
				
}