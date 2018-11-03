<?php
namespace App\Http\Transformers;

use App\Http\Transformers;

class UserInterestsTransformer extends Transformer
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
            "userinterestsId" => (int) $item->id, "userinterestsUserId" =>  $item->user_id, "userinterestsInterestedUserId" =>  $item->interested_user_id, "userinterestsIsAccepted" =>  $item->is_accepted, "userinterestsIsDecline" =>  $item->is_decline, "userinterestsDescription" =>  $item->description, "userinterestsCreatedAt" =>  $item->created_at, "userinterestsUpdatedAt" =>  $item->updated_at, 
        ];
    }
}