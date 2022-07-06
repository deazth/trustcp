<?php

namespace App\Http\Controllers\Admin\Charts;

// use Backpack\CRUD\app\Http\Controllers\ChartController;
// use ConsoleTVs\Charts\Classes\Chartjs\Chart;
use ConsoleTVs\Charts\Classes\Echarts\Chart;
use App\Models\Building;
use App\common\SeatHelper;
// use Illuminate\Http\Request;

/**
 * Class SbdBuildUtilChartController
 * @package App\Http\Controllers\Admin\Charts
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class SbdBuildUtilChartController extends BaseChartControllerClass
{
    public function setup()
    {
      // dd($this);
        $this->chart = new Chart();
        // load the label if it's not called by API
        if(isset($this->p1) && isset($this->p2)){
          $build = Building::findOrFail($this->p1);
          $summ = SeatHelper::GetBuildingSeatSummary($build, $this->p2);
          // $this->chart->labels(array_column($summ, 'fc_name'));
          $itemname = array_column($summ, 'fc_name');
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
        }


        // MANDATORY. Set the labels for the dataset points
        // $this->chart->labels(['2']);

        // $this->chart->options([
        //   'responsive' => true,
        // ]);

        // RECOMMENDED. Set URL that the ChartJS library should call, to get its data using AJAX.
        $this->chart->load(backpack_url('charts/sbd-build-util/' . $this->p1 . '/' . $this->p2));

        // OPTIONAL
        // $this->chart->minimalist(false);
        // $this->chart->displayLegend(true);
        // dd($this);
    }

    /**
     * Respond to AJAX calls with all the chart data points.
     *
     * @return json
     */
    public function data()
    {
      $build = Building::findOrFail($this->p1);
      $summ = SeatHelper::GetBuildingSeatSummary($build, $this->p2);
      // dd($summ);
      // dd(array_column($summ, 'total_seat'));
      $this->chart->labels(array_column($summ, 'fc_name'));

      $this->chart->dataset('Peak Utilization %', 'bar', array_column($summ, 'utilization'))
        // ->color(['rgba(255, 32, 31, 1)'])
        ->options([
          'label' => ['show' => true]
        ])
      ;
    }
}
