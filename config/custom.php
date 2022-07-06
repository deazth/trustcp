<?php

return [


    'ldap' => [
      'hostname' => env('TMLDAP_HOSTNAME'),
      'adminuser' => env('TMLDAP_ADMINUSER'),
      'adminpass' => env('TMLDAP_ADMINPASS'),
    ] ,

    'tmoip_token' => env('TMOIP_TOKEN'),

    'era' => [
      'api_key' => env('APIGATE_KEY'),
      'api_uri' => env('APIGATE_URI'),
    ] ,
    'trust_reserve_dur' => env('TRUST_RESERVE_DUR', 15),

    'ga_key' => env('GA_KEY', 'G-F7SZ6P7TBT'),
    'neo_base_url' => env('NEO_API_URI'),

];
