<?php

namespace App\Http\Controllers\Admin\Charts;

// use Backpack\CRUD\app\Http\Controllers\ChartController;
// use ConsoleTVs\Charts\Classes\Chartjs\Chart;
use ConsoleTVs\Charts\Classes\Echarts\Chart;
use App\Models\Building;
use App\common\SeatHelper;

/**
 * Class SbBuildIntvChartController
 * @package App\Http\Controllers\Admin\Charts
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class SbBuildIntvChartController extends BaseChartControllerClass
{
    public function setup()
    {
        $this->chart = new Chart();
        if(isset($this->p1) && isset($this->p2)){
          $build = Building::findOrFail($this->p1);
          $summ = SeatHelper::GetBuildIntvData($build, $this->p2);
          $this->chart->labels(array_column($summ, 'time'));
          // $this->chart->width('100%');


          $this->chart->options([
            'tooltip' => [
              'trigger' => 'axis'
            ],
          ]);
        }

        // RECOMMENDED. Set URL that the ChartJS library should call, to get its data using AJAX.
        $this->chart->load(backpack_url('charts/sb-build-intv/' . $this->p1 . '/' . $this->p2));

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
      $build = Building::findOrFail($this->p1);
      $summ = SeatHelper::GetBuildIntvData($build, $this->p2);

      $this->chart->dataset('Seat Utilization', 'line', array_column($summ, 'occupied_seat'))
        ->color(['rgba(32, 32, 255, 1)'])
        ->options([
          'label' => ['show' => true],
          // 'areaStyle' => [],
          'emphasis' => ['focus' => 'series']
        ])
      ;
      $this->chart->dataset('Free Seat', 'line', array_column($summ, 'free_seat'))
        ->color(['rgba(211, 211, 2, 1)'])
        ->options([
          'label' => ['show' => true],
          'emphasis' => ['focus' => 'series']
        ])
      ;

      $this->chart->dataset('Total Seat', 'line', array_column($summ, 'total_seat'))
      ->color(['rgba(255, 32, 31, 1)'])
      ->options([
        'emphasis' => ['focus' => 'series']
      ]);
    }
}
