<?php namespace App\Models\Images;

/**
 * Class Images
 *
 * @author Anuj Jaha ( er.anujjaha@gmail.com)
 */

use App\Models\BaseModel;
use App\Models\Images\Traits\Attribute\Attribute;
use App\Models\Images\Traits\Relationship\Relationship;

class Images extends BaseModel
{
    use Attribute, Relationship;
    /**
     * Database Table
     *
     */
    protected $table = "data_user_images";

    /**
     * Fillable Database Fields
     *
     */
    protected $fillable = [
        "id", "user_id", "image", "created_at", "updated_at", 
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