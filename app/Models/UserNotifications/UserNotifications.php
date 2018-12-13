<?php namespace App\Models\UserNotifications;

/**
 * Class UserNotifications
 *
 * @author Anuj Jaha ( er.anujjaha@gmail.com)
 */

use App\Models\BaseModel;
use App\Models\UserNotifications\Traits\Attribute\Attribute;
use App\Models\UserNotifications\Traits\Relationship\Relationship;

class UserNotifications extends BaseModel
{
    use Attribute, Relationship;
    /**
     * Database Table
     *
     */
    protected $table = "data_user_notifications";

    /**
     * Fillable Database Fields
     *
     */
    protected $fillable = [
        "id", "user_id", "other_user_id", "message_id", "title", "notification_type", "is_read", "is_deleted", "created_at", "updated_at", 
    ];

    /**
     * Timestamp flag
     *
     */
    public $timestamps = true;

    /**
     * Guarded ID Column
     *
     */
    protected $guarded = ["id"];
}