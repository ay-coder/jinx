<?php namespace App\Models\Messages;

/**
 * Class Messages
 *
 * @author Anuj Jaha ( er.anujjaha@gmail.com)
 */

use App\Models\BaseModel;
use App\Models\Messages\Traits\Attribute\Attribute;
use App\Models\Messages\Traits\Relationship\Relationship;

class Messages extends BaseModel
{
    use Attribute, Relationship;
    /**
     * Database Table
     *
     */
    protected $table = "data_messages";

    /**
     * Fillable Database Fields
     *
     */
    protected $fillable = [
        "id", "user_id", "other_user_id", "message", "is_read", "created_at", "updated_at", 
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