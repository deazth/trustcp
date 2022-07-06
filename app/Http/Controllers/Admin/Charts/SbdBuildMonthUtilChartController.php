<?php

namespace App\Http\Controllers\Admin\Charts;

use ConsoleTVs\Charts\Classes\Echarts\Chart;
use App\Models\Building;
use App\common\SeatHelper;

/**
 * Class SbdBuildMonthUtilChartController
 * @package App\Http\Controllers\Admin\Charts
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class SbdBuildMonthUtilChartController extends BaseChartControllerClass
{
    public function setup()
    {
        $this->chart = new Chart();

        if(isset($this->p1) && isset($this->p2)){
          $build = Building::findOrFail($this->p1);
          $summ = SeatHelper::GetBuildingSeatMonthlySummary($build, $this->p2);
          // $this->chart->labels(array_column($summ, 'fc_name'));
          $itemname = array_column($summ, 'fc_name');
          $this->chart->height(140 + sizeof($itemname) * 35);

          $this->chart->labels(array_column($summ, 'fc_name'));
          //dd($summ);
          $data = [];
         
          foreach($summ as $sum){
           // dd($sum);
            try{
            $data[] = [
              'name' => $sum['fc_name'],
              'value' => $sum['pretty_util'],
              'value1'  => $sum['utilization']
            ];
          }catch(\Exception $e){dd($e);}

          }
          //dd($data);

          $this->chart->dataset('Peak Utilization %', 'bar',
          $data)
            // ->color(['rgba(255, 32, 31, 1)'])
            ->options([
              'label' => ['show' => true, 'offset' => [18,0]]
            ])
          ;

          $this->chart->options([
              'xAxis' => ['type' => 'value'],
              'yAxis' => [
                'type' => 'category',
                'data' => $itemname
              ],
              'tooltip' => [
                'trigger' => 'axis',
                'axisPointer' => ['type' => 'shadow'],
               'formatter' => $this->chart->rawObject('buildMonthTooltip')
                
              ],
              'grid' => ['containLabel' => true]
            ]
          );
        }

        // RECOMMENDED. Set URL that the ChartJS library should call, to get its data using AJAX.
        // $this->chart->load(backpack_url('charts/sbd-build-month-util'));

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
