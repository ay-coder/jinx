<?php
namespace App\Http\Transformers;

use App\Http\Transformers;
use URL;

class UserNotificationsTransformer extends Transformer
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

        $item->user         = (object)$item->user;
        $item->other_user   = (object)$item->other_user;

        return [
            "notification_id"   => (int) $item->id,
            "user_id"           => (int) $item->user_id,
            "other_user_id"     => (int) $item->other_user_id,
            "message_id"        => (int) $item->message_id,
            "title"             => $this->nulltoBlank($item->title),
            "notification_type" => $this->nulltoBlank($item->notification_type),
            "is_read"           => (int) $item->is_read,
            "user_name"         => isset($item->user) ? $item->user->name : '',
            "user_profile_pic"  => isset($item->user) ? URL::to('/').'/uploads/user/' . $item->user->profile_pic : '',
            'other_user_name'  => isset($item->other_user) ? $item->other_user->name : '',
            'other_user_profile_pic'   => isset($item->other_user) ? URL::to('/').'/uploads/user/' . $item->other_user->profile_pic : '',
            "created_at"        => $item->created_at 
        ];
    }
}