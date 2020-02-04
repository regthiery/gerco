<?php

include_once "ResolutionsController.php" ;


#=============================================================================
	class GeneralMeetingController extends HashController
#=============================================================================
{

	protected $ownersController ;
	protected $lotsController ;
	
	protected $resolutionsController ;
	protected $meetingIndex ;
	protected $meeting ;
	
	public function __construct ()
		{
		$this->setPrimaryKey("index") ;
		$this->resolutionsController = new ResolutionsController ;
		$this->resolutionsController -> setGeneralMeetingController ($this) ;
		}
		
	public function setOwnersController ($ownersController)	
		{
		$this -> ownersController = $ownersController ;
		$this -> resolutionsController -> setOwnersController ($ownersController) ;
		}

	public function setLotsController ($lotsController)	
		{
		$this -> lotsController = $lotsController ;
		}
		
	public function setImputationsController ($imputationsController)	
		{
		$this->resolutionsController -> setImputationsController ($imputationsController) ;
		}
		
	public function setMeetingIndex ($index)	
		{
		$this->meetingIndex = $index ;
		$this->meeting = $this->getObjectWithKey ($this->meetingIndex) ;
		$filename = $this->meeting["resolutionsFileName"] ;
		$this->resolutionsController -> readFile ("../00-data/$filename") ;
		}
		
	public function getMeeting ()	
		{
		return $this->meeting ;
		}
	
	public function checkAttendance ()			
		{
		$meeting = $this->meeting ;
		
		$presentsData = array () ;
		$presentsByBatData = array () ;
		foreach ($meeting["presents"] as $i => $pseudo)
			{
			$owner = $this->ownersController -> getObjectWithKeyValue ("pseudo", $pseudo) ;
			if ( $owner == NULL )
				{
				print ("There are no data for the owner \"$pseudo\" \n") ;
				}
			else
				{
				$batiment = $owner["lotData"]["batiment"] ;
				$special = $owner["lotData"]["special$batiment"] ;
				$owner["lotData"]["special"] = $special ;

				$presentsData[$pseudo] = $owner ;
				$presentsByBatData[$batiment][$pseudo] = $owner ;
				}
			}


		foreach ($presentsByBatData as $batiment => $ownersOfBat )
			{
			uasort ( $ownersOfBat, function ($a,$b)
				{ return strcmp($a["pseudo"], $b["pseudo"]) ; } ) ;
			}

		ksort ($presentsByBatData) ;


		$absents = array () ;
		$absentByBat = array () ;
		
		foreach ($this->ownersController->getObjects() as $ownerKey => $ownerData)
			{
			$pseudo = $ownerData["pseudo"] ;
			if ( array_key_exists("closed", $ownerData) && $ownerData["closed"]==="yes" )
				continue ;
			if (  ! in_array ($pseudo, $meeting["presents"]) )
				{
				$absents[$pseudo] = $ownerData ;

				$batiment = $ownerData["lotData"]["batiment"] ;
				$special = $ownerData["lotData"]["special$batiment"] ;
				$ownerData["lotData"]["special"] = $special ;

				$absentsData[$pseudo] = $ownerData ;
				$absentsByBatData[$batiment][$pseudo] = $ownerData ;
				}
			}

		ksort($absentsData) ;
		foreach ($absentsByBatData as $batiment => $ownersOfBat )
			{
			ksort ($ownersOfBat) ;
			$absentsByBatData[$batiment] = $ownersOfBat ;
			}

		ksort ($absentsByBatData) ;

		$meeting["presentsData"] = $presentsData ;
		$meeting["presentsByBatData"] = $presentsByBatData ;
		$meeting["absentsData"] = $absentsData ;
		$meeting["absentsByBatData"] = $absentsByBatData ;

		$this->objects[$this->meetingIndex] = $meeting ;
		$this->meeting = $meeting ;
		}
		
	public function displayAttendance ()
		{
		$meeting = $this->meeting ;

		$i = 1 ;
		$generalSum = 0 ;
		printf("\033[1mCopropriété \033[0m (présents ou représentés)\n") ;
		foreach ($meeting["presentsData"] as $pseudo=> $ownerData)
			{
			$lastname = $ownerData["lastname"] ;
			$firstname = $ownerData["firstname"] ;
			$general = $ownerData["general"] ;
			$batiment = $ownerData["lotData"]["batiment"] ;
			printf ("%5d \t%-20s \t%-20s \t%5s %8.0f\n", $i, $lastname, $firstname, $batiment, $general) ;
			$generalSum += $general ;
			$i++ ;
			}
		printf ("\033[1mGeneral %8.0lf\033[0m\n\n", $generalSum) ;
		
		foreach ($meeting["presentsByBatData"] as $batiment => $owners )
			{
			printf("\033[1mBatiment %s \033[0m (présents ou représentés)\n", $batiment) ;
			$i = 1 ;
			$specialSum = 0 ;
			foreach ($owners as $pseudo => $ownerData)
				{
				$lastname = $ownerData["lastname"] ;
				$firstname = $ownerData["firstname"] ;
				$special = $ownerData["lotData"]["special"] ;
				printf ("%5d \t%-20s \t%-20s \t%5s %8.0f\n", $i, $lastname, $firstname, $batiment, $special) ;
				$specialSum += $special ;
				$i++ ;
				}
			printf ("\033[1mSpécial %8.0lf\033[0m\n\n", $specialSum) ;
			}

		$i = 1 ;
		$generalSum = 0 ;
		printf("\033[1mCopropriété \033[0m (absents)\n") ;
		foreach ($meeting["absentsData"] as $pseudo=> $ownerData)
			{
			$lastname = $ownerData["lastname"] ;
			$firstname = $ownerData["firstname"] ;
			$general = $ownerData["general"] ;
			$batiment = $ownerData["lotData"]["batiment"] ;
			printf ("%5d \t%-20s \t%-20s \t%5s %8.0f\n", $i, $lastname, $firstname, $batiment, $general) ;
			$generalSum += $general ;
			$i++ ;
			}
		printf ("\033[1mGeneral %8.0lf\033[0m\n\n", $generalSum) ;


		foreach ($meeting["absentsByBatData"] as $batiment => $owners )
			{
			printf("\033[1mBatiment %s \033[0m (absents)\n", $batiment) ;
			$i = 1 ;
			$specialSum = 0 ;
			foreach ($owners as $pseudo => $ownerData)
				{
				$lastname = $ownerData["lastname"] ;
				$firstname = $ownerData["firstname"] ;
				$special = $ownerData["lotData"]["special"] ;
				printf ("%5d \t%-20s \t%-20s \t%5s %8.0f\n", $i, $lastname, $firstname, $batiment, $special) ;
				$specialSum += $special ;
				$i++ ;
				}
			printf ("\033[1mSpécial\033[0m %8.0lf\n\n", $specialSum) ;
			}
			
//		print_r ($meeting["absentsByBatData"])	;
		}
		
	public function displayResolutions ()
		{
		$meeting = $this->meeting ;
		$this->resolutionsController -> displayResolutions () ;
		}	
		
		
	public function calculateVotingResults ()	
		{
		$this->resolutionsController -> calculateVotingResults () ;
		}

	public function displayVotingResults ()	
		{
		$this->resolutionsController -> displayVotingResults () ;
		}
}