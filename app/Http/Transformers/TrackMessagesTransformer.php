<?php
namespace App\Http\Transformers;

use App\Http\Transformers;

class TrackMessagesTransformer extends Transformer
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
            "trackmessagesId" => (int) $item->id, "trackmessagesUserId" =>  $item->user_id, "trackmessagesOtherUserId" =>  $item->other_user_id, "trackmessagesLastMessageUserId" =>  $item->last_message_user_id, "trackmessagesIsAdmin" =>  $item->is_admin, "trackmessagesLastMessageCreatedAt" =>  $item->last_message_created_at, "trackmessagesCreatedAt" =>  $item->created_at, "trackmessagesUpdatedAt" =>  $item->updated_at, 
        ];
    }
}