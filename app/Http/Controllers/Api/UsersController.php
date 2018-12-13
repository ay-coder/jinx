<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Models\Access\User\User;
use Response;
use Carbon;
use App\Repositories\Backend\User\UserContract;
use App\Repositories\Backend\UserNotification\UserNotificationRepositoryContract;
use App\Http\Transformers\UserTransformer;
use App\Http\Utilities\FileUploads;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuthExceptions\JWTException;
use App\Http\Controllers\Api\BaseApiController;
use Auth;
use App\Repositories\Backend\Access\User\UserRepository;
use Illuminate\Support\Facades\Validator;
use App\Models\Connections\Connections;
use App\Library\Push\PushNotification;
use App\Models\Categories\Categories;
use URL;
use DB;
use App\Models\Templates\Templates;
use App\Models\Settings\Settings;
use App\Models\Images\Images;
use App\Models\UserInterests\UserInterests;
use App\Models\BlockUsers\BlockUsers;
use App\Models\SocialImages\SocialImages;

class UsersController extends BaseApiController
{
    protected $userTransformer;
    /**
     * __construct
     */
    public function __construct()
    {
        $this->userTransformer  = new UserTransformer;
        
    }

    /**
     * Login request
     * 
     * @param Request $request
     * @return type
     */
    public function login(Request $request) 
    {
        $credentials = $request->only('username', 'password');

        try {
            // verify the credentials and create_function(args, code) a token for the user
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'error'     => 'Invalid Credentials',
                    'message'   => 'No User Found for given details',
                    'status'    => false,
                    ], 401);
            }
        } catch (JWTException $e) {
            // something went wrong
            return response()->json([
                    'error'     => 'Somethin Went Wrong!',
                    'message'   => 'Unable to Generate Token!',
                    'status'    => false,
                    ], 500);
        }
        

        if($request->get('device_token'))
        {
            $user = Auth::user();
            $user->device_token = $request->get('device_token');


            if($request->has('spotify_token'))
            {
                $user->spotify_token = $request->get('spotify_token');
            }

            if($request->has('insta_token'))
            {
                $user->insta_token = $request->get('insta_token');
            }

            $user->save();
        }

        $user = Auth::user()->toArray();
        $userData = array_merge($user, ['token' => $token]);

        $responseData = $this->userTransformer->transform((object)$userData);

        return $this->successResponse($responseData);
    }

    /**
     * Logout request
     * @param  Request $request
     * @return json
     */
    public function logout(Request $request) 
    {
        $userInfo   = $this->getApiUserInfo();
        $user       = User::find($userInfo['userId']);

        $user->device_token = '';

        if($user->save()) 
        {
            $successResponse = [
                'message' => 'User Logged out successfully.'
            ];

            return $this->successResponse($successResponse);
        }

        return $this->setStatusCode(200)->failureResponse([
            'reason' => 'User Not Found !'
        ], 'User Not Found !');
    }

    /**
     * socialCreate
     *
     * @param Request $request
     * @return string
     */
    public function socialCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'social_token'      => 'required'
        ]);

        if($validator->fails())
        {
            $messageData = '';
            foreach($validator->messages()->toArray() as $message)
            {
                $messageData = $message[0];
            }
            return $this->failureResponse($validator->messages(), $messageData);
        }

        $user = User::where([
            'social_token' => $request->get('social_token')
        ])->first();

        if(isset($user) && $user->id)
        {
            return $this->socialLogin($request);
        }

        $validator = Validator::make($request->all(), [
            'name'          => 'required',
            'email'         => 'required|unique:users|max:255',
            'social_token'  => 'required|unique:users|max:255'
        ]);

        if($validator->fails())
        {
            $messageData = '';
            foreach($validator->messages()->toArray() as $message)
            {
                $messageData = $message[0];
            }
            return $this->failureResponse($validator->messages(), $messageData);
        }

        $repository = new UserRepository;
        $input      = $request->all();
        $input      = array_merge($input, [
            'signup_by'   => 1,
            'profile_pic' => 'default.png'
        ]);

        if($request->file('profile_pic'))
        {
            $imageName  = rand(11111, 99999) . '_user.' . $request->file('profile_pic')->getClientOriginalExtension();
            if(strlen($request->file('profile_pic')->getClientOriginalExtension()) > 0)
            {
                $request->file('profile_pic')->move(base_path() . '/public/uploads/user/', $imageName);
                $input = array_merge($input, ['profile_pic' => $imageName]);
            }
        }
        
        $user = $repository->createSocialUserStub($input);
        if($user)
        {
            $from = new \DateTime($input['birthdate']);
            $to   = new \DateTime('today');
            $age  = $from->diff($to)->y;

            Settings::create([
                'user_id'           => $user->id,
                'ghost_mode'        => 0,
                'interested'        => isset($input['gender']) && $input['gender'] == 'Female' ? 'Male' : 'Female',
                'age_start_range'   => $age - 5,
                'age_end_range'     => $age + 5,
                'distance'          => 5
            ]);
            Auth::loginUsingId($user->id, true);

            $user           = Auth::user()->toArray();
            $token          = JWTAuth::fromUser(Auth::user());
            $userData       = array_merge($user, ['token' => $token]);
            $responseData   = $this->userTransformer->transform((object)$userData);
            return $this->successResponse($responseData);
        }
        return $this->setStatusCode(200)->failureResponse([
            'reason' => 'Invalid Inputs'
            ], 'Something went wrong !');
    }

    /**
     * Login request
     *
     * @param Request $request
     * @return type
     */
    public function socialLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'social_token'      => 'required'
        ]);

        if($validator->fails())
        {
            $messageData = '';
            foreach($validator->messages()->toArray() as $message)
            {
                $messageData = $message[0];
            }
            return $this->failureResponse($validator->messages(), $messageData);
        }


        $user = User::where([
            'social_token'=> $request->get('social_token')
        ])->first();

        if(isset($user) && $user->id)
        {
            Auth::loginUsingId($user->id, true);

            if($request->get('device_token'))
            {
                $user = Auth::user();
                $user->device_type  = $request->get('device_type');
                $user->device_token = $request->get('device_token');

                if($request->has('spotify_token'))
                {
                    $user->spotify_token = $request->get('spotify_token');
                }

                if($request->has('latitude'))
                {
                    $user->latitude = $request->get('latitude');
                }

                if($request->has('longitude'))
                {
                    $user->longitude = $request->get('longitude');
                }

                if($request->has('insta_token'))
                {
                    $user->insta_token = $request->get('insta_token');
                }

                $user->save();
            }

            $user       = Auth::user()->toArray();
            $token      = JWTAuth::fromUser(Auth::user());
            $userData   = array_merge($user, ['token' => $token]);
            $responseData = $this->userTransformer->transform((object)$userData);

            return $this->successResponse($responseData);
        }

        return response()->json([
            'error'     => 'Invalid Credentials',
            'message'   => 'No User Found for given details',
            'status'    => false,
            ], 200);
    }


    /**
     * Config
     * 
     * @param  Request $request [description]
     * @return json
     */
    public function config(Request $request)
    {
        $successResponse = [
            'support_number'        => '99999999',
            'app_url'               => route('frontend.privacy-policy'),
            'rateus_url'            => route('frontend.privacy-policy'),
            'privacy_policy_url'    => route('frontend.privacy-policy'),
            'about_us_url'          => route('frontend.privacy-policy'),
            'terms_conditions_url'  => route('frontend.privacy-policy'),
            'help_support'          => route('frontend.privacy-policy')
        ];

        return $this->successResponse($successResponse);
    }

    /**
     * Create
     *
     * @param Request $request
     * @return string
     */
    public function create(Request $request)
    {
        $repository = new UserRepository;
        $input      = $request->all();
        $signup_by  = $request->has('email') ? 1 : 2;
        $input      = array_merge($input, [
            'signup_by'     => $signup_by,
            'profile_pic'   => 'default.png'
        ]);



        if($request->file('profile_pic'))
        {
            $imageName  = rand(11111, 99999) . '_user.' . $request->file('profile_pic')->getClientOriginalExtension();
            if(strlen($request->file('profile_pic')->getClientOriginalExtension()) > 0)
            {
                $request->file('profile_pic')->move(base_path() . '/public/uploads/user/', $imageName);
                $input = array_merge($input, ['profile_pic' => $imageName]);
            }
        }

        $validator = Validator::make($request->all(), [
            'username'  => 'required|unique:users|max:255',
            'name'      => 'required',
            'password'  => 'required',
        ]);

        if($validator->fails()) 
        {
            $messageData = '';

            foreach($validator->messages()->toArray() as $message)
            {
                $messageData = $message[0];
            }
            return $this->failureResponse($validator->messages(), $messageData);
        }

        if($request->has('email'))
        {
            $isExist = User::where('email', $request->get('email'))->first();

            if(isset($isExist->id) && count($isExist))
            {
               return $this->setStatusCode(200)->failureResponse([
                'reason' => 'Email Already Exists !'
                ], 'Email Already Exists !'); 
            }
        }

        $user = $repository->createUserStub($input);

        if($user)
        {
            Auth::loginUsingId($user->id, true);

            $credentials = [
                'username'  => $input['username'],
                'password'  => $input['password']
            ];
            
            $token          = JWTAuth::attempt($credentials);
            $user           = Auth::user()->toArray();
            $userData       = array_merge($user, ['token' => $token]);  
            $responseData   = $this->userTransformer->transform((object)$userData);

            /*Connections::create([
                'user_id'           => $user['id'],
                'other_user_id'     => 1,
                'requested_user_id' => $user['id'],
                'is_accepted'       => 1
            ]);*/

            return $this->successResponse($responseData);
        }

        return $this->setStatusCode(200)->failureResponse([
            'reason' => 'Invalid Inputs'
            ], 'Something went wrong !');
    }
    
    /**
     * Forgot Password
     *
     * @param Request $request
     * @return string
     */
    public function forgotpassword(Request $request)
    {
        if($request->get('email'))
        {
            $userObj = new User;

            $user = $userObj->where('email', $request->get('email'))->first();

            if($user)
            {
                $password       = str_random(6);
                $user->password = bcrypt($password);
                if($user->save() && 1 == 2 )  
                {
                    $to = $user->email;
                    $subject = "Reset Password - Gratitude";

                    $message = "
                    <html>
                    <head>
                    <title>Reset Password Gratitude App</title>
                    </head>
                    <body>
                    <p>
                        Hello $user->name,
                    </p>
                    <p>
                     Please use this password for Login <strong>$password </strong> Let us know if you have any concern.
                    </p>
                    <p>
                    Spottr
                    
                    </p>
                    </body>
                    </html>
                    ";

                    // Always set content-type when sending HTML email
                    $headers = "MIME-Version: 1.0" . "\r\n";
                    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

                    // More headers
                    $headers .= 'From: <info@grattitude.com>' . "\r\n";
                    if(mail($to, $subject, $message, $headers))
                    {
                        $successResponse = [
                            'message' => 'Reset Password Mail send successfully.'
                        ];
                    }
                }

                // Need to Remove
                $successResponse = [
                    'message' => 'Reset Password Mail send successfully.'
                ];
                return $this->successResponse($successResponse, 'Reset Password Mail send successfully.');
            }

            return $this->setStatusCode(200)->failureResponse([
                'error' => 'User not Found !'
            ], 'User not Found !');
        }

        return $this->setStatusCode(200)->failureResponse([
            'reason' => 'Invalid Inputs'
        ], 'Something went wrong !');
    }

    /**
     * Get User Profile
     * 
     * @param Request $request
     * @return json
     */
    public function getUserProfile(Request $request)
    {
        if($request->get('user_id'))
        {
            $userObj            = new User;
            $connectionModel    = new Connections;

            $user           = $userObj->with([
                'posts', 'post_requests', 'user_posts', 'connections', 'user_notifications', 'my_connections', 'accepted_connections'
            ])->find($request->get('user_id'));
            $userInfo       = $this->getAuthenticatedUser();
            
            if($user)
            {
                $responseData = $this->userTransformer->userInfo($user);
                
                return $this->successResponse($responseData);
            }

            return $this->setStatusCode(200)->failureResponse([
                'error' => 'User not Found !'
            ], 'Something went wrong !');
        }

        return $this->setStatusCode(200)->failureResponse([
            'reason' => 'Invalid Inputs'
        ], 'Something went wrong !');     
    }

    /**
     * Update User Profile
     * 
     * @param Request $request
     * @return json
     */
    /*public function updageUserProfile(Request $request)
    {
        $headerToken = request()->header('Authorization');

        if($headerToken)
        {
            $token      = explode(" ", $headerToken);
            $userToken  = $token[1];
        }
        
        $userInfo   = $this->getApiUserInfo();
        $repository = new UserRepository;
        $input      = $request->all();
        
        if($request->file('profile_pic'))
        {
            $imageName  = rand(11111, 99999) . '_user.' . $request->file('profile_pic')->getClientOriginalExtension();
            if(strlen($request->file('profile_pic')->getClientOriginalExtension()) > 0)
            {
                $request->file('profile_pic')->move(base_path() . '/public/uploads/user/', $imageName);
                $input = array_merge($input, ['profile_pic' => $imageName]);
            }
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if($validator->fails()) 
        {
            $messageData = '';

            foreach($validator->messages()->toArray() as $message)
            {
                $messageData = $message[0];
            }
            return $this->failureResponse($validator->messages(), $messageData);
        }

        $status = $repository->updateUserStub($userInfo['userId'], $input);

        if($status)
        {
            $userObj = new User;

            $user = $userObj->find($userInfo['userId']);

            if($user)
            {
                $responseData = $this->userTransformer->updateUser($user);
                
                return $this->successResponse($responseData);
            }

            return $this->setStatusCode(200)->failureResponse([
                'error' => 'User not Found !'
            ], 'Something went wrong !');
        }

        return $this->setStatusCode(200)->failureResponse([
            'reason' => 'Invalid Inputs'
        ], 'Something went wrong !');     
    }*/

    /**
     * Change Password
     * 
     * @param Request $request
     * @return string
     */
    public function changePassword(Request $request)
    {
        if($request->has('password') && $request->has('old_password'))
        {   
            $userInfo = $this->getAuthenticatedUser();
            $credentials = [
                'email'     => $userInfo->email,
                'password'  => $request->get('old_password')
            ];

            if(! Auth::attempt($credentials))
            {
                return $this->setStatusCode(200)->failureResponse([
                    'reason' => 'Invalid Old Password'
                ], 'Invalid Old Password !');
            }

            $userInfo->password = bcrypt($request->get('password'));

            if ($userInfo->save()) 
            {
                event(new UserPasswordChanged($userInfo));

                $successResponse = [
                    'message' => 'Password Updated successfully.'
                ];
            
                return $this->successResponse($successResponse);
            }
        }

        return $this->setStatusCode(200)->failureResponse([
            'reason' => 'Invalid Inputs'
        ], 'Something went wrong !');
    }

    public function updageUserPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password'  => 'required',
        ]);

        if($validator->fails()) 
        {
            $messageData = '';

            foreach($validator->messages()->toArray() as $message)
            {
                $messageData = $message[0];
            }
            return $this->failureResponse($validator->messages(), $messageData);
        }
        
        $userInfo   = $this->getApiUserInfo();
        $user       = User::find($userInfo['userId']);

        $user->password = bcrypt($request->get('password'));

        if ($user->save())
        {
            $successResponse = [
                'message' => 'Password Updated successfully.'
            ];
            
            return $this->successResponse($successResponse, 'Password Updated successfully.');
        }

        return $this->setStatusCode(200)->failureResponse([
            'reason' => 'Invalid Inputs'
        ], 'Something went wrong !');
    }

    /**
     * Update User Profile
     * 
     * @param Request $request
     * @return json
     */
    public function updageUserProfile(Request $request)
    {
        $headerToken = request()->header('Authorization');

        if($headerToken)
        {
            $token      = explode(" ", $headerToken);
            $userToken  = $token[1];
        }
        
        $userInfo   = $this->getApiUserInfo();
        $repository = new UserRepository;
        $input      = $request->all();
        
        if($request->file('profile_pic'))
        {
            $imageName  = rand(11111, 99999) . '_user.' . $request->file('profile_pic')->getClientOriginalExtension();
            if(strlen($request->file('profile_pic')->getClientOriginalExtension()) > 0)
            {
                $request->file('profile_pic')->move(base_path() . '/public/uploads/user/', $imageName);
                $input = array_merge($input, ['profile_pic' => $imageName]);
            }
        }

        if($request->file('images'))
        {
            $files      = $request->file('images');
            $imageData  = [];

            foreach($files as $file)
            {
                $imageName  = rand(11111, 99999) . '_user.' . $file->getClientOriginalExtension();
                if(strlen($file->getClientOriginalExtension()) > 0)
                {
                    $file->move(base_path() . '/public/uploads/user/', $imageName);
                    $imageData[] = [
                        'user_id'   => $userInfo['userId'],
                        'image'     => $imageName
                    ];
                }  

                if(count($imageData))
                {
                    Images::insert($imageData);
                    unset($input['images']);
                }
            }
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if($validator->fails()) 
        {
            $messageData = '';

            foreach($validator->messages()->toArray() as $message)
            {
                $messageData = $message[0];
            }
            return $this->failureResponse($validator->messages(), $messageData);
        }

        $status = $repository->updateUserStub($userInfo['userId'], $input);

        if($status)
        {
            $userObj = new User;

            $user = $userObj->with('user_images')->find($userInfo['userId']);

            if($user)
            {
                $headerToken = request()->header('Authorization');

                if($headerToken)
                {
                    $token      = explode(" ", $headerToken);
                    $userToken  = $token[1];
                }
                
                $user->token = $userToken;  
            
                $responseData = $this->userTransformer->userInfo($user);
                
                return $this->successResponse($responseData);
            }

            return $this->setStatusCode(200)->failureResponse([
                'error' => 'User not Found !'
            ], 'Something went wrong !');
        }

        return $this->setStatusCode(200)->failureResponse([
            'reason' => 'Invalid Inputs'
        ], 'Something went wrong !');     
    }


    /**
     * Validate User
     * @param  Request $request
     * @return json
     */
    public function validateUser(Request $request) 
    {
        if($request->has('username'))
        {
            $phone = $request->has('phone') ? $request->get('phone') : false;


            $email = $request->has('email') ? $request->get('email') : false;

            if($phone && $email)
            {
                $user = User::where('username', $request->get('username'))
                    ->orWhere('phone', $phone)
                    ->orWhere('email', $email)
                    ->first();
            }
            else if ($phone)
            {
                $user = User::where('username', $request->get('username'))
                ->orWhere('phone', $phone)
                ->first();
            }
            else if ($email)
            {
                $user = User::where('username', $request->get('username'))
                ->orWhere('email', $email)
                ->first();
            }
            else
            {
                $user = User::where('username', $request->get('username'))->first();
            }

            if(isset($user) && isset($user->id))
            {
                return $this->setStatusCode(200)->failureResponse([
                    'reason' => 'User exist with Username or Phone Number!'
                ], 'User exist with Username or Phone Number');
            }
            else
            {
                $successResponse = [
                    'message' => 'No User found ! Continue for Signup.'
                ];

                return $this->successResponse($successResponse);
            }

        }

        return $this->setStatusCode(200)->failureResponse([
            'reason' => 'Invalid Input'
        ], 'Invalid Input');
    }

    public function testNotification(Request $request)
    {
        $text       = 'This is Test Push Notification';
        $payload    = [
            'mtitle' => '',
            'mdesc'  => $text,
        ];
                    
        if($request->get('device_token'))
        {
            PushNotification::iOS($payload, $request->get('device_token'));
            $successResponse = [
                    'message' => 'Push Notification Done'
            ];

            return $this->successResponse($successResponse);
        }

        PushNotification::iOS($payload, '4f224e9fae894057074cb1a20682bd665f8bcb57');
            $successResponse = [
                    'message' => 'Push Notification Done to Default Device'
            ];

        return $this->successResponse($successResponse);
    }

    public function changeDeviceToken(Request $request)
    {
        if($request->has('device_token'))        
        {
            $userInfo = $this->getAuthenticatedUser();

            $userInfo->device_token = $request->get('device_token');

            if($request->has('device_type'))
            {
                $userInfo->device_type = $request->get('device_type');
            }

            if($userInfo->save())
            {
                $successResponse = [
                    'message' => 'Device Token Updated successfully.'
                ];

                return $this->successResponse($successResponse);                
            }
        }

        return $this->setStatusCode(200)->failureResponse([
            'reason' => 'Invalid Input !'
        ], 'Invalid Input!');
    }

    /**
     * Invite Users
     * 
     * @param  Request $request
     * @return json
     */
    public function inviteUsers(Request $request)
    {
        $inviteUsers = $request->all();
        $appUsers    = User::all();
        $response    = [];

        if(isset($inviteUsers) && count($inviteUsers))
        {
            foreach($inviteUsers  as $inviteUser)
            {
                $email = isset($inviteUser['email']) ? $inviteUser['email'] : false;
                $phone = isset($inviteUser['phone']) ? $inviteUser['phone'] : false;
                $flag  = true;

                if(isset($email) && strlen($email) > 0)
                {
                    $emailExist = $appUsers->where('email', $email)->first();

                    if(isset($emailExist) && count($emailExist))
                    {
                        $flag = false;
                    }
                }

                if(isset($phone) && strlen($phone) > 0)
                {
                    $phoneExist = $appUsers->where('phone', $phone)->first();

                    if(isset($phoneExist) && count($phoneExist))
                    {
                        $flag = false;
                    }
                }

                if($flag)
                {
                   $response[] = $inviteUser;
                }
            }
        }

        return $this->successResponse($response);                
    }

    /**
     * Get Roster Users
     * 
     * @param Request $request
     * @return return
     */
    public function getRosterUsers(Request $request)
    {
        $userInfo       = $this->getAuthenticatedUser();
        $myInterestIds  = UserInterests::where('interested_user_id', $userInfo->id)->pluck('user_id')->toArray();

        $otherInterestIds = UserInterests::where('user_id', $userInfo->id)->pluck('interested_user_id')->toArray();

        $interestedIds = array_merge($myInterestIds, $otherInterestIds);

        $interestedIds = array_unique($interestedIds);

        /*$rosterUserIds  = UserInterests::where('interested_user_id', $userInfo->id)->orWhere('user_id', $userInfo->id)->pluck('user_id')->toArray();*/

        $users          = User::with('user_images')->where('id', '!=', $userInfo->id)->where('id', '!=', 1)->whereIn('id', $interestedIds)->get();
        
        $responseData = $this->userTransformer->showUsersTransform($users);

        return $this->successResponse($responseData);
    }

    /**
     * Get Users
     * 
     * @param Request $request
     * @return return
     */
    public function getUsers(Request $request)
    {
        $userInfo   = $this->getAuthenticatedUser();
        $settings   = access()->getUserSettings($userInfo->id);
        $blockUserIds = access()->getMyBlockedUserIds($userInfo->id);
        $distanceUsers = false;
        $condition  = [];

        if($request->has('latitude') && $request->has('longitude'))
        {
            $lat    = $request->get('latitude');
            $long   = $request->get('longitude');
            $distanceUsers  = DB::select("SELECT id, ( 6371 * acos( cos( radians($lat) ) * cos( radians( `latitude` ) ) * cos( radians( `longitude` ) - radians($long
                    ) ) + sin( radians($lat) ) * sin( radians( `latitude` ) ) ) ) AS distance
                FROM users
                ORDER BY distance ASC");
            if(isset($distanceUsers))
            {
                $distanceUsers = collect($distanceUsers);
            }
        }


        if(isset($settings->interested) && $settings->interested != 'Everyone')
        {
            $condition  = [
                'gender' => $settings->interested
            ];
        }
        $users      = User::with('user_images')->whereNotIn('id', $blockUserIds)->where($condition)->where('id', '!=', 1)->where('id', '!=', $userInfo->id)->get();

        $users  = $users->filter(function($item) use($settings, $distanceUsers) 
        {
            if($distanceUsers)
            {
                $isExist = $distanceUsers->where('id', $item->id)->first();

                if($isExist)
                {
                    $item->distance = $isExist->distance;
                }
            }
            
            $from = new \DateTime($item->birthdate);
            $to   = new \DateTime('today');
            $age  = $from->diff($to)->y;

            if(isset($settings->age_start_range) && isset($settings->age_end_range))
            {
                if($age >= $settings->age_start_range && $age <= $settings->age_end_range)
                {
                    return $item;
                }

                return null;
            }
            else
            {
                return $item;
            }
        });
        
        if(isset($settings->distance))
        {
            $users  = $users->where('distance', '>=', $settings->distance);
        }

        $responseData = $this->userTransformer->showUsersTransform($users);

        return $this->successResponse($responseData);
    }

    /**
     * Get User Profile
     * 
     * @param Request $request
     * @return return
     */
    public function getSingleUserProfile(Request $request)
    {
        if($request->has('user_id'))
        {
            $user = User::with('user_images')->where('id', $request->get('user_id'))->first();
            $responseData = $this->userTransformer->showSingleUserTransform($user);

            return $this->successResponse($responseData);
        }
       
        return response()->json([
        'error'     => 'No User Found for given details',
        'message'   => 'No User Found for given details',
        'status'    => false,
        ], 200);

    }

    

    /**
     * Get Users
     * 
     * @param Request $request
     * @return return
     */
    public function getBlockedUsers(Request $request)
    {
        $userInfo       = $this->getAuthenticatedUser();
        $blockUserIds   = BlockUsers::where('user_id', $userInfo->id)->pluck('block_user_id')->toArray();

        $users      = User::with('user_images')->where('id', '!=', 1)->whereIn('id', $blockUserIds)->get();

        $responseData = $this->userTransformer->showUsersTransform($users);

        return $this->successResponse($responseData);
    }

    public function checkUser(Request $request)
    {
        if($request->has('social_token'))
        {
            $user = User::where([
                'social_token'=> $request->get('social_token')
            ])->first();

            if(isset($user) && $user->id)
            {
                Auth::loginUsingId($user->id, true);

                if($request->get('device_token'))
                {
                    $user = Auth::user();
                    $user->device_token = $request->get('device_token');
                    $user->save();
                }

                $user       = Auth::user()->toArray();
                $token      = JWTAuth::fromUser(Auth::user());
                $userData   = array_merge($user, ['token' => $token]);
                $responseData = $this->userTransformer->transform((object)$userData);

                return $this->successResponse($responseData);
            }
        }   
        
        return response()->json([
            'error'     => 'Invalid Credentials',
            'message'   => 'No User Found for given details',
            'status'    => false,
            ], 200);
    }

    /**
     * Get Social Token
     * 
     * @param  Request $request
     * @return array
     */
    public function getSocialToken(Request $request)
    {
        $userInfo = $this->getAuthenticatedUser();
        
        return $this->successResponse([
            'spotify_token' => isset($userInfo->spotify_token) ? $userInfo->spotify_token : '',
            'insta_token'   => isset($userInfo->insta_token) ? $userInfo->insta_token : ''
        ]);        
    }

    /**
     * update Social Token
     * 
     * @param  Request $request
     * @return array
     */
    public function updateSocialToken(Request $request)
    {
        $userInfo = $this->getAuthenticatedUser();
        $user     = User::where('id', $userInfo->id)->first();

        if($request->has('spotify_token'))
        {
            $user->spotify_token    = $request->get('spotify_token');
        }

        if($request->has('spotify_user_id'))
        {
            $user->spotify_user_id = $request->get('spotify_user_id');
        }

        if($request->has('spotify_images'))
        {
            $spotifyImages = $request->get('spotify_images');
            $spotifyData   = [];

            foreach($spotifyImages as $spotifyImage)
            {
                $spotifyData[] = [ 
                    'user_id'       => $userInfo->id,
                    'social_type'   => 'spotify',
                    'image_url'     => $spotifyImage
                ];
            }

            if(count($spotifyData) > 0)
            {
                SocialImages::where([
                    'user_id'       => $userInfo->id,
                    'social_type'   => 'spotify'
                ])->delete();

                SocialImages::insert($spotifyData);
            }
        }

        if($request->has('insta_token'))
        {
            $user->insta_token = $request->get('insta_token');
        }

        if($request->has('insta_images'))
        {
            $instaImages    = $request->get('insta_images');
            $instaData      = [];

            foreach($instaImages as $instaImage)
            {
                $instaData[] = [ 
                    'user_id'       => $userInfo->id,
                    'social_type'   => 'instagram',
                    'image_url'     => $instaImage
                ];
            }

            if(count($instaData) > 0)
            {
                SocialImages::where([
                    'user_id'       => $userInfo->id,
                    'social_type'   => 'instagram'
                ])->delete();

                SocialImages::insert($instaData);
            }
        }
        
        if($user->save())
        {
            $userInstaImages        = [];
            $userSpotifyImages      = [];

            if(isset($user->social_images) && count($user->social_images))
            {
                foreach($user->social_images as $socialImage)
                {
                    if($socialImage->social_type == 'instagram')   
                    {
                        $userInstaImages[] = [
                            'social_image_id'   => (int) $socialImage->id,
                            'social_image_url'  => $socialImage->image_url
                        ];
                    }
                    else
                    {
                        $userSpotifyImages[] = [
                            'social_image_id'   => (int) $socialImage->id,
                            'social_image_url'  => $socialImage->image_url
                        ];
                    }
                }
            }
            return $this->successResponse([
                'spotify_token' => $user->spotify_token,
                'insta_token'   => $user->insta_token,
                'insta_images'  => $userInstaImages,
                'spotify_images'  => $userSpotifyImages

            ]);        
        }
        
        return response()->json([
        'error'     => 'No User Found for given details',
        'message'   => 'No User Found for given details',
        'status'    => false,
        ], 200);
    }
}
