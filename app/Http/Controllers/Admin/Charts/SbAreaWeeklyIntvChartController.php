<?php

namespace App\Http\Controllers\Admin\Charts;

use ConsoleTVs\Charts\Classes\Echarts\Chart;
use App\Models\Building;
use App\common\MeetingAreaHelper;

/**
 * Class SbAreaWeeklyIntvChartController
 * @package App\Http\Controllers\Admin\Charts
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class SbAreaWeeklyIntvChartController extends BaseChartControllerClass
{
    public function setup()
    {
        $this->chart = new Chart();

        if(isset($this->p1) && isset($this->p2)){
          $build = Building::findOrFail($this->p1);
          $chdata = MeetingAreaHelper::GetBuildWeeklyIntvData($build, $this->p2);


          // dd($chdata);
          $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thi', 'Fri', 'Sat'];
          $this->chart->height(140 + sizeof($chdata['area']) * 35);

          $this->chart->options([
              'xAxis' => [
                'type' => 'category',
                'data' => $days,
                'splitArea' => [ 'show' => true ]
              ],
              'yAxis' => [
                'type' => 'category',
                'data' => $chdata['area'],
                'splitArea' => [ 'show' => true ]
              ],
              'grid' => ['containLabel' => true],
              'tooltip' => [
                'show' => false
              ],
              'visualMap' => [
                'min' => 0,
                'max' => 32,
                'calculable' => true,
                'orient' => 'horizontal',
                'left' => 'center',
                // 'bottom' => '50px'
              ]
            ]
          );

          $this->chart->dataset('total hour used by day', 'heatmap', $chdata['data'])
            // ->color(['rgba(32, 32, 255, 1)'])
            ->options([
              'label' => ['show' => true, 'color' => 'blue'],
            ])
          ;
        }
    }

    /**
     * Respond to AJAX calls with all the chart data points.
     *
     * @return json
     */
    // public function data()
    // {
    //     $users_created_today = \App\User::whereDate('created_at', today())->count();

    //     $this->chart->dataset('Users Created', 'bar', [
    //                 $users_created_today,
    //             ])
    //         ->color('rgba(205, 32, 31, 1)')
    //         ->backgroundColor('rgba(205, 32, 31, 0.4)');
    // }
}
