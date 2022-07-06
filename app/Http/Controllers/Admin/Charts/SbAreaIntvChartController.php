<?php

namespace App\Http\Controllers\Admin\Charts;

// use Backpack\CRUD\app\Http\Controllers\ChartController;
use ConsoleTVs\Charts\Classes\Echarts\Chart;
use App\Models\Building;
use App\common\MeetingAreaHelper;

/**
 * Class SbAreaIntvChartController
 * @package App\Http\Controllers\Admin\Charts
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class SbAreaIntvChartController extends BaseChartControllerClass
{
    public function setup()
    {
        $this->chart = new Chart();

        if(isset($this->p1) && isset($this->p2)){
          $build = Building::findOrFail($this->p1);
          $chdata = MeetingAreaHelper::GetBuildIntvData($build, $this->p2);


          // dd($chdata);
          $hours = [];
          $hours[] = '12a';
          for ($i=1; $i < 12; $i++) {
            $hours[] = $i . 'a';
          }

          $hours[] = '12p';
          for ($i=1; $i < 12; $i++) {
            $hours[] = $i . 'p';
          }

          // $hours = [1, 2, 3];

          $this->chart->height(140 + sizeof($chdata['area']) * 35);

          $this->chart->options([
              'xAxis' => [
                'type' => 'category',
                'data' => $hours,
                'splitArea' => [ 'show' => true ]
              ],
              'yAxis' => [
                'type' => 'category',
                'data' => $chdata['area'],
                'splitArea' => [ 'show' => true ]
              ],
              'tooltip' => [
                'position' => 'top'
              ],
              'grid' => ['containLabel' => true]
            ]
          );

          $this->chart->dataset('Area in use', 'heatmap', $chdata['data'])
            // ->color(['rgba(32, 32, 255, 1)'])
            ->options([
              'label' => ['show' => false],
            ])
          ;
        }





        // RECOMMENDED. Set URL that the ChartJS library should call, to get its data using AJAX.
        // $this->chart->load(backpack_url('charts/sb-area-intv/' . $this->p1 . '/' . $this->p2));

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
    //   $build = Building::findOrFail($this->p1);
    //   $chdata = MeetingAreaHelper::GetBuildIntvData($build, $this->p2);
    //
    //   $this->chart->dataset('Area Usage', 'heatmap', $chdata['data'])
    //     ->color(['rgba(32, 32, 255, 1)'])
    //     ->options([
    //       'label' => ['show' => false],
    //     ])
    //   ;
    // }
}
