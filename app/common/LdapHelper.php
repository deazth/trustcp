<?php

namespace App\common;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Unit;

class LdapHelper
{
  public static function doLogin($username, $password){
    error_log('reach doLogin '.$username);
    // set_error_handler(array($this, 'errorHandler'));
    set_error_handler(function($errno, $errstr){
      return self::respond_json($errno, $errstr);
    });
    $errorcode = 200;
    $errm = 'success';

    $udn = "cn=$username,ou=users,o=data";
    $hostnameSSL = config('custom.ldap.hostname');
    //	ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
    putenv('LDAPTLS_REQCERT=never');

    $con =  ldap_connect($hostnameSSL);
    error_log('reach doLogin.ldap_connect '.$hostnameSSL);
    if (is_resource($con)){
      if (ldap_set_option($con, LDAP_OPT_PROTOCOL_VERSION, 3)){
        ldap_set_option($con, LDAP_OPT_REFERRALS, 0);

        // try to mind / authenticate
        try{
        if (ldap_bind($con,$udn, $password)){
          $errm = 'success';

          // insert into login access table
          // $loginacc = new LoginAccess;
          // $loginacc->STAFF_ID = $username;
          // $loginacc->FROM_IP = request()->ip();
          // $loginacc->save();

        } else {
          $errorcode = 401;
          $errm = ldap_error($con);
        }} catch(Exception $e) {
          $errorcode = 500;
          $errm = $e->getMessage();
        }

      } else {
        $errorcode = 500;
        $errm = "TLS not supported. Unable to set LDAP protocol version to 3";
      }

      // clean up after done
      ldap_close($con);

    } else {
      $errorcode = 500;
      $errm = "Unable to connect to $hostnameSSL";
    }

    if($errorcode == 200){
      // self::logs($username, 'Login', []);

      return self::fetchUser($username);
    }
    error_log('end doLogin.ldap_connect ');
    return self::respond_json($errorcode, $errm);

  }


  /**
  *	get the information for the requested user
  *	to be used internally
  */
  public static function fetchUser($username, $searchtype = 'cn'){

    //set_error_handler(array($this, 'errorHandler'));

    // do the ldap things
    $errm = 'success';
    $errorcode = 200;
    $adminuser = config('custom.ldap.adminuser');
    $password = config('custom.ldap.adminpass');
    $hostnameSSL = config('custom.ldap.hostname');
    $udn= "cn=$adminuser, ou=serviceAccount, o=Telekom";
    $retdata = [];
    //	ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
    error_log('reach fetchUser '.$username);

    putenv('LDAPTLS_REQCERT=never');

    $con =  ldap_connect($hostnameSSL);


    error_log('reach fetchUser.ldap_connect '.$hostnameSSL);
    if (is_resource($con)){

      if (ldap_set_option($con, LDAP_OPT_PROTOCOL_VERSION, 3)){
        ldap_set_option($con, LDAP_OPT_REFERRALS, 0);

        // try to bind / authenticate
        try{

        if (ldap_bind($con,$udn, $password)){

          // perform the search
          $ldres = ldap_search($con, 'ou=users,o=data', "$searchtype=$username");
          $ldapdata = ldap_get_entries($con, $ldres);
          // dd($ldapdata);
          // return $ldapdata;

          if($ldapdata['count'] == 0){
            $errorcode = 404;
            $errm = 'user not found';
          } else {
            $costcenter = $ldapdata['0']['ppcostcenter']['0'];
            $stid = $ldapdata['0']['cn']['0'];
            $sppersno = 0;
            $sptrustid = 0;
            $emptype = $ldapdata['0']['employeetype']['0'];

            if($emptype == 'Vendors'){
              $unit = 'Vendors';
              $subunit = $ldapdata['0']['ppdivision']['0'];
              $dept = 123;

              if (isset($ldapdata['0']['ppreportto']['0'])) {
                  $rpttosno = $ldapdata['0']['ppreportto']['0'];
              } else {
                  $rpttosno = $ldapdata['0']['ppreporttoid']['0'];
              }
              $boss = User::where('staff_no', $rpttosno)->first();
              if($boss){
                $sppersno = $boss->persno;
                $sptrustid = $boss->id;
              }

              $eprsno = null;
            }
            elseif ($emptype == 'Leasing-Staff'|| $emptype == 'Trainees') {
              // first find the boss
              if (isset($ldapdata['0']['ppreportto']['0'])) {
                  $rpttosno = $ldapdata['0']['ppreportto']['0'];
              } else {
                  $rpttosno = $ldapdata['0']['ppreporttoid']['0'];
              }

              $boss = User::where('staff_no', $rpttosno)->first();
              $trainee = User::where('staff_no', $stid)->first();
              if ($boss) {
                  $sppersno = $boss->persno;
                  $unit = $boss->unit;
                  $sptrustid = $boss->id;
              } else {
                $unit = 123;
              }


              $subunit = $ldapdata['0']['pplocation']['0'];

              if ($trainee) {
                  $eprsno = $trainee->persno;
                  $dept = $trainee->lob;
              } else {
                  $eprsno = $ldapdata['0']['employeenumber']['0'];
                  $dept = $boss->lob;
              }
              if (strlen($eprsno) > 7) {
                  $eprsno = substr($eprsno, 2) + 0;
              }
          }

            else {
              $unit = $ldapdata['0']['pporgunitdesc']['0'];
              $subunit = $ldapdata['0']['ppsuborgunitdesc']['0'];
              $dept = $ldapdata['0']['pporgunit']['0'];
              $eprsno = $ldapdata['0']['employeenumber']['0'];
              if(strlen($eprsno) > 6){
                $eprsno = substr($eprsno, -6) + 0;
              }

              $sppersno = $ldapdata['0']['ppreportto']['0'] + 0;
            }


            $retdata = [
              'STAFF_NO' => $stid,
              'NAME' => $ldapdata['0']['fullname']['0'],
              'UNIT' => $unit,
              'SUBUNIT' => $subunit,
              'DEPARTMENT' => $dept,
              'COST_CENTER' => $costcenter,
              'SAP_NUMBER' => $ldapdata['0']['employeenumber']['0'],
              'JOB_GRADE' => $ldapdata['0']['ppjobgrade']['0'],
              'NIRC' => $ldapdata['0']['ppnewic']['0'],
              'EMAIL' => $ldapdata['0']['mail']['0'],
              'MOBILE_NO' => $ldapdata['0']['mobile']['0'],
              'SUPERIOR' => $ldapdata['0']['ppreporttoname']['0'],
              'SP_PERSNO' => $sppersno,
              'PERSNO' => $eprsno,
              'ORI_PERSNO' => $ldapdata['0']['employeenumber']['0'],
              'EMPLOYEE_TYPE' => $emptype,
              'SP_TRUSTID' => $sptrustid
            ];


          }

        } else {

          $errorcode = 403;
          $errm = 'Invalid admin credentials.';
        }} catch(Exception $e) {
          $errorcode = 500;
          $errm = $e->getMessage();
        }

      } else {
        $errorcode = 500;
        $errm = "TLS not supported. Unable to set LDAP protocol version to 3";
      }

      // clean up after done

      ldap_close($con);

    } else {

      $errorcode = 500;
      $errm = "Unable to connect to $hostnameSSL";
    }

    return self::respond_json($errorcode, $errm, $retdata);
    //return [ 'code'=>$errorcode, 'msg'=> $errm, 'data'=>$retdata];
  }

  public static function LdapScanner($username, $searchtype = 'ppcostcenter'){

    //set_error_handler(array($this, 'errorHandler'));

    // do the ldap things
    $errm = 'success';
    $errorcode = 200;
    $adminuser = config('custom.ldap.adminuser');
    $password = config('custom.ldap.adminpass');
    $hostnameSSL = config('custom.ldap.hostname');
    $udn= "cn=$adminuser, ou=serviceAccount, o=Telekom";
    $retdata = [];
    //	ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);

    putenv('LDAPTLS_REQCERT=never');

    $con =  ldap_connect($hostnameSSL);
    if (is_resource($con)){

      if (ldap_set_option($con, LDAP_OPT_PROTOCOL_VERSION, 3)){
        ldap_set_option($con, LDAP_OPT_REFERRALS, 0);

        // try to bind / authenticate
        try{

        if (ldap_bind($con,$udn, $password)){

          // perform the search
          $ldres = ldap_search($con, 'ou=users,o=data', "$searchtype=$username");
          $ldapdata = ldap_get_entries($con, $ldres);
          // dd($ldapdata);
          // return $ldapdata;

          foreach ($ldapdata as $key => $value) {
            if($key == 'count'){
              continue;
            }

            $stid = $value['cn']['0'];
            $disabled = array_key_exists('logindisabled', $value) ? $value['logindisabled']['0'] : 'FALSE';
            if($disabled != 'FALSE'){
              continue;
            }


            try {
              $costcenter = $value['ppcostcenter']['0'];
              $sppersno = 0;
              $emptype = $value['employeetype']['0'];

              if($emptype == 'Vendors'){
                $unit = 'Vendors';
                $subunit = array_key_exists('ppdivision', $value) ? $value['ppdivision']['0'] : 'N/A';
                $dept = 123;

                if (isset($value['ppreportto']['0'])) {
                    $rpttosno = $value['ppreportto']['0'];
                } else {
                    $rpttosno = $value['ppreporttoid']['0'];
                }
                $boss = User::where('staff_no', $rpttosno)->first();
                if($boss){
                  $sppersno = $boss->persno;
                }
                $eprsno = $value['employeenumber']['0'];

                if (strlen($eprsno) > 7) {
                  $eprsno = substr($eprsno, 2) + 0;
                }
              } elseif ($emptype == 'Leasing-Staff'|| $emptype == 'Trainees') {
                // first find the boss
                if (isset($value['ppreportto']['0'])) {
                    $rpttosno = $value['ppreportto']['0'];
                } else {
                    $rpttosno = $value['ppreporttoid']['0'];
                }

                $boss = User::where('staff_no', $rpttosno)->first();
                $trainee = User::where('staff_no', $stid)->first();
                if ($boss) {
                    $sppersno = $boss->persno;
                    $unit = $boss->unit;
                } else {
                  $unit = $boss->unit;
                }


                $subunit = $value['pplocation']['0'];

                if ($trainee) {
                    $eprsno = $trainee->persno;
                    $dept = $trainee->lob;
                } else {
                    $eprsno = $value['employeenumber']['0'];
                    $dept = $boss->lob;
                }
                if (strlen($eprsno) > 7) {
                    $eprsno = substr($eprsno, 2) + 0;
                }
              } else {
                $unit = $value['pporgunitdesc']['0'];
                $subunit = $value['ppsuborgunitdesc']['0'];
                $dept = $value['pporgunit']['0'];
                $eprsno = $value['employeenumber']['0'];
                if(strlen($eprsno) > 6){
                  $eprsno = substr($eprsno, -6) + 0;
                }

                $sppersno = $value['ppreportto']['0'] + 0;
              }


              $retdata[] = [
                'STAFF_NO' => $stid,
                'NAME' => $value['fullname']['0'],
                'UNIT' => $unit,
                'SUBUNIT' => $subunit,
                'DEPARTMENT' => $dept,
                'COST_CENTER' => $costcenter,
                'SAP_NUMBER' => $value['employeenumber']['0'],
                // 'JOB_GRADE' => $value['ppjobgrade']['0'],
                // 'NIRC' => $value['ppnewic']['0'],
                'EMAIL' => $value['mail']['0'],
                'MOBILE_NO' => $value['mobile']['0'],
                'SUPERIOR' => $value['ppreporttoname']['0'],
                'SP_PERSNO' => $sppersno,
                'PERSNO' => $eprsno,
                'ORI_PERSNO' => $value['employeenumber']['0'],
                'EMPLOYEE_TYPE' => $emptype
              ];
            } catch (\Exception $e) {
              \Illuminate\Support\Facades\Log::info('ldapscanner ' . $stid . ' : ' . $e->getMessage());
              \Illuminate\Support\Facades\Log::info($value);
            }




          }

        } else {

          $errorcode = 403;
          $errm = 'Invalid admin credentials.';
        }} catch(Exception $e) {
          $errorcode = 500;
          $errm = $e->getMessage();
        }

      } else {
        $errorcode = 500;
        $errm = "TLS not supported. Unable to set LDAP protocol version to 3";
      }

      // clean up after done

      ldap_close($con);

    } else {

      $errorcode = 500;
      $errm = "Unable to connect to $hostnameSSL";
    }

    return $retdata;
    //return [ 'code'=>$errorcode, 'msg'=> $errm, 'data'=>$retdata];
  }

  public static function fetchUserRaw($username, $searchtype = 'cn'){

    //set_error_handler(array($this, 'errorHandler'));

    // do the ldap things
    $errm = 'success';
    $errorcode = 200;
    $adminuser = config('custom.ldap.adminuser');
    $password = config('custom.ldap.adminpass');
    $hostnameSSL = config('custom.ldap.hostname');
    $udn= "cn=$adminuser, ou=serviceAccount, o=Telekom";
    $retdata = [];
    //	ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
    error_log('reach fetchUser '.$username);

    putenv('LDAPTLS_REQCERT=never');

    $con =  ldap_connect($hostnameSSL);


    error_log('reach fetchUser.ldap_connect '.$hostnameSSL);
    if (is_resource($con)){

      if (ldap_set_option($con, LDAP_OPT_PROTOCOL_VERSION, 3)){
        ldap_set_option($con, LDAP_OPT_REFERRALS, 0);

        // try to bind / authenticate
        try{

        if (ldap_bind($con,$udn, $password)){

          // perform the search
          $ldres = ldap_search($con, 'ou=users,o=data', "$searchtype=$username");
          $retdata = ldap_get_entries($con, $ldres);
          // dd($ldapdata);
        } else {

          $errorcode = 403;
          $errm = 'Invalid admin credentials.';
        }} catch(Exception $e) {
          $errorcode = 500;
          $errm = $e->getMessage();
        }

      } else {
        $errorcode = 500;
        $errm = "TLS not supported. Unable to set LDAP protocol version to 3";
      }

      // clean up after done

      ldap_close($con);

    } else {

      $errorcode = 500;
      $errm = "Unable to connect to $hostnameSSL";
    }

    return self::respond_json($errorcode, $errm, $retdata);
    //return [ 'code'=>$errorcode, 'msg'=> $errm, 'data'=>$retdata];
  }

  function fetchLeasingUsers($username, $searchparam = 'ppreportto'){

    set_error_handler(array($this, 'errorHandler'));

    // do the ldap things
    $errm = 'success';
    $errorcode = 200;
    $adminuser = config('custom.ldap.adminuser');
    $password = config('custom.ldap.adminpass');
    $hostnameSSL = config('custom.ldap.hostname');
    $udn= "cn=$adminuser, ou=serviceAccount, o=Telekom";
    $retdata = [];
    //	ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
    putenv('LDAPTLS_REQCERT=never');

    $con =  ldap_connect($hostnameSSL);
    if (is_resource($con)){
      if (ldap_set_option($con, LDAP_OPT_PROTOCOL_VERSION, 3)){
        ldap_set_option($con, LDAP_OPT_REFERRALS, 0);

        // try to bind / authenticate
        try{
        if (ldap_bind($con,$udn, $password)){

          // perform the search
          $ldres = ldap_search($con, 'ou=users,o=data', "$searchparam=$username");
          $ldapdata = ldap_get_entries($con, $ldres);
          // dd($ldapdata);
          // return $ldapdata;

          if($ldapdata['count'] == 0){
            $errorcode = 404;
            $errm = 'user not found';
          } else {
            foreach($ldapdata as $oneentry){
              if(is_array($oneentry)){

                $costcenter = $oneentry['ppcostcenter']['0'];
                $stid = $oneentry['cn']['0'];
                $sppersno = 0;
                $emptype = $oneentry['employeetype']['0'];
                if($emptype == 'Leasing-Staff'){
                  $subunit = $oneentry['pplocation']['0'];
                  $eprsno = $oneentry['employeenumber']['0'];
                  if(strlen($eprsno) > 6){
                    $eprsno = substr($eprsno, -6) + 0;
                  }
                } else {
                  // skip non leasing staff
                  continue;
                }


                array_push($retdata, [
                  'STAFF_NO' => $stid,
                  'NAME' => $oneentry['fullname']['0'],
                  'SUBUNIT' => $subunit,
                  'COST_CENTER' => $costcenter,
                  'SAP_NUMBER' => $oneentry['employeenumber']['0'],
                  'JOB_GRADE' => $oneentry['ppjobgrade']['0'],
                  'NIRC' => $oneentry['ppnewic']['0'],
                  'EMAIL' => $oneentry['mail']['0'],
                  'MOBILE_NO' => $oneentry['mobile']['0'],
                  'PERSNO' => $eprsno,
                  'ORI_PERSNO' => $oneentry['employeenumber']['0']
                ]);
              }
            }



            //$retdata = $ldapdata;
          }

        } else {
          $errorcode = 403;
          $errm = 'Invalid admin credentials.';
        }} catch(Exception $e) {
          $errorcode = 500;
          $errm = $e->getMessage();
        }

      } else {
        $errorcode = 500;
        $errm = "TLS not supported. Unable to set LDAP protocol version to 3";
      }

      // clean up after done
      ldap_close($con);

    } else {
      $errorcode = 500;
      $errm = "Unable to connect to $hostnameSSL";
    }

    return self::respond_json($errorcode, $errm, $retdata);
  }


  /**
   * get units and subunit under the LOB (departmentnumber)
   */
  function fetchSubUnits($lob){

    set_error_handler(array($this, 'errorHandler'));

    // do the ldap things
    $errm = 'success';
    $errorcode = 200;
    $adminuser = config('custom.ldap.adminuser');
    $password = config('custom.ldap.adminpass');
    $hostnameSSL = config('custom.ldap.hostname');

    $udn= "cn=$adminuser, ou=serviceAccount, o=Telekom";
    $retdata = [];
    //	ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
    putenv('LDAPTLS_REQCERT=never');

    $con =  ldap_connect($hostnameSSL);
    if (is_resource($con)){
      if (ldap_set_option($con, LDAP_OPT_PROTOCOL_VERSION, 3)){
        ldap_set_option($con, LDAP_OPT_REFERRALS, 0);

        // try to bind / authenticate
        try{
        if (ldap_bind($con,$udn, $password)){

          // perform the search
          $ldres = ldap_search(
            $con, 'ou=users,o=data', "departmentnumber=$lob",
            array('ppsuborg', 'pporgunit', 'pporgunitdesc', 'ppsuborgunitdesc'),
            0, 0
          );
          $ldapdata = ldap_get_entries($con, $ldres);

          // return $ldapdata;
          $sublist = [];

          if($ldapdata['count'] == 0){
            $errorcode = 404;
            $errm = 'LOB not found';
          } else {
            for ($i=0; $i < $ldapdata['count']; $i++) {
              $ppsuborg = (isset($ldapdata[$i]['ppsuborg']['0'])) ? $ldapdata[$i]['ppsuborg']['0'] : 1;
              $pporgunit = (isset($ldapdata[$i]['pporgunit']['0'])) ? $ldapdata[$i]['pporgunit']['0'] : 1;
              $pporgunitdesc = (isset($ldapdata[$i]['pporgunitdesc']['0'])) ? $ldapdata[$i]['pporgunitdesc']['0'] : 'Empty';
              $ppsuborgunitdesc = (isset($ldapdata[$i]['ppsuborgunitdesc']['0'])) ? $ldapdata[$i]['ppsuborgunitdesc']['0'] : 'Empty';

              if(in_array($ldapdata[$i]['ppsuborg']['0'], $sublist)){
                // already fetched. skip
              } else {
                array_push($sublist, $ldapdata[$i]['ppsuborg']['0']);
                $subdata = [
                  'ppsuborg' => $ppsuborg,
                  'pporgunit' => $pporgunit,
                  'pporgunitdesc' => $pporgunitdesc,
                  'ppsuborgunitdesc' => $ppsuborgunitdesc
                ];

                array_push($retdata, $subdata);
              }

            }
            //$retdata = $ldapdata;
          }

        } else {
          $errorcode = 403;
          $errm = 'Invalid admin credentials.';
        }} catch(Exception $e) {
          $errorcode = 500;
          $errm = $e->getMessage();
        }

      } else {
        $errorcode = 500;
        $errm = "TLS not supported. Unable to set LDAP protocol version to 3";
      }

      // clean up after done
      ldap_close($con);

    } else {
      $errorcode = 500;
      $errm = "Unable to connect to $hostnameSSL";
    }

    return self::respond_json($errorcode, $errm, $retdata);
  }

  function getSubordinate($username){

    set_error_handler(array($this, 'errorHandler'));

    // do the ldap things
    $errm = 'success';
    $errorcode = 200;
    $adminuser = config('custom.ldap.adminuser');
    $password = config('custom.ldap.adminpass');
    $hostnameSSL = config('custom.ldap.hostname');

    $udn= "cn=$adminuser, ou=serviceAccount, o=Telekom";
    $retdata = [];
    //	ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
    putenv('LDAPTLS_REQCERT=never');

    $stype = 'ppreporttoname';

    $con =  ldap_connect($hostnameSSL);
    if (is_resource($con)){
      if (ldap_set_option($con, LDAP_OPT_PROTOCOL_VERSION, 3)){
        ldap_set_option($con, LDAP_OPT_REFERRALS, 0);

        // try to bind / authenticate
        try{
        if (ldap_bind($con,$udn, $password)){

          // perform the search
          $ldres = ldap_search(
            $con, 'ou=users,o=data', "$stype=$username",
            array('sn', 'cn')
          );
          $ldapdata = ldap_get_entries($con, $ldres);

          if($ldapdata['count'] == 0){
            $errorcode = 404;
            $errm = 'user not found';
          } else {
            for ($i=0; $i < $ldapdata['count']; $i++) {
              $subdata = [
                'staff_no' => $ldapdata[$i]['cn']['0'],
                'staff_name' => $ldapdata[$i]['sn']['0']
              ];

              array_push($retdata, $subdata);
            }
          }

        } else {
          $errorcode = 403;
          $errm = 'Invalid admin credentials.';
        }} catch(Exception $e) {
          $errorcode = 500;
          $errm = $e->getMessage();
        }

      } else {
        $errorcode = 500;
        $errm = "TLS not supported. Unable to set LDAP protocol version to 3";
      }

      // clean up after done
      ldap_close($con);

    } else {
      $errorcode = 500;
      $errm = "Unable to connect to $hostnameSSL";
    }

    return self::respond_json($errorcode, $errm, $retdata);
  }

  private static function respond_json($code, $msg, $data = []){
    $curtime = date("Y-m-d h:i:sa");
		$retval = [
			'code' => $code,
      'status_code' => $code,
			'msg'  => $msg,
			'time' => $curtime,
			'data' => $data
		];

		return $retval;
  }

}
