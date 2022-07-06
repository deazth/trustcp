<?php

namespace App\Http\Controllers\Admin\Charts;

use Backpack\CRUD\app\Http\Controllers\ChartController;
use ConsoleTVs\Charts\Classes\Echarts\Chart;
// use ConsoleTVs\Charts\Classes\Highcharts\Chart;

/**
 * Class InvUsageChartController
 * @package App\Http\Controllers\Admin\Charts
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class InvUsageChartController extends BaseChartControllerClass
{

    public function setup()
    {
        $this->chart = new Chart();

        // MANDATORY. Set the labels for the dataset points
        // $this->chart->labels([
        //     'Vacant', 'Occupied'
        // ]);

        // $this->chart->options([
        //   'scales' => [
        //     'xAxes' => [[
        //       'gridLines' => ['display' => false]
        //     ]],
        //     'yAxes' => [[
        //       'gridLines' => ['display' => false]
        //     ]]
        //   ]
        // ]);

        $class = null;
        $filter = null;
        $nexttype = 'f';
        $lblfield = 'building_name';

        if($this->p1 == 'f'){
          $class = \App\Models\Floor::class;
          $filter = 'building_id';
          $lblfield = 'floor_name';
          $nexttype = 'fc';
        } elseif ($this->p1 == 'fc') {
          $class = \App\Models\FloorSection::class;
          $lblfield = 'label';
          $filter = 'floor_id';
        } elseif ($this->p1 == 'm') {
          $class = \App\Models\Seat::class;
          $lblfield = 'label';
          $filter = 'floor_section_id';
        } else {
          // either blank or b
          $class = \App\Models\Building::class;
        }

        $searcher = $class::query();
        if($filter){
          $searcher->where($filter, $this->p2);
        }

        $res = $searcher->orderBy($lblfield, 'desc')->get();
        $itemname = [];
        $itemlist = [];
        foreach ($res as $key => $value) {
          $itemlist[] = [
            'id' => $value->id,
            'name' => $value->GetLabel()
          ];
          $itemname[] = $value->GetLabel();
        }

        if($this->p1 != 'fc'){
          $this->chart->clickfunc = 'goToNext';
          $this->chart->itemlist = $itemlist;
        }

        $this->chart->height(140 + sizeof($itemname) * 35);


        $this->chart->options([
            'xAxis' => ['type' => 'value'],
            'yAxis' => [
              'type' => 'category',
              'data' => $itemname,
              'triggerEvent' => $this->p1 != 'fc'
            ],
            'tooltip' => [
              'trigger' => 'axis',
              'axisPointer' => ['type' => 'shadow']
            ],
            'grid' => ['containLabel' => true]
          ]
        );

        // RECOMMENDED. Set URL that the ChartJS library should call, to get its data using AJAX.
        $this->chart->load(backpack_url('charts/inv-usage/' . $this->p1 . '/' . $this->p2));
        // $this->chart->dataset('Seat Utilization', 'pie', [
        //             4,7,3
        //         ])
        //     // ->color('rgba(205, 32, 31, 1)')
        //     ->backgroundColor([
        //   'rgba(77, 189, 116, 0.8)',
        //   'rgba(196, 92, 16, 0.8)'
        //     ]);
        // OPTIONAL
        // $this->chart->minimalist(true);
        // $this->chart->displayLegend(true);

    }

    /**
     * Respond to AJAX calls with all the chart data points.
     *
     * @return json
     */
    public function data()
    {
      $class = null;
      $filter = null;
      $lblfield = 'building_name';

      if($this->p1 == 'f'){
        $class = \App\Models\Floor::class;
        $lblfield = 'floor_name';
        $filter = 'building_id';
      } elseif ($this->p1 == 'fc') {
        $class = \App\Models\FloorSection::class;
        $lblfield = 'label';
        $filter = 'floor_id';
      } elseif ($this->p1 == 'm') {
        $class = \App\Models\Seat::class;
        $lblfield = 'label';
        $filter = 'floor_section_id';
      } else {
        // either blank or b
        $class = \App\Models\Building::class;
      }

      $searcher = $class::query();
      if($filter){
        $searcher->where($filter, $this->p2);
      }

      $res = $searcher->orderBy($lblfield, 'desc')->get();
      $itemname = [];
      $itemavail = [];
      $itembooked = [];
      $itemoccupied = [];

      foreach ($res as $key => $value) {
        $seatsumm = $value->SeatSummary();
        $itemname[] = $value->GetLabel();
        $itemavail[] = strval($seatsumm['vacant']);
        $itembooked[] = strval($seatsumm['booked']);
        $itemoccupied[] = strval($seatsumm['used']);
      }

      $this->chart->labels(['Available', 'Occupied', 'Booked']);

      $this->chart->dataset('Available', 'bar', $itemavail)
        ->color('rgba(77, 189, 116, 0.8)')
        ->options([
          'stack' => 'total',
          'label' => [
            'show' => true,
          ],
          'emphasis' => ['focus' => 'series'],
        ]);
      $this->chart->dataset('Occupied', 'bar', $itemoccupied)
        ->color('rgba(196, 92, 16, 0.8)')
        ->options([
          'stack' => 'total',
          'label' => ['show' => true],
          'emphasis' => ['focus' => 'series'],
        ]);
      $this->chart->dataset('Booked', 'bar', $itembooked)
        ->color('rgba(50, 92, 200, 0.8)')
        ->options([
          'stack' => 'total',
          'label' => ['show' => true],
          'emphasis' => ['focus' => 'series'],
        ]);

    }
}
