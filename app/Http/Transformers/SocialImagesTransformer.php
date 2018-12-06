<?php
namespace App\Http\Transformers;

use App\Http\Transformers;

class SocialImagesTransformer extends Transformer
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
            "socialimagesId" => (int) $item->id, "socialimagesUserId" =>  $item->user_id, "socialimagesImageUrl" =>  $item->image_url, "socialimagesSocialType" =>  $item->social_type, "socialimagesCreatedAt" =>  $item->created_at, "socialimagesUpdatedAt" =>  $item->updated_at, 
        ];
    }
}