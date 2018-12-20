<?php
namespace App\Http\Transformers;

use App\Http\Transformers;

class AdminMessagesTransformer extends Transformer
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
            "adminmessagesId" => (int) $item->id, "adminmessagesUserId" =>  $item->user_id, "adminmessagesMessageId" =>  $item->message_id, "adminmessagesCreatedAt" =>  $item->created_at, "adminmessagesUpdatedAt" =>  $item->updated_at, 
        ];
    }
}