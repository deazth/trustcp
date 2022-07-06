<?php

namespace App\Http\Controllers\Admin\Charts;

use ConsoleTVs\Charts\Classes\Echarts\Chart;
use App\common\UserHelper;
use \Carbon\Carbon;

/**
 * Class DailyUserStatsChartController
 * @package App\Http\Controllers\Admin\Charts
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class DailyUserStatsChartController extends BaseChartControllerClass
{
    public function setup()
    {
        $this->chart = new Chart();
        $this->chart->theme = 'dark';
        $smon = new Carbon($this->p1);
        $emon = new Carbon($this->p2);

        $lable = [];

        while($smon->lte($emon)){
          $lable[] = $smon->format('D d');
          $smon->addDay();
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
        ]);

        // RECOMMENDED. Set URL that the ChartJS library should call, to get its data using AJAX.
        $this->chart->load(backpack_url('charts/daily-user-stats/' . $this->p1 . '/' . $this->p2));

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

      while($smon->lte($emon)){
        $rec = UserHelper::GetDailyUserStat($smon->toDateString());
        $locc[] = $rec['location_count'];
        $qrsc[] = $rec['workspace_count'];
        $gwdc[] = $rec['diary_count'];
        $uusr[] = $rec['user_count'] - $rec['unique_count'];
        $tusr[] = $rec['user_count'];

        $smon->addDay();
      }

      $this->chart->dataset('Diary Entries', 'line', $gwdc)
          ->color('rgba(205, 32, 31, 1)')->options([
            'label' => [
              'show' => true
            ]
          ]);
      $this->chart->dataset('Location Checkin', 'line', $locc)
          ->color('rgba(32, 32, 205, 1)')->options([
            'label' => [
              'show' => true
            ]
          ]);
      $this->chart->dataset('Workspace Checkin', 'line', $qrsc)
          ->color('rgba(32, 105, 31, 1)')->options([
            'label' => [
              'show' => true
            ]
          ]);
      $this->chart->dataset('No Activity', 'line', $uusr)
          ->color('rgba(99, 99, 99, 1)')->options([
            'label' => [
              'show' => true
            ]
          ]);

      $this->chart->dataset('Total Staff', 'line', $tusr)
          ->color('rgba(211, 105, 31, 1)')->options([
            'label' => [
              'show' => true
            ]
          ]);


    }
}
