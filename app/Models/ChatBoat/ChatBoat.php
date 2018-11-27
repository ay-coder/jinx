<?php namespace App\Models\ChatBoat;

/**
 * Class ChatBoat
 *
 * @author Anuj Jaha ( er.anujjaha@gmail.com)
 */

use App\Models\BaseModel;
use App\Models\ChatBoat\Traits\Attribute\Attribute;
use App\Models\ChatBoat\Traits\Relationship\Relationship;

class ChatBoat extends BaseModel
{
    use Attribute, Relationship;
    /**
     * Database Table
     *
     */
    protected $table = "data_chat_boat";

    /**
     * Fillable Database Fields
     *
     */
    protected $fillable = [
        "id", "user_id", "other_user_id", "question", "user_answer", "other_user_answer", "accept_user_id", "accept_other_user_id", "is_ready", "user_answer_time", "other_user_answer_time", "created_at", "updated_at", 
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