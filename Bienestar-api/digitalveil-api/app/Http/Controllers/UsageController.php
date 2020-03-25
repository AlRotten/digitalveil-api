<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DateTime;
use DB;
use App\User;
use App\Usage;
use App\Application;

class UsageController extends Controller {
    //Function designed to store all data storaged in the CSV file
    public function storeCSV(Request $request) {
        //Response array initillization
        $response = array('code' => 400, 'error_msg' => []);

        //Array of the CSV
        $csv = array_map('str_getcsv' ,file('C:\Users\Alvaro\Desktop\Repositories\DigitalveilApi\digitalveil-api\Bienestar-api\digitalveil-api\storage\app\usage.csv'));
        $countArray = count($csv);

        //TODO - para que usuario?
        $email = $request->email;
        $user = User::where('email', '=' , $email)->first();

        //Loop throught the CSV file 
        for ($i=1; $i < $countArray ; $i++) { 
            //Apps data declaration
            $openDate = new DateTime ($csv[$i][0]);
            $application = $csv[$i][1];
            $openLocation = $csv[$i][3] . "," . $csv[$i][4];
            $i++;

            $closeDate =  new DateTime ($csv[$i][0]);

            //Get time difference in seconds.
            $timeUsed = $openDate->diff($closeDate);
            $timeUsed = $timeUsed->format('%S');

            //Date format reasignment
            $openDate = $openDate->format('Y-m-d H:i:s');
            $closeDate = $closeDate->format('Y-m-d H:i:s');

            //TODO
            //Get with currents application name 
            $existingApplication = Application::where('name', '=', $application)->first();

            //App exists on the data base
            if (!empty($existingApplication)) {

                //Call of check storaged function
                try {
                    $usageStoraged = Usage::where('day', date('Y-m-d H:i:s', strtotime($openDate)))->first();
                    //Check and create Usage in case that doesn't exist
                    $response = $this->checkStoragedUsage($usageStoraged, $openDate, $timeUsed, $openLocation, $user->id, $existingApplication->id, $application);
                } catch (\Throwable $exception) {
                    $response = array('code' => 500, 'applicationCode' => 500, 'msg' => 'There was an error getting the new usage for the application "' . $application->name . '"', 'error_msg' => $exception->getMessage());
                }

            } else {
                //Create Application if doesn't exist
                try {
                    $newApplication = new Application();
                    $newApplication->icon = "default.png";
                    $newApplication->name = $application;
                    $newApplication->save();    

                    $response = array('code' => 200, 'applicationCode' => 200, 'application' => $newApplication, 'msg' => 'Application' . $newApplication . 'created');
                } catch (\Throwable $exception) {
                    $response = array('code' => 500, 'applicationCode' => 500, 'msg' => 'There was an error creating the new application "' . $application . '"', 'error_msg' => $exception->getMessage());
                }

                //Call of check storaged function
                try {
                    $usageStoraged = Usage::where('day', date('Y-m-d H:i:s', strtotime($openDate)))->first();
                    //Check and create Usage in case that doesn't exist
                    $response = $this->checkStoragedUsage($usageStoraged, $openDate, $timeUsed, $openLocation, $user->id, $existingApplication->id, $application);
                } catch (\Throwable $exception) {
                    $response = array('code' => 500, 'applicationCode' => 500, 'msg' => 'There was an error getting the new usage for the application', 'error_msg' => $exception->getMessage());
                }
            }               

        }

        return $response;
    }

    //Check if a given usage already exists on the database and calls the create function if it doesn't exists 
    public function checkStoragedUsage($usageStoraged, $openDate, $timeUsed, $openLocation, $user_id, $application_id, $application_name){
        //Usage doesn't exist condition
        if ($usageStoraged == NULL) {
            //Create usage function call
            $usageResponse = $this->create($openDate,$timeUsed,$openLocation,$user_id, $application_id, $application_name);

            $response = array('code' => $usageResponse['code'], 'usage' => $usageResponse['usage'], 'msg' => $usageResponse['msg'], 'error_msg' => $usageResponse['error_msg']);
        } else {
            $response = array('code' => 400, 'usage' => $usageStoraged, 'msg' => 'An exact usage for one or more applications where founded (Application ID:' . $usageStoraged->id . ') ', 'error_msg' => $usageStoraged['error_msg']);                
        }

        return $response;
    }

    //Create a new usage with the given data 
    public function create($day, $use_time, $location, $user_id, $application_id, $application) {
        //Response array initiallization 
        $response = array('code' => 400, 'error_msg' => []);
        //Usage create
        try {
            $usage = new Usage();
            $usage->day = $day;
            $usage->use_time = $use_time;
            $usage->location = $location;
            $usage->user_id = $user_id;
            $usage->application_id = $application_id;
            $usage->save();

            $response = array('code' => 200, 'usage' => $usage, 'msg' => 'New usage added to "' . $application . '"', 'error_msg' => '');    
        } catch (\Throwable $exception) {
            $response = array('code' => 500, 'usage' => '' , 'msg' => '','error_msg' => 'There was an error creating usage of the application ' . $application, 'error_msg' => $exception->getMessage());
        }

        return $response;
    }

    /*-------------- BASIC FUNCTIONS --------------*/ 

    //Function designed to create only one usage record 
    public function createOne(Request $request) {
        //Response array initiallization 
        $response = array('code' => 400, 'error_msg' => []);

        //Check if function receives data
        if (isset($request)) {
            try {
                //TODO - $request->application_id is not dynamic
                $usage = $this->create($request->day, $request->use_time, $request->location, $request->user_id, $request->application_id, $request->application);
    
                $response = array('code' => 200, 'usage' => $usage, 'msg' => 'New usage added to "' . $request->application . '"', 'error_msg' => '');    
            } catch (\Throwable $exception) {
                $response = array('code' => 500, 'usage' => '' , 'msg' => '','error_msg' => 'There was an error creating usage of the application ' . $request->application, 'error_msg' => $exception->getMessage());
            }
        } else {
            $response = array('code' => 400, 'error_msg' => 'No data received');
        }

        return $response;
    }

    //Usage get by a given user id
    public function get($id) {
        //Response array initiallization 
        $response = array('code' => 400, 'error_msg' => []);

        //Usage get
        try {
            $usages = DB::table('usage')->select('user_id','application_id','day', DB::raw("SUM(use_time) as totalTime"))
                ->from('usage')
                ->where('user_id', $id)
                ->groupBy('user_id','application_id','day')
                ->get();

                $response = array('code' => 200, 'usages' => [$usages] , 'msg' => '','error_msg' => '');
        } catch (\Throwable $exception) {
            $response = array('code' => 400, 'usage' => '' , 'msg' => 'No usages found with the given user', 'error_msg' => $exception->getMessage());
        }

        return $response;
    }

    //Get a certain user locations and more details of every app from a given email  
    public function getMapData(Request $request) {
        //Get user by email
        try {    
            $user = User::where('email',$request->email)->first();

            $response = array('code' => 200, 'userResponse' => $user , 'userMsg' => 'Successfully get user');
            
            //Get location of apps by user id
            try {
                $usage = new usage();        
                $usage = $this->getLocation($user->id);
                $response = array('code' => 200, 'usage' => $usage , 'msg' => 'Successfully get location of all apps');
            } catch (\Throwable $exception) {
                $response = array('code' => 500, 'usage' => '' , 'msg' => 'There was an error getting locations with that user ID', 'error_msg' => $exception->getMessage());
            }

        } catch (\Throwable $exception) {
            $response = array('code' => 500, 'usage' => '' , 'msg' => 'No users found with the given email', 'error_msg' => $exception->getMessage());
        }

        return $response;
    }

    //Get usage and locations from them for an specific user
    public function getLocation($user_id) {
        $usages = DB::table('usage')->select('user_id','application_id','day','location', DB::raw("SUM(use_time) as totalTime"))
        ->from('usage')
        ->where('user_id', $user_id)
        ->groupBy('user_id','application_id','day','location')
        ->get();  

        return $usages;
    }

    //Get a certain user usages of every app by a given email
    public function getAll(Request $request) {
        $response = array('code' => 400, 'error_msg' => []);

        //Received data condition
        if (isset($request) && $request->email != '') {
            //Check if user exists and get all usages
            try {
                $user = User::where('email', '=', $request->email)->first();
                //User exists condition
                if (!empty($user)) {
                    //Get all usages
                    try {
                        $usages = Usage::where('user_id', '=', $user->id)->get();
                        $response = array('code' => 200, 'usages' => [$usages] ,'msg' => 'Successful Get of all usage locations');

                    } catch (\Throwable $exception) {
                        $response = array('code' => 500,'error_msg' => $exception->getMessage());
                    }

                } else {
                    $response['error_msg'] = 'User not found';
                }

            } catch (\Throwable $exception) {
                $response = array('code' => 500, 'error_msg' => $exception->getMessage());
            }

        } else {
            $response['error_msg'] = 'No email received';
        }
        
        return $response; 
    }


    /*-------------- STATISTICS FUNCTION --------------*/ 

    //Get an average use of an app by a certain user and application
    public function getAverage($id , $appId) {
        //Response array initiallization 
        $response = array('code' => 400, 'error_msg' => []);

        try {
            $average = Usage::where('user_id', '=', $id)->where('application_id', '=', $appId)->get();
            $response = array('code' => 200, 'average' => $average , 'msg' => 'Successfully get average use of the selected application', 'averageHours' => '', 'averageMinutes' => '');

            if (isset($average)) {
                $totalAverage = 0;
                foreach ($average as $e) {
                    $totalAverage += $e->use_time;
                }
    
                $response = UsageController::manageTime($totalAverage, $response);
            }

        } catch (\Throwable $exception) {
            $response = array('code' => 500, 'average' => '' , 'msg' => 'There was an error getting the average of the selected application', 'error_msg' => $exception->getMessage());
        }

        

        return $response;
    }

    //Receives the use time of an specific app in minutes an parse it to hours if its 
    public function manageTime($totalAverage, $response) {
        //Response array initiallization
        $newResponse = array('code' => 400, 'error_msg' => []);

        //Received parameters condition
        if (isset($totalAverage) && $totalAverage != ''){
            if ($totalAverage >= 60) {
                $totalAverage = $totalAverage/60;
            }

            //Has decimals condition
            if (is_float($totalAverage)) {
                //Integer part of the number
                $averageHours = (int) $totalAverage;
                //Decimal part of the number
                $averageMinutes  = $totalAverage - $averageHours;
                //Format the decimal part to minutes and round it
                $averageMinutes = round($averageMinutes * 60, 2);
            }

            $newResponse = array('code' => 200, 'average' => $response['average'] , 'msg' => 'Successfully get average use of the selected application', 'averageCode' => 200, 'averageHours' => $averageHours, 'averageMinutes' => $averageMinutes);
        } else {
            $newResponse = array('code' => 200, 'average' => $response['average'] , 'msg' => 'There was an error formatting the average of the selected application', 'averageCode' => 500, 'error_msg' => $exception->getMessage());
        }

        return $newResponse;
    }

}


