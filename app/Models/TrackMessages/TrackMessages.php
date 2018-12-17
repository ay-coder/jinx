<?php namespace App\Models\TrackMessages;

/**
 * Class TrackMessages
 *
 * @author Anuj Jaha ( er.anujjaha@gmail.com)
 */

use App\Models\BaseModel;
use App\Models\TrackMessages\Traits\Attribute\Attribute;
use App\Models\TrackMessages\Traits\Relationship\Relationship;

class TrackMessages extends BaseModel
{
    use Attribute, Relationship;
    /**
     * Database Table
     *
     */
    protected $table = "data_messages_monitor";

    /**
     * Fillable Database Fields
     *
     */
    protected $fillable = [
        "id", "user_id", "other_user_id", "last_message_user_id", "is_admin", "last_message_created_at", "created_at", "updated_at", 
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