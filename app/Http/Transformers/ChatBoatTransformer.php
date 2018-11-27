<?php
namespace App\Http\Transformers;

use App\Http\Transformers;
use URL;

class ChatBoatTransformer extends Transformer
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
            "chatboatId" => (int) $item->id, "chatboatUserId" =>  $item->user_id, "chatboatOtherUserId" =>  $item->other_user_id, "chatboatQuestion" =>  $item->question, "chatboatUserAnswer" =>  $item->user_answer, "chatboatOtherUserAnswer" =>  $item->other_user_answer, "chatboatAcceptUserId" =>  $item->accept_user_id, "chatboatAcceptOtherUserId" =>  $item->accept_other_user_id, "chatboatUserAnswerTime" =>  $item->user_answer_time, "chatboatOtherUserAnswerTime" =>  $item->other_user_answer_time, "chatboatCreatedAt" =>  $item->created_at, "chatboatUpdatedAt" =>  $item->updated_at, 
        ];
    }

    /**
     * Transform ChatBoat
     *
     * @param object $items
     * @return array
     */
    public function transformChatBoat($items)
    {
        $response = [];

        if($items)
        {
            foreach($items as $item)
            {
                $response[] = [
                    'chat_boat_id'  => (int) $item->id,
                    'question'      => $item->question,
                    'user_id'       => isset($item->user) ? $item->user->id : 0,
                    'other_user_id'       => isset($item->other_user) ? $item->other_user->id : 0,
                    'user_name'      => isset($item->user) ? $item->user->name : '',
                    'user_profile_pic'  => isset($item->user) ? URL::to('/').'/uploads/user/' . $item->user->profile_pic : '',
                    'other_user_name'  => isset($item->other_user) ? $item->other_user->name : '',
                    'other_user_profile_pic'   => isset($item->other_user) ? URL::to('/').'/uploads/user/' . $item->other_user->profile_pic : '',
                    'created_at'    => date('Y-m-d H:i:s', strtotime($item->created_at))
                ];
            }
        }

        return $response;   
    }
}