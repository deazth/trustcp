<?php

namespace App\Http\Controllers\Admin\Charts;

use ConsoleTVs\Charts\Classes\Echarts\Chart;
use App\Models\Building;
use App\common\MeetingAreaHelper;

/**
 * Class SbAreaMonthlyChartController
 * @package App\Http\Controllers\Admin\Charts
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class SbAreaMonthlyChartController extends BaseChartControllerClass
{
    public function setup()
    {
        $this->chart = new Chart();

        if(isset($this->p1) && isset($this->p2)){
          $build = Building::findOrFail($this->p1);
          $utildata = MeetingAreaHelper::GetBuildMonthlyData($build, $this->p2);
          $itemname = array_column($utildata, 'name');
          $this->chart->height(140 + sizeof($itemname) * 35);
          $this->chart->options([
              'xAxis' => ['type' => 'value'],
              'yAxis' => [
                'type' => 'category',
                'data' => $itemname
              ],
              'tooltip' => [
                'trigger' => 'axis',
                'axisPointer' => ['type' => 'shadow']
              ],
              'grid' => ['containLabel' => true]
            ]
          );

          $this->chart->dataset('Hour slot used', 'bar', array_column($utildata, 'hours'))
            // ->color(['rgba(55, 55, 255, 1)'])
            ->options([
              'label' => ['show' => true],
              'xAxis' => ['type' => 'value'],
              'yAxis' => ['type' => 'category'],
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
