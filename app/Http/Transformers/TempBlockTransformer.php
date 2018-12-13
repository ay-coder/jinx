<?php
namespace App\Http\Transformers;

use App\Http\Transformers;

class TempBlockTransformer extends Transformer
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
            "tempblockId" => (int) $item->id, "tempblockUserId" =>  $item->user_id, "tempblockBlockUserId" =>  $item->block_user_id, "tempblockDescription" =>  $item->description, "tempblockCreatedAt" =>  $item->created_at, "tempblockUpdatedAt" =>  $item->updated_at, 
        ];
    }
}