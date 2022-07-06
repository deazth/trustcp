<?php

namespace App\Http\Controllers\Admin\Charts;

// use Backpack\CRUD\app\Http\Controllers\ChartController;
// use ConsoleTVs\Charts\Classes\Chartjs\Chart;
use ConsoleTVs\Charts\Classes\Echarts\Chart;
use App\Models\Floor;
use App\common\SeatHelper;

/**
 * Class SbFloorIntvChartController
 * @package App\Http\Controllers\Admin\Charts
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class SbFloorIntvChartController extends BaseChartControllerClass
{
    public function setup()
    {
        $this->chart = new Chart();
        if(isset($this->p1) && isset($this->p2)){
          $build = Floor::findOrFail($this->p1);
          $summ = SeatHelper::GetFloorIntvData($build, $this->p2);
          $this->chart->labels(array_column($summ, 'time'));
          // $this->chart->width('100%');


          $this->chart->options([
            'tooltip' => [
              'trigger' => 'axis'
            ],
          ]);
        }

        // RECOMMENDED. Set URL that the ChartJS library should call, to get its data using AJAX.
        $this->chart->load(backpack_url('charts/sb-floor-intv/' . $this->p1 . '/' . $this->p2));

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
      $build = Floor::findOrFail($this->p1);
      $summ = SeatHelper::GetFloorIntvData($build, $this->p2);

      $this->chart->dataset('Seat Utilization', 'line', array_column($summ, 'occupied_seat'))
        ->color(['rgba(32, 32, 255, 1)'])
        ->options([
          'label' => ['show' => true],
          // 'areaStyle' => []
        ])
      ;
      $this->chart->dataset('Free Seat', 'line', array_column($summ, 'free_seat'))
        ->color(['rgba(211, 211, 2, 1)'])
        ->options([
          'label' => ['show' => true],
          'smooth' => true
        ])
      ;

      $this->chart->dataset('Total Seat', 'line', array_column($summ, 'total_seat'))
      ->color(['rgba(255, 32, 31, 1)']);
    }
}
