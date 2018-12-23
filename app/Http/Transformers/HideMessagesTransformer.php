<?php
namespace App\Http\Transformers;

use App\Http\Transformers;

class HideMessagesTransformer extends Transformer
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
            "hidemessagesId" => (int) $item->id, "hidemessagesUserId" =>  $item->user_id, "hidemessagesMessageId" =>  $item->message_id, "hidemessagesCreatedAt" =>  $item->created_at, "hidemessagesUpdatedAt" =>  $item->updated_at, 
        ];
    }
}