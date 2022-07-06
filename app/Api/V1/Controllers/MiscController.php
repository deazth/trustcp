<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;
use App\Feedback;
use App\ActivityType;
use App\TaskCategory;
use App\Announcement;
use App\News;
use App\User;
use App\DailyPerformance;
use App\common\GDWActions;
use App\common\UserHelper;

class MiscController extends Controller
{
	function sendFeedback(Request $req){
		$input = app('request')->all();

		$rules = [
			'title' => ['required'],
			'content' => ['required'],
			'device' => ['required']
		];

		$validator = app('validator')->make($input, $rules);
		if($validator->fails()){
			return $this->respond_json(412, 'Invalid input', $input);
		}

		$fb = new Feedback;
		$fb->staff_id = $req->filled('staff_id') ? $req->staff_id : 0;
    $fb->title = $req->title;
    $fb->content = $req->content;
		$fb->agent = $req->device;
		$fb->contact = $req->filled('ctc') ? $req->ctc : '';
		$fb->status = 1;
		$fb->save();

		return $this->respond_json(200, 'Success', $fb);
	}

	function GwdAddActivity(Request $req){
		$input = app('request')->all();

		$rules = [
			'title' => ['required'],
			'staff_id' => ['required'],
			'hours' => ['required'],
			'acttype' => ['required'],
			'actcat' => ['required'],
		];

		$validator = app('validator')->make($input, $rules);
		if($validator->fails()){
			return $this->respond_json(412, 'Invalid input', $input);
		}

		$act = GDWActions::addActivity($req, $req->staff_id);

		if($act == '402'){
			return $this->respond_json(402, 'Invalid number of hours relative to current time', $input);
		}

		return $this->respond_json(200, 'Success', $act);
	}

	function GwdEditActivity(Request $req){
		$input = app('request')->all();

		$rules = [
			'id' => ['required'],
		];

		$validator = app('validator')->make($input, $rules);
		if($validator->fails()){
			return $this->respond_json(412, 'Invalid input', $input);
		}

		$act = GDWActions::editActivity($req, $req->staff_id);
		if($act == '404'){
			return $this->respond_json(404, 'gwd activity not found', $input);
		}

		if($act == '402'){
			return $this->respond_json(402, 'Invalid number of hours relative to current time', $input);
		}

		return $this->respond_json(200, 'Success', $act);
	}

	function GwdDelActivity(Request $req){
		$input = app('request')->all();

		$rules = [
			'id' => ['required'],
		];

		$validator = app('validator')->make($input, $rules);
		if($validator->fails()){
			return $this->respond_json(412, 'Invalid input', $input);
		}

		$act = GDWActions::deleteActivity($req->id);
		if($act == '404'){
			return $this->respond_json(404, 'gwd activity not found', $input);
		}
		return $this->respond_json(200, 'Deleted', []);

	}

	function GwdGetSummary(Request $req){
		$input = app('request')->all();

		$rules = [
			'staff_id' => ['required'],
			'start_date' => ['required'],
			'end_date' => ['required'],
		];

		$validator = app('validator')->make($input, $rules);
		if($validator->fails()){
			return $this->respond_json(412, 'Invalid input', $input);
		}

		$eetime = new \DateTime($req->end_date);
		$eetime->setTime(0,0,1);

		$daterange = new \DatePeriod(
      new \DateTime($req->start_date),
      \DateInterval::createFromDateString('1 day'),
      $eetime
    );
		$seed = rand(0, 12);
		$fretdata = [];
		foreach ($daterange as $ondete) {
			$redata = GDWActions::getActInfoOnDate($req->staff_id, $ondete, $seed);
			$nuredata = [];
			$totaldatday = 0;

	    // rearrange to new format for api
	    for($i = 0; $i < count($redata['label']); $i++) {
				$totaldatday += $redata['data'][$i];
	      array_push($nuredata, [
	        'key' => $redata['label'][$i],
	        'value' => $redata['data'][$i],
	        'svg' => ['fill' => $redata['bg'][$i]],
	      ]);
	    }

			array_push($fretdata, [
				'date' => $ondete->format('Y-m-d'),
				'total_hours' => $totaldatday,
				'data' => $nuredata
			]);
		}

    return $this->respond_json(200, 'Success', $fretdata);
	}

	function GwdGetActivities(Request $req){
		$input = app('request')->all();

		$rules = [
			'staff_id' => ['required'],
			'date' => ['required']
		];

		$validator = app('validator')->make($input, $rules);
		if($validator->fails()){
			return $this->respond_json(412, 'Invalid input', $input);
		}

		$redata = GDWActions::getGwdActivities($req->staff_id, $req->date);

		return $this->respond_json(200, 'Success', $redata);
	}

	function GwdGetActType(Request $req){
		if($req->filled('cat_id')){
			$redata = TaskCategory::find($req->cat_id);
		} elseif($req->filled('tag')) {
			$redata = TaskCategory::where('descr', $req->tag)->where('status', 1)->first();
		} else {
			// no input. return all
			$redata = ActivityType::where('status', 1)->get();
			foreach ($redata as $key => $value) {
				$value->value = $value->descr;
			}
			return $this->respond_json(200, 'Success', $redata);
		}
		// $redata = ActivityType::where('status', 1)->get();

		if($redata){
			$ars = [];
			foreach ($redata->acttypes as $key => $value) {
				$value->value = $value->descr;
				array_push($ars, $value);
			}
			return $this->respond_json(200, 'Success', $ars);
		} else {
			return $this->respond_json(200, 'Success', []);
		}

	}

	function GwdGetActSubType(Request $req){
		$input = app('request')->all();
		if($req->filled('type') && $req->filled('staff_id')){
      // get user's group
			$cuser = User::find($req->staff_id);
			if($cuser){

			} else {
				return $this->respond_json(404, 'User not found', $input);
			}

      $gp = $cuser->Division->Group;
      if($gp){
        $ret = [];
        $type = ActivityType::where('descr', $req->type)->where('status', 1)->first();
        if($type){
          $subs = UserHelper::GetSubTypes($type->id, $gp->id);
          foreach ($subs as $key => $value) {
            array_push($ret, [
              'id' => $value->id,
              'label' => $value->descr,
							'value' => $value->descr
            ]);
          }
					return $this->respond_json(200, 'Success', $ret);
        } else {
          // type 404
          return $this->respond_json(200, 'Type 404', []);
        }

      } else {
        // no group so definitely no subtype
        return $this->respond_json(200, 'Not in any group', []);
      }
    } else {
      return $this->respond_json(412, 'Invalid input', $input);
    }
	}

	function GwdGetActCat(Request $req){
		if($req->filled('staff_id')){
			$redata = UserHelper::GetUserTaskCat($req->staff_id);
		} else {
			$redata = UserHelper::GetUserTaskCatDefault();
		}

		// $redata = TaskCategory::where('status', 1)->get();
		foreach ($redata as $key => $value) {
			$value->value = $value->descr;
		}
		return $this->respond_json(200, 'Success', $redata);
	}

	function GetNews(){
		// announcement
		$today = date('Y-m-d');

		$anlist = Announcement::whereDate('start_date', '<=', $today)
			->whereDate('end_date', '>=', $today)
			->get(['start_date', 'content', 'url', 'url_text']);

		$newlist = News::orderBy('created_at', 'DESC')->limit(10)->get(['created_at', 'title', 'content']);

		return $this->respond_json(200, 'Success', [
			'announcement' => $anlist,
			'news' => $newlist
		]);

	}

}
