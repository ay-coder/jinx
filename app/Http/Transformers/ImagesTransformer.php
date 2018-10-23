<?php
namespace App\Http\Transformers;

use App\Http\Transformers;

class ImagesTransformer extends Transformer
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
            "imagesId" => (int) $item->id, "imagesUserId" =>  $item->user_id, "imagesImage" =>  $item->image, "imagesCreatedAt" =>  $item->created_at, "imagesUpdatedAt" =>  $item->updated_at, 
        ];
    }
}