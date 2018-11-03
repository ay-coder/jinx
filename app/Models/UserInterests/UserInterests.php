<?php namespace App\Models\UserInterests;

/**
 * Class UserInterests
 *
 * @author Anuj Jaha ( er.anujjaha@gmail.com)
 */

use App\Models\BaseModel;
use App\Models\UserInterests\Traits\Attribute\Attribute;
use App\Models\UserInterests\Traits\Relationship\Relationship;

class UserInterests extends BaseModel
{
    use Attribute, Relationship;
    /**
     * Database Table
     *
     */
    protected $table = "data_user_interests";

    /**
     * Fillable Database Fields
     *
     */
    protected $fillable = [
        "id", "user_id", "interested_user_id", "is_accepted", "is_decline", "description", "created_at", "updated_at", 
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