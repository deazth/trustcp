<?php

namespace App\common;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer as Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelHandler {

  const BG_WEEKEND = 'adad99';
  const BG_LEAVE = 'f283f0';  // purple pink
  const BG_LEAVE0 = '66ff66'; // light green
  const BG_PH = 'add836';
  const BG_HEADER = '000000';
  const BG_NORMAL = 'NA';
  const BG_INFO = 'NA';

  // productivity group colors
  const PD_G0 = 'ff1a1a';  // = 0 #ff1a1a
  const PD_GA = 'ff944d';  // < 50 #ff944d
  const PD_GB = 'ffff00';  // 50-80 #ffff00
  const PD_GC = '00ff00';  // 80-100 #00ff00
  const PD_GD = '0066ff';  // > 100 #0066ff
  const PD_NA = 'c34bfa';  // zeroed #c34bfa


  private $filename = "";
  private $spreadsheet = null;

  public function __construct($fname){
    $this->spreadsheet = new Spreadsheet;
    $this->filename = $fname;
  }

  public function addSheet($sheetname, $content, $header = []){
    $cursheet = new Worksheet($this->spreadsheet, $sheetname);
    $this->spreadsheet->addSheet($cursheet);

    // first, populatae the header
    $colcount = 1;
    $rowcount = 1;
    foreach($header as $head){
      $cursheet->setCellValueByColumnAndRow($colcount, $rowcount, $head);
      $cstyle = $cursheet->getStyleByColumnAndRow($colcount, $rowcount);
      $cstyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
       ->getStartColor()->setARGB(self::BG_HEADER);
      $cstyle->getFont()->getColor()->setARGB('ffffff');
      $colcount++;
    }
    $rowcount++;

    // then populate the data
    foreach ($content as $value) {
      $colcount = 1;
      foreach ($value as $oncel) {
        $cursheet->setCellValueByColumnAndRow($colcount, $rowcount, $oncel['v'].'');

        if($oncel['t'] != self::BG_NORMAL){
          $cstyle = $cursheet->getStyleByColumnAndRow($colcount, $rowcount);
          $cstyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB($oncel['t']);

        }

        $colcount++;
      }
      $rowcount++;
    }
  }

  public function getBinary(){
    return base64_encode(serialize($this->spreadsheet));
  }

  public function saveToPerStorage(){
    $writer = new Writer\Xlsx($this->spreadsheet);
    $writer->save('storage/app/reports/' . $this->filename);
  }

  public function download(){
    $writer = new Writer\Xlsx($this->spreadsheet);

    $response =  new StreamedResponse(
        function () use ($writer) {
            $writer->save('php://output');
        }
    );
    // $response->headers->set('Content-Type', 'application/vnd.ms-excel');

    $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $response->headers->set('Content-Disposition', 'attachment;filename="'.$this->filename.'"');
    $response->headers->set('Cache-Control','max-age=0');
    return $response;
  }

  public static function DownloadFromBin($datafromdb, $fname){
    $unserialize_sp = unserialize(base64_decode($datafromdb));
    $writer = new Writer\Xlsx($unserialize_sp);

    $response =  new StreamedResponse(
        function () use ($writer) {
            $writer->save('php://output');
        }
    );
    // $response->headers->set('Content-Type', 'application/vnd.ms-excel');

    $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $response->headers->set('Content-Disposition', 'attachment;filename="'.$fname.'"');
    $response->headers->set('Cache-Control','max-age=0');
    return $response;
  }

  public static function DownloadFromPerStorage($fname){
    // dd($fname);
    if(\Storage::exists('reports/'.$fname)){
      // dd('ada');
      return response()->download(storage_path("app/reports/".$fname));
      // \Storage::download('reports/'.$fname, $fname, [
      //   'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      //   'Content-Disposition' => 'attachment;filename="'.$fname.'"',
      //   'Cache-Control' => 'max-age=0'
      // ]);
    } else {
      return "report 404";
    }

  }
}
