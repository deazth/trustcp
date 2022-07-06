<?php

namespace App\Http\Controllers\Admin\Charts;

use ConsoleTVs\Charts\Classes\Echarts\Chart;
use App\Models\Building;
use App\Models\UtilFloorSectionDaily;
use \Carbon\Carbon;

/**
 * Class SbdBuildDurUtilChartController
 * @package App\Http\Controllers\Admin\Charts
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class SbdBuildDurUtilChartController extends BaseChartControllerClass
{
    public function setup()
    {
        $this->chart = new Chart();

        $sdate = new Carbon($this->p2);
        $edate = new Carbon($this->p3);
        $lbls = [];
        while($sdate->lte($edate)){
          $lbls[] = $sdate->format('D d-M');
          $sdate->addDay();
        }

        // MANDATORY. Set the labels for the dataset points
        $this->chart->labels($lbls);
        $this->chart->options([
            'yAxis' => ['type' => 'value'],
            'xAxis' => [
              'type' => 'category',
              'data' => $lbls,
              'axisLabel' => ['interval' => 0, 'rotate' => sizeof($lbls) > 8 ? 30 : 0],
              'splitline' => ['show' => false]
            ],
            'tooltip' => [
              'trigger' => 'axis',
              'axisPointer' => ['type' => 'shadow']
            ],
            'grid' => ['containLabel' => true]
          ]
        );

        // RECOMMENDED. Set URL that the ChartJS library should call, to get its data using AJAX.
        $this->chart->load(backpack_url('charts/sbd-build-dur-util/' . $this->p1 . '/' . $this->p2 . '/' . $this->p3));

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
      $sdate = new Carbon($this->p2);
      $edate = new Carbon($this->p3);
      $maxseat = [];
      $usedseat = [];
      while($sdate->lte($edate)){
        $bdt = UtilFloorSectionDaily::where('building_id', $this->p1)
          ->whereDate('report_date', $sdate->toDateString())->get();

        $maxseat[] = $bdt->sum('total_seat');
        $usedseat[] = $bdt->sum('max_occupied_seat');
        $sdate->addDay();
      }

      $this->chart->dataset('Total Seats', 'bar', $maxseat)
        ->color('rgba(32, 32, 255, 0.9)')
        ->options([
          'label' => [
            'show' => true,
            'position' => 'top'
          ]
        ]);
      $this->chart->dataset('Peak Util', 'line', $usedseat)
          ->color('rgba(244, 222, 11, 1)')
          ->options([
            'label' => [
              'show' => true
            ]
          ]);
    }
}
