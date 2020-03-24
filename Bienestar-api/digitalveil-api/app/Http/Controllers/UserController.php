<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Mail;

//Controller to manage all user functionality from the app
class UserController extends Controller {

    //Create an user with relative data
    public function create(Request $request) {
        //Array initiallization
        $response = array('code' => 400, 'error_msg' => []);

            //Data check
            if (isset($request)) {
                if (!$request->email) array_push($response['error_msg'], 'Email is required');
                if (!$request->password) array_push($response['error_msg'], 'Password is required');
                if (!$request->name) array_push($response['error_msg'], 'Name is required');
                
                //No error condition - basic validation
                if (!count($response['error_msg']) > 0) {
                    try {
                        $user = User::Where('email', '=', $request->email);

                        //Check if the email already exist
                        if(!$user->count()) {
                            try {
                                $user = new User();
                                $user->name = $request->name;
                                $user->password = hash('sha256', $request->password);
                                $user->email = $request->email;
                                $user->save();
                                
                                $response = array('code' => 200, 'user' => $user, 'msg' => 'User created');
                            } catch (\Throwable $exception) {
                                $response = array('code' => 500, 'error_msg' => $exception->getMessage());
                            }
                        } else {
                            $response = array('code' => 400, 'error_msg' => "Email already registered");
                        }
                    } catch (\Throwable $exception) {
                        $response = array('code' => 500, 'error_msg' => $exception->getMessage());
                    }
                }
                
        } else {
            $response['error_msg'] = 'Nothing to create';
        }   

        return response($response,$response['code']);
    }

    //Get an specific user by ID
    public function get($id) {
        //Array initiallization
        $response = array('code' => 400, 'error_msg' => []);

        //Data check
        if (isset($id)) {
            try {
                $user = User::find($id);
            } catch (\Exception $exception) {
                $response = array('code' => 500, 'error_msg' => $exception->getMessage());
            }

            if (!empty($user)) {
                $response = array('code' => 200, 'user' => $user);
            } else {
                $response = array('code' => 404, 'error_msg' => ['User not found']);
            }
        }

       return response($response,$response['code']);
    }

    //Modify fields of an specific user by ID 
    public function update(Request $request, $id) {
        //Array initiallization
        $response = array('code' => 400, 'error_msg' => []);
        
        //Data check
        if (isset($request) && isset($id)){
            try {
                $user = User::find($id);

                if (!empty($user)) {
                    try {
                        $user->email = $request->email ? $request->email : $user->email;
                        $user->password = $request->password ? hash('sha256', $request->password) : $user->password;
                        $user->name = $request->name ? $request->name : $user->name;
                        $user->save();
                        $response = array('code' => 200, 'msg' => 'User updated');
                    } catch (\Exception $exception) {
                        $response = array('code' => 500, 'error_msg' => $exception->getMessage());
                    }
                }
            } catch (\Throwable $th) {
                $response = array('code' => 500, 'error_msg' => $exception->getMessage());
            }

        } else {
            $response['error_msg'] = 'Nothing to update';
        }

       return response($response,$response['code']);
    }

    //TODO - Revisar si deprecated
    //Delete an specific user by ID
    public function delete(Request $request, $id) {
        //Array initiallization
        $response = array('code' => 400, 'error_msg' => []);

        //Data check
        if (isset($request) && isset($id)) {

            try {
                $user = User::find($id);

                if (!empty($user)) {
                    try {
                        $user->active = 0;
                        $user->save();
                        $response = array('code' => 200, 'msg' => 'User deleted');

                    } catch (\Exception $exception) {
                        $response = array('code' => 500, 'error_msg' => $exception->getMessage());
                    }
        
                } else {
                    $response = array('code' => 401, 'error_msg' => 'Unautorized');
                }

            } catch (\Throwable $exception) {
                $response = array('code' => 500, 'error_msg' => $exception->getMessage());
            }
        }
       
       return response($response,$response['code']);
    }

    //User login function
    public function login(Request $request) {
        //Array initiallization
        $response = array('code' => 400, 'error_msg' => []);

        //Data check
        if ($request->email && $request->password) {
            //TODO - TO TEST
            try {
                $user = User::where('email', $request->email)->first();

                //Email check
                if (!empty($user)) {
                    //Password check
                    if ($user->password === hash('sha256', $request->password)) {
                        try {
                            //TODO Columna TOKEN en base de datos
                            // $token = uniqid() . $user->email;
                            // $user->token = hash('sha256', $token);
                            $user->save();
                            $response = array('code' => 200, 'user' => $user, 'msg' => 'Login successful');
                        } catch (\Exception $exception) {
                            $response = array('code' => 500, 'error_msg' => $exception->getMessage());
                        }
                    } else {
                        $response['error_msg'] = 'Wrong password';
                    }
                } else {
                    $response['error_msg'] = 'User not found';
                }
            } catch (\Throwable $th) {
                $response = array('code' => 500, 'error_msg' => $exception->getMessage());
            }
            

        } else {
            $response['error_msg'] = 'Email and password are required';
        }

        return response($response,$response['code']);
    }

    //TODO - NOT INPLEMENTED
    //Mail sender function
    public function sendMail(Request $request) {
        //Array initiallization
        $response = array('code' => 400, 'error_msg' => []);

        //Data check
        if (isset($request)){

            try {
                //User object
                $user = User::where('email', '=', $request->email)->first();

                //Checking if the email exist
                if (!empty($user)) {
                    //New password of the user
                    $newPass = $this->rand_string(8);
                    //User data that will be used on the email
                    $email = $user->email;
                    $name = $user->name;
        
                    //Hash the new password  
                    $password = hash('sha256', $newPass);
        
                    //Save the new password to the user
                    $user->password = $password;
                    $user->save();
        
                    //Email sender and relative data 
                    $data = [
                        'name' => $name,
                        'password' => $newPass,
                    ];
        
                    $subject = "Digital Veil App - Reset password request";
                    $from =  env("MAIL_USERNAME");
        
                    try {
                        //Send Mail
                        $mailMsg = Mail::send('mail', ["data" => $data], function ($msg) use ($subject, $email, $from) {
                            $msg->from($from, "🚥 Digital Veil App 🚥");
                            $msg->subject($subject);
                            $msg->to($email);
                        });
                        $response = array('code' => 200, 'error_msg' => 'Email sended!');
                    } catch (\Throwable $exception) {
                        $response = array('code' => 500, 'error_msg' => $exception->getMessage() . ' ' . $exception->getLine(). ' ' . $exception->getFile());
                    }

                } else {
                    $response = array('code' => 400, 'error_msg' => 'User not found');
                }

            } catch (\Throwable $exception) {
                $response = array('code' => 500, 'error_msg' => $exception->getMessage());
            }
    
        } else {
            $response = array('code' => 400, 'error_msg' => 'No email received');
        }

       return response($response,$response['code']);
    }

    //Pass generator
    function rand_string($length) {
        $chars = "abcdefghijklmnopqrstuvwxyz1234567890";
        return substr(str_shuffle($chars), 0, $length);
    }

}
