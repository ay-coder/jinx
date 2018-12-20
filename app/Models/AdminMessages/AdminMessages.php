<?php namespace App\Models\AdminMessages;

/**
 * Class AdminMessages
 *
 * @author Anuj Jaha ( er.anujjaha@gmail.com)
 */

use App\Models\BaseModel;
use App\Models\AdminMessages\Traits\Attribute\Attribute;
use App\Models\AdminMessages\Traits\Relationship\Relationship;

class AdminMessages extends BaseModel
{
    use Attribute, Relationship;
    /**
     * Database Table
     *
     */
    protected $table = "data_admin_unread_messages";

    /**
     * Fillable Database Fields
     *
     */
    protected $fillable = [
        "id", "user_id", "other_user_id", "message_id", "created_at", "updated_at", 
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