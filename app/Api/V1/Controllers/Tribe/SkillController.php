<?php

namespace App\Api\V1\Controllers\Tribe;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CommonSkillset;
use App\Models\PersonalSkillset;

class SkillController extends Controller
{
    public function getSkills(Request $req)
    {
        $skillset = CommonSkillset::all('id', 'name');
        $competency = [
         ['id'=> 1, 'descr'=>'Beginner'],
         ['id'=>2, 'descr'=>'Intermediate'],
         ['id'=>3, 'descr'=>'Expert']];

        $result = [
         'competency'=>$competency,
         'skillset'=>$skillset
         
      ];


        return $result;
    }
    public function getUsersBySkills(Request $req)
    {
        $users = User::query();
  

        $skills = $req->skills;
        /*
        foreach ($skills as $key => $value) {
         $users->whereIn('id', PersonalSkillset::where('common_skill_id', $value['skill'])->where('level', '=', $value['lvl'])->pluck('staff_id'));
     }
     */

        foreach ($skills as $key => $value) {
            if ($value['rule'] == 'and') {
                $users->where(function ($query) use ($value) {
                    $query->whereIn(
                        'id',
                        PersonalSkillset::where('common_skill_id', $value['skill'])
                    ->where('level', '=', $value['lvl'])
                    ->pluck('user_id')
                    );
                });
            }

            if ($value['rule'] == 'or') {
                $users->whereOr(function ($query) use ($value) {
                    $query->whereIn(
                        'id',
                        PersonalSkillset::where('common_skill_id', $value['skill'])
                    ->where('level', '=', $value['lvl'])
                    ->pluck('user_id')
                    );
                });
            }
        }

        $usr = $users->get(['id','persno']);

        $content = [];


        $result = "";
        //  dd($users->get());
        foreach ($users->get() as $key => $value) {
            $isi = ['user_id'=> $value->id,'persno'=>$value->persno,
            'skills'=>$value->getPersonalSkillset()
        ];
            array_push($content, $isi);
        }
        
        $result= ["users"=>$content];
  
    
        return $content;
    }

    public function getUsersBySkills2(Request $req)
    {
        $users = User::query();
  

        $skills = $req->skills;
        /*

        {
  "id1": 1,
  "level1": 2,
  "cond1": "or",
  "id2": 2,
  "level2": 2,
  "id3": 3,
  "cond2": "or",
  "level3": 2,
  "id4": 4,
  "level4": 2,
  "cond": "and"
}

SELECT
    *
FROM  users
WHERE
  (id IN (SELECT staff_id
                FROM trust.personal_skillsets
                WHERE common_skill_id = 77
                AND level = '2'
            )
    OR id IN (SELECT staff_id
                FROM trust.personal_skillsets
                WHERE common_skill_id = 82
                  AND level = '3')
     )
or (id IN (SELECT staff_id
                FROM trust.personal_skillsets
                WHERE common_skill_id = 77
                AND level = '2'
            )
    OR id IN (SELECT staff_id
                FROM trust.personal_skillsets
                WHERE common_skill_id = 82
                  AND level = '3')
     )

     */


        /*

                $users->where(function ($query) use ($req) {
                    $query->whereIn(
                        'id',
                        PersonalSkillset::where('common_skill_id', $req->id1)
                            ->where('level', '=', $req->level1)
                            ->pluck('staff_id')
                    );


                });
        */
        //dd($req->cond2);
        $users->whereRaw(
            "(id IN (SELECT user_id
            FROM personal_skillsets
            WHERE common_skill_id = ".$req->id1.
            "  AND status not in ('D') " . 
            ($req->level1==0 ? "" : " AND level = ".$req->level1)
            ."
        )
        ".$req->cond1." id IN (SELECT user_id
            FROM personal_skillsets
            WHERE common_skill_id = ".$req->id2.
            ($req->level2 == '0' ? "" : " AND level = ".$req->level2)
            . ")
        )
        ".$req->cond." (id IN (SELECT user_id
            FROM personal_skillsets
            WHERE common_skill_id = ".$req->id3.
            "  AND status not in ('D') " . 
            ($req->level3 == '0' ? "" : " AND level = ".$req->level3)
            ."
        )
        ".$req->cond2." id IN (SELECT user_id
            FROM personal_skillsets
            WHERE common_skill_id = ".$req->id4.
            "  AND status not in ('D') " . 
            ($req->level4 == '0' ? "" : " AND level = ".$req->level4)
            .") 
         )"
        );
            
       


      //   dd($users->toSql());
        

        $usr = $users->get(['id','persno']);

        

        $content = [];


        $result = "";
        //  dd($users->get());
        foreach ($users->get() as $key => $value) {
            $isi = ['user_id'=> $value->id,'persno'=>$value->persno,
            'skills'=>$value->getPersonalSkillset()
        ];
            array_push($content, $isi);
        }
        
        $result= ["users"=>$content];
  
    
        return $content;
    }

    public function getUserSkills(Request $req)
    {
        $persno = $req->persno;
        
        $user = User::where('persno', $persno)->first();
     
        $usr  = User::where('id', $user->id)->get(['id','persno','staff_no','name']);
    
       
        $skills = $user->getPersonalSkillset2();

        return ["user"=>$usr, "skills" => $skills];
    }
}
