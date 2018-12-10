<?php
namespace App\Http\Transformers;

use App\Http\Transformers;
use URL;

class MessagesTransformer extends Transformer
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
            "messagesId" => (int) $item->id, "messagesUserId" =>  $item->user_id, "messagesOtherUserId" =>  $item->other_user_id, "messagesMessage" =>  $item->message, "messagesIsRead" =>  $item->is_read, "messagesCreatedAt" =>  $item->created_at, "messagesUpdatedAt" =>  $item->updated_at, 
        ];
    }

    public function messageTranform($items)
    {
        $response = [];

        if($items)   
        {
            $currentUserId = access()->user()->id;
            foreach($items as $item)
            {
                $isRead     = $currentUserId == $item->user_id ? 1 : $item->is_read;
                $response[] = $this->singleMessageTranform($item);
            }
        }

        return $response;
    }

    public function singleMessageTranform($item)
    {
        if($item)   
        {
            $currentUserId = access()->user()->id;
            
            $isRead = $currentUserId == $item->user_id ? 1 : $item->is_read;
            return [
                'message_id'    => (int) $item->id,
                'user_id'       => (int) $item->user_id,
                'is_admin'      => (int) $item->is_admin,
                'other_user_id'   => (int) $item->other_user_id,
                'message'       => $item->message,
                'user_name'      => isset($item->user) ? $item->user->name : '',
                'uesr_profile_pic'  => isset($item->user) ? URL::to('/').'/uploads/user/' . $item->user->profile_pic : '',
                'other_user_name'  => isset($item->other_user) ? $item->other_user->name : '',
                'other_user_patient_pic'   => isset($item->other_user) ? URL::to('/').'/uploads/user/' . $item->other_user->profile_pic : '',
                'is_read'       => $isRead,
                'created_at'    => date('Y-m-d H:i:s', strtotime($item->created_at))
            ];
        }

        return [];

    }
}