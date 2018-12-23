<?php namespace App\Models\HideMessages;

/**
 * Class HideMessages
 *
 * @author Anuj Jaha ( er.anujjaha@gmail.com)
 */

use App\Models\BaseModel;
use App\Models\HideMessages\Traits\Attribute\Attribute;
use App\Models\HideMessages\Traits\Relationship\Relationship;

class HideMessages extends BaseModel
{
    use Attribute, Relationship;
    /**
     * Database Table
     *
     */
    protected $table = "data_hide_messages";

    /**
     * Fillable Database Fields
     *
     */
    protected $fillable = [
        "id", "user_id", "message_id", "created_at", "updated_at", 
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