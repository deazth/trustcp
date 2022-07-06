<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SapEmpProfile;
use App\Models\SapLeaveInfo;
use App\Models\BatchJob;
use App\common\BatchHelper;
use App\Jobs\CreateDailyPerformance;
use App\Jobs\DailyUserStatLoader;
use App\Jobs\DiaryReminder;
use App\Models\HappyReason;
use App\Models\HappyType;

class BatchController extends Controller
{
	public function loadEmplProfile(){
		// just in case it takes too long
		set_time_limit(0);

		BatchHelper::loadOMData();

		return "completed";

	}

	public function loadEmplLeave(){
		// just in case it takes too long
		set_time_limit(0);

		BatchHelper::loadCutiData();

		return "completed";

	}



	public function GwdCreateDayPerf(Request $req){
		if($req->filled('date')){
			$ddate = $req->date;
		} else {
			$ddate = date('Y-m-d');
		}

		$curjob = BatchJob::where('job_type', 'Daily SAP Job')
			->whereDate('from_date', $ddate)
			->whereIn('status', ['New', 'Processing'])
			->first();

		if($curjob){
			// already got the job
			return $this->respond_json(200, 'Job already exist', []);
		} else {
			CreateDailyPerformance::dispatch($ddate)->onQueue('bjobs');
		}

		return $this->respond_json(200, 'Job Scheduled', []);

	}

	public function SendDiaryReminder(Request $req){

		$ddate = date('Y-m-d');


		$curjob = BatchJob::where('job_type', 'Diary Reminder')
			->whereDate('from_date', $ddate)
			->whereIn('status', ['New', 'Processing'])
			->first();

		if($curjob){
			// already got the job
			return $this->respond_json(200, 'Job already exist', []);
		} else {
			DiaryReminder::dispatch()->onQueue('bjobs');
		}

		return $this->respond_json(200, 'Job Scheduled', []);

	}



  public function getHappyReason(){
    $newid = \App\common\IopHandler::GetHappyReasons();
		$resp = json_decode($newid);
		$counter = 0;



		if(sizeof($resp) > 0){
			// first, clear the old records
			HappyReason::truncate();

			// then add new
			/*
"id": 23,
"type": 3,
"reason": "Work Environment ",
"remark": null,
"status": 0,
"last_update_on": "2020-10-24T19:38:32.000Z"
			*/
			foreach($resp as $onedata){
				if($onedata->status == 0){
					$counter++;
					$nuhr = new HappyReason;
					$nuhr->id = $onedata->id;
					$nuhr->type_id = $onedata->type;
					$nuhr->reason = $onedata->reason;
					$nuhr->remark = $onedata->remark;
					$nuhr->save();
				}
			}
		}

		return $this->respond_json(200, 'Done', ['count' => $counter]);

  }


  public function getHappyType(){
    $newid = \App\common\IopHandler::GetHappyTypes();
		$resp = json_decode($newid);
       // dd($resp);

		$counter = 0;

		if(sizeof($resp) > 0){
			// first, clear the old records
			HappyType::truncate();

			// then add new
			/*
			*/
			foreach($resp as $onedata){

					$counter++;
					$ht = new HappyType;
					$ht->id = $onedata->id;
					$ht->type = $onedata->text;
					$ht->remark = $onedata->emoticon;
					$ht->save();

			}
		}

		return $this->respond_json(200, 'Done', ['count' => $counter]);

  }

	public function ReloadDailyUserStat(Request $req){
		if($req->filled('date')){
			DailyUserStatLoader::dispatch($req->date, true);
			return $this->respond_json(200, 'DailyUserStatLoader queued for ' . $req->date);
		}

		return $this->respond_json(200, 'No input date');
	}

	public function ReloadWeeklyUserStat(Request $req){
		if($req->filled('date')){
			DailyUserStatLoader::dispatch($req->date, true);
			return $this->respond_json(200, 'DailyUserStatLoader queued for ' . $req->date);
		}

		return $this->respond_json(200, 'No input date');
	}
}
