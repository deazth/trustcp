<?php

namespace App\Http\Controllers\Admin\Charts;

use ConsoleTVs\Charts\Classes\Echarts\Chart;
use App\Models\Building;
use App\common\SeatHelper;
/**
 * Class SbdDailyBldWfallChartController
 * @package App\Http\Controllers\Admin\Charts
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class SbdDailyBldWfallChartController extends BaseChartControllerClass
{
    public function setup()
    {
        $this->chart = new Chart();

        if(isset($this->p1) && isset($this->p2)){
          $build = Building::findOrFail($this->p1);
          $summ = SeatHelper::GetBuildingSeatSummary($build, $this->p2);
          usort($summ, function($a, $b) {
            return $a['max_occupied_seat'] <=> $b['max_occupied_seat'];
          });
          // $this->chart->labels(array_column($summ, 'fc_name'));
          $itemname = array_column($summ, 'fc_name');
          $this->chart->height(600);

          $this->chart->options([
              'yAxis' => ['type' => 'value'],
              'xAxis' => [
                'type' => 'category',
                'data' => $itemname,
                'axisLabel' => ['interval' => 0, 'rotate' => 30],
                'splitline' => ['show' => false]
              ],
              'tooltip' => [
                'trigger' => 'axis',
                'axisPointer' => ['type' => 'shadow']
              ],
              'grid' => ['containLabel' => true]
            ]
          );
        }

        // RECOMMENDED. Set URL that the ChartJS library should call, to get its data using AJAX.
        $this->chart->load(backpack_url('charts/sbd-daily-bld-wfall/' . $this->p1 . '/' . $this->p2));

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
      $summ = SeatHelper::GetBuildingSeatSummary($build, $this->p2);
      usort($summ, function($a, $b) {
        return $a['max_occupied_seat'] <=> $b['max_occupied_seat'];
      });
      $actualdata = array_column($summ, 'max_occupied_seat');
      $gapfiller = [];
      $cusrum = 0;

      foreach($actualdata as $d){
        array_push($gapfiller, $cusrum);
        $cusrum += $d;
      }

      $this->chart->labels(array_column($summ, 'fc_name'));

      $this->chart->dataset('Sum Previous', 'bar', $gapfiller)
        ->color('transparent')
        ->options([
          'stack' => 'total',
          'itemStyle' => [
            'borderColor' => 'transparent',
            'color' => 'transparent'
          ],
          'emphasis' => [
            'itemStyle' => [
              'borderColor' => 'transparent',
              'color' => 'transparent'
            ]
          ],
        ]);

      $this->chart->dataset('Peak Util Count', 'bar', $actualdata)
        ->color('teal')
        ->options([
          'stack' => 'total',
          'label' => [
            'show' => true,
          ]
        ]);
    }
}
