<?php
namespace App\Http\Transformers;

use App\Http\Transformers;
use URL;

class ConnectionsTransformer extends Transformer
{
    /**
     * Transform
     *
     * @param array $data
     * @return array
     */
    public function transform($item)
    {
        if(is_array($item))
        {
            $item = (object)$item;
        }

        return [
            "connectionsId" => (int) $item->id, "connectionsUserId" =>  $item->user_id, "connectionsOtherUserId" =>  $item->other_user_id, "connectionsRequestedUserId" =>  $item->requested_user_id, "connectionsIsAccepted" =>  $item->is_accepted, "connectionsIsRead" =>  $item->is_read, "connectionsCreatedAt" =>  $item->created_at, "connectionsUpdatedAt" =>  $item->updated_at, 
        ];
    }

    
    public function myRequestTransform($items)
    {
        $response = [];

        if($items)
        {
            foreach($items as $item)
            {
                $response[] = [
                    'request_id'        => (int) $item->id,
                    'other_user_id'     => (int) $item->other_user_id,
                    'requested_user_id' => (int) $item->user->id,
                    'user_id'           => $item->user->id,
                    'name'              => $item->other_user->name,
                    'email'             => $item->other_user->email,
                    'phone'             => $item->other_user->phone,
                    'profile_pic'       => isset($item->other_user->profile_pic) ? URL::to('/').'/uploads/user/' . $item->other_user->profile_pic : ''
                ];
            }
        }

        return $response;
    }

    public function requestTransform($items)
    {
        $response = [];

        if($items)
        {
            foreach($items as $item)
            {
                $response[] = [
                    'request_id'        => (int) $item->id,
                    'other_user_id'     => (int) $item->other_user_id,
                    'requested_user_id' => (int) $item->user->id,
                    'user_id'           => $item->user->id,
                    'name'              => $item->user->name,
                    'email'             => $item->user->email,
                    'phone'             => $item->user->phone,
                    'profile_pic'       => isset($item->user->profile_pic) ? URL::to('/').'/uploads/user/' . $item->user->profile_pic : ''
                ];
            }
        }

        return $response;
    }

    /**
     * Connection Transform
     * 
     * @param object $items
     * @return array
     */
    public function connectionTransform($items)
    {
        $response = [];

        if(isset($items) && count($items))
        {
            foreach($items as $data)
            {
                $response[] = [
                    'user_id'       => (int) $data->id,
                    'name'          => $this->nulltoBlank($data->name),
                    'email'         => $this->nulltoBlank($data->email),
                    'phone'         => $this->nulltoBlank($data->phone),
                    'profile_pic'   => isset($data->profile_pic) ? URL::to('/').'/uploads/user/' . $data->profile_pic : '',
                    'bio'           => $this->nulltoBlank($data->bio),
                ];
            }
        }

        return $response;
    }

    public function searchTranform($items)
    {
        $response = [];

        if(isset($items) && count($items))
        {
            foreach($items as $data)
            {
                $response[] = [
                    'user_id'       => (int) $data->id,
                    'name'          => $this->nulltoBlank($data->name),
                    'email'         => $this->nulltoBlank($data->email),
                    'phone'         => $this->nulltoBlank($data->phone),
                    'profile_pic'   => isset($data->profile_pic) ? URL::to('/').'/uploads/user/' . $data->profile_pic : '',
                    'dob'           => $this->nulltoBlank($data->dob),
                    'gender'        => $this->nulltoBlank($data->gender)
                ];
            }
        }

        return $response;
    }

    public function searchUserTranform($items, $myConnectionList = array(), $userRequestIds = array(), $userInfo = null, $myRequestIds = array())
    {
        $response = [];

        if(isset($items) && count($items))
        {
            foreach($items as $data)
            {
                if(isset($userInfo) && $userInfo->id == $data->id)
                {
                    continue;
                }

                if(in_array($data->id, $myRequestIds))
                {
                    continue;
                }

                $isConnected    = in_array($data->id, $myConnectionList) ? 1 : 0;
                $response[]     = [
                    'user_id'       => (int) $data->id,
                    'name'          => $this->nulltoBlank($data->name),
                    'email'         => $this->nulltoBlank($data->email),
                    'phone'         => $this->nulltoBlank($data->phone),
                    'is_connected'  => $isConnected,
                    'profile_pic'   => isset($data->profile_pic) ? URL::to('/').'/uploads/user/' . $data->profile_pic : '',
                ];
            }
        }

        return $response;
    }

    public function searchAppUserTranform($items, $userInfo = null, $allConnections = array())
    {
        $response = [];

        if(isset($items) && count($items))
        {
            foreach($items as $data)
            {
                if(isset($userInfo) && $userInfo->id == $data->id)
                {
                    continue;
                }

                if(in_array($data->id, $allConnections))
                {
                    continue;
                }
                
                $response[]     = [
                    'user_id'       => (int) $data->id,
                    'name'          => $this->nulltoBlank($data->name),
                    'email'         => $this->nulltoBlank($data->email),
                    'phone'         => $this->nulltoBlank($data->phone),
                    'profile_pic'   => isset($data->profile_pic) ? URL::to('/').'/uploads/user/' . $data->profile_pic : '',
                ];
            }
        }

        return $response;
    }
}