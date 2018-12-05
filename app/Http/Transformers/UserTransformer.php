<?php

namespace App\Http\Transformers;

use URL;
use App\Http\Transformers;

class UserTransformer extends Transformer 
{
    public function transform($data) 
    {
        $userImages[] = [
            'image_id'  => 0,
            'image'     => URL::to('/').'/uploads/user/' . $data->profile_pic
        ];

        if(isset($images))
        {
            foreach($images as $image)   
            {
                $userImages[] = [
                    'image_id'  => (int) $image->id,
                    'image'     =>  URL::to('/').'/uploads/user/'. $image->image
                ];
            }
        }
        return [
            'user_id'       => $data->id,
            'username'      => $data->username,
            'token'         => $this->nulltoBlank($data->token),
            'device_token'  => $data->device_token,
            'spotify_token' => $this->nulltoBlank($data->spotify_token),
            'insta_token'   => $this->nulltoBlank($data->insta_token),
            'device_type'   =>   $data->device_type,
            'social_token'   =>   $this->nulltoBlank($data->social_token),
            'social_provider'   =>   $this->nulltoBlank($data->social_provider),
            'profession'    => $this->nulltoBlank($data->profession),
            'education'     => $this->nulltoBlank($data->education),
            'name'          => $this->nulltoBlank($data->name),
            'email'         => $this->nulltoBlank($data->email),
            'latitude'         => $this->nulltoBlank($data->latitude),
            'longitude'         => $this->nulltoBlank($data->longitude),
            'education'         => $this->nulltoBlank($data->education),
            'profession'         => $this->nulltoBlank($data->profession),
            'birthdate'         => $this->nulltoBlank($data->birthdate),
            'gender'         => $this->nulltoBlank($data->gender),
            'address'         => $this->nulltoBlank($data->address),
            'city'         => $this->nulltoBlank($data->city),
            'state'         => $this->nulltoBlank($data->state),
            'bio'           => $this->nulltoBlank($data->bio),
            'signup_by'     => (int) $data->signup_by,
            'phone'         => $this->nulltoBlank($data->phone),
            'birthdate'     => $this->nulltoBlank($data->birthdate),
            'profile_pic'   => isset($data->profile_pic) ? URL::to('/').'/uploads/user/' . $data->profile_pic : '',
            'images'        => $userImages
        ];
    }
    
    public function userInfo($data)
    {
        $data       = (object) $data;
        $images     = (object) $data->user_images;
        $userImages[] = [
            'image_id'  => 0,
            'image'     => URL::to('/').'/uploads/user/' . $data->profile_pic
        ];

        if(isset($images))
        {
            foreach($images as $image)   
            {
                $userImages[] = [
                    'image_id'  => (int) $image->id,
                    'image'     =>  URL::to('/').'/uploads/user/'. $image->image
                ];
            }
        }

        return [
            'user_id'       => $data->id,
            'username'      => $data->username,
            'username'      => $data->username,
            'token'         => isset($data->token) ? $this->nulltoBlank($data->token) : '',
            'device_token'  => $data->device_token,
            'device_type'   => $this->nulltoBlank($data->device_type),
            'profession'    => $this->nulltoBlank($data->profession),
            'education'     => $this->nulltoBlank($data->education),
            'name'          => $this->nulltoBlank($data->name),
            'email'         => $this->nulltoBlank($data->email),
            'bio'           => $this->nulltoBlank($data->bio),
            'signup_by'     => (int) $data->signup_by,
            'phone'         => $this->nulltoBlank($data->phone),
            'profile_pic'   => isset($data->profile_pic) ? URL::to('/').'/uploads/user/' . $data->profile_pic : '',
            'address'       => $this->nulltoBlank($data->address),
            'city'          => $this->nulltoBlank($data->city),
            'zip'           => $this->nulltoBlank($data->zip),
            'birthdate'     => $this->nulltoBlank($data->birthdate),
            'gender'        => $this->nulltoBlank($data->gender),
            'spotify_token' => $this->nulltoBlank($data->spotify_token),
            'insta_token'   => $this->nulltoBlank($data->insta_token),
            'images'        => $userImages
        ];
    }

    public function getUserInfo($data) 
    {
        return [
            'userId'    => $data->id,
            'name'      => $this->nulltoBlank($data->name),
            'email'     => $this->nulltoBlank($data->email)
        ];
    }
    
    /**
     * userDetail
     * Single user detail
     * 
     * @param type $data
     * @return type
     */
    public function userDetail($data) {
        return [
            'UserId' => isset($data['id']) ? $data['id'] : "",
            'QuickBlocksId' => isset($data['quick_blocks_id']) ? $data['quick_blocks_id'] : "",
            'MobileNumber' => isset($data['mobile_number']) ? $data['mobile_number'] : "",
            'Name' => isset($data['username']) ? $data['username'] : "",
            'Specialty' => isset($data['specialty']) ? $data['specialty'] : "",
            'ProfilePhoto' => isset($data['profile_photo'])?$this->getUserImage($data['profile_photo']):""
        ];
    }

    /*
     * User Detail and it's parameters
     */
    public function singleUserDetail($data){        
        return [
            'UserId' => $data['id'],            
            'Name' => $this->nulltoBlank($data['name']),
            'Email' => $this->nulltoBlank($data['email']),
            'MobileNumber' => $this->nulltoBlank($data['mobile_number']),
        ];
    }
    
    public function transformStateCollection(array $items) {
        return array_map([$this, 'getState'], $items);

    }

    /**
     * Update User
     * 
     * @param object $data
     * @return array
     */
    public function updateUser($data)
    {
        $headerToken = request()->header('Authorization');
        $userToken   = '';

        if($headerToken)
        {
            $token      = explode(" ", $headerToken);
            $userToken  = $token[1];
        }

        return [
            'user_id'       => $data->id,
            'username'      => $data->username,
            'token'         => $this->nulltoBlank($data->token),
            'device_token'  => $data->device_token,
            'device_type'   =>   $data->device_type,
            'profession'    => $this->nulltoBlank($data->profession),
            'education'     => $this->nulltoBlank($data->education),
            'name'          => $this->nulltoBlank($data->name),
            'spotify_token' => $this->nulltoBlank($data->spotify_token),
            'insta_token'   => $this->nulltoBlank($data->insta_token),
            'email'         => $this->nulltoBlank($data->email),
            'phone'         => $this->nulltoBlank($data->phone),
            'profile_pic'   => isset($data->profile_pic) ? URL::to('/').'/uploads/user/' . $data->profile_pic : '',
            'bio'           => $this->nulltoBlank($data->bio),
        ]; 
    }

    public function showUsersTransform($users)
    {
        $response = [];

        if(isset($users))
        {
            foreach($users as $user)
            {
                $images = [];

                $images[] = [
                    'image_id'  => 0,
                    'image'     => URL::to('/').'/uploads/user/' . $user->profile_pic
                ];

                if(isset($user->user_images))                
                {   
                    foreach($user->user_images as $userImage)
                    {
                        $images[] = [
                            'image_id'  => (int) $userImage->id,
                            'image'     => URL::to('/').'/uploads/user/'.$userImage->image
                        ];
                    }
                }

                $response[] = [
                    'user_id'       => (int) $user->id,
                    'name'          => $this->nulltoBlank($user->name),
                    'email'         => $this->nulltoBlank($user->email),
                    'phone'         => $this->nulltoBlank($user->phone),
                    'profile_pic'   => isset($user->profile_pic) ? URL::to('/').'/uploads/user/' . $user->profile_pic : '',
                    'bio'           => $this->nulltoBlank($user->bio),
                    'gender'        => $this->nulltoBlank($user->gender),
                    'profession'    => $this->nulltoBlank($user->profession),
                    'education'     => $this->nulltoBlank($user->education),
                    'birthdate'     => $this->nulltoBlank($user->birthdate),
                    'spotify_token' => $this->nulltoBlank($data->spotify_token),
                    'insta_token'   => $this->nulltoBlank($data->insta_token),
                    'distance'      => 10,
                    'address'       => $this->nulltoBlank($user->address) . ' '.$this->nulltoBlank($user->city),
                    'userImages'    => $images
                ];
            }
        }

        return $response;
    }

    /**
     * Show Single User Transform
     * 
     * @param object $user
     * @return array
     */
    public function showSingleUserTransform($user)
    {
        if(isset($user))
        {
            $images[] = [
                'image_id'  => 0,
                'image'     => URL::to('/').'/uploads/user/' . $user->profile_pic
            ];

            if(isset($user->user_images))                
            {   
                foreach($user->user_images as $userImage)
                {
                    $images[] = [
                        'image_id'  => (int) $userImage->id,
                        'image'     => URL::to('/').'/uploads/user/'.$userImage->image
                    ];
                }
            }

            return [
                'user_id'       => (int) $user->id,
                'name'          => $this->nulltoBlank($user->name),
                'email'         => $this->nulltoBlank($user->email),
                'phone'         => $this->nulltoBlank($user->phone),
                'profile_pic'   => isset($user->profile_pic) ? URL::to('/').'/uploads/user/' . $user->profile_pic : '',
                'bio'           => $this->nulltoBlank($user->bio),
                'gender'        => $this->nulltoBlank($user->gender),
                'profession'    => $this->nulltoBlank($user->profession),
                'education'     => $this->nulltoBlank($user->education),
                'birthdate'     => $this->nulltoBlank($user->birthdate),
                'spotify_token' => $this->nulltoBlank($data->spotify_token),
                'insta_token'   => $this->nulltoBlank($data->insta_token),
                'distance'      => 10,
                'address'       => $this->nulltoBlank($user->address) . ' '.$this->nulltoBlank($user->city),
                'userImages'    => $images
            ];
        }

        return [];
    }
}
