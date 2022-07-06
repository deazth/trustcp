<?php

namespace App\Http\Controllers\Admin\Charts;

use ConsoleTVs\Charts\Classes\Echarts\Chart;
use App\Models\Building;
use App\common\SeatHelper;

/**
 * Class SbdBuildMonthIntvChartController
 * @package App\Http\Controllers\Admin\Charts
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class SbdBuildMonthIntvChartController extends BaseChartControllerClass
{
    public function setup()
    {
        $this->chart = new Chart();

        if(isset($this->p1) && isset($this->p2)){
          $build = Building::findOrFail($this->p1);
          $chdata = SeatHelper::GetBuildMonthIntvData($build, $this->p2);
          // dd($chdata);
          $days = [];

          $sdate = new \Carbon\Carbon($this->p2);
          $edate = new \Carbon\Carbon($this->p2);
          $edate->addMonth();

          while($sdate->lt($edate)){
            $days[] = $sdate->format('j - D');
            $sdate->addDay();
          }

          // dd($days);

          $this->chart->height(140 + sizeof($chdata['seat']) * 35);

          $this->chart->options([
              'xAxis' => [
                'type' => 'category',
                'data' => $days,
                'splitArea' => [ 'show' => true ]
              ],
              'yAxis' => [
                'type' => 'category',
                'data' => $chdata['seat'],
                'splitArea' => [ 'show' => true ]
              ],
              'grid' => ['containLabel' => true],
              'tooltip' => [
                'show' => false
              ],
              'visualMap' => [
                'min' => 0,
                'max' => 100,
                'calculable' => true,
                'orient' => 'horizontal',
                'left' => 'center',
                // 'bottom' => '50px'
              ]
            ]
          );

          $this->chart->dataset('Max % Utilization', 'heatmap', $chdata['data'])
            // ->color(['rgba(32, 32, 255, 1)'])
            ->options([
              'label' => ['show' => true, 'color' => 'blue'],
            ])
          ;
        }

        // RECOMMENDED. Set URL that the ChartJS library should call, to get its data using AJAX.
        // $this->chart->load(backpack_url('charts/sbd-build-month-intv/' . $this->p1 . '/' . $this->p2));

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
