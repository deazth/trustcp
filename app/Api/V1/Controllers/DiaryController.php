<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;
use App\Models\GwdActivity;
use App\Models\DailyPerformance;
use App\common\DiaryHelper;
use App\common\CommonHelper;
use App\common\TribeApiCallHandler;
use App\common\UserHelper;
use \Carbon\Carbon;


class DiaryController extends Controller
{

	public function GetMonCalendar(Request $req){

		$tdate = date('Y-m-d');
		if($req->filled('indate')){
			$tdate = $req->indate;
		}

		$today = new Carbon($tdate);
		$today->hour = 0;
		$today->minute = 0;
		$today->second = 0;
		$today->day = 1;

		$nextmon = new Carbon($today);
		$nextmon->addMonth()->subDay();

		$dps = DailyPerformance::where('user_id', $req->user()->id)
			->whereBetween('record_date', [$today->toDateString(), $nextmon->toDateString()])
			->get();

			$resp = null;
		// format it accordingly
		foreach ($dps as $key => $dp) {
			$dotcolor = 'grey';
			$textcolor = 'white';

			if($dp->expected_hours > 0){
				if($dp->performance == 0){
					$dotcolor = 'red';
					$textcolor = 'white';
				} elseif ($dp->performance < 80) {
					$dotcolor = 'yellow';
					$textcolor = 'black';
				} elseif ($dp->performance <= 100) {
					$dotcolor = 'blue';
					$textcolor = 'white';
				} else {
					$dotcolor = 'green';
					$textcolor = 'white';
				}
			}

			$resp[$dp->record_date] = [
				// 'marked' => true,
				'startingDay' => true,
				'endingDay' => true,
				'color' => $dotcolor,
				'textColor' => $textcolor
			];
		}


		return $this->respond_json(200, 'Success', $resp);
	}


	public function GetGwdEntries(Request $req){
		$tdate = date('Y-m-d');
		if($req->filled('indate')){
			$tdate = $req->indate;
		}

		$gwds = GwdActivity::where('user_id', $req->user()->id)
			->whereDate('activity_date', $tdate)->get();

		$entries = [];
		$totalhrs = 0;
		foreach ($gwds as $key => $gwd) {
			$totalhrs+= $gwd->hours_spent;

			$entries[] = [
				'id' => $gwd->id,
				'title' => $gwd->parent_number,
				'tag_desc' => $gwd->ActivityTag->descr,
				'type_desc' => $gwd->ActivityType->descr,
				'hours_spent' => $gwd->hours_spent
			];
		}

		return $this->respond_json(200, 'Success', [
			'total' => $totalhrs,
			'entries' => $entries
		]);


	}

	public function GwdDetail(Request $req){
		if($req->filled('gwd_id')){

			$gwd = GwdActivity::find($req->gwd_id);
			if($gwd){
				$val = [
					'id' => $gwd->id,
					'title' => $gwd->parent_number,
					'details' => $gwd->details,
					'tag_id' => $gwd->task_category_id,
					'type_id' => $gwd->activity_type_id,
					'subtype_id' => $gwd->act_sub_type_id,
					'tribe_id' => $gwd->tribe_id,
					'hours_spent' => $gwd->hours_spent
				];

				return $this->respond_json(200, 'Success', $val);
			} else {
				return $this->respond_json(404, 'Record not found');
			}
		} else {
			return $this->respond_json(412, 'Invalid input', $req->all());
		}
	}

	/*
	indate: seldate,
  tag_id: tagID,
  type_id: typeID,
  subtype_id: subtypeID,
  title: gwdTitle,
  detail: gwdDetail,
  tribe_id: tribeID,
  hours: hrSpent
	*/
	public function AddGwd(Request $req){
		$df = DiaryHelper::GetDailyPerfObj($req->user()->id, $req->indate);
		if($df->actual_hours + $req->hours > 24){
			return $this->respond_json(201, 'Total exceed 24 hours');
		}

		if(trim($req->title) == ""){
			return $this->respond_json(202, 'ID/Title is empty');
		}

		if(trim($req->detail) == ""){
			return $this->respond_json(203, 'Detail is empty');
		}

		try {
			$gwd = GwdActivity::create([
				'daily_performance_id' => $df->id,
				'hours_spent' => $req->hours,
				'activity_date' => $req->indate,
				'task_category_id' => $req->tag_id,
				'activity_type_id' => $req->type_id,
				'act_sub_type_id' => $req->subtype_id,
				'parent_number' => $req->title,
				'details' => $req->detail,
				'tribe_id' => $req->tribe_id,
				'user_id' => $req->user()->id,
				'unit_id' => $req->user()->unit_id,
				'title' => 'mobile'
			]);
			$df->actual_hours += $req->hours;
			$df->save();
			return $this->respond_json(200, 'Success');
		} catch (\Exception $e) {
			return $this->respond_json(500, 'Error while adding new entry');
		}
	}

	/*
	gwd_id: gwdid,
  tag_id: tagID,
  type_id: typeID,
  subtype_id: subtypeID,
  title: gwdTitle,
  detail: gwdDetail,
  tribe_id: tribeID,
  hours: hrSpent
	*/
	public function EditGwd(Request $req){

		$df = DiaryHelper::GetDailyPerfObj($req->user()->id, $req->indate);

		if(trim($req->title) == ""){
			return $this->respond_json(202, 'ID/Title is empty');
		}

		if(trim($req->detail) == ""){
			return $this->respond_json(203, 'Detail is empty');
		}

		$gwd = GwdActivity::find($req->gwd_id);
		if($gwd){
			if($gwd->user_id != $req->user()->id){
				return $this->respond_json(403, 'Record belongs to someone else');
			}
		} else {
			return $this->respond_json(404, 'Record not found');
		}

		$diff = $gwd->hours_spent - $req->hours;
		$newtotal = $df->actual_hours - $diff;
		if($newtotal > 24){
			return $this->respond_json(201, 'Total exceed 24 hours');
		}

		try {

			// $gwd->hours_spent = $req->hours;
			// $gwd->task_category_id = $req->tag_id;
			// $gwd->activity_type_id = $req->type_id;
			// $gwd->act_sub_type_id = $req->subtype_id;
			// $gwd->parent_number = $req->title;
			// $gwd->details = $req->detail;
			// $gwd->tribe_id = $req->tribe_id;
			// $gwd->save();

			$gwd = GwdActivity::where('id', $req->gwd_id)
				->where('user_id', $req->user()->id)
				->update([
					'hours_spent' => $req->hours,
					'task_category_id' => $req->tag_id,
					'activity_type_id' => $req->type_id,
					'act_sub_type_id' => $req->subtype_id == 'null' ? null : $req->subtype_id,
					'parent_number' => $req->title,
					'details' => $req->detail,
					'tribe_id' => $req->tribe_id == 'null' ? null : $req->tribe_id
			]);
			$df->actual_hours -= $diff;
			$df->save();
			return $this->respond_json(200, 'Success');
		} catch (\Exception $e) {
			return $this->respond_json(500, 'Error while updating record: ' . $e->getMessage());
		}
	}

	public function DelGwd(Request $req){

		$input = app('request')->all();

		$rules = [
			'gwd_id' => ['required']
		];

		$validator = app('validator')->make($input, $rules);
		if($validator->fails()){
			return $this->respond_json(412, 'Invalid input', $input);
		}

		$obj = GwdActivity::find($req->gwd_id);
		if($obj){
			// check if this user is allowed to edit this gwd
			if ($req->user()->id != $obj->user_id) {
				return $this->respond_json(403, 'Not allowed');
			}

			// deduct the hour from df
			$df = DiaryHelper::GetDailyPerfObj($obj->user_id, $obj->activity_date);
			$df->actual_hours -= $obj->hours_spent;
			$df->save();
			// delete the gwd transaction
			$obj->delete();
			return $this->respond_json(200, 'Success');
		} else {
			return $this->respond_json(404, 'Record no longer exist');
		}
	}

	public function GetActTag(Request $req){
		$tags = UserHelper::GetUserTaskCat(backpack_user()->id);
		$rets = [];
		foreach ($tags as $key => $value) {

			$rets[] = ['id' => $value->id,
			'descr' => $value->descr];
		}

		return $this->respond_json(200, 'Success', $rets);
	}

	public function GetActType(Request $req){
		if($req->filled('tag_id')){
			$rets = [];
			$subcek = DiaryHelper::GetActType($req->tag_id);
			if($subcek !== false){
				$types = $subcek->get();
				foreach ($types as $key => $value) {
					$rets[] = ['id' => $value->id,
					'descr' => $value->descr];
				}
			}

			return $this->respond_json(200, 'Success', $rets);
		}

		return $this->respond_json(412, 'invalid input', $req->all());
	}

	public function GetActSubType(Request $req){
		if($req->filled('type_id')){
			$rets = [];
			$subcek = DiaryHelper::GetGrpActSubType($req->user()->id, $req->type_id);
			if($subcek !== false){
				$stypes = $subcek->get();
				foreach ($stypes as $key => $value) {
					$rets[] = ['id' => $value->id,
					'descr' => $value->descr];
				}
			}

			return $this->respond_json(200, 'Success', $rets);
		}

		return $this->respond_json(412, 'invalid input', $req->all());
	}

	public function GetTribeList(Request $req){
		$cctribe = CommonHelper::GetCConfig('tribe', 'true') == 'true';

		if($cctribe){
			try {
				$ass = TribeApiCallHandler::getTribeAssigment($req->user());
				return $this->respond_json(200, 'Success', $ass["assigments"]);
			} catch (\Exception $e) {
			}
		}

		return $this->respond_json(200, 'Success', []);
	}



}
