<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Application;

class ApplicationController extends Controller {
    //TODO check if application Exists too?
    //Create a new application with the given data 
    public function create(Request $request) {
        $response = array('code' => 400, 'error_msg' => []);

        if (isset($request)) {
            if (!$request->name) array_push($response['error_msg'], 'Name is required');
            if (!$request->icon) array_push($response['error_msg'], 'Icon is required');
            if (!count($response['error_msg']) > 0) {

                //Check if user exists
                try {
                    $user = User::where('email', '=', $request->email);

                    if (empty($application)) {
                        try {
                            $application = new Application();
                            $application->name = $request->name;
                            $application->icon = $request->icon;
                            $application->save();

                            $response = array('code' => 200, 'application' => $application, 'msg' => 'Application created');
                        } catch (\Exception $exception) {
                            $response = array('code' => 500, 'error_msg' => $exception->getMessage());
                        }

                    } else {
                        $response = array('code' => 400, 'error_msg' => "User not found");
                    }

                } catch (\Throwable $exception) {
                    $response = array('code' => 500, 'error_msg' => $exception->getMessage());
                }

            }

        } else {
            $response['error_msg'] = 'Nothing to create';
        }

        return $response;
    }

    //Modify fields of an specific application by name 
    public function update(Request $request) {
        //Response array initillization
        $response = array('code' => 400, 'error_msg' => []);

        if(isset($request)) {
            //Get application by name
            try {
                $application = Application::where('name', $request->name)->first();

                if(!empty($application)) {
                    //Save new data
                    try {
                        $application->name = $request->name ? $request->new_name : $application->name;
                        $application->icon = $request->icon ? $request->icon : $application->icon;
                        $application->save();

                        $response = array('code' => 200, 'application' => $application, 'msg' => 'usage updated for "' . $application->name . '"', 'error_msg' => '');    
                    } catch (\Throwable $exception) {
                        $response = array('code' => 500, 'msg' => "There was an error updating application " . $request->name, 'error_msg' => $exception->getMessage() . ' ' . $exception->getLine() . ' ' . $exception->getFile());
                    }

                } else {
                    $response['error_msg'] = 'No app to update';
                }
                
            } catch (\Throwable $exception) {
                $response = array('code' => 400, 'msg' => 'No application founded with name "' . $request->name . '"', 'error_msg' => $exception->getMessage());
            }

        } else {
            $response['error_msg'] = 'Nothing to update';
        }

        return $response;
    }

    //Get all applications registered
    public function getAll() {
        //Response array initillization
        $response = array('code' => 400, 'error_msg' => []);

        //Get All Applications
        try {
            $applications = Application::all();
            $response = array('code' => 200, 'applications' => [$applications] ,'msg' => 'Successful Get of all apps');
        } catch (\Throwable $exception) {
            $response = array('code' => 500, 'msg' => 'Get of apps failed' ,'error_msg' => $exception->getMessage());
        }

        return $response;        
    }

    //TODO - still not inplemented
    public function destroy(Request $request) {
        $application = application::where('name',$request->name)->first();
        if (isset($application)) {
            $application->delete();
        
            return response()->json(["Success" => "Se ha borrado la aplicacion."]);
        }else{
            return response()->json(["Error" => "La aplicacion no existe"]);
        }
    }
}
