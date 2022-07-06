<?php

namespace App\Http\Controllers\Admin\Charts;

use Backpack\CRUD\app\Http\Controllers\ChartController;
//use ConsoleTVs\Charts\Classes\Chartjs\Chart;
use ConsoleTVs\Charts\Classes\Echarts\Chart;
use \Carbon\Carbon;
use App\common\GDWActions;


/**
 * Class SampleChartController
 * @package App\Http\Controllers\Admin\Charts
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class DiaryChartController extends BaseChartControllerClass
{


    public function setup()
    {

        $this->chart = new Chart();
        // MANDATORY. Set the labels for the dataset points
        $labels = [];
        $labels2 = [];


        $cdate = new Carbon();
        $ldate = new Carbon();
        $daterange = new \DatePeriod(
            $cdate->subDays(6),
            \DateInterval::createFromDateString('1 day'),
            $ldate->addDay()
        );

        $weekMap = [
            0 => 'Sun',
            1 => 'Mon',
            2 => 'Tue',
            3 => 'Wed',
            4 => 'Thu',
            5 => 'Fri',
            6 => 'Sat',
        ];


        foreach ($daterange as $indate) {
            //$labels[] = $indate->toDateString();
            //$labels[] = $indate->format('l');
            $labels[] = [$weekMap[$indate->dayOfWeek] , $indate->format('M d')] ;
            $labels2[] = $indate->toDateString();
        }




        // $this->chart->labels($labels);
        $this->chart->options([


            'xAxis' => [
                'type' => 'category',
                'data' => $labels,
                'showBackground' => true,
                'axisLabel' => [
                    'rotate' => 90,
                    'interval' => 0
                ]

            ],



            'yAxis' => [
                'name' => 'percentage',
                'type' => 'value',
                'axisLabel' => [
                    'formatter' => '{value} %'
                ],

                'nameLocation' => 'middle',
                'nameGap' => 50
            ],
            'grid' => ['containLabel' => true]


        ]);


        // dd($this->p1);

        // RECOMMENDED. Set URL that the ChartJS library should call, to get its data using AJAX.
        $uri = 'charts/diari/' . $this->p1;
        $this->chart->load(backpack_url($uri));


        // OPTIONAL
        $this->chart->minimalist(false);
        $this->chart->displayLegend(true);
        //$this->chart->displayAxes(false);
        //$this->chart->displayLegend(true);
        //$this->chart->labels(['HTML', 'CSS', 'PHP', 'JS']);

    }

    /**
     * Respond to AJAX calls with all the chart data points.
     *
     * @return json
     */


    public function data()
    {

        $cdate = new Carbon();
        $ldate = new Carbon();
        $daterange = new \DatePeriod(
            $cdate->subDays(6),
            \DateInterval::createFromDateString('1 day'),
            $ldate->addDay()
        );

        $labels2[] = [];


        $gdata = GDWActions::GetStaffRecentPerf($this->p1, $daterange);
        foreach ($gdata  as $key) {
            //dd($key['date']);

            $datas[] = $key["perc"];
        }



        $this->chart->dataset('Recent Performance', 'bar', $datas)
            ->color('#E56717')->options([
                'showBackground' => true,
                'backgroundStyle' => [
                    'color' => 'grey'
                ],
                'label' => [
                    'show'=> true,
                    'position'=>'inside'
                ],

                ]);




    }
}
