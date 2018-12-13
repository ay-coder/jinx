<?php namespace App\Models\TempBlock;

/**
 * Class TempBlock
 *
 * @author Anuj Jaha ( er.anujjaha@gmail.com)
 */

use App\Models\BaseModel;
use App\Models\TempBlock\Traits\Attribute\Attribute;
use App\Models\TempBlock\Traits\Relationship\Relationship;

class TempBlock extends BaseModel
{
    use Attribute, Relationship;
    /**
     * Database Table
     *
     */
    protected $table = "data_users_temp_block";

    /**
     * Fillable Database Fields
     *
     */
    protected $fillable = [
        "id", "user_id", "block_user_id", "description", "created_at", "updated_at", 
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