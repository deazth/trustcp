<?php

namespace App\Http\Controllers\Admin\Charts;

// use Backpack\CRUD\app\Http\Controllers\ChartController;
// use ConsoleTVs\Charts\Classes\Chartjs\Chart;
use ConsoleTVs\Charts\Classes\Echarts\Chart;
use App\Models\Building;
use App\common\MeetingAreaHelper;

/**
 * Class SbAreaDailyChartController
 * @package App\Http\Controllers\Admin\Charts
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class SbAreaDailyChartController extends BaseChartControllerClass
{
    public function setup()
    {
        $this->chart = new Chart();
        if(isset($this->p1) && isset($this->p2)){
          $build = Building::findOrFail($this->p1);
          $utildata = MeetingAreaHelper::GetBuildDailyData($build, $this->p2);
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


        // RECOMMENDED. Set URL that the ChartJS library should call, to get its data using AJAX.
        // $this->chart->load(backpack_url('charts/sb-area-daily'));

        // OPTIONAL
        // $this->chart->minimalist(false);
        // $this->chart->displayLegend(true);
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
