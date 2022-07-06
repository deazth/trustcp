<?php

namespace App\Http\Controllers\Admin\Charts;

use Backpack\CRUD\app\Http\Controllers\ChartController;
use ConsoleTVs\Charts\Classes\Echarts\Chart;
use App\Models\User;
use App\Models\Unit;
use App\common\CommonHelper;

/**
 * Class InvolveStatChartController
 * @package App\Http\Controllers\Admin\Charts
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class InvolveStatChartController extends ChartController
{
    public function setup()
    {
        $this->chart = new Chart();
        $gitdlabel = CommonHelper::GetCConfig('gitd_label', 'IT & Digital');
        // list of division
        $divisions = Unit::find(User::where('status', 1)->where('lob_descr', $gitdlabel)->distinct()->pluck('unit_id'));

        $grplabel = [];
        $divcounts = [];
        $inv0 = [];
        $inv99 = [];
        $inv100 = [];
        $inv101 = [];

        foreach ($divisions as $key => $value) {
          $grplabel[] = $value->pporgunitdesc;
          // get the user count
          $divcounts[] = $value->Staffs->count();

          // staff that havent fill in involvements
          $ia = 0;
          $ib = 0;
          $ic = 0;
          $id = 0;
          foreach ($value->Staffs as $st) {

            $gotinvs = $st->Involvements;
            if($gotinvs){
              $sump = $gotinvs->sum('perc');
              if($sump == 0){
                $ia++;
              } elseif ($sump < 100) {
                $ib++;
              } elseif ($sump == 100) {
                $ic++;
              } else {
                $id++;
              }
            } else {
              $ia++;
            }

          }

          $inv0[] = $ia;
          $inv99[] = $ib;
          $inv100[] = $ic;
          $inv101[] = $id;

        }


        // RECOMMENDED. Set URL that the ChartJS library should call, to get its data using AJAX.
        // $this->chart->load(backpack_url('charts/involve-stat'));

        $this->chart->labels($grplabel);
        $this->chart->height(140 + sizeof($grplabel) * 100);
        $this->chart->options(['responsive' => true,
          'xAxis' => ['type' => 'value'],
          'yAxis' => [
            'type' => 'category',
            'data' => $grplabel,
            // 'axisLabel' => ['interval' => 0, 'rotate' => sizeof($lable) > 8 ? 30 : 0],
            // 'splitline' => ['show' => false]
          ],
          'tooltip' => [
            'trigger' => 'axis',
            'axisPointer' => ['type' => 'shadow']
          ],
          'grid' => ['containLabel' => true]
        ]);

        $this->chart->dataset('Staff Count', 'bar', $divcounts)
          ->color('rgba(11, 11, 116, 0.8)')
          ->options([
            'label' => [
              'show' => true,
            ],
            'emphasis' => ['focus' => 'series'],
          ]);
        $this->chart->dataset('No Data', 'bar', $inv0)
          ->color('rgba(244, 11, 11, 0.8)')
          ->options([
            'stack' => 'total',
            'label' => [
              'show' => true,
            ],
            'emphasis' => ['focus' => 'series'],
          ]);
        $this->chart->dataset('< 100%', 'bar', $inv99)
          ->color('rgba(211, 189, 33, 0.8)')
          ->options([
            'stack' => 'total',
            'label' => [
              'show' => true,
            ],
            'emphasis' => ['focus' => 'series'],
          ]);
        $this->chart->dataset('100%', 'bar', $inv100)
          ->color('rgba(11, 189, 11, 0.8)')
          ->options([
            'stack' => 'total',
            'label' => [
              'show' => true,
            ],
            'emphasis' => ['focus' => 'series'],
          ]);
        $this->chart->dataset('> 100%', 'bar', $inv101)
          ->color('rgba(231, 13, 116, 0.8)')
          ->options([
            'stack' => 'total',
            'label' => [
              'show' => true,
            ],
            'emphasis' => ['focus' => 'series'],
          ]);

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
