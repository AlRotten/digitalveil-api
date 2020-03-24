<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Restriction;
use App\User;
use DB;

class RestrictionController extends Controller {
    //TODO check if application Exists too?
    //Create a new restriction with the given data 
    public function create(Request $request) {
        $response = array('code' => 400, 'error_msg' => []);

        if (isset($request)){
            if (!$request->id) array_push($response['error_msg'], 'User ID is required');
            if (!$request->appId) array_push($response['error_msg'], 'Application ID is required');

            //Check if user exists
            try {
                $user = User::where('user_id', '=', $request->user_id);

                if (!empty($user)) {
                    
                    try {
                        $restriction = new Restriction();
                        $restriction->max_time = $request->max_time;
                        $restriction->start_hour_restriction = $request->start_hour_restriction;
                        $restriction->finish_hour_restriction = $request->finish_hour_restriction;
                        $restriction->user_id = $request->id;
                        $restriction->application_id = $request->appId;
                        $restriction->save();
            
                        $response = array('code' => 200, 'restriction' => $restriction, 'msg' => 'Restriction created');
                    } catch (\Throwable $exception) {
                        $response = array('code' => 500, 'msg' => 'An error ocurred while trying to create the restriction' ,'error_msg' => $exception->getMessage());
                    }

                } else {
                    $response = array('code' => 400, 'error_msg' => "User not found");
                }

            } catch (\Throwable $exception) {
                $response = array('code' => 500, 'error_msg' => $exception->getMessage());
            }
            
        } else {
            $response['error_msg'] = 'Nothing to create';
            $response['code'] = 500;
        }
        
       return $response;
    }

    //All restriction get by a given user id
    public function getAll($id) {
        //Response array initiallization 
        $response = array('code' => 400, 'error_msg' => []);

        //Restriction get
        try {
            $restrictions = DB::table('restrictions')->select('user_id','application_id','max_time','start_hour_restriction','finish_hour_restriction')
                ->from('restrictions')
                ->where('user_id', $id)
                ->groupBy('user_id','application_id','max_time','start_hour_restriction','finish_hour_restriction')
                ->get();

                $response = array('code' => 200, 'restrictions' => [$restrictions] , 'msg' => 'Succesfull Operation','error_msg' => '');
        } catch (\Throwable $exception) {
            $response = array('code' => 400, 'restrictions' => '' , 'msg' => 'No usages found with the given user', 'error_msg' => $exception->getMessage());
        }

        return $response;
    }

    //Restriction get by a given user id and also an applicatioon id
    public function get($id, $appId) {
        //Response array initiallization 
        $response = array('code' => 400, 'error_msg' => []);

        //Restriction get
        try {
            $restrictions = Restriction::where('user_id', $id)->where('application_id', $appId)->get();
            $response = array('code' => 200, 'restrictions' => [$restrictions] , 'msg' => 'Succesfull Operation','error_msg' => '');
            
        } catch (\Throwable $exception) {
            $response = array('code' => 400, 'restrictions' => '' , 'msg' => 'No usages found with the given user', 'error_msg' => $exception->getMessage());
        }

        return $response;
    }

    //TODO check if application Exists too?
    //Modify fields of an specific restriction by ID 
    public function update(Request $request) {
        //Response array initillization
        $response = array('code' => 400, 'error_msg' => []);

        if (isset($request)){
            //Get restriction by ID
            try {
                $restriction = Restriction::where('user_id',$request->id)->first();
    
                if (!empty($restriction)) {
                    //Save new data
                    try {
                        $restriction->max_time = $request->max_time ? $request->max_time : $restriction->max_time;
                        $restriction->start_hour_restriction = $request->start_hour_restriction ? $request->start_hour_restriction : $restriction->start_hour_restriction;
                        $restriction->finish_hour_restriction = $request->finish_hour_restriction ? $request->finish_hour_restriction : $restriction->finish_hour_restriction;
                        $restriction->save();

                        $response = array('code' => 200, 'restriction' => $restriction, 'msg' => 'Restriction with the ID "' . $restriction->id . '" updated', 'error_msg' => '');    
                    } catch (\Throwable $exception) {
                        $response = array('code' => 500, 'msg' => "There was an error updating restriction with the ID " . $request->id, 'error_msg' => $exception->getMessage());
                    }
    
                    $response = array('code' => 200, 'restriction' => $restriction, 'msg' => 'Restriction updated - Restriction ID: "' . $application->id . '"', 'error_msg' => '');    
    
                }else{
                    $response['error_msg'] = 'No restriction to update';
                } 
    
            } catch (\Throwable $exception) {
                $response = array('code' => 400, 'msg' => 'No restriction founded with the ID "' . $request->id . '"', 'error_msg' => $exception->getMessage());
            }


        } else {
            $response['error_msg'] = 'No data received to update';
        }

        return $response;
    }

    //TODO - Still not implemented
    public function destroy(Request $request) {
        $restriction = restriction::where('id',$request->id)->first();
         if (isset($restriction)) {
            $restriction->delete();
            return response()->json(["Success" => "Se ha modificado la restriccion."]);
            
        }else{
            return response()->json(["Error" => "La restriccion no existe"]);
        }
    }

}
