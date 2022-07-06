<?php

namespace App\Http\Controllers\Admin\Charts;

use ConsoleTVs\Charts\Classes\Echarts\Chart;
// use ConsoleTVs\Charts\Classes\ChartJS\Chart;
use App\common\UserHelper;
use App\Models\DailyUserStat;
use \Carbon\Carbon;

/**
 * Class UserStatsDurLobChartController
 * @package App\Http\Controllers\Admin\Charts
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class UserStatsDurLobChartController extends BaseChartControllerClass
{
    public function setup()
    {
        $this->chart = new Chart();
        $smon = new Carbon($this->p1);
        $emon = new Carbon($this->p2);
        $this->chart->theme = 'dark';
        $lable = DailyUserStat::whereBetween('record_date', [$smon->toDateString(), $emon->toDateString()])
          ->distinct()->select('lob_descr')->pluck('lob_descr')->toArray();

        $this->chart->labels($lable);
        $this->chart->height(140 + sizeof($lable) * 100);
        $this->chart->options(['responsive' => true,
          'xAxis' => ['type' => 'value'],
          'yAxis' => [
            'type' => 'category',
            'data' => $lable,
            // 'axisLabel' => ['interval' => 0, 'rotate' => sizeof($lable) > 8 ? 30 : 0],
            // 'splitline' => ['show' => false]
          ],
          'tooltip' => [
            'trigger' => 'axis',
            'axisPointer' => ['type' => 'shadow']
          ],
          'grid' => ['containLabel' => true]
        ]);
        // RECOMMENDED. Set URL that the ChartJS library should call, to get its data using AJAX.
        $this->chart->load(backpack_url('charts/user-stats-dur-lob/' . $this->p1 . '/' . $this->p2));

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
      $uusr = []; // user with no activity
      $tusr = []; // total expected user

      $lable = DailyUserStat::whereBetween('record_date', [$smon->toDateString(), $emon->toDateString()])
        ->distinct()->select('lob_descr')->pluck('lob_descr')->toArray();

      foreach ($lable as $key => $value) {
        $rec = UserHelper::GetDurLobUserStat($smon->toDateString(), $emon->toDateString(), $value);
        $locc[] = $rec['location_count'];
        $qrsc[] = $rec['workspace_count'];
        $gwdc[] = $rec['diary_count'];
        $uusr[] = $rec['user_count'] - $rec['unique_count'];
        $tusr[] = $rec['user_count'];
      }



      $this->chart->dataset('Diary Entries', 'bar', $gwdc)
          ->color('rgba(205, 32, 31, 1)')->options([
            'label' => [
              'show' => true
            ],
            'emphasis' => ['focus' => 'series']
          ]);
      $this->chart->dataset('Location Checkin', 'bar', $locc)
          ->color('rgba(32, 32, 205, 1)')->options([
            'label' => [
              'show' => true
            ]
          ]);
      $this->chart->dataset('Workspace Checkin', 'bar', $qrsc)
          ->color('rgba(32, 105, 31, 1)')->options([
            'label' => [
              'show' => true
            ]
          ]);
      $this->chart->dataset('No Activity', 'bar', $uusr)
          ->color('rgba(99, 99, 99, 1)')->options([
            'label' => [
              'show' => true
            ]
          ]);

      $this->chart->dataset('Total Staff', 'bar', $tusr)
          ->color('rgba(211, 105, 31, 1)')->options([
            'label' => [
              'show' => true
            ],
            'emphasis' => ['focus' => 'series']
          ]);

    }
}
