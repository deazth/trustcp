<?php

namespace App\Http\Controllers\Admin\Charts;

use ConsoleTVs\Charts\Classes\Echarts\Chart;
use App\Models\Attendance;
use App\Models\SeatCheckin;
use App\Models\GwdActivity;
use \Carbon\Carbon;

/**
 * Class SbdUserStatChartController
 * @package App\Http\Controllers\Admin\Charts
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class SbdUserStatChartController extends BaseChartControllerClass
{
    public function setup()
    {
        $this->chart = new Chart();
        $smon = new Carbon($this->p1);
        $emon = new Carbon($this->p2);

        $lable = [];

        while($smon->lt($emon)){
          $lable[] = $smon->format('M Y');
          $smon->addMonth();
        }

        // MANDATORY. Set the labels for the dataset points
        $this->chart->labels($lable);
        $this->chart->options([
          'yAxis' => ['type' => 'value'],
          'xAxis' => [
            'type' => 'category',
            'data' => $lable,
            'axisLabel' => ['interval' => 0, 'rotate' => sizeof($lable) > 8 ? 30 : 0],
            'splitline' => ['show' => false]
          ],
          'tooltip' => [
            'trigger' => 'axis',
            'axisPointer' => ['type' => 'shadow']
          ],
          'grid' => ['containLabel' => true]
        ]
      );

        // RECOMMENDED. Set URL that the ChartJS library should call, to get its data using AJAX.
        $this->chart->load(backpack_url('charts/sbd-user-stat/' . $this->p1 . '/' . $this->p2));

        // OPTIONAL
        // $this->chart->minimalist(false);
        // $this->chart->displayLegend(true);
    }

    /**
     * Respond to AJAX calls with all the chart data points.
     *
     * @return json
     */
    public function data()
    {
      $smon = new Carbon($this->p1);
      $emon = new Carbon($this->p2);
      $gwdc = [];
      $locc = [];
      $qrsc = [];
      $uusr = [];

      while($smon->lt($emon)){
        $sdate = $smon->copy()->startOfMonth()->toDateTimeString();
        $rec = \App\common\UserHelper::GetUserStat($sdate);
        $locc[] = $rec['location_user'];
        $qrsc[] = $rec['workspace_user'];
        $gwdc[] = $rec['diary_user'];
        $uusr[] = $rec['active_user'];


        // $locc[] = Attendance::whereBetween('created_at', [$sdate, $edate])
        //   ->distinct()->count('user_id');
        //
        // $qrsc[] = SeatCheckin::whereBetween('created_at', [$sdate, $edate])
        //   ->distinct()->count('user_id');
        //
        // $gwdc[] = GwdActivity::whereBetween('created_at', [$sdate, $edate])
        //   ->distinct()->count('user_id');
        //
        // $locr = Attendance::whereBetween('created_at', [$sdate, $edate])->select('user_id');
        //
        // $qrsr = SeatCheckin::whereBetween('created_at', [$sdate, $edate])->select('user_id');
        //
        // $uusr[] = GwdActivity::whereBetween('created_at', [$sdate, $edate])->select('user_id')
        //   ->union($locr)->union($qrsr)->count();



        $smon->addMonth();
      }

        $this->chart->dataset('Diary Entries', 'line', $gwdc)
            ->color('rgba(205, 32, 31, 1)')->options([
              'label' => [
                'show' => true
              ]
            ]);
        $this->chart->dataset('Location Checkin', 'line', $locc)
            ->color('rgba(32, 92, 205, 1)')->options([
              'label' => [
                'show' => true
              ]
            ]);
        $this->chart->dataset('Workspace Checkin', 'line', $qrsc)
            ->color('rgba(32, 205, 31, 1)')->options([
              'label' => [
                'show' => true
              ]
            ]);
        $this->chart->dataset('Unique User', 'line', $uusr)
            ->color('rgba(211, 205, 31, 1)')->options([
              'label' => [
                'show' => true
              ]
            ]);
    }
}
