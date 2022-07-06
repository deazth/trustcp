<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

use \Carbon\Carbon;
use App\Models\BatchDiaryReport;
use App\common\ExcelHandler;
use App\common\GDWActions;
use App\Models\DailyPerformance;
// use App\Mail\SendDiaryRpt;
use App\Models\CompGroup;

class BatchDiaryRptProcessor implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $bdr_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id)
    {
      $this->bdr_id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      // Log::info('started');
      set_time_limit(0);

      $bdr = BatchDiaryReport::find($this->bdr_id);
      if($bdr){
        $bdr->status = 'Processing';
        $bdr->processed_at = now();
        $bdr->save();

        // check class type
        if($bdr->class_name != 'CompGroup'){
          $bdr->status = 'Failed';
          $bdr->extra_info = 'Invalid class name: ' . $bdr->class_name;
          $bdr->completed_at = now();
          $bdr->save();
          return;
        }

        // get the comp group
        $cgrp = CompGroup::find($bdr->obj_id);
        if($cgrp){
          // Log::info('CompGroup found: ' . $cgrp->Units->count());
        } else {
          $bdr->status = 'Failed';
          $bdr->extra_info = 'Group not found: ' . $bdr->obj_id;
          $bdr->completed_at = now();
          $bdr->save();
          return;
        }

        try {
          $cdate = new Carbon($bdr->to_date);
          $ldate = new Carbon($bdr->from_date);

          $noww = Carbon::now();
          // Log::info('build filename');
          $fname = 'diary_'
            . $ldate->format('Ymd') . '_' . $cdate->format('Ymd')
            . '_' . str_replace(' ', '', $cgrp->name)
            . '_' . $noww->format('Ymd_His') . '.xlsx';

          $cdate->addSecond();

          // Log::info('prep header');
          $headers = ['Name', 'Staff No', 'Band', 'Division', 'Section', 'Email'];

          $hrsdata = [];
          $mandaydata = [];
          $dayinfo = [];

          // Log::info('prep daterange');
          $daterange = new \DatePeriod(
            $ldate,
            \DateInterval::createFromDateString('1 day'),
            $cdate
          );

          foreach ($daterange as $key => $value) {
            // Log::info('process date ' . $value->format('Y-m-d'));
            array_push($headers, $value->format('d (D)'));

            $dtype = GDWActions::GetDayType($value->format('Y-m-d'));
            $expthrs = GDWActions::GetExpectedHours($value->format('Y-m-d'));
            switch($dtype){
              case GDWActions::DT_NW:
                $bgcolor = ExcelHandler::BG_NORMAL;
                break;
              case GDWActions::DT_PH:
                $bgcolor = ExcelHandler::BG_PH;
                break;
              case GDWActions::DT_WK:
                $bgcolor = ExcelHandler::BG_WEEKEND;
                break;
              default:
                $bgcolor = ExcelHandler::BG_NORMAL;
            }

            array_push($dayinfo, [
              'date' => $value->format('Y-m-d'),
              'type' => $dtype,
              'bgcolor' => $bgcolor,
              'exhrs' => $expthrs
            ]);
          }

          array_push($headers, 'Actual');
          array_push($headers, 'Expected');

          // prep the excel handler
          $eksel = new ExcelHandler($fname);

          // get the list of divs under this group
          foreach ($cgrp->Units as $onemember) {
            foreach ($onemember->Staffs as $value) {
              $expectedentry = 0;
              $expectedmd = 0;
              $expectedhrs = 0;
              $sumentry = 0;
              $sumhrs = 0;
              $summd = 0;

              $datmd = [
                ['v' => $value->name, 't' => ExcelHandler::BG_INFO],
                ['v' => $value->staff_no, 't' => ExcelHandler::BG_INFO],
                ['v' => $value->job_grade, 't' => ExcelHandler::BG_INFO],
                ['v' => $onemember->pporgunitdesc, 't' => ExcelHandler::BG_INFO],
                ['v' => $value->subunit, 't' => ExcelHandler::BG_INFO],
                ['v' => $value->email, 't' => ExcelHandler::BG_INFO]
              ];

              $dathrs = [
                ['v' => $value->name, 't' => ExcelHandler::BG_INFO],
                ['v' => $value->staff_no, 't' => ExcelHandler::BG_INFO],
                ['v' => $value->job_grade, 't' => ExcelHandler::BG_INFO],
                ['v' => $onemember->pporgunitdesc, 't' => ExcelHandler::BG_INFO],
                ['v' => $value->subunit, 't' => ExcelHandler::BG_INFO],
                ['v' => $value->email, 't' => ExcelHandler::BG_INFO]
              ];

              $pdaycount = 0;
              $zdaycount = 0;
              foreach ($dayinfo as $odt) {
                $pdaycount++;
                $dpu = GDWActions::GetDailyPerfObj($value->id, $odt['date'], false);

                $dbg = $odt['bgcolor'];
                $hrs = 0;

                if($dpu){
                  if($dpu->zerorized == true){
                    $zdaycount++;
                  }

                  if($dpu->is_public_holiday){
                    $dbg = ExcelHandler::BG_PH;
                  } else {
                    // check for off day
                    if($dpu->is_off_day){
                      if($dpu->expected_hours < 5){
                        $dbg = ExcelHandler::BG_LEAVE0;
                      } else {
                        $dbg = ExcelHandler::BG_LEAVE;
                      }

                    } else {
                      // get day type background
                      if($dpu->remark == 'Rest Day' || $dpu->remark == 'Off Day'){
                        $dbg = ExcelHandler::BG_WEEKEND;
                      } else {
                        $dbg = ExcelHandler::BG_NORMAL;
                      }
                    }

                    if($dpu->expected_hours > 0){
                      $expectedentry++;
                      $expectedmd++;
                      $expectedhrs += $dpu->expected_hours;

                      if($dpu->actual_hours > 0){
                        $sumentry++;
                      }
                    }
                  }

                  if($dpu->actual_hours > 0){
                    $sumhrs += $dpu->actual_hours;
                    $hrs = $dpu->actual_hours;
                  }

                } else {
                  // no entry for that date. so just in case, load default values
                  if($odt['type'] == GDWActions::DT_NW){
                    $expectedentry++;
                    $expectedmd++;
                    $expectedhrs += $odt['exhrs'];
                  }

                }

                $md = $hrs / ($odt['exhrs'] == 0 ? 8 : $odt['exhrs']);
                $summd += $md;
                // populate today's data
                array_push($datmd, ['v' => $md, 't' => $dbg]);
                array_push($dathrs, ['v' => $hrs, 't' => $dbg]);
              }

              // populate the final sums for md
              array_push($datmd, ['v' => $summd, 't' => ExcelHandler::BG_INFO]);
              array_push($datmd, ['v' => $expectedmd, 't' => ExcelHandler::BG_INFO]);

              // push the MD data to main array
              array_push($mandaydata, $datmd);

              // final sums for hours
              array_push($dathrs, ['v' => $sumhrs, 't' => ExcelHandler::BG_INFO]);
              array_push($dathrs, ['v' => $expectedhrs, 't' => ExcelHandler::BG_INFO]);
              array_push($dathrs, ['v' => $sumentry, 't' => ExcelHandler::BG_INFO]);
              array_push($dathrs, ['v' => $expectedentry, 't' => ExcelHandler::BG_INFO]);

              // calc the productivity
              if($pdaycount == $zdaycount){
                // $pdtivity = 100 + ($sumhrs / (8 * $pdaycount) * 100);
                $pdtivity = 'N/A';
                $pdgrp = 'N/A';
                $pbg = ExcelHandler::PD_NA;
              } elseif($expectedhrs == 0){
                if($sumhrs > 0){
                  $pdtivity = 100 + ($sumhrs / (8 * $pdaycount) * 100);
                  $pdgrp = '> 100%';
                  $pbg = ExcelHandler::PD_GD;
                } else {
                  $pdtivity = 100;
                  $pdgrp = '80% - 100%';
                  $pbg = ExcelHandler::PD_GC;
                }
              } else {
                $pdtivity = $sumhrs / $expectedhrs * 100;
                if($pdtivity == 0){
                  $pdgrp = '0%';
                  $pbg = ExcelHandler::PD_G0;
                } elseif($pdtivity < 50){
                  $pdgrp = '1% - 49%';
                  $pbg = ExcelHandler::PD_GA;
                } elseif($pdtivity < 80){
                  $pdgrp = '50% - 79%';
                  $pbg = ExcelHandler::PD_GB;
                } elseif($pdtivity <= 100){
                  $pdgrp = '80% - 100%';
                  $pbg = ExcelHandler::PD_GC;
                } else {
                  $pdgrp = '> 100%';
                  $pbg = ExcelHandler::PD_GD;
                }
              }


              array_push($dathrs, ['v' => ($pdtivity == 'N/A' ? $pdtivity : number_format($pdtivity, 2)), 't' => $pbg]);
              array_push($dathrs, ['v' => $pdgrp, 't' => $pbg]);

              // push hours to main array
              array_push($hrsdata, $dathrs);

            }
          }

          $eksel->addSheet('By Man-Days', $mandaydata, $headers);

          array_push($headers, 'Actual entry');
          array_push($headers, 'Expected entry');
          array_push($headers, 'Productivity');
          array_push($headers, 'Range');
          $eksel->addSheet('By Hours',$hrsdata, $headers);
          $eksel->saveToPerStorage();

          $bdr->status = 'Completed';
          // $bjob->attachment = $eksel->getBinary();
          $bdr->filename = $fname;
          // $bdr->extra_info = $fname;
          $bdr->completed_at = now();
          $bdr->save();

          // email to rep
          // $recps = $cgrp->Users()->pluck('email');
          //
          // Log::info('jobid: ' . $this->bjobid . ', Recipients: ' . json_encode($recps));

          // try {
          //   foreach($recps as $oemail){
          //     \Mail::to($oemail)->send(new SendDiaryRpt($bjob));
          //   }
          // } catch(\Throwable $te){
          //   // $bjob->attachment = $eksel->getBinary();
          //   $bjob->extra_info = $te->getMessage();
          //   $bjob->save();
          // }



        } catch (\Exception $e) {
          Log::error($e);
          $bdr->status = 'Failed';
          $bdr->extra_info = $e->getMessage();
          $bdr->completed_at = now();
          $bdr->save();
        }

      } else {
        Log::error('BatchDiaryReport 404: #' . $this->bdr_id);
      }

      // Log::info('ended');
    }

    public function failed(\Throwable $exception)
    {
      Log::error('BatchDiaryReport failed: #' . $this->bdr_id);
      Log::error($exception);
    }
}
