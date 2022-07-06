<?php

namespace App\Http\Controllers\Admin\Charts;

use Backpack\CRUD\app\Http\Controllers\ChartController;
// use ConsoleTVs\Charts\Classes\Chartjs\Chart;

/**
 * Class InvUsageChartController
 * @package App\Http\Controllers\Admin\Charts
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class BaseChartControllerClass extends ChartController
{
  protected $p1 = null;
  protected $p2 = null;
  protected $p3 = null;
  public function __construct($param1 = null, $param2 = null, $param3 = null)
  {
    // Pass to custom setup method the parameter
    $this->p1 = $param1;
    $this->p2 = $param2;
    $this->p3 = $param3;

    parent::__construct();
  }

  public function response($param1 = null, $param2 = null, $param3 = null)
  {
      $this->p1 = $param1;
      $this->p2 = $param2;
      $this->p3 = $param3;
      // call the data() method, if present
      if (method_exists($this, 'data')) {
          $this->data();
      }

      if ($this->chart) {
          $response = $this->chart->api();
      } else {
          $response = $this->api();
      }

      return response($response)->withHeaders([
          'Content-Type' => 'application/json',
      ]);
  }
}
